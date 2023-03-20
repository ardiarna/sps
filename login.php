<?php

require_once 'init.php';

?><!DOCTYPE html>
<html>
    <head>
        <title>PT. SRIREJEKI PERDANA STEEL</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="stylesheet" type="text/css" href="css/default.css">
        <link rel="stylesheet" type="text/css" href="css/login.css">
    </head>
    <body>
        <form action='action/login.php' method='post'>
        <table class='login' cellspacing='0' cellpadding='0' align="center" style="">
            <tr>
                <td style='height: 100px; background-color: rgba(255,255,255,0.7);'>
                    <table cellpadding='10'>
                        <tr>
                            <td><img src='images/logo.gif' width='60'></td>
                            <td>
                                <div style='font-weight: bold; color: darkcyan; font-size: 28px; text-shadow: 0px 0px 14px #fff;'>PIPE APPLICATION</div>
                                <div style='color: #222; font-weight: bold; font-size: 16px;'>PT. SRIREJEKI PERDANA STEEL</div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td style='background-color: rgba(255,255,255,0.7);' align='center'>
                    <table>
                        <tr>
                            <td></td>
                            <td style='color: red; font-size: 12px;'><?php echo $_SESSION[$APP_ID]['auth-message']; unset($_SESSION[$APP_ID]['auth-message']); ?></td>
                        </tr>
                        <tr>
                            <td>Identitas Pengguna</td>
                            <td><input name='username' type='text' style='width: 160px;'></td>
                        </tr>
                        <tr>
                            <td>Kata Sandi</td>
                            <td><input name='password' type='password' style='width: 160px;'></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td><input type='submit' value=' Login '></td>
                        </tr>
                    </table>
                    
        <div style='text-align: center; font-size: 11px; padding-top: 20px; color: #222;'>
            PT. SRIREJEKI PERDANA STEEL<br>
            Kampung Gombong, Dusun III RT.02/RW.05<br>
            Bekasi, Jawa Barat, Indonesia 17550
        </div>
                    
                    
                </td>
            </tr>
        </table>
        </form>
    </body>
</html>
