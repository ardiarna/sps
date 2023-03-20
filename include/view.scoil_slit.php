<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 12/01/2014 21:24:36
 */


echo "<div class='title'>Stock Coil Slit</div>";

require_once 'Structures/DataGrid.php';
require_once 'HTML/Table.php';

if (is_array($_SESSION[$GLOBALS['APP_ID']]['view.scoil_slit']['columns'])) {
    $cgx_def_columns = $_SESSION[$GLOBALS['APP_ID']]['view.scoil_slit']['columns'];
} else {
    $cgx_def_columns = array(
        'm_coil_slit_id' => 1,
        'product_code' => 1,
        'spec' => 1,
        'thickness' => 1,
        'od' => 1,
        'no_coil' => 1,
        'no_lot' => 1,        
        'quantity' => 1,
        'weight' => 1,
        'weight_total' => 1,
    );
    $_SESSION[$GLOBALS['APP_ID']]['view.scoil_slit']['columns'] = $cgx_def_columns;
}

$cgx_sql = "SELECT m_coil_slit_id, m_coil_slit.m_product_id, m_coil_slit.m_coil_id, 
if(out_qty = 1, 0, m_coil_slit.weight) weight, m_coil_slit.quantity,
            (if(out_qty = 1, 0, m_coil_slit.weight) * m_coil_slit.quantity) AS weight_total,
            no_coil, no_lot, product_code, spec, od, thickness, length 
            FROM m_coil_slit
            JOIN m_product ON (m_coil_slit.m_product_id=m_product.m_product_id) 
            JOIN m_coil ON(m_coil_slit.m_coil_id=m_coil.m_coil_id) 
            WHERE 1 = 1";

$cgx_sqltotal = "SELECT SUM(if(out_qty = 1, 0, m_coil_slit.weight)) AS weight, SUM(m_coil_slit.quantity) AS quantity,
            SUM((if(out_qty = 1, 0, m_coil_slit.weight) * m_coil_slit.quantity)) AS weight_total
            FROM m_coil_slit
            JOIN m_product ON (m_coil_slit.m_product_id=m_product.m_product_id) 
            JOIN m_coil ON(m_coil_slit.m_coil_id=m_coil.m_coil_id) 
            WHERE 1 = 1";

$cgx_datagrid = new Structures_DataGrid($cgx_max_rows);
$cgx_options = array('dsn' => $cgx_dsn);
$cgx_datagrid->setDefaultSort(array('no_lot' => 'ASC'));

$spec = urldecode($_REQUEST['spec']);
$od = urldecode($_REQUEST['od']);
$thickness = urldecode($_REQUEST['thickness']);
$length = urldecode($_REQUEST['length']);
$cgx_search = $_REQUEST['cgx_search'];

echo "<form name='frmFILTER' action='{$_SERVER['SCRIPT_NAME']}'>\n";
echo "<input type='hidden' name='m' value='{$_REQUEST['m']}'>\n";
echo "<table id='bar' class='datagrid_bar' width='100%' border='0'><tr>\n";
echo "<td align='right'>Spec</td>\n";
echo "<td align='left'>" . cgx_filter('spec', "SELECT DISTINCT spec, spec FROM m_coil_slit JOIN m_product ON(m_coil_slit.m_product_id=m_product.m_product_id) ORDER BY spec", $spec, TRUE) . "</td>\n";
echo "<td align='right'>Thickness</td>\n";
echo "<td align='left'>" . cgx_filter('thickness', "SELECT DISTINCT thickness, thickness FROM m_coil_slit JOIN m_product ON(m_coil_slit.m_product_id=m_product.m_product_id) ORDER BY thickness", $thickness, TRUE) . "</td>\n";
echo "<td align='right'>OD</td>\n";
echo "<td align='left'>" . cgx_filter('length', "SELECT DISTINCT length, length FROM m_coil_slit JOIN m_product ON(m_coil_slit.m_product_id=m_product.m_product_id) ORDER BY length", $length, TRUE) . "</td>\n";
echo "<td align='right'>Width</td>\n";
echo "<td align='left'>" . cgx_filter('od', "SELECT DISTINCT od, od FROM m_coil_slit JOIN m_product ON(m_coil_slit.m_product_id=m_product.m_product_id) ORDER BY od", $od, TRUE) . "</td>\n";    
echo "<td align='right'><input type='text' size='20' name='cgx_search' value=\"{$cgx_search}\"></td>\n";
echo "<td width='1'><input title='Cari' type='image' src='images/icon_search.png' border='0' style='padding-right: 20px;'></td>\n";
echo "<td></td>\n";
echo "<td width='20'></td>\n";
echo "<td width='1' class='datagrid_bar_icon'><a title='Export semua data ke CSV' href='javascript:exportCSV();'><img border='0' src='images/icon_csv.png'></a></td>\n";
echo "<td width='1' class='datagrid_bar_icon'><a title='Kustomisasi kolom' href='javascript:customizeColumn(true);'><img border='0' src='images/icon_columns.png'></a></td>\n";
echo "</tr></table>\n";
echo "</form>\n";

