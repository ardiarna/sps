<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 03/01/2014 20:22:31
 */


echo "<div class='title'>Master Data Mitra Bisnis</div>";

function cgx_edit($data) {
    $href = "module.php?&m={$_REQUEST['m']}&pkey[c_bpartner_id]={$data['record']['c_bpartner_id']}";
    $out = "<a href='{$href}'><img title='Edit baris ini' src='images/icon_edit.png' border='0'></a>";
    return $out;
}

function cgx_delete($data) {
    $href  = "javascript:if(confirm('Menghapus baris ini?')){window.location='action/master.mitra.php";
    $href .= "?backvar=module.php%253F%2526m%253Dmaster.mitra&mode=delete&pkey[c_bpartner_id]={$data['record']['c_bpartner_id']}';}";
    $out = "<a href=\"{$href}\"><img title='Hapus baris ini' src='images/icon_delete.png' border='0'></a>";
    return $out;
}

if (strlen($_REQUEST['pkey']['c_bpartner_id']) > 0) {
    $cgx_id = $_REQUEST['id'];
    $cgx_data = cgx_fetch_table("SELECT c_bpartner.*, organization FROM c_bpartner JOIN app_org USING (app_org_id) WHERE c_bpartner.c_bpartner_id = '" . mysql_escape_string($_REQUEST['pkey']['c_bpartner_id']) . "'");


    echo "<form action='action/master.mitra.php' method='post'>\n";
    echo "<input type='hidden' name='backvar' value='" . urlencode("module.php?&m={$_REQUEST['m']}") . "'>\n";
    echo "<input type='hidden' name='mode' value='" . ($_REQUEST['pkey']['c_bpartner_id'] == '0' ? 'new' : 'update') . "'>\n";
    echo "<input type='hidden' name='pkey[c_bpartner_id]' value=\"{$_REQUEST['pkey']['c_bpartner_id']}\">\n";
    echo "<input type='hidden' name='table' value='c_bpartner'>\n";

    if ($_SESSION[$GLOBALS['APP_ID']]['master.mitra']['error']) {
        echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['master.mitra']['error']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['master.mitra']['error']);
    }

    if ($_SESSION[$GLOBALS['APP_ID']]['master.mitra']['info']) {
        echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['master.mitra']['info']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['master.mitra']['info']);
    }

    echo "<ul class='cgx_form'>\n";
    echo "    <input type='hidden' name='type[c_bpartner_id]' value='N'>";
    echo "    <li class='even'>\n";
    echo "        <label class='cgx_form' for='data_c_bpartner_id'>ID</label>\n";
    echo "        <input id='data_c_bpartner_id' name='data[c_bpartner_id]' type='text' value=\"{$cgx_data['c_bpartner_id']}\" size='8' maxlength='8' style='text-align: right;' disabled />\n";
    echo "    </li>\n";
    echo "    <input type='hidden' name='type[app_org_id]' value='N'>";
    echo "    <li class='odd'>\n";
    echo "        <label class='cgx_form' for='data_app_org_id'>Organization</label>\n";
    echo cgx_form_select('data[app_org_id]', "SELECT app_org_id, organization FROM app_org", $cgx_data['app_org_id'], FALSE, "id='data_app_org_id'");
    echo "    </li>\n";
    echo "    <input type='hidden' name='type[partner_code]' value='T'>";
    echo "    <li class='even'>\n";
    echo "        <label class='cgx_form' for='data_partner_code'>Kode Customer</label>\n";
    echo "        <input id='data_partner_code' name='data[partner_code]' type='text' value=\"{$cgx_data['partner_code']}\" size='10' maxlength='10' style='text-align: left;' />\n";
    echo "    </li>\n";
    echo "    <input type='hidden' name='type[partner_name]' value='T'>";
    echo "    <li class='odd'>\n";
    echo "        <label class='cgx_form' for='data_partner_name'>Nama Customer</label>\n";
    echo "        <input id='data_partner_name' name='data[partner_name]' type='text' value=\"{$cgx_data['partner_name']}\" size='50' maxlength='100' style='text-align: left;' />\n";
    echo "    </li>\n";
    echo "    <input type='hidden' name='type[active]' value='T'>";
    echo "    <li class='even'>\n";
    echo "        <label class='cgx_form' for='data_active'>Active</label>\n";
    echo cgx_form_select('data[active]', array('Y' => 'Ya', 'N' => 'Tidak'), $cgx_data['active'], FALSE, "id='data_active'");
    echo "    </li>\n";
    echo "    <li class='odd'>\n";
    echo "        <label class='cgx_form' >Status</label>\n";
    echo "        <input name='data[vendor]' type='checkbox' value='Y' ". ($cgx_data['vendor'] == 'Y' ? 'checked' : '' ) ."/> Vendor \n";
    echo "        <input name='data[customer]' type='checkbox' value='Y' ". ($cgx_data['customer'] == 'Y' ? 'checked' : '' ) ."/> Customer \n";
    echo "        <input name='data[employee]' type='checkbox' value='Y' ". ($cgx_data['employee'] == 'Y' ? 'checked' : '' ) ."/> Employee \n";
    echo "    </li>\n";
    echo "    <li class='even'>\n";
    echo "        <label class='cgx_form'></label>\n";
    echo "        <input type='submit' value='Simpan'>\n";
    echo "        <input type='button' value='Kembali' onclick=\"window.location = 'module.php?&m=master.mitra';\">\n";
    echo "    </li>\n";
    echo "</ul>\n";
    echo "</form>\n";

} else {
    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';

    if (is_array($_SESSION[$GLOBALS['APP_ID']]['master.mitra']['columns'])) {
        $cgx_def_columns = $_SESSION[$GLOBALS['APP_ID']]['master.mitra']['columns'];
    } else {
        $cgx_def_columns = array(
            'c_bpartner_id' => 1,
            'partner_code' => 1,
            'partner_name' => 1,
            'vendor' => 1,
            'customer' => 1,
            'employee' => 1,
        );
        $_SESSION[$GLOBALS['APP_ID']]['master.mitra']['columns'] = $cgx_def_columns;
    }

    $cgx_sql = "SELECT c_bpartner.*, organization FROM c_bpartner JOIN app_org USING (app_org_id) WHERE 1 = 1";
    $cgx_datagrid = new Structures_DataGrid($cgx_max_rows);
    $cgx_options = array('dsn' => $cgx_dsn);

    $cgx_filter1 = urldecode($_REQUEST['f1']);
    $cgx_filter2 = urldecode($_REQUEST['f2']);
    $cgx_filter3 = urldecode($_REQUEST['f3']);
    $cgx_search = $_REQUEST['q'];

    echo "<form name='frmFILTER' action='{$_SERVER['SCRIPT_NAME']}'>\n";
    echo "<input type='hidden' name='m' value='{$_REQUEST['m']}'>\n";
    echo "<table id='bar' class='datagrid_bar' width='100%'><tr>\n";
    echo "<td align='right'>Organization</td>\n";
    echo "<td>" . cgx_filter('f1', "SELECT app_org_id, organization FROM app_org", $cgx_filter1, TRUE) . "</td>\n";
    echo "<td align='right'>Status</td>\n";
    echo "<td>" . cgx_filter('f2', array('C' => 'Customer', 'V' => 'Vendor', 'E' => 'Employee'), $cgx_filter2, TRUE) . "</td>\n";
    echo "<td align='right'>Active</td>\n";
    echo "<td>" . cgx_filter('f3', array('Y' => 'Ya', 'N' => 'Tidak'), $cgx_filter3, TRUE) . "</td>\n";
    echo "<td align='right'><input type='text' size='20' name='q' value=\"{$cgx_search}\"></td>\n";
    echo "<td width='1'><input title='Cari' type='image' src='images/icon_search.png' border='0' style='padding-right: 20px;'></td>\n";
    echo "<td></td>\n";
    echo "<td width='20'></td>\n";
    if (has_privilege('master.mitra')) {
        echo "<td width='1' class='datagrid_bar_icon'><a title='Tambah data' href='module.php?&m={$_REQUEST['m']}&pkey[c_bpartner_id]=0'><img border='0' src='images/icon_add.png'></a></td>\n";
    } else {
        echo "<td width='1' class='datagrid_bar_icon'><img border='0' src='images/icon_add_dis.png'></td>\n";
    }
    echo "<td width='1' class='datagrid_bar_icon'><a title='Export semua data ke CSV' href='action/master.mitra.php?mode=export-all'><img border='0' src='images/icon_csv.png'></a></td>\n";
    echo "<td width='1' class='datagrid_bar_icon'><a title='Kustomisasi kolom' href='javascript:customizeColumn(true);'><img border='0' src='images/icon_columns.png'></a></td>\n";
    echo "</tr></table>\n";
    echo "</form>\n";

    echo "<form name='frmCUSTOMIZE' action='action/cgx_customize.php' method='post'>\n";
    echo "<input type='hidden' name='back' value='" . urlencode($_SERVER['REQUEST_URI']) . "'>\n";
    echo "<input type='hidden' name='dg_name' value='master.mitra'>\n";
    echo "<input type='hidden' name='col[partner_code]' value='on'>\n";
    echo "<input type='hidden' name='col[partner_name]' value='on'>\n";
    echo "<table id='columns' class='datagrid_bar' style='display: none;'><tr>\n";
    echo "<td width='99%' valign='top'>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['c_bpartner_id'] == 1 ? ' checked' : '') . " id='col_c_bpartner_id' name='col[c_bpartner_id]' type='checkbox'></td><td width='99%'><label for='col_c_bpartner_id'>ID</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['organization'] == 1 ? ' checked' : '') . " id='col_organization' name='col[organization]' type='checkbox'></td><td width='99%'><label for='col_organization'>Organization</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_partner_code' name='col[partner_code]' type='checkbox'></td><td width='99%'><label for='col_partner_code'>Kode</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_partner_name' name='col[partner_name]' type='checkbox'></td><td width='99%'><label for='col_partner_name'>Nama Mitra Bisnis</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['vendor'] == 1 ? ' checked' : '') . " id='col_vendor' name='col[vendor]' type='checkbox'></td><td width='99%'><label for='col_vendor'>Vendor</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['customer'] == 1 ? ' checked' : '') . " id='col_customer' name='col[customer]' type='checkbox'></td><td width='99%'><label for='col_customer'>Customer</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['employee'] == 1 ? ' checked' : '') . " id='col_employee' name='col[employee]' type='checkbox'></td><td width='99%'><label for='col_employee'>Employee</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['active'] == 1 ? ' checked' : '') . " id='col_active' name='col[active]' type='checkbox'></td><td width='99%'><label for='col_active'>Active</label></td></tr></table>\n";
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

    if (strlen($cgx_filter1) > 0) $cgx_sql .= " AND c_bpartner.app_org_id = '" . mysql_escape_string($cgx_filter1) . "'";
    if (strlen($cgx_filter2) > 0) {
        switch ($cgx_filter2) {
            case 'V':
                $cgx_sql .= " AND c_bpartner.vendor ='Y'";            
                break;
            case 'C':
                $cgx_sql .= " AND c_bpartner.customer ='Y'";            
                break;
            case 'E':
                $cgx_sql .= " AND c_bpartner.employee ='Y'";            
                break;
        }
    } 
    if (strlen($cgx_filter3) > 0) $cgx_sql .= " AND c_bpartner.active = '" . mysql_escape_string($cgx_filter3) . "'";
    $cgx_sql .= " and ( c_bpartner.partner_code LIKE '%{$cgx_search}%' OR c_bpartner.partner_name LIKE '%{$cgx_search}%')";
    
    if ($_SESSION[$GLOBALS['APP_ID']]['master.mitra']['error']) {
        echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['master.mitra']['error']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['master.mitra']['error']);
    }

    if ($_SESSION[$GLOBALS['APP_ID']]['master.mitra']['info']) {
        echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['master.mitra']['info']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['master.mitra']['info']);
    }


    $cgx_test = $cgx_datagrid->bind($cgx_sql, $cgx_options);
    if (PEAR::isError($cgx_test)) {
        echo $cgx_test->getMessage();
    }

    if ($cgx_def_columns['c_bpartner_id'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('ID', 'c_bpartner_id', 'c_bpartner_id', array('align' => 'right'), NULL, NULL));
    if ($cgx_def_columns['organization'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Organization', 'organization', 'organization', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['partner_code'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Kode', 'partner_code', 'partner_code', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['partner_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Nama Mitra Bisnis', 'partner_name', 'partner_name', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['vendor'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Vendor', 'vendor', 'vendor', array('align' => 'center'), NULL, "cgx_format_yesno()"));
    if ($cgx_def_columns['customer'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Customer', 'customer', 'customer', array('align' => 'center'), NULL, "cgx_format_yesno()"));
    if ($cgx_def_columns['employee'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Employee', 'employee', 'employee', array('align' => 'center'), NULL, "cgx_format_yesno()"));
    if ($cgx_def_columns['active'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Active', 'active', 'active', array('align' => 'center'), NULL, "cgx_format_yesno()"));
    if (has_privilege('master.mitra')) $cgx_datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, 'cgx_edit()'));
    if (has_privilege('master.mitra')) $cgx_datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, 'cgx_delete()'));

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