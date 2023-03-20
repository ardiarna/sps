<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 13/01/2014 00:26:12
 */

echo "<div class='title'>Sales Order Detail</div>";

function cgx_format_status($data) {
    $arr = array('O' => 'Open', 'C' => 'Close');
    return $arr[$data['record'][$data['fieldName']]];
}

require_once 'Structures/DataGrid.php';
require_once 'HTML/Table.php';

if (is_array($_SESSION[$GLOBALS['APP_ID']]['view.so']['columns'])) {
    $cgx_def_columns = $_SESSION[$GLOBALS['APP_ID']]['view.so']['columns'];
} else {
    $cgx_def_columns = array(
        'document_no' => 1,
        'remark' => 1,
        'reference_no' => 1,
        'partner_name' => 1,
        'product_code' => 1,
        'order_date' => 1,
        'schedule_delivery_date' => 1,
        'order_quantity' => 1,
    );
    $_SESSION[$GLOBALS['APP_ID']]['view.so']['columns'] = $cgx_def_columns;
}

$cgx_sql = "SELECT c_order.*, partner_code, partner_name, m_product.m_product_id, product_code, spec, od, thickness, length, description, 
    schedule_delivery_date, order_quantity, delivered_quantity, (order_quantity - delivered_quantity) as outstanding 
    FROM c_order JOIN c_order_line USING (c_order_id) 
    JOIN c_bpartner USING (c_bpartner_id) 
    JOIN m_product USING (m_product_id) WHERE 1 = 1 AND m_transaction_type_id = '1' ";
if (org() != '1') {  
$cgx_sql .= " AND " . org_filter_trx('c_order.app_org_id');
}

$cgx_sqltotal = "SELECT sum(order_quantity) as order_quantity, sum(return_quantity) as return_quantity, 
    sum(delivered_quantity) as delivery_quantity, sum(order_quantity-delivered_quantity+return_quantity) as outstanding 
    FROM c_order JOIN c_order_line USING (c_order_id) 
    JOIN c_bpartner USING (c_bpartner_id) 
    JOIN m_product USING (m_product_id) WHERE 1 = 1 AND m_transaction_type_id = '1' ";
if (org() != '1') {  
$cgx_sqltotal .= " AND " . org_filter_trx('c_order.app_org_id');
}

$cgx_datagrid = new Structures_DataGrid($cgx_max_rows);
$cgx_options = array('dsn' => $cgx_dsn);

$cgx_filter1 = urldecode($_REQUEST['f1']);
$cgx_search = $_REQUEST['q'];

$order_f = $_REQUEST['order_f'];
$order_t = $_REQUEST['order_t'];
$delivery_f = $_REQUEST['delivery_f'];
$delivery_t = $_REQUEST['delivery_t'];
$customer = $_REQUEST['customer'];
$sc_number = $_REQUEST['sc_number'];
$item_number = $_REQUEST['item_number'];

echo "<form name='frmFILTER' action='{$_SERVER['SCRIPT_NAME']}'>\n";
echo "<input type='hidden' name='m' value='{$_REQUEST['m']}'>\n";
echo "<table id='bar' class='datagrid_bar' width='100%' border='0'><tr>\n";
echo "<td align='right'>Status</td>\n";
echo "<td>" . cgx_filter('f1', array('O' => 'Open', 'C' => 'Close'), $cgx_filter1, TRUE) . "</td>\n";
echo "<td align='right'>Customer</td>\n";
echo "<td align='left'><input type='text'style='width: 150px;' name='customer' value=\"{$customer}\"></td>\n";
echo "<td align='right'>Tanggal Order</td>\n";
echo "<td align='left' width='1'><input type='text'style='width: 120px; text-align: center;' id='order_f' name='order_f' value=\"{$order_f}\"></td>\n";
echo "<td align='center'>s/d</td>\n";
echo "<td align='left' width='1'><input type='text'style='width: 120px; text-align: center;' id='order_t' name='order_t' value=\"{$order_t}\"></td>\n";
echo "<td width='1'><input title='Cari' type='image' src='images/icon_search.png' border='0' style='padding-right: 20px;'></td>\n";
echo "<td></td>\n";
echo "<td width='20'></td>\n";
echo "<td width='1' class='datagrid_bar_icon'><a title='Export semua data ke CSV' href='javascript:exportCSV();'><img border='0' src='images/icon_csv.png'></a></td>\n";
echo "<td width='1' class='datagrid_bar_icon'><a title='Kustomisasi kolom' href='javascript:customizeColumn(true);'><img border='0' src='images/icon_columns.png'></a></td>\n";
echo "</tr><tr>";
echo "<td align='right'>Remark</td>\n";
echo "<td align='left'><input type='text'style='width: 150px;' name='sc_number' value=\"{$sc_number}\"></td>\n";
echo "<td align='right'>Item Number</td>\n";
echo "<td align='left'><input type='text'style='width: 150px;' name='item_number' value=\"{$item_number}\"></td>\n";
echo "<td align='right'>Jadwal Kirim</td>\n";
echo "<td align='left' width='1'><input type='text'style='width: 120px; text-align: center;' id='delivery_f' name='delivery_f' value=\"{$delivery_f}\"></td>\n";
echo "<td align='center'>s/d</td>\n";
echo "<td align='left' width='1'><input type='text'style='width: 120px; text-align: center;' id='delivery_t' name='delivery_t' value=\"{$delivery_t}\"></td>\n";
echo "</tr></table>\n";
echo "</form>\n";

