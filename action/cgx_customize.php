<?php

require_once '../init.php';

if (is_array($_REQUEST['col'])) {
    unset($_SESSION[$APP_ID][$_REQUEST['dg_name']]['columns']);
    foreach ($_REQUEST['col'] as $colname => $colvalue) {
        $_SESSION[$APP_ID][$_REQUEST['dg_name']]['columns'][$colname] = 1;
    }
}

header("Location: " . urldecode($_REQUEST['back']));
exit;

?>