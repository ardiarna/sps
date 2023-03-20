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

$cgx_sql = "SELECT 
            m_receipt_longpipe.m_receipt_longpipe_id, 
            m_receipt_longpipe.receipt_date tanggalpenerimaan, 
            m_receipt_longpipe.document_no nomordokumen, 
            m_work_order.document_no norequest, 
            
            m_work_order.order_date tanggalrequest,
            remark nosc, 
            reference_no nopo,
            partner_name, 
            document_no_bmbj,
            quantity_bmbj,
            m_product.*,
            cu.user_fullname,
            au.user_fullname user_fullname_u

            FROM m_receipt_longpipe

            JOIN m_work_order USING (m_work_order_id)
            JOIN m_receipt_longpipe_line USING (m_receipt_longpipe_id)
            JOIN m_work_order_line USING (m_work_order_line_id)
 
            LEFT JOIN c_order USING (c_order_id) 
            JOIN c_bpartner ON (c_order.c_bpartner_id = c_bpartner.c_bpartner_id)
            JOIN m_product ON(m_work_order_line.m_product_material=m_product.m_product_id)
            LEFT JOIN app_user cu ON (m_receipt_longpipe.create_user=cu.user_id) 
            LEFT JOIN app_user au ON (m_receipt_longpipe.update_user=au.user_id)

            WHERE 1=1";

$date_f = $_REQUEST['date_f'];
$date_t = $_REQUEST['date_t'];
$date_f2 = $_REQUEST['date_f2'];
$date_t2 = $_REQUEST['date_t2'];
$item_number = $_REQUEST['item_number'];
$no_request = $_REQUEST['no_request'];
$sc_number = $_REQUEST['sc_number'];
$customer = $_REQUEST['customer'];

if ($no_request) $cgx_sql .= " AND m_work_order.document_no LIKE '%" . mysql_escape_string($no_request) . "%'";
if ($item_number) $cgx_sql .= " AND (product_code LIKE '%" . mysql_escape_string($item_number) . "%' OR product_name LIKE '%" . mysql_escape_string($item_number) . "%' OR description LIKE '%" . mysql_escape_string($item_number) . "%')";
if ($date_f) $cgx_sql .= " AND m_work_order.order_date >= '" . npl_dmy2ymd($date_f) . "'";
if ($date_t) $cgx_sql .= " AND m_work_order.order_date <= '" . npl_dmy2ymd($date_t) . "'";
if ($date_f2) $cgx_sql .= " AND m_receipt_longpipe.receipt_date >= '". npl_dmy2ymd($date_f2)."'";
if ($date_t2) $cgx_sql .= " AND m_receipt_longpipe.receipt_date <= '". npl_dmy2ymd($date_t2)."'";
if ($sc_number) $cgx_sql .= " AND c_order.remark LIKE '%" . mysql_escape_string($sc_number) . "%'";
if ($customer) $cgx_sql .= " AND c_bpartner.partner_name LIKE '%" . mysql_escape_string($customer) . "%'";

if ($_REQUEST['mode'] == 'export-all') {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=\"penerimaan-request-LP-" . date("Y-m-d") . ".csv\"");
    echo "\"NO. DOKUMEN\"";
    echo ",\"TANGGAL PENERIMAAN\"";
    echo ",\"NO. REQUEST\"";
    echo ",\"TANGGAL REQUEST\"";
    echo ",\"NO. SC\"";
    echo ",\"NO. PO\"";
    echo ",\"CUSTOMER\"";
    echo ",\"ITEM NUMBER\"";
    echo ",\"SPEC\"";
    echo ",\"OD\"";
    echo ",\"THICKNESS\"";
    echo ",\"LENGTH\"";
    echo ",\"BMBB\"";
    echo ",\"QTY PENERIMAAN\"";
    echo "\n";
    $cgx_rs_export = mysql_query($cgx_sql, $cgx_connection);
    
    while (($cgx_dt_export = mysql_fetch_array($cgx_rs_export, MYSQL_ASSOC)) !== FALSE) {
        echo "\"" . str_replace("\"", "\"\"", $cgx_dt_export['nomordokumen']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['tanggalpenerimaan']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['norequest']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['tanggalrequest']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['nosc']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['nopo']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['partner_name']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['product_code']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['spec']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['od']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['thickness']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['length']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['document_no_bmbj']). "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['quantity_bmbj']) . "\"";
       echo "\n";
    }
    mysql_free_result($cgx_rs_export);
    exit;
} elseif ($_REQUEST['mode'] == 'delete') {
    $cgx_sql = "DELETE FROM  ";
    $cgx_sql .= " WHERE";
}

if (@mysql_query($cgx_sql, $cgx_connection)) {
    $_SESSION[$GLOBALS['APP_ID']]['view.prlp']['error'] = FALSE;
    $_SESSION[$GLOBALS['APP_ID']]['view.prlp']['info'] = 'Data anda sudah berhasil diperbarui';
    if ($_REQUEST['mode'] == 'new') $cgx_new_id = $_REQUEST['data']['Array'];
} else {
    $_SESSION[$GLOBALS['APP_ID']]['view.prlp']['error'] = mysql_error($cgx_connection);
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