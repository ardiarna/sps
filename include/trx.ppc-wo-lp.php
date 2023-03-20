<?php

/*
 * Azwari Nugraha <nugraha@duabelas.org>
 */

$def_org = 5; // long pipe ERW
$def_year = $_REQUEST['yyyy'] ? $_REQUEST['yyyy'] : date('Y');

echo "<div class='title'>Create Work Order Long Pipe</div>";
echo "<form action='module.php'>";
echo "<input type='hidden' name='m' value='{$_REQUEST['m']}'>";
echo "<div class='data_box'>";
echo "<table><tr>";
echo "<td>Select Year</td>";
echo "<td><select name='yyyy'>";
$rsx = mysql_query(
        "SELECT DISTINCT YEAR(planning_date) yyyy "
        . "FROM c_production_plan "
        . "WHERE app_org_id = '{$def_org}' "
        . "AND isworkorder != 'Y' "
        . "ORDER BY yyyy", $APP_CONNECTION);
while ($dtx = mysql_fetch_array($rsx, MYSQL_ASSOC)) {
    if ($def_year == $dtx['yyyy']) {
        echo "<option selected value='{$dtx['yyyy']}'>{$dtx['yyyy']}</option>";
    } else {
        echo "<option value='{$dtx['yyyy']}'>{$dtx['yyyy']}</option>";
    }
}
mysql_free_result($rsx);
echo "</select></td>";
echo "<td><input type='submit' value='View'></td>";
echo "<td><input type='button' value='Back' onclick=\"window.location = 'module.php?m=trx.ppc-wo-lp1';\"></td>";
echo "</tr></table>";
echo "</form>";
echo "</div>";

// select week
echo "<div style='margin-top: 4px;' class='datagrid_background'>";
echo "<table cellspacing='1' cellpadding='2' width='100%'>";
echo "<tr>";
echo "<th class='datagrid_header'>Period</th>";
echo "<th class='datagrid_header'>Date Start</th>";
echo "<th class='datagrid_header'>Date End</th>";
echo "</tr>";
$rsx = mysql_query(
        "SELECT DISTINCT WEEK(planning_date, 3) ww, YEARWEEK(planning_date, 3) yyyyww "
        . "FROM c_production_plan "
        . "WHERE app_org_id = '{$def_org}' AND YEAR(planning_date) = '{$def_year}' AND isworkorder != 'Y' ORDER BY ww",
        $APP_CONNECTION);
while ($dtx = mysql_fetch_array($rsx, MYSQL_ASSOC)) {
    $date1 = strtotime($def_year . 'W' . substr($dtx['yyyyww'], 4));
    $date2 = $date1 + (86400 * 6);
    echo "<tr style='background: #fff'>";
    echo "<td align='center'>Week " . substr($dtx['yyyyww'], 4) . "</td>";
    echo "<td align='center'>" . date($APP_DATE_FORMAT, $date1) . "</td>";
    echo "<td align='center'>" . date($APP_DATE_FORMAT, $date2) . "</td>";
    echo "<td align='center' width='1'><input onclick=\"window.location = 'module.php?m=trx.ppc-wo-lp.form&yyyy={$def_year}&ww={$dtx['ww']}';\" type='button' value='Create WO'></td>";
    echo "</tr>";
}
mysql_free_result($rsx);
echo "</table>";
echo "</div>";

?>