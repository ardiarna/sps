<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 07/01/2014 13:47:15
 */

require_once '../init.php';

if (!authenticated()) {
    header("Location: ../index.php");
    exit;
}

if ($_REQUEST['mode'] == 'update' || $_REQUEST['mode'] == 'new') if (!has_privilege('trx.bkbb')) die ('akses ditolak');
if ($_REQUEST['mode'] == 'delete') if (!has_privilege('trx.bkbb')) die ('akses ditolak');

if ($_REQUEST['mode'] == 'export-all') {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=\"trx.bkbb-" . date("Y-m-d") . ".csv\"");
    echo "\"NOMOR\"";
    echo ",\"TANGGAL\"";
    echo ",\"NO WO RECUTTING\"";
    echo ",\"MESIN\"";
    echo ",\"NO LOT NUMBER\"";
    echo ",\"PO NUMBER\"";
    echo ",\"CUSTOMER\"";
    echo ",\"SPEC\"";
    echo ",\"OD\"";
    echo ",\"THICKNESS\"";
    echo ",\"LENGTH\"";
    echo ",\"KODE COIL\"";
    echo "\n";
    $cgx_sql = "SELECT m_inout.*, 
                machine_name, reference_no, partner_name, spec, od, thickness, length, no_box FROM m_inout
                LEFT JOIN m_machine ON(m_inout.tuj_org_id=m_machine.m_machine_id) 
                LEFT JOIN c_order ON (m_inout.c_order_id=c_order.c_order_id)
                LEFT JOIN c_bpartner ON(c_order.c_bpartner_id=c_bpartner.c_bpartner_id)
                JOIN m_inout_line ON(m_inout.m_inout_id=m_inout_line.m_inout_id)
                JOIN m_product ON(m_inout_line.m_product_id=m_product.m_product_id)
                WHERE m_inout.m_transaction_type_id = 4 ";
    $cgx_sql .= " AND " . org_filter_trx('m_inout.app_org_id');
    $cgx_rs_export = mysql_query($cgx_sql, $cgx_connection);
    while (($cgx_dt_export = mysql_fetch_array($cgx_rs_export, MYSQL_ASSOC)) !== FALSE) {
        echo "\"" . str_replace("\"", "\"\"", $cgx_dt_export['document_no']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['m_inout_date']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['dokumen']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['machine_name']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['no_kendaraan']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['reference_no']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['partner_name']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['spec']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['od']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['thickness']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['length']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['no_box']) . "\"";
        echo "\n";
    }
    mysql_free_result($cgx_rs_export);
    exit;
} elseif ($_REQUEST['mode'] == 'update') {
    
} elseif ($_REQUEST['mode'] == 'new') {
    require_once 'lib/class.Penomoran.php';
    $nomor = new Penomoran();
    $document_no = $nomor->urut('BK', org());
    $cgx_sql = "INSERT INTO m_inout (app_org_id, m_inout_date, document_no, c_order_id, m_transaction_type_id, tuj_org_id, dokumen, no_kendaraan) 
            VALUES ('". org() ."','" . cgx_dmy2ymd($_REQUEST['data']['m_inout_date']) . "','{$document_no}','{$_REQUEST['data']['c_order_id']}','4','{$_REQUEST['data']['m_machine_id']}','" . mysql_escape_string($_REQUEST['data']['dokumen']) . "','" . mysql_escape_string($_REQUEST['data']['no_kendaraan']) . "')";
} elseif ($_REQUEST['mode'] == 'delete') {
    
}

