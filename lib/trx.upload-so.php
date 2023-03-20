<?php

/*
 * Upload SO functions
 * Azwari Nugraha <nugraha@pt-gai.org>
 * Oct 31, 2013 9:47:38 PM
 */

require_once 'default.php';

function get_c_bpartner_id($partner_name, $partner_code = NULL) {
    global $APP_CONNECTION;
    $bpartner = npl_fetch_table(
            "SELECT * FROM c_bpartner WHERE partner_name = '{$partner_name}'");
    if (is_array($bpartner)) {
        return $bpartner['c_bpartner_id'];
    } else {
        mysql_query(
            "INSERT INTO c_bpartner (partner_code, partner_name, partner_status) " .
            "VALUES (NULL, '" . mysql_escape_string($partner_name) . "', 'C')",
            $APP_CONNECTION);
        $c_bpartner_id = mysql_insert_id($APP_CONNECTION);
        if (empty($partner_code)) $partner_code = 'L' . str_pad($c_bpartner_id, 5, '0', STR_PAD_LEFT);
        mysql_query(
            "UPDATE c_bpartner SET partner_code = '{$partner_code}' " .
            "WHERE c_bpartner_id = '{$c_bpartner_id}'",
            $APP_CONNECTION);
        return $c_bpartner_id;
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
        $product_name = build_product_name($spec, $od, $tebal, $panjang);
        mysql_query(
            "INSERT INTO m_product (product_code, spec, thickness, od, length, product_name, description) " .
            "VALUES ('{$item_code}', '{$spec}', '{$tebal}', '{$od}', '{$panjang}', '{$product_name}', '{$description}')",
            $APP_CONNECTION);
        return mysql_insert_id($APP_CONNECTION);
    }
}

?>