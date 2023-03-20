<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 05/12/2013 14:35:39
 */

require_once '../init.php';

if (!authenticated()) {
    header("Location: ../index.php");
    exit;
}

$cgx_sql = "SELECT c_order.c_order_id, document_no, remark, reference_no, partner_name, wol.m_product_id, product_code, product_name, spec, od, thickness, length, order_so, order_wo, (order_wo - order_so) sisa_wo 
FROM c_order JOIN (SELECT c_order_id, m_product_id, SUM(order_quantity) order_wo FROM m_work_order_line GROUP BY c_order_id, m_product_id) wol ON(c_order.c_order_id = wol.c_order_id)
JOIN (SELECT c_order_id, m_product_id, SUM(order_quantity) order_so FROM c_order_line WHERE order_quantity - delivered_quantity + return_quantity > 0
AND line_status != 'C' GROUP BY c_order_id, m_product_id) col ON(wol.c_order_id = col.c_order_id AND wol.m_product_id = col.m_product_id)
JOIN c_bpartner ON(c_order.c_bpartner_id = c_bpartner.c_bpartner_id)
JOIN m_product ON(wol.m_product_id = m_product.m_product_id) WHERE 1 = 1 AND (order_wo - order_so) < 0 AND " . org_filter_trx('c_order.app_org_id');

$item_number = $_REQUEST['item_number'];
$sc_number = $_REQUEST['sc_number'];
$customer = $_REQUEST['customer'];

if ($item_number) $cgx_sql .= " AND (product_code LIKE '%" . mysql_escape_string($item_number) . "%' OR product_name LIKE '%" . mysql_escape_string($item_number) . "%' OR description LIKE '%" . mysql_escape_string($item_number) . "%')";
if ($sc_number) $cgx_sql .= " AND c_order.remark LIKE '%" . mysql_escape_string($sc_number) . "%'";
if ($customer) $cgx_sql .= " AND c_bpartner.partner_name LIKE '%" . mysql_escape_string($customer) . "%'";

if ($_REQUEST['mode'] == 'export-all') {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=\"SO vs WO-" . date("Y-m-d") . ".csv\"");
    echo "\"NO. SC\"";
    echo ",\"REMARK\"";
    echo ",\"PO NUMBER\"";
    echo ",\"CUSTOMER\"";
    echo ",\"ITEM NUMBER\"";
    echo ",\"SPEC\"";
    echo ",\"OD\"";
    echo ",\"THICKNESS\"";
    echo ",\"LENGTH\"";
    echo ",\"QTY ORDER\"";
    echo ",\"QTY WO\"";
    echo ",\"SISA WO\"";
    echo "\n";
    $cgx_rs_export = mysql_query($cgx_sql, $cgx_connection);
    while (($cgx_dt_export = mysql_fetch_array($cgx_rs_export, MYSQL_ASSOC)) !== FALSE) {
        echo "\"" . str_replace("\"", "\"\"", $cgx_dt_export['document_no']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['remark']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['reference_no']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['partner_name']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['product_code']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['spec']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['od']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['thickness']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['length']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['order_so']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['order_wo']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['sisa_wo']) . "\"";
        echo "\n";
    }
    mysql_free_result($cgx_rs_export);
    exit;
} elseif ($_REQUEST['mode'] == 'delete') {
    $cgx_sql = "DELETE FROM  ";
    $cgx_sql .= " WHERE";
}

if (@mysql_query($cgx_sql, $cgx_connection)) {
    $_SESSION[$GLOBALS['APP_ID']]['view.sowo']['error'] = FALSE;
    $_SESSION[$GLOBALS['APP_ID']]['view.sowo']['info'] = 'Data anda sudah berhasil diperbarui';
    if ($_REQUEST['mode'] == 'new') $cgx_new_id = $_REQUEST['data']['Array'];
} else {
    $_SESSION[$GLOBALS['APP_ID']]['view.sowo']['error'] = mysql_error($cgx_connection);
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