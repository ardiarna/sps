<?php

/*
 * chart-so
 * Azwari Nugraha <nugraha@duabelas.org>
 * Dec 3, 2013 7:48:01 AM
 */

require_once '../init.php';

$def_year = 2013;

$data = array();
for ($m = 1; $m <= 12; $m++) {
    $month = date('M', mktime(0, 0, 0, $m));
    foreach ($SO_STATUS as $skey => $sname) {
        $data[$skey][$month] = 0;
    }
}

$rsx = mysql_query(
        "SELECT DATE_FORMAT(order_date, '%b') MON, status STATUS, COUNT(c_order_id) CNT " .
        "FROM c_order " .
        "WHERE YEAR(order_date) = '{$def_year}' " .
        "GROUP BY MON, STATUS " .
        "ORDER BY order_date",
        $APP_CONNECTION);
while ($dtx = mysql_fetch_array($rsx)) $data[$dtx['STATUS']][$dtx['MON']] = $dtx['CNT'];
mysql_free_result($rsx);

header("Content-Type: application/xml");
echo "<graph bgcolor='DFEFFF' xAxisName='Bulan' caption='Sales Order Tahun {$def_year}' yAxisName='Jumlah SO' decimalPrecision='0' rotateNames='1' numDivLines='3' showValues='0' formatNumberScale='0'>";
echo "<categories>";
foreach ($data['O'] as $month => $value) echo "<category name='{$month}'/>";
echo "</categories>";

$n = 0;
foreach ($data as $status => $d1) {
    echo "<dataset seriesName='{$SO_STATUS[$status]}' color='{$app_chart_colors[$n]}' showValues='0'>";
    foreach ($d1 as $d2) echo "<set value='{$d2}'/>";
    echo "</dataset>";
    $n++;
}

echo "</graph>";

?>