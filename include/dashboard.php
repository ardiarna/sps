<?php

return;

require_once 'lib/dashboard.php';

$def_year = 2013;
echo "<div class='dashboard-title'>Welcome to SPS Re-cutting application...</div>";

echo "<div class='data_box'>";
echo "<div class='portlet-title'>Sales Order</div>";
echo "<table width='100%' border='0'><tr>";
echo "<td valign='top' width='50%' id='chart-so' align='center' class='chart_area'></td>";
echo "<td valign='top' width='50%' style='padding-top: 50px;'>";
echo portlet(NULL,
        "SELECT MONTHNAME(order_date) mon, " .
        "SUM(IF(status = 'O', 1, 0)) open, " .
        "SUM(IF(status = 'C', 1, 0)) closed, " .
        "COUNT(c_order_id) total FROM c_order " .
        "WHERE YEAR(order_date) = '{$def_year}' " .
        "GROUP BY mon ORDER BY order_date",
        array('mon' => 'Bulan', 'open' => 'Open',  'closed' => 'Closed', 'total' => 'Total'),
        array('mon' => 'left',  'open' => 'right', 'closed' => 'right',  'total' => 'right'),
        array('mon' => '25%',   'open' => '25%',   'closed' => '25%',    'total' => '25%'));
echo "</td>";
echo "</tr></table>";
echo "</div>";


echo "<script type='text/javascript'>";
echo "    var chart = new FusionCharts('charts/FCF_StackedColumn2D.swf', 'chartId', '500', '300');";
echo "    chart.setDataURL('" . urlencode("services/chart-so.php") . "');";
echo "    chart.render('chart-so');";
echo "</script>";

?>