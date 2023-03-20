<?php

/**
 * (c) Azwari Nugraha <nugraha@pt-gai.com>
 * 25/11/2013 00:52:21
 */


echo "<div class='title'>Realisasi Work Order</div>";

require_once 'Structures/DataGrid.php';
require_once 'HTML/Table.php';

if (is_array($_SESSION[$GLOBALS['APP_ID']]['view.hpm']['columns'])) {
    $cgx_def_columns = $_SESSION[$GLOBALS['APP_ID']]['view.hpm']['columns'];
} else {
    $cgx_def_columns = array(
        'm_production_id' => 1,
        'production_date' => 1,
        'document_no' => 1,
        'wo' => 1,
        'remark' => 1,
        'reference_no' => 1,
        'partner_name' => 1,
        'machine_name' => 1,
        'product_name' => 1,
        //'no_coil' => 1,
        //'no_lot' => 1,
        'good' => 1,
        'good_ch' => 1,
        'good_sk' => 1,
        'good_pl' => 1,
        'good_bd' => 1,
        'good_qc' => 1,
    );
    $_SESSION[$GLOBALS['APP_ID']]['view.hpm']['columns'] = $cgx_def_columns;
}
$cgx_sql = "SELECT m_production.*, m_production_line.*, m_work_order.document_no wo, c_order.document_no so, COALESCE(remark,c_forecast.document_no) remark, reference_no, 
    COALESCE(c_bpartner.partner_name,cb2.partner_name) partner_name, machine_name, product_code, product_name, auc.user_fullname, auu.user_fullname user_fullname_u 
    FROM m_production
    JOIN m_work_order USING (m_work_order_id) 
    JOIN m_machine ON (m_production.m_machine_id = m_machine.m_machine_id)
    JOIN m_production_line USING (m_production_id)
    JOIN m_work_order_line USING (m_work_order_line_id) 
    LEFT JOIN c_order USING (c_order_id) 
    LEFT JOIN c_bpartner USING (c_bpartner_id)
    LEFT JOIN c_forecast ON (m_work_order_line.c_forecast_id=c_forecast.c_forecast_id)
    LEFT JOIN c_bpartner cb2 ON (c_forecast.c_bpartner_id=cb2.c_bpartner_id) 
    JOIN m_product ON(m_work_order_line.m_product_id=m_product.m_product_id) 
    LEFT JOIN app_user auc ON (m_production.create_user=auc.user_id) 
    LEFT JOIN app_user auu ON (m_production.update_user=auu.user_id) 
    WHERE 1 = 1 ";
$cgx_sql .= " AND " . org_filter_trx('m_work_order.app_org_id');

$cgx_datagrid = new Structures_DataGrid($cgx_max_rows);
$cgx_options = array('dsn' => $cgx_dsn);
$cgx_datagrid->setDefaultSort(array('production_date' => 'DESC'));

$mesin = $_REQUEST['mesin'];
$date_f = $_REQUEST['date_f'];
$date_t = $_REQUEST['date_t'];
$item_number = $_REQUEST['item_number'];
$document_no = $_REQUEST['document_no'];
$sc_number = $_REQUEST['sc_number'];
$customer = $_REQUEST['customer'];
$cgx_search = $_REQUEST['q'];

echo "<form name='frmFILTER' action='{$_SERVER['SCRIPT_NAME']}'>\n";
echo "<input type='hidden' name='m' value='{$_REQUEST['m']}'>\n";
echo "<table id='bar' class='datagrid_bar' width='100%'><tr>\n";
echo "<td align='right'>No. WO</td>\n";
echo "<td align='left'><input type='text'style='width: 150px;' name='document_no' value=\"{$document_no}\"></td>\n";
echo "<td align='right'>Item Number</td>\n";
echo "<td align='left'><input type='text'style='width: 150px;' name='item_number' value=\"{$item_number}\"></td>\n";
echo "<td align='right'>Tgl. WO</td>\n";
echo "<td align='left' width='1'><input type='text'style='width: 120px; text-align: center;' id='date_f' name='date_f' value=\"{$date_f}\"></td>\n";
echo "<td align='center'>s/d</td>\n";
echo "<td align='left' width='1'><input type='text'style='width: 120px; text-align: center;' id='date_t' name='date_t' value=\"{$date_t}\"></td>\n";
echo "<td width='1'><input title='Cari' type='image' src='images/icon_search.png' border='0' style='padding-right: 20px;'></td>\n";
echo "<td></td>\n";
echo "<td width='20'></td>\n"; 
echo "<td width='1' class='datagrid_bar_icon'><a title='Export data ke CSV' href='javascript:exportCSV();'><img border='0' src='images/icon_csv.png'></a></td>\n";
echo "<td width='1' class='datagrid_bar_icon'><a title='Kustomisasi kolom' href='javascript:customizeColumn(true);'><img border='0' src='images/icon_columns.png'></a></td>\n";
echo "</tr><tr>";
echo "<td align='right'>Remark</td>\n";
echo "<td align='left'><input type='text'style='width: 150px;' name='sc_number' value=\"{$sc_number}\"></td>\n";
echo "<td align='right'>Customer</td>\n";
echo "<td align='left'><input type='text'style='width: 150px;' name='customer' value=\"{$customer}\"></td>\n";
echo "<td align='right'>Mesin</td>\n";
echo "<td colspan='2'>" . cgx_filter('mesin', "SELECT m_machine_id, machine_name FROM m_machine WHERE " . org_filter_master() . " ORDER BY machine_name", $mesin, TRUE) . "</td>\n";
echo "<td align='right'><input type='text' size='27' name='q' value=\"{$cgx_search}\"></td>\n";
echo "</tr></table>\n";
echo "</form>\n";

