<?php

/*
 * Default Library
 * Azwari Nugraha <nugraha@duabelas.org>
 * Oct 1, 2012 4:47:02 PM
 */

function org() {
    global $APP_ID;
    return $_SESSION[$APP_ID]['role']['app_org_id'];
}

function org_filter_trx($field = 'app_org_id') {
    return "{$field} = '" . org() . "'";
}

function org_filter_master($field = 'app_org_id') {
    return "org_allowed({$field}) LIKE '%|" . org() . "|%'";
}

//PENDING
//function org_hierarchy() {
//    global $APP_ID;
//    org_recursive($_SESSION[$APP_ID]['role']['app_org_id'], $arr);
//    print_r($arr);
//}
//
//function org_recursive($app_org_id, &$arr_org = NULL) {
//    global $APP_CONNECTION;
//    if (!is_array($arr_org)) $arr_org = array();
//    $arr_org[] = $app_org_id;
//    $org = array();
//    $rsx = mysql_query("SELECT * FROM app_org WHERE parent_org_id = '{$app_org_id}' AND app_org_id <> parent_org_id", $APP_CONNECTION);
//    while ($dtx = mysql_fetch_array($rsx)) $org[] = $dtx;
//    mysql_free_result($rsx);
//    if (count($org) > 0) foreach ($org as $o) org_recursive ($o['app_org_id'], $arr_org);
//}

function has_privilege($priv_id) {
    global $APP_ID;
    return $_SESSION[$APP_ID]['priv'][$priv_id];
}

function login($id, $password) {
    global $APP_CONNECTION, $APP_ID, $lang;
    $rsx = mysql_query(
            "SELECT * " .
            "FROM app_user " .
            "WHERE user_name = '" . mysql_real_escape_string($id) . "' " .
            "AND user_password = '" . md5($password) . "'",
            $APP_CONNECTION);
    if ($dtx = mysql_fetch_array($rsx)) {
        
        if ($dtx['user_active'] == 'N') {
            unset($_SESSION[$APP_ID]);
            $_SESSION[$APP_ID]['auth-message'] = "Akun anda tidak aktif, silahkan hubungi administrator";
            return FALSE;
        } else {
            // update
            mysql_query(
                    "UPDATE app_user SET " .
                    "last_ip = '{$_SERVER['REMOTE_ADDR']}' " .
                    "WHERE user_id = '{$dtx['user_id']}'",
                    $APP_CONNECTION);

            $user = npl_fetch_table("SELECT * FROM app_user WHERE user_id = '{$dtx['user_id']}'");
            unset($_SESSION[$APP_ID]);
            $_SESSION[$APP_ID]['authenticated'] = 1;
            $_SESSION[$APP_ID]['user'] = $user;
            
            apply_role();

            return TRUE;
        }
    } else {
        unset($_SESSION[$APP_ID]);
        $_SESSION[$APP_ID]['auth-message'] = "Nama user/password salah";
        return FALSE;
    }
}

function apply_role($role_id = NULL) {
    global $APP_CONNECTION, $APP_ID;
    $user_id = user();
    $default_role = is_null($role_id) ? user('default_role_id') : $role_id;
    if (is_null($default_role)) {
        $user_role = npl_fetch_table("SELECT * FROM app_user_role WHERE user_id = '{$user_id}' LIMIT 1");
        if (is_array($user_role)) {
            mysql_query("UPDATE app_user SET default_role_id = '{$user_role['app_role_id']}' WHERE user_id = '{$user_id}'", $APP_CONNECTION);
            $default_role = $user_role['app_role_id'];
        } else {
            return FALSE;
        }
    }
    $role = npl_fetch_table("SELECT app_role.*, organization FROM app_role JOIN app_org USING (app_org_id) WHERE app_role_id = '{$default_role}'");
    $_SESSION[$APP_ID]['role'] = $role;
    $rsx = mysql_query("SELECT * FROM app_role_priv WHERE app_role_id = '{$default_role}'", $APP_CONNECTION);
    $priv = array();
    while ($dtx = mysql_fetch_array($rsx)) $priv[$dtx['app_priv_id']] = TRUE;
    mysql_free_result($rsx);
    $_SESSION[$APP_ID]['priv'] = $priv;
    return TRUE;
}

