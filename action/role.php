<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 26/12/2013 17:53:13
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
    header("Content-Disposition: attachment; filename=\"role-" . date("Y-m-d") . ".csv\"");
    echo "\"ID\",\"Role\",\"Organization\",\"Organization\",\"Active\"\n";
    $cgx_rs_export = mysql_query("SELECT app_role_id, app_org_id, role, organization, app_role.active FROM app_role JOIN app_org USING (app_org_id)", $cgx_connection);
    while (($cgx_dt_export = mysql_fetch_array($cgx_rs_export, MYSQL_ASSOC)) !== FALSE) {
        echo "\"" . str_replace("\"", "\"\"", $cgx_dt_export['app_role_id']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['role']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['app_org_id']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['organization']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['active']) . "\"";
        echo "\n";
    }
    mysql_free_result($cgx_rs_export);
    exit;
} elseif ($_REQUEST['mode'] == 'update') {
    $cgx_sql = "UPDATE app_role SET";
    $cgx_sql .= " role = '" . mysql_escape_string($_REQUEST['data']['role']) . "'";
    $cgx_sql .= ", app_org_id = '{$_REQUEST['data']['app_org_id']}'";
    $cgx_sql .= ", active = '" . mysql_escape_string($_REQUEST['data']['active']) . "'";
    $cgx_sql .= " WHERE";
    $cgx_sql .= " app_role_id = '{$_REQUEST['pkey']['app_role_id']}'";
} elseif ($_REQUEST['mode'] == 'new') {
    $cgx_sql = "INSERT INTO app_role (";
    $cgx_sql .= "role,app_org_id,active";
    $cgx_sql .= ") values (";
    $cgx_sql .= "'" . mysql_escape_string($_REQUEST['data']['role']) . "'";
    $cgx_sql .= ",'{$_REQUEST['data']['app_org_id']}'";
    $cgx_sql .= ",'" . mysql_escape_string($_REQUEST['data']['active']) . "'";
    $cgx_sql .= ")";
} elseif ($_REQUEST['mode'] == 'delete') {
    $cgx_sql = "DELETE FROM app_role ";
    $cgx_sql .= " WHERE";
    $cgx_sql .= " app_role_id = '{$_REQUEST['pkey']['app_role_id']}'";
}

if (@mysql_query($cgx_sql, $cgx_connection)) {
    $_SESSION[$GLOBALS['APP_ID']]['role']['error'] = FALSE;
    $_SESSION[$GLOBALS['APP_ID']]['role']['info'] = 'Data anda sudah berhasil diperbarui';
    if ($_REQUEST['mode'] == 'new') {
        $cgx_new_id = mysql_insert_id($cgx_connection);
        $cgx_id = $cgx_new_id;
    } else {
        $cgx_id = $_REQUEST['pkey']['app_role_id'];
    }
} else {
    $_SESSION[$GLOBALS['APP_ID']]['role']['error'] = mysql_error($cgx_connection);
}

if ($_REQUEST['mode'] == 'new' || $_REQUEST['mode'] == 'update') {
    mysql_query("DELETE FROM app_role_priv WHERE app_role_id = '{$cgx_id}'", $APP_CONNECTION);
    if (is_array($_REQUEST['priv'])) {
        foreach ($_REQUEST['priv'] as $priv => $dummy) {
            mysql_query("INSERT INTO app_role_priv (app_role_id, app_priv_id) " .
                    "VALUES ('{$cgx_id}', '{$priv}')", $APP_CONNECTION);
        }
    }
}

if ($_REQUEST['mode'] == 'update') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']) . "&pkey[app_role_id]={$_REQUEST['pkey']['app_role_id']}");
} elseif ($_REQUEST['mode'] == 'new') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']) . "&pkey[app_role_id]={$cgx_new_id}");
} elseif ($_REQUEST['mode'] == 'delete') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']));
}
exit;

?>