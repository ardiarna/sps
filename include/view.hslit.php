<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 05/12/2013 13:19:29
 */


echo "<div class='title'>Stock Slit</div>";

require_once 'Structures/DataGrid.php';
require_once 'HTML/Table.php';

if (is_array($_SESSION[$GLOBALS['APP_ID']]['view.hslit']['columns'])) {
    $cgx_def_columns = $_SESSION[$GLOBALS['APP_ID']]['view.hslit']['columns'];
} else {
    $cgx_def_columns = array(
        'no_coil' => 1,
        'no_lot' => 1,
        'weight' => 1,
        'od' => 1,
        'thickness' => 1,
        'spec' => 1,
        'quantity' => 1
        
    );
    $_SESSION[$GLOBALS['APP_ID']]['view.hslit']['columns'] = $cgx_def_columns;
}

$cgx_sql = "
SELECT
C.spec
,C.thickness
,C.od
,B.no_coil
,B.no_lot
,A.weight
,A.quantity

FROM
m_coil_slit AS A JOIN m_coil AS B ON (A.m_coil_id =  B.m_coil_id)
                 LEFT JOIN m_product AS C ON (A.m_product_id = C.m_product_id)
            
WHERE 1=1

";

$cgx_sql_filter_od = "
SELECT
C.od
,C.od

FROM
m_coil_slit AS A JOIN m_coil AS B ON (A.m_coil_id =  B.m_coil_id)
                 LEFT JOIN m_product AS C ON (A.m_product_id = C.m_product_id)
GROUP BY
od

ORDER BY
od ASC
";

$cgx_sql_filter_thickness = "
SELECT
C.thickness
,C.thickness

FROM
m_coil_slit AS A JOIN m_coil AS B ON (A.m_coil_id =  B.m_coil_id)
                 LEFT JOIN m_product AS C ON (A.m_product_id = C.m_product_id)
GROUP BY
thickness

ORDER BY
thickness ASC
";

$cgx_sql_filter_spec = "
SELECT
C.spec
,C.spec

FROM
m_coil_slit AS A JOIN m_coil AS B ON (A.m_coil_id =  B.m_coil_id)
                 LEFT JOIN m_product AS C ON (A.m_product_id = C.m_product_id)
GROUP BY
spec

ORDER BY
spec ASC
";

$cgx_sql_num_row = "
    
    SELECT
    A.m_coil_id
    ,IF(A.`status` = 'O','0',A.weight) AS weight_2
    
    FROM
    m_coil AS A LEFT JOIN m_product AS B ON (A.m_product_id = B.m_product_id)

WHERE 1=1 AND A.`status` = 'I'

GROUP BY
A.no_lot, A.no_lot

";

$result = mysql_query($cgx_sql_num_row);
$num_row = mysql_num_rows($result);
//echo $num_row;

$cgx_sum_berat = "
SELECT 
SUM(A.weight) AS total_berat

FROM
m_coil_slit AS A JOIN m_coil AS B ON (A.m_coil_id =  B.m_coil_id)
                 LEFT JOIN m_product AS C ON (A.m_product_id = C.m_product_id)
WHERE 1=1
";

$cgx_datagrid = new Structures_DataGrid($cgx_max_rows);
$cgx_options = array('dsn' => $cgx_dsn);

$cgx_search = $_REQUEST['search'];
$cgx_search1 = $_REQUEST['search1'];

$cgx_filter_row = urldecode($_REQUEST['row']);

$cgx_filter1 = urldecode($_REQUEST['f1']);
$cgx_filter2 = urldecode($_REQUEST['f2']);
$cgx_filter3 = urldecode($_REQUEST['f3']);



