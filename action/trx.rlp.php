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

if ($_REQUEST['mode'] == 'update' || $_REQUEST['mode'] == 'new') if (!has_privilege('trx.rlp')) die ('access denied');
if ($_REQUEST['mode'] == 'delete') if (!has_privilege('trx.rlp')) die ('access denied');

if ($_REQUEST['mode'] == 'export-all') {
    
} elseif ($_REQUEST['mode'] == 'update') {
    
} elseif ($_REQUEST['mode'] == 'new') {
    
} elseif ($_REQUEST['mode'] == 'delete') {
    $cgx_sql = "DELETE FROM m_work_order ";
    $cgx_sql .= " WHERE";
    $cgx_sql .= " m_work_order_id = '{$_REQUEST['pkey']['m_work_order_id']}'";
    
    mysql_query("DELETE FROM m_work_order_line WHERE m_work_order_id = '{$_REQUEST['pkey']['m_work_order_id']}'", $APP_CONNECTION);
}

if (@mysql_query($cgx_sql, $cgx_connection)) {
    $_SESSION[$GLOBALS['APP_ID']]['trx.rlp']['error'] = FALSE;
    $_SESSION[$GLOBALS['APP_ID']]['trx.rlp']['info'] = 'Your data has been successfully updated';
    if ($_REQUEST['mode'] == 'new') $cgx_new_id = mysql_insert_id($cgx_connection);
} else {
    $_SESSION[$GLOBALS['APP_ID']]['trx.rlp']['error'] = mysql_error($cgx_connection);
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