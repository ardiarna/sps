<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.orgm>
 * Nov 24, 2013 8:18:31 AM
 */

function ctl_edit_rr($data) {
    if ($data['record']['m_inout_line_id']) {
        $href = "xajax_editForm('{$data['record']['line']}');";
        $out = "<img onclick=\"{$href}\" style='cursor: pointer;' src='images/icon_edit.png' border='0'>";
    return $out;
    }
}

function ctl_delete_rr($data) {
    $href = "xajax_deleteLine('{$data['record']['line']}');";
    $out = "<img onclick=\"{$href}\" style='cursor: pointer;' src='images/icon_delete.png' border='0'>";
    return $out;
}

function showLines($m_inout_id, $mode = NULL) {
    global $APP_CONNECTION, $APP_ID, $cgx_TableAttribs, $cgx_HeaderAttribs,
        $cgx_EvenRowAttribs, $cgx_OddRowAttribs, $cgx_RendererOptions;
    
    $data = $_SESSION[$APP_ID]['bkbj'];
    if ($data['m_inout_id'] != $m_inout_id) return;
    
    if (is_array($data['lines'])) {
        $n = 0;
        foreach ($data['lines'] as $k => $d) {
            $n++;
            $data['lines'][$k]['line'] = $n;
        }
    }
    $_SESSION[$APP_ID]['bkbj'] = $data;

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
    $datagrid->addColumn(new Structures_DataGrid_Column('Gudang', 'warehouse_name', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('No Box', 'no_box', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Isi Box', 'isi_box', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Jumlah Barang', 'quantity', NULL, array('align' => 'right'), NULL, "cgx_format_3digit"));
    if ($mode == 'editH') $datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '20'), NULL, 'ctl_edit_rr()'));
    //if ($mode == 'edit') $datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, 'ctl_delete_rr()'));

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
    
    foreach ($_SESSION[$APP_ID]['bkbj']['lines'] as $line) {
        if ($line['line'] == $line_no) {
            $data = $line;
            break;
        }
    }

    $html .= "<form id='frmLine'>";
    $html .= "<input type='hidden' name='line' value='{$data['line']}'>";
    $html .= "<input type='hidden' id='m_product_id' name='m_product_id' value='{$data['m_product_id']}'>";
    $html .= "<input type='hidden' id='m_warehouse_id' name='m_warehouse_id' value='{$data['m_warehouse_id']}'>";
    $html .= "<table width='100%'>";
    $html .= "<tr>";
    $html .= "<td width='12%'>Item Number {$mandatory}</td>";
    $html .= "<td width='33%'><input id='product_code' type='text' size='20' value=\"{$data['product_code']}\" name='product_code' readonly='readonly'>". ($line_no ? "" : "<img onclick=\"popupReference('product_wh');\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'>") ."</td>";
    $html .= "<td width='10%'></td>";
    $html .= "<td width='12%'>Gudang</td>";
    $html .= "<td width='33%'><input type='text' size='30' id='warehouse_name' value=\"{$data['warehouse_name']}\" name='warehouse_name' readonly='readonly'></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Barang</td>";
    $html .= "<td><input type='text' name='product_name' id='product_name' size='30' value=\"{$data['product_name']}\" readonly='readonly'></td>";
    $html .= "<td></td>";
    $html .= "<td>No Box</td>";
    $html .= "<td><input type='text' size='42' name='no_box' value=\"{$data['no_box']}\" style='text-align: left;'></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Description</td>";
    $html .= "<td><input type='text' size='30' id='description' value=\"{$data['description']}\" name='description' readonly='readonly'></td>";
    $html .= "<td></td>";
    $html .= "<td>Isi Box</td>";
    $html .= "<td><input type='text' size='42' name='isi_box' value=\"{$data['isi_box']}\" style='text-align: left;'></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>" . ($line_no ? "" : "Stock</td>");
    $html .= "<td>" . ($line_no ? "" : "<input type='text' size='10' id='balance_quantity' value=\"{$data['balance_quantity']}\" name='balance_quantity' readonly='readonly'></td>");
    $html .= "<td></td>";
    $html .= "<td>Jumlah Barang {$mandatory}</td>";
    $html .= "<td><input type='text' size='10' name='quantity' value=\"{$data['quantity']}\" style='text-align: right;'></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td></td>";
    $html .= "<td><input type='button' value='" . ($line_no ? 'Update' : 'Tambahkan') . "' onclick=\"". ($line_no ? "xajax_updateLineEdit(xajax.getFormValues('frmLine')); \"> &nbsp;" : "xajax_updateLine(xajax.getFormValues('frmLine')); \"> &nbsp;");
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
    
    $bkbj = $_SESSION[$APP_ID]['bkbj'];
    foreach ($bkbj['lines'] as $k => $line) {
        if ($line['line'] == $line_no) {
            $del = $k;
            if ($line['m_inout_line_id']) {
                if ($bkbj['delete']) {
                    $bkbj['delete'][] = $line['m_inout_line_id'];
                } else {
                    $bkbj['delete'] = array($line['m_inout_line_id']);
                }
            }
        }
    }
    unset($bkbj['lines'][$del]);
    $_SESSION[$APP_ID]['bkbj'] = $bkbj;

    $res = new xajaxResponse();
    $res->script("xajax_showLines('{$bkbj['m_inout_id']}', 'edit');");
    $res->assign('area-edit', 'style.display', 'none');
    $res->assign('master-button', 'style.display', '');
    return $res;
}

