<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 05/12/2013 14:06:29
 */


echo "<div class='title'>SPK</div>";

require_once 'Structures/DataGrid.php';
require_once 'HTML/Table.php';

if (is_array($_SESSION[$GLOBALS['APP_ID']]['view.spk']['columns'])) {
    $cgx_def_columns = $_SESSION[$GLOBALS['APP_ID']]['view.spk']['columns'];
} else {
    $cgx_def_columns = array(
        'document_no' => 1,
        'spk_date' => 1,
        'product_code' => 1,
        'product_name' => 1,
        'machine_name' => 1,
        'quantity' => 1,
        'lengthm' => 1,
    );
    $_SESSION[$GLOBALS['APP_ID']]['view.spk']['columns'] = $cgx_def_columns;
}

$cgx_sql = "SELECT c_spk.* , c_spk_line.c_spk_line_id, c_spk_line.quantity, c_wo.document_no wo, machine_name, mp.product_code, mp.product_name, mp.spec, mp.od, mp.thickness, mp.length,
mat.length lengthm, (c_spk_line.quantity / m_material_requirement.multipleqty) quantity_mat, c_order.document_no so, reference_no, remark, partner_name, c_wo_line.m_product_id
FROM c_spk 
JOIN c_spk_line ON(c_spk.c_spk_id=c_spk_line.c_spk_id)
JOIN c_wo ON(c_spk.c_wo_id=c_wo.c_wo_id)
JOIN m_machine ON(c_spk.m_machine_id=m_machine.m_machine_id)
JOIN c_wo_line ON(c_spk_line.c_wo_line_id=c_wo_line.c_wo_line_id)
JOIN c_production_plan ON(c_wo_line.c_production_plan_id=c_production_plan.c_production_plan_id)
JOIN c_delivery_plan ON (c_production_plan.plan_ref = c_delivery_plan.c_delivery_plan_id)
JOIN c_order_line ON (c_delivery_plan.order_line_ref = c_order_line.c_order_line_id)
JOIN c_order USING (c_order_id)
LEFT JOIN c_bpartner ON (c_order.c_bpartner_id = c_bpartner.c_bpartner_id)
JOIN m_product mp ON(c_wo_line.m_product_id=mp.m_product_id)
LEFT JOIN m_material_requirement ON (c_wo_line.m_product_id=m_material_requirement.m_product_fg)
LEFT JOIN m_product mat ON (m_material_requirement.m_product_material=mat.m_product_id)
WHERE 1=1 ";
$cgx_sql .= "AND " . org_filter_trx('c_production_plan.app_org_id');


$cgx_sqltotal = "SELECT sum(c_spk_line.quantity) as quantity 
FROM c_spk 
JOIN c_spk_line ON(c_spk.c_spk_id=c_spk_line.c_spk_id)
JOIN c_wo ON(c_spk.c_wo_id=c_wo.c_wo_id)
JOIN m_machine ON(c_spk.m_machine_id=m_machine.m_machine_id)
JOIN c_wo_line ON(c_spk_line.c_wo_line_id=c_wo_line.c_wo_line_id)
JOIN c_production_plan ON(c_wo_line.c_production_plan_id=c_production_plan.c_production_plan_id)
JOIN c_delivery_plan ON (c_production_plan.plan_ref = c_delivery_plan.c_delivery_plan_id)
JOIN c_order_line ON (c_delivery_plan.order_line_ref = c_order_line.c_order_line_id)
JOIN c_order USING (c_order_id)
LEFT JOIN c_bpartner ON (c_order.c_bpartner_id = c_bpartner.c_bpartner_id)
JOIN m_product mp ON(c_wo_line.m_product_id=mp.m_product_id)
LEFT JOIN m_material_requirement ON (c_wo_line.m_product_id=m_material_requirement.m_product_fg)
LEFT JOIN m_product mat ON (m_material_requirement.m_product_material=mat.m_product_id)
WHERE 1=1 ";
$cgx_sqltotal .= "AND " . org_filter_trx('c_production_plan.app_org_id');



$cgx_datagrid = new Structures_DataGrid($cgx_max_rows);
$cgx_options = array('dsn' => $cgx_dsn);

