<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.org>
 * Nov 25, 2013 1:06:48 PM
 */

function ctl_edit_rwos($data) {
    if ($data['record']['m_prod_slit_line_id']) {
        $href = "xajax_editForm('{$data['record']['line']}');";
        $out = "<img onclick=\"{$href}\" style='cursor: pointer;' src='images/icon_edit.png' border='0'>";
    return $out;
    }
}

// function ctl_delete_coil($data) {
//     if ($data['record']['m_coil_id']) {
//         $href = "xajax_deleteLineDua('{$data['record']['line']}');";
//         $out = "<img onclick=\"{$href}\" style='cursor: pointer;' title='Hapus' src='images/icon_delete.png' border='0'>";
//         return $out;
//     }
// }

function saveRWOS($data) {
    global $APP_ID, $APP_CONNECTION;
    $rwos = $_SESSION[$APP_ID]['rwos'];
    $res = new xajaxResponse();
    if (empty($data['m_wo_slit_id'])) {
        $error = "Work Order tidak boleh kosong";
    } elseif (cgx_emptydate($data['production_date'])) {
        $error = "Tanggal tidak boleh kosong";
    } elseif (count($data['lines']) == 0) {
        $error = "Detail item barang tidak boleh kosong, silahkan pilih minimal satu barang.";
    }
    if ($error) {
        $res->alert($error);
        return $res;
    }
    $n = 0;
    foreach ($data['lines'] as $m_wo_slit_line_id => $good) {
        $n++;
        if ($good <= 0) $line_error .= " * Baris ke {$n}: jumlah barang tidak boleh kosong.\n";
    }   
    // if any error, display it and cancel operation
    if ($line_error) {
        $res->alert("Tidak bisa memproses:\n" . $line_error);
        return $res;
    }
    // everything should be ok start the process
    require_once 'lib/class.Penomoran.php';
    $nomor = new Penomoran();
    $document_no = $nomor->urut('HP', org());
    $sql =
        "INSERT INTO m_prod_slit (document_no, m_wo_slit_id, production_date, m_warehouse_id, quantity_raw, production_type, create_user, create_date)
        VALUES ('{$document_no}', '{$data['m_wo_slit_id']}', '" . cgx_dmy2ymd($data['production_date']) . "', '{$data['m_warehouse_id']}', '{$data['quantity_raw']}', 1, '". user() ."', NOW())";
    $rsx = mysql_query($sql, $APP_CONNECTION);
    $rwos_id = mysql_insert_id($APP_CONNECTION);
    //==================================================================================== update stock on hand ==== raw (-)
    stock_onhand(org(), user(), $data['m_product_id'], $data['production_date'], 0, $data['quantity_raw']);
    //==================================================================================== update stock balance ==== raw (-)
    inout(org(), $data['m_product_id'], $data['m_warehouse_id'], 0, $data['quantity_raw'], FALSE);
    if (!$rsx) {
        $error = mysql_error($APP_CONNECTION);
        $res->alert($error);
        return $res;
    }
    foreach ($data['lines'] as $m_wo_slit_line_id => $good) {
        foreach ($data['lines'][$m_wo_slit_line_id] as $m_coil_id => $nilai) {
            $line = cgx_fetch_table("SELECT * FROM m_wo_slit_line WHERE m_wo_slit_line_id = '{$m_wo_slit_line_id}'");
            $sql = "INSERT INTO m_prod_slit_line (m_prod_slit_id, m_wo_slit_line_id, m_warehouse_id, m_coil_id, good, weight, ket)
                VALUES ('{$rwos_id}', '{$m_wo_slit_line_id}', '{$data['wh'][$m_wo_slit_line_id]}', '{$m_coil_id}', '{$nilai}', '{$data['linesweight'][$m_wo_slit_line_id][$m_coil_id]}', '{$data['linesket'][$m_wo_slit_line_id][$m_coil_id]}')";
            $rsx = mysql_query($sql, $APP_CONNECTION);
            // ======================================================================================== create wo slit ===========
            mysql_query("INSERT INTO m_coil_slit(m_coil_id, m_product_id, quantity, weight) VALUES('{$m_coil_id}', 
                '{$line['m_product_id']}', '{$nilai}', '{$data['linesweight'][$m_wo_slit_line_id][$m_coil_id]}') 
                ON DUPLICATE KEY UPDATE quantity = quantity + '{$nilai}'",$APP_CONNECTION);
            // ======================================================================================== update work order ===========
            mysql_query("UPDATE m_wo_slit_line SET producted_quantity = producted_quantity + {$nilai} ,
                producted_weight = producted_weight + {$data['linesweight'][$m_wo_slit_line_id][$m_coil_id]}
                WHERE m_wo_slit_line_id = '{$m_wo_slit_line_id}'",$APP_CONNECTION);
            //================================================================================== update stock on hand ==== slitting (+)
            stock_onhand(org(), user(), $line['m_product_id'], $data['production_date'], $nilai, 0);
            //================================================================================== update stock weight ==== slitting (+)
            $beratslit = $nilai * $data['linesweight'][$m_wo_slit_line_id][$m_coil_id];
            stock_weight(org(), user(), $line['m_product_id'], $data['production_date'], $beratslit, 0);
            //================================================================================== update stock balance ==== slitting (+)
            inout(org(), $line['m_product_id'], $data['wh'][$m_wo_slit_line_id], $nilai);
            
            $sql = "UPDATE m_coil SET status = 'O' , m_out_id = '{$rwos_id}' WHERE m_coil_id = '{$m_coil_id}'";
            $rsx = mysql_query($sql, $APP_CONNECTION);
        }
    }

    $sql0 = "SELECT sum(weight) weight FROM m_coil WHERE m_out_id = '{$rwos_id}'";    
    $result0 = mysql_query($sql0, $APP_CONNECTION);
    $hasil0 = mysql_result($result0,0);
    $total_weight = $hasil0;
    $sql = "UPDATE m_prod_slit SET weight_raw = '{$total_weight}' WHERE m_prod_slit_id = '{$rwos_id}'";
    $rsx = mysql_query($sql, $APP_CONNECTION);
    //=================================================================================== update stock weight ==== raw (-)
    stock_weight(org(), user(), $data['m_product_id'], $data['production_date'], 0, $total_weight);

    $_SESSION[$APP_ID]['trx.rwos']['info'] = "Dokumen sudah berhasil disimpan";
    $res->script("window.location = 'module.php?m=trx.rwos&pkey[m_prod_slit_id]={$rwos_id}';");
    return $res;
}

function updateRWOS($data) {
    global $APP_ID, $APP_CONNECTION;
    $rwos = $_SESSION[$APP_ID]['rwos'];
    $res = new xajaxResponse();
    if (cgx_emptydate($data['production_date'])) {
        $error = "Tanggal tidak boleh kosong";
    } 
    if ($error) {
        $res->alert($error);
        return $res;
    }
    $awal = cgx_fetch_table("SELECT * FROM m_prod_slit WHERE m_prod_slit_id = '{$data['m_prod_slit_id']}'");
    $sql = "UPDATE m_prod_slit SET production_date = '" . cgx_dmy2ymd($data['production_date']) . "', 
        update_date = NOW(), update_user = '" . user() . "' WHERE m_prod_slit_id = '{$data['m_prod_slit_id']}'";
    $rsx = mysql_query($sql, $APP_CONNECTION);
    //============================================================================================= update stock on hand raw return
    //$qty_aw_min = $awal['quantity_raw'] * -1 ;
    //stock_onhand(org(), user(), $data['m_product_id'], $data['production_date_a'], 0, $qty_aw_min);
    //============================================================================================= update stock weight raw return
    //$weight_aw_min = $awal['weight_raw'] * -1 ;
    //stock_weight(org(), user(), $data['m_product_id'], $data['production_date_a'], 0, $weight_aw_min);
    //============================================================================================  update stock on hand raw
    //stock_onhand(org(), user(), $data['m_product_id'], $data['production_date'], 0, $data['quantity_raw']);
    //===================================================================================== update balance raw
    //$adj_qty = $data['quantity_raw'] - $awal['quantity_raw'];
    //inout(org(), $data['m_product_id'], $rwos['m_warehouse_id'], 0, $adj_qty, FALSE);

    if (!$rsx) {
        $error = mysql_error($APP_CONNECTION);
        $res->alert($error);
        return $res;
    }
    foreach ($rwos['lines'] as $line) {
        $awal = cgx_fetch_table("SELECT * FROM m_prod_slit_line WHERE m_prod_slit_line_id = '{$line['m_prod_slit_line_id']}'");
        $sql = "UPDATE m_prod_slit_line SET good = '{$line['good']}', weight = '{$line['weight']}', ket = '{$line['ket']}' 
            WHERE m_prod_slit_line_id = '{$line['m_prod_slit_line_id']}'";
        $rsx = mysql_query($sql, $APP_CONNECTION);
        //======================================================================================== update m_coil_slit ===========
        $test_coil = cgx_fetch_table("SELECT m_coil_id, no_coil, no_lot FROM m_coil WHERE no_coil = '{$line['no_coil']}'");
        $go = $test_coil['m_coil_id'];
        //$res->alert($go);
        //return $res;
        $sql_m_coil = "UPDATE m_coil_slit SET quantity = '{$line['good']}', weight = '{$line['weight']}' 
            WHERE m_coil_id = '{$go}'";
        //$res->alert($sql_m_coil);
        //return $res;
        $rsx = mysql_query($sql_m_coil, $APP_CONNECTION);
        //======================================================================================== update work order ===========
        $good0 = $line['good'] - $awal['good'];
        $weight0 = $line['weight'] - $awal['weight'];
        mysql_query("UPDATE m_wo_slit_line SET producted_quantity = producted_quantity + {$good0} ,
            producted_weight = producted_weight + {$weight0}
            WHERE m_wo_slit_line_id = '{$line['m_wo_slit_line_id']}'",$APP_CONNECTION);
        //=========================================================================== update stock on hand slitting return
        $qty_awal = $awal['good'] * -1 ;
        stock_onhand(org(), user(), $line['m_product_id'], $data['production_date_a'], $qty_awal, 0);
        //=========================================================================== update stock weight slitting return
        $weight_awal = $awal['weight'] * -1 ;
        stock_weight(org(), user(), $line['m_product_id'], $data['production_date_a'], $weight_awal, 0);
        //=========================================================================== update stock on hand slitting
        stock_onhand(org(), user(), $line['m_product_id'], $data['production_date'], $line['good'], 0);
        //=========================================================================== update stock weight slitting
        stock_weight(org(), user(), $line['m_product_id'], $data['production_date'], $line['weight'], 0);
        //===================================================================================================== update stock balance
        $adj_qty = $line['good'] - $awal['good'];
        inout(org(), $line['m_product_id'], $line['m_warehouse_id'], $adj_qty);  
    }

    // foreach ($rwos['linesdua'] as $linedua) {
    //     $sql = "UPDATE m_coil SET status = 'O' , m_out_id = '{$data['m_prod_slit_id']}' WHERE m_coil_id = '{$linedua['m_coil_id']}'";   
    //     $rsx = mysql_query($sql, $APP_CONNECTION);
    // }

    // if (is_array($rwos['deletedua'])) {
    //     foreach ($rwos['deletedua'] as $d) {
    //         $sql = "UPDATE m_coil SET status = 'I' , m_out_id = null WHERE m_coil_id = '{$d}'";
    //         mysql_query($sql, $APP_CONNECTION);
    //     }
    // }

    // $sql0 = "SELECT sum(weight) weight FROM m_coil WHERE m_out_id = '{$data['m_prod_slit_id']}'";    
    // $result0 = mysql_query($sql0, $APP_CONNECTION);
    // $hasil0 = mysql_result($result0,0);
    // $total_weight = $hasil0;
    // $sql = "UPDATE m_prod_slit SET weight_raw = '{$total_weight}' WHERE m_prod_slit_id = '{$data['m_prod_slit_id']}'";
    // $rsx = mysql_query($sql, $APP_CONNECTION);
    // //============================================================================================  update stock weight raw
    // stock_weight(org(), user(), $data['m_product_id'], $data['production_date'], 0, $total_weight);

    $_SESSION[$APP_ID]['trx.rwos']['info'] = "Dokumen sudah berhasil diperbaharui";
    $res->script("window.location = 'module.php?m=trx.rwos&pkey[m_prod_slit_id]={$data['m_prod_slit_id']}';");
    return $res;
}

function editForm($line_no) {
    global $APP_CONNECTION, $APP_ID, $APP_DATE_FORMAT, $APP_DATE_FORMAT_JAVA;
    global $mandatory;
    
    foreach ($_SESSION[$APP_ID]['rwos']['lines'] as $line) {
        if ($line['line'] == $line_no) {
            $data = $line;
            break;
        }
    }
    $html .= "<form id='frmLine'>";
    $html .= "<input type='hidden' name='line' value='{$data['line']}'>";
    $html .= "<input type='hidden' id='m_wo_slit_line_id' name='m_wo_slit_line_id' value='{$data['m_wo_slit_line_id']}'>";
    $html .= "<table width='100%'>";
    $html .= "<tr>";
    $html .= "<td width='12%'>Item Number</td>";
    $html .= "<td width='33%'><input id='product_code' type='text' size='20' value=\"{$data['product_code']}\" readonly='readonly'></td>";
    $html .= "<td width='10%'></td>";
    $html .= "<td width='12%'>Gudang</td>";
    $html .= "<td width='33%'>" . cgx_filter('m_warehouse_id', "SELECT m_warehouse_id, CONCAT(warehouse_name,' (',COALESCE(balance_quantity,0),')') wh FROM m_warehouse 
        LEFT JOIN (SELECT * FROM m_stock_warehouse_2 WHERE m_product_id = '{$data['m_product_id']}' AND app_org_id='".org()."' AND latest = 'Y') X USING (m_warehouse_id) 
        WHERE m_warehouse.app_org_id = '".org()."' ORDER BY balance_quantity DESC, warehouse_name", $data['m_warehouse_id'], FALSE, ' disabled') . "</td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Barang</td>";
    $html .= "<td><input type='text' name='product_name' id='product_name' size='30' value=\"{$data['product_name']}\" readonly='readonly'></td>";
    $html .= "<td></td>";
    $html .= "<td>Jumlah {$mandatory}</td>";
    $html .= "<td><input type='text' size='10' name='good' value=\"{$data['good']}\" style='text-align: right;'></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Customer</td>";
    $html .= "<td><input type='text' name='partner_name' id='partner_name' size='35' value=\"{$data['partner']}\" readonly='readonly'></td>";
    $html .= "<td></td>";
    $html .= "<td>Berat (Kg) {$mandatory}</td>";
    $html .= "<td><input type='text' size='10' name='weight' value=\"{$data['weight']}\" style='text-align: right;'></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>No. Coil</td>";
    $html .= "<td><input type='text' size='20' name='no_coil' value=\"{$data['no_coil']}\" readonly='readonly'></td>";
    $html .= "<td></td>";
    $html .= "<td>Keterangan</td>";
    $html .= "<td><input type='text' size='40' name='ket' value=\"{$data['ket']}\" style='text-align: left;'></td>";
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
    return $res;
}

// function editFormDua($line_no) {
//     global $APP_CONNECTION, $APP_ID, $APP_DATE_FORMAT, $APP_DATE_FORMAT_JAVA;
//     global $mandatory;
//     foreach ($_SESSION[$APP_ID]['rwos']['linesdua'] as $line) {
//         if ($line['line'] == $line_no) {
//             $data = $line;
//             break;
//         }
//     }
//     $html .= "<form id='frmLineDua'>";
//     $html .= "<input type='hidden' name='line' value='{$data['line']}'>";
//     $html .= "<input type='hidden' id='m_coil_id' name='m_coil_id' value='{$data['m_coil_id']}'>";
//     $html .= "<table width='100%'>";
//     $html .= "<tr>";
//     $html .= "<td width='13%'>Nomor Coil {$mandatory}</td>";
//     $html .= "<td width='32%'><input type='text' size='30' id='no_coil' value=\"{$data['no_coil']}\" style='text-align: left;' readonly='readonly'><img onclick=\"popupReferenceAmbil('m_coil','&p1=' + document.getElementById('m_product_id').value);\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'></td>";
//     $html .= "<td width='10%'></td>";
//     $html .= "<td width='13%'>Berat (Kg)</td>";
//     $html .= "<td width='32%'><input type='text' size='10' id='weight' value=\"{$data['weight']}\" style='text-align: right;' readonly='readonly'></td>";
//     $html .= "</tr>";
//     $html .= "<tr>";
//     $html .= "<td>Kode Coil</td>";
//     $html .= "<td><input type='text' size='30' id='no_lot' value=\"{$data['no_lot']}\" style='text-align: left;' readonly='readonly'></td>";    
//     $html .= "</tr>";
//     $html .= "<tr>";
//     $html .= "<td></td>";
//     $html .= "<td><input type='button' value='" . ($line_no ? 'Update' : 'Tambahkan') . "' onclick=\"xajax_updateLineDua(xajax.getFormValues('frmLineDua'));\"> &nbsp; ";
//     $html .= "<input type='button' value='Batal' onclick=\"document.getElementById('area-edit').style.display = 'none'; document.getElementById('master-button').style.display = '';\"></td>";
//     $html .= "</tr>";
//     $html .= "</table>";
//     $html .= "</form>";
    
//     $res = new xajaxResponse();
//     $res->assign('area-edit', 'innerHTML', $html);
//     $res->assign('area-edit', 'style.display', '');
//     $res->assign('master-button', 'style.display', 'none');
    
//     return $res;
// }

function updateLine($data) {
    global $APP_ID;    
    $rwos = $_SESSION[$APP_ID]['rwos'];
    $res = new xajaxResponse();
    if ((int) $data['good'] < 0) {
        $error = "Jumlah barang hasil produksi tidak boleh kosong";
    }
    if ($error) {
        $res->alert($error);
        return $res;
    }
    if (is_array($rwos['lines'])) {
        foreach ($rwos['lines'] as $k => $d) {
            if ($d['line'] == $data['line']) {
                $rwos['lines'][$k]['good'] = $data['good'];
                $rwos['lines'][$k]['weight'] = $data['weight'];
                $rwos['lines'][$k]['ket'] = $data['ket'];
                $product = cgx_fetch_table("SELECT * FROM m_product WHERE m_product_id = '{$data['m_product_id']}'");
                if (is_array($product)) foreach ($product as $pk => $pv) if (!is_numeric($pk)) $rwos['lines'][$k][$pk] = $pv;
                $line_updated = TRUE;
                break;
            }
        }
    }
    $_SESSION[$APP_ID]['rwos'] = $rwos;
    $res->script("xajax_showLines('{$rwos['m_prod_slit_id']}', 'editH');");
    $res->assign('area-edit', 'style.display', 'none');
    $res->assign('master-button', 'style.display', '');
    return $res;
}

// function updateLineDua($data) {
//     global $APP_ID;
//     $rwos = $_SESSION[$APP_ID]['rwos'];
//     $res = new xajaxResponse();
//     if (empty($data['m_coil_id'])) {
//         $error = "No. Coil tidak boleh kosong";
//     }
//     if ($error) {
//         $res->alert($error);
//         return $res;
//     }
//     if (is_array($rwos['linesdua'])) {
//         foreach ($rwos['linesdua'] as $k => $d) {
//             if ($d['line'] == $data['line']) {
//                 $rwos['linesdua'][$k]['m_coil_id'] = $data['m_coil_id'];
//                 $coil = cgx_fetch_table("SELECT * FROM m_coil WHERE m_coil_id = '{$data['m_coil_id']}'");
//                 if (is_array($coil)) foreach ($coil as $pk => $pv) if (!is_numeric($pk)) $rwos['linesdua'][$k][$pk] = $pv;
//                 $line_updated = TRUE;
//                 break;
//             }
//         }
//     }
//     if (!$line_updated) {
//         $new_line['m_coil_id'] = $data['m_coil_id'];
//         $coil = cgx_fetch_table("SELECT * FROM m_coil WHERE m_coil_id = '{$data['m_coil_id']}'");
//         if (is_array($coil)) foreach ($coil as $pk => $pv) if (!is_numeric($pk)) $new_line[$pk] = $pv;
//         $rwos['linesdua'][] = $new_line;
//     }
//     $_SESSION[$APP_ID]['rwos'] = $rwos;
//     $res->script("xajax_showLinesDua('{$rwos['m_prod_slit_id']}', 'editH');");
//     $res->assign('area-edit', 'style.display', 'none');
//     $res->assign('master-button', 'style.display', '');
//     return $res;
// }

// function deleteLineDua($line_no) {
//     global $APP_ID, $APP_CONNECTION;
//     $rwos = $_SESSION[$APP_ID]['rwos'];
//     foreach ($rwos['linesdua'] as $k => $line) {
//         if ($line['line'] == $line_no) {
//             $del = $k;
//             if ($line['m_coil_id']) {
//                 if ($rwos['deletedua']) {
//                     $rwos['deletedua'][] = $line['m_coil_id'];
//                 } else {
//                     $rwos['deletedua'] = array($line['m_coil_id']);
//                 }
//             }
//         }
//     }
//     unset($rwos['linesdua'][$del]);
//     $_SESSION[$APP_ID]['rwos'] = $rwos;
//     $res = new xajaxResponse();
//     $res->script("xajax_showLinesDua('{$rwos['m_prod_slit_id']}', 'editH');");
//     $res->assign('area-edit', 'style.display', 'none');
//     $res->assign('master-button', 'style.display', '');
//     return $res;
// }

function showLines($m_prod_slit_id, $mode = NULL) {
    global $APP_CONNECTION, $APP_ID, $cgx_TableAttribs, $cgx_HeaderAttribs,
        $cgx_EvenRowAttribs, $cgx_OddRowAttribs, $cgx_RendererOptions;
    
    $data = $_SESSION[$APP_ID]['rwos'];
    if ($data['m_prod_slit_id'] != $m_prod_slit_id) return;
    
    if (is_array($data['lines'])) {
        $n = 0;
        foreach ($data['lines'] as $k => $d) {
            $n++;
            $data['lines'][$k]['line'] = $n;
        }
    }
    $_SESSION[$APP_ID]['rwos'] = $data;
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
    $datagrid->addColumn(new Structures_DataGrid_Column('Thick', 'thickness', NULL, array('align' => 'right', 'width' => '8%'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Width', 'od', NULL, array('align' => 'right', 'width' => '8%'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('No. Coil', 'no_coil', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Gudang', 'warehouse_name', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Jumlah Slit', 'good', NULL, array('align' => 'right'), NULL, "cgx_format_3digit"));
    $datagrid->addColumn(new Structures_DataGrid_Column('Berat per Slit (Kg)', 'weight', NULL, array('align' => 'right'), NULL, "cgx_format_money"));
    $datagrid->addColumn(new Structures_DataGrid_Column('Keterangan', 'ket', NULL, array('align' => 'left'), NULL, NULL));
    if ($mode == 'editH') $datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '20'), NULL, 'ctl_edit_rwos()'));

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

// function showLinesDua($m_prod_slit_id, $mode = NULL) {
//     global $APP_CONNECTION, $APP_ID, $cgx_TableAttribs, $cgx_HeaderAttribs,
//         $cgx_EvenRowAttribs, $cgx_OddRowAttribs, $cgx_RendererOptions;
    
//     $data = $_SESSION[$APP_ID]['rwos'];
//     if ($data['m_prod_slit_id'] != $m_prod_slit_id) return;
    
//     if (is_array($data['linesdua'])) {
//         $n = 0;
//         foreach ($data['linesdua'] as $k => $d) {
//             $n++;
//             $data['linesdua'][$k]['line'] = $n;
//         }
//     }
//     $_SESSION[$APP_ID]['rwos'] = $data;

//     require_once 'Structures/DataGrid.php';
//     require_once 'HTML/Table.php';
    
//     $datagrid = new Structures_DataGrid(9999);
//     $datagrid->bind($data['linesdua'], array(), 'Array');
//     $cgx_table = new HTML_Table($cgx_TableAttribs);
//     $cgx_tableHeader = & $cgx_table->getHeader();
//     $cgx_tableBody = & $cgx_table->getBody();
    
//     $datagrid->addColumn(new Structures_DataGrid_Column('Baris', 'line', NULL, array('align' => 'center'), NULL, NULL));
//     $datagrid->addColumn(new Structures_DataGrid_Column('No. Coil Raw', 'no_coil', NULL, array('align' => 'left'), NULL, NULL));
//     $datagrid->addColumn(new Structures_DataGrid_Column('Kode Coil Raw', 'no_lot', NULL, array('align' => 'left'), NULL, NULL));
//     $datagrid->addColumn(new Structures_DataGrid_Column('Berat (Kg)', 'weight', NULL, array('align' => 'right'), NULL, "cgx_format_money"));
//     if ($mode == 'editH') $datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '20'), NULL, 'ctl_delete_coil()'));

//     $datagrid->fill($cgx_table, $cgx_RendererOptions);
//     $cgx_tableHeader->setRowAttributes(0, $cgx_HeaderAttribs);
//     $cgx_tableBody->altRowAttributes(0, $cgx_EvenRowAttribs, $cgx_OddRowAttribs, TRUE);

//     $html  = "<div class='datagrid_background'>\n";
//     $html .= $cgx_table->toHtml();
//     $html .= "</div>\n";

//     $res = new xajaxResponse();
//     $res->assign('area-lines-dua', 'innerHTML', $html);
//     return $res;
// }

function grid_qty_form($dtx, $dty) {
    $row_id = $dtx['m_wo_slit_line_id'];
    $row_in = $dty['m_coil_id'];
    $html .= "<input id='txt{$row_id}[{$row_in}]' name='lines[{$row_id}][{$row_in}]' type='text' value ='{$dtx['order_quantity']}' size='8' style='text-align: right;' disabled>";
    return $html;
}

function grid_weight_form($dtx, $dty) {
    $row_id = $dtx['m_wo_slit_line_id'];
    $row_in = $dty['m_coil_id'];
    $wo = cgx_fetch_table("SELECT m_wo_slit.* FROM m_wo_slit_line JOIN m_wo_slit USING(m_wo_slit_id) WHERE m_wo_slit_line_id = '{$row_id}'");
    $beratPerSlit = ($dtx['od'] / $wo['width_actual']) * $dty['weight'];
    $html .= "<input id='txtweight{$row_id}[{$row_in}]' name='linesweight[{$row_id}][{$row_in}]' type='text' value ='". number_format($beratPerSlit, 2, ".","") ."' size='10' style='text-align: right;' disabled>";
    return $html;
}

function grid_ket_form($dtx, $dty) {
    $row_id = $dtx['m_wo_slit_line_id'];
    $row_in = $dty['m_coil_id'];
    $html .= "<input id='txtket{$row_id}[{$row_in}]' name='linesket[{$row_id}][{$row_in}]' type='text' size='15' style='text-align: left;' disabled>";
    return $html;
}

function grid_wh_form($dtx) {
    global $APP_CONNECTION;
    $row_id = $dtx['m_wo_slit_line_id'];
    $rsx = mysql_query(
        "SELECT m_warehouse_id, warehouse_name, COALESCE(balance_quantity, 0) balance_quantity 
            FROM m_warehouse LEFT JOIN 
            (SELECT * FROM m_stock_warehouse_2 WHERE m_product_id = '{$dtx['m_product_id']}' AND app_org_id='".org()."' AND latest = 'Y') X USING (m_warehouse_id) 
            WHERE m_warehouse.app_org_id = '".org()."' AND (m_warehouse_id = 282 OR m_warehouse_id = 283) ORDER BY balance_quantity DESC, warehouse_name DESC",
        $APP_CONNECTION);
    $html = "<select id='wh{$row_id}' name='wh[{$row_id}]' >";
    while ($dtx = mysql_fetch_array($rsx)) {
        $html .= "<option value='{$dtx['m_warehouse_id']}'>{$dtx['warehouse_name']} ({$dtx['balance_quantity']})</option>";
    }
    $html .= "</select>";
    mysql_free_result($rsx);
    return $html;
}

function grid_chk_all($m_wo_slit_id) {
    global $APP_CONNECTION;
    $string_chk = "";
    $rsx = mysql_query("SELECT m_wo_slit_line.* 
        FROM m_wo_slit_line
        WHERE m_wo_slit_id = '{$m_wo_slit_id}'", $APP_CONNECTION);
    while ($dtx = mysql_fetch_array($rsx, MYSQL_ASSOC)) {
        $rsy = mysql_query("SELECT m_coil.* FROM m_coil WHERE m_wo_slit_id = '{$m_wo_slit_id}' ORDER BY no_lot", $APP_CONNECTION);
        $jml_coil = mysql_num_rows($rsy);
        while ($dty = mysql_fetch_array($rsy, MYSQL_ASSOC)) {
            $string_chk .= "document.getElementById('chk{$dty['m_coil_id']}[{$dtx['m_wo_slit_line_id']}]').checked = this.checked;";
            $string_chk .= "document.getElementById('txt{$dtx['m_wo_slit_line_id']}[{$dty['m_coil_id']}]').disabled = !this.checked;";
            $string_chk .= "document.getElementById('txtket{$dtx['m_wo_slit_line_id']}[{$dty['m_coil_id']}]').disabled = !this.checked;";
            $string_chk .= "document.getElementById('txtweight{$dtx['m_wo_slit_line_id']}[{$dty['m_coil_id']}]').disabled = !this.checked;";    
        }
    }
    $html .= "<input type='checkbox' id='chk_all' onclick=\"{$string_chk}\"
            onchange=\"
            var chk_id = document.getElementById('chk_all');
            if(chk_all.checked == true){
                document.getElementById('quantity_raw').value = {$jml_coil};        
            }else{
                document.getElementById('quantity_raw').value = 0;
            }
            
            \"    
         >";
    return $html;
}

function grid_chk_form($dtx, $dty) {
    $row_id = $dtx['m_wo_slit_line_id'];
    $row_in = $dty['m_coil_id'];
    $html .= "<input name='chk{$row_in}' id='chk{$row_in}[{$row_id}]' type='checkbox' onclick=\"document.getElementById('txt{$row_id}[{$row_in}]').disabled = !this.checked;document.getElementById('txtket{$row_id}[{$row_in}]').disabled = !this.checked;document.getElementById('txtweight{$row_id}[{$row_in}]').disabled = !this.checked;\"
        onchange=\"
        var qty_raw = document.getElementById('quantity_raw').value;
        var chk_id = document.getElementById('chk{$row_in}[{$row_id}]');
        var chk = document.getElementsByName('chk{$row_in}');
        var ada_cek = 0;
        for (var i=0; i < chk.length ; i++) { 
            if(chk[i].checked == true){
                ada_cek++        
            }
        }
        console.log(ada_cek);
        if(chk_id.checked == true && ada_cek == 1){
            qty_raw++
        }else if(ada_cek == 0){
            qty_raw--
        }
        document.getElementById('quantity_raw').value = qty_raw;\"    
         >";
    return $html;
}

// function grid_chk_dua_form($data) {
//     $row_id = $data['record']['m_coil_id'];
//     // $html .= "<input id='txtdua{$row_id}' name='linesdua[{$row_id}]' type='checkbox' value='{$row_id}' onchange=\"xajax_editFormDua();\">";
//     $html .= "<input id='txtdua{$row_id}' name='linesdua[{$row_id}]' type='checkbox' value='{$row_id}' checked onchange=\"
//     var qty_raw = document.getElementById('quantity_raw').value;
//     var chk_dua = document.getElementById('txtdua{$row_id}');
//     if(chk_dua.checked == true){
//         qty_raw++
//     }else{
//         qty_raw--
//     }
//     document.getElementById('quantity_raw').value = qty_raw;\">";
//     return $html;
// }

function workOrderLinesForm($m_wo_slit_id) {
    global $APP_CONNECTION, $cgx_TableAttribs, $cgx_HeaderAttribs, $cgx_dsn,
        $cgx_EvenRowAttribs, $cgx_OddRowAttribs, $cgx_RendererOptions;
    
    // require_once 'Structures/DataGrid.php';
    // require_once 'HTML/Table.php';
    // $datagrid = new Structures_DataGrid(9999);
    // $cgx_options = array('dsn' => $cgx_dsn);
    // $cgx_sql = "SELECT @curRow := @curRow + 1 AS line, m_wo_slit_line.*, m_product.*, mid(partner_name,1,20) partner_name 
    //         FROM m_wo_slit_line 
    //         JOIN m_product USING (m_product_id)
    //         JOIN c_bpartner ON(m_wo_slit_line.c_bpartner_id=c_bpartner.c_bpartner_id) 
    //         JOIN (SELECT @curRow := 0) r 
    //         WHERE m_wo_slit_id = '{$m_wo_slit_id}'";
    // $datagrid->bind($cgx_sql, $cgx_options);
    
    // $cgx_table = new HTML_Table($cgx_TableAttribs);
    // $cgx_tableHeader = & $cgx_table->getHeader();
    // $cgx_tableBody = & $cgx_table->getBody();
    
    // $datagrid->addColumn(new Structures_DataGrid_Column('Baris', 'line', NULL, array('align' => 'center'), NULL, NULL));
    // $datagrid->addColumn(new Structures_DataGrid_Column('Spec', 'spec', NULL, array('align' => 'left'), NULL, NULL));
    // $datagrid->addColumn(new Structures_DataGrid_Column('Thick', 'thickness', NULL, array('align' => 'right'), NULL, NULL));
    // $datagrid->addColumn(new Structures_DataGrid_Column('Width', 'od', NULL, array('align' => 'right'), NULL, NULL));
    // $datagrid->addColumn(new Structures_DataGrid_Column('Customer', 'partner_name', NULL, array('align' => 'left'), NULL, NULL));
    // $datagrid->addColumn(new Structures_DataGrid_Column('Qty <br> WO', 'order_quantity', NULL, array('align' => 'right', 'width' => '8%'), NULL, "cgx_format_3digit"));
    // $datagrid->addColumn(new Structures_DataGrid_Column('Qty <br> Producted', 'producted_quantity', NULL, array('align' => 'right', 'width' => '8%'), NULL, "cgx_format_3digit"));
    // //$datagrid->addColumn(new Structures_DataGrid_Column('Weight <br> WO', 'order_weight', NULL, array('align' => 'right', 'width' => '8%'), NULL, "cgx_format_money"));
    // $datagrid->addColumn(new Structures_DataGrid_Column('Weight <br> Producted', 'producted_weight', NULL, array('align' => 'right', 'width' => '8%'), NULL, "cgx_format_money"));
    // $datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, "grid_chk_form"));
    // $datagrid->addColumn(new Structures_DataGrid_Column('Stok Gudang', NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, "grid_wh_form"));
    // $datagrid->addColumn(new Structures_DataGrid_Column('Jumlah <br> (Pcs)', NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, "grid_qty_form"));
    // $datagrid->addColumn(new Structures_DataGrid_Column('Berat <br> (Kg)', NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, "grid_weight_form"));
    // $datagrid->addColumn(new Structures_DataGrid_Column('Keterangan', NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, "grid_ket_form"));

    // $datagrid->fill($cgx_table, $cgx_RendererOptions);
    // $cgx_tableHeader->setRowAttributes(0, $cgx_HeaderAttribs);
    // $cgx_tableBody->altRowAttributes(0, $cgx_EvenRowAttribs, $cgx_OddRowAttribs, TRUE);
    $html = "<table width='100%' cellspacing='1' class='datagrid_background' style='margin-top: 4px;'>";
    $html .= "<tr style='height: 30px;'>";
    $html .= "<th class='datagrid_header'>No</th>";
    $html .= "<th class='datagrid_header'>Spec</th>";
    $html .= "<th class='datagrid_header'>Thick</th>";
    $html .= "<th class='datagrid_header'>Width</th>";
    $html .= "<th class='datagrid_header'>No Coil</th>";
    $html .= "<th class='datagrid_header'>Kode Coil</th>";
    $html .= "<th class='datagrid_header'>Berat Coil</th>";
    $html .= "<th class='datagrid_header' width='1'>". grid_chk_all($m_wo_slit_id) ."</th>";
    $html .= "<th class='datagrid_header' width='1'>Stok Gudang</th>";
    $html .= "<th class='datagrid_header' width='1'>Jumlah Slit</th>";
    $html .= "<th class='datagrid_header' width='1'>Berat per Slit</th>";
    $html .= "<th class='datagrid_header' width='1'>Keterangan</th>";
    $html .= "</tr>";
    $rsx = mysql_query("SELECT @curRow := @curRow + 1 AS line, m_wo_slit_line.*, m_product.* 
        FROM m_wo_slit_line 
        JOIN m_product USING (m_product_id)
        JOIN (SELECT @curRow := 0) r 
        WHERE m_wo_slit_id = '{$m_wo_slit_id}'", $APP_CONNECTION);
    
    while ($dtx = mysql_fetch_array($rsx, MYSQL_ASSOC)) {
        $html .= "<tr style='background: #ffffff; height: 26px;'>";
        //$html .= "<td align='right'>" . (++$i) . "</td>";
        
        $rsy = mysql_query("SELECT m_coil.* FROM m_coil WHERE m_wo_slit_id = '{$m_wo_slit_id}' ORDER BY no_lot", $APP_CONNECTION);
        $jml_coil = mysql_num_rows($rsy);

        $html .= "<td rowspan='{$jml_coil}' align='center'>{$dtx['line']}</td>";
        $html .= "<td rowspan='{$jml_coil}'>{$dtx['spec']}</td>";
        $html .= "<td rowspan='{$jml_coil}' align='right'>{$dtx['thickness']}</td>";
        $html .= "<td rowspan='{$jml_coil}' align='right'>{$dtx['od']}</td>";
        $inisial_awal = 1;
        while ($dty = mysql_fetch_array($rsy, MYSQL_ASSOC)) {
            $html .= "<td>{$dty['no_coil']}</td>";
            $html .= "<td>{$dty['no_lot']}</td>";
            $html .= "<td align='right'>{$dty['weight']}</td>";
            $html .= "<td>".grid_chk_form($dtx, $dty)."</td>";
            if($inisial_awal == 1){
                $html .= "<td rowspan='{$jml_coil}'>".grid_wh_form($dtx)."</td>";
                $inisial_awal = 2;
            }
            $html .= "<td>".grid_qty_form($dtx, $dty)."</td>";
            $html .= "<td>".grid_weight_form($dtx, $dty)."</td>";
            $html .= "<td>".grid_ket_form($dtx, $dty)."</td>";
            $html .= "</tr>";
            $html .= "<tr style='background: #ffffff; height: 5px;'>";
        }
    }
    $html .= "</tr>";


    $html .= "</table>";
    $html .= "<div class='datagrid_background'>\n";
    //$html .= $cgx_table->toHtml();
    $html .= "</div>\n";

    $res = new xajaxResponse();
    $res->assign('area-lines', 'innerHTML', $html);
    return $res;
}

// function workOrderCoilForm($m_wo_slit_id) {
//     global $APP_CONNECTION, $cgx_TableAttribs, $cgx_HeaderAttribs, $cgx_dsn,
//         $cgx_EvenRowAttribs, $cgx_OddRowAttribs, $cgx_RendererOptions;
    
//     require_once 'Structures/DataGrid.php';
//     require_once 'HTML/Table.php';
//     $datagrid = new Structures_DataGrid(9999);
//     $cgx_options = array('dsn' => $cgx_dsn);
//     $cgx_sql = "SELECT @curRow := @curRow + 1 AS line, m_coil.* 
//             FROM m_coil 
//             JOIN (SELECT @curRow := 0) r 
//             WHERE m_wo_slit_id = '{$m_wo_slit_id}'";
//     $datagrid->bind($cgx_sql, $cgx_options);
    
//     $cgx_table = new HTML_Table($cgx_TableAttribs);
//     $cgx_tableHeader = & $cgx_table->getHeader();
//     $cgx_tableBody = & $cgx_table->getBody();
    
//     $datagrid->addColumn(new Structures_DataGrid_Column('Baris', 'line', NULL, array('align' => 'center'), NULL, NULL));
//     $datagrid->addColumn(new Structures_DataGrid_Column('No. Coil Raw', 'no_coil', NULL, array('align' => 'left'), NULL, NULL));
//     $datagrid->addColumn(new Structures_DataGrid_Column('Kode Coil Raw', 'no_lot', NULL, array('align' => 'left'), NULL, NULL));
//     $datagrid->addColumn(new Structures_DataGrid_Column('Berat (Kg)', 'weight', NULL, array('align' => 'right'), NULL, NULL));
//     $datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, "grid_chk_dua_form"));
    
//     $datagrid->fill($cgx_table, $cgx_RendererOptions);
//     $cgx_tableHeader->setRowAttributes(0, $cgx_HeaderAttribs);
//     $cgx_tableBody->altRowAttributes(0, $cgx_EvenRowAttribs, $cgx_OddRowAttribs, TRUE);

//     $html  = "<div class='datagrid_background'>\n";
//     $html .= $cgx_table->toHtml();
//     $html .= "</div>\n";

//     $res = new xajaxResponse();
//     $res->assign('area-lines-dua', 'innerHTML', $html);
//     return $res;
// }

$xajax->register(XAJAX_FUNCTION, "workOrderLinesForm");
//$xajax->register(XAJAX_FUNCTION, "workOrderCoilForm");
$xajax->register(XAJAX_FUNCTION, "saveRWOS");
$xajax->register(XAJAX_FUNCTION, "updateRWOS");
$xajax->register(XAJAX_FUNCTION, 'editForm');
$xajax->register(XAJAX_FUNCTION, 'updateLine');
$xajax->register(XAJAX_FUNCTION, 'showLines');
//$xajax->register(XAJAX_FUNCTION, 'editFormDua');
//$xajax->register(XAJAX_FUNCTION, 'updateLineDua');
//$xajax->register(XAJAX_FUNCTION, 'showLinesDua');
//$xajax->register(XAJAX_FUNCTION, 'deleteLineDua');

?>