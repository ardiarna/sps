<?php

/*
 * Production Plan Long Pipe
 * Azwari Nugraha <nugraha@pt-gai.com>
 * Wed May  7 19:54:35 WIB 2014
 */

// range hari maksimal untuk menghindari data terlalu besar
$max_days = 31;

// geser 1 hari ke tanggal sebelum recutting
$shift_days = -3;

// organization
$def_org = 5; // long pipe
$src_org = 3; // recutting

if ($_REQUEST['d1']) {
    $d1 = $_REQUEST['d1'];
    $d2 = $_REQUEST['d2'];
} else {
    $d1 = date('d-m-Y', mktime(0, 0, 0, date('n'), 1, date('Y')));
    $d2 = date('d-m-Y', mktime(0, 0, 0, date('n') + 1, 0, date('Y')));
}
$d1sql = npl_dmy2ymd($d1);
$d2sql = npl_dmy2ymd($d2);


// load hari libur dari m_calendar
// select +/- 7 hari supaya aman dari pergeseran
$h1sql = date('Y-m-d', strtotime($d1sql) - (7 * 86400));
$h2sql = date('Y-m-d', strtotime($d2sql) + (7 * 86400));
$rsx = mysql_query(
        "SELECT * "
        . "FROM m_calendar "
        . "WHERE calendar_date BETWEEN '{$h1sql}' AND '{$h2sql}'",
        $APP_CONNECTION);
while ($dtx = mysql_fetch_array($rsx, MYSQL_ASSOC)) {
    $holiday[$dtx['calendar_date']] = $dtx;
}
mysql_free_result($rsx);


// fetch from recutting
$rsx = mysql_query(
        "SELECT * FROM c_production_plan "
        . "JOIN m_material_requirement ON (c_production_plan.m_product_id = m_material_requirement.m_product_fg) "
        . "WHERE app_org_id = '{$src_org}' "
        . "AND planning_date BETWEEN '{$d1sql}' AND '{$d2sql}' "
        . "AND processed = 'N'",
        $APP_CONNECTION);
while ($dtx = mysql_fetch_array($rsx, MYSQL_ASSOC)) {
    
    // cari hari sebelumnya yang tidak libur
    $ts = strtotime($dtx['planning_date']);
    while (TRUE) {
        $ts += $shift_days * 86400;
        if (!in_array(date('N', $ts), $APP_WORKING_DAYS)) continue;
        if ($holiday[date('Y-m-d', $ts)]) continue;
        $planning_date = date('Y-m-d', $ts);
        break;
    }
    
    $qty = ceil($dtx['quantity'] / $dtx['divqty']);
    
    $sql1 = "INSERT INTO c_production_plan ("
            . "app_org_id, planning_date, m_product_id, "
            . "quantity, plan_ref, schedule_delivery_date "
            . ") VALUES ("
            . "'{$def_org}', '{$planning_date}', '{$dtx['m_product_material']}', "
            . "'{$qty}', '{$dtx['c_production_plan_id']}', '{$dtx['planning_date']}')";
    $sql2 = "UPDATE c_production_plan "
            . "SET processed = 'Y' "
            . "WHERE c_production_plan_id = '{$dtx['c_production_plan_id']}'";
    if (mysql_query($sql1, $APP_CONNECTION)) mysql_query ($sql2, $APP_CONNECTION);
}
mysql_free_result($rsx);


// form filter, date and organization
echo "<div class='title'>Production Long Pipe</div>";
echo "<div class='data_box'>";
echo "<form action='module.php'>";
echo "<input type='hidden' name='m' value='trx.lp'>";
echo "<table>";
echo "<tr>";
echo "<td>Recutting Date</td>";
echo "<td><input name='d1' id='d1' type='text' size='10' style='text-align: center;' value='{$d1}'></td>";
echo "<td> to </td>";
echo "<td><input name='d2' id='d2' type='text' size='10' style='text-align: center;' value='{$d2}'></td>";
echo "</tr>";
echo "<tr>";
echo "<td>Organization</td>";
echo "<td colspan='3'>" . cgx_form_select('org', "SELECT app_org_id, organization FROM app_org WHERE is_trx = 'Y' AND app_org_id = '{$def_org}' ORDER BY organization", $def_org, FALSE) . "</td>";
echo "</tr>";
echo "<tr>";
echo "<td></td>";
echo "<td><input type='submit' value='View'></td>";
echo "</tr>";
echo "</table>";
echo "</form>";
echo "</div>";

echo "<div class='data_box'>";
echo "<form id='text-finder' class='text-finder' action='module.php'>";
echo "<input type='text' size='40' placeholder='Find...'><input type='reset' value='&times;'>";
echo "</form>";
echo "</div>";

$dd = npl_fetch_table(
        "SELECT MIN(planning_date) d1, MAX(planning_date) d2 "
        . "FROM c_production_plan "
        . "WHERE schedule_delivery_date BETWEEN '{$d1sql}' AND '{$d2sql}'");

// display grid
$ts1 = strtotime($dd['d1']);
$ts2 = strtotime($dd['d2']);

