<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.org>
 */

function ctl_edit_po($data) {
//    if ($data['record']['line_status'] == 'O') {
        $href = "xajax_editForm('{$data['record']['line']}');";
        $out = "<img onclick=\"{$href}\" style='cursor: pointer;' title='Edit' src='images/icon_edit.png' border='0'>";
        return $out;
//    }
}

function ctl_delete_po($data) {
//    if ($data['record']['line_status'] == 'O' && $data['record']['delivered_quantity'] == 0 && $data['record']['return_quantity'] == 0) {
        $href = "xajax_deleteLine('{$data['record']['line']}');";
        $out = "<img onclick=\"{$href}\" style='cursor: pointer;' title='Hapus' src='images/icon_delete.png' border='0'>";
        return $out;
//    }
}

function showLines($c_po_id, $mode = NULL, $fc0 = NULL, $fc1 = NULL, $fc2 = NULL) {
    global $APP_CONNECTION, $APP_ID, $cgx_TableAttribs, $cgx_HeaderAttribs,
        $cgx_EvenRowAttribs, $cgx_OddRowAttribs, $cgx_RendererOptions;
    
    $data = $_SESSION[$APP_ID]['po'];
    if ($data['c_po_id'] != $c_po_id) return;
    
    if (is_array($data['lines'])) {
        $n = 0;
        foreach ($data['lines'] as $k => $d) {
            $n++;
            $data['lines'][$k]['line'] = $n;
        }
    }
    $_SESSION[$APP_ID]['po'] = $data;
    
    if ($fc0 && $fc1 && $fc2) {
        $nfc1 = strtotime(npl_period2mysqldate($fc1));
        $nfc2 = strtotime(npl_period2mysqldate($fc2));
        $dt = $nfc1;
        $zz = 1;
        $periods = array();
        while ($dt <= $nfc2) {
            $zz++;
            $periods[] = $dt;
            $dt = mktime(0, 0, 0, date('n', $dt) + 1, 1, date('Y', $dt));
            if ($zz > 24) break;
        }
        $fc['active'] = 1;
        $fc['from'] = $nfc1;
        $fc['to'] = $nfc2;
        $fc['period'] = $periods;
    } else {
        $fc['active'] = 0;
        unset($fc['from']);
        unset($fc['to']);
        unset($fc['period']);
    }
    $_SESSION[$APP_ID]['po']['fc'] = $fc;

    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';
    
    $datagrid = new Structures_DataGrid(9999);
    $datagrid->bind($data['lines'], array(), 'Array');
    $cgx_table = new HTML_Table($cgx_TableAttribs);
    $cgx_tableHeader = & $cgx_table->getHeader();
    $cgx_tableBody = & $cgx_table->getBody();
    
    $datagrid->addColumn(new Structures_DataGrid_Column('Baris', 'line', NULL, array('align' => 'center'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Item Number', 'product_code', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Spec', 'spec', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('OD', 'od', NULL, array('align' => 'right', 'width' => '8%'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Tebal', 'thickness', NULL, array('align' => 'right', 'width' => '8%'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Panjang', 'length', NULL, array('align' => 'right', 'width' => '8%'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Jumlah<br>Order', 'quantity', NULL, array('align' => 'right', 'width' => '8%'), NULL, "cgx_format_3digit"));
    if (is_array($periods) && empty($c_po_id)) {
        foreach ($periods as $p) {
            $fname = 'fc' . date('Ym', $p);
            $datagrid->addColumn(new Structures_DataGrid_Column('Forecast<br>' . date('m-Y', $p), $fname, NULL, array('align' => 'right', 'width' => '8%'), NULL, "cgx_format_3digit"));
        }
    }
    if ($mode == 'edit') $datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '20'), NULL, 'ctl_edit_po()'));
    if ($mode == 'edit') $datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '20'), NULL, 'ctl_delete_po()'));

    $datagrid->fill($cgx_table, $cgx_RendererOptions);
    $cgx_tableHeader->setRowAttributes(0, $cgx_HeaderAttribs);
    $cgx_tableBody->altRowAttributes(0, $cgx_EvenRowAttribs, $cgx_OddRowAttribs, TRUE);

    $html  = "<div class='datagrid_background'>\n";
    $html .= $cgx_table->toHtml();
    $html .= "</div>\n";

    $res = new xajaxResponse();
    $res->assign('area-lines', 'innerHTML', $html);
    
//    if ($fc1 || $fc2) $res->alert($fc0 . '---' . $fc1 . '+++' . $fc2);
//    $res->alert(print_r($periods, TRUE));
//    $res->alert(print_r($data['lines'], TRUE));
    
    return $res;
}

