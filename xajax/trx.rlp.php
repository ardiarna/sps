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
    
    $data = $_SESSION[$APP_ID]['rlp'];
    if ($data['m_work_order_id'] != $m_work_order_id) return;
    
    if (is_array($data['lines'])) {
        $n = 0;
        foreach ($data['lines'] as $k => $d) {
            $n++;
            $data['lines'][$k]['line'] = $n;
        }
    }
    $_SESSION[$APP_ID]['rlp'] = $data;

    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';
    
    $datagrid = new Structures_DataGrid(9999);
    $datagrid->bind($data['lines'], array(), 'Array');
    $cgx_table = new HTML_Table($cgx_TableAttribs);
    $cgx_tableHeader = & $cgx_table->getHeader();
    $cgx_tableBody = & $cgx_table->getBody();
    
    $datagrid->addColumn(new Structures_DataGrid_Column('Baris', 'line', NULL, array('align' => 'center'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Remark', 'remark', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Customer', 'partner_name', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Item Number', 'product_code_lp', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Spec', 'spec_lp', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Cutting', 'length', NULL, array('align' => 'right'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Size Long Pipe', 'size_lp', NULL, array('align' => 'right'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Quantity LP', 'material_quantity', NULL, array('align' => 'right'), NULL, "cgx_format_3digit"));
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
    
    foreach ($_SESSION[$APP_ID]['rlp']['lines'] as $line) {
        if ($line['line'] == $line_no) {
            $data = $line;
            break;
        }
    }
    
    $html .= "<form id='frmLine'>";
    $html .= "<input type='hidden' name='line' value='{$data['line']}'>";
    $html .= "<input type='hidden' name='m_work_order_line_id' value='{$data['m_work_order_line_id']}'>";
    $html .= "<input type='hidden' id='c_order_id' name='c_order_id' value='{$data['c_order_id']}'>";
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
    $html .= "<td>Remark</td>";
    $html .= "<td><input readonly='readonly' type='text' id='remark' size='20' value=\"{$data['remark']}\"></td>";
    $html .= "<td></td>";
    $html .= "<td>Spec</td>";
    $html .= "<td><input readonly='readonly' type='text' id='spec_lp' size='20' value=\"{$data['spec_lp']}\"></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Customer</td>";
    $html .= "<td><input readonly='readonly' type='text' id='partner_name' size='35' value=\"{$data['partner_name']}\"></td>";
    $html .= "<td></td>";
    $html .= "<td>Quantity LP {$mandatory}</td>";
    $html .= "<td><input type='text' size='10' id='material_quantity' name='material_quantity' value=\"{$data['material_quantity']}\" style='text-align: right;'></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Produk Cutting</td>";
    $html .= "<td><input readonly='readonly' type='text' id='product_name' size='35' value=\"{$data['product_name']}\"></td>";
    $html .= "<td></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Quantity Order</td>";
    $html .= "<td><input readonly='readonly' type='text' id='order_quantity' name='order_quantity' size='10' value=\"{$data['order_quantity']}\"></td>";
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
    
    $wo = $_SESSION[$APP_ID]['rlp'];
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
    $_SESSION[$APP_ID]['rlp'] = $wo;

    $res = new xajaxResponse();
    $res->script("xajax_showLines('{$wo['m_work_order_id']}', 'edit');");
    $res->assign('area-edit', 'style.display', 'none');
    $res->assign('master-button', 'style.display', '');
    return $res;
}

function saveWO($data) {
    global $APP_ID, $APP_CONNECTION;
    
    $wo = $_SESSION[$APP_ID]['rlp'];
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
        $sql = "UPDATE m_work_order SET order_date = '" . cgx_dmy2ymd($data['order_date']) . "', update_date = NOW(), update_user = '" . user() . "' WHERE m_work_order_id = '{$data['m_work_order_id']}'";
    } else {
        require_once 'lib/class.Penomoran.php';
        $nomor = new Penomoran();
        $document_no = $nomor->urut('RL', org());
        $sql = "INSERT INTO m_work_order (app_org_id, document_no, order_date, type_id, create_date, create_user)
            VALUES ('" . org() . "', '{$document_no}', '" . cgx_dmy2ymd($data['order_date']) . "', 'R', NOW() , '". user() ."')";
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
                material_quantity = '{$line['material_quantity']}' 
                WHERE m_work_order_line_id = '{$line['m_work_order_line_id']}'";
        } else {  
            $sql = "INSERT INTO m_work_order_line (m_work_order_id, m_product_id, c_order_id, order_quantity, m_product_material, 
                material_quantity) VALUES ('{$wo_id}', '{$line['m_product_id']}', '{$line['c_order_id']}', 
                '{$line['order_quantity']}', '{$line['m_product_material']}', '{$line['material_quantity']}')";
        }
        $rsx = mysql_query($sql, $APP_CONNECTION);
    }
    
    if (is_array($wo['delete'])) {
        foreach ($wo['delete'] as $d) {
            $sql = "DELETE FROM m_work_order_line WHERE m_work_order_line_id = '{$d}'";
            mysql_query($sql, $APP_CONNECTION);
        }
    }
    
    $_SESSION[$APP_ID]['trx.rlp']['info'] = "Dokumen sudah berhasil disimpan";
    
    $res->script("window.location = 'module.php?m=trx.rlp&pkey[m_work_order_id]={$wo_id}';");
    return $res;
}

