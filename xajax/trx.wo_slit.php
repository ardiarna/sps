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

function ctl_delete_coil($data) {
    $href = "xajax_deleteLineDua('{$data['record']['line']}');";
    $out = "<img onclick=\"{$href}\" style='cursor: pointer;' title='Hapus' src='images/icon_delete.png' border='0'>";
    return $out;
}

function showLines($m_wo_slit_id, $mode = NULL) {
    global $APP_CONNECTION, $APP_ID, $cgx_TableAttribs, $cgx_HeaderAttribs,
        $cgx_EvenRowAttribs, $cgx_OddRowAttribs, $cgx_RendererOptions;
    
    $data = $_SESSION[$APP_ID]['wo_slit'];
    if ($data['m_wo_slit_id'] != $m_wo_slit_id) return;
    
    if (is_array($data['lines'])) {
        $n = 0;
        foreach ($data['lines'] as $k => $d) {
            $n++;
            $data['lines'][$k]['line'] = $n;
        }
    }
    $_SESSION[$APP_ID]['wo_slit'] = $data;

    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';
    
    $datagrid = new Structures_DataGrid(9999);
    $datagrid->bind($data['lines'], array(), 'Array');
    $cgx_table = new HTML_Table($cgx_TableAttribs);
    $cgx_tableHeader = & $cgx_table->getHeader();
    $cgx_tableBody = & $cgx_table->getBody();
    
    $datagrid->addColumn(new Structures_DataGrid_Column('Baris', 'line', NULL, array('align' => 'center'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Item Slitting', 'product_code_slit', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Spec Slitting', 'spec_slit', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Ukuran Slitting', 'ukuran_slit', NULL, array('align' => 'right'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Jalur', 'order_quantity', NULL, array('align' => 'right', 'width' => '10%'), NULL, "cgx_format_3digit"));    
    $datagrid->addColumn(new Structures_DataGrid_Column('Long Pipe', 'od_lp', NULL, array('align' => 'right', 'width' => '10%'), NULL, NULL));
    if ($mode == 'edit' OR $mode == 'editH')  $datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '20'), NULL, 'ctl_edit_wo()'));
    if ($mode == 'edit' OR $mode == 'editH') $datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '20'), NULL, 'ctl_delete_wo()'));

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

function showLinesDua($m_wo_slit_id, $mode = NULL) {
    global $APP_CONNECTION, $APP_ID, $cgx_TableAttribs, $cgx_HeaderAttribs,
        $cgx_EvenRowAttribs, $cgx_OddRowAttribs, $cgx_RendererOptions;
    
    $data = $_SESSION[$APP_ID]['wo_slit'];
    if ($data['m_wo_slit_id'] != $m_wo_slit_id) return;
    
    if (is_array($data['linesdua'])) {
        $n = 0;
        foreach ($data['linesdua'] as $k => $d) {
            $n++;
            $data['linesdua'][$k]['line'] = $n;
        }
    }
    $_SESSION[$APP_ID]['wo_slit'] = $data;

    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';
    
    $datagrid = new Structures_DataGrid(9999);
    $datagrid->bind($data['linesdua'], array(), 'Array');
    $cgx_table = new HTML_Table($cgx_TableAttribs);
    $cgx_tableHeader = & $cgx_table->getHeader();
    $cgx_tableBody = & $cgx_table->getBody();
    
    $datagrid->addColumn(new Structures_DataGrid_Column('Baris', 'line', NULL, array('align' => 'center'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('No. Coil Raw', 'no_coil', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Kode Coil Raw', 'no_lot', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Berat (Kg)', 'weight', NULL, array('align' => 'right'), NULL, "cgx_format_money"));
    if ($mode == 'editH') $datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '20'), NULL, 'ctl_delete_coil()'));

    $datagrid->fill($cgx_table, $cgx_RendererOptions);
    $cgx_tableHeader->setRowAttributes(0, $cgx_HeaderAttribs);
    $cgx_tableBody->altRowAttributes(0, $cgx_EvenRowAttribs, $cgx_OddRowAttribs, TRUE);

    $html  = "<div class='datagrid_background'>\n";
    $html .= $cgx_table->toHtml();
    $html .= "</div>\n";
    $res = new xajaxResponse();
    $nilai = 0 ;
    $qty = 0;
    foreach ($data['linesdua'] as $linedua) {
        $nilai += $linedua['weight'];
        $qty += 1;
    }
    $res->script("document.getElementById('quantity').value=$qty");
    $res->script("document.getElementById('weight_raw').value=$nilai");
    $res->assign('area-lines-dua', 'innerHTML', $html);
    return $res;
}

