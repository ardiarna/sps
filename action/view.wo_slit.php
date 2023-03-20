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

$cgx_filter1 = urldecode($_REQUEST['cgx_filter1']);
$date_f = $_REQUEST['date_f'];
$date_t = $_REQUEST['date_t'];
$item_number = $_REQUEST['item_number'];
$document_no = $_REQUEST['document_no'];
$customer = $_REQUEST['customer'];
//$cgx_search = $_REQUEST['q'];
$tgl_f = $_REQUEST['tgl_f'];
$tgl_t = $_REQUEST['tgl_t'];

$tgl_param = "";
if ($tgl_f) $tgl_param .= " AND production_date >= '" . npl_dmy2ymd($tgl_f) . "'";
if ($tgl_t) $tgl_param .= " AND production_date <= '" . npl_dmy2ymd($tgl_t) . "'";

$cgx_sql = "SELECT m_wo_slit.*, mp1.m_product_id as id_raw, mp1.product_code as code_raw, mp1.product_name as nama_raw,  
    m_wo_slit_line.*, mp2.m_product_id as id_fg, mp2.product_code as code_fg, mp2.product_name as nama_fg, (m_wo_slit.quantity * order_quantity) as order_total,  
    COALESCE(qty_production,0) qty_production, ((m_wo_slit.quantity * order_quantity) - COALESCE(qty_production,0)) as qty_outstanding, 
    cu.user_name as user_fullname, uu.user_name user_fullname_u, m_wo_slit.create_date, m_wo_slit.update_date
    FROM m_wo_slit 
    JOIN m_product mp1 ON (m_wo_slit.m_product_id=mp1.m_product_id)
    JOIN m_wo_slit_line ON(m_wo_slit.m_wo_slit_id=m_wo_slit_line.m_wo_slit_id)
    JOIN m_product mp2 ON (m_wo_slit_line.m_product_id=mp2.m_product_id)
    LEFT JOIN (SELECT m_prod_slit_line.m_wo_slit_line_id, sum(good) qty_production
        FROM m_prod_slit
        JOIN m_prod_slit_line ON(m_prod_slit.m_prod_slit_id=m_prod_slit_line.m_prod_slit_id)
        JOIN m_wo_slit_line ON (m_prod_slit_line.m_wo_slit_line_id = m_wo_slit_line.m_wo_slit_line_id)
        WHERE m_prod_slit.production_type = '1' ". $tgl_param ." GROUP BY m_wo_slit_line_id) col
    ON(m_wo_slit_line.m_wo_slit_line_id=col.m_wo_slit_line_id)
    LEFT JOIN app_user cu ON (m_wo_slit.create_user = cu.user_id)
    LEFT JOIN app_user uu ON (m_wo_slit.update_user = uu.user_id)                 
    WHERE 1 = 1 ";
$cgx_sql .= " AND " . org_filter_trx('m_wo_slit.app_org_id');

if ($document_no) $cgx_sql .= " AND m_wo_slit.document_no LIKE '%" . mysql_escape_string($document_no) . "%'";
if ($item_number) $cgx_sql .= " AND (mp2.product_code LIKE '%" . mysql_escape_string($item_number) . "%' OR mp2.product_name LIKE '%" . mysql_escape_string($item_number) . "%' OR mp2.description LIKE '%" . mysql_escape_string($item_number) . "%')";
if ($date_f) $cgx_sql .= " AND m_wo_slit.order_date >= '" . npl_dmy2ymd($date_f) . "'";
if ($date_t) $cgx_sql .= " AND m_wo_slit.order_date <= '" . npl_dmy2ymd($date_t) . "'";
if ($customer) $cgx_sql .= " AND m_wo_slit.partner LIKE '%" . mysql_escape_string($customer) . "%'";
// $cgx_sql .= " AND ( auc.user_name LIKE '%{$cgx_search}%' OR auc.user_fullname LIKE '%{$cgx_search}%')";
if (strlen($cgx_filter1) > 0){
    switch ($cgx_filter1) {
        case 'O':
            $cgx_sql .= " AND ((m_wo_slit.quantity * order_quantity) - COALESCE(qty_production,0)) > 0";            
            $cgx_sqltotal .= " AND ((m_wo_slit.quantity * order_quantity) - COALESCE(qty_production,0)) > 0";            
            break;
    }
}

if ($_REQUEST['mode'] == 'export-all') {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=\"work-order-slitting-" . date("Y-m-d") . ".csv\"");
    echo "\"NO. WO\"";
    echo ",\"TANGGAL WO\"";
    echo ",\"NAMA CUSTOMER\"";
    echo ",\"ITEM NUMBER\"";
    echo ",\"SPEC\"";
    echo ",\"THICKNESS\"";
    echo ",\"WIDTH\"";
    echo ",\"QTY WO\"";
    echo ",\"QTY REALISASI\"";
    echo ",\"QTY OUTSTANDING\"";
    echo "\n";
    
    $cgx_rs_export = mysql_query($cgx_sql, $cgx_connection);
    while (($cgx_dt_export = mysql_fetch_array($cgx_rs_export, MYSQL_ASSOC)) !== FALSE) {
        echo "\"" . str_replace("\"", "\"\"", $cgx_dt_export['document_no']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['order_date']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['partner']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['code_fg']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['spec']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['thickness']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['width']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['order_total']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['qty_production']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['qty_outstanding']) . "\"";
        echo "\n";
    }
    mysql_free_result($cgx_rs_export);
    exit;
} elseif ($_REQUEST['mode'] == 'delete') {
    $cgx_sql = "DELETE FROM  ";
    $cgx_sql .= " WHERE";
}

if (@mysql_query($cgx_sql, $cgx_connection)) {
    $_SESSION[$GLOBALS['APP_ID']]['view.wo_slit']['error'] = FALSE;
    $_SESSION[$GLOBALS['APP_ID']]['view.wo_slit']['info'] = 'Data anda sudah berhasil diperbarui';
    if ($_REQUEST['mode'] == 'new') $cgx_new_id = $_REQUEST['data']['Array'];
} else {
    $_SESSION[$GLOBALS['APP_ID']]['view.wo_slit']['error'] = mysql_error($cgx_connection);
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