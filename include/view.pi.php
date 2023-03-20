<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 05/12/2013 14:06:29
 */


echo "<div class='title'>Physical Inventory</div>";

require_once 'Structures/DataGrid.php';
require_once 'HTML/Table.php';

if (is_array($_SESSION[$GLOBALS['APP_ID']]['view.pi']['columns'])) {
    $cgx_def_columns = $_SESSION[$GLOBALS['APP_ID']]['view.pi']['columns'];
} else {
    $cgx_def_columns = array(
        'product_code' => 1,
        'spec' => 1,
        'od' => 1,
        'thickness' => 1,
        'length' => 1,
        'warehouse_name' => 1,
        'qty_oht' => 1,
        'qty_oh' => 1,
        'qty_count' => 1,
        'qty_adj' => 1,
    );
    $_SESSION[$GLOBALS['APP_ID']]['view.pi']['columns'] = $cgx_def_columns;
}

$cgx_sql = "SELECT mi.document_no, mi.m_inout_date, mp.product_code, mp.product_name, mp.spec, mp.od, mp.thickness, mp.length, mw.warehouse_name, cpl.qty_oht, cpl.qty_oh, cpl.qty_count, cpl.qty_adj
FROM c_pinventory cp JOIN m_inout mi USING(m_inout_id)
JOIN c_pinventory_line cpl USING(c_pinventory_id) 
JOIN m_product mp ON(cpl.m_product_id=mp.m_product_id) 
JOIN m_warehouse mw ON(cpl.m_warehouse_id=mw.m_warehouse_id) 
WHERE mi.m_transaction_type_id = 10 ";
$cgx_sql .= " AND " . org_filter_trx('cp.app_org_id');
//$cgx_sql .= " ORDER BY mi.m_inout_date DESC, mi.m_inout_id DESC, cpl.m_inout_line_id";

$cgx_datagrid = new Structures_DataGrid($cgx_max_rows);
$cgx_options = array('dsn' => $cgx_dsn);

$gudang = $_REQUEST['gudang'];
$date_f = $_REQUEST['date_f'];
$date_t = $_REQUEST['date_t'];
$item_number = $_REQUEST['item_number'];
$document_no = $_REQUEST['document_no'];

echo "<form name='frmFILTER' action='{$_SERVER['SCRIPT_NAME']}'>\n";
echo "<input type='hidden' name='m' value='{$_REQUEST['m']}'>\n";
echo "<table id='bar' class='datagrid_bar' width='100%' border=0><tr>\n";
echo "<td align='right'>No. Penerimaan</td>\n";
echo "<td align='left'><input type='text'style='width: 150px;' name='document_no' value=\"{$document_no}\"></td>\n";
echo "<td align='right'>Item Number</td>\n";
echo "<td align='left'><input type='text'style='width: 150px;' name='item_number' value=\"{$item_number}\"></td>\n";
echo "<td align='right'>Tanggal</td>\n";
echo "<td align='left' width='1'><input type='text'style='width: 120px; text-align: center;' id='date_f' name='date_f' value=\"{$date_f}\"></td>\n";
echo "<td align='center'>s/d</td>\n";
echo "<td align='left' width='1'><input type='text'style='width: 120px; text-align: center;' id='date_t' name='date_t' value=\"{$date_t}\"></td>\n";
echo "<td width='1'><input title='Cari' type='image' src='images/icon_search.png' border='0' style='padding-right: 20px;'></td>\n";
echo "<td></td>\n";
echo "<td width='20'></td>\n";
echo "<td width='1' class='datagrid_bar_icon'><a title='Export semua data ke CSV' href='javascript:exportCSV();'><img border='0' src='images/icon_csv.png'></a></td>\n";
echo "<td width='1' class='datagrid_bar_icon'><a title='Kustomisasi kolom' href='javascript:customizeColumn(true);'><img border='0' src='images/icon_columns.png'></a></td>\n";
echo "</tr><tr>";
echo "<td colspan='2'></td>";
echo "<td align='right'>Gudang</td>\n";
echo "<td>" . cgx_filter('gudang', "SELECT m_warehouse_id, warehouse_name FROM m_warehouse WHERE " . org_filter_master() . " ORDER BY warehouse_name", $gudang, TRUE) . "</td>\n";
echo "</tr></table>\n";
echo "</form>\n";

