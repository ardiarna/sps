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
$cgx_sql = "SELECT m_inout.*, c_order.document_no so, partner_code, mid(partner_name,1,20) partner_name, order_date, remark, m_inout_line_id, m_inout_line.m_product_id, 
    m_inout_line.c_order_line_id, m_inout_line.m_warehouse_id, m_inout_line.quantity, product_code, product_name, spec, thickness, od , 
    warehouse_name, m_coil_id, m_coil.no_coil, m_coil.no_lot, m_coil.weight, auc.user_fullname, auu.user_fullname user_fullname_u
    FROM m_inout
    JOIN c_order ON(m_inout.c_order_id = c_order.c_order_id)
    JOIN c_bpartner ON(c_order.c_bpartner_id = c_bpartner.c_bpartner_id)
    JOIN m_inout_line ON(m_inout.m_inout_id = m_inout_line.m_inout_id)
    JOIN m_product ON(m_inout_line.m_product_id = m_product.m_product_id)
    JOIN m_warehouse ON(m_inout_line.m_warehouse_id=m_warehouse.m_warehouse_id)
    JOIN m_coil ON(m_inout_line.m_inout_line_id=m_coil.m_in_id)
    LEFT JOIN app_user auc ON (m_inout.create_user=auc.user_id) 
    LEFT JOIN app_user auu ON (m_inout.update_user=auu.user_id) 
    WHERE m_inout.m_transaction_type_id = 12";
$cgx_sql .= " AND " . org_filter_trx('m_inout.app_org_id');
          
$date_f = $_REQUEST['date_f'];
$date_t = $_REQUEST['date_t'];
$item_number = $_REQUEST['item_number'];
$document_no = $_REQUEST['document_no'];
$remark = $_REQUEST['remark'];
$supplier = $_REQUEST['supplier'];
$no_coil = $_REQUEST['no_coil'];
$no_lot = $_REQUEST['no_lot'];

if ($document_no) $cgx_sql .= " AND m_inout.document_no LIKE '%" . mysql_escape_string($document_no) . "%'";
if ($remark) $cgx_sql .= " AND c_order.remark LIKE '%" . mysql_escape_string($remark) . "%'";
if ($item_number) $cgx_sql .= " AND (m_product.product_code LIKE '%" . mysql_escape_string($item_number) . "%' OR m_product.product_name LIKE '%" . mysql_escape_string($item_number) . "%' OR m_product.description LIKE '%" . mysql_escape_string($item_number) . "%' OR m_product.description_2 LIKE '%" . mysql_escape_string($item_number) . "%')";
if ($date_f) $cgx_sql .= " AND m_inout.m_inout_date >= '" . npl_dmy2ymd($date_f) . "'";
if ($date_t) $cgx_sql .= " AND m_inout.m_inout_date <= '" . npl_dmy2ymd($date_t) . "'";
if ($supplier) $cgx_sql .= " AND c_bpartner.partner_name LIKE '%" . mysql_escape_string($supplier) . "%'";
if ($no_coil) $cgx_sql .= " AND m_coil.no_coil LIKE '%" . mysql_escape_string($no_coil) . "%'";
if ($no_lot) $cgx_sql .= " AND m_coil.no_lot LIKE '%" . mysql_escape_string($no_lot) . "%'";

if ($_REQUEST['mode'] == 'export-all') {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=\"penerimaan_bahan_baku-" . date("Y-m-d") . ".csv\"");
    echo "\"NO. RECEIPT\"";
    echo ",\"TANGGAL MASUK\"";
    echo ",\"NO. CONTRACT\"";
    echo ",\"NAMA SUPPLIER\"";
    echo ",\"NOMOR SURAT JALAN\"";
    echo ",\"TANGGAL SURAT JALAN\"";
    echo ",\"ITEM NUMBER\"";
    echo ",\"SPEC\"";
    echo ",\"THICKNESS\"";
    echo ",\"WIDTH\"";
    echo ",\"NO. COIL\"";
    echo ",\"KODE COIL\"";
    echo ",\"BERAT (KG)\"";
    echo ",\"QUANTITY (PCS)\"";
    echo "\n";
    
    $cgx_rs_export = mysql_query($cgx_sql, $cgx_connection);
    while (($cgx_dt_export = mysql_fetch_array($cgx_rs_export, MYSQL_ASSOC)) !== FALSE) {
        echo "\"" . str_replace("\"", "\"\"", $cgx_dt_export['document_no']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['m_inout_date']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['remark']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['partner_name']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['no_kendaraan']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['sj_date']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['product_code']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['spec']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['thickness']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['od']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['no_coil']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['no_lot']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['weight']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['quantity']) . "\"";
        echo "\n";
    }
    mysql_free_result($cgx_rs_export);
    exit;
}

if (@mysql_query($cgx_sql, $cgx_connection)) {
    $_SESSION[$GLOBALS['APP_ID']]['view.good_rcp']['error'] = FALSE;
    $_SESSION[$GLOBALS['APP_ID']]['view.good_rcp']['info'] = 'Data anda sudah berhasil diperbarui';
    if ($_REQUEST['mode'] == 'new') $cgx_new_id = $_REQUEST['data']['Array'];
} else {
    $_SESSION[$GLOBALS['APP_ID']]['view.good_rcp']['error'] = mysql_error($cgx_connection);
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