$mesin = $_REQUEST['mesin'];
$date_f = $_REQUEST['date_f'];
$date_t = $_REQUEST['date_t'];
$item_number = $_REQUEST['item_number'];
$document_no = $_REQUEST['document_no'];

echo "<form name='frmFILTER' action='{$_SERVER['SCRIPT_NAME']}'>\n";
echo "<input type='hidden' name='m' value='{$_REQUEST['m']}'>\n";
echo "<table id='bar' class='datagrid_bar' width='100%' border=0><tr>\n";
echo "<td align='right'>No. SPK</td>\n";
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
echo "<td align='right'>Mesin</td>\n";
echo "<td>" . cgx_filter('mesin', "SELECT m_machine_id, machine_name FROM m_machine WHERE " . org_filter_master() . " ORDER BY machine_name", $mesin, TRUE) . "</td>\n";
echo "</tr></table>\n";
echo "</form>\n";

echo "<form name='frmCUSTOMIZE' action='action/cgx_customize.php' method='post'>\n";
echo "<input type='hidden' name='back' value='" . urlencode($_SERVER['REQUEST_URI']) . "'>\n";
echo "<input type='hidden' name='dg_name' value='view.spk'>\n";
echo "<input type='hidden' name='col[document_no]' value='on'>\n";
echo "<input type='hidden' name='col[spk_date]' value='on'>\n";
echo "<input type='hidden' name='col[product_code]' value='on'>\n";
echo "<table id='columns' class='datagrid_bar' style='display: none;'><tr>\n";
echo "<td width='99%' valign='top'>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_document_no' name='col[document_no]' type='checkbox'></td><td width='99%'><label for='col_document_no'>No. SPK</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_spk_date' name='col[spk_date]' type='checkbox'></td><td width='99%'><label for='col_spk_date'>Tanggal</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['wo'] == 1 ? ' checked' : '') . " id='col_wo' name='col[wo]' type='checkbox'></td><td width='99%'><label for='col_wo'>No. W/O</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['m_product_id'] == 1 ? ' checked' : '') . " id='col_m_product_id' name='col[m_product_id]' type='checkbox'></td><td width='99%'><label for='col_m_product_id'>Product ID</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_product_code' name='col[product_code]' type='checkbox'></td><td width='99%'><label for='col_product_code'>Item Number</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['spec'] == 1 ? ' checked' : '') . " id='col_spec' name='col[spec]' type='checkbox'></td><td width='99%'><label for='col_spec'>Spec</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['od'] == 1 ? ' checked' : '') . " id='col_weight' name='col[od]' type='checkbox'></td><td width='99%'><label for='col_weight'>OD</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['thickness'] == 1 ? ' checked' : '') . " id='col_thickness' name='col[thickness]' type='checkbox'></td><td width='99%'><label for='col_thickness'>Thickness</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['length'] == 1 ? ' checked' : '') . " id='col_length' name='col[length]' type='checkbox'></td><td width='99%'><label for='col_length'>Length</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['product_name'] == 1 ? ' checked' : '') . " id='col_product_name' name='col[product_name]' type='checkbox'></td><td width='99%'><label for='col_product_name'>Nama Barang</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['lengthm'] == 1 ? ' checked' : '') . " id='col_lengthm' name='col[lengthm]' type='checkbox'></td><td width='99%'><label for='col_lengthm'>Long Pipe</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['quantity_mat'] == 1 ? ' checked' : '') . " id='col_quantity_mat' name='col[quantity_mat]' type='checkbox'></td><td width='99%'><label for='col_quantity_mat'>Qty LP</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['so'] == 1 ? ' checked' : '') . " id='col_so' name='col[so]' type='checkbox'></td><td width='99%'><label for='col_so'>No. SC</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['partner_name'] == 1 ? ' checked' : '') . " id='col_partner_name' name='col[partner_name]' type='checkbox'></td><td width='99%'><label for='col_partner_name'>Customer</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['machine_name'] == 1 ? ' checked' : '') . " id='col_machine_name' name='col[machine_name]' type='checkbox'></td><td width='99%'><label for='col_machine_name'>Mesin</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['quantity'] == 1 ? ' checked' : '') . " id='col_quantity' name='col[quantity]' type='checkbox'></td><td width='99%'><label for='col_quantity'>Qty Rec</label></td></tr></table>\n";
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
    form.setAttribute("action", "action/view.spk.php");

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "mode");
    hiddenField.setAttribute("value", "export-all");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "mesin");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['mesin']; ?>");
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