function authenticated() {
    global $APP_ID;
    return $_SESSION[$APP_ID]['authenticated'] == 1;
}

function role($field = 'app_role_id') {
    global $APP_ID;
    return $_SESSION[$APP_ID]['role'][$field];
}

function user($field = 'user_id') {
    global $APP_ID;
    return $_SESSION[$APP_ID]['user'][$field];
}

function is_admin() {
    return TRUE;
    //return user('user_admin') == 'Y';
}

function npl_emptydate($date) {
    return empty($date) || $date == '0000-00-00' || $date == '0000-00-00 00:00:00';
}

function npl_dmy2ymd($dmy) {
    $arr = explode("-", $dmy);
    $out = $arr[2] . '-' . $arr[1] . '-' . $arr[0];
    $out = npl_emptydate($out) || $out == '--' ? '0000-00-00' : $out;
    return $out;
}

function npl_fetch_table($sql) {
    $r1 = mysql_query($sql, $GLOBALS['APP_CONNECTION']);
    if (mysql_num_rows($r1) == 0) {
        $ret = null;
    } else {
        if(($d1 = mysql_fetch_array($r1)) !== FALSE) {
            $ret = $d1;
        } else {
            $ret = NULL;
        }
    }
    mysql_free_result($r1);
    return $ret;
}

function npl_format_date($date) {
    if (npl_emptydate($date)) return NULL;
    $format = strlen($GLOBALS['APP_DATE_FORMAT']) > 0 ? $GLOBALS['APP_DATE_FORMAT'] : 'd-M-Y';
    return date($format, strtotime($date));
}

function npl_format_period($date) {
    if (npl_emptydate($date)) return NULL;
    return date('m-Y', strtotime($date));
}

function npl_get_setting($set_name) {
    $setting = npl_fetch_table("SELECT * FROM app_setting WHERE set_name = '" . mysql_real_escape_string($set_name) . "'");
    return $setting['set_value'];
}

function npl_period2mysqldate($period) {
    $a = explode('-', $period);
    return $a[1] . '-' . $a[0] . '-01';
}

function valid_url($url)
{
    return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
}

function valid_email_address($email) {
    // First, we check that there's one @ symbol,
    // and that the lengths are right.
    if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $email)) {
        // Email invalid because wrong number of characters
        // in one section or wrong number of @ symbols.
        return false;
    }
    // Split it into sections to make life easier
    $email_array = explode("@", $email);
    $local_array = explode(".", $email_array[0]);
    for ($i = 0; $i < sizeof($local_array); $i++) {
        if (!ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&'*+/=?^_`{|}~\\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$", $local_array[$i])) {
            return false;
        }
    }
    // Check if domain is IP. If not,
    // it should be valid domain name
    if (!ereg("^\\[?[0-9\\.]+\\]?$", $email_array[1])) {
        $domain_array = explode(".", $email_array[1]);
        if (sizeof($domain_array) < 2) {
            return false; // Not enough parts to domain
        }
        for ($i = 0; $i < sizeof($domain_array); $i++) {
            if (!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$",$domain_array[$i])) {
                return false;
            }
        }
    }
    return true;
}

function replace_template($template, $vars) {
    foreach ($template as $tk => $tv) {
        $result[$tk] = $tv;
        foreach ($vars as $vk => $vv) {
            $result[$tk] = str_replace("<\${$vk}\$>", $vv, $result[$tk]);
        }
    }
    return $result;
}

