<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 13/01/2014 00:26:12
 */

echo "<div class='title'>Box</div>";

require_once 'Structures/DataGrid.php';
require_once 'HTML/Table.php';

if (is_array($_SESSION[$GLOBALS['APP_ID']]['view.box2']['columns'])) {
    $cgx_def_columns = $_SESSION[$GLOBALS['APP_ID']]['view.box2']['columns'];
} else {
    $cgx_def_columns = array(
        'm_box_id' => 1,
        'box_number' => 1,
        'box_code' => 1,
        'kapasitas_box' => 1,
        'partner_name' => 1,
    );
    $_SESSION[$GLOBALS['APP_ID']]['view.box2']['columns'] = $cgx_def_columns;
}

$cgx_sql = "SELECT
            MAX(m_box_inout.m_box_inout_date) tanggal,
            m_box.*,
            cbbox.partner_name partner_name,
            cblok.partner_name partner_name_lok,
            if(m_box.location='I','Di dalam','Di luar') as location
            
            from m_box
            
            LEFT JOIN m_box_inout_line ON m_box.m_box_id = m_box_inout_line.m_box_id
            LEFT JOIN m_box_inout ON m_box_inout_line.m_box_inout_id = m_box_inout.m_box_inout_id
            LEFT JOIN c_bpartner cbbox ON m_box.c_bpartner_id = cbbox.c_bpartner_id
            LEFT JOIN c_bpartner cblok ON m_box_inout.c_bpartner_id = cblok.c_bpartner_id
            
            where 1 = 1 AND ". org_filter_trx('m_box.app_org_id');

$cgx_datagrid = new Structures_DataGrid($cgx_max_rows);
$cgx_options = array('dsn' => $cgx_dsn);

$f1 = urldecode($_REQUEST['f1']);
$customer = $_REQUEST['customer'];
$box_code= $_REQUEST['box_code'];
$box_number = $_REQUEST['box_number'];
$customer_lok = $_REQUEST['customer_lok'];
$date_inout_f = $_REQUEST['date_inout_f'];
$date_inout_t = $_REQUEST['date_inout_t'];

echo "<form name='frmFILTER' action='{$_SERVER['SCRIPT_NAME']}'>\n";
echo "<input type='hidden' name='m' value='{$_REQUEST['m']}'>\n";
echo "  <table id='bar' class='datagrid_bar' width='100%' border='0'>
            <tr>\n";
echo "          <td align='right'>Box Number</td>\n";
echo "          <td align='left'><input type='text'style='width: 150px;' name='box_number' value=\"{$box_number}\"></td>\n";
echo "          <td align='right'>Customer</td>\n";
echo "          <td align='left'><input type='text'style='width: 150px;' name='customer' value=\"{$customer}\"></td>\n";

echo "          <td align='right'>Tanggal</td>\n";
echo "          <td align='left' width='1'><input type='text'style='width: 100px; text-align: center;' id='date_inout_f' name='date_inout_f' value=\"{$date_inout_f}\"></td>\n";
echo "          <td align='center'>s/d</td>\n";
echo "          <td align='left' width='1'><input type='text'style='width: 100px; text-align: center;' id='date_inout_t' name='date_inout_t' value=\"{$date_inout_t}\"></td>\n";

echo "          <td width='1'><input title='Cari' type='image' src='images/icon_search.png' border='0' style='padding-right: 20px;'></td>\n";

