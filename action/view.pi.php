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

$cgx_sql = "SELECT mi.document_no, mi.m_inout_date, mp.product_code, mp.product_name, mp.spec, mp.od, mp.thickness, mp.length, mw.warehouse_name, cpl.qty_oht, cpl.qty_oh, cpl.qty_count, cpl.qty_adj
FROM c_pinventory cp JOIN m_inout mi USING(m_inout_id)
JOIN c_pinventory_line cpl USING(c_pinventory_id) 
JOIN m_product mp ON(cpl.m_product_id=mp.m_product_id) 
JOIN m_warehouse mw ON(cpl.m_warehouse_id=mw.m_warehouse_id) 
WHERE mi.m_transaction_type_id = 10 AND ". org_filter_trx('cp.app_org_id');

$gudang = $_REQUEST['gudang'];
$date_f = $_REQUEST['date_f'];
$date_t = $_REQUEST['date_t'];
$item_number = $_REQUEST['item_number'];
$document_no = $_REQUEST['document_no'];

if ($gudang) $cgx_sql .= " AND cpl.m_warehouse_id = '" . mysql_escape_string($gudang) . "'";
if ($document_no) $cgx_sql .= " AND mi.document_no LIKE '%" . mysql_escape_string($document_no) . "%'";
if ($item_number) $cgx_sql .= " AND mp.product_code LIKE '%" . mysql_escape_string($item_number) . "%'";
if ($date_f) $cgx_sql .= " AND mi.m_inout_date >= '" . npl_dmy2ymd($date_f) . "'";
if ($date_t) $cgx_sql .= " AND mi.m_inout_date <= '" . npl_dmy2ymd($date_t) . "'";

if ($_REQUEST['mode'] == 'export-all') {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=\"view.pi-" . date("Y-m-d") . ".csv\"");
    echo "\"Nomor Dokumen\",\"Tgl Physical\",\"Item Number\",\"Nama Barang\",\"Spec\",\"OD\",\"Tebal\",\"Panjang\",\"Gudang\",\"Jumlah On Hand Total\",\"Jumlah On Hand Gudang\",\"Jumlah Hitung\",\"Adjusment\"\n";
    $cgx_rs_export = mysql_query($cgx_sql, $cgx_connection);
    while (($cgx_dt_export = mysql_fetch_array($cgx_rs_export, MYSQL_ASSOC)) !== FALSE) {
        echo "\"" . str_replace("\"", "\"\"", $cgx_dt_export['document_no']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['m_inout_date']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['product_code']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['product_name']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['spec']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['od']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['thickness']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['length']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['warehouse_name']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['qty_oht']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['qty_oh']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['qty_count']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['qty_adj']) . "\"";
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
    $_SESSION[$GLOBALS['APP_ID']]['view.pi']['error'] = FALSE;
    $_SESSION[$GLOBALS['APP_ID']]['view.pi']['info'] = 'Data anda sudah berhasil diperbarui';
    if ($_REQUEST['mode'] == 'new') $cgx_new_id = $_REQUEST['data']['Array'];
} else {
    $_SESSION[$GLOBALS['APP_ID']]['view.pi']['error'] = mysql_error($cgx_connection);
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