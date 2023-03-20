<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.org>
 * Nov 24, 2013 8:18:31 AM
 */

function ctl_edit_puo($data) {
    if ($data['record']['line_status'] == 'O') {
        $href = "xajax_editForm('{$data['record']['line']}');";
        $out = "<img onclick=\"{$href}\" style='cursor: pointer;' title='Edit' src='images/icon_edit.png' border='0'>";
        return $out;
    }
}

function ctl_delete_puo($data) {
    if ($data['record']['line_status'] == 'O' && $data['record']['receipt_weight'] == 0 ) {
        $href = "xajax_deleteLine('{$data['record']['line']}');";
        $out = "<img onclick=\"{$href}\" style='cursor: pointer;' title='Hapus' src='images/icon_delete.png' border='0'>";
        return $out;
    }
}

function ctl_close_puo($data) {
    if ($data['record']['line_status'] == 'O') {
        $outstanding = $data['record']['order_weight'] - $data['record']['receipt_weight'];
        if ($outstanding > 0) {
            $href = "if (confirm('Baris {$data['record']['line']} masih mempunyai outstanding sebanyak " . number_format($outstanding) . "\\nApakah anda tetap ingin menutup baris ini?')) xajax_closeLine('{$data['record']['c_order_id']}', '{$data['record']['c_order_line_id']}')";
        } else {
            $href = "xajax_closeLine('{$data['record']['c_order_id']}', '{$data['record']['c_order_line_id']}');";
        }
        $out = "<img onclick=\"{$href}\" style='cursor: pointer;' title='Close' src='images/icon_close.png' border='0'>";
    }
    return $out;
}

function grid_jumlah_outstanding($data) {
    return number_format($data['record']['order_weight'] - $data['record']['receipt_weight'], 2);
}

function grid_status($data) {
    if ($data['record']['line_status'] == 'O') {
        $status = "<img src='images/icon_outstanding.png' title='Outstanding'>";
    } else {
        $status = "<img src='images/icon_close2.png' title='Closed'>";
    }
    return $status;
}

function showLines($c_order_id, $mode = NULL) {
    global $APP_CONNECTION, $APP_ID, $cgx_TableAttribs, $cgx_HeaderAttribs,
        $cgx_EvenRowAttribs, $cgx_OddRowAttribs, $cgx_RendererOptions;
    
    $data = $_SESSION[$APP_ID]['pu_order'];
    if ($data['c_order_id'] != $c_order_id) return;
    
    if (is_array($data['lines'])) {
        $n = 0;
        foreach ($data['lines'] as $k => $d) {
            $n++;
            $data['lines'][$k]['line'] = $n;
        }
    }
    $_SESSION[$APP_ID]['pu_order'] = $data;

    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';
    
    $datagrid = new Structures_DataGrid(9999);
    $datagrid->bind($data['lines'], array(), 'Array');
    $cgx_table = new HTML_Table($cgx_TableAttribs);
    $cgx_tableHeader = & $cgx_table->getHeader();
    $cgx_tableBody = & $cgx_table->getBody();
    
    $datagrid->addColumn(new Structures_DataGrid_Column('Baris', 'line', NULL, array('align' => 'center'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Jadwal<br>Penerimaan', 'schedule_delivery_date', NULL, array('align' => 'center'), NULL, "cgx_format_date"));
    $datagrid->addColumn(new Structures_DataGrid_Column('Item Number', 'product_code', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Spec', 'spec', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Thickness', 'thickness', NULL, array('align' => 'right', 'width' => '8%'), NULL, NULL));
    
    $datagrid->addColumn(new Structures_DataGrid_Column('Width', 'od', NULL, array('align' => 'right', 'width' => '8%'), NULL, NULL));
//    $datagrid->addColumn(new Structures_DataGrid_Column('Panjang', 'length', NULL, array('align' => 'right', 'width' => '8%'), NULL, NULL));
//    $datagrid->addColumn(new Structures_DataGrid_Column('Description', 'item_description', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Berat Order<br>(Kg)', 'order_weight', NULL, array('align' => 'right', 'width' => '10%'), NULL, "cgx_format_money"));
    $datagrid->addColumn(new Structures_DataGrid_Column('Berat Diterima<br>(Kg)', 'receipt_weight', NULL, array('align' => 'right', 'width' => '10%'), NULL, "cgx_format_money"));
    $datagrid->addColumn(new Structures_DataGrid_Column('Qty Diterima<br>(Pcs)', 'delivered_quantity', NULL, array('align' => 'right', 'width' => '8%'), NULL, "cgx_format_3digit"));
    $datagrid->addColumn(new Structures_DataGrid_Column('Outstanding<br>(Kg)', NULL, NULL, array('align' => 'right', 'width' => '10%'), NULL, "grid_jumlah_outstanding"));
    $datagrid->addColumn(new Structures_DataGrid_Column('Status', 'line_status', NULL, array('align' => 'center', 'width' => '1%'), NULL, "grid_status"));
    if ($mode == 'edit') $datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '20'), NULL, 'ctl_close_puo()'));
    if ($mode == 'edit') $datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '20'), NULL, 'ctl_edit_puo()'));
    if ($mode == 'edit') $datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '20'), NULL, 'ctl_delete_puo()'));

    $datagrid->fill($cgx_table, $cgx_RendererOptions);
    $cgx_tableHeader->setRowAttributes(0, $cgx_HeaderAttribs);
    $cgx_tableBody->altRowAttributes(0, $cgx_EvenRowAttribs, $cgx_OddRowAttribs, TRUE);

    $html  = "<div class='datagrid_background'>\n";
    $html .= $cgx_table->toHtml();
    $html .= "</div>\n";

    $res = new xajaxResponse();
    $res->assign('area-lines', 'innerHTML', $html);
    return $res;
}

