<?php

/*
 * Upload SO functions
 * Azwari Nugraha <nugraha@pt-gai.org>
 * Oct 31, 2013 9:47:38 PM
 */

require_once 'default.php';

function get_m_warehouse_id($warehouse_code) {
    global $APP_CONNECTION;
    $mwarehouse = npl_fetch_table(
            "SELECT * FROM m_warehouse WHERE warehouse_code like '%{$warehouse_code}%'");
    if (is_array($mwarehouse)) {
        return $mwarehouse['m_warehouse_id'];
    } else {
        return 0;
    }
}

function get_c_bpartner_id($partner_code) {
    global $APP_CONNECTION;
    $mpartner = npl_fetch_table(
            "SELECT * FROM c_bpartner WHERE partner_code like '%{$partner_code}%'");
    if (is_array($mpartner)) {
        return $mpartner['c_bpartner_id'];
    } else {
        return 0;
    }
}

function get_c_order_id($remark) {
    global $APP_CONNECTION;
    $corder = npl_fetch_table(
            "SELECT * FROM c_order WHERE remark like '%{$remark}%'");
    if (is_array($corder)) {
        return $corder['c_order_id'];
    } else {
        return 0;
    }
}

function build_product_name($spec, $od, $tebal, $panjang) {
    $ret = $spec;
    $ret .= '-' . number_format($od, 2);
    $ret .= '-' . number_format($tebal, 2);
    $ret .= '-' . number_format($panjang, 2);
    return $ret;
}

function get_m_product_id($spec, $od, $tebal, $panjang, $item_code, $description = NULL) {
    global $APP_CONNECTION;
    $product = npl_fetch_table(
            "SELECT * FROM m_product " .
            "WHERE spec = '{$spec}' AND od = '{$od}' " .
            "AND thickness = '{$tebal}' AND length = '{$panjang}'");
    if (is_array($product)) {
        return $product['m_product_id'];
    } else {
        return 0;
    }
}

// function get_m_product_id($spec, $od, $tebal, $panjang, $item_code, $description = NULL) {
//     global $APP_CONNECTION;
//     $product = npl_fetch_table(
//             "SELECT * FROM m_product WHERE product_code like '%{$item_code}%'");
//     if (is_array($product)) {
//         return $product['m_product_id'];
//     } else {
//         return 0;
//     }
// }

function get_data_1($org_id,$tgl,$product) {
    global $APP_CONNECTION;
    $tglymd = cgx_dmy2ymd($tgl);
    $sql_data = npl_fetch_table(
            "SELECT mso1.* FROM m_stock_onhand mso1 INNER JOIN (
            SELECT m_product_id, MAX(m_inout_date) m_inout_date FROM m_stock_onhand 
            WHERE m_inout_date <= '{$tglymd}' AND app_org_id = '{$org_id}' GROUP BY m_product_id) mso2 
            ON(mso1.m_product_id = mso2.m_product_id AND mso1.m_inout_date = mso2.m_inout_date) 
            WHERE mso1.m_product_id = '{$product}' AND mso1.app_org_id = '{$org_id}'");
    if (is_array($sql_data)) {
        return $sql_data['balance_quantity'];
    } else {
        return 0;
    }
}

function get_data_2($product,$org_id) {
    global $APP_CONNECTION;
    $sql_data = npl_fetch_table(
            "SELECT mso1.* FROM m_stock_onhand mso1 INNER JOIN (
            SELECT m_product_id, MAX(m_inout_date) m_inout_date FROM m_stock_onhand 
            WHERE app_org_id = '{$org_id}' GROUP BY m_product_id) mso2 
            ON(mso1.m_product_id = mso2.m_product_id AND mso1.m_inout_date = mso2.m_inout_date) 
            WHERE mso1.m_product_id = '{$product}' AND mso1.app_org_id = '{$org_id}'");
    if (is_array($sql_data)) {
        return $sql_data['balance_quantity'];
    } else {
        return 0;
    }
}

?>