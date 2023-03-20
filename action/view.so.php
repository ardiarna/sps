<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 15/01/2014 14:08:39
 */

require_once '../init.php';

if (!authenticated()) {
    header("Location: ../index.php");
    exit;
}

$cgx_sql = "SELECT c_order.*, partner_code, partner_name, product_code, spec, od, thickness, length, schedule_delivery_date, 
    order_quantity, delivered_quantity, (order_quantity - delivered_quantity) as outstanding 
    FROM c_order JOIN c_order_line USING (c_order_id) 
    JOIN c_bpartner USING (c_bpartner_id) 
    JOIN m_product USING (m_product_id) 
    WHERE m_transaction_type_id = '1' AND " . org_filter_trx('c_order.app_org_id');

$order_f = $_REQUEST['order_f'];
$order_t = $_REQUEST['order_t'];
$delivery_f = $_REQUEST['delivery_f'];
$delivery_t = $_REQUEST['delivery_t'];
$customer = $_REQUEST['customer'];
$sc_number = $_REQUEST['sc_number'];
$item_number = $_REQUEST['item_number'];

if ($sc_number) $cgx_sql .= " AND c_order.remark LIKE '%" . mysql_escape_string($sc_number) . "%'";
if ($customer) $cgx_sql .= " AND c_bpartner.partner_name LIKE '%" . mysql_escape_string($customer) . "%'";
if ($item_number) $cgx_sql .= " AND (m_product.product_code LIKE '%" . mysql_escape_string($item_number) . "%' OR m_product.product_name LIKE '%" . mysql_escape_string($item_number) . "%' OR m_product.description LIKE '%" . mysql_escape_string($item_number) . "%')";
if ($order_f) $cgx_sql .= " AND c_order.order_date >= '" . npl_dmy2ymd($order_f) . "'";
if ($order_t) $cgx_sql .= " AND c_order.order_date <= '" . npl_dmy2ymd($order_t) . "'";
if ($delivery_f) $cgx_sql .= " AND c_order_line.schedule_delivery_date >= '" . npl_dmy2ymd($delivery_f) . "'";
if ($delivery_t) $cgx_sql .= " AND c_order_line.schedule_delivery_date <= '" . npl_dmy2ymd($delivery_t) . "'";

if ($_REQUEST['mode'] == 'export-all') {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=\"SO-detail-" . date("Y-m-d") . ".csv\"");
    echo "\"ID\",\"Remark\",\"PO Number\",\"Kode Customer\",\"Customer\",\"Item Number\",\"Spec\",\"OD\",\"Tebal\",\"Panjang\",\"Tgl Order\",\"Jadwal Kirim\",\"Jumlah Order\",\"Jumlah Delivery\",\"Kurang Delivery\"\n";
    $cgx_rs_export = mysql_query($cgx_sql, $cgx_connection);
    while (($cgx_dt_export = mysql_fetch_array($cgx_rs_export, MYSQL_ASSOC)) !== FALSE) {
        echo "\"" . str_replace("\"", "\"\"", $cgx_dt_export['c_order_id']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['remark']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['reference_no']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['partner_code']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['partner_name']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['product_code']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['spec']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['od']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['thickness']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['length']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['order_date']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['schedule_delivery_date']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['order_quantity']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['delivered_quantity']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['outstanding']) . "\"";
        echo "\n";
    }
    mysql_free_result($cgx_rs_export);
    exit;
} elseif ($_REQUEST['mode'] == 'update') {
    $cgx_sql = "UPDATE  SET";
    $cgx_sql .= " WHERE";
} elseif ($_REQUEST['mode'] == 'new') {
    $cgx_sql = "INSERT INTO  (";
    $cgx_sql .= "INSERT INTO  (";
    $cgx_sql .= ") values (";
    $cgx_sql .= ")";
} elseif ($_REQUEST['mode'] == 'delete') {
    $cgx_sql = "DELETE FROM  ";
    $cgx_sql .= " WHERE";
}

if (@mysql_query($cgx_sql, $cgx_connection)) {
    $_SESSION[$GLOBALS['APP_ID']]['view.so']['error'] = FALSE;
    $_SESSION[$GLOBALS['APP_ID']]['view.so']['info'] = 'Data anda sudah berhasil diperbarui';
    if ($_REQUEST['mode'] == 'new') $cgx_new_id = $_REQUEST['data']['Array'];
} else {
    $_SESSION[$GLOBALS['APP_ID']]['view.so']['error'] = mysql_error($cgx_connection);
}

if ($_REQUEST['mode'] == 'update') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']) . "&");
} elseif ($_REQUEST['mode'] == 'new') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']) . "&");
} elseif ($_REQUEST['mode'] == 'delete') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']));
}
exit;

?>