<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 05/12/2013 14:35:39
 */


echo "<div class='title'>Work Order Pipa</div>";

require_once 'Structures/DataGrid.php';
require_once 'HTML/Table.php';

if (is_array($_SESSION[$GLOBALS['APP_ID']]['view.wo_pipa']['columns'])) {
    $cgx_def_columns = $_SESSION[$GLOBALS['APP_ID']]['view.wo_pipa']['columns'];
} else {
    $cgx_def_columns = array(
        'document_no' => 1,
        'order_date' => 1,
        'partner_name' => 1,
        'no_bon' => 1,
        'nama_fg' => 1,
        'order_quantity' => 1,
        'qty_production' => 1,
        'qty_outstanding' => 1,
    );
    $_SESSION[$GLOBALS['APP_ID']]['view.wo_pipa']['columns'] = $cgx_def_columns;
}

$cgx_filter1 = urldecode($_REQUEST['cgx_filter1']);
$date_f = $_REQUEST['date_f'];
$date_t = $_REQUEST['date_t'];
$item_number = $_REQUEST['item_number'];
$document_no = $_REQUEST['document_no'];
$customer = $_REQUEST['customer'];
$spec = urldecode($_REQUEST['spec']);
$od = urldecode($_REQUEST['od']);
$thickness = urldecode($_REQUEST['thickness']);
$length = urldecode($_REQUEST['length']);
//$cgx_search = $_REQUEST['q'];
$tgl_f = $_REQUEST['tgl_f'];
$tgl_t = $_REQUEST['tgl_t'];

$tgl_param = "";
if ($tgl_f) $tgl_param .= " AND production_date >= '" . npl_dmy2ymd($tgl_f) . "'";
if ($tgl_t) $tgl_param .= " AND production_date <= '" . npl_dmy2ymd($tgl_t) . "'";

$cgx_sql = "SELECT m_wo_pipa.*, m_wo_pipa_line.*, m_product.m_product_id as id_fg, c_bpartner.partner_code, c_bpartner.partner_name, 
    product_code as code_fg, product_name as nama_fg, 
    COALESCE(qty_production,0) qty_production,
    (m_wo_pipa_line.order_quantity - COALESCE(qty_production,0)) as qty_outstanding, cu.user_name as user_fullname,
    uu.user_name user_fullname_u, m_wo_pipa.create_date, m_wo_pipa.update_date
    FROM m_wo_pipa
    JOIN m_wo_pipa_line ON(m_wo_pipa.m_wo_pipa_id=m_wo_pipa_line.m_wo_pipa_id) 
    JOIN m_product ON (m_wo_pipa_line.m_product_id=m_product.m_product_id) 
    JOIN c_bpartner ON (m_wo_pipa_line.c_bpartner_id = c_bpartner.c_bpartner_id)
    LEFT JOIN (SELECT m_prod_slit_line.m_wo_slit_line_id, sum(good) qty_production
        FROM m_prod_slit
        JOIN m_prod_slit_line ON(m_prod_slit.m_prod_slit_id=m_prod_slit_line.m_prod_slit_id)
        JOIN m_wo_pipa_line ON (m_prod_slit_line.m_wo_slit_line_id = m_wo_pipa_line.m_wo_pipa_line_id)
        WHERE m_prod_slit.production_type = '2' ". $tgl_param ." GROUP BY m_wo_slit_line_id) col
    ON(m_wo_pipa_line.m_wo_pipa_line_id=col.m_wo_slit_line_id)
    LEFT JOIN app_user cu ON (m_wo_pipa.create_user = cu.user_id)
    LEFT JOIN app_user uu ON (m_wo_pipa.update_user = uu.user_id)
    WHERE 1 = 1";
    $cgx_sql .= " AND " . org_filter_trx('m_wo_pipa.app_org_id');

