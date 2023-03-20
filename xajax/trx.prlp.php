<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.org>
 * Nov 24, 2013 8:18:31 AM
 */

function ctl_edit_prlp($data) {
    $href = "xajax_editForm('{$data['record']['line']}');";
    $out = "<img onclick=\"{$href}\" style='cursor: pointer;' title='Edit' src='images/icon_edit.png' border='0'>";
    return $out;
}

function ctl_delete_prlp($data) {    
    $href = "xajax_deleteLine('{$data['record']['line']}');";
    $out = "<img onclick=\"{$href}\" style='cursor: pointer;' title='Hapus' src='images/icon_delete.png' border='0'>";
    return $out;
}

function showLines($m_receipt_longpipe_id, $mode = NULL) {
    global $APP_CONNECTION, $APP_ID, $cgx_TableAttribs, $cgx_HeaderAttribs,
        $cgx_EvenRowAttribs, $cgx_OddRowAttribs, $cgx_RendererOptions;
    
    $data = $_SESSION[$APP_ID]['prlp'];
    if ($data['m_receipt_longpipe_id'] != $m_receipt_longpipe_id) return;
    
    if (is_array($data['lines'])) {
        $n = 0;
        foreach ($data['lines'] as $k => $d) {
            $n++;
            $data['lines'][$k]['line'] = $n;
        }
    }
    $_SESSION[$APP_ID]['prlp'] = $data;

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
    $datagrid->addColumn(new Structures_DataGrid_Column('Item Number', 'product_code', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Spec', 'spec', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('OD', 'od', NULL, array('align' => 'right'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Thickness', 'thickness', NULL, array('align' => 'right'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Length', 'length', NULL, array('align' => 'right'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('BMBJ', 'document_no_bmbj', NULL, array('align' => 'left', 'width' => '8%'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Qty Penerimaan', 'quantity_bmbj', NULL, array('align' => 'right', 'width' => '8%'), NULL, "cgx_format_3digit"));
    
    if ($mode == 'edit') $datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '20'), NULL, 'ctl_edit_prlp()'));
    //if ($mode == 'edit') $datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '20'), NULL, 'ctl_delete_prlp()'));

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
    
    foreach ($_SESSION[$APP_ID]['prlp']['lines'] as $line) {
        if ($line['line'] == $line_no) {
            $data = $line;
            break;
        }
    }
    
    $select_prlp = "<img onclick=\"popupReferenceAmbil('work-order-id','&p1='+ document.getElementById('m_work_order_id').value);\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'>";    
    $select_bmbj = "<img onclick=\"popupReference('work-order-bmbj');\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'>";
    
    $html .= "<form id='frmLine'>";
    $html .= "  <input type='hidden' name='line' value='{$data['line']}'>";
    
    $html .= "  <input type='hidden' id='m_receipt_longpipe_line_id' name='m_receipt_longpipe_line_id' value='{$data['m_receipt_longpipe_line_id']}'>";
    $html .= "  <input type='hidden' id='m_work_order_line_id' name='m_work_order_line_id' value='{$data['m_work_order_line_id']}'>";
    $html .= "  <input type='hidden' id='m_product_id' name='m_product_id' value='{$data['m_product_id']}'>";
     $html .= "  <input type='hidden' id='c_order_id' name='c_order_id' value='{$data['c_order_id']}'>";
    $html .= "  <input type='hidden' id='m_inout_id' name='m_inout_id' value='{$data['m_inout_id']}'>";
  
    $html .= "  <table width='100%'>";
    $html .= "      <tr>";
    $html .= "          <td width='10%'>Item Number {$mandatory}</td>";
    $html .= "          <td width='32%'><input id='product_code' type='text' size='20' name='product_code' value=\"{$data['product_code']}\" style='text-align: left;' readonly='readonly'>{$select_prlp}</td>";
    //$html .= "          <td width='3%'></td>";
    $html .= "          <td width='10%'>BMBB</td>";
    $html .= "          <td width='32%'><input id='document_no_bmbj' type='text' size='20' name='document_no_bmbj' value=\"{$data['document_no_bmbj']}\" style='text-align: left;'>{$select_bmbj}</td>";
    $html .= "      </tr>";
    $html .= "      <tr>";    
    $html .= "          <td width='10%'>Product Name</td>";
    $html .= "          <td width='32%'><input id='product_name' type='text' size='40' name='product_name' value=\"{$data['product_name']}\" style='text-align: left;' readonly='readonly'></td>";
    //$html .= "          <td width='3%'></td>";
    $html .= "          <td width='14%'>Quantity Penerimaan {$mandatory}</td>";
    $html .= "          <td width='32%'><input id='quantity_bmbj' type='text' size='6' name='quantity_bmbj' value=\"{$data['quantity_bmbj']}\" style='text-align: left;'></td>";
    
    $html .= "      </tr>";
    $html .= "      <tr>";    
    $html .= "          <td width='10%'>Quantity Request</td>";
    $html .= "          <td width='32%'><input id='material_quantity' type='text' size='6' name='material_quantity' value=\"{$data['material_quantity']}\" style='text-align: left;' readonly='readonly'></td>";
    $html .= "      </tr>";    
    $html .= "      <tr>
                    <td></td><td>
                    <input type='button' value='" . ($line_no ? 'Update' : 'Tambahkan') . "' onclick=\"xajax_updateLine(xajax.getFormValues('frmLine'));\">
                    ";
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
    
    $prlp = $_SESSION[$APP_ID]['prlp'];
    foreach ($prlp['lines'] as $k => $line) {
        if ($line['line'] == $line_no) {
            $del = $k;
            if ($line['m_receipt_longpipe_line_id']) {
                if ($prlp['delete']) {
                    $prlp['delete'][] = $line['m_receipt_longpipe_line_id'];
                } else {
                    $prlp['delete'] = array($line['m_receipt_longpipe_line_id']);
                }
            }
        }
    }
    unset($prlp['lines'][$del]);
    $_SESSION[$APP_ID]['prlp'] = $prlp;

    $res = new xajaxResponse();
    $res->script("xajax_showLines('{$prlp['m_receipt_longpipe_id']}', 'edit');");
    $res->assign('area-edit', 'style.display', 'none');
    $res->assign('master-button', 'style.display', '');
    return $res;
}

