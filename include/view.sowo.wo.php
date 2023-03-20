<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 05/12/2013 14:35:39
 */


echo "<div class='title'>Work Order</div>";

function grid_so($data) {
    $href = "module.php?m=trx.so&back_to=view.sowo.wo&mode=view&pkey[c_order_id]={$data['record']['c_order_id']}";
    $out = "<a href='{$href}'>{$data['record']['remark']}</a>";
    return $out;
}

function grid_wo($data) {
    $href = "module.php?m=trx.so&back_to=view.sowo.wo&mode=view&pkey[c_order_id]={$data['record']['c_order_id']}";
    $out = "<a href='{$href}'>".number_format($data['record']['order_wo'])."</a>";
    return $out;
}

require_once 'Structures/DataGrid.php';
require_once 'HTML/Table.php';

if (is_array($_SESSION[$GLOBALS['APP_ID']]['view.sowo.wo']['columns'])) {
    $cgx_def_columns = $_SESSION[$GLOBALS['APP_ID']]['view.sowo.wo']['columns'];
} else {
    $cgx_def_columns = array(
        'document_no' => 1,
        'order_date' => 1,
        'machine_name' => 1,
        'product_code' => 1,
        'product_name' => 1,
        'order_quantity' => 1,
    );
    $_SESSION[$GLOBALS['APP_ID']]['view.sowo.wo']['columns'] = $cgx_def_columns;
}

$cgx_sql = "SELECT *
FROM m_work_order
JOIN m_machine USING (m_machine_id)
JOIN m_work_order_line USING (m_work_order_id)
JOIN m_product USING (m_product_id)
WHERE m_work_order_line.c_order_id = '{$_REQUEST['pkey']['c_order_id']}' AND m_work_order_line.m_product_id = '{$_REQUEST['pkey']['m_product_id']}' ";

$cgx_sqltotal = "SELECT SUM(order_quantity) order_quantity
FROM m_work_order
JOIN m_machine USING (m_machine_id)
JOIN m_work_order_line USING (m_work_order_id)
JOIN m_product USING (m_product_id)
WHERE m_work_order_line.c_order_id = '{$_REQUEST['pkey']['c_order_id']}' AND m_work_order_line.m_product_id = '{$_REQUEST['pkey']['m_product_id']}' ";

$cgx_datagrid = new Structures_DataGrid($cgx_max_rows);
$cgx_options = array('dsn' => $cgx_dsn);
$cgx_datagrid->setDefaultSort(array('order_date' => 'DESC'));

echo "<form name='frmFILTER' action='{$_SERVER['SCRIPT_NAME']}'>\n";
echo "<input type='hidden' name='m' value='{$_REQUEST['m']}'>\n";
echo "<table id='bar' class='datagrid_bar' width='100%'><tr>\n";
echo "<td align='right'>Remark : </td>\n";
echo "<td align='left'>{$_REQUEST['remark']}</td>\n";
echo "<td align='right'>PO Number : </td>\n";
echo "<td align='left'>{$_REQUEST['po']}</td>\n";
echo "<td align='right'>Customer : </td>\n";
echo "<td align='left'>{$_REQUEST['partner']}</td>\n";
echo "<td></td>\n";
echo "<td align='right'><input type='button' value='Kembali' onclick=\"window.location = 'module.php?m={$_REQUEST['back_to']}'\">";
//echo "<td width='1' class='datagrid_bar_icon'><a title='Kustomisasi kolom' href='javascript:customizeColumn(true);'><img border='0' src='images/icon_columns.png'></a></td>\n";
echo "</tr></table>\n";
echo "</form>\n";

// echo "<form name='frmCUSTOMIZE' action='action/cgx_customize.php' method='post'>\n";
// echo "<input type='hidden' name='back' value='" . urlencode($_SERVER['REQUEST_URI']) . "'>\n";
// echo "<input type='hidden' name='dg_name' value='view.sowo.wo'>\n";
// echo "<input type='hidden' name='col[remark]' value='on'>\n";
// echo "<input type='hidden' name='col[document_no]' value='on'>\n";
// echo "<table id='columns' class='datagrid_bar' style='display: none;'><tr>\n";
// echo "<td width='99%' valign='top'>\n";
// echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_document_no' name='col[document_no]' type='checkbox'></td><td width='99%'><label for='col_document_no'>No. SC</label></td></tr></table>\n";
// echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_remark' name='col[remark]' type='checkbox'></td><td width='99%'><label for='col_remark'>Remark</label></td></tr></table>\n";
// echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['reference_no'] == 1 ? ' checked' : '') . " id='col_reference_no' name='col[reference_no]' type='checkbox'></td><td width='99%'><label for='col_reference_no'>No. PO</label></td></tr></table>\n";
// echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['partner_name'] == 1 ? ' checked' : '') . " id='col_partner_name' name='col[partner_name]' type='checkbox'></td><td width='99%'><label for='col_partner_name'>Nama Customer</label></td></tr></table>\n";
// echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['product_code'] == 1 ? ' checked' : '') . " id='col_product_code' name='col[product_code]' type='checkbox'></td><td width='99%'><label for='col_product_code'>Item Number</label></td></tr></table>\n";
// echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['product_name'] == 1 ? ' checked' : '') . " id='col_product_name' name='col[product_name]' type='checkbox'></td><td width='99%'><label for='col_product_name'>Nama Produk</label></td></tr></table>\n";
// echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['spec'] == 1 ? ' checked' : '') . " id='col_spec' name='col[spec]' type='checkbox'></td><td width='99%'><label for='col_spec'>Spec</label></td></tr></table>\n";
// echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['od'] == 1 ? ' checked' : '') . " id='col_od' name='col[od]' type='checkbox'></td><td width='99%'><label for='col_od'>OD</label></td></tr></table>\n";
// echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['thickness'] == 1 ? ' checked' : '') . " id='col_thickness' name='col[thickness]' type='checkbox'></td><td width='99%'><label for='col_thickness'>Thickness</label></td></tr></table>\n";
// echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['length'] == 1 ? ' checked' : '') . " id='col_length' name='col[length]' type='checkbox'></td><td width='99%'><label for='col_length'>Length</label></td></tr></table>\n";
// echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['order_so'] == 1 ? ' checked' : '') . " id='col_order_so' name='col[order_so]' type='checkbox'></td><td width='99%'><label for='col_order_so'>Qty Order</label></td></tr></table>\n";
// echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['order_wo'] == 1 ? ' checked' : '') . " id='col_order_wo' name='col[order_wo]' type='checkbox'></td><td width='99%'><label for='col_order_wo'>Qty WO</label></td></tr></table>\n";
// echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['sisa_wo'] == 1 ? ' checked' : '') . " id='col_sisa_wo' name='col[sisa_wo]' type='checkbox'></td><td width='99%'><label for='col_sisa_wo'>Sisa WO</label></td></tr></table>\n";
// echo "</td>\n";
// echo "<td width='1' valign='top'><input type='submit' value='Simpan'></td>\n";
// echo "<td width='1' valign='top'><input type='button' value='Batalkan' onclick='customizeColumn(false);'></td>\n";
// echo "</tr></table>\n";
// echo "</form>\n";
?>
<script type="text/javascript">
// <!--

