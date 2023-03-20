<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.org>
 * Nov 25, 2013 1:06:48 PM
 */

function cekNomor($doc, $id) {
    $res = new xajaxResponse();
    if (cgx_fetch_table("SELECT * FROM m_inout WHERE document_no = '{$doc}' AND m_inout_id <> '{$id}'")){
        $error = "Nomor receipt =  ". $doc ."  Sudah dipakai";
        $res->alert($error);
        return $res;
    }
}

function ctl_edit_good_rcp($data) {
    if ($data['record']['m_inout_line_id']) {
        $href = "xajax_editForm('{$data['record']['line']}');";
        $out = "<img onclick=\"{$href}\" style='cursor: pointer;' src='images/icon_edit.png' border='0'>";
    return $out;
    }
}

function ctl_edit_coil($data) {
    if ($data['record']['m_coil_id']) {
        $href = "xajax_editFormDua('{$data['record']['line']}');";
        $out = "<img onclick=\"{$href}\" style='cursor: pointer;' src='images/icon_edit.png' border='0'>";
    return $out;
    }
}

function ctl_delete_coil($data) {
    if ($data['record']['m_coil_id']) {
        $href = "xajax_deleteLineDua('{$data['record']['line']}');";
        $out = "<img onclick=\"{$href}\" style='cursor: pointer;' title='Hapus' src='images/icon_delete.png' border='0'>";
        return $out;
    }
}

