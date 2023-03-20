<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 26/05/2014 17:29:39
 */


echo "<div class='title'>SPK - Long Pipe</div>";

function cgx_format_week($data) {
    return substr($data['record'][$data['fieldName']], 0, 4) . ' Week ' .
        substr($data['record'][$data['fieldName']], 4, 2);
}

function cgx_print($data) {
    $href = "report.php?path=/reports/SPS/Perintah_Kerja_No&param[REPORT_SPK]={$data['record']['c_spk_id']}&type=pdf&param[REPORT_USER]=".user('user_fullname')."&param[REPORT_ORG_NAME]=".role('organization')."&fname={$data['record']['document_no']}";
    $out = "<a href='{$href}'><img title='Print SPK' src='images/icon_print.png' border='0'></a>";
    return $out;
}

require_once 'Structures/DataGrid.php';
require_once 'HTML/Table.php';

if (is_array($_SESSION[$GLOBALS['APP_ID']]['trx.ppc-sp-lp1']['columns'])) {
    $cgx_def_columns = $_SESSION[$GLOBALS['APP_ID']]['trx.ppc-sp-lp1']['columns'];
} else {
    $cgx_def_columns = array(
        'document_no' => 1,
        'spk_date' => 1,
        'machine_code' => 1,
        'wo_no' => 1,
        'wo_week' => 1,
    );
    $_SESSION[$GLOBALS['APP_ID']]['trx.ppc-sp-lp1']['columns'] = $cgx_def_columns;
}

$cgx_sql = "SELECT DISTINCT c_spk.*, c_wo.document_no wo_no, wo_week, wo_date, machine_code FROM c_spk JOIN c_wo USING (c_wo_id) 
        JOIN m_machine USING (m_machine_id) JOIN c_wo_line USING (c_wo_id) JOIN c_production_plan USING (c_production_plan_id)
        WHERE c_production_plan.app_org_id = 5 AND 1 = 1";
$cgx_datagrid = new Structures_DataGrid($cgx_max_rows);
$cgx_options = array('dsn' => $cgx_dsn);

$cgx_filter1 = urldecode($_REQUEST['f1']);
$cgx_search = $_REQUEST['q'];

echo "<form name='frmFILTER' action='{$_SERVER['SCRIPT_NAME']}'>\n";
echo "<input type='hidden' name='m' value='{$_REQUEST['m']}'>\n";
echo "<table id='bar' class='datagrid_bar' width='100%'><tr>\n";
echo "<td><input type='button' value='Create SPK' onclick=\"window.location = 'module.php?m=trx.ppc-sp-lp';\"></td>\n";
echo "<td>\n";
echo "<table align='left' cellspacing='0' cellpadding='0' border='0'><tr>\n";
echo "<td align='right'><nobr><label for='f1'>Machine</label></nobr></td>\n";
echo "<td>&nbsp;</td>\n";
echo "<td>" . cgx_filter('f1', "SELECT m_machine_id, machine_name FROM m_machine WHERE active = 'Y' ORDER BY machine_name", $cgx_filter1, TRUE) . "</td>\n";
echo "<td width='20'></td>\n";
echo "</tr></table>\n";
echo "</td>\n";
echo "<td align='right'><input type='text' size='20' name='q' value=\"{$cgx_search}\"></td>\n";
echo "<td width='1'><input title='Cari' type='image' src='images/icon_search.png' border='0' style='padding-right: 20px;'></td>\n";
echo "<td></td>\n";
echo "<td width='20'></td>\n";
echo "</tr></table>\n";
echo "</form>\n";

if (strlen($cgx_filter1) > 0) $cgx_sql .= " AND c_spk.m_machine_id = '" . mysql_escape_string($cgx_filter1) . "'";
$cgx_sql .= " and ( c_spk.c_spk_id LIKE '%{$cgx_search}%' OR c_spk.document_no LIKE '%{$cgx_search}%' OR c_wo.document_no LIKE '%{$cgx_search}%')";
if ($_SESSION[$GLOBALS['APP_ID']]['trx.ppc-sp-lp1']['error']) {
    echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['trx.ppc-sp-lp1']['error']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['trx.ppc-sp-lp1']['error']);
}

if ($_SESSION[$GLOBALS['APP_ID']]['trx.ppc-sp-lp1']['info']) {
    echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['trx.ppc-sp-lp1']['info']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['trx.ppc-sp-lp1']['info']);
}

$cgx_test = $cgx_datagrid->bind($cgx_sql, $cgx_options);
if (PEAR::isError($cgx_test)) {
    echo $cgx_test->getMessage();
}

if ($cgx_def_columns['document_no'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('SPK No', 'document_no', 'document_no', array('align' => 'center'), NULL, NULL));
if ($cgx_def_columns['spk_date'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('SPK Date', 'spk_date', 'spk_date', array('align' => 'center'), NULL, "cgx_format_date()"));
if ($cgx_def_columns['machine_code'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Machine', 'machine_code', 'machine_code', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['wo_no'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Work Order', 'wo_no', 'wo_no', array('align' => 'center'), NULL, NULL));
if ($cgx_def_columns['wo_week'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Week', 'wo_week', 'wo_week', array('align' => 'center'), NULL, "cgx_format_week()"));
$cgx_datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, 'cgx_print()'));

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

?>