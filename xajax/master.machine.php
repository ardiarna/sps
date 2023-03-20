<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.org>
 * Nov 24, 2013 8:18:31 AM
 */

function ctl_edit_so($data) {
        $href = "xajax_editForm('{$data['record']['line']}');";
        $out = "<img onclick=\"{$href}\" style='cursor: pointer;' title='Edit' src='images/icon_edit.png' border='0'>";
        return $out;
}

function ctl_delete_so($data) {
        $href = "xajax_deleteLine('{$data['record']['line']}');";
        $out = "<img onclick=\"{$href}\" style='cursor: pointer;' title='Hapus' src='images/icon_delete.png' border='0'>";
        return $out;
}

function showLines($m_machine_id, $mode = NULL) {
    global $APP_CONNECTION, $APP_ID, $cgx_TableAttribs, $cgx_HeaderAttribs,
        $cgx_EvenRowAttribs, $cgx_OddRowAttribs, $cgx_RendererOptions;
    
    $data = $_SESSION[$APP_ID]['machine'];
    if ($data['m_machine_id'] != $m_machine_id) return;
    
    if (is_array($data['lines'])) {
        $n = 0;
        foreach ($data['lines'] as $k => $d) {
            $n++;
            $data['lines'][$k]['line'] = $n;
        }
    }
    $_SESSION[$APP_ID]['machine'] = $data;

    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';
    
    $datagrid = new Structures_DataGrid(9999);
    $datagrid->bind($data['lines'], array(), 'Array');
    $cgx_table = new HTML_Table($cgx_TableAttribs);
    $cgx_tableHeader = & $cgx_table->getHeader();
    $cgx_tableBody = & $cgx_table->getBody();
    
    $datagrid->addColumn(new Structures_DataGrid_Column('Baris', 'line', NULL, array('align' => 'center'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Item Number', 'product_code', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Customer', 'partner_name', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Spec', 'spec', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('OD', 'od', NULL, array('align' => 'right', 'width' => '8%'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Tebal', 'thickness', NULL, array('align' => 'right', 'width' => '8%'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Panjang', 'length', NULL, array('align' => 'right', 'width' => '8%'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Cycle Time<br>(detik)', 'cycle_time', NULL, array('align' => 'right'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Target/Jam<br>(PCS)', 'result_hour', NULL, array('align' => 'right'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Target/Shift<br>(PCS)', 'result_shift', NULL, array('align' => 'right'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Aktif', 'isactive', NULL, array('align' => 'left', 'width' => '8%'), NULL, NULL));
//    $datagrid->addColumn(new Structures_DataGrid_Column('Description', 'item_description', NULL, array('align' => 'left'), NULL, NULL));
    if ($mode == 'edit') $datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '20'), NULL, 'ctl_edit_so()'));
    if ($mode == 'edit') $datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '20'), NULL, 'ctl_delete_so()'));

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
    
    foreach ($_SESSION[$APP_ID]['machine']['lines'] as $line) {
        if ($line['line'] == $line_no) {
            $data = $line;
            break;
        }
    }
    
    $html .= "<form id='frmLine'>";
    $html .= "<input type='hidden' name='m_machine_item_id' value='{$data['m_machine_item_id']}'>";
    $html .= "<input type='hidden' name='line' value='{$data['line']}'>";
    $html .= "<input type='hidden' id='m_product_id' name='m_product_id' value='{$data['m_product_id']}'>";
    $html .= "<table width='100%'>";
    $html .= "<tr>";
    $html .= "<td width='12%'>Item Number {$mandatory}</td>";
    $html .= "<td width='33%'><input id='product_code' type='text' size='20' value=\"{$data['product_code']}\" readonly='readonly'><img onclick=\"popupReference('product');\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'></td>";
    $html .= "<td width='10%'></td>";
    $html .= "<td width='12%'>Cycle Time (dt)</td>";
    $html .= "<td width='33%'><input type='text' size='10' name='cycle_time' value=\"{$data['cycle_time']}\" style='text-align: right;'></td>";
    $html .= "<tr>";
    $html .= "<td>Nama Barang</td>";
    $html .= "<td><input type='text' name='product_name' id='product_name' size='30' value=\"{$data['product_name']}\" readonly='readonly'></td>";
    $html .= "<td></td>";
    $html .= "<td>Target/Jam (Pcs)</td>";
    $html .= "<td><input type='text' size='15' name='result_hour' value=\"{$data['result_hour']}\" style='text-align: right;'></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Description</td>";
    $html .= "<td><input type='text' size='30' id='item_description' value=\"{$data['item_description']}\" name='item_description' readonly='readonly'></td>";
    $html .= "<td></td>";
    $html .= "<td>Target/Shift (Pcs)</td>";
    $html .= "<td><input type='text' size='15' name='result_shift' value=\"{$data['result_shift']}\" style='text-align: right;'></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Aktif</td>";
    $html .= "<td>". cgx_form_select('isactive', array('Y' => 'Ya', 'N' => 'Tidak'), $data['isactive'], FALSE, "id='isactive'");$html .= "</td>";
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
    
    $machine = $_SESSION[$APP_ID]['machine'];
    foreach ($machine['lines'] as $k => $line) {
        if ($line['line'] == $line_no) {
            $del = $k;
            if ($line['m_machine_item_id']) {
                if ($machine['delete']) {
                    $machine['delete'][] = $line['m_machine_item_id'];
                } else {
                    $machine['delete'] = array($line['m_machine_item_id']);
                }
            }
        }
    }
    unset($machine['lines'][$del]);
    $_SESSION[$APP_ID]['machine'] = $machine;

    $res = new xajaxResponse();
    $res->script("xajax_showLines('{$machine['m_machine_id']}', 'edit');");
    $res->assign('area-edit', 'style.display', 'none');
    $res->assign('master-button', 'style.display', '');
    return $res;
}