$cgx_sqltotal = "SELECT sum(order_quantity) as order_quantity, sum(COALESCE(qty_production,0)) as qty_production, 
    SUM(m_wo_pipa_line.order_quantity - COALESCE(qty_production,0)) as qty_outstanding  
    FROM m_wo_pipa
    JOIN m_wo_pipa_line ON(m_wo_pipa.m_wo_pipa_id=m_wo_pipa_line.m_wo_pipa_id) 
    JOIN m_product ON (m_wo_pipa_line.m_product_id=m_product.m_product_id) 
    JOIN c_bpartner ON (m_wo_pipa_line.c_bpartner_id = c_bpartner.c_bpartner_id)
    LEFT JOIN (SELECT m_prod_slit_line.m_wo_slit_line_id, sum(good) qty_production
        FROM m_prod_slit
        JOIN m_prod_slit_line ON(m_prod_slit.m_prod_slit_id=m_prod_slit_line.m_prod_slit_id)
        JOIN m_wo_pipa_line ON (m_prod_slit_line.m_wo_slit_line_id = m_wo_pipa_line.m_wo_pipa_line_id)
        WHERE m_prod_slit.production_type = '2' ". $tgl_param ." GROUP BY m_wo_slit_line_id) col
    ON(m_wo_pipa_line.m_wo_pipa_line_id=col.m_wo_slit_line_id)
    LEFT JOIN app_user cu ON (m_wo_pipa.create_user = cu.user_id)
    LEFT JOIN app_user uu ON (m_wo_pipa.update_user = uu.user_id)
    WHERE 1 = 1";
$cgx_sqltotal .= " AND " . org_filter_trx('m_wo_pipa.app_org_id');

$cgx_datagrid = new Structures_DataGrid($cgx_max_rows);
$cgx_options = array('dsn' => $cgx_dsn);
$cgx_datagrid->setDefaultSort(array('order_date' => 'DESC'));

echo "<form name='frmFILTER' action='{$_SERVER['SCRIPT_NAME']}'>\n";
echo "<input type='hidden' name='m' value='{$_REQUEST['m']}'>\n";
echo "  <table id='bar' class='datagrid_bar' width='100%'><tr>\n";
echo "      <td align='right'>No. WO</td>\n";
echo "      <td align='left'><input type='text'style='width: 150px;' name='document_no' value=\"{$document_no}\"></td>\n";
echo "      <td align='right'>Item Number</td>\n";
echo "      <td align='left'><input type='text'style='width: 150px;' name='item_number' value=\"{$item_number}\"></td>\n";
echo "      <td align='right'>Tgl. WO</td>\n";
echo "      <td align='left' width='1'><input type='text'style='width: 120px; text-align: center;' id='date_f' name='date_f' value=\"{$date_f}\"></td>\n";
echo "      <td align='center'>s/d</td>\n";
echo "      <td align='left' width='1'><input type='text'style='width: 120px; text-align: center;' id='date_t' name='date_t' value=\"{$date_t}\"></td>\n";
echo "      <td width='1'><input title='Cari' type='image' src='images/icon_search.png' border='0' style='padding-right: 20px;'></td>\n";
echo "      <td></td>\n";
echo "      <td width='20'></td>\n"; 
echo "      <td width='1' class='datagrid_bar_icon'><a title='Export data ke CSV' href='javascript:exportCSV();'><img border='0' src='images/icon_csv.png'></a></td>\n";
echo "      <td width='1' class='datagrid_bar_icon'><a title='Kustomisasi kolom' href='javascript:customizeColumn(true);'><img border='0' src='images/icon_columns.png'></a></td>\n";
echo "</tr><tr>";
echo "      <td colspan='2'></td>";
echo "      <td align='right'>Customer</td>\n";
echo "      <td align='left'><input type='text'style='width: 150px;' name='customer' value=\"{$customer}\"></td>\n";
echo "      <td align='right'>Tgl. Realisasi</td>\n";
echo "      <td align='left' width='1'><input type='text'style='width: 120px; text-align: center;' id='tgl_f' name='tgl_f' value=\"{$tgl_f}\"></td>\n";
echo "      <td align='center'>s/d</td>\n";
echo "      <td align='left' width='1'><input type='text'style='width: 120px; text-align: center;' id='tgl_t' name='tgl_t' value=\"{$tgl_t}\"></td>\n";
//echo "      <td align='left'><input type='text' size='27' name='q' value=\"{$cgx_search}\"></td>\n";
echo "</tr><tr>";
echo "      <td align='right'>Spec</td>\n";
echo "      <td align='left'>" . cgx_filter('spec', "SELECT DISTINCT spec, spec FROM m_wo_pipa_line JOIN m_product ON(m_wo_pipa_line.m_product_id=m_product.m_product_id) ORDER BY spec", $spec, TRUE) . "</td>\n";
echo "      <td align='right'>OD x T x L</td>\n";
echo "      <td align='left'>" . cgx_filter('od', "SELECT DISTINCT od, od FROM m_wo_pipa_line JOIN m_product ON(m_wo_pipa_line.m_product_id=m_product.m_product_id) ORDER BY od", $od, TRUE) ."x". cgx_filter('thickness', "SELECT DISTINCT thickness, thickness FROM m_wo_pipa_line JOIN m_product ON(m_wo_pipa_line.m_product_id=m_product.m_product_id) ORDER BY thickness", $thickness, TRUE) ."x". cgx_filter('length', "SELECT DISTINCT length, length FROM m_wo_pipa_line JOIN m_product ON(m_wo_pipa_line.m_product_id=m_product.m_product_id) ORDER BY length", $length, TRUE) . "</td>\n";
echo "      <td colspan='3'></td>\n";
echo "      <td>" . cgx_filter('cgx_filter1', array('O' => 'Outstanding'), $cgx_filter1, TRUE) . "</td>\n";
echo "</tr></table>\n";
echo "</form>\n";