if (@mysql_query($cgx_sql, $cgx_connection)) {
    $_SESSION[$GLOBALS['APP_ID']]['trx.bkbb']['error'] = FALSE;
    $_SESSION[$GLOBALS['APP_ID']]['trx.bkbb']['info'] = 'Data anda sudah berhasil diperbarui';
    if ($_REQUEST['mode'] == 'new'){
        $cgx_new_id = mysql_insert_id($cgx_connection);
        $cgx_sql = "INSERT INTO m_inout_line (m_inout_id, m_product_id, m_warehouse_id, quantity, no_box) 
                VALUES ('{$cgx_new_id}','{$_REQUEST['data']['m_product_id']}','{$_REQUEST['data']['m_warehouse_id']}','{$_REQUEST['data']['quantity']}','" . mysql_escape_string($_REQUEST['data']['no_box']) . "')";
        mysql_query($cgx_sql, $cgx_connection);
        //================================================================================================== update stock on hand
        $sql = "SELECT * FROM m_stock_onhand WHERE m_product_id = '{$_REQUEST['data']['m_product_id']}' AND m_inout_date = '" . cgx_dmy2ymd($_REQUEST['data']['m_inout_date']) . "' AND app_org_id = '" . org() . "' ";
        $result = mysql_query($sql, $cgx_connection);
        $hasil = mysql_fetch_array($result, MYSQL_ASSOC);
        //mengecek tanggal sebelumnya
        $sql2 = "SELECT max(m_inout_date) m_inout_date FROM m_stock_onhand WHERE m_product_id = '{$_REQUEST['data']['m_product_id']}' AND m_inout_date < '" . cgx_dmy2ymd($_REQUEST['data']['m_inout_date']) . "' AND app_org_id = '" . org() . "' ";    
        $result2 = mysql_query($sql2, $cgx_connection);
        $hasil2 = mysql_result($result2,0);
        if($hasil2){
            $sql3 = "SELECT balance_quantity FROM m_stock_onhand WHERE m_product_id = '{$_REQUEST['data']['m_product_id']}' AND m_inout_date = '{$hasil2}' AND app_org_id = '" . org() . "' ";    
            $result3 = mysql_query($sql3, $cgx_connection);
            $hasil3 = mysql_result($result3,0);
            if($hasil3){
                $prev_qty = $hasil3;
            }else{
                $prev_qty = '0';
            }
        }else{
            $prev_qty = '0';   
        }

        if($hasil){
            $in_qty = $hasil['in_quantity'];
            $out_qty = $hasil['out_quantity'] + $_REQUEST['data']['quantity'];
            $balance_qty = $prev_qty + $in_qty - $out_qty;
            $sql = "UPDATE m_stock_onhand SET prev_quantity = '{$prev_qty}', out_quantity = '{$out_qty}', balance_quantity = '{$balance_qty}', update_user = '" . user() . "', update_date = NOW() WHERE m_stock_onhand_id = '{$hasil['m_stock_onhand_id']}'";
            $result = mysql_query($sql, $cgx_connection);
        }else{
            $balance_qty = $prev_qty - $_REQUEST['data']['quantity'];
            $sql = "INSERT INTO m_stock_onhand (app_org_id,m_product_id,m_inout_date,prev_quantity,in_quantity,out_quantity,balance_quantity, "
                    . "update_user,update_date) VALUES ("
                    . "'" . org() . "', '{$_REQUEST['data']['m_product_id']}', '" . cgx_dmy2ymd($_REQUEST['data']['m_inout_date']) . "', '{$prev_qty}', '0', "
                    . "'{$_REQUEST['data']['quantity']}', '{$balance_qty}', '" . user() . "', NOW())";
            $result = mysql_query($sql, $cgx_connection); 
        }

        //update stock di tanggal-tanggal selanjutnya
        $sql = "select * from m_stock_onhand where m_product_id = '{$_REQUEST['data']['m_product_id']}' AND m_inout_date > '" . cgx_dmy2ymd($_REQUEST['data']['m_inout_date']) . "' AND app_org_id = '" . org() . "' ORDER BY m_inout_date ";    
        $result = mysql_query($sql, $cgx_connection);
        while ($hasil = mysql_fetch_array($result, MYSQL_ASSOC)) {
            $sql2 = "SELECT max(m_inout_date) m_inout_date FROM m_stock_onhand WHERE m_product_id = '{$_REQUEST['data']['m_product_id']}' AND m_inout_date < '{$hasil['m_inout_date']}' AND app_org_id = '" . org() . "' ";    
            $result2 = mysql_query($sql2, $cgx_connection);
            $hasil2 = mysql_result($result2,0);            
            if($hasil2){
                $sql3 = "SELECT balance_quantity FROM m_stock_onhand WHERE m_product_id = '{$_REQUEST['data']['m_product_id']}' AND m_inout_date = '{$hasil2}' AND app_org_id = '" . org() . "' ";    
                $result3 = mysql_query($sql3, $cgx_connection);
                $hasil3 = mysql_result($result3,0);
                if($hasil3){
                    $prevqty = $hasil3;
                }else{
                    $prevqty = '0';
                }
            }else{
                $prevqty = '0';   
            }
            $balanceqty = $prevqty + $hasil['in_quantity'] - $hasil['out_quantity'];;
            $sql_up = "UPDATE m_stock_onhand SET prev_quantity = '{$prevqty}', balance_quantity = '{$balanceqty}' WHERE m_stock_onhand_id = '{$hasil['m_stock_onhand_id']}'";
            $result_up = mysql_query($sql_up, $cgx_connection);
        }
        //====================================================================================== update balance
        inout(org(),$_REQUEST['data']['m_product_id'], $_REQUEST['data']['m_warehouse_id'], 0, $_REQUEST['data']['quantity'], FALSE);
    } 
} else {
    $_SESSION[$GLOBALS['APP_ID']]['trx.bkbb']['error'] = mysql_error($cgx_connection);
}

if ($_REQUEST['mode'] == 'update') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']) . "&pkey[m_inout_id]={$_REQUEST['pkey']['m_inout_id']}");
} elseif ($_REQUEST['mode'] == 'new') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']) . "&pkey[m_inout_id]={$cgx_new_id}");
} elseif ($_REQUEST['mode'] == 'delete') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']));
}
exit;

?>