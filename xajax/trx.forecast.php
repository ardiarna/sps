<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.org>
 * Nov 24, 2013 8:18:31 AM
 */

function ctl_edit_fc($data) {
    //if ($data['record']['line_status'] == 'O') {
        $href = "xajax_editForm('{$data['record']['line']}');";
        $out = "<img onclick=\"{$href}\" style='cursor: pointer;' title='Edit' src='images/icon_edit.png' border='0'>";
        return $out;
    //}
}

function ctl_delete_fc($data) {
    //if ($data['record']['line_status'] == 'O' && $data['record']['delivered_quantity'] == 0 && $data['record']['return_quantity'] == 0) {
        $href = "xajax_deleteLine('{$data['record']['line']}');";
        $out = "<img onclick=\"{$href}\" style='cursor: pointer;' title='Hapus' src='images/icon_delete.png' border='0'>";
        return $out;
    //}
}

/*function grid_jumlah_outstanding($data) {
    return number_format($data['record']['order_quantity'] - $data['record']['delivered_quantity'] + $data['record']['return_quantity']);
}*/
/*
function grid_status($data) {
    if ($data['record']['line_status'] == 'O') {
        $status = "<img src='images/icon_outstanding.png' title='Outstanding'>";
    } else {
        $status = "<img src='images/icon_close2.png' title='Closed'>";
    }
    return $status;
}
*/
function showLines($c_forecast_id, $mode = NULL) {
    global $APP_CONNECTION, $APP_ID, $cgx_TableAttribs, $cgx_HeaderAttribs,
        $cgx_EvenRowAttribs, $cgx_OddRowAttribs, $cgx_RendererOptions;
    
    $data = $_SESSION[$APP_ID]['forecast'];
    if ($data['c_forecast_id'] != $c_forecast_id) return;
    
    if (is_array($data['lines'])) {
        $n = 0;
        foreach ($data['lines'] as $k => $d) {
            $n++;
            $data['lines'][$k]['line'] = $n;
        }
    }
    $_SESSION[$APP_ID]['forecast'] = $data;

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
    $datagrid->addColumn(new Structures_DataGrid_Column('Description', 'description', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Jumlah<br>Order', 'quantity', NULL, array('align' => 'right', 'width' => '8%'), NULL, "cgx_format_3digit"));
    if ($mode == 'edit') $datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '20'), NULL, 'ctl_edit_fc()'));
    if ($mode == 'edit') $datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '20'), NULL, 'ctl_delete_fc()'));

    $datagrid->fill($cgx_table, $cgx_RendererOptions);
    $cgx_tableHeader->setRowAttributes(0, $cgx_HeaderAttribs);
    $cgx_tableBody->altRowAttributes(0, $cgx_EvenRowAttribs, $cgx_OddRowAttribs, TRUE);
//print_r($cgx_table);
//exit;
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
    
    foreach ($_SESSION[$APP_ID]['forecast']['lines'] as $line) {
        if ($line['line'] == $line_no) {
            $data = $line;
            break;
        }
    }
    
    $html .= "<form id='frmLine'>";
    $html .= "<input type='hidden' name='c_forecast_line_id' value='{$data['c_forecast_line_id']}'>";
    $html .= "<input type='hidden' name='line' value='{$data['line']}'>";
    $html .= "<input type='hidden' id='m_product_id' name='m_product_id' value='{$data['m_product_id']}'>";
    $html .= "<table width='100%'>";
    $html .= "<tr>";
    $html .= "<td width='12%'>Item Number {$mandatory}</td>";
    $html .= "<td width='33%'><input id='product_code' type='text' size='20' value=\"{$data['product_code']}\" readonly='readonly'><img onclick=\"popupReferenceAmbil('product-dari-so','&p1=' + document.getElementById('c_bpartner_id').value);\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'></td>";
    $html .= "<td width='10%'></td>";
