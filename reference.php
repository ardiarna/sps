<?php

/*
 * Popup References
 * Azwari Nugraha <nugraha@duabelas.org>
 * Aug 17, 2013 11:19:49 AM
 */

require_once 'init.php';

$file = $APP_BASE_DIR . '/reference/' . $_REQUEST['s'] . '.ini';
if (!file_exists($file)) return;

$spec = parse_ini_file($file, TRUE);
$ref = $spec['reference'];
if (empty($ref)) return;

$ref['columns'] = array_map('trim', explode(",", $ref['columns']));
$ref['headers'] = array_map('trim', explode(",", $ref['headers']));
$ref['search'] = array_map('trim', explode(",", $ref['search']));
$ref['sort'] = array_map('trim', explode(",", $ref['sort']));
$ref['align'] = array_map('trim', explode(",", $ref['align']));
$ref['return'] = array_map('trim', explode(",", $ref['return']));

foreach ($ref['columns'] as $key => $value) {
    if ($ref['headers'][$key]) {
        $columns[$value] = $ref['headers'][$key];
    } else {
        $columns[$value] = $value;
    }
    if ($ref['align'][$key]) {
        $align[$value] = $ref['align'][$key];
    } else {
        $align[$value] = 'left';
    }
}

if (is_array($ref['sort'])) {
    foreach ($ref['sort'] as $colname) {
        $tmp = explode(' ', $colname);
        $sort[$tmp[0]] = strtoupper($tmp[1] == 'DESC') ? 'DESC' : 'ASC';
    }
}

foreach ($spec as $spec_id => $spec_value) {
    $tmp = array_map('trim', explode(':', $spec_id));
    if ($tmp[0] == 'param') {
        $param[$tmp[1]] = $spec_value;
    } elseif ($tmp[0] == 'filter') {
        $filter[$tmp[1]] = $spec_value;
    }
}

$rsx = mysql_query($ref['sql'], $APP_CONNECTION);
$num_fields = mysql_num_rows($rsx);
for ($n = 1; $n <= $num_fields; $n++) $field_type[mysql_field_name($rsx, $n)] = mysql_field_type($rsx, $n);

require_once 'Structures/DataGrid.php';
require_once 'HTML/Table.php';

function format_row($data) {
    $r = $data['fieldName'];
    if ($GLOBALS['field_type'][$r] == 'date' || $GLOBALS['field_type'][$r] == 'timestamp') {
        if (empty($data['record'][$data['fieldName']])) {
            $str = "";
        } else {
            $str = date($GLOBALS['APP_DATE_FORMAT'], strtotime($data['record'][$data['fieldName']]));
        }
    } elseif ($GLOBALS['field_type'][$r] == 'int') {
        $str = number_format($data['record'][$data['fieldName']], 0);
    } else {
        $str = $data['record'][$data['fieldName']];
    }
    return $str;
}

