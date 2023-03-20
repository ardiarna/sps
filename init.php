<?php

/*
 * init
 * Azwari Nugraha <nugraha@duabelas.org>
 * Jan 28, 2013 12:47:05 PM
 */
$root_path = dirname(__FILE__);
require_once $root_path . '/config.php';
session_start();

date_default_timezone_set($APP_TIMEZONE);
ini_set('track_errors', true);
set_time_limit(0);
set_include_path("." .
    PATH_SEPARATOR . $APP_BASE_DIR .
    PATH_SEPARATOR . $APP_BASE_DIR . '/lib' .
    PATH_SEPARATOR . $APP_BASE_DIR . '/lib/pear');

require_once $APP_BASE_DIR . '/lib/default.php';
require_once $APP_BASE_DIR . '/lib/cgx.php';

// database connection
$APP_DSN = "mysql://{$APP_DB_USER}:{$APP_DB_PASSWORD}@{$APP_DB_HOST}:{$APP_DB_PORT}/{$APP_DB_NAME}";
@$APP_CONNECTION = mysql_pconnect("{$APP_DB_HOST}:{$APP_DB_PORT}", $APP_DB_USER, $APP_DB_PASSWORD);
if ($APP_CONNECTION) {
    if (! mysql_select_db($APP_DB_NAME, $APP_CONNECTION)) {
        die(mysql_error($APP_CONNECTION));
    }
} else {
    die($php_errormsg);
}
@mysql_query("SET NAMES 'utf8'");

// cgx related config
$cgx_connection = $APP_CONNECTION;
$cgx_dsn = $APP_DSN;
$cgx_max_rows = $APP_DATAGRID_MAXROWS;
$cgx_TableAttribs = array('width' => '100%', 'cellspacing' => '1', 'cellpadding' => '2');
$cgx_HeaderAttribs = array('class' => 'datagrid_header');
$cgx_EvenRowAttribs = array('bgcolor' => '#FFFFFF', 'style' => 'font-size: 11px;');
$cgx_OddRowAttribs = array('bgcolor' => '#EEEEEE', 'style' => 'font-size: 11px;');
$cgx_RendererOptions = array('sortIconASC' => '&uArr;', 'sortIconDESC' => '&dArr;');

// jasper report setting
$JASPER_MIME                = array(
    'html'  => 'text/html',
    'pdf'   => 'application/pdf',
    'xls'   => 'application/vnd.ms-excel',
    'xlsx'  => 'application/vnd.ms-excel',
    'docx'  => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'csv'   => 'text/csv',
    'rtf'   => 'text/rtf',
    'odt'   => 'application/vnd.oasis.opendocument.text',
    'ods'   => 'application/vnd.oasis.opendocument.spreadsheet'
);

$SO_STATUS = array(
    'O' => 'Open',
    'C' => 'Closed'
);

// chart colors
$app_chart_colors = array('#ff8c00',
    '#ff0033','#ffcc00','#3333cc','#56B9F9','#C9198D',
    '#00CC00','#9900FF','#006600','#00FFFF');

//
$mandatory = "<img src='{$APP_BASE_URL}/images/icon_mandatory.gif'>";
$autonumber = '[ AUTONUMBER ]';

// xajax
if (isset($_SERVER['HTTP_USER_AGENT'])) {
    require_once $APP_BASE_DIR . '/lib/xajax_core/xajax.inc.php';
    $xajax = new xajax();
    if ($_REQUEST['dbg'] == 1) $xajax->setFlag('debug', TRUE);
    $xajax->configure('javascript URI', $APP_BASE_URL . '/js/');
    require_once $APP_BASE_DIR . '/xajax/default.php';
    if (file_exists($APP_BASE_DIR . '/xajax/' . $_REQUEST['m'] . '.php')) {
        require_once $APP_BASE_DIR . '/xajax/' . $_REQUEST['m'] . '.php';
    }
    $xajax->processRequest();
}

?>
