<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.org>
 * Feb 20, 2014 3:58:17 PM
 */

function aasort(&$array, $key) {
    $sorter=array();
    $ret=array();
    reset($array);
    foreach ($array as $ii => $va) {
        $sorter[$ii]=$va[$key];
    }
    asort($sorter);
    foreach ($sorter as $ii => $va) {
        $ret[$ii]=$array[$ii];
    }
    $array=$ret;
}

function allocShow($c_po_line_id) {
    global $APP_ID, $APP_DATE_FORMAT_JAVA;
    
    $line = npl_fetch_table("SELECT * FROM c_po_line WHERE c_po_line_id = '{$c_po_line_id}'");
    $alloc = $_SESSION[$APP_ID]['po-alloc'][$c_po_line_id];
    if (empty($alloc)) {
        $alloc[0] = array(
            'schedule' => NULL,
            'qty' => $line['quantity']
        );
        $_SESSION[$APP_ID]['po-alloc'][$c_po_line_id] = $alloc;
    }
    $total = 0;
    foreach ($alloc as $a) $total += $a['qty'];

    $html .= "<table width='100%' cellspacing='1' cellpadding='2' style='background-color: #DFEFFF;'>";
    $html .= "<tr style='background-color: #DFEFFF;'>";
    $html .= "<th width='1'>Jadwal</th>";
    $html .= "<th width='1'>Jumlah</th>";
    $html .= "</tr>";
    foreach ($alloc as $i => $a) {
        $html .= "<tr style='background-color: #fff;'>";
        $html .= "<td><input name='schedule[{$c_po_line_id}][{$i}]' id='tgl-{$c_po_line_id}-{$i}' value=\"{$a['schedule']}\" type='text' style='width: 120px; text-align: center;'></td>";
        $html .= "<td><input name='quantity[{$c_po_line_id}][{$i}]' type='text' style='width: 100px; text-align: right;' value=\"{$a['qty']}\" onblur=\"xajax_allocUpdateCell('{$c_po_line_id}', '{$i}', this.value);\"></td>";
        if ($i == 0) {
            $html .= "<td width='1'><img src='images/icon_delete2.png'></td>";
        } else {
            $html .= "<td width='1'><img onclick=\"xajax_allocDel('{$c_po_line_id}', '{$i}');\" style='cursor: pointer;' src='images/icon_delete.png'></td>";
        }
        $html .= "</tr>";
    }
    $html .= "<tr style='background-color: #eee;'>";
    $html .= "<td align='right'>Total</td>";
    if ($line['quantity'] == $total) {
        $style = "color: darkgreen;";
    } else {
        $style = "color: red;";
    }
    $html .= "<td><input id='total-{$c_po_line_id}' type='text' readonly='readonly' style='{$style}width: 100px; text-align: right;' value=\"" . number_format($total) . "\"></td>";
    $html .= "<td width='1'><img onclick=\"xajax_allocAdd('{$c_po_line_id}')\" style='cursor: pointer;' src='images/icon_add.png'></td>";
    $html .= "</tr>";
    $html .= "</table>";
    
    $res = new xajaxResponse();
    $res->assign('alloc-' . $c_po_line_id, 'innerHTML', $html);
    foreach ($alloc as $i => $a) $res->script("$('#tgl-{$c_po_line_id}-{$i}').datepicker({dateFormat: '{$APP_DATE_FORMAT_JAVA}', onSelect: function(dateText) { xajax_allocUpdateCell('{$c_po_line_id}', '{$i}', dateText, 'd'); }});");
    return $res;
}

function allocAdd($c_po_line_id) {
    global $APP_ID;
    $alloc = $_SESSION[$APP_ID]['po-alloc'][$c_po_line_id];
    $alloc[] = array(
            'schedule' => NULL,
            'qty' => 0
    );
    $_SESSION[$APP_ID]['po-alloc'][$c_po_line_id] = $alloc;
    
    $res = new xajaxResponse();
    $res->script("xajax_allocShow('{$c_po_line_id}')");
    return $res;
}

function allocDel($c_po_line_id, $index) {
    global $APP_ID;
    unset($_SESSION[$APP_ID]['po-alloc'][$c_po_line_id][$index]);
    
    $res = new xajaxResponse();
    $res->script("xajax_allocShow('{$c_po_line_id}')");
    return $res;
}

function allocUpdateCell($c_po_line_id, $index, $value, $type = 'n') {
    global $APP_ID;
    $alloc = $_SESSION[$APP_ID]['po-alloc'][$c_po_line_id];
    if ($type == 'd') {
        $alloc[$index]['schedule'] = $value;
    } else {
        $alloc[$index]['qty'] = $value;
    }
    $_SESSION[$APP_ID]['po-alloc'][$c_po_line_id] = $alloc;
    
    $res = new xajaxResponse();
    $res->script("xajax_allocShow('{$c_po_line_id}')");
    return $res;
}

