<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 05/12/2013 14:35:39
 */


echo "<div class='title'>Penerimaan Bahan Baku</div>";

require_once 'Structures/DataGrid.php';
require_once 'HTML/Table.php';

if (is_array($_SESSION[$GLOBALS['APP_ID']]['view.good_rcp']['columns'])) {
    $cgx_def_columns = $_SESSION[$GLOBALS['APP_ID']]['view.good_rcp']['columns'];
} else {
    $cgx_def_columns = array(
        'document_no' => 1,
        'm_inout_date' => 1,
        'remark' => 1,
        'partner_name' => 1,
        'no_kendaraan' => 1,
        'sj_date' => 1,
        'product_code' => 1,
        'product_name' => 1,
        'no_coil' => 1,        
        'no_lot' => 1,
        'weight' => 1,
        'quantity' => 1,
    );
    $_SESSION[$GLOBALS['APP_ID']]['view.good_rcp']['columns'] = $cgx_def_columns;
}

$cgx_sql = "SELECT m_inout.*, c_order.document_no so, partner_code, mid(partner_name,1,20) partner_name, order_date, remark, m_inout_line_id, m_inout_line.m_product_id, 
    m_inout_line.c_order_line_id, m_inout_line.m_warehouse_id, m_inout_line.quantity, product_code, product_name, spec, thickness, od , 
    warehouse_name, m_coil_id, m_coil.no_coil, m_coil.no_lot, m_coil.weight, auc.user_fullname, auu.user_fullname user_fullname_u
    FROM m_inout
    JOIN c_order ON(m_inout.c_order_id = c_order.c_order_id)
    JOIN c_bpartner ON(c_order.c_bpartner_id = c_bpartner.c_bpartner_id)
    JOIN m_inout_line ON(m_inout.m_inout_id = m_inout_line.m_inout_id)
    JOIN m_product ON(m_inout_line.m_product_id = m_product.m_product_id)
    JOIN m_warehouse ON(m_inout_line.m_warehouse_id=m_warehouse.m_warehouse_id)
    JOIN m_coil ON(m_inout_line.m_inout_line_id=m_coil.m_in_id)
    LEFT JOIN app_user auc ON (m_inout.create_user=auc.user_id) 
    LEFT JOIN app_user auu ON (m_inout.update_user=auu.user_id) 
    WHERE m_inout.m_transaction_type_id = 12";
$cgx_sql .= " AND " . org_filter_trx('m_inout.app_org_id');
//print_r($cgx_sql);
$cgx_datagrid = new Structures_DataGrid($cgx_max_rows);
$cgx_options = array('dsn' => $cgx_dsn);
$cgx_datagrid->setDefaultSort(array('m_inout_date' => 'DESC'));

$date_f = $_REQUEST['date_f'];
$date_t = $_REQUEST['date_t'];
$item_number = $_REQUEST['item_number'];
$document_no = $_REQUEST['document_no'];
$remark = $_REQUEST['remark'];
$supplier = $_REQUEST['supplier'];
$no_coil = $_REQUEST['no_coil'];
$no_lot = $_REQUEST['no_lot'];