function editForm($line_no) {
    global $APP_CONNECTION, $APP_ID, $APP_DATE_FORMAT, $APP_DATE_FORMAT_JAVA;
    global $mandatory;
    
    foreach ($_SESSION[$APP_ID]['pu_order']['lines'] as $line) {
        if ($line['line'] == $line_no) {
            $data = $line;
            break;
        }
    }
    
    $html .= "<form id='frmLine'>";
    $html .= "<input type='hidden' name='c_order_line_id' value='{$data['c_order_line_id']}'>";
    $html .= "<input type='hidden' name='line' value='{$data['line']}'>";
    $html .= "<input type='hidden' id='m_product_id' name='m_product_id' value='{$data['m_product_id']}'>";
    $html .= "<table width='100%'>";
    $html .= "<tr>";
    $html .= "<td width='12%'>Item Number {$mandatory}</td>";
    $html .= "<td width='40%'><input id='product_code' type='text' size='20' value=\"{$data['product_code']}\" readonly='readonly'><img onclick=\"popupReference('product_purchase');\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'></td>";
    $html .= "<td width='10%'></td>";
    $html .= "<td width='12%'>Jadwal Penerimaan {$mandatory}</td>";
    $delivery_date = cgx_emptydate($data['schedule_delivery_date']) ? '' : date($APP_DATE_FORMAT, strtotime($data['schedule_delivery_date']));
    $html .= "<td width='26%'><input type='text' size='10' id='schedule_delivery_date' name='schedule_delivery_date' value=\"{$delivery_date}\"></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Nama Produk</td>";
    $html .= "<td><input type='text' name='product_name' id='product_name' size='45' value=\"{$data['product_name']}\" readonly='readonly'></td>";
    $html .= "<td></td>";
    $html .= "<td>Berat Order (Kg) {$mandatory}</td>";
    $html .= "<td><input type='text' size='10' name='order_weight' value=\"{$data['order_weight']}\" style='text-align: right;'></td>";
    $html .= "</tr>";
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
    
    $order = $_SESSION[$APP_ID]['pu_order'];
    foreach ($order['lines'] as $k => $line) {
        if ($line['line'] == $line_no) {
            $del = $k;
            if ($line['c_order_line_id']) {
                if ($order['delete']) {
                    $order['delete'][] = $line['c_order_line_id'];
                } else {
                    $order['delete'] = array($line['c_order_line_id']);
                }
            }
        }
    }
    unset($order['lines'][$del]);
    $_SESSION[$APP_ID]['pu_order'] = $order;

    $res = new xajaxResponse();
    $res->script("xajax_showLines('{$order['c_order_id']}', 'edit');");
    $res->assign('area-edit', 'style.display', 'none');
    $res->assign('master-button', 'style.display', '');
    return $res;
}

