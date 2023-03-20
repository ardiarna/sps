<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 05/12/2013 13:19:29
 */

function cgx_format_status($data) {
    $arr = $STATUS_DELIVERY_TYPE = array(
        'I' => 'O.K Coil',
        'W' => 'Proses W.O',
        'O' => 'OK Slit',
        'SC' => 'SLOW COIL',
        'SS' => 'SLOW SLIT'
    );
    return $arr[$data['record'][$data['fieldName']]];
}

function cgx_purchase_order($data) {
    $href = "module.php?m=trx.puo&pkey[c_order_id]={$data['record']['c_order_id']}";
    $out = "<a href='{$href}'><b>{$data['record']['no_purchase_order']}</b></a>";
    return $out;
}

function cgx_penerimaan_bahan_baku($data) {
    $href = "module.php?m=trx.good_rcp&pkey[m_inout_id]={$data['record']['m_inout_id']}";
    $out = "<a href='{$href}'><b>{$data['record']['no_penerimaan_bahan_baku']}</b></a>";
    return $out;
}

function cgx_wo_slitting($data) {
    $href = "module.php?m=trx.wo_slit&pkey[m_wo_slit_id]={$data['record']['m_wo_slit_id']}";
    $out = "<a href='{$href}'><b>{$data['record']['no_wo_slitting']}</b></a>";
    return $out;
}

function cgx_realisasi_wo($data) {
    $href = "module.php?m=trx.rwos&pkey[m_prod_slit_id]={$data['record']['m_prod_slit_id']}";
    $out = "<a href='{$href}'><b>{$data['record']['no_realisasi_wo']}</b></a>";
    return $out;
}

function cgx_stock_coil($data){
    $href = "module.php?m=view.scoil&q={$data['record']['product_code']}";
    $out = "<a href='{$href}'><b>{$data['record']['product_code']}</b></a>";
    return $out;
}

function raw_slit($data){
    
    if($data['record']['category']=='R'){
        $link = 'view.sraw';
    }
    elseif($data['record']['category']=='S'){
        $link = 'view.sslit';
    }
    
    $href = "module.php?m={$link}&q={$data['record']['product_code']}";
    $out = "<a href='{$href}'><b>{$data['record']['no_lot']}</b></a>";
    return $out;
    
}


echo "<div class='title'>Stock Coil</div>";

require_once 'Structures/DataGrid.php';
require_once 'HTML/Table.php';

if (is_array($_SESSION[$GLOBALS['APP_ID']]['view.hcoil']['columns'])) {
    $cgx_def_columns = $_SESSION[$GLOBALS['APP_ID']]['view.hcoil']['columns'];
} else {
    $cgx_def_columns = array(
        'no_coil' => 1,
        'no_lot' => 1,
        'weight_2' => 1,
        'od' => 1,
        'thickness' => 1,
        'spec' => 1,
        'status' => 1,
        'm_inout_date' => 1,
        'umur' => 1
        
    );
    $_SESSION[$GLOBALS['APP_ID']]['view.hcoil']['columns'] = $cgx_def_columns;
}

$cgx_sql = "
SELECT
A.m_coil_id
,A.m_product_id
,A.no_coil
,A.no_lot
,A.weight
,A.`status` AS `status`
,IF(A.`status` = 'O','0',A.weight) AS weight_2
,B.product_code
,B.od
,B.thickness
,B.spec
,C.document_no AS no_wo_slitting
,C.m_wo_slit_id
,D.document_no AS no_realisasi_wo
,D.m_prod_slit_id
,F.document_no AS no_penerimaan_bahan_baku
,F.m_inout_id
,H.document_no AS no_purchase_order
,H.c_order_id
,B.category
,I.m_inout_date
,DATEDIFF(CURDATE(),I.m_inout_date) AS umur

FROM
m_coil AS A LEFT JOIN m_product AS B ON (A.m_product_id = B.m_product_id)
	    LEFT JOIN m_wo_slit AS C ON (A.m_wo_slit_id = C.m_wo_slit_id)
            LEFT JOIN m_prod_slit AS D ON (A.m_out_id = D.m_prod_slit_id)
	    LEFT JOIN m_inout_line AS E ON (A.m_in_id = E.m_inout_line_id)	
	    LEFT JOIN m_inout AS F ON (E.m_inout_id = F.m_inout_id)
	    LEFT JOIN c_order_line AS G ON (E.c_order_line_id = G.c_order_line_id)
	    LEFT JOIN c_order AS H ON (G.c_order_id = H.c_order_id)
            LEFT JOIN m_stock_onhand AS I ON (A.m_product_id = I.m_product_id)
            
