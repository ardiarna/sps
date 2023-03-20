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

$cgx_sql = "SELECT c_spk.* , c_spk_line.c_spk_line_id, c_spk_line.quantity, c_wo.document_no wo, machine_name, mp.product_code, mp.product_name, mp.spec, mp.od, mp.thickness, mp.length,
mat.length lengthm, (c_spk_line.quantity / m_material_requirement.multipleqty) quantity_mat, c_order.document_no so, reference_no, remark, partner_name, c_wo_line.m_product_id
FROM c_spk 
JOIN c_spk_line ON(c_spk.c_spk_id=c_spk_line.c_spk_id)
JOIN c_wo ON(c_spk.c_wo_id=c_wo.c_wo_id)
JOIN m_machine ON(c_spk.m_machine_id=m_machine.m_machine_id)
JOIN c_wo_line ON(c_spk_line.c_wo_line_id=c_wo_line.c_wo_line_id)
JOIN c_production_plan ON(c_wo_line.c_production_plan_id=c_production_plan.c_production_plan_id)
JOIN c_delivery_plan ON (c_production_plan.plan_ref = c_delivery_plan.c_delivery_plan_id)
JOIN c_order_line ON (c_delivery_plan.order_line_ref = c_order_line.c_order_line_id)
JOIN c_order USING (c_order_id)
LEFT JOIN c_bpartner ON (c_order.c_bpartner_id = c_bpartner.c_bpartner_id)
JOIN m_product mp ON(c_wo_line.m_product_id=mp.m_product_id)
LEFT JOIN m_material_requirement ON (c_wo_line.m_product_id=m_material_requirement.m_product_fg)
LEFT JOIN m_product mat ON (m_material_requirement.m_product_material=mat.m_product_id)
WHERE 1=1 AND " . org_filter_trx('c_production_plan.app_org_id');

$mesin = $_REQUEST['mesin'];
$date_f = $_REQUEST['date_f'];
$date_t = $_REQUEST['date_t'];
$item_number = $_REQUEST['item_number'];
$document_no = $_REQUEST['document_no'];

if ($mesin) $cgx_sql .= " AND c_spk.m_machine_id = '" . mysql_escape_string($mesin) . "'";
if ($document_no) $cgx_sql .= " AND c_spk.document_no LIKE '%" . mysql_escape_string($document_no) . "%'";
if ($item_number) $cgx_sql .= " AND mp.product_code LIKE '%" . mysql_escape_string($item_number) . "%'";
if ($date_f) $cgx_sql .= " AND spk_date >= '" . npl_dmy2ymd($date_f) . "'";
if ($date_t) $cgx_sql .= " AND spk_date <= '" . npl_dmy2ymd($date_t) . "'";

if ($_REQUEST['mode'] == 'export-all') {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=\"view.spk-" . date("Y-m-d") . ".csv\"");
    echo "\"NO. SPK\"";
    echo ",\"TANGGAL\"";
    echo ",\"NO. W/O\"";
    echo ",\"NO. SC\"";
    echo ",\"CUSTOMER\"";
    echo ",\"MESIN\"";
    echo ",\"ITEM NUMBER\"";
    echo ",\"SPEC\"";
    echo ",\"OD\"";
    echo ",\"THICKNESS\"";
    echo ",\"LENGTH\"";
    echo ",\"LENGTH LONGPIPE\"";
    echo ",\"LONGPIPE REQ\"";
    echo ",\"QUANTITY RECUTTING\"";
    echo "\n";
    $cgx_rs_export = mysql_query($cgx_sql, $cgx_connection);
    while (($cgx_dt_export = mysql_fetch_array($cgx_rs_export, MYSQL_ASSOC)) !== FALSE) {
        echo "\"" . str_replace("\"", "\"\"", $cgx_dt_export['document_no']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['spk_date']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['wo']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['so']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['partner_name']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['machine_name']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['product_code']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['spec']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['od']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['thickness']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['length']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['lengthm']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['quantity_mat']) . "\"";
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
    $_SESSION[$GLOBALS['APP_ID']]['view.spk']['error'] = FALSE;
    $_SESSION[$GLOBALS['APP_ID']]['view.spk']['info'] = 'Data anda sudah berhasil diperbarui';
    if ($_REQUEST['mode'] == 'new') $cgx_new_id = $_REQUEST['data']['Array'];
} else {
    $_SESSION[$GLOBALS['APP_ID']]['view.spk']['error'] = mysql_error($cgx_connection);
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