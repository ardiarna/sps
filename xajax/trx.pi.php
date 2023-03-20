<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.org>
 * Nov 24, 2013 8:18:31 AM
 */


// function dapat(){
//     global $APP_CONNECTION;
//     $sql = "select balance_quantity from m_stock_warehouse_d_2 where m_product_id = 1 AND m_warehouse_id = 1 AND L";
//     $result = mysql_query($sql, $APP_CONNECTION);
//     $row = mysql_result($result,0);
//     return $row;
// }

function ctl_edit_bk($data) {
    $href = "xajax_editForm('{$data['record']['line']}');";
    $out = "<img onclick=\"{$href}\" style='cursor: pointer;' src='images/icon_edit.png' border='0'>";
    return $out;
}

function ctl_delete_bk($data) {
    $href = "xajax_deleteLine('{$data['record']['line']}');";
    $out = "<img onclick=\"{$href}\" style='cursor: pointer;' src='images/icon_delete.png' border='0'>";
    return $out;
}

function showLines($m_inout_id, $mode = NULL) {
    global $APP_CONNECTION, $APP_ID, $cgx_TableAttribs, $cgx_HeaderAttribs,
        $cgx_EvenRowAttribs, $cgx_OddRowAttribs, $cgx_RendererOptions;
    
    $data = $_SESSION[$APP_ID]['pi'];
    if ($data['m_inout_id'] != $m_inout_id) return;
    
    if (is_array($data['lines'])) {
        $n = 0;
        foreach ($data['lines'] as $k => $d) {
            $n++;
            $data['lines'][$k]['line'] = $n;
        }
    }
    $_SESSION[$APP_ID]['pi'] = $data;

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
    $datagrid->addColumn(new Structures_DataGrid_Column('Jumlah On Hand<br>Total', 'qty_oht', NULL, array('align' => 'right'), NULL, "cgx_format_3digit"));
    $datagrid->addColumn(new Structures_DataGrid_Column('Jumlah On Hand<br>Gudang', 'qty_oh', NULL, array('align' => 'right'), NULL, "cgx_format_3digit"));
    $datagrid->addColumn(new Structures_DataGrid_Column('Jumlah Hitung', 'quantity', NULL, array('align' => 'right'), NULL, "cgx_format_3digit"));
    $datagrid->addColumn(new Structures_DataGrid_Column('Adjusment', 'qty_adj', NULL, array('align' => 'right'), NULL, "cgx_format_3digit"));
    
    //if ($mode == 'edit') $datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, 'ctl_edit_bk()'));
    //if ($mode == 'edit') $datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, 'ctl_delete_bk()'));

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
    
    foreach ($_SESSION[$APP_ID]['pi']['lines'] as $line) {
        if ($line['line'] == $line_no) {
            $data = $line;
            break;
        }
    }

    $html .= "<form id='frmLine'>";
    $html .= "<input type='hidden' name='line' value='{$data['line']}'>";
    $html .= "<input type='hidden' id='m_product_id' name='m_product_id' value='{$data['m_product_id']}'>";
    $html .= "<table width='100%'>";
    $html .= "<tr>";
    $html .= "<td width='12%'>Item Number {$mandatory}</td>";
    $html .= "<td width='33%'><input id='product_code' type='text' size='20' value=\"{$data['product_code']}\" readonly='readonly'><img onclick=\"popupReference('product');\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'></td>";
    $html .= "<td width='10%'></td>";
    $html .= "<td width='12%'>Gudang{$mandatory}</td>";
    $html .= "<td width='33%'>" . cgx_form_select('m_warehouse_id', "SELECT m_warehouse_id, warehouse_name FROM m_warehouse WHERE " . org_filter_master() . " ORDER BY warehouse_name", $data['m_warehouse_id'], FALSE) . "</td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Barang</td>";
    $html .= "<td><input type='text' name='product_name' id='product_name' size='30' value=\"{$data['product_name']}\" readonly='readonly'></td>";
    $html .= "<td></td>";
    $html .= "<td>Jumlah {$mandatory}</td>";
    $html .= "<td><input type='text' size='10' name='quantity' value=\"{$data['quantity']}\" style='text-align: right;'></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Description</td>";
    $html .= "<td><input type='text' size='30' id='item_description' value=\"{$data['item_description']}\" name='item_description' readonly='readonly'></td>";
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

// function deleteLine($line_no) {
//     global $APP_ID, $APP_CONNECTION;
    
//     $pi = $_SESSION[$APP_ID]['bki'];
//     foreach ($pi['lines'] as $k => $line) {
//         if ($line['line'] == $line_no) {
//             $del = $k;
//             if ($line['m_inout_line_id']) {
//                 if ($pi['delete']) {
//                     $pi['delete'][] = $line['m_inout_line_id'];
//                 } else {
//                     $pi['delete'] = array($line['m_inout_line_id']);
//                 }
//             }
//         }
//     }
//     unset($pi['lines'][$del]);
//     $_SESSION[$APP_ID]['bki'] = $pi;

//     $res = new xajaxResponse();
//     $res->script("xajax_showLines('{$pi['m_inout_id']}', 'edit');");
//     $res->assign('area-edit', 'style.display', 'none');
//     $res->assign('master-button', 'style.display', '');
//     return $res;
// }

function savePI($data) {
    global $APP_ID, $APP_CONNECTION;
    
    $pi = $_SESSION[$APP_ID]['pi'];
    $res = new xajaxResponse();
    if (cgx_emptydate($data['m_inout_date'])) {
        $error = "Tanggal transaksi tidak boleh kosong";
    } elseif (count($pi['lines']) == 0) {
        $error = "Detail item barang tidak boleh kosong, harus ada minimal satu baris.";
    }
    if ($error) {
        $res->alert($error);
        return $res;
    }
    
    if ($data['m_inout_id']) {
        $sql = "UPDATE m_inout SET m_inout_date = '" . cgx_dmy2ymd($data['m_inout_date']) . "' WHERE m_inout_id = '{$data['m_inout_id']}'";
    } else {
        require_once 'lib/class.Penomoran.php';
        $nomor = new Penomoran();
        $document_no = $nomor->urut('PI', org());
        $sql =
        "INSERT INTO m_inout (app_org_id, document_no, m_inout_date, m_transaction_type_id)
         VALUES ('" . org() . "',  '{$document_no}', '" . cgx_dmy2ymd($data['m_inout_date']) . "', 10)";
    }
    
    $rsx = mysql_query($sql, $APP_CONNECTION);
    if (!$rsx) {
        $error = mysql_error($APP_CONNECTION);
        $res->alert($error);
        return $res;
    }

    if ($data['m_inout_id']) {
        $pi_id = $data['m_inout_id'];
    } else {
        $pi_id = mysql_insert_id($APP_CONNECTION);
    }

    if ($data['m_inout_id']) {
        $sql = "UPDATE c_pinventory SET create_date = '" . cgx_dmy2ymd($data['m_inout_date']) . "', create_user = '". USER() ."' WHERE m_inout_id = '{$data['m_inout_id']}'";
    } else {
        $sql = 
        "INSERT INTO c_pinventory (app_org_id, m_inout_id, create_date, create_user) 
         VALUES ('" . org() . "',  '{$pi_id}', '" . cgx_dmy2ymd($data['m_inout_date']) . "', '" . USER() . "')";
    }

    $rsx = mysql_query($sql, $APP_CONNECTION);
    if (!$rsx) {
        $error = mysql_error($APP_CONNECTION);
        $res->alert($error);
        return $res;
    }
    $pit_id = mysql_insert_id($APP_CONNECTION);

    foreach ($pi['lines'] as $line) {
        if ($line['m_inout_line_id']) {
            $sql = "";
        } else {
            $sql = "select balance_quantity from m_stock_warehouse_d_2 where m_product_id = '{$line['m_product_id']}' AND m_warehouse_id = '{$line['m_warehouse_id']}' AND latest = 'Y' ";
            $result = mysql_query($sql, $APP_CONNECTION);
            $hasil = mysql_result($result,0);

            if($hasil){
                $qty_oh = $hasil;
            }else{
                $qty_oh ='0';
            }

            $qty_adj = $line['quantity'] - $qty_oh;

            $sql = "select balance_quantity from m_stock_balance_d_2 where m_product_id = '{$line['m_product_id']}' AND app_org_id = '". org() ."' AND latest = 'Y' ";
            $result = mysql_query($sql, $APP_CONNECTION);
            $hasil = mysql_result($result,0);

            if($hasil){
                $qty_oht = $hasil;
            }else{
                $qty_oht ='0';
            }

            $sql = "INSERT INTO m_inout_line (m_inout_id, m_product_id, quantity, m_warehouse_id)
                     VALUES ('{$pi_id}', '{$line['m_product_id']}', '{$line['quantity']}', '{$line['m_warehouse_id']}')";
            $rsx = mysql_query($sql, $APP_CONNECTION);
            if (!$rsx) {
                $error = mysql_error($APP_CONNECTION);
                $res->alert($error);
                return $res;
            }

            $pi_line_id = mysql_insert_id($APP_CONNECTION);
            $sql = "INSERT INTO c_pinventory_line (c_pinventory_id, m_inout_line_id, m_product_id, m_warehouse_id, qty_count, qty_oh, qty_oht, qty_adj) 
                     VALUES ('{$pit_id}', '{$pi_line_id}' ,'{$line['m_product_id']}', '{$line['m_warehouse_id']}', '{$line['quantity']}', '{$qty_oh}', '{$qty_oht}', '{$qty_adj}')";
            $rsx = mysql_query($sql, $APP_CONNECTION);
            if (!$rsx) {
                $error = mysql_error($APP_CONNECTION);
                $res->alert($error);
                return $res;
            }

            

            if($qty_adj >= 0){
                inout(org(),$line['m_product_id'], $line['m_warehouse_id'], $qty_adj);
            }else{
                $qty_adj = $qty_adj * -1 ;
                inout(org(),$line['m_product_id'], $line['m_warehouse_id'], 0, $qty_adj, FALSE);    
            }
            
        }   
    }
    $_SESSION[$APP_ID]['trx.pi']['info'] = "Dokumen sudah berhasil disimpan";
    $res->script("window.location = 'module.php?m=trx.pi&pkey[m_inout_id]={$pi_id}';");
    return $res;
}

function updateLine($data) {
    global $APP_ID;
    
    $pi = $_SESSION[$APP_ID]['pi'];
    $res = new xajaxResponse();
    if ((int) $data['quantity'] < 0) {
        $error = "Jumlah barang tidak boleh minus";
    } elseif (empty($data['m_product_id'])) {
        $error = "Kode barang tidak boleh kosong";
    }
    if ($error) {
        $res->alert($error);
        return $res;
    }
    
    if (is_array($pi['lines'])) {
        foreach ($pi['lines'] as $k => $d) {
            // if ($d['line'] == $data['line']) {
            //     $pi['lines'][$k]['quantity'] = $data['quantity'];
            //     $pi['lines'][$k]['m_product_id'] = $data['m_product_id'];
            //     $pi['lines'][$k]['m_warehouse_id'] = $data['m_warehouse_id'];
            //     $pi['lines'][$k]['qty_adj'] = $data['m_warehouse_id'];
            //     $product = cgx_fetch_table("SELECT * FROM m_product WHERE m_product_id = '{$data['m_product_id']}'");
            //     if (is_array($product)) foreach ($product as $pk => $pv) if (!is_numeric($pk)) $pi['lines'][$k][$pk] = $pv;
            //     $warehouse = cgx_fetch_table("SELECT * FROM m_warehouse WHERE m_warehouse_id = '{$data['m_warehouse_id']}'");
            //     if (is_array($warehouse)) foreach ($warehouse as $pk => $pv) if (!is_numeric($pk)) $pi['lines'][$k][$pk] = $pv;
            //     $pinventory = cgx_fetch_table("SELECT m_stock_warehouse_d_id, balance_quantity as qty_oh FROM m_stock_warehouse_d_2 WHERE m_product_id = '{$data['m_product_id']}' AND m_warehouse_id = '{$data['m_warehouse_id']}' AND latest = 'Y'");
            //     if (is_array($pinventory)) foreach ($pinventory as $pk => $pv) if (is_numeric($pk)) $pi['lines'][$k][$pk] = $pv;
                
            //     $line_updated = TRUE;
            //     break;
            // }
        }
    }
    if (!$line_updated) {
        $new_line['quantity'] = $data['quantity'];
        $new_line['m_product_id'] = $data['m_product_id'];
        $new_line['m_warehouse_id'] = $data['m_warehouse_id'];
        $product = cgx_fetch_table("SELECT * FROM m_product WHERE m_product_id = '{$data['m_product_id']}'");
        if (is_array($product)) foreach ($product as $pk => $pv) if (!is_numeric($pk)) $new_line[$pk] = $pv;
        $warehouse = cgx_fetch_table("SELECT * FROM m_warehouse WHERE m_warehouse_id = '{$data['m_warehouse_id']}'");
        if (is_array($warehouse)) foreach ($warehouse as $pk => $pv) if (!is_numeric($pk)) $new_line[$pk] = $pv;
        $pinventory = cgx_fetch_table("SELECT m_stock_warehouse_d_id, balance_quantity as qty_oh FROM m_stock_warehouse_d_2 WHERE m_product_id = '{$data['m_product_id']}' AND m_warehouse_id = '{$data['m_warehouse_id']}' AND latest = 'Y'");
        if (is_array($pinventory)) foreach ($pinventory as $pk => $pv) if (!is_numeric($pk)) $new_line[$pk] = $pv;
        $pinventoryt = cgx_fetch_table("SELECT m_stock_balance_d_id, balance_quantity as qty_oht FROM m_stock_balance_d_2 WHERE m_product_id = '{$data['m_product_id']}' AND app_org_id = '" . org() ."' AND latest = 'Y'");
        if (is_array($pinventoryt)) foreach ($pinventoryt as $pk => $pv) if (!is_numeric($pk)) $new_line[$pk] = $pv;
        $new_line['qty_adj'] = $data['quantity'] - $new_line['qty_oh'];
        $pi['lines'][] = $new_line;
    }
    $_SESSION[$APP_ID]['pi'] = $pi;
    $res->script("xajax_showLines('{$pi['m_inout_id']}', 'edit');");
    $res->assign('area-edit', 'style.display', 'none');
    $res->assign('master-button', 'style.display', '');
    return $res;
}

$xajax->register(XAJAX_FUNCTION, 'showLines');
$xajax->register(XAJAX_FUNCTION, 'editForm');
$xajax->register(XAJAX_FUNCTION, 'updateLine');
$xajax->register(XAJAX_FUNCTION, 'deleteLine');
$xajax->register(XAJAX_FUNCTION, 'savePI');

?>
