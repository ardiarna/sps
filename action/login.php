<?php

/*
 * login
 * Azwari Nugraha <nugraha@duabelas.org>
 * Feb 28, 2013 6:46:35 PM
 */

require_once '../init.php';

if (login($_REQUEST['username'], $_REQUEST['password'])) {
    header("Location: ../");
    exit;
} else {
    header("Location: ../login.php");
    exit;
}

?>