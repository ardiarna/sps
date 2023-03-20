<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.org>
 * Dec 4, 2013 9:28:48 PM
 */

function display_textbox($data) {
    $html = "<input name='koreksi[{$data['record']['m_inout_line_id']}]' type='text' style='width: 100px; text-align: right;' value=\"{$data['record']['quantity']}\">";
    return $html;
}

function showLines($m_inout_id, $mode = NULL) {
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
    $datagrid->addColumn(new Structures_DataGrid_Column('Jumah Barang<br>Diterima', 'quantity', NULL, array('align' => 'right', 'width' => '10%'), NULL, "cgx_format_3digit"));
    $datagrid->addColumn(new Structures_DataGrid_Column('Koreksi<br>Jumlah Barang', NULL, NULL, array('align' => 'center', 'width' => '1%'), NULL, "display_textbox"));
    
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

function saveRM($data) {
    global $APP_CONNECTION;
    
    $res = new xajaxResponse();
    
    // mandatory field check
    if (cgx_emptydate($data['m_inout_date'])) {
        $res->alert("Tanggal koreksi tidak boleh kosong");
        return $res;
    } elseif (empty($data['bm_m_inout_id'])) {
        $res->alert("Nomor penerimaan barang tidak boleh kosong");
        return $res;
    }
    
    // reject negative value
    $has_negative = FALSE;
    foreach ($data['koreksi'] as $v) {
        if ($v < 0) $has_negative = TRUE;
    }
    if ($has_negative) {
        $res->alert("Nilai koreksi harus lebih besar atau sama dengan nol");
        return $res;
    }
    
    // is there any correction?
    $rsx = mysql_query(
            "SELECT * FROM m_inout_line " .
            "WHERE m_inout_id = '{$data['bm_m_inout_id']}' " .
            "ORDER BY m_inout_line_id",
            $APP_CONNECTION);
    $changed = FALSE;
    $line = 0;
    while ($dtx = mysql_fetch_array($rsx, MYSQL_ASSOC)) {
        $line++;
        if ($data['koreksi'][$dtx['m_inout_line_id']] != $dtx['quantity']) $changed = TRUE;
        $changes = $data['koreksi'][$dtx['m_inout_line_id']] - $dtx['quantity'];
        $correction[$dtx['m_inout_line_id']] = $dtx;
        $correction[$dtx['m_inout_line_id']]['value'] = $changes;
        $correction[$dtx['m_inout_line_id']]['line'] = $line;
    }
    mysql_free_result($rsx);
    if ($changed == FALSE) {
        $res->alert("Tidak ada koreksi penerimaan barang");
        return $res;
    }
    
    // check stock
    foreach ($correction as $m_inout_line_id => $c) {
        if ($c['value'] == 0) continue;
        $stock = npl_fetch_table(
                "SELECT balance_quantity, warehouse_name " .
                "FROM m_stock_warehouse_2 " .
                "JOIN m_warehouse USING (m_warehouse_id) " .
                "WHERE m_product_id = '{$c['m_product_id']}' " .
                "AND m_warehouse_id = '{$c['m_warehouse_id']}' " .
                "AND latest = 'Y'");
        if ($stock['balance_quantity'] + $c['value'] < 0) {
            $error .= "Baris {$c['line']}: Stok di '{$stock['warehouse_name']}' tidak mencukupi untuk dikoreksi\n";
        }
    }
    if ($error) {
        $res->alert($error);
        return $res;
    }
    
    // execute correction process
    require_once 'lib/class.Penomoran.php';
    $nomor = new Penomoran();
    $document_no = $nomor->urut('RM', org());
    mysql_query(
            "INSERT INTO m_inout (app_org_id, m_inout_date, document_no, m_transaction_type_id, m_inout_id_ref) " .
            "VALUES ('" . org() . "', '" . cgx_dmy2ymd($data['m_inout_date']) . "', '{$document_no}', 6, '{$data['bm_m_inout_id']}')",
            $APP_CONNECTION);
    $rm_id = mysql_insert_id($APP_CONNECTION);
    foreach ($correction as $m_inout_line_id => $c) {
        if ($c['value'] == 0) continue;
        mysql_query(
                "INSERT INTO m_inout_line (m_inout_id, m_product_id, m_warehouse_id, return_ref, quantity) " .
                "VALUES ('{$rm_id}', '{$c['m_product_id']}', '{$c['m_warehouse_id']}', '{$m_inout_line_id}', '{$c['value']}')",
                $APP_CONNECTION);
        if ($c['value'] > 0) {
            inout(org(),$c['m_product_id'], $c['m_warehouse_id'], $c['value'], 0, FALSE);
        } else {
            inout(org(),$c['m_product_id'], $c['m_warehouse_id'], 0, 0 - $c['value'], FALSE);
        }
    //======================================================================================    
        //update stock on hand
        //mengecek tanggal
        $sql = "SELECT * FROM m_stock_onhand WHERE m_product_id = '{$c['m_product_id']}' AND m_inout_date = '" . cgx_dmy2ymd($data['bm_m_inout_date']) . "' AND app_org_id = '" . org() . "' ";
        $result = mysql_query($sql, $APP_CONNECTION);
        $hasil = mysql_fetch_array($result, MYSQL_ASSOC);
        //mengecek tanggal sebelumnya
        $sql2 = "SELECT max(m_inout_date) m_inout_date FROM m_stock_onhand WHERE m_product_id = '{$c['m_product_id']}' AND m_inout_date < '" . cgx_dmy2ymd($data['bm_m_inout_date']) . "' AND app_org_id = '" . org() . "' ";    
        $result2 = mysql_query($sql2, $APP_CONNECTION);
        $hasil2 = mysql_result($result2,0);
        if($hasil2){
            $sql3 = "SELECT balance_quantity FROM m_stock_onhand WHERE m_product_id = '{$c['m_product_id']}' AND m_inout_date = '{$hasil2}' AND app_org_id = '" . org() . "' ";    
            $result3 = mysql_query($sql3, $APP_CONNECTION);
            $hasil3 = mysql_result($result3,0);
            if($hasil3){
                $prev_qty = $hasil3;
            }else{
                $prev_qty = '0';
            }
        }else{
            $prev_qty = '0';   
        }

        if($hasil){
            $in_qty = $hasil['in_quantity'] + $c['value'];
            $out_qty = $hasil['out_quantity'];
            $balance_qty = $prev_qty + $in_qty - $out_qty;
            $sql = "UPDATE m_stock_onhand SET prev_quantity = '{$prev_qty}', in_quantity = '{$in_qty}', balance_quantity = '{$balance_qty}', update_user = '" . user() . "', update_date = NOW() WHERE m_stock_onhand_id = '{$hasil['m_stock_onhand_id']}'";
            $result = mysql_query($sql, $APP_CONNECTION);
        }else{
            $balance_qty = $prev_qty + $c['value'];
            $sql = "INSERT INTO m_stock_onhand (app_org_id,m_product_id,m_inout_date,prev_quantity,in_quantity,out_quantity,balance_quantity, "
                    . "update_user,update_date) VALUES ("
                    . "'" . org() . "', '{$c['m_product_id']}', '" . cgx_dmy2ymd($data['bm_m_inout_date']) . "', '{$prev_qty}', '{$c['value']}', "
                    . "'0', '{$balance_qty}', '" . user() . "', NOW())";
            $result = mysql_query($sql, $APP_CONNECTION); 
        }

        //update stock di tanggal-tanggal selanjutnya
        $sql = "select * from m_stock_onhand where m_product_id = '{$c['m_product_id']}' AND m_inout_date > '" . cgx_dmy2ymd($data['bm_m_inout_date']) . "' AND app_org_id = '" . org() . "' ORDER BY m_inout_date ";    
        $result = mysql_query($sql, $APP_CONNECTION);
        while ($hasil = mysql_fetch_array($result, MYSQL_ASSOC)) {
            $sql2 = "SELECT max(m_inout_date) m_inout_date FROM m_stock_onhand WHERE m_product_id = '{$c['m_product_id']}' AND m_inout_date < '{$hasil['m_inout_date']}' AND app_org_id = '" . org() . "' ";    
            $result2 = mysql_query($sql2, $APP_CONNECTION);
            $hasil2 = mysql_result($result2,0);            
            if($hasil2){
                $sql3 = "SELECT balance_quantity FROM m_stock_onhand WHERE m_product_id = '{$c['m_product_id']}' AND m_inout_date = '{$hasil2}' AND app_org_id = '" . org() . "' ";    
                $result3 = mysql_query($sql3, $APP_CONNECTION);
                $hasil3 = mysql_result($result3,0);
                if($hasil3){
                    $prevqty = $hasil3;
                }else{
                    $prevqty = '0';
                }
            }else{
                $prevqty = '0';   
            }
            $balanceqty = $prevqty + $hasil['in_quantity'] - $hasil['out_quantity'];;
            $sql_up = "UPDATE m_stock_onhand SET prev_quantity = '{$prevqty}', balance_quantity = '{$balanceqty}' WHERE m_stock_onhand_id = '{$hasil['m_stock_onhand_id']}'";
            $result_up = mysql_query($sql_up, $APP_CONNECTION);
        }
    //======================================================================================
    }
    
    $res->script("window.location = 'module.php?&m=trx.rm&pkey[m_inout_id]={$rm_id}';");
    return $res;
}

$xajax->register(XAJAX_FUNCTION, 'showLines');
$xajax->register(XAJAX_FUNCTION, 'saveRM');

?>