echo "<form name='frmCUSTOMIZE' action='action/cgx_customize.php' method='post'>\n";
echo "<input type='hidden' name='back' value='" . urlencode($_SERVER['REQUEST_URI']) . "'>\n";
echo "<input type='hidden' name='dg_name' value='view.pi'>\n";
echo "<input type='hidden' name='col[document_no]' value='on'>\n";
echo "<input type='hidden' name='col[m_inout_date]' value='on'>\n";
echo "<input type='hidden' name='col[product_code]' value='on'>\n";
echo "<table id='columns' class='datagrid_bar' style='display: none;'><tr>\n";
echo "<td width='99%' valign='top'>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['document_no'] == 1 ? ' checked' : '') . " id='col_document_no' name='col[document_no]' type='checkbox'></td><td width='99%'><label for='col_document_no'>No Dokumen</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['m_inout_date'] == 1 ? ' checked' : '') . " id='col_m_inout_date' name='col[m_inout_date]' type='checkbox'></td><td width='99%'><label for='col_m_inout_date'>Tanggal Physical</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_product_code' name='col[product_code]' type='checkbox'></td><td width='99%'><label for='col_product_code'>Item Number</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['product_name'] == 1 ? ' checked' : '') . " id='col_product_name' name='col[product_name]' type='checkbox'></td><td width='99%'><label for='col_product_name'>Nama Barang</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['spec'] == 1 ? ' checked' : '') . " id='col_spec' name='col[spec]' type='checkbox'></td><td width='99%'><label for='col_spec'>Spec</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['od'] == 1 ? ' checked' : '') . " id='col_od' name='col[od]' type='checkbox'></td><td width='99%'><label for='col_od'>OD</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['thickness'] == 1 ? ' checked' : '') . " id='col_thickness' name='col[thickness]' type='checkbox'></td><td width='99%'><label for='col_thickness'>Tebal</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['length'] == 1 ? ' checked' : '') . " id='col_length' name='col[length]' type='checkbox'></td><td width='99%'><label for='col_length'>Panjang</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['warehouse_name'] == 1 ? ' checked' : '') . " id='col_warehouse_name' name='col[warehouse_name]' type='checkbox'></td><td width='99%'><label for='col_warehouse_name'>Gudang</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['qty_oht'] == 1 ? ' checked' : '') . " id='col_qty_oht' name='col[qty_oht]' type='checkbox'></td><td width='99%'><label for='col_qty_oht'>Jumlah On Hand Total</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['qty_oh'] == 1 ? ' checked' : '') . " id='col_qty_oh' name='col[qty_oh]' type='checkbox'></td><td width='99%'><label for='col_qty_oh'>Jumlah On Hand Gudang</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['qty_count'] == 1 ? ' checked' : '') . " id='col_qty_count' name='col[qty_count]' type='checkbox'></td><td width='99%'><label for='col_qty_count'>Jumlah Hitung</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['qty_adj'] == 1 ? ' checked' : '') . " id='col_qty_adj' name='col[qty_adj]' type='checkbox'></td><td width='99%'><label for='col_qty_adj'>Adjusment</label></td></tr></table>\n";
echo "</td>\n";
echo "<td width='1' valign='top'><input type='submit' value='Simpan'></td>\n";
echo "<td width='1' valign='top'><input type='button' value='Batalkan' onclick='customizeColumn(false);'></td>\n";
echo "</tr></table>\n";
echo "</form>\n";
?>
<script type="text/javascript">
<!--
$(function() {
    $("#date_f").datepicker({dateFormat: '<?php echo $APP_DATE_FORMAT_JAVA; ?>'});
    $("#date_t").datepicker({dateFormat: '<?php echo $APP_DATE_FORMAT_JAVA; ?>'});
});

function exportCSV() {   
    form = document.createElement("form");
    form.setAttribute("method", "post");
    form.setAttribute("action", "action/view.pi.php");

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "mode");
    hiddenField.setAttribute("value", "export-all");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "gudang");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['gudang']; ?>");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "document_no");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['document_no']; ?>");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "item_number");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['item_number']; ?>");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "date_f");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['date_f']; ?>");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "date_t");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['date_t']; ?>");
    form.appendChild(hiddenField);

    document.body.appendChild(form);
    form.submit();    
}

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

if ($gudang) $cgx_sql .= " AND cpl.m_warehouse_id = '" . mysql_escape_string($gudang) . "'";
if ($document_no) $cgx_sql .= " AND mi.document_no LIKE '%" . mysql_escape_string($document_no) . "%'";
if ($item_number) $cgx_sql .= " AND mp.product_code LIKE '%" . mysql_escape_string($item_number) . "%'";
if ($date_f) $cgx_sql .= " AND mi.m_inout_date >= '" . npl_dmy2ymd($date_f) . "'";
if ($date_t) $cgx_sql .= " AND mi.m_inout_date <= '" . npl_dmy2ymd($date_t) . "'";
// echo $cgx_sql;

$cgx_test = $cgx_datagrid->bind($cgx_sql, $cgx_options);
if (PEAR::isError($cgx_test)) {
    echo $cgx_test->getMessage();
}

if ($cgx_def_columns['document_no'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Nomor Dokumen', 'document_no', 'document_no', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['m_inout_date'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Tgl Physical', 'm_inout_date', 'm_inout_date', array('align' => 'center'), NULL, "cgx_format_date()"));
if ($cgx_def_columns['product_code'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Item Number', 'product_code', 'product_code', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['product_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Nama Barang', 'product_name', 'product_name', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['spec'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Spec', 'spec', 'spec', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['od'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('OD', 'od', 'od', array('align' => 'right'), NULL, NULL));
if ($cgx_def_columns['thickness'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Tebal', 'thickness', 'thickness', array('align' => 'right'), NULL, NULL));
if ($cgx_def_columns['length'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Panjang', 'length', 'length', array('align' => 'right'), NULL, NULL));
if ($cgx_def_columns['warehouse_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Gudang', 'warehouse_name', 'warehouse_name', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['qty_oht'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Jumlah On Hand<br>Total', 'qty_oht', 'qty_oht', array('align' => 'right'), NULL, "cgx_format_3digit()"));
if ($cgx_def_columns['qty_oh'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Jumlah On Hand<br>Gudang', 'qty_oh', 'qty_oh', array('align' => 'right'), NULL, "cgx_format_3digit()"));
if ($cgx_def_columns['qty_count'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Jumlah Hitung', 'qty_count', 'qty_count', array('align' => 'right'), NULL, "cgx_format_3digit()"));
if ($cgx_def_columns['qty_adj'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Adjusment', 'qty_adj', 'qty_adj', array('align' => 'right'), NULL, "cgx_format_3digit()"));

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