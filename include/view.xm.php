<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 18/12/2013 21:19:49
 */


echo "<div class='title'>Box Masuk</div>";
   
require_once 'Structures/DataGrid.php';
require_once 'HTML/Table.php';

if (is_array($_SESSION[$GLOBALS['APP_ID']]['view.xm']['columns'])) {
    $cgx_def_columns = $_SESSION[$GLOBALS['APP_ID']]['view.xm']['columns'];
} else {
    $cgx_def_columns = array(
        'm_box_inout_id' => 1,
        'document_no' => 1,
        'm_box_inout_date' => 1,
        'partner_name'=> 1,
        'nama_supir' => 1,
        'no_truck' => 1,
        'box_number' => 1,
        'box_code' => 1,
    );
    $_SESSION[$GLOBALS['APP_ID']]['view.xm']['columns'] = $cgx_def_columns;
}

$cgx_sql = "SELECT mb.*, partner_name, box_number, box_code FROM m_box_inout mb 
            JOIN c_bpartner cb ON (mb.c_bpartner_id=cb.c_bpartner_id)
            JOIN m_box_inout_line mbl ON (mb.m_box_inout_id=mbl.m_box_inout_id)
            JOIN m_box ON (mbl.m_box_id=m_box.m_box_id)
            WHERE m_transaction_type_id = 7 ";
$cgx_datagrid = new Structures_DataGrid($cgx_max_rows);
$cgx_options = array('dsn' => $cgx_dsn);
$cgx_datagrid->setDefaultSort(array('m_box_inout_id' => 'DESC'));

$date_f = $_REQUEST['date_f'];
$date_t = $_REQUEST['date_t'];
$customer_name = $_REQUEST['customer_name'];
$document_no = $_REQUEST['document_no'];

echo "<form name='frmFILTER' action='{$_SERVER['SCRIPT_NAME']}'>\n";
echo "<input type='hidden' name='m' value='{$_REQUEST['m']}'>\n";
echo "<table id='bar' class='datagrid_bar' width='100%'><tr>\n";
echo "<td align='right'>No. Dokumen</td>\n";
echo "<td align='left'><input type='text'style='width: 150px;' name='document_no' value=\"{$document_no}\"></td>\n";
echo "<td align='right'>Customer</td>\n";
echo "<td align='left'><input type='text'style='width: 150px;' name='customer_name' value=\"{$customer_name}\"></td>\n";
echo "<td align='right'>Tanggal</td>\n";
echo "<td align='left' width='1'><input type='text'style='width: 120px; text-align: center;' id='date_f' name='date_f' value=\"{$date_f}\"></td>\n";
echo "<td align='center'>s/d</td>\n";
echo "<td align='left' width='1'><input type='text'style='width: 120px; text-align: center;' id='date_t' name='date_t' value=\"{$date_t}\"></td>\n";
echo "<td width='1'><input title='Cari' type='image' src='images/icon_search.png' border='0' style='padding-right: 20px;'></td>\n";
echo "<td></td>\n";
echo "<td width='20'></td>\n";
//echo "<td width='1' class='datagrid_bar_icon'><a title='Export semua data ke CSV' href='action/view.xm.php?mode=export-all'><img border='0' src='images/icon_csv.png'></a></td>\n";
echo "<td width='1' class='datagrid_bar_icon'><a title='Kustomisasi kolom' href='javascript:customizeColumn(true);'><img border='0' src='images/icon_columns.png'></a></td>\n";
echo "</tr></table>\n";
echo "</form>\n";

echo "<form name='frmCUSTOMIZE' action='action/cgx_customize.php' method='post'>\n";
echo "<input type='hidden' name='back' value='" . urlencode($_SERVER['REQUEST_URI']) . "'>\n";
echo "<input type='hidden' name='dg_name' value='view.xm'>\n";
echo "<input type='hidden' name='col[m_box_inout_id]' value='on'>\n";
echo "<input type='hidden' name='col[document_no]' value='on'>\n";
echo "<input type='hidden' name='col[m_box_inout_date]' value='on'>\n";
echo "<input type='hidden' name='col[partner_name]' value='on'>\n";
echo "<table id='columns' class='datagrid_bar' style='display: none;'><tr>\n";
echo "<td width='99%' valign='top'>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['m_box_inout_id'] == 1 ? ' checked' : '') . " id='col_m_box_inout_id' name='col[m_box_inout_id]' type='checkbox'></td><td width='99%'><label for='col_m_box_inout_id'>ID</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_document_no' name='col[document_no]' type='checkbox'></td><td width='99%'><label for='col_document_no'>Nomor Dokumen</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_m_box_inout_date' name='col[m_box_inout_date]' type='checkbox'></td><td width='99%'><label for='col_m_box_inout_date'>Tanggal</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_partner_name' name='col[partner_name]' type='checkbox'></td><td width='99%'><label for='col_partner_name'>Customer</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['nama_supir'] == 1 ? ' checked' : '') . " id='col_nama_supir' name='col[nama_supir]' type='checkbox'></td><td width='99%'><label for='col_nama_supir'>Supir</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['no_truck'] == 1 ? ' checked' : '') . " id='col_no_truck' name='col[no_truck]' type='checkbox'></td><td width='99%'><label for='col_no_truck'>No. Truk</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['box_number'] == 1 ? ' checked' : '') . " id='col_box_number' name='col[box_number]' type='checkbox'></td><td width='99%'><label for='col_box_number'>No. Box</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['box_code'] == 1 ? ' checked' : '') . " id='col_box_code' name='col[box_code]' type='checkbox'></td><td width='99%'><label for='col_box_code'>Kode Box</label></td></tr></table>\n";
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

if ($document_no) $cgx_sql .= " AND mb.document_no LIKE '%" . mysql_escape_string($document_no) . "%'";
if ($customer_name) $cgx_sql .= " AND cb.partner_name LIKE '%" . mysql_escape_string($customer_name) . "%'";
if ($date_f) $cgx_sql .= " AND m_box_inout_date >= '" . npl_dmy2ymd($date_f) . "'";
if ($date_t) $cgx_sql .= " AND m_box_inout_date <= '" . npl_dmy2ymd($date_t) . "'";    

if ($_SESSION[$GLOBALS['APP_ID']]['view.xm']['error']) {
    echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['view.xm']['error']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['view.xm']['error']);
}

if ($_SESSION[$GLOBALS['APP_ID']]['view.xm']['info']) {
    echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['view.xm']['info']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['view.xm']['info']);
}


$cgx_test = $cgx_datagrid->bind($cgx_sql, $cgx_options);
if (PEAR::isError($cgx_test)) {
    echo $cgx_test->getMessage();
}

if ($cgx_def_columns['m_box_inout_id'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('ID', 'm_box_inout_id', 'm_box_inout_id', array('align' => 'right'), NULL, NULL));
if ($cgx_def_columns['document_no'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Nomor Dokumen', 'document_no', 'document_no', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['m_box_inout_date'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Tanggal', 'm_box_inout_date', 'm_box_inout_date', array('align' => 'center'), NULL, "cgx_format_date()"));
if ($cgx_def_columns['partner_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Customer', 'partner_name', 'partner_name', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['nama_supir'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Supir', 'nama_supir', 'nama_supir', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['no_truck'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('No. Truk', 'no_truck', 'no_truck', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['box_number'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('No. Box', 'box_number', 'box_number', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['box_code'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Kode Box', 'box_code', 'box_code', array('align' => 'left'), NULL, NULL));
if (has_privilege('view.box-masuk')) $cgx_datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, 'cgx_edit()'));

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
