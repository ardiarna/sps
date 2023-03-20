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

if ($_REQUEST['mode'] == 'update' || $_REQUEST['mode'] == 'new') if (!has_privilege('master.calendar')) die ('akses ditolak');
if ($_REQUEST['mode'] == 'delete') if (!has_privilege('master.calendar')) die ('akses ditolak');

if ($_REQUEST['mode'] == 'export-all') {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=\"master.calendar-" . date("Y-m-d") . ".csv\"");
    echo "\"ID\",\"Calendar Date\",\"Note\",\"Holiday\"\n";
    $cgx_rs_export = mysql_query("SELECT * FROM m_calendar", $cgx_connection);
    while (($cgx_dt_export = mysql_fetch_array($cgx_rs_export, MYSQL_ASSOC)) !== FALSE) {
        echo "\"" . str_replace("\"", "\"\"", $cgx_dt_export['m_calendar_id']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['calendar_date']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['note']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['isholiday']) . "\"";
        echo "\n";
    }
    mysql_free_result($cgx_rs_export);
    exit;
} elseif ($_REQUEST['mode'] == 'update') {
    $cgx_sql = "UPDATE m_calendar SET";
    $cgx_sql .= " calendar_date = '" . cgx_dmy2ymd($_REQUEST['data']['calendar_date']) . "'";
    $cgx_sql .= ", note = '" . mysql_escape_string($_REQUEST['data']['note']) . "'";
    $cgx_sql .= ", isholiday = '" . mysql_escape_string($_REQUEST['data']['isholiday']) . "'";
    $cgx_sql .= " WHERE";
    $cgx_sql .= " m_calendar_id = '{$_REQUEST['pkey']['m_calendar_id']}'";
} elseif ($_REQUEST['mode'] == 'new') {
    $cgx_sql = "INSERT INTO m_calendar (";
    $cgx_sql .= "calendar_date,note,isholiday";
    $cgx_sql .= ") values (";
    $cgx_sql .= "'" . cgx_dmy2ymd($_REQUEST['data']['calendar_date']) . "'";
    $cgx_sql .= ",'" . mysql_escape_string($_REQUEST['data']['note']) . "'";
    $cgx_sql .= ",'" . mysql_escape_string($_REQUEST['data']['isholiday']) . "'";
    $cgx_sql .= ")";
} elseif ($_REQUEST['mode'] == 'delete') {
    $cgx_sql = "DELETE FROM m_calendar ";
    $cgx_sql .= " WHERE";
    $cgx_sql .= " m_calendar_id = '{$_REQUEST['pkey']['m_calendar_id']}'";
}

if (@mysql_query($cgx_sql, $cgx_connection)) {
    $_SESSION[$GLOBALS['APP_ID']]['master.calendar']['error'] = FALSE;
    $_SESSION[$GLOBALS['APP_ID']]['master.calendar']['info'] = 'Data anda sudah berhasil diperbarui';
    if ($_REQUEST['mode'] == 'new') $cgx_new_id = mysql_insert_id($cgx_connection);
} else {
    $_SESSION[$GLOBALS['APP_ID']]['master.calendar']['error'] = mysql_error($cgx_connection);
}

if ($_REQUEST['mode'] == 'update') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']) . "&pkey[m_calendar_id]={$_REQUEST['pkey']['m_calendar_id']}");
} elseif ($_REQUEST['mode'] == 'new') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']) . "&pkey[m_calendar_id]={$cgx_new_id}");
} elseif ($_REQUEST['mode'] == 'delete') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']));
}
exit;

?>