WHERE 1=1

";

$cgx_sql_filter_od = "
SELECT
B.od
,B.od

FROM
m_coil AS A LEFT JOIN m_product AS B ON (A.m_product_id = B.m_product_id)

GROUP BY
od

ORDER BY
od ASC
";

$cgx_sql_filter_thickness = "
SELECT
B.thickness
,B.thickness

FROM
m_coil AS A LEFT JOIN m_product AS B ON (A.m_product_id = B.m_product_id)

GROUP BY
thickness

ORDER BY
thickness ASC
";

$cgx_sql_filter_spec = "
SELECT
B.spec
,B.spec

FROM
m_coil AS A LEFT JOIN m_product AS B ON (A.m_product_id = B.m_product_id)
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
m_coil AS A LEFT JOIN m_product AS B ON (A.m_product_id = B.m_product_id)

WHERE 1=1 
";

$cgx_sum_slow_coil = "
SELECT 
SUM(A.weight) AS total_berat_slow_coil

FROM
m_coil AS A LEFT JOIN m_product AS B ON (A.m_product_id = B.m_product_id)

WHERE 1=1 AND A.`status` = 'SC'
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

echo "<td width='1' align='right' class='datagrid_bar_icon'><a title='Export semua data ke CSV' href='action/view.hcoil.php?mode=export-all'><img border='0' src='images/icon_csv.png'></a>&nbsp&nbsp&nbsp<a title='Kustomisasi kolom' href='javascript:customizeColumn(true);'><img border='0' src='images/icon_columns.png'></a></td>\n";
echo "</tr><tr>"
. "<td>Lebar " . cgx_filter('f1', $cgx_sql_filter_od, $cgx_filter1, TRUE, "class='form-control input-sm'") . "&nbsp&nbsp&nbsp"
. "Tebal " . cgx_filter('f2', $cgx_sql_filter_thickness, $cgx_filter2, TRUE, "class='form-control input-sm'") . "&nbsp&nbsp&nbsp"
. "Jenis Bahan " . cgx_filter('f3', $cgx_sql_filter_spec, $cgx_filter3, TRUE, "class='form-control input-sm'") . "&nbsp&nbsp&nbsp </td>"
        . "<tr>";
echo "</tr></table>\n";
echo "</form>\n";

echo "<form name='frmCUSTOMIZE' action='action/cgx_customize.php' method='post'>\n";
echo "<input type='hidden' name='back' value='" . urlencode($_SERVER['REQUEST_URI']) . "'>\n";
echo "<input type='hidden' name='dg_name' value='view.hcoil'>\n";
//echo "<input type='hidden' name='col[outstanding]' value='on'>\n";
echo "<input type='hidden' name='col[m_coil_id]' value='on'>\n";
echo "<table id='columns' class='datagrid_bar' style='display: none;'><tr>\n";
echo "<td width='99%' valign='top'>\n";

//echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_m_coil_id' name='col[m_coil_id]' type='checkbox'></td><td width='99%'><label for='col_m_coil_id'>Coil ID</label></td></tr></table>\n";

echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['m_product_id'] == 1 ? ' checked' : '') . " id='col_m_product_id' name='col[m_product_id]' type='checkbox'></td><td width='99%'><label for='col_m_product_id'>Product ID</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['no_coil'] == 1 ? ' checked' : '') . " id='col_no_coil' name='col[no_coil]' type='checkbox'></td><td width='99%'><label for='col_no_coil'>No Coil</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['no_lot'] == 1 ? ' checked' : '') . " id='col_no_lot' name='col[no_lot]' type='checkbox'></td><td width='99%'><label for='col_no_lot'>Kode Coil</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['weight_2'] == 1 ? ' checked' : '') . " id='col_weight_2' name='col[weight_2]' type='checkbox'></td><td width='99%'><label for='col_weight_2'>Berat</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['product_code'] == 1 ? ' checked' : '') . " id='col_product_code' name='col[product_code]' type='checkbox'></td><td width='99%'><label for='col_product_code'>Item Number</label></td></tr></table>\n";

echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['od'] == 1 ? ' checked' : '') . " id='col_od' name='col[od]' type='checkbox'></td><td width='99%'><label for='col_od'>Lebar</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['thickness'] == 1 ? ' checked' : '') . " id='col_thickness' name='col[thickness]' type='checkbox'></td><td width='99%'><label for='col_thickness'>Tebal</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['spec'] == 1 ? ' checked' : '') . " id='col_spec' name='col[spec]' type='checkbox'></td><td width='99%'><label for='col_spec'>Jenis Bahan</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['no_purchase_order'] == 1 ? ' checked' : '') . " id='col_no_purchase_order' name='col[no_purchase_order]' type='checkbox'></td><td width='99%'><label for='col_no_purchase_order'>No. Purchase Order</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['no_penerimaan_bahan_baku'] == 1 ? ' checked' : '') . " id='col_no_penerimaan_bahan_baku' name='col[no_penerimaan_bahan_baku]' type='checkbox'></td><td width='99%'><label for='col_no_penerimaan_bahan_baku'>No. Penerimaan Bahan Baku</label></td></tr></table>\n";

echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['no_wo_slitting'] == 1 ? ' checked' : '') . " id='col_no_wo_slitting' name='col[no_wo_slitting]' type='checkbox'></td><td width='99%'><label for='col_length'>No. W.O Slitting</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['no_realisasi_wo'] == 1 ? ' checked' : '') . " id='col_no_realisasi_wor' name='col[no_realisasi_wo]' type='checkbox'></td><td width='99%'><label for='col_no_realisasi_wo'>No. Realisasi W.O</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['status'] == 1 ? ' checked' : '') . " id='col_status' name='col[status]' type='checkbox'></td><td width='99%'><label for='col_no_realisasi_wo'>Status</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['category'] == 1 ? ' checked' : '') . " id='col_category' name='col[category]' type='checkbox'></td><td width='99%'><label for='col_category'>Category</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['m_inout_date'] == 1 ? ' checked' : '') . " id='col_m_inout_date' name='col[m_inout_date]' type='checkbox'></td><td width='99%'><label for='col_m_inout_date'>Tgl. Masuk Gudang SPS</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['umur'] == 1 ? ' checked' : '') . " id='col_umur' name='col[umur]' type='checkbox'></td><td width='99%'><label for='col_umur'>umur</label></td></tr></table>\n";


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


if (strlen($cgx_filter1) > 0) $cgx_sql .= " AND B.od = '" . mysql_escape_string($cgx_filter1) . "'";  
if (strlen($cgx_filter2) > 0) $cgx_sql .= " AND B.thickness = '" . mysql_escape_string($cgx_filter2) . "'";
if (strlen($cgx_filter3) > 0) $cgx_sql .= " AND B.spec = '" . mysql_escape_string($cgx_filter3) . "'";
if ($cgx_search) $cgx_sql .= " AND A.no_lot = '" . mysql_escape_string($cgx_search) . "'";
if ($cgx_search1) $cgx_sql .= " AND A.no_coil = '" . mysql_escape_string($cgx_search1) . "'";
if ($cgx_filter_row) $cgx_sql .= " AND A.`status` = 'I'";
$cgx_sql .= " GROUP BY A.no_lot, A.no_lot";

if (strlen($cgx_filter1) > 0) $cgx_sum_berat .= " AND B.od = '" . mysql_escape_string($cgx_filter1) . "'";  
if (strlen($cgx_filter2) > 0) $cgx_sum_berat .= " AND B.thickness = '" . mysql_escape_string($cgx_filter2) . "'";
if (strlen($cgx_filter3) > 0) $cgx_sum_berat .= " AND B.spec = '" . mysql_escape_string($cgx_filter3) . "'";
if ($cgx_search) $cgx_sum_berat .= " AND A.no_lot = '" . mysql_escape_string($cgx_search) . "'";
if ($cgx_search1) $cgx_sum_berat .= " AND A.no_coil = '" . mysql_escape_string($cgx_search1) . "'";
 
    $cgx_status_cari = cgx_fetch_table("SELECT `status` FROM m_coil WHERE no_lot = '" . mysql_escape_string($cgx_search) . "' ");
    $cgx_status_cari2 = $cgx_status_cari['status'];
    
    if ($cgx_filter_row){
        $cgx_sum_berat .= " AND A.`status` = '{$cgx_status_cari['status']}' ";
    }
    else{
        $cgx_sum_berat .= " AND A.`status` = 'I'  ";
    }
        
//$cgx_sum_berat .= " GROUP BY A.no_lot, A.no_lot";

if ($_SESSION[$GLOBALS['APP_ID']]['view.hcoil']['error']) {
    echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['view.hcoil']['error']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['view.hcoil']['error']);
}

