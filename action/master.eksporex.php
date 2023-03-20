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

if ($_REQUEST['mode'] == 'export-all') {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=\"m_stock_warehouse_d_2-" . date("Y-m-d") . ".csv\"");
    echo "\"c_order_id\"";
    echo "\n";
    $cgx_sql = "SELECT distinct c_order_line.c_order_id FROM c_order co JOIN c_order_line USING (c_order_id) WHERE co.app_org_id='3' and delivered_quantity=0 and schedule_delivery_date < '2014-06-30'";
    $cgx_rs_export = mysql_query($cgx_sql, $cgx_connection);
    while (($cgx_dt_export = mysql_fetch_array($cgx_rs_export, MYSQL_ASSOC)) !== FALSE) {
        echo "\"" . str_replace("\"", "\"\"", $cgx_dt_export['c_order_id']) . "\"";
        echo "\n";
    }
    // echo "\"\"";
    // echo ",\"\"";
    // echo "\n";
    // $cgx_sql = "";
    // $cgx_rs_export = mysql_query($cgx_sql, $cgx_connection);
    // while (($cgx_dt_export = mysql_fetch_array($cgx_rs_export, MYSQL_ASSOC)) !== FALSE) {
    //     echo "\"" . str_replace("\"", "\"\"", $cgx_dt_export['']) . "\"";
    //     echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['']) . "\"";
    //     echo "\n";
    // }
    mysql_free_result($cgx_rs_export);
    exit;
}

if (@mysql_query($cgx_sql, $cgx_connection)) {
    $_SESSION[$GLOBALS['APP_ID']]['master.eksporex']['error'] = FALSE;
    $_SESSION[$GLOBALS['APP_ID']]['master.eksporex']['info'] = 'Data anda sudah berhasil diperbarui'; 
} else {
    $_SESSION[$GLOBALS['APP_ID']]['master.eksporex']['error'] = mysql_error($cgx_connection);
}
exit;

?>