//    $html .= "<td width='12%'>Jadwal Pengiriman {$mandatory}</td>";
  //  $delivery_date = cgx_emptydate($data['schedule_delivery_date']) ? '' : date($APP_DATE_FORMAT, strtotime($data['schedule_delivery_date']));
 //   $html .= "<td width='33%'><input type='text' size='10' id='schedule_delivery_date' name='schedule_delivery_date' value=\"{$delivery_date}\"></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Barang</td>";
    $html .= "<td><input type='text' name='product_name' id='product_name' size='30' value=\"{$data['product_name']}\" readonly='readonly'></td>";
    $html .= "<td></td>";
    $html .= "<td></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Description</td>";
    $html .= "<td><input type='text' size='30' id='description' value=\"{$data['description']}\" name='description' readonly='readonly'></td>";
    $html .= "<td></td>";
    $html .= "<td></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Jumlah Barang {$mandatory}</td>";
    $html .= "<td><input type='text' size='10' name='quantity' value=\"{$data['quantity']}\" style='text-align: right;'></td>";
    $html .= "<td></td>";
    $html .= "<td></td>";
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
    
    $forecast= $_SESSION[$APP_ID]['forecast'];
    foreach ($forecast['lines'] as $k => $line) {
        if ($line['line'] == $line_no) {
            $del = $k;
            if ($line['c_forecast_line_id']) {
                if ($forecast['delete']) {
                    $forecast['delete'][] = $line['c_forecast_line_id'];
                } else {
                    $forecast['delete'] = array($line['c_forecast_line_id']);
                }
            }
        }
    }
    unset($forecast['lines'][$del]);
    $_SESSION[$APP_ID]['forecast'] = $forecast;

    $res = new xajaxResponse();
    $res->script("xajax_showLines('{$forecast['c_forecast_id']}', 'edit');");
    $res->assign('area-edit', 'style.display', 'none');
    $res->assign('master-button', 'style.display', '');
    return $res;
}

function saveFC($data) {
    global $APP_ID, $APP_CONNECTION;
    
    $forecast = $_SESSION[$APP_ID]['forecast'];
    $res = new xajaxResponse();
    if (empty($data['c_bpartner_id'])) {
        $error = "Customer tidak boleh kosong";
    }//elseif (empty($data['app_org_id'])) {
       // $error = "Organisasi tidak boleh kosong";
    //}elseif (cgx_emptydate($data['quantity'])) {
       // $error = "Tanggal order tidak boleh kosong";
     elseif (count($forecast['lines']) == 0) {
        $error = "Detail item barang tidak boleh kosong, harus ada minimal satu baris.";
    }
    if ($error) {
        $res->alert($error);
        return $res;
    }
    
    if ($data['c_forecast_id']) {
        $sql = "UPDATE c_forecast SET c_bpartner_id = '{$data['c_bpartner_id']}',
            periode = '" . npl_period2mysqldate($data['periode']) . "', notes = '{$data['notes']}',
            
            update_date = NOW(), update_user = '" . user() . "'
            WHERE c_forecast_id = '{$data['c_forecast_id']}'";
    } else {
        require_once 'lib/class.Penomoran.php';
        $nomor = new Penomoran();
        $document_no = $nomor->urut('FC', org());
        $sql =
            "INSERT INTO c_forecast (app_org_id, document_no, periode, c_bpartner_id, notes, create_date, create_user, update_date, update_user)
            VALUES ('" .org(). "', '{$document_no}', '" . npl_period2mysqldate($data['periode']) . "', '{$data['c_bpartner_id']}', 
            '{$data['notes']}', NOW(), '" . user() . "', NOW(), '" . user() . "')";
    }
    $rsx = mysql_query($sql, $APP_CONNECTION);
    if (!$rsx) {
        $error = mysql_error($APP_CONNECTION);
        $res->alert($error);
        return $res;
    }
    if ($data['c_forecast_id']) {
        $forecast_id = $data['c_forecast_id'];
    } else {
        $forecast_id = mysql_insert_id($APP_CONNECTION);
    }
    
    foreach ($forecast['lines'] as $line) {
        if ($line['c_forecast_line_id']) {
            $sql = "UPDATE c_forecast_line SET 
                m_product_id = '{$line['m_product_id']}', 
                quantity = '{$line['quantity']}'
                
                
                WHERE c_forecast_line_id = '{$line['c_forecast_line_id']}'";
        } else {
            $sql = "INSERT INTO c_forecast_line (c_forecast_id, m_product_id, quantity)
                VALUES ('{$forecast_id}', '{$line['m_product_id']}', '{$line['quantity']}')";
        }
        $rsx = mysql_query($sql, $APP_CONNECTION);
    }
    
    if (is_array($forecast['delete'])) {
        foreach ($forecast['delete'] as $d) {
            $sql = "DELETE FROM c_forecast_line WHERE c_forecast_line_id = '{$d}'";
            mysql_query($sql, $APP_CONNECTION);
        }
    }
    
    $_SESSION[$APP_ID]['trx.forecast']['info'] = "Dokumen sudah berhasil disimpan";
    
    $res->script("window.location = 'module.php?m=trx.forecast&pkey[c_forecast_id]={$forecast_id}';");
    return $res;
}

