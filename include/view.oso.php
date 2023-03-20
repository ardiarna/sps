<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 05/12/2013 13:19:29
 */


echo "<div class='title'>Outstanding Sales Order<div style='font-size: 12px;'>As of " . date($APP_DATETIME_FORMAT) . "</div></div>";

require_once 'Structures/DataGrid.php';
require_once 'HTML/Table.php';

function format_delivery_date($data) {
    global $APP_DATE_FORMAT;
    $d = date($APP_DATE_FORMAT, strtotime($data['record']['schedule_delivery_date']));
    if (strtotime($data['record']['schedule_delivery_date']) < mktime()) {
        return "<span style='color: red;'>{$d}</span>";
    } else {
        return $d;
    }
}

if (is_array($_SESSION[$GLOBALS['APP_ID']]['view.oso']['columns'])) {
    $cgx_def_columns = $_SESSION[$GLOBALS['APP_ID']]['view.oso']['columns'];
} else {
    $cgx_def_columns = array(
        'remark' => 1,
        'partner_name' => 1,
        'product_code' => 1,
        'spec' => 1,
        'od' => 1,
        'thickness' => 1,
        'length' => 1,
        'order_date' => 1,
        'schedule_delivery_date' => 1,
        'balance_quantity' => 1,
        'order_quantity' => 1,
        'delivered_quantity' => 1,
        'return_quantity' => 1,
        'outstanding' => 1
    );
    $_SESSION[$GLOBALS['APP_ID']]['view.oso']['columns'] = $cgx_def_columns;
}

$cgx_sql = "SELECT
m_product_id, 
CONCAT(od,'%',thickness,'%',length) AS descrips,
o.remark, 
order_date, 
partner_code, 
partner_name, 
product_code, 
product_name, 
spec, 
od, 
thickness, 
length , 
ol.*, 
balance_quantity, 
order_quantity - delivered_quantity + return_quantity outstanding 

FROM 
c_order o JOIN c_order_line ol ON (o.c_order_id = ol.c_order_id)
          JOIN c_bpartner bp USING (c_bpartner_id)
          JOIN m_product USING (m_product_id)
     LEFT JOIN (SELECT m_product_id, balance_quantity FROM m_stock_balance_d_2 WHERE latest = 'Y' AND app_org_id = '". org() ."') sb USING (m_product_id)

WHERE 
o.m_transaction_type_id = '1' 
AND order_quantity - delivered_quantity + return_quantity > 0
AND line_status != 'C' AND status != 'C' ";

$cgx_sqltotal = "SELECT sum(order_quantity) as order_quantity, sum(delivered_quantity) as delivery_quantity, sum(return_quantity) as return_quantity,
sum(order_quantity - delivered_quantity + return_quantity) outstanding
FROM c_order o
JOIN c_order_line ol ON (o.c_order_id = ol.c_order_id)
JOIN c_bpartner bp USING (c_bpartner_id)
JOIN m_product USING (m_product_id)
LEFT JOIN (SELECT m_product_id, balance_quantity FROM m_stock_balance_d_2 WHERE latest = 'Y' AND app_org_id = '". org() ."') sb USING (m_product_id)
WHERE 
o.m_transaction_type_id = '1' 
AND order_quantity - delivered_quantity + return_quantity > 0
AND line_status != 'C' AND status != 'C' ";

if (org() != '1') {  
$cgx_sql .= "AND " . org_filter_trx('o.app_org_id');
}

if (org() != '1') {  
$cgx_sqltotal .= "AND " . org_filter_trx('o.app_org_id');
}

$cgx_datagrid = new Structures_DataGrid($cgx_max_rows);
$cgx_options = array('dsn' => $cgx_dsn);

$cgx_filter1 = urldecode($_REQUEST['f1']);
$order_f = $_REQUEST['order_f'];
$order_t = $_REQUEST['order_t'];
$delivery_f = $_REQUEST['delivery_f'];
$delivery_t = $_REQUEST['delivery_t'];
$customer = $_REQUEST['customer'];
$sc_number = $_REQUEST['sc_number'];
$item_number = $_REQUEST['item_number'];

