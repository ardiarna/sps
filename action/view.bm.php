<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 05/12/2013 14:07:57
 */

require_once '../init.php';

if (!authenticated()) {
    header("Location: ../index.php");
    exit;
}

$cgx_sql = "SELECT m_inout.document_no, m_inout.m_inout_date, m_inout.dokumen, m_inout.c_order_id, c_order.remark so_number, m_inout_line.*, 
m_product.*, warehouse_name, quantity, partner_code, partner_name, m_inout.create_date, m_inout.update_date, auc.user_fullname, auu.user_fullname user_fullname_u
FROM m_inout
LEFT JOIN c_order USING (c_order_id)
LEFT JOIN c_bpartner USING (c_bpartner_id)
JOIN m_inout_line USING (m_inout_id)
JOIN m_product USING (m_product_id)
JOIN m_warehouse USING (m_warehouse_id)
LEFT JOIN app_user auc ON (m_inout.create_user=auc.user_id)
LEFT JOIN app_user auu ON (m_inout.update_user=auu.user_id)
WHERE m_inout.m_transaction_type_id = 3 AND " . org_filter_trx('m_inout.app_org_id');

$title_header = $_REQUEST['title_header'];
$gudang = $_REQUEST['gudang'];
$date_f = $_REQUEST['date_f'];
$date_t = $_REQUEST['date_t'];
$item_number = $_REQUEST['item_number'];
$document_no = $_REQUEST['document_no'];
$sc_number = $_REQUEST['sc_number'];
$customer = $_REQUEST['customer'];

if ($gudang) $cgx_sql .= " AND m_inout_line.m_warehouse_id = '" . mysql_escape_string($gudang) . "'";
if ($document_no) $cgx_sql .= " AND m_inout.document_no LIKE '%" . mysql_escape_string($document_no) . "%'";
if ($item_number) $cgx_sql .= " AND (m_product.product_code LIKE '%" . mysql_escape_string($item_number) . "%' OR m_product.product_name LIKE '%" . mysql_escape_string($item_number) . "%' OR m_product.description LIKE '%" . mysql_escape_string($item_number) . "%')";
if ($date_f) $cgx_sql .= " AND m_inout_date >= '" . npl_dmy2ymd($date_f) . "'";
if ($date_t) $cgx_sql .= " AND m_inout_date <= '" . npl_dmy2ymd($date_t) . "'";
if ($sc_number) $cgx_sql .= " AND c_order.remark LIKE '%" . mysql_escape_string($sc_number) . "%'";
if ($customer) $cgx_sql .= " AND c_bpartner.partner_name LIKE '%" . mysql_escape_string($customer) . "%'";

if ($_REQUEST['mode'] == 'export-all') {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=\"".$title_header."-" . date("Y-m-d") . ".csv\"");
    echo "\"No. Penerimaan\",\"Tgl Penerimaan\",\"Remark\",\"Kode Customer\",\"Nama Customer\",\"Item Number\",\"Spec\",\"OD\",\"Thickness\",\"Length\",\"Description\",\"Gudang\",\"Jumlah Barang\"\n";
    $cgx_rs_export = mysql_query($cgx_sql, $cgx_connection);
    while (($cgx_dt_export = mysql_fetch_array($cgx_rs_export, MYSQL_ASSOC)) !== FALSE) {
        echo "\"" . str_replace("\"", "\"\"", $cgx_dt_export['document_no']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['m_inout_date']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['so_number']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['partner_code']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['partner_name']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['product_code']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['spec']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['od']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['thickness']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['length']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['description']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['warehouse_name']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['quantity']) . "\"";
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
    $_SESSION[$GLOBALS['APP_ID']]['view.bm']['error'] = FALSE;
    $_SESSION[$GLOBALS['APP_ID']]['view.bm']['info'] = 'Data anda sudah berhasil diperbarui';
    if ($_REQUEST['mode'] == 'new') $cgx_new_id = $_REQUEST['data']['Array'];
} else {
    $_SESSION[$GLOBALS['APP_ID']]['view.bm']['error'] = mysql_error($cgx_connection);
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