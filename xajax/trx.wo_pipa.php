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

function ctl_edit_coil($data) {
    $href = "xajax_editFormDua('{$data['record']['line']}');";
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

function showLines($m_wo_pipa_id, $mode = NULL) {
    global $APP_CONNECTION, $APP_ID, $cgx_TableAttribs, $cgx_HeaderAttribs,
        $cgx_EvenRowAttribs, $cgx_OddRowAttribs, $cgx_RendererOptions;
    
    $data = $_SESSION[$APP_ID]['wo_pipa'];
    if ($data['m_wo_pipa_id'] != $m_wo_pipa_id) return;
    
    if (is_array($data['lines'])) {
        $n = 0;
        foreach ($data['lines'] as $k => $d) {
            $n++;
            $data['lines'][$k]['line'] = $n;
        }
    }
    $_SESSION[$APP_ID]['wo_pipa'] = $data;

    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';
    
    $datagrid = new Structures_DataGrid(9999);
    $datagrid->bind($data['lines'], array(), 'Array');
    $cgx_table = new HTML_Table($cgx_TableAttribs);
    $cgx_tableHeader = & $cgx_table->getHeader();
    $cgx_tableBody = & $cgx_table->getBody();
    
    $datagrid->addColumn(new Structures_DataGrid_Column('Baris', 'line', NULL, array('align' => 'center'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Customer', 'partner_name', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Item Number', 'product_code', NULL, array('align' => 'left'), NULL, NULL));
    // $datagrid->addColumn(new Structures_DataGrid_Column('Spec', 'spec', NULL, array('align' => 'left'), NULL, NULL));
    // $datagrid->addColumn(new Structures_DataGrid_Column('OD', 'od', NULL, array('align' => 'right'), NULL, NULL));
    // $datagrid->addColumn(new Structures_DataGrid_Column('Thickness', 'thickness', NULL, array('align' => 'right'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Length', 'length', NULL, array('align' => 'right'), NULL, NULL));    
    $datagrid->addColumn(new Structures_DataGrid_Column('Jumlah (btg)', 'order_quantity', NULL, array('align' => 'right', 'width' => '10%'), NULL, "cgx_format_3digit"));
    $datagrid->addColumn(new Structures_DataGrid_Column('No. Product Spec', 'customer_code', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Keterangan', 'keterangan', NULL, array('align' => 'left'), NULL, NULL));
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

function showLinesDua($m_wo_pipa_id, $mode = NULL) {
    global $APP_CONNECTION, $APP_ID, $cgx_TableAttribs, $cgx_HeaderAttribs,
        $cgx_EvenRowAttribs, $cgx_OddRowAttribs, $cgx_RendererOptions;
    
    $data = $_SESSION[$APP_ID]['wo_pipa'];
    if ($data['m_wo_pipa_id'] != $m_wo_pipa_id) return;
    
    if (is_array($data['linesdua'])) {
        $n = 0;
        foreach ($data['linesdua'] as $k => $d) {
            $n++;
            $data['linesdua'][$k]['line'] = $n;
        }
    }
    $_SESSION[$APP_ID]['wo_pipa'] = $data;

    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';
    
    $datagrid = new Structures_DataGrid(9999);
    $datagrid->bind($data['linesdua'], array(), 'Array');
    $cgx_table = new HTML_Table($cgx_TableAttribs);
    $cgx_tableHeader = & $cgx_table->getHeader();
    $cgx_tableBody = & $cgx_table->getBody();
    
    $datagrid->addColumn(new Structures_DataGrid_Column('Baris', 'line', NULL, array('align' => 'center'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('No. Coil', 'no_coil', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Kode Coil', 'no_lot', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Jumlah Coil', 'quantity', NULL, array('align' => 'right'), NULL, "cgx_format_3digit"));
    $datagrid->addColumn(new Structures_DataGrid_Column('Berat per Coil', 'weight', NULL, array('align' => 'right'), NULL, "cgx_format_money"));
    $datagrid->addColumn(new Structures_DataGrid_Column('Berat Total', 'weight_total', NULL, array('align' => 'right'), NULL, "cgx_format_money"));
    if ($mode == 'edit') $datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '20'), NULL, 'ctl_edit_coil()'));
    if ($mode == 'edit') $datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '20'), NULL, 'ctl_delete_coil()'));

    $datagrid->fill($cgx_table, $cgx_RendererOptions);
    $cgx_tableHeader->setRowAttributes(0, $cgx_HeaderAttribs);
    $cgx_tableBody->altRowAttributes(0, $cgx_EvenRowAttribs, $cgx_OddRowAttribs, TRUE);

    $html  = "<div class='datagrid_background'>\n";
    $html .= $cgx_table->toHtml();
    $html .= "</div>\n";
    $res = new xajaxResponse();
    $nilai = 0;
    $pcs = 0;
    foreach ($data['linesdua'] as $linedua) {
        $nilai += ($linedua['weight'] * $linedua['quantity']);
        $pcs += $linedua['quantity'];
    }
    $res->script("document.getElementById('weight_raw').value=$nilai");
    $res->script("document.getElementById('quantity').value=$pcs");
    $res->assign('area-lines-dua', 'innerHTML', $html);
    return $res;
}

function editForm($line_no) {
    global $APP_CONNECTION, $APP_ID, $APP_DATE_FORMAT, $APP_DATE_FORMAT_JAVA;
    global $mandatory;
    
    foreach ($_SESSION[$APP_ID]['wo_pipa']['lines'] as $line) {
        if ($line['line'] == $line_no) {
            $data = $line;
            break;
        }
    }
    
    $html .= "<form id='frmLine'>";
    $html .= "<input type='hidden' name='line' value='{$data['line']}'>";
    $html .= "<input type='hidden' name='m_wo_pipa_line_id' value='{$data['m_wo_pipa_line_id']}'>";
    $html .= "<input type='hidden' id='m_product_id' name='m_product_id' value='{$data['m_product_id']}'>";
    $html .= "<input type='hidden' id='c_bpartner_id' name='c_bpartner_id' value='{$data['c_bpartner_id']}'>";
    $html .= "<input type='hidden' id='id' name='id' value='{$data['id']}'>";
    $html .= "<table width='100%'>";
    $html .= "<tr>";
    $html .= "<td width='12%'>Item Number {$mandatory}</td>";
    $html .= "<td width='30%'><input type='text' size='20' id='product_code' value=\"{$data['product_code']}\" readonly='readonly'><img onclick=\"popupReferenceAmbil('hasil-pipa','&p1=' + document.getElementById('od').value + '&p2=' + document.getElementById('thickness').value);\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'></td>"; 
    $html .= "<td width='6%'></td>";
    $html .= "<td width='15%'>Jumlah (btg) {$mandatory}</td>";
    $html .= "<td width='37%'><input type='text' size='10' name='order_quantity' value=\"{$data['order_quantity']}\" style='text-align: right;'></td>";  
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Length</td>";
    $html .= "<td><input type='text' size='10' id='length' value=\"{$data['length']}\" readonly='readonly' style='text-align: right;'></td>";
    $html .= "<td></td>";
    $html .= "<td>No. Product Spec</td>";
    $html .= "<td><input readonly='readonly' id='customer_code' type='text' size='25' name='no_product' value=\"{$data['customer_code']}\" style='text-align: left;'><img onclick=\"popupForm('master.code_cust','&pkey[id]=0');\" style='cursor: pointer; margin: -2px 5px;' src='images/icon_add.png'><img onclick=\"popupReferenceAmbil('customer-code-pipe','&p1=' + document.getElementById('od').value + '&p2=' + document.getElementById('thickness').value);\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Customer</td>";
    $html .= "<td><input readonly='readonly' type='text' id='partner_name' name='partner_name' size='30' value=\"{$data['partner_name']}\"><img onclick=\"popupReference('business-partner-c');\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'></td>";
    $html .= "<td></td>";
    $html .= "<td>Keterangan</td>";
    $html .= "<td><input type='text' size='25' name='keterangan' value=\"{$data['keterangan']}\" style='text-align: left;'></td>";
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
    foreach ($_SESSION[$APP_ID]['wo_pipa']['linesdua'] as $line) {
        if ($line['line'] == $line_no) {
            $data = $line;
            break;
        }
    }
    $html .= "<form id='frmLineDua'>";
    $html .= "<input type='hidden' name='line' value='{$data['line']}'>";
    $html .= "<input type='hidden' name='m_wo_pipa_line_2_id' value='{$data['m_wo_pipa_line_2_id']}'>";
    $html .= "<input type='hidden' id='m_coil_slit_id' name='m_coil_slit_id' value='{$data['m_coil_slit_id']}'>";
    $html .= "<table width='100%'>";
    $html .= "<tr>";
    $html .= "<td width='13%'>Nomor Coil {$mandatory}</td>";
    $html .= "<td width='32%'><input type='text' size='30' id='no_coil' value=\"{$data['no_coil']}\" style='text-align: left;' readonly='readonly'><img onclick=\"popupReferenceAmbil('m_coil_slit','&p1=' + document.getElementById('od').value + '&p2=' + document.getElementById('thickness').value);\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'></td>";
    $html .= "<td width='10%'></td>";
    $html .= "<td width='13%'>Berat per Coil (Kg) {$mandatory}</td>";
    $html .= "<td width='32%'><input type='text' size='10' id='weight' name='weight' value=\"{$data['weight']}\" style='text-align: right;'></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Kode Coil</td>";
    $html .= "<td><input type='text' size='30' id='no_lot' value=\"{$data['no_lot']}\" style='text-align: left;' readonly='readonly'></td>";    
    $html .= "<td></td>";
    $html .= "<td>Jumlah Coil {$mandatory}</td>";
    $html .= "<td><input type='text' size='10' name='quantity' value=\"{$data['quantity']}\" style='text-align: right;'></td>";
    $html .= "</tr>";
    $html .= "<tr>";
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
    $wo_pipa = $_SESSION[$APP_ID]['wo_pipa'];
    foreach ($wo_pipa['lines'] as $k => $line) {
        if ($line['line'] == $line_no) {
            $del = $k;
            if ($line['m_wo_pipa_line_id']) {
                if ($wo_pipa['delete']) {
                    $wo_pipa['delete'][] = $line['m_wo_pipa_line_id'];
                } else {
                    $wo_pipa['delete'] = array($line['m_wo_pipa_line_id']);
                }
            }
        }
    }
    unset($wo_pipa['lines'][$del]);
    $_SESSION[$APP_ID]['wo_pipa'] = $wo_pipa;

    $res = new xajaxResponse();
    $res->script("xajax_showLines('{$wo_pipa['m_wo_pipa_id']}', 'edit');");
    $res->assign('area-edit', 'style.display', 'none');
    $res->assign('master-button', 'style.display', '');
    return $res;
}

function deleteLineDua($line_no) {
    global $APP_ID, $APP_CONNECTION;
    $wo_pipa = $_SESSION[$APP_ID]['wo_pipa'];
    foreach ($wo_pipa['linesdua'] as $k => $line) {
        if ($line['line'] == $line_no) {
            $del = $k;
            if ($line['m_wo_pipa_line_2_id']) {
                if ($wo_pipa['deletedua']) {
                    $wo_pipa['deletedua'][] = $line['m_wo_pipa_line_2_id'];
                } else {
                    $wo_pipa['deletedua'] = array($line['m_wo_pipa_line_2_id']);
                }
            }
        }
    }
    unset($wo_pipa['linesdua'][$del]);
    $_SESSION[$APP_ID]['wo_pipa'] = $wo_pipa;
    $res = new xajaxResponse();
    $res->script("xajax_showLinesDua('{$wo_pipa['m_wo_pipa_id']}', 'edit');");
    $res->assign('area-edit', 'style.display', 'none');
    $res->assign('master-button', 'style.display', '');
    return $res;
}

function saveWO($data) {
    global $APP_ID, $APP_CONNECTION;
    
    $wo_pipa = $_SESSION[$APP_ID]['wo_pipa'];
    $res = new xajaxResponse();
    
    if (empty($data['od'])) {
        $error = "Size OD long pipe tidak boleh kosong";
    } elseif (empty($data['thickness'])) {
        $error = "Size Thickness long pipe tidak boleh kosong";
    } elseif (cgx_emptydate($data['order_date'])) {
        $error = "Tanggal WO tidak boleh kosong";
    } elseif (count($wo_pipa['lines']) == 0) {
        $error = "Detail item pipa tidak boleh kosong, harus ada minimal satu baris.";
    } elseif (count($wo_pipa['linesdua']) == 0) {
        $error = "Detail coil tidak boleh kosong, harus ada minimal satu baris.";
    }
    if ($error) {
        $res->alert($error);
        return $res;
    }
    
    if ($data['m_wo_pipa_id']) {
        $sql = "UPDATE m_wo_pipa SET document_no = '{$data['document_no']}', order_date = '" . cgx_dmy2ymd($data['order_date']) . "', no_bon = '{$data['no_bon']}', spec = '{$data['spec']}', 
            od = '{$data['od']}', thickness = '{$data['thickness']}', yield = '{$data['yield']}', update_date = NOW(), update_user = '" . user() . "' WHERE m_wo_pipa_id = '{$data['m_wo_pipa_id']}'";
    } else {
        require_once 'lib/class.Penomoran.php';
        $nomor = new Penomoran();
        if (empty($data['document_no'])) {
            $document_no = $nomor->urut('WO', org());
        } else{
            $document_no = $data['document_no'];
        }
        $sql = "INSERT INTO m_wo_pipa (app_org_id, document_no, order_date, no_bon, spec, od, thickness, yield, create_user, create_date )
        VALUES ('" . org() . "', '{$document_no}', '" . cgx_dmy2ymd($data['order_date']) . "', '{$data['no_bon']}', '{$data['spec']}', '{$data['od']}', '{$data['thickness']}', '{$data['yield']}', '". user()  ."', NOW())";
    }
    $rsx = mysql_query($sql, $APP_CONNECTION);
    if (!$rsx) {
        $error = mysql_error($APP_CONNECTION);
        $res->alert($error);
        return $res;
    }
    if ($data['m_wo_pipa_id']) {
        $wo_pipa_id = $data['m_wo_pipa_id'];
    } else {
        $wo_pipa_id = mysql_insert_id($APP_CONNECTION);
    }
    
    foreach ($wo_pipa['lines'] as $line) {
        if ($line['m_wo_pipa_line_id']) {
            $sql = "UPDATE m_wo_pipa_line SET m_product_id = '{$line['m_product_id']}', c_bpartner_id = '{$line['c_bpartner_id']}', order_quantity = '{$line['order_quantity']}', 
            no_product = '{$line['id']}', keterangan = '{$line['keterangan']}' WHERE m_wo_pipa_line_id = '{$line['m_wo_pipa_line_id']}'";
        } else {  
            $sql = "INSERT INTO m_wo_pipa_line (m_wo_pipa_id, m_product_id, c_bpartner_id, order_quantity, no_product, keterangan) VALUES(
                '{$wo_pipa_id}', '{$line['m_product_id']}', '{$line['c_bpartner_id']}', '{$line['order_quantity']}', '{$line['id']}', '{$line['keterangan']}')";
        }
        $rsx = mysql_query($sql, $APP_CONNECTION);
    }

    foreach ($wo_pipa['linesdua'] as $linedua) {
        if ($linedua['m_wo_pipa_line_2_id']) {
            $awal = cgx_fetch_table("SELECT * FROM m_wo_pipa_line_2 WHERE m_wo_pipa_line_2_id = '{$linedua['m_wo_pipa_line_2_id']}'");
            mysql_query("UPDATE m_wo_pipa_line_2 SET m_coil_slit_id = '{$linedua['m_coil_slit_id']}', quantity = '{$linedua['quantity']}', 
                        weight = '{$linedua['weight']}' WHERE m_wo_pipa_line_2_id = '{$linedua['m_wo_pipa_line_2_id']}'", $APP_CONNECTION);
            $qty = $linedua['quantity'] - $awal['quantity'];
            mysql_query("UPDATE m_coil_slit SET wo_qty = wo_qty + {$qty} WHERE m_coil_slit_id = '{$linedua['m_coil_slit_id']}'", $APP_CONNECTION);          
        } else{
            mysql_query("INSERT INTO m_wo_pipa_line_2 (m_wo_pipa_id, m_coil_slit_id, quantity, weight) VALUES(
                        '{$wo_pipa_id}', '{$linedua['m_coil_slit_id']}', '{$linedua['quantity']}', '{$linedua['weight']}')", $APP_CONNECTION);
            mysql_query("UPDATE m_coil_slit SET wo_qty = wo_qty + {$linedua['quantity']} WHERE m_coil_slit_id = '{$linedua['m_coil_slit_id']}'", $APP_CONNECTION);
        }
    }
    
    if (is_array($wo_pipa['delete'])) {
        foreach ($wo_pipa['delete'] as $d) {
            mysql_query("DELETE FROM m_wo_pipa_line WHERE m_wo_pipa_line_id = '{$d}'", $APP_CONNECTION);
        }
    }

    if (is_array($wo_pipa['deletedua'])) {
        foreach ($wo_pipa['deletedua'] as $d) {
            $awal = cgx_fetch_table("SELECT * FROM m_wo_pipa_line_2 WHERE m_wo_pipa_line_2_id = '{$d}'");
            mysql_query("DELETE FROM m_wo_pipa_line_2 WHERE m_wo_pipa_line_2_id = '{$d}'", $APP_CONNECTION);
            mysql_query("UPDATE m_coil_slit SET wo_qty = wo_qty - {$awal['quantity']} WHERE m_coil_slit_id = '{$awal['m_coil_slit_id']}'", $APP_CONNECTION);
        }
    }
    
    $_SESSION[$APP_ID]['trx.wo_pipa']['info'] = "Dokumen sudah berhasil disimpan";
    
    $res->script("window.location = 'module.php?m=trx.wo_pipa&pkey[m_wo_pipa_id]={$wo_pipa_id}';");
    return $res;
}

function updateLine($data) {
    global $APP_ID;
    
    $wo_pipa = $_SESSION[$APP_ID]['wo_pipa'];
    $res = new xajaxResponse();
    /*
    if ((int) $data['order_quantity'] <= 0 ) {
        $error = "Quantity tidak boleh kosong";
   } else
    */
    if (empty($data['m_product_id'])) {
        $error = "Produk pipa tidak boleh kosong";
    }

    if ($error) {
        $res->alert($error);
        return $res;
    }
    
    if (is_array($wo_pipa['lines'])) {
        foreach ($wo_pipa['lines'] as $k => $d) {
            if ($d['line'] == $data['line']) {
                $wo_pipa['lines'][$k]['order_quantity'] = $data['order_quantity'];
                $wo_pipa['lines'][$k]['m_product_id'] = $data['m_product_id'];
                $wo_pipa['lines'][$k]['c_bpartner_id'] = $data['c_bpartner_id'];
                $wo_pipa['lines'][$k]['no_product'] = $data['no_product'];
                $wo_pipa['lines'][$k]['keterangan'] = $data['keterangan'];
                $wo_pipa['lines'][$k]['id'] = $data['id'];
                $product = cgx_fetch_table("SELECT product_code, length FROM m_product WHERE m_product_id = '{$data['m_product_id']}'");
                if (is_array($product)) foreach ($product as $pk => $pv) if (!is_numeric($pk)) $wo_pipa['lines'][$k][$pk] = $pv;                
                $customer = cgx_fetch_table("SELECT partner_name FROM c_bpartner WHERE c_bpartner_id = '{$data['c_bpartner_id']}'");
                if (is_array($customer)) foreach ($customer as $pk => $pv) if (!is_numeric($pk)) $wo_pipa['lines'][$k][$pk] = $pv;
                $customer_code = cgx_fetch_table("SELECT customer_code FROM m_code_prod_lp WHERE id = '{$data['id']}'");
                if (is_array($customer_code)) foreach ($customer_code as $pk => $pv) if (!is_numeric($pk)) $wo_pipa['lines'][$k][$pk] = $pv;
                $line_updated = TRUE;
                break;
            }
        }
    }
    if (!$line_updated) {
        $new_line['order_quantity'] = $data['order_quantity'];
        $new_line['m_product_id'] = $data['m_product_id'];
        $new_line['c_bpartner_id'] = $data['c_bpartner_id'];
        $new_line['no_product'] = $data['no_product'];
        $new_line['keterangan'] = $data['keterangan'];
        $new_line['id'] = $data['id'];
        $product = cgx_fetch_table("SELECT product_code, length FROM m_product WHERE m_product_id = '{$data['m_product_id']}'");
        if (is_array($product)) foreach ($product as $pk => $pv) if (!is_numeric($pk)) $new_line[$pk] = $pv;
        $customer = cgx_fetch_table("SELECT partner_name FROM c_bpartner WHERE c_bpartner_id = '{$data['c_bpartner_id']}'");
        if (is_array($customer)) foreach ($customer as $pk => $pv) if (!is_numeric($pk)) $new_line[$pk] = $pv;
        $customer_code = cgx_fetch_table("SELECT customer_code FROM m_code_prod_lp WHERE id = '{$data['id']}'");
        if (is_array($customer_code)) foreach ($customer_code as $pk => $pv) if (!is_numeric($pk)) $new_line[$pk] = $pv;        
        $wo_pipa['lines'][] = $new_line;
    }
    $_SESSION[$APP_ID]['wo_pipa'] = $wo_pipa;
    
    $res->script("xajax_showLines('{$wo_pipa['m_wo_pipa_id']}', 'edit');");
    $res->assign('area-edit', 'style.display', 'none');
    $res->assign('master-button', 'style.display', '');
    return $res;
}

function updateLineDua($data) {
    global $APP_ID;
    $wo_pipa = $_SESSION[$APP_ID]['wo_pipa'];
    $res = new xajaxResponse();
    /*
    if ((int) $data['quantity'] <= 0 ) {
        $error = "Quantity tidak boleh kosong";
    } else
    */
    if (empty($data['m_coil_slit_id'])) {
        $error = "No. Coil tidak boleh kosong";
    }

    if ($error) {
        $res->alert($error);
        return $res;
    }
    if (is_array($wo_pipa['linesdua'])) {
        foreach ($wo_pipa['linesdua'] as $k => $d) {
            if ($d['line'] == $data['line']) {
                $wo_pipa['linesdua'][$k]['m_coil_slit_id'] = $data['m_coil_slit_id'];
                $wo_pipa['linesdua'][$k]['quantity'] = $data['quantity'];
                $wo_pipa['linesdua'][$k]['weight'] = $data['weight'];
                $coil = cgx_fetch_table("SELECT no_coil, no_lot, ('{$data['weight']}' * '{$data['quantity']}') as weight_total FROM m_coil_slit JOIN m_coil USING(m_coil_id) WHERE m_coil_slit_id = '{$data['m_coil_slit_id']}'");
                if (is_array($coil)) foreach ($coil as $pk => $pv) if (!is_numeric($pk)) $wo_pipa['linesdua'][$k][$pk] = $pv;
                $line_updated = TRUE;
                break;
            }
        }
    }
    if (!$line_updated) {
        $new_line['m_coil_slit_id'] = $data['m_coil_slit_id'];
        $new_line['quantity'] = $data['quantity'];
        $new_line['weight'] = $data['weight'];
        $coil = cgx_fetch_table("SELECT no_coil, no_lot, ('{$data['weight']}' * '{$data['quantity']}') as weight_total FROM m_coil_slit JOIN m_coil USING(m_coil_id) WHERE m_coil_slit_id = '{$data['m_coil_slit_id']}'");
        if (is_array($coil)) foreach ($coil as $pk => $pv) if (!is_numeric($pk)) $new_line[$pk] = $pv;
        $wo_pipa['linesdua'][] = $new_line;
    }
    $_SESSION[$APP_ID]['wo_pipa'] = $wo_pipa;
    $res->script("xajax_showLinesDua('{$wo_pipa['m_wo_pipa_id']}', 'edit');");
    $res->assign('area-edit', 'style.display', 'none');
    $res->assign('master-button', 'style.display', '');
    return $res;
}

$xajax->register(XAJAX_FUNCTION, 'showLines');
$xajax->register(XAJAX_FUNCTION, 'showLinesDua');
$xajax->register(XAJAX_FUNCTION, 'editForm');
$xajax->register(XAJAX_FUNCTION, 'editFormDua');
$xajax->register(XAJAX_FUNCTION, 'updateLine');
$xajax->register(XAJAX_FUNCTION, 'updateLineDua');
$xajax->register(XAJAX_FUNCTION, 'deleteLine');
$xajax->register(XAJAX_FUNCTION, 'deleteLineDua');
$xajax->register(XAJAX_FUNCTION, 'saveWO');

?>