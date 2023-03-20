<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.org>
 * Nov 25, 2013 1:06:48 PM
 */

function ctl_edit_bm($data) {
    if ($data['record']['m_inout_line_id']) {
        $href = "xajax_editForm('{$data['record']['line']}');";
        $out = "<img onclick=\"{$href}\" style='cursor: pointer;' src='images/icon_edit.png' border='0'>";
    return $out;
    }
}

function saveBM($data) {
    global $APP_ID, $APP_CONNECTION;
    $res = new xajaxResponse();
    
    if (empty($data['c_order_id'])) {
        $error = "Sales order tidak boleh kosong";
    } elseif (cgx_emptydate($data['m_inout_date'])) {
        $error = "Tanggal penerimaan tidak boleh kosong";
    } elseif (count($data['lines']) == 0) {
        $error = "Detail item barang tidak boleh kosong, silahkan pilih minimal satu barang yang akan dikirim.";
    }
    if ($error) {
        $res->alert($error);
        return $res;
    }
    // calculate total item required for each product on each warehouse
    $n = 0;
    foreach ($data['lines'] as $c_order_line_id => $quantity) {
        $n++;
        $line = cgx_fetch_table(
            "SELECT c_order_line_id, c_order_line.m_product_id, product_code, m_product.spec, order_quantity, balance_quantity " .
            "FROM c_order_line " .
            "JOIN m_product ON (c_order_line.m_product_id = m_product.m_product_id) " .
            "LEFT JOIN m_stock_warehouse_2 ON (m_product.m_product_id = m_stock_warehouse_2.m_product_id AND m_stock_warehouse_2.m_warehouse_id = '{$data['wh'][$c_order_line_id]}' AND m_stock_warehouse_2.app_org_id='".org()."' AND latest = 'Y') " .
            "WHERE c_order_line_id = '{$c_order_line_id}'");
        $product_info[$line['m_product_id']] = $line['product_code'];
        $product_stock[$line['m_product_id']][$data['wh'][$c_order_line_id]] = $line['balance_quantity'];
        $product_required[$line['m_product_id']][$data['wh'][$c_order_line_id]] += $quantity;
        if ($quantity <= 0) $line_error .= " * Baris ke {$n}: jumlah barang dikirim tidak boleh kosong.\n";
    }
    // get warehouse name
    $rsx = mysql_query("SELECT * FROM m_warehouse", $APP_CONNECTION);
    while ($dtx = mysql_fetch_array($rsx)) $wh_name[$dtx['m_warehouse_id']] = $dtx['warehouse_name'];
    mysql_free_result($rsx);
    // if any error, display it and cancel operation
    if ($line_error) {
        $res->alert("Tidak bisa memproses penerimaan barang:\n" . $line_error);
        return $res;
    }
    // everything should be ok start the process
    require_once 'lib/class.Penomoran.php';
    $nomor = new Penomoran();
    $document_no = $nomor->urut('BM', org());
    $sql =
        "INSERT INTO m_inout (app_org_id, document_no, c_order_id, m_inout_date, m_transaction_type_id, dokumen, create_user)
        VALUES ('" . org() . "', '{$document_no}', '{$data['c_order_id']}', '" . cgx_dmy2ymd($data['m_inout_date']) . "', 3, '{$data['dokumen']}', '". user() ."')";
    $rsx = mysql_query($sql, $APP_CONNECTION);
    if (!$rsx) {
        $error = mysql_error($APP_CONNECTION);
        $res->alert($error);
        return $res;
    }
    $bm_id = mysql_insert_id($APP_CONNECTION);
    foreach ($data['lines'] as $c_order_line_id => $quantity) {
        $line = cgx_fetch_table(
            "SELECT c_order_line_id, c_order_line.m_product_id, product_code, m_product.spec, order_quantity, balance_quantity " .
            "FROM c_order_line " .
            "JOIN m_product ON (c_order_line.m_product_id = m_product.m_product_id) " .
            "LEFT JOIN m_stock_balance_2 ON (c_order_line.m_product_id = m_stock_balance_2.m_product_id AND m_stock_balance_2.app_org_id='".org()."' AND latest = 'Y') " .
            "WHERE c_order_line_id = '{$c_order_line_id}'");
        if(org() == 3 or org() == 4) {
            $lnobox = $data['linesnobox'][$c_order_line_id];
            $lisibox = $data['linesisibox'][$c_order_line_id];
            $lnocoil = $data['linesnocoil'][$c_order_line_id];
            $lnolot = $data['linesnolot'][$c_order_line_id];
        } else {
            $lnobox = '';
            $lisibox = 0;
            $lnocoil = '';
            $lnolot = '';
        }
        $sql = "INSERT INTO m_inout_line (m_inout_id, m_product_id, quantity, m_warehouse_id, c_order_line_id, no_box, isi_box, no_coil, no_lot)
            VALUES ('{$bm_id}', '{$line['m_product_id']}', '{$quantity}', '{$data['wh'][$c_order_line_id]}', '{$c_order_line_id}', '{$lnobox}','{$lisibox}','{$lnocoil}','{$lnolot}')";
        $rsx = mysql_query($sql, $APP_CONNECTION);
        //===================================================================================================== update stock on hand
        stock_onhand(org(), user(), $line['m_product_id'], $data['m_inout_date'], $quantity, 0);
        //===================================================================================================== update stock balance
        inout(org(),$line['m_product_id'], $data['wh'][$c_order_line_id], $quantity);
    }
    $_SESSION[$APP_ID]['trx.bm']['info'] = "Dokumen sudah berhasil disimpan";
    $res->script("window.location = 'module.php?m=trx.bm&pkey[m_inout_id]={$bm_id}';");
    return $res;
}

