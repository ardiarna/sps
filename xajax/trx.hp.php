<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.org>
 * Nov 25, 2013 1:06:48 PM
 */

function saveBK($data) {
    global $APP_ID, $APP_CONNECTION;

    $res = new xajaxResponse();
    
    if (empty($data['c_spk_id'])) {
        $error = "Surat Perintah Kerja tidak boleh kosong";
    } elseif (cgx_emptydate($data['m_inout_date'])) {
        $error = "Tanggal produksi tidak boleh kosong";
    } elseif (count($data['lines']) == 0) {
        $error = "Detail item barang tidak boleh kosong, silahkan pilih minimal satu barang hail produksi.";
    }
    if ($error) {
        $res->alert($error);
        return $res;
    }
    
    // calculate total item required for each product on each warehouse
    $n = 0;
    foreach ($data['lines'] as $c_spk_line_id => $quantity) {
        $n++;
        $line = cgx_fetch_table(
            "SELECT c_spk_line_id, c_wo_line.m_product_id, product_code, m_product.spec, c_spk_line.quantity, balance_quantity " .
            "FROM c_spk_line JOIN c_wo_line USING (c_wo_line_id) " .
            "JOIN m_product ON (c_wo_line.m_product_id = m_product.m_product_id) " .
            "LEFT JOIN m_stock_warehouse ON (m_product.m_product_id = m_stock_warehouse.m_product_id AND m_stock_warehouse.m_warehouse_id = '{$data['wh'][$c_spk_line_id]}' AND latest = 'Y') " .
            "WHERE c_spk_line_id = '{$c_spk_line_id}'");
        $product_info[$line['m_product_id']] = $line['product_code'];
        $product_stock[$line['m_product_id']][$data['wh'][$c_spk_line_id]] = $line['balance_quantity'];
        $product_required[$line['m_product_id']][$data['wh'][$c_spk_line_id]] += $quantity;
        if ($quantity <= 0) $line_error .= " * Baris ke {$n}: jumlah barang diproduki tidak boleh kosong.\n";
    }
    
    // get warehouse name
    $rsx = mysql_query("SELECT * FROM m_warehouse", $APP_CONNECTION);
    while ($dtx = mysql_fetch_array($rsx)) $wh_name[$dtx['m_warehouse_id']] = $dtx['warehouse_name'];
    mysql_free_result($rsx);

    // if any error, display it and cancel operation
    if ($line_error) {
        $res->alert("Tidak bisa memproses :\n" . $line_error);
        return $res;
    }
    
    
    // everything should be ok
    // start the process
    require_once 'lib/class.Penomoran.php';
    $nomor = new Penomoran();
    $document_no = $nomor->urut('HP', org());
    $sql =
        "INSERT INTO m_inout (app_org_id, document_no, m_inout_date, c_spk_id, m_transaction_type_id)
        VALUES ('" . org() . "', '{$document_no}', '" . cgx_dmy2ymd($data['m_inout_date']) . "', {$data['c_spk_id']},10)";

    $rsx = mysql_query($sql, $APP_CONNECTION);
    if (!$rsx) {
        $error = mysql_error($APP_CONNECTION);
        $res->alert($error);
        return $res;
    }
    $bk_id = mysql_insert_id($APP_CONNECTION);
    
    foreach ($data['lines'] as $c_spk_line_id => $quantity) {
        $line = cgx_fetch_table(
            "SELECT c_spk_line_id, c_wo_line.m_product_id, c_production_plan_id ,product_code, m_product.spec, c_spk_line.quantity, balance_quantity " .
            "FROM c_spk_line JOIN c_wo_line USING (c_wo_line_id) " .
            "JOIN m_product ON (c_wo_line.m_product_id = m_product.m_product_id) " .
            "LEFT JOIN m_stock_balance ON (c_wo_line.m_product_id = m_stock_balance.m_product_id AND latest = 'Y') " .
            "WHERE c_spk_line_id = '{$c_spk_line_id}'");
        $sql = "INSERT INTO m_inout_line (m_inout_id, m_product_id, quantity, m_warehouse_id, c_spk_line_id)
            VALUES ('{$bk_id}', '{$line['m_product_id']}', '{$quantity}', '{$data['wh'][$c_spk_line_id]}', '{$c_spk_line_id}')";
        $rsx = mysql_query($sql, $APP_CONNECTION);
        
        $quantity0 = (double) $quantity;
        // mysql_query(
        //         "UPDATE c_spk_line SET producted_quantity = producted_quantity + {$quantity0}, nogood_quantity = nogood_quantity + {$data['linesket'][$c_spk_line_id]} , m_machine_id = '{$data['ms'][$c_spk_line_id]}' " .
        //         "WHERE c_spk_line_id = '{$c_spk_line_id}'",
        //         $APP_CONNECTION);
        mysql_query(
                "UPDATE c_production_plan SET production_result = production_result + {$quantity0} " .
                "WHERE c_production_plan_id = '{$line['c_production_plan_id']}'",
                $APP_CONNECTION);
        
        // update balance
        inout(org(),$line['m_product_id'], $data['wh'][$c_spk_line_id], $quantity);
    }
    
    $_SESSION[$APP_ID]['trx.hp']['info'] = "Dokumen sudah berhasil disimpan";
    $res->script("window.location = 'module.php?m=trx.hp&pkey[m_inout_id]={$bk_id}';");
    return $res;
}