echo "<form name='frmCUSTOMIZE' action='action/cgx_customize.php' method='post'>\n";
echo "<input type='hidden' name='back' value='" . urlencode($_SERVER['REQUEST_URI']) . "'>\n";
echo "<input type='hidden' name='dg_name' value='view.wo_pipa'>\n";
echo "<input type='hidden' name='col[document_no]' value='on'>\n";
echo "<input type='hidden' name='col[order_date]' value='on'>\n";
echo "<table id='columns' class='datagrid_bar' style='display: none;'><tr>\n";
echo "<td width='99%' valign='top'>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_document_no' name='col[document_no]' type='checkbox'></td><td width='99%'><label for='col_document_no'>No. WO</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_order_date' name='col[order_date]' type='checkbox'></td><td width='99%'><label for='col_order_date'>Tgl WO</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['partner_code'] == 1 ? ' checked' : '') . " id='col_partner_code' name='col[partner_code]' type='checkbox'></td><td width='99%'><label for='col_partner_code'>Kode Customer</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['no_bon'] == 1 ? ' checked' : '') . " id='col_no_bon' name='col[no_bon]' type='checkbox'></td><td width='99%'><label for='col_no_bon'>Nomor Bon</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['partner_name'] == 1 ? ' checked' : '') . " id='col_partner_name' name='col[partner_name]' type='checkbox'></td><td width='99%'><label for='col_partner_name'>Nama Customer</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['id_slit'] == 1 ? ' checked' : '') . " id='col_id_slit' name='col[id_slit]' type='checkbox'></td><td width='99%'><label for='col_id_slit'>ID Coil Slitting</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['code_slit'] == 1 ? ' checked' : '') . " id='col_code_slit' name='col[code_slit]' type='checkbox'></td><td width='99%'><label for='col_code_slit'>Item Number Slitting</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['nama_slit'] == 1 ? ' checked' : '') . " id='col_nama_slit' name='col[nama_slit]' type='checkbox'></td><td width='99%'><label for='col_nama_slit'>Product Coil Slitting</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['id_fg'] == 1 ? ' checked' : '') . " id='col_id_fg' name='col[id_fg]' type='checkbox'></td><td width='99%'><label for='col_id_fg'>ID Pipa</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['code_fg'] == 1 ? ' checked' : '') . " id='col_code_fg' name='col[code_fg]' type='checkbox'></td><td width='99%'><label for='col_code_fg'>Item Number Pipa</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['nama_fg'] == 1 ? ' checked' : '') . " id='col_nama_fg' name='col[nama_fg]' type='checkbox'></td><td width='99%'><label for='col_nama_fg'>Product Pipa</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['order_quantity'] == 1 ? ' checked' : '') . " id='col_order_quantity' name='col[order_quantity]' type='checkbox'></td><td width='99%'><label for='col_order_quantity'>Qty WO</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['qty_production'] == 1 ? ' checked' : '') . " id='col_qty_production' name='col[qty_production]' type='checkbox'></td><td width='99%'><label for='col_qty_production'>Qty Realisasi</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['qty_outstanding'] == 1 ? ' checked' : '') . " id='col_qty_outstanding' name='col[qty_outstanding]' type='checkbox'></td><td width='99%'><label for='col_qty_outstanding'>Qty Outstanding</label></td></tr></table>\n";
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
    $("#tgl_f").datepicker({dateFormat: '<?php echo $APP_DATE_FORMAT_JAVA; ?>'});
    $("#tgl_t").datepicker({dateFormat: '<?php echo $APP_DATE_FORMAT_JAVA; ?>'});
});