echo "<form name='frmCUSTOMIZE' action='action/cgx_customize.php' method='post'>\n";
echo "<input type='hidden' name='back' value='" . urlencode($_SERVER['REQUEST_URI']) . "'>\n";
echo "<input type='hidden' name='dg_name' value='view.scoil_slit'>\n";
echo "<table id='columns' class='datagrid_bar' style='display: none;'><tr>\n";
echo "<td width='99%' valign'top'>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_m_coil_slit_id' name='col[m_coil_slit_id]' type='checkbox'></td><td width='99%'><label for='col_m_coil_slit_id'>ID</label></td></tr></table>\n";    
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_product_code' name='col[product_code]' type='checkbox'></td><td width='99%'><label for='col_product_code'>Item Number</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['spec'] == 1 ? ' checked' : '') . " id='col_spec' name='col[spec]' type='checkbox'></td><td width='99%'><label for='col_spec'>Spec</label></td></tr></table>\n";   
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['thickness'] == 1 ? ' checked' : '') . " id='col_thickness' name='col[thickness]' type='checkbox'></td><td width='99%'><label for='col_thickness'>Thickness</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['length'] == 1 ? ' checked' : '') . " id='col_length' name='col[length]' type='checkbox'></td><td width='99%'><label for='col_length'>OD</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['od'] == 1 ? ' checked' : '') . " id='col_weight' name='col[od]' type='checkbox'></td><td width='99%'><label for='col_weight'>Width</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['no_coil'] == 1 ? ' checked' : '') . " id='col_no_coil' name='col[no_coil]' type='checkbox'></td><td width='99%'><label for='col_no_coil'>No. Coil</label></td></tr></table>\n";   
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['no_lot'] == 1 ? ' checked' : '') . " id='col_no_lot' name='col[no_lot]' type='checkbox'></td><td width='99%'><label for='col_no_lot'>Kode Coil</label></td></tr></table>\n";   
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['quantity'] == 1 ? ' checked' : '') . " id='col_quantity' name='col[quantity]' type='checkbox'></td><td width='99%'><label for='col_quantity'>Jumlah Slit</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['weight'] == 1 ? ' checked' : '') . " id='col_weight' name='col[weight]' type='checkbox'></td><td width='99%'><label for='col_weight'>Berat per Slit</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['weight_total'] == 1 ? ' checked' : '') . " id='col_weight_total' name='col[weight_total]' type='checkbox'></td><td width='99%'><label for='col_weight_total'>Berat Total</label></td></tr></table>\n"; 
echo "</td>\n";
echo "<td width='1' align='top'><input type='submit' value='Simpan'></td>\n";
echo "<td width='1' align='top'><input type='button' value='Batalkan' onclick='customizeColumn(false);'></td>\n";
echo "</tr></table>\n";
echo "</form>\n";

?>
    <script type="text/javascript">
    <!--
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

function exportCSV() {
    form = document.createElement("form");
    form.setAttribute("method", "post");
    form.setAttribute("action", "action/view.scoil_slit.php");

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "mode");
    hiddenField.setAttribute("value", "export-all");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "title_header");
    hiddenField.setAttribute("value", "<?php echo title_header('view.scoil_slit', org()); ?>");
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
    hiddenField.setAttribute("name", "cgx_search");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['cgx_search']; ?>");
    form.appendChild(hiddenField);

    document.body.appendChild(form);
    form.submit();    
}

    $(function(){
        $(".hasDatePicker").datepicker({
            showOn: "button",
            buttonImage: "images/calendar.png",
            buttonImageOnly: true,
            changeMonth: true,
            changeYear: true,
            yearRange: '1950:2050',
            dateFormat: 'dd-M-yy'
        });
    });
    //-->
    </script>