function updateBM($data) {
    global $APP_ID, $APP_CONNECTION;
    $bm = $_SESSION[$APP_ID]['bm'];
    $res = new xajaxResponse();
    
    if (cgx_emptydate($data['m_inout_date'])) {
        $error = "Tanggal penerimaan tidak boleh kosong";
    } 
    if ($error) {
        $res->alert($error);
        return $res;
    }
    $sql = "UPDATE m_inout SET m_inout_date = '" . cgx_dmy2ymd($data['m_inout_date']) . "', c_order_id = '{$data['c_order_id']}', dokumen = '{$data['dokumen']}', update_date = NOW(), update_user = '" . user() . "' WHERE m_inout_id = '{$data['m_inout_id']}'";
    $rsx = mysql_query($sql, $APP_CONNECTION);
    if (!$rsx) {
        $error = mysql_error($APP_CONNECTION);
        $res->alert($error);
        return $res;
    }
    foreach ($bm['lines'] as $line) {
        $sql0 = "SELECT quantity FROM m_inout_line WHERE m_inout_line_id = '{$line['m_inout_line_id']}'";    
        $result0 = mysql_query($sql0, $APP_CONNECTION);
        $hasil0 = mysql_result($result0,0);
        if($hasil0){
            $qty_aw = $hasil0;
        }
         if(org() == 3 or org() == 4) {
            $lnobox = $line['no_box'];
            $lisibox = $line['isi_box'];
            $lnocoil = $line['no_coil'];
            $lnolot = $line['no_lot'];
        } else {
            $lnobox = '';
            $lisibox = 0;
            $lnocoil = '';
            $lnolot = '';
        }
        $sql = "UPDATE m_inout_line SET m_warehouse_id = '{$line['m_warehouse_id']}', no_box = '{$lnobox}', isi_box = '{$lisibox}', no_coil = '{$lnocoil}',  
                no_lot = '{$lnolot}', quantity = '{$line['quantity']}' WHERE m_inout_line_id = '{$line['m_inout_line_id']}'";
        $rsx = mysql_query($sql, $APP_CONNECTION);
        //============================================================================================= update stock on hand return
        $qty_aw_min = $qty_aw * -1 ;
        stock_onhand(org(), user(), $line['m_product_id'], $data['m_inout_date_a'], $qty_aw_min, 0);
        //============================================================================================  update stock on hand
        stock_onhand(org(), user(), $line['m_product_id'], $data['m_inout_date'], $line['quantity'], 0);
        //===================================================================================== update balance
        $adj_qty = $line['quantity'] - $qty_aw;
        inout(org(),$line['m_product_id'], $line['m_warehouse_id'], $adj_qty);
    }

    $_SESSION[$APP_ID]['trx.bm']['info'] = "Dokumen sudah berhasil diperbaharui";
    $res->script("window.location = 'module.php?m=trx.bm&pkey[m_inout_id]={$data['m_inout_id']}';");
    return $res;
}

