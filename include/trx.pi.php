<?php

/**
 * (c) Azwari Nugraha <nugraha@pt-gai.com>
 * 25/11/2013 00:52:21
 */

echo "<div class='title'>Physical Inventory</div>";

function cgx_edit($data) {
    $href = "module.php?&m={$_REQUEST['m']}&pkey[m_inout_id]={$data['record']['m_inout_id']}";
    $out = "<a href='{$href}'><img title='Edit' src='images/icon_edit.png' border='0'></a>";
    return $out;
}

if (strlen($_REQUEST['pkey']['m_inout_id']) > 0) {
    include_once 'trx.pi.edit.php';
} else {
    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';

    $cgx_sql = "SELECT * FROM m_inout JOIN c_pinventory USING(m_inout_id) WHERE m_inout.m_transaction_type_id = 10 ";
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
    if (has_privilege('trx.pi')) {
        echo "<td width='1' class='datagrid_bar_icon'><a title='New record' href='module.php?&m={$_REQUEST['m']}&pkey[m_inout_id]=0&mode=edit'><img border='0' src='images/icon_add.png'></a></td>\n";
    } else {
        echo "<td width='1' class='datagrid_bar_icon'><img border='0' src='images/icon_add_dis.png'></td>\n";
    }
    echo "</tr></table>\n";
    echo "</form>\n";
    $cgx_sql .= " and ( m_inout.m_inout_id LIKE '%{$cgx_search}%' OR m_inout.document_no LIKE '%{$cgx_search}%')";
    if ($_SESSION[$GLOBALS['APP_ID']]['trx.pi']['error']) {
        echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['trx.pi']['error']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['trx.pi']['error']);
    }

    if ($_SESSION[$GLOBALS['APP_ID']]['trx.pi']['info']) {
        echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['trx.pi']['info']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['trx.pi']['info']);
    }


    $cgx_test = $cgx_datagrid->bind($cgx_sql, $cgx_options);
    if (PEAR::isError($cgx_test)) {
        echo $cgx_test->getMessage();
    }

    $cgx_datagrid->addColumn(new Structures_DataGrid_Column('ID', 'm_inout_id', 'm_inout_id', array('align' => 'right'), NULL, NULL));
    $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Tanggal', 'm_inout_date', 'm_inout_date', array('align' => 'center'), NULL, "cgx_format_date()"));
    $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Nomor Dokumen', 'document_no', 'document_no', array('align' => 'left'), NULL, NULL));
    if (has_privilege('trx.pi')) $cgx_datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, 'cgx_edit()'));

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