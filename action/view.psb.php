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

$cgx_sql = "SELECT * FROM m_stock_warehouse_2
            JOIN m_product USING (m_product_id)
            JOIN m_warehouse ON m_stock_warehouse_2.m_warehouse_id = m_warehouse.m_warehouse_id 
            LEFT JOIN c_bpartner ON(m_product.c_bpartner_id = c_bpartner.c_bpartner_id)
            WHERE latest = 'Y' ";
$cgx_sql .= " AND " . org_filter_trx("m_stock_warehouse_2.app_org_id");

$f1 = $_REQUEST['f1'];
$q = $_REQUEST['q'];

if ($f1) $cgx_sql .= " AND m_stock_warehouse_2.m_warehouse_id = '" . mysql_escape_string($f1) . "'";
if ($q) $cgx_sql .= " and ( m_product_id LIKE '%{$q}%' OR product_code LIKE '%{$q}%'
        OR product_name LIKE '%{$q}%' OR partner_name LIKE '%{$q}%'
        OR od LIKE '%{$q}%' OR spec LIKE '%{$q}%' OR thickness LIKE '%{$q}%' OR length LIKE'%{$q}%')";
        
        
if ($_REQUEST['mode'] == 'export-all') {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=\"view.psb-" . date("Y-m-d") . ".csv\"");
    echo "\"ID\",\"Warehouse\",\"Item Number\",\"Spec\",\"OD\",\"Tebal\",\"Panjang\",\"Balance Quantity\"\n";
    $cgx_rs_export = mysql_query($cgx_sql, $cgx_connection);
    //print_r($cgx_sql);
    //exit;
    while (($cgx_dt_export = mysql_fetch_array($cgx_rs_export, MYSQL_ASSOC)) !== FALSE) {
        echo "\"" . str_replace("\"", "\"\"", $cgx_dt_export['m_product_id']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['warehouse_name']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['product_code']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['spec']) . "\"";;
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['od']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['thickness']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['length']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['balance_quantity']) . "\"";
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
    $_SESSION[$GLOBALS['APP_ID']]['view.psb']['error'] = FALSE;
    $_SESSION[$GLOBALS['APP_ID']]['view.psb']['info'] = 'Data anda sudah berhasil diperbarui';
    if ($_REQUEST['mode'] == 'new') $cgx_new_id = $_REQUEST['data']['Array'];
} else {
    $_SESSION[$GLOBALS['APP_ID']]['view.psb']['error'] = mysql_error($cgx_connection);
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