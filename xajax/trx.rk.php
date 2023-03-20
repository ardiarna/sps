<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.org>
 * Dec 4, 2013 9:28:48 PM
 */

function display_textbox($data) {
    $html = "<input name='koreksi[{$data['record']['m_inout_line_id']}]' type='text' style='width: 100px; text-align: right;' value=\"0\">";
    return $html;
}

function showLines($m_inout_id) {
    global $cgx_TableAttribs, $cgx_HeaderAttribs, $cgx_dsn,
        $cgx_EvenRowAttribs, $cgx_OddRowAttribs, $cgx_RendererOptions;
    
    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';
    
    $datagrid = new Structures_DataGrid(9999);
    $cgx_options = array('dsn' => $cgx_dsn);
    $cgx_sql = "SELECT @curRow := @curRow + 1 AS line, m_inout_line_id, m_product.*, warehouse_name, quantity
        FROM m_inout_line
        JOIN m_product USING (m_product_id)
        JOIN m_warehouse USING (m_warehouse_id)
        JOIN (SELECT @curRow := 0) r
        WHERE m_inout_id = '{$m_inout_id}'";
    $datagrid->setDefaultSort(array('m_inout_line_id' => 'ASC'));
    $datagrid->bind($cgx_sql, $cgx_options);
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
    $datagrid->addColumn(new Structures_DataGrid_Column('Jumah Barang<br>Dikirim', 'quantity', NULL, array('align' => 'right', 'width' => '10%'), NULL, "cgx_format_3digit"));
    $datagrid->addColumn(new Structures_DataGrid_Column('Jumlah Barang<br>Return', NULL, NULL, array('align' => 'center', 'width' => '1%'), NULL, "display_textbox"));
    
    $datagrid->fill($cgx_table, $cgx_RendererOptions);
    $cgx_tableHeader->setRowAttributes(0, $cgx_HeaderAttribs);
    $cgx_tableBody->altRowAttributes(0, $cgx_EvenRowAttribs, $cgx_OddRowAttribs, TRUE);

    $html  = "<div class='datagrid_background' style='margin-top: 4px;'>\n";
    $html .= $cgx_table->toHtml();
    $html .= "</div>\n";
    
    $res = new xajaxResponse();
    $res->assign('area-lines', 'innerHTML', $html);
    return $res;
}

function saveRK($data) {
    global $APP_CONNECTION;
    
    $res = new xajaxResponse();
    
    // mandatory field check
    if (cgx_emptydate($data['m_inout_date'])) {
        $res->alert("Tanggal return tidak boleh kosong");
        return $res;
    } elseif (empty($data['bk_m_inout_id'])) {
        $res->alert("Nomor pengiriman barang tidak boleh kosong");
        return $res;
    }
    
    // is there any value?
    $count = 0;
    foreach ($data['koreksi'] as $n) $count += $n;
    if ($count == 0) {
        $res->alert("Harus ada jumlah yang di-return, tidak boleh semuanya nol");
        return $res;
    }
    
    // return value validation
    // the value must be between 0 and number of delivered
    $rsx = mysql_query(
            "SELECT * FROM m_inout_line " .
            "WHERE m_inout_id = '{$data['bk_m_inout_id']}' " .
            "ORDER BY m_inout_line_id",
            $APP_CONNECTION);
    $line = 0;
    while ($dtx = mysql_fetch_array($rsx, MYSQL_ASSOC)) {
        $line++;
        if ($data['koreksi'][$dtx['m_inout_line_id']] < 0
                || $data['koreksi'][$dtx['m_inout_line_id']] > $dtx['quantity']) {
            $error .= "Baris {$line}: Nilai yang diperbolehkan adalah antara 0 sampai dengan {$dtx['quantity']}\n";
        }
    }
    mysql_free_result($rsx);
    if ($error) {
        $res->alert($error);
        return $res;
    }
    
    // check stock
//    $line = 0;
//    foreach ($data['koreksi'] as $m_inout_line_id => $return_value) {
//        $line++;
//        if ($return_value == 0) continue;
//        $delivered = npl_fetch_table(
//                "SELECT * FROM m_inout_line WHERE m_inout_line_id = '{$m_inout_line_id}'");
//        $stock = npl_fetch_table(
//                "SELECT balance_quantity, warehouse_name " .
//                "FROM m_stock_warehouse_2 " .
//                "JOIN m_warehouse USING (m_warehouse_id) " .
//                "WHERE m_product_id = '{$delivered['m_product_id']}' " .
//                "AND m_warehouse_id = '{$delivered['m_warehouse_id']}' " .
//                "AND latest = 'Y'");
//        if ($stock['balance_quantity'] < $return_value) {
//            $error .= "Baris {$line}: Stok di '{$stock['warehouse_name']}' tidak mencukupi untuk di-return\n";
//        }
//    }
//    if ($error) {
//        $res->alert($error);
//        return $res;
//    }
    
    // execute return process
    require_once 'lib/class.Penomoran.php';
    $nomor = new Penomoran();
    $document_no = $nomor->urut('RK', org());
    mysql_query(
            "INSERT INTO m_inout (app_org_id, m_inout_date, document_no, m_transaction_type_id, m_inout_id_ref) " .
            "VALUES ('" . org() . "', '" . cgx_dmy2ymd($data['m_inout_date']) . "', '{$document_no}', 5, '{$data['bk_m_inout_id']}')",
            $APP_CONNECTION);
    $rk_id = mysql_insert_id($APP_CONNECTION);
    foreach ($data['koreksi'] as $m_inout_line_id => $return_value) {
        if ($return_value == 0) continue;
        $delivered = npl_fetch_table(
                "SELECT * FROM m_inout_line WHERE m_inout_line_id = '{$m_inout_line_id}'");
        mysql_query(
                "UPDATE c_order_line " .
                "SET return_quantity = return_quantity + '{$return_value}' " .
                "WHERE c_order_line_id = '{$delivered['c_order_line_id']}'",
                $APP_CONNECTION);
        mysql_query(
                "INSERT INTO m_inout_line (m_inout_id, m_product_id, m_warehouse_id, return_ref, quantity) " .
                "VALUES ('{$rk_id}', '{$delivered['m_product_id']}', '{$delivered['m_warehouse_id']}', '{$m_inout_line_id}', '{$return_value}')",
                $APP_CONNECTION);
        inout(org(),$delivered['m_product_id'], $delivered['m_warehouse_id'], $return_value, 0, FALSE);
    }
    
    $res->script("window.location = 'module.php?m=trx.rk&pkey[m_inout_id]={$rk_id}';");
    return $res;
}

$xajax->register(XAJAX_FUNCTION, 'showLines');
$xajax->register(XAJAX_FUNCTION, 'saveRK');

?>