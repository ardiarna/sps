<?php

/*
 * Azwari Nugraha <nugraha@duabelas.org>
 */

$def_org = 5; // long pipe ERW
$def_year = $_REQUEST['yyyy'] ? $_REQUEST['yyyy'] : date('Y');
$def_week = str_pad($_REQUEST['ww'], 2, '0', STR_PAD_LEFT);

$date1 = strtotime($def_year . 'W' . $def_week);
$date2 = $date1 + (86400 * 6);

$d1sql = date('Y-m-d', $date1);
$d2sql = date('Y-m-d', $date2);

// load hari libur dari m_calendar
$rsx = mysql_query(
        "SELECT * "
        . "FROM m_calendar "
        . "WHERE calendar_date BETWEEN '{$d1sql}' AND '{$d2sql}'",
        $APP_CONNECTION);
while ($dtx = mysql_fetch_array($rsx, MYSQL_ASSOC)) {
    $holiday[$dtx['calendar_date']] = $dtx;
}
mysql_free_result($rsx);


echo "<div class='title'>Create Work Order Long Pipe</div>";
echo "<form action='action/trx.ppc-wo-lp.form.php'>";
echo "<input type='hidden' name='year' value='{$def_year}'>";
echo "<input type='hidden' name='week' value='{$def_week}'>";

echo "<div class='data_box'>";
echo "<table>";
echo "<tr>";
echo "<td>Work Order Number</td>";
echo "<td><input type='text' style='width: 160px;' readonly value='[AUTONUMBER]'></td>";
echo "</tr>";
echo "<tr>";
echo "<td>Week</td>";
echo "<td><input type='text' style='width: 100px;' readonly value='{$def_year}W{$def_week}'></td>";
echo "</tr>";
echo "</table>";
echo "</div>";


echo "<table cellspacing='1' class='datagrid_background' style='margin-top: 4px;' width='100%'>";
echo "<tr style='height: 30px;'>";
echo "<th class='datagrid_header' style='width: 30px;'>No</th>";
echo "<th class='datagrid_header'>Item Number</th>";
echo "<th class='datagrid_header'>Stock Qty</th>";
echo "<th class='datagrid_header' width='1%'>Mesin</th>";
for ($t = $date1; $t <= $date2; $t += 86400) {
    $day++;
    if ($holiday[date('Y-m-d', $t)]) {
        echo "<th class='datagrid_header' style='width: 80px; background: #ddd; color: #000; cursor: pointer;'><span title='" . $holiday[date('Y-m-d', $t)]['note'] . "'>" . date('D d/m', $t) . "</span></th>";
    } elseif (!in_array(date('N', $t), $APP_WORKING_DAYS)) {
        echo "<th class='datagrid_header' style='width: 80px; background: #ddd; color: red;'>" . date('D d/m', $t) . "</th>";
    } else {
        echo "<th class='datagrid_header' style='width: 80px;'>" . date('D d/m', $t) . "</th>";
    }
}
echo "</tr>";

$rsx = mysql_query(
        "SELECT DISTINCT m_product_id, product_code, balance_quantity "
        . "FROM c_production_plan "
        . "JOIN m_product USING (m_product_id) "
        . "LEFT JOIN (SELECT m_product_id, balance_quantity, max(balance_date) FROM m_stock_balance_d_2 WHERE balance_date <= '{$d1sql}' GROUP BY m_product_id) sb USING (m_product_id) "
        . "WHERE c_production_plan.app_org_id = '{$def_org}' "
        . "AND isworkorder != 'Y' "
        . "AND planning_date BETWEEN '{$d1sql}' AND '{$d2sql}' ",
        $APP_CONNECTION);
$n = 0;
while ($dtx = mysql_fetch_array($rsx, MYSQL_ASSOC)) {
    $n++;
    echo "<tr style='background: #fff;'>";
    echo "<td align='right'>{$n}</td>";
    echo "<td>{$dtx['product_code']}</td>";
    echo "<td align='right'>" . number_format($dtx['balance_quantity']) . "</td>";
    echo "<td><select name='mesin[{$dtx['m_product_id']}]'>";
    // echo "<option value=''></option>";
    $rsy = mysql_query(
            "SELECT m_machine_id, machine_code "
            . "FROM m_machine_item "
            . "JOIN m_machine USING (m_machine_id) "
            . "WHERE m_product_id = '{$dtx['m_product_id']}' "
            . "AND active = 'Y' "
            . "ORDER BY machine_code",
            $APP_CONNECTION);
    while ($dty = mysql_fetch_array($rsy, MYSQL_ASSOC)) {
        echo "<option value='{$dty['m_machine_id']}'>{$dty['machine_code']}</option>";
    }
    mysql_free_result($rsy);
    echo "</select></td>";
    unset($data);
    $rsy = mysql_query(
            "SELECT planning_date, SUM(quantity) qty "
            . "FROM c_production_plan "
            . "JOIN m_product USING (m_product_id) "
            . "WHERE c_production_plan.app_org_id = '{$def_org}' "
            . "AND isworkorder != 'Y' "
            . "AND m_product_id = '{$dtx['m_product_id']}' "
            . "AND planning_date BETWEEN '{$d1sql}' AND '{$d2sql}' "
            . "GROUP BY planning_date",
            $APP_CONNECTION);
    while ($dty = mysql_fetch_array($rsy, MYSQL_ASSOC)) {
        $data[$dty['planning_date']] = $dty;
    }
    mysql_free_result($rsy);
    for ($t = $date1; $t <= $date2; $t += 86400) {
        $d = date('Y-m-d', $t);
        if ($holiday[date('Y-m-d', $t)] || !in_array(date('N', $t), $APP_WORKING_DAYS)) {
            $textstyle = "text-align: right; background: #eee; color: red;";
        } else {
            $textstyle = "text-align: right;";
        }
        echo "<td style='{$textstyle}'>" . number_format($data[$d]['qty']) . "</td>";
    }
    echo "</tr>";
}
mysql_free_result($rsx);

echo "</table>";

echo "<div class='area-button'>";
echo "<input type='submit' value='Create Work Order'>";
echo "<input type='button' value='Back' onclick=\"window.location = 'module.php?m=trx.ppc-wo-lp&yyyy={$def_year}';\">";
echo "</div>";

echo "</form>";

?>