echo "<form name='frmFILTER' action='{$_SERVER['SCRIPT_NAME']}'>\n";
echo "<input type='hidden' name='m' value='{$_REQUEST['m']}'>\n";
echo "<table id='bar' class='datagrid_bar' width='100%' border=0><tr>\n";
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
echo "<input type='hidden' name='dg_name' value='view.oso'>\n";
echo "<input type='hidden' name='col[outstanding]' value='on'>\n";
echo "<input type='hidden' name='col[remark]' value='on'>\n";
echo "<table id='columns' class='datagrid_bar' style='display: none;'><tr>\n";
echo "<td width='99%' valign='top'>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_remark' name='col[remark]' type='checkbox'></td><td width='99%'><label for='col_remark'>Remark</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['partner_code'] == 1 ? ' checked' : '') . " id='col_partner_code' name='col[partner_code]' type='checkbox'></td><td width='99%'><label for='col_partner_code'>Kode Customer</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['partner_name'] == 1 ? ' checked' : '') . " id='col_partner_name' name='col[partner_name]' type='checkbox'></td><td width='99%'><label for='col_partner_name'>Customer</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['product_code'] == 1 ? ' checked' : '') . " id='col_product_code' name='col[product_code]' type='checkbox'></td><td width='99%'><label for='col_product_code'>Item Number</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['spec'] == 1 ? ' checked' : '') . " id='col_spec' name='col[spec]' type='checkbox'></td><td width='99%'><label for='col_spec'>Spec</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['od'] == 1 ? ' checked' : '') . " id='col_od' name='col[od]' type='checkbox'></td><td width='99%'><label for='col_od'>OD</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['thickness'] == 1 ? ' checked' : '') . " id='col_thickness' name='col[thickness]' type='checkbox'></td><td width='99%'><label for='col_thickness'>Thickness</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['length'] == 1 ? ' checked' : '') . " id='col_length' name='col[length]' type='checkbox'></td><td width='99%'><label for='col_length'>Length</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['product_name'] == 1 ? ' checked' : '') . " id='col_product_name' name='col[product_name]' type='checkbox'></td><td width='99%'><label for='col_product_name'>Nama Barang</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['order_date'] == 1 ? ' checked' : '') . " id='col_order_date' name='col[order_date]' type='checkbox'></td><td width='99%'><label for='col_order_date'>Tgl Order</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['schedule_delivery_date'] == 1 ? ' checked' : '') . " id='col_schedule_delivery_date' name='col[schedule_delivery_date]' type='checkbox'></td><td width='99%'><label for='col_schedule_delivery_date'>Jadwal Kirim</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['order_quantity'] == 1 ? ' checked' : '') . " id='col_order_quantity' name='col[order_quantity]' type='checkbox'></td><td width='99%'><label for='col_order_quantity'>Jumlah Order</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['delivered_quantity'] == 1 ? ' checked' : '') . " id='col_delivered_quantity' name='col[delivered_quantity]' type='checkbox'></td><td width='99%'><label for='col_delivered_quantity'>Sudah Dikirim</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['return_quantity'] == 1 ? ' checked' : '') . " id='col_return_quantity' name='col[return_quantity]' type='checkbox'></td><td width='99%'><label for='col_return_quantity'>Jumlah Return</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_outstanding' name='col[outstanding]' type='checkbox'></td><td width='99%'><label for='col_outstanding'>Outstanding</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['balance_quantity'] == 1 ? ' checked' : '') . " id='col_balance_quantity' name='col[balance_quantity]' type='checkbox'></td><td width='99%'><label for='col_balance_quantity'>Stock Balance</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['m_product_id'] == 1 ? ' checked' : '') . " id='col_m_product_id' name='col[m_product_id]' type='checkbox'></td><td width='99%'><label for='col_m_product_id'>Product ID.</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['descrips'] == 1 ? ' checked' : '') . " id='col_descrips' name='col[descrips]' type='checkbox'></td><td width='99%'><label for='col_descrips'>Desc.</label></td></tr></table>\n";
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
    form.setAttribute("action", "action/view.oso.php");

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

if (strlen($cgx_filter1) > 0) $cgx_sql .= " AND o.status = '" . mysql_escape_string($cgx_filter1) . "'";
if ($sc_number) $cgx_sql .= " AND o.remark LIKE '%" . mysql_escape_string($sc_number) . "%'";
if ($customer) $cgx_sql .= " AND bp.partner_name LIKE '%" . mysql_escape_string($customer) . "%'";
if ($item_number) $cgx_sql .= " AND (m_product.product_code LIKE '%" . mysql_escape_string($item_number) . "%' OR m_product.product_name LIKE '%" . mysql_escape_string($item_number) . "%' OR m_product.description LIKE '%" . mysql_escape_string($item_number) . "%')";
if ($order_f) $cgx_sql .= " AND o.order_date >= '" . npl_dmy2ymd($order_f) . "'";
if ($order_t) $cgx_sql .= " AND o.order_date <= '" . npl_dmy2ymd($order_t) . "'";
if ($delivery_f) $cgx_sql .= " AND ol.schedule_delivery_date >= '" . npl_dmy2ymd($delivery_f) . "'";
if ($delivery_t) $cgx_sql .= " AND ol.schedule_delivery_date <= '" . npl_dmy2ymd($delivery_t) . "'";