function editForm($line_no) {
    global $APP_CONNECTION, $APP_ID, $APP_DATE_FORMAT, $APP_DATE_FORMAT_JAVA;
    global $mandatory;
    
    foreach ($_SESSION[$APP_ID]['bm']['lines'] as $line) {
        if ($line['line'] == $line_no) {
            $data = $line;
            break;
        }
    }

    $html .= "<form id='frmLine'>";
    $html .= "<input type='hidden' name='line' value='{$data['line']}'>";
    $html .= "<input type='hidden' id='c_order_line_id' name='c_order_line_id' value='{$data['c_order_line_id']}'>";
    $html .= "<input type='hidden' id='m_product_id' name='m_product_id' value='{$data['m_product_id']}'>";
    $html .= "<input type='hidden' id='m_warehouse_id' name='m_warehouse_id' value='{$data['m_warehouse_id']}'>";
    $html .= "<table width='100%'>";
    $html .= "<tr>";
    $html .= "<td width='12%'>Item Number {$mandatory}</td>";
    $html .= "<td width='33%'><input id='product_code' type='text' size='20' value=\"{$data['product_code']}\" readonly='readonly'></td>";
    $html .= "<td width='6%'></td>";
    $html .= "<td width='12%'>Kode Coil</td>";
    $html .= "<td width='37%'><input type='text' size='30' name='no_coil' value=\"{$data['no_coil']}\" style='text-align: left;'></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Barang</td>";
    $html .= "<td><input type='text' name='product_name' id='product_name' size='30' value=\"{$data['product_name']}\" readonly='readonly'></td>";
    $html .= "<td></td>";
    $html .= "<td>Lot Number</td>";
    $html .= "<td><input type='text' size='30' name='no_lot' value=\"{$data['no_lot']}\" style='text-align: left;'></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Description</td>";
    $html .= "<td><input type='text' size='30' id='description' value=\"{$data['description']}\" name='description' readonly='readonly'></td>";
    $html .= "<td></td>";
    $html .= "<td>No Box</td>";
    $html .= "<td><input type='text' size='42' name='no_box' value=\"{$data['no_box']}\" style='text-align: left;'></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Gudang {$mandatory}</td>";
    $html .= "<td>" . cgx_filter('m_warehouse_id', "SELECT m_warehouse_id, warehouse_name FROM m_warehouse WHERE " . org_filter_master() . " ORDER BY warehouse_name", $data['m_warehouse_id'], FALSE, ' disabled') . "</td>";
    $html .= "<td></td>";
    $html .= "<td>Isi Box</td>";
    $html .= "<td><input type='text' size='42' name='isi_box' value=\"{$data['isi_box']}\" style='text-align: left;'></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td></td>";
    $html .= "<td></td>";
    $html .= "<td></td>";
    $html .= "<td>Jumlah Barang {$mandatory}</td>";
    $html .= "<td><input type='text' size='10' name='quantity' value=\"{$data['quantity']}\" style='text-align: right;'></td>";
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
    //$res->script("\$(function() { \$('#schedule_delivery_date').datepicker({dateFormat: '{$APP_DATE_FORMAT_JAVA}'}); });");
    
    return $res;
}

