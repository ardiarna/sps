<?php

/*
 * logout
 * Azwari Nugraha <nugraha@duabelas.org>
 * Feb 28, 2013 6:53:41 PM
 */

require_once '../init.php';

unset($_SESSION[$APP_ID]);
header("Location: ../");

?>