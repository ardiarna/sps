<?php

/**
 * (c) Azwari Nugraha <nugraha@pt-gai.com>
 * 24/11/2013 23:46:47
 */

require_once '../init.php';

if (!authenticated()) {
    header("Location: ../index.php");
    exit;
}

if ($_REQUEST['mode'] == 'delete') if (!has_privilege('trx.hpm')) die ('access denied');

if ($_REQUEST['mode'] == 'delete') {
    $sql = "SELECT * FROM m_production_line WHERE m_production_id = '{$_REQUEST['pkey']['m_production_id']}'";    
    $result = mysql_query($sql, $APP_CONNECTION);
    while ($hasil = mysql_fetch_array($result, MYSQL_ASSOC)) {
        mysql_query("UPDATE m_work_order_line SET producted_quantity = producted_quantity - {$hasil['good']},
            ch_quantity = ch_quantity - {$hasil['good_ch']}, sk_quantity = sk_quantity - {$hasil['good_sk']},
            pl_quantity = pl_quantity - {$hasil['good_pl']}, bd_quantity = bd_quantity - {$hasil['good_bd']},
            qc_quantity = qc_quantity - {$hasil['good_qc']}, pc_quantity = pc_quantity - {$hasil['good_pc']}  
            WHERE m_work_order_line_id = '{$hasil['m_work_order_line_id']}'",$APP_CONNECTION);
    }
    $cgx_sql = "DELETE FROM m_production ";
    $cgx_sql .= " WHERE";
    $cgx_sql .= " m_production_id = '{$_REQUEST['pkey']['m_production_id']}'";
    mysql_query("DELETE FROM m_production_line WHERE m_production_id = '{$_REQUEST['pkey']['m_production_id']}'", $APP_CONNECTION);
}

if (@mysql_query($cgx_sql, $cgx_connection)) {
    $_SESSION[$GLOBALS['APP_ID']]['trx.hpm']['error'] = FALSE;
    $_SESSION[$GLOBALS['APP_ID']]['trx.hpm']['info'] = 'Your data has been successfully updated';
    if ($_REQUEST['mode'] == 'new') $cgx_new_id = mysql_insert_id($cgx_connection);
} else {
    $_SESSION[$GLOBALS['APP_ID']]['trx.hpm']['error'] = mysql_error($cgx_connection);
}

if ($_REQUEST['mode'] == 'delete') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']));
}
exit;

?>