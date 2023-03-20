<?php

require_once 'init.php';

?><!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>PT. SRIREJEKI PERDANA STEEL</title>
        <script type="text/javascript" src="<?php echo $APP_BASE_URL; ?>/js/jquery-1.10.2.min.js"></script> 
        <script type="text/javascript" src="<?php echo $APP_BASE_URL; ?>/js/jquery-ui-1.10.3.custom.min.js"></script>
        <script type="text/javascript" src="<?php echo $APP_BASE_URL; ?>/js/FusionCharts.js"></script>
        <script type="text/javascript" src="<?php echo $APP_BASE_URL; ?>/js/default.js"></script>
        <link rel="stylesheet" type="text/css" href="<?php echo $APP_BASE_URL; ?>/css/ui-lightness/jquery-ui-1.10.3.custom.css">
        <link rel="icon" type="image/png" href="<?php echo $APP_BASE_URL; ?>/images/favicon.png" />
        <link rel="stylesheet" type="text/css" href="<?php echo $APP_BASE_URL; ?>/css/app.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $APP_BASE_URL; ?>/css/cgx.css" />
        <?php if (is_object($xajax)) $xajax->printJavascript(); ?>
    </head>
    <body style="background: #fff;">
        <div class="app-content">
            <?php
            
            if (authenticated()) {
                $content = $_REQUEST['m'];
                $content = empty($content) ? 'dashboard' : $content;
                include_once $APP_BASE_DIR . '/include/' . $content . '.php';
            } else {
                echo "<script>";
                echo "window.top.location = '{$APP_BASE_URL}';";
                echo "</script>";
            }
            
            ?>
        </div>
    </body>
</html>