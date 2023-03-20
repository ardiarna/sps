<?php

/*
 * dashboard
 * Azwari Nugraha <nugraha@duabelas.org>
 * Oct 9, 2013 11:42:34 AM
 */

function portlet($title, $sql, $fields, $align, $width, $link_prefix = NULL, $link_field = NULL) {
    global $APP_CONNECTION;
    
    if (is_array($title)) {
        $html .= "<table width='100%' cellspacing='0' cellpadding='0'><tr>";
        $html .= "<td class='portlet-title' valign='bottom'>{$title[0]}</td>";
        $html .= "<td class='portlet-info' valign='bottom' align='right'>{$title[1]}</td>";
        $html .= "</tr></table>";
    } else {
        $html .= "<div class='portlet-title'>{$title}</div>";
    }
    $html .= "<table width='100%' class='portlet' cellspacing='1' cellpadding='4'>";
    $html .= "<tr>";
    foreach ($fields as $f) $html .= "<th>{$f}</th>";
    $html .= "</tr>";
    
    $rsx = mysql_query($sql, $APP_CONNECTION);
    while ($dtx = mysql_fetch_array($rsx)) {
        $class = $class == 'a' ? 'b' : 'a';
        $html .= "<tr>";
        if ($link_field) {
            foreach ($fields as $fname => $fhead) {
                $href = $link_prefix . $dtx[$link_field];
                $html .= "<td class='{$class}' align='{$align[$fname]}' width='{$width[$fname]}'><a href='{$href}'>{$dtx[$fname]}</a></td>";
            }
        } else {
            foreach ($fields as $fname => $fhead) $html .= "<td class='{$class}' align='{$align[$fname]}' width='{$width[$fname]}'>{$dtx[$fname]}</td>";
        }
        $html .= "</tr>";
    }
    mysql_free_result($rsx);
    
    $html .= "</table>";
    return $html;
}

?>