echo "<form name='frmCUSTOMIZE' action='action/cgx_customize.php' method='post'>\n";
echo "<input type='hidden' name='back' value='" . urlencode($_SERVER['REQUEST_URI']) . "'>\n";
echo "<input type='hidden' name='dg_name' value='view.so'>\n";
echo "<table id='columns' class='datagrid_bar' style='display: none;'><tr>\n";
echo "<td width='99%' valign='top'>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['remark'] == 1 ? ' checked' : '') . " id='col_remark' name='col[remark]' type='checkbox'></td><td width='99%'><label for='col_remark'>Remark</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['document_no'] == 1 ? ' checked' : '') . " id='col_document_no' name='col[document_no]' type='checkbox'></td><td width='99%'><label for='col_document_no'>SC Number</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['reference_no'] == 1 ? ' checked' : '') . " id='col_reference_no' name='col[reference_no]' type='checkbox'></td><td width='99%'><label for='col_reference_no'>PO Number</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['partner_code'] == 1 ? ' checked' : '') . " id='col_partner_code' name='col[partner_code]' type='checkbox'></td><td width='99%'><label for='col_partner_code'>Kode Customer</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['partner_name'] == 1 ? ' checked' : '') . " id='col_partner_name' name='col[partner_name]' type='checkbox'></td><td width='99%'><label for='col_partner_name'>Customer</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['m_product_id'] == 1 ? ' checked' : '') . " id='col_m_product_id' name='col[m_product_id]' type='checkbox'></td><td width='99%'><label for='col_m_product_id'>ID Produk</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['product_code'] == 1 ? ' checked' : '') . " id='col_product_code' name='col[product_code]' type='checkbox'></td><td width='99%'><label for='col_product_code'>Item Number</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['spec'] == 1 ? ' checked' : '') . " id='col_spec' name='col[spec]' type='checkbox'></td><td width='99%'><label for='col_spec'>Spec</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['od'] == 1 ? ' checked' : '') . " id='col_od' name='col[od]' type='checkbox'></td><td width='99%'><label for='col_od'>OD</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['thickness'] == 1 ? ' checked' : '') . " id='col_thickness' name='col[thickness]' type='checkbox'></td><td width='99%'><label for='col_thickness'>Tebal</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['length'] == 1 ? ' checked' : '') . " id='col_length' name='col[length]' type='checkbox'></td><td width='99%'><label for='col_length'>Panjang</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['description'] == 1 ? ' checked' : '') . " id='col_description' name='col[description]' type='checkbox'></td><td width='99%'><label for='col_description'>Description</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['order_date'] == 1 ? ' checked' : '') . " id='col_order_date' name='col[order_date]' type='checkbox'></td><td width='99%'><label for='col_order_date'>Tgl Order</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['schedule_delivery_date'] == 1 ? ' checked' : '') . " id='col_schedule_delivery_date' name='col[schedule_delivery_date]' type='checkbox'></td><td width='99%'><label for='col_schedule_delivery_date'>Jadwal Kirim</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['order_quantity'] == 1 ? ' checked' : '') . " id='col_order_quantity' name='col[order_quantity]' type='checkbox'></td><td width='99%'><label for='col_order_quantity'>Jumlah Barang</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['status'] == 1 ? ' checked' : '') . " id='col_status' name='col[status]' type='checkbox'></td><td width='99%'><label for='col_status'>Status</label></td></tr></table>\n";
echo "</td>\n";
echo "<td width='1' valign='top'><input type='submit' value='Simpan'></td>\n";
echo "<td width='1' valign='top'><input type='button' value='Batalkan' onclick='customizeColumn(false);'></td>\n";
echo "</tr></table>\n";
echo "</form>\n";
?>
<script type="text/javascript">
<!--
$(function() {
    $("#order_f").datepicker({dateFormat: '<?php echo $APP_DATE_FORMAT_JAVA; ?>'});
    $("#order_t").datepicker({dateFormat: '<?php echo $APP_DATE_FORMAT_JAVA; ?>'});
    $("#delivery_f").datepicker({dateFormat: '<?php echo $APP_DATE_FORMAT_JAVA; ?>'});
    $("#delivery_t").datepicker({dateFormat: '<?php echo $APP_DATE_FORMAT_JAVA; ?>'});
});

