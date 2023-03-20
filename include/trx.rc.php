<?php

/*
 * Production Plan Recutting
 * Azwari Nugraha <nugraha@pt-gai.com>
 * Wed Apr 30 11:31:44 WIT 2014
 */

// range hari maksimal untuk menghindari data terlalu besar
$max_days = 31;

// geser 1 hari ke tanggal sebelumnya
$shift_days = -3;

// load default value
$def_org = 3; // recutting
if ($_REQUEST['d1']) {
    $d1 = $_REQUEST['d1'];
    $d2 = $_REQUEST['d2'];
} else {
    $d1 = date('d-m-Y', mktime(0, 0, 0, date('n'), 1, date('Y')));
    $d2 = date('d-m-Y', mktime(0, 0, 0, date('n') + 1, 0, date('Y')));
}
$d1sql = npl_dmy2ymd($d1);
$d2sql = npl_dmy2ymd($d2);
$jml_hari = 0;

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


// fetch from c_delivery_plan
$rsx = mysql_query(
        "SELECT c_delivery_plan.*, (multipleqty * bundle) as min_cut FROM c_delivery_plan "
        . "LEFT JOIN m_material_requirement ON(c_delivery_plan.m_product_id=m_material_requirement.m_product_fg) "
        . "WHERE app_org_id = '{$def_org}' "
        . "AND is_production_plan = 'N' "
        . "AND schedule_delivery_date BETWEEN '{$d1sql}' AND '{$d2sql}' "
        . "AND order_quantity > 0 "
        . "order by c_delivery_plan.m_product_id ",
        $APP_CONNECTION);
    $oh = 0;
    $new_product =0;
    $order = 0;