function exportCSV() {
    form = document.createElement("form");
    form.setAttribute("method", "post");
    form.setAttribute("action", "action/view.wo_pipa.php");

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
    hiddenField.setAttribute("name", "tgl_f");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['tgl_f']; ?>");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "tgl_t");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['tgl_t']; ?>");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "customer");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['customer']; ?>");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "spec");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['spec']; ?>");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "od");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['od']; ?>");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "thickness");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['thickness']; ?>");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "length");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['length']; ?>");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "cgx_filter1");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['cgx_filter1']; ?>");
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

if ($document_no) $cgx_sql .= " AND m_wo_pipa.document_no LIKE '%" . mysql_escape_string($document_no) . "%'";
if ($item_number) $cgx_sql .= " AND (m_product.product_code LIKE '%" . mysql_escape_string($item_number) . "%' OR m_product.product_name LIKE '%" . mysql_escape_string($item_number) . "%' OR m_product.description LIKE '%" . mysql_escape_string($item_number) . "%')";
if ($date_f) $cgx_sql .= " AND m_wo_pipa.order_date >= '" . npl_dmy2ymd($date_f) . "'";
if ($date_t) $cgx_sql .= " AND m_wo_pipa.order_date <= '" . npl_dmy2ymd($date_t) . "'";
if ($customer) $cgx_sql .= " AND c_bpartner.partner_name LIKE '%" . mysql_escape_string($customer) . "%'";
if (strlen($spec) > 0) {
    $cgx_sql .= " AND m_product.spec = '" . mysql_escape_string($spec) . "'";
    $cgx_sqltotal .= " AND m_product.spec = '" . mysql_escape_string($spec) . "'";
}
if (strlen($od) > 0) {
    $cgx_sql .= " AND m_product.od = '" . mysql_escape_string($od) . "'";
    $cgx_sqltotal .= " AND m_product.od = '" . mysql_escape_string($od) . "'";
}
if (strlen($thickness) > 0) {
    $cgx_sql .= " AND m_product.thickness = '" . mysql_escape_string($thickness) . "'";
    $cgx_sqltotal .= " AND m_product.thickness = '" . mysql_escape_string($thickness) . "'";
}
if (strlen($length) > 0) {
    $cgx_sql .= " AND m_product.length = '" . mysql_escape_string($length) . "'";
    $cgx_sqltotal .= " AND m_product.length = '" . mysql_escape_string($length) . "'";
}
if (strlen($cgx_filter1) > 0){
    switch ($cgx_filter1) {
        case 'O':
            $cgx_sql .= " AND (m_wo_pipa_line.order_quantity - COALESCE(qty_production,0)) > 0";            
            $cgx_sqltotal .= " AND (m_wo_pipa_line.order_quantity - COALESCE(qty_production,0)) > 0";            
            break;
    }
}

