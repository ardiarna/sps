<?php

/**
 * (c) Azwari Nugraha <nugraha@pt-gai.com>
 * 25/11/2013 00:52:21
 */


echo "<div class='title'>Penerimaan Request LP</div>";

require_once 'Structures/DataGrid.php';
require_once 'HTML/Table.php';

function cgx_format_timestamp2($data) {
    if (cgx_emptydate($data['record'][$data['fieldName']])) return NULL;
    $format = strlen($GLOBALS['APP_DATETIME_FORMAT']) > 0 ? $GLOBALS['APP_DATETIME_FORMAT'] : 'd-M-Y H:i';
    return date($format, strtotime($data['record'][$data['fieldName']]));
}

if (is_array($_SESSION[$GLOBALS['APP_ID']]['view.prlp']['columns'])) {
    $cgx_def_columns = $_SESSION[$GLOBALS['APP_ID']]['view.prlp']['columns'];
} else {
    $cgx_def_columns = array(
        'tanggalpenerimaan' => 1,
        'nomordokumen' => 1,
        'norequest' => 1,
        'tanggalrequest' => 1,
        'nosc' => 1,
        'nopo' => 1,
        'partner_name' => 1,
        'product_name' => 1,
        'document_no_bmbj' => 1,
        'quantity_bmbj' => 1,
       
    );
    $_SESSION[$GLOBALS['APP_ID']]['view.prlp']['columns'] = $cgx_def_columns;
}
    $cgx_sql = "SELECT 
            m_receipt_longpipe.m_receipt_longpipe_id, 
            m_receipt_longpipe.receipt_date tanggalpenerimaan, 
            m_receipt_longpipe.document_no nomordokumen, 
            m_work_order.document_no norequest, 
            
            m_work_order.order_date tanggalrequest,
            remark nosc, 
            reference_no nopo,
            partner_name, 
            document_no_bmbj,
            quantity_bmbj,
            m_product.*,
            cu.user_fullname,
            m_receipt_longpipe.create_date,
            au.user_fullname user_fullname_u,
            m_receipt_longpipe.update_date

            FROM m_receipt_longpipe

            JOIN m_work_order USING (m_work_order_id)
            JOIN m_receipt_longpipe_line USING (m_receipt_longpipe_id)
            JOIN m_work_order_line USING (m_work_order_line_id)
 
            LEFT JOIN c_order USING (c_order_id) 
            JOIN c_bpartner ON (c_order.c_bpartner_id = c_bpartner.c_bpartner_id)
            JOIN m_product ON(m_work_order_line.m_product_material=m_product.m_product_id)
            LEFT JOIN app_user cu ON (m_receipt_longpipe.create_user=cu.user_id) 
            LEFT JOIN app_user au ON (m_receipt_longpipe.update_user=au.user_id)

            WHERE 1=1";
            
$cgx_datagrid = new Structures_DataGrid($cgx_max_rows);//$cgx_max_rows = 20
$cgx_options = array('dsn' => $cgx_dsn);//Array ( [dsn] => mysql://root:@localhost:3306/sps)
$cgx_datagrid->setDefaultSort(array('tanggalpenerimaan' => 'DESC'));

$date_f = $_REQUEST['date_f'];
$date_t = $_REQUEST['date_t'];
$date_f2 = $_REQUEST['date_f2'];
$date_t2 = $_REQUEST['date_t2'];
$item_number = $_REQUEST['item_number'];
$no_request = $_REQUEST['no_request'];
$sc_number = $_REQUEST['sc_number'];
$customer = $_REQUEST['customer'];

echo "<form name='frmFILTER' action='{$_SERVER['SCRIPT_NAME']}'>\n";
echo "  <input type='hidden' name='m' value='{$_REQUEST['m']}'>\n";
echo "      <table id='bar' class='datagrid_bar' width='100%'>
                <tr>\n";
echo "              <td align='right'>No. Request</td>\n";
echo "              <td align='left'><input type='text'style='width: 150px;' name='no_request' value=\"{$no_request}\"></td>\n";
echo "              <td align='right'>Item Number</td>\n";
echo "              <td align='left'><input type='text'style='width: 150px;' name='item_number' value=\"{$item_number}\"></td>\n";
echo "              <td align='right'>Tgl. Request</td>\n";
echo "              <td align='left' width='1'><input type='text'style='width: 120px; text-align: center;' id='date_f' name='date_f' value=\"{$date_f}\"></td>\n";
echo "              <td align='center'>s/d</td>\n";
echo "              <td align='left' width='1'><input type='text'style='width: 120px; text-align: center;' id='date_t' name='date_t' value=\"{$date_t}\"></td>\n";
echo "              <td width='1'><input title='Cari' type='image' src='images/icon_search.png' border='0' style='padding-right: 20px;'></td>\n";
echo "              <td></td>\n";
echo "              <td width='20'></td>\n"; 
echo "              <td width='1' class='datagrid_bar_icon'><a title='Export data ke CSV' href='javascript:exportCSV();'><img border='0' src='images/icon_csv.png'></a></td>\n";
echo "              <td width='1' class='datagrid_bar_icon'><a title='Kustomisasi kolom' href='javascript:customizeColumn(true);'><img border='0' src='images/icon_columns.png'></a></td>\n";
echo "          </tr>
                <tr>";
