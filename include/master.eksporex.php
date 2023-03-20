<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 07/01/2014 13:47:15
 */


echo "<div class='title'>Eksport Excell</div>";

    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';

    echo "<form name='frmFILTER' action='{$_SERVER['SCRIPT_NAME']}'>\n";
    echo "<input type='hidden' name='m' value='{$_REQUEST['m']}'>\n";
    echo "<table id='bar' class='datagrid_bar' width='100%'><tr>\n";
    echo "<td width='1' class='datagrid_bar_icon'><a title='Export semua data ke CSV' href='action/master.eksporex.php?mode=export-all'><img border='0' src='images/icon_csv.png'></a></td>\n";
    echo "<td>Export</td>";
    echo "</tr></table>\n";
    echo "</form>\n";

    if ($_SESSION[$GLOBALS['APP_ID']]['master.eksporex']['error']) {
        echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['master.eksporex']['error']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['master.eksporex']['error']);
    }

    if ($_SESSION[$GLOBALS['APP_ID']]['master.eksporex']['info']) {
        echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['master.eksporex']['info']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['master.eksporex']['info']);
    }