function updateLine($data) {
    global $APP_ID;
    
    $bm = $_SESSION[$APP_ID]['bm'];
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
    
    if (is_array($bm['lines'])) {
        foreach ($bm['lines'] as $k => $d) {
            if ($d['line'] == $data['line']) {
                $bm['lines'][$k]['quantity'] = $data['quantity'];
                $bm['lines'][$k]['m_product_id'] = $data['m_product_id'];
                $bm['lines'][$k]['m_warehouse_id'] = $data['m_warehouse_id'];
                $bm['lines'][$k]['isi_box'] = $data['isi_box'];
                $bm['lines'][$k]['no_box'] = $data['no_box'];
                $bm['lines'][$k]['no_coil'] = $data['no_coil'];
                $bm['lines'][$k]['no_lot'] = $data['no_lot'];
                $product = cgx_fetch_table("SELECT * FROM m_product WHERE m_product_id = '{$data['m_product_id']}'");
                if (is_array($product)) foreach ($product as $pk => $pv) if (!is_numeric($pk)) $bm['lines'][$k][$pk] = $pv;
                $warehouse = cgx_fetch_table("SELECT * FROM m_warehouse WHERE m_warehouse_id = '{$data['m_warehouse_id']}'");
                if (is_array($warehouse)) foreach ($warehouse as $pk => $pv) if (!is_numeric($pk)) $bm['lines'][$k][$pk] = $pv;
                $line_updated = TRUE;
                break;
            }
        }
    }
    if (!$line_updated) {
        $new_line['quantity'] = $data['quantity'];
        $new_line['m_product_id'] = $data['m_product_id'];
        $new_line['m_warehouse_id'] = $data['m_warehouse_id'];
        $new_line['isi_box'] = $data['isi_box'];
        $new_line['no_box'] = $data['no_box'];
        $new_line['no_coil'] = $data['no_coil'];
        $new_line['no_lot'] = $data['no_lot'];
        $product = cgx_fetch_table("SELECT * FROM m_product WHERE m_product_id = '{$data['m_product_id']}'");
        if (is_array($product)) foreach ($product as $pk => $pv) if (!is_numeric($pk)) $new_line[$pk] = $pv;
        $warehouse = cgx_fetch_table("SELECT * FROM m_warehouse WHERE m_warehouse_id = '{$data['m_warehouse_id']}'");
        if (is_array($warehouse)) foreach ($warehouse as $pk => $pv) if (!is_numeric($pk)) $new_line[$pk] = $pv;
        $bm['lines'][] = $new_line;
    }
    $_SESSION[$APP_ID]['bm'] = $bm;
    
    $res->script("xajax_showLines('{$bm['m_inout_id']}', 'editH');");
    $res->assign('area-edit', 'style.display', 'none');
    $res->assign('master-button', 'style.display', '');
    return $res;
}

function grid_qty_form($data) {
    $row_id = $data['record']['c_order_line_id'];
    //$def_delivery = $data['record']['order_quantity'] - $data['record']['delivered_quantity'] + $data['record']['return_quantity'];
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
        "SELECT m_warehouse_id, warehouse_name, COALESCE(balance_quantity, 0) balance_quantity "
            . "FROM m_warehouse LEFT JOIN "
            . "(SELECT * FROM m_stock_warehouse_2 WHERE m_product_id = '{$data['record']['m_product_id']}' AND app_org_id='".org()."' AND latest = 'Y') X USING (m_warehouse_id) "
            . "ORDER BY balance_quantity DESC, warehouse_name",
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
    
    $data = $_SESSION[$APP_ID]['bm'];
    if ($data['m_inout_id'] != $m_inout_id) return;
    
    if (is_array($data['lines'])) {
        $n = 0;
        foreach ($data['lines'] as $k => $d) {
            $n++;
            $data['lines'][$k]['line'] = $n;
        }
    }
    $_SESSION[$APP_ID]['bm'] = $data;

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
    //$datagrid->addColumn(new Structures_DataGrid_Column('Ket', 'ket', NULL, array('align' => 'left'), NULL, NULL));
    if(org() == 3 or org() == 4) {
    $datagrid->addColumn(new Structures_DataGrid_Column('Kode Coil', 'no_coil', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Lot Number', 'no_lot', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('No Box', 'no_box', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Isi Box', 'isi_box', NULL, array('align' => 'left'), NULL, NULL));
    }
    $datagrid->addColumn(new Structures_DataGrid_Column('Jumlah Barang', 'quantity', NULL, array('align' => 'right'), NULL, "cgx_format_3digit"));
    if ($mode == 'editH') $datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '20'), NULL, 'ctl_edit_bm()'));

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