<?php

$cgx_sql .= " AND ( product_code LIKE '%{$cgx_search}%' OR no_coil LIKE '%{$cgx_search}%' OR no_lot LIKE '%{$cgx_search}%')";
if (strlen($spec) > 0) $cgx_sql .= " AND m_product.spec = '" . mysql_escape_string($spec) . "'";
if (strlen($od) > 0) $cgx_sql .= " AND m_product.od = '" . mysql_escape_string($od) . "'";
if (strlen($thickness) > 0) $cgx_sql .= " AND m_product.thickness = '" . mysql_escape_string($thickness) . "'";
if (strlen($length) > 0) $cgx_sql .= " AND m_product.length = '" . mysql_escape_string($length) . "'";

$cgx_sqltotal .= " AND ( product_code LIKE '%{$cgx_search}%' OR no_coil LIKE '%{$cgx_search}%' OR no_lot LIKE '%{$cgx_search}%')";
if (strlen($spec) > 0) $cgx_sqltotal .= " AND m_product.spec = '" . mysql_escape_string($spec) . "'";
if (strlen($od) > 0) $cgx_sqltotal .= " AND m_product.od = '" . mysql_escape_string($od) . "'";
if (strlen($thickness) > 0) $cgx_sqltotal .= " AND m_product.thickness = '" . mysql_escape_string($thickness) . "'";
if (strlen($length) > 0) $cgx_sqltotal .= " AND m_product.length = '" . mysql_escape_string($length) . "'";

if ($_SESSION[$GLOBALS['APP_ID']]['view.scoil_slit']['error']) {
    echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['view.scoil_slit']['error']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['view.scoil_slit']['error']);
}

if ($_SESSION[$GLOBALS['APP_ID']]['view.scoil_slit']['info']) {
    echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['view.scoil_slit']['info']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['view.scoil_slit']['info']);
}

$cgx_test = $cgx_datagrid->bind($cgx_sql, $cgx_options);
if (PEAR::isError($cgx_test)) {
    echo $cgx_test->getMessage();
}

if ($cgx_def_columns['m_coil_slit_id'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('ID', 'm_coil_slit_id', 'm_coil_slit_id', array('align' => 'right'), NULL, NULL));
if ($cgx_def_columns['product_code'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Item Number', 'product_code', 'product_code', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['spec'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Spec', 'spec', 'spec', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['thickness'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Thickness', 'thickness', 'thickness', array('align' => 'right'), NULL, NULL));
if ($cgx_def_columns['length'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('OD', 'length', 'length', array('align' => 'right'), NULL, NULL));
if ($cgx_def_columns['od'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Width', 'od', 'od', array('align' => 'right'), NULL, NULL));    
if ($cgx_def_columns['no_coil'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('No. Coil', 'no_coil', 'no_coil', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['no_lot'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Kode Coil', 'no_lot', 'no_lot', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['quantity'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Jumlah Slit', 'quantity', 'quantity', array('align' => 'right'), NULL, NULL));
if ($cgx_def_columns['weight'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Berat per Slit', 'weight', 'weight', array('align' => 'right'), NULL, NULL));
 if ($cgx_def_columns['weight_total'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Berat Total', 'weight_total', 'weight_total', array('align' => 'right'), NULL, NULL));
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
    echo "      <td><b>Grand Total Jumlah Slit</b></td>";
    echo "      <td width='10' align='center'>:</td>";
    echo "      <td align='right'><b>".  number_format($cgx_data_sum["quantity"], 2)."</b></td>";
    echo "      <td width='100px;'>&nbsp;</td>";
    echo "  </tr>"; 
    echo "  <tr>";
    echo "      <td><b>Grand Total Berat</b></td>";
    echo "      <td width='10' align='center'>:</td>";
    echo "      <td align='right'><b>".  number_format($cgx_data_sum["weight_total"], 2)."</b></td>";
    echo "  </tr>";     
    echo "  </table>";
    echo "</div>";

?>