function saveSO($data) {
    global $APP_ID, $APP_CONNECTION;
    
    $machine = $_SESSION[$APP_ID]['machine'];
    $res = new xajaxResponse();
    if (empty($data['app_org_id'])) {
        $error = "Organisasi tidak boleh kosong";
    } 
    // elseif (count($machine['lines']) == 0) {
    //     $error = "Detail item barang tidak boleh kosong, harus ada minimal satu baris.";
    // }
    if ($error) {
        $res->alert($error);
        return $res;
    }
    
    if ($data['m_machine_id']) {
        $sql = "UPDATE m_machine SET app_org_id = '{$data['app_org_id']}', machine_code = '{$data['machine_code']}',
            machine_name = '{$data['machine_name']}',
            resultperday = '{$data['resultperday']}',
            active = '{$data['active']}' 
            WHERE m_machine_id = '{$data['m_machine_id']}'";
    } else {
        require_once 'lib/class.Penomoran.php';
        $sql =
            "INSERT INTO m_machine (app_org_id, machine_code, machine_name, resultperday, active)
            VALUES ('{$data['app_org_id']}', '{$data['machine_code']}', '{$data['machine_name']}', '{$data['resultperday']}', '{$data['active']}')";
    }
    $rsx = mysql_query($sql, $APP_CONNECTION);
    if (!$rsx) {
        $error = mysql_error($APP_CONNECTION);
        $res->alert($error);
        return $res;
    }
    if ($data['m_machine_id']) {
        $machine_id = $data['m_machine_id'];
    } else {
        $machine_id = mysql_insert_id($APP_CONNECTION);
    }
    
    foreach ($machine['lines'] as $line) {
        if ($line['m_machine_item_id']) {
            $sql = "UPDATE m_machine_item SET m_product_id = '{$line['m_product_id']}', isactive = '{$line['isactive']}', cycle_time = '{$line['cycle_time']}', result_hour = '{$line['result_hour']}', result_shift = '{$line['result_shift']}'  
                WHERE m_machine_item_id = '{$line['m_machine_item_id']}'";
        } else {
            $sql = "INSERT INTO m_machine_item (m_machine_id, m_product_id, isactive, cycle_time, result_hour, result_shift)
                VALUES ('{$machine_id}', '{$line['m_product_id']}', '{$line['isactive']}', '{$line['cycle_time']}', '{$line['result_hour']}', '{$line['result_shift']}')";
        }
        $rsx = mysql_query($sql, $APP_CONNECTION);
    }
    
    if (is_array($machine['delete'])) {
        foreach ($machine['delete'] as $d) {
            $sql = "DELETE FROM m_machine_item WHERE m_machine_item_id = '{$d}'";
            mysql_query($sql, $APP_CONNECTION);
        }
    }
    
    $_SESSION[$APP_ID]['master.machine']['info'] = "Record sudah berhasil disimpan";
    
    $res->script("window.location = 'module.php?m=master.machine&pkey[m_machine_id]={$machine_id}';");
    return $res;
}

