<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 27/12/2013 14:10:44
 */


echo "<div class='title'>Pengaturan Pengguna</div>";

function cgx_edit($data) {
    $href = "module.php?&m={$_REQUEST['m']}&pkey[user_id]={$data['record']['user_id']}";
    $out = "<a href='{$href}'><img title='Edit baris ini' src='images/icon_edit.png' border='0'></a>";
    return $out;
}

function cgx_delete($data) {
    if ($data['record']['user_system'] == 'Y') return NULL;
    $href  = "javascript:if(confirm('Menghapus baris ini?')){window.location='action/user.php";
    $href .= "?backvar=module.php%253F%2526m%253Duser&mode=delete&pkey[user_id]={$data['record']['user_id']}';}";
    $out = "<a href=\"{$href}\"><img title='Hapus baris ini' src='images/icon_delete.png' border='0'></a>";
    return $out;
}

if (strlen($_REQUEST['pkey']['user_id']) > 0) {
    $cgx_id = $_REQUEST['id'];
    $cgx_data = cgx_fetch_table("SELECT * FROM app_user WHERE app_user.user_id = '" . mysql_escape_string($_REQUEST['pkey']['user_id']) . "'");


    echo "<form action='action/user.php' method='post'>\n";
    echo "<input type='hidden' name='backvar' value='" . urlencode("module.php?&m={$_REQUEST['m']}") . "'>\n";
    echo "<input type='hidden' name='mode' value='" . ($_REQUEST['pkey']['user_id'] == '0' ? 'new' : 'update') . "'>\n";
    echo "<input type='hidden' name='pkey[user_id]' value=\"{$_REQUEST['pkey']['user_id']}\">\n";
    echo "<input type='hidden' name='table' value='app_user'>\n";

    if ($_SESSION[$GLOBALS['APP_ID']]['user']['error']) {
        echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['user']['error']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['user']['error']);
    }

    if ($_SESSION[$GLOBALS['APP_ID']]['user']['info']) {
        echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['user']['info']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['user']['info']);
    }
    
    echo "<fieldset>";
    echo "<legend>Pengguna</legend>";
    echo "<table width='100%'>";
    echo "<tr>";
    echo "<td width='15%'><label for='data_user_id'>ID</label></td>";
    echo "<td width='30%'><input id='data_user_id' name='data[user_id]' type='text' value=\"{$cgx_data['user_id']}\" size='8' maxlength='8' style='text-align: right;' disabled /></td>";
    echo "<td width='10%'></td>";
    echo "<td width='15%'></td>";
    echo "<td width='30%'></td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td><label for='data_user_name'>Nama Login</label></td>";
    echo "<td><input id='data_user_name' name='data[user_name]' type='text' value=\"{$cgx_data['user_name']}\" size='16' maxlength='16' style='text-align: left;' /></td>";
    echo "<td></td>";
    echo "<td><label for='data_user_email'>Email</label></td>";
    echo "<td><input id='data_user_email' name='data[user_email]' type='text' value=\"{$cgx_data['user_email']}\" size='20' maxlength='50' style='text-align: left;' /></td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td><label for='data_user_fullname'>Nama Lengkap</label></td>";
    echo "<td><input id='data_user_fullname' name='data[user_fullname]' type='text' value=\"{$cgx_data['user_fullname']}\" size='20' maxlength='25' style='text-align: left;' /></td>";
    echo "<td></td>";
    echo "<td><label for='data_user_active'>Aktif</label></td>";
    echo "<td>" . cgx_form_select('data[user_active]', array('Y' => 'Ya', 'N' => 'Tidak'), $cgx_data['user_active'], FALSE, "id='data_user_active'") . "</td>";
    echo "</tr>";
    echo "</table>";
    echo "</fieldset>";
    
    echo "<fieldset>";
    echo "<legend>Password</legend>";
    echo "<table width='100%'>";
    echo "<tr>";
    echo "<td colspan='2'><table><tr><td><input onclick=\"document.getElementById('password').disabled = !this.checked;\" type='checkbox' name='reset-password' id='reset-passsword'></td><td><label for='reset-passsword'>Reset Password</label></td></tr></table></td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td width='15%'>Password Baru</td>";
    echo "<td><input id='password' name='password' type='password' size='16' disabled></td>";
    echo "</tr>";
    echo "</table>";
    echo "</fieldset>";

    echo "<fieldset>";
    echo "<legend>Role</legend>";
    echo "<table>";
    $rsx = mysql_query("SELECT * FROM app_role"
            . " LEFT JOIN (SELECT * FROM app_user_role WHERE user_id = '{$_REQUEST['pkey']['user_id']}') X USING (app_role_id) "
            . " WHERE active = 'Y' ORDER BY role", $APP_CONNECTION);
    while ($dtx = mysql_fetch_array($rsx)) {
        echo "<tr>";
        echo "<td><input" . ($dtx['user_id'] ? ' checked' : '') . " name='role[{$dtx['app_role_id']}]' id='role_{$dtx['app_role_id']}' type='checkbox'></td>";
        echo "<td><label for='role_{$dtx['app_role_id']}'><nobr>{$dtx['role']}</nobr></label></td>";
        echo "</tr>";
    }
    mysql_free_result($rsx);
    echo "</table>";
    echo "</fieldset>";
    
    echo "<input type='submit' value='Simpan'>\n";
    echo "<input type='button' value='Kembali' onclick=\"window.location = 'module.php?m=user';\">\n";
    
    echo "</form>\n";

} else {
    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';

    if (is_array($_SESSION[$GLOBALS['APP_ID']]['user']['columns'])) {
        $cgx_def_columns = $_SESSION[$GLOBALS['APP_ID']]['user']['columns'];
    } else {
        $cgx_def_columns = array(
            'user_id' => 1,
            'user_name' => 1,
            'user_fullname' => 1,
            'user_active' => 1,
            'last_ip' => 1,
            'last_login' => 1,
        );
        $_SESSION[$GLOBALS['APP_ID']]['user']['columns'] = $cgx_def_columns;
    }

    $cgx_sql = "SELECT * FROM app_user WHERE 1 = 1";
    $cgx_datagrid = new Structures_DataGrid($cgx_max_rows);
    $cgx_options = array('dsn' => $cgx_dsn);

    $cgx_search = $_REQUEST['q'];

    echo "<form name='frmFILTER' action='{$_SERVER['SCRIPT_NAME']}'>\n";
    echo "<input type='hidden' name='m' value='{$_REQUEST['m']}'>\n";
    echo "<table id='bar' class='datagrid_bar' width='100%'><tr>\n";
    echo "<td align='right'><input type='text' size='20' name='q' value=\"{$cgx_search}\"></td>\n";
    echo "<td width='1'><input title='Cari' type='image' src='images/icon_search.png' border='0' style='padding-right: 20px;'></td>\n";
    echo "<td></td>\n";
    echo "<td width='20'></td>\n";
    if (is_admin()) {
        echo "<td width='1' class='datagrid_bar_icon'><a title='Tambah data' href='module.php?&m={$_REQUEST['m']}&pkey[user_id]=0'><img border='0' src='images/icon_add.png'></a></td>\n";
    } else {
        echo "<td width='1' class='datagrid_bar_icon'><img border='0' src='images/icon_add_dis.png'></td>\n";
    }
    echo "<td width='1' class='datagrid_bar_icon'><a title='Kustomisasi kolom' href='javascript:customizeColumn(true);'><img border='0' src='images/icon_columns.png'></a></td>\n";
    echo "</tr></table>\n";
    echo "</form>\n";

    echo "<form name='frmCUSTOMIZE' action='action/cgx_customize.php' method='post'>\n";
    echo "<input type='hidden' name='back' value='" . urlencode($_SERVER['REQUEST_URI']) . "'>\n";
    echo "<input type='hidden' name='dg_name' value='user'>\n";
    echo "<input type='hidden' name='col[user_fullname]' value='on'>\n";
    echo "<input type='hidden' name='col[user_id]' value='on'>\n";
    echo "<input type='hidden' name='col[user_name]' value='on'>\n";
    echo "<table id='columns' class='datagrid_bar' style='display: none;'><tr>\n";
    echo "<td width='99%' valign='top'>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_user_id' name='col[user_id]' type='checkbox'></td><td width='99%'><label for='col_user_id'>ID</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_user_name' name='col[user_name]' type='checkbox'></td><td width='99%'><label for='col_user_name'>Nama Login</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_user_fullname' name='col[user_fullname]' type='checkbox'></td><td width='99%'><label for='col_user_fullname'>Nama Lengkap</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['user_email'] == 1 ? ' checked' : '') . " id='col_user_email' name='col[user_email]' type='checkbox'></td><td width='99%'><label for='col_user_email'>Email</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['user_active'] == 1 ? ' checked' : '') . " id='col_user_active' name='col[user_active]' type='checkbox'></td><td width='99%'><label for='col_user_active'>Aktif</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['last_ip'] == 1 ? ' checked' : '') . " id='col_last_ip' name='col[last_ip]' type='checkbox'></td><td width='99%'><label for='col_last_ip'>Alamat IP</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['last_login'] == 1 ? ' checked' : '') . " id='col_last_login' name='col[last_login]' type='checkbox'></td><td width='99%'><label for='col_last_login'>Tanggal Login</label></td></tr></table>\n";
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
    $cgx_sql .= " and ( app_user.user_name LIKE '%{$cgx_search}%' OR app_user.user_fullname LIKE '%{$cgx_search}%' OR app_user.user_email LIKE '%{$cgx_search}%')";
    if ($_SESSION[$GLOBALS['APP_ID']]['user']['error']) {
        echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['user']['error']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['user']['error']);
    }

    if ($_SESSION[$GLOBALS['APP_ID']]['user']['info']) {
        echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['user']['info']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['user']['info']);
    }


    $cgx_test = $cgx_datagrid->bind($cgx_sql, $cgx_options);
    if (PEAR::isError($cgx_test)) {
        echo $cgx_test->getMessage();
    }

    if ($cgx_def_columns['user_id'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('ID', 'user_id', 'user_id', array('align' => 'right'), NULL, NULL));
    if ($cgx_def_columns['user_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Nama Login', 'user_name', 'user_name', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['user_fullname'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Nama Lengkap', 'user_fullname', 'user_fullname', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['user_email'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Email', 'user_email', 'user_email', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['user_active'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Aktif', 'user_active', 'user_active', array('align' => 'center'), NULL, "cgx_format_yesno()"));
    if ($cgx_def_columns['last_ip'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Alamat IP', 'last_ip', 'last_ip', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['last_login'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Tanggal Login', 'last_login', 'last_login', array('align' => 'center'), NULL, "cgx_format_timestamp()"));
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