if ($mesin) $cgx_sql .= " AND c_spk.m_machine_id = '" . mysql_escape_string($mesin) . "'";
if ($document_no) $cgx_sql .= " AND c_spk.document_no LIKE '%" . mysql_escape_string($document_no) . "%'";
if ($item_number) $cgx_sql .= " AND mp.product_code LIKE '%" . mysql_escape_string($item_number) . "%'";
if ($date_f) $cgx_sql .= " AND spk_date >= '" . npl_dmy2ymd($date_f) . "'";
if ($date_t) $cgx_sql .= " AND spk_date <= '" . npl_dmy2ymd($date_t) . "'";


if ($mesin) $cgx_sqltotal .= " AND c_spk.m_machine_id = '" . mysql_escape_string($mesin) . "'";
if ($document_no) $cgx_sqltotal .= " AND c_spk.document_no LIKE '%" . mysql_escape_string($document_no) . "%'";
if ($item_number) $cgx_sqltotal .= " AND mp.product_code LIKE '%" . mysql_escape_string($item_number) . "%'";
if ($date_f) $cgx_sqltotal .= " AND spk_date >= '" . npl_dmy2ymd($date_f) . "'";
if ($date_t) $cgx_sqltotal .= " AND spk_date <= '" . npl_dmy2ymd($date_t) . "'";



$cgx_test = $cgx_datagrid->bind($cgx_sql, $cgx_options);
if (PEAR::isError($cgx_test)) {
    echo $cgx_test->getMessage();
}

if ($cgx_def_columns['document_no'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Nomor SPK', 'document_no', 'document_no', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['spk_date'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Tanggal', 'spk_date', 'spk_date', array('align' => 'center'), NULL, "cgx_format_date()"));
if ($cgx_def_columns['wo'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Nomor W/O', 'wo', 'wo', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['m_product_id'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Product ID', 'm_product_id', 'm_product_id', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['product_code'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Item Number', 'product_code', 'product_code', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['spec'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Spec', 'spec', 'spec', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['od'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('OD', 'od', 'od', array('align' => 'right'), NULL, NULL));
if ($cgx_def_columns['thickness'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Thickness', 'thickness', 'thickness', array('align' => 'right'), NULL, NULL));
if ($cgx_def_columns['length'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Length', 'length', 'length', array('align' => 'right'), NULL, NULL));
if ($cgx_def_columns['product_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Nama Barang', 'product_name', 'product_name', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['lengthm'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Long Pipe', 'lengthm', 'lengthm', array('align' => 'right'), NULL, NULL));
if ($cgx_def_columns['quantity_mat'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Qty LP', 'quantity_mat', 'quantity_mat', array('align' => 'right'), NULL, NULL));
if ($cgx_def_columns['so'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('No. SC', 'so', 'so', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['partner_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Customer', 'partner_name', 'partner_name', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['machine_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Mesin', 'machine_name', 'machine_name', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['quantity'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Qty Rec', 'quantity', 'quantity', array('align' => 'right'), NULL, "cgx_format_3digit()"));

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


    $cgx_data_sum = cgx_fetch_table($cgx_sqltotal);

    echo "<div class='tbl-header-box' style='font-size: 12px; border: 1px solid #ccc; margin-top: 10px;'>";
    echo "  <table class=''>";
    echo "  <tr>";
    echo "      <td><b>Total Jumlah Barang</b></td>";
    echo "      <td width='10' align='center'>:</td>";
    echo "      <td align='right'><b>".  number_format($cgx_data_sum["quantity"], 2)."</b></td>";
    echo "      <td width='100px;'>&nbsp;</td>";
    echo "  </tr>";     
    echo "  </table>";
    echo "</div>";

?>