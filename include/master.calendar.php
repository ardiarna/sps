<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 07/01/2014 13:47:15
 */


echo "<div class='title'>Master Data Calendar</div>";

function cgx_edit($data) {
    $href = "module.php?&m={$_REQUEST['m']}&pkey[m_calendar_id]={$data['record']['m_calendar_id']}";
    $out = "<a href='{$href}'><img title='Edit baris ini' src='images/icon_edit.png' border='0'></a>";
    return $out;
}

function cgx_delete($data) {
    $href  = "javascript:if(confirm('Menghapus baris ini?')){window.location='action/master.calendar.php";
    $href .= "?backvar=module.php%253F%2526m%253Dmaster.calendar&mode=delete&pkey[m_calendar_id]={$data['record']['m_calendar_id']}';}";
    $out = "<a href=\"{$href}\"><img title='Hapus baris ini' src='images/icon_delete.png' border='0'></a>";
    return $out;
}

if (strlen($_REQUEST['pkey']['m_calendar_id']) > 0) {
    $cgx_id = $_REQUEST['id'];
    $cgx_data = cgx_fetch_table("SELECT * FROM m_calendar WHERE m_calendar_id = '" . mysql_escape_string($_REQUEST['pkey']['m_calendar_id']) . "'");
    
    echo "<form action='action/master.calendar.php' method='post'>\n";
    echo "<input type='hidden' name='backvar' value='" . urlencode("module.php?&m={$_REQUEST['m']}") . "'>\n";
    echo "<input type='hidden' name='mode' value='" . ($_REQUEST['pkey']['m_calendar_id'] == '0' ? 'new' : 'update') . "'>\n";
    echo "<input type='hidden' name='pkey[m_calendar_id]' value=\"{$_REQUEST['pkey']['m_calendar_id']}\">\n";
    echo "<input type='hidden' name='table' value='m_calendar'>\n";

    if ($_SESSION[$GLOBALS['APP_ID']]['master.calendar']['error']) {
        echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['master.calendar']['error']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['master.calendar']['error']);
    }

    if ($_SESSION[$GLOBALS['APP_ID']]['master.calendar']['info']) {
        echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['master.calendar']['info']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['master.calendar']['info']);
    }

    $cgx_data['calendar_date'] = empty($cgx_data['calendar_date']) ? date($APP_DATE_FORMAT) : date($APP_DATE_FORMAT, strtotime($cgx_data['calendar_date']));

    echo "<ul class='cgx_form'>\n";
    echo "    <input type='hidden' name='type[calendar_date]' value='T'>";
    echo "    <li class='even'>\n";
    echo "        <label class='cgx_form' for='data_calendar_date'>Calendar Date</label>\n";
    echo "        <input id='data_calendar_date' name='data[calendar_date]' type='text' value=\"{$cgx_data['calendar_date']}\" size='10' maxlength='10' style='text-align: left;' />\n";
    echo "    </li>\n";
    echo "    <input type='hidden' name='type[isholiday]' value='T'>";
    echo "    <li class='odd'>\n";
    echo "        <label class='cgx_form' for='data_isholiday'>Holiday</label>\n";
    echo cgx_form_select('data[isholiday]', array('Y' => 'Ya', 'N' => 'Tidak'), $cgx_data['isholiday'], FALSE, "id='data_isholiday'");
    echo "    </li>\n";
    echo "    <input type='hidden' name='type[note]' value='T'>";
    echo "    <li class='even'>\n";
    echo "        <label class='cgx_form' for='data_note'>Note</label>\n";
    echo "        <input id='data_note' name='data[note]' type='text' value=\"{$cgx_data['note']}\" size='50' maxlength='100' style='text-align: left;' />\n";
    echo "    </li>\n";
    echo "    <li class='odd'>\n";
    echo "        <label class='cgx_form'></label>\n";
    echo "        <input type='submit' value='Simpan'>\n";
    echo "        <input type='button' value='Kembali' onclick=\"window.location = 'module.php?&m=master.calendar';\">\n";
    echo "    </li>\n";
    echo "</ul>\n";
    echo "</form>\n";

} else {
    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';

    $_REQUEST['f1'] = empty($_REQUEST['f1']) ? org() : $_REQUEST['f1'];
    
    if (is_array($_SESSION[$GLOBALS['APP_ID']]['master.calendar']['columns'])) {
        $cgx_def_columns = $_SESSION[$GLOBALS['APP_ID']]['master.calendar']['columns'];
    } else {
        $cgx_def_columns = array(
            'calendar_date' => 1,
            'note' => 1,
            'active' => 1,
        );
        $_SESSION[$GLOBALS['APP_ID']]['master.calendar']['columns'] = $cgx_def_columns;
    }

    $cgx_sql = "SELECT * FROM m_calendar WHERE 1 = 1";
    $cgx_datagrid = new Structures_DataGrid($cgx_max_rows);
    $cgx_options = array('dsn' => $cgx_dsn);

    $cgx_filter2 = urldecode($_REQUEST['f2']);
    $cgx_search = $_REQUEST['q'];

    echo "<form name='frmFILTER' action='{$_SERVER['SCRIPT_NAME']}'>\n";
    echo "<input type='hidden' name='m' value='{$_REQUEST['m']}'>\n";
    echo "<table id='bar' class='datagrid_bar' width='100%'><tr>\n";
    echo "<td>\n";
    echo "<table align='left' cellspacing='0' cellpadding='0' border='0'><tr>\n";
    echo "<td><nobr><label for='f2'>Holiday</label></nobr></td>\n";
    echo "<td>&nbsp;</td>\n";
    echo "<td>" . cgx_filter('f2', array('Y' => 'Ya', 'N' => 'Tidak'), $cgx_filter2, TRUE) . "</td>\n";
    echo "<td width='20'></td>\n";
    echo "</tr></table>\n";
    echo "</td>\n";
    echo "<td align='right'><input type='text' size='20' name='q' value=\"{$cgx_search}\"></td>\n";
    echo "<td width='1'><input title='Cari' type='image' src='images/icon_search.png' border='0' style='padding-right: 20px;'></td>\n";
    echo "<td></td>\n";
    echo "<td width='20'></td>\n";
    if (has_privilege('master.calendar')) {
        echo "<td width='1' class='datagrid_bar_icon'><a title='Tambah data' href='module.php?&m={$_REQUEST['m']}&pkey[m_calendar_id]=0'><img border='0' src='images/icon_add.png'></a></td>\n";
    } else {
        echo "<td width='1' class='datagrid_bar_icon'><img border='0' src='images/icon_add_dis.png'></td>\n";
    }
    echo "<td width='1' class='datagrid_bar_icon'><a title='Export semua data ke CSV' href='action/master.calendar.php?mode=export-all'><img border='0' src='images/icon_csv.png'></a></td>\n";
    echo "<td width='1' class='datagrid_bar_icon'><a title='Kustomisasi kolom' href='javascript:customizeColumn(true);'><img border='0' src='images/icon_columns.png'></a></td>\n";
    echo "</tr></table>\n";
    echo "</form>\n";

    echo "<form name='frmCUSTOMIZE' action='action/cgx_customize.php' method='post'>\n";
    echo "<input type='hidden' name='back' value='" . urlencode($_SERVER['REQUEST_URI']) . "'>\n";
    echo "<input type='hidden' name='dg_name' value='master.calendar'>\n";
    echo "<table id='columns' class='datagrid_bar' style='display: none;'><tr>\n";
    echo "<td width='99%' valign='top'>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['m_calendar_id'] == 1 ? ' checked' : '') . " id='col_m_calendar_id' name='col[m_calendar_id]' type='checkbox'></td><td width='99%'><label for='col_m_calendar_id'>ID</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['calendar_date'] == 1 ? ' checked' : '') . " id='col_calendar_date' name='col[calendar_date]' type='checkbox'></td><td width='99%'><label for='col_calendar_date'>Calendar Date</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['note'] == 1 ? ' checked' : '') . " id='col_note' name='col[note]' type='checkbox'></td><td width='99%'><label for='col_note'>Note</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['isholiday'] == 1 ? ' checked' : '') . " id='col_isholiday' name='col[isholiday]' type='checkbox'></td><td width='99%'><label for='col_isholiday'>Holiday</label></td></tr></table>\n";
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

    if (strlen($cgx_filter2) > 0) $cgx_sql .= " AND isholiday = '" . mysql_escape_string($cgx_filter2) . "'";
    $cgx_sql .= " and ( calendar_date LIKE '%{$cgx_search}%' OR note LIKE '%{$cgx_search}%')";
    if ($_SESSION[$GLOBALS['APP_ID']]['master.calendar']['error']) {
        echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['master.calendar']['error']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['master.calendar']['error']);
    }

    if ($_SESSION[$GLOBALS['APP_ID']]['master.calendar']['info']) {
        echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['master.calendar']['info']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['master.calendar']['info']);
    }


    $cgx_test = $cgx_datagrid->bind($cgx_sql, $cgx_options);
    if (PEAR::isError($cgx_test)) {
        echo $cgx_test->getMessage();
    }

    if ($cgx_def_columns['m_calendar_id'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('ID', 'm_calendar_id', 'm_calendar_id', array('align' => 'right'), NULL, NULL));
    if ($cgx_def_columns['calendar_date'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Calendar Date', 'calendar_date', 'calendar_date', array('align' => 'left'), NULL, "cgx_format_date()"));
    if ($cgx_def_columns['note'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Note', 'note', 'note', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['isholiday'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Holiday', 'isholiday', 'isholiday', array('align' => 'center'), NULL, "cgx_format_yesno()"));
    if (has_privilege('master.calendar')) $cgx_datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, 'cgx_edit()'));
    if (has_privilege('master.calendar')) $cgx_datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, 'cgx_delete()'));

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
<!--

$(function() {
    $("#data_calendar_date").datepicker({dateFormat: '<?php echo $APP_DATE_FORMAT_JAVA; ?>'});
});

//-->
</script>