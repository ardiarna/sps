<?php

/*
 * Upload SO functions
 * Azwari Nugraha <nugraha@pt-gai.org>
 * Oct 31, 2013 9:47:38 PM
 */

require_once 'default.php';

function get_m_warehouse_id($warehouse_name, $org = 3) {
    global $APP_CONNECTION;
    $mwarehouse = npl_fetch_table(
            "SELECT * FROM m_warehouse WHERE warehouse_name = '{$warehouse_name}'");
    if (is_array($mwarehouse)) {
        return $mwarehouse['m_warehouse_id'];
    } else {
        mysql_query(
            "INSERT INTO m_warehouse (app_org_id, warehouse_code, warehouse_name) " .
            "VALUES ('{$org}', '" . mysql_escape_string($warehouse_code) . "','" . mysql_escape_string($warehouse_code) . "')",
            $APP_CONNECTION);
        $m_warehouse_id = mysql_insert_id($APP_CONNECTION);
        return $m_warehouse_id;
    }
}

//function get_c_bpartner_id($partner_code, $partner_name) {
function get_c_bpartner_id($partner_code) {
    global $APP_CONNECTION;
    $bpartner = npl_fetch_table(
            "SELECT * FROM c_bpartner WHERE partner_code = '{$partner_code}'");
    if (is_array($bpartner)) {
        return $bpartner['c_bpartner_id'];
    } else {
        // mysql_query(
        //     "INSERT INTO c_bpartner (partner_code, partner_name, partner_status) " .
        //     "VALUES ('{$partner_code}', '" . mysql_escape_string($partner_name) . "', 'C')",
        //     $APP_CONNECTION);
        // return mysql_insert_id($APP_CONNECTION);
        return 0;
    }
}

function get_m_machine_id($machine_name) {
    global $APP_CONNECTION;
    $machine = npl_fetch_table(
            "SELECT * FROM m_machine WHERE machine_name = '{$machine_name}'");
    if (is_array($machine)) {
        return $machine['m_machine_id'];
    } else {
        return 0;
    }
}

// function get_c_bpartner_id($partner_name, $partner_code = NULL) {
//     global $APP_CONNECTION;
//     $bpartner = npl_fetch_table(
//             "SELECT * FROM c_bpartner WHERE partner_name = '{$partner_name}'");
//     if (is_array($bpartner)) {
//         return $bpartner['c_bpartner_id'];
//     } else {
//         mysql_query(
//             "INSERT INTO c_bpartner (partner_code, partner_name, partner_status) " .
//             "VALUES (NULL, '" . mysql_escape_string($partner_name) . "', 'C')",
//             $APP_CONNECTION);
//         $c_bpartner_id = mysql_insert_id($APP_CONNECTION);
//         if (empty($partner_code)) $partner_code = 'L' . str_pad($c_bpartner_id, 5, '0', STR_PAD_LEFT);
//         mysql_query(
//             "UPDATE c_bpartner SET partner_code = '{$partner_code}' " .
//             "WHERE c_bpartner_id = '{$c_bpartner_id}'",
//             $APP_CONNECTION);
//         return $c_bpartner_id;
//     }
// }



function get_m_product_id($spec, $tebal, $od) {
    global $APP_CONNECTION;
    $product = npl_fetch_table(
            "SELECT * FROM m_product " .
            "WHERE od = '{$od}' " .
            "AND thickness = '{$tebal}' AND spec = '{$spec}'");
    if (is_array($product)) {
        return $product['m_product_id'];
    } else {
        return 0;
    }
}

function get_m_coil_id($no_coil, $no_lot, $fil_bantu) {
    global $APP_CONNECTION;
    $coil = npl_fetch_table("SELECT * FROM m_coil WHERE no_coil = '{$no_coil}' AND no_lot = '{$no_lot}'");
    if (is_array($coil)) {
        return $coil['m_coil_id'];
    } else {
        mysql_query(
            "INSERT INTO m_coil(m_product_id, no_coil, no_lot, status, fil_bantu) VALUES('0','{$no_coil}','{$no_lot}', 'O', '{$fil_bantu}')", $APP_CONNECTION);
        $m_coil = mysql_insert_id($APP_CONNECTION);
        return $m_coil;
    }
}

// function get_m_product_id($spec, $od, $tebal, $panjang) {
//     global $APP_CONNECTION;
//     $product = npl_fetch_table(
//             "SELECT * FROM m_product " .
//             "WHERE spec like '%{$spec}%' AND od = '{$od}' " .
//             "AND thickness = '{$tebal}' AND length = '{$panjang}'");
//     if (is_array($product)) {
//         return $product['m_product_id'];
//     } else {
//         return 0;
//     }
// }

// function get_m_product_id($kodeproduk) {
//     global $APP_CONNECTION;
//     $product = npl_fetch_table(
//             "SELECT * FROM m_product " .
//             "WHERE product_code = '{$kodeproduk}'");
//     if (is_array($product)) {
//         return $product['m_product_id'];
//     } else {
//         return 0;
//     }
// }

?>