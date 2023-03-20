<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.org>
 * Nov 24, 2013 8:18:31 AM
 */

function ctl_edit_wo($data) {
    $href = "xajax_editForm('{$data['record']['line']}');";
    $out = "<img onclick=\"{$href}\" style='cursor: pointer;' title='Edit' src='images/icon_edit.png' border='0'>";
    return $out;
}

function ctl_delete_wo($data) {    
    $href = "xajax_deleteLine('{$data['record']['line']}');";
    $out = "<img onclick=\"{$href}\" style='cursor: pointer;' title='Hapus' src='images/icon_delete.png' border='0'>";
    return $out;
}

function showLines($m_work_order_id, $mode = NULL) {
    global $APP_CONNECTION, $APP_ID, $cgx_TableAttribs, $cgx_HeaderAttribs,
        $cgx_EvenRowAttribs, $cgx_OddRowAttribs, $cgx_RendererOptions;
    
    $data = $_SESSION[$APP_ID]['wo'];
    if ($data['m_work_order_id'] != $m_work_order_id) return;
    
    if (is_array($data['lines'])) {
        $n = 0;
        foreach ($data['lines'] as $k => $d) {
            $n++;
            $data['lines'][$k]['line'] = $n;
        }
    }
    $_SESSION[$APP_ID]['wo'] = $data;

    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';
    
    $datagrid = new Structures_DataGrid(9999);
    $datagrid->bind($data['lines'], array(), 'Array');
    $cgx_table = new HTML_Table($cgx_TableAttribs);
    $cgx_tableHeader = & $cgx_table->getHeader();
    $cgx_tableBody = & $cgx_table->getBody();
    
    $datagrid->addColumn(new Structures_DataGrid_Column('Baris', 'line', NULL, array('align' => 'center'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Remark /<br>Forecast', 'remark', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Customer', 'partner_name', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Item Number', 'product_code', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Spec', 'spec', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('OD', 'od', NULL, array('align' => 'right'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Thickness', 'thickness', NULL, array('align' => 'right'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Length<br>Recutting', 'length', NULL, array('align' => 'right'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Length<br>Long Pipe', 'size_lp', NULL, array('align' => 'right'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Tolerance Size', 'tolerance_size', NULL, array('align' => 'center'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Long Pipe<br>Quantity', 'material_quantity', NULL, array('align' => 'right'), NULL, "cgx_format_3digit"));
    $datagrid->addColumn(new Structures_DataGrid_Column('Recutting<br>Quantity', 'order_quantity', NULL, array('align' => 'right'), NULL, "cgx_format_3digit"));
    if ($mode == 'edit') $datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '20'), NULL, 'ctl_edit_wo()'));
    if ($mode == 'edit') $datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '20'), NULL, 'ctl_delete_wo()'));

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
    
    foreach ($_SESSION[$APP_ID]['wo']['lines'] as $line) {
        if ($line['line'] == $line_no) {
            $data = $line;
            break;
        }
    }
    
    $html .= "<form id='frmLine'>";
    $html .= "<input type='hidden' name='line' value='{$data['line']}'>";
    $html .= "<input type='hidden' name='m_work_order_line_id' value='{$data['m_work_order_line_id']}'>";
    $html .= "<input type='hidden' id='c_order_id' name='c_order_id' value='{$data['c_order_id']}'>";
    $html .= "<input type='hidden' id='c_forecast_id' name='c_forecast_id' value='{$data['c_forecast_id']}'>";
    $html .= "<input type='hidden' id='m_product_id' name='m_product_id' value='{$data['m_product_id']}'>";
    $html .= "<input type='hidden' id='m_product_material' name='m_product_material' value='{$data['m_product_material']}'>";
    $html .= "<table width='100%'>";
    $html .= "<tr>";
    $html .= "<td width='12%'>Sales Order {$mandatory}</td>";
    $html .= "<td width='33%'><input id='sales_order' type='text' size='20' value=\"{$data['so']}\" readonly='readonly'><img onclick=\"popupReference('sales-order-forwo');\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'></td>";
    $html .= "<td width='10%'></td>";
    $html .= "<td width='12%'>Long Pipe {$mandatory}</td>";
    $html .= "<td width='33%'><input id='size_lp' type='text' size='35' value=\"{$data['size_lp']}\" readonly='readonly'><img onclick=\"popupReferenceAmbil('material','&p1=' + document.getElementById('m_product_id').value);\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Remark / Forecast</td>";
    $html .= "<td><input readonly='readonly' type='text' id='remark' size='20' value=\"{$data['remark']}\"></td>";
    $html .= "<td></td>";
    $html .= "<td>Tolerance Size</td>";
    $html .= "<td><input type='text' size='30' name='tolerance_size' value=\"{$data['tolerance_size']}\" style='text-align: left;'></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Customer</td>";
    $html .= "<td><input readonly='readonly' type='text' id='partner_name' size='35' value=\"{$data['partner_name']}\"></td>";
    $html .= "<td></td>";
    $html .= "<td>Long Pipe Quantity {$mandatory}</td>";
    $html .= "<td><input type='text' size='10' name='material_quantity' value=\"{$data['material_quantity']}\" style='text-align: right;'></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Produk Recutting</td>";
    $html .= "<td><input readonly='readonly' type='text' id='product_name' size='35' value=\"{$data['product_name']}\"></td>";
    $html .= "<td></td>";
    $html .= "<td>Recutting Quantity {$mandatory}</td>";
    $html .= "<td><input type='text' size='10' name='order_quantity' value=\"{$data['order_quantity']}\" style='text-align: right;'></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Quantity Order</td>";
    $html .= "<td><input readonly='readonly' type='text' id='order_qty_so' size='10' value=\"{$data['order_qty_so']}\"></td>";
    $html .= "<td></td>";
    $html .= "<td>Forecast</td>";
    $html .= "<td><img onclick=\"popupReference('forecast');\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'></td>";
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
    
    $wo = $_SESSION[$APP_ID]['wo'];
    foreach ($wo['lines'] as $k => $line) {
        if ($line['line'] == $line_no) {
            $del = $k;
            if ($line['m_work_order_line_id']) {
                if ($wo['delete']) {
                    $wo['delete'][] = $line['m_work_order_line_id'];
                } else {
                    $wo['delete'] = array($line['m_work_order_line_id']);
                }
            }
        }
    }
    unset($wo['lines'][$del]);
    $_SESSION[$APP_ID]['wo'] = $wo;

    $res = new xajaxResponse();
    $res->script("xajax_showLines('{$wo['m_work_order_id']}', 'edit');");
    $res->assign('area-edit', 'style.display', 'none');
    $res->assign('master-button', 'style.display', '');
    return $res;
}

function saveWO($data) {
    global $APP_ID, $APP_CONNECTION;
    
    $wo = $_SESSION[$APP_ID]['wo'];
    $res = new xajaxResponse();
    if (cgx_emptydate($data['order_date'])) {
        $error = "Tanggal WO tidak boleh kosong";
    } elseif (count($wo['lines']) == 0) {
        $error = "Detail item barang tidak boleh kosong, harus ada minimal satu baris.";
    }
    if ($error) {
        $res->alert($error);
        return $res;
    }
    
    if ($data['m_work_order_id']) {
        $sql = "UPDATE m_work_order SET order_date = '" . cgx_dmy2ymd($data['order_date']) . "', m_machine_id = '{$data['m_machine_id']}', 
            c_proces_id = '{$data['c_proces_id']}', delivery_from = '" . cgx_dmy2ymd($data['delivery_from']) . "', delivery_end = '" . cgx_dmy2ymd($data['delivery_end']) . "', 
            update_date = NOW(), update_user = '" . user() . "' WHERE m_work_order_id = '{$data['m_work_order_id']}'";
    } else {
        require_once 'lib/class.Penomoran.php';
        $nomor = new Penomoran();
        $document_no = $nomor->urut('WO', org());
        $sql = "INSERT INTO m_work_order (app_org_id, document_no, order_date, type_id, m_machine_id, c_proces_id, delivery_from, delivery_end, create_date, create_user)
            VALUES ('" . org() . "', '{$document_no}', '" . cgx_dmy2ymd($data['order_date']) . "', 'W', '{$data['m_machine_id']}', '{$data['c_proces_id']}', '" . cgx_dmy2ymd($data['delivery_from']) . "', '" . cgx_dmy2ymd($data['delivery_end']) . "', NOW() , '". user() ."')";
    }
    $rsx = mysql_query($sql, $APP_CONNECTION);
    if (!$rsx) {
        $error = mysql_error($APP_CONNECTION);
        $res->alert($error);
        return $res;
    }
    if ($data['m_work_order_id']) {
        $wo_id = $data['m_work_order_id'];
    } else {
        $wo_id = mysql_insert_id($APP_CONNECTION);
    }
    
    foreach ($wo['lines'] as $line) {
        if ($line['m_work_order_line_id']) {
            $sql = "UPDATE m_work_order_line SET c_order_id = '{$line['c_order_id']}', m_product_id = '{$line['m_product_id']}', 
                m_product_material = '{$line['m_product_material']}', order_quantity = '{$line['order_quantity']}', 
                material_quantity = '{$line['material_quantity']}', tolerance_size = '{$line['tolerance_size']}', c_forecast_id = '{$line['c_forecast_id']}'  
                WHERE m_work_order_line_id = '{$line['m_work_order_line_id']}'";
        } else {  
            $sql = "INSERT INTO m_work_order_line (m_work_order_id, m_product_id, c_order_id, order_quantity, m_product_material, 
                material_quantity, tolerance_size, c_forecast_id) VALUES ('{$wo_id}', '{$line['m_product_id']}', '{$line['c_order_id']}', 
                '{$line['order_quantity']}', '{$line['m_product_material']}', '{$line['material_quantity']}', '{$line['tolerance_size']}', '{$line['c_forecast_id']}')";
        }
        $rsx = mysql_query($sql, $APP_CONNECTION);
    }
    
    if (is_array($wo['delete'])) {
        foreach ($wo['delete'] as $d) {
            $sql = "DELETE FROM m_work_order_line WHERE m_work_order_line_id = '{$d}'";
            mysql_query($sql, $APP_CONNECTION);
        }
    }
    
    $_SESSION[$APP_ID]['trx.wo']['info'] = "Dokumen sudah berhasil disimpan";
    
    $res->script("window.location = 'module.php?m=trx.wo&pkey[m_work_order_id]={$wo_id}';");
    return $res;
}

