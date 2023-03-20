<?php

/**
 * (c) Azwari Nugraha <nugraha@pt-gai.com>
 * 23/11/2013 12:40:39
 */


echo "<div class='title'>Work Order Manual</div>";

function cgx_edit($data) {
    $href = "module.php?m={$_REQUEST['m']}&pkey[m_work_order_id]={$data['record']['m_work_order_id']}";
    $out = "<a href='{$href}'><img title='Edit' src='images/icon_edit.png' border='0'></a>";
    return $out;
}

function cgx_delete($data) {
    $href  = "javascript:if(confirm('Hapus baris ini?')){window.location='action/trx.wo.php";
    $href .= "?backvar=module.php%253F%2526m%253Dtrx.wo&mode=delete&pkey[m_work_order_id]={$data['record']['m_work_order_id']}';}";
    $out = "<a href=\"{$href}\"><img title='Delete this row' src='images/icon_delete.png' border='0'></a>";
    return $out;
}

function grid_wo($data) {
    $href = "module.php?m={$_REQUEST['m']}&pkey[m_work_order_id]={$data['record']['m_work_order_id']}";
    $out = "<a href='{$href}'>{$data['record']['document_no']}</a>";
    return $out;
}

function grid_status1($data) {
    return $GLOBALS['WO_STATUS'][$data['record']['status']];
}

if (strlen($_REQUEST['pkey']['m_work_order_id']) > 0) {
    include_once 'trx.wo.edit.php';
} else {
    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';

    if (is_array($_SESSION[$GLOBALS['APP_ID']]['trx.wo']['columns'])) {
        $cgx_def_columns = $_SESSION[$GLOBALS['APP_ID']]['trx.wo']['columns'];
    } else {
        $cgx_def_columns = array(
            'm_work_order_id' => 1,
            'order_date' => 1,
            'document_no' => 1,
            'machine_name' => 1,
            'so' => 1,
            'partner_name' => 1,
            'proces_name' => 1,
        );
        $_SESSION[$GLOBALS['APP_ID']]['trx.wo']['columns'] = $cgx_def_columns;
    }
    
    $cgx_sql = "SELECT m_work_order.*, COALESCE(remark,c_forecast.document_no) so, COALESCE(c_bpartner.partner_name,cb2.partner_name) partner_name, 
        machine_name, proces_name 
        FROM m_work_order 
        JOIN m_machine USING (m_machine_id)
        JOIN c_proces USING (c_proces_id) 
        JOIN m_work_order_line USING (m_work_order_id) 
        LEFT JOIN c_order USING (c_order_id)
        LEFT JOIN c_bpartner USING (c_bpartner_id)
        LEFT JOIN c_forecast ON (m_work_order_line.c_forecast_id=c_forecast.c_forecast_id)
        LEFT JOIN c_bpartner cb2 ON (c_forecast.c_bpartner_id=cb2.c_bpartner_id) WHERE m_work_order.type_id = 'W' ";
    $cgx_sql .= " AND " . org_filter_trx('m_work_order.app_org_id');
    
    $cgx_datagrid = new Structures_DataGrid($cgx_max_rows);
    $cgx_options = array('dsn' => $cgx_dsn);
    $cgx_datagrid->setDefaultSort(array('order_date' => 'DESC'));
    $cgx_search = $_REQUEST['q'];
    
    echo "<form name='frmFILTER' action='{$_SERVER['SCRIPT_NAME']}'>\n";
    echo "<input type='hidden' name='m' value='{$_REQUEST['m']}'>\n";
    echo "<table id='bar' class='datagrid_bar' width='100%' border='0'><tr>\n";
    echo "<td><input type='button' value='Dokumen Baru' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}&pkey[m_work_order_id]=0&mode=edit';\"></td>";
    echo "<td align='right'><input type='text' size='20' name='q' value=\"{$cgx_search}\"></td>\n";
    echo "<td width='1'><input title='Cari' type='image' src='images/icon_search.png' border='0' style='padding-right: 20px;'></td>\n";
    echo "<td></td>\n";
    echo "<td width='20'></td>\n";
    if (has_privilege('trx.wo')) {
        echo "<td width='1' class='datagrid_bar_icon'><a title='Dokumen Baru' href='module.php?m={$_REQUEST['m']}&pkey[m_work_order_id]=0&mode=edit'><img border='0' src='images/icon_add.png'></a></td>\n";
    } else {
        echo "<td width='1' class='datagrid_bar_icon'><img border='0' src='images/icon_add_dis.png'></td>\n";
    }
    echo "</tr></table>\n";
    echo "</form>\n";    
    $cgx_sql .= " and ( m_work_order.m_work_order_id LIKE '%{$cgx_search}%' OR m_work_order.document_no LIKE '%{$cgx_search}%' OR machine_name LIKE '%{$cgx_search}%' OR c_order.remark LIKE '%{$cgx_search}%' OR c_bpartner.partner_name LIKE '%{$cgx_search}%' OR cb2.partner_name LIKE '%{$cgx_search}%' OR proces_name LIKE '%{$cgx_search}%')";
    if ($_SESSION[$GLOBALS['APP_ID']]['trx.wo']['error']) {
        echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['trx.wo']['error']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['trx.wo']['error']);
    }

    if ($_SESSION[$GLOBALS['APP_ID']]['trx.wo']['info']) {
        echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['trx.wo']['info']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['trx.wo']['info']);
    }


    $cgx_test = $cgx_datagrid->bind($cgx_sql, $cgx_options);
    if (PEAR::isError($cgx_test)) {
        echo $cgx_test->getMessage();
    }

    if ($cgx_def_columns['m_work_order_id'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('ID', 'm_work_order_id', 'm_work_order_id', array('align' => 'right'), NULL, NULL));
    if ($cgx_def_columns['order_date'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Tanggal', 'order_date', 'order_date', array('align' => 'center'), NULL, "cgx_format_date()"));
    if ($cgx_def_columns['document_no'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Nomor Dokumen', 'document_no', 'document_no', array('align' => 'left'), NULL, "grid_wo"));
    if ($cgx_def_columns['machine_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Mesin', 'machine_name', 'machine_name', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['so'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Remark / Forecast', 'so', 'so', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['partner_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Pelanggan', 'partner_name', 'partner_name', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['proces_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Next Proces', 'proces_name', 'proces_name', array('align' => 'left'), NULL, NULL));
    if (has_privilege('trx.wo')) $cgx_datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, 'cgx_edit()'));
    if (has_privilege('trx.wo') AND (user() == 2 OR user() == 46 OR user() == 51)) $cgx_datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, 'cgx_delete()'));
    
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