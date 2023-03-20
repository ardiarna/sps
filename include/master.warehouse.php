<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 07/01/2014 13:47:15
 */


echo "<div class='title'>Master Data Gudang</div>";

function cgx_edit($data) {
    $href = "module.php?&m={$_REQUEST['m']}&pkey[m_warehouse_id]={$data['record']['m_warehouse_id']}";
    $out = "<a href='{$href}'><img title='Edit baris ini' src='images/icon_edit.png' border='0'></a>";
    return $out;
}

function cgx_delete($data) {
    $href  = "javascript:if(confirm('Menghapus baris ini?')){window.location='action/master.warehouse.php";
    $href .= "?backvar=module.php%253F%2526m%253Dmaster.warehouse&mode=delete&pkey[m_warehouse_id]={$data['record']['m_warehouse_id']}';}";
    $out = "<a href=\"{$href}\"><img title='Hapus baris ini' src='images/icon_delete.png' border='0'></a>";
    return $out;
}

if (strlen($_REQUEST['pkey']['m_warehouse_id']) > 0) {
    $cgx_id = $_REQUEST['id'];
    $cgx_data = cgx_fetch_table("SELECT m_warehouse.*, organization FROM m_warehouse JOIN app_org USING (app_org_id) WHERE m_warehouse.m_warehouse_id = '" . mysql_escape_string($_REQUEST['pkey']['m_warehouse_id']) . "'");
    $cgx_data['app_org_id'] = empty($cgx_data['app_org_id']) ? org() : $cgx_data['app_org_id'];

    echo "<form action='action/master.warehouse.php' method='post'>\n";
    echo "<input type='hidden' name='backvar' value='" . urlencode("module.php?&m={$_REQUEST['m']}") . "'>\n";
    echo "<input type='hidden' name='mode' value='" . ($_REQUEST['pkey']['m_warehouse_id'] == '0' ? 'new' : 'update') . "'>\n";
    echo "<input type='hidden' name='pkey[m_warehouse_id]' value=\"{$_REQUEST['pkey']['m_warehouse_id']}\">\n";
    echo "<input type='hidden' name='table' value='m_warehouse'>\n";

    if ($_SESSION[$GLOBALS['APP_ID']]['master.warehouse']['error']) {
        echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['master.warehouse']['error']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['master.warehouse']['error']);
    }

    if ($_SESSION[$GLOBALS['APP_ID']]['master.warehouse']['info']) {
        echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['master.warehouse']['info']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['master.warehouse']['info']);
    }

    echo "<ul class='cgx_form'>\n";
    echo "    <input type='hidden' name='type[app_org_id]' value='N'>";
    echo "    <li class='even'>\n";
    echo "        <label class='cgx_form' for='data_app_org_id'>Organization</label>\n";
    echo cgx_form_select('data[app_org_id]', "SELECT app_org_id, organization FROM app_org", $cgx_data['app_org_id'], FALSE, "id='data_app_org_id'");
    echo "    </li>\n";
    echo "    <input type='hidden' name='type[warehouse_code]' value='T'>";
    echo "    <li class='odd'>\n";
    echo "        <label class='cgx_form' for='data_warehouse_code'>Kode Gudang</label>\n";
    echo "        <input id='data_warehouse_code' name='data[warehouse_code]' type='text' value=\"{$cgx_data['warehouse_code']}\" size='10' maxlength='10' style='text-align: left;' />\n";
    echo "    </li>\n";
    echo "    <input type='hidden' name='type[warehouse_name]' value='T'>";
    echo "    <li class='even'>\n";
    echo "        <label class='cgx_form' for='data_warehouse_name'>Nama Gudang</label>\n";
    echo "        <input id='data_warehouse_name' name='data[warehouse_name]' type='text' value=\"{$cgx_data['warehouse_name']}\" size='50' maxlength='100' style='text-align: left;' />\n";
    echo "    </li>\n";
    echo "    <input type='hidden' name='type[active]' value='T'>";
    echo "    <li class='odd'>\n";
    echo "        <label class='cgx_form' for='data_active'>Aktif</label>\n";
    echo cgx_form_select('data[active]', array('Y' => 'Ya', 'N' => 'Tidak'), $cgx_data['active'], FALSE, "id='data_active'");
    echo "    </li>\n";
    echo "    <li class='even'>\n";
    echo "        <label class='cgx_form'></label>\n";
    echo "        <input type='submit' value='Simpan'>\n";
    echo "        <input type='button' value='Kembali' onclick=\"window.location = 'module.php?&m=master.warehouse';\">\n";
    echo "    </li>\n";
    echo "</ul>\n";
    echo "</form>\n";

} else {
    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';

    $_REQUEST['f1'] = empty($_REQUEST['f1']) ? org() : $_REQUEST['f1'];
    
    if (is_array($_SESSION[$GLOBALS['APP_ID']]['master.warehouse']['columns'])) {
        $cgx_def_columns = $_SESSION[$GLOBALS['APP_ID']]['master.warehouse']['columns'];
    } else {
        $cgx_def_columns = array(
            'warehouse_code' => 1,
            'warehouse_name' => 1,
            'active' => 1,
        );
        $_SESSION[$GLOBALS['APP_ID']]['master.warehouse']['columns'] = $cgx_def_columns;
    }

    $cgx_sql = "SELECT m_warehouse.*, organization FROM m_warehouse JOIN app_org USING (app_org_id) WHERE 1 = 1";
    $cgx_datagrid = new Structures_DataGrid($cgx_max_rows);
    $cgx_options = array('dsn' => $cgx_dsn);

    $cgx_filter1 = urldecode($_REQUEST['f1']);
    $cgx_filter2 = urldecode($_REQUEST['f2']);
    $cgx_search = $_REQUEST['q'];

    echo "<form name='frmFILTER' action='{$_SERVER['SCRIPT_NAME']}'>\n";
    echo "<input type='hidden' name='m' value='{$_REQUEST['m']}'>\n";
    echo "<table id='bar' class='datagrid_bar' width='100%'><tr>\n";
    echo "<td>\n";
    echo "<table align='left' cellspacing='0' cellpadding='0' border='0'><tr>\n";
    echo "<td><nobr><label for='f1'>Organization</label></nobr></td>\n";
    echo "<td>&nbsp;</td>\n";
    echo "<td>" . cgx_filter('f1', "SELECT app_org_id, organization FROM app_org", $cgx_filter1, TRUE) . "</td>\n";
    echo "<td width='20'></td>\n";
    echo "</tr></table>\n";
    echo "<table align='left' cellspacing='0' cellpadding='0' border='0'><tr>\n";
    echo "<td><nobr><label for='f2'>Aktif</label></nobr></td>\n";
    echo "<td>&nbsp;</td>\n";
    echo "<td>" . cgx_filter('f2', array('Y' => 'Ya', 'N' => 'Tidak'), $cgx_filter2, TRUE) . "</td>\n";
    echo "<td width='20'></td>\n";
    echo "</tr></table>\n";
    echo "</td>\n";
    echo "<td align='right'><input type='text' size='20' name='q' value=\"{$cgx_search}\"></td>\n";
    echo "<td width='1'><input title='Cari' type='image' src='images/icon_search.png' border='0' style='padding-right: 20px;'></td>\n";
    echo "<td></td>\n";
    echo "<td width='20'></td>\n";
    if (has_privilege('master.wh')) {
        echo "<td width='1' class='datagrid_bar_icon'><a title='Tambah data' href='module.php?&m={$_REQUEST['m']}&pkey[m_warehouse_id]=0'><img border='0' src='images/icon_add.png'></a></td>\n";
    } else {
        echo "<td width='1' class='datagrid_bar_icon'><img border='0' src='images/icon_add_dis.png'></td>\n";
    }
    echo "<td width='1' class='datagrid_bar_icon'><a title='Export semua data ke CSV' href='action/master.warehouse.php?mode=export-all'><img border='0' src='images/icon_csv.png'></a></td>\n";
    echo "<td width='1' class='datagrid_bar_icon'><a title='Kustomisasi kolom' href='javascript:customizeColumn(true);'><img border='0' src='images/icon_columns.png'></a></td>\n";
    echo "</tr></table>\n";
    echo "</form>\n";

    echo "<form name='frmCUSTOMIZE' action='action/cgx_customize.php' method='post'>\n";
    echo "<input type='hidden' name='back' value='" . urlencode($_SERVER['REQUEST_URI']) . "'>\n";
    echo "<input type='hidden' name='dg_name' value='master.warehouse'>\n";
    echo "<table id='columns' class='datagrid_bar' style='display: none;'><tr>\n";
    echo "<td width='99%' valign='top'>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['m_warehouse_id'] == 1 ? ' checked' : '') . " id='col_m_warehouse_id' name='col[m_warehouse_id]' type='checkbox'></td><td width='99%'><label for='col_m_warehouse_id'>ID</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['organization'] == 1 ? ' checked' : '') . " id='col_organization' name='col[organization]' type='checkbox'></td><td width='99%'><label for='col_organization'>Organization</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['warehouse_code'] == 1 ? ' checked' : '') . " id='col_warehouse_code' name='col[warehouse_code]' type='checkbox'></td><td width='99%'><label for='col_warehouse_code'>Kode Gudang</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['warehouse_name'] == 1 ? ' checked' : '') . " id='col_warehouse_name' name='col[warehouse_name]' type='checkbox'></td><td width='99%'><label for='col_warehouse_name'>Nama Gudang</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['active'] == 1 ? ' checked' : '') . " id='col_active' name='col[active]' type='checkbox'></td><td width='99%'><label for='col_active'>Aktif</label></td></tr></table>\n";
    echo "</td>\n";
    echo "<td width='1' valign='top'><input type='submit' value='Simpan'></td>\n";
    echo "<td width='1' valign='top'><input type='button' value='Batalkan' onclick='customizeColumn(false);'></td>\n";
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

    if (strlen($cgx_filter1) > 0) $cgx_sql .= " AND m_warehouse.app_org_id = '" . mysql_escape_string($cgx_filter1) . "'";
    if (strlen($cgx_filter2) > 0) $cgx_sql .= " AND m_warehouse.active = '" . mysql_escape_string($cgx_filter2) . "'";
    $cgx_sql .= " and ( m_warehouse.warehouse_code LIKE '%{$cgx_search}%' OR m_warehouse.warehouse_name LIKE '%{$cgx_search}%')";
    if ($_SESSION[$GLOBALS['APP_ID']]['master.warehouse']['error']) {
        echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['master.warehouse']['error']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['master.warehouse']['error']);
    }

    if ($_SESSION[$GLOBALS['APP_ID']]['master.warehouse']['info']) {
        echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['master.warehouse']['info']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['master.warehouse']['info']);
    }


    $cgx_test = $cgx_datagrid->bind($cgx_sql, $cgx_options);
    if (PEAR::isError($cgx_test)) {
        echo $cgx_test->getMessage();
    }

    if ($cgx_def_columns['m_warehouse_id'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('ID', 'm_warehouse_id', 'm_warehouse_id', array('align' => 'right'), NULL, NULL));
    if ($cgx_def_columns['organization'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Organization', 'organization', 'organization', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['warehouse_code'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Kode Gudang', 'warehouse_code', 'warehouse_code', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['warehouse_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Nama Gudang', 'warehouse_name', 'warehouse_name', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['active'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Aktif', 'active', 'active', array('align' => 'center'), NULL, "cgx_format_yesno()"));
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