echo "<form name='frmCUSTOMIZE' action='action/cgx_customize.php' method='post'>\n";
echo "<input type='hidden' name='back' value='" . urlencode($_SERVER['REQUEST_URI']) . "'>\n";
echo "<input type='hidden' name='dg_name' value='view.hpm'>\n";
echo "<input type='hidden' name='col[production_date]' value='on'>\n";
echo "<input type='hidden' name='col[document_no]' value='on'>\n";
echo "<input type='hidden' name='col[wo]' value='on'>\n";
echo "<input type='hidden' name='col[remark]' value='on'>\n";
echo "<table id='columns' class='datagrid_bar' style='display: none;'><tr>\n";
echo "<td width='99%' valign='top'>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_production_date' name='col[production_date]' type='checkbox'></td><td width='99%'><label for='col_production_date'>Tanggal</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_document_no' name='col[document_no]' type='checkbox'></td><td width='99%'><label for='col_document_no'>Nomor Dokumen</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_wo' name='col[wo]' type='checkbox'></td><td width='99%'><label for='col_wo'>No W/O</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_remark' name='col[remark]' type='checkbox'></td><td width='99%'><label for='col_remark'>Remark/Forecast</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['reference_no'] == 1 ? ' checked' : '') . " id='col_reference_no' name='col[reference_no]' type='checkbox'></td><td width='99%'><label for='col_reference_no'>No. PO</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['partner_name'] == 1 ? ' checked' : '') . " id='col_partner_name' name='col[partner_name]' type='checkbox'></td><td width='99%'><label for='col_partner_name'>Pelanggan</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['machine_name'] == 1 ? ' checked' : '') . " id='col_machine_name' name='col[machine_name]' type='checkbox'></td><td width='99%'><label for='col_machine_name'>Mesin</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['product_name'] == 1 ? ' checked' : '') . " id='col_product_name' name='col[product_name]' type='checkbox'></td><td width='99%'><label for='col_product_name'>Produk</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['product_code'] == 1 ? ' checked' : '') . " id='col_product_code' name='col[product_code]' type='checkbox'></td><td width='99%'><label for='col_product_code'>Item Number</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['product_name'] == 1 ? ' checked' : '') . " id='col_product_name' name='col[product_name]' type='checkbox'></td><td width='99%'><label for='col_product_name'>Product Name</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['no_coil'] == 1 ? ' checked' : '') . " id='col_no_coil' name='col[no_coil]' type='checkbox'></td><td width='99%'><label for='col_no_coil'>Kode Coil</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['no_lot'] == 1 ? ' checked' : '') . " id='col_no_lot' name='col[no_lot]' type='checkbox'></td><td width='99%'><label for='col_no_lot'>Lot Number</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['good'] == 1 ? ' checked' : '') . " id='col_good' name='col[good]' type='checkbox'></td><td width='99%'><label for='col_good'>Qty CUT</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['good_ch'] == 1 ? ' checked' : '') . " id='col_good_ch' name='col[good_ch]' type='checkbox'></td><td width='99%'><label for='col_good_ch'>Qty CH</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['good_sk'] == 1 ? ' checked' : '') . " id='col_good_sk' name='col[good_sk]' type='checkbox'></td><td width='99%'><label for='col_good_sk'>Qty SK</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['good_pl'] == 1 ? ' checked' : '') . " id='col_good_pl' name='col[good_pl]' type='checkbox'></td><td width='99%'><label for='col_good_pl'>Qty PL</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['good_bd'] == 1 ? ' checked' : '') . " id='col_good_bd' name='col[good_bd]' type='checkbox'></td><td width='99%'><label for='col_good_bd'>Qty BD</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['good_qc'] == 1 ? ' checked' : '') . " id='col_good_qc' name='col[good_qc]' type='checkbox'></td><td width='99%'><label for='col_good_qc'>Qty QC</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['good_pc'] == 1 ? ' checked' : '') . " id='col_good_pc' name='col[good_pc]' type='checkbox'></td><td width='99%'><label for='col_good_pc'>Qty PC</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['user_fullname'] == 1 ? ' checked' : '') . " id='col_user_fullname' name='col[user_fullname]' type='checkbox'></td><td width='99%'><label for='col_user_fullname'>Create User</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['user_fullname_u'] == 1 ? ' checked' : '') . " id='col_user_fullname_u' name='col[user_fullname_u]' type='checkbox'></td><td width='99%'><label for='col_user_fullname_u'>Update User</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['create_date'] == 1 ? ' checked' : '') . " id='col_create_date' name='col[create_date]' type='checkbox'></td><td width='99%'><label for='col_create_date'>Create Date</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['update_date'] == 1 ? ' checked' : '') . " id='col_update_date' name='col[update_date]' type='checkbox'></td><td width='99%'><label for='col_update_date'>Update Date</label></td></tr></table>\n";
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
    form.setAttribute("action", "action/view.hpm.php");

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

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "sc_number");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['sc_number']; ?>");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "customer");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['customer']; ?>");
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

