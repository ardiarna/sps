<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 07/01/2014 13:47:15
 */

require_once '../init.php';

if (!authenticated()) {
    header("Location: ../index.php");
    exit;
}

if ($_REQUEST['mode'] == 'update' || $_REQUEST['mode'] == 'new') if (!has_privilege('master.wh')) die ('akses ditolak');
if ($_REQUEST['mode'] == 'delete') if (!has_privilege('master.wh')) die ('akses ditolak');

if ($_REQUEST['mode'] == 'export-all') {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=\"master.warehouse-" . date("Y-m-d") . ".csv\"");
    echo "\"ID\",\"Organization\",\"Kode Gudang\",\"Nama Gudang\",\"Aktif\"\n";
    $cgx_rs_export = mysql_query("SELECT m_warehouse.*, organization FROM m_warehouse JOIN app_org USING (app_org_id)", $cgx_connection);
    while (($cgx_dt_export = mysql_fetch_array($cgx_rs_export, MYSQL_ASSOC)) !== FALSE) {
        echo "\"" . str_replace("\"", "\"\"", $cgx_dt_export['m_warehouse_id']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['organization']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['warehouse_code']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['warehouse_name']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['active']) . "\"";
        echo "\n";
    }
    mysql_free_result($cgx_rs_export);
    exit;
} elseif ($_REQUEST['mode'] == 'update') {
    $cgx_sql = "UPDATE m_warehouse SET";
    $cgx_sql .= " app_org_id = '{$_REQUEST['data']['app_org_id']}'";
    $cgx_sql .= ", warehouse_code = '" . mysql_escape_string($_REQUEST['data']['warehouse_code']) . "'";
    $cgx_sql .= ", warehouse_name = '" . mysql_escape_string($_REQUEST['data']['warehouse_name']) . "'";
    $cgx_sql .= ", active = '" . mysql_escape_string($_REQUEST['data']['active']) . "'";
    $cgx_sql .= " WHERE";
    $cgx_sql .= " m_warehouse_id = '{$_REQUEST['pkey']['m_warehouse_id']}'";
} elseif ($_REQUEST['mode'] == 'new') {
    $cgx_sql = "INSERT INTO m_warehouse (";
    $cgx_sql .= "app_org_id,warehouse_code,warehouse_name,active";
    $cgx_sql .= ") values (";
    $cgx_sql .= "'{$_REQUEST['data']['app_org_id']}'";
    $cgx_sql .= ",'" . mysql_escape_string($_REQUEST['data']['warehouse_code']) . "'";
    $cgx_sql .= ",'" . mysql_escape_string($_REQUEST['data']['warehouse_name']) . "'";
    $cgx_sql .= ",'" . mysql_escape_string($_REQUEST['data']['active']) . "'";
    $cgx_sql .= ")";
} elseif ($_REQUEST['mode'] == 'delete') {
    $cgx_sql = "DELETE FROM m_warehouse ";
    $cgx_sql .= " WHERE";
    $cgx_sql .= " m_warehouse_id = '{$_REQUEST['pkey']['m_warehouse_id']}'";
}

if (@mysql_query($cgx_sql, $cgx_connection)) {
    $_SESSION[$GLOBALS['APP_ID']]['master.warehouse']['error'] = FALSE;
    $_SESSION[$GLOBALS['APP_ID']]['master.warehouse']['info'] = 'Data anda sudah berhasil diperbarui';
    if ($_REQUEST['mode'] == 'new') $cgx_new_id = mysql_insert_id($cgx_connection);
} else {
    $_SESSION[$GLOBALS['APP_ID']]['master.warehouse']['error'] = mysql_error($cgx_connection);
}

if ($_REQUEST['mode'] == 'update') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']) . "&pkey[m_warehouse_id]={$_REQUEST['pkey']['m_warehouse_id']}");
} elseif ($_REQUEST['mode'] == 'new') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']) . "&pkey[m_warehouse_id]={$cgx_new_id}");
} elseif ($_REQUEST['mode'] == 'delete') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']));
}
exit;

?>