echo "<form name='frmFILTER' action='{$_SERVER['SCRIPT_NAME']}'>\n";
echo "<input type='hidden' name='m' value='{$_REQUEST['m']}'>\n";
echo "<table id='bar' class='datagrid_bar' width='100%' border=0><tr>\n";
echo "<td align='left' width='80'>"
. "Kode Coil&nbsp&nbsp&nbsp<input type='text'style='width: 200px; text-align: left;' name='search' value=\"{$cgx_search}\">&nbsp&nbsp&nbsp"
. "Nomor Coil&nbsp&nbsp&nbsp<input type='text'style='width: 200px; text-align: left;' name='search1' value=\"{$cgx_search1}\">"
. "&nbsp&nbsp&nbsp<input title='Cari' type='image' src='images/icon_search.png' border='0' style='padding-right: 20px;'></td>\n";

echo "<td width='1' align='right' class='datagrid_bar_icon'><a title='Export semua data ke CSV' href='action/view.hslit.php?mode=export-all'><img border='0' src='images/icon_csv.png'></a>&nbsp&nbsp&nbsp<a title='Kustomisasi kolom' href='javascript:customizeColumn(true);'><img border='0' src='images/icon_columns.png'></a></td>\n";
echo "</tr><tr>"
. "<td>Lebar " . cgx_filter('f1', $cgx_sql_filter_od, $cgx_filter1, TRUE, "class='form-control input-sm'") . "&nbsp&nbsp&nbsp"
. "Tebal " . cgx_filter('f2', $cgx_sql_filter_thickness, $cgx_filter2, TRUE, "class='form-control input-sm'") . "&nbsp&nbsp&nbsp"
. "Jenis Bahan " . cgx_filter('f3', $cgx_sql_filter_spec, $cgx_filter3, TRUE, "class='form-control input-sm'") . "&nbsp&nbsp&nbsp </td>"
        . "<tr>";
echo "</tr></table>\n";
echo "</form>\n";

echo "<form name='frmCUSTOMIZE' action='action/cgx_customize.php' method='post'>\n";
echo "<input type='hidden' name='back' value='" . urlencode($_SERVER['REQUEST_URI']) . "'>\n";
echo "<input type='hidden' name='dg_name' value='view.hslit'>\n";
echo "<input type='hidden' name='col[m_coil_slit_id]' value='on'>\n";
echo "<table id='columns' class='datagrid_bar' style='display: none;'><tr>\n";
echo "<td width='99%' valign='top'>\n";

//echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_m_coil_id' name='col[m_coil_id]' type='checkbox'></td><td width='99%'><label for='col_m_coil_id'>Coil ID</label></td></tr></table>\n";

echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['no_coil'] == 1 ? ' checked' : '') . " id='col_no_coil' name='col[no_coil]' type='checkbox'></td><td width='99%'><label for='col_no_coil'>No Coil</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['no_lot'] == 1 ? ' checked' : '') . " id='col_no_lot' name='col[no_lot]' type='checkbox'></td><td width='99%'><label for='col_no_lot'>Kode Coil</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['weight'] == 1 ? ' checked' : '') . " id='col_weight' name='col[weight]' type='checkbox'></td><td width='99%'><label for='col_weight'>Berat</label></td></tr></table>\n";

echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['od'] == 1 ? ' checked' : '') . " id='col_od' name='col[od]' type='checkbox'></td><td width='99%'><label for='col_od'>Lebar</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['thickness'] == 1 ? ' checked' : '') . " id='col_thickness' name='col[thickness]' type='checkbox'></td><td width='99%'><label for='col_thickness'>Tebal</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['spec'] == 1 ? ' checked' : '') . " id='col_spec' name='col[spec]' type='checkbox'></td><td width='99%'><label for='col_spec'>Jenis Bahan</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['quantity'] == 1 ? ' checked' : '') . " id='col_quantity' name='col[quantity]' type='checkbox'></td><td width='99%'><label for='col_quantity'>Qty.</label></td></tr></table>\n";


echo "</td>\n";
echo "<td width='1' valign='top'><input type='submit' value='Simpan'></td>\n";
echo "<td width='1' valign='top'><input type='button' value='Batalkan' onclick='customizeColumn(false);'></td>\n";
echo "</tr></table>\n";
echo "</form>\n";
?>
<script type="text/javascript">

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
</script>
<?php