function savePUO($data) {
    global $APP_ID, $APP_CONNECTION;
    
    $order = $_SESSION[$APP_ID]['pu_order'];
    $res = new xajaxResponse();
    if (empty($data['c_bpartner_id'])) {
        $error = "Vendor tidak boleh kosong";
    }elseif (empty($data['app_org_id'])) {
        $error = "Organisasi tidak boleh kosong";
    }elseif (cgx_emptydate($data['order_date'])) {
        $error = "Tanggal order tidak boleh kosong";
    } elseif (count($order['lines']) == 0) {
        $error = "Detail item barang tidak boleh kosong, harus ada minimal satu baris.";
    }
    if ($error) {
        $res->alert($error);
        return $res;
    }
    
    if ($data['c_order_id']) {
        $sql = "UPDATE c_order SET document_no = '{$data['document_no']}', app_org_id = '{$data['app_org_id']}', c_bpartner_id = '{$data['c_bpartner_id']}',
            order_date = '" . cgx_dmy2ymd($data['order_date']) . "', reference_no = '{$data['reference']}',
            remark = '{$data['remark']}',
            update_date = NOW(), update_user = '" . user() . "'
            WHERE c_order_id = '{$data['c_order_id']}'";
    } else {
        require_once 'lib/class.Penomoran.php';
        $nomor = new Penomoran();
        if (empty($data['document_no'])) {
            $document_no = $nomor->urut('PO', $data['app_org_id']);
        } else{
            $document_no = $data['document_no'];
        }
        $sql = "INSERT INTO c_order (app_org_id, document_no, c_bpartner_id, order_date, reference_no, remark, m_transaction_type_id, create_user, create_date, update_user, update_date)
            VALUES ('{$data['app_org_id']}', '{$document_no}', '{$data['c_bpartner_id']}', '" . cgx_dmy2ymd($data['order_date']) . "',
            '{$data['reference']}', '{$data['remark']}', 2, '" . user() . "', NOW(), '" . user() . "', NOW())";
    }
    $rsx = mysql_query($sql, $APP_CONNECTION);
    if (!$rsx) {
        $error = mysql_error($APP_CONNECTION);
        $res->alert($error);
        return $res;
    }
    if ($data['c_order_id']) {
        $order_id = $data['c_order_id'];
    } else {
        $order_id = mysql_insert_id($APP_CONNECTION);
    }
    
    foreach ($order['lines'] as $line) {
        if ($line['c_order_line_id']) {
            $sql = "UPDATE c_order_line SET schedule_delivery_date = '{$line['schedule_delivery_date']}',
                m_product_id = '{$line['m_product_id']}', order_weight = '{$line['order_weight']}',
                item_description = '{$line['item_description']}', item_number = '{$line['item_description']}',
                line_status = '{$line['line_status']}'
                WHERE c_order_line_id = '{$line['c_order_line_id']}'";
        } else {
            $sql = "INSERT INTO c_order_line (c_order_id, schedule_delivery_date, m_product_id, order_weight, item_description, item_number)
                VALUES ('{$order_id}', '{$line['schedule_delivery_date']}', '{$line['m_product_id']}', '{$line['order_weight']}',
                '{$line['item_description']}', '{$line['product_code']}')";
        }
        $rsx = mysql_query($sql, $APP_CONNECTION);
    }
    
    if (is_array($order['delete'])) {
        foreach ($order['delete'] as $d) {
            $sql = "DELETE FROM c_order_line WHERE c_order_line_id = '{$d}'";
            mysql_query($sql, $APP_CONNECTION);
        }
    }
    
    $_SESSION[$APP_ID]['trx.puo']['info'] = "Dokumen sudah berhasil disimpan";
    
    $res->script("window.location = 'module.php?m=trx.puo&pkey[c_order_id]={$order_id}';");
    return $res;
}

