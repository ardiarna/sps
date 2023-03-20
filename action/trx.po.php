<?php

/**
 * (c) Azwari Nugraha <nugraha@pt-gai.com>
 */

require_once '../init.php';

if (!authenticated()) {
    header("Location: ../index.php");
    exit;
}

if ($_REQUEST['mode'] == 'update' || $_REQUEST['mode'] == 'new') if (!has_privilege('trx.po')) die ('access denied');
if ($_REQUEST['mode'] == 'delete') if (!has_privilege('trx.po')) die ('access denied');

if ($_REQUEST['mode'] == 'export-all') {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=\"trx.po-" . date("Y-m-d") . ".csv\"");
    echo "\"ID\",\"Document No\",\"Partner Name\",\"Order Date\",\"Reference No\",\"Remark\"\n";
    $cgx_rs_export = oci_parse($cgx_connection, "SELECT c_po_id, document_no, partner_name, po_date, reference_no, remark
FROM c_po
JOIN c_bpartner USING (c_bpartner_id)");
    oci_execute($cgx_rs_export);
    while (($cgx_dt_export = oci_fetch_array($cgx_rs_export, OCI_ASSOC)) !== FALSE) {
        echo "\"" . str_replace("\"", "\"\"", $cgx_dt_export['c_po_id']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['document_no']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['partner_name']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['po_date']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['reference_no']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['remark']) . "\"";
        echo "\n";
    }
    oci_free_statement($cgx_rs_export);
    exit;
} elseif ($_REQUEST['mode'] == 'update') {
    $cgx_sql = "UPDATE c_po SET";
    $cgx_sql .= " document_no = '" . mysql_escape_string($_REQUEST['data']['document_no']) . "'";
    $cgx_sql .= " WHERE";
    $cgx_sql .= " c_po_id = '{$_REQUEST['pkey']['c_po_id']}'";
} elseif ($_REQUEST['mode'] == 'new') {
    $cgx_sql = "INSERT INTO c_po (";
    $cgx_sql .= "document_no";
    $cgx_sql .= ") values (";
    $cgx_sql .= "'" . mysql_escape_string($_REQUEST['data']['document_no']) . "'";
    $cgx_sql .= ")";
} elseif ($_REQUEST['mode'] == 'delete') {
    $cgx_sql = "DELETE FROM c_po ";
    $cgx_sql .= " WHERE";
    $cgx_sql .= " c_po_id = '{$_REQUEST['pkey']['c_po_id']}'";
    
    mysql_query("DELETE FROM c_po_line WHERE c_po_id = '{$_REQUEST['pkey']['c_po_id']}'", $APP_CONNECTION);
}

if (@mysql_query($cgx_sql, $cgx_connection)) {
    $_SESSION[$GLOBALS['APP_ID']]['trx.po']['error'] = FALSE;
    $_SESSION[$GLOBALS['APP_ID']]['trx.po']['info'] = 'Your data has been successfully updated';
    if ($_REQUEST['mode'] == 'new') $cgx_new_id = mysql_insert_id($cgx_connection);
} else {
    $_SESSION[$GLOBALS['APP_ID']]['trx.po']['error'] = mysql_error($cgx_connection);
}

if ($_REQUEST['mode'] == 'update') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']) . "&pkey[c_po_id]={$_REQUEST['pkey']['c_po_id']}");
} elseif ($_REQUEST['mode'] == 'new') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']) . "&pkey[c_po_id]={$cgx_new_id}");
} elseif ($_REQUEST['mode'] == 'delete') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']));
}
exit;

?>