<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 12/01/2014 23:59:15
 */

require_once '../init.php';

if (!authenticated()) {
    header("Location: ../index.php");
    exit;
}

if ($_REQUEST['mode'] == 'export-all') {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=\"Kartu Stock -" . date("Y-m-d") . ".csv\"");
    //echo "\"Product ID\",\"Item Number\",\"Nama Barang\",\"Tanggal\",\"Stok Awal\",\"Masuk\",\"Keluar\",\"Balance\"\n";
    echo "\"Spec\",\"OD\",\"Tebal\",\"Panjang\",\"Kode Barang\",\"Nama Barang\",\"Deskripsi\",\"Stock Awal\",\"Masuk\",\"Keluar\",\"Balance\",\"Customer\"\n";
    $cgx_rs_export = mysql_query("SELECT *, C.partner_name FROM m_stock_balance_d_2 JOIN m_product ON (m_stock_balance_d_2.m_product_id = m_product.m_product_id) LEFT JOIN c_bpartner AS C ON (m_product.c_bpartner_id = C.c_bpartner_id) WHERE m_stock_balance_d_2.latest = 'Y'  AND m_stock_balance_d_2.app_org_id = " . org(), $cgx_connection);
    while (($cgx_dt_export = mysql_fetch_array($cgx_rs_export, MYSQL_ASSOC)) !== FALSE) {
        echo "\"" . str_replace("\"", "\"\"", $cgx_dt_export['spec']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['od']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['thickness']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['length']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['product_code']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['product_name']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['description']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['prev_quantity']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['in_quantity']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['out_quantity']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['balance_quantity']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['partner_name']) . "\"";
        echo "\n";
    }
    mysql_free_result($cgx_rs_export);
    exit;
} elseif ($_REQUEST['mode'] == 'export-all-d') {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=\"view.sb-histori-" . date("Y-m-d") . ".csv\"");
    echo "\"Item Number\",\"Nama Barang\",\"Tanggal\",\"Stok Awal\",\"Masuk\",\"Keluar\",\"Balance\"\n";
    $cgx_rs_export = mysql_query("SELECT *
                FROM m_stock_balance_2
                JOIN m_product ON ( m_stock_balance_2.m_product_id = m_product.m_product_id )
                WHERE m_stock_balance_2.m_product_id ='{$_REQUEST['pc']}' AND m_stock_balance_2.app_org_id = '". org() ."' 
                ORDER BY m_stock_balance_2.m_stock_balance_id", $cgx_connection);
    while (($cgx_dt_export = mysql_fetch_array($cgx_rs_export, MYSQL_ASSOC)) !== FALSE) {
        echo "\"" . str_replace("\"", "\"\"", $cgx_dt_export['product_code']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['product_name']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['balance_date']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['prev_quantity']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['in_quantity']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['out_quantity']) . "\"";
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
    $_SESSION[$GLOBALS['APP_ID']]['view.sb']['error'] = FALSE;
    $_SESSION[$GLOBALS['APP_ID']]['view.sb']['info'] = 'Data anda sudah berhasil diperbarui';
    if ($_REQUEST['mode'] == 'new') $cgx_new_id = $_REQUEST['data']['Array'];
} else {
    $_SESSION[$GLOBALS['APP_ID']]['view.sb']['error'] = mysql_error($cgx_connection);
}

if ($_REQUEST['mode'] == 'update') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']) . "&");
} elseif ($_REQUEST['mode'] == 'new') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']) . "&");
} elseif ($_REQUEST['mode'] == 'delete') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']));
}
exit;

?>