function editForm($line_no) {
    global $APP_CONNECTION, $APP_ID, $APP_DATE_FORMAT, $APP_DATE_FORMAT_JAVA;
    global $mandatory;
    
    foreach ($_SESSION[$APP_ID]['po']['lines'] as $line) {
        if ($line['line'] == $line_no) {
            $data = $line;
            break;
        }
    }
    
    $html .= "<form id='frmLine'>";
    $html .= "<input type='hidden' name='c_po_line_id' value='{$data['c_po_line_id']}'>";
    $html .= "<input type='hidden' name='line' value='{$data['line']}'>";
    $html .= "<input type='hidden' id='m_product_id' name='m_product_id' value='{$data['m_product_id']}'>";
    $html .= "<table width='100%'>";
    $html .= "<tr>";
    $html .= "<td width='12%'>Item Number {$mandatory}</td>";
    $html .= "<td width='33%'><input id='product_code' type='text' size='20' value=\"{$data['product_code']}\" readonly='readonly'><img onclick=\"popupReference('product-all');\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'></td>";
    $html .= "<td width='10%'></td>";
    $html .= "<td width='12%'></td>";
    $html .= "<td width='33%'></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Barang</td>";
    $html .= "<td><input type='text' name='product_name' id='product_name' size='30' value=\"{$data['product_name']}\" readonly='readonly'></td>";
    $html .= "<td></td>";
    $html .= "<td></td>";
    $html .= "<td></td>";
    $html .= "</tr>";
    
    $html .= "<tr>";
    $html .= "<td>Jumlah Order {$mandatory}</td>";
    $html .= "<td><input type='text' size='10' name='quantity' value=\"{$data['quantity']}\" style='text-align: right;'></td>";
    $html .= "<td></td>";
    $html .= "<td></td>";
    $html .= "<td></td>";
    $html .= "</tr>";

    $html .= "<tr>";
    $html .= "<td>&nbsp;</td>";
    $html .= "</tr>";
    
    if (empty($_SESSION[$APP_ID]['po']['c_po_id'])) {
        foreach ($_SESSION[$APP_ID]['po']['fc']['period'] as $p) {
            $fname = date('Y-m', $p);
            $cname = 'fc' . date('Ym', $p);
            $html .= "<tr>";
            $html .= "<td>Forecast " . date('m-Y', $p) . "</td>";
            $html .= "<td><input type='text' size='10' name='fc[{$fname}]' value=\"{$data[$cname]}\" style='text-align: right;'></td>";
            $html .= "<td></td>";
            $html .= "<td></td>";
            $html .= "<td></td>";
            $html .= "</tr>";
        }
    }
    
    $html .= "<tr>";
    $html .= "<td></td>";
    $html .= "<td><input type='button' value='" . ($line_no ? 'Update' : 'Tambahkan') . "' onclick=\"xajax_updateLine(xajax.getFormValues('frmLine'));\"> &nbsp; ";
    $html .= "<input type='button' value='Batal' onclick=\"document.getElementById('area-edit').style.display = 'none'; document.getElementById('master-button').style.display = '';\"></td>";
    $html .= "</tr>";
    $html .= "</table>";
    $html .= "</form>";
    
    $res = new xajaxResponse();
    $res->assign('area-edit', 'innerHTML', $html);
    $res->assign('area-edit', 'style.display', '');
    $res->assign('master-button', 'style.display', 'none');
    $res->script("\$(function() { \$('#schedule_delivery_date').datepicker({dateFormat: '{$APP_DATE_FORMAT_JAVA}'}); });");
    
    return $res;
}

function deleteLine($line_no) {
    global $APP_ID, $APP_CONNECTION;
    
    $po = $_SESSION[$APP_ID]['po'];
    foreach ($po['lines'] as $k => $line) {
        if ($line['line'] == $line_no) {
            $del = $k;
            if ($line['c_po_line_id']) {
                if ($po['delete']) {
                    $po['delete'][] = $line['c_po_line_id'];
                } else {
                    $po['delete'] = array($line['c_po_line_id']);
                }
            }
        }
    }
    unset($po['lines'][$del]);
    $_SESSION[$APP_ID]['po'] = $po;

    $res = new xajaxResponse();
    $res->script("xajax_showLines('{$po['c_po_id']}', 'edit', document.getElementById('chkFC').checked, document.getElementById('txtFC1').value, document.getElementById('txtFC2').value);");
    $res->assign('area-edit', 'style.display', 'none');
    $res->assign('master-button', 'style.display', '');
    return $res;
}