function saveRR($data) {
    global $APP_ID, $APP_CONNECTION;
    
    $bkbj = $_SESSION[$APP_ID]['bkbj'];
    $res = new xajaxResponse();
    if (cgx_emptydate($data['m_inout_date'])) {
        $error = "Tanggal transaksi tidak boleh kosong";
    } elseif (count($bkbj['lines']) == 0) {
        $error = "Detail item barang tidak boleh kosong, harus ada minimal satu baris.";
    }
    if ($error) {
        $res->alert($error);
        return $res;
    }
    

    if ($data['m_inout_id']) {
        $sql = "UPDATE m_inout SET m_inout_date = '" . cgx_dmy2ymd($data['m_inout_date']) . "', no_kendaraan = '{$data['no_kendaraan']}', dokumen = '{$data['dokumen']}', 
            tuj_org_id = '{$data['c_bpartner_id']}', c_order_id = '{$data['c_order_id']}', update_date = NOW(), update_user = '" . user() . "' WHERE m_inout_id = '{$data['m_inout_id']}'";
    } else {
        require_once 'lib/class.Penomoran.php';
        $nomor = new Penomoran();
        $document_no = $nomor->urut('BK', org());
        $sql =
            "INSERT INTO m_inout (app_org_id, document_no, tuj_org_id, m_inout_date, m_transaction_type_id, c_order_id, no_kendaraan, dokumen, create_user)
            VALUES ('" . org() . "',  '{$document_no}', '{$data['c_bpartner_id']}', '" . cgx_dmy2ymd($data['m_inout_date']) . "', 4, '{$data['c_order_id']}', '{$data['no_kendaraan']}', '{$data['dokumen']}', '". user() ."')";
    }    
    $rsx = mysql_query($sql, $APP_CONNECTION);
    if (!$rsx) {
        $error = mysql_error($APP_CONNECTION);
        $res->alert($error);
        return $res;
    }
    
    if ($data['m_inout_id']) {
        $bkbj_id = $data['m_inout_id'];
    } else {
        $bkbj_id = mysql_insert_id($APP_CONNECTION);
    }
    foreach ($bkbj['lines'] as $line) {
        if ($line['m_inout_line_id']) {  // JIKA BKBJ EDIT
            $sql0 = "SELECT quantity FROM m_inout_line WHERE m_inout_line_id = '{$line['m_inout_line_id']}'";    
            $result0 = mysql_query($sql0, $APP_CONNECTION);
            $hasil0 = mysql_result($result0,0);
            if($hasil0){
                $qty_a = $hasil0;
            }
            $sql = "UPDATE m_inout_line SET m_warehouse_id = '{$line['m_warehouse_id']}', no_box = '{$line['no_box']}', isi_box = '{$line['isi_box']}', 
                    quantity = '{$line['quantity']}' WHERE m_inout_line_id = '{$line['m_inout_line_id']}'";
            $rsx = mysql_query($sql, $APP_CONNECTION);
            //====================================================================================== update stock on hand return
            $qty_a_min = $qty_a * -1 ;
            stock_onhand(org(), user(), $line['m_product_id'], $data['m_inout_date_a'], 0, $qty_a_min);
            //=================================================================================================== update balance
            $qty = $line['quantity'] - $qty_a;
            inout(org(),$line['m_product_id'], $line['m_warehouse_id'], 0, $qty, FALSE);
        } else {  // JIKA BKBJ BARU
            $sql = "INSERT INTO m_inout_line (m_inout_id, m_product_id, quantity, m_warehouse_id, no_box, isi_box)
                    VALUES ('{$bkbj_id}', '{$line['m_product_id']}', '{$line['quantity']}', '{$line['m_warehouse_id']}', '{$line['no_box']}', '{$line['isi_box']}')";
            $rsx = mysql_query($sql, $APP_CONNECTION);
            //=================================================================================================== update balance
            inout(org(),$line['m_product_id'], $line['m_warehouse_id'], 0, $line['quantity'], FALSE);
        }
        //======================================================================================================= update stock on hand
        stock_onhand(org(), user(), $line['m_product_id'], $data['m_inout_date'], 0, $line['quantity']);
    }
    $_SESSION[$APP_ID]['trx.bkbj']['info'] = "Dokumen sudah berhasil disimpan";
    $res->script("window.location = 'module.php?m=trx.bkbj&pkey[m_inout_id]={$bkbj_id}';");
    return $res;
}

