<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 05/12/2013 14:35:39
 */


echo "<div class='title'>Realisasi Work Order Slitting</div>";

require_once 'Structures/DataGrid.php';
require_once 'HTML/Table.php';

if (is_array($_SESSION[$GLOBALS['APP_ID']]['view.rwos']['columns'])) {
    $cgx_def_columns = $_SESSION[$GLOBALS['APP_ID']]['view.rwos']['columns'];
} else {
    $cgx_def_columns = array(
        'document_no' => 1,
        'production_date' => 1,
        'partner_name' => 1,
        'wo' => 1,
        'nama_fg' => 1,
        //'order_quantity' => 1,
        //'producted_quantity' => 1,
        //'warehouse_name' => 1,
        'good' => 1,
        'weight' => 1,
    );
    $_SESSION[$GLOBALS['APP_ID']]['view.rwos']['columns'] = $cgx_def_columns;
}

$cgx_sql = "SELECT
            m_prod_slit.*, 
            m_wo_slit.document_no wo, mp1.m_product_id as id_slit, 
            mp1.product_code as code_slit, 
            mp1.product_name as nama_slit, 
            m_wo_slit_line.*, 
            c_bpartner.partner_code, 
            c_bpartner.partner_name, 
            mp2.m_product_id as id_fg, 
            mp2.product_code as code_fg, 
            mp2.product_name as nama_fg, 
            mp2.spec, mp2.od, mp2.thickness, 
            mp2.length, auc.user_fullname, auu.user_fullname user_fullname_u, m_prod_slit_line.good as good, m_prod_slit_line.weight as weight, m_warehouse.warehouse_name 
            
            FROM m_prod_slit 
            JOIN m_prod_slit_line ON (m_prod_slit.m_prod_slit_id = m_prod_slit_line.m_prod_slit_id) 
            JOIN m_wo_slit ON (m_prod_slit.m_wo_slit_id = m_wo_slit.m_wo_slit_id) 
            JOIN m_wo_slit_line ON (m_prod_slit_line.m_wo_slit_line_id = m_wo_slit_line.m_wo_slit_line_id) 
            JOIN m_warehouse ON (m_prod_slit_line.m_warehouse_id = m_warehouse.m_warehouse_id) 
            JOIN m_product mp1 ON (m_wo_slit.m_product_id = mp1.m_product_id) 
            JOIN m_product mp2 ON (m_wo_slit_line.m_product_id = mp2.m_product_id) 
            JOIN c_bpartner ON (m_wo_slit.c_bpartner_id = c_bpartner.c_bpartner_id) 
            LEFT JOIN app_user auc ON (m_prod_slit.create_user=auc.user_id) 
            LEFT JOIN app_user auu ON (m_prod_slit.update_user=auu.user_id) 
            WHERE m_prod_slit.production_type = 1";
$cgx_sql .= " AND " . org_filter_trx('m_wo_slit.app_org_id');
//print_r($cgx_sql);
$cgx_datagrid = new Structures_DataGrid($cgx_max_rows);
$cgx_options = array('dsn' => $cgx_dsn);
$cgx_datagrid->setDefaultSort(array('production_date' => 'DESC'));

$date_f = $_REQUEST['date_f'];
$date_t = $_REQUEST['date_t'];
$item_number = $_REQUEST['item_number'];
$document_no = $_REQUEST['document_no'];
$wo = $_REQUEST['wo'];
$customer = $_REQUEST['customer'];