function savePO($data) {
    global $APP_ID, $APP_CONNECTION;

    require_once 'lib/class.Penomoran.php';
    $res = new xajaxResponse();

    // load PO session data to variable
    $po = $_SESSION[$APP_ID]['po'];
    
    // field validation
    if (empty($data['c_bpartner_id'])) {
        $error = "Customer tidak boleh kosong";
    } elseif (cgx_emptydate($data['po_date'])) {
        $error = "Tanggal PO tidak boleh kosong";
    } elseif (count($po['lines']) == 0) {
        $error = "Detail item barang tidak boleh kosong, harus ada minimal satu baris.";
    }
    if ($error) {
        $res->alert($error);
        return $res;
    }
    
    // save forecast only for new PO
    if (empty($data['c_po_id']) && $data['chkFC']) {
        
        // insert forecast header
        $period_f = date('Y-m-d', $po['fc']['from']);
        $period_t = date('Y-m-d', $po['fc']['to']);
        $nomor = new Penomoran();
        $document_no = $nomor->urut('FC', -1);
        $sql = "INSERT INTO m_forecast (document_no, c_bpartner_id, period_start, period_end) " .
                "VALUES ('{$document_no}', '{$data['c_bpartner_id']}', '{$period_f}', '{$period_t}')";
        mysql_query($sql, $APP_CONNECTION);
        $fc_header_id = mysql_insert_id($APP_CONNECTION);
        
        // insert forecast line
        foreach ($po['lines'] as $po_line) {
            foreach ($po['fc']['period'] as $fc_period) {
                $lastes = npl_fetch_table(
                    "SELECT m_forecast_line_id FROM m_forecast_line mfl " 
                    . "JOIN m_forecast mf USING (m_forecast_id) "
                    . "WHERE mf.c_bpartner_id = '{$data['c_bpartner_id']}' "
                    . "AND mfl.m_product_id = '{$po_line['m_product_id']}' "
                    . "AND mfl.period = '" . date('Y-m-d', $fc_period) . "' AND latest='Y'"); 
                mysql_query("UPDATE m_forecast_line set latest = 'N' where m_forecast_line_id='{$lastes['m_forecast_line_id']}'", $APP_CONNECTION);
            }
        }

        foreach ($po['lines'] as $po_line) {
            foreach ($po['fc']['period'] as $fc_period) {
                $qty = $po_line['fc' . date('Ym', $fc_period)];
                $sql = "INSERT INTO m_forecast_line (m_forecast_id, period, m_product_id, latest, qty) " .
                        "VALUES ('{$fc_header_id}', '" . date('Y-m-d', $fc_period) . "', '{$po_line['m_product_id']}', 'Y', '{$qty}')";
                mysql_query($sql, $APP_CONNECTION);
            }
        }
        
    }
    
    if ($data['c_po_id']) {
        // update existing PO
        $sql = "UPDATE c_po SET c_bpartner_id = '{$data['c_bpartner_id']}',
            po_date = '" . cgx_dmy2ymd($data['po_date']) . "'
            WHERE c_po_id = '{$data['c_po_id']}'";
    } else {
        // insert new PO
        $nomor = new Penomoran();
        $document_no = $nomor->urut('PO', -1);
        $sql =
            "INSERT INTO c_po (document_no, m_forecast_id, c_bpartner_id, po_date, create_user, create_date)
            VALUES ('{$document_no}', '{$fc_header_id}', '{$data['c_bpartner_id']}', '" . cgx_dmy2ymd($data['po_date']) . "',
            '" . user() . "', NOW())";
    }
    $rsx = mysql_query($sql, $APP_CONNECTION);
    if (!$rsx) {
        $error = mysql_error($APP_CONNECTION);
        $res->alert($error);
        return $res;
    }
    if ($data['c_po_id']) {
        $po_id = $data['c_po_id'];
    } else {
        $po_id = mysql_insert_id($APP_CONNECTION);
    }
    
    foreach ($po['lines'] as $line) {
        if ($line['c_po_line_id']) {
            // update PO line
            $sql = "UPDATE c_po_line SET m_product_id = '{$line['m_product_id']}', quantity = '{$line['quantity']}'
                WHERE c_po_line_id = '{$line['c_po_line_id']}'";
        } else {
            // insert PO line
            $sql = "INSERT INTO c_po_line (c_po_id, m_product_id, quantity)
                VALUES ('{$po_id}', '{$line['m_product_id']}', '{$line['quantity']}')";
        }
        $rsx = mysql_query($sql, $APP_CONNECTION);
    }
    
    // remove PO line
    if (is_array($po['delete'])) {
        foreach ($po['delete'] as $d) {
            $sql = "DELETE FROM c_po_line WHERE c_po_line_id = '{$d}'";
            mysql_query($sql, $APP_CONNECTION);
        }
    }
    
    // set notes on forecast to inform that the forecast is created by PO form
    if ($fc_header_id && empty($data['c_po_id']) && $data['chkFC']) {
        mysql_query(
                "UPDATE m_forecast "
                . "SET notes = '{$document_no}' "
                . "WHERE m_forecast_id = '{$fc_header_id}'", $APP_CONNECTION);
    }
    
    $_SESSION[$APP_ID]['trx.po']['info'] = "Dokumen sudah berhasil disimpan";
    unset($_SESSION[$APP_ID]['po']);
    
    $res->script("window.location = 'module.php?m=trx.po&pkey[c_po_id]={$po_id}';");
    return $res;
}

