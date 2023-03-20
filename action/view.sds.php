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

if ($_REQUEST['mode'] == 'export-all') {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=\"view.sds-" . date("Y-m-d") . ".csv\"");
    echo "\"Tanggal Kirim\",\"Kode Customer\",\"Nama Customer\",\"Item Number\",\"Spec\",\"OD\",\"Thickness\",\"Length\",\"Quantity\",\"Stock Qty\",\"Saldo\",\"SC Number\"\n";
    $cgx_rs_export = mysql_query("SELECT o.document_no,  order_date, partner_code, partner_name,
product_code, product_name, spec, od, thickness, length, ol.*, balance_quantity,
balance_quantity - order_quantity saldo
FROM c_order o
JOIN c_order_line ol ON (o.c_order_id = ol.c_order_id)
JOIN c_bpartner bp USING (c_bpartner_id)
JOIN m_product USING (m_product_id)
LEFT JOIN (SELECT m_product_id, balance_quantity FROM m_stock_balance_d_2 WHERE latest = 'Y') sb USING (m_product_id)
WHERE order_quantity - delivered_quantity + return_quantity > 0
AND line_status != 'C' AND status != 'C' AND " . org_filter_trx('o.app_org_id'), $cgx_connection);
    while (($cgx_dt_export = mysql_fetch_array($cgx_rs_export, MYSQL_ASSOC)) !== FALSE) {
        echo "\"" . str_replace("\"", "\"\"", $cgx_dt_export['schedule_delivery_date']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['partner_code']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['partner_name']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['product_code']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['spec']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['od']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['thickness']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['length']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['order_quantity']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['balance_quantity']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['saldo']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['document_no']) . "\"";
        echo "\n";
    }
    mysql_free_result($cgx_rs_export);
    exit;
}

if (@mysql_query($cgx_sql, $cgx_connection)) {
    $_SESSION[$GLOBALS['APP_ID']]['view.sds']['error'] = FALSE;
    $_SESSION[$GLOBALS['APP_ID']]['view.sds']['info'] = 'Data anda sudah berhasil diperbarui';
    if ($_REQUEST['mode'] == 'new') $cgx_new_id = $_REQUEST['data']['Array'];
} else {
    $_SESSION[$GLOBALS['APP_ID']]['view.sds']['error'] = mysql_error($cgx_connection);
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