if ($_SESSION[$GLOBALS['APP_ID']]['view.hcoil']['info']) {
    echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['view.hcoil']['info']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['view.hcoil']['info']);
}

//print_r($cgx_sum_slow_coil);
$cgx_test = $cgx_datagrid->bind($cgx_sql, $cgx_options);

if (PEAR::isError($cgx_test)) {
    echo $cgx_test->getMessage();
}

if ($cgx_def_columns['m_product_id'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Product Id.', 'm_product_id', 'm_product_id', array('align' => 'left'), NULL, NULL));

if ($cgx_def_columns['spec'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Jenis Bahan', 'spec', 'spec', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['thickness'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Tebal', 'thickness', 'thickness', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['od'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Lebar', 'od', 'od', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['m_inout_date'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Tgl. Masuk', 'm_inout_date', 'm_inout_date', array('align' => 'left'), NULL, "cgx_format_date()"));
if ($cgx_def_columns['umur'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Umur', 'umur', 'umur', array('align' => 'left'), NULL, NULL));

if ($cgx_def_columns['no_coil'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Nomor Coil', 'no_coil', 'no_coil', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['no_lot'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Kode Coil', 'no_lot', 'no_lot', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['weight_2'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Berat', 'weight_2', 'weight_2', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['product_code'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Item Number', 'product_code', 'product_code', array('align' => 'left'), NULL, "cgx_stock_coil"));

if ($cgx_def_columns['no_purchase_order'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('No. Purchase Order', 'no_purchase_order', 'no_purchase_order', array('align' => 'left'), NULL, "cgx_purchase_order"));
if ($cgx_def_columns['no_penerimaan_bahan_baku'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('No. Pener. Bahan Baku', 'no_penerimaan_bahan_baku', 'no_penerimaan_bahan_baku', array('align' => 'left'), NULL, "cgx_penerimaan_bahan_baku"));
if ($cgx_def_columns['no_wo_slitting'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('No. W.O Slitting', 'no_wo_slitting', 'no_wo_slitting', array('align' => 'left'), NULL, "cgx_wo_slitting"));
if ($cgx_def_columns['no_realisasi_wo'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('No. Realisasi W.O', 'no_realisasi_wo', 'no_realisasi_wo', array('align' => 'left'), NULL, "cgx_realisasi_wo"));
if ($cgx_def_columns['status'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Status', 'status', 'status', array('align' => 'left'), NULL, "cgx_format_status()"));
if ($cgx_def_columns['category'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Category', 'category', 'category', array('align' => 'left'), NULL, NULL));


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
$cgx_data_sum_slow_coil = cgx_fetch_table($cgx_sum_slow_coil);

function cgx_sum_row($data) {
    $href = "module.php?m=view.hcoil&row=I";
    $out = "<a href='{$href}'><b>.$num_row.</b></a>";
    return $out;
}

echo "<div class='tbl-header-box' style='font-size: 12px; border: 1px solid #ccc; margin-top: 10px;'>";
echo "  <table class=''>";
echo "  <tr>";
echo "      <td><b>Jumlah Coil Yang Belum Teralisasi</b></td>";
echo "      <td width='10' align='center'>:</td>";
//echo "      <td align='right'><b>".cgx_sum_row()." Coil</b></td>";
echo "      <td align='right'><b><a href='module.php?m=view.hcoil&row=I'>".$num_row."<a/> Coil</b></td>";
echo "      <td width='100px;'>&nbsp;</td>";
echo "  </tr>";
echo "  <tr>";
echo "      <td><b>Total Inventory Berat Fast Coil</b></td>";
echo "      <td width='10' align='center'>:</td>";
echo "      <td align='right'><b>".  number_format($cgx_data_sum["total_berat"], 2)." (Kg)</b></td>";
echo "      <td width='100px;'>&nbsp;</td>";
echo "  </tr>";
echo "  <tr>";
echo "      <td><b>Total Inventory Berat Slow Coil</b></td>";
echo "      <td width='10' align='center'>:</td>";
echo "      <td align='right'><b>".  number_format($cgx_data_sum_slow_coil["total_berat_slow_coil"], 2)." (Kg)</b></td>";
echo "      <td width='100px;'>&nbsp;</td>";
echo "  </tr>";
echo "  </table>";
echo "</div>";

$starttime = microtime(true);

//Do your query and stuff here
$endtime = microtime(true);
$duration = $endtime - $starttime; 
//echo $duration;
//echo "Run Query ".date("i:s",$duration)." Seconds";
//-----------------------------------------------------------------------------------------------------------------------------------
    
?>
