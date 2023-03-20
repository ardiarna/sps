<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 03/01/2014 20:12:20
 */

require_once '../init.php';

if (!authenticated()) {
    header("Location: ../index.php");
    exit;
}

if ($_REQUEST['mode'] == 'update' || $_REQUEST['mode'] == 'new') if (!has_privilege('master.code_cust')) die ('akses ditolak');
if ($_REQUEST['mode'] == 'delete') if (!has_privilege('master.code_cust')) die ('akses ditolak');

if ($_REQUEST['mode'] == 'export-all') {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=\"master.product_c-" . date("Y-m-d") . ".csv\"");
    echo "\"ID\",\"Organization\",\"Kode Produk\",\"Spec\",\"Thickness\",\"Width\",\"Nama Produk\",\"Minimum Quantity\",\"Purchase\",\"Sale\",\"Kategori\",\"Active\"\n";
    $cgx_rs_export = mysql_query("SELECT m_product.*, organization FROM m_product JOIN app_org USING (app_org_id) WHERE category = 'R' OR category = 'C'", $cgx_connection);
    while (($cgx_dt_export = mysql_fetch_array($cgx_rs_export, MYSQL_ASSOC)) !== FALSE) {
        echo "\"" . str_replace("\"", "\"\"", $cgx_dt_export['m_product_id']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['organization']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['product_code']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['spec']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['thickness']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['od']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['product_name']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['minimum_qty']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['purchase']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['sale']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['category']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['active']) . "\"";
        echo "\n";
    }
    mysql_free_result($cgx_rs_export);
    exit;
} elseif ($_REQUEST['mode'] == 'update') {
    
    $cgx_sql = "UPDATE m_code_prod_lp SET";
    $cgx_sql .= " customer_code = '{$_REQUEST['data']['customer_code']}'";
    $cgx_sql .= ", od = '{$_REQUEST['data']['od']}'";
    $cgx_sql .= ", thickness = '{$_REQUEST['data']['thickness']}'";
    $cgx_sql .= ", length = '{$_REQUEST['data']['length']}'";
    $cgx_sql .= ", spec = '{$_REQUEST['data']['spec']}'";
    $cgx_sql .= " WHERE";
    $cgx_sql .= " id = '{$_REQUEST['pkey']['id']}'";
    
    //print_r($cgx_sql);
    //exit();
    
} elseif ($_REQUEST['mode'] == 'new') {
    
    $cgx_sql = "INSERT INTO m_code_prod_lp (";
    $cgx_sql .= "customer_code,od,thickness,length,spec";
    $cgx_sql .= ") values (";
    $cgx_sql .= "'{$_REQUEST['data']['customer_code']}'";
    $cgx_sql .= ",'{$_REQUEST['data']['od']}'";
    $cgx_sql .= ",'{$_REQUEST['data']['thickness']}'";
    $cgx_sql .= ",'{$_REQUEST['data']['length']}'";
    $cgx_sql .= ",'{$_REQUEST['data']['spec']}'";
    $cgx_sql .= ")";
    
    //print_r($cgx_sql);
    //exit();
    
} elseif ($_REQUEST['mode'] == 'delete') {
    $cgx_sql = "DELETE FROM m_code_prod_lp ";
    $cgx_sql .= " WHERE";
    $cgx_sql .= " id = '{$_REQUEST['pkey']['id']}'";
}

if (@mysql_query($cgx_sql, $cgx_connection)) {
    $_SESSION[$GLOBALS['APP_ID']]['master.code_cust']['error'] = FALSE;
    $_SESSION[$GLOBALS['APP_ID']]['master.code_cust']['info'] = 'Data anda sudah berhasil diperbarui';
    if ($_REQUEST['mode'] == 'new') $cgx_new_id = mysql_insert_id($cgx_connection);
} else {
    $_SESSION[$GLOBALS['APP_ID']]['master.code_cust']['error'] = mysql_error($cgx_connection);
}

if ($_REQUEST['mode'] == 'update') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']) . "&pkey[id]={$_REQUEST['pkey']['id']}");
} elseif ($_REQUEST['mode'] == 'new') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']) . "&pkey[id]={$cgx_new_id}");
} elseif ($_REQUEST['mode'] == 'delete') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']));
}
exit;

?>