echo "<form name='frmFILTER' action='{$_SERVER['SCRIPT_NAME']}'>\n";
echo "<input type='hidden' name='m' value='{$_REQUEST['m']}'>\n";
echo "  <table id='bar' class='datagrid_bar' width='100%'><tr>\n";
echo "      <td align='right'>No. Receipt</td>\n";
echo "      <td align='left'><input type='text'style='width: 150px;' name='document_no' value=\"{$document_no}\"></td>\n";
echo "      <td align='right'>Item Number</td>\n";
echo "      <td align='left'><input type='text'style='width: 150px;' name='item_number' value=\"{$item_number}\"></td>\n";
echo "      <td align='right'>No. Coil</td>\n";
echo "      <td align='left'><input type='text'style='width: 120px;' name='no_coil' value=\"{$no_coil}\"></td>\n";
echo "      <td align='right'>Tgl. Masuk</td>\n";
echo "      <td align='left' width='1'><input type='text'style='width: 120px; text-align: center;' id='date_f' name='date_f' value=\"{$date_f}\"></td>\n";
echo "      <td width='1'><input title='Cari' type='image' src='images/icon_search.png' border='0' style='padding-right: 20px;'></td>\n";
echo "      <td></td>\n";
echo "      <td width='20'></td>\n"; 
echo "      <td width='1' class='datagrid_bar_icon'><a title='Export data ke CSV' href='javascript:exportCSV();'><img border='0' src='images/icon_csv.png'></a></td>\n";
echo "      <td width='1' class='datagrid_bar_icon'><a title='Kustomisasi kolom' href='javascript:customizeColumn(true);'><img border='0' src='images/icon_columns.png'></a></td>\n";
echo "  </tr><tr>";
echo "      <td align='right'>No. Contract</td>\n";
echo "      <td align='left'><input type='text'style='width: 150px;' name='remark' value=\"{$remark}\"></td>\n";
echo "      <td align='right'>Supplier</td>\n";
echo "      <td align='left'><input type='text'style='width: 150px;' name='supplier' value=\"{$supplier}\"></td>\n";
echo "      <td align='right'>Kode Coil</td>\n";
echo "      <td align='left'><input type='text'style='width: 120px;' name='no_lot' value=\"{$no_lot}\"></td>\n";
echo "      <td align='right'>s/d</td>\n";
echo "      <td align='left' width='1'><input type='text'style='width: 120px; text-align: center;' id='date_t' name='date_t' value=\"{$date_t}\"></td>\n";
echo "      <td></td>\n";
//echo "      <td align='left'><input type='text' size='27' name='q' value=\"{$cgx_search}\"></td>\n";
echo "  </tr></table>\n";
echo "</form>\n";

echo "<form name='frmCUSTOMIZE' action='action/cgx_customize.php' method='post'>\n";
echo "<input type='hidden' name='back' value='" . urlencode($_SERVER['REQUEST_URI']) . "'>\n";
echo "<input type='hidden' name='dg_name' value='view.good_rcp'>\n";
echo "<input type='hidden' name='col[document_no]' value='on'>\n";
echo "<input type='hidden' name='col[m_inout_date]' value='on'>\n";
echo "<table id='columns' class='datagrid_bar' style='display: none;'><tr>\n";
echo "<td width='99%' valign='top'>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_document_no' name='col[document_no]' type='checkbox'></td><td width='99%'><label for='col_document_no'>No. Receipt</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_m_inout_date' name='col[m_inout_date]' type='checkbox'></td><td width='99%'><label for='col_m_inout_date'>Tgl Masuk</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['remark'] == 1 ? ' checked' : '') . " id='col_remark' name='col[remark]' type='checkbox'></td><td width='99%'><label for='col_remark'>No. Contract</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['partner_code'] == 1 ? ' checked' : '') . " id='col_partner_code' name='col[partner_code]' type='checkbox'></td><td width='99%'><label for='col_partner_code'>Kode Supplier</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['partner_name'] == 1 ? ' checked' : '') . " id='col_partner_name' name='col[partner_name]' type='checkbox'></td><td width='99%'><label for='col_partner_name'>Nama Supplier</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['no_kendaraan'] == 1 ? ' checked' : '') . " id='col_no_kendaraan' name='col[no_kendaraan]' type='checkbox'></td><td width='99%'><label for='col_no_kendaraan'>Nomor SJ</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['sj_date'] == 1 ? ' checked' : '') . " id='col_sj_date' name='col[sj_date]' type='checkbox'></td><td width='99%'><label for='col_sj_date'>Tanggal SJ</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['m_product_id'] == 1 ? ' checked' : '') . " id='col_m_product_id' name='col[m_product_id]' type='checkbox'></td><td width='99%'><label for='col_m_product_id'>ID Produk</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['product_code'] == 1 ? ' checked' : '') . " id='col_product_code' name='col[product_code]' type='checkbox'></td><td width='99%'><label for='col_product_code'>Item Number</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['product_name'] == 1 ? ' checked' : '') . " id='col_product_name' name='col[product_name]' type='checkbox'></td><td width='99%'><label for='col_product_name'>Produk Coil</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['m_coil_id'] == 1 ? ' checked' : '') . " id='col_m_coil_id' name='col[m_coil_id]' type='checkbox'></td><td width='99%'><label for='col_m_coil_id'>ID Coil</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['no_coil'] == 1 ? ' checked' : '') . " id='col_no_coil' name='col[no_coil]' type='checkbox'></td><td width='99%'><label for='col_no_coil'>No. Coil</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['no_lot'] == 1 ? ' checked' : '') . " id='col_no_lot' name='col[no_lot]' type='checkbox'></td><td width='99%'><label for='col_no_lot'>Kode Coil</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['warehouse_name'] == 1 ? ' checked' : '') . " id='col_warehouse_name' name='col[warehouse_name]' type='checkbox'></td><td width='99%'><label for='col_warehouse_name'>Gudang</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['weight'] == 1 ? ' checked' : '') . " id='col_weight' name='col[weight]' type='checkbox'></td><td width='99%'><label for='col_weight'>Berat (Kg)</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['quantity'] == 1 ? ' checked' : '') . " id='col_quantity' name='col[quantity]' type='checkbox'></td><td width='99%'><label for='col_quantity'>Quantity (Pcs)</label></td></tr></table>\n";
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
    form.setAttribute("action", "action/view.good_rcp.php");

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "mode");
    hiddenField.setAttribute("value", "export-all");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "document_no");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['document_no']; ?>");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "remark");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['remark']; ?>");
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
    hiddenField.setAttribute("name", "supplier");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['supplier']; ?>");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "no_coil");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['no_coil']; ?>");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "no_lot");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['no_lot']; ?>");
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

