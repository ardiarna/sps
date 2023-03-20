<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 26/12/2013 17:13:12
 */


echo "<div class='title'>Role</div>";

function cgx_edit($data) {
    if ($data['record']['app_role_id'] == 0) return;
    $href = "module.php?&m={$_REQUEST['m']}&pkey[app_role_id]={$data['record']['app_role_id']}";
    $out = "<a href='{$href}'><img title='Edit baris ini' src='images/icon_edit.png' border='0'></a>";
    return $out;
}

function cgx_delete($data) {
    if ($data['record']['app_role_id'] == 0) return;
    $href  = "javascript:if(confirm('Menghapus baris ini?')){window.location='action/role.php";
    $href .= "?backvar=module.php%253F%2526m%253Drole&mode=delete&pkey[app_role_id]={$data['record']['app_role_id']}';}";
    $out = "<a href=\"{$href}\"><img title='Hapus baris ini' src='images/icon_delete.png' border='0'></a>";
    return $out;
}

if (strlen($_REQUEST['pkey']['app_role_id']) > 0) {
    $cgx_id = $_REQUEST['id'];
    $cgx_data = cgx_fetch_table("SELECT app_role_id, app_org_id, role, organization, app_role.active FROM app_role JOIN app_org USING (app_org_id) WHERE app_role.app_role_id = '" . mysql_escape_string($_REQUEST['pkey']['app_role_id']) . "'");

    if ($_SESSION[$GLOBALS['APP_ID']]['role']['error']) {
        echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['role']['error']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['role']['error']);
    }

    if ($_SESSION[$GLOBALS['APP_ID']]['role']['info']) {
        echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['role']['info']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['role']['info']);
    }

    echo "<form action='action/role.php' method='post'>\n";
    echo "<div class='data_box'>";
    echo "<input type='hidden' name='backvar' value='" . urlencode("module.php?&m={$_REQUEST['m']}") . "'>\n";
    echo "<input type='hidden' name='mode' value='" . ($_REQUEST['pkey']['app_role_id'] == '0' ? 'new' : 'update') . "'>\n";
    echo "<input type='hidden' name='pkey[app_role_id]' value=\"{$_REQUEST['pkey']['app_role_id']}\">\n";
    echo "<input type='hidden' name='table' value='app_role'>\n";
    
    echo "<table border='0' width='100%'>";
    echo "<tr>";
    echo "<td width='15%'><label for='data_app_role_id'>Role ID</label></td>";
    echo "<td width='30%'><input id='data_app_role_id' name='data[app_role_id]' type='text' value=\"{$cgx_data['app_role_id']}\" size='8' maxlength='8' style='text-align: right;' disabled /></td>";
    echo "<td width='10%'></td>";
    echo "<td width='15%'><label for='data_app_org_id'>Organization</label></td>";
    echo "<td width='30%'>" . cgx_form_select('data[app_org_id]', "SELECT app_org_id, organization FROM app_org WHERE is_trx = 'Y' ORDER BY organization", $cgx_data['app_org_id'], FALSE, "id='data_app_org_id'") . "</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td><label for='data_role'>Role</label></td>";
    echo "<td><input id='data_role' name='data[role]' type='text' value=\"{$cgx_data['role']}\" size='30' maxlength='50' style='text-align: left;' /></td>";
    echo "<td></td>";
    echo "<td><label for='data_active'>Active</label></td>";
    echo "<td>" . cgx_form_select('data[active]', array('Y' => 'Ya', 'N' => 'Tidak'), $cgx_data['active'], FALSE, "id='data_active'") . "</td>";
    echo "</tr>";
    echo "</table>";
    echo "</div>";
    
    $rsx = mysql_query("SELECT * FROM app_priv_group ORDER BY sort_order", $APP_CONNECTION);
    while ($dtx = mysql_fetch_array($rsx)) {
        if ($dtx['app_priv_group_id'] == 'admin') continue;
        echo "<fieldset>";
        echo "<legend>{$dtx['priv_group']}</legend>";
        
        $rsy = mysql_query("SELECT * FROM app_priv LEFT JOIN (SELECT app_priv_id, app_role_id FROM app_role_priv WHERE app_role_id = '{$_REQUEST['pkey']['app_role_id']}') X USING (app_priv_id) WHERE app_priv_group_id = '{$dtx['app_priv_group_id']}'", $APP_CONNECTION);
        while ($dty = mysql_fetch_array($rsy)) {
            echo "<table align='left' cellspacing='0' style='margin-right: 20px;'><tr>";
            echo "<td><input" . ($dty['app_role_id'] ? ' checked' : '') . " name='priv[{$dty['app_priv_id']}]' type='checkbox'></td>";
            echo "<td><nobr>{$dty['privilege']}</nobr></td>";
            echo "</tr></table>";
        }
        mysql_free_result($rsy);
        
        echo "</fieldset>";
    }
    mysql_free_result($rsx);
    
    echo "<input type='submit' value='Simpan'>\n";
    echo "<input type='button' value='Kembali' onclick=\"window.location = 'module.php?&m=role';\">\n";

    echo "</form>\n";
} else {
    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';

    if (is_array($_SESSION[$GLOBALS['APP_ID']]['role']['columns'])) {
        $cgx_def_columns = $_SESSION[$GLOBALS['APP_ID']]['role']['columns'];
    } else {
        $cgx_def_columns = array(
            'app_role_id' => 1,
            'role' => 1,
            'organization' => 1,
            'active' => 1,
        );
        $_SESSION[$GLOBALS['APP_ID']]['role']['columns'] = $cgx_def_columns;
    }

    $cgx_sql = "SELECT app_role_id, app_org_id, role, organization, app_role.active FROM app_role JOIN app_org USING (app_org_id) WHERE 1 = 1";
    $cgx_datagrid = new Structures_DataGrid($cgx_max_rows);
    $cgx_options = array('dsn' => $cgx_dsn);

    $cgx_filter1 = urldecode($_REQUEST['f1']);
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
    echo "</td>\n";
    echo "<td align='right'><input type='text' size='20' name='q' value=\"{$cgx_search}\"></td>\n";
    echo "<td width='1'><input title='Cari' type='image' src='images/icon_search.png' border='0' style='padding-right: 20px;'></td>\n";
    echo "<td></td>\n";
    echo "<td width='20'></td>\n";
    if (is_admin()) {
        echo "<td width='1' class='datagrid_bar_icon'><a title='Tambah data' href='module.php?&m={$_REQUEST['m']}&pkey[app_role_id]=0'><img border='0' src='images/icon_add.png'></a></td>\n";
    } else {
        echo "<td width='1' class='datagrid_bar_icon'><img border='0' src='images/icon_add_dis.png'></td>\n";
    }
    echo "</tr></table>\n";
    echo "</form>\n";

    if (strlen($cgx_filter1) > 0) $cgx_sql .= " AND app_role.app_org_id = '" . mysql_escape_string($cgx_filter1) . "'";
    $cgx_sql .= " and ( app_role.app_role_id LIKE '%{$cgx_search}%' OR app_role.role LIKE '%{$cgx_search}%')";
    if ($_SESSION[$GLOBALS['APP_ID']]['role']['error']) {
        echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['role']['error']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['role']['error']);
    }

    if ($_SESSION[$GLOBALS['APP_ID']]['role']['info']) {
        echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['role']['info']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['role']['info']);
    }


    $cgx_test = $cgx_datagrid->bind($cgx_sql, $cgx_options);
    if (PEAR::isError($cgx_test)) {
        echo $cgx_test->getMessage();
    }

    if ($cgx_def_columns['app_role_id'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('ID', 'app_role_id', 'app_role_id', array('align' => 'right'), NULL, NULL));
    if ($cgx_def_columns['role'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Role', 'role', 'role', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['app_org_id'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Organization', 'app_org_id', 'app_org_id', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['organization'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Organization', 'organization', 'organization', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['active'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Active', 'active', 'active', array('align' => 'center'), NULL, "cgx_format_yesno()"));
    if (is_admin()) $cgx_datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, 'cgx_edit()'));
    if (is_admin()) $cgx_datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, 'cgx_delete()'));

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