if ($mesin) $cgx_sql .= " AND m_production.m_machine_id = '" . mysql_escape_string($mesin) . "'";
if ($document_no) $cgx_sql .= " AND m_work_order.document_no LIKE '%" . mysql_escape_string($document_no) . "%'";
if ($item_number) $cgx_sql .= " AND (product_code LIKE '%" . mysql_escape_string($item_number) . "%' OR product_name LIKE '%" . mysql_escape_string($item_number) . "%' OR description LIKE '%" . mysql_escape_string($item_number) . "%')";
if ($date_f) $cgx_sql .= " AND m_work_order.order_date >= '" . npl_dmy2ymd($date_f) . "'";
if ($date_t) $cgx_sql .= " AND m_work_order.order_date <= '" . npl_dmy2ymd($date_t) . "'";
if ($sc_number) $cgx_sql .= " AND c_order.remark LIKE '%" . mysql_escape_string($sc_number) . "%'";
if ($customer) $cgx_sql .= " AND (c_bpartner.partner_name LIKE '%" . mysql_escape_string($customer) . "%' OR cb2.partner_name LIKE '%" . mysql_escape_string($customer) . "%')";
$cgx_sql .= " AND ( auc.user_name LIKE '%{$cgx_search}%' OR auc.user_fullname LIKE '%{$cgx_search}%')";

$cgx_test = $cgx_datagrid->bind($cgx_sql, $cgx_options);
if (PEAR::isError($cgx_test)) {
    echo $cgx_test->getMessage();
}

if ($cgx_def_columns['production_date'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Tanggal', 'production_date', 'production_date', array('align' => 'center'), NULL, "cgx_format_date()"));
if ($cgx_def_columns['document_no'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Nomor Dokumen', 'document_no', 'document_no', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['wo'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('No W/O', 'wo', 'wo', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['so'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('No. SC', 'so', 'so', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['remark'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Remark / Forecast', 'remark', 'remark', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['reference_no'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('No. PO', 'reference_no', 'reference_no', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['partner_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Pelanggan', 'partner_name', 'partner_name', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['machine_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Mesin', 'machine_name', 'machine_name', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['product_code'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Item Number', 'product_code', 'product_code', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['product_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Product Name', 'product_name', 'product_name', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['no_coil'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Kode Coil', 'no_coil', 'no_coil', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['no_lot'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Lot Number', 'no_lot', 'no_lot', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['good'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Qty CUT', 'good', 'good', array('align' => 'right'), NULL, "cgx_format_3digit"));
if ($cgx_def_columns['good_ch'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Qty CH', 'good_ch', 'good_ch', array('align' => 'right'), NULL, "cgx_format_3digit"));
if ($cgx_def_columns['good_sk'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Qty SK', 'good_sk', 'good_sk', array('align' => 'right'), NULL, "cgx_format_3digit"));
if ($cgx_def_columns['good_pl'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Qty PL', 'good_pl', 'good_pl', array('align' => 'right'), NULL, "cgx_format_3digit"));
if ($cgx_def_columns['good_bd'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Qty BD', 'good_bd', 'good_bd', array('align' => 'right'), NULL, "cgx_format_3digit"));
if ($cgx_def_columns['good_qc'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Qty QC', 'good_qc', 'good_qc', array('align' => 'right'), NULL, "cgx_format_3digit"));
if ($cgx_def_columns['good_pc'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Qty PC', 'good_pc', 'good_pc', array('align' => 'right'), NULL, "cgx_format_3digit"));
if ($cgx_def_columns['user_fullname'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Create User', 'user_fullname', 'user_fullname', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['user_fullname_u'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Update User', 'user_fullname_u', 'user_fullname_u', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['create_date'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Create Date', 'create_date', 'create_date', array('align' => 'center'), NULL, "cgx_format_timestamp()"));
if ($cgx_def_columns['update_date'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Update Date', 'update_date', 'update_date', array('align' => 'center'), NULL, "cgx_format_timestamp()"));

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