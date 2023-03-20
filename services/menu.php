<?php

$skip_ajax = TRUE;
require_once '../init.php';

function get_menu($parent = '') {
    global $APP_CONNECTION;
    
    // default menu
    $rsx = mysql_query(
            "SELECT * FROM app_menu "
            . "WHERE app_org_id = -1 "
            . "AND parent_menu_id = '{$parent}' "
            . "AND (app_priv_id = '' OR app_priv_id IN (SELECT app_priv_id FROM app_role_priv WHERE app_role_id = '" . role() . "')) "
            . "ORDER BY sort_order", $APP_CONNECTION);
    while ($dtx = mysql_fetch_array($rsx, MYSQL_ASSOC)) $tmp[$dtx['app_menu_id']] = $dtx;
    mysql_free_result($rsx);
    
    // org menu
    $rsx = mysql_query(
            "SELECT * FROM app_menu "
            . "WHERE app_org_id = '" . org() . "' "
            . "AND parent_menu_id = '{$parent}' "
            . "AND (app_priv_id = '' OR app_priv_id IN (SELECT app_priv_id FROM app_role_priv WHERE app_role_id = '" . role() . "')) "
            . "ORDER BY sort_order", $APP_CONNECTION);
    while ($dtx = mysql_fetch_array($rsx, MYSQL_ASSOC)) $tmp[$dtx['app_menu_id']] = $dtx;
    mysql_free_result($rsx);
    
    $dtxs = array();
    foreach ($tmp as $t) $dtxs[] = $t;
    if (empty($dtxs)) return NULL;
    
    foreach ($dtxs as $dtx) {
        $submenu = get_menu($dtx['app_menu_id']);
        $tmp = array(
            'id' => $dtx['app_menu_id'],
            'text' => $dtx['title'],
            'priv' => $dtx['app_priv_id']
        );
        if ($submenu) $tmp['sub'] = $submenu;
        if ($dtx['image_0']) {
            $images = array($dtx['image_0']);
            $tmp['image'] = $images;
        }
        if ($submenu || $dtx['image_0']) $menu[] = $tmp;
    }
    return $menu;
}

function printRecursive($menu) {
    if (!is_array($menu)) return;
    foreach ($menu as $menu0) {
        $im0 = "";
        if (is_array($menu0['image'])) {
            $im0 = " im0=\"{$menu0['image'][0]}\"";
        }
        if (sizeof($menu0['sub']) > 0) {
            $open = $menu0['open'] ? " open=\"1\"" : "";
            echo "<item id=\"{$menu0['id']}\" text=\"{$menu0['text']}\"{$open}>\n";
            printRecursive($menu0['sub']);
            echo "</item>\n";
        } else {
            echo "<item id=\"{$menu0['id']}\" text=\"{$menu0['text']}\"{$im0} />\n";
        }
    }
}

header("Content-Type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
echo "<tree id=\"0\">\n";
printRecursive(get_menu());
echo "</tree>\n";

?>