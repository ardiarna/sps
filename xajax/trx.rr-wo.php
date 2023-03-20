<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.org>
 * Nov 25, 2013 1:06:48 PM
 */

function saveBK($data) {
    global $APP_ID, $APP_CONNECTION;

    $res = new xajaxResponse();
    
    if (empty($data['m_work_order_id'])) {
        $error = "Work order tidak boleh kosong";
    } elseif (cgx_emptydate($data['m_inout_date'])) {
        $error = "Tanggal penerimaan tidak boleh kosong";
    } elseif (count($data['lines']) == 0) {
        $error = "Detail item barang tidak boleh kosong, silahkan pilih minimal satu barang yang akan diterima.";
    }
    if ($error) {
        $res->alert($error);
        return $res;
    }
    
    // // calculate total item required for each product on each warehouse
    // $n = 0;
    // foreach ($data['lines'] as $m_work_order_line_id => $quantity) {
    //     $n++;
    //     $line = cgx_fetch_table(
    //         "SELECT m_work_order_line_id, m_product.m_product_id, product_code, m_product.spec, order_quantity " .
    //         "FROM m_work_order_line " .
    //         "JOIN m_product ON (m_work_order_line.m_lp_id = m_product.m_product_id) " .
    //         "WHERE m_work_order_line_id = '{$m_work_order_line_id}'");
    //     $product_info[$line['m_product_id']] = $line['product_code'];
    //     $product_stock[$line['m_product_id']][$data['wh'][$m_work_order_line_id]] = $line['balance_quantity'];
    //     $product_required[$line['m_product_id']][$data['wh'][$m_work_order_line_id]] += $quantity;
    //     if ($quantity <= 0) $line_error .= " * Baris ke {$n}: jumlah barang dikirim tidak boleh kosong.\n";
    // }
    
    // get warehouse name
    $rsx = mysql_query("SELECT * FROM m_warehouse", $APP_CONNECTION);
    while ($dtx = mysql_fetch_array($rsx)) $wh_name[$dtx['m_warehouse_id']] = $dtx['warehouse_name'];
    mysql_free_result($rsx);
    
    // if any error, display it and cancel operation
    if ($line_error) {
        $res->alert("Tidak bisa memproses penerimaan barang:\n" . $line_error);
        return $res;
    }
    
    
    // everything should be ok
    // start the process
    require_once 'lib/class.Penomoran.php';
    $nomor = new Penomoran();
    $document_no = $nomor->urut('BM', org());
    $sql =
        "INSERT INTO m_inout (app_org_id, document_no, m_work_order_id, m_inout_date, m_transaction_type_id)
        VALUES ('" . org() . "', '{$document_no}', '{$data['m_work_order_id']}', '" . cgx_dmy2ymd($data['m_inout_date']) . "', 3)";

    $rsx = mysql_query($sql, $APP_CONNECTION);
    if (!$rsx) {
        $error = mysql_error($APP_CONNECTION);
        $res->alert($error);
        return $res;
    }
    $bk_id = mysql_insert_id($APP_CONNECTION);
    
    foreach ($data['lines'] as $m_work_order_line_id => $quantity) {
        $line = cgx_fetch_table(
            "SELECT m_work_order_line_id, m_product.m_product_id, product_code, m_product.spec, order_quantity " .
            "FROM m_work_order_line " .
            "JOIN m_product ON (m_work_order_line.m_lp_id = m_product.m_product_id) " .
            "WHERE m_work_order_line_id = '{$m_work_order_line_id}'");
        $sql = "INSERT INTO m_inout_line (m_inout_id, m_product_id, quantity, m_warehouse_id, m_work_order_line_id, ket)
            VALUES ('{$bk_id}', '{$line['m_product_id']}', '{$quantity}', '{$data['wh'][$m_work_order_line_id]}', '{$m_work_order_line_id}', '{$data['linesket'][$m_work_order_line_id]}')";
        $rsx = mysql_query($sql, $APP_CONNECTION);
        
        $quantity0 = (double) $quantity;
        mysql_query(
                "UPDATE m_work_order_line SET producted_quantity = producted_quantity + {$quantity0} " .
                "WHERE m_work_order_line_id = '{$m_work_order_line_id}'",
                $APP_CONNECTION);
        
        // update balance
        inout(org(),$line['m_product_id'], $data['wh'][$m_work_order_line_id], 0, $quantity, FALSE);
    }
    
    $_SESSION[$APP_ID]['trx.rr-wo']['info'] = "Dokumen sudah berhasil disimpan";
    $res->script("window.location = 'module.php?m=trx.rr-wo&pkey[m_inout_id]={$bk_id}';");
    return $res;
}