while ($dtx = mysql_fetch_array($rsx, MYSQL_ASSOC)) {
    if ($new_product != $dtx['m_product_id'])
    {
        $new_product = $dtx['m_product_id'];
        $oh = 0;
        $order = 0;
        $saldo = 0;
        //$dd = npl_fetch_table(
        //"SELECT balance_quantity FROM m_stock_balance_d_2 WHERE latest = 'Y' AND m_product_id = '{$dtx['m_product_id']}'");
        $dd = npl_fetch_table(
        "SELECT m_product_id, balance_quantity, max(balance_date) FROM m_stock_balance_d_2 WHERE balance_date <= '{$d1sql}' and app_org_id = '{$def_org}' and m_product_id = '{$dtx['m_product_id']}' GROUP BY m_product_id");
        
        // display grid
        $saldo = $dd['balance_quantity'];
        //$oh = $oh + $saldo;
    }
    
    // cari hari sebelumnya yang tidak libur
    $ts = strtotime($dtx['schedule_delivery_date']);
    while (TRUE) {
        $ts += $shift_days * 86400;
        if (!in_array(date('N', $ts), $APP_WORKING_DAYS)) continue;
        if ($holiday[date('Y-m-d', $ts)]) continue;
        $planning_date = date('Y-m-d', $ts);
        break;
    }
    /*if ($saldo>0)
    {
        $kebutuhan = $dtx['order_quantity']-$saldo; // 60 - 40 = 20
    }
    else 
    {
        $kebutuhan = $dtx['order_quantity']; // 60
    }*/
    $kebutuhan = $dtx['order_quantity']-$saldo; // 60 - 40 = 20
    //echo 'saldo = '.$saldo.'->kebutuhan = '. $kebutuhan;
    if ($kebutuhan>0)
    {
        //perlu produksi -> saldo lebih kecil dari order
        //$order = $order + $dtx['order_quantity'];
        ///$saldo = $dtx['order_quantity']-$kebutuhan;
        //echo 'saldo = '.$saldo.'->kebutuhan = '. $kebutuhan;
        /*
            if ((($kebutuhan/$dtx['min_cut'])-round($kebutuhan/$dtx['min_cut']))>0) 
            {
               $produksi = (round($kebutuhan/$dtx['min_cut'])+1)*$dtx['min_cut'];    
            }
            else 
            {
                $produksi = round($kebutuhan/$dtx['min_cut'])*$dtx['min_cut'];    
            }         
         */
        $produksi = ceil($kebutuhan/$dtx['min_cut'])*$dtx['min_cut']; 
        //echo 'saldo = '.$saldo.'->kebutuhan = '. $kebutuhan.' -> produksi = '.$produksi;
            if ($produksi>0)
                {
                    $sql1 = "INSERT INTO c_production_plan ("
                            . "app_org_id, planning_date, m_product_id, "
                            . "quantity, plan_ref, schedule_delivery_date, delivery_quantity "
                            . ") VALUES ("
                            . "'{$dtx['app_org_id']}', '{$planning_date}', '{$dtx['m_product_id']}', "
                            . "'{$produksi}', '{$dtx['c_delivery_plan_id']}', '{$dtx['schedule_delivery_date']}', '{$dtx['order_quantity']}')";
                    //$oh = $oh + $produksi; - $dtx['order_quantity'] +
                    //$order = 0;        
                }
        $saldo = $saldo + $produksi;
    }
    else 
    {
        $saldo = $saldo - $dtx['order_quantity'];
       // $order = $order + $dtx['order_quantity'];
        $produksi = 0;
        $sql1 = "INSERT INTO c_production_plan ("
                            . "app_org_id, planning_date, m_product_id, "
                            . "quantity, plan_ref, schedule_delivery_date, delivery_quantity "
                            . ") VALUES ("
                            . "'{$dtx['app_org_id']}', '{$planning_date}', '{$dtx['m_product_id']}', "
                            . "'{$produksi}', '{$dtx['c_delivery_plan_id']}', '{$dtx['schedule_delivery_date']}', '{$dtx['order_quantity']}')";
        
    }
    //if (($dtx['order_quantity']-$saldo)>0) 
    //{
       /* if (($dtx['order_quantity']-$saldo)>$dtx['min_cut']) 
        {
            if ($oh <= $order) 
            {
                $produksi = round(($dtx['order_quantity']-$saldo)/$dtx['min_cut'],0,'PHP_ROUND_HALF_EVEN')*$dtx['min_cut'];    
                if ($produksi>0)
                {
                    $sql1 = "INSERT INTO c_production_plan ("
                            . "app_org_id, planning_date, m_product_id, "
                            . "quantity, plan_ref, schedule_delivery_date, delivery_quantity "
                            . ") VALUES ("
                            . "'{$dtx['app_org_id']}', '{$planning_date}', '{$dtx['m_product_id']}', "
                            . "'{$produksi}', '{$dtx['c_delivery_plan_id']}', '{$dtx['schedule_delivery_date']}', '{$dtx['order_quantity']}')";
                    $oh = $oh + $produksi;
                }
                $saldo = $saldo - $produksi;
                }
            }
        else 
        {
            if ($oh <= $order) 
                {
                $sql1 = "INSERT INTO c_production_plan ("
                . "app_org_id, planning_date, m_product_id, "
                . "quantity, plan_ref, schedule_delivery_date, delivery_quantity "
                . ") VALUES ("
                . "'{$dtx['app_org_id']}', '{$planning_date}', '{$dtx['m_product_id']}', "
                . "'{$dtx['min_cut']}', '{$dtx['c_delivery_plan_id']}', '{$dtx['schedule_delivery_date']}', '{$dtx['order_quantity']}')";
                $oh = $oh + $produksi;
            }        
        }*/
    //}
    $sql2 = "UPDATE c_delivery_plan "
            . "SET is_production_plan = 'Y' "
            . "WHERE c_delivery_plan_id = '{$dtx['c_delivery_plan_id']}'";
    if (mysql_query($sql1, $APP_CONNECTION)) mysql_query ($sql2, $APP_CONNECTION);
}
mysql_free_result($rsx);


// form filter, date and organization
echo "<div class='title'>Production Recutting</div>";
echo "<div class='data_box'>";
echo "<form action='module.php'>";
echo "<input type='hidden' name='m' value='trx.rc'>";
echo "<table>";
echo "<tr>";
echo "<td>Sales Order Date</td>";
echo "<td><input name='d1' id='d1' type='text' size='10' style='text-align: center;' value='{$d1}'></td>";
echo "<td> to </td>";
echo "<td><input name='d2' id='d2' type='text' size='10' style='text-align: center;' value='{$d2}'></td>";
echo "</tr>";
echo "<tr>";
echo "<td>Organization.</td>";
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
echo "<form action='action/trx.rc.php' method='post'>";

echo "<input type='hidden' name='ref' value='{$_REQUEST['ref']}'>";
echo "<input type='hidden' name='product' value='{$_REQUEST['product']}'>";
echo "<input type='hidden' name='org' value='{$def_org}'>";
echo "<input type='hidden' name='d1' value='{$d1}'>";
echo "<input type='hidden' name='d2' value='{$d2}'>";

echo "<table id='bar' class='datagrid_bar' width='100%' border='0' style='margin-top: 4px;'><tr>";
echo "<td><input type='submit' value='Save'></td>";
echo "</tr></table>";

