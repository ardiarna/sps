<?php

/**
 * (c) Azwari Nugraha <nugraha@pt-gai.com>
 * 23/11/2013 12:40:39
 */


echo "<div class='title'>Master Data Mesin</div>";

function cgx_edit($data) {
    if ($data['record']['active'] == 'C') return;
    $href = "module.php?m={$_REQUEST['m']}&pkey[m_machine_id]={$data['record']['m_machine_id']}";
    $out = "<a href='{$href}'><img title='Edit' src='images/icon_edit.png' border='0'></a>";
    return $out;
}

function cgx_delete($data) {
    if ($data['record']['active'] == 'C') return;
    $href  = "javascript:if(confirm('Hapus baris ini?')){window.location='action/master.machine.php";
    $href .= "?backvar=module.php%253F%2526m%253Dmaster.machine&mode=delete&pkey[m_machine_id]={$data['record']['m_machine_id']}';}";
    $out = "<a href=\"{$href}\"><img title='Delete this row' src='images/icon_delete.png' border='0'></a>";
    return $out;
}

if (strlen($_REQUEST['pkey']['m_machine_id']) > 0) {
    include_once 'master.machine.edit.php';
} else {

    //$_REQUEST['f1'] = empty($_REQUEST['f1']) ? org() : $_REQUEST['f1'];
    
    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';

    if (is_array($_SESSION[$GLOBALS['APP_ID']]['master.machine']['columns'])) {
        $cgx_def_columns = $_SESSION[$GLOBALS['APP_ID']]['master.machine']['columns'];
    } else {
        $cgx_def_columns = array(
            'machine_code' => 1,
            'machine_name' => 1,
            'resultperday' => 1,
            'active' => 1,
        );
        $_SESSION[$GLOBALS['APP_ID']]['master.machine']['columns'] = $cgx_def_columns;
    }
    
    //$_REQUEST['active'] = empty($_REQUEST['active']) ? 'O' : $_REQUEST['active'];

    $cgx_sql = "SELECT m_machine.*, organization FROM m_machine JOIN app_org USING (app_org_id) WHERE 1 = 1";
    $cgx_datagrid = new Structures_DataGrid($cgx_max_rows);
    $cgx_options = array('dsn' => $cgx_dsn);
    $cgx_search = $_REQUEST['q'];
    
    $cgx_filter1 = urldecode($_REQUEST['f1']);
    $cgx_filter2 = urldecode($_REQUEST['f2']);

    echo "<form name='frmFILTER' action='{$_SERVER['SCRIPT_NAME']}'>\n";
    echo "<input type='hidden' name='m' value='{$_REQUEST['m']}'>\n";
    echo "<table id='bar' class='datagrid_bar' width='100%' border='0'><tr>\n";
    echo "<td align='right'><label for='f1'>Organization</label></td>\n";
    echo "<td>" . cgx_filter('f1', "SELECT app_org_id, organization FROM app_org", $cgx_filter1, FALSE) . "</td>\n";
    echo "<td width='20'></td>\n";
    echo "<td align='right'><label for='f2'>Aktif</label></td>\n";
    echo "<td>" . cgx_filter('f2', array('Y' => 'Ya', 'N' => 'Tidak'), $cgx_filter2, TRUE) . "</td>\n"; 
    echo "<td align='right'><input type='text' size='20' name='q' value=\"{$cgx_search}\"></td>\n";
    echo "<td width='1'><input title='Cari' type='image' src='images/icon_search.png' border='0' style='padding-right: 20px;'></td>\n";
    echo "<td></td>\n";
    echo "<td width='20'></td>\n";
    if (has_privilege('master.machine')) {
        echo "<td width='1' class='datagrid_bar_icon'><a title='Tambah Data' href='module.php?m={$_REQUEST['m']}&pkey[m_machine_id]=0&mode=edit'><img border='0' src='images/icon_add.png'></a></td>\n";
    } else {
        echo "<td width='1' class='datagrid_bar_icon'><img border='0' src='images/icon_add_dis.png'></td>\n";
    }
    echo "<td width='1' class='datagrid_bar_icon'><a title='Export semua data ke CSV' href='action/master.machine.php?mode=export-all'><img border='0' src='images/icon_csv.png'></a></td>\n";
    echo "<td width='1' class='datagrid_bar_icon'><a title='Customize columns' href='javascript:customizeColumn(true);'><img border='0' src='images/icon_columns.png'></a></td>\n";
    echo "</tr></table>\n";
    echo "</form>\n";

    echo "<form name='frmCUSTOMIZE' action='action/cgx_customize.php' method='post'>\n";
    echo "<input type='hidden' name='back' value='" . urlencode($_SERVER['REQUEST_URI']) . "'>\n";
    echo "<input type='hidden' name='dg_name' value='master.machine'>\n";
    echo "<input type='hidden' name='col[m_machine_id]' value='on'>\n";
    echo "<input type='hidden' name='col[machine_code]' value='on'>\n";
    echo "<table id='columns' class='datagrid_bar' style='display: none;'><tr>\n";
    echo "<td width='99%' valign='top'>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_m_machine_id' name='col[m_machine_id]' type='checkbox'></td><td width='99%'><label for='col_m_machine_id'>ID</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['organization'] == 1 ? ' checked' : '') . " id='col_organization' name='col[organization]' type='checkbox'></td><td width='99%'><label for='col_organization'>Organization</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_machine_code' name='col[machine_code]' type='checkbox'></td><td width='99%'><label for='col_machine_code'>Kode Mesin</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['machine_name'] == 1 ? ' checked' : '') . " id='col_machine_name' name='col[machine_name]' type='checkbox'></td><td width='99%'><label for='col_machine_name'>Nama Mesin</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['resultperday'] == 1 ? ' checked' : '') . " id='col_resultperday' name='col[resultperday]' type='checkbox'></td><td width='99%'><label for='col_resultperday'>Hasil Produksi Perhari (Pcs)</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['active'] == 1 ? ' checked' : '') . " id='col_active' name='col[active]' type='checkbox'></td><td width='99%'><label for='col_active'>Aktif</label></td></tr></table>\n";
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
    
    if (strlen($cgx_filter1) > 0) $cgx_sql .= " AND m_machine.app_org_id = '" . mysql_escape_string($cgx_filter1) . "'";
    if (strlen($cgx_filter2) > 0) $cgx_sql .= " AND m_machine.active = '" . mysql_escape_string($cgx_filter2) . "'";
    $cgx_sql .= " and ( m_machine.machine_code LIKE '%{$cgx_search}%' OR m_machine.machine_name LIKE '%{$cgx_search}%')";
    if ($_SESSION[$GLOBALS['APP_ID']]['master.machine']['error']) {
        echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['master.machine']['error']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['master.machine']['error']);
    }

    if ($_SESSION[$GLOBALS['APP_ID']]['master.machine']['info']) {
        echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['master.machine']['info']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['master.machine']['info']);
    }


    $cgx_test = $cgx_datagrid->bind($cgx_sql, $cgx_options);
    if (PEAR::isError($cgx_test)) {
        echo $cgx_test->getMessage();
    }

    if ($cgx_def_columns['m_machine_id'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('ID', 'm_machine_id', 'm_machine_id', array('align' => 'right'), NULL, NULL));
    if ($cgx_def_columns['organization'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Organization', 'organization', 'organization', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['machine_code'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Kode Mesin', 'machine_code', 'machine_code', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['machine_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Nama Mesin', 'machine_name', 'machine_name', array('align' => 'left'), NULL, NULL));
    //if ($cgx_def_columns['resultperday'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Hasil Produksi Per Hari (Pcs)', 'resultperday', 'resultperday', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['active'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Aktif', 'active', 'active', array('align' => 'center'), NULL, "cgx_format_yesno()"));
    //if (trx) $cgx_datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, 'cgx_edit()'));
    //if (trx) $cgx_datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, 'cgx_delete()'));
    if (has_privilege('master.wh')) $cgx_datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, 'cgx_edit()'));
    if (has_privilege('master.wh')) $cgx_datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, 'cgx_delete()'));

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