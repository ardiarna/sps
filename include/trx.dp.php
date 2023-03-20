<?php

/*
 * Delivery Plan
 * Azwari Nugraha <nugraha@pt-gai.com>
 * Apr 28, 2014 11:19:31 AM
 */

// range hari maksimal untuk menghindari data terlalu besar
$max_days = 31;


// default value
$def_org = $_REQUEST['org'] ? $_REQUEST['org'] : org();
$customer = $_REQUEST['customer'] ? $_REQUEST['customer'] : '';

if($customer == ''){
    $sql_customer = "";    
}else{
    $sql_customer = " AND c_order.c_bpartner_id = '" . mysql_escape_string($customer) . "' ";    
}


if ($_REQUEST['d1']) {
    $d1 = $_REQUEST['d1'];
    $d2 = $_REQUEST['d2'];
} else {
    $d1 = date('d-m-Y', mktime(0, 0, 0, date('n'), 1, date('Y')));
    $d2 = date('d-m-Y', mktime(0, 0, 0, date('n') + 1, 0, date('Y')));
}
$d1sql = npl_dmy2ymd($d1);
$d2sql = npl_dmy2ymd($d2);


// load hari libur
$rsx = mysql_query(
        "SELECT * "
        . "FROM m_calendar "
        . "WHERE calendar_date BETWEEN '{$d1sql}' AND '{$d2sql}' "
        . "AND isholiday = 'Y'",
        $APP_CONNECTION);
while ($dtx = mysql_fetch_array($rsx, MYSQL_ASSOC)) {
    $holiday[$dtx['calendar_date']] = $dtx;
}
mysql_free_result($rsx);


// fetch from c_order_line
$rsx = mysql_query(
        "SELECT * FROM c_order_line "
        . "JOIN c_order USING (c_order_id) "
        . "WHERE processed = 'N' "
        . "AND schedule_delivery_date BETWEEN '{$d1sql}' AND '{$d2sql}' "
        . "AND order_quantity > 0",
        $APP_CONNECTION);
while ($dtx = mysql_fetch_array($rsx, MYSQL_ASSOC)) {
    $sql1 = "INSERT INTO c_delivery_plan ("
            . "app_org_id, schedule_delivery_date, m_product_id, "
            . "order_quantity, delivered_quantity, order_line_ref "
            . ") VALUES ("
            . "'{$dtx['app_org_id']}', '{$dtx['schedule_delivery_date']}', '{$dtx['m_product_id']}', "
            . "'{$dtx['order_quantity']}', '{$dtx['delivered_quantity']}', '{$dtx['c_order_line_id']}')";
    $sql2 = "UPDATE c_order_line SET processed = 'Y' WHERE c_order_line_id = '{$dtx['c_order_line_id']}'";
    if (mysql_query($sql1, $APP_CONNECTION)) mysql_query ($sql2, $APP_CONNECTION);
}
mysql_free_result($rsx);


// form filter, date and organization
echo "<div class='title'>Delivery Plan</div>";
echo "<div class='data_box'>";
echo "<form action='module.php'>";
echo "<input type='hidden' name='m' value='trx.dp'>";
echo "<table>";
echo "<tr>";
echo "<td>Sales Order Period</td>";
echo "<td><input name='d1' id='d1' type='text' size='10' style='text-align: center;' value='{$d1}'></td>";
echo "<td> to </td>";
echo "<td><input name='d2' id='d2' type='text' size='10' style='text-align: center;' value='{$d2}'></td>";
echo "</tr>";
echo "<tr>";
echo "<td>Organization.</td>";
echo "<td colspan='3'>" . cgx_form_select('org', "SELECT app_org_id, organization FROM app_org WHERE is_trx = 'Y' ORDER BY organization", $def_org, FALSE) . "</td>";
echo "</tr>";
echo "<tr>";
echo "<td>Customer</td>";
echo "<td colspan='3'>" . cgx_filter('customer', "SELECT c_bpartner_id, partner_name FROM c_bpartner WHERE " . org_filter_master() . " ORDER BY partner_name", $customer, TRUE) . "</td>\n";
echo "</tr>";
echo "<tr>";
echo "<td></td>";
echo "<td><input type='submit' value='View'></td>";
echo "</tr>";
echo "</table>";
echo "</form>";
echo "</div>";