function savegood_rcp($data) {
    global $APP_ID, $APP_CONNECTION;
    $good_rcp = $_SESSION[$APP_ID]['good_rcp'];
    $res = new xajaxResponse();
    
    if (empty($data['document_no'])) {
        $error = "Nomor Receipt tidak boleh kosong";
    } elseif (empty($data['c_order_id'])) {
        $error = "Purchase order tidak boleh kosong";
    } elseif (cgx_emptydate($data['m_inout_date'])) {
        $error = "Tanggal penerimaan tidak boleh kosong";
    } elseif (count($good_rcp['lines']) == 0) {
        $error = "Detail item barang tidak boleh kosong, silahkan pilih minimal satu barang yang akan dikirim.";
    }elseif (cgx_fetch_table("SELECT * FROM m_inout WHERE document_no = '{$data['document_no']}' AND m_inout_id <> '{$data['m_inout_id']}'")){
        $error = "Nomor receipt = ". $data['document_no'] ." Sudah ada/dipakai";
    }

    if ($error) {
        $res->alert($error);
        return $res;
    }

    if ($data['m_inout_id']) {
        $sql = "UPDATE m_inout SET m_inout_date = '" . cgx_dmy2ymd($data['m_inout_date']) . "', c_order_id = '{$data['c_order_id']}',
        document_no = '{$data['document_no']}', no_kendaraan = '{$data['no_kendaraan']}', sj_date = '" . cgx_dmy2ymd($data['sj_date']) . "', update_date = NOW(), update_user = '" . user() . "' WHERE m_inout_id = '{$data['m_inout_id']}'";
    }else{
        $sql = "INSERT INTO m_inout (app_org_id, document_no, c_order_id, m_inout_date, m_transaction_type_id, no_kendaraan, sj_date, create_user)
        VALUES ('" . org() . "', '{$data['document_no']}', '{$data['c_order_id']}', '" . cgx_dmy2ymd($data['m_inout_date']) . "', 12, '{$data['no_kendaraan']}', '" . cgx_dmy2ymd($data['sj_date']) . "', '". user() ."')";
    }
    $rsx = mysql_query($sql, $APP_CONNECTION);
    if (!$rsx) {
        $error = mysql_error($APP_CONNECTION);
        $res->alert($error);
        return $res;
    }

    if ($data['m_inout_id']) {
        $good_rcp_id = $data['m_inout_id'];
    } else {
        $good_rcp_id = mysql_insert_id($APP_CONNECTION);
    }

    foreach ($good_rcp['lines'] as $line) {
        if ($line['m_inout_line_id']) {
            $sql0 = "SELECT weight FROM m_coil WHERE m_in_id = '{$line['m_inout_line_id']}'";    
            $result0 = mysql_query($sql0, $APP_CONNECTION);
            $hasil0 = mysql_result($result0,0);
            $weight_awal = $hasil0;
            $awal = cgx_fetch_table("SELECT * FROM m_inout_line WHERE m_inout_line_id = '{$line['m_inout_line_id']}'");
            $sql = "UPDATE m_inout_line SET quantity = '{$line['quantity']}' WHERE m_inout_line_id = '{$line['m_inout_line_id']}'";
            $rsx = mysql_query($sql, $APP_CONNECTION);
            $sql = "UPDATE m_coil SET no_coil = '{$line['no_coil']}', no_lot = '{$line['no_lot']}', 
                weight = '{$line['weight']}' WHERE m_in_id = '{$line['m_inout_line_id']}'";
            $rsx = mysql_query($sql, $APP_CONNECTION);
            //======================================================================================== return sales order ===========
            mysql_query( "UPDATE c_order_line SET delivered_quantity = delivered_quantity - {$awal['quantity']} , receipt_weight = receipt_weight - {$weight_awal}
                WHERE c_order_line_id = '{$line['c_order_line_id']}'", $APP_CONNECTION);
            //===================================================================================================== return stock on hand
            $qty_a_min = $awal['quantity'] * -1 ;
            stock_onhand(org(), user(), $line['m_product_id'], $data['m_inout_date_a'], $qty_a_min, 0);
            //===================================================================================================== return stock weight
            $weight_awal_min = $weight_awal * -1;
            stock_weight(org(), user(), $line['m_product_id'], $data['m_inout_date_a'], $weight_awal_min, 0);
            //======================================================================================== update sales order ===========
            mysql_query( "UPDATE c_order_line SET delivered_quantity = delivered_quantity + {$line['quantity']} ,
                receipt_weight = receipt_weight + {$line['weight']}
                WHERE c_order_line_id = '{$line['c_order_line_id']}'", $APP_CONNECTION);
            //=================================================================================================== update balance (+-)
            $qty_bal = $line['quantity'] - $awal['quantity'];
            inout(org(), $line['m_product_id'], $line['m_warehouse_id'], $qty_bal);
            //===================================================================================================== update stock on hand
            stock_onhand(org(), user(), $line['m_product_id'], $data['m_inout_date'], $line['quantity'], 0);
            //===================================================================================================== update stock weight
            stock_weight(org(), user(), $line['m_product_id'], $data['m_inout_date'], $line['weight'], 0);
        } else{
            if($line['quantity']){
                $qty_pcs = $line['quantity'];
            } else{
                $qty_pcs = 1;
            }
            $sql = "INSERT INTO m_inout_line (m_inout_id, m_product_id, quantity, m_warehouse_id, c_order_line_id)
                VALUES ('{$good_rcp_id}', '{$line['m_product_id']}', '{$qty_pcs}', '{$line['m_warehouse_id']}', '{$line['c_order_line_id']}')";
            $rsx = mysql_query($sql, $APP_CONNECTION);
            $minout_line_id = mysql_insert_id($APP_CONNECTION);
            
//=============== INI YANG PILIHAN PENERIMAAN COIL ATAU SLIT            
            if($line['warehouse_name'] == 'GUDANG COIL'){
                $sql = "INSERT INTO m_coil (m_product_id, no_coil, no_lot, weight, status, m_in_id) 
                VALUES ('{$line['m_product_id']}', '{$line['no_coil']}', '{$line['no_lot']}', '{$line['weight']}', 'I', '{$minout_line_id}')";
                //$res->alert($sql);
                //return $res;
                $rsx = mysql_query($sql, $APP_CONNECTION);
                
                stock_weight(org(), user(), $line['m_product_id'], $data['m_inout_date'], $line['weight'], 0);
                
                inout(org(), $line['m_product_id'], $line['m_warehouse_id'], $qty_pcs);
            
                mysql_query( "UPDATE c_order_line SET delivered_quantity = delivered_quantity + {$qty_pcs} ,
                    receipt_weight = receipt_weight + {$line['weight']}
                    WHERE c_order_line_id = '{$line['c_order_line_id']}'", $APP_CONNECTION);
            
                stock_onhand(org(), user(), $line['m_product_id'], $data['m_inout_date'], $qty_pcs, 0);
                
            }
            else{            
               $no_coil = $line['no_coil'];
               $no_lot = $line['no_lot'];
               $total_weight = $line['weight'] * $line['quantity']; 
               
               $coil = cgx_fetch_table("SELECT m_coil_id FROM m_coil WHERE no_lot = '{$no_lot}'");
               //INI BUKAN KANG ?
               if (is_array($coil)) {
                   $m_coil_id = $coil['m_coil_id'];
                   $sql = "UPDATE m_coil SET "
                           . "status = 'O'"
                           . ",m_in_id = '{$minout_line_id}',weight = '{$total_weight}' "
                   . "WHERE m_coil_id = '{$m_coil_id}' ";
                   $rsx = mysql_query($sql, $APP_CONNECTION);
               } else {
                   $sql = "INSERT INTO m_coil(m_product_id, no_coil, no_lot, status, weight, m_in_id) VALUES('0','{$no_coil}','{$no_lot}', 'O', '{$total_weight}', '{$minout_line_id}')";
                   //$res->alert($sql);
                   $rsx = mysql_query($sql, $APP_CONNECTION);
                   $m_coil_id = mysql_insert_id($APP_CONNECTION);
               }
               $sql2 = "INSERT INTO m_coil_slit(m_coil_id, m_product_id, quantity, weight) VALUES('{$m_coil_id}', '{$line['m_product_id']}', '{$line['quantity']}', '{$line['weight']}')";    
               //$res->alert($sql2);
               //return $res;
               $rsx = mysql_query($sql2, $APP_CONNECTION);
               
               
               stock_weight(org(), user(), $line['m_product_id'], $data['m_inout_date'], $total_weight, 0);
               
               inout(org(), $line['m_product_id'], $line['m_warehouse_id'], $qty_pcs);
            
                mysql_query( "UPDATE c_order_line SET delivered_quantity = delivered_quantity + {$qty_pcs} ,
                receipt_weight = receipt_weight + {$total_weight}
                WHERE c_order_line_id = '{$line['c_order_line_id']}'", $APP_CONNECTION);
            
                stock_onhand(org(), user(), $line['m_product_id'], $data['m_inout_date'], $qty_pcs, 0);
               
            }//END OF ELSE
//====================================
            
            /*
            $sql = "INSERT INTO m_coil (m_product_id, no_coil, no_lot, weight, status, m_in_id) 
                VALUES ('{$line['m_product_id']}', '{$line['no_coil']}', '{$line['no_lot']}', '{$line['weight']}', 'I', '{$minout_line_id}')";
            $rsx = mysql_query($sql, $APP_CONNECTION);
            */
            //inout(org(), $line['m_product_id'], $line['m_warehouse_id'], $qty_pcs);
            
            //======================================================================================== update sales order ===========
            /*
            mysql_query( "UPDATE c_order_line SET delivered_quantity = delivered_quantity + {$qty_pcs} ,
                receipt_weight = receipt_weight + {$line['weight']}
                WHERE c_order_line_id = '{$line['c_order_line_id']}'", $APP_CONNECTION);
            /*
            mysql_query( "UPDATE c_order_line SET delivered_quantity = delivered_quantity + {$qty_pcs} ,
                receipt_weight = receipt_weight + {$line['weight']}
                WHERE c_order_line_id = '{$line['c_order_line_id']}'", $APP_CONNECTION);
            */    
            //===================================================================================================== update stock on hand
            //stock_onhand(org(), user(), $line['m_product_id'], $data['m_inout_date'], $qty_pcs, 0);
            //===================================================================================================== update stock weight
            //stock_weight(org(), user(), $line['m_product_id'], $data['m_inout_date'], $line['weight'], 0);
        }
    }

    $_SESSION[$APP_ID]['trx.good_rcp']['info'] = "Dokumen sudah berhasil disimpan";
    $res->script("window.location = 'module.php?m=trx.good_rcp&pkey[m_inout_id]={$good_rcp_id}';");
    return $res;
}

function tipePenerimaanCoil($nilai, $line_no) {
    global $mandatory;
    
    $html = "";
    if($line_no == 0 AND $nilai == 283){
        $html .= "<td>Quantity (Pcs) {$mandatory}</td>";
        $html .= "<td><input type='text' size='10' name='quantity' style='text-align: right;'></td>";
    }
    $res = new xajaxResponse();
    $res->assign('baris-pcs', 'innerHTML', $html);
    return $res;
}

function editForm($line_no, $c_order_line_id_head, $m_product_id_head, $product_name_head) {
    global $APP_CONNECTION, $APP_ID, $APP_DATE_FORMAT, $APP_DATE_FORMAT_JAVA;
    global $mandatory;
    
    foreach ($_SESSION[$APP_ID]['good_rcp']['lines'] as $line) {
        if ($line['line'] == $line_no) {
            $data = $line;
            break;
        }
    }

    $html .= "<form id='frmLine'>";
    $html .= "<input type='hidden' name='line' value='{$data['line']}'>";
    $html .= "<input type='hidden' id='c_order_line_id' name='c_order_line_id' value=\"" . ($line_no ? $data['c_order_line_id'] : $c_order_line_id_head) . "\">";
    $html .= "<input type='hidden' id='m_product_id' name='m_product_id' value=\"" . ($line_no ? $data['m_product_id'] : $m_product_id_head) . "\">";
    $html .= "<input type='hidden' id='m_warehouse_id' name='m_warehouse_id' value='{$data['m_warehouse_id']}'>";
    $html .= "<table width='100%'>";
    $html .= "<tr>";
    $html .= "<td width='13%'>Nomor Coil {$mandatory}</td>";
    $html .= "<td width='32%'><input type='text' size='30' name='no_coil' value=\"{$data['no_coil']}\" style='text-align: left;'></td>";    
    $html .= "<td width='6%'></td>";
    $html .= "<td width='12%'>Gudang {$mandatory}</td>";
    $html .= "<td width='37%'>" . cgx_form_select('m_warehouse_id', "SELECT m_warehouse_id, warehouse_name FROM m_warehouse WHERE m_warehouse.app_org_id = '".org()."' AND (m_warehouse_id = 282 OR m_warehouse_id = 283) ORDER BY warehouse_name", $data['m_warehouse_id'], FALSE, "onchange=\"xajax_tipePenerimaanCoil(this.value, {$line_no});\"") . "</td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Kode Coil {$mandatory}</td>";
    $html .= "<td><input type='text' size='30' name='no_lot' value=\"{$data['no_lot']}\" style='text-align: left;'></td>";
    $html .= "<td></td>";
    $html .= "<td>Produk Coil {$mandatory}</td>";
    $html .= "<td><input id='product_name' type='text' size='35' value=\"" . ($line_no ? $data['product_name'] : $product_name_head) . "\" readonly='readonly'></td>";  //. ($line_no ? "<img onclick=\"popupReferenceAmbil('purchase-order-detailedit','&p1=' + document.getElementById('c_order_id').value);\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'>" : '') ."</td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Berat (Kg) {$mandatory}</td>";
    $html .= "<td><input type='text' size='10' name='weight' value=\"{$data['weight']}\" style='text-align: right;'></td>";
    $html .= "<td></td>";
    $html .= "<td>" . ($line_no ? "Quantity (Pcs) {$mandatory}</td>" : "");
    $html .= "<td>" . ($line_no ? "<input type='text' size='10' name='quantity' value=\"{$data['quantity']}\" style='text-align: right;'></td>" : "");
    $html .= "</tr>";
    $html .= "<tr id ='baris-pcs'></tr>";
    $html .= "<tr>";
    $html .= "<td></td>";
    $html .= "<td><input type='button' value='" . ($line_no ? 'Update' : 'Tambahkan') . "' onclick=\"xajax_updateLine(xajax.getFormValues('frmLine'));\"> &nbsp; ";
    $html .= "<input type='button' value='Batal' onclick=\"document.getElementById('area-edit').style.display = 'none'; document.getElementById('master-button').style.display = '';\"></td>";
    $html .= "</tr>";
    $html .= "</table>";
    
    $html .= "</form>";
    
    $res = new xajaxResponse();
    $res->assign('area-edit', 'innerHTML', $html);
    $res->assign('area-edit', 'style.display', '');
    $res->assign('master-button', 'style.display', 'none');
    //$res->script("\$(function() { \$('#schedule_delivery_date').datepicker({dateFormat: '{$APP_DATE_FORMAT_JAVA}'}); });");
    
    return $res;
}

function editFormDua($line_no) {
    global $APP_CONNECTION, $APP_ID, $APP_DATE_FORMAT, $APP_DATE_FORMAT_JAVA;
    global $mandatory;
    
    foreach ($_SESSION[$APP_ID]['good_rcp']['linesdua'] as $line) {
        if ($line['line'] == $line_no) {
            $data = $line;
            break;
        }
    }

    $html .= "<form id='frmLineDua'>";
    $html .= "<input type='hidden' name='line' value='{$data['line']}'>";
    $html .= "<table width='100%'>";
    $html .= "<tr>";
    $html .= "<td width='13%'>Nomor Coil</td>";
    $html .= "<td width='32%'><input type='text' size='30' name='no_coil' value=\"{$data['no_coil']}\" style='text-align: left;'></td>";
    $html .= "<td width='10%'></td>";
    $html .= "<td width='13%'>Berat (Kg) {$mandatory}</td>";
    $html .= "<td width='32%'><input type='text' size='10' name='weight' value=\"{$data['weight']}\" style='text-align: right;'></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Kode Coil</td>";
    $html .= "<td><input type='text' size='30' name='no_lot' value=\"{$data['no_lot']}\" style='text-align: left;'></td>";    
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td></td>";
    $html .= "<td><input type='button' value='" . ($line_no ? 'Update' : 'Tambahkan') . "' onclick=\"xajax_updateLineDua(xajax.getFormValues('frmLineDua'));\"> &nbsp; ";
    $html .= "<input type='button' value='Batal' onclick=\"document.getElementById('area-edit').style.display = 'none'; document.getElementById('master-button').style.display = '';\"></td>";
    $html .= "</tr>";
    $html .= "</table>";
    $html .= "</form>";
    
    $res = new xajaxResponse();
    $res->assign('area-edit', 'innerHTML', $html);
    $res->assign('area-edit', 'style.display', '');
    $res->assign('master-button', 'style.display', 'none');
    
    return $res;
}

function updateLine($data) {
    global $APP_ID;
    
    $good_rcp = $_SESSION[$APP_ID]['good_rcp'];
    $res = new xajaxResponse();
    
    if (empty($data['m_product_id'])) {
        $error = "Produl Coil harus diisi";
    } elseif (empty($data['no_coil'])) {
        $error = "Nomor Coil harus diisi";
    } 
    /*
    elseif (empty($data['no_lot'])) {
        $error = "Kode Coil harus diisi";
    } 
    elseif (empty($data['weight']) OR (int) $data['weight'] < 0) {
        $error = "Berat Coil tidak boleh kosong";
    }
    */
    if ($error) {
        $res->alert($error);
        return $res;
    }
    
    if (is_array($good_rcp['lines'])) {
        foreach ($good_rcp['lines'] as $k => $d) {
            if ($d['line'] == $data['line']) {
                $good_rcp['lines'][$k]['c_order_line_id'] = $data['c_order_line_id'];
                $good_rcp['lines'][$k]['m_product_id'] = $data['m_product_id'];
                $good_rcp['lines'][$k]['m_warehouse_id'] = $data['m_warehouse_id'];
                $good_rcp['lines'][$k]['isi_box'] = $data['isi_box'];
                $good_rcp['lines'][$k]['no_box'] = $data['no_box'];
                $good_rcp['lines'][$k]['no_coil'] = $data['no_coil'];
                $good_rcp['lines'][$k]['no_lot'] = $data['no_lot'];
                $good_rcp['lines'][$k]['weight'] = $data['weight'];
                $good_rcp['lines'][$k]['quantity'] = $data['quantity'];
                $product = cgx_fetch_table("SELECT * FROM m_product WHERE m_product_id = '{$data['m_product_id']}'");
                if (is_array($product)) foreach ($product as $pk => $pv) if (!is_numeric($pk)) $good_rcp['lines'][$k][$pk] = $pv;
                $warehouse = cgx_fetch_table("SELECT * FROM m_warehouse WHERE m_warehouse_id = '{$data['m_warehouse_id']}'");
                if (is_array($warehouse)) foreach ($warehouse as $pk => $pv) if (!is_numeric($pk)) $good_rcp['lines'][$k][$pk] = $pv;
                $line_updated = TRUE;
                break;
            }
        }
    }
    if (!$line_updated) {
        $new_line['c_order_line_id'] = $data['c_order_line_id'];
        $new_line['m_product_id'] = $data['m_product_id'];
        $new_line['m_warehouse_id'] = $data['m_warehouse_id'];
        $new_line['isi_box'] = $data['isi_box'];
        $new_line['no_box'] = $data['no_box'];
        $new_line['no_coil'] = $data['no_coil'];
        $new_line['no_lot'] = $data['no_lot'];
        $new_line['weight'] = $data['weight'];
        $new_line['quantity'] = $data['quantity'];
        $product = cgx_fetch_table("SELECT * FROM m_product WHERE m_product_id = '{$data['m_product_id']}'");
        if (is_array($product)) foreach ($product as $pk => $pv) if (!is_numeric($pk)) $new_line[$pk] = $pv;
        $warehouse = cgx_fetch_table("SELECT * FROM m_warehouse WHERE m_warehouse_id = '{$data['m_warehouse_id']}'");
        if (is_array($warehouse)) foreach ($warehouse as $pk => $pv) if (!is_numeric($pk)) $new_line[$pk] = $pv;
        $good_rcp['lines'][] = $new_line;
    }
    $_SESSION[$APP_ID]['good_rcp'] = $good_rcp;
    
    $res->script("xajax_showLines('{$good_rcp['m_inout_id']}', 'editH');");
    $res->assign('area-edit', 'style.display', 'none');
    $res->assign('master-button', 'style.display', '');
    return $res;
}

function updateLineDua($data) {
    global $APP_ID;
    
    $good_rcp = $_SESSION[$APP_ID]['good_rcp'];
    $res = new xajaxResponse();
    /*
    if ((int) $data['weight'] < 0 OR $data['weight']=='') {
        $error = "Berat Coil tidak boleh kosong";
    } else
    */
    if (empty($data['no_coil'])) {
        $error = "No. Coil tidak boleh kosong";
    }
    if ($error) {
        $res->alert($error);
        return $res;
    }
    
    if (is_array($good_rcp['linesdua'])) {
        foreach ($good_rcp['linesdua'] as $k => $d) {
            if ($d['line'] == $data['line']) {
                $good_rcp['linesdua'][$k]['weight'] = $data['weight'];
                $good_rcp['linesdua'][$k]['no_coil'] = $data['no_coil'];
                $good_rcp['linesdua'][$k]['no_lot'] = $data['no_lot'];
                $line_updated = TRUE;
                break;
            }
        }
    }
    if (!$line_updated) {
        $new_line['weight'] = $data['weight'];
        $new_line['no_coil'] = $data['no_coil'];
        $new_line['no_lot'] = $data['no_lot'];
        $good_rcp['linesdua'][] = $new_line;
    }
    $_SESSION[$APP_ID]['good_rcp'] = $good_rcp;
    
    $res->script("xajax_showLinesDua('{$good_rcp['m_inout_id']}', 'editH');");
    $res->assign('area-edit', 'style.display', 'none');
    $res->assign('master-button', 'style.display', '');
    return $res;
}

function deleteLineDua($line_no) {
    global $APP_ID, $APP_CONNECTION;
    
    $good_rcp = $_SESSION[$APP_ID]['good_rcp'];
    foreach ($good_rcp['linesdua'] as $k => $line) {
        if ($line['line'] == $line_no) {
            $del = $k;
            if ($line['m_coil_id']) {
                if ($good_rcp['deletedua']) {
                    $good_rcp['deletedua'][] = $line['m_coil_id'];
                } else {
                    $good_rcp['deletedua'] = array($line['m_coil_id']);
                }
            }
        }
    }
    unset($good_rcp['linesdua'][$del]);
    $_SESSION[$APP_ID]['good_rcp'] = $good_rcp;

    $res = new xajaxResponse();
    $res->script("xajax_showLinesDua('{$good_rcp['m_inout_id']}', 'editH');");
    $res->assign('area-edit', 'style.display', 'none');
    $res->assign('master-button', 'style.display', '');
    return $res;
}

function grid_qty_form($data) {
    $row_id = $data['record']['c_order_line_id'];
    $html .= "<input id='txt{$row_id}' name='lines[{$row_id}]' type='text' size='8' value='0' style='text-align: right;' disabled>";
    return $html;
}

// function grid_ket_form($data) {
//     $row_id = $data['record']['c_order_line_id'];
//     $html .= "<input id='txtket{$row_id}' name='linesket[{$row_id}]' type='text' size='8' style='text-align: left;' disabled>";
//     return $html;
// }

function grid_nocoil_form($data) {
    $row_id = $data['record']['c_order_line_id'];
    $html .= "<input id='txtnocoil{$row_id}' name='linesnocoil[{$row_id}]' type='text' size='8' style='text-align: left;' disabled>";
    return $html;
}

function grid_nolot_form($data) {
    $row_id = $data['record']['c_order_line_id'];
    $html .= "<input id='txtnolot{$row_id}' name='linesnolot[{$row_id}]' type='text' size='8' style='text-align: left;' disabled>";
    return $html;
}

function grid_nobox_form($data) {
    $row_id = $data['record']['c_order_line_id'];
    $html .= "<input id='txtnobox{$row_id}' name='linesnobox[{$row_id}]' type='text' size='8' style='text-align: left;' disabled>";
    return $html;
}

function grid_isibox_form($data) {
    $row_id = $data['record']['c_order_line_id'];
    $html .= "<input id='txtisibox{$row_id}' name='linesisibox[{$row_id}]' type='text' size='8' style='text-align: right;' disabled>";
    return $html;
}

function grid_wh_form($data) {
    global $APP_CONNECTION;
    $row_id = $data['record']['c_order_line_id'];
    $rsx = mysql_query(
        "SELECT m_warehouse_id, warehouse_name, COALESCE(balance_quantity, 0) balance_quantity 
            FROM m_warehouse LEFT JOIN 
            (SELECT * FROM m_stock_warehouse_2 WHERE m_product_id = '{$data['record']['m_product_id']}' AND app_org_id='".org()."' AND latest = 'Y') X USING (m_warehouse_id) 
            WHERE m_warehouse.app_org_id = '".org()."' ORDER BY balance_quantity DESC, warehouse_name",
        $APP_CONNECTION);
    //if (mysql_num_rows($rsx) == 0) return "<span style='color: red; white-space: nowrap;'>Tidak ada barang</span>";
    $html = "<select id='wh{$row_id}' name='wh[{$row_id}]' disabled>";
    while ($dtx = mysql_fetch_array($rsx)) {
        $html .= "<option value='{$dtx['m_warehouse_id']}'>{$dtx['warehouse_name']} ({$dtx['balance_quantity']})</option>";
    }
    $html .= "</select>";
    mysql_free_result($rsx);
    return $html;
}

function grid_chk_form($data) {
    $row_id = $data['record']['c_order_line_id'];
    $html .= "<input type='checkbox' onclick=\"document.getElementById('txt{$row_id}').disabled = !this.checked;document.getElementById('wh{$row_id}').disabled = !this.checked;document.getElementById('txtnocoil{$row_id}').disabled = !this.checked;document.getElementById('txtnolot{$row_id}').disabled = !this.checked;document.getElementById('txtnobox{$row_id}').disabled = !this.checked;document.getElementById('txtisibox{$row_id}').disabled = !this.checked;\">";
    return $html;
}

function showLines($m_inout_id, $mode = NULL) {
    global $APP_CONNECTION, $APP_ID, $cgx_TableAttribs, $cgx_HeaderAttribs,
        $cgx_EvenRowAttribs, $cgx_OddRowAttribs, $cgx_RendererOptions;
    
    $data = $_SESSION[$APP_ID]['good_rcp'];
    if ($data['m_inout_id'] != $m_inout_id) return;
    
    if (is_array($data['lines'])) {
        $n = 0;
        foreach ($data['lines'] as $k => $d) {
            $n++;
            $data['lines'][$k]['line'] = $n;
        }
    }
    $_SESSION[$APP_ID]['good_rcp'] = $data;

    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';
    
    $datagrid = new Structures_DataGrid(9999);
    $datagrid->bind($data['lines'], array(), 'Array');
    $cgx_table = new HTML_Table($cgx_TableAttribs);
    $cgx_tableHeader = & $cgx_table->getHeader();
    $cgx_tableBody = & $cgx_table->getBody();
    
    // $datagrid->addColumn(new Structures_DataGrid_Column('Baris', 'line', NULL, array('align' => 'center'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('No. Coil Induk', 'no_coil', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Kode Coil', 'no_lot', NULL, array('align' => 'left'), NULL, NULL));    
    $datagrid->addColumn(new Structures_DataGrid_Column('Item Number', 'product_code', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Spec', 'spec', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Thickness', 'thickness', NULL, array('align' => 'right', 'width' => '8%'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Width', 'od', NULL, array('align' => 'right', 'width' => '8%'), NULL, NULL));
    // $datagrid->addColumn(new Structures_DataGrid_Column('Panjang', 'length', NULL, array('align' => 'right', 'width' => '8%'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Gudang', 'warehouse_name', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Berat (Kg)', 'weight', NULL, array('align' => 'right'), NULL, "cgx_format_money"));
    $datagrid->addColumn(new Structures_DataGrid_Column('Quantity (Pcs)', 'quantity', NULL, array('align' => 'right'), NULL, "cgx_format_3digit"));
    // $datagrid->addColumn(new Structures_DataGrid_Column('No Box', 'no_box', NULL, array('align' => 'left'), NULL, NULL));
    // $datagrid->addColumn(new Structures_DataGrid_Column('Isi Box', 'isi_box', NULL, array('align' => 'left'), NULL, NULL));
    if ($mode == 'editH' || $mode == 'edit' ) $datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '20'), NULL, 'ctl_edit_good_rcp()'));
    if ($mode == 'edit' ) $datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '20'), NULL, 'ctl_edit_coil()'));
    
    $datagrid->fill($cgx_table, $cgx_RendererOptions);
    $cgx_tableHeader->setRowAttributes(0, $cgx_HeaderAttribs);
    $cgx_tableBody->altRowAttributes(0, $cgx_EvenRowAttribs, $cgx_OddRowAttribs, TRUE);

    $html  = "<div class='datagrid_background'>\n";
    $html .= $cgx_table->toHtml();
    $html .= "</div>\n";

    $res = new xajaxResponse();
    $nilai = 0 ;
    $qty = 0;
    
    
    foreach ($data['lines'] as $line) {
        if($line['warehouse_name'] == 'GUDANG SLIT'){
            $nilai += $line['weight']*$line['quantity'];
            $qty += $line['quantity'];
        }
        else{
            $nilai += $line['weight'];
            $qty += 1;
        }
    }
    
    /*
    foreach ($data['lines'] as $line) {
        $nilai += $line['weight'];
        $qty += 1;
    }
    */
    
    $res->script("document.getElementById('total_quantity').value=$qty");
    $res->script("document.getElementById('total_weight').value=$nilai");
    $res->assign('area-lines', 'innerHTML', $html);
    return $res;
}

function showLinesDua($m_inout_id, $mode = NULL) {
    global $APP_CONNECTION, $APP_ID, $cgx_TableAttribs, $cgx_HeaderAttribs,
        $cgx_EvenRowAttribs, $cgx_OddRowAttribs, $cgx_RendererOptions;
    
    $data = $_SESSION[$APP_ID]['good_rcp'];
    if ($data['m_inout_id'] != $m_inout_id) return;
    
    if (is_array($data['linesdua'])) {
        $n = 0;
        foreach ($data['linesdua'] as $k => $d) {
            $n++;
            $data['linesdua'][$k]['line'] = $n;
        }
    }
    $_SESSION[$APP_ID]['good_rcp'] = $data;

    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';
    
    $datagrid = new Structures_DataGrid(9999);
    $datagrid->bind($data['linesdua'], array(), 'Array');
    $cgx_table = new HTML_Table($cgx_TableAttribs);
    $cgx_tableHeader = & $cgx_table->getHeader();
    $cgx_tableBody = & $cgx_table->getBody();
    
    $datagrid->addColumn(new Structures_DataGrid_Column('Baris', 'line', NULL, array('align' => 'center'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('No. Coil Induk', 'no_coil', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Kode Coil', 'no_lot', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Berat (Kg)', 'weight', NULL, array('align' => 'right'), NULL, "cgx_format_money"));
    if ($mode == 'editH') $datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '20'), NULL, 'ctl_edit_coil()'));
    if ($mode == 'editH') $datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '20'), NULL, 'ctl_delete_coil()'));

    $datagrid->fill($cgx_table, $cgx_RendererOptions);
    $cgx_tableHeader->setRowAttributes(0, $cgx_HeaderAttribs);
    $cgx_tableBody->altRowAttributes(0, $cgx_EvenRowAttribs, $cgx_OddRowAttribs, TRUE);

    $html  = "<div class='datagrid_background'>\n";
    $html .= $cgx_table->toHtml();
    $html .= "</div>\n";

    $res = new xajaxResponse();
    $res->assign('area-lines-dua', 'innerHTML', $html);
    return $res;
}

