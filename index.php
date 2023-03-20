<?php

/**
 * Azwari Nugraha <nugraha@duabelas.org>
 * 03-MAR-2011
 */

require_once 'init.php';

if (!authenticated()) {
    header("Location: {$APP_BASE_URL}/login.php");
    exit;
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>PT. SRIREJEKI PERDANA STEEL</title>
    <link rel="icon" type="image/png" href="images/favicon.png" />
	<script type="text/javascript" src="<?php echo $APP_BASE_URL; ?>/js/dhtmlx/dhtmlxcommon.js"></script>
	<script type="text/javascript" src="<?php echo $APP_BASE_URL; ?>/js/dhtmlx/dhtmlxlayout.js"></script>
	<script type="text/javascript" src="<?php echo $APP_BASE_URL; ?>/js/dhtmlx/dhtmlxcontainer.js"></script>
    <script type="text/javascript" src="<?php echo $APP_BASE_URL; ?>/js/dhtmlx/dhtmlxtree.js"></script>
    <script type="text/javascript" src="<?php echo $APP_BASE_URL; ?>/js/jquery-1.10.2.min.js"></script> 
    <script type="text/javascript" src="<?php echo $APP_BASE_URL; ?>/js/jqueryui/js/jquery-ui-1.10.3.custom.min.js"></script>
    <link rel="stylesheet" type="text/css" href="<?php echo $APP_BASE_URL; ?>/js/jqueryui/css/ui-lightness/jquery-ui-1.10.3.custom.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $APP_BASE_URL; ?>/css/app.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $APP_BASE_URL; ?>/js/dhtmlx/custom/dhtmlx_custom.css">
	<link rel="stylesheet" type="text/css" href="<?php echo $APP_BASE_URL; ?>/js/dhtmlx/dhtmlxtree.css">
    <?php if (is_object($xajax)) $xajax->printJavascript(); ?>
</head>
<body>

<div id="divTitle" class="divTitle">
<table width="100%" border="0" style="border-bottom: 2px solid #3375C6; background-image: url('images/background.png');">
<tr>
    <td width="1"><div><img height="46px" style="margin: 0px 0px 0px 10px;" src="images/logo.gif" border="0"></div></td>
    <td width="70%" style="padding: 2px 18px; font-size: 22px; font-weight: bold; color: darkcyan;">
        PIPE APPLICATION
        <div style="font-size: 12px; padding-left: 150px; color: #444;">PT. Srirejeki Perdana Steel</div>
    </td>
    <td align="right" style="padding-right:10px;">
        <div style="text-align: right; line-height: 16px; font-size: 13px;">
            <a href='#' onclick="execMenu('password');">Ubah Password</a>
        <span style='margin: 6px; font-size: 12px; color: #444;'>|</span>
        <a href='<?php echo $APP_BASE_URL; ?>/action/logout.php'>Logout</a>
        <br><nobr>
        <span id="selectRole"><img onclick="xajax_changeRoleForm();" src="images/icon_gear.png" style="vertical-align: bottom; cursor: pointer;"> <b><?php echo role('role'); ?></b></span>
        <span style='margin: 6px; font-size: 12px; color: #444;'>|</span>
        <b><?php echo user('user_fullname'); ?></b></nobr>
        </div>
    </td>
</tr>
</table>
</div>

<div id="divMain" style="position:relative; width: 100%; height: 100%; "></div>
<div id="divFoot" class="divFoot">PT. SRIREJEKI PERDANA STEEL</div>
<script>

function execMenu(id) {
	if (id == 'logout') {
	    window.top.location = 'action/logout.php';
	} else {
	    dhxLayout.cells('b').attachURL("module.php?m=" + id);
	}
}

function refreshSize() {
	var winW = 630, winH = 460;
	if (parseInt(navigator.appVersion)>3) {
		if (navigator.appName == "Netscape") {
			winW = window.innerWidth;
			winH = window.innerHeight;
		}
		if (navigator.appName.indexOf("Microsoft") != -1) {
			winW = document.documentElement.clientWidth;
			winH = document.documentElement.clientHeight;
		}
	}
    var divMain = document.getElementById("divMain");
    var divTitle = document.getElementById("divTitle");
    var divMainHeight = winH - divTitle.offsetHeight - 21;
    divMain.style.height = divMainHeight + 'px';
}

refreshSize();

var dhxLayout = new dhtmlXLayoutObject("divMain", "2U");
dhxLayout.cells('a').setWidth(260);
dhxLayout.cells('a').setText('Menu Utama SPS');
dhxLayout.cells('b').hideHeader();
dhxLayout.cells('b').attachURL("module.php?m=dashboard");
dhxTree = dhxLayout.cells("a").attachTree();
dhxTree.setOnClickHandler(execMenu);
dhxTree.setImagePath("js/dhtmlx/imgs/csh_dhx_skyblue/");
dhxTree.loadXML("services/menu.php");

</script>
</body>
</html>
