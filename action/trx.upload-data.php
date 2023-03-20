<?php

/*
 * Upload SO
 * Azwari Nugraha <nugraha@pt-gai.org>
 * Oct 31, 2013 9:37:14 AM
 */

require_once '../init.php';
require_once '../lib/PHPExcel/IOFactory.php';
require_once '../lib/trx.upload-data.php';

if (!has_privilege('trx.upload-data')) die ('access denied');

if ($_REQUEST['step'] == 'reset') {
    @unlink($_SESSION[$APP_ID]['upload-data']['tmp']);
    unset($_SESSION[$APP_ID]['upload-data']);
    header("Location: ../module.php?m=trx.upload-data");
    exit;
} if ($_REQUEST['step'] == 1) {
    $tmp_path = $APP_BASE_DIR . '/tmp';
    @mkdir($tmp_path, 0777);
    $tmp_file = tempnam($tmp_path, user() . '-');
    if (move_uploaded_file($_FILES['pi']['tmp_name'], $tmp_file)) {
        $_SESSION[$APP_ID]['upload-data']['name'] = $_FILES['pi']['name'];
        $_SESSION[$APP_ID]['upload-data']['tmp'] = $tmp_file;
        $excel = PHPExcel_IOFactory::load($tmp_file);
        $_SESSION[$APP_ID]['upload-data']['sheets'] = $excel->getSheetNames();
        $_SESSION[$APP_ID]['upload-data']['step'] = 2;
        $_SESSION[$APP_ID]['upload-data']['org_id'] = $_REQUEST['app_org_id'];
    } else {
        unset($_SESSION[$APP_ID]['upload-data']);
        $_SESSION[$APP_ID]['upload-data']['step'] = 1;
        $_SESSION[$APP_ID]['upload-data']['error'] = 'Gagal meng-upload file';
    }
    header("Location: ../module.php?m=trx.upload-data");
    exit;
} if ($_REQUEST['step'] == 2) {
    $excel = PHPExcel_IOFactory::load($_SESSION[$APP_ID]['upload-data']['tmp']);
    $sheet = $excel->getSheet($_REQUEST['sheet']);
    unset($_SESSION[$APP_ID]['upload-data']['error']);
    unset($_SESSION[$APP_ID]['upload-data']['data']);
    if ($_REQUEST['mode'] == 'reselect') {
        unset($_SESSION[$APP_ID]['upload-data']['error']);
        $_SESSION[$APP_ID]['upload-data']['step'] = 2;
    } elseif (PHPExcel_Cell::columnIndexFromString($sheet->getHighestColumn()) < 3) {
        $_SESSION[$APP_ID]['upload-data']['error'] = "Data pada sheet <b>" . $_SESSION[$APP_ID]['upload-data']['sheets'][$_REQUEST['sheet']] . "</b> tidak memenuhi ketentuan upload data";
        $_SESSION[$APP_ID]['upload-data']['step'] = 2;
    } else {
        $_SESSION[$APP_ID]['upload-data']['step'] = 3;
        $_SESSION[$APP_ID]['upload-data']['sheet'] = $_REQUEST['sheet'];
        $_SESSION[$APP_ID]['upload-data']['sheetname'] = $_SESSION[$APP_ID]['upload-data']['sheets'][$_REQUEST['sheet']];
        for ($row = 1; $row <= $sheet->getHighestRow(); $row++) {
            $pi_id = $sheet->getCell('A' . $row)->getValue();
            if ($pi_id == 'NO ID') continue;
            if (empty($data[$pi_id]['line'])) $data[$pi_id]['line'] = array();
            $line = array(
                $sheet->getCell('B' . $row)->getValue(),
                $sheet->getCell('C' . $row)->getValue(),
                $sheet->getCell('D' . $row)->getValue(),
                $sheet->getCell('E' . $row)->getValue(),
                $sheet->getCell('F' . $row)->getValue(),
                $sheet->getCell('G' . $row)->getValue(),
                $sheet->getCell('H' . $row)->getValue(),
                $sheet->getCell('I' . $row)->getValue()
            );
            $data[$pi_id]['line'][] = $line;
        }
        $_SESSION[$APP_ID]['upload-data']['data'] = $data;
    }
    header("Location: ../module.php?m=trx.upload-data");
    exit;
} if ($_REQUEST['step'] == 4) {
    foreach ($_SESSION[$APP_ID]['upload-data']['data'] as $pi_id => $data) {
            foreach ($data['line'] as $l) {
                $produk = get_m_product_id($l[1],$l[2],$l[3]);
                $coil = get_m_coil_id($l[4], $l[5], $l[0]);
                $tgl = '30-12-2014';

                $sql = "INSERT INTO m_coil_slit(m_coil_id, m_product_id, quantity, weight) VALUES('{$coil}','{$produk}','{$l[7]}','{$l[6]}')";
                $result = mysql_query($sql, $APP_CONNECTION);
                inout(org(), $produk, '283', $l[7]);
                stock_onhand(org(), user(), $produk, $tgl, $l[7], 0);
                $weight_total = $l[7] * $l[6] ;
                stock_weight(org(), user(), $produk, $tgl, $weight_total, 0);
            }   
            unset($_SESSION[$APP_ID]['upload-data']['data'][$pi_id]['exec-status']);         
    }       
    $_SESSION[$APP_ID]['upload-data']['step'] = 4;
    header("Location: ../module.php?m=trx.upload-data");
    exit;
}

?>