echo "<form name='frmFILTER' action='{$_SERVER['SCRIPT_NAME']}'>\n";
echo "<input type='hidden' name='m' value='{$_REQUEST['m']}'>\n";
echo "  <table id='bar' class='datagrid_bar' width='100%'><tr>\n";
echo "      <td align='right'>No. Produksi</td>\n";
echo "      <td align='left'><input type='text'style='width: 150px;' name='document_no' value=\"{$document_no}\"></td>\n";
echo "      <td align='right'>Item Number</td>\n";
echo "      <td align='left'><input type='text'style='width: 150px;' name='item_number' value=\"{$item_number}\"></td>\n";
echo "      <td align='right'>Tgl. Produksi</td>\n";
echo "      <td align='left' width='1'><input type='text'style='width: 120px; text-align: center;' id='date_f' name='date_f' value=\"{$date_f}\"></td>\n";
echo "      <td align='center'>s/d</td>\n";
echo "      <td align='left' width='1'><input type='text'style='width: 120px; text-align: center;' id='date_t' name='date_t' value=\"{$date_t}\"></td>\n";
echo "      <td width='1'><input title='Cari' type='image' src='images/icon_search.png' border='0' style='padding-right: 20px;'></td>\n";
echo "      <td></td>\n";
echo "      <td width='20'></td>\n"; 
echo "      <td width='1' class='datagrid_bar_icon'><a title='Export data ke CSV' href='javascript:exportCSV();'><img border='0' src='images/icon_csv.png'></a></td>\n";
echo "      <td width='1' class='datagrid_bar_icon'><a title='Kustomisasi kolom' href='javascript:customizeColumn(true);'><img border='0' src='images/icon_columns.png'></a></td>\n";
echo "  </tr><tr>";
echo "      <td align='right'>No. WO</td>\n";
echo "      <td align='left'><input type='text'style='width: 150px;' name='wo' value=\"{$wo}\"></td>\n";
echo "      <td align='right'>Customer</td>\n";
echo "      <td align='left'><input type='text'style='width: 150px;' name='customer' value=\"{$customer}\"></td>\n";
echo "      <td></td>\n";
//echo "      <td align='left'><input type='text' size='27' name='q' value=\"{$cgx_search}\"></td>\n";
echo "  </tr></table>\n";
echo "</form>\n";

echo "<form name='frmCUSTOMIZE' action='action/cgx_customize.php' method='post'>\n";
echo "<input type='hidden' name='back' value='" . urlencode($_SERVER['REQUEST_URI']) . "'>\n";
echo "<input type='hidden' name='dg_name' value='view.rwos'>\n";
echo "<input type='hidden' name='col[document_no]' value='on'>\n";
echo "<input type='hidden' name='col[production_date]' value='on'>\n";
echo "<table id='columns' class='datagrid_bar' style='display: none;'><tr>\n";
echo "<td width='99%' valign='top'>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_document_no' name='col[document_no]' type='checkbox'></td><td width='99%'><label for='col_document_no'>No. Produksi</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_production_date' name='col[production_date]' type='checkbox'></td><td width='99%'><label for='col_production_date'>Tgl Produksi</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['wo'] == 1 ? ' checked' : '') . " id='col_wo' name='col[wo]' type='checkbox'></td><td width='99%'><label for='col_wo'>No. WO</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['partner_code'] == 1 ? ' checked' : '') . " id='col_partner_code' name='col[partner_code]' type='checkbox'></td><td width='99%'><label for='col_partner_code'>Kode Customer</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['partner_name'] == 1 ? ' checked' : '') . " id='col_partner_name' name='col[partner_name]' type='checkbox'></td><td width='99%'><label for='col_partner_name'>Nama Customer</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['id_slit'] == 1 ? ' checked' : '') . " id='col_id_slit' name='col[id_slit]' type='checkbox'></td><td width='99%'><label for='col_id_slit'>ID Coil Slitting</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['code_slit'] == 1 ? ' checked' : '') . " id='col_code_slit' name='col[code_slit]' type='checkbox'></td><td width='99%'><label for='col_code_slit'>Item Number Slitting</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['nama_slit'] == 1 ? ' checked' : '') . " id='col_nama_slit' name='col[nama_slit]' type='checkbox'></td><td width='99%'><label for='col_nama_slit'>Product Coil Slitting</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['id_fg'] == 1 ? ' checked' : '') . " id='col_id_fg' name='col[id_fg]' type='checkbox'></td><td width='99%'><label for='col_id_fg'>ID Pipa</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['code_fg'] == 1 ? ' checked' : '') . " id='col_code_fg' name='col[code_fg]' type='checkbox'></td><td width='99%'><label for='col_code_fg'>Item Number Pipa</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['nama_fg'] == 1 ? ' checked' : '') . " id='col_nama_fg' name='col[nama_fg]' type='checkbox'></td><td width='99%'><label for='col_nama_fg'>Product Pipa</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['warehouse_name'] == 1 ? ' checked' : '') . " id='col_warehouse_name' name='col[warehouse_name]' type='checkbox'></td><td width='99%'><label for='col_warehouse_name'>Gudang</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['good'] == 1 ? ' checked' : '') . " id='col_good' name='col[good]' type='checkbox'></td><td width='99%'><label for='col_good'>Jumlah Slitted</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['weight'] == 1 ? ' checked' : '') . " id='col_weight' name='col[weight]' type='checkbox'></td><td width='99%'><label for='col_weight'>Berat per Slit</label></td></tr></table>\n";
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
    form.setAttribute("action", "action/view.rwos.php");

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
    hiddenField.setAttribute("name", "wo");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['wo']; ?>");
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