function savePRLP($data) {
    global $APP_ID, $APP_CONNECTION;
    
    $prlp = $_SESSION[$APP_ID]['prlp'];
    $res = new xajaxResponse();
    if(empty($data['wo'])){
        $error = "Request LP tidak boleh kosong";
    }elseif (cgx_emptydate($data['receipt_date'])) {
        $error = "Tanggal receipt tidak boleh kosong";
    } elseif (count($prlp['lines']) == 0) {
        $error = "Detail item barang tidak boleh kosong, harus ada minimal satu baris.";
        //print_r($wo['lines']);
    }
    
    if ($error) {
        $res->alert($error);
        return $res;
    }
    
    if ($data['m_receipt_longpipe_id']) {
        $sql = "UPDATE m_receipt_longpipe SET receipt_date = '" . cgx_dmy2ymd($data['receipt_date']) . "',
         update_date = NOW(), update_user = '" . user() . "' WHERE m_receipt_longpipe_id = '{$data['m_receipt_longpipe_id']}'";
    } else {
        require_once 'lib/class.Penomoran.php';
        $nomor = new Penomoran();
        $document_no = $nomor->urut('PR', org());
        $sql = "INSERT INTO m_receipt_longpipe (document_no, receipt_date, m_work_order_id, create_date, create_user)
         VALUES ('{$document_no}', '" . cgx_dmy2ymd($data['receipt_date']) . "', '{$data['m_work_order_id']}', NOW() , '". user() ."')";
    }
    $rsx = mysql_query($sql, $APP_CONNECTION);
    if (!$rsx) {
        $error = mysql_error($APP_CONNECTION);
        $res->alert($error);
        return $res;
    }
    if ($data['m_receipt_longpipe_id']) {
        $prlp_id = $data['m_receipt_longpipe_id'];
    } else {
        $prlp_id = mysql_insert_id($APP_CONNECTION);
    }
    
    foreach ($prlp['lines'] as $line) {
        if ($line['m_receipt_longpipe_line_id']) {
            $awal = cgx_fetch_table("SELECT * FROM m_receipt_longpipe_line WHERE m_receipt_longpipe_line_id = '{$line['m_receipt_longpipe_line_id']}'");
            
            $sql = "UPDATE m_receipt_longpipe_line SET 
                    document_no_bmbj = '{$line['document_no_bmbj']}', quantity_bmbj = '{$line['quantity_bmbj']}'  
                     WHERE m_receipt_longpipe_line_id = '{$line['m_receipt_longpipe_line_id']}'";
            //$rsx = mysql_query($sql, $APP_CONNECTION);
            $rsx = mysql_query($sql, $APP_CONNECTION);
            $quantity_bmbj = $line['quantity_bmbj'] - $awal['quantity_bmbj'];
            
            mysql_query("UPDATE m_work_order_line SET producted_quantity = producted_quantity + {$quantity_bmbj} 
            WHERE m_work_order_line_id = '{$line['m_work_order_line_id']}'",$APP_CONNECTION);
            
        } else {  
            $sql = "INSERT INTO m_receipt_longpipe_line (m_receipt_longpipe_id, m_work_order_line_id, document_no_bmbj, quantity_bmbj) 
            VALUES ('{$prlp_id}', '{$line['m_work_order_line_id']}', '{$line['document_no_bmbj']}', '{$line['quantity_bmbj']}')";
            
            
          $rsx = mysql_query($sql, $APP_CONNECTION);  
            mysql_query("UPDATE m_work_order_line SET producted_quantity = producted_quantity + {$line['quantity_bmbj']} 
            WHERE m_work_order_line_id = '{$line['m_work_order_line_id']}'",$APP_CONNECTION);
        }
        
    }
    
    if (is_array($prlp['delete'])) {
        foreach ($prlp['delete'] as $d) {
            $sql = "DELETE FROM m_receipt_longpipe_line WHERE m_receipt_longpipe_line_id = '{$d}'";
            mysql_query($sql, $APP_CONNECTION);
        }
    }
    
    $_SESSION[$APP_ID]['trx.prlp']['info'] = "Dokumen sudah berhasil disimpan";
            
    
    $res->script("window.location = 'module.php?m=trx.prlp&pkey[m_receipt_longpipe_id]={$prlp_id}';");
    return $res;
}

function updateLine($data) {
    global $APP_ID;
    
    $prlp = $_SESSION[$APP_ID]['prlp'];
    $res = new xajaxResponse();
    if(empty($data['product_code'])){
        $error = "Item number tidak boleh kosong";
    }
    elseif(empty($data['quantity_bmbj'])){
        $error = "Quantity penerimaan tidak boleh kosong";
    }
    //if(empty($data['request_lp'])) {
       // $error = "Request LP tidak boleh kosong";
    //}
    //if ((int) $data['quantity'] <= 0) {
      //  $error = "Jumlah barang tidak boleh kosong";
    //} 
    //} elseif (empty($data['m_product_id'])) {
        //$error = "Kode barang tidak boleh kosong";
    //} elseif (empty($data['m_product_material'])) {
        //$error = "Long Pipe tidak boleh kosong";}
    
    if ($error) {
        $res->alert($error);
        return $res;
    }
    
    if (is_array($prlp['lines'])) {
        foreach ($prlp['lines'] as $k => $d) {
            if ($d['line'] == $data['line']) {
                
                //$prlp['lines'][$k]['order_quantity'] = $data['order_quantity'];
                //$prlp['lines'][$k]['c_order_id'] = $data['c_order_id'];
                $prlp['lines'][$k]['product_code'] = $data['product_code'];
                $prlp['lines'][$k]['product_name'] = $data['product_name'];
                $prlp['lines'][$k]['material_quantity'] = $data['material_quantity'];
                $prlp['lines'][$k]['document_no_bmbj'] = $data['document_no_bmbj'];
                $prlp['lines'][$k]['quantity_bmbj'] = $data['quantity_bmbj'];
                
                $prlp['lines'][$k]['m_product_id'] = $data['m_product_id'];
                $prlp['lines'][$k]['c_order_id'] = $data['c_order_id'];
                $prlp['lines'][$k]['m_work_order_line_id'] = $data['m_work_order_line_id'];
                $prlp['lines'][$k]['m_inout_id'] = $data['m_inout_id'];
                
                //$prlp['lines'][$k]['m_product_material'] = $data['m_product_material'];
                $product = cgx_fetch_table("SELECT * FROM m_product WHERE m_product_id = '{$data['m_product_id']}'");
                if (is_array($product)) foreach ($product as $pk => $pv) if (!is_numeric($pk)) $prlp['lines'][$k][$pk] = $pv;
                
                //$product = cgx_fetch_table("SELECT * FROM m_work_order_line WHERE m_work_order_line_id = '{$data['m_work_order_line_id']}'");
                //if (is_array($product)) foreach ($product as $pk => $pv) if (!is_numeric($pk)) $prlp['lines'][$k][$pk] = $pv;
                //$materia = cgx_fetch_table("SELECT CONCAT(od, ' x ', thickness, ' x ', length) size, spec, product_code FROM m_product WHERE m_product_id = '{$data['m_product_id']}'");
                //if (is_array($materia)) foreach ($materia as $pk => $pv) if (!is_numeric($pk)) $prlp['lines'][$k][$pk] = $pv;
                $corder = cgx_fetch_table("SELECT c_order_id, remark, mid(partner_name,1,20) partner_name FROM c_order JOIN c_bpartner USING (c_bpartner_id) WHERE c_order_id = '{$data['c_order_id']}'");
                if (is_array($corder)) foreach ($corder as $pk => $pv) if (!is_numeric($pk)) $prlp['lines'][$k][$pk] = $pv;
                $line_updated = TRUE;
                break;
            }
        }
    }
    if (!$line_updated) {
        $new_line['product_code'] = $data['product_code'];
        $new_line['product_name'] = $data['product_name'];
        $new_line['material_quantity'] = $data['material_quantity'];
        
        $new_line['m_product_id'] = $data['m_product_id'];
        $new_line['c_order_id'] = $data['c_order_id'];
        $new_line['m_work_order_line_id'] = $data['m_work_order_line_id'];
        $new_line['m_inout_id'] = $data['m_inout_id'];
        
        $new_line['document_no_bmbj'] = $data['document_no_bmbj'];
        $new_line['quantity_bmbj'] = $data['quantity_bmbj'];
        
        $product = cgx_fetch_table("SELECT * FROM m_product WHERE m_product_id = '{$data['m_product_id']}'");
        if (is_array($product)) foreach ($product as $pk => $pv) if (!is_numeric($pk)) $new_line[$pk] = $pv;
        //$materia = cgx_fetch_table("SELECT CONCAT(od, ' x ', thickness, ' x ', length) size, spec, product_code FROM m_product WHERE m_product_id = '{$data['m_product_id']}'");
        //if (is_array($materia)) foreach ($materia as $pk => $pv) if (!is_numeric($pk)) $new_line[$pk] = $pv;
        $corder = cgx_fetch_table("SELECT document_no so, remark, mid(partner_name,1,20) partner_name FROM c_order JOIN c_bpartner USING (c_bpartner_id) WHERE c_order_id = '{$data['c_order_id']}'");
        if (is_array($corder)) foreach ($corder as $pk => $pv) if (!is_numeric($pk)) $new_line[$pk] = $pv;
        $prlp['lines'][] = $new_line;      
    }
    $_SESSION[$APP_ID]['prlp'] = $prlp;
    
    $res->script("xajax_showLines('{$prlp['m_receipt_longpipe_id']}', 'edit');");
    $res->assign('area-edit', 'style.display', 'none');
    $res->assign('master-button', 'style.display', '');
    return $res;
}

$xajax->register(XAJAX_FUNCTION, 'showLines');
$xajax->register(XAJAX_FUNCTION, 'editForm');
$xajax->register(XAJAX_FUNCTION, 'updateLine');
$xajax->register(XAJAX_FUNCTION, 'deleteLine');
$xajax->register(XAJAX_FUNCTION, 'savePRLP');
$xajax->register(XAJAX_FUNCTION, 'updatePRLP');

?>