if ($document_no) $cgx_sqltotal .= " AND m_wo_pipa.document_no LIKE '%" . mysql_escape_string($document_no) . "%'";
if ($item_number) $cgx_sqltotal .= " AND (m_product.product_code LIKE '%" . mysql_escape_string($item_number) . "%' OR m_product.product_name LIKE '%" . mysql_escape_string($item_number) . "%' OR m_product.description LIKE '%" . mysql_escape_string($item_number) . "%')";
if ($date_f) $cgx_sqltotal .= " AND m_wo_pipa.order_date >= '" . npl_dmy2ymd($date_f) . "'";
if ($date_t) $cgx_sqltotal .= " AND m_wo_pipa.order_date <= '" . npl_dmy2ymd($date_t) . "'";
if ($customer) $cgx_sqltotal .= " AND c_bpartner.partner_name LIKE '%" . mysql_escape_string($customer) . "%'";

$cgx_test = $cgx_datagrid->bind($cgx_sql, $cgx_options);
if (PEAR::isError($cgx_test)) {
    echo $cgx_test->getMessage();
}

if ($cgx_def_columns['document_no'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('No. WO', 'document_no', 'document_no', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['order_date'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Tgl WO', 'order_date', 'order_date', array('align' => 'center'), NULL, "cgx_format_date()"));
if ($cgx_def_columns['no_bon'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Nomor Bon', 'no_bon', 'no_bon', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['partner_code'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Kode Customer', 'partner_code', 'partner_code', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['partner_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Nama Customer', 'partner_name', 'partner_name', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['id_slit'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('ID Coil Slitting', 'id_slit', 'id_slit', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['code_slit'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Item Number Slitting', 'code_slit', 'code_slit', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['nama_slit'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Product Coil Slitting', 'nama_slit', 'nama_slit', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['id_fg'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('ID Pipa', 'id_fg', 'id_fg', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['code_fg'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Item Number Pipa', 'code_fg', 'code_fg', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['nama_fg'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Product Pipa', 'nama_fg', 'nama_fg', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['order_quantity'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Qty WO', 'order_quantity', 'order_quantity', array('align' => 'right'), NULL, "cgx_format_3digit()"));
if ($cgx_def_columns['qty_production'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Qty Realisasi', 'qty_production', 'qty_production', array('align' => 'right'), NULL, "cgx_format_3digit()"));
if ($cgx_def_columns['qty_outstanding'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Qty Outstanding', 'qty_outstanding', 'qty_outstanding', array('align' => 'right'), NULL, "cgx_format_3digit()"));
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

    $cgx_data_sum = cgx_fetch_table($cgx_sqltotal);

    echo "<div class='tbl-header-box' style='font-size: 12px; border: 1px solid #ccc; margin-top: 10px;'>";
    echo "  <table class=''>";
    echo "  <tr>";
    echo "      <td><b>Total Quantity WO</b></td>";
    echo "      <td width='10' align='center'>:</td>";
    echo "      <td align='right'><b>".  number_format($cgx_data_sum["order_quantity"], 2)."</b></td>";
    echo "      <td width='100px;'>&nbsp;</td>";
    echo "  </tr>"; 
    echo "  <tr>";
    echo "      <td><b>Total Quantity Realisasi</b></td>";
    echo "      <td width='10' align='center'>:</td>";
    echo "      <td align='right'><b>".  number_format($cgx_data_sum["qty_production"], 2)."</b></td>";
    echo "      <td width='100px;'>&nbsp;</td>";
    echo "  </tr>";
    echo "  <tr>";
    echo "      <td colspan='3'><hr></td>";
    echo "  </tr>";
    echo "  <tr>";
    echo "      <td><b>Total Quantity Outstanding</b></td>";
    echo "      <td width='10' align='center'>:</td>";
    echo "      <td align='right'><b>".  number_format($cgx_data_sum["qty_outstanding"], 2)."</b></td>";
    echo "      <td width='100px;'>&nbsp;</td>";
    echo "  </tr>";
    echo "  </table>";
    echo "</div>";

?>