function exportCSV() {   
    form = document.createElement("form");
    form.setAttribute("method", "post");
    form.setAttribute("action", "action/view.so.php");

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "mode");
    hiddenField.setAttribute("value", "export-all");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "customer");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['customer']; ?>");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "sc_number");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['sc_number']; ?>");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "item_number");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['item_number']; ?>");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "order_f");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['order_f']; ?>");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "order_t");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['order_t']; ?>");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "delivery_f");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['delivery_f']; ?>");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "delivery_t");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['delivery_t']; ?>");
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

if (strlen($cgx_filter1) > 0) $cgx_sql .= " AND c_order.status = '" . mysql_escape_string($cgx_filter1) . "'";
if ($sc_number) $cgx_sql .= " AND c_order.remark LIKE '%" . mysql_escape_string($sc_number) . "%'";
if ($customer) $cgx_sql .= " AND c_bpartner.partner_name LIKE '%" . mysql_escape_string($customer) . "%'";
if ($item_number) $cgx_sql .= " AND (m_product.product_code LIKE '%" . mysql_escape_string($item_number) . "%' OR m_product.product_name LIKE '%" . mysql_escape_string($item_number) . "%' OR m_product.description LIKE '%" . mysql_escape_string($item_number) . "%')";
if ($order_f) $cgx_sql .= " AND c_order.order_date >= '" . npl_dmy2ymd($order_f) . "'";
if ($order_t) $cgx_sql .= " AND c_order.order_date <= '" . npl_dmy2ymd($order_t) . "'";
if ($delivery_f) $cgx_sql .= " AND c_order_line.schedule_delivery_date >= '" . npl_dmy2ymd($delivery_f) . "'";
if ($delivery_t) $cgx_sql .= " AND c_order_line.schedule_delivery_date <= '" . npl_dmy2ymd($delivery_t) . "'";

if (strlen($cgx_filter1) > 0) $cgx_sqltotal .= " AND c_order.status = '" . mysql_escape_string($cgx_filter1) . "'";
if ($sc_number) $cgx_sqltotal .= " AND c_order.remark LIKE '%" . mysql_escape_string($sc_number) . "%'";
if ($customer) $cgx_sqltotal .= " AND c_bpartner.partner_name LIKE '%" . mysql_escape_string($customer) . "%'";
if ($item_number) $cgx_sqltotal .= " AND (m_product.product_code LIKE '%" . mysql_escape_string($item_number) . "%' OR m_product.product_name LIKE '%" . mysql_escape_string($item_number) . "%' OR m_product.description LIKE '%" . mysql_escape_string($item_number) . "%')";
if ($order_f) $cgx_sqltotal .= " AND c_order.order_date >= '" . npl_dmy2ymd($order_f) . "'";
if ($order_t) $cgx_sqltotal .= " AND c_order.order_date <= '" . npl_dmy2ymd($order_t) . "'";
if ($delivery_f) $cgx_sqltotal .= " AND c_order_line.schedule_delivery_date >= '" . npl_dmy2ymd($delivery_f) . "'";
if ($delivery_t) $cgx_sqltotal .= " AND c_order_line.schedule_delivery_date <= '" . npl_dmy2ymd($delivery_t) . "'";