function grid_qty_form($data) {
    $row_id = $data['record']['m_work_order_line_id'];
    $html .= "<input id='txt{$row_id}' name='lines[{$row_id}]' type='text' size='8' style='text-align: right;' disabled>";
    return $html;
}

function grid_ket_form($data) {
    $row_id = $data['record']['m_work_order_line_id'];
    $html .= "<input id='txtket{$row_id}' name='linesket[{$row_id}]' type='text' size='8' style='text-align: left;' disabled>";
    return $html;
}

function grid_wh_form($data) {
    global $APP_CONNECTION;
    $row_id = $data['record']['m_work_order_line_id'];
    $rsx = mysql_query(
        "SELECT m_warehouse_id, warehouse_name FROM m_warehouse WHERE " . org_filter_master() . " ORDER BY warehouse_name",
        $APP_CONNECTION);
    $html = "<select id='wh{$row_id}' name='wh[{$row_id}]' disabled>";
    while ($dtx = mysql_fetch_array($rsx)) {
        $html .= "<option value='{$dtx['m_warehouse_id']}'>{$dtx['warehouse_name']}</option>";
    }
    $html .= "</select>";
    mysql_free_result($rsx);
    return $html;
}

function grid_chk_form($data) {
    $row_id = $data['record']['m_work_order_line_id'];
    $html .= "<input type='checkbox' onclick=\"document.getElementById('txt{$row_id}').disabled = !this.checked;document.getElementById('wh{$row_id}').disabled = !this.checked;document.getElementById('txtket{$row_id}').disabled = !this.checked;\">";
    return $html;
}

function salesOrderLinesForm($m_work_order_id) {
    global $APP_CONNECTION, $cgx_TableAttribs, $cgx_HeaderAttribs, $cgx_dsn,
        $cgx_EvenRowAttribs, $cgx_OddRowAttribs, $cgx_RendererOptions;
    
    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';
    
    $datagrid = new Structures_DataGrid(9999);
    $cgx_options = array('dsn' => $cgx_dsn);
    $cgx_sql = "SELECT @curRow := @curRow + 1 AS line, m_work_order_line.*, m_product.*, balance_quantity " .
            "FROM m_work_order_line " .
            "JOIN m_product ON m_work_order_line.m_lp_id = m_product.m_product_id " .
            "JOIN (SELECT @curRow := 0) r " .
            "LEFT JOIN m_stock_balance ON (m_product.m_product_id = m_stock_balance.m_product_id AND latest = 'Y') " .
            "WHERE m_work_order_id = '{$m_work_order_id}'";
    $datagrid->bind($cgx_sql, $cgx_options);
    
    $cgx_table = new HTML_Table($cgx_TableAttribs);
    $cgx_tableHeader = & $cgx_table->getHeader();
    $cgx_tableBody = & $cgx_table->getBody();
    
    $datagrid->addColumn(new Structures_DataGrid_Column('Baris', 'line', NULL, array('align' => 'center'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Item<br>Number', 'product_code', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Spec', 'spec', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('OD', 'od', NULL, array('align' => 'right', 'width' => '8%'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Tebal', 'thickness', NULL, array('align' => 'right', 'width' => '8%'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Panjang', 'length', NULL, array('align' => 'right', 'width' => '8%'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, "grid_chk_form"));
    $datagrid->addColumn(new Structures_DataGrid_Column('Gudang', NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, "grid_wh_form"));
    $datagrid->addColumn(new Structures_DataGrid_Column('Ket', NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, "grid_ket_form"));
    $datagrid->addColumn(new Structures_DataGrid_Column('Jumlah', NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, "grid_qty_form"));

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
$xajax->register(XAJAX_FUNCTION, "saveBK");

?>