echo "<table cellspacing='1' class='datagrid_background' style='margin-top: 4px;'>";
echo "<tr style='height: 30px;'>";
echo "<th class='datagrid_header' style='width: 30px;'>No</th>";
echo "<th class='datagrid_header'>Item Number</th>";
echo "<th class='datagrid_header'>Product Name</th>";
echo "<th class='datagrid_header'>Customer</th>";
echo "<th class='datagrid_header'>Stock Qty</th>";
// echo "<th class='datagrid_header'>Kap Mesin</th>";
echo "<th class='datagrid_header'>Min Produksi</th>";
echo "<th class='datagrid_header'>Order Qty</th>";
echo "<th class='datagrid_header'>Qty Produksi</th>";
echo "<th class='datagrid_header'>Sisa Qty Produksi</th>";
$day = 0;
for ($t = $ts1; $t <= $ts2; $t += 86400) {
    $day++;
    if ($holiday[date('Y-m-d', $t)]) {
        echo "<th class='datagrid_header' style='width: 50px; background: #ddd; color: #000; cursor: pointer;'><span title='" . $holiday[date('Y-m-d', $t)]['note'] . "'>" . date('d/m', $t) . "</span></th>";
    } elseif (!in_array(date('N', $t), $APP_WORKING_DAYS)) {
        echo "<th class='datagrid_header' style='width: 50px; background: #ddd; color: red;'>" . date('d/m', $t) . "</th>";
    } else {
        echo "<th class='datagrid_header' style='width: 50px;'>" . date('d/m', $t) . "</th>";
        $jml_hari = $jml_hari + 1;
    }
    if ($day >= $max_days) break;
}
echo "</tr>";
// balance_quantity,  
$rsx = mysql_query(
        "SELECT m_product_id, product_code, spec, od, thickness, length, partner_name, SUM(quantity) quantity, " 
        . " (multipleqty * bundle) as min_cut, SUM(delivery_quantity) delivery_quantity "
        . "FROM c_production_plan "
        . "JOIN m_product USING (m_product_id) "
        . "LEFT JOIN m_material_requirement ON(c_production_plan.m_product_id=m_material_requirement.m_product_fg) "
        . "LEFT JOIN c_bpartner ON (m_product.c_bpartner_id=c_bpartner.c_bpartner_id) " 
        //. "LEFT JOIN (SELECT m_product_id, balance_quantity FROM m_stock_balance_d_2 WHERE latest = 'Y') sb USING (m_product_id) "
        . "WHERE schedule_delivery_date BETWEEN '{$d1sql}' AND '{$d2sql}' "
        . "AND c_production_plan.app_org_id = '{$def_org}' "
        . "GROUP BY m_product_id, product_code",
        $APP_CONNECTION);