if (strlen($cgx_filter1) > 0) $cgx_sqltotal .= " AND o.status = '" . mysql_escape_string($cgx_filter1) . "'";
if ($sc_number) $cgx_sqltotal .= " AND o.remark LIKE '%" . mysql_escape_string($sc_number) . "%'";
if ($customer) $cgx_sqltotal .= " AND bp.partner_name LIKE '%" . mysql_escape_string($customer) . "%'";
if ($item_number) $cgx_sqltotal .= " AND (m_product.product_code LIKE '%" . mysql_escape_string($item_number) . "%' OR m_product.product_name LIKE '%" . mysql_escape_string($item_number) . "%' OR m_product.description LIKE '%" . mysql_escape_string($item_number) . "%')";
if ($order_f) $cgx_sqltotal .= " AND o.order_date >= '" . npl_dmy2ymd($order_f) . "'";
if ($order_t) $cgx_sqltotal .= " AND o.order_date <= '" . npl_dmy2ymd($order_t) . "'";
if ($delivery_f) $cgx_sqltotal .= " AND ol.schedule_delivery_date >= '" . npl_dmy2ymd($delivery_f) . "'";
if ($delivery_t) $cgx_sqltotal .= " AND ol.schedule_delivery_date <= '" . npl_dmy2ymd($delivery_t) . "'";

//echo $cgx_sql;
$cgx_test = $cgx_datagrid->bind($cgx_sql, $cgx_options);
if (PEAR::isError($cgx_test)) {
    echo $cgx_test->getMessage();
}

if ($cgx_def_columns['remark'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Remark', 'remark', 'remark', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['partner_code'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Kode<br>Customer', 'partner_code', 'partner_code', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['partner_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Customer', 'partner_name', 'partner_name', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['product_code'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Item<br>Number', 'product_code', 'product_code', array('align' => 'left'), NULL, NULL));
//===================================================================
if ($cgx_def_columns['spec'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Spec', 'spec', 'spec', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['od'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('OD', 'od', 'od', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['thickness'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Thickness', 'thickness', 'thickness', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['length'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Length', 'length', 'length', array('align' => 'left'), NULL, NULL));
//===================================================================
if ($cgx_def_columns['product_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Nama<br>Barang', 'product_name', 'product_name', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['order_date'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Tgl<br>Order', 'order_date', 'order_date', array('align' => 'center'), NULL, "cgx_format_date()"));
if ($cgx_def_columns['schedule_delivery_date'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Jadwal<br>Kirim', 'schedule_delivery_date', 'schedule_delivery_date', array('align' => 'center'), NULL, "format_delivery_date()"));
if ($cgx_def_columns['order_quantity'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Jumlah<br>Order', 'order_quantity', 'order_quantity', array('align' => 'right'), NULL, "cgx_format_3digit()"));
if ($cgx_def_columns['delivered_quantity'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Sudah<br>Dikirim', 'delivered_quantity', 'delivered_quantity', array('align' => 'right'), NULL, "cgx_format_3digit()"));
if ($cgx_def_columns['return_quantity'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Jumlah<br>Return', 'return_quantity', 'return_quantity', array('align' => 'right'), NULL, "cgx_format_3digit()"));
if ($cgx_def_columns['outstanding'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Outstanding', 'outstanding', 'outstanding', array('align' => 'right'), NULL, "cgx_format_3digit()"));
if ($cgx_def_columns['balance_quantity'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Stock<br>Balance', 'balance_quantity', 'balance_quantity', array('align' => 'right'), NULL, "cgx_format_3digit()"));

if ($cgx_def_columns['m_product_id'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Product ID.', 'm_product_id', 'm_product_id', array('align' => 'right'), NULL, NULL));
if ($cgx_def_columns['descrips'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Desc.', 'descrips', 'descrips', array('align' => 'right'), NULL, NULL));


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
