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

$cgx_sql = "SELECT m_work_order.document_no, m_work_order.order_date,
c_order.document_no so_number, c_order.order_date s_order_date, COALESCE(remark,c_forecast.document_no) remark, reference_no, machine_name, proces_name, 
COALESCE(c_bpartner.partner_code,cb2.partner_code) partner_code, COALESCE(c_bpartner.partner_name,cb2.partner_name) partner_name, rec.product_code, rec.product_name, rec.spec, rec.od, rec.thickness, rec.length,
mat.product_code product_codem, mat.product_name product_namem, mat.od odm, mat.thickness thicknessm, mat.length lengthm,
m_work_order_line.*, order_qty_so, m_work_order.create_date, m_work_order.update_date, auc.user_fullname, auu.user_fullname user_fullname_u
FROM m_work_order
JOIN m_machine USING (m_machine_id)
JOIN c_proces USING (c_proces_id)
JOIN m_work_order_line USING (m_work_order_id)
LEFT JOIN c_order USING (c_order_id)
LEFT JOIN c_bpartner USING (c_bpartner_id)
LEFT JOIN c_forecast ON (m_work_order_line.c_forecast_id=c_forecast.c_forecast_id)
LEFT JOIN c_bpartner cb2 ON (c_forecast.c_bpartner_id=cb2.c_bpartner_id)
LEFT JOIN  (SELECT c_order_id, m_product_id, sum(order_quantity) order_qty_so FROM c_order_line GROUP BY c_order_id, m_product_id) col 
ON(m_work_order_line.c_order_id=col.c_order_id AND m_work_order_line.m_product_id=col.m_product_id)
JOIN m_product rec ON (m_work_order_line.m_product_id=rec.m_product_id)
JOIN m_product mat ON (m_work_order_line.m_product_material=mat.m_product_id)
LEFT JOIN app_user auc ON (m_work_order.create_user=auc.user_id)
LEFT JOIN app_user auu ON (m_work_order.update_user=auu.user_id) WHERE m_work_order.type_id = 'W' AND " . org_filter_trx('m_work_order.app_org_id');

$mesin = $_REQUEST['mesin'];
$date_f = $_REQUEST['date_f'];
$date_t = $_REQUEST['date_t'];
$item_number = $_REQUEST['item_number'];
$document_no = $_REQUEST['document_no'];
$sc_number = $_REQUEST['sc_number'];
$customer = $_REQUEST['customer'];

if ($mesin) $cgx_sql .= " AND m_work_order.m_machine_id = '" . mysql_escape_string($mesin) . "'";
if ($document_no) $cgx_sql .= " AND m_work_order.document_no LIKE '%" . mysql_escape_string($document_no) . "%'";
if ($item_number) $cgx_sql .= " AND rec.product_code LIKE '%" . mysql_escape_string($item_number) . "%'";
if ($date_f) $cgx_sql .= " AND m_work_order.order_date >= '" . npl_dmy2ymd($date_f) . "'";
if ($date_t) $cgx_sql .= " AND m_work_order.order_date <= '" . npl_dmy2ymd($date_t) . "'";
if ($sc_number) $cgx_sql .= " AND c_order.remark LIKE '%" . mysql_escape_string($sc_number) . "%'";
if ($customer) $cgx_sql .= " AND (c_bpartner.partner_name LIKE '%" . mysql_escape_string($customer) . "%' OR cb2.partner_name LIKE '%" . mysql_escape_string($customer) . "%')";

if ($_REQUEST['mode'] == 'export-all') {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=\"work-order-detail-" . date("Y-m-d") . ".csv\"");
    echo "\"NO. WO\"";
    echo ",\"TANGGAL WO\"";
    echo ",\"NO. SC\"";
    echo ",\"REMARK / FORECAST\"";
    echo ",\"PO NUMBER\"";
    echo ",\"TANGGAL ORDER\"";
    echo ",\"CUSTOMER\"";
    echo ",\"QTY ORDER\"";
    echo ",\"MACHINE\"";
    echo ",\"ITEM NUMBER\"";
    echo ",\"SPEC\"";
    echo ",\"OD\"";
    echo ",\"THICKNESS\"";
    echo ",\"LENGTH\"";
    echo ",\"QTY WO\"";
    echo ",\"QTY CUTTING\"";
    echo ",\"QTY CHAMPER\"";
    echo ",\"QTY SIKAT\"";
    echo ",\"QTY POLESING\"";
    echo ",\"QTY BENDING\"";
    echo ",\"QTY QUENCING\"";
    echo ",\"QTY PACKING\"";
    echo "\n";
    $cgx_rs_export = mysql_query($cgx_sql, $cgx_connection);
    while (($cgx_dt_export = mysql_fetch_array($cgx_rs_export, MYSQL_ASSOC)) !== FALSE) {
        echo "\"" . str_replace("\"", "\"\"", $cgx_dt_export['document_no']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['order_date']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['so_number']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['remark']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['reference_no']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['s_order_date']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['partner_name']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['order_qty_so']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['machine_name']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['product_code']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['spec']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['od']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['thickness']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['length']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['order_quantity']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['producted_quantity']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['ch_quantity']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['sk_quantity']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['pl_quantity']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['bd_quantity']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['qc_quantity']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['pc_quantity']) . "\"";
        echo "\n";
    }
    mysql_free_result($cgx_rs_export);
    exit;
} elseif ($_REQUEST['mode'] == 'delete') {
    $cgx_sql = "DELETE FROM  ";
    $cgx_sql .= " WHERE";
}

if (@mysql_query($cgx_sql, $cgx_connection)) {
    $_SESSION[$GLOBALS['APP_ID']]['view.wo']['error'] = FALSE;
    $_SESSION[$GLOBALS['APP_ID']]['view.wo']['info'] = 'Data anda sudah berhasil diperbarui';
    if ($_REQUEST['mode'] == 'new') $cgx_new_id = $_REQUEST['data']['Array'];
} else {
    $_SESSION[$GLOBALS['APP_ID']]['view.wo']['error'] = mysql_error($cgx_connection);
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