function salesOrderLinesForm($c_order_id) {
    global $APP_CONNECTION, $cgx_TableAttribs, $cgx_HeaderAttribs, $cgx_dsn,
        $cgx_EvenRowAttribs, $cgx_OddRowAttribs, $cgx_RendererOptions;
    
    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';
    
    $datagrid = new Structures_DataGrid(9999);
    $cgx_options = array('dsn' => $cgx_dsn);
    $cgx_sql = "SELECT @curRow := @curRow + 1 AS line, c_order_line.*, (c_order_line.order_quantity - c_order_line.delivered_quantity) as sisa_order, m_product.*, balance_quantity " .
            "FROM c_order_line " .
            "JOIN m_product USING (m_product_id) " .
            "JOIN (SELECT @curRow := 0) r " .
            "LEFT JOIN m_stock_balance_2 ON (m_product.m_product_id = m_stock_balance_2.m_product_id AND m_stock_balance_2.app_org_id='".org()."' AND latest = 'Y') " .
            "WHERE c_order_id = '{$c_order_id}'";
    $datagrid->bind($cgx_sql, $cgx_options);
    
    $cgx_table = new HTML_Table($cgx_TableAttribs);
    $cgx_tableHeader = & $cgx_table->getHeader();
    $cgx_tableBody = & $cgx_table->getBody();
    
    $datagrid->addColumn(new Structures_DataGrid_Column('Baris', 'line', NULL, array('align' => 'center'), NULL, NULL));
    //$datagrid->addColumn(new Structures_DataGrid_Column('Jadwal<br>Pengiriman', 'schedule_delivery_date', NULL, array('align' => 'center'), NULL, "cgx_format_date"));
    $datagrid->addColumn(new Structures_DataGrid_Column('Item<br>Number', 'product_code', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Spec', 'spec', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Deskripsi', 'description', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Sisa<br>Order', 'sisa_order', NULL, array('align' => 'right'), NULL, "cgx_format_3digit"));
    //$datagrid->addColumn(new Structures_DataGrid_Column('Jumlah<br>Order', 'order_quantity', NULL, array('align' => 'right'), NULL, "cgx_format_3digit"));
    //$datagrid->addColumn(new Structures_DataGrid_Column('Sudah<br>Dikirim', 'delivered_quantity', NULL, array('align' => 'right'), NULL, "cgx_format_3digit"));
    //$datagrid->addColumn(new Structures_DataGrid_Column('Jumlah<br>Return', 'return_quantity', NULL, array('align' => 'right'), NULL, "cgx_format_3digit"));
    $datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, "grid_chk_form"));
    $datagrid->addColumn(new Structures_DataGrid_Column('Stok<br>Gudang', NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, "grid_wh_form"));
    //$datagrid->addColumn(new Structures_DataGrid_Column('Ket', NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, "grid_ket_form"));
    if(org() == 3 or org() == 4) {
    $datagrid->addColumn(new Structures_DataGrid_Column('Kode Coil', NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, "grid_nocoil_form"));
    $datagrid->addColumn(new Structures_DataGrid_Column('Lot Number', NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, "grid_nolot_form"));
    $datagrid->addColumn(new Structures_DataGrid_Column('No Box', NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, "grid_nobox_form"));
    $datagrid->addColumn(new Structures_DataGrid_Column('Isi Box', NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, "grid_isibox_form"));
    }
    $datagrid->addColumn(new Structures_DataGrid_Column('Jumlah<br>Masuk', NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, "grid_qty_form"));    
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


$xajax->register(XAJAX_FUNCTION, "salesOrderLinesForm");
$xajax->register(XAJAX_FUNCTION, "saveBM");
$xajax->register(XAJAX_FUNCTION, "updateBM");
$xajax->register(XAJAX_FUNCTION, 'editForm');
$xajax->register(XAJAX_FUNCTION, 'updateLine');
$xajax->register(XAJAX_FUNCTION, 'showLines');

?>