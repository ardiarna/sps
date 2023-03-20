<?php

/*
 * Upload SO
 * Azwari Nugraha <nugraha@pt-gai.org>
 * Oct 31, 2013 9:37:14 AM
 */

require_once '../init.php';
require_once '../lib/PHPExcel/IOFactory.php';
require_once '../lib/trx.upload-so.php';

if (!has_privilege('trx.so-import')) die ('access denied');

if ($_REQUEST['step'] == 'reset') {
    @unlink($_SESSION[$APP_ID]['upload-so']['tmp']);
    unset($_SESSION[$APP_ID]['upload-so']);
    header("Location: ../module.php?m=trx.upload-so");
    exit;
} if ($_REQUEST['step'] == 1) {
    $tmp_path = $APP_BASE_DIR . '/tmp';
    @mkdir($tmp_path, 0777);
    $tmp_file = tempnam($tmp_path, user() . '-');
    if (move_uploaded_file($_FILES['so']['tmp_name'], $tmp_file)) {
        $_SESSION[$APP_ID]['upload-so']['name'] = $_FILES['so']['name'];
        $_SESSION[$APP_ID]['upload-so']['tmp'] = $tmp_file;
        $excel = PHPExcel_IOFactory::load($tmp_file);
        $_SESSION[$APP_ID]['upload-so']['sheets'] = $excel->getSheetNames();
        $_SESSION[$APP_ID]['upload-so']['step'] = 2;
        $_SESSION[$APP_ID]['upload-so']['org_id'] = $_REQUEST['app_org_id'];
    } else {
        unset($_SESSION[$APP_ID]['upload-so']);
        $_SESSION[$APP_ID]['upload-so']['step'] = 1;
        $_SESSION[$APP_ID]['upload-so']['error'] = 'Gagal meng-upload file';
    }
    header("Location: ../module.php?m=trx.upload-so");
    exit;
} if ($_REQUEST['step'] == 2) {
    $excel = PHPExcel_IOFactory::load($_SESSION[$APP_ID]['upload-so']['tmp']);
    $sheet = $excel->getSheet($_REQUEST['sheet']);
    unset($_SESSION[$APP_ID]['upload-so']['error']);
    unset($_SESSION[$APP_ID]['upload-so']['data']);
    if ($_REQUEST['mode'] == 'reselect') {
        unset($_SESSION[$APP_ID]['upload-so']['error']);
        $_SESSION[$APP_ID]['upload-so']['step'] = 2;
    } elseif (PHPExcel_Cell::columnIndexFromString($sheet->getHighestColumn()) < 12) {
        $_SESSION[$APP_ID]['upload-so']['error'] = "Data pada sheet <b>" . $_SESSION[$APP_ID]['upload-so']['sheets'][$_REQUEST['sheet']] . "</b> tidak memenuhi ketentuan SO";
        $_SESSION[$APP_ID]['upload-so']['step'] = 2;
    } else {
        $_SESSION[$APP_ID]['upload-so']['step'] = 3;
        $_SESSION[$APP_ID]['upload-so']['sheet'] = $_REQUEST['sheet'];
        $_SESSION[$APP_ID]['upload-so']['sheetname'] = $_SESSION[$APP_ID]['upload-so']['sheets'][$_REQUEST['sheet']];
        for ($row = 1; $row <= $sheet->getHighestRow(); $row++) {
            $so_id = $sheet->getCell('A' . $row)->getValue();
            if ($so_id == 'Sales Order') continue;
            $data[$so_id]['company-code'] = $sheet->getCell('B' . $row)->getValue();
            $data[$so_id]['company'] = $sheet->getCell('C' . $row)->getValue();
            $data[$so_id]['order-date'] = gmdate('Y-m-d H:i:s', PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell('D' . $row)->getValue()));
            $data[$so_id]['remark'] = $sheet->getCell('E' . $row)->getValue();
            $data[$so_id]['po_number'] = $sheet->getCell('F' . $row)->getValue();
            if (empty($data[$so_id]['line'])) $data[$so_id]['line'] = array();
            $line = array(
                gmdate('Y-m-d H:i:s', PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell('G' . $row)->getValue())),
                $sheet->getCell('H' . $row)->getValue(),
                $sheet->getCell('I' . $row)->getValue(),
                $sheet->getCell('J' . $row)->getValue(),
                $sheet->getCell('K' . $row)->getValue(),
                $sheet->getCell('L' . $row)->getValue(),
                $sheet->getCell('M' . $row)->getValue(),
                $sheet->getCell('N' . $row)->getValue()
            );
            $data[$so_id]['line'][] = $line;
        }
        $_SESSION[$APP_ID]['upload-so']['data'] = $data;
    }
    header("Location: ../module.php?m=trx.upload-so");
    exit;
} if ($_REQUEST['step'] == 4) {
    foreach ($_SESSION[$APP_ID]['upload-so']['data'] as $so_id => $data) {
        $c_bpartner_id = get_c_bpartner_id($data['company'], $data['company-code']);
        $sql_so = "INSERT INTO c_order (app_org_id, document_no, c_bpartner_id, order_date, reference_no, remark, m_transaction_type_id, create_date, create_user, update_date, update_user) " .
                "VALUES ('{$_SESSION[$APP_ID]['upload-so']['org_id']}', '{$so_id}', '{$c_bpartner_id}', '{$data['order-date']}', '{$data['po_number']}', '{$data['remark']}', 1, NOW(), '" . user() ."', NOW(), '" . user() ."')";
        if (mysql_query($sql_so, $APP_CONNECTION)) {
            $so_rec_id = mysql_insert_id($APP_CONNECTION);
            foreach ($data['line'] as $l) {
                $m_product_id = get_m_product_id($l[2], $l[4], $l[5], $l[6], $l[3], $l[7]);
                $sql_so_line = "INSERT INTO c_order_line (c_order_id, schedule_delivery_date, m_product_id, order_quantity, item_description, item_number) " .
                        "VALUES ('{$so_rec_id}', '{$l[0]}', '{$m_product_id}', '{$l[1]}', '{$l[7]}', '{$l[3]}')";
                mysql_query($sql_so_line, $APP_CONNECTION);
            }
            unset($_SESSION[$APP_ID]['upload-so']['data'][$so_id]['exec-status']);
        } else {
            $_SESSION[$APP_ID]['upload-so']['data'][$so_id]['exec-status'] = mysql_error($APP_CONNECTION);
        }
    }
    $_SESSION[$APP_ID]['upload-so']['step'] = 4;
    header("Location: ../module.php?m=trx.upload-so");
    exit;
}

?>