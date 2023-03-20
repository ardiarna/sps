<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 05/12/2013 13:19:29
 */


echo "<div class='title'>Sisa Work Order<div style='font-size: 12px;'>As of " . date($APP_DATETIME_FORMAT) . "</div></div>";

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

if (is_array($_SESSION[$GLOBALS['APP_ID']]['view.owo']['columns'])) {
    $cgx_def_columns = $_SESSION[$GLOBALS['APP_ID']]['view.owo']['columns'];
} else {
    $cgx_def_columns = array(
        'document_no' => 1,
        'partner_code' => 1,
        'partner_name' => 1,
        'product_code' => 1,
        'product_name' => 1,
        'spec' => 1,
        'od' => 1,
        'thickness' => 1,
        'length' => 1,
        'order_date' => 1,
        'order_quantity' => 1,
        //'machine_code' => 1,
        'machine_name' => 1,
        'delivered_quantity' => 1,
        'producted_quantity' => 1,
        'return_quantity' => 1,
        'balance_quantity' => 1,
        'outstanding' => 1
    );
    $_SESSION[$GLOBALS['APP_ID']]['view.owo']['columns'] = $cgx_def_columns;
}

$cgx_sql = "SELECT o.document_no,  order_date, partner_code, partner_name,
product_code, product_name, spec, od, thickness, length , machine_code, machine_name, ol.*, balance_quantity,
order_quantity - producted_quantity outstanding
FROM m_work_order o
JOIN m_work_order_line ol ON (o.m_work_order_id = ol.m_work_order_id)
JOIN c_bpartner bp USING (c_bpartner_id)
JOIN m_product USING (m_product_id)
LEFT JOIN m_machine USING (m_machine_id)
LEFT JOIN (SELECT m_product_id, balance_quantity FROM m_stock_balance_d_2 WHERE latest = 'Y') sb USING (m_product_id)
WHERE order_quantity - producted_quantity > 0
AND line_status != 'C' AND status != 'C' ";
$cgx_sql .= "AND " . org_filter_trx('o.app_org_id');

$cgx_datagrid = new Structures_DataGrid($cgx_max_rows);
$cgx_options = array('dsn' => $cgx_dsn);

$cgx_filter1 = urldecode($_REQUEST['f1']);
$order_f = $_REQUEST['order_f'];
$order_t = $_REQUEST['order_t'];
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
echo "<td width='1' class='datagrid_bar_icon'><a title='Export semua data ke CSV' href='action/view.owo.php?mode=export-all'><img border='0' src='images/icon_csv.png'></a></td>\n";
echo "<td width='1' class='datagrid_bar_icon'><a title='Kustomisasi kolom' href='javascript:customizeColumn(true);'><img border='0' src='images/icon_columns.png'></a></td>\n";
echo "</tr><tr>";
echo "<td align='right'>SC Number</td>\n";
echo "<td align='left'><input type='text'style='width: 150px;' name='sc_number' value=\"{$sc_number}\"></td>\n";
echo "<td align='right'>Item Number</td>\n";
echo "<td align='left'><input type='text'style='width: 150px;' name='item_number' value=\"{$item_number}\"></td>\n";
echo "</tr></table>\n";
echo "</form>\n";