if ($document_no) $cgx_sql .= " AND m_inout.document_no LIKE '%" . mysql_escape_string($document_no) . "%'";
if ($remark) $cgx_sql .= " AND c_order.remark LIKE '%" . mysql_escape_string($remark) . "%'";
if ($item_number) $cgx_sql .= " AND (m_product.product_code LIKE '%" . mysql_escape_string($item_number) . "%' OR m_product.product_name LIKE '%" . mysql_escape_string($item_number) . "%' OR m_product.description LIKE '%" . mysql_escape_string($item_number) . "%' OR m_product.description_2 LIKE '%" . mysql_escape_string($item_number) . "%')";
if ($date_f) $cgx_sql .= " AND m_inout.m_inout_date >= '" . npl_dmy2ymd($date_f) . "'";
if ($date_t) $cgx_sql .= " AND m_inout.m_inout_date <= '" . npl_dmy2ymd($date_t) . "'";
if ($supplier) $cgx_sql .= " AND c_bpartner.partner_name LIKE '%" . mysql_escape_string($supplier) . "%'";
if ($no_coil) $cgx_sql .= " AND m_coil.no_coil LIKE '%" . mysql_escape_string($no_coil) . "%'";
if ($no_lot) $cgx_sql .= " AND m_coil.no_lot LIKE '%" . mysql_escape_string($no_lot) . "%'";

$cgx_test = $cgx_datagrid->bind($cgx_sql, $cgx_options);
if (PEAR::isError($cgx_test)) {
    echo $cgx_test->getMessage();
}

if ($cgx_def_columns['document_no'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('No. Receipt', 'document_no', 'document_no', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['m_inout_date'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Tanggal<br>Masuk', 'm_inout_date', 'm_inout_date', array('align' => 'left'), NULL, "cgx_format_date()"));
if ($cgx_def_columns['remark'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('No. Contract', 'remark', 'remark', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['partner_code'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Kode Supplier', 'partner_code', 'partner_code', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['partner_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Nama Supplier', 'partner_name', 'partner_name', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['no_kendaraan'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Nomor<br>Surat Jalan', 'no_kendaraan', 'no_kendaraan', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['sj_date'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Tanggal<br>Surat Jalan', 'sj_date', 'sj_date', array('align' => 'left'), NULL, "cgx_format_date()"));
if ($cgx_def_columns['m_product_id'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('ID Produk', 'm_product_id', 'm_product_id', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['product_code'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Item Number', 'product_code', 'product_code', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['product_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Produk Coil', 'product_name', 'product_name', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['m_coil_id'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('ID Coil', 'm_coil_id', 'm_coil_id', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['no_coil'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('No. Coil', 'no_coil', 'no_coil', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['no_lot'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Kode Coil', 'no_lot', 'no_lot', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['warehouse_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Gudang', 'warehouse_name', 'warehouse_name', array('align' => 'right'), NULL, NULL));
if ($cgx_def_columns['weight']== 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Berat<br>(Kg)', 'weight', 'weight', array('align' => 'right'), NULL, "cgx_format_3digit()"));
if ($cgx_def_columns['quantity'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Quantity<br>(Pcs)', 'quantity', 'quantity', array('align' => 'right'), NULL, "cgx_format_3digit()"));
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
