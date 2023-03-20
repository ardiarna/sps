<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 03/01/2014 20:23:24
 */

require_once '../init.php';

if (!authenticated()) {
    header("Location: ../index.php");
    exit;
}

if ($_REQUEST['mode'] == 'update' || $_REQUEST['mode'] == 'new') if (!has_privilege('master.mitra')) die ('akses ditolak');
if ($_REQUEST['mode'] == 'delete') if (!has_privilege('master.mitra')) die ('akses ditolak');

if ($_REQUEST['mode'] == 'export-all') {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=\"master.mitra-" . date("Y-m-d") . ".csv\"");
    echo "\"ID\",\"ORGANISASI\",\"KODE\",\"NAMA MITRA BISNIS\",\"VENDOR\",\"CUSTOMER\",\"EMPLOYEE\",\"AKTIF\"\n";
    $cgx_rs_export = mysql_query("SELECT c_bpartner.*, organization FROM c_bpartner JOIN app_org USING (app_org_id)", $cgx_connection);
    while (($cgx_dt_export = mysql_fetch_array($cgx_rs_export, MYSQL_ASSOC)) !== FALSE) {
        echo "\"" . str_replace("\"", "\"\"", $cgx_dt_export['c_bpartner_id']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['organization']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['partner_code']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['partner_name']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['vendor']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['customer']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['employee']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['active']) . "\"";
        echo "\n";
    }
    mysql_free_result($cgx_rs_export);
    exit;
} elseif ($_REQUEST['mode'] == 'update') {
    $cgx_sql = "UPDATE c_bpartner SET";
    $cgx_sql .= " app_org_id = '{$_REQUEST['data']['app_org_id']}'";
    $cgx_sql .= ", partner_code = '" . mysql_escape_string($_REQUEST['data']['partner_code']) . "'";
    $cgx_sql .= ", partner_name = '" . mysql_escape_string($_REQUEST['data']['partner_name']) . "'";
    $cgx_sql .= ", active = '" . mysql_escape_string($_REQUEST['data']['active']) . "'";
    $cgx_sql .= ", vendor = '" . mysql_escape_string($_REQUEST['data']['vendor']) . "'";
    $cgx_sql .= ", customer = '" . mysql_escape_string($_REQUEST['data']['customer']) . "'";
    $cgx_sql .= ", employee = '" . mysql_escape_string($_REQUEST['data']['employee']) . "'";
    $cgx_sql .= " WHERE";
    $cgx_sql .= " c_bpartner_id = '{$_REQUEST['pkey']['c_bpartner_id']}'";
} elseif ($_REQUEST['mode'] == 'new') {
    $cgx_sql = "INSERT INTO c_bpartner (";
    $cgx_sql .= "app_org_id,partner_code,partner_name,active,vendor,customer,employee";
    $cgx_sql .= ") values (";
    $cgx_sql .= "'{$_REQUEST['data']['app_org_id']}'";
    $cgx_sql .= ",'" . mysql_escape_string($_REQUEST['data']['partner_code']) . "'";
    $cgx_sql .= ",'" . mysql_escape_string($_REQUEST['data']['partner_name']) . "'";
    $cgx_sql .= ",'" . mysql_escape_string($_REQUEST['data']['active']) . "'";
    $cgx_sql .= ",'" . mysql_escape_string($_REQUEST['data']['vendor']) . "'";
    $cgx_sql .= ",'" . mysql_escape_string($_REQUEST['data']['customer']) . "'";
    $cgx_sql .= ",'" . mysql_escape_string($_REQUEST['data']['employee']) . "'";
    $cgx_sql .= ")";
} elseif ($_REQUEST['mode'] == 'delete') {
    $cgx_sql = "DELETE FROM c_bpartner ";
    $cgx_sql .= " WHERE";
    $cgx_sql .= " c_bpartner_id = '{$_REQUEST['pkey']['c_bpartner_id']}'";
}

if (@mysql_query($cgx_sql, $cgx_connection)) {
    $_SESSION[$GLOBALS['APP_ID']]['master.mitra']['error'] = FALSE;
    $_SESSION[$GLOBALS['APP_ID']]['master.mitra']['info'] = 'Data anda sudah berhasil diperbarui';
    if ($_REQUEST['mode'] == 'new') $cgx_new_id = mysql_insert_id($cgx_connection);
} else {
    $_SESSION[$GLOBALS['APP_ID']]['master.mitra']['error'] = mysql_error($cgx_connection);
}

if ($_REQUEST['mode'] == 'update') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']) . "&pkey[c_bpartner_id]={$_REQUEST['pkey']['c_bpartner_id']}");
} elseif ($_REQUEST['mode'] == 'new') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']) . "&pkey[c_bpartner_id]={$cgx_new_id}");
} elseif ($_REQUEST['mode'] == 'delete') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']));
}
exit;

?>