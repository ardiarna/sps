<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 12/01/2014 23:59:15
 */

require_once '../init.php';

if (!authenticated()) {
    header("Location: ../index.php");
    exit;
}

if ($_REQUEST['mode'] == 'export-all') {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=\"stock_balance-" . date("Y-m-d") . ".csv\"");
    
    echo "\"Jenis Bahan\",\"Tebal\",\"Lebar\",\"Nomor Coil\",\"Kode Coil\",\"Berat\",\"Qty\"\n";
    
    $sql = " 
        SELECT
        C.spec
        ,C.thickness
        ,C.od
        ,B.no_coil
        ,B.no_lot
        ,A.weight
        ,A.quantity

        FROM
        m_coil_slit AS A JOIN m_coil AS B ON (A.m_coil_id =  B.m_coil_id)
                         LEFT JOIN m_product AS C ON (A.m_product_id = C.m_product_id)
         
        ";
    
    $cgx_rs_export = mysql_query($sql, $cgx_connection);
    while (($cgx_dt_export = mysql_fetch_array($cgx_rs_export, MYSQL_ASSOC)) !== FALSE) {
        echo "\"" . str_replace("\"", "\"\"", $cgx_dt_export['spec']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['thickness']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['od']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['no_coil']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['no_lot']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['weight']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['quantity']) . "\"";
        echo "\n";
    }
    mysql_free_result($cgx_rs_export);
    exit;
} 

if (@mysql_query($cgx_sql, $cgx_connection)) {
    $_SESSION[$GLOBALS['APP_ID']]['view.hslit']['error'] = FALSE;
    $_SESSION[$GLOBALS['APP_ID']]['view.hslit']['info'] = 'Data anda sudah berhasil diperbarui';
    if ($_REQUEST['mode'] == 'new') $cgx_new_id = $_REQUEST['data']['Array'];
} else {
    $_SESSION[$GLOBALS['APP_ID']]['view.hslit']['error'] = mysql_error($cgx_connection);
}

if ($_REQUEST['mode'] == 'update') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']) . "&");
} elseif ($_REQUEST['mode'] == 'new') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']) . "&");
} elseif ($_REQUEST['mode'] == 'delete') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']));
}
exit;

?>