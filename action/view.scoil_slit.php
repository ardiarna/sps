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

$cgx_sql = "SELECT m_coil_slit_id, m_coil_slit.m_product_id, m_coil_slit.m_coil_id, m_coil_slit.weight, m_coil_slit.quantity,
            (m_coil_slit.weight * m_coil_slit.quantity) AS weight_total,
            no_coil, no_lot, product_code, spec, od, thickness, length 
            FROM m_coil_slit
            JOIN m_product ON (m_coil_slit.m_product_id=m_product.m_product_id) 
            JOIN m_coil ON(m_coil_slit.m_coil_id=m_coil.m_coil_id) 
            WHERE 1 = 1";

$title_header = $_REQUEST['title_header'];
$spec = urldecode($_REQUEST['spec']);
$od = urldecode($_REQUEST['od']);
$thickness = urldecode($_REQUEST['thickness']);
$length = urldecode($_REQUEST['length']);
$cgx_search = $_REQUEST['cgx_search'];

$cgx_sql .= " AND ( product_code LIKE '%{$cgx_search}%' OR no_coil LIKE '%{$cgx_search}%' OR no_lot LIKE '%{$cgx_search}%')";
if (strlen($spec) > 0) $cgx_sql .= " AND m_product.spec = '" . mysql_escape_string($spec) . "'";
if (strlen($od) > 0) $cgx_sql .= " AND m_product.od = '" . mysql_escape_string($od) . "'";
if (strlen($thickness) > 0) $cgx_sql .= " AND m_product.thickness = '" . mysql_escape_string($thickness) . "'";
if (strlen($length) > 0) $cgx_sql .= " AND m_product.length = '" . mysql_escape_string($length) . "'";

if ($_REQUEST['mode'] == 'export-all') {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=\"".$title_header."-" . date("Y-m-d") . ".csv\"");
    echo "\"Item Number\",\"Spec\",\"OD\",\"Thickness\",\"Width\",\"No Coil\",\"Kode Coil\",\"Jumlah Slit\",\"Berat per Slit\",\"Berat Total\"\n";
    $cgx_rs_export = mysql_query($cgx_sql, $cgx_connection);
    while (($cgx_dt_export = mysql_fetch_array($cgx_rs_export, MYSQL_ASSOC)) !== FALSE) {
        echo "\"" . str_replace("\"", "\"\"", $cgx_dt_export['product_code']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['spec']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['length']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['thickness']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['od']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['no_coil']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['no_lot']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['quantity']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['weight']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['weight_total']) . "\"";
        echo "\n";
    }
    mysql_free_result($cgx_rs_export);
    exit;
}

?>