if ($document_no) $cgx_sql .= " AND m_prod_slit.document_no LIKE '%" . mysql_escape_string($document_no) . "%'";
if ($wo) $cgx_sql .= " AND m_wo_slit.document_no LIKE '%" . mysql_escape_string($wo) . "%'";
if ($item_number) $cgx_sql .= " AND (mp2.product_code LIKE '%" . mysql_escape_string($item_number) . "%' OR mp2.product_name LIKE '%" . mysql_escape_string($item_number) . "%' OR mp2.description LIKE '%" . mysql_escape_string($item_number) . "%')";
if ($date_f) $cgx_sql .= " AND m_prod_slit.production_date >= '" . npl_dmy2ymd($date_f) . "'";
if ($date_t) $cgx_sql .= " AND m_prod_slit.production_date <= '" . npl_dmy2ymd($date_t) . "'";
if ($customer) $cgx_sql .= " AND c_bpartner.partner_name LIKE '%" . mysql_escape_string($customer) . "%'";

$cgx_test = $cgx_datagrid->bind($cgx_sql, $cgx_options);
if (PEAR::isError($cgx_test)) {
    echo $cgx_test->getMessage();
}

if ($cgx_def_columns['document_no'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('No. Produksi', 'document_no', 'document_no', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['production_date'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Tgl Produksi', 'production_date', 'production_date', array('align' => 'left'), NULL, "cgx_format_date()"));
if ($cgx_def_columns['wo'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('No. WO', 'wo', 'wo', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['partner_code'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Kode Customer', 'partner_code', 'partner_code', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['partner_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Nama Customer', 'partner_name', 'partner_name', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['id_slit'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('ID Coil Raw', 'id_slit', 'id_slit', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['code_slit'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Item Number Raw', 'code_slit', 'code_slit', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['nama_slit'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Product Coil Raw', 'nama_slit', 'nama_slit', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['id_fg'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('ID Slitting', 'id_fg', 'id_fg', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['code_fg'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Item Number Slitting', 'code_fg', 'code_fg', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['nama_fg'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Product Slitting', 'nama_fg', 'nama_fg', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['warehouse_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Gudang', 'warehouse_name', 'warehouse_name', array('align' => 'right'), NULL, NULL));
if ($cgx_def_columns['good'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Jumlah Slitted', 'good', 'good', array('align' => 'right'), NULL, "cgx_format_3digit()"));
if ($cgx_def_columns['weight']== 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Berat per Slit', 'weight', 'weight', array('align' => 'right'), NULL, "cgx_format_3digit()"));
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
