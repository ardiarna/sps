<?php

/**
 * (c) Azwari Nugraha <nugraha@pt-gai.com>
 * 22/10/2013 03:04:11
 */


echo "<div class='title'>Master Transaksi</div>";

function cgx_edit($data) {
    $href = "module.php?&m={$_REQUEST['m']}&pkey[m_transaction_type_id]={$data['record']['m_transaction_type_id']}";
    $out = "<a href='{$href}'><img title='Edit this row' src='images/icon_edit.png' border='0'></a>";
    return $out;
}

function cgx_delete($data) {
    $href  = "javascript:if(confirm('Delete this row?')){window.location='action/master.transaction_type.php";
    $href .= "?backvar=module.php%253F%2526m%253Dmaster.transaction_type&mode=delete&pkey[m_transaction_type_id]={$data['record']['m_transaction_type_id']}';}";
    $out = "<a href=\"{$href}\"><img title='Delete this row' src='images/icon_delete.png' border='0'></a>";
    return $out;
}

if (strlen($_REQUEST['pkey']['m_transaction_type_id']) > 0) {
    $cgx_id = $_REQUEST['id'];
    $cgx_data = cgx_fetch_table("SELECT * FROM m_transaction_type WHERE m_transaction_type.m_transaction_type_id = '" . mysql_escape_string($_REQUEST['pkey']['m_transaction_type_id']) . "'");


    echo "<form action='action/master.transaction_type.php' method='post'>\n";
    echo "<input type='hidden' name='backvar' value='" . urlencode("module.php?&m={$_REQUEST['m']}") . "'>\n";
    echo "<input type='hidden' name='mode' value='" . ($_REQUEST['pkey']['m_transaction_type_id'] == '0' ? 'new' : 'update') . "'>\n";
    echo "<input type='hidden' name='pkey[m_transaction_type_id]' value=\"{$_REQUEST['pkey']['m_transaction_type_id']}\">\n";
    echo "<input type='hidden' name='table' value='m_transaction_type'>\n";

    if ($_SESSION[$GLOBALS['APP_ID']]['master.transaction_type']['error']) {
        echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['master.transaction_type']['error']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['master.transaction_type']['error']);
    }

    if ($_SESSION[$GLOBALS['APP_ID']]['master.transaction_type']['info']) {
        echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['master.transaction_type']['info']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['master.transaction_type']['info']);
    }

    echo "<ul class='cgx_form'>\n";
    echo "    <input type='hidden' name='type[m_transaction_type_id]' value='N'>";
    echo "    <li class='even'>\n";
    echo "        <label class='cgx_form' for='data_m_transaction_type_id'>ID</label>\n";
    echo "        <input id='data_m_transaction_type_id' name='data[m_transaction_type_id]' type='text' value=\"{$cgx_data['m_transaction_type_id']}\" size='10' maxlength='8' style='text-align: right;' disabled />\n";
    echo "    </li>\n";
    echo "    <input type='hidden' name='type[type_code]' value='T'>";
    echo "    <li class='odd'>\n";
    echo "        <label class='cgx_form' for='data_type_code'>Kode Tipe</label>\n";
    echo "        <input id='data_type_code' name='data[type_code]' type='text' value=\"{$cgx_data['type_code']}\" size='10' maxlength='40' style='text-align: left;' />\n";
    echo "    </li>\n";
    echo "    <input type='hidden' name='type[type_name]' value='T'>";
    echo "    <li class='even'>\n";
    echo "        <label class='cgx_form' for='data_type_name'>Nama Tipe</label>\n";
    echo "        <input id='data_type_name' name='data[type_name]' type='text' value=\"{$cgx_data['type_name']}\" size='40' maxlength='10' style='text-align: left;' />\n";
    echo "    </li>\n";
    echo "    <input type='hidden' name='type[operation]' value='T'>";
    echo "    <li class='odd'>\n";
    echo "        <label class='cgx_form' for='data_operation'>Operasi</label>\n";
    echo cgx_form_select('data[operation]', array('-1' => 'Keluar', '1' => 'Masuk'), $cgx_data['operation'], FALSE, "id='data_operation'");
    echo "    </li>\n";
    echo "    <li class='odd'>\n";
    echo "        <label class='cgx_form'></label>\n";
    echo "        <input type='submit' value='Submit'>\n";
    echo "        <input type='button' value='Back' onclick=\"window.location = 'module.php?&m=master.transaction_type';\">\n";
    echo "    </li>\n";
    echo "</ul>\n";
    echo "</form>\n";

} else {
    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';

    if (is_array($_SESSION[$GLOBALS['APP_ID']]['master.transaction_type']['columns'])) {
        $cgx_def_columns = $_SESSION[$GLOBALS['APP_ID']]['master.transaction_type']['columns'];
    } else {
        $cgx_def_columns = array(
            'm_transaction_type_id' => 1,
            'type_code' => 1,
            'type_name' => 1,
            'operation' => 1,
        );
        $_SESSION[$GLOBALS['APP_ID']]['master.transaction_type']['columns'] = $cgx_def_columns;
    }

    $cgx_sql = "SELECT * FROM m_transaction_type WHERE 1 = 1";
    $cgx_datagrid = new Structures_DataGrid($cgx_max_rows);
    $cgx_options = array('dsn' => $cgx_dsn);

    $cgx_search = $_REQUEST['q'];

    echo "<form name='frmFILTER' action='{$_SERVER['SCRIPT_NAME']}'>\n";
    echo "<input type='hidden' name='m' value='{$_REQUEST['m']}'>\n";
    echo "<table id='bar' class='datagrid_bar' width='100%'><tr>\n";
    echo "<td align='right'><input type='text' size='20' name='q' value=\"{$cgx_search}\"></td>\n";
    echo "<td width='1'><input title='Search' type='image' src='images/icon_search.png' border='0' style='padding-right: 20px;'></td>\n";
    echo "<td></td>\n";
    echo "<td width='20'></td>\n";
    if (has_privilege('master')) {
        echo "<td width='1' class='datagrid_bar_icon'><a title='New record' href='module.php?&m={$_REQUEST['m']}&pkey[m_transaction_type_id]=0'><img border='0' src='images/icon_add.png'></a></td>\n";
    } else {
        echo "<td width='1' class='datagrid_bar_icon'><img border='0' src='images/icon_add_dis.png'></td>\n";
    }
    echo "<td width='1' class='datagrid_bar_icon'><a title='Export all (CSV)' href='action/master.transaction_type.php?mode=export-all'><img border='0' src='images/icon_csv.png'></a></td>\n";
    echo "<td width='1' class='datagrid_bar_icon'><a title='Customize columns' href='javascript:customizeColumn(true);'><img border='0' src='images/icon_columns.png'></a></td>\n";
    echo "</tr></table>\n";
    echo "</form>\n";

    echo "<form name='frmCUSTOMIZE' action='action/cgx_customize.php' method='post'>\n";
    echo "<input type='hidden' name='back' value='" . urlencode($_SERVER['REQUEST_URI']) . "'>\n";
    echo "<input type='hidden' name='dg_name' value='master.transaction_type'>\n";
    echo "<input type='hidden' name='col[m_transaction_type_id]' value='on'>\n";
    echo "<input type='hidden' name='col[type_code]' value='on'>\n";
    echo "<input type='hidden' name='col[operation]' value='on'>\n";
    echo "<table id='columns' class='datagrid_bar' style='display: none;'><tr>\n";
    echo "<td width='99%' valign='top'>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_m_transaction_type_id' name='col[m_transaction_type_id]' type='checkbox'></td><td width='99%'><label for='col_m_transaction_type_id'>ID</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_type_code' name='col[type_code]' type='checkbox'></td><td width='99%'><label for='col_type_code'>Kode Tipe</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['type_name'] == 1 ? ' checked' : '') . " id='col_type_name' name='col[type_name]' type='checkbox'></td><td width='99%'><label for='col_type_name'>Nama Tipe</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_operation' name='col[operation]' type='checkbox'></td><td width='99%'><label for='col_operation'>Operasi</label></td></tr></table>\n";
    echo "</td>\n";
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
    $cgx_sql .= " and ( m_transaction_type.m_transaction_type_id LIKE '%{$cgx_search}%' OR m_transaction_type.type_code LIKE '%{$cgx_search}%' OR m_transaction_type.operation LIKE '%{$cgx_search}%')";
    if ($_SESSION[$GLOBALS['APP_ID']]['master.transaction_type']['error']) {
        echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['master.transaction_type']['error']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['master.transaction_type']['error']);
    }

    if ($_SESSION[$GLOBALS['APP_ID']]['master.transaction_type']['info']) {
        echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['master.transaction_type']['info']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['master.transaction_type']['info']);
    }


    $cgx_test = $cgx_datagrid->bind($cgx_sql, $cgx_options);
    if (PEAR::isError($cgx_test)) {
        echo $cgx_test->getMessage();
    }

    if ($cgx_def_columns['m_transaction_type_id'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('ID', 'm_transaction_type_id', 'm_transaction_type_id', array('align' => 'right'), NULL, NULL));
    if ($cgx_def_columns['type_code'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Kode Tipe', 'type_code', 'type_code', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['type_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Nama Tipe', 'type_name', 'type_name', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['operation'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Operasi', 'operation', 'operation', array('align' => 'left'), NULL, NULL));
    if (has_privilege('master')) $cgx_datagrid->addColumn(new Structures_DataGrid_Column(Null, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, 'cgx_edit()'));
    if (has_privilege('master')) $cgx_datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, 'cgx_delete()'));

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
    echo "<td class='datagrid_pager'>Found " . number_format($cgx_datagrid->getRecordCount()) . " record(s)</td>\n";
    echo "<td align='right' class='datagrid_pager'>\n";
    $cgx_test = $cgx_datagrid->render(DATAGRID_RENDER_PAGER);
    if (PEAR::isError($cgx_test)) {
        echo $cgx_test->getMessage();
    }
    echo "</td></tr></table>\n";
}

?>