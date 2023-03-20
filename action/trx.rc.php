<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.org>
 * Apr 29, 2014 11:57:46 AM
 */

require_once '../init.php';

foreach ($_REQUEST['data'] as $date => $value) {
    if ($value == 0) {
        mysql_query(
            "DELETE FROM c_production_plan "
            . "WHERE app_org_id = '{$_REQUEST['org']}' "
            . "AND planning_date = '{$date}' "
            . "AND m_product_id = '{$_REQUEST['product']}' "
            . "AND plan_ref = '{$_REQUEST['ref']}'",
            $APP_CONNECTION);
    } else {
        mysql_query(
            "INSERT INTO c_production_plan ("
                . "app_org_id, planning_date, m_product_id, "
                . "quantity, plan_ref, schedule_delivery_date) VALUES ("
                . "'{$_REQUEST['org']}', '{$date}', '{$_REQUEST['product']}', "
                . "'{$value}', '{$_REQUEST['ref']}','{$_REQUEST['schedule_delivery_date']}') "
                . "ON DUPLICATE KEY UPDATE "
                . "quantity = '{$value}'",
            $APP_CONNECTION);
/*                
$sql1 = "INSERT INTO c_production_plan ("
            . "app_org_id, planning_date, m_product_id, "
            . "quantity, plan_ref, schedule_delivery_date "
            . ") VALUES ("
            . "'{$dtx['app_org_id']}', '{$planning_date}', '{$dtx['m_product_id']}', "
            . "'{$dtx['order_quantity']}', '{$dtx['c_delivery_plan_id']}', '{$dtx['schedule_delivery_date']}')";
  */          
    }
    echo mysql_error($APP_CONNECTION);
}

header("Location: ../module.php?m=trx.rc&d1={$_REQUEST['d1']}&d2={$_REQUEST['d2']}&org={$_REQUEST['org']}");
exit;

?>