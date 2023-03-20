<?php

/*
 * tree-org
 * Azwari Nugraha <nugraha@duabelas.org>
 * Jan 9, 2014 8:14:39 AM
 */

require_once '../init.php';

function array_tree($root = NULL) {
    global $APP_CONNECTION;
    $root_filter = is_null($root) ? "parent_org_id IS NULL" : "parent_org_id = '{$root}'";
    $rsx = mysql_query(
            "SELECT app_org_id, parent_org_id, organization " .
            "FROM app_org " .
            "WHERE {$root_filter}", $APP_CONNECTION);
    $arr = array();
    while ($dtx = mysql_fetch_array($rsx, MYSQL_ASSOC))
            $arr[] = array('id' => $dtx['app_org_id'], 'text' => $dtx['organization']);
    mysql_free_result($rsx);
    foreach ($arr as $k => $v) {
        $tmp = array_tree($v['id']);
        if (count($tmp) > 0) $arr[$k]['children'] = $tmp;
    }
    return $arr;
}

$a = array_tree();
echo json_encode($a);

?>