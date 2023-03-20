<?php

/*
 * forecast
 * Azwari Nugraha <nugraha@duabelas.org>
 * Jan 26, 2014 6:04:53 PM
 */

function showLines($m_forecast_id, $mode = NULL) {
    global $APP_CONNECTION;
    $res = new xajaxResponse();
    if ($m_forecast_id == 0) return $res;
    
    $forecast = npl_fetch_table(
            "SELECT * "
            . "FROM m_forecast " 
            . "WHERE m_forecast_id = '{$m_forecast_id}'");
    $ds = strtotime($forecast['period_start']);
    $de = strtotime($forecast['period_end']);
    $dt = $ds;
    $zz = 1;
    $periods = array();
    while ($dt <= $de) {
        $zz++;
        $periods[] = $dt;
        $dt = mktime(0, 0, 0, date('n', $dt) + 1, 1, date('Y', $dt));
        if ($zz > 24) break;
    }
    
    $rsx = mysql_query(
            "SELECT * FROM m_forecast_line WHERE m_forecast_id = '{$m_forecast_id}'",
            $APP_CONNECTION);
    while ($dtx = mysql_fetch_array($rsx, MYSQL_ASSOC)) {
        $lines[$dtx['m_product_id']][$dtx['period']] = $dtx['qty'];
    }
    mysql_free_result($rsx);
    
    $html .= "<div class='datagrid_background'>";
    $html .= "<table width='100%' cellspacing='1' cellpadding='2'>";
    $html .= "<tr>";
    $html .= "<th class='datagrid_header'>Item<br>Number</th>";
    $html .= "<th class='datagrid_header'>Spec</th>";
    $html .= "<th class='datagrid_header'>Dimension</th>";
    foreach ($periods as $p) {
        $html .= "<th class='datagrid_header'>" . date('M', $p) . '<br>' . date('Y', $p) . "</th>";
    }
    $html .= "</tr>";
    
    $rsx = mysql_query(
            "SELECT DISTINCT m_product_id, product_code, spec, od, thickness, length "
            . "FROM m_forecast_line "
            . "JOIN m_product USING (m_product_id) "
            . "WHERE m_forecast_id = '{$m_forecast_id}'",
            $APP_CONNECTION);
    while ($dtx = mysql_fetch_array($rsx, MYSQL_ASSOC)) {
        $html .= "<tr bgcolor='#ffffff'>";
        $html .= "<td>{$dtx['product_code']}</td>";
        $html .= "<td>{$dtx['spec']}</td>";
        $html .= "<td>&empty;{$dtx['od']} x {$dtx['thickness']} x {$dtx['length']}</td>";
        foreach ($periods as $p) {
            $value = (int) $lines[$dtx['m_product_id']][date('Y-m-d', $p)];
            if ($mode == 'edit') {
                $html .= "<td width='1'><input name='fc[{$dtx['m_product_id']}][" . date('Y-m-d', $p) . "]' type='text' style='width: 50px; text-align: right;' value='{$value}'></td>";
            } else {
                $html .= "<td align='right'>" . number_format($value) . "</td>";
            }
        }
        if ($mode == 'edit') $html .= "<td width='24' align='center'><img onclick=\"xajax_delProduct({$m_forecast_id}, {$dtx['m_product_id']});\" src='images/icon_delete.png' style='cursor: pointer;' title='Hapus item'></td>";
        $html .= "</tr>";
    }
    mysql_free_result($rsx);
    
    $html .= "</table>";
    $html .= "</div>";
    
    $res->assign('area-lines', 'innerHTML', $html);
    return $res;
}

function addProduct($m_forecast_id, $m_product_id) {
    global $APP_CONNECTION;
    $forecast = npl_fetch_table(
            "SELECT * "
            . "FROM m_forecast " 
            . "WHERE m_forecast_id = '{$m_forecast_id}'");
    $res = new xajaxResponse();
    mysql_query("INSERT INTO m_forecast_line (m_forecast_id, period, m_product_id, qty) " .
            "VALUES ('{$m_forecast_id}', '{$forecast['period_start']}', '{$m_product_id}', 0)",
            $APP_CONNECTION);
    $res->script("window.location = 'module.php?m=forecast&pkey[m_forecast_id]={$m_forecast_id}&mode=edit';");
    return $res;
}