function salesOrderLinesForm($c_order_line_id) {
    global $APP_CONNECTION, $cgx_TableAttribs, $cgx_HeaderAttribs, $cgx_dsn,
        $cgx_EvenRowAttribs, $cgx_OddRowAttribs, $cgx_RendererOptions;
    
    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';
    
    $datagrid = new Structures_DataGrid(9999);
    $cgx_options = array('dsn' => $cgx_dsn);
    $cgx_sql = "SELECT @curRow := @curRow + 1 AS line, c_order_line.*, (c_order_line.order_weight - c_order_line.receipt_weight) as sisa_order, m_product.*, balance_quantity " .
            "FROM c_order_line " .
            "JOIN m_product USING (m_product_id) " .
            "JOIN (SELECT @curRow := 0) r " .
            "LEFT JOIN m_stock_balance_2 ON (m_product.m_product_id = m_stock_balance_2.m_product_id AND m_stock_balance_2.app_org_id='".org()."' AND latest = 'Y') " .
            "WHERE c_order_line_id = '{$c_order_line_id}'";
    $datagrid->bind($cgx_sql, $cgx_options);
    
    $cgx_table = new HTML_Table($cgx_TableAttribs);
    $cgx_tableHeader = & $cgx_table->getHeader();
    $cgx_tableBody = & $cgx_table->getBody();
    
    // $datagrid->addColumn(new Structures_DataGrid_Column('Baris', 'line', NULL, array('align' => 'center'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Jadwal<br>Penerimaan', 'schedule_delivery_date', NULL, array('align' => 'center'), NULL, "cgx_format_date"));
    $datagrid->addColumn(new Structures_DataGrid_Column('Item<br>Number', 'product_code', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Spec', 'spec', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Thickness', 'thickness', NULL, array('align' => 'right', 'width' => '8%'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Width', 'od', NULL, array('align' => 'right', 'width' => '8%'), NULL, NULL));
    // $datagrid->addColumn(new Structures_DataGrid_Column('Sisa<br>Order', 'sisa_order', NULL, array('align' => 'right'), NULL, "cgx_format_3digit"));
    $datagrid->addColumn(new Structures_DataGrid_Column('Berat Order<br>(Kg)', 'order_weight', NULL, array('align' => 'right'), NULL, "cgx_format_money"));
    $datagrid->addColumn(new Structures_DataGrid_Column('Berat Diterima<br>(Kg)', 'receipt_weight', NULL, array('align' => 'right'), NULL, "cgx_format_money"));
    $datagrid->addColumn(new Structures_DataGrid_Column('Qty Diterima<br>(Pcs)', 'delivered_quantity', NULL, array('align' => 'right', 'width' => '8%'), NULL, "cgx_format_3digit"));
    // $datagrid->addColumn(new Structures_DataGrid_Column('Jumlah<br>Return', 'return_quantity', NULL, array('align' => 'right'), NULL, "cgx_format_3digit"));
    $datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, "grid_chk_form"));
    $datagrid->addColumn(new Structures_DataGrid_Column('Stok<br>Gudang', NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, "grid_wh_form"));
    // $datagrid->addColumn(new Structures_DataGrid_Column('Ket', NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, "grid_ket_form"));
    // $datagrid->addColumn(new Structures_DataGrid_Column('Kode Coil', NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, "grid_nocoil_form"));
    // $datagrid->addColumn(new Structures_DataGrid_Column('Lot Number', NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, "grid_nolot_form"));
    // $datagrid->addColumn(new Structures_DataGrid_Column('No Box', NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, "grid_nobox_form"));
    // $datagrid->addColumn(new Structures_DataGrid_Column('Isi Box', NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, "grid_isibox_form"));
    $datagrid->addColumn(new Structures_DataGrid_Column('Qty Masuk<br>(Pcs)', NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, "grid_qty_form"));
    $datagrid->fill($cgx_table, $cgx_RendererOptions);
    $cgx_tableHeader->setRowAttributes(0, $cgx_HeaderAttribs);
    $cgx_tableBody->altRowAttributes(0, $cgx_EvenRowAttribs, $cgx_OddRowAttribs, TRUE);

    $html  = "<div class='datagrid_background'>\n";
    $html .= $cgx_table->toHtml();
    $html .= "<table width='100%'><tr>";
    $html .= "<td width='100%' align='right'><input type='button' value='Input Nomor Coil' onclick=\"xajax_editFormDua();\"></td>";
    $html .= "</tr></table>";
    $html .= "</div>\n";

    $res = new xajaxResponse();
    $res->assign('area-lines', 'innerHTML', $html);
    return $res;
}

$xajax->register(XAJAX_FUNCTION, 'cekNomor');
$xajax->register(XAJAX_FUNCTION, "salesOrderLinesForm");
$xajax->register(XAJAX_FUNCTION, "savegood_rcp");
$xajax->register(XAJAX_FUNCTION, "updategood_rcp");
$xajax->register(XAJAX_FUNCTION, 'tipePenerimaanCoil');
$xajax->register(XAJAX_FUNCTION, 'editForm');
$xajax->register(XAJAX_FUNCTION, 'editFormDua');
$xajax->register(XAJAX_FUNCTION, 'updateLine');
$xajax->register(XAJAX_FUNCTION, 'updateLineDua');
$xajax->register(XAJAX_FUNCTION, 'showLines');
$xajax->register(XAJAX_FUNCTION, 'showLinesDua');
$xajax->register(XAJAX_FUNCTION, 'deleteLineDua');

?>