if (strlen($cgx_filter1) > 0) $cgx_sql .= " AND C.od = '" . mysql_escape_string($cgx_filter1) . "'";  
if (strlen($cgx_filter2) > 0) $cgx_sql .= " AND C.thickness = '" . mysql_escape_string($cgx_filter2) . "'";
if (strlen($cgx_filter3) > 0) $cgx_sql .= " AND C.spec = '" . mysql_escape_string($cgx_filter3) . "'";
if ($cgx_search) $cgx_sql .= " AND B.no_lot = '" . mysql_escape_string($cgx_search) . "'";
if ($cgx_search1) $cgx_sql .= " AND B.no_coil = '" . mysql_escape_string($cgx_search1) . "'";
//if ($cgx_filter_row) $cgx_sql .= " AND A.`status` = 'I'";
//$cgx_sql .= " GROUP BY B.no_lot, B.no_lot";

if (strlen($cgx_filter1) > 0) $cgx_sum_berat .= " AND C.od = '" . mysql_escape_string($cgx_filter1) . "'";  
if (strlen($cgx_filter2) > 0) $cgx_sum_berat .= " AND C.thickness = '" . mysql_escape_string($cgx_filter2) . "'";
if (strlen($cgx_filter3) > 0) $cgx_sum_berat .= " AND C.spec = '" . mysql_escape_string($cgx_filter3) . "'";
if ($cgx_search) $cgx_sum_berat .= " AND B.no_lot = '" . mysql_escape_string($cgx_search) . "'";
if ($cgx_search1) $cgx_sum_berat .= " AND B.no_coil = '" . mysql_escape_string($cgx_search1) . "'";
//if ($cgx_filter_row) $cgx_sum_berat .= " AND A.`status` = 'I'";
//$cgx_sum_berat .= " GROUP BY B.no_lot, B.no_lot";

if ($_SESSION[$GLOBALS['APP_ID']]['view.hslit']['error']) {
    echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['view.hslit']['error']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['view.hcoil']['error']);
}

if ($_SESSION[$GLOBALS['APP_ID']]['view.hslit']['info']) {
    echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['view.hslit']['info']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['view.hslit']['info']);
}

//print_r($cgx_sql);
$cgx_test = $cgx_datagrid->bind($cgx_sql, $cgx_options);

if (PEAR::isError($cgx_test)) {
    echo $cgx_test->getMessage();
}

if ($cgx_def_columns['m_product_id'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Product Id.', 'm_product_id', 'm_product_id', array('align' => 'left'), NULL, NULL));

if ($cgx_def_columns['spec'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Jenis Bahan', 'spec', 'spec', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['thickness'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Tebal', 'thickness', 'thickness', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['od'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Lebar', 'od', 'od', array('align' => 'left'), NULL, NULL));

if ($cgx_def_columns['no_coil'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Nomor Coil', 'no_coil', 'no_coil', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['no_lot'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Kode Coil', 'no_lot', 'no_lot', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['weight'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Berat', 'weight', 'weight', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['quantity'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Qty', 'quantity', 'quantity', array('align' => 'left'), NULL, NULL));


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

$cgx_data_sum = cgx_fetch_table($cgx_sum_berat);

echo "<div class='tbl-header-box' style='font-size: 12px; border: 1px solid #ccc; margin-top: 10px;'>";
echo "  <table class=''>";
echo "  <tr>";
echo "      <td><b>Total Inventory Berat</b></td>";
echo "      <td width='10' align='center'>:</td>";
echo "      <td align='right'><b>".  number_format($cgx_data_sum["total_berat"], 2)." (Kg)</b></td>";
echo "      <td width='100px;'>&nbsp;</td>";
echo "  </tr>";
echo "  </table>";
echo "</div>";


?>