echo "<div class='data_box'>";
echo "<center><form id='text-finder' class='text-finder' action='module.php'>";
echo "<input type='text' size='40' placeholder='Find...'><input type='reset' value='&times;'>";
echo "</form></center>";
echo "</div>";

// display grid
$ts1 = strtotime($d1sql);
$ts2 = strtotime($d2sql);
echo "<form action='action/trx.dp.php' method='post'>";

echo "<input type='hidden' name='ref' value='{$_REQUEST['ref']}'>";
echo "<input type='hidden' name='product' value='{$_REQUEST['product']}'>";
echo "<input type='hidden' name='mode' value='update'>";
echo "<input type='hidden' name='org' value='{$def_org}'>";
echo "<input type='hidden' name='customer' value='{$customer}'>";
echo "<input type='hidden' name='d1' value='{$d1}'>";
echo "<input type='hidden' name='d2' value='{$d2}'>";

echo "<table id='bar' class='datagrid_bar' width='100%' border='0' style='margin-top: 4px;'><tr>";
echo "<td><input type='submit' value='Save'></td>";
echo "<td width='1' class='datagrid_bar_icon'><a title='Export semua data ke CSV' href='javascript:exportCSV();'><img border='0' src='images/icon_csv.png'></a></td>\n";
echo "</tr></table>";

echo "<table cellspacing='1' class='datagrid_background' style='margin-top: 4px;'>";
echo "<tr style='height: 30px;'>";
echo "<th class='datagrid_header' style='width: 30px;'>No</th>";
echo "<th class='datagrid_header'>Item Number</th>";
echo "<th class='datagrid_header'>Product Name</th>";
echo "<th class='datagrid_header'>No. SC</th>";
echo "<th class='datagrid_header'>Remark</th>";
echo "<th class='datagrid_header'>Customer</th>";
echo "<th class='datagrid_header'>Stock Qty</th>";
// echo "<th class='datagrid_header'>Kap. Mesin</th>";
// echo "<th class='datagrid_header'>Min. Cutt</th>";
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
        "SELECT c_delivery_plan.m_product_id, product_code, spec, od, thickness, length, 
        SUM(c_delivery_plan.order_quantity) order_quantity, balance_quantity, (multipleqty * bundle) as min_cut
        FROM c_delivery_plan
        JOIN c_order_line ON (c_delivery_plan.order_line_ref = c_order_line.c_order_line_id)
        JOIN c_order USING (c_order_id)  
        JOIN m_product ON (c_delivery_plan.m_product_id=m_product.m_product_id)
        LEFT JOIN m_material_requirement ON(c_delivery_plan.m_product_id=m_material_requirement.m_product_fg)
        LEFT JOIN (SELECT m_product_id, balance_quantity FROM m_stock_balance_d_2 WHERE app_org_id = '{$def_org}' AND latest='Y') sb ON (c_delivery_plan.m_product_id=sb.m_product_id)
        WHERE c_delivery_plan.schedule_delivery_date BETWEEN '{$d1sql}' AND '{$d2sql}'
        AND c_delivery_plan.app_org_id = '{$def_org}'
        ". $sql_customer ."
        GROUP BY m_product_id, product_code ORDER BY m_product_id",
        $APP_CONNECTION);