echo "              <td align='right'>No. SC</td>\n";
echo "              <td align='left'><input type='text'style='width: 150px;' name='sc_number' value=\"{$sc_number}\"></td>\n";
echo "              <td align='right'>Customer</td>\n";
echo "              <td align='left'><input type='text'style='width: 150px;' name='customer' value=\"{$customer}\"></td>\n";
echo "              <td align='right'>Tgl. Penerimaan</td>\n";
echo "              <td align='left' width='1'><input type='text'style='width: 120px; text-align: center;' id='date_f2' name='date_f2' value=\"{$date_f2}\"></td>\n";
echo "              <td align='center'>s/d</td>\n";
echo "              <td align='left' width='1'><input type='text'style='width: 120px; text-align: center;' id='date_t2' name='date_t2' value=\"{$date_t2}\"></td>\n";
echo "          </tr>
            </table>\n";
echo "</form>\n";

echo "<form name='frmCUSTOMIZE' action='action/cgx_customize.php' method='post'>\n";
echo "<input type='hidden' name='back' value='" . urlencode($_SERVER['REQUEST_URI']) . "'>\n";
echo "<input type='hidden' name='dg_name' value='view.prlp'>\n";
echo "<input type='hidden' name='col[tanggalpenerimaan]' value='on'>\n";
echo "<input type='hidden' name='col[nomordokumen]' value='on'>\n";
echo "<input type='hidden' name='col[norequest]' value='on'>\n";
echo "<input type='hidden' name='col[tanggalrequest]' value='on'>\n";
echo "  <table id='columns' class='datagrid_bar' style='display: none;'>
            <tr>\n";
echo "          <td width='99%' valign='top'>\n";
echo "          <table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_tanggalpenerimaan' name='col[tanggalpenerimaan]' type='checkbox'></td><td width='99%'><label for='col_tanggalpenerimaan'>Tanggal Penerimaan</label></td></tr></table>\n";
echo "          <table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_nomordokumen' name='col[nomordokumen]' type='checkbox'></td><td width='99%'><label for='col_nomordokumen'>Nomor Dokumen</label></td></tr></table>\n";
echo "          <table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_norequest' name='col[norequest]' type='checkbox'></td><td width='99%'><label for='col_norequest'>No Request</label></td></tr></table>\n";
echo "          <table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_tanggalrequest' name='col[tanggalrequest]' type='checkbox'></td><td width='99%'><label for='col_tanggalrequest'>Tanggal Request</label></td></tr></table>\n";
echo "          <table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['nosc'] == 1 ? ' checked' : '') . " id='col_nosc' name='col[nosc]' type='checkbox'></td><td width='99%'><label for='col_nosc'>No. SC</label></td></tr></table>\n";
echo "          <table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['nopo'] == 1 ? ' checked' : '') . " id='col_nopo' name='col[nopo]' type='checkbox'></td><td width='99%'><label for='col_nopo'>No. PO</label></td></tr></table>\n";
echo "          <table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['product_name'] == 1 ? ' checked' : '') . " id='col_product_name' name='col[product_name]' type='checkbox'></td><td width='99%'><label for='col_product_name'>Product Name</label></td></tr></table>\n";
echo "          <table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['partner_name'] == 1 ? ' checked' : '') . " id='col_partner_name' name='col[partner_name]' type='checkbox'></td><td width='99%'><label for='col_partner_name'>Pelanggan</label></td></tr></table>\n";
echo "          <table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['quantity_bmbj'] == 1 ? ' checked' : '') . " id='col_quantity_bmbj' name='col[quantity_bmbj]' type='checkbox'></td><td width='99%'><label for='col_quantity_bmbj'>Quantity Penerimaan</label></td></tr></table>\n";
echo "          <table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['product_name'] == 1 ? ' checked' : '') . " id='col_product_name' name='col[product_namquantitye]' type='checkbox'></td><td width='99%'><label for='col_product_name'>Product Name</label></td></tr></table>\n";
echo "          <table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['document_no_bmbj'] == 1 ? ' checked' : '') . " id='col_document_no_bmbj' name='col[document_no_bmbj]' type='checkbox'></td><td width='99%'><label for='col_document_no_bmbj'>BMBB</label></td></tr></table>\n";
echo "          <table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['user_fullname'] == 1 ? ' checked' : '') . " id='col_user_fullname' name='col[user_fullname]' type='checkbox'></td><td width='99%'><label for='col_user_fullname'>Create User</label></td></tr></table>\n";
echo "          <table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['create_date'] == 1 ? ' checked' : '') . " id='col_create_date' name='col[create_date]' type='checkbox'></td><td width='99%'><label for='col_create_date'>Create Date</label></td></tr></table>\n";
echo "          <table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['user_fullname_u'] == 1 ? ' checked' : '') . " id='col_user_fullname_u' name='col[user_fullname_u]' type='checkbox'></td><td width='99%'><label for='col_user_fullname_u'>Update User</label></td></tr></table>\n";
echo "          <table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['update_date'] == 1 ? ' checked' : '') . " id='col_update_date' name='col[update_date]' type='checkbox'></td><td width='99%'><label for='col_update_date'>Update Date</label></td></tr></table>\n";
echo "          </td>\n";
echo "          <td width='1' valign='top'><input type='submit' value='Simpan'></td>\n";
echo "          <td width='1' valign='top'><input type='button' value='Batalkan' onclick='customizeColumn(false);'></td>\n";
echo "      </tr></table>\n";
echo "</form>\n";
?>
<script type="text/javascript">
<!--

