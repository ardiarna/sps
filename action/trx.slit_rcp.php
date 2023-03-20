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

if ($_REQUEST['mode'] == 'update' || $_REQUEST['mode'] == 'new') if (!has_privilege('trx.slit_rcp')) die ('akses ditolak');
if ($_REQUEST['mode'] == 'delete') if (!has_privilege('trx.slit_rcp')) die ('akses ditolak');

$m_coil_slit_id = $_REQUEST['m_coil_slit_id'];
$m_product_id = $_REQUEST['m_product_id'];
$no_coil = mysql_escape_string($_REQUEST['no_coil']);
$no_lot = mysql_escape_string($_REQUEST['no_lot']);
$quantity = $_REQUEST['quantity'];
$weight = $_REQUEST['weight'];

if ($_REQUEST['mode'] == 'update') {
    $awal = cgx_fetch_table("SELECT * FROM m_coil_slit WHERE m_coil_id = '{$_REQUEST['m_coil_slit_id']}'");
    mysql_query("UPDATE m_coil SET no_coil = '{$no_coil}', no_lot = '{$no_lot}' WHERE m_coil_id = '{$_REQUEST['m_coil_id']}'", $APP_CONNECTION);
    $cgx_sql = "UPDATE m_coil_slit SET m_product_id = '{$m_product_id}', quantity = '{$quantity}', weight = '{$weight}' WHERE m_coil_slit_id = '{$_REQUEST['m_coil_slit_id']}'";
    if (mysql_query($cgx_sql, $cgx_connection)) {
        $_SESSION[$GLOBALS['APP_ID']]['trx.slit_rcp']['error'] = FALSE;
        $quantity_min = $awal['quantity'] * -1 ;
        inout(org(), $awal['m_product_id'], '283', $quantity_min);
        inout(org(), $m_product_id, '283', $quantity);
        stock_onhand(org(), user(), $awal['m_product_id'], NOW(), $quantity_min, 0);
        stock_onhand(org(), user(), $m_product_id, NOW(), $quantity, 0);
        $weight_all_min = ($awal['weight'] * $awal['quantity']) * -1 ;
        stock_weight(org(), user(), $awal['m_product_id'], NOW(), $weight_all_min, 0);
        $weight_all = $weight * $quantity ;
        stock_weight(org(), user(), $m_product_id, NOW(), $weight_all, 0);
        $_SESSION[$GLOBALS['APP_ID']]['trx.slit_rcp']['info'] = 'Data anda sudah berhasil diperbaharui';
    } else {
        $_SESSION[$GLOBALS['APP_ID']]['trx.slit_rcp']['error'] = mysql_error($cgx_connection);
    }
} elseif ($_REQUEST['mode'] == 'new') {
    $coil = cgx_fetch_table("SELECT * FROM m_coil WHERE no_coil = '{$no_coil}' AND no_lot = '{$no_lot}'");
     if (is_array($coil)) {
        $m_coil_id = $coil['m_coil_id'];
    } else {
        mysql_query("INSERT INTO m_coil(m_product_id, no_coil, no_lot, status) VALUES('0','{$no_coil}','{$no_lot}', 'O')", $APP_CONNECTION);
        $m_coil_id = mysql_insert_id($APP_CONNECTION);
    }
    $cgx_sql = "INSERT INTO m_coil_slit(m_coil_id, m_product_id, quantity, weight) VALUES('{$m_coil_id}', '{$m_product_id}', '{$quantity}', '{$weight}')";    
    if (mysql_query($cgx_sql, $cgx_connection)) {
        $_SESSION[$GLOBALS['APP_ID']]['trx.slit_rcp']['error'] = FALSE;
        $cgx_new_id = mysql_insert_id($cgx_connection);
        inout(org(), $m_product_id, '283', $quantity);
        stock_onhand(org(), user(), $m_product_id, date(), $quantity, 0);
        $weight_all = $weight * $quantity ;
        stock_weight(org(), user(), $m_product_id, date(), $weight_all, 0);
        $_SESSION[$GLOBALS['APP_ID']]['trx.slit_rcp']['info'] = 'Data anda sudah berhasil disimpan';
    } else {
        $_SESSION[$GLOBALS['APP_ID']]['trx.slit_rcp']['error'] = mysql_error($cgx_connection);
    }
} elseif ($_REQUEST['mode'] == 'delete') {
    $cgx_sql = "DELETE FROM m_product ";
    $cgx_sql .= " WHERE";
    $cgx_sql .= " m_product_id = '{$_REQUEST['pkey']['m_product_id']}'";
}

if ($_REQUEST['mode'] == 'update') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']) . "&pkey[m_coil_slit_id]={$_REQUEST['pkey']['m_coil_slit_id']}");
} elseif ($_REQUEST['mode'] == 'new') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']) . "&pkey[m_coil_slit_id]={$cgx_new_id}");
} elseif ($_REQUEST['mode'] == 'delete') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']));
}
exit;

?>