function updateLine($data) {
    global $APP_ID;
    
    $wo = $_SESSION[$APP_ID]['wo'];
    $res = new xajaxResponse();
    if ((int) $data['order_quantity'] <= 0) {
        $error = "Jumlah barang tidak boleh kosong";
    } elseif (empty($data['c_order_id']) AND empty($data['c_forecast_id']) ) {
        $error = "Sales Order atau Forecast tidak boleh kosong";
    } elseif (empty($data['m_product_id'])) {
        $error = "Kode barang tidak boleh kosong";
    } elseif (empty($data['m_product_material'])) {
        $error = "Long Pipe tidak boleh kosong";
    }
    if ($error) {
        $res->alert($error);
        return $res;
    }
    
    if (is_array($wo['lines'])) {
        foreach ($wo['lines'] as $k => $d) {
            if ($d['line'] == $data['line']) {
                $wo['lines'][$k]['order_quantity'] = $data['order_quantity'];
                $wo['lines'][$k]['material_quantity'] = $data['material_quantity'];
                $wo['lines'][$k]['c_order_id'] = $data['c_order_id'];
                $wo['lines'][$k]['c_forecast_id'] = $data['c_forecast_id'];
                $wo['lines'][$k]['m_product_id'] = $data['m_product_id'];
                $wo['lines'][$k]['m_product_material'] = $data['m_product_material'];
                $wo['lines'][$k]['tolerance_size'] = $data['tolerance_size'];
                $product = cgx_fetch_table("SELECT * FROM m_product WHERE m_product_id = '{$data['m_product_id']}'");
                if (is_array($product)) foreach ($product as $pk => $pv) if (!is_numeric($pk)) $wo['lines'][$k][$pk] = $pv;
                $materia = cgx_fetch_table("SELECT CONCAT(od, ' x ', thickness, ' x ', length) size_lp FROM m_product WHERE m_product_id = '{$data['m_product_material']}'");
                if (is_array($materia)) foreach ($materia as $pk => $pv) if (!is_numeric($pk)) $wo['lines'][$k][$pk] = $pv;
                if (empty($data['c_order_id'])){
                    $sql_nya = "SELECT document_no remark, mid(partner_name,1,20) partner_name FROM c_forecast JOIN c_bpartner USING (c_bpartner_id) WHERE c_forecast_id = '{$data['c_forecast_id']}'";
                }else{
                    $sql_nya = "SELECT remark, mid(partner_name,1,20) partner_name FROM c_order JOIN c_bpartner USING (c_bpartner_id) WHERE c_order_id = '{$data['c_order_id']}'";    
                }
                $corder = cgx_fetch_table($sql_nya);
                if (is_array($corder)) foreach ($corder as $pk => $pv) if (!is_numeric($pk)) $wo['lines'][$k][$pk] = $pv;
                $line_updated = TRUE;
                break;
            }
        }
    }
    if (!$line_updated) {
        $new_line['order_quantity'] = $data['order_quantity'];
        $new_line['material_quantity'] = $data['material_quantity'];
        $new_line['c_order_id'] = $data['c_order_id'];
        $new_line['c_forecast_id'] = $data['c_forecast_id'];
        $new_line['m_product_id'] = $data['m_product_id'];
        $new_line['m_product_material'] = $data['m_product_material'];
        $new_line['tolerance_size'] = $data['tolerance_size'];
        $product = cgx_fetch_table("SELECT * FROM m_product WHERE m_product_id = '{$data['m_product_id']}'");
        if (is_array($product)) foreach ($product as $pk => $pv) if (!is_numeric($pk)) $new_line[$pk] = $pv;
        $materia = cgx_fetch_table("SELECT CONCAT(od, ' x ', thickness, ' x ', length) size_lp FROM m_product WHERE m_product_id = '{$data['m_product_material']}'");
        if (is_array($materia)) foreach ($materia as $pk => $pv) if (!is_numeric($pk)) $new_line[$pk] = $pv;
        if (empty($data['c_order_id'])){
            $sql_nya = "SELECT document_no remark, mid(partner_name,1,20) partner_name FROM c_forecast JOIN c_bpartner USING (c_bpartner_id) WHERE c_forecast_id = '{$data['c_forecast_id']}'";
        }else{
            $sql_nya = "SELECT remark, mid(partner_name,1,20) partner_name FROM c_order JOIN c_bpartner USING (c_bpartner_id) WHERE c_order_id = '{$data['c_order_id']}'";    
        }
        $corder = cgx_fetch_table($sql_nya);
        if (is_array($corder)) foreach ($corder as $pk => $pv) if (!is_numeric($pk)) $new_line[$pk] = $pv;
        $wo['lines'][] = $new_line;
    }
    $_SESSION[$APP_ID]['wo'] = $wo;
    
    $res->script("xajax_showLines('{$wo['m_work_order_id']}', 'edit');");
    $res->assign('area-edit', 'style.display', 'none');
    $res->assign('master-button', 'style.display', '');
    return $res;
}

$xajax->register(XAJAX_FUNCTION, 'showLines');
$xajax->register(XAJAX_FUNCTION, 'editForm');
$xajax->register(XAJAX_FUNCTION, 'updateLine');
$xajax->register(XAJAX_FUNCTION, 'deleteLine');
$xajax->register(XAJAX_FUNCTION, 'saveWO');

?>