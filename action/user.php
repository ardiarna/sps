<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 27/12/2013 14:10:44
 */

require_once '../init.php';

if (!authenticated()) {
    header("Location: ../index.php");
    exit;
}

if ($_REQUEST['mode'] == 'update' || $_REQUEST['mode'] == 'new') if (!is_admin()) die ('akses ditolak');
if ($_REQUEST['mode'] == 'delete') if (!is_admin()) die ('akses ditolak');

if ($_REQUEST['mode'] == 'export-all') {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=\"user-" . date("Y-m-d") . ".csv\"");
    echo "\"ID\",\"Nama Login\",\"Nama Lengkap\",\"Email\",\"Aktif\",\"Alamat IP\",\"Tanggal Login\"\n";
    $cgx_rs_export = mysql_query("SELECT * FROM app_user", $cgx_connection);
    while (($cgx_dt_export = mysql_fetch_array($cgx_rs_export, MYSQL_ASSOC)) !== FALSE) {
        echo "\"" . str_replace("\"", "\"\"", $cgx_dt_export['user_id']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['user_name']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['user_fullname']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['user_email']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['user_active']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['last_ip']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['last_login']) . "\"";
        echo "\n";
    }
    mysql_free_result($cgx_rs_export);
    exit;
} elseif ($_REQUEST['mode'] == 'update') {
    $cgx_sql = "UPDATE app_user SET";
    $cgx_sql .= " user_name = '" . mysql_escape_string($_REQUEST['data']['user_name']) . "'";
    $cgx_sql .= ", user_fullname = '" . mysql_escape_string($_REQUEST['data']['user_fullname']) . "'";
    $cgx_sql .= ", user_email = '" . mysql_escape_string($_REQUEST['data']['user_email']) . "'";
    $cgx_sql .= ", user_active = '" . mysql_escape_string($_REQUEST['data']['user_active']) . "'";
    $cgx_sql .= " WHERE";
    $cgx_sql .= " user_id = '{$_REQUEST['pkey']['user_id']}'";
} elseif ($_REQUEST['mode'] == 'new') {
    $cgx_sql = "INSERT INTO app_user (";
    $cgx_sql .= "user_name,user_fullname,user_email,user_active";
    $cgx_sql .= ") values (";
    $cgx_sql .= "'" . mysql_escape_string($_REQUEST['data']['user_name']) . "'";
    $cgx_sql .= ",'" . mysql_escape_string($_REQUEST['data']['user_fullname']) . "'";
    $cgx_sql .= ",'" . mysql_escape_string($_REQUEST['data']['user_email']) . "'";
    $cgx_sql .= ",'" . mysql_escape_string($_REQUEST['data']['user_active']) . "'";
    $cgx_sql .= ")";
} elseif ($_REQUEST['mode'] == 'delete') {
    $cgx_sql = "DELETE FROM app_user ";
    $cgx_sql .= " WHERE";
    $cgx_sql .= " user_id = '{$_REQUEST['pkey']['user_id']}'";
}

if (@mysql_query($cgx_sql, $cgx_connection)) {
    $_SESSION[$GLOBALS['APP_ID']]['user']['error'] = FALSE;
    $_SESSION[$GLOBALS['APP_ID']]['user']['info'] = 'Data anda sudah berhasil diperbarui';
    if ($_REQUEST['mode'] == 'new') {
        $cgx_new_id = mysql_insert_id($cgx_connection);
        $cgx_id = $cgx_new_id;
    } else {
        $cgx_id = $_REQUEST['pkey']['user_id'];
    }
} else {
    $_SESSION[$GLOBALS['APP_ID']]['user']['error'] = mysql_error($cgx_connection);
}

mysql_query("DELETE FROM app_user_role WHERE user_id = '{$cgx_id}'", $APP_CONNECTION);
if (is_array($_REQUEST['role'])) {
    foreach ($_REQUEST['role'] as $role => $dummy) {
        mysql_query("INSERT INTO app_user_role (user_id, app_role_id) VALUES ('{$cgx_id}', '{$role}')", $APP_CONNECTION);
    }
}

if ($_REQUEST['password']) mysql_query("UPDATE app_user SET user_password = '" . md5($_REQUEST['password']) . "' WHERE user_id = '{$cgx_id}'", $APP_CONNECTION);

if ($_REQUEST['mode'] == 'update') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']) . "&pkey[user_id]={$_REQUEST['pkey']['user_id']}");
} elseif ($_REQUEST['mode'] == 'new') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']) . "&pkey[user_id]={$cgx_new_id}");
} elseif ($_REQUEST['mode'] == 'delete') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']));
}
exit;

?>