<?php

/*
 * trx
 * Azwari Nugraha <nugraha@pt-gai.com>
 * May 25, 2014 11:56:07 PM
 */

if (count($_REQUEST['item']) == 0) die;

require_once '../init.php';
require_once '../lib/class.Penomoran.php';

$nomor = new Penomoran();
$document_no = $nomor->urut('SP', 5);

$sql1 = "INSERT INTO c_spk (c_wo_id, spk_date, m_machine_id, document_no) "
        . "VALUES ('{$_REQUEST['wo']}', '{$_REQUEST['d']}', '{$_REQUEST['m']}', '{$document_no}')";
if (mysql_query($sql1, $APP_CONNECTION)) {
    $spk_id = mysql_insert_id($APP_CONNECTION);
    
    foreach ($_REQUEST['item'] as $wo_line_id => $qty) {
        $sql2 = "INSERT INTO c_spk_line (c_spk_id, c_wo_line_id, quantity) VALUES "
                . "('{$spk_id}', '{$wo_line_id}', '{$qty}')";
        $sql3 = "UPDATE c_wo_line "
                . "SET allocated = 'Y' "
                . "WHERE c_wo_line_id = '{$wo_line_id}'";
        mysql_query($sql2, $APP_CONNECTION);
        mysql_query($sql3, $APP_CONNECTION);
    }
    
}

header("Location: ../module.php?m=trx.ppc-sp-lp1");
exit;
        
?>