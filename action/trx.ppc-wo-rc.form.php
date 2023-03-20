<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.org>
 * May 16, 2014 3:01:56 PM
 */

require_once '../init.php';
require_once '../lib/class.Penomoran.php';

$def_org = 3; // recutting
$def_year = $_REQUEST['year'];
$def_week = str_pad($_REQUEST['week'], 2, '0', STR_PAD_LEFT);

$date1 = strtotime($def_year . 'W' . $def_week);
$date2 = $date1 + (86400 * 6);

$d1sql = date('Y-m-d', $date1);
$d2sql = date('Y-m-d', $date2);

$nomor = new Penomoran();
$document_no = $nomor->urut('PP', 3);
$document_date = date('Y-m-d');

$rsx = mysql_query(
        "INSERT INTO c_wo (document_no, wo_date, wo_week) VALUES "
        . "('{$document_no}', '{$document_date}', '{$_REQUEST['year']}{$_REQUEST['week']}')",
        $APP_CONNECTION);
if ($rsx) {
    $wo_id = mysql_insert_id($APP_CONNECTION);
    
    $rsy = mysql_query(
            "SELECT * "
            . "FROM c_production_plan "
            . "WHERE app_org_id = '{$def_org}' "
            . "AND isworkorder != 'Y' "
            . "AND planning_date BETWEEN '{$d1sql}' AND '{$d2sql}' ",
            $APP_CONNECTION);
    while ($dty = mysql_fetch_array($rsy, MYSQL_ASSOC)) {
        $sql1 = "INSERT INTO c_wo_line ("
                . "c_wo_id, working_date, m_machine_id, m_product_id, "
                . "quantity, allocated, c_production_plan_id) VALUES ( "
                . "'{$wo_id}', '{$dty['planning_date']}', "
                . "'{$_REQUEST['mesin'][$dty['m_product_id']]}', '{$dty['m_product_id']}', "
                . "'{$dty['quantity']}', 'N', '{$dty['c_production_plan_id']}')";
        $sql2 = "UPDATE c_production_plan "
                . "SET isworkorder = 'Y' "
                . "WHERE c_production_plan_id = '{$dty['c_production_plan_id']}'";
        if (mysql_query($sql1, $APP_CONNECTION)) mysql_query($sql2, $APP_CONNECTION);
    }
    mysql_free_result($rsy);
    
}

header("Location: ../module.php?m=trx.ppc-wo-rc1");
exit;

?>