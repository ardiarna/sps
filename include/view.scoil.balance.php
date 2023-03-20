<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 05/12/2013 14:35:39
 */


echo "<div class='title'>Stock Coil - Detail Balance</div>";

require_once 'Structures/DataGrid.php';
require_once 'HTML/Table.php';

if (is_array($_SESSION[$GLOBALS['APP_ID']]['view.scoil.balance']['columns'])) {
    $cgx_def_columns = $_SESSION[$GLOBALS['APP_ID']]['view.scoil.balance']['columns'];
} else {
    $cgx_def_columns = array(
        'c_order_id' => 1,
        'document_no' => 1,
        'remark' => 1,
        'partner_name' => 1,
        'm_inout_date' => 1,
        'no_kendaraan' => 1,
        'no_lot' => 1,
        'weight' => 1,
        'no_coil' => 1,
    );
    $_SESSION[$GLOBALS['APP_ID']]['view.scoil.balance']['columns'] = $cgx_def_columns;
}

//echo "go";

$cgx_sql = "
SELECT
 A.m_product_id
,A.no_lot
,A.weight
,A.no_coil
,C.document_no
,C.m_inout_date
,C.sj_date
,C.no_kendaraan
,D.c_order_id
,D.remark
,E.partner_name
,A.m_in_id 

FROM

m_coil AS A LEFT JOIN m_inout_line AS B ON (A.m_in_id = B.m_inout_line_id)
            LEFT JOIN m_inout AS C ON (C.m_inout_id = B.m_inout_id)
            LEFT JOIN c_order AS D ON (C.c_order_id = D.c_order_id)            
	    LEFT JOIN c_bpartner AS E ON (D.c_bpartner_id = E.c_bpartner_id)
WHERE 
A.m_product_id = '{$_REQUEST['id_product']}'

GROUP BY
 A.m_product_id
,A.no_lot
,A.weight
,A.no_coil
,C.m_inout_date
,C.sj_date
,D.c_order_id
,D.remark
,E.partner_name

 ";

$cgx_sql_sum = "
SELECT
 
SUM(A.weight) AS total_berat

FROM

m_coil AS A LEFT JOIN m_inout_line AS B ON (A.m_in_id = B.m_inout_line_id)
            LEFT JOIN m_inout AS C ON (C.m_inout_id = B.m_inout_id)
            LEFT JOIN c_order AS D ON (C.c_order_id = D.c_order_id)            
	    LEFT JOIN c_bpartner AS E ON (D.c_bpartner_id = E.c_bpartner_id)
WHERE 
A.m_product_id = '{$_REQUEST['id_product']}'
 ";

$cgx_datagrid = new Structures_DataGrid($cgx_max_rows);
$cgx_options = array('dsn' => $cgx_dsn);
$cgx_datagrid->setDefaultSort(array('m_inout_date' => 'DESC'));

echo "<form name='frmFILTER' action='{$_SERVER['SCRIPT_NAME']}'>\n";
echo "<input type='hidden' name='m' value='{$_REQUEST['m']}'>\n";
echo "<table id='bar' class='datagrid_bar' width='100%'><tr>\n";
echo "<td align='right'>Item Number : </td>\n";
echo "<td align='left'>{$_REQUEST['product_code']}</td>\n";
echo "<td align='right'>Spec : </td>\n";
echo "<td align='left'>{$_REQUEST['spec']}</td>\n";
echo "<td align='right'>Tebal : </td>\n";
echo "<td align='left'>{$_REQUEST['thickness']}</td>\n";
echo "<td align='right'>Lebar : </td>\n";
echo "<td align='left'>{$_REQUEST['od']}</td>\n";
echo "<td align='right'>Tanggal : </td>\n";
echo "<td align='left'>{$_REQUEST['m_inout_date']}</td>\n";
echo "<td></td>\n";
echo "<td align='right'><input type='button' value='Kembali' onclick=\"window.location = 'module.php?m=view.scoil'\">";
//echo "<td width='1' class='datagrid_bar_icon'><a title='Kustomisasi kolom' href='javascript:customizeColumn(true);'><img border='0' src='images/icon_columns.png'></a></td>\n";
echo "</tr></table>\n";
echo "</form>\n";

?>
<script type="text/javascript">
</script>
<?php

//print_r($cgx_sql);
$cgx_test = $cgx_datagrid->bind($cgx_sql, $cgx_options);
if (PEAR::isError($cgx_test)) {
    echo $cgx_test->getMessage();
}

//if ($cgx_def_columns['c_order_id'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('ID. Order', 'c_order_id', 'c_order_id', array('align' => 'left'), NULL, NULL));

if ($cgx_def_columns['no_lot'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Kode Coil', 'no_lot', 'product_code', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['no_coil'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('No. Coil', 'no_coil', 'no_coil', array('align' => 'right'), NULL, NULL));
if ($cgx_def_columns['weight'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Berat', 'weight', 'weight', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['document_no'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('No. Receipt', 'document_no', 'document_no', array('align' => 'center'), NULL, NULL));
if ($cgx_def_columns['m_inout_date'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Tgl. Masuk', 'm_inout_date', 'm_inout_date', array('align' => 'left'), NULL, "cgx_format_date()"));
if ($cgx_def_columns['no_kendaraan'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('No. Surat Jalan', 'no_kendaraan', 'no_kendaraan', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['sj_date'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Tgl. Surat Jalan', 'sj_date', 'sj_date', array('align' => 'right'), NULL, "cgx_format_date()"));
if ($cgx_def_columns['remark'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('No. Contract', 'remark', 'remark', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['partner_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Vendor', 'partner_name', 'partner_name', array('align' => 'left'), NULL, NULL));

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

$cgx_data_sum = cgx_fetch_table($cgx_sql_sum);

echo "<div class='tbl-header-box' style='font-size: 12px; border: 1px solid #ccc; margin-top: 10px;'>";
echo "  <table class=''>";
echo "  <tr>";
echo "      <td><b>Total Berat</b></td>";
echo "      <td width='10' align='center'>:</td>";
echo "      <td align='right'><b>".  number_format($cgx_data_sum["total_berat"], 2)."</b></td>";
echo "      <td width='100px;'>&nbsp;</td>";
echo "  </tr>";
echo "  </table>";
echo "</div>";

?>