function editForm($line_no) {
    global $APP_CONNECTION, $APP_ID, $APP_DATE_FORMAT, $APP_DATE_FORMAT_JAVA;
    global $mandatory;
    
    foreach ($_SESSION[$APP_ID]['wo_slit']['lines'] as $line) {
        if ($line['line'] == $line_no) {
            $data = $line;
            break;
        }
    }
    
    $html .= "<form id='frmLine'>";
    $html .= "<input type='hidden' name='line' value='{$data['line']}'>";
    $html .= "<input type='hidden' name='m_wo_slit_line_id' value='{$data['m_wo_slit_line_id']}'>";
    $html .= "<input type='hidden' id='m_product_slit' name='m_product_slit' value='{$data['m_product_slit']}'>";
    $html .= "<table width='100%'>";
    $html .= "<tr>";
    $html .= "<td width='12%'>Item Slitting{$mandatory}</td>";
    $html .= "<td width='30%'><input type='text' size='20' id='product_code_slit' name='product_code_slit' value=\"{$data['product_code_slit']}\" readonly='readonly'><img onclick=\"popupForm('form.master.product_c','&id=' + document.getElementById('m_product_id').value);\" style='cursor: pointer; margin: -2px 5px;' src='images/icon_add.png'><img onclick=\"popupReferenceAmbil('hasil-slit','&p1=' + document.getElementById('thickness').value);\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'></td>"; 
    $html .= "<td width='6%'></td>";
    $html .= "<td width='15%'> Jumlah Jalur {$mandatory}</td>";
    $html .= "<td width='37%'><input type='text' size='10' name='order_quantity' value=\"{$data['order_quantity']}\" style='text-align: right;'></td>";    
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Spec Slitting</td>";
    $html .= "<td><input type='text' size='30' id='spec_slit' name='spec_slit' value=\"{$data['spec_slit']}\" readonly='readonly'></td>";
    $html .= "<td></td>";
    $html .= "<td>OD Longpipe</td>";
    $html .= "<td><input name='od_lp' type='text' size='10' value=\"{$data['od_lp']}\" style='text-align: right;'></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Ukuran Slitting</td>";
    $html .= "<td><input type='text' size='30' id='ukuran_slit' name='ukuran_slit' value=\"{$data['ukuran_slit']}\" readonly='readonly'></td>";
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

function editFormDua($line_no) {
    global $APP_CONNECTION, $APP_ID, $APP_DATE_FORMAT, $APP_DATE_FORMAT_JAVA;
    global $mandatory;
    foreach ($_SESSION[$APP_ID]['wo_slit']['linesdua'] as $line) {
        if ($line['line'] == $line_no) {
            $data = $line;
            break;
        }
    }
    $html .= "<form id='frmLineDua'>";
    $html .= "<input type='hidden' name='line' value='{$data['line']}'>";
    $html .= "<input type='hidden' id='m_coil_id' name='m_coil_id' value='{$data['m_coil_id']}'>";
    $html .= "<table width='100%'>";
    $html .= "<tr>";
    $html .= "<td width='13%'>Nomor Coil {$mandatory}</td>";
    $html .= "<td width='32%'><input type='text' size='30' id='no_coil' value=\"{$data['no_coil']}\" style='text-align: left;' readonly='readonly'><img onclick=\"popupReferenceAmbil('m_coil','&p1=' + document.getElementById('m_product_id').value);\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'></td>";
    $html .= "<td width='10%'></td>";
    $html .= "<td width='13%'>Berat (Kg)</td>";
    $html .= "<td width='32%'><input type='text' size='10' id='weight' value=\"{$data['weight']}\" style='text-align: right;' readonly='readonly'></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Kode Coil</td>";
    $html .= "<td><input type='text' size='30' id='no_lot' value=\"{$data['no_lot']}\" style='text-align: left;' readonly='readonly'></td>";    
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td></td>";
    $html .= "<td><input type='button' value='" . ($line_no ? 'Update' : 'Tambahkan') . "' onclick=\"xajax_updateLineDua(xajax.getFormValues('frmLineDua'));\"> &nbsp; ";
    $html .= "<input type='button' value='Batal' onclick=\"document.getElementById('area-edit').style.display = 'none'; document.getElementById('master-button').style.display = '';\"></td>";
    $html .= "</tr>";
    $html .= "</table>";
    $html .= "</form>";
    
    $res = new xajaxResponse();
    $res->assign('area-edit', 'innerHTML', $html);
    $res->assign('area-edit', 'style.display', '');
    $res->assign('master-button', 'style.display', 'none');
    
    return $res;
}

function deleteLine($line_no) {
    global $APP_ID, $APP_CONNECTION;
    $wo_slit = $_SESSION[$APP_ID]['wo_slit'];
    foreach ($wo_slit['lines'] as $k => $line) {
        if ($line['line'] == $line_no) {
            $del = $k;
            if ($line['m_wo_slit_line_id']) {
                if ($wo_slit['delete']) {
                    $wo_slit['delete'][] = $line['m_wo_slit_line_id'];
                } else {
                    $wo_slit['delete'] = array($line['m_wo_slit_line_id']);
                }
            }
        }
    }
    unset($wo_slit['lines'][$del]);
    $_SESSION[$APP_ID]['wo_slit'] = $wo_slit;

    $res = new xajaxResponse();
    $res->script("xajax_showLines('{$wo_slit['m_wo_slit_id']}', 'edit');");
    $res->assign('area-edit', 'style.display', 'none');
    $res->assign('master-button', 'style.display', '');
    return $res;
}

function deleteLineDua($line_no) {
    global $APP_ID, $APP_CONNECTION;
    $wo_slit = $_SESSION[$APP_ID]['wo_slit'];
    foreach ($wo_slit['linesdua'] as $k => $line) {
        if ($line['line'] == $line_no) {
            $del = $k;
            if ($line['m_coil_id']) {
                if ($wo_slit['deletedua']) {
                    $wo_slit['deletedua'][] = $line['m_coil_id'];
                } else {
                    $wo_slit['deletedua'] = array($line['m_coil_id']);
                }
            }
        }
    }
    unset($wo_slit['linesdua'][$del]);
    $_SESSION[$APP_ID]['wo_slit'] = $wo_slit;
    $res = new xajaxResponse();
    $res->script("xajax_showLinesDua('{$wo_slit['m_wo_slit_id']}', 'editH');");
    $res->assign('area-edit', 'style.display', 'none');
    $res->assign('master-button', 'style.display', '');
    return $res;
}

function saveWO($data) {
    global $APP_ID, $APP_CONNECTION;
    
    $wo_slit = $_SESSION[$APP_ID]['wo_slit'];
    $res = new xajaxResponse();
    
    if (empty($data['m_product_id'])) {
        $error = "Item number material tidak boleh kosong";
    } elseif (cgx_emptydate($data['order_date'])) {
        $error = "Tanggal WO tidak boleh kosong";
    } elseif (empty($data['quantity'])) {
        $error = "Quantity Material tidak boleh kosong";
    } 
    /*
    elseif (empty($data['c_bpartner_id'])) {
        $error = "Customer tidak boleh kosong";
    }
    */
    elseif (empty($data['partner_name'])) {
        $error = "Customer tidak boleh kosong";
    }
    
    elseif (empty($data['width_actual'])) {
        $error = "Lebar aktual tidak boleh kosong";
    } elseif (empty($data['scrap'])) {
        $error = "Scrap tidak boleh kosong";
    } elseif (count($wo_slit['lines']) == 0) {
        $error = "Detail item slitting tidak boleh kosong, harus ada minimal satu baris.";
    }
    if ($error) {
        $res->alert($error);
        return $res;
    }
    
    require_once 'lib/class.Penomoran.php';
    $nomor = new Penomoran();
    if (empty($data['document_no'])) {
        $document_no = $nomor->urut('WS', org());
    } else{
        $document_no = $data['document_no'];
    }
    
    /*
    if($data['c_bpartner_id']==''){
        $field_partner = partner;
        $field_customer = $data['partner_name'];
    }
    else{
        $field_partner = c_bpartner_id;
        $field_customer = $data['c_bpartner_id'];
    }
    
    
    $sql = "INSERT INTO m_wo_slit (app_org_id, document_no, c_bpartner_id, m_product_id, quantity, weight, width_actual, scrap, order_date, create_user, create_date )
                      VALUES ('". org() . "', '{$document_no}', '{$data['c_bpartner_id']}', '{$data['m_product_id']}', '{$data['quantity']}', '{$data['weight_raw']}', 
        '{$data['width_actual']}', '{$data['scrap']}', '" . cgx_dmy2ymd($data['order_date']) . "', '". user()  ."', NOW())";
    */    
    
    $sql = "INSERT INTO m_wo_slit (app_org_id, document_no, partner , m_bkpc_id, m_product_id, quantity, weight, width_actual, scrap, order_date, create_user, create_date )
                      VALUES ('". org() . "', '{$document_no}', '{$data['partner_name']}', '{$data['m_bkpc_id']}', '{$data['m_product_id']}', '{$data['quantity']}', '{$data['weight_raw']}', 
        '{$data['width_actual']}', '{$data['scrap']}', '" . cgx_dmy2ymd($data['order_date']) . "', '". user()  ."', NOW())";
    
    
    //$res->alert($sql);
    //return $res;
    //exit();
        
    $rsx = mysql_query($sql, $APP_CONNECTION);
    if (!$rsx) {
        $error = mysql_error($APP_CONNECTION);
        $res->alert($error);
        return $res;
    }
    
    $wo_slit_id = mysql_insert_id($APP_CONNECTION);
    
    foreach ($wo_slit['lines'] as $line) {
        $sql = "INSERT INTO m_wo_slit_line (m_wo_slit_id,  m_product_id, order_quantity, od_lp) VALUES(
            '{$wo_slit_id}', '{$line['m_product_slit']}', '{$line['order_quantity']}', '{$line['od_lp']}')";
        $rsx = mysql_query($sql, $APP_CONNECTION);
    }

    foreach ($data['chkcoil'] as $m_coil_id => $coil_id) {
        $sql = "UPDATE m_coil SET status = 'W' , m_wo_slit_id = '{$wo_slit_id}' WHERE m_coil_id = '{$m_coil_id}'";
        $rsx = mysql_query($sql, $APP_CONNECTION);
    }
    
    $_SESSION[$APP_ID]['trx.wo_slit']['info'] = "Dokumen sudah berhasil disimpan";
    $res->script("window.location = 'module.php?m=trx.wo_slit&pkey[m_wo_slit_id]={$wo_slit_id}';");
    return $res;
}