echo "<table cellspacing='1' class='datagrid_background' style='margin-top: 4px;'>";
echo "<tr style='height: 30px;'>";
echo "<th class='datagrid_header' style='width: 30px;'>No</th>";
echo "<th class='datagrid_header'>Item Number</th>";
echo "<th class='datagrid_header'>Product Name</th>";
echo "<th class='datagrid_header'>Customer</th>";
echo "<th class='datagrid_header'>Stock Qty</th>";
echo "<th class='datagrid_header'>Order Qty</th>";
$day = 0;
for ($t = $ts1; $t <= $ts2; $t += 86400) {
    $day++;
    if ($holiday[date('Y-m-d', $t)]) {
        echo "<th class='datagrid_header' style='width: 50px; background: #ddd; color: #000; cursor: pointer;'><span title='" . $holiday[date('Y-m-d', $t)]['note'] . "'>" . date('d/m', $t) . "</span></th>";
    } elseif (!in_array(date('N', $t), $APP_WORKING_DAYS)) {
        echo "<th class='datagrid_header' style='width: 50px; background: #ddd; color: red;'>" . date('d/m', $t) . "</th>";
    } else {
        echo "<th class='datagrid_header' style='width: 50px;'>" . date('d/m', $t) . "</th>";
    }
    if ($day >= $max_days) break;
}
echo "</tr>";
$rsx = mysql_query(
        "SELECT m_product_id, product_code, spec, od, thickness, length, partner_name, SUM(quantity) quantity, balance_quantity "
        . "FROM c_production_plan "
        . "JOIN m_product USING (m_product_id) "
        . "LEFT JOIN c_bpartner ON (m_product.c_bpartner_id=c_bpartner.c_bpartner_id) "
        . "LEFT JOIN (SELECT m_product_id, balance_quantity, max(balance_date) FROM m_stock_balance_d_2 WHERE balance_date <= '{$d1sql}' GROUP BY m_product_id) sb USING (m_product_id) "
        . "WHERE schedule_delivery_date BETWEEN '{$d1sql}' AND '{$d2sql}' "
        . "AND c_production_plan.app_org_id = '{$def_org}' "
        . "GROUP BY m_product_id, product_code",
        $APP_CONNECTION);
while ($dtx = mysql_fetch_array($rsx, MYSQL_ASSOC)) {
    echo "<tr style='background: #ffffff; height: 26px;'>";
    echo "<td align='right'>" . (++$i) . "</td>";
    echo "<td>{$dtx['product_code']}</td>";
    echo "<td>{$dtx['spec']}-{$dtx['od']}-{$dtx['thickness']}-{$dtx['length']}</td>";
    echo "<td>{$dtx['partner_name']}</td>";
    echo "<td align='right'>" . number_format($dtx['balance_quantity']) . "</td>";
    echo "<td align='right'>" . number_format($dtx['quantity']) . "</td>";
    $rsy = mysql_query(
            "SELECT * "
            . "FROM c_production_plan "
            . "WHERE schedule_delivery_date BETWEEN '{$d1sql}' AND '{$d2sql}' "
            . "AND app_org_id = '{$def_org}' "
            . "AND m_product_id = '{$dtx['m_product_id']}'",
            $APP_CONNECTION);
    unset($data);
    while ($dty = mysql_fetch_array($rsy, MYSQL_ASSOC)) {
        $data[$dty['planning_date']] = $dty;
    }
    mysql_free_result($rsy);
    $day = 0;
    for ($t = $ts1; $t <= $ts2; $t += 86400) {
        $day++;
        $d = date('Y-m-d', $t);
        $iname = "data[{$d}]";
        echo "<td align='right'>" . number_format($data[$d]['quantity']) . "</th>";
        if ($day >= $max_days) break;
    }
    echo "</tr>";
}
mysql_free_result($rsx);
echo "</table>";

?>
<style>
.highlight {
  background-color:yellow; /* highlight color */
}
</style>

<script>
$(function() {
    $("#d1").datepicker({dateFormat: '<?php echo $APP_DATE_FORMAT_JAVA; ?>'});
    $("#d2").datepicker({dateFormat: '<?php echo $APP_DATE_FORMAT_JAVA; ?>'});

    $.fn.highlight = function(pat) {
        function innerHighlight(node, pat) {
            var skip = 0;
            if (node.nodeType == 3) {
                var pos = node.data.toUpperCase().indexOf(pat);
                if (pos >= 0) {
                    var spannode = document.createElement('span');
                    spannode.className = 'highlight';
                    var middlebit = node.splitText(pos);
                    var endbit = middlebit.splitText(pat.length);
                    var middleclone = middlebit.cloneNode(true);
                    spannode.appendChild(middleclone);
                    middlebit.parentNode.replaceChild(spannode, middlebit);
                    skip = 1;
                }
            } else if (node.nodeType == 1 && node.childNodes && !/(script|style)/i.test(node.tagName)) {
                for (var i = 0; i < node.childNodes.length; ++i) {
                    i += innerHighlight(node.childNodes[i], pat);
                }
            }
            return skip;
        }
        return this.length && pat && pat.length ? this.each(function() {
            innerHighlight(this, pat.toUpperCase());
        }) : this;
    };
    $.fn.removeHighlight = function() {
        return this.find("span.highlight").each(function() {
            this.parentNode.firstChild.nodeName;
            with (this.parentNode) {
                replaceChild(this.firstChild, this);
                normalize();
            }
        }).end();
    };

    $(document).ready(function() {
        var $finder = $('#text-finder'),
            $field = $finder.children().first(),
            $clear = $field.next(),
            $area = $(document.body),
            $viewport = $('html, body');
        $field.on("keyup", function() {
            $area.removeHighlight().highlight(this.value); // Highlight text inside `$area` on keyup
            $viewport.scrollTop($area.find('span.highlight').first().offset().top - 50); // Jump the viewport to the first highlighted term
        });
        $clear.on("click", function() {
            $area.removeHighlight(); // Remove all highlight inside `$area`
            $field.val('').trigger("focus"); // Clear the search field
            $viewport.scrollTop(0); // Jump the viewport to the top
            return false;
        });
    });

});
</script>