function updateLine($data) {
    global $APP_ID;
    
    $order = $_SESSION[$APP_ID]['pu_order'];
    $res = new xajaxResponse();
    if ((int) $data['order_weight'] <= 0) {
        $error = "Jumlah barang tidak boleh kosong";
    } elseif (cgx_emptydate($data['schedule_delivery_date'])) {
        $error = "Jadwal pengiriman tidak boleh kosong";
    } elseif (empty($data['m_product_id'])) {
        $error = "Kode barang tidak boleh kosong";
    }
    if ($error) {
        $res->alert($error);
        return $res;
    }
    
    if (is_array($order['lines'])) {
        foreach ($order['lines'] as $k => $d) {
            if ($d['line'] == $data['line']) {
                $order['lines'][$k]['schedule_delivery_date'] = cgx_dmy2ymd($data['schedule_delivery_date']);
                $order['lines'][$k]['order_weight'] = $data['order_weight'];
                $order['lines'][$k]['item_description'] = $data['item_description'];
                $order['lines'][$k]['m_product_id'] = $data['m_product_id'];
                $product = cgx_fetch_table("SELECT * FROM m_product WHERE m_product_id = '{$data['m_product_id']}'");
                if (is_array($product)) foreach ($product as $pk => $pv) if (!is_numeric($pk)) $order['lines'][$k][$pk] = $pv;
                $line_updated = TRUE;
                break;
            }
        }
    }
    if (!$line_updated) {
        $new_line['schedule_delivery_date'] = cgx_dmy2ymd($data['schedule_delivery_date']);
        $new_line['order_weight'] = $data['order_weight'];
        $new_line['item_description'] = $data['item_description'];
        $new_line['m_product_id'] = $data['m_product_id'];
        $product = cgx_fetch_table("SELECT * FROM m_product WHERE m_product_id = '{$data['m_product_id']}'");
        if (is_array($product)) foreach ($product as $pk => $pv) if (!is_numeric($pk)) $new_line[$pk] = $pv;
        $order['lines'][] = $new_line;
    }
    $_SESSION[$APP_ID]['pu_order'] = $order;
    
    $res->script("xajax_showLines('{$order['c_order_id']}', 'edit');");
    $res->assign('area-edit', 'style.display', 'none');
    $res->assign('master-button', 'style.display', '');
    return $res;
}

function closeLine($c_order_id, $c_order_line_id) {
    global $APP_ID;
    
    $order = $_SESSION[$APP_ID]['pu_order'];
    foreach ($order['lines'] as $key => $line) {
        if ($line['c_order_line_id'] == $c_order_line_id) {
            $order['lines'][$key]['line_status'] = 'C';
        }
    }
    $_SESSION[$APP_ID]['pu_order'] = $order;
    
    $res = new xajaxResponse();
    $res->script("xajax_showLines('{$c_order_id}', 'edit');");
    $res->assign('area-edit', 'style.display', 'none');
    $res->assign('master-button', 'style.display', '');
    return $res;
}

function closePUO($c_order_id, $confirmed = FALSE) {
    global $APP_CONNECTION, $APP_ID;
    
    $res = new xajaxResponse();

    if ($confirmed) {
        mysql_query("UPDATE c_order_line SET line_status = 'C' WHERE c_order_id = '{$c_order_id}'", $APP_CONNECTION);
        mysql_query("UPDATE c_order SET status = 'C' WHERE c_order_id = '{$c_order_id}'", $APP_CONNECTION);
        $res->script("window.location = 'module.php?m=trx.puo&pkey[c_order_id]={$c_order_id}';");
        return $res;
    } else {
        $outstanding = FALSE;
        $order = $_SESSION[$APP_ID]['pu_order'];
        foreach ($order['lines'] as $key => $line) {
            $tmp = $line['order_weight'] - $line['receipt_weight'];
            if ($tmp > 0) {
                $outstanding = TRUE;
                break;
            }
        }
        if ($outstanding) {
            $message  = "Item dalam sales order ini belum seluruhnya diproses/dikirim. ";
            $message .= "Apakah anda yakin menutup sales order ini?";
            $res->script("if(confirm('{$message}')) xajax_closePUO('{$c_order_id}', true);");
        } else {
            $res->script("xajax_closePUO('{$c_order_id}', true);");
        }
        return $res;
    }
    return $res;
}

$xajax->register(XAJAX_FUNCTION, 'showLines');
$xajax->register(XAJAX_FUNCTION, 'editForm');
$xajax->register(XAJAX_FUNCTION, 'updateLine');
$xajax->register(XAJAX_FUNCTION, 'deleteLine');
$xajax->register(XAJAX_FUNCTION, 'closeLine');
$xajax->register(XAJAX_FUNCTION, 'closePUO');
$xajax->register(XAJAX_FUNCTION, 'savePUO');

?>