function updateLine($data) {
    global $APP_ID;
    
    $po = $_SESSION[$APP_ID]['po'];
    $res = new xajaxResponse();
    if ((int) $data['quantity'] <= 0) {
        $error = "Jumlah barang tidak boleh kosong";
    } elseif (empty($data['m_product_id'])) {
        $error = "Kode barang tidak boleh kosong";
    }
    if ($error) {
        $res->alert($error);
        return $res;
    }
    
    if (is_array($po['lines'])) {
        foreach ($po['lines'] as $k => $d) {
            if ($d['line'] == $data['line']) {
                $po['lines'][$k]['quantity'] = $data['quantity'];
                $po['lines'][$k]['m_product_id'] = $data['m_product_id'];
                $product = cgx_fetch_table("SELECT * FROM m_product WHERE m_product_id = '{$data['m_product_id']}'");
                if (is_array($product)) foreach ($product as $pk => $pv) if (!is_numeric($pk)) $po['lines'][$k][$pk] = $pv;
                if (is_array($data['fc'])) {
                    foreach ($data['fc'] as $fck => $fcv) {
                        $fname = explode('-', $fck);
                        $fname = 'fc' . $fname[0] . $fname[1];
                        $po['lines'][$k][$fname] = $fcv;
                    }
                }
                $line_updated = TRUE;
                break;
            }
        }
    }
    
    if (!$line_updated) {
        $new_line['quantity'] = $data['quantity'];
        $new_line['m_product_id'] = $data['m_product_id'];
        $product = cgx_fetch_table("SELECT * FROM m_product WHERE m_product_id = '{$data['m_product_id']}'");
        if (is_array($product)) foreach ($product as $pk => $pv) if (!is_numeric($pk)) $new_line[$pk] = $pv;
        if (is_array($data['fc'])) {
            foreach ($data['fc'] as $fck => $fcv) {
                $fname = explode('-', $fck);
                $fname = 'fc' . $fname[0] . $fname[1];
                $new_line[$fname] = $fcv;
            }
        }
        $po['lines'][] = $new_line;
    }
    $_SESSION[$APP_ID]['po'] = $po;
    
    $res->script("xajax_showLines('{$po['c_po_id']}', 'edit', document.getElementById('chkFC').checked, document.getElementById('txtFC1').value, document.getElementById('txtFC2').value);");
    $res->assign('area-edit', 'style.display', 'none');
    $res->assign('master-button', 'style.display', '');
    return $res;
}


$xajax->register(XAJAX_FUNCTION, 'showLines');
$xajax->register(XAJAX_FUNCTION, 'editForm');
$xajax->register(XAJAX_FUNCTION, 'updateLine');
$xajax->register(XAJAX_FUNCTION, 'deleteLine');
$xajax->register(XAJAX_FUNCTION, 'savePO');

?>