function updateWO($data) {
    global $APP_ID, $APP_CONNECTION;
    
    $wo_slit = $_SESSION[$APP_ID]['wo_slit'];
    $res = new xajaxResponse();
    
    if (empty($data['m_product_id'])) {
        $error = "Item number material tidak boleh kosong";
    } elseif (cgx_emptydate($data['order_date'])) {
        $error = "Tanggal WO tidak boleh kosong";
    } elseif (empty($data['quantity'])) {
        $error = "Quantity Material tidak boleh kosong";
    }
    /*
    elseif (empty($data['c_bpartner_id'])) {
        $error = "Customer tidak boleh kosong";
    }
    */
    elseif (empty($data['partner_name'])) {
        $error = "Customer tidak boleh kosong";
    }
    elseif (empty($data['width_actual'])) {
        $error = "Lebar aktual tidak boleh kosong";
    } elseif (empty($data['scrap'])) {
        $error = "Scrap tidak boleh kosong";
    } elseif (count($wo_slit['lines']) == 0) {
        $error = "Detail item slitting tidak boleh kosong, harus ada minimal satu baris.";
    }
    if ($error) {
        $res->alert($error);
        return $res;
    }
    
    /*
    if($data['c_bpartner_id']==''){
        $field_partner = partner;
        $field_customer = $data['partner_name'];
    }
    else{
        $field_partner = c_bpartner_id;
        $field_customer = $data['c_bpartner_id'];
    }    
    
    if ($data['m_wo_slit_id']) {
        $sql = "UPDATE m_wo_slit SET order_date = '" . cgx_dmy2ymd($data['order_date']) . "', m_product_id = '{$data['m_product_id']}', c_bpartner_id = '{$data['c_bpartner_id']}', 
            document_no = '{$data['document_no']}', quantity = '{$data['quantity']}', weight = '{$data['weight_raw']}', 
            width_actual = '{$data['width_actual']}', scrap = '{$data['scrap']}', update_date = NOW(), update_user = '" . user() . "' WHERE m_wo_slit_id = '{$data['m_wo_slit_id']}'";
    } else {
        require_once 'lib/class.Penomoran.php';
        $nomor = new Penomoran();
        if (empty($data['document_no'])) {
            $document_no = $nomor->urut('WS', org());
        } else{
            $document_no = $data['document_no'];
        }
        $sql = "INSERT INTO m_wo_slit (app_org_id, document_no, c_bpartner_id, m_product_id, quantity, weight, width_actual, scrap, order_date, create_user, create_date )
            VALUES ('" . org() . "', '{$document_no}', '{$data['c_bpartner_id']}', '{$data['m_product_id']}', '{$data['quantity']}', '{$data['weight_raw']}', 
            '{$data['width_actual']}', '{$data['scrap']}', '" . cgx_dmy2ymd($data['order_date']) . "', '". user()  ."', NOW())";
    }
    */
    
    if ($data['m_wo_slit_id']) {
        $sql = "UPDATE m_wo_slit SET order_date = '" . cgx_dmy2ymd($data['order_date']) . "', m_bkpc_id = '{$data['m_bkpc_id']}', m_product_id = '{$data['m_product_id']}', partner = '{$data['partner_name']}', 
            document_no = '{$data['document_no']}', quantity = '{$data['quantity']}', weight = '{$data['weight_raw']}', 
            width_actual = '{$data['width_actual']}', scrap = '{$data['scrap']}', update_date = NOW(), update_user = '" . user() . "' WHERE m_wo_slit_id = '{$data['m_wo_slit_id']}'";
        //$res->alert($sql);
        //return $res;    
    } else {
        require_once 'lib/class.Penomoran.php';
        $nomor = new Penomoran();
        if (empty($data['document_no'])) {
            $document_no = $nomor->urut('WS', org());
        } else{
            $document_no = $data['document_no'];
        }
        $sql = "INSERT INTO m_wo_slit (app_org_id, document_no, partner, m_product_id, quantity, weight, width_actual, scrap, order_date, create_user, create_date )
            VALUES ('" . org() . "', '{$document_no}', '{$data['partner_name']}', '{$data['m_product_id']}', '{$data['quantity']}', '{$data['weight_raw']}', 
            '{$data['width_actual']}', '{$data['scrap']}', '" . cgx_dmy2ymd($data['order_date']) . "', '". user()  ."', NOW())";
    
        //$res->alert($sql);
        //return $res;
    }
    
    $rsx = mysql_query($sql, $APP_CONNECTION);
    if (!$rsx) {
        $error = mysql_error($APP_CONNECTION);
        $res->alert($error);
        return $res;
    }
    if ($data['m_wo_slit_id']) {
        $wo_slit_id = $data['m_wo_slit_id'];
    } else {
        $wo_slit_id = mysql_insert_id($APP_CONNECTION);
    }
    
    foreach ($wo_slit['lines'] as $line) {
        if ($line['m_wo_slit_line_id']) {
            $sql = "UPDATE m_wo_slit_line SET m_product_id = '{$line['m_product_slit']}', order_quantity = '{$line['order_quantity']}', 
            od_lp = '{$line['od_lp']}' WHERE m_wo_slit_line_id = '{$line['m_wo_slit_line_id']}'";
        } else {  
            $sql = "INSERT INTO m_wo_slit_line (m_wo_slit_id,  m_product_id, order_quantity, od_lp) VALUES(
                '{$wo_slit_id}', '{$line['m_product_slit']}', '{$line['order_quantity']}', '{$line['od_lp']}')";
        }
        $rsx = mysql_query($sql, $APP_CONNECTION);
    }

    foreach ($wo_slit['linesdua'] as $linedua) {
        $sql = "UPDATE m_coil SET status = 'W' , m_wo_slit_id = '{$wo_slit_id}' WHERE m_coil_id = '{$linedua['m_coil_id']}'";
        $rsx = mysql_query($sql, $APP_CONNECTION);
    }
    
    if (is_array($wo_slit['delete'])) {
        foreach ($wo_slit['delete'] as $d) {
            $sql = "DELETE FROM m_wo_slit_line WHERE m_wo_slit_line_id = '{$d}'";
            mysql_query($sql, $APP_CONNECTION);
        }
    }

    if (is_array($wo_slit['deletedua'])) {
        foreach ($wo_slit['deletedua'] as $d) {
            $sql = "UPDATE m_coil SET status = 'I' , m_wo_slit_id = null WHERE m_coil_id = '{$d}'";
            mysql_query($sql, $APP_CONNECTION);
        }
    }
    
    $_SESSION[$APP_ID]['trx.wo_slit']['info'] = "Dokumen sudah berhasil disimpan";
    
    $res->script("window.location = 'module.php?m=trx.wo_slit&pkey[m_wo_slit_id]={$wo_slit_id}';");
    return $res;
}

