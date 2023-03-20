<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 18/12/2013 21:42:48
 */

require_once '../init.php';

if (!authenticated()) {
    header("Location: ../index.php");
    exit;
}

if ($_REQUEST['mode'] == 'update' || $_REQUEST['mode'] == 'new') if (!has_privilege('master.box')) die ('akses ditolak');
if ($_REQUEST['mode'] == 'delete') if (!has_privilege('master.box')) die ('akses ditolak');

if ($_REQUEST['mode'] == 'export-all') {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=\"master.box-" . date("Y-m-d") . ".csv\"");
    echo "\"ID\",\"Box Number\"\n";
    $cgx_rs_export = mysql_query("SELECT * FROM m_box WHERE app_org_id = '" . org() . "'", $cgx_connection);
    while (($cgx_dt_export = mysql_fetch_array($cgx_rs_export, MYSQL_ASSOC)) !== FALSE) {
        echo "\"" . str_replace("\"", "\"\"", $cgx_dt_export['m_box_id']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['box_number']) . "\"";
        echo "\n";
    }
    mysql_free_result($cgx_rs_export);
    exit;
} elseif ($_REQUEST['mode'] == 'update') {
    $cgx_sql = "UPDATE m_box SET";
    $cgx_sql .= " app_org_id = '" . mysql_escape_string($_REQUEST['data']['app_org_id']) . "',";
    $cgx_sql .= " box_number = '" . mysql_escape_string($_REQUEST['data']['box_number']) . "',";
    $cgx_sql .= " box_code = '" . mysql_escape_string($_REQUEST['data']['box_code']) . "',";
    $cgx_sql .= " c_bpartner_id = '" . mysql_escape_string($_REQUEST['data']['c_bpartner_id']) . "',";
    $cgx_sql .= " box_size = '" . mysql_escape_string($_REQUEST['data']['box_size']) . "',";
    $cgx_sql .= " pipe_size = '" . mysql_escape_string($_REQUEST['data']['pipe_size']) . "',";
    $cgx_sql .= " kapasitas_box = '" . mysql_escape_string($_REQUEST['data']['kapasitas_box']) . "'";
    $cgx_sql .= " WHERE";
    $cgx_sql .= " m_box_id = '{$_REQUEST['pkey']['m_box_id']}'";
} elseif ($_REQUEST['mode'] == 'new') {
    if($_REQUEST['xtype'] == 'single'){
        $cgx_sql = "INSERT INTO m_box (";
        $cgx_sql .= "app_org_id,box_number,box_code,c_bpartner_id,box_size,pipe_size,kapasitas_box";
        $cgx_sql .= ") values (";
        $cgx_sql .= "'" . mysql_escape_string($_REQUEST['data']['app_org_id']) . "',";
        $cgx_sql .= "'" . mysql_escape_string($_REQUEST['data']['box_number']) . "',";
        $cgx_sql .= "'" . mysql_escape_string($_REQUEST['data']['box_code']) . "',";
        $cgx_sql .= "'" . mysql_escape_string($_REQUEST['data']['c_bpartner_id']) . "',";
        $cgx_sql .= "'" . mysql_escape_string($_REQUEST['data']['box_size']) . "',";
        $cgx_sql .= "'" . mysql_escape_string($_REQUEST['data']['pipe_size']) . "',";
        $cgx_sql .= "'" . mysql_escape_string($_REQUEST['data']['kapasitas_box']) . "'";
        $cgx_sql .= ")";
    }else{
        $cgx_sql = "INSERT INTO m_box (";
        $cgx_sql .= "app_org_id,box_number,box_code,c_bpartner_id,box_size,pipe_size,kapasitas_box";
        $cgx_sql .= ") values ";
        for($i=intval($_REQUEST['data']['box_number_start']); $i<=intval($_REQUEST['data']['box_number_end']); $i++){
            $cgx_sql .= "(";
            $cgx_sql .= "'" . mysql_escape_string($_REQUEST['data']['app_org_id']) . "',";
            $cgx_sql .= "'" . str_pad($i, 4, '0', STR_PAD_LEFT) . "',";
            $cgx_sql .= "'" . mysql_escape_string($_REQUEST['data']['box_code']) . " " . str_pad($i, 4, '0', STR_PAD_LEFT) . "',";
            $cgx_sql .= "'" . mysql_escape_string($_REQUEST['data']['c_bpartner_id']) . "',";
            $cgx_sql .= "'-',";
            $cgx_sql .= "'-',";
            $cgx_sql .= "'" . mysql_escape_string($_REQUEST['data']['kapasitas_box']) . "'";
            $cgx_sql .= ")";
            if($i != $_REQUEST['data']['box_number_end']){
                $cgx_sql.=",";
            }
        }
    }
} elseif ($_REQUEST['mode'] == 'delete') {
    $cgx_sql = "DELETE FROM m_box ";
    $cgx_sql .= " WHERE";
    $cgx_sql .= " m_box_id = '{$_REQUEST['pkey']['m_box_id']}'";
}

if (@mysql_query($cgx_sql, $cgx_connection)) {
    $_SESSION[$GLOBALS['APP_ID']]['master.box']['error'] = FALSE;
    $_SESSION[$GLOBALS['APP_ID']]['master.box']['info'] = 'Data anda sudah berhasil diperbarui';
    if ($_REQUEST['mode'] == 'new') $cgx_new_id = mysql_insert_id($cgx_connection);
} else {
    $_SESSION[$GLOBALS['APP_ID']]['master.box']['error'] = mysql_error($cgx_connection);
}

if ($_REQUEST['mode'] == 'update') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']) . "&pkey[m_box_id]={$_REQUEST['pkey']['m_box_id']}&pkey[type]={$_REQUEST['xtype']}");
} elseif ($_REQUEST['mode'] == 'new') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']) . "&pkey[m_box_id]={$cgx_new_id}&pkey[type]={$_REQUEST['xtype']}");
} elseif ($_REQUEST['mode'] == 'delete') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']));
}
exit;

?>