echo "          <td width='1' class='datagrid_bar_icon'><a title='Export semua data ke CSV' href='javascript:exportCSV();'><img border='0' src='images/icon_csv.png'></a></td>\n";
echo "          <td width='1' class='datagrid_bar_icon'><a title='Kustomisasi kolom' href='javascript:customizeColumn(true);'><img border='0' src='images/icon_columns.png'></a></td>\n";
echo "      </tr>";
echo "      <tr>";
echo "          <td align='right'>Box Code</td>\n";
echo "          <td align='left'><input type='text'style='width: 150px;' name='box_code' value=\"{$box_code}\"></td>\n";
echo "          <td align='right'>Lokasi Customer</td>\n";
echo "          <td align='left'><input type='text'style='width: 150px;' name='customer_lok' value=\"{$customer_lok}\"></td>\n";
echo "          <td align='right'>Lokasi</td>\n";
echo "          <td>" . cgx_filter('f1', array('I' => 'Di dalam', 'O' => 'Di luar'), $f1, TRUE) . "</td>\n";
echo "          <td></td>\n";
echo "          <td width='20'></td>\n";
echo "          <td></td>\n";
echo "      </tr>";
echo "  </table>\n";
echo "</form>\n";

echo "<form name='frmCUSTOMIZE' action='action/cgx_customize.php' method='post'>\n";
echo "  <input type='hidden' name='back' value='" . urlencode($_SERVER['REQUEST_URI']) . "'>\n";
echo "  <input type='hidden' name='dg_name' value='view.box2'>\n";
echo "  <input type='hidden' name='col[m_box_id]' value='on'>\n";
echo "      <table id='columns' class='datagrid_bar' style='display: none;'><tr>\n";                                                                                                             
echo "          <td width='99%' valign='top'>\n";
echo "              <table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_m_box_id' name='col[m_box_id]' type='checkbox'></td><td width='99%'><label for='col_m_box_id'>ID</label></td></tr></table>\n";
echo "              <table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['box_number'] == 1 ? ' checked' : '') . " id='col_box_number' name='col[box_number]' type='checkbox'></td><td width='99%'><label for='col_box_number'>Box Number</label></td></tr></table>\n";
echo "              <table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['box_code'] == 1 ? ' checked' : '') . " id='col_box_code' name='col[box_code]' type='checkbox'></td><td width='99%'><label for='col_box_code'>Box Code</label></td></tr></table>\n";
echo "              <table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['box_size'] == 1 ? ' checked' : '') . " id='col_box_size' name='col[box_size]' type='checkbox'></td><td width='99%'><label for='col_box_size'>Box Size</label></td></tr></table>\n";
echo "              <table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['pipe_size'] == 1 ? ' checked' : '') . " id='col_pipe_size' name='col[pipe_size]' type='checkbox'></td><td width='99%'><label for='col_pipe_size'>Pipe Size</label></td></tr></table>\n";
echo "              <table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['kapasitas_box'] == 1 ? ' checked' : '') . " id='col_kapasitas_box' name='col[kapasitas_box]' type='checkbox'></td><td width='99%'><label for='col_kapasitas_box'>Kapasitas Box</label></td></tr></table>\n";
echo "              <table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['c_bpartner_id'] == 1 ? ' checked' : '') . " id='col_c_bpartner_id' name='col[c_bpartner_id]' type='checkbox'></td><td width='99%'><label for='col_c_bpartner_id'>Customer Code</label></td></tr></table>\n";
echo "              <table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['partner_name'] == 1 ? ' checked' : '') . " id='col_partner_name' name='col[partner_name]' type='checkbox'></td><td width='99%'><label for='col_partner_name'>Customer</label></td></tr></table>\n";
echo "              <table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['location'] == 1 ? ' checked' : '') . " id='col_location' name='col[location]' type='checkbox'></td><td width='99%'><label for='col_location'>Location</label></td></tr></table>\n";
echo "              <table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['partner_name_lok'] == 1 ? ' checked' : '') . " id='col_partner_name_lok' name='col[partner_name_lok]' type='checkbox'></td><td width='99%'><label for='col_partner_name_lok'>Lokasi Customer</label></td></tr></table>\n";
echo "              <table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['tanggal'] == 1 ? ' checked' : '') . " id='col_tanggal' name='col[tanggal]' type='checkbox'></td><td width='99%'><label for='col_tanggal'>Tanggal</label></td></tr></table>\n";
echo "              <table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['update_date'] == 1 ? ' checked' : '') . " id='col_update_date' name='col[update_date]' type='checkbox'></td><td width='99%'><label for='col_update_date'>Update Date</label></td></tr></table>\n";