function updateLine($data) {
    global $APP_ID;
    
    $wo_slit = $_SESSION[$APP_ID]['wo_slit'];
    $res = new xajaxResponse();
    if ((int) $data['order_quantity'] <= 0 ) {
        $error = "Jumlah jalur tidak boleh kosong";
    } elseif (empty($data['m_product_slit'])) {
        $error = "Produk Slitting tidak boleh kosong";
    }

    if ($error) {
        $res->alert($error);
        return $res;
    }
    
    if (is_array($wo_slit['lines'])) {
        foreach ($wo_slit['lines'] as $k => $d) {
            if ($d['line'] == $data['line']) {
                $wo_slit['lines'][$k]['order_quantity'] = $data['order_quantity'];
                $wo_slit['lines'][$k]['m_product_slit'] = $data['m_product_slit'];
                $wo_slit['lines'][$k]['product_code_slit'] = $data['product_code_slit'];
                $wo_slit['lines'][$k]['ukuran_slit'] = $data['ukuran_slit'];
                $wo_slit['lines'][$k]['spec_slit'] = $data['spec_slit'];
                $wo_slit['lines'][$k]['od_lp'] = $data['od_lp'];                
                $line_updated = TRUE;
                break;
            }
        }
    }
    if (!$line_updated) {
        $new_line['order_quantity'] = $data['order_quantity'];
        $new_line['m_product_slit'] = $data['m_product_slit'];
        $new_line['product_code_slit'] = $data['product_code_slit'];
        $new_line['ukuran_slit'] = $data['ukuran_slit'];
        $new_line['spec_slit'] = $data['spec_slit'];
        $new_line['od_lp'] = $data['od_lp'];
        $wo_slit['lines'][] = $new_line;
    }
    $_SESSION[$APP_ID]['wo_slit'] = $wo_slit;
    
    $res->script("xajax_showLines('{$wo_slit['m_wo_slit_id']}', 'edit');");
    $res->assign('area-edit', 'style.display', 'none');
    $res->assign('master-button', 'style.display', '');
    return $res;
}

