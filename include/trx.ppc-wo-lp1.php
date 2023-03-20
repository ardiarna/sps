<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 26/05/2014 00:14:18
 */


echo "<div class='title'>Work Order - Long Pipe</div>";

function cgx_print($data) {
    global $APP_CONNECTION;
    $wo = $data['record']['c_wo_id'];
    $sql = "SELECT DISTINCT working_date FROM c_wo_line JOIN c_wo USING (c_wo_id) WHERE c_wo.c_wo_id = {$wo} ORDER BY working_date";
    $rsx = mysql_query($sql , $APP_CONNECTION);
    $n = 0;
    while ($dtx = mysql_fetch_array($rsx, MYSQL_ASSOC)) {
         $n++;
         $datax[$n] = $dtx['working_date'];    
    }
    for ($i=1; $i<=7 ; $i++) {
        if ($datax[$i]) {
            $datay[$i] = $datax[$i];
        }else{
            $datay[$i] = "";
        }
    }
    $href = "report.php?path=/reports/SPS/Work_Order&param[REPORT_WO]={$wo}&type=pdf&fname={$data['record']['document_no']}&param[REPORT_USER]=".user('user_fullname')."&param[REPORT_ORG_NAME]=".role('organization')."&param[DAY_1]={$datay[1]}&param[DAY_2]={$datay[2]}&param[DAY_3]={$datay[3]}&param[DAY_4]={$datay[4]}&param[DAY_5]={$datay[5]}&param[DAY_6]={$datay[6]}&param[DAY_7]={$datay[7]}";
    $out = "<a href='{$href}'><img title='Print Work Order' src='images/icon_print.png' border='0'></a>";
    return $out;
}

function cgx_format_week($data) {
    return substr($data['record'][$data['fieldName']], 0, 4) . ' Week ' .
        substr($data['record'][$data['fieldName']], 4, 2);
}

if (strlen($_REQUEST['pkey']['c_wo_id']) > 0) {
    $cgx_id = $_REQUEST['id'];
    $cgx_data = cgx_fetch_table("SELECT * FROM c_wo WHERE c_wo.c_wo_id = '" . mysql_escape_string($_REQUEST['pkey']['c_wo_id']) . "'");


    echo "<form action='action/trx.ppc-wo-lp1.php' method='post'>\n";
    echo "<input type='hidden' name='backvar' value='" . urlencode("module.php?&m={$_REQUEST['m']}") . "'>\n";
    echo "<input type='hidden' name='mode' value='" . ($_REQUEST['pkey']['c_wo_id'] == '0' ? 'new' : 'update') . "'>\n";
    echo "<input type='hidden' name='pkey[c_wo_id]' value=\"{$_REQUEST['pkey']['c_wo_id']}\">\n";
    echo "<input type='hidden' name='table' value='c_wo'>\n";

    if ($_SESSION[$GLOBALS['APP_ID']]['trx.ppc-wo-lp1']['error']) {
        echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['trx.ppc-wo-lp1']['error']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['trx.ppc-wo-lp1']['error']);
    }

    if ($_SESSION[$GLOBALS['APP_ID']]['trx.ppc-wo-lp1']['info']) {
        echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['trx.ppc-wo-lp1']['info']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['trx.ppc-wo-lp1']['info']);
    }

    echo "<ul class='cgx_form'>\n";
    echo "    <li class='even'>\n";
    echo "        <label class='cgx_form'></label>\n";
    echo "        <input type='submit' value='Simpan'>\n";
    echo "        <input type='button' value='Kembali' onclick=\"window.location = 'module.php?&m=trx.ppc-wo-lp1';\">\n";
    echo "    </li>\n";
    echo "</ul>\n";
    echo "</form>\n";

} else {
    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';

    if (is_array($_SESSION[$GLOBALS['APP_ID']]['trx.ppc-wo-lp1']['columns'])) {
        $cgx_def_columns = $_SESSION[$GLOBALS['APP_ID']]['trx.ppc-wo-lp1']['columns'];
    } else {
        $cgx_def_columns = array(
            'document_no' => 1,
            'wo_date' => 1,
            'wo_week' => 1,
        );
        $_SESSION[$GLOBALS['APP_ID']]['trx.ppc-wo-lp1']['columns'] = $cgx_def_columns;
    }

    $cgx_sql = "SELECT DISTINCT c_wo_id, document_no, wo_date, wo_week FROM c_wo JOIN c_wo_line USING (c_wo_id) 
                JOIN c_production_plan USING (c_production_plan_id) WHERE app_org_id=5 AND 1 = 1";
    $cgx_datagrid = new Structures_DataGrid($cgx_max_rows);
    $cgx_options = array('dsn' => $cgx_dsn);
    $cgx_datagrid->setDefaultSort(array('wo_week' => 'DESC'));
    
    $cgx_search = $_REQUEST['q'];

    echo "<form name='frmFILTER' action='{$_SERVER['SCRIPT_NAME']}'>\n";
    echo "<input type='hidden' name='m' value='{$_REQUEST['m']}'>\n";
    echo "<table id='bar' class='datagrid_bar' width='100%'><tr>\n";
    if (has_privilege('trx.ppc-wo-lp1')) {
        echo "<td><input onclick=\"window.location = 'module.php?m=trx.ppc-wo-lp';\" type='button' value='Create Work Order'></td>\n";
    }
    echo "<td align='right'><input type='text' size='20' name='q' value=\"{$cgx_search}\"></td>\n";
    echo "<td width='1'><input title='Cari' type='image' src='images/icon_search.png' border='0' style='padding-right: 20px;'></td>\n";
    echo "<td></td>\n";
    echo "<td width='20'></td>\n";
    echo "</tr></table>\n";
    echo "</form>\n";
    $cgx_sql .= " and ( c_wo.document_no LIKE '%{$cgx_search}%' OR c_wo.wo_week LIKE '%{$cgx_search}%')";
    if ($_SESSION[$GLOBALS['APP_ID']]['trx.ppc-wo-lp1']['error']) {
        echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['trx.ppc-wo-lp1']['error']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['trx.ppc-wo-lp1']['error']);
    }

    if ($_SESSION[$GLOBALS['APP_ID']]['trx.ppc-wo-lp1']['info']) {
        echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['trx.ppc-wo-lp1']['info']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['trx.ppc-wo-lp1']['info']);
    }


    $cgx_test = $cgx_datagrid->bind($cgx_sql, $cgx_options);
    if (PEAR::isError($cgx_test)) {
        echo $cgx_test->getMessage();
    }

    if ($cgx_def_columns['document_no'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Nomor Dokumen', 'document_no', 'document_no', array('align' => 'center'), NULL, NULL));
    if ($cgx_def_columns['wo_date'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Date', 'wo_date', 'wo_date', array('align' => 'center'), NULL, "cgx_format_date()"));
    if ($cgx_def_columns['wo_week'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Week', 'wo_week', 'wo_week', array('align' => 'center'), NULL, "cgx_format_week()"));
    if (has_privilege('trx.ppc-wo-lp1')) $cgx_datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, 'cgx_print()'));

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