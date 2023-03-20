<?php

/*
 * password
 * Azwari Nugraha <nugraha@duabelas.org>
 * Jan 7, 2014 11:48:16 PM
 */

function changePassword($data) {
    global $APP_CONNECTION;
    $res = new xajaxResponse();

    mysql_query(
            "UPDATE app_user SET user_fullname = '{$data['name']}' " .
            "WHERE user_id = '" . user() . "'",
            $APP_CONNECTION);
   
    if ($data['reset-password']) {
        if (user('user_password') != md5($data['password1'])) {
            $res->alert("Password salah!");
            return $res;
        } elseif ($data['password2'] != $data['password3']) {
            $res->alert("Konfirmasi password tidak sama!");
            return $res;
        } elseif (empty($data['password2'])) {
            $res->alert("Password tidak boleh kosong!");
            return $res;
        } else {
            mysql_query(
                    "UPDATE app_user SET user_password = '" . md5($data['password2']) . "' " .
                    "WHERE user_id = '" . user() . "'",
                    $APP_CONNECTION);
        }
    }

    $res->alert("Perubahan data profil pengguna telah berhasil disimpan.");
    $res->redirect("module.php?m=password");
    return $res;
}

$xajax->register(XAJAX_FUNCTION, "changePassword");

?>