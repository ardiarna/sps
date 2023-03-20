<?php

/**
 * (c) Azwari Nugraha <nugraha@pt-gai.com>
 * 23/11/2013 12:40:39
 */


echo "<div class='title'>Sales Order Q</div>";

function cgx_edit($data) {
    $href = "module.php?m={$_REQUEST['m']}&pkey[c_order_id]={$data['record']['c_order_id']}";
    $out = "<a href='{$href}'><img title='Edit' src='images/icon_edit.png' border='0'></a>";
    return $out;
}

function cgx_delete($data) {
    $href  = "javascript:if(confirm('Hapus baris ini?')){window.location='action/trx.so1.php";
    $href .= "?backvar=module.php%253F%2526m%253Dtrx.so1&mode=delete&pkey[c_order_id]={$data['record']['c_order_id']}';}";
    $out = "<a href=\"{$href}\"><img title='Delete this row' src='images/icon_delete.png' border='0'></a>";
    return $out;
}

function grid_so($data) {
    $href = "module.php?m={$_REQUEST['m']}&pkey[c_order_id]={$data['record']['c_order_id']}";
    $out = "<a href='{$href}'>{$data['record']['document_no']}</a>";
    return $out;
}

function grid_status1($data) {
    return $GLOBALS['SO_STATUS'][$data['record']['status']];
}

if (strlen($_REQUEST['pkey']['c_order_id']) > 0) {
    include_once 'trx.so1.edit.php';
} else {
    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';

    if (is_array($_SESSION[$GLOBALS['APP_ID']]['trx.so1']['columns'])) {
        $cgx_def_columns = $_SESSION[$GLOBALS['APP_ID']]['trx.so1']['columns'];
    } else {
        $cgx_def_columns = array(
            'c_order_id' => 1,
            'document_no' => 1,
            'partner_name' => 1,
            'order_date' => 1,
            'reference_no' => 1,
            'remark' => 1,
            'status' => 1
        );
        $_SESSION[$GLOBALS['APP_ID']]['trx.so1']['columns'] = $cgx_def_columns;
    }
    
    $_REQUEST['status'] = empty($_REQUEST['status']) ? 'O' : $_REQUEST['status'];

    $cgx_sql = "SELECT c_order_id, document_no, partner_name, order_date, reference_no, remark, status, c_order.app_org_id as org_id, organization
                FROM c_order
                JOIN c_bpartner USING (c_bpartner_id) 
                JOIN app_org ON(c_order.app_org_id=app_org.app_org_id) WHERE 1 = 1";
    $cgx_sql .= " AND " . org_filter_trx('c_order.app_org_id');

    $cgx_datagrid = new Structures_DataGrid($cgx_max_rows);
    $cgx_options = array('dsn' => $cgx_dsn);
    $cgx_datagrid->setDefaultSort(array('order_date' => 'DESC'));
    $cgx_search = $_REQUEST['q'];
    
    // $cgx_filter1 = urldecode($_REQUEST['f1']);

    if ($_REQUEST['status'] != 'A') $cgx_sql .= " AND status = '{$_REQUEST['status']}'";

    echo "<form name='frmFILTER' action='{$_SERVER['SCRIPT_NAME']}'>\n";
    echo "<input type='hidden' name='m' value='{$_REQUEST['m']}'>\n";
    echo "<table id='bar' class='datagrid_bar' width='100%' border='0'><tr>\n";
    echo "<td><input type='button' value='Dokumen Baru' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}&pkey[c_order_id]=0&mode=edit';\"></td>";
    // echo "<td align='right'><label for='f1'>Organization</label></td>\n";
    // echo "<td>" . cgx_filter('f1', "SELECT app_org_id, organization FROM app_org", $cgx_filter1, FALSE) . "</td>\n";
    echo "<td width='20'></td>\n";
    $arr_status['A'] = '(Semua)';
    foreach ($SO_STATUS as $k => $v) $arr_status[$k] = $v;
    echo "<td align='right'>Status</td>";
    echo "<td>" . cgx_filter('status', $arr_status, $_REQUEST['status'], FALSE, "onclick=\"window.document = 'module.php?m=trx.so1&q={$_REQUEST['q']}&status={$_REQUEST['status']}';\"") . "</td>";
    
    echo "<td align='right'><input type='text' size='20' name='q' value=\"{$cgx_search}\"></td>\n";
    echo "<td width='1'><input title='Cari' type='image' src='images/icon_search.png' border='0' style='padding-right: 20px;'></td>\n";
    echo "<td></td>\n";
    echo "<td width='20'></td>\n";
    if (has_privilege('trx.so1')) {
        echo "<td width='1' class='datagrid_bar_icon'><a title='Dokumen Baru' href='module.php?m={$_REQUEST['m']}&pkey[c_order_id]=0&mode=edit'><img border='0' src='images/icon_add.png'></a></td>\n";
    } else {
        echo "<td width='1' class='datagrid_bar_icon'><img border='0' src='images/icon_add_dis.png'></td>\n";
    }
    echo "<td width='1' class='datagrid_bar_icon'><a title='Customize columns' href='javascript:customizeColumn(true);'><img border='0' src='images/icon_columns.png'></a></td>\n";
    echo "</tr></table>\n";
    echo "</form>\n";

    echo "<form name='frmCUSTOMIZE' action='action/cgx_customize.php' method='post'>\n";
    echo "<input type='hidden' name='back' value='" . urlencode($_SERVER['REQUEST_URI']) . "'>\n";
    echo "<input type='hidden' name='dg_name' value='trx.so1'>\n";
    echo "<input type='hidden' name='col[c_order_id]' value='on'>\n";
    echo "<input type='hidden' name='col[document_no]' value='on'>\n";
    echo "<table id='columns' class='datagrid_bar' style='display: none;'><tr>\n";
    echo "<td width='99%' valign='top'>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_c_order_id' name='col[c_order_id]' type='checkbox'></td><td width='99%'><label for='col_c_order_id'>ID</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_document_no' name='col[document_no]' type='checkbox'></td><td width='99%'><label for='col_document_no'>Nomor Dokumen</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['partner_name'] == 1 ? ' checked' : '') . " id='col_partner_name' name='col[partner_name]' type='checkbox'></td><td width='99%'><label for='col_partner_name'>Customer</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['order_date'] == 1 ? ' checked' : '') . " id='col_order_date' name='col[order_date]' type='checkbox'></td><td width='99%'><label for='col_order_date'>Tgl Order</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['reference_no'] == 1 ? ' checked' : '') . " id='col_reference_no' name='col[reference_no]' type='checkbox'></td><td width='99%'><label for='col_reference_no'>Reference</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['remark'] == 1 ? ' checked' : '') . " id='col_remark' name='col[remark]' type='checkbox'></td><td width='99%'><label for='col_remark'>Remark</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['status'] == 1 ? ' checked' : '') . " id='col_status' name='col[status]' type='checkbox'></td><td width='99%'><label for='col_status'>Status</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['organization'] == 1 ? ' checked' : '') . " id='col_organization' name='col[organization]' type='checkbox'></td><td width='99%'><label for='col_organization'>Organization</label></td></tr></table>\n";
    echo "<td width='1' valign='top'><input type='submit' value='Update'></td>\n";
    echo "<td width='1' valign='top'><input type='button' value='Cancel' onclick='customizeColumn(false);'></td>\n";
    echo "</tr></table>\n";
    echo "</form>\n";
