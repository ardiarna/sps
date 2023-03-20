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

if ($_REQUEST['mode'] == 'update' || $_REQUEST['mode'] == 'new') if (!has_privilege('master.machine')) die ('akses ditolak');
if ($_REQUEST['mode'] == 'delete') if (!has_privilege('master.machine')) die ('akses ditolak');

if ($_REQUEST['mode'] == 'export-all') {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=\"master.machine-" . date("Y-m-d") . ".csv\"");
    echo "\"MACHINE ITEM ID\"";
    echo ",\"MACHINE CODE\"";
    echo ",\"MACHINE NAME\"";
    echo ",\"ACTIVE\"";
    echo ",\"ITEM NUMBER\"";
    echo ",\"CUSTOMER\"";
    echo ",\"SPEC\"";
    echo ",\"OD\"";
    echo ",\"THICKNESS\"";
    echo ",\"LENGTH\"";
    echo ",\"CYCLE TIME (S)\"";
    echo ",\"RESULT/HOUR (PCS)\"";
    echo ",\"RESULT/SHIFT (PCS\"";
    echo "\n";
    $cgx_sql = "SELECT m_machine.*, m_machine_item_id, cycle_time, result_hour, result_shift, product_code, 
            spec, od, thickness, length, partner_name FROM m_machine 
            LEFT JOIN m_machine_item USING (m_machine_id) 
            LEFT JOIN m_product ON (m_machine_item.m_product_id=m_product.m_product_id) 
            LEFT JOIN c_bpartner ON (m_product.c_bpartner_id=c_bpartner.c_bpartner_id) 
            WHERE m_machine.app_org_id='". org() ."' ORDER BY machine_name, m_machine_item_id";
    $cgx_rs_export = mysql_query($cgx_sql, $cgx_connection);
    while (($cgx_dt_export = mysql_fetch_array($cgx_rs_export, MYSQL_ASSOC)) !== FALSE) {
        echo "\"" . str_replace("\"", "\"\"", $cgx_dt_export['m_machine_item_id']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['machine_code']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['machine_name']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['active']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['product_code']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['partner_name']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['spec']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['od']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['thickness']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['length']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['cycle_time']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['result_hour']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['result_shift']) . "\"";
        echo "\n";
    }
    mysql_free_result($cgx_rs_export);
    exit;
} elseif ($_REQUEST['mode'] == 'update') {
    $cgx_sql = "UPDATE m_machine SET";
    $cgx_sql .= " app_org_id = '{$_REQUEST['data']['app_org_id']}'";
    $cgx_sql .= ", machine_code = '" . mysql_escape_string($_REQUEST['data']['machine_code']) . "'";
    $cgx_sql .= ", machine_name = '" . mysql_escape_string($_REQUEST['data']['machine_name']) . "'";
    $cgx_sql .= ", active = '" . mysql_escape_string($_REQUEST['data']['active']) . "'";
    $cgx_sql .= ", resultperday = '" . mysql_escape_string($_REQUEST['data']['resultperday']) . "'";
    $cgx_sql .= " WHERE";
    $cgx_sql .= " m_machine_id = '{$_REQUEST['pkey']['m_machine_id']}'";
} elseif ($_REQUEST['mode'] == 'new') {
    $cgx_sql = "INSERT INTO m_machine (";
    $cgx_sql .= "app_org_id,machine_code,machine_name,active, resultperday";
    $cgx_sql .= ") values (";
    $cgx_sql .= "'{$_REQUEST['data']['app_org_id']}'";
    $cgx_sql .= ",'" . mysql_escape_string($_REQUEST['data']['machine_code']) . "'";
    $cgx_sql .= ",'" . mysql_escape_string($_REQUEST['data']['machine_name']) . "'";
    $cgx_sql .= ",'" . mysql_escape_string($_REQUEST['data']['active']) . "'";
    $cgx_sql .= ",'" . mysql_escape_string($_REQUEST['data']['resultperday']) . "'";
    $cgx_sql .= ")";
} elseif ($_REQUEST['mode'] == 'delete') {
    $cgx_sql = "DELETE FROM m_machine ";
    $cgx_sql .= " WHERE";
    $cgx_sql .= " m_machine_id = '{$_REQUEST['pkey']['m_machine_id']}'";
}

if (@mysql_query($cgx_sql, $cgx_connection)) {
    $_SESSION[$GLOBALS['APP_ID']]['master.machine']['error'] = FALSE;
    $_SESSION[$GLOBALS['APP_ID']]['master.machine']['info'] = 'Data anda sudah berhasil diperbarui';
    if ($_REQUEST['mode'] == 'new') $cgx_new_id = mysql_insert_id($cgx_connection);
} else {
    $_SESSION[$GLOBALS['APP_ID']]['master.machine']['error'] = mysql_error($cgx_connection);
}

if ($_REQUEST['mode'] == 'update') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']) . "&pkey[m_machine_id]={$_REQUEST['pkey']['m_machine_id']}");
} elseif ($_REQUEST['mode'] == 'new') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']) . "&pkey[m_machine_id]={$cgx_new_id}");
} elseif ($_REQUEST['mode'] == 'delete') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']));
}
exit;

?>