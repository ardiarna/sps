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

if ($_REQUEST['mode'] == 'update' || $_REQUEST['mode'] == 'new') if (!has_privilege('trx.wo_slit')) die ('akses ditolak');
if ($_REQUEST['mode'] == 'delete') if (!has_privilege('trx.wo_slit')) die ('akses ditolak');

if ($_REQUEST['mode'] == 'new') {
    
    $product_code = $_REQUEST['data']['product_code'];
    $kode = $_REQUEST['data']['kode'];
    $spec = $_REQUEST['data']['spec'];
    
    $substr_product_code = substr($product_code, 0, 2);
    
    if($substr_product_code == 'SP' ){
        $product_code_0 = 'Slit Coil Hitam CRC';
    }
    elseif($substr_product_code == 'SH'){
        $product_code_0 = 'Slit Coil Putih HRC';
    }
    elseif(substr_product_code == 'HC'){
        $product_code_0 = 'Plat Coil HRC';
    }
    elseif ($substr_product_code == 'CC') {
        $product_code_0 = 'Plat Coil CRC';
    }
    
    $width_0 = filter_var($_REQUEST['data']['od']);
    $thickness_0 = filter_var($_REQUEST['data']['thickness']);
    
    $width_1 = filter_var($width_0, FILTER_SANITIZE_NUMBER_INT);
    $thickness_1 = filter_var($thickness_0, FILTER_SANITIZE_NUMBER_INT);
    
    $width_2 = str_pad($width_1,5,0,STR_PAD_LEFT);
    $thickness_2 = str_pad($thickness_1,4,0,STR_PAD_LEFT);
    
    $width_3 = strlen($width_2);
    //echo 'ini width 2 :'.$width_2.' ini width 3 :'.$width_3.'<br>';
    
    if($width_3 > 5){
        $width_4 = substr($width_2, 0, 5);
    }
    else{
        $width_4 = $width_2;
    }
    
    //echo 'ini width 4 :'.$width_4;
    
    $product_code = $kode.$width_4.'.'.$thickness_2.'.'.'COIL';
    $product_name = $spec.' - '.$thickness_0.' - '.$width_0;
    $description  = $product_code_0.' - '.$spec; 
    $description2 = $width_0.' x '.$thickness_0.' x '.'COIL';
    
    //echo 'Item Number  : '.$product_code.'<br>';
    //echo 'Nama Produk  : '.$product_name.'<br>';
    //echo 'Description  : '.$description.'<br>';
    //echo 'Description2 : '.$description2.'<br>';
    
    //return;
    
    $cgx_sql = "INSERT INTO m_product (";
    $cgx_sql .= "app_org_id,product_code,spec,thickness,od,product_name,description,description_2,minimum_qty,purchase,sale,category,active";
    $cgx_sql .= ") values (";
    $cgx_sql .= "'{$_REQUEST['data']['app_org_id']}'";
    //$cgx_sql .= ",'" . mysql_escape_string($_REQUEST['data']['product_code']) . "'";
    $cgx_sql .= ",'$product_code'";
    $cgx_sql .= ",'" . mysql_escape_string($_REQUEST['data']['spec']) . "'";
    $cgx_sql .= ",'{$_REQUEST['data']['thickness']}'";
    $cgx_sql .= ",'{$_REQUEST['data']['od']}'";
    //$cgx_sql .= ",'" . mysql_escape_string($_REQUEST['data']['product_name']) . "'";
    $cgx_sql .= ",'$product_name'";
    //$cgx_sql .= ",'" . mysql_escape_string($_REQUEST['data']['description']) . "'";
    $cgx_sql .= ",'$description'";
    //$cgx_sql .= ",'" . mysql_escape_string($_REQUEST['data']['description_2']) . "'";
    $cgx_sql .= ",'$description2'";
    //$cgx_sql .= ",'{$_REQUEST['data']['minimum_qty']}'";
    $cgx_sql .= ",'0'";
    $cgx_sql .= ",'" . mysql_escape_string($_REQUEST['data']['purchase']) . "'";
    $cgx_sql .= ",'" . mysql_escape_string($_REQUEST['data']['sale']) . "'";
    $cgx_sql .= ",'S'";
    //$cgx_sql .= ",'" . mysql_escape_string($_REQUEST['data']['active']) . "'";
    $cgx_sql .= ",'Y'";
    $cgx_sql .= ")";
    
    //print_r($cgx_sql);
    //return;
    
}

if (@mysql_query($cgx_sql, $cgx_connection)) {
    $_SESSION[$GLOBALS['APP_ID']]['form.master.product_c']['error'] = FALSE;
    $_SESSION[$GLOBALS['APP_ID']]['form.master.product_c']['info'] = 'Data anda sudah berhasil diperbarui';
    if ($_REQUEST['mode'] == 'new') $cgx_new_id = mysql_insert_id($cgx_connection);
} else {
    $_SESSION[$GLOBALS['APP_ID']]['form.master.product_c']['error'] = mysql_error($cgx_connection);
}

if ($_REQUEST['mode'] == 'new') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']) . "&id={$cgx_new_id}");
}
exit;

?>