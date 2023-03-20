<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.org>
 * Nov 24, 2013 8:18:31 AM
 */


function showLines($product_id, $mode = NULL) {
    global $APP_CONNECTION, $APP_ID, $cgx_TableAttribs, $cgx_HeaderAttribs,
        $cgx_EvenRowAttribs, $cgx_OddRowAttribs, $cgx_RendererOptions;
    
    $data = $_SESSION[$APP_ID]['stobal'];
    if ($data['m_product_id'] != $product_id) return;
    
    if (is_array($data['lines'])) {
        $n = 0;
        foreach ($data['lines'] as $k => $d) {
            $n++;
            $data['lines'][$k]['line'] = $n;
        }
    }
    $_SESSION[$APP_ID]['stobal'] = $data;

    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';
    
    $datagrid = new Structures_DataGrid(9999);
    $datagrid->bind($data['lines'], array(), 'Array');
    $cgx_table = new HTML_Table($cgx_TableAttribs);
    $cgx_tableHeader = & $cgx_table->getHeader();
    $cgx_tableBody = & $cgx_table->getBody();
    
    $datagrid->addColumn(new Structures_DataGrid_Column('ID Barang', 'm_product_id', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Item Number', 'product_code', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Nama Barang', 'product_name', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Tanggal', 'm_inout_date', NULL, array('align' => 'center'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Prev Quantity', 'prev_quantity', NULL, array('align' => 'right'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('In Quantity', 'in_quantity', NULL, array('align' => 'right'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Out Quantity', 'out_quantity', NULL, array('align' => 'right'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Balance Quantity', 'balance_quantity', NULL, array('align' => 'right'), NULL, "cgx_format_3digit()"));

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

function ctl_edit_wh($data) {
    $href = "xajax_editFormWH('{$data['record']['line']}');";
    $out = "<img onclick=\"{$href}\" style='cursor: pointer;' title='Edit' src='images/icon_edit.png' border='0'>";
    return $out;
}

function ctl_delete_wh($data) {    
    $href = "xajax_deleteLineWH('{$data['record']['line']}');";
    $out = "<img onclick=\"{$href}\" style='cursor: pointer;' title='Hapus' src='images/icon_delete.png' border='0'>";
    return $out;
}

function showLinesWH($product_id, $mode = NULL) {
    global $APP_CONNECTION, $APP_ID, $cgx_TableAttribs, $cgx_HeaderAttribs,
        $cgx_EvenRowAttribs, $cgx_OddRowAttribs, $cgx_RendererOptions;
    
    $data = $_SESSION[$APP_ID]['stobal'];
    if ($data['m_product_id'] != $product_id) return;
    
    if (is_array($data['lines'])) {
        $n = 0;
        foreach ($data['lines'] as $k => $d) {
            $n++;
            $data['lines'][$k]['line'] = $n;
        }
    }
    $_SESSION[$APP_ID]['stobal'] = $data;

    
    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';
    
    $datagrid = new Structures_DataGrid(9999);
    $datagrid->bind($data['lines'], array(), 'Array');
    $cgx_table = new HTML_Table($cgx_TableAttribs);
    $cgx_tableHeader = & $cgx_table->getHeader();
    $cgx_tableBody = & $cgx_table->getBody();
    
    $datagrid->addColumn(new Structures_DataGrid_Column('Warehouse', 'warehouse_name', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Item Number', 'product_code', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Spec', 'spec', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('OD', 'od', NULL, array('align' => 'right'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Tebal', 'thickness', NULL, array('align' => 'right'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Panjang', 'length', NULL, array('align' => 'right'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Balance Quantity', 'balance_quantity', NULL, array('align' => 'right'), NULL, "cgx_format_3digit()"));
    if ($mode == 'edit') $datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '20'), NULL, 'ctl_edit_wh()'));
    if ($mode == 'edit') $datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '20'), NULL, 'ctl_delete_wh()'));

    $datagrid->fill($cgx_table, $cgx_RendererOptions);
    $cgx_tableHeader->setRowAttributes(0, $cgx_HeaderAttribs);
    $cgx_tableBody->altRowAttributes(0, $cgx_EvenRowAttribs, $cgx_OddRowAttribs, TRUE);

    $html  = "<div class='datagrid_background'>\n";
    $html .= $cgx_table->toHtml();
    $html .= "</div>\n";

    $res = new xajaxResponse();
    $res->assign('whouse-lines', 'innerHTML', $html);
    $res->script("xajax_hitungTotal();"); 
    return $res;
}

function editFormWH($line_no) {
    global $APP_CONNECTION, $APP_ID, $APP_DATE_FORMAT, $APP_DATE_FORMAT_JAVA;
    global $mandatory;
    
    foreach ($_SESSION[$APP_ID]['stobal']['lines'] as $line) {
        if ($line['line'] == $line_no) {
            $data = $line;
            break;
        }
    }
    
    $html .= "<form id='frmLineWH'>";
    $html .= "<input type='hidden' name='line' value='{$data['line']}'>";
    $html .= "<input type='hidden' name='m_stock_warehouse_id' id='m_stock_warehouse_id' value='{$data['m_stock_warehouse_id']}'>";
    $html .= "<table width='100%'>";
    $html .= "<tr>";
    $html .= "<td width='12%'>Warehouse {$mandatory}</td>";
    $html .= "<td width='33%'>" . cgx_form_select('m_warehouse_id', "SELECT m_warehouse_id, warehouse_name FROM m_warehouse WHERE " . org_filter_master() . " ORDER BY warehouse_name", $data['m_warehouse_id'], FALSE, ($data['m_stock_warehouse_id'] ? " disabled" : "")) . "</td>";
    $html .= "<td width='10%'></td>";
    $html .= "<td width='12%'>Quantity {$mandatory}</td>";
    $html .= "<td width='33%'><input type='text' size='10' name='balance_quantity' value=\"{$data['balance_quantity']}\" style='text-align: right;'></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td></td>";
    $html .= "<td><input type='button' value='" . ($line_no ? 'Update' : 'Tambahkan') . "' onclick=\"xajax_updateLineWH(xajax.getFormValues('frmLineWH'));\"> &nbsp; ";
    $html .= "<input type='button' value='Batal' onclick=\"document.getElementById('area-editWH').style.display = 'none'; document.getElementById('master-button').style.display = ''; document.getElementById('daerah-total').style.display = '';\"></td>";
    $html .= "</tr>";
    $html .= "</table>";
    $html .= "</form>";
    
    $res = new xajaxResponse();
    $res->assign('area-editWH', 'innerHTML', $html);
    $res->assign('area-editWH', 'style.display', '');
    $res->assign('master-button', 'style.display', 'none');
    $res->assign('daerah-total', 'style.display', 'none');

    return $res;
}

function updateLineWH($data) {
    global $APP_ID;
    
    $wh = $_SESSION[$APP_ID]['stobal'];
    $res = new xajaxResponse();
    if ((int) $data['balance_quantity'] <= 0) {
        $error = "Jumlah barang tidak boleh kosong";
    }
    if ($error) {
        $res->alert($error);
        return $res;
    }
    
    if (is_array($wh['lines'])) {
        foreach ($wh['lines'] as $k => $d) {
            if ($d['line'] == $data['line']) {
                $wh['lines'][$k]['m_stock_warehouse_id'] = $data['m_stock_warehouse_id'];
                $wh['lines'][$k]['balance_quantity'] = $data['balance_quantity'];
                $warehouse = cgx_fetch_table("SELECT * FROM m_warehouse WHERE m_warehouse_id = '{$data['m_warehouse_id']}'");
                if (is_array($warehouse)) foreach ($warehouse as $pk => $pv) if (!is_numeric($pk)) $wh['lines'][$k][$pk] = $pv;
                $line_updated = TRUE;
                break;
            }
        }
    }
    if (!$line_updated) {
        $new_line['m_stock_warehouse_id'] = $data['m_stock_warehouse_id'];
        $new_line['m_warehouse_id'] = $data['m_warehouse_id'];
        $new_line['m_product_id'] = $_REQUEST['pkey']['m_product_id'];
        $new_line['balance_quantity'] = $data['balance_quantity'];
        $warehouse = cgx_fetch_table("SELECT * FROM m_warehouse WHERE m_warehouse_id = '{$data['m_warehouse_id']}'");
        if (is_array($warehouse)) foreach ($warehouse as $pk => $pv) if (!is_numeric($pk)) $new_line[$pk] = $pv;
        $produk = cgx_fetch_table("SELECT * FROM m_product WHERE m_product_id = '{$_REQUEST['pkey']['m_product_id']}'");
        if (is_array($produk)) foreach ($produk as $pk => $pv) if (!is_numeric($pk)) $new_line[$pk] = $pv;
        $wh['lines'][] = $new_line;
    }
    $_SESSION[$APP_ID]['stobal'] = $wh;
    
    $res->script("xajax_showLinesWH('{$wh['m_product_id']}', 'edit');");
    $res->assign('area-editWH', 'style.display', 'none');
    $res->assign('master-button', 'style.display', '');
    return $res;
}

function deleteLineWH($line_no) {
    global $APP_ID, $APP_CONNECTION;
    
    $wh = $_SESSION[$APP_ID]['stobal'];
    foreach ($wh['lines'] as $k => $line) {
        if ($line['line'] == $line_no) {
            $del = $k;
            if ($line['m_stock_warehouse_id']) {
                if ($wh['delete']) {
                    $wh['delete'][] = $line['m_stock_warehouse_id'];
                } else {
                    $wh['delete'] = array($line['m_stock_warehouse_id']);
                }
            }
        }
    }
    unset($wh['lines'][$del]);
    $_SESSION[$APP_ID]['stobal'] = $wh;

    $res = new xajaxResponse();
    $res->script("xajax_showLinesWH('{$wh['m_product_id']}', 'edit');");
    $res->assign('area-editWH', 'style.display', 'none');
    $res->assign('master-button', 'style.display', '');
    return $res;
}

function hitungTotal() {
    global $APP_ID, $APP_CONNECTION;
    
    $wh = $_SESSION[$APP_ID]['stobal'];
    $tot_balance_qty = 0;
    foreach ($wh['lines'] as $line) {
        $tot_balance_qty += $line['balance_quantity'];
    }

    
    if($_REQUEST['mode'] == 'edit'){
        $html  = "<table style='float:right;margin-top:10px;' width='37%'><tr>";
        $html .= "<td>Total</td>";
        $html .= "<td>:</td>";
        $html .= "<td align='right'>".number_format($tot_balance_qty)."</td>";
        $html .= "<td width='45px;'>&nbsp;</td>";
        $html .= "</tr></table>";
    } else {
        $html  = "<table style='float:right;margin-top:10px;' width='35%'><tr>";
        $html .= "<td>Total</td>";
        $html .= "<td>:</td>";
        $html .= "<td align='right'>".number_format($tot_balance_qty)."</td>";
        $html .= "</tr></table>";
    }

    $res = new xajaxResponse();
    $res->assign('daerah-total', 'innerHTML', $html);
    $res->assign('daerah-total', 'style.display', '');
    return $res;
}

function saveWH($produk) {
    global $APP_ID, $APP_CONNECTION;
    
    $wh = $_SESSION[$APP_ID]['stobal'];
    $res = new xajaxResponse();
    $tglHRini = date("d-m-Y");
    foreach ($wh['lines'] as $line) {
        if ($line['m_stock_warehouse_id']) {
            $current = cgx_fetch_table("SELECT * FROM m_stock_warehouse_2 WHERE m_stock_warehouse_id = '{$line['m_stock_warehouse_id']}' ");        
            $prev_balance = (double) $current['balance_quantity'];
        } else {
            $prev_balance = 0;
        }
        $adj_qty =  $line['balance_quantity'] - $prev_balance;
        if($adj_qty < 0) {
            $quantity = $adj_qty * -1 ;
            //============== update balance ================================================================================
            inout(org(), $line['m_product_id'], $line['m_warehouse_id'], 0, $quantity, FALSE);
            //============== update stock on hand ==========================================================================
            stock_onhand(org(), user(), $line['m_product_id'], $tglHRini, 0, $quantity);
        } else {
            $quantity = $adj_qty;
            //============== update balance ================================================================================
            inout(org(), $line['m_product_id'], $line['m_warehouse_id'], $quantity);
            //============== update stock on hand ==========================================================================
            stock_onhand(org(), user(), $line['m_product_id'], $tglHRini, $quantity, 0);
        }
        //$produk .= org()." -- ".$line['m_product_id']." -- ".$line['m_warehouse_id']." -- "."0"." -- ".$quantity." -- ".$tglHRini." // ";
    }
    if (is_array($wh['delete'])) {
        $tot_hapus = 0;
        foreach ($wh['delete'] as $d) {
            $current = cgx_fetch_table("SELECT * FROM m_stock_warehouse_2 WHERE m_stock_warehouse_id = '{$d}' ");
            $prev_balance = (double) $current['balance_quantity'];
            $tot_hapus += $prev_balance;
            mysql_query("UPDATE m_stock_warehouse_2 SET latest = 'N' WHERE m_stock_warehouse_id = '{$d}'", $APP_CONNECTION);
            mysql_query("UPDATE m_stock_warehouse_d_2 SET latest = 'N' WHERE m_product_id = '{$current['m_product_id']}' AND m_warehouse_id = '{$current['m_warehouse_id']}' AND app_org_id = '".org()."' ", $APP_CONNECTION);
        }
        //============== update stock on hand ==========================================================================
        stock_onhand(org(), user(), $produk, $tglHRini, 0, $tot_hapus);
        // total balance detail
        $current = cgx_fetch_table(
            "SELECT * FROM m_stock_balance_2 " .
            "WHERE m_product_id = '{$produk}' AND latest = 'Y' AND app_org_id = '".org()."' ");
        $prev_balance = (double) $current['balance_quantity'];
        $balance = $prev_balance - $tot_hapus;
        mysql_query(
            "UPDATE m_stock_balance_2 SET latest = 'N' WHERE m_product_id = '{$produk}' AND app_org_id = '".org()."' ",
            $APP_CONNECTION);
        mysql_query(
            "INSERT INTO m_stock_balance_2 (app_org_id,m_product_id, balance_date, prev_quantity, " .
            "out_quantity, balance_quantity, latest) VALUES " .
            "('".org()."','{$produk}', NOW(), '{$prev_balance}', '{$tot_hapus}', '{$balance}', 'Y')",
            $APP_CONNECTION); 
        // total balance daily
        $current = cgx_fetch_table(
            "SELECT * FROM m_stock_balance_d_2 " .
            "WHERE m_product_id = '{$produk}' AND latest = 'Y' AND app_org_id = '".org()."' ");
        $prev_balance = (double) $current['balance_quantity'];
        $balance = $prev_balance - $tot_hapus;
        mysql_query(
            "UPDATE m_stock_balance_d_2 SET latest = 'N' WHERE m_product_id = '{$produk}' AND app_org_id = '".org()."' ",
            $APP_CONNECTION);
        mysql_query(
            "INSERT INTO m_stock_balance_d_2 (app_org_id,m_product_id, balance_date, prev_quantity, " .
            "out_quantity, balance_quantity, latest) VALUES " .
            "('".org()."','{$produk}', NOW(), '{$prev_balance}', '{$tot_hapus}', '{$balance}', 'Y') " .
            "ON DUPLICATE KEY UPDATE " .
            "out_quantity = out_quantity + '{$tot_hapus}', " .
            "balance_quantity = prev_quantity + in_quantity - out_quantity, " .
            "latest = 'Y'",
            $APP_CONNECTION);
    }
    $_SESSION[$APP_ID]['view.stobal']['info'] = "[Success] Good Movement";
    $res->script("window.location = 'module.php?m=view.stobal&pkey[m_product_id]={$produk}&whouse=y';");
    return $res;
}

$xajax->register(XAJAX_FUNCTION, 'showLines');
$xajax->register(XAJAX_FUNCTION, 'showLinesWH');
$xajax->register(XAJAX_FUNCTION, 'editFormWH');
$xajax->register(XAJAX_FUNCTION, 'updateLineWH');
$xajax->register(XAJAX_FUNCTION, 'deleteLineWH');
$xajax->register(XAJAX_FUNCTION, 'hitungTotal');
$xajax->register(XAJAX_FUNCTION, 'saveWH');

?>