// $rsx = mysql_query(
//         "SELECT m_product_id, product_code, SUM(quantity) quantity, balance_quantity "
//         . "FROM c_production_plan "
//         . "JOIN m_product USING (m_product_id) "
//         . "LEFT JOIN (SELECT m_product_id, balance_quantity, balance_date FROM m_stock_balance_d_2 WHERE latest='Y') sb USING (m_product_id) "
//         . "WHERE schedule_delivery_date BETWEEN '{$d1sql}' AND '{$d2sql}' "
//         . "AND c_production_plan.app_org_id = '{$def_org}' "
//         . "GROUP BY m_product_id, product_code",
//         $APP_CONNECTION);
$n_product = 0;        
while ($dtx = mysql_fetch_array($rsx, MYSQL_ASSOC)) {
    if ($n_product != $dtx['m_product_id'])
    {
        $n_product = $dtx['m_product_id'];
        //$dd = npl_fetch_table(
        //"SELECT balance_quantity FROM m_stock_balance_d_2 WHERE latest = 'Y' AND m_product_id = '{$dtx['m_product_id']}'");
        $dd = npl_fetch_table(
        "SELECT m_product_id, balance_quantity, max(balance_date) FROM m_stock_balance_d_2 WHERE balance_date <= '{$d1sql}' and app_org_id = '{$def_org}' and m_product_id = '{$dtx['m_product_id']}' GROUP BY m_product_id");
        // display grid
        $saldox = $dd['balance_quantity'];
        
    }
    echo "<tr style='background: #ffffff; height: 26px;'>";
    echo "<td align='right'>" . (++$i) . "</td>";
    echo "<td>{$dtx['product_code']}</td>";
    echo "<td>{$dtx['spec']}-{$dtx['od']}-{$dtx['thickness']}-{$dtx['length']}</td>";
    echo "<td>{$dtx['partner_name']}</td>";
    //echo "<td align='right'>" . number_format($dtx['balance_quantity']) . "</td>";
    echo "<td align='right'>" . number_format($saldox) . "</td>";
    /*
     $rsza = mysql_query(
            "SELECT result_shift, machine_code, resultperday FROM m_machine_item
            JOIN m_machine ON(m_machine_item.m_machine_id=m_machine.m_machine_id)
            WHERE m_product_id = '{$dtx['m_product_id']}'",
            $APP_CONNECTION);
    $kaps_mesin = '';
    while ($dtza = mysql_fetch_array($rsza, MYSQL_ASSOC)){
        $kaps_mesin .= $dtza['machine_code'] .'('. $dtza['resultperday'] .'), ';
    }
    echo "<td>{$kaps_mesin}</td>";
     */
    echo "<td align='right'>" . number_format($dtx['min_cut']) . "</td>";
    echo "<td align='right'>" . number_format($dtx['delivery_quantity']) . "</td>";    
    /*if ($dtx['min_cut'] > $dtx['quantity']) 
    {
        echo "<td align='right'>" . number_format($dtx['min_cut']) . "</td>";
    }
    else
    {*/
        echo "<td align='right'>" . number_format($dtx['quantity']) . "</td>";
    //}
    echo "<td align='right'>" . number_format($dtx['quantity']-$dtx['delivery_quantity']) . "</td>";     
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
        if ($holiday[date('Y-m-d', $t)] || !in_array(date('N', $t), $APP_WORKING_DAYS)) {
            $textstyle = "background: #eee; color: red;";
        } else {
            $textstyle = "";
        }
        if ($data[$d]['quantity'] > 0) {
            if ($_REQUEST['ref'] == $data[$d]['plan_ref']) {
                echo "<td style='{$textstyle}' align='right'><input name='{$iname}' type='text' style='{$textstyle} width: 40px; text-align: right;' value='{$data[$d]['quantity']}'></th>";
            } else {
                $link = "module.php?m=trx.rc&d1={$d1}&d2={$d2}&org={$def_org}&product={$data[$d]['m_product_id']}&ref={$data[$d]['plan_ref']}";
                echo "<td style='{$textstyle}' align='right'><a href='{$link}' >" . number_format($data[$d]['quantity']) . "</a></th>";
            }
        } else {
            if ($_REQUEST['product'] == $dtx['m_product_id']) {
                echo "<td style='{$textstyle}' align='right'><input name='{$iname}' type='text' style='{$textstyle} width: 40px; text-align: right;' value='0'></th>";
            } else {
                echo "<td style='{$textstyle}' align='right' style='color: #888888;'>0</th>";
            }
        }
        // echo "<td align='right' style='{$textstyle}'>" . number_format($data[$d]['quantity']) . "</th>";
        if ($day >= $max_days) break;
    }
    echo "</tr>";
}
mysql_free_result($rsx);

$rsx1 = mysql_query(
        "SELECT 'Total' total, SUM(quantity) quantity "
        . "FROM c_production_plan "
        . "JOIN m_product USING (m_product_id) "
        . "WHERE schedule_delivery_date BETWEEN '{$d1sql}' AND '{$d2sql}' "
        . "AND c_production_plan.app_org_id = '{$def_org}' ",
        $APP_CONNECTION);
while ($dtx1 = mysql_fetch_array($rsx1, MYSQL_ASSOC)) {
    echo "<tr style='background: #ffffff; height: 26px;'>";
    echo "<td align='right'>" . (++$i) . "</td>";
    echo "<td align='right'>Rata2</td>";echo "<td align='right'>".number_format($dtx1['quantity']/$jml_hari)."</td>";echo "<td align='right'>-</td>";echo "<td align='right'>-</td>";
    echo "<td align='right'>-</td>";//echo "<td align='right'>-</td>";
    echo "<td>{$dtx1['total']}</td>";
    echo "<td align='right'>" . number_format($dtx1['quantity']) . "</td>";
    echo "<td align='right'>-</td>";
    $rsy1 = mysql_query(
            "SELECT planning_date, sum(quantity) as quantity "
            . "FROM c_production_plan "
            . "WHERE schedule_delivery_date BETWEEN '{$d1sql}' AND '{$d2sql}' "
            . "AND app_org_id = '{$def_org}' "
            . "group by planning_date",
            $APP_CONNECTION);
    unset($data);
    while ($dty1 = mysql_fetch_array($rsy1, MYSQL_ASSOC)) {
        $data[$dty1['planning_date']] = $dty1;
    }
    mysql_free_result($rsy1);
    $day = 0;
    for ($t = $ts1; $t <= $ts2; $t += 86400) {
        $day++;
        $d = date('Y-m-d', $t);
        $iname = "data[{$d}]";
        if ($holiday[date('Y-m-d', $t)] || !in_array(date('N', $t), $APP_WORKING_DAYS)) {
            $textstyle = "background: #eee; color: red;";
        } else {
            $textstyle = "";
        }
        echo "<td align='right' style='{$textstyle}'>" . number_format($data[$d]['quantity']) . "</th>";
        if ($day >= $max_days) break;
    }
    echo "</tr>";
}
mysql_free_result($rsx1);

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