while ($dtx = mysql_fetch_array($rsx, MYSQL_ASSOC)) {
    echo "<tr style='background: #ffffff; height: 26px;'>";
    echo "<td align='right'>" . (++$i) . "</td>";
    echo "<td>{$dtx['product_code']}</td>";
    echo "<td>{$dtx['spec']}-{$dtx['od']}-{$dtx['thickness']}-{$dtx['length']}</td>";
    $rsz = mysql_query(
            "SELECT DISTINCT c_order.document_no sales_order, remark, mid(partner_name,1,20) partner_name
            FROM c_delivery_plan
            JOIN c_order_line ON (c_delivery_plan.order_line_ref = c_order_line.c_order_line_id)
            JOIN c_order USING (c_order_id)
            LEFT JOIN c_bpartner ON (c_order.c_bpartner_id=c_bpartner.c_bpartner_id)
            WHERE c_delivery_plan.schedule_delivery_date BETWEEN '{$d1sql}' AND '{$d2sql}'
            AND c_delivery_plan.app_org_id = '{$def_org}'
            AND c_delivery_plan.m_product_id = '{$dtx['m_product_id']}'",
            $APP_CONNECTION);
    $no_sc_nya = '';
    $remark_nya = '';
    $partner_nya = '';
    while ($dtz = mysql_fetch_array($rsz, MYSQL_ASSOC)){
        $no_sc_nya .= $dtz['sales_order'] .', ';
        $remark_nya .= $dtz['remark'] .', ';
        $partner_nya .= $dtz['partner_name'] .', ';
    }
    echo "<td>{$no_sc_nya}</td>";
    echo "<td>{$remark_nya}</td>";
    echo "<td>{$partner_nya}</td>";
    echo "<td align='right'>" . number_format($dtx['balance_quantity']) . "</td>";
    // $rsza = mysql_query(
    //         "SELECT result_shift, machine_code FROM m_machine_item
    //         JOIN m_machine ON(m_machine_item.m_machine_id=m_machine.m_machine_id)
    //         WHERE m_product_id = '{$dtx['m_product_id']}'",
    //         $APP_CONNECTION);
    // $kaps_mesin = '';
    // while ($dtza = mysql_fetch_array($rsza, MYSQL_ASSOC)){
    //     $kaps_mesin .= $dtza['machine_code'] .'('. $dtza['result_shift'] .'), ';
    // }
    // echo "<td>{$kaps_mesin}</td>";
    // echo "<td align='right'>" . number_format($dtx['min_cut']) . "</td>";
    echo "<td align='right'>" . number_format($dtx['order_quantity']) . "</td>";
    $rsy = mysql_query(
            "SELECT c_delivery_plan.*
            FROM c_delivery_plan
            WHERE schedule_delivery_date BETWEEN '{$d1sql}' AND '{$d2sql}'
            AND app_org_id = '{$def_org}'
            AND m_product_id = '{$dtx['m_product_id']}'",
            $APP_CONNECTION);
    unset($data);
    while ($dty = mysql_fetch_array($rsy, MYSQL_ASSOC)) {
        $data[$dty['schedule_delivery_date']] = $dty;
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
        if ($data[$d]['order_quantity'] > 0) {
            if ($_REQUEST['ref'] == $data[$d]['order_line_ref']) {
                echo "<td style='{$textstyle}' align='right'><input name='{$iname}' type='text' style='{$textstyle} width: 40px; text-align: right;' value='{$data[$d]['order_quantity']}'></th>";
            } else {
                $link = "module.php?m=trx.dp&d1={$d1}&d2={$d2}&org={$def_org}&customer={$customer}&product={$data[$d]['m_product_id']}&ref={$data[$d]['order_line_ref']}";
                echo "<td style='{$textstyle}' align='right'><a href='{$link}'>" . number_format($data[$d]['order_quantity']) . "</a></th>";
            }
        } else {
            if ($_REQUEST['product'] == $dtx['m_product_id']) {
                echo "<td style='{$textstyle}' align='right'><input name='{$iname}' type='text' style='{$textstyle} width: 40px; text-align: right;' value='0'></th>";
            } else {
                echo "<td style='{$textstyle}' align='right' style='color: #888888;'>0</th>";
            }
        }
        if ($day >= $max_days) break;
    }
    echo "</tr>";
}
mysql_free_result($rsx);
echo "</table>";

echo "<table id='bar' class='datagrid_bar' width='100%' border='0' style='margin-top: 4px;'><tr>";
echo "<td><input type='submit' value='Save'></td>";
echo "</tr></table>";

echo "</form>";

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

function exportCSV() {   
    form = document.createElement("form");
    form.setAttribute("method", "post");
    form.setAttribute("action", "action/trx.dp.php");

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "mode");
    hiddenField.setAttribute("value", "export-all");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "def_org");
    hiddenField.setAttribute("value", "<?php echo $def_org; ?>");
    form.appendChild(hiddenField);    

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "d1sql");
    hiddenField.setAttribute("value", "<?php echo $d1sql; ?>");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "d2sql");
    hiddenField.setAttribute("value", "<?php echo $d2sql; ?>");
    form.appendChild(hiddenField);

    document.body.appendChild(form);
    form.submit();    
}

</script>