function updateLine($data) {
    global $APP_ID;
    
    $wo = $_SESSION[$APP_ID]['rlp'];
    $res = new xajaxResponse();
    if ((int) $data['material_quantity'] <= 0) {
        $error = "Jumlah barang tidak boleh kosong";
    } elseif (empty($data['c_order_id'])) {
        $error = "Sales Order tidak boleh kosong";
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
                $wo['lines'][$k]['material_quantity'] = $data['material_quantity'];
                $wo['lines'][$k]['order_quantity'] = $data['order_quantity'];
                $wo['lines'][$k]['c_order_id'] = $data['c_order_id'];
                $wo['lines'][$k]['m_product_id'] = $data['m_product_id'];
                $wo['lines'][$k]['m_product_material'] = $data['m_product_material'];
                $product = cgx_fetch_table("SELECT * FROM m_product WHERE m_product_id = '{$data['m_product_id']}'");
                if (is_array($product)) foreach ($product as $pk => $pv) if (!is_numeric($pk)) $wo['lines'][$k][$pk] = $pv;
                $materia = cgx_fetch_table("SELECT CONCAT(od, ' x ', thickness, ' x ', length) size_lp, spec spec_lp, product_code product_code_lp FROM m_product WHERE m_product_id = '{$data['m_product_material']}'");
                if (is_array($materia)) foreach ($materia as $pk => $pv) if (!is_numeric($pk)) $wo['lines'][$k][$pk] = $pv;
                $corder = cgx_fetch_table("SELECT document_no so, remark, mid(partner_name,1,20) partner_name FROM c_order JOIN c_bpartner USING (c_bpartner_id) WHERE c_order_id = '{$data['c_order_id']}'");
                if (is_array($corder)) foreach ($corder as $pk => $pv) if (!is_numeric($pk)) $wo['lines'][$k][$pk] = $pv;
                $line_updated = TRUE;
                break;
            }
        }
    }
    if (!$line_updated) {
        $new_line['material_quantity'] = $data['material_quantity'];
        $new_line['order_quantity'] = $data['order_quantity'];
        $new_line['c_order_id'] = $data['c_order_id'];
        $new_line['m_product_id'] = $data['m_product_id'];
        $new_line['m_product_material'] = $data['m_product_material'];
        $product = cgx_fetch_table("SELECT * FROM m_product WHERE m_product_id = '{$data['m_product_id']}'");
        if (is_array($product)) foreach ($product as $pk => $pv) if (!is_numeric($pk)) $new_line[$pk] = $pv;
        $materia = cgx_fetch_table("SELECT CONCAT(od, ' x ', thickness, ' x ', length) size_lp, spec spec_lp, product_code product_code_lp FROM m_product WHERE m_product_id = '{$data['m_product_material']}'");
        if (is_array($materia)) foreach ($materia as $pk => $pv) if (!is_numeric($pk)) $new_line[$pk] = $pv;
        $corder = cgx_fetch_table("SELECT document_no so, remark, mid(partner_name,1,20) partner_name FROM c_order JOIN c_bpartner USING (c_bpartner_id) WHERE c_order_id = '{$data['c_order_id']}'");
        if (is_array($corder)) foreach ($corder as $pk => $pv) if (!is_numeric($pk)) $new_line[$pk] = $pv;
        $wo['lines'][] = $new_line;
    }
    $_SESSION[$APP_ID]['rlp'] = $wo;
    
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