function grid_qty_form($data) {
    $row_id = $data['record']['c_spk_line_id'];
    //$def_producted = $data['record']['quantity'] - $data['record']['producted_quantity'];
    $def_producted = $data['record']['quantity'];
    $html .= "<input id='txt{$row_id}' name='lines[{$row_id}]' type='text' size='8' value='{$def_producted}' style='text-align: right;' disabled>";
    return $html;
}

function grid_ket_form($data) {
    $row_id = $data['record']['c_spk_line_id'];
    $html .= "<input id='txtket{$row_id}' name='linesket[{$row_id}]' type='text' size='8' value='0' style='text-align: right;' disabled>";
    return $html;
}

function grid_ms_form($data) {
    global $APP_CONNECTION;
    $row_id = $data['record']['c_spk_line_id'];
    $rsx = mysql_query(
        "SELECT m_machine_id, machine_name FROM m_machine where app_org_id = ". org() ." ORDER BY machine_name ",$APP_CONNECTION);
    //if (mysql_num_rows($rsx) == 0) return "<span style='color: red; white-space: nowrap;'>Tidak ada barang</span>";
    $html = "<select id='ms{$row_id}' name='ms[{$row_id}]' disabled>";
    while ($dtx = mysql_fetch_array($rsx)) {
        $html .= "<option value='{$dtx['m_machine_id']}'>{$dtx['machine_name']}</option>";
    }
    $html .= "</select>";
    mysql_free_result($rsx);
    return $html;
}


function grid_wh_form($data) {
    global $APP_CONNECTION;
    $row_id = $data['record']['c_spk_line_id'];
    $rsx = mysql_query(
        "SELECT m_warehouse_id, warehouse_name, COALESCE(balance_quantity, 0) balance_quantity "
            . "FROM m_warehouse LEFT JOIN "
            . "(SELECT * FROM m_stock_warehouse WHERE m_product_id = '{$data['record']['m_product_id']}' AND latest = 'Y') X USING (m_warehouse_id) "
            . "ORDER BY balance_quantity DESC",
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
    $row_id = $data['record']['c_spk_line_id'];
    //$html .= "<input type='checkbox' onclick=\"document.getElementById('txt{$row_id}').disabled = !this.checked;document.getElementById('ms{$row_id}').disabled = !this.checked;document.getElementById('wh{$row_id}').disabled = !this.checked;document.getElementById('txtket{$row_id}').disabled = !this.checked;\">";
    $html .= "<input type='checkbox' onclick=\"document.getElementById('txt{$row_id}').disabled = !this.checked;document.getElementById('ms{$row_id}').disabled = !this.checked;document.getElementById('wh{$row_id}').disabled = !this.checked;\">";
    return $html;
}

function spkLinesForm($c_spk_id) {
    global $APP_CONNECTION, $cgx_TableAttribs, $cgx_HeaderAttribs, $cgx_dsn,
        $cgx_EvenRowAttribs, $cgx_OddRowAttribs, $cgx_RendererOptions;
    
    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';
    
    $datagrid = new Structures_DataGrid(9999);
    $cgx_options = array('dsn' => $cgx_dsn);
    $cgx_sql = "SELECT @curRow := @curRow + 1 AS line, c_spk_line.*, m_product.*, balance_quantity, production_result " .
            "FROM c_spk_line JOIN c_wo_line USING (c_wo_line_id) " .
            "JOIN c_production_plan USING (c_production_plan_id) " .
            "JOIN m_product ON (c_wo_line.m_product_id=m_product.m_product_id) " .
            "JOIN (SELECT @curRow := 0) r " .
            "LEFT JOIN m_stock_balance ON (m_product.m_product_id = m_stock_balance.m_product_id AND latest = 'Y') " .
            "WHERE production_result < c_spk_line.quantity  AND c_spk_id = '{$c_spk_id}'";
    $datagrid->bind($cgx_sql, $cgx_options);
    
    $cgx_table = new HTML_Table($cgx_TableAttribs);
    $cgx_tableHeader = & $cgx_table->getHeader();
    $cgx_tableBody = & $cgx_table->getBody();
    
    $datagrid->addColumn(new Structures_DataGrid_Column('Baris', 'line', NULL, array('align' => 'center'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Item<br>Number', 'product_code', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Spec', 'spec', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Jumah<br>Order', 'quantity', NULL, array('align' => 'right'), NULL, "cgx_format_3digit"));
    $datagrid->addColumn(new Structures_DataGrid_Column('Sudah<br>Diproduksi', 'production_result', NULL, array('align' => 'right'), NULL, "cgx_format_3digit"));
    $datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, "grid_chk_form"));
    $datagrid->addColumn(new Structures_DataGrid_Column('Mesin', NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, "grid_ms_form"));
    $datagrid->addColumn(new Structures_DataGrid_Column('Gudang', NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, "grid_wh_form"));
    $datagrid->addColumn(new Structures_DataGrid_Column('Jumlah<br>Diproduki', NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, "grid_qty_form"));
    //$datagrid->addColumn(new Structures_DataGrid_Column('Jumlah<br>N.G', NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, "grid_ket_form"));
    

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

$xajax->register(XAJAX_FUNCTION, "spkLinesForm");
$xajax->register(XAJAX_FUNCTION, "saveBK");

?>