//echo $cgx_sql;

$cgx_test = $cgx_datagrid->bind($cgx_sql, $cgx_options);
if (PEAR::isError($cgx_test)) {
    echo $cgx_test->getMessage();
}

if ($cgx_def_columns['remark'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Remark', 'remark', 'remark', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['document_no'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('SC Number', 'document_no', 'document_no', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['reference_no'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('PO Number', 'reference_no', 'reference_no', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['partner_code'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Kode Customer', 'partner_code', 'partner_code', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['partner_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Customer', 'partner_name', 'partner_name', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['m_product_id'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('ID Produk', 'm_product_id', 'm_product_id', array('align' => 'right'), NULL, NULL));
if ($cgx_def_columns['product_code'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Item Number', 'product_code', 'product_code', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['spec'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Spec', 'spec', 'spec', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['od'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('OD', 'od', 'od', array('align' => 'right'), NULL, NULL));
if ($cgx_def_columns['thickness'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Tebal', 'thickness', 'thickness', array('align' => 'right'), NULL, NULL));
if ($cgx_def_columns['length'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Panjang', 'length', 'length', array('align' => 'right'), NULL, NULL));
if ($cgx_def_columns['description'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Description', 'description', 'description', array('align' => 'right'), NULL, NULL));
if ($cgx_def_columns['order_date'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Tgl Order', 'order_date', 'order_date', array('align' => 'center'), NULL, "cgx_format_date()"));
if ($cgx_def_columns['schedule_delivery_date'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Jadwal Kirim', 'schedule_delivery_date', 'schedule_delivery_date', array('align' => 'center'), NULL, "cgx_format_date()"));
if ($cgx_def_columns['order_quantity'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Jumlah Order', 'order_quantity', 'order_quantity', array('align' => 'right'), NULL, "cgx_format_3digit()"));
if ($cgx_def_columns['order_quantity'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Jumlah Delivery', 'delivered_quantity', 'delivered_quantity', array('align' => 'right'), NULL, "cgx_format_3digit()"));
if ($cgx_def_columns['order_quantity'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Kurang Delivery', 'outstanding', 'outstanding', array('align' => 'right'), NULL, "cgx_format_3digit()"));
if ($cgx_def_columns['status'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Status', 'status', 'status', array('align' => 'left'), NULL, "cgx_format_status()"));

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

//-----------------------------------------------------------------------------------------------------------------------------------
    $cgx_data_sum = cgx_fetch_table($cgx_sqltotal);

    echo "<div class='tbl-header-box' style='font-size: 12px; border: 1px solid #ccc; margin-top: 10px;'>";
    echo "  <table class=''>";
    echo "  <tr>";
    echo "      <td><b>Total Order</b></td>";
    echo "      <td width='10' align='center'>:</td>";
    echo "      <td align='right'><b>".  number_format($cgx_data_sum["order_quantity"], 2)."</b></td>";
    echo "      <td width='100px;'>&nbsp;</td>";
    echo "  </tr>"; 
    echo "  <tr>";
    echo "      <td><b>Total Delivery</b></td>";
    echo "      <td width='10' align='center'>:</td>";
    echo "      <td align='right'><b>".  number_format($cgx_data_sum["delivery_quantity"], 2)."</b></td>";
    echo "  </tr>"; 
    echo "  <tr>";
    echo "      <td><b>Total dikembaliankan</b></td>";
    echo "      <td width='10' align='center'>:</td>";
    echo "      <td align='right'><b><u>".  number_format($cgx_data_sum["return_quantity"], 2)."</u></b></td>";
    echo "  </tr>"; 
    echo "  <tr>";
    echo "      <td><b>Total Outstanding Delivery</b></td>";
    echo "      <td width='10' align='center'>:</td>";
    echo "      <td align='right'><b>".  number_format($cgx_data_sum["outstanding"], 2)."</b></td>";    
    echo "  </tr>";    
    echo "  </table>";
    echo "</div>";
    
?>
