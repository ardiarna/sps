<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.orgm>
 * Nov 24, 2013 8:18:31 AM
 */

function ctl_edit_rr($data) {
    if ($data['record']['m_inout_line_id']) {
        $href = "xajax_editForm('{$data['record']['line']}');";
        $out = "<img onclick=\"{$href}\" style='cursor: pointer;' src='images/icon_edit.png' border='0'>";
    return $out;
    }
}

function ctl_delete_rr($data) {
    $href = "xajax_deleteLine('{$data['record']['line']}');";
    $out = "<img onclick=\"{$href}\" style='cursor: pointer;' src='images/icon_delete.png' border='0'>";
    return $out;
}

function showLines($m_inout_id, $mode = NULL) {
    global $APP_CONNECTION, $APP_ID, $cgx_TableAttribs, $cgx_HeaderAttribs,
        $cgx_EvenRowAttribs, $cgx_OddRowAttribs, $cgx_RendererOptions;
    
    $data = $_SESSION[$APP_ID]['rrg'];
    if ($data['m_inout_id'] != $m_inout_id) return;
    
    if (is_array($data['lines'])) {
        $n = 0;
        foreach ($data['lines'] as $k => $d) {
            $n++;
            $data['lines'][$k]['line'] = $n;
        }
    }
    $_SESSION[$APP_ID]['rrg'] = $data;

    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';
    
    $datagrid = new Structures_DataGrid(9999);
    $datagrid->bind($data['lines'], array(), 'Array');
    $cgx_table = new HTML_Table($cgx_TableAttribs);
    $cgx_tableHeader = & $cgx_table->getHeader();
    $cgx_tableBody = & $cgx_table->getBody();
    
    $datagrid->addColumn(new Structures_DataGrid_Column('Baris', 'line', NULL, array('align' => 'center'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Item Number', 'product_code', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Spec', 'spec', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('OD', 'od', NULL, array('align' => 'right', 'width' => '8%'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Tebal', 'thickness', NULL, array('align' => 'right', 'width' => '8%'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Panjang', 'length', NULL, array('align' => 'right', 'width' => '8%'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Gudang', 'warehouse_name', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Jumlah Barang', 'quantity', NULL, array('align' => 'right'), NULL, "cgx_format_3digit"));
    if ($mode == 'edit' OR $mode == 'editH')$datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '20'), NULL, 'ctl_edit_rr()'));
    //if ($mode == 'edit') $datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, 'ctl_delete_rr()'));

    $datagrid->fill($cgx_table, $cgx_RendererOptions);
    $cgx_tableHeader->setRowAttributes(0, $cgx_HeaderAttribs);
    $cgx_tableBody->altRowAttributes(0, $cgx_EvenRowAttribs, $cgx_OddRowAttribs, TRUE);

    $html  = "<div class='datagrid_background'>\n";
    $html .= $cgx_table->toHtml();
    $html .= "</div>\n";

    $res = new xajaxResponse();
    $res->assign('area-lines', 'innerHTML', $html);
    return $res;
}

function editForm($line_no) {
    global $APP_CONNECTION, $APP_ID, $APP_DATE_FORMAT, $APP_DATE_FORMAT_JAVA;
    global $mandatory;
    
    foreach ($_SESSION[$APP_ID]['rrg']['lines'] as $line) {
        if ($line['line'] == $line_no) {
            $data = $line;
            break;
        }
    }

    $html .= "<form id='frmLine'>";
    $html .= "<input type='hidden' name='line' value='{$data['line']}'>";
    $html .= "<input type='hidden' id='m_product_id' name='m_product_id' value='{$data['m_product_id']}'>";
    $html .= "<input type='hidden' id='m_warehouse_id' name='m_warehouse_id' value='{$data['m_warehouse_id']}'>";
    $html .= "<input type='hidden' id='m_warehouse_id_a' name='m_warehouse_id_a' value='{$data['m_warehouse_id']}'>";
    $html .= "<table width='100%'>";
    $html .= "<tr>";
    $html .= "<td width='12%'>Item Number {$mandatory}</td>";
    $html .= "<td width='33%'><input id='product_code' type='text' size='20' value=\"{$data['product_code']}\" readonly='readonly'>". ($line_no ? "" : ( org() == '3' ? "<img onclick=\"popupReference('product');\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'>" : "<img onclick=\"popupReference('product');\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'>")) ."</td>";
    //$html .= "<td width='33%'><input id='product_code' type='text' size='20' value=\"{$data['product_code']}\" readonly='readonly'><img onclick=\"popupReference('product');\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'></td>";
    $html .= "<td width='10%'></td>";
    $html .= "<td width='12%'>Gudang {$mandatory}</td>";
    //$html .= "<td width='33%'>" . cgx_filter('m_warehouse_id', "SELECT m_warehouse_id, warehouse_name FROM m_warehouse WHERE app_org_id = '".org()."' ORDER BY warehouse_name", $data['m_warehouse_id'], FALSE, $line_no ? ' disabled' : '') . "</td>";
    $html .= "<td width='33%'>" . cgx_filter('m_warehouse_id', "SELECT m_warehouse_id, warehouse_name FROM m_warehouse WHERE app_org_id = '".org()."' ORDER BY warehouse_name", $data['m_warehouse_id'], FALSE) . "</td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Barang</td>";
    $html .= "<td><input type='text' name='product_name' id='product_name' size='30' value=\"{$data['product_name']}\" readonly='readonly'></td>";
    $html .= "<td></td>";
    $html .= "<td>Jumlah Barang {$mandatory}</td>";
    $html .= "<td><input type='text' size='10' name='quantity' value=\"{$data['quantity']}\" style='text-align: right;'></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Description</td>";
    $html .= "<td><input type='text' size='30' id='description' value=\"{$data['description']}\" name='description' readonly='readonly'></td>";
    $html .= "<td></td>";
    $html .= "</tr>";
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
    $res->script("\$(function() { \$('#schedule_delivery_date').datepicker({dateFormat: '{$APP_DATE_FORMAT_JAVA}'}); });");
    
    return $res;
}

function deleteLine($line_no) {
    global $APP_ID, $APP_CONNECTION;
    
    $rrg = $_SESSION[$APP_ID]['rrg'];
    foreach ($rrg['lines'] as $k => $line) {
        if ($line['line'] == $line_no) {
            $del = $k;
            if ($line['m_inout_line_id']) {
                if ($rrg['delete']) {
                    $rrg['delete'][] = $line['m_inout_line_id'];
                } else {
                    $rrg['delete'] = array($line['m_inout_line_id']);
                }
            }
        }
    }
    unset($rrg['lines'][$del]);
    $_SESSION[$APP_ID]['rrg'] = $rrg;

    $res = new xajaxResponse();
    $res->script("xajax_showLines('{$rrg['m_inout_id']}', 'edit');");
    $res->assign('area-edit', 'style.display', 'none');
    $res->assign('master-button', 'style.display', '');
    return $res;
}

function saveRR($data) {
    global $APP_ID, $APP_CONNECTION;
    
    $rrg = $_SESSION[$APP_ID]['rrg'];
    $res = new xajaxResponse();
    if (cgx_emptydate($data['m_inout_date'])) {
        $error = "Tanggal transaksi tidak boleh kosong";
    } elseif (count($rrg['lines']) == 0) {
        $error = "Detail item barang tidak boleh kosong, harus ada minimal satu baris.";
    }
    if ($error) {
        $res->alert($error);
        return $res;
    }
    if ($data['m_inout_id']) {
        $sql = "UPDATE m_inout SET m_inout_date = '" . cgx_dmy2ymd($data['m_inout_date']) . "', dokumen = '{$data['dokumen']}', 
            c_order_id = '{$data['c_order_id']}', update_date = NOW(), update_user = '" . user() . "' WHERE m_inout_id = '{$data['m_inout_id']}'";
    } else {
        require_once 'lib/class.Penomoran.php';
        $nomor = new Penomoran();
        $document_no = $nomor->urut('BM', org());
        $sql =
            "INSERT INTO m_inout (app_org_id, document_no, c_order_id, m_inout_date, m_transaction_type_id, dokumen, create_user)
            VALUES ('" . org() . "',  '{$document_no}', '{$data['c_order_id']}', '" . cgx_dmy2ymd($data['m_inout_date']) . "', 3, '{$data['dokumen']}', '". user() ."')";
    }    
    $rsx = mysql_query($sql, $APP_CONNECTION);
    if (!$rsx) {
        $error = mysql_error($APP_CONNECTION);
        $res->alert($error);
        return $res;
    }
    if ($data['m_inout_id']) {
        $rrg_id = $data['m_inout_id'];
    } else {
        $rrg_id = mysql_insert_id($APP_CONNECTION);
    }
    foreach ($rrg['lines'] as $line) {
        if ($line['m_inout_line_id']) {  // JIKA BMBJ EDIT
            $sql0 = "SELECT quantity FROM m_inout_line WHERE m_inout_line_id = '{$line['m_inout_line_id']}'";    
            $result0 = mysql_query($sql0, $APP_CONNECTION);
            $hasil0 = mysql_result($result0,0);
            if($hasil0){
                $qty_a = $hasil0;
            }
            $sql = "UPDATE m_inout_line SET m_product_id = '{$line['m_product_id']}', m_warehouse_id = '{$line['m_warehouse_id']}',  
                    quantity = '{$line['quantity']}' WHERE m_inout_line_id = '{$line['m_inout_line_id']}'";
            $rsx = mysql_query($sql, $APP_CONNECTION);
            //====================================================================================== update stock on hand return
            $qty_a_min = $qty_a * -1 ;
            stock_onhand(org(), user(), $line['m_product_id'], $data['m_inout_date_a'], $qty_a_min, 0);
            //=================================================================================================== update balance return
            if ($line['m_warehouse_id_a']) {
                inout(org(),$line['m_product_id'], $line['m_warehouse_id_a'], $qty_a_min);
                //=================================================================================================== update balance
                inout(org(),$line['m_product_id'], $line['m_warehouse_id'], $line['quantity']);
            } else {
                $qty = $line['quantity'] - $qty_a;
                inout(org(),$line['m_product_id'], $line['m_warehouse_id'], $qty);                                
            }
        } else {  // JIKA BMBJ BARU
            $sql = "INSERT INTO m_inout_line (m_inout_id, m_product_id, quantity, m_warehouse_id)
                    VALUES ('{$rrg_id}', '{$line['m_product_id']}', '{$line['quantity']}', '{$line['m_warehouse_id']}')";
            $rsx = mysql_query($sql, $APP_CONNECTION);
            //=================================================================================================== update balance
            inout(org(),$line['m_product_id'], $line['m_warehouse_id'], $line['quantity']);
        }
        //====================================================================================== update stock on hand
        stock_onhand(org(), user(), $line['m_product_id'], $data['m_inout_date'], $line['quantity'], 0);
    }   
    $_SESSION[$APP_ID]['trx.rrg']['info'] = "Dokumen sudah berhasil disimpan";
    $res->script("window.location = 'module.php?m=trx.rrg&pkey[m_inout_id]={$rrg_id}';");
    return $res;
}

function updateLine($data) {
    global $APP_ID;
    
    $rrg = $_SESSION[$APP_ID]['rrg'];
    $res = new xajaxResponse();
    if ((int) $data['quantity'] < 0) {
        $error = "Jumlah barang tidak boleh kosong";
    } elseif (empty($data['m_product_id'])) {
        $error = "Kode barang tidak boleh kosong";
    }
    if ($error) {
        $res->alert($error);
        return $res;
    }
    
    if (is_array($rrg['lines'])) {
        foreach ($rrg['lines'] as $k => $d) {
            if ($d['line'] == $data['line']) {
                $rrg['lines'][$k]['quantity'] = $data['quantity'];
                $rrg['lines'][$k]['m_product_id'] = $data['m_product_id'];
                $rrg['lines'][$k]['m_warehouse_id'] = $data['m_warehouse_id'];
                $rrg['lines'][$k]['m_warehouse_id_a'] = $data['m_warehouse_id_a'];
                $product = cgx_fetch_table("SELECT * FROM m_product WHERE m_product_id = '{$data['m_product_id']}'");
                if (is_array($product)) foreach ($product as $pk => $pv) if (!is_numeric($pk)) $rrg['lines'][$k][$pk] = $pv;
                $warehouse = cgx_fetch_table("SELECT * FROM m_warehouse WHERE m_warehouse_id = '{$data['m_warehouse_id']}'");
                if (is_array($warehouse)) foreach ($warehouse as $pk => $pv) if (!is_numeric($pk)) $rrg['lines'][$k][$pk] = $pv;
                $line_updated = TRUE;
                break;
            }
        }
    }
    if (!$line_updated) {
        $new_line['quantity'] = $data['quantity'];
        $new_line['m_product_id'] = $data['m_product_id'];
        $new_line['m_warehouse_id'] = $data['m_warehouse_id'];
        $new_line['m_warehouse_id_a'] = $data['m_warehouse_id_a'];
        $product = cgx_fetch_table("SELECT * FROM m_product WHERE m_product_id = '{$data['m_product_id']}'");
        if (is_array($product)) foreach ($product as $pk => $pv) if (!is_numeric($pk)) $new_line[$pk] = $pv;
        $warehouse = cgx_fetch_table("SELECT * FROM m_warehouse WHERE m_warehouse_id = '{$data['m_warehouse_id']}'");
        if (is_array($warehouse)) foreach ($warehouse as $pk => $pv) if (!is_numeric($pk)) $new_line[$pk] = $pv;
        $rrg['lines'][] = $new_line;
    }
    $_SESSION[$APP_ID]['rrg'] = $rrg;
    
    $res->script("xajax_showLines('{$rrg['m_inout_id']}', 'edit');");
    $res->assign('area-edit', 'style.display', 'none');
    $res->assign('master-button', 'style.display', '');
    return $res;
}

$xajax->register(XAJAX_FUNCTION, 'showLines');
$xajax->register(XAJAX_FUNCTION, 'editForm');
$xajax->register(XAJAX_FUNCTION, 'updateLine');
$xajax->register(XAJAX_FUNCTION, 'deleteLine');
$xajax->register(XAJAX_FUNCTION, 'saveRR');

?>