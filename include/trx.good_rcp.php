<?php

/**
 * (c) Azwari Nugraha <nugraha@pt-gai.com>
 * 25/11/2013 00:52:21
 */

// Penerimaan Barang berdasarkan sales order

echo "<div class='title'>".title_header('trx.good_rcp', org())."</div>";

function cgx_edit($data) {
    $href = "module.php?&m={$_REQUEST['m']}&pkey[m_inout_id]={$data['record']['m_inout_id']}";
    $out = "<a href='{$href}'><img title='Edit' src='images/icon_edit.png' border='0'></a>";
    return $out;
}

function cgx_delete($data) {
    $href  = "javascript:if(confirm('Hapus baris ini?')){window.location='action/trx.good_rcp.php";
    $href .= "?backvar=module.php%253F%2526m%253Dtrx.good_rcp&mode=delete&pkey[m_inout_id]={$data['record']['m_inout_id']}';}";
    $out = "<a href=\"{$href}\"><img title='Delete this row' src='images/icon_delete.png' border='0'></a>";
    return $out;
}

if (strlen($_REQUEST['pkey']['m_inout_id']) > 0) {
    include_once 'trx.good_rcp.edit.php';
} else {
    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';

    if (is_array($_SESSION[$GLOBALS['APP_ID']]['trx.good_rcp']['columns'])) {
        $cgx_def_columns = $_SESSION[$GLOBALS['APP_ID']]['trx.good_rcp']['columns'];
    } else {
        $cgx_def_columns = array(
            'm_inout_id' => 1,
            'm_inout_date' => 1,
            'document_no' => 1,
            'so' => 1,
            'remark' => 1,
            'partner_name' => 1,
            'no_kendaraan' => 1,
            'sj_date' => 1,
        );
        $_SESSION[$GLOBALS['APP_ID']]['trx.good_rcp']['columns'] = $cgx_def_columns;
    }
    
    $cgx_sql = "SELECT m_inout.*, c_order.document_no so, remark, partner_name  
            FROM m_inout JOIN c_order USING (c_order_id) 
            JOIN c_bpartner USING (c_bpartner_id) 
            WHERE m_inout.m_transaction_type_id = 12 ";
    $cgx_sql .= " AND " . org_filter_trx('m_inout.app_org_id');
    
    $cgx_datagrid = new Structures_DataGrid($cgx_max_rows);
    $cgx_options = array('dsn' => $cgx_dsn);
    $cgx_datagrid->setDefaultSort(array('m_inout_date' => 'DESC'));

    $cgx_search = $_REQUEST['q'];

    echo "<form name='frmFILTER' action='{$_SERVER['SCRIPT_NAME']}'>\n";
    echo "<input type='hidden' name='m' value='{$_REQUEST['m']}'>\n";
    echo "<table id='bar' class='datagrid_bar' width='100%'><tr>\n";
    echo "<td><input type='button' value='Dokumen Baru' onclick=\"window.location = 'module.php?&m={$_REQUEST['m']}&pkey[m_inout_id]=0&mode=edit';\"></td>";
    echo "<td align='right'><input type='text' size='20' name='q' value=\"{$cgx_search}\"></td>\n";
    echo "<td width='1'><input title='Cari' type='image' src='images/icon_search.png' border='0' style='padding-right: 20px;'></td>\n";
    echo "<td></td>\n";
    echo "<td width='20'></td>\n";
    if (has_privilege('trx.good_rcp')) {
        echo "<td width='1' class='datagrid_bar_icon'><a title='Dokumen Baru' href='module.php?&m={$_REQUEST['m']}&pkey[m_inout_id]=0&mode=edit'><img border='0' src='images/icon_add.png'></a></td>\n";
    } else {
        echo "<td width='1' class='datagrid_bar_icon'><img border='0' src='images/icon_add_dis.png'></td>\n";
    }
    echo "</tr></table>\n";
    echo "</form>\n";
    $cgx_sql .= " and ( m_inout.m_inout_id LIKE '%{$cgx_search}%' OR m_inout.document_no LIKE '%{$cgx_search}%' OR m_inout.dokumen LIKE '%{$cgx_search}%' OR c_order.document_no LIKE '%{$cgx_search}%' OR remark LIKE '%{$cgx_search}%' OR partner_name LIKE '%{$cgx_search}%')";
    if ($_SESSION[$GLOBALS['APP_ID']]['trx.good_rcp']['error']) {
        echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['trx.good_rcp']['error']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['trx.good_rcp']['error']);
    }

    if ($_SESSION[$GLOBALS['APP_ID']]['trx.good_rcp']['info']) {
        echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['trx.good_rcp']['info']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['trx.good_rcp']['info']);
    }


    $cgx_test = $cgx_datagrid->bind($cgx_sql, $cgx_options);
    if (PEAR::isError($cgx_test)) {
        echo $cgx_test->getMessage();
    }

    if ($cgx_def_columns['m_inout_id'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('ID', 'm_inout_id', 'm_inout_id', array('align' => 'right'), NULL, NULL));
    if ($cgx_def_columns['m_inout_date'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Tanggal Masuk', 'm_inout_date', 'm_inout_date', array('align' => 'center'), NULL, "cgx_format_date()"));
    if ($cgx_def_columns['document_no'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Nomor Receipt', 'document_no', 'document_no', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['so'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Purchase Order', 'so', 'so', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['remark'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Contract No', 'remark', 'remark', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['partner_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Vendor', 'partner_name', 'partner_name', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['no_kendaraan'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Nomor SJ', 'no_kendaraan', 'no_kendaraan', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['sj_date'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Tanggal SJ', 'sj_date', 'sj_date', array('align' => 'center'), NULL, "cgx_format_date()"));
    if (has_privilege('trx.good_rcp')) $cgx_datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, 'cgx_edit()'));
    //if (has_privilege('trx.good_rcp')) $cgx_datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, 'cgx_delete()'));

    $cgx_table = new HTML_Table($cgx_TableAttribs);
    $cgx_tableHeader = & $cgx_table->getHeader();
    $cgx_tableBody = & $cgx_table->getBody();

    $cgx_test = $cgx_datagrid->fill($cgx_table, $cgx_RendererOptions);
    if (PEAR::isError($cgx_test)) {
        echo $cgx_test->getMessage();
    }

    $cgx_tableHeader->setRowAttributes(0, $cgx_HeaderAttribs);
    $cgx_tableBody->altRowAttributes(0, $cgx_EvenRowAttribs, $cgx_OddRowAttribs, TRUE);

    echo "<div class='datagrid_background'>\n";
    echo $cgx_table->toHtml();
    echo "</div>\n";

    echo "<table width='100%'><tr>\n";
    echo "<td class='datagrid_pager'>Data berjumlah " . number_format($cgx_datagrid->getRecordCount()) . " baris</td>\n";
    echo "<td align='right' class='datagrid_pager'>\n";
    $cgx_test = $cgx_datagrid->render(DATAGRID_RENDER_PAGER);
    if (PEAR::isError($cgx_test)) {
        echo $cgx_test->getMessage();
    }
    echo "</td></tr></table>\n";
}

?>