<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 15/01/2014 16:09:52
 */


echo "<div class='title'>Posisi Box</div>";

$cgx_sql = "SELECT c_bpartner_id, partner_code, partner_name, "
        . "SUM(IF(location = 'I', 1, 0)) inside, SUM(IF(location = 'O', 1, 0)) outside, "
        . "COUNT(location) total FROM m_box JOIN c_bpartner USING (c_bpartner_id) "
        . "WHERE c_bpartner_id = '{$_REQUEST['id']}'";
$box = npl_fetch_table($cgx_sql);

echo "<div class='data_box' style='margin-bottom: 4px;'>";
echo "<table width='100%'>";
echo "<tr>";
echo "<td width='12%'>Kode Customer</td>";
echo "<td width='36%'><input style='width: 120px;' readonly value='{$box['partner_code']}'></td>";
echo "<td width='4%'></td>";
echo "<td width='12%'>Box Didalam</td>";
echo "<td width='36%'><input style='width: 120px;' readonly value='{$box['inside']}'></td>";
echo "</tr>";
echo "<tr>";
echo "<td>Nama Customer</td>";
echo "<td><input style='width: 300px;' readonly value='{$box['partner_name']}'></td>";
echo "<td></td>";
echo "<td>Box Diluar</td>";
echo "<td><input style='width: 120px;' readonly value='{$box['outside']}'></td>";
echo "</tr>";
echo "<tr>";
echo "<td colspan='3'></td>";
echo "<td>Total Box</td>";
echo "<td><input style='width: 120px;' readonly value='{$box['total']}'></td>";
echo "</tr>";
echo "</table>";
echo "</div>";

require_once 'Structures/DataGrid.php';
require_once 'HTML/Table.php';

function print_lokasi($data) {
    if ($data['record']['location'] == 'I') {
        return 'Didalam';
    } else {
        return 'Diluar';
    }
}

if (is_array($_SESSION[$GLOBALS['APP_ID']]['view.box-detail']['columns'])) {
    $cgx_def_columns = $_SESSION[$GLOBALS['APP_ID']]['view.box-detail']['columns'];
} else {
    $cgx_def_columns = array(
//        'm_box_id' => 1,
//        'update_date' => 1,
        'm_box_inout_date' => 1,
        'box_code' => 1,
        'box_number' => 1,
        'box_size' => 1,
        'pipe_size' => 1,
        'kapasitas_box' => 1,
        'location' => 1,
        'partner_name' => 1,        
    );
    $_SESSION[$GLOBALS['APP_ID']]['view.box-detail']['columns'] = $cgx_def_columns;
}

$cgx_sql = "SELECT 

            m_box.* ,
            max(m_box_inout.m_box_inout_date) m_box_inout_date
            
            FROM m_box 

            LEFT JOIN m_box_inout_line ON m_box.m_box_id = m_box_inout_line.m_box_id 
            LEFT JOIN m_box_inout ON m_box_inout_line.m_box_inout_id = m_box_inout.m_box_inout_id 

            WHERE m_box.c_bpartner_id = '{$_REQUEST['id']}'
            
            GROUP BY m_box.m_box_id";
            
$cgx_datagrid = new Structures_DataGrid($cgx_max_rows);
$cgx_options = array('dsn' => $cgx_dsn);

if ($_SESSION[$GLOBALS['APP_ID']]['view.box-detail']['error']) {
    echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['view.box-detail']['error']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['view.box-detail']['error']);
}

if ($_SESSION[$GLOBALS['APP_ID']]['view.box-detail']['info']) {
    echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['view.box-detail']['info']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['view.box-detail']['info']);
}


$cgx_test = $cgx_datagrid->bind($cgx_sql, $cgx_options);
if (PEAR::isError($cgx_test)) {
    echo $cgx_test->getMessage();
}

if ($cgx_def_columns['m_box_inout_date'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Tanggal', 'm_box_inout_date', 'm_box_inout_date', array('align' => 'center'), NULL, "cgx_format_date()"));
if ($cgx_def_columns['box_code'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Box Code', 'box_code', 'box_code', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['box_number'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Box Number', 'box_number', 'box_number', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['box_size'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Box Size', 'box_size', 'box_size', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['pipe_size'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Pipe Size', 'pipe_size', 'pipe_size', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['kapasitas_box'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Kapasitas Box', 'kapasitas_box', 'kapasitas_box', array('align' => 'right'), NULL, NULL));
if ($cgx_def_columns['location'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Lokasi', 'location', 'location', array('align' => 'left'), NULL, "print_lokasi()"));
//if ($cgx_def_columns['partner_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Lokasi Customer', 'partner_name', 'partner_name', array('align' => 'left'), NULL, NULL));


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

echo "<br><input type='button' value='Kembali' onclick=\"window.location = 'module.php?m=view.box';\">";

?>