function updateLineDua($data) {
    global $APP_ID;
    $wo_slit = $_SESSION[$APP_ID]['wo_slit'];
    $res = new xajaxResponse();
    if (empty($data['m_coil_id'])) {
        $error = "No. Coil tidak boleh kosong";
    }
    if ($error) {
        $res->alert($error);
        return $res;
    }
    if (is_array($wo_slit['linesdua'])) {
        foreach ($wo_slit['linesdua'] as $k => $d) {
            if ($d['line'] == $data['line']) {
                $wo_slit['linesdua'][$k]['m_coil_id'] = $data['m_coil_id'];
                $coil = cgx_fetch_table("SELECT * FROM m_coil WHERE m_coil_id = '{$data['m_coil_id']}'");
                if (is_array($coil)) foreach ($coil as $pk => $pv) if (!is_numeric($pk)) $wo_slit['linesdua'][$k][$pk] = $pv;
                $line_updated = TRUE;
                break;
            }
        }
    }
    if (!$line_updated) {
        $new_line['m_coil_id'] = $data['m_coil_id'];
        $coil = cgx_fetch_table("SELECT * FROM m_coil WHERE m_coil_id = '{$data['m_coil_id']}'");
        if (is_array($coil)) foreach ($coil as $pk => $pv) if (!is_numeric($pk)) $new_line[$pk] = $pv;
        $wo_slit['linesdua'][] = $new_line;
    }
    $_SESSION[$APP_ID]['wo_slit'] = $wo_slit;
    $res->script("xajax_showLinesDua('{$wo_slit['m_wo_slit_id']}', 'editH');");
    $res->assign('area-edit', 'style.display', 'none');
    $res->assign('master-button', 'style.display', '');
    return $res;
}

