<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 05/12/2013 14:35:39
 */


echo "<div class='title'>Sales Order vs Work Order </div>";

function grid_so($data) {
    $href = "module.php?m=trx.so&back_to=view.sowo&mode=view&pkey[c_order_id]={$data['record']['c_order_id']}";
    $out = "<a href='{$href}'>{$data['record']['remark']}</a>";
    return $out;
}

function grid_wo($data) {
    $href = "module.php?m=view.sowo.wo&back_to=view.sowo&pkey[c_order_id]={$data['record']['c_order_id']}&pkey[m_product_id]={$data['record']['m_product_id']}&remark={$data['record']['remark']}&po={$data['record']['reference_no']}&partner={$data['record']['partner_name']}";
    $out = "<a href='{$href}'>".number_format($data['record']['order_wo'])."</a>";
    return $out;
}

require_once 'Structures/DataGrid.php';
require_once 'HTML/Table.php';

if (is_array($_SESSION[$GLOBALS['APP_ID']]['view.sowo']['columns'])) {
    $cgx_def_columns = $_SESSION[$GLOBALS['APP_ID']]['view.sowo']['columns'];
} else {
    $cgx_def_columns = array(
        'document_no' => 1,
        'remark' => 1,
        'reference_no' => 1,
        'partner_name' => 1,
        'product_name' => 1,
        'order_so' => 1,
        'order_wo' => 1,
        'sisa_wo' => 1,
    );
    $_SESSION[$GLOBALS['APP_ID']]['view.sowo']['columns'] = $cgx_def_columns;
}

$cgx_sql = "SELECT c_order.c_order_id, document_no, remark, reference_no, partner_name, wol.m_product_id, product_code, product_name, spec, od, thickness, length, order_so, order_wo, (order_wo - order_so) sisa_wo 
FROM c_order
JOIN (SELECT c_order_id, m_product_id, SUM(order_quantity) order_wo FROM m_work_order_line GROUP BY c_order_id, m_product_id) wol ON(c_order.c_order_id = wol.c_order_id)
JOIN (SELECT c_order_id, m_product_id, SUM(order_quantity) order_so FROM c_order_line WHERE order_quantity - delivered_quantity + return_quantity > 0
    AND line_status != 'C' GROUP BY c_order_id, m_product_id) col ON(wol.c_order_id = col.c_order_id AND wol.m_product_id = col.m_product_id)
JOIN c_bpartner ON(c_order.c_bpartner_id = c_bpartner.c_bpartner_id)
JOIN m_product ON(wol.m_product_id = m_product.m_product_id) WHERE 1 = 1 AND (order_wo - order_so) < 0";
$cgx_sql .= " AND " . org_filter_trx('c_order.app_org_id');

// $cgx_sqltotal = "SELECT SUM(order_so) order_so, SUM(order_wo) order_wo, SUM(order_wo - order_so) sisa_wo 
// FROM c_order
// JOIN (SELECT c_order_id, m_product_id, SUM(order_quantity) order_wo FROM m_work_order_line GROUP BY c_order_id, m_product_id) wol ON(c_order.c_order_id = wol.c_order_id)
// JOIN (SELECT c_order_id, m_product_id, SUM(order_quantity) order_so FROM c_order_line GROUP BY c_order_id, m_product_id) col ON(wol.c_order_id = col.c_order_id AND wol.m_product_id = col.m_product_id)
// JOIN c_bpartner ON(c_order.c_bpartner_id = c_bpartner.c_bpartner_id)
// JOIN m_product ON(wol.m_product_id = m_product.m_product_id) WHERE 1 = 1 ";
// $cgx_sqltotal .= " AND " . org_filter_trx('C_order.app_org_id');

$cgx_datagrid = new Structures_DataGrid($cgx_max_rows);
$cgx_options = array('dsn' => $cgx_dsn);

$item_number = $_REQUEST['item_number'];
$sc_number = $_REQUEST['sc_number'];
$customer = $_REQUEST['customer'];

