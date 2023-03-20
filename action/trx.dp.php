<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.org>
 * Apr 29, 2014 11:57:46 AM
 */

require_once '../init.php';


if ($_REQUEST['mode'] == 'export-all') {
    $max_days = 31;
    $def_org = $_REQUEST['def_org'];
    $d1sql = $_REQUEST['d1sql'];
    $d2sql = $_REQUEST['d2sql'];
    $ts1 = strtotime($d1sql);
    $ts2 = strtotime($d2sql);
    $cgx_sql = "SELECT c_delivery_plan.m_product_id, product_code, spec, od, thickness, length, 
        SUM(c_delivery_plan.order_quantity) order_quantity, balance_quantity, (multipleqty * bundle) as min_cut
        FROM c_delivery_plan  
        JOIN m_product ON (c_delivery_plan.m_product_id=m_product.m_product_id)
        LEFT JOIN m_material_requirement ON(c_delivery_plan.m_product_id=m_material_requirement.m_product_fg)
        LEFT JOIN (SELECT m_product_id, balance_quantity FROM m_stock_balance_d_2 WHERE app_org_id = '{$def_org}' AND latest='Y') sb ON (c_delivery_plan.m_product_id=sb.m_product_id)
        WHERE c_delivery_plan.schedule_delivery_date BETWEEN '{$d1sql}' AND '{$d2sql}'
        AND c_delivery_plan.app_org_id = '{$def_org}'
        GROUP BY m_product_id, product_code ORDER BY m_product_id";

    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=\"delivey-" . date("Y-m-d") . ".csv\"");
    echo "\"PRODUK ID\"";
    echo ",\"ITEM NUMBER\"";
    echo ",\"SPEC\"";
    echo ",\"OD\"";
    echo ",\"THICKNESS\"";
    echo ",\"LENGTH\"";
    echo ",\"NO. SC\"";
    echo ",\"REMARK\"";
    echo ",\"CUSTOMER\"";
    echo ",\"STOK QTY\"";
    echo ",\"ORDER QTY\"";
    $day = 0;
        for ($t = $ts1; $t <= $ts2; $t += 86400) {
            $day++;
            echo ",\"".date('y/m/d', $t)."\"";
            if ($day >= $max_days) break;
        }
    echo "\n";
    $cgx_rs_export = mysql_query($cgx_sql, $cgx_connection);
    while (($cgx_dt_export = mysql_fetch_array($cgx_rs_export, MYSQL_ASSOC)) !== FALSE) {
        $rsz = mysql_query(
            "SELECT DISTINCT c_order.document_no sales_order, remark, mid(partner_name,1,20) partner_name 
            FROM c_delivery_plan
            JOIN c_order_line ON (c_delivery_plan.order_line_ref = c_order_line.c_order_line_id)
            JOIN c_order USING (c_order_id)
            LEFT JOIN c_bpartner ON (c_order.c_bpartner_id=c_bpartner.c_bpartner_id)
            WHERE c_delivery_plan.schedule_delivery_date BETWEEN '{$d1sql}' AND '{$d2sql}'
            AND c_delivery_plan.app_org_id = '{$def_org}'
            AND c_delivery_plan.m_product_id = '{$cgx_dt_export['m_product_id']}'",
            $APP_CONNECTION);
        $no_sc_nya = '';
        $remark_nya = '';
        $partner_nya = '';
        while ($dtz = mysql_fetch_array($rsz, MYSQL_ASSOC)){
            $no_sc_nya .= $dtz['sales_order'] .', ';
            $remark_nya .= $dtz['remark'] .', ';
            $partner_nya .= $dtz['partner_name'] .', ';
        }
        echo "\"" . str_replace("\"", "\"\"", $cgx_dt_export['m_product_id']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['product_code']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['spec']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['od']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['thickness']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['length']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $no_sc_nya) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $remark_nya) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $partner_nya) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['balance_quantity']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['order_quantity']) . "\"";
        $rsy = mysql_query(
                "SELECT * FROM c_delivery_plan WHERE schedule_delivery_date BETWEEN '{$d1sql}' AND '{$d2sql}' 
                AND app_org_id = '{$def_org}' AND m_product_id = '{$cgx_dt_export['m_product_id']}'",$APP_CONNECTION);
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
            if ($data[$d]['order_quantity'] > 0) {
                echo ",\"" . str_replace("\"", "\"\"", $data[$d]['order_quantity']) . "\"";
            }else{
                echo ",\"" . str_replace("\"", "\"\"", 0) . "\"";
            }
            if ($day >= $max_days) break;
        }
        echo "\n";
    }
    mysql_free_result($cgx_rs_export);
    exit;
} elseif ($_REQUEST['mode'] == 'update') {
    foreach ($_REQUEST['data'] as $date => $value) {
        if ($value == 0) {
            mysql_query(
                "DELETE FROM c_delivery_plan "
                . "WHERE app_org_id = '{$_REQUEST['org']}' "
                . "AND schedule_delivery_date = '{$date}' "
                . "AND m_product_id = '{$_REQUEST['product']}' "
                . "AND order_line_ref = '{$_REQUEST['ref']}'",
                $APP_CONNECTION);
        } else {
            mysql_query(
                "INSERT INTO c_delivery_plan ("
                    . "app_org_id, schedule_delivery_date, m_product_id, "
                    . "order_quantity, order_line_ref) VALUES ("
                    . "'{$_REQUEST['org']}', '{$date}', '{$_REQUEST['product']}', "
                    . "'{$value}', '{$_REQUEST['ref']}') "
                    . "ON DUPLICATE KEY UPDATE "
                    . "order_quantity = '{$value}'",
                $APP_CONNECTION);
        }
        echo mysql_error($APP_CONNECTION);
    }
}

header("Location: ../module.php?m=trx.dp&d1={$_REQUEST['d1']}&d2={$_REQUEST['d2']}&org={$_REQUEST['org']}&customer={$_REQUEST['customer']}");
exit;

?>