function grid_chk_form($data) {
    $row_id = $data['record']['m_coil_id'];
    $weight = $data['record']['weight'];
    $html .= "<input type='checkbox' id='chkcoil[{$row_id}]' name='chkcoil[{$row_id}]' value='{$row_id}' onchange=\"
                var chk = document.getElementById('chkcoil[{$row_id}]');
                if(chk.checked == true){
                    weight_total = parseFloat(document.getElementById('weight_raw').value) + {$weight};
                    qty_total = parseFloat(document.getElementById('quantity').value) + 1;        
                }else{
                    weight_total = parseFloat(document.getElementById('weight_raw').value) - {$weight};
                    qty_total = parseFloat(document.getElementById('quantity').value) - 1;
                }
                document.getElementById('weight_raw').value = weight_total;
                document.getElementById('quantity').value = qty_total;
            \">";
    return $html;
}

function mCoilLinesForm($m_bkpc_id) {
    global $APP_CONNECTION, $cgx_TableAttribs, $cgx_HeaderAttribs, $cgx_dsn, $cgx_EvenRowAttribs, $cgx_OddRowAttribs, $cgx_RendererOptions;
    
    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';
    $datagrid = new Structures_DataGrid(9999);
    $cgx_options = array('dsn' => $cgx_dsn);
    $cgx_sql = "SELECT @curRow := @curRow + 1 AS line, m_coil.* FROM m_bkpc_line JOIN m_coil ON(m_bkpc_line.m_coil_id=m_coil.m_coil_id) 
        JOIN (SELECT @curRow := 0) r WHERE  m_coil.status<>'W' AND m_coil.m_bkpc_id = '{$m_bkpc_id}' ORDER BY no_lot";
    $datagrid->bind($cgx_sql, $cgx_options);
    $cgx_table = new HTML_Table($cgx_TableAttribs);
    $cgx_tableHeader = & $cgx_table->getHeader();
    $cgx_tableBody = & $cgx_table->getBody();
    //$datagrid->addColumn(new Structures_DataGrid_Column('Baris', 'line', NULL, array('align' => 'center'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Nomor Coil', 'no_coil', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Kode Coil', 'no_lot', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Berat (Kg)', 'weight', NULL, array('align' => 'right'), NULL, "cgx_format_3digit"));
    $datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, "grid_chk_form"));
    $datagrid->fill($cgx_table, $cgx_RendererOptions);
    $cgx_tableHeader->setRowAttributes(0, $cgx_HeaderAttribs);
    $cgx_tableBody->altRowAttributes(0, $cgx_EvenRowAttribs, $cgx_OddRowAttribs, TRUE);

    $html  = "<div class='datagrid_background'>\n";
    $html .= $cgx_table->toHtml();
    $html .= "</div>\n";
    $res = new xajaxResponse();
    $res->assign('area-lines-dua', 'innerHTML', $html);
    return $res;
}

$xajax->register(XAJAX_FUNCTION, 'mCoilLinesForm');
$xajax->register(XAJAX_FUNCTION, 'showLines');
$xajax->register(XAJAX_FUNCTION, 'showLinesDua');
$xajax->register(XAJAX_FUNCTION, 'editForm');
$xajax->register(XAJAX_FUNCTION, 'editFormDua');
$xajax->register(XAJAX_FUNCTION, 'updateLine');
$xajax->register(XAJAX_FUNCTION, 'updateLineDua');
$xajax->register(XAJAX_FUNCTION, 'deleteLine');
$xajax->register(XAJAX_FUNCTION, 'deleteLineDua');
$xajax->register(XAJAX_FUNCTION, 'saveWO');
$xajax->register(XAJAX_FUNCTION, 'updateWO');
$xajax->register(XAJAX_FUNCTION, 'grid_chk_form');

?>