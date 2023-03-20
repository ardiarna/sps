<?php

/*
 * default
 * Azwari Nugraha <nugraha@duabelas.org>
 * Dec 27, 2013 3:20:12 PM
 */

function changeRole($role_id) {
    global $APP_BASE_URL;
    $res = new xajaxResponse();
    if ($role_id == -1) {
        $html = "<img onclick=\"xajax_changeRoleForm();\" src=\"images/icon_gear.png\" style=\"vertical-align: middle; cursor: pointer;\"> " .
                "<b>" . role('role') . "</b>";
        $res->assign('selectRole', 'innerHTML', $html);
    } else {
        apply_role($role_id);
        $res->redirect($APP_BASE_URL);
    }
    return $res;
}

function changeRoleForm() {
    global $APP_CONNECTION, $APP_ID;
    $html = "Role <select onchange=\"xajax_changeRole(this.value);\">";
    $rsx = mysql_query(
            "SELECT app_role_id, role "
            . "FROM app_role "
            . "JOIN app_user_role USING (app_role_id) "
            . "WHERE user_id = '" . user() . "'", $APP_CONNECTION);
    while ($dtx = mysql_fetch_array($rsx)) {
        if ($dtx['app_role_id'] == role()) {
            $html .= "<option selected value='{$dtx['app_role_id']}'>{$dtx['role']}</option>";
        } else {
            $html .= "<option value='{$dtx['app_role_id']}'>{$dtx['role']}</option>";
        }
    }
    $html .= "<option value='-1'>---[ CANCEL ]---</option>";
    mysql_free_result($rsx);
    $html .= "</select>";
    
    $res = new xajaxResponse();
    $res->assign('selectRole', 'innerHTML', $html);
    return $res;
}

$xajax->register(XAJAX_FUNCTION, 'changeRoleForm');
$xajax->register(XAJAX_FUNCTION, 'changeRole');

?>