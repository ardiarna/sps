<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 05/12/2013 14:35:39
 */

require_once '../init.php';

if (!authenticated()) {
    header("Location: ../index.php");
    exit;
}

$cgx_sql = "SELECT
            MAX(m_box_inout.m_box_inout_date) tanggal,
            m_box.*,
            cbbox.partner_name partner_name,
            cblok.partner_name partner_name_lok,
            if(m_box.location='I','di dalam','di luar') as location
            
            from m_box
            
            LEFT JOIN m_box_inout_line ON m_box.m_box_id = m_box_inout_line.m_box_id
            LEFT JOIN m_box_inout ON m_box_inout_line.m_box_inout_id = m_box_inout.m_box_inout_id
            LEFT JOIN c_bpartner cbbox ON m_box.c_bpartner_id = cbbox.c_bpartner_id
            LEFT JOIN c_bpartner cblok ON m_box_inout.c_bpartner_id = cblok.c_bpartner_id
            
            where 1 = 1 AND ". org_filter_trx('m_box.app_org_id');


$f1 = $_REQUEST['f1'];
$customer = $_REQUEST['customer'];
$box_code= $_REQUEST['box_code'];
$box_number = $_REQUEST['box_number'];
$customer_lok = $_REQUEST['customer_lok'];
$date_inout_f = $_REQUEST['date_inout_f'];
$date_inout_t = $_REQUEST['date_inout_t'];

if ($f1) $cgx_sql .= " AND m_box.location = '" . mysql_escape_string($f1) . "'";
if ($box_code) $cgx_sql .= " AND m_box.box_code LIKE '%" . mysql_escape_string($box_code) . "%'";
if ($box_number) $cgx_sql .= " AND m_box.box_number LIKE '%" . mysql_escape_string($box_number) . "%'";
if ($customer) $cgx_sql .= " AND cbbox.partner_name LIKE '%" . mysql_escape_string($customer) . "%'"; 
if ($customer_lok) $cgx_sql .= " AND cblok.partner_name LIKE '%" . mysql_escape_string($customer_lok) . "%'";
$cgx_sql .= " GROUP BY m_box.m_box_id";
if ($date_inout_f) $cgx_sql .= " HAVING tanggal >= '" . npl_dmy2ymd($date_inout_f) . "'";
if ($date_inout_t) $cgx_sql .= " AND tanggal <= '" . npl_dmy2ymd($date_inout_t) . "'";
if ($date_inout_f or $date_inout_t) $cgx_sql .= " ORDER BY tanggal DESC"; 

      
if ($_REQUEST['mode'] == 'export-all') {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=\"view.box2-" . date("Y-m-d") . ".csv\"");
    echo "\"ID\",\"Box Number\",\"Box Code\",\"Box Size\",\"Pipe Size \",\"Kapasitas Box\",\"Customer\",\"Lokasi\",\"Lokasi Customer\",\"Tanggal\"\n";
    $cgx_rs_export = mysql_query($cgx_sql, $cgx_connection);

    while (($cgx_dt_export = mysql_fetch_array($cgx_rs_export, MYSQL_ASSOC)) !== FALSE) {
        echo "\"" . str_replace("\"", "\"\"", $cgx_dt_export['m_box_id']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['box_number']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['box_code']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['box_size']) . "\"";;
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['pipe_size']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['kapasitas_box']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['partner_name']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['location']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['partner_name_lok']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['tanggal']) . "\"";
        echo "\n";
    }
    mysql_free_result($cgx_rs_export);
    exit;
} elseif ($_REQUEST['mode'] == 'update') {
    $cgx_sql = "UPDATE  SET";
    $cgx_sql .= " WHERE";
} elseif ($_REQUEST['mode'] == 'new') {
    $cgx_sql = "INSERT INTO  (";
    $cgx_sql .= "INSERT INTO  (";
    $cgx_sql .= ") values (";
    $cgx_sql .= ")";
} elseif ($_REQUEST['mode'] == 'delete') {
    $cgx_sql = "DELETE FROM  ";
    $cgx_sql .= " WHERE";
}

if (@mysql_query($cgx_sql, $cgx_connection)) {
    $_SESSION[$GLOBALS['APP_ID']]['view.box2']['error'] = FALSE;
    $_SESSION[$GLOBALS['APP_ID']]['view.box2']['info'] = 'Data anda sudah berhasil diperbarui';
    if ($_REQUEST['mode'] == 'new') $cgx_new_id = $_REQUEST['data']['Array'];
} else {
    $_SESSION[$GLOBALS['APP_ID']]['view.box2']['error'] = mysql_error($cgx_connection);
}

if ($_REQUEST['mode'] == 'update') {
    header("Location: ../index.php?" . urldecode($_REQUEST['backvar']) . "&");
} elseif ($_REQUEST['mode'] == 'new') {
    header("Location: ../index.php?" . urldecode($_REQUEST['backvar']) . "&");
} elseif ($_REQUEST['mode'] == 'delete') {
    header("Location: ../index.php?" . urldecode($_REQUEST['backvar']));
}
exit;

?>