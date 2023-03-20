<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 16/01/2014 13:10:28
 */

require_once '../init.php';

if (!authenticated()) {
    header("Location: ../index.php");
    exit;
}

if ($_REQUEST['mode'] == 'update' || $_REQUEST['mode'] == 'new') if (!has_privilege('forecast')) die ('akses ditolak');
if ($_REQUEST['mode'] == 'delete') if (!has_privilege('forecast')) die ('akses ditolak');

if ($_REQUEST['mode'] == 'export-all') {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=\"forecast-" . date("Y-m-d") . ".csv\"");
    echo "\"ID\",\"Kode Customer\",\"Customer\",\"Item Number\",\"Nama Barang\",\"Periode\",\"Jumlah PCS\",\"Berat (Kg)\"\n";
    $cgx_rs_export = mysql_query("SELECT m_forecast.*, partner_code, partner_name, product_code, product_name FROM m_forecast JOIN c_bpartner ON (m_forecast.c_bpartner_id = c_bpartner.c_bpartner_id) JOIN m_product ON (m_forecast.m_product_id = m_product.m_product_id)", $cgx_connection);
    while (($cgx_dt_export = mysql_fetch_array($cgx_rs_export, MYSQL_ASSOC)) !== FALSE) {
        echo "\"" . str_replace("\"", "\"\"", $cgx_dt_export['m_forecast_id']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['partner_code']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['partner_name']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['product_code']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['product_name']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['period']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['qty']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['weight']) . "\"";
        echo "\n";
    }
    mysql_free_result($cgx_rs_export);
    exit;
} elseif ($_REQUEST['mode'] == 'update') {
    $cgx_sql = "UPDATE m_forecast SET";
    $cgx_sql .= " c_bpartner_id = '{$_REQUEST['data']['c_bpartner_id']}'";
    $cgx_sql .= ", m_product_id = '{$_REQUEST['data']['m_product_id']}'";
    $cgx_sql .= ", period = '" . cgx_dmy2ymd($_REQUEST['data']['period']) . "'";
    $cgx_sql .= ", qty = '{$_REQUEST['data']['qty']}'";
    $cgx_sql .= ", weight = '{$_REQUEST['data']['weight']}'";
    $cgx_sql .= " WHERE";
    $cgx_sql .= " m_forecast_id = '{$_REQUEST['pkey']['m_forecast_id']}'";
} elseif ($_REQUEST['mode'] == 'new') {
    $cgx_sql = "INSERT INTO m_forecast (";
    $cgx_sql .= "c_bpartner_id,m_product_id,period,qty,weight";
    $cgx_sql .= ") values (";
    $cgx_sql .= "'{$_REQUEST['data']['c_bpartner_id']}'";
    $cgx_sql .= ",'{$_REQUEST['data']['m_product_id']}'";
    $cgx_sql .= ",'" . cgx_dmy2ymd($_REQUEST['data']['period']) . "'";
    $cgx_sql .= ",'{$_REQUEST['data']['qty']}'";
    $cgx_sql .= ",'{$_REQUEST['data']['weight']}'";
    $cgx_sql .= ")";
} elseif ($_REQUEST['mode'] == 'delete') {
    $cgx_sql = "DELETE FROM m_forecast ";
    $cgx_sql .= " WHERE";
    $cgx_sql .= " m_forecast_id = '{$_REQUEST['pkey']['m_forecast_id']}'";
}

if (@mysql_query($cgx_sql, $cgx_connection)) {
    $_SESSION[$GLOBALS['APP_ID']]['forecast']['error'] = FALSE;
    $_SESSION[$GLOBALS['APP_ID']]['forecast']['info'] = 'Data anda sudah berhasil diperbarui';
    if ($_REQUEST['mode'] == 'new') $cgx_new_id = mysql_insert_id($cgx_connection);
} else {
    $_SESSION[$GLOBALS['APP_ID']]['forecast']['error'] = mysql_error($cgx_connection);
}

if ($_REQUEST['mode'] == 'update') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']) . "&pkey[m_forecast_id]={$_REQUEST['pkey']['m_forecast_id']}");
} elseif ($_REQUEST['mode'] == 'new') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']) . "&pkey[m_forecast_id]={$cgx_new_id}");
} elseif ($_REQUEST['mode'] == 'delete') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']));
}
exit;

?>