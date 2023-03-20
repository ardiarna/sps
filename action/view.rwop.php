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

$cgx_sql = "SELECT m_prod_slit.*, m_wo_pipa.document_no wo, remark,
            mp1.m_product_id as id_slit, 
            mp1.product_code as code_slit, 
            mp1.product_name as nama_slit, m_wo_pipa_line.*, 
            c_bpartner.partner_code, c_bpartner.partner_name,
            mp2.m_product_id as id_fg, 
            mp2.product_code as code_fg, 
            mp2.product_name as nama_fg,
            mp2.spec,
            mp2.od,
            mp2.thickness,
            mp2.length,
            auc.user_fullname, 
            auu.user_fullname user_fullname_u,
            m_prod_slit_line.good, m_prod_slit_line.weight,
            m_warehouse.warehouse_name

            FROM m_prod_slit 
            
            JOIN m_prod_slit_line ON (m_prod_slit.m_prod_slit_id = m_prod_slit_line.m_prod_slit_id)
            JOIN m_wo_pipa ON (m_prod_slit.m_wo_slit_id = m_wo_pipa.m_wo_pipa_id)
            
            JOIN m_wo_pipa_line ON (m_prod_slit_line.m_wo_slit_line_id = m_wo_pipa_line.m_wo_pipa_line_id)
            JOIN m_warehouse ON (m_prod_slit_line.m_warehouse_id = m_warehouse.m_warehouse_id)
            JOIN m_product mp1 ON (m_wo_pipa.m_product_id = mp1.m_product_id)
            JOIN m_product mp2 ON (m_wo_pipa_line.m_product_id = mp2.m_product_id)
            JOIN c_order ON (m_wo_pipa_line.c_order_id = c_order.c_order_id) 
            JOIN c_bpartner ON (c_order.c_bpartner_id = c_bpartner.c_bpartner_id)
            LEFT JOIN app_user auc ON (m_prod_slit.create_user=auc.user_id) 
            LEFT JOIN app_user auu ON (m_prod_slit.update_user=auu.user_id) 
            
            WHERE m_prod_slit.production_type = 2";
$cgx_sql .= " AND " . org_filter_trx('m_wo_pipa.app_org_id');
            
$date_f = $_REQUEST['date_f'];
$date_t = $_REQUEST['date_t'];
$item_number = $_REQUEST['item_number'];
$document_no = $_REQUEST['document_no'];
$customer = $_REQUEST['customer'];

if ($document_no) $cgx_sql .= " AND m_prod_slit.document_no LIKE '%" . mysql_escape_string($document_no) . "%'";
if ($item_number) $cgx_sql .= " AND (mp2.product_code LIKE '%" . mysql_escape_string($item_number) . "%' OR mp2.product_name LIKE '%" . mysql_escape_string($item_number) . "%' OR mp2.description LIKE '%" . mysql_escape_string($item_number) . "%')";
if ($date_f) $cgx_sql .= " AND m_prod_slit.production_date >= '" . npl_dmy2ymd($date_f) . "'";
if ($date_t) $cgx_sql .= " AND m_prod_slit.production_date <= '" . npl_dmy2ymd($date_t) . "'";
if ($customer) $cgx_sql .= " AND c_bpartner.partner_name LIKE '%" . mysql_escape_string($customer) . "%'";


if ($_REQUEST['mode'] == 'export-all') {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=\"realisasi-work-order-slitting-" . date("Y-m-d") . ".csv\"");
    echo "\"NO. PRODUKSI\"";
    echo ",\"TANGGAL PRODUKSI\"";
    echo ",\"REMARK\"";
    echo ",\"NAMA CUSTOMER\"";
    //echo ",\"PRODUCT PIPA\"";
    echo ",\"ITEM NUMBER\"";
    echo ",\"SPEC\"";
    echo ",\"OD\"";
    echo ",\"THICKNESS\"";
    echo ",\"LENGTH\"";
    //echo ",\"GUDANG\"";
    echo ",\"QTY (PCS)\"";
    echo ",\"WEIGHT (KG)\"";
    echo "\n";
    
    $cgx_rs_export = mysql_query($cgx_sql, $cgx_connection);
    while (($cgx_dt_export = mysql_fetch_array($cgx_rs_export, MYSQL_ASSOC)) !== FALSE) {
        echo "\"" . str_replace("\"", "\"\"", $cgx_dt_export['document_no']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['production_date']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['remark']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['partner_name']) . "\"";
        //echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['nama_fg']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['code_fg']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['spec']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['od']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['thickness']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['length']) . "\"";
        //echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['warehouse_name']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['good']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['weight']) . "\"";
        echo "\n";
    }
    mysql_free_result($cgx_rs_export);
    exit;
} elseif ($_REQUEST['mode'] == 'delete') {
    $cgx_sql = "DELETE FROM  ";
    $cgx_sql .= " WHERE";
}

if (@mysql_query($cgx_sql, $cgx_connection)) {
    $_SESSION[$GLOBALS['APP_ID']]['view.wo_pipa']['error'] = FALSE;
    $_SESSION[$GLOBALS['APP_ID']]['view.wo_pipa']['info'] = 'Data anda sudah berhasil diperbarui';
    if ($_REQUEST['mode'] == 'new') $cgx_new_id = $_REQUEST['data']['Array'];
} else {
    $_SESSION[$GLOBALS['APP_ID']]['view.wo_pipa']['error'] = mysql_error($cgx_connection);
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