echo "<form name='frmCUSTOMIZE' action='action/cgx_customize.php' method='post'>\n";
echo "<input type='hidden' name='back' value='" . urlencode($_SERVER['REQUEST_URI']) . "'>\n";
echo "<input type='hidden' name='dg_name' value='view.owo'>\n";
echo "<input type='hidden' name='col[outstanding]' value='on'>\n";
echo "<input type='hidden' name='col[document_no]' value='on'>\n";
echo "<table id='columns' class='datagrid_bar' style='display: none;'><tr>\n";
echo "<td width='99%' valign='top'>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_document_no' name='col[document_no]' type='checkbox'></td><td width='99%'><label for='col_document_no'>Work Order</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['partner_code'] == 1 ? ' checked' : '') . " id='col_partner_code' name='col[partner_code]' type='checkbox'></td><td width='99%'><label for='col_partner_code'>Kode Customer</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['partner_name'] == 1 ? ' checked' : '') . " id='col_partner_name' name='col[partner_name]' type='checkbox'></td><td width='99%'><label for='col_partner_name'>Customer</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['product_code'] == 1 ? ' checked' : '') . " id='col_product_code' name='col[product_code]' type='checkbox'></td><td width='99%'><label for='col_product_code'>Item Number</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['spec'] == 1 ? ' checked' : '') . " id='col_spec' name='col[spec]' type='checkbox'></td><td width='99%'><label for='col_spec'>Spec</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['od'] == 1 ? ' checked' : '') . " id='col_od' name='col[od]' type='checkbox'></td><td width='99%'><label for='col_od'>OD</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['thickness'] == 1 ? ' checked' : '') . " id='col_thickness' name='col[thickness]' type='checkbox'></td><td width='99%'><label for='col_thickness'>Thickness</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['length'] == 1 ? ' checked' : '') . " id='col_length' name='col[length]' type='checkbox'></td><td width='99%'><label for='col_length'>Length</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['product_name'] == 1 ? ' checked' : '') . " id='col_product_name' name='col[product_name]' type='checkbox'></td><td width='99%'><label for='col_product_name'>Nama Barang</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['order_date'] == 1 ? ' checked' : '') . " id='col_order_date' name='col[order_date]' type='checkbox'></td><td width='99%'><label for='col_order_date'>Tgl Order</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['machine_code'] == 1 ? ' checked' : '') . " id='col_machine_code' name='col[machine_code]' type='checkbox'></td><td width='99%'><label for='col_machine_code'>Kode Mesin</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['machine_name'] == 1 ? ' checked' : '') . " id='col_machine_name' name='col[machine_name]' type='checkbox'></td><td width='99%'><label for='col_machine_name'>Mesin</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['order_quantity'] == 1 ? ' checked' : '') . " id='col_order_quantity' name='col[order_quantity]' type='checkbox'></td><td width='99%'><label for='col_order_quantity'>Jumlah Order</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['producted_quantity'] == 1 ? ' checked' : '') . " id='col_producted_quantity' name='col[producted_quantity]' type='checkbox'></td><td width='99%'><label for='producted_quantity'>Sudah Dikerjakan</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_outstanding' name='col[outstanding]' type='checkbox'></td><td width='99%'><label for='col_outstanding'>Belum Terproduksi</label></td></tr></table>\n";
//echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['balance_quantity'] == 1 ? ' checked' : '') . " id='col_balance_quantity' name='col[balance_quantity]' type='checkbox'></td><td width='99%'><label for='col_balance_quantity'>Stock Balance</label></td></tr></table>\n";
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
if ($sc_number) $cgx_sql .= " AND o.document_no LIKE '%" . mysql_escape_string($sc_number) . "%'";
if ($customer) $cgx_sql .= " AND bp.partner_name LIKE '%" . mysql_escape_string($customer) . "%'";
if ($item_number) $cgx_sql .= " AND m_product.product_code LIKE '%" . mysql_escape_string($item_number) . "%'";
if ($order_f) $cgx_sql .= " AND o.order_date >= '" . npl_dmy2ymd($order_f) . "'";
if ($order_t) $cgx_sql .= " AND o.order_date <= '" . npl_dmy2ymd($order_t) . "'";

// echo $cgx_sql;
$cgx_test = $cgx_datagrid->bind($cgx_sql, $cgx_options);
if (PEAR::isError($cgx_test)) {
    echo $cgx_test->getMessage();
}

if ($cgx_def_columns['document_no'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('SC<br>Number', 'document_no', 'document_no', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['partner_code'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Kode<br>Customer', 'partner_code', 'partner_code', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['partner_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Customer', 'partner_name', 'partner_name', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['product_code'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Item<br>Number', 'product_code', 'product_code', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['spec'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Spec', 'spec', 'spec', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['od'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('OD', 'od', 'od', array('align' => 'right'), NULL, NULL));
if ($cgx_def_columns['thickness'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Thickness', 'thickness', 'thickness', array('align' => 'right'), NULL, NULL));
if ($cgx_def_columns['length'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Length', 'length', 'length', array('align' => 'right'), NULL, NULL));
if ($cgx_def_columns['product_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Nama<br>Barang', 'product_name', 'product_name', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['order_date'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Tgl<br>Order', 'order_date', 'order_date', array('align' => 'center'), NULL, "cgx_format_date()"));
if ($cgx_def_columns['machine_code'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Kode<br>Mesin', 'machine_code', 'machine_code', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['machine_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Mesin', 'machine_name', 'machine_name', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['order_quantity'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Jumlah<br>Order', 'order_quantity', 'order_quantity', array('align' => 'right'), NULL, "cgx_format_3digit()"));
if ($cgx_def_columns['producted_quantity'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Sudah<br>Dikerjakan', 'producted_quantity', 'producted_quantity', array('align' => 'right'), NULL, "cgx_format_3digit()"));
if ($cgx_def_columns['outstanding'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Belum<br>Terproduksi', 'outstanding', 'outstanding', array('align' => 'right'), NULL, "cgx_format_3digit()"));
//if ($cgx_def_columns['balance_quantity'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Stock<br>Balance', 'balance_quantity', 'balance_quantity', array('align' => 'right'), NULL, "cgx_format_3digit()"));

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