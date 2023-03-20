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
    
    echo "\"Product ID.\",\"Kode Coil\",\"Nomor Coil\",\"Berat\",\"Tanggal Masuk Gudang\",\"Umur\",\"Lebar\",\"Tebal\",\"Spec\",\"No. Purchase Order\",\"No. Penerimaan Bahan Bakur\",\"No. W.O Slitting\",\"No. Realisasi W.O\",\"Status\"\n";
    
    $sql = " 
        SELECT
        A.m_product_id
        ,A.no_lot        
        ,A.no_coil
        ,A.weight
        ,A.`status` AS `status`
        ,IF(A.`status` = 'O','0',A.weight) AS weight_2
        ,B.product_code
        ,B.od
        ,B.thickness
        ,B.spec
        ,C.document_no AS no_wo_slitting
        ,C.m_wo_slit_id
        ,D.document_no AS no_realisasi_wo
        ,D.m_prod_slit_id
        ,F.document_no AS no_penerimaan_bahan_baku
        ,F.m_inout_id
        ,H.document_no AS no_purchase_order
        ,H.c_order_id
        ,B.category
        ,I.m_inout_date
        ,DATEDIFF(CURDATE(),I.m_inout_date) AS umur
        
        FROM
        m_coil AS A LEFT JOIN m_product AS B ON (A.m_product_id = B.m_product_id)
                    LEFT JOIN m_wo_slit AS C ON (A.m_wo_slit_id = C.m_wo_slit_id)
                    LEFT JOIN m_prod_slit AS D ON (A.m_out_id = D.m_prod_slit_id)
                    LEFT JOIN m_inout_line AS E ON (A.m_in_id = E.m_inout_line_id)	
                    LEFT JOIN m_inout AS F ON (E.m_inout_id = F.m_inout_id)
                    LEFT JOIN c_order_line AS G ON (E.c_order_line_id = G.c_order_line_id)
                    LEFT JOIN c_order AS H ON (G.c_order_id = H.c_order_id)
                    LEFT JOIN m_stock_onhand AS I ON (A.m_product_id = I.m_product_id)
            
        WHERE 1=1

        GROUP BY A.no_lot, A.no_lot
        
        ";
    
    $cgx_rs_export = mysql_query($sql, $cgx_connection);
    while (($cgx_dt_export = mysql_fetch_array($cgx_rs_export, MYSQL_ASSOC)) !== FALSE) {
        echo "\"" . str_replace("\"", "\"\"", $cgx_dt_export['m_product_id']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['no_lot']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['no_coil']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['weight_2']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['m_inout_date']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['umur']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['od']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['thickness']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['spec']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['no_purchase_order']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['no_penerimaan_bahan_baku']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['no_wo_slitting']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['no_realisasi_wo']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['status']) . "\"";
        echo "\n";
    }
    mysql_free_result($cgx_rs_export);
    exit;
} 

if (@mysql_query($cgx_sql, $cgx_connection)) {
    $_SESSION[$GLOBALS['APP_ID']]['view.hcoil']['error'] = FALSE;
    $_SESSION[$GLOBALS['APP_ID']]['view.hcoil']['info'] = 'Data anda sudah berhasil diperbarui';
    if ($_REQUEST['mode'] == 'new') $cgx_new_id = $_REQUEST['data']['Array'];
} else {
    $_SESSION[$GLOBALS['APP_ID']]['view.hcoil']['error'] = mysql_error($cgx_connection);
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