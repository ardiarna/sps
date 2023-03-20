<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 15/01/2014 15:46:41
 */

function cgx_edit($data) {
    $href = "module.php?m=view.box-detail&id={$data['record']['c_bpartner_id']}";
    $out = "<a href='{$href}'><img src='images/icon_detail.png' border='0'></a>";
    return $out;
}

echo "<div class='title'>Posisi Box</div>";

require_once 'Structures/DataGrid.php';
require_once 'HTML/Table.php';

if (is_array($_SESSION[$GLOBALS['APP_ID']]['view.box']['columns'])) {
    $cgx_def_columns = $_SESSION[$GLOBALS['APP_ID']]['view.box']['columns'];
} else {
    $cgx_def_columns = array(
        'partner_code' => 1,
        'partner_name' => 1,
        'inside' => 1,
        'outside' => 1,
        'total' => 1,
    );
    $_SESSION[$GLOBALS['APP_ID']]['view.box']['columns'] = $cgx_def_columns;
}

$cgx_sql = "SELECT c_bpartner_id, partner_code, partner_name, "
        . "SUM(IF(location = 'I', 1, 0)) inside, SUM(IF(location = 'O', 1, 0)) outside, "
        . "COUNT(location) total FROM m_box JOIN c_bpartner USING (c_bpartner_id) "
        . "WHERE " . org_filter_trx('m_box.app_org_id');

$cgx_datagrid = new Structures_DataGrid($cgx_max_rows);
$cgx_options = array('dsn' => $cgx_dsn);

$cgx_search = $_REQUEST['q'];


echo "<form name='frmFILTER' action='{$_SERVER['SCRIPT_NAME']}'>\n";
echo "<input type='hidden' name='m' value='{$_REQUEST['m']}'>\n";
echo "<table id='bar' class='datagrid_bar' width='100%'><tr>\n";
echo "<td align='right'>Customer</td>";
echo "<td align='right' width='1'''><input type='text' style='width: 150px;' name='q' value=\"{$cgx_search}\"></td>\n";
echo "<td width='1'><input title='Cari' type='image' src='images/icon_search.png' border='0' style='padding-right: 20px;'></td>\n";
echo "<td></td>\n";
echo "<td width='20'></td>\n";
echo "<td width='1' class='datagrid_bar_icon'><a title='Export semua data ke CSV' href='action/view.box.php?mode=export-all'><img border='0' src='images/icon_csv.png'></a></td>\n";
echo "</tr></table>\n";
echo "</form>\n";

if($cgx_search) $cgx_sql .= " AND ( c_bpartner.partner_code LIKE '%{$cgx_search}%' OR c_bpartner.partner_name LIKE '%{$cgx_search}%')";
$cgx_sql .= " GROUP BY c_bpartner_id";
$cgx_test = $cgx_datagrid->bind($cgx_sql, $cgx_options);

if (PEAR::isError($cgx_test)) {
    echo $cgx_test->getMessage();
}

if ($cgx_def_columns['partner_code'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Kode Customer', 'partner_code', 'partner_code', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['partner_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Customer', 'partner_name', 'partner_name', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['inside'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Box Didalam', 'inside', 'inside', array('align' => 'right'), NULL, "cgx_format_3digit()"));
if ($cgx_def_columns['outside'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Box Diluar', 'outside', 'outside', array('align' => 'right'), NULL, "cgx_format_3digit()"));
if ($cgx_def_columns['total'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Jumlah Box', 'total', 'total', array('align' => 'right'), NULL, "cgx_format_3digit()"));
$cgx_datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, 'cgx_edit()'));

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