function inout($org_id,$m_product_id, $m_warehouse_id, $in_quantity = 0, $out_quantity = 0, $prevent_negative = TRUE) {
    global $APP_CONNECTION;

    // warehouse balance detail
    $current = cgx_fetch_table(
            "SELECT * FROM m_stock_warehouse_2 " .
            "WHERE m_product_id = '{$m_product_id}' AND m_warehouse_id = '{$m_warehouse_id}' AND latest = 'Y' AND app_org_id = '{$org_id}' ");
    $prev_balance = (double) $current['balance_quantity'];
    $balance = $prev_balance + $in_quantity - $out_quantity;
    //if ($prevent_negative && $balance < 0) return FALSE;
    mysql_query(
        "UPDATE m_stock_warehouse_2 SET latest = 'N' WHERE m_product_id = '{$m_product_id}' AND m_warehouse_id = '{$m_warehouse_id}' AND app_org_id = '{$org_id}' ",
        $APP_CONNECTION);
    mysql_query(
        "INSERT INTO m_stock_warehouse_2 (app_org_id,m_product_id, m_warehouse_id, balance_date, prev_quantity, " .
        "in_quantity, out_quantity, balance_quantity, latest) VALUES " .
        "('{$org_id}','{$m_product_id}', '{$m_warehouse_id}', NOW(), '{$prev_balance}', '{$in_quantity}', " .
        "'{$out_quantity}', '{$balance}', 'Y')",
        $APP_CONNECTION);
        
    // warehouse balance daily
    $current = cgx_fetch_table(
            "SELECT * FROM m_stock_warehouse_d_2 " .
            "WHERE m_product_id = '{$m_product_id}' AND m_warehouse_id = '{$m_warehouse_id}' AND latest = 'Y' AND app_org_id = '{$org_id}'");
    $prev_balance = (double) $current['balance_quantity'];
    $balance = $prev_balance + $in_quantity - $out_quantity;
    mysql_query(
        "UPDATE m_stock_warehouse_d_2 SET latest = 'N' WHERE m_product_id = '{$m_product_id}' AND m_warehouse_id = '{$m_warehouse_id}' AND app_org_id = '{$org_id}' ",
        $APP_CONNECTION);
    mysql_query(
        "INSERT INTO m_stock_warehouse_d_2 (app_org_id,m_product_id, m_warehouse_id, balance_date, prev_quantity, " .
        "in_quantity, out_quantity, balance_quantity, latest) VALUES " .
        "('{$org_id}','{$m_product_id}', '{$m_warehouse_id}', NOW(), '{$prev_balance}', '{$in_quantity}', " .
        "'{$out_quantity}', '{$balance}', 'Y') " .
        "ON DUPLICATE KEY UPDATE " .
        "in_quantity = in_quantity + '{$in_quantity}', " .
        "out_quantity = out_quantity + '{$out_quantity}', " .
        "balance_quantity = prev_quantity + in_quantity - out_quantity, " .
        "latest = 'Y'",
        $APP_CONNECTION);
        
    // total balance detail
    $current = cgx_fetch_table(
            "SELECT * FROM m_stock_balance_2 " .
            "WHERE m_product_id = '{$m_product_id}' AND latest = 'Y' AND app_org_id = '{$org_id}' ");
    $prev_balance = (double) $current['balance_quantity'];
    $balance = $prev_balance + $in_quantity - $out_quantity;
    mysql_query(
        "UPDATE m_stock_balance_2 SET latest = 'N' WHERE m_product_id = '{$m_product_id}' AND app_org_id = '{$org_id}' ",
        $APP_CONNECTION);
    mysql_query(
        "INSERT INTO m_stock_balance_2 (app_org_id,m_product_id, balance_date, prev_quantity, " .
        "in_quantity, out_quantity, balance_quantity, latest) VALUES " .
        "('{$org_id}','{$m_product_id}', NOW(), '{$prev_balance}', '{$in_quantity}', " .
        "'{$out_quantity}', '{$balance}', 'Y')",
        $APP_CONNECTION);
        
    // total balance daily
    $current = cgx_fetch_table(
            "SELECT * FROM m_stock_balance_d_2 " .
            "WHERE m_product_id = '{$m_product_id}' AND latest = 'Y' AND app_org_id = '{$org_id}' ");
    $prev_balance = (double) $current['balance_quantity'];
    $balance = $prev_balance + $in_quantity - $out_quantity;
    mysql_query(
        "UPDATE m_stock_balance_d_2 SET latest = 'N' WHERE m_product_id = '{$m_product_id}' AND app_org_id = '{$org_id}' ",
        $APP_CONNECTION);
    mysql_query(
        "INSERT INTO m_stock_balance_d_2 (app_org_id,m_product_id, balance_date, prev_quantity, " .
        "in_quantity, out_quantity, balance_quantity, latest) VALUES " .
        "('{$org_id}','{$m_product_id}', NOW(), '{$prev_balance}', '{$in_quantity}', " .
        "'{$out_quantity}', '{$balance}', 'Y') " .
        "ON DUPLICATE KEY UPDATE " .
        "in_quantity = in_quantity + '{$in_quantity}', " .
        "out_quantity = out_quantity + '{$out_quantity}', " .
        "balance_quantity = prev_quantity + in_quantity - out_quantity, " .
        "latest = 'Y'",
        $APP_CONNECTION);

    return $balance;
}


