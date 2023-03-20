<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 05/12/2013 13:41:52
 */

require_once '../init.php';

if (!authenticated()) {
    header("Location: ../index.php");
    exit;
}

$cgx_sql = "SELECT o.document_no,  order_date, partner_code, partner_name,
product_code, product_name, spec, od, thickness, length , ol.*,
order_quantity - delivered_quantity + return_quantity outstanding,
DATEDIFF(NOW(),schedule_delivery_date) total_delay
FROM c_order o
JOIN c_order_line ol ON (o.c_order_id = ol.c_order_id)
JOIN c_bpartner bp USING (c_bpartner_id)
JOIN m_product USING (m_product_id)
WHERE order_quantity - delivered_quantity + return_quantity > 0
AND line_status != 'C' AND status != 'C'
AND schedule_delivery_date <= NOW()";


if (org() != '1') {  
$cgx_sql .= "AND " . org_filter_trx('o.app_org_id');
}

$customer = $_REQUEST['partner_name'];

if ($customer) $cgx_sql .= " AND bp.partner_name LIKE '%" . mysql_escape_string($customer) . "%'";

if ($_REQUEST['mode'] == 'export-all') {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=\"view.dd-" . date("Y-m-d") . ".csv\"");
    echo "\"SC Number\",\"Item Code\",\"Item Name\",\"Sch. Qty\",\"Actual Qty\",\"Balance\",\"Due Date\",\"Total Delay (days)\"\n";
    $cgx_rs_export = mysql_query($cgx_sql, $cgx_connection);
    while (($cgx_dt_export = mysql_fetch_array($cgx_rs_export, MYSQL_ASSOC)) !== FALSE) {
        echo "\"" . str_replace("\"", "\"\"", $cgx_dt_export['document_no']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['product_code']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['product_name']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['order_quantity']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['delivered_quantity']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['outstanding']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['schedule_delivery_date']) . "\"";      
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['total_delay']) . "\"";
        echo "\n";
    }
    mysql_free_result($cgx_rs_export);
    exit;
}

if (@mysql_query($cgx_sql, $cgx_connection)) {
    $_SESSION[$GLOBALS['APP_ID']]['view.dd']['error'] = FALSE;
    $_SESSION[$GLOBALS['APP_ID']]['view.dd']['info'] = 'Data anda sudah berhasil diperbarui';
    if ($_REQUEST['mode'] == 'new') $cgx_new_id = $_REQUEST['data']['Array'];
} else {
    $_SESSION[$GLOBALS['APP_ID']]['view.dd']['error'] = mysql_error($cgx_connection);
}

if ($_REQUEST['mode'] == 'update') {
    header("Location: ../index.php?" . urldecode($_REQUEST['backvar']) . "&");
} elseif ($_REQUEST['mode'] == 'new') {
    header("Location: ../index.php?" . urldecode($_REQUEST['backvar']) . "&");
} elseif ($_REQUEST['mode'] == 'delete') {
    header("Location: ../index.php?" . urldecode($_REQUEST['backvar']));
}
exit;

?>