$(function(){
    $("#date_f").datepicker({dateFormat: '<?php echo $APP_DATE_FORMAT_JAVA; ?>'});
    $("#date_t").datepicker({dateFormat: '<?php echo $APP_DATE_FORMAT_JAVA; ?>'});
    $("#date_f2").datepicker({dateFormat : '<?php echo $APP_DATE_FORMAT_JAVA; ?>'});
    $("#date_t2").datepicker({dateFormat : '<?php echo $APP_DATE_FORMAT_JAVA; ?>'});        
});

function exportCSV() {
    form = document.createElement("form");
    form.setAttribute("method", "post");
    form.setAttribute("action", "action/view.prlp.php");

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "mode");
    hiddenField.setAttribute("value", "export-all");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "no_request");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['no_request']; ?>");
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
    hiddenField.setAttribute("name", "date_f2");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['date_f2']; ?>");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "date_t2");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['date_t2']; ?>");
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

if ($no_request) $cgx_sql .= " AND m_work_order.document_no LIKE '%" . mysql_escape_string($no_request) . "%'";
if ($item_number) $cgx_sql .= " AND (product_code LIKE '%" . mysql_escape_string($item_number) . "%' OR product_name LIKE '%" . mysql_escape_string($item_number) . "%' OR description LIKE '%" . mysql_escape_string($item_number) . "%')";
if ($date_f) $cgx_sql .= " AND m_work_order.order_date >= '" . npl_dmy2ymd($date_f) . "'";
if ($date_t) $cgx_sql .= " AND m_work_order.order_date <= '" . npl_dmy2ymd($date_t) . "'";
if ($date_f2) $cgx_sql .= " AND m_receipt_longpipe.receipt_date >= '". npl_dmy2ymd($date_f2)."'";
if ($date_t2) $cgx_sql .= " AND m_receipt_longpipe.receipt_date <= '". npl_dmy2ymd($date_t2)."'";
if ($sc_number) $cgx_sql .= " AND c_order.remark LIKE '%" . mysql_escape_string($sc_number) . "%'";
if ($customer) $cgx_sql .= " AND c_bpartner.partner_name LIKE '%" . mysql_escape_string($customer) . "%'";

$cgx_test = $cgx_datagrid->bind($cgx_sql, $cgx_options);//$cgx_test = 1
if (PEAR::isError($cgx_test)) {
    echo $cgx_test->getMessage();
}

if ($cgx_def_columns['tanggalpenerimaan'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Tanggal Penerimaan', 'tanggalpenerimaan', 'tanggalpenerimaan', array('align' => 'center'), NULL, "cgx_format_date()"));
if ($cgx_def_columns['nomordokumen'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Nomor Dokumen', 'nomordokumen', 'nomordokumen', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['norequest'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('No Request', 'norequest', 'norequest', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['tanggalrequest'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Tanggal Request', 'tanggalrequest', 'tanggalrequest', array('align' => 'center'), NULL, "cgx_format_date()"));
if ($cgx_def_columns['nosc'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('No. SC', 'nosc', 'nosc', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['nopo'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('No. PO', 'nopo', 'nopo', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['partner_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Pelanggan', 'partner_name', 'partner_name', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['product_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Product Name', 'product_name', 'product_name', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['document_no_bmbj'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('BMBB', 'document_no_bmbj', 'document_no_bmbj', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['quantity_bmbj'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Quantity Penerimaan', 'quantity_bmbj', 'quantity_bmbj', array('align' => 'right'), NULL, NULL));
if ($cgx_def_columns['user_fullname'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Create User', 'user_fullname', 'user_fullname', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['create_date'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Create Date', 'create_date', 'create_date', array('align' => 'left'), NULL, "cgx_format_timestamp2()"));
if ($cgx_def_columns['user_fullname_u'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Update User', 'user_fullname_u', 'user_fullname_u', array('align' => 'center'), NULL, NULL));
if ($cgx_def_columns['update_date'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Update Date', 'update_date', 'update_date', array('align' => 'center'), NULL, "cgx_format_timestamp2()"));

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