echo "<form name='frmFILTER' action='{$_SERVER['SCRIPT_NAME']}'>\n";
echo "<input type='hidden' name='m' value='{$_REQUEST['m']}'>\n";
echo "<table id='bar' class='datagrid_bar' width='100%'><tr>\n";
echo "<td align='right'>Remark</td>\n";
echo "<td align='left'><input type='text'style='width: 150px;' name='sc_number' value=\"{$sc_number}\"></td>\n";
echo "<td align='right'>Item Number</td>\n";
echo "<td align='left'><input type='text'style='width: 150px;' name='item_number' value=\"{$item_number}\"></td>\n";
echo "<td align='right'>Customer</td>\n";
echo "<td align='left'><input type='text'style='width: 150px;' name='customer' value=\"{$customer}\"></td>\n";
echo "<td width='1'><input title='Cari' type='image' src='images/icon_search.png' border='0' style='padding-right: 20px;'></td>\n";
echo "<td></td>\n";
echo "<td width='20'></td>\n"; 
echo "<td width='1' class='datagrid_bar_icon'><a title='Export data ke CSV' href='javascript:exportCSV();'><img border='0' src='images/icon_csv.png'></a></td>\n";
echo "<td width='1' class='datagrid_bar_icon'><a title='Kustomisasi kolom' href='javascript:customizeColumn(true);'><img border='0' src='images/icon_columns.png'></a></td>\n";
echo "</tr></table>\n";
echo "</form>\n";

echo "<form name='frmCUSTOMIZE' action='action/cgx_customize.php' method='post'>\n";
echo "<input type='hidden' name='back' value='" . urlencode($_SERVER['REQUEST_URI']) . "'>\n";
echo "<input type='hidden' name='dg_name' value='view.sowo'>\n";
echo "<input type='hidden' name='col[remark]' value='on'>\n";
echo "<input type='hidden' name='col[document_no]' value='on'>\n";
echo "<table id='columns' class='datagrid_bar' style='display: none;'><tr>\n";
echo "<td width='99%' valign='top'>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_document_no' name='col[document_no]' type='checkbox'></td><td width='99%'><label for='col_document_no'>No. SC</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_remark' name='col[remark]' type='checkbox'></td><td width='99%'><label for='col_remark'>Remark</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['reference_no'] == 1 ? ' checked' : '') . " id='col_reference_no' name='col[reference_no]' type='checkbox'></td><td width='99%'><label for='col_reference_no'>No. PO</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['partner_name'] == 1 ? ' checked' : '') . " id='col_partner_name' name='col[partner_name]' type='checkbox'></td><td width='99%'><label for='col_partner_name'>Nama Customer</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['product_code'] == 1 ? ' checked' : '') . " id='col_product_code' name='col[product_code]' type='checkbox'></td><td width='99%'><label for='col_product_code'>Item Number</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['product_name'] == 1 ? ' checked' : '') . " id='col_product_name' name='col[product_name]' type='checkbox'></td><td width='99%'><label for='col_product_name'>Nama Produk</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['spec'] == 1 ? ' checked' : '') . " id='col_spec' name='col[spec]' type='checkbox'></td><td width='99%'><label for='col_spec'>Spec</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['od'] == 1 ? ' checked' : '') . " id='col_od' name='col[od]' type='checkbox'></td><td width='99%'><label for='col_od'>OD</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['thickness'] == 1 ? ' checked' : '') . " id='col_thickness' name='col[thickness]' type='checkbox'></td><td width='99%'><label for='col_thickness'>Thickness</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['length'] == 1 ? ' checked' : '') . " id='col_length' name='col[length]' type='checkbox'></td><td width='99%'><label for='col_length'>Length</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['order_so'] == 1 ? ' checked' : '') . " id='col_order_so' name='col[order_so]' type='checkbox'></td><td width='99%'><label for='col_order_so'>Qty Order</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['order_wo'] == 1 ? ' checked' : '') . " id='col_order_wo' name='col[order_wo]' type='checkbox'></td><td width='99%'><label for='col_order_wo'>Qty WO</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['sisa_wo'] == 1 ? ' checked' : '') . " id='col_sisa_wo' name='col[sisa_wo]' type='checkbox'></td><td width='99%'><label for='col_sisa_wo'>Sisa WO</label></td></tr></table>\n";
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
    form.setAttribute("action", "action/view.sowo.php");

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "mode");
    hiddenField.setAttribute("value", "export-all");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "item_number");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['item_number']; ?>");
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

