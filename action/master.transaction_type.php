<?php

/**
 * (c) Azwari Nugraha <nugraha@pt-gai.com>
 * 22/10/2013 01:37:47
 */

require_once '../init.php';

if (!authenticated()) {
    header("Location: ../index.php");
    exit;
}

if ($_REQUEST['mode'] == 'update' || $_REQUEST['mode'] == 'new') if (!has_privilege('master')) die ('access denied');
if ($_REQUEST['mode'] == 'delete') if (!has_privilege('master')) die ('access denied');

if ($_REQUEST['mode'] == 'export-all') {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=\"master.transaction_type-" . date("Y-m-d") . ".csv\"");
    echo "\"ID\",\"Tipe Code\",\"Tipe Name\",\"Operation\"\n";
    $cgx_rs_export = oci_parse($cgx_connection, "SELECT * FROM m_transaction_type");
    oci_execute($cgx_rs_export);
    while (($cgx_dt_export = oci_fetch_array($cgx_rs_export, OCI_ASSOC)) !== FALSE) {
        echo "\"" . str_replace("\"", "\"\"", $cgx_dt_export['m_transaction_type_id']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['type_code']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['type_name']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['operation']) . "\"";
        echo "\n";
    }
    oci_free_statement($cgx_rs_export);
    exit;
} elseif ($_REQUEST['mode'] == 'update') {
    $cgx_sql = "UPDATE m_transaction_type SET";
    $cgx_sql .= " type_code = '" . mysql_escape_string($_REQUEST['data']['type_code']) . "'";
    $cgx_sql .= ", type_name = '" . mysql_escape_string($_REQUEST['data']['type_name']) . "'";
    $cgx_sql .= ", operation = '{$_REQUEST['data']['operation']}'";
    $cgx_sql .= " WHERE";
    $cgx_sql .= " m_transaction_type_id = '{$_REQUEST['pkey']['m_transaction_type_id']}'";
} elseif ($_REQUEST['mode'] == 'new') {
    $cgx_sql = "INSERT INTO m_transaction_type (";
    $cgx_sql .= "type_code,type_name,operation";
    $cgx_sql .= ") values (";
    $cgx_sql .= "'" . mysql_escape_string($_REQUEST['data']['type_code']) . "'";
    $cgx_sql .= ",'" . mysql_escape_string($_REQUEST['data']['type_name']) . "'";
    $cgx_sql .= ",'{$_REQUEST['data']['operation']}'";
    $cgx_sql .= ")";
} elseif ($_REQUEST['mode'] == 'delete') {
    $cgx_sql = "DELETE FROM m_transaction_type ";
    $cgx_sql .= " WHERE";
    $cgx_sql .= " m_transaction_type_id = '{$_REQUEST['pkey']['m_transaction_type_id']}'";
}

if (@mysql_query($cgx_sql, $cgx_connection)) {
    $_SESSION[$GLOBALS['APP_ID']]['master.transaction_type']['error'] = FALSE;
    $_SESSION[$GLOBALS['APP_ID']]['master.transaction_type']['info'] = 'Your data has been successfully updated';
    if ($_REQUEST['mode'] == 'new') $cgx_new_id = mysql_insert_id($cgx_connection);
} else {
    $_SESSION[$GLOBALS['APP_ID']]['master.transaction_type']['error'] = mysql_error($cgx_connection);
}

if ($_REQUEST['mode'] == 'update') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']) . "&pkey[m_transaction_type_id]={$_REQUEST['pkey']['m_transaction_type_id']}");
} elseif ($_REQUEST['mode'] == 'new') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']) . "&pkey[m_transaction_type_id]={$cgx_new_id}");
} elseif ($_REQUEST['mode'] == 'delete') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']));
}
exit;

?>