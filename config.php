<?php

/*
 * Configuration File
 * Azwari Nugraha <nugraha@duabelas.org>
 * Monday, October 14 2013 15:35:44
 * 
 */


// database connection
$APP_DB_HOST                = "localhost";
$APP_DB_PORT                = 3306;
$APP_DB_USER                = "root";
$APP_DB_PASSWORD            = "root";
$APP_DB_NAME                = "sps";


// general setting
$APP_TIMEOUT                = 300;
$APP_DATAGRID_MAXROWS       = 20;
$APP_TIMEZONE               = 'Asia/Jakarta';
$APP_ID                     = "7031b6518a99ce96a9f2b5991fc49551";
$APP_BASE_DIR               = "/Applications/MAMP/htdocs/sps";
$APP_BASE_URL               = "http://" . $_SERVER['SERVER_NAME'] . '/sps';
$APP_PASSWORD_MIN_LENGTH    = 6;
$APP_DEFAULT_LANGUAGE       = 'id';

// 1 = senin, 7 = minggu
$APP_WORKING_DAYS           = array(1, 2, 3, 4, 5, 6);

// application configuration
$APP_DATE_FORMAT            = "d-m-Y";
$APP_DATE_FORMAT_JAVA       = "dd-mm-yy";
$APP_DATETIME_FORMAT        = "d-m-Y H:i";

// jasper report setting
$JASPER_HOST                = "localhost";
$JASPER_PORT                = 8080;
$JASPER_USERNAME            = "jasperadmin";
$JASPER_PASSWORD            = "spsjasperadmin";
$JASPER_PATH                = "/jasperserver";
$JASPER_IMAGES              = $APP_BASE_URL . '/images/report';

// debug
// $APP_DEBUG_REFERENCE        = TRUE;

?>
