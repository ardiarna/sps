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
$cgx_sql = "SELECT mmr.*, mp1.product_code, mp1.spec, mp1.od, mp1.thickness, mp1.length, partner_name,
                mp2.product_code product_codem, mp2.spec specm, mp2.od odm, mp2.thickness thicknessm, mp2.length lengthm
                FROM m_material_requirement mmr
                JOIN m_product mp1 ON(mmr.m_product_fg=mp1.m_product_id)
                LEFT JOIN c_bpartner ON(mp1.c_bpartner_id=c_bpartner.c_bpartner_id)
                JOIN m_product mp2 ON(mmr.m_product_material=mp2.m_product_id)
                WHERE mp1.category = 'L' AND mp2.category = 'S' ";
                
$cgx_search = $_REQUEST['q'];
                 
if($cgx_search) $cgx_sql .= " and ( mp1.product_code LIKE '%{$cgx_search}%' OR mp1.product_name LIKE '%{$cgx_search}%' 
                 OR mp2.product_code LIKE '%{$cgx_search}%' OR mp2.product_name LIKE '%{$cgx_search}%' )";
//print_r($cgx_sql);exit;
if ($_REQUEST['mode'] == 'update' || $_REQUEST['mode'] == 'new') if (!has_privilege('master.m_coil')) die ('akses ditolak');
if ($_REQUEST['mode'] == 'delete') if (!has_privilege('master.m_coil')) die ('akses ditolak');

if ($_REQUEST['mode'] == 'export-all') {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=\"master.m_coil-" . date("Y-m-d") . ".csv\"");
    echo "\"MATERIAL ID\"";
    echo ",\"ITEM NUMBER SLITTING\"";
    echo ",\"CUSTOMER\"";
    echo ",\"SPEC SLITTING\"";
    echo ",\"OD SLITTING\"";
    echo ",\"THICKNESS SLITTING\"";
    echo ",\"LENGTH SLITTING\"";
    echo ",\"ITEM NUMBER\"";
    echo ",\"SPEC\"";
    echo ",\"OD\"";
    echo ",\"THICKNESS\"";
    echo ",\"LENGTH\"";    
    echo "\n";
    
    $cgx_rs_export = mysql_query($cgx_sql, $cgx_connection);
    while (($cgx_dt_export = mysql_fetch_array($cgx_rs_export, MYSQL_ASSOC)) !== FALSE) {
        echo "\"" . str_replace("\"", "\"\"", $cgx_dt_export['m_material_requirement_id']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['product_code']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['partner_name']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['spec']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['od']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['thickness']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['length']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['product_codem']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['specm']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['odm']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['thicknessm']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['lengthm']) . "\"";
        echo "\n";
    }
    mysql_free_result($cgx_rs_export);
    exit;
} elseif ($_REQUEST['mode'] == 'update') {
    $cgx_sql = "UPDATE m_material_requirement SET";
    $cgx_sql .= " m_product_fg = '{$_REQUEST['data']['m_product_fg']}'";
    $cgx_sql .= ", m_product_material = '" . mysql_escape_string($_REQUEST['data']['m_product_material']) . "'";
    $cgx_sql .= " WHERE";
    $cgx_sql .= " m_material_requirement_id = '{$_REQUEST['pkey']['m_material_requirement_id']}'";
} elseif ($_REQUEST['mode'] == 'new') {
    $cgx_sql = "INSERT INTO m_material_requirement (";
    $cgx_sql .= "m_product_fg,m_product_material";
    $cgx_sql .= ") values (";
    $cgx_sql .= "'{$_REQUEST['data']['m_product_fg']}'";
    $cgx_sql .= ",'" . mysql_escape_string($_REQUEST['data']['m_product_material']) . "'";
    $cgx_sql .= ")";
} elseif ($_REQUEST['mode'] == 'delete') {
    $cgx_sql = "DELETE FROM m_material_requirement ";
    $cgx_sql .= " WHERE";
    $cgx_sql .= " m_material_requirement_id = '{$_REQUEST['pkey']['m_material_requirement_id']}'";
}

if (@mysql_query($cgx_sql, $cgx_connection)) {
    $_SESSION[$GLOBALS['APP_ID']]['master.m_coil']['error'] = FALSE;
    $_SESSION[$GLOBALS['APP_ID']]['master.m_coil']['info'] = 'Data anda sudah berhasil diperbarui';
    if ($_REQUEST['mode'] == 'new') $cgx_new_id = mysql_insert_id($cgx_connection);
} else {
    $_SESSION[$GLOBALS['APP_ID']]['master.m_coil']['error'] = mysql_error($cgx_connection);
}

if ($_REQUEST['mode'] == 'update') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']) . "&pkey[m_material_requirement_id]={$_REQUEST['pkey']['m_material_requirement_id']}");
} elseif ($_REQUEST['mode'] == 'new') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']) . "&pkey[m_material_requirement_id]={$cgx_new_id}");
} elseif ($_REQUEST['mode'] == 'delete') {
    header("Location: ../module.php?" . urldecode($_REQUEST['backvar']));
}
exit;

?>