function updateLine($data) {
    global $APP_ID;
    
    $machine = $_SESSION[$APP_ID]['machine'];
    $res = new xajaxResponse();
    if (empty($data['m_product_id'])) {
        $error = "Kode barang tidak boleh kosong";
    }
    if ($error) {
        $res->alert($error);
        return $res;
    }
    
    if (is_array($machine['lines'])) {
        foreach ($machine['lines'] as $k => $d) {
            if ($d['line'] == $data['line']) {
                $machine['lines'][$k]['isactive'] = $data['isactive'];
                $machine['lines'][$k]['cycle_time'] = $data['cycle_time'];
                $machine['lines'][$k]['result_hour'] = $data['result_hour'];
                $machine['lines'][$k]['result_shift'] = $data['result_shift'];
                $machine['lines'][$k]['m_product_id'] = $data['m_product_id'];
                $product = cgx_fetch_table("SELECT * FROM m_product WHERE m_product_id = '{$data['m_product_id']}'");
                if (is_array($product)) foreach ($product as $pk => $pv) if (!is_numeric($pk)) $machine['lines'][$k][$pk] = $pv;
                $line_updated = TRUE;
                break;
            }
        }
    }
    if (!$line_updated) {
        $new_line['isactive'] = $data['isactive'];
        $new_line['cycle_time'] = $data['cycle_time'];
        $new_line['result_hour'] = $data['result_hour'];
        $new_line['result_shift'] = $data['result_shift'];
        $new_line['m_product_id'] = $data['m_product_id'];
        $product = cgx_fetch_table("SELECT * FROM m_product WHERE m_product_id = '{$data['m_product_id']}'");
        if (is_array($product)) foreach ($product as $pk => $pv) if (!is_numeric($pk)) $new_line[$pk] = $pv;
        $machine['lines'][] = $new_line;
    }
    $_SESSION[$APP_ID]['machine'] = $machine;
    
    $res->script("xajax_showLines('{$machine['m_machine_id']}', 'edit');");
    $res->assign('area-edit', 'style.display', 'none');
    $res->assign('master-button', 'style.display', '');
    return $res;
}

function closeLine($m_machine_id, $m_machine_item_id) {
    global $APP_ID;
    
    $machine = $_SESSION[$APP_ID]['machine'];
    foreach ($machine['lines'] as $key => $line) {
        if ($line['m_machine_item_id'] == $m_machine_item_id) {
            $machine['lines'][$key]['line_status'] = 'C';
        }
    }
    $_SESSION[$APP_ID]['machine'] = $machine;
    
    $res = new xajaxResponse();
    $res->script("xajax_showLines('{$m_machine_id}', 'edit');");
    $res->assign('area-edit', 'style.display', 'none');
    $res->assign('master-button', 'style.display', '');
    return $res;
}

$xajax->register(XAJAX_FUNCTION, 'showLines');
$xajax->register(XAJAX_FUNCTION, 'editForm');
$xajax->register(XAJAX_FUNCTION, 'updateLine');
$xajax->register(XAJAX_FUNCTION, 'deleteLine');
$xajax->register(XAJAX_FUNCTION, 'closeLine');
$xajax->register(XAJAX_FUNCTION, 'saveSO');

?>