function stock_onhand($org_id, $user, $m_product_id, $m_inout_date, $in_quantity = 0, $out_quantity = 0, $prevent_negative = TRUE) {
    global $APP_CONNECTION;

    $current = cgx_fetch_table("SELECT * FROM m_stock_onhand WHERE m_product_id = '{$m_product_id}' AND 
            m_inout_date = '" . cgx_dmy2ymd($m_inout_date) . "' AND app_org_id = '{$org_id}' ");
    $before = cgx_fetch_table("SELECT mso1.* FROM m_stock_onhand mso1 INNER JOIN (
            SELECT m_product_id, MAX(m_inout_date) m_inout_date FROM m_stock_onhand 
            WHERE m_inout_date < '" . cgx_dmy2ymd($m_inout_date) . "' AND app_org_id = '{$org_id}' GROUP BY m_product_id) mso2 
            ON(mso1.m_product_id = mso2.m_product_id AND mso1.m_inout_date = mso2.m_inout_date) 
            WHERE mso1.m_product_id = '{$m_product_id}' AND mso1.app_org_id = '{$org_id}' ");
    if($before){
        $prev_qty = $before['balance_quantity'];
    }else{
        $prev_qty = '0';
    }

    if($current){
        $in_qty  = $current['in_quantity'] + $in_quantity;
        $out_qty = $current['out_quantity'] + $out_quantity;
        $balance_qty = $prev_qty + $in_qty - $out_qty;
        mysql_query("UPDATE m_stock_onhand SET prev_quantity = '{$prev_qty}', in_quantity = '{$in_qty}', out_quantity = '{$out_qty}', balance_quantity = '{$balance_qty}', 
            update_user = '{$user}', update_date = NOW() WHERE m_stock_onhand_id = '{$current['m_stock_onhand_id']}'", $APP_CONNECTION);
    }else{
        $balance_qty = $prev_qty + $in_quantity - $out_quantity;
        mysql_query("INSERT INTO m_stock_onhand (app_org_id,m_product_id,m_inout_date,prev_quantity,in_quantity,out_quantity,balance_quantity, 
            update_user,update_date) VALUES ('{$org_id}', '{$m_product_id}', '" . cgx_dmy2ymd($m_inout_date) . "', '{$prev_qty}', 
            '{$in_quantity}', '{$out_quantity}', '{$balance_qty}', '{$user}', NOW())", $APP_CONNECTION);
    }

    //update stock di tanggal-tanggal selanjutnya
    $sql = "select * from m_stock_onhand where m_product_id = '{$m_product_id}' AND m_inout_date > '" . cgx_dmy2ymd($m_inout_date) . "' AND app_org_id = '{$org_id}' ORDER BY m_inout_date ";    
    $result = mysql_query($sql, $APP_CONNECTION);
    while ($hasil = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $before = cgx_fetch_table("SELECT mso1.* FROM m_stock_onhand mso1 INNER JOIN (
            SELECT m_product_id, MAX(m_inout_date) m_inout_date FROM m_stock_onhand 
            WHERE m_inout_date < '{$hasil['m_inout_date']}' AND app_org_id = '{$org_id}' GROUP BY m_product_id) mso2 
            ON(mso1.m_product_id = mso2.m_product_id AND mso1.m_inout_date = mso2.m_inout_date) 
            WHERE mso1.m_product_id = '{$m_product_id}' AND mso1.app_org_id = '{$org_id}' ");
        if($before){
            $prevqty = $before['balance_quantity'];
        }else{
            $prevqty = '0';
        }
        $balanceqty = $prevqty + $hasil['in_quantity'] - $hasil['out_quantity'];;
        mysql_query("UPDATE m_stock_onhand SET prev_quantity = '{$prevqty}', balance_quantity = '{$balanceqty}' 
            WHERE m_stock_onhand_id = '{$hasil['m_stock_onhand_id']}'", $APP_CONNECTION);
    }
    
    return $balance_qty;
}

function stock_weight($org_id, $user, $m_product_id, $m_inout_date, $in_weight = 0, $out_weight = 0, $prevent_negative = TRUE) {
    global $APP_CONNECTION;

    $current = cgx_fetch_table("SELECT * FROM m_stock_weight WHERE m_product_id = '{$m_product_id}' AND 
            m_inout_date = '" . cgx_dmy2ymd($m_inout_date) . "' AND app_org_id = '{$org_id}' ");
    $before = cgx_fetch_table("SELECT mso1.* FROM m_stock_weight mso1 INNER JOIN (
            SELECT m_product_id, MAX(m_inout_date) m_inout_date FROM m_stock_weight 
            WHERE m_inout_date < '" . cgx_dmy2ymd($m_inout_date) . "' AND app_org_id = '{$org_id}' GROUP BY m_product_id) mso2 
            ON(mso1.m_product_id = mso2.m_product_id AND mso1.m_inout_date = mso2.m_inout_date) 
            WHERE mso1.m_product_id = '{$m_product_id}' AND mso1.app_org_id = '{$org_id}' ");
    if($before){
        $prev_qty = $before['balance_weight'];
    }else{
        $prev_qty = '0';
    }

    if($current){
        $in_qty  = $current['in_weight'] + $in_weight;
        $out_qty = $current['out_weight'] + $out_weight;
        $balance_qty = $prev_qty + $in_qty - $out_qty;
        mysql_query("UPDATE m_stock_weight SET prev_weight = '{$prev_qty}', in_weight = '{$in_qty}', out_weight = '{$out_qty}', balance_weight = '{$balance_qty}', 
            update_user = '{$user}', update_date = NOW() WHERE m_stock_weight_id = '{$current['m_stock_weight_id']}'", $APP_CONNECTION);
    }else{
        $balance_qty = $prev_qty + $in_weight - $out_weight;
        mysql_query("INSERT INTO m_stock_weight (app_org_id,m_product_id,m_inout_date,prev_weight,in_weight,out_weight,balance_weight, 
            update_user,update_date) VALUES ('{$org_id}', '{$m_product_id}', '" . cgx_dmy2ymd($m_inout_date) . "', '{$prev_qty}', 
            '{$in_weight}', '{$out_weight}', '{$balance_qty}', '{$user}', NOW())", $APP_CONNECTION);
    }

    //update stock di tanggal-tanggal selanjutnya
    $sql = "select * from m_stock_weight where m_product_id = '{$m_product_id}' AND m_inout_date > '" . cgx_dmy2ymd($m_inout_date) . "' AND app_org_id = '{$org_id}' ORDER BY m_inout_date ";    
    $result = mysql_query($sql, $APP_CONNECTION);
    while ($hasil = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $before = cgx_fetch_table("SELECT mso1.* FROM m_stock_weight mso1 INNER JOIN (
            SELECT m_product_id, MAX(m_inout_date) m_inout_date FROM m_stock_weight 
            WHERE m_inout_date < '{$hasil['m_inout_date']}' AND app_org_id = '{$org_id}' GROUP BY m_product_id) mso2 
            ON(mso1.m_product_id = mso2.m_product_id AND mso1.m_inout_date = mso2.m_inout_date) 
            WHERE mso1.m_product_id = '{$m_product_id}' AND mso1.app_org_id = '{$org_id}' ");
        if($before){
            $prevqty = $before['balance_weight'];
        }else{
            $prevqty = '0';
        }
        $balanceqty = $prevqty + $hasil['in_weight'] - $hasil['out_weight'];;
        mysql_query("UPDATE m_stock_weight SET prev_weight = '{$prevqty}', balance_weight = '{$balanceqty}' 
            WHERE m_stock_weight_id = '{$hasil['m_stock_weight_id']}'", $APP_CONNECTION);
    }
    
    return $balance_qty;
}

function title_header($app_menu_id, $org_id){
    global $APP_CONNECTION;

    $result = mysql_query("SELECT title FROM app_menu WHERE app_menu_id = '{$app_menu_id}' AND app_org_id = '{$org_id}'", $APP_CONNECTION);
    $hasil = mysql_result($result,0);
    if($hasil){
        $title = $hasil;
    }else{
        $result = mysql_query("SELECT title FROM app_menu WHERE app_menu_id = '{$app_menu_id}' AND app_org_id = '-1'", $APP_CONNECTION);
        $hasil = mysql_result($result,0);
        if($hasil){
            $title = $hasil;
        }
    }

    return $title;
}

?>
