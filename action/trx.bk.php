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

if ($_REQUEST['mode'] == 'delete') if (!has_privilege('trx.barang-keluar')) die ('access denied');

if ($_REQUEST['mode'] == 'delete') {
    $cgx_sql = "DELETE FROM m_inout ";
    $cgx_sql .= " WHERE";
    $cgx_sql .= " m_inout_id = '{$_REQUEST['pkey']['m_inout_id']}'";
    
    mysql_query("DELETE FROM m_inout_line WHERE m_inout_id = '{$_REQUEST['pkey']['m_inout_id']}'", $APP_CONNECTION);
}

if (@mysql_query($cgx_sql, $cgx_connection)) {
    $_SESSION[$GLOBALS['APP_ID']]['trx.bk']['error'] = FALSE;
    $_SESSION[$GLOBALS['APP_ID']]['trx.bk']['info'] = 'Your data has been successfully updated';
    if ($_REQUEST['mode'] == 'new') $cgx_new_id = mysql_insert_id($cgx_connection);
} else {
    $_SESSION[$GLOBALS['APP_ID']]['trx.bk']['error'] = mysql_error($cgx_connection);
}

if ($_REQUEST['mode'] == 'delete') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']));
}
exit;

?>