<?php

/*
 * Upload PO
 * Azwari Nugraha <nugraha@pt-gai.org>
 * Oct 31, 2013 9:37:14 AM
 */

require_once '../init.php';
require_once '../lib/PHPExcel/IOFactory.php';
require_once '../lib/trx.upload-po.php';

if (!has_privilege('trx.upload-po')) die ('access denied');

if ($_REQUEST['step'] == 'reset') {
    @unlink($_SESSION[$APP_ID]['upload-po']['tmp']);
    unset($_SESSION[$APP_ID]['upload-po']);
    header("Location: ../module.php?m=trx.upload-po");
    exit;
} if ($_REQUEST['step'] == 1) {
    $tmp_path = $APP_BASE_DIR . '/tmp';
    @mkdir($tmp_path, 0777);
    $tmp_file = tempnam($tmp_path, user() . '-');
    if (move_uploaded_file($_FILES['po']['tmp_name'], $tmp_file)) {
        $_SESSION[$APP_ID]['upload-po']['name'] = $_FILES['po']['name'];
        $_SESSION[$APP_ID]['upload-po']['tmp'] = $tmp_file;
        $excel = PHPExcel_IOFactory::load($tmp_file);
        $_SESSION[$APP_ID]['upload-po']['sheets'] = $excel->getSheetNames();
        $_SESSION[$APP_ID]['upload-po']['step'] = 2;
        $_SESSION[$APP_ID]['upload-po']['org_id'] = $_REQUEST['app_org_id'];
    } else {
        unset($_SESSION[$APP_ID]['upload-po']);
        $_SESSION[$APP_ID]['upload-po']['step'] = 1;
        $_SESSION[$APP_ID]['upload-po']['error'] = 'Gagal meng-upload file';
    }
    header("Location: ../module.php?m=trx.upload-po");
    exit;
} if ($_REQUEST['step'] == 2) {
    $excel = PHPExcel_IOFactory::load($_SESSION[$APP_ID]['upload-po']['tmp']);
    $sheet = $excel->getSheet($_REQUEST['sheet']);
    unset($_SESSION[$APP_ID]['upload-po']['error']);
    unset($_SESSION[$APP_ID]['upload-po']['data']);
    if ($_REQUEST['mode'] == 'reselect') {
        unset($_SESSION[$APP_ID]['upload-po']['error']);
        $_SESSION[$APP_ID]['upload-po']['step'] = 2;
    } elseif (PHPExcel_Cell::columnIndexFromString($sheet->getHighestColumn()) < 12) {
        $_SESSION[$APP_ID]['upload-po']['error'] = "Data pada sheet <b>" . $_SESSION[$APP_ID]['upload-po']['sheets'][$_REQUEST['sheet']] . "</b> tidak memenuhi ketentuan po";
        $_SESSION[$APP_ID]['upload-po']['step'] = 2;
    } else {
        $_SESSION[$APP_ID]['upload-po']['step'] = 3;
        $_SESSION[$APP_ID]['upload-po']['sheet'] = $_REQUEST['sheet'];
        $_SESSION[$APP_ID]['upload-po']['sheetname'] = $_SESSION[$APP_ID]['upload-po']['sheets'][$_REQUEST['sheet']];
        for ($row = 1; $row <= $sheet->getHighestRow(); $row++) {
            $po_id = $sheet->getCell('A' . $row)->getValue();
            if ($po_id == 'Purchase Order') continue;
            $data[$po_id]['remark'] = $sheet->getCell('B' . $row)->getValue();
            $data[$po_id]['po_number'] = $sheet->getCell('C' . $row)->getValue();
            $data[$po_id]['order-date'] = gmdate('Y-m-d H:i:s', PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell('D' . $row)->getValue()));
            $data[$po_id]['company-code'] = $sheet->getCell('E' . $row)->getValue();
            $data[$po_id]['company'] = $sheet->getCell('F' . $row)->getValue();
            if (empty($data[$po_id]['line'])) $data[$po_id]['line'] = array();
            $line = array(
                gmdate('Y-m-d H:i:s', PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell('G' . $row)->getValue())),   // schedule date 0
                $sheet->getCell('H' . $row)->getValue(),    //order_quantity    1
                $sheet->getCell('I' . $row)->getValue(),    //spec              2
                $sheet->getCell('J' . $row)->getValue(),    //item_number       3
                $sheet->getCell('K' . $row)->getValue(),    //lebar             4
                $sheet->getCell('L' . $row)->getValue(),    //tebal             5
                $sheet->getCell('M' . $row)->getValue(),    //decr              6
                $sheet->getCell('N' . $row)->getValue()     //decr 2            7
            );
            $data[$po_id]['line'][] = $line;
        }
        $_SESSION[$APP_ID]['upload-po']['data'] = $data;
    }
    header("Location: ../module.php?m=trx.upload-po");
    exit;
} if ($_REQUEST['step'] == 4) {
    foreach ($_SESSION[$APP_ID]['upload-po']['data'] as $po_id => $data) {
        $c_bpartner_id = get_c_bpartner_id($data['company'], $data['company-code']);
        $sql_po = "INSERT INTO c_order (app_org_id, document_no, c_bpartner_id, order_date, reference_no, remark, m_transaction_type_id, create_date, create_user, update_date, update_user) " .
                "VALUES ('{$_SESSION[$APP_ID]['upload-po']['org_id']}', '{$po_id}', '{$c_bpartner_id}', '{$data['order-date']}', '{$data['po_number']}', '{$data['remark']}', 2, NOW(), '" . user() ."', NOW(), '" . user() ."')";
        if (mysql_query($sql_po, $APP_CONNECTION)) {
            $po_rec_id = mysql_insert_id($APP_CONNECTION);
            foreach ($data['line'] as $l) {
                $m_product_id = get_m_product_id($l[2], $l[4], $l[5], $l[3], $l[7], $l[6]);
                $sql_po_line = "INSERT INTO c_order_line (c_order_id, schedule_delivery_date, m_product_id, order_weight, item_description, item_number) " .
                        "VALUES ('{$po_rec_id}', '{$l[0]}', '{$m_product_id}', '{$l[1]}', '{$l[6]}', '{$l[3]}')";
                mysql_query($sql_po_line, $APP_CONNECTION);
            }
            unset($_SESSION[$APP_ID]['upload-po']['data'][$po_id]['exec-status']);
        } else {
            $_SESSION[$APP_ID]['upload-po']['data'][$po_id]['exec-status'] = mysql_error($APP_CONNECTION);
        }
    }
    $_SESSION[$APP_ID]['upload-po']['step'] = 4;
    header("Location: ../module.php?m=trx.upload-po");
    exit;
}

?>