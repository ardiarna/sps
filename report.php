<?php

/*
 * Jasper Report
 * Azwari Nugraha <nugraha@duabelas.org>
 * Sep 17, 2013 10:50:24 PM
 */

require_once 'init.php';
require_once 'lib/Jasper/JasperClient.php';

authenticated() || die('akses ditolak');

if ($_REQUEST['path']) {
    $rsession['path'] = $_REQUEST['path'];
    $rsession_id = md5($_REQUEST['path']);
    $rsession['param'] = $_REQUEST['param'];
    $rsession['fname'] = $_REQUEST['fname'];
    $_SESSION[$APP_ID][$rsession_id] = $rsession;
    
    // if direct download / export
    if (!empty($_REQUEST['type']) && $_REQUEST['type'] != 'html') $direct_type = '&type=' . $_REQUEST['type'];
    
    header("Location: {$_SERVER['SCRIPT_NAME']}?session={$rsession_id}{$direct_type}");
    exit;
}

$rsession_id = $_REQUEST['session'];
$rsession = $_SESSION[$APP_ID][$rsession_id];
$rsession['path'] || die('report tidak ditemukan');

$rpath = $rsession['path'];
$param = $rsession['param'];
$fname = $rsession['fname'];
$rtype = $_REQUEST['type'] ? $_REQUEST['type'] : 'html';
$page  = empty($_REQUEST['page']) ? 1 : $_REQUEST['page'];
$param['REPORT_IMAGE_PREFIX'] = $JASPER_IMAGES;

$jasper = new Jasper\JasperClient(
        $JASPER_HOST,
        $JASPER_PORT,
        $JASPER_USERNAME,
        $JASPER_PASSWORD,
        $JASPER_PATH);

try {
    $report = $jasper->runReport($rpath, $rtype, $rtype == 'html' ? $page : null, null, $param);
    if ($page > 0) {
        $_SESSION[$APP_ID][$rsession_id]['report-page-last-ok'] = $page;
    } else {
        unset($_SESSION[$APP_ID][$rsession_id]['report-page-last-ok']);
    }
} catch (Exception $e) {
    if ($_SESSION[$APP_ID][$rsession_id]['report-page-last-ok']) {
        header("Location: {$_SERVER['SCRIPT_NAME']}?&session={$rsession_id}&page={$_SESSION[$APP_ID][$rsession_id]['report-page-last-ok']}");
        exit;
    } else {
        die($e->getMessage());
    }
}

if ($rtype == 'html') {
    $dom = new DOMDocument;
    $mock = new DOMDocument;
    $dom->loadHTML($report);
    $body = $dom->getElementsByTagName('body')->item(0);
    foreach ($body->childNodes as $child){
        $mock->appendChild($mock->importNode($child, true));
    }
    $body = $mock->saveHTML();

    $export_url = $_SERVER['SCRIPT_NAME'] . '?session=' . $rsession_id;
    $prev_url = $_SERVER['SCRIPT_NAME'] . '?session=' . $rsession_id . '&page=' . ($page-1);
    $next_url = $_SERVER['SCRIPT_NAME'] . '?session=' . $rsession_id . '&page=' . ($page+1);
    
    $toolbar = <<<EOL
    <div class='report-toolbar'>
        <table width='100%' border='0' cellpadding='0'>
            <tr>
            <td width='40' align='center' style='padding-top: 3px; cursor: pointer;'><img onclick="window.location = '{$prev_url}';" src='images/rpt-prev.png'></td>
            <td width='14' align='center'>{$page}</td>
            <td width='40' align='center' style='padding-top: 3px; cursor: pointer;'><img onclick="window.location = '{$next_url}';" src='images/rpt-next.png'></td>
            <td></td>
            <td width='40' align='center'><img alt='Export' title='Export' style='cursor: pointer;' src='images/rpt-export.png' data-dropdown='#dropdown-1'></td>
            </tr>
        </table>
    </div>
            
    <div id='dropdown-1' class='dropdown dropdown-tip dropdown-anchor-right'>
        <ul class='dropdown-menu'>
            <li><a href='{$export_url}&type=pdf'>Adobe PDF (.pdf)</a></li>
            <li><a href='{$export_url}&type=xls'>Microsoft Excel (.xls)</a></li>
            <li><a href='{$export_url}&type=xlsx'>Microsoft Excel 2007 (.xlsx)</a></li>
            <li><a href='{$export_url}&type=docx'>Microsoft Word 2007 (.docx)</a></li>
            <li><a href='{$export_url}&type=ods'>Open Document Spreadsheet (.ods)</a></li>
            <li><a href='{$export_url}&type=odt'>Open Document Text (.odt)</a></li>
            <li><a href='{$export_url}&type=csv'>Comma Separated Values (.csv)</a></li>
            <li><a href='{$export_url}&type=rtf'>Rich Text Format (.rtf)</a></li>
        </ul>
    </div>
EOL;
    
    $html  = "<!DOCTYPE html>\n";
    $html .= "<html>\n";
    $html .= "<head>\n";
    $html .= "<script type='text/javascript' src='{$APP_BASE_URL}/js/jquery-1.9.0.js'></script>\n";
    $html .= "<script type='text/javascript' src='{$APP_BASE_URL}/js/jquery-ui-1.10.3.custom.min.js'></script>\n";
    $html .= "<script type='text/javascript' src='{$APP_BASE_URL}/js/jquery.dropdown.min.js'></script>\n";
    $html .= "<link rel='stylesheet' type='text/css' href='{$APP_BASE_URL}/css/report.css' />\n";
    $html .= "<link rel='stylesheet' type='text/css' href='{$APP_BASE_URL}/css/jquery.dropdown.css' />\n";
    $html .= "<link rel='stylesheet' type='text/css' href='{$APP_BASE_URL}/css/ui-lightness/jquery-ui-1.10.3.custom.css' />\n";
    $html .= "</head>\n";
    $html .= "<body>\n";
    $html .= $toolbar;
    $html .= $body;
    $html .= "</body>\n";
    $html .= "</html>\n";
    echo $html;
} elseif ($JASPER_MIME[$rtype]) {
    if ($fname) {
        $filename = $fname . '.' . $rtype;
    } else {
        $filename = end(explode('/', $rpath)) . '.' . $rtype;
    }
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Description: File Transfer');
    header('Content-Disposition: attachment; filename='.$filename);
    header('Content-Transfer-Encoding: binary');
    header("Content-Type: {$JASPER_MIME[$rtype]}");
    header('Content-Length: ' . strlen($report));
    echo $report;
}

?>