?>
<script type="text/javascript">
<!--
function customizeColumn(s) {
    var divCols = document.getElementById('columns');
    var divBar = document.getElementById('bar');
    if (s) {
        divCols.style.display = 'block';
        divBar.style.display = 'none';
    } else {
        window.location = window.location;
    }
}
//-->
</script>
<?php
    
    // if (strlen($cgx_filter1) > 0) $cgx_sql .= " AND c_order.app_org_id = '" . mysql_escape_string($cgx_filter1) . "'";
    $cgx_sql .= " and ( c_order.c_order_id LIKE '%{$cgx_search}%' OR c_order.document_no LIKE '%{$cgx_search}%' OR c_bpartner.partner_name LIKE '%{$cgx_search}%' OR c_order.reference_no LIKE '%{$cgx_search}%' OR c_order.remark LIKE '%{$cgx_search}%')";
    if ($_SESSION[$GLOBALS['APP_ID']]['trx.so1']['error']) {
        echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['trx.so1']['error']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['trx.so1']['error']);
    }

    if ($_SESSION[$GLOBALS['APP_ID']]['trx.so1']['info']) {
        echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['trx.so1']['info']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['trx.so1']['info']);
    }


    $cgx_test = $cgx_datagrid->bind($cgx_sql, $cgx_options);
    if (PEAR::isError($cgx_test)) {
        echo $cgx_test->getMessage();
    }

    if ($cgx_def_columns['c_order_id'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('ID', 'c_order_id', 'c_order_id', array('align' => 'right'), NULL, NULL));
    if ($cgx_def_columns['document_no'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Nomor Dokumen', 'document_no', 'document_no', array('align' => 'left'), NULL, "grid_so"));
    if ($cgx_def_columns['partner_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Customer', 'partner_name', 'partner_name', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['order_date'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Tgl Order', 'order_date', 'order_date', array('align' => 'center'), NULL, "cgx_format_date()"));
    if ($cgx_def_columns['reference_no'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('ReferenceNo', 'reference_no', 'reference_no', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['remark'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Remark', 'remark', 'remark', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['status'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Status', 'status', 'status', array('align' => 'left'), NULL, "grid_status1"));
    if ($cgx_def_columns['organization'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Organization', 'organization', 'organization', array('align' => 'left'), NULL, NULL));
    if (trx) $cgx_datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, 'cgx_edit()'));
    if (trx) $cgx_datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, 'cgx_delete()'));

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