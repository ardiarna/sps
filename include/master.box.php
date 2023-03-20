<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 18/12/2013 21:42:48
 */


echo "<div class='title'>Master Data Box</div>";

function cgx_edit($data) {
    $href = "module.php?&m={$_REQUEST['m']}&pkey[m_box_id]={$data['record']['m_box_id']}&pkey[type]=single";
    $out = "<a href='{$href}'><img title='Edit baris ini' src='images/icon_edit.png' border='0'></a>";
    return $out;
}

function cgx_delete($data) {
    $href  = "javascript:if(confirm('Menghapus baris ini?')){window.location='action/master.box.php";
    $href .= "?backvar=module.php%253F%2526m%253Dmaster.box&mode=delete&pkey[m_box_id]={$data['record']['m_box_id']}';}";
    $out = "<a href=\"{$href}\"><img title='Hapus baris ini' src='images/icon_delete.png' border='0'></a>";
    return $out;
}

if (strlen($_REQUEST['pkey']['m_box_id']) > 0) {
    $cgx_id = $_REQUEST['id'];
    $cgx_data = cgx_fetch_table("SELECT * FROM m_box bx,c_bpartner cb WHERE bx.c_bpartner_id=cb.c_bpartner_id AND bx.m_box_id = '" . mysql_escape_string($_REQUEST['pkey']['m_box_id']) . "'");

    $select_partner = "<img onclick=\"popupReference('business-partner');\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'>";

    echo "<form action='action/master.box.php' method='post'>\n";
    echo "<input type='hidden' name='backvar' value='" . urlencode("module.php?&m={$_REQUEST['m']}") . "'>\n";
    echo "<input type='hidden' name='mode' value='" . ($_REQUEST['pkey']['m_box_id'] == '0' ? 'new' : 'update') . "'>\n";
    echo "<input type='hidden' name='pkey[m_box_id]' value=\"{$_REQUEST['pkey']['m_box_id']}\">\n";
    echo "<input type='hidden' name='table' value='m_box'>\n";

    if ($_SESSION[$GLOBALS['APP_ID']]['master.box']['error']) {
        echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['master.box']['error']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['master.box']['error']);
    }

    if ($_SESSION[$GLOBALS['APP_ID']]['master.box']['info']) {
        echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['master.box']['info']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['master.box']['info']);
    }
    $cgx_data['app_org_id'] = empty($cgx_data['app_org_id']) ? org() : $cgx_data['app_org_id'];

    if($_REQUEST['pkey']['type']=="single"){
        echo "<input type='hidden' name='xtype' value='single'>\n";
        echo "<ul class='cgx_form'>\n";
        echo "    <input type='hidden' name='type[m_box_id]' value='N'>";
        echo "    <li class='even'>\n";
        echo "        <label class='cgx_form' for='data_m_box_id'>ID</label>\n";
        echo "        <input id='data_m_box_id' name='data[m_box_id]' type='text' value=\"{$cgx_data['m_box_id']}\" size='8' maxlength='8' style='text-align: right;' disabled />\n";
        echo "    </li>\n";
        echo "    <li class='odd'>\n";
        echo "        <label class='cgx_form' for='data_app_org_id'>Organization</label>\n";
        echo cgx_form_select('data[app_org_id]', "SELECT app_org_id, organization FROM app_org", $cgx_data['app_org_id'], FALSE, "id='data_app_org_id'");
        echo "    </li>\n";
        echo "    <input type='hidden' id='c_bpartner_id' name='data[c_bpartner_id]' value='{$cgx_data['c_bpartner_id']}'>";
        echo "    <li class='odd'>\n";
        echo "        <label class='cgx_form' for='data_partner_name'>Customer {$mandatory}</label>";
        echo "        <input readonly='readonly' type='text' id='partner_name' size='30' value=\"{$cgx_data['partner_name']}\">{$select_partner}</td>";
        echo "    </li>\n";
        echo "    <input type='hidden' name='type[box_code]' value='T'>";
        echo "    <li class='odd'>\n";
        echo "        <label class='cgx_form' for='data_box_code'>Box Code</label>\n";
        echo "        <input id='data_box_code' name='data[box_code]' type='text' value=\"{$cgx_data['box_code']}\" size='15' style='text-align: left;' />\n";
        echo "    </li>\n";
        echo "    <input type='hidden' name='type[box_number]' value='T'>";
        echo "    <li class='odd'>\n";
        echo "        <label class='cgx_form' for='data_box_number'>Box Number</label>\n";
        echo "        <input id='data_box_number' name='data[box_number]' type='text' value=\"{$cgx_data['box_number']}\" size='4' maxlength='4' style='text-align: left;' />\n";
        echo "    </li>\n";
        echo "    <input type='hidden' name='type[box_size]' value='T'>";
        echo "    <li class='odd'>\n";
        echo "        <label class='cgx_form' for='data_box_size'>Box Size</label>\n";
        echo "        <input id='data_box_size' name='data[box_size]' type='text' value=\"{$cgx_data['box_size']}\" size='20' style='text-align: left;' />\n";
        echo "    </li>\n";
        echo "    <input type='hidden' name='type[pipe_size]' value='T'>";
        echo "    <li class='odd'>\n";
        echo "        <label class='cgx_form' for='data_pipe_size'>Pipe Size</label>\n";
        echo "        <input id='data_pipe_size' name='data[pipe_size]' type='text' value=\"{$cgx_data['pipe_size']}\" size='20' style='text-align: left;' />\n";
        echo "    </li>\n";
        echo "    <input type='hidden' name='type[kapasitas_box]' value='T'>";
        echo "    <li class='odd'>\n";
        echo "        <label class='cgx_form' for='data_kapasitas_box'>Box Capacity</label>\n";
        echo "        <input id='data_kapasitas_box' name='data[kapasitas_box]' type='text' value=\"{$cgx_data['kapasitas_box']}\" size='5' maxlength=5 style='text-align: right;' />\n";
        echo "    </li>\n";
        echo "    <li class='even'>\n";
        echo "        <label class='cgx_form'></label>\n";
        echo "        <input type='submit' value='Simpan'>\n";
        echo "        <input type='button' value='Kembali' onclick=\"window.location = 'module.php?&m=master.box';\">\n";
        echo "    </li>\n";
        echo "</ul>\n";
    }else{
        echo "<input type='hidden' name='xtype' value='multi'>\n";
        echo "<ul class='cgx_form'>";
        echo "    <li class='odd'>\n";
        echo "        <label class='cgx_form' for='data_app_org_id'>Organization</label>\n";
        echo cgx_form_select('data[app_org_id]', "SELECT app_org_id, organization FROM app_org", $cgx_data['app_org_id'], FALSE, "id='data_app_org_id'");
        echo "    </li>\n";
        echo "    <input type='hidden' id='c_bpartner_id' name='data[c_bpartner_id]' value='{$cgx_data['c_bpartner_id']}'>";
        echo "    <li class='odd'>\n";
        echo "        <label class='cgx_form' for='data_partner_name'>Customer {$mandatory}</label>";
        echo "        <input readonly='readonly' type='text' id='partner_name' size='30' value=\"{$cgx_data['partner_name']}\">{$select_partner}</td>";
        echo "    </li>\n";
        echo "    <li class='odd'>\n";
        echo "        <label class='cgx_form' for='data_box_code'>Box Code</label>\n";
        echo "        <input id='data_box_code' name='data[box_code]' type='text' value=\"-\" size='15' style='text-align: left;' />\n";
        echo "    </li>\n";
        echo "    <input type='hidden' name='type[box_number]' value='T'>";
        echo "    <li class='odd'>\n";
        echo "        <label class='cgx_form' for='data_box_number'>Box Number</label>\n";
        echo "        <input id='data_box_number' name='data[box_number_start]' type='text' value=\"0\" size='4' maxlength='4' style='text-align: left;' /> s/d ";
        echo "        <input id='data_box_number' name='data[box_number_end]' type='text' value=\"0\" size='4' maxlength='4' style='text-align: left;' />\n";
        echo "    </li>\n";
        echo "    <input type='hidden' name='type[kapasitas_box]' value='T'>";
        echo "    <li class='odd'>\n";
        echo "        <label class='cgx_form' for='data_kapasitas_box'>Box Capacity</label>\n";
        echo "        <input id='data_kapasitas_box' name='data[kapasitas_box]' type='text' value=\"0\" size='5' maxlength=5 style='text-align: right;' />\n";
        echo "    </li>\n";
        echo "    <li class='even'>\n";
        echo "        <label class='cgx_form'></label>\n";
        echo "        <input type='submit' value='Simpan'>\n";
        echo "        <input type='button' value='Kembali' onclick=\"window.location = 'module.php?&m=master.box';\">\n";
        echo "    </li>\n";
        echo "</ul>";
    }
    echo "</form>\n";

} else {
    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';
    
    $_REQUEST['app_org_id'] = empty($_REQUEST['app_org_id']) ? org() : $_REQUEST['app_org_id'];

    if (is_array($_SESSION[$GLOBALS['APP_ID']]['master.box']['columns'])) {
        $cgx_def_columns = $_SESSION[$GLOBALS['APP_ID']]['master.box']['columns'];
    } else {
        $cgx_def_columns = array(
            'm_box_id' => 1,
            'box_number' => 1,
            'box_code' => 1,
            'c_bpartner_id' => 1,
            'box_size' => 1,
            'pipe_size' => 1,
            'kapasitas_box' => 1,
            'partner_name' => 1
        );
        $_SESSION[$GLOBALS['APP_ID']]['master.box']['columns'] = $cgx_def_columns;
    }

    $cgx_sql = "SELECT * FROM m_box bx, c_bpartner cb WHERE bx.c_bpartner_id = cb.c_bpartner_id";
    if ($_REQUEST['app_org_id']) $cgx_sql .= " AND bx.app_org_id = '{$_REQUEST['app_org_id']}' ";
    $cgx_datagrid = new Structures_DataGrid($cgx_max_rows);
    $cgx_options = array('dsn' => $cgx_dsn);

    $cgx_search = $_REQUEST['q'];

    echo "<form name='frmFILTER' action='{$_SERVER['SCRIPT_NAME']}'>\n";
    echo "<input type='hidden' name='m' value='{$_REQUEST['m']}'>\n";
    echo "<table id='bar' class='datagrid_bar' width='100%' border='0'><tr>\n";
    echo "<td><label>Organization</label><br>" . cgx_filter('app_org_id', "SELECT app_org_id, organization FROM app_org", $_REQUEST['app_org_id'], FALSE) . "</td>\n";
    echo "<td width='1'><label>Box Number</label><br><input type='text' size='10' name='box_number' value=\"{$_REQUEST['box_number']}\"></td>\n";
    echo "<td width='1'><label>Box Code</label><br><input type='text' size='10' name='box_code' value=\"{$_REQUEST['box_code']}\"></td>\n";
    echo "<td width='1'><label>Partner Name</label><br><input type='text' size='10' name='partner_name' value=\"{$_REQUEST['partner_name']}\"></td>\n";
    echo "<td width='1' style='vertical-align: bottom; padding-bottom: 4px;'><input title='Cari' type='image' src='images/icon_search.png' border='0' style='padding-right: 20px;'></td>\n";
    echo "<td></td>\n";
    echo "<td width='20'></td>\n";
    if (has_privilege('master.box')) {
        echo "<td style='vertical-align: bottom; padding-bottom: 4px;' width='1' class='datagrid_bar_icon'><a title='Generate data' href='module.php?&m={$_REQUEST['m']}&pkey[m_box_id]=0&pkey[type]=multi'><img border='0' src='images/multiple_input.png' width='16'></a></td>\n";
        echo "<td style='vertical-align: bottom; padding-bottom: 4px;' width='1' class='datagrid_bar_icon'><a title='Tambah data' href='module.php?&m={$_REQUEST['m']}&pkey[m_box_id]=0&pkey[type]=single'><img border='0' src='images/icon_add.png'></a></td>\n";
    } else {
        echo "<td style='vertical-align: bottom; padding-bottom: 4px;' width='1' class='datagrid_bar_icon'><img border='0' src='images/icon_add_dis.png'></td>\n";
    }
    echo "<td style='vertical-align: bottom; padding-bottom: 4px;' width='1' class='datagrid_bar_icon'><a title='Export semua data ke CSV' href='action/master.box.php?mode=export-all'><img border='0' src='images/icon_csv.png'></a></td>\n";
    echo "<td style='vertical-align: bottom; padding-bottom: 4px;' width='1' class='datagrid_bar_icon'><a title='Kustomisasi kolom' href='javascript:customizeColumn(true);'><img border='0' src='images/icon_columns.png'></a></td>\n";
    echo "</tr></table>\n";
    echo "</form>\n";

    echo "<form name='frmCUSTOMIZE' action='action/cgx_customize.php' method='post'>\n";
    echo "<input type='hidden' name='back' value='" . urlencode($_SERVER['REQUEST_URI']) . "'>\n";
    echo "<input type='hidden' name='dg_name' value='master.box'>\n";
    echo "<input type='hidden' name='col[box_number]' value='on'>\n";
    echo "<input type='hidden' name='col[m_box_id]' value='on'>\n";
    echo "<input type='hidden' name='col[partner_name]' value='on'>\n";
    echo "<table id='columns' class='datagrid_bar' style='display: none;'><tr>\n";
    echo "<td width='99%' valign='top'>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_m_box_id' name='col[m_box_id]' type='checkbox'></td><td width='99%'><label for='col_m_box_id'>ID</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input checked id='col_box_number' name='col[box_number]' type='checkbox'></td><td width='99%'><label for='col_box_number'>Box Number</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input checked id='col_box_code' name='col[box_code]' type='checkbox'></td><td width='99%'><label for='col_box_code'>Box Code</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input checked id='col_box_size' name='col[box_size]' type='checkbox'></td><td width='99%'><label for='col_box_size'>Box Size</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input checked id='col_pipe_size' name='col[pipe_size]' type='checkbox'></td><td width='99%'><label for='col_pipe_size'>Pipe Size</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input checked id='col_kapasitas_box' name='col[kapasitas_box]' type='checkbox'></td><td width='99%'><label for='col_kapasitas_box'>Kapasitas Box</label></td></tr></table>\n";
    echo "<table cellspacing=l.'0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input checked id='col_partner_name' name='col[partner_name]' type='checkbox'></td><td width='99%'><label for='col_partner_name'>Customer</label></td></tr></table>\n";
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
    if(!empty($_REQUEST['box_number'])){
        $cgx_sql .= " and ( bx.box_number LIKE '%{$_REQUEST['box_number']}%')";
    }

    if(!empty($_REQUEST['box_code'])){
        $cgx_sql .= " and ( bx.box_code LIKE '%{$_REQUEST['box_code']}%')";
    }

    if(!empty($_REQUEST['partner_name'])){
        $cgx_sql .= " and ( cb.partner_name LIKE '%{$_REQUEST['partner_name']}%')";
    }
    

    if ($_SESSION[$GLOBALS['APP_ID']]['master.box']['error']) {
        echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['master.box']['error']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['master.box']['error']);
    }

    if ($_SESSION[$GLOBALS['APP_ID']]['master.box']['info']) {
        echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['master.box']['info']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['master.box']['info']);
    }

    $cgx_test = $cgx_datagrid->bind($cgx_sql, $cgx_options);
    if (PEAR::isError($cgx_test)) {
        echo $cgx_test->getMessage();
    }

    if ($cgx_def_columns['m_box_id'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('ID', 'm_box_id', 'm_box_id', array('align' => 'right'), NULL, NULL));
    if ($cgx_def_columns['box_number'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Box Number', 'box_number', 'box_number', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['box_code'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Box Code', 'box_code', 'box_code', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['box_size'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Box Size', 'box_size', 'box_size', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['pipe_size'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Pipe Size', 'pipe_size', 'pipe_size', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['kapasitas_box'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Box Capacity', 'kapasitas_box', 'kapasitas_box', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['partner_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Customer', 'partner_name', 'partner_name', array('align' => 'left'), NULL, NULL));
    if (has_privilege('master.box')) $cgx_datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, 'cgx_edit()'));
    if (has_privilege('master.box')) $cgx_datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, 'cgx_delete()'));

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
<script type="text/javascript">
function setBusinessPartner(id, name) {
    var txt_name = document.getElementById('partner_name');
    var hid_id = document.getElementById('c_bpartner_id');
    txt_name.value = name;
    hid_id.value = id;
}
</script>