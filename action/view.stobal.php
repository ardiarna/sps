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
    echo "\"ID Product\",\"Item Number\",\"Nama Barang\",\"Kode Customer\",\"Nama Customer\",\"Spec\",\"OD\",\"Thickness\",\"Length\",\"Tanggal\",\"Stok Awal\",\"Masuk\",\"Keluar\",\"Balance (Pcs)\",\"Weight (Kg)\"\n";
    $sql = "SELECT mso1.*, mp.product_code, mp.product_name, mp.spec, mp.od, mp.Thickness, mp.Length, cb.partner_code, cb.partner_name,
            ((((mp.od - mp.thickness) * mp.thickness * 0.02466 * mp.length) / 1000) * balance_quantity) as weight FROM m_stock_onhand mso1 
                INNER JOIN (
                    SELECT m_product_id, MAX(m_inout_date) m_inout_date
                    FROM m_stock_onhand WHERE app_org_id = '". org() ."' GROUP BY m_product_id
                ) mso2
            ON(mso1.m_product_id = mso2.m_product_id AND mso1.m_inout_date = mso2.m_inout_date)
            JOIN m_product mp ON(mso1.m_product_id = mp.m_product_id)
            LEFT JOIN c_bpartner cb ON(mp.c_bpartner_id=cb.c_bpartner_id) 
            WHERE mso1.app_org_id='". org() ."'";
    $cgx_rs_export = mysql_query($sql, $cgx_connection);
    while (($cgx_dt_export = mysql_fetch_array($cgx_rs_export, MYSQL_ASSOC)) !== FALSE) {
        echo "\"" . str_replace("\"", "\"\"", $cgx_dt_export['m_product_id']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['product_code']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['product_name']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['partner_code']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['partner_name']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['spec']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['od']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['Thickness']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['Length']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['m_inout_date']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['prev_quantity']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['in_quantity']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['out_quantity']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['balance_quantity']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['weight']) . "\"";
        echo "\n";
    }
    mysql_free_result($cgx_rs_export);
    exit;
} elseif ($_REQUEST['mode'] == 'export-all-d') {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=\"stock_balance[". $_REQUEST['pc'] ."]-" . date("Y-m-d") . ".csv\"");
    echo "\"Item Number\",\"Nama Barang\",\"Spec\",\"OD\",\"Thickness\",\"Length\",\"Tanggal\",\"Stok Awal\",\"Masuk\",\"Keluar\",\"Balance\"\n";
    $sql = "SELECT mso.*, mp.product_code, mp.product_name, mp.spec, mp.od, mp.Thickness, mp.Length FROM m_stock_onhand mso JOIN m_product mp ON(mso.m_product_id = mp.m_product_id)
            WHERE mso.m_product_id ='{$_REQUEST['pc']}' AND mso.app_org_id = '". org() ."'  
            ORDER BY mso.m_inout_date";
    $cgx_rs_export = mysql_query($sql, $cgx_connection);
    while (($cgx_dt_export = mysql_fetch_array($cgx_rs_export, MYSQL_ASSOC)) !== FALSE) {
        echo "\"" . str_replace("\"", "\"\"", $cgx_dt_export['product_code']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['product_name']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['spec']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['od']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['Thickness']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['Length']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['m_inout_date']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['prev_quantity']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['in_quantity']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['out_quantity']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['balance_quantity']) . "\"";
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
    $_SESSION[$GLOBALS['APP_ID']]['view.stobal']['error'] = FALSE;
    $_SESSION[$GLOBALS['APP_ID']]['view.stobal']['info'] = 'Data anda sudah berhasil diperbarui';
    if ($_REQUEST['mode'] == 'new') $cgx_new_id = $_REQUEST['data']['Array'];
} else {
    $_SESSION[$GLOBALS['APP_ID']]['view.stobal']['error'] = mysql_error($cgx_connection);
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