echo "          </td>\n";
echo "          <td width='1' valign='top'><input type='submit' value='Simpan'></td>\n";
echo "          <td width='1' valign='top'><input type='button' value='Batalkan' onclick='customizeColumn(false);'></td>\n";
echo "      </tr></table>\n";
echo "</form>\n";
?>
<script type="text/javascript">

$(function() {
    $("#date_inout_f").datepicker({dateFormat: '<?php echo $APP_DATE_FORMAT_JAVA; ?>'});
    $("#date_inout_t").datepicker({dateFormat: '<?php echo $APP_DATE_FORMAT_JAVA; ?>'});
});

function exportCSV() {   
    form = document.createElement("form");
    form.setAttribute("method", "post");
    form.setAttribute("action", "action/view.box2.php");

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
    hiddenField.setAttribute("name", "box_code");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['box_code']; ?>");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "box_number");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['box_number']; ?>");
    form.appendChild(hiddenField);
    
    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "customer_lok");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['customer_lok']; ?>");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "f1");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['f1']; ?>");
    form.appendChild(hiddenField);
    
    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "date_inout_f");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['date_inout_f']; ?>");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "date_inout_t");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['date_inout_t']; ?>");
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

</script>
<?php
if (strlen($f1) > 0) $cgx_sql .= " AND m_box.location = '" . mysql_escape_string($f1) . "'";
if ($box_code) $cgx_sql .= " AND m_box.box_code LIKE '%" . mysql_escape_string($box_code) . "%'";
if ($box_number) $cgx_sql .= " AND m_box.box_number LIKE '%" . mysql_escape_string($box_number) . "%'";
if ($customer) $cgx_sql .= " AND cbbox.partner_name LIKE '%" . mysql_escape_string($customer) . "%'";
if ($customer_lok) $cgx_sql .= " AND cblok.partner_name LIKE '%" . mysql_escape_string($customer_lok) . "%'"; 
$cgx_sql .= " GROUP BY m_box.m_box_id";
if ($date_inout_f) $cgx_sql .= " HAVING tanggal >= '" . npl_dmy2ymd($date_inout_f) . "'";
if ($date_inout_t) $cgx_sql .= " AND tanggal <= '" . npl_dmy2ymd($date_inout_t) . "'";
if ($date_inout_f or $date_inout_t) $cgx_sql .= " ORDER BY tanggal DESC";

$cgx_test = $cgx_datagrid->bind($cgx_sql, $cgx_options);
if (PEAR::isError($cgx_test)) {
    echo $cgx_test->getMessage();
}

if ($cgx_def_columns['m_box_id'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('ID', 'm_box_id', 'm_box_id', array('align' => 'right'), NULL, NULL));
if ($cgx_def_columns['box_number'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Box Number', 'box_number', 'box_number', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['box_code'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Box Code', 'box_code', 'box_code', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['box_size'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Box Size', 'box_size', 'box_size', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['pipe_size'] == 1)$cgx_datagrid->addColumn(new Structures_DataGrid_Column('Pipe Size', 'pipe_size', 'pipe_size', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['kapasitas_box'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Kapasitas Box', 'kapasitas_box', 'kapasitas_box', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['c_bpartner_id'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Customer Code', 'c_bpartner_id', 'c_bpartner_id', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['partner_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Customer', 'partner_name', 'partner_name', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['location'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Lokasi', 'location', 'location', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['partner_name_lok'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Lokasi Customer', 'partner_name_lok', 'partner_name_lok', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['update_date'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Update Date', 'update_date', 'update_date', array('align' => 'center'), NULL, "cgx_format_date()"));
if ($cgx_def_columns['tanggal'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Tanggal', 'tanggal', 'tanggal', array('align' => 'center'), NULL, "cgx_format_date()"));

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
?>