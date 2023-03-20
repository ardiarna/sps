<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 12/01/2014 21:24:36
 */


echo "<div class='title'>Stock Warehouse</div>";

require_once 'Structures/DataGrid.php';
require_once 'HTML/Table.php';

if (is_array($_SESSION[$GLOBALS['APP_ID']]['view.psb']['columns'])) {
    $cgx_def_columns = $_SESSION[$GLOBALS['APP_ID']]['view.psb']['columns'];
} else {
    $cgx_def_columns = array(
        'm_product_id' => 1,
        'warehouse_name' => 1,        
        'product_code' => 1,
        //'partner_name' => 1,
        'spec' => 1,
        'od' => 1,
        'thickness' => 1,
        'length' => 1,
        //'minimum_qty' => 1,
        'balance_quantity' => 1,
    );
    $_SESSION[$GLOBALS['APP_ID']]['view.psb']['columns'] = $cgx_def_columns;
}

// if ($_REQUEST['f1']) {
//     $cgx_sql = "SELECT * FROM m_product JOIN m_stock_warehouse_2 USING (m_product_id) "; //WHERE latest = 'Y' ";
//     $cgx_sql .= " JOIN m_warehouse ON m_stock_warehouse_2.m_warehouse_id = m_warehouse.m_warehouse_id and org_allowed(m_warehouse.app_org_id) LIKE '%|" . org() . "|%' ";
//     $cgx_sql .= " WHERE latest = 'Y' ";
// } else {
//     //$cgx_sql = "SELECT * FROM m_product JOIN m_stock_balance_d USING (m_product_id) ";
//     $cgx_sql = "SELECT * FROM m_product JOIN m_stock_warehouse_2 USING (m_product_id) ";
//     $cgx_sql .= " JOIN m_warehouse ON m_stock_warehouse_2.m_warehouse_id = m_warehouse.m_warehouse_id and org_allowed(m_warehouse.app_org_id) LIKE '%|" . org() . "|%' ";
//     $cgx_sql .= " WHERE latest = 'Y' ";
// }



$cgx_sql = "SELECT * FROM m_stock_warehouse_2
            JOIN m_product USING (m_product_id)
            JOIN m_warehouse ON m_stock_warehouse_2.m_warehouse_id = m_warehouse.m_warehouse_id 
            LEFT JOIN c_bpartner ON(m_product.c_bpartner_id = c_bpartner.c_bpartner_id)
            WHERE latest = 'Y'";
$cgx_sql .= " AND " . org_filter_trx("m_stock_warehouse_2.app_org_id");

$cgx_datagrid = new Structures_DataGrid($cgx_max_rows);
$cgx_options = array('dsn' => $cgx_dsn);

$f1 = $_REQUEST['f1'];
$q = $_REQUEST['q'];
//$f1 = $_REQUEST['f1'];

echo "<form name='frmFILTER' action='{$_SERVER['SCRIPT_NAME']}'>\n";
echo "<input type='hidden' name='m' value='{$_REQUEST['m']}'>\n";
echo "<table id='bar' class='datagrid_bar' width='100%' border='0'><tr>\n";
echo "<td>\n";
echo "<table align='left' cellspacing='0' cellpadding='0' border='0'><tr>\n";
echo "<td><nobr><label for='f1'>Warehouse</label></nobr></td>\n";
echo "<td>&nbsp;</td>\n";
echo "<td>" . cgx_filter('f1', "SELECT m_warehouse_id, warehouse_name FROM m_warehouse WHERE app_org_id = '" . org() . "' ORDER BY warehouse_name", $f1, TRUE, NULL, "[ ALL WAREHOUSE ]") . "</td>\n";
echo "<td width='20'></td>\n";
echo "</tr></table>\n";
echo "</td>\n";
echo "<td align='right'><input type='text' size='20' name='q' value=\"{$q}\"></td>\n";
echo "<td width='1'><input title='Cari' type='image' src='images/icon_search.png' border='0' style='padding-right: 20px;'></td>\n";
echo "<td></td>\n";

echo "<td width='20'></td>\n";
echo "<td width='1' class='datagrid_bar_icon'><a title='Export semua data ke CSV' href='javascript:exportCSV();'><img border='0' src='images/icon_csv.png'></a></td>\n";
echo "<td width='1' class='datagrid_bar_icon'><a title='Kustomisasi kolom' href='javascript:customizeColumn(true);'><img border='0' src='images/icon_columns.png'></a></td>\n";
    
echo "</tr></table>\n";
echo "</form>\n";

echo "<form name='frmCUSTOMIZE' action='action/cgx_customize.php' method='post'>\n";
echo "<input type='hidden' name='back' value='" . urlencode($_SERVER['REQUEST_URI']) . "'>\n";
echo "<input type='hidden' name='dg_name' value='view.psb'>\n";
echo "<table id='columns' class='datagrid_bar' style='display: none;'><tr>\n";
echo "<td width='99%' valign'top'>\n";

echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" .($cgx_def_columns['m_product_id'] == 1 ? ' checked' : '') ." id='col_m_product_id' name='col[m_product_id]' type='checkbox'></td><td width='99%'><label for='col_m_product_id'>ID</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['warehouse_name'] == 1 ? ' checked' : '') . " id='col_warehouse_name' name='col[warehouse_name]' type='checkbox'></td><td width='99%'><label for='col_warehouse_name'>Warehouse</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['product_code'] == 1 ? ' checked' : '') . " id='col_product_code' name='col[product_code]' type='checkbox'></td><td width='99%'><label for='col_product_code'>Item Number</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['partner_name'] == 1 ? ' checked' : '') . " id='col_partner_name' name='col[partner_name]' type='checkbox'></td><td width='99%'><label for='col_partner_name'>Partner Name</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['spec'] == 1 ? ' checked' : '') . " id='col_spec' name='col[spec]' type='checkbox'></td><td width='99%'><label for='col_spec'>Spec</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['od'] == 1 ? ' checked' : '') . " id='col_od' name='col[od]' type='checkbox'></td><td width='99%'><label for='col_od'>OD</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['thickness'] == 1 ? ' checked' : '') . " id='col_thickness' name='col[thickness]' type='checkbox'></td><td width='99%'><label for='col_thickness'>Tebal</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['length'] == 1 ? ' checked' : '') . " id='col_length' name='col[length]' type='checkbox'></td><td width='99%'><label for='col_length'>Panjang</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['minimum_qty'] == 1 ? ' checked' : '') . " id='col_minimum_qty' name='col[minimum_qty]' type='checkbox'></td><td width='99%'><label for='col_minimum_qty'>Minimum Qty</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['balance_quantity'] == 1 ? ' checked' : '') . " id='col_balance_quantity' name='col[balance_quantity]' type='checkbox'></td><td width='99%'><label for='col_balance_quantity'>Balance Quantity</label></td></tr></table>\n";


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
    form.setAttribute("action", "action/view.psb.php");

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "mode");
    hiddenField.setAttribute("value", "export-all");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "f1");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['f1']; ?>");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "q");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['q']; ?>");
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

if ($f1) $cgx_sql .= " AND m_stock_warehouse_2.m_warehouse_id = '" . mysql_escape_string($f1) . "'";

if($q) $cgx_sql .= " and ( m_product_id LIKE '%{$q}%' OR product_code LIKE '%{$q}%'
        OR product_name LIKE '%{$q}%' OR partner_name LIKE '%{$q}%'
        OR od LIKE '%{$q}%' OR spec LIKE '%{$q}%' OR thickness LIKE '%{$q}%' OR length LIKE'%{$q}%')";
      
       

//$cgx_sql .= " and ( m_product.product_code LIKE '%{$q}%')";

//if ($_REQUEST['product_code']) $cgx_sql .= " AND product_code LIKE '%{$_REQUEST['product_code']}%'";
//if ($_REQUEST['product_name']) $cgx_sql .= " AND product_name LIKE '%{$_REQUEST['product_name']}%'";

if ($_SESSION[$GLOBALS['APP_ID']]['view.psb']['error']) {
    echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['view.psb']['error']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['view.psb']['error']);
}

if ($_SESSION[$GLOBALS['APP_ID']]['view.psb']['info']) {
    echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['view.psb']['info']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['view.psb']['info']);
}

$cgx_test = $cgx_datagrid->bind($cgx_sql, $cgx_options);
if (PEAR::isError($cgx_test)) {
    echo $cgx_test->getMessage();
}

if ($cgx_def_columns['m_product_id'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('ID', 'm_product_id', 'm_product_id', array('align' => 'right'), NULL, NULL));
if ($cgx_def_columns['warehouse_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Warehouse', 'warehouse_name', 'warehouse_name', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['product_code'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Item Number', 'product_code', 'product_code', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['partner_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Partner Name', 'partner_name', 'partner_name', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['spec'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Spec', 'spec', 'spec', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['od'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('OD', 'od', 'od', array('align' => 'right', 'width' => 100), NULL, NULL));
if ($cgx_def_columns['thickness'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Tebal', 'thickness', 'thickness', array('align' => 'right', 'width' => 100), NULL, NULL));
if ($cgx_def_columns['length'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Panjang', 'length', 'length', array('align' => 'right', 'width' => 100), NULL, NULL));
if ($cgx_def_columns['minimum_qty'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Minimum Qty', 'minimum_qty', 'minimum_qty', array('align' => 'right', 'width' => 120), NULL, "cgx_format_3digit()"));
if ($cgx_def_columns['balance_quantity'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Balance Quantity', 'balance_quantity', 'balance_quantity', array('align' => 'right', 'width' => 120), NULL, "cgx_format_3digit()"));

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