function updateLine($data) {
    global $APP_ID;
    $bkbj = $_SESSION[$APP_ID]['bkbj'];
    $res = new xajaxResponse();

    if ((int) $data['quantity'] < 0) {
        $error = "Jumlah barang tidak boleh kosong";
    } elseif (empty($data['m_product_id'])) {
        $error = "Kode barang tidak boleh kosong";
    } elseif ((int) $data['balance_quantity'] < (int) $data['quantity']) {
        $error = "Stock Barang '{$data['product_code']}' di Gudang {$data['warehouse_name']} tidak mencukupi (tersedia = {$data['balance_quantity']}, diperlukan = {$data['quantity']})";
    }

    if ($error) {
        $res->alert($error);
        return $res;
    }

    if (is_array($bkbj['lines'])) {
        foreach ($bkbj['lines'] as $k => $d) {
            if ($d['line'] == $data['line']) {
                $bkbj['lines'][$k]['quantity'] = $data['quantity'];
                $bkbj['lines'][$k]['m_product_id'] = $data['m_product_id'];
                $bkbj['lines'][$k]['m_warehouse_id'] = $data['m_warehouse_id'];
                //$bkbj['lines'][$k]['m_work_order_line_id'] = $data['m_work_order_line_id'];
                $bkbj['lines'][$k]['work_order'] = $data['work_order'];
                $bkbj['lines'][$k]['no_box'] = $data['no_box'];
                $bkbj['lines'][$k]['isi_box'] = $data['isi_box'];
                $product = cgx_fetch_table("SELECT * FROM m_product WHERE m_product_id = '{$data['m_product_id']}'");
                if (is_array($product)) foreach ($product as $pk => $pv) if (!is_numeric($pk)) $bkbj['lines'][$k][$pk] = $pv;
                $warehouse = cgx_fetch_table("SELECT * FROM m_warehouse WHERE m_warehouse_id = '{$data['m_warehouse_id']}'");
                if (is_array($warehouse)) foreach ($warehouse as $pk => $pv) if (!is_numeric($pk)) $bkbj['lines'][$k][$pk] = $pv;
                $line_updated = TRUE;
                break;
            }
        }
    }
    if (!$line_updated) {
        $new_line['quantity'] = $data['quantity'];
        $new_line['m_product_id'] = $data['m_product_id'];
        $new_line['m_warehouse_id'] = $data['m_warehouse_id'];
        //$new_line['m_work_order_line_id'] = $data['m_work_order_line_id'];
        $new_line['work_order'] = $data['work_order'];
        $new_line['no_box'] = $data['no_box'];
        $new_line['isi_box'] = $data['isi_box'];
        $product = cgx_fetch_table("SELECT * FROM m_product WHERE m_product_id = '{$data['m_product_id']}'");
        if (is_array($product)) foreach ($product as $pk => $pv) if (!is_numeric($pk)) $new_line[$pk] = $pv;
        $warehouse = cgx_fetch_table("SELECT * FROM m_warehouse WHERE m_warehouse_id = '{$data['m_warehouse_id']}'");
        if (is_array($warehouse)) foreach ($warehouse as $pk => $pv) if (!is_numeric($pk)) $new_line[$pk] = $pv;
        $bkbj['lines'][] = $new_line;
    }
    $_SESSION[$APP_ID]['bkbj'] = $bkbj;
    
    $res->script("xajax_showLines('{$bkbj['m_inout_id']}', 'edit');");
    $res->assign('area-edit', 'style.display', 'none');
    $res->assign('master-button', 'style.display', '');
    return $res;
}