function updateLine($data) {
    global $APP_ID;
    
    $forecast = $_SESSION[$APP_ID]['forecast'];
    $res = new xajaxResponse();
    if ((int) $data['quantity'] < 0) {
        $error = "Jumlah barang tidak boleh kosong";
//    } elseif (cgx_emptydate($data['schedule_delivery_date'])) {
  //      $error = "Jadwal pengiriman tidak boleh kosong";
    } elseif (empty($data['m_product_id'])) {
        $error = "Kode barang tidak boleh kosong";
    }
    if ($error) {
        $res->alert($error);
        return $res;
    }
    
    if (is_array($forecast['lines'])) {
        foreach ($forecast['lines'] as $k => $d) {
            if ($d['line'] == $data['line']) {
                //$order['lines'][$k]['schedule_delivery_date'] = cgx_dmy2ymd($data['schedule_delivery_date']);
                $forecast['lines'][$k]['quantity'] = $data['quantity'];
                $forecast['lines'][$k]['description'] = $data['description'];
                $forecast['lines'][$k]['m_product_id'] = $data['m_product_id'];
                $product = cgx_fetch_table("SELECT * FROM m_product WHERE m_product_id = '{$data['m_product_id']}'");
                if (is_array($product)) foreach ($product as $pk => $pv) if (!is_numeric($pk)) $forecast['lines'][$k][$pk] = $pv;
                $line_updated = TRUE;
                break;
            }
        }
    }
    if (!$line_updated) {
        //$new_line['schedule_delivery_date'] = cgx_dmy2ymd($data['schedule_delivery_date']);
        $new_line['quantity'] = $data['quantity'];
        //$new_line['description'] = $data['description'];
        $new_line['m_product_id'] = $data['m_product_id'];
        $product = cgx_fetch_table("SELECT * FROM m_product WHERE m_product_id = '{$data['m_product_id']}'");
        if (is_array($product)) foreach ($product as $pk => $pv) if (!is_numeric($pk)) $new_line[$pk] = $pv;
        $forecast['lines'][] = $new_line;
    }
    $_SESSION[$APP_ID]['forecast'] = $forecast;
    
    $res->script("xajax_showLines('{$forecast['c_forecast_id']}', 'edit');");
    $res->assign('area-edit', 'style.display', 'none');
    $res->assign('master-button', 'style.display', '');
    return $res;
}
/*
function closeLine($c_order_id, $c_order_line_id) {
    global $APP_ID;
    
    $order = $_SESSION[$APP_ID]['order'];
    foreach ($order['lines'] as $key => $line) {
        if ($line['c_forecast_line_id'] == $c_forecast_line_id) {
            $forecast['lines'][$key]['line_status'] = 'C';
        }
    }
    $_SESSION[$APP_ID]['forecast'] = $order;
    
    $res = new xajaxResponse();
    $res->script("xajax_showLines('{$c_forecast_id}', 'edit');");
    $res->assign('area-edit', 'style.display', 'none');
    $res->assign('master-button', 'style.display', '');
    return $res;
}*/
/*
function closeFC($c_forecast_id, $confirmed = FALSE) {
    global $APP_CONNECTION, $APP_ID;
    
    $res = new xajaxResponse();

    if ($confirmed) {
        mysql_query("UPDATE c_order_line SET line_status = 'C' WHERE c_order_id = '{$c_order_id}'", $APP_CONNECTION);
        mysql_query("UPDATE c_order SET status = 'C' WHERE c_order_id = '{$c_order_id}'", $APP_CONNECTION);
        $res->script("window.location = 'module.php?m=trx.so&pkey[c_order_id]={$c_order_id}';");
        return $res;
    } else {
        $outstanding = FALSE;
        $order = $_SESSION[$APP_ID]['order'];
        foreach ($order['lines'] as $key => $line) {
            $tmp = $line['order_quantity'] - $line['delivered_quantity'] + $line['return_quantity'];
            if ($tmp > 0) {
                $outstanding = TRUE;
                break;
            }
        }
        if ($outstanding) {
            $message  = "Item dalam sales order ini belum seluruhnya diproses/dikirim. ";
            $message .= "Apakah anda yakin menutup sales order ini?";
            $res->script("if(confirm('{$message}')) xajax_closeSO('{$c_order_id}', true);");
        } else {
            $res->script("xajax_closeFC('{$c_order_id}', true);");
        }
        return $res;
    }
    return $res;
}
*/
$xajax->register(XAJAX_FUNCTION, 'showLines');
$xajax->register(XAJAX_FUNCTION, 'editForm');
$xajax->register(XAJAX_FUNCTION, 'updateLine');
$xajax->register(XAJAX_FUNCTION, 'deleteLine');
$xajax->register(XAJAX_FUNCTION, 'closeLine');
//$xajax->register(XAJAX_FUNCTION, 'closeFC');
$xajax->register(XAJAX_FUNCTION, 'saveFC');

?>