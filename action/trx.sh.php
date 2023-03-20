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

if ($_REQUEST['mode'] == 'update' || $_REQUEST['mode'] == 'new') if (!has_privilege('trx')) die ('access denied');
if ($_REQUEST['mode'] == 'delete') if (!has_privilege('trx')) die ('access denied');

if ($_REQUEST['mode'] == 'export-all') {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=\"trx.sh-" . date("Y-m-d") . ".csv\"");
    echo "\"ID Pengiriman Barang\",\"Tanggal Pengiriman\",\"No Dokumen\",\"ID Transaksi\"\n";
    $cgx_rs_export = oci_parse($cgx_connection, "SELECT * FROM m_inout");
    oci_execute($cgx_rs_export);
    while (($cgx_dt_export = oci_fetch_array($cgx_rs_export, OCI_ASSOC)) !== FALSE) {
        echo "\"" . str_replace("\"", "\"\"", $cgx_dt_export['m_inout_id']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['m_inout_date']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['document_no']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['m_transaction_type_id']) . "\"";
        echo "\n";
    }
    oci_free_statement($cgx_rs_export);
    exit;
} elseif ($_REQUEST['mode'] == 'update') {
    $cgx_sql = "UPDATE m_inout SET";
    $cgx_sql .= " m_inout_date = '" . mysql_escape_string($_REQUEST['data']['m_inout_date']) . "'";
    $cgx_sql .= ", document_no = '{$_REQUEST['data']['document_no']}'";
    $cgx_sql .= ", m_transaction_type_id = '{$_REQUEST['data']['m_transaction_type_id']}'";
    $cgx_sql .= " WHERE";
    $cgx_sql .= " m_inout_id = '{$_REQUEST['pkey']['m_inout_id']}'";
} elseif ($_REQUEST['mode'] == 'new') {
    $_SESSION["SH"]["m_inout_date"]=mysql_escape_string($_REQUEST['data']['m_inout_date']);
    $_SESSION["SH"]["document_no"]=$_REQUEST['data']['document_no'];
    $_SESSION["SH"]["m_transaction_type_id"]=4;

    $query=mysql_query("SELECT MAX(m_inout_id) as idmax FROM m_inout");
    $data=mysql_fetch_array($query);

    $cgx_new_id=$data["idmax"]+1;

    $_SESSION["SO"]["m_inout_id"]=$cgx_new_id;

    $status="add";
} elseif ($_REQUEST['mode'] == 'delete') {
    $cgx_sql = "DELETE FROM m_inout ";
    $cgx_sql .= " WHERE";
    $cgx_sql .= " m_inout_id = '{$_REQUEST['pkey']['m_inout_id']}'";
}


if ($_REQUEST['mode'] == 'update') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']) . "&pkey[m_inout_id]={$_REQUEST['pkey']['m_inout_id']}");
} elseif ($_REQUEST['mode'] == 'new') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']) . "&pkey[m_inout_id]={$cgx_new_id}&pkey[PROCESS_STATUS]={$status}");
} elseif ($_REQUEST['mode'] == 'delete') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']));
}
exit;

?>