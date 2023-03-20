<?php

/*
 * Upload SO
 * Azwari Nugraha <nugraha@pt-gai.org>
 * Oct 31, 2013 9:37:14 AM
 */

require_once '../init.php';
require_once '../lib/PHPExcel/IOFactory.php';
require_once '../lib/trx.upload-pi.php';

if (!has_privilege('trx.pi-import')) die ('access denied');

if ($_REQUEST['step'] == 'reset') {
    @unlink($_SESSION[$APP_ID]['upload-pi']['tmp']);
    unset($_SESSION[$APP_ID]['upload-pi']);
    header("Location: ../module.php?m=trx.upload-pi");
    exit;
} if ($_REQUEST['step'] == 1) {
    $tmp_path = $APP_BASE_DIR . '/tmp';
    @mkdir($tmp_path, 0777);
    $tmp_file = tempnam($tmp_path, user() . '-');
    if (move_uploaded_file($_FILES['pi']['tmp_name'], $tmp_file)) {
        $_SESSION[$APP_ID]['upload-pi']['name'] = $_FILES['pi']['name'];
        $_SESSION[$APP_ID]['upload-pi']['tmp'] = $tmp_file;
        $excel = PHPExcel_IOFactory::load($tmp_file);
        $_SESSION[$APP_ID]['upload-pi']['sheets'] = $excel->getSheetNames();
        $_SESSION[$APP_ID]['upload-pi']['step'] = 2;
        $_SESSION[$APP_ID]['upload-pi']['org_id'] = $_REQUEST['app_org_id'];
    } else {
        unset($_SESSION[$APP_ID]['upload-pi']);
        $_SESSION[$APP_ID]['upload-pi']['step'] = 1;
        $_SESSION[$APP_ID]['upload-pi']['error'] = 'Gagal meng-upload file';
    }
    header("Location: ../module.php?m=trx.upload-pi");
    exit;
} if ($_REQUEST['step'] == 2) {
    $excel = PHPExcel_IOFactory::load($_SESSION[$APP_ID]['upload-pi']['tmp']);
    $sheet = $excel->getSheet($_REQUEST['sheet']);
    unset($_SESSION[$APP_ID]['upload-pi']['error']);
    unset($_SESSION[$APP_ID]['upload-pi']['data']);
    if ($_REQUEST['mode'] == 'reselect') {
        unset($_SESSION[$APP_ID]['upload-pi']['error']);
        $_SESSION[$APP_ID]['upload-pi']['step'] = 2;
    } elseif (PHPExcel_Cell::columnIndexFromString($sheet->getHighestColumn()) < 7) {
        $_SESSION[$APP_ID]['upload-pi']['error'] = "Data pada sheet <b>" . $_SESSION[$APP_ID]['upload-pi']['sheets'][$_REQUEST['sheet']] . "</b> tidak memenuhi ketentuan Physical Inventory";
        $_SESSION[$APP_ID]['upload-pi']['step'] = 2;
    } else {
        $_SESSION[$APP_ID]['upload-pi']['step'] = 3;
        $_SESSION[$APP_ID]['upload-pi']['sheet'] = $_REQUEST['sheet'];
        $_SESSION[$APP_ID]['upload-pi']['sheetname'] = $_SESSION[$APP_ID]['upload-pi']['sheets'][$_REQUEST['sheet']];
        for ($row = 1; $row <= $sheet->getHighestRow(); $row++) {
            $pi_id = $sheet->getCell('A' . $row)->getValue();  // nomor phisical inventory
            if ($pi_id == 'Physical Inventory') continue;
            $data[$pi_id]['pi-date'] = gmdate('d-m-Y', PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell('B' . $row)->getValue()));  //tgl phisical inventory
            $data[$pi_id]['customer'] = $sheet->getCell('C' . $row)->getValue(); // kode customer
            if (empty($data[$pi_id]['line'])) $data[$pi_id]['line'] = array(); 
            $line = array(
                $sheet->getCell('D' . $row)->getValue(),  // item number        0
                $sheet->getCell('E' . $row)->getValue(),  // spec               1
                $sheet->getCell('F' . $row)->getValue(),  // od                 2
                $sheet->getCell('G' . $row)->getValue(),  // tebal              3
                $sheet->getCell('H' . $row)->getValue(),  // panjang            4
                $sheet->getCell('I' . $row)->getValue(),  // quantity           5
                $sheet->getCell('J' . $row)->getValue(),  // kode gudang        6
                $sheet->getCell('K' . $row)->getValue(),  // kode koil          7
                $sheet->getCell('L' . $row)->getValue(),  // lot number         8
                $sheet->getCell('M' . $row)->getValue(),  // no sc / remark     9
                $sheet->getCell('N' . $row)->getValue(),  // no box             10
                $sheet->getCell('O' . $row)->getValue()   // isi box            11
            );
            $data[$pi_id]['line'][] = $line;
        }
        $_SESSION[$APP_ID]['upload-pi']['data'] = $data;
    }
    header("Location: ../module.php?m=trx.upload-pi");
    exit;
} if ($_REQUEST['step'] == 4) {
    foreach ($_SESSION[$APP_ID]['upload-pi']['data'] as $pi_id => $data) {
        $daybefore = $newdate = date('d-m-Y',strtotime('-1 days',strtotime(cgx_dmy2ymd($data['pi-date']))));
            foreach ($data['line'] as $l) {
                $m_product_id = get_m_product_id($l[1], $l[2], $l[3], $l[4], $l[0]);
                $m_warehouse_id = get_m_warehouse_id($l[6]);
                $data_1 = get_data_1($_SESSION[$APP_ID]['upload-pi']['org_id'],$daybefore,$m_product_id);
                $min_balan = $data_1 * -1;
                inout($_SESSION[$APP_ID]['upload-pi']['org_id'] , $m_product_id, $m_warehouse_id, $min_balan);
                stock_onhand($_SESSION[$APP_ID]['upload-pi']['org_id'], user(), $m_product_id, $daybefore, $min_balan, 0);
            }   
            unset($_SESSION[$APP_ID]['upload-pi']['data'][$pi_id]['exec-status']);         
    }  
    foreach ($_SESSION[$APP_ID]['upload-pi']['data'] as $pi_id => $data) {
        $daybefore = $newdate = date('d-m-Y',strtotime('-1 days',strtotime(cgx_dmy2ymd($data['pi-date']))));
        //$c_partner_id = get_c_bpartner_id($data['customer']);
        $c_order_id = 0;
        $sql = "INSERT INTO m_inout (app_org_id, document_no, c_order_id, m_inout_date, m_transaction_type_id, dokumen, create_user)
                VALUES ('{$_SESSION[$APP_ID]['upload-pi']['org_id']}','{$pi_id}', '{$c_order_id}', '" . cgx_dmy2ymd($data['pi-date']) . "', 3, 'STOCK OPNAME', '". user() ."')";
        if (mysql_query($sql, $APP_CONNECTION)) {
            $pi_rec_id = mysql_insert_id($APP_CONNECTION);
            foreach ($data['line'] as $l) {
                $m_product_id = get_m_product_id($l[1], $l[2], $l[3], $l[4], $l[0]);
                $m_warehouse_id = get_m_warehouse_id($l[6]);
                $sqline = "INSERT INTO m_inout_line (m_inout_id, m_product_id, quantity, m_warehouse_id, no_coil, no_lot, no_box, isi_box)
                        VALUES ('{$pi_rec_id}', '{$m_product_id}', '{$l[5]}', '{$m_warehouse_id}', '{$l[7]}', '{$l[8]}', '{$l[10]}', '{$l[11]}')";
                $rsx = mysql_query($sqline, $APP_CONNECTION);
                //======================================================================================
                inout($_SESSION[$APP_ID]['upload-pi']['org_id'] , $m_product_id, $m_warehouse_id, $l[5]);
                stock_onhand($_SESSION[$APP_ID]['upload-pi']['org_id'], user(), $m_product_id, $data['pi-date'], $l[5], 0);    
            }   
            unset($_SESSION[$APP_ID]['upload-pi']['data'][$pi_id]['exec-status']);
        }else{
            $_SESSION[$APP_ID]['upload-pi']['data'][$pi_id]['exec-status'] = mysql_error($APP_CONNECTION);
        }         
    }       
    $_SESSION[$APP_ID]['upload-pi']['step'] = 4;
    header("Location: ../module.php?m=trx.upload-pi");
    exit;
}

?>