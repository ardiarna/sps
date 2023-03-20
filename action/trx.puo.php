<?php

/**
 * (c) Azwari Nugraha <nugraha@pt-gai.com>
 * 24/11/2013 23:46:47
 */

require_once '../init.php';

if (!authenticated()) {
    header("Location: ../index.php");
    exit;
}

if ($_REQUEST['mode'] == 'update' || $_REQUEST['mode'] == 'new') if (!has_privilege('trx.puo')) die ('access denied');
if ($_REQUEST['mode'] == 'delete') if (!has_privilege('trx.puo')) die ('access denied');

if ($_REQUEST['mode'] == 'export-all') {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=\"Purchase Order-" . date("Y-m-d") . ".csv\"");
    echo "\"ID\",\"Document No\",\"Partner Name\",\"Order Date\",\"Remark\"\n";
    $cgx_rs_export = oci_parse($cgx_connection, "SELECT c_order_id, document_no, partner_name, order_date, reference_no, remark
FROM c_order
JOIN c_bpartner USING (c_bpartner_id) WHERE m_transaction_type_id = 2 ");
    oci_execute($cgx_rs_export);
    while (($cgx_dt_export = oci_fetch_array($cgx_rs_export, OCI_ASSOC)) !== FALSE) {
        echo "\"" . str_replace("\"", "\"\"", $cgx_dt_export['c_order_id']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['document_no']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['partner_name']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['order_date']) . "\"";
        //echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['reference_no']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['remark']) . "\"";
        echo "\n";
    }
    oci_free_statement($cgx_rs_export);
    exit;
} elseif ($_REQUEST['mode'] == 'update') {
    $cgx_sql = "UPDATE c_order SET";
    $cgx_sql .= " document_no = '" . mysql_escape_string($_REQUEST['data']['document_no']) . "'";
    $cgx_sql .= " WHERE";
    $cgx_sql .= " c_order_id = '{$_REQUEST['pkey']['c_order_id']}'";
} elseif ($_REQUEST['mode'] == 'new') {
    $cgx_sql = "INSERT INTO c_order (";
    $cgx_sql .= "document_no";
    $cgx_sql .= ") values (";
    $cgx_sql .= "'" . mysql_escape_string($_REQUEST['data']['document_no']) . "'";
    $cgx_sql .= ")";
} elseif ($_REQUEST['mode'] == 'delete') {
    $cgx_sql = "DELETE FROM c_order ";
    $cgx_sql .= " WHERE";
    $cgx_sql .= " c_order_id = '{$_REQUEST['pkey']['c_order_id']}'";
    
    mysql_query("DELETE FROM c_order_line WHERE c_order_id = '{$_REQUEST['pkey']['c_order_id']}'", $APP_CONNECTION);
}

if (@mysql_query($cgx_sql, $cgx_connection)) {
    $_SESSION[$GLOBALS['APP_ID']]['trx.puo']['error'] = FALSE;
    $_SESSION[$GLOBALS['APP_ID']]['trx.puo']['info'] = 'Your data has been successfully updated';
    if ($_REQUEST['mode'] == 'new') $cgx_new_id = mysql_insert_id($cgx_connection);
} else {
    $_SESSION[$GLOBALS['APP_ID']]['trx.puo']['error'] = mysql_error($cgx_connection);
}

if ($_REQUEST['mode'] == 'update') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']) . "&pkey[c_order_id]={$_REQUEST['pkey']['c_order_id']}");
} elseif ($_REQUEST['mode'] == 'new') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']) . "&pkey[c_order_id]={$cgx_new_id}");
} elseif ($_REQUEST['mode'] == 'delete') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']));
}
exit;

?>