// $(function() {
//     $("#date_f").datepicker({dateFormat: '<?php echo $APP_DATE_FORMAT_JAVA; ?>'});
//     $("#date_t").datepicker({dateFormat: '<?php echo $APP_DATE_FORMAT_JAVA; ?>'});
// });

// function exportCSV() {
//     form = document.createElement("form");
//     form.setAttribute("method", "post");
//     form.setAttribute("action", "action/view.sowo.wo.php");

//     hiddenField = document.createElement("input");
//     hiddenField.setAttribute("type", "hidden");
//     hiddenField.setAttribute("name", "mode");
//     hiddenField.setAttribute("value", "export-all");
//     form.appendChild(hiddenField);

//     hiddenField = document.createElement("input");
//     hiddenField.setAttribute("type", "hidden");
//     hiddenField.setAttribute("name", "item_number");
//     hiddenField.setAttribute("value", "<?php echo $_REQUEST['item_number']; ?>");
//     form.appendChild(hiddenField);

//     hiddenField = document.createElement("input");
//     hiddenField.setAttribute("type", "hidden");
//     hiddenField.setAttribute("name", "sc_number");
//     hiddenField.setAttribute("value", "<?php echo $_REQUEST['sc_number']; ?>");
//     form.appendChild(hiddenField);

//     hiddenField = document.createElement("input");
//     hiddenField.setAttribute("type", "hidden");
//     hiddenField.setAttribute("name", "customer");
//     hiddenField.setAttribute("value", "<?php echo $_REQUEST['customer']; ?>");
//     form.appendChild(hiddenField);

//     document.body.appendChild(form);
//     form.submit();    
// }

// function customizeColumn(s) {
//     var divCols = document.getElementById('columns');
//     var divBar = document.getElementById('bar');
//     if (s) {
//         divCols.style.display = 'block';
//         divBar.style.display = 'none';
//     } else {
//         window.location = window.location;
//     }
// }
// //-->
</script>
<?php

$cgx_test = $cgx_datagrid->bind($cgx_sql, $cgx_options);
if (PEAR::isError($cgx_test)) {
    echo $cgx_test->getMessage();
}

if ($cgx_def_columns['document_no'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('No. WO', 'document_no', 'document_no', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['order_date'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Tgl WO', 'order_date', 'order_date', array('align' => 'center'), NULL, "cgx_format_date()"));
if ($cgx_def_columns['machine_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Mesin', 'machine_name', 'machine_name', array('align' => 'left'), NULL, NULL));
//if ($cgx_def_columns['m_product_id'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Product ID', 'm_product_id', 'm_product_id', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['product_code'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Item Number', 'product_code', 'product_code', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['product_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Nama Produk', 'product_name', 'product_name', array('align' => 'left'), NULL, NULL));
// if ($cgx_def_columns['spec'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Spec', 'spec', 'spec', array('align' => 'left'), NULL, NULL));
// if ($cgx_def_columns['od'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('OD', 'od', 'od', array('align' => 'left'), NULL, NULL));
// if ($cgx_def_columns['thickness'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Thickness', 'thickness', 'thickness', array('align' => 'left'), NULL, NULL));
// if ($cgx_def_columns['length'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Length', 'length', 'length', array('align' => 'left'), NULL, NULL));
// if ($cgx_def_columns['tolerance_size'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Tolerance Size', 'tolerance_size', 'tolerance_size', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['order_quantity'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Qty WO', 'order_quantity', 'order_quantity', array('align' => 'right'), NULL, "cgx_format_3digit()"));
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
    echo "      <td><b>Total Order WO</b></td>";
    echo "      <td width='10' align='center'>:</td>";
    echo "      <td align='right'><b>".  number_format($cgx_data_sum["order_quantity"], 2)."</b></td>";
    echo "      <td width='100px;'>&nbsp;</td>";
    echo "  </tr>";
    echo "  </table>";
    echo "</div>";

?>
