<?php

function cgx_fetch_table($sql) {
    $r1 = mysql_query($sql, $GLOBALS['cgx_connection']);
    if (mysql_num_rows($r1) == 0) {
        $ret = null;
    } else {
        if(($d1 = mysql_fetch_array($r1)) != FALSE) {
            $ret = $d1;
        } else {
            $ret = NULL;
        }
    }
    mysql_free_result($r1);
    return $ret;
}

function cgx_get_connection($persistent) {
    @$connection = mysql_connect(
        "{$GLOBALS['APP_DB_HOST']}:{$GLOBALS['APP_DB_PORT']}",
        $GLOBALS['APP_DB_USER'],
        $GLOBALS['APP_DB_PASSWORD']);

    if ($connection) {
        if (! mysql_select_db($GLOBALS['APP_DB_NAME'], $connection)) {
            die(mysql_error($connection));
            return FALSE;
        } else {
            return $connection;
        }
    } else {
        die($php_errormsg);
    }
}

function cgx_form_radio($name, $sql, $default, $ext = '') {
    if (is_array($sql)) {
        foreach ($sql as $key => $value) {
            $id = "{$name}_{$key}";
            if ($key == $default) {
                $out .= "<input {$ext} id='{$id}' checked type='radio' name='{$name}' value='" . urlencode($key) . "' />";
            } else {
                $out .= "<input {$ext} id='{$id}' type='radio' name='{$name}' value='" . urlencode($key) . "' />";
            }
            $out .= "<label class='cgx_radio' for='{$id}'>{$value}</label>";
        }
    } else {
        $rs = mysql_query($sql, $GLOBALS['cgx_connection']);
        while (($dt = mysql_fetch_array($rs, MYSQL_NUM)) != FALSE) {
            if (strlen($dt[0]) == 0) continue;
            $id = "{$name}_{$dt[0]}";
            if ($dt[0] == $default) {
                $out .= "<input {$ext} id='{$id}' checked type='radio' name='{$name}' value='" . urlencode($dt[0]) . "' />";
            } else {
                $out .= "<input {$ext} id='{$id}' type='radio' name='{$name}' value='" . urlencode($dt[0]) . "' />";
            }
            $out .= "<label class='cgx_radio' for='{$id}'>{$dt[1]}</label>";
        }
        mysql_free_result($rs);
    }
    return $out;
}

function cgx_form_select($name, $sql, $default, $blank = TRUE, $ext = '') {
    $out  = "<select id='{$name}' name='{$name}' {$ext}>";
    if ($blank) $out .= "<option value=''></option>";
    if (is_array($sql)) {
        foreach ($sql as $key => $value) {
            if ($key == $default) {
                $out .= "<option selected value='" . urlencode($key) . "'>{$value}</option>";
            } else {
                $out .= "<option value='" . urlencode($key) . "'>{$value}</option>";
            }
        }
    } else {
        $rs = mysql_query($sql, $GLOBALS['cgx_connection']);
        while (($dt = mysql_fetch_array($rs, MYSQL_NUM)) != FALSE) {
            if (strlen($dt[0]) == 0) continue;
            if ($dt[0] == $default) {
                $out .= "<option selected value='" . urlencode($dt[0]) . "'>{$dt[1]}</option>";
            } else {
                $out .= "<option value='" . urlencode($dt[0]) . "'>{$dt[1]}</option>";
            }
        }
        mysql_free_result($rs);
    }
    $out .= "</select>";
    return $out;
}

function cgx_filter($name, $sql, $default, $blank = TRUE, $ext = '', $all = '(All)') {
    $out  = "<select id='{$name}' name='{$name}' {$ext} onchange='frmFILTER.submit()'>";
    if ($blank) $out .= "<option value=''>{$all}</option>";
    if (is_array($sql)) {
        foreach ($sql as $key => $value) {
            if ($key == $default) {
                $out .= "<option selected value='" . urlencode($key) . "'>{$value}</option>";
            } else {
                $out .= "<option value='" . urlencode($key) . "'>{$value}</option>";
            }
        }
    } else {
        $rs = mysql_query($sql, $GLOBALS['cgx_connection']);
        while (($dt = mysql_fetch_array($rs, MYSQL_NUM)) != FALSE) {
            if (strlen($dt[0]) == 0) continue;
            if ($dt[0] == $default) {
                $out .= "<option selected value='" . urlencode($dt[0]) . "'>{$dt[1]}</option>";
            } else {
                $out .= "<option value='" . urlencode($dt[0]) . "'>{$dt[1]}</option>";
            }
        }
        mysql_free_result($rs);
    }
    $out .= "</select>";
    return $out;
}

function cgx_ddmmyyyy($date) {
    return date('d-m-Y', strtotime($date));
}

function cgx_emptydate($date) {
    return empty($date) || $date == '0000-00-00';
}

function cgx_dmy2ymd($dmy) {
    $arr = explode("-", $dmy);
    $out = $arr[2] . '-' . $arr[1] . '-' . $arr[0];
    $out = cgx_emptydate($out) || $out == '--' ? '0000-00-00' : $out;
    return $out;
}

function cgx_format_timestamp($data) {
    $format = strlen($GLOBALS['APP_DATETIME_FORMAT']) > 0 ? $GLOBALS['APP_DATETIME_FORMAT'] : 'd-M-Y H:i';
    return date($format, strtotime($data['record'][$data['fieldName']]));
}

function cgx_format_date($data) {
    if (cgx_emptydate($data['record'][$data['fieldName']])) return NULL;
    $format = strlen($GLOBALS['APP_DATE_FORMAT']) > 0 ? $GLOBALS['APP_DATE_FORMAT'] : 'd-M-Y';
    return date($format, strtotime($data['record'][$data['fieldName']]));
}

function cgx_format_yesno($data) {
    $arr = array('Y' => 'Yes', 'N' => 'No');
    return $arr[$data['record'][$data['fieldName']]];
}

function cgx_format_money($data) {
    return number_format($data['record'][$data['fieldName']], 2);
}

function cgx_format_3digit($data) {
    return number_format($data['record'][$data['fieldName']]);
}

?>