function format_check($data) {
    global $APP_BASE_URL;
    if (is_array($GLOBALS['ref']['return'])) {
        foreach ($GLOBALS['ref']['return'] as $r) {
            $lr = strtolower($r);
            if (empty($params)) {
                $params = "'{$data['record'][$lr]}'";
            } else {
                $params .= ", '{$data['record'][$lr]}'";
            }
        }
    }
    return "<img onmouseout=\"this.src='{$APP_BASE_URL}/images/icon_check2.png';\" onmouseover=\"this.src='{$APP_BASE_URL}/images/icon_check.png';\" style='cursor: pointer;' onclick=\"window.opener.{$GLOBALS['ref']['callback']}({$params}); window.close();\" src='{$APP_BASE_URL}/images/icon_check2.png'>";
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title><?php echo $ref['title']; ?></title>
        <link rel="icon" type="image/png" href="<?php echo $APP_BASE_URL; ?>/images/favicon.png" />
        <link rel="stylesheet" type="text/css" href="<?php echo $APP_BASE_URL; ?>/css/default.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $APP_BASE_URL; ?>/css/app.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $APP_BASE_URL; ?>/css/cgx.css" />
    </head>
    <body style="background: #DFEFFF;">
    <div style="padding: 4px;">
<?php

if ($ref['description-f']) {
    $description = "<div style='text-shadow: 0px 0px 0px; color: darkgreen; font-size: 12px; font-weight: normal;'>" . eval("return {$ref['description-f']};") . "</div>";
} elseif ($ref['description']) {
    $description = "<div style='text-shadow: 0px 0px 0px; color: darkgreen; font-size: 12px; font-weight: normal;'>{$ref['description']}</div>";
}

$_REQUEST['q'] = trim($_REQUEST['q']);
echo "<form name='frmFILTER' action='" . $_SERVER['SCRIPT_NAME'] . "'>\n";
echo "<input type='hidden' name='s' value='{$_REQUEST['s']}'>\n";
echo "<input type='hidden' name='p1' value='{$_REQUEST['p1']}'>\n";
echo "<input type='hidden' name='p2' value='{$_REQUEST['p2']}'>\n";
echo "<table border='0' id='bar' class='datagrid_bar' width='100%'><tr>\n";
echo "<td style='padding-left: 4px; color: green; font-size: 18px; text-shadow: 1px 1px 2px #BAFF60;'>{$ref['title']}{$description}</td>";
echo "<td align='right'><input type='text' size='20' name='q' value=\"{$_REQUEST['q']}\"></td>\n";
echo "<td width='1'><input title='Cari' type='image' src='images/icon_search.png' border='0' style='padding-right: 20px;'></td>\n";    
echo "<td></td>\n";
echo "<td width='20'></td>\n";
echo "</tr></table>\n";
echo "</form>\n";


$datagrid = new Structures_DataGrid($APP_DATAGRID_MAXROWS - 2);
$options = array('dsn' => $APP_DSN);
if (is_array($sort)) $datagrid->setDefaultSort($sort);


$ref['sql'] .= " WHERE 1 = 1";
if (is_array($filter)) {
    foreach ($filter as $fkey => $fval) {
        if ($fval['type'] == 'function') {
            $ref['sql'] .= " AND {$fkey} = '" . eval("return " . $fval['value'] . ';') . "'";
        } elseif ($fval['type'] == 'static') {
            $ref['sql'] .= " AND {$fkey} = {$fval['value']}";
        } elseif ($fval['type'] == 'staticu') {
            $ref['sql'] .= " AND {$fkey} > {$fval['value']}";
        } elseif ($fval['type'] == 'staticor') {
            $ref['sql'] .= " OR {$fkey} = {$fval['value']}";
        } elseif ($fval['type'] == 'null') {
            $ref['sql'] .= " AND {$fkey} IS NULL";
        } elseif ($fval['type'] == 'org-master') {
            $ref['sql'] .= " AND org_allowed({$fkey}) LIKE '%|{$_SESSION[$APP_ID]['role']['app_org_id']}|%'";
        } elseif ($fval['type'] == 'org-trx') {
            $ref['sql'] .= " AND {$fkey} = '{$_SESSION[$APP_ID]['role']['app_org_id']}'";
        }
    }
}

if (is_array($param)) {
    foreach ($param as $fkey => $fval) {
        $ref['sql'] .= " AND {$fkey} = '" . $_REQUEST[$fval['name']] . "'";
    }
}

if ($_REQUEST['q'] && is_array($ref['search'])) {
    $first = TRUE;
    $ref['sql'] .= " AND (";
    foreach ($ref['search'] as $colname) {
        if ($first) {
            $first = FALSE;
        } else {
            $ref['sql'] .= " OR ";
        }
        $ref['sql'] .= "UPPER({$colname}) LIKE '%" . strtoupper($_REQUEST['q']) . "%'";
    }
    $ref['sql'] .= ")";
}

if ($ref['debug'] || $APP_DEBUG_REFERENCE) {
    echo "<div class='info'>";
    echo "<div style='font-weight: bold; text-decoration: underline;'>SQL Statement</div>";
    echo $ref['sql'];
    echo "</div>";
}

$test = $datagrid->bind($ref['sql'], $options);
if (PEAR::isError($test)) {
    echo $test->getMessage();
}

if ($ref['callback']) {
    $datagrid->addColumn(new Structures_DataGrid_Column(
        NULL, 
        NULL,
        NULL,
        array('align' => 'center', 'width' => '30'),
        NULL,
        "format_check()"));
}

foreach ($columns as $colname => $coltitle) {
    $datagrid->addColumn(new Structures_DataGrid_Column(
        $coltitle, 
        strtolower($colname),
        strtoupper($colname),
        array('align' => $align[$colname]),
        NULL,
        "format_row()"));
}

$table = new HTML_Table($cgx_TableAttribs);
$tableHeader = & $table->getHeader();
$tableBody = & $table->getBody();

$test = $datagrid->fill($table, $cgx_RendererOptions);
if (PEAR::isError($test)) {
    echo $test->getMessage();
}

$tableHeader->setRowAttributes(0, $cgx_HeaderAttribs);
$tableBody->altRowAttributes(0, $cgx_EvenRowAttribs, $cgx_OddRowAttribs, TRUE);

echo "<div class='datagrid_background'>\n";
echo $table->toHtml();
echo "</div>\n";

echo "<table width='100%'><tr>";
echo "<td class='datagrid_pager'>Data berjumlah " . number_format($datagrid->getRecordCount()). " baris</td>";
echo "<td align='right' class='datagrid_pager'>\n";
$test = $datagrid->render(DATAGRID_RENDER_PAGER);
if (PEAR::isError($test)) {
    echo $test->getMessage();
}
echo "</td></tr></table>\n";


?>
    </div>
    </body>
</html>