function updateLineEdit($data) {
    global $APP_ID;
    
    $bkbj = $_SESSION[$APP_ID]['bkbj'];
    $res = new xajaxResponse();

    if ((int) $data['quantity'] < 0) {
        $error = "Jumlah barang tidak boleh kosong";
    } elseif (empty($data['m_product_id'])) {
        $error = "Kode barang tidak boleh kosong";
    }

    if ($error) {
        $res->alert($error);
        return $res;
    }

    if (is_array($bkbj['lines'])) {
        foreach ($bkbj['lines'] as $k => $d) {
            if ($d['line'] == $data['line']) {
                $bkbj['lines'][$k]['quantity'] = $data['quantity'];
                $bkbj['lines'][$k]['m_product_id'] = $data['m_product_id'];
                $bkbj['lines'][$k]['m_warehouse_id'] = $data['m_warehouse_id'];
                //$bkbj['lines'][$k]['m_work_order_line_id'] = $data['m_work_order_line_id'];
                $bkbj['lines'][$k]['work_order'] = $data['work_order'];
                $bkbj['lines'][$k]['no_box'] = $data['no_box'];
                $bkbj['lines'][$k]['isi_box'] = $data['isi_box'];
                $product = cgx_fetch_table("SELECT * FROM m_product WHERE m_product_id = '{$data['m_product_id']}'");
                if (is_array($product)) foreach ($product as $pk => $pv) if (!is_numeric($pk)) $bkbj['lines'][$k][$pk] = $pv;
                $warehouse = cgx_fetch_table("SELECT * FROM m_warehouse WHERE m_warehouse_id = '{$data['m_warehouse_id']}'");
                if (is_array($warehouse)) foreach ($warehouse as $pk => $pv) if (!is_numeric($pk)) $bkbj['lines'][$k][$pk] = $pv;
                $line_updated = TRUE;
                break;
            }
        }
    }
    if (!$line_updated) {
        $new_line['quantity'] = $data['quantity'];
        $new_line['m_product_id'] = $data['m_product_id'];
        $new_line['m_warehouse_id'] = $data['m_warehouse_id'];
        //$new_line['m_work_order_line_id'] = $data['m_work_order_line_id'];
        $new_line['work_order'] = $data['work_order'];
        $new_line['no_box'] = $data['no_box'];
        $new_line['isi_box'] = $data['isi_box'];
        $product = cgx_fetch_table("SELECT * FROM m_product WHERE m_product_id = '{$data['m_product_id']}'");
        if (is_array($product)) foreach ($product as $pk => $pv) if (!is_numeric($pk)) $new_line[$pk] = $pv;
        $warehouse = cgx_fetch_table("SELECT * FROM m_warehouse WHERE m_warehouse_id = '{$data['m_warehouse_id']}'");
        if (is_array($warehouse)) foreach ($warehouse as $pk => $pv) if (!is_numeric($pk)) $new_line[$pk] = $pv;
        $bkbj['lines'][] = $new_line;
    }
    $_SESSION[$APP_ID]['bkbj'] = $bkbj;
    
    $res->script("xajax_showLines('{$bkbj['m_inout_id']}', 'edit');");
    $res->assign('area-edit', 'style.display', 'none');
    $res->assign('master-button', 'style.display', '');
    return $res;
}

$xajax->register(XAJAX_FUNCTION, 'showLines');
$xajax->register(XAJAX_FUNCTION, 'editForm');
$xajax->register(XAJAX_FUNCTION, 'updateLine');
$xajax->register(XAJAX_FUNCTION, 'deleteLine');
$xajax->register(XAJAX_FUNCTION, 'saveRR');
$xajax->register(XAJAX_FUNCTION, 'updateLineEdit');

?>