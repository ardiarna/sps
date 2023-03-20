<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 05/12/2013 13:41:52
 */

require_once '../init.php';

if (!authenticated()) {
    header("Location: ../index.php");
    exit;
}

$cgx_sql="SELECT 
                c_forecast_id,
                document_no,
                partner_name,
                periode,
                notes,
                product_code,
                product_name,
                quantity,
                create_date,
                buat.user_name as createuser,
                update_date,
                edit.user_name as updateuser
                
                FROM c_forecast
                
                JOIN c_forecast_line USING (c_forecast_id)
                JOIN c_bpartner USING (c_bpartner_id) 
                JOIN m_product USING (m_product_id)
                JOIN app_org ON(c_forecast.app_org_id=app_org.app_org_id)
                JOIN app_user buat ON (c_forecast.create_user = buat.user_id)
                JOIN app_user edit ON (c_forecast.update_user = edit.user_id)
                
                WHERE 1=1 AND ". org_filter_trx('c_forecast.app_org_id');

$document_no= $_REQUEST['document_no'];
$partner_name= $_REQUEST['partner_name'];
$periode = $_REQUEST['periode'];

if($document_no) $cgx_sql .=" AND c_forecast.document_no  LIKE '%{$document_no}%'"; 
if($partner_name) $cgx_sql .= " AND  c_bpartner.partner_name LIKE '%{$partner_name}%'";
if ($periode) $cgx_sql .= " AND c_forecast.periode = '" . npl_period2mysqldate($periode) . "'"; 
     
if ($_REQUEST['mode'] == 'export-all') {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=\"view.fo-" . date("Y-m-d") . ".csv\"");
    echo "\"ID\",\"Nomor Dokumen\",\"Customer\",\"Periode\",\"Notes\",\"Item Number\",\"Product Name\",\"Quantity\",\"Create Date\",\"Create User\",\"Update Date\",\"Update User\"\n";
    $cgx_rs_export = mysql_query($cgx_sql, $cgx_connection);
    while (($cgx_dt_export = mysql_fetch_array($cgx_rs_export, MYSQL_ASSOC)) !== FALSE) {
        echo "\"" . str_replace("\"", "\"\"", $cgx_dt_export['c_forecast_id']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['document_no']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['partner_name']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['periode']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['notes']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['product_code']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['product_name']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['quantity']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['create_date']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['createuser']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['update_date']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['updateuser']) . "\"";
        echo "\n";
    }
    mysql_free_result($cgx_rs_export);
    exit; 
}if (@mysql_query($cgx_sql, $cgx_connection)) {
    $_SESSION[$GLOBALS['APP_ID']]['view.fo']['error'] = FALSE;
    //$_SESSION[$GLOBALS['APP_ID']]['view.fo']['info'] = 'Data anda sudah berhasil diperbarui';
    if ($_REQUEST['mode'] == 'new') $cgx_new_id = $_REQUEST['data']['Array'];
} else {
    $_SESSION[$GLOBALS['APP_ID']]['view.fo']['error'] = mysql_error($cgx_connection);
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