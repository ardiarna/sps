<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.org>
 * May 18, 2014 10:41:44 PM
 */

if ($_REQUEST['wo']) {
    $wo = npl_fetch_table("SELECT * FROM c_wo WHERE c_wo_id = '{$_REQUEST['wo']}'");
    $spk_date = npl_dmy2ymd($_REQUEST['d']);
}

echo "<div class='title'>Create SPK - Recutting</div>";

$select_wo = "<img onclick=\"popupReference('work-order-date');\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'>";

echo "<form id='frmSPK' action='module.php'>";
echo "<div class='data_box'>";
echo "<input type='hidden' name='m' value='trx.ppc-sp-rc'>";
echo "<input type='hidden' id='wo' name='wo' value='{$_REQUEST['wo']}'>";
echo "<input type='hidden' id='d' name='d' value='{$_REQUEST['d']}'>";
echo "<table width='100%'>";
echo "<tr>";
echo "<td width='13%'>Nomor SPK</td>";
echo "<td width='32%'><input disabled name='document_no' type='text' size='15' value=\"{$autonumber}\"></td>";
echo "<td width='10%'></td>";
echo "<td width='13%'>Working Date {$mandatory}</td>";
echo "<td width='32%'><input readonly=readonly type='text' size='10' value=\"{$spk_date}\"></td>";
echo "</tr>";
echo "<tr>";
echo "<td>Work Order {$mandatory}</td>";
echo "<td><input readonly='readonly' type='text' id='document_no' size='15' value=\"{$wo['document_no']}\">{$select_wo}</td>";
echo "<td></td>";
echo "<td>Mesin</td>";
echo "<td>" . cgx_form_select("machine", "SELECT m_machine_id, machine_code FROM m_machine ORDER BY machine_code", $_REQUEST['machine'], TRUE, "onchange=\"document.getElementById('frmSPK').submit();\"") . "</td>";
echo "</tr>";
echo "</table>";
echo "</div>";
echo "</form>";

if ($_REQUEST['machine'] && $_REQUEST['wo']) {
    echo "<form id='frmSPKexec' action='action/trx.ppc-sp-rc.php' method='POST'>";
    echo "<input type='hidden' id='wo' name='wo' value='{$_REQUEST['wo']}'>";
    echo "<input type='hidden' id='d' name='d' value='{$_REQUEST['d']}'>";
    echo "<input type='hidden' id='m' name='m' value='{$_REQUEST['machine']}'>";
    
    echo "<table cellspacing='1' class='datagrid_background' style='margin-top: 4px;' width='100%'>";
    echo "<tr style='height: 30px;'>";
    echo "<th class='datagrid_header' width='1'></th>";
    echo "<th class='datagrid_header'>Item Number</th>";
    echo "<th class='datagrid_header'>Product Name</th>";
    echo "<th class='datagrid_header'>Customer</th>";
    echo "<th class='datagrid_header'>Stock Qty</th>";
    echo "<th class='datagrid_header'>Quantity</th>";
    echo "</tr>";
    
    $rsx = mysql_query(
            "SELECT c_wo_line.*, product_code, spec, od, thickness, length, partner_name, balance_quantity FROM c_wo_line  "
            //=========================================================
            . "JOIN c_production_plan ON (c_wo_line.c_production_plan_id=c_production_plan.c_production_plan_id) "
            . "JOIN c_delivery_plan ON (c_production_plan.plan_ref = c_delivery_plan.c_delivery_plan_id) "
            . "JOIN c_order_line ON (c_delivery_plan.order_line_ref = c_order_line.c_order_line_id) "
            . "JOIN c_order USING (c_order_id) "
            . "LEFT JOIN c_bpartner ON (c_order.c_bpartner_id = c_bpartner.c_bpartner_id) "
            . "JOIN m_product ON ( c_wo_line.m_product_id=m_product.m_product_id) "
            //=========================================================
            . "LEFT JOIN (SELECT m_product_id, balance_quantity, max(balance_date) FROM m_stock_balance_d_2 WHERE balance_date <= '{$_REQUEST['d']}' GROUP BY m_product_id) sb ON (c_wo_line.m_product_id=sb.m_product_id) "
            . "WHERE c_wo_id = '{$_REQUEST['wo']}' "
            . "AND allocated = 'N' "
            . "AND working_date = '{$_REQUEST['d']}'",
            $APP_CONNECTION);
    while ($dtx = mysql_fetch_array($rsx, MYSQL_ASSOC)) {
        echo "<tr style='background: #fff;'>";
        echo "<td><input name='item[{$dtx['c_wo_line_id']}]' type='checkbox' value='{$dtx['quantity']}'></td>";
        echo "<td>{$dtx['product_code']}</td>";
        echo "<td>{$dtx['spec']}-{$dtx['od']}-{$dtx['thickness']}-{$dtx['length']}</td>";
        echo "<td>{$dtx['partner_name']}</td>";
        echo "<td align='right'>" . number_format($dtx['balance_quantity']) . "</td>";
        echo "<td align='right'>" . number_format($dtx['quantity']) . "</td>";
        echo "</tr>";
    }
    mysql_free_result($rsx);
    
    echo "</table>";
    
    echo "<div class='area-button'>";
    echo "<input type='submit' value='Create SPK'>";
    echo "<input type='button' value='Back' onclick=\"window.location = '';\">";
    echo "</div>";
    
    echo "</form>";
}

?>
<script type="text/javascript">
function setWO(wo_id, tgl) {
    var frm = document.getElementById('frmSPK');
    document.getElementById('wo').value = wo_id;
    document.getElementById('d').value = tgl;
    frm.submit();
}
</script>