function delProduct($m_forecast_id, $m_product_id) {
    global $APP_CONNECTION;
    mysql_query(
            "DELETE FROM m_forecast_line " 
            . "WHERE m_forecast_id = '{$m_forecast_id}' AND m_product_id = '{$m_product_id}'",
            $APP_CONNECTION);
    $res = new xajaxResponse();
    $res->script("window.location = 'module.php?m=forecast&pkey[m_forecast_id]={$m_forecast_id}&mode=edit';");
    return $res;
}

function saveFC($data) {
    global $APP_CONNECTION;
    
    $res = new xajaxResponse();
    if (empty($data['c_bpartner_id'])) {
        $error = "Customer tidak boleh kosong";
    } elseif (empty($data['period-s']) || empty($data['period-e'])) {
        $error = "Periode tidak boleh kosong";
    }
    if ($error) {
        $res->alert($error);
        return $res;
    }
    
    $data['period-s'] = npl_period2mysqldate($data['period-s']);
    $data['period-e'] = npl_period2mysqldate($data['period-e']);
    
    if ($data['m_forecast_id'] > 0) {
        mysql_query("UPDATE m_forecast SET " .
                "c_bpartner_id = '{$data['c_bpartner_id']}', " .
                "period_start = '{$data['period-s']}', " .
                "period_end = '{$data['period-e']}', " .
                "notes = '{$data['notes']}' " .
                "WHERE m_forecast_id = '{$data['m_forecast_id']}'",
                $APP_CONNECTION);
        if (is_array($data['fc'])) {
            foreach ($data['fc'] as $prod => $d1) {
                foreach ($d1 as $period => $qty) {
                    $lastes = npl_fetch_table(
                        "SELECT m_forecast_line_id FROM m_forecast_line mfl " 
                        . "JOIN m_forecast mf USING (m_forecast_id) "
                        . "WHERE mf.c_bpartner_id = '{$data['c_bpartner_id']}' "
                        . "AND mfl.m_product_id = '{$prod}' "
                        . "AND mfl.period = '{$period}' AND latest='Y'"); 
                    mysql_query("UPDATE m_forecast_line set latest = 'N' where m_forecast_line_id='{$lastes['m_forecast_line_id']}'", $APP_CONNECTION);        
                }
            }
            foreach ($data['fc'] as $prod => $d1) {
                foreach ($d1 as $period => $qty) {
                    mysql_query(
                            "INSERT INTO m_forecast_line (m_forecast_id, period, m_product_id, latest, qty) " .
                            "VALUES ('{$data['m_forecast_id']}', '{$period}', '{$prod}', 'Y', '{$qty}') " .
                            "ON DUPLICATE KEY UPDATE qty = '{$qty}', latest='Y'", $APP_CONNECTION);        
                }
            }
            
        }
         $res->script("window.location = 'module.php?m=forecast&pkey[m_forecast_id]={$data['m_forecast_id']}';");  

    } else {
        require_once 'lib/class.Penomoran.php';
        $nomor = new Penomoran();
        $document_no = $nomor->urut('FC', -1);
        mysql_query(
                "INSERT INTO m_forecast (document_no, c_bpartner_id, period_start, period_end, notes) " .
                "VALUES ('{$document_no}', '{$data['c_bpartner_id']}', '{$data['period-s']}', '{$data['period-e']}', '{$data['notes']}')",
                $APP_CONNECTION);
        $data['m_forecast_id'] = mysql_insert_id($APP_CONNECTION);
        $res->script("window.location = 'module.php?m=forecast&pkey[m_forecast_id]={$data['m_forecast_id']}&mode=edit';");
    }
    
    return $res;
}

$xajax->register(XAJAX_FUNCTION, 'saveFC');
$xajax->register(XAJAX_FUNCTION, 'addProduct');
$xajax->register(XAJAX_FUNCTION, 'delProduct');
$xajax->register(XAJAX_FUNCTION, 'showLines');

?>