if ($item_number) $cgx_sql .= " AND (product_code LIKE '%" . mysql_escape_string($item_number) . "%' OR product_name LIKE '%" . mysql_escape_string($item_number) . "%' OR description LIKE '%" . mysql_escape_string($item_number) . "%')";
if ($sc_number) $cgx_sql .= " AND c_order.remark LIKE '%" . mysql_escape_string($sc_number) . "%'";
if ($customer) $cgx_sql .= " AND c_bpartner.partner_name LIKE '%" . mysql_escape_string($customer) . "%'";

// if ($item_number) $cgx_sqltotal .= " AND (product_code LIKE '%" . mysql_escape_string($item_number) . "%' OR product_name LIKE '%" . mysql_escape_string($item_number) . "%' OR description LIKE '%" . mysql_escape_string($item_number) . "%')";
// if ($sc_number) $cgx_sqltotal .= " AND c_order.remark LIKE '%" . mysql_escape_string($sc_number) . "%'";
// if ($customer) $cgx_sqltotal .= " AND c_bpartner.partner_name LIKE '%" . mysql_escape_string($customer) . "%'";

$cgx_test = $cgx_datagrid->bind($cgx_sql, $cgx_options);
if (PEAR::isError($cgx_test)) {
    echo $cgx_test->getMessage();
}



if ($cgx_def_columns['document_no'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('No. SC', 'document_no', 'document_no', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['remark'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Remark', 'remark', 'remark', array('align' => 'left'), NULL, "grid_so"));
if ($cgx_def_columns['reference_no'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('No. PO', 'reference_no', 'reference_no', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['partner_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Nama Customer', 'partner_name', 'partner_name', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['product_code'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Item Number', 'product_code', 'product_code', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['product_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Nama Produk', 'product_name', 'product_name', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['spec'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Spec', 'spec', 'spec', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['od'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('OD', 'od', 'od', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['thickness'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Thickness', 'thickness', 'thickness', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['length'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Length', 'length', 'length', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['order_so'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Qty Order', 'order_so', 'order_so', array('align' => 'right'), NULL, "cgx_format_3digit()"));
if ($cgx_def_columns['order_wo'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Qty WO', 'order_wo', 'order_wo', array('align' => 'right'), NULL, "grid_wo"));
if ($cgx_def_columns['sisa_wo'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Sisa WO', 'sisa_wo', 'sisa_wo', array('align' => 'right'), NULL, "cgx_format_3digit()"));
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


    // $cgx_data_sum = cgx_fetch_table($cgx_sqltotal);

    // echo "<div class='tbl-header-box' style='font-size: 12px; border: 1px solid #ccc; margin-top: 10px;'>";
    // echo "  <table class=''>";
    // echo "  <tr>";
    // echo "      <td><b>QTY Order</b></td>";
    // echo "      <td width='10' align='center'>:</td>";
    // echo "      <td align='right'><b>".  number_format($cgx_data_sum["order_so"], 2)."</b></td>";
    // echo "      <td width='100px;'>&nbsp;</td>";
    // echo "  </tr>";
    // echo "  <tr>";
    // echo "      <td><b>QTY WO</b></td>";
    // echo "      <td width='10' align='center'>:</td>";
    // echo "      <td align='right'><b>".  number_format($cgx_data_sum["order_wo"], 2)."</b></td>";
    // echo "      <td width='100px;'>&nbsp;</td>";
    // echo "  </tr>";
    // echo "  <tr>";
    // echo "      <td><b>SISA WO</b></td>";
    // echo "      <td width='10' align='center'>:</td>";
    // echo "      <td align='right'><b>".  number_format($cgx_data_sum["sisa_wo"], 2)."</b></td>";
    // echo "      <td width='100px;'>&nbsp;</td>";
    // echo "  </tr>";      
    // echo "  </table>";
    // echo "</div>";

?>
