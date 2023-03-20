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

if ($_REQUEST['mode'] == 'update' || $_REQUEST['mode'] == 'new') if (!has_privilege('master.product_c')) die ('akses ditolak');
if ($_REQUEST['mode'] == 'delete') if (!has_privilege('master.product_c')) die ('akses ditolak');

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
    $cgx_sql = "UPDATE m_product SET";
    $cgx_sql .= " app_org_id = '{$_REQUEST['data']['app_org_id']}'";
    $cgx_sql .= ", c_bpartner_id = '{$_REQUEST['data']['c_bpartner_id']}'";
    $cgx_sql .= ", product_code = '" . mysql_escape_string($_REQUEST['data']['product_code']) . "'";//character khusus jika ada tanda kutip supaya bisa masuk ke database
    $cgx_sql .= ", spec = '" . mysql_escape_string($_REQUEST['data']['spec']) . "'";
    $cgx_sql .= ", thickness = '{$_REQUEST['data']['thickness']}'";
    $cgx_sql .= ", od = '{$_REQUEST['data']['od']}'";
    $cgx_sql .= ", length = '{$_REQUEST['data']['length']}'";
    $cgx_sql .= ", product_name = '" . mysql_escape_string($_REQUEST['data']['product_name']) . "'";
    $cgx_sql .= ", minimum_qty = '{$_REQUEST['data']['minimum_qty']}'";
    $cgx_sql .= ", purchase = '" . mysql_escape_string($_REQUEST['data']['purchase']) . "'";
    $cgx_sql .= ", sale = '" . mysql_escape_string($_REQUEST['data']['sale']) . "'";
    $cgx_sql .= ", category = '" . mysql_escape_string($_REQUEST['data']['category']). "'";
    $cgx_sql .= ", active = '" . mysql_escape_string($_REQUEST['data']['active']) . "'";
    $cgx_sql .= " WHERE";
    $cgx_sql .= " m_product_id = '{$_REQUEST['pkey']['m_product_id']}'";
} elseif ($_REQUEST['mode'] == 'new') {
    $cgx_sql = "INSERT INTO m_product (";
    $cgx_sql .= "app_org_id,c_bpartner_id,product_code,spec,thickness,od,length,product_name,minimum_qty,purchase,sale,category,active";
    $cgx_sql .= ") values (";
    $cgx_sql .= "'{$_REQUEST['data']['app_org_id']}'";
    $cgx_sql .= ",'{$_REQUEST['data']['c_bpartner_id']}'";
    $cgx_sql .= ",'" . mysql_escape_string($_REQUEST['data']['product_code']) . "'";
    $cgx_sql .= ",'" . mysql_escape_string($_REQUEST['data']['spec']) . "'";
    $cgx_sql .= ",'{$_REQUEST['data']['thickness']}'";
    $cgx_sql .= ",'{$_REQUEST['data']['od']}'";
    $cgx_sql .= ",'{$_REQUEST['data']['length']}'";
    $cgx_sql .= ",'" . mysql_escape_string($_REQUEST['data']['product_name']) . "'";
    $cgx_sql .= ",'{$_REQUEST['data']['minimum_qty']}'";
    $cgx_sql .= ",'" . mysql_escape_string($_REQUEST['data']['purchase']) . "'";
    $cgx_sql .= ",'" . mysql_escape_string($_REQUEST['data']['sale']) . "'";
    $cgx_sql .= ",'" . mysql_escape_string($_REQUEST['data']['category']) . "'";
    $cgx_sql .= ",'" . mysql_escape_string($_REQUEST['data']['active']) . "'";
    $cgx_sql .= ")";
} elseif ($_REQUEST['mode'] == 'delete') {
    $cgx_sql = "DELETE FROM m_product ";
    $cgx_sql .= " WHERE";
    $cgx_sql .= " m_product_id = '{$_REQUEST['pkey']['m_product_id']}'";
}

if (@mysql_query($cgx_sql, $cgx_connection)) {
    $_SESSION[$GLOBALS['APP_ID']]['master.product_c']['error'] = FALSE;
    $_SESSION[$GLOBALS['APP_ID']]['master.product_c']['info'] = 'Data anda sudah berhasil diperbarui';
    if ($_REQUEST['mode'] == 'new') $cgx_new_id = mysql_insert_id($cgx_connection);
} else {
    $_SESSION[$GLOBALS['APP_ID']]['master.product_c']['error'] = mysql_error($cgx_connection);
}

if ($_REQUEST['mode'] == 'update') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']) . "&pkey[m_product_id]={$_REQUEST['pkey']['m_product_id']}");
} elseif ($_REQUEST['mode'] == 'new') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']) . "&pkey[m_product_id]={$cgx_new_id}");
} elseif ($_REQUEST['mode'] == 'delete') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']));
}
exit;

?>