function allocate($data) {
    global $APP_CONNECTION;
    
    $res = new xajaxResponse();
    
    $error = array();
    foreach ($data['org'] as $i => $o) {
        if (empty($o)) {
            $error[] = array('line' => (int) $data['line'][$i], 'message' => "Baris {$data['line'][$i]}: Organisasi tidak boleh kosong");
        }
    }
    foreach ($data['schedule'] as $i => $s) {
        $empty = FALSE;
        foreach ($s as $t) if (empty($t)) $empty = TRUE;
        if ($empty) $error[] = array('line' => (int) $data['line'][$i], 'message' => "Baris {$data['line'][$i]}: Jadwal pengiriman tidak boleh kosong");
    }
    foreach ($data['quantity'] as $i => $s) {
        $empty = FALSE;
        foreach ($s as $t) if (((int) $t) == 0) $empty = TRUE;
        if ($empty) $error[] = array('line' => (int) $data['line'][$i], 'message' => "Baris {$data['line'][$i]}: Jumlah barang tidak boleh nol");
    }
    
    $rsx = mysql_query("SELECT * FROM c_po_line JOIN m_product USING (m_product_id) WHERE c_po_id = '{$data['c_po_id']}'", $APP_CONNECTION);
    while ($dtx = mysql_fetch_array($rsx, MYSQL_ASSOC)) {
        $po_line_data[$dtx['c_po_line_id']] = $dtx;
        $total = 0;
        foreach ($data['quantity'][$dtx['c_po_line_id']] as $q) $total += $q;
        if ($total != $dtx['quantity']) $error[] = array('line' => (int) $data['line'][$dtx['c_po_line_id']], 'message' => "Baris {$data['line'][$dtx['c_po_line_id']]}: Alokasi jumlah barang tidak sesuai dengan order");
    }
    mysql_free_result($rsx);
    
    foreach ($data['org'] as $id => $org) {
        if (is_array($so_group[$org])) {
            $so_group[$org][] = $id;
        } else {
            $so_group[$org] = array($id);
        }
    }
    
    if ($error) {
        aasort($error, 'line');
        foreach ($error as $e) $message .= $e['message'] . "\n";
        $res->alert("Terjadi Kesalahan:\n" . $message);
        return $res;
    }

    // GO
    $po = npl_fetch_table("SELECT * FROM c_po WHERE c_po_id = '{$data['c_po_id']}'");
    require_once 'lib/class.Penomoran.php';
    $nomor = new Penomoran();
    foreach ($so_group as $org_id => $po_line) {
        $document_no = $nomor->urut('SO', $org_id);
        $sql = "INSERT INTO c_order ("
                . "app_org_id, document_no, c_bpartner_id, "
                . "order_date, reference_no, m_transaction_type_id, "
                . "status, c_po_id, create_date, create_user) "
                . "VALUES ('{$org_id}', '{$document_no}', '{$po['c_bpartner_id']}', "
                . "'{$po['po_date']}', '{$po['document_no']}', 1, "
                . "'O', '{$data['c_po_id']}', NOW(), '" . user() ."')";
        //$res->alert(print_r($sql, TRUE));
        mysql_query($sql, $APP_CONNECTION);
        $c_order_id = mysql_insert_id($APP_CONNECTION);
        foreach ($po_line as $po_line_id) {
            foreach ($data['schedule'][$po_line_id] as $index => $schedule) {
                $schedule2 = npl_dmy2ymd($schedule);
                $sql = "INSERT INTO c_order_line ("
                        . "c_order_id, schedule_delivery_date, m_product_id, "
                        . "order_quantity, item_description, item_number, "
                        . "line_status, c_po_line_id) "
                        . "VALUES ('{$c_order_id}', '{$schedule2}', '{$po_line_data[$po_line_id]['m_product_id']}', "
                        . "'{$data['quantity'][$po_line_id][$index]}', '{$po_line_data[$po_line_id]['description']}', '{$po_line_data[$po_line_id]['product_code']}', "
                        . "'O', '{$po_line_id}')";
                //$res->alert(print_r($sql, TRUE));
                mysql_query($sql, $APP_CONNECTION);
            }
        }
    }
    mysql_query("UPDATE c_po SET allocated = 'Y' WHERE c_po_id = '{$data['c_po_id']}'", $APP_CONNECTION);
    $res->script("window.location = 'module.php?m=trx.po&pkey[c_po_id]={$data['c_po_id']}';");
    //$res->alert(print_r($data, TRUE));
    unset($_SESSION[$APP_ID]['po-alloc']);
    return $res;
}

$xajax->register(XAJAX_FUNCTION, 'allocShow');
$xajax->register(XAJAX_FUNCTION, 'allocAdd');
$xajax->register(XAJAX_FUNCTION, 'allocDel');
$xajax->register(XAJAX_FUNCTION, 'allocUpdateCell');
$xajax->register(XAJAX_FUNCTION, 'allocate');

?>