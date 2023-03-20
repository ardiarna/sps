<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.orgm>
 * Nov 24, 2013 8:18:31 AM
 */

function ctl_edit_rwop($data) {
    //if ($data['record']['m_prod_slit_line_id']) {
        $href = "xajax_editForm('{$data['record']['line']}');";
        $out = "<img onclick=\"{$href}\" style='cursor: pointer;' src='images/icon_edit.png' border='0'>";
        return $out;
    //}
}

function ctl_delete_rwop($data) {
    $href = "xajax_deleteLine('{$data['record']['line']}');";
    $out = "<img onclick=\"{$href}\" style='cursor: pointer;' src='images/icon_delete.png' border='0'>";
    return $out;
}



function editForm($line_no) {
    global $APP_CONNECTION, $APP_ID, $APP_DATE_FORMAT, $APP_DATE_FORMAT_JAVA;
    global $mandatory;
    
    foreach ($_SESSION[$APP_ID]['rwop']['lines'] as $line) {
        if ($line['line'] == $line_no) {
            $data = $line;
            break;
        }
    }

    $html .= "<form id='frmLine'>";
    $html .= "<input type='hidden' name='line' value='{$data['line']}'>";
    $html .= "<input type='hidden' id='m_wo_pipa_line_id' name='m_wo_pipa_line_id' value='{$data['m_wo_pipa_line_id']}'>";
    $html .= "<input type='hidden' id='m_product_pipa' name='m_product_pipa' value='{$data['m_product_pipa']}'>";
    //$html .= "m_product_pipa<input type='text' id='m_product_pipa' name='m_product_pipa' value='{$data['m_product_id']}'>";
    $html .= "<input type='hidden' id='m_coil_slit_id' name='m_coil_slit_id' value='{$data['m_coil_slit_id']}'>";
    $html .= "<input type='hidden' id='m_coil_id' name='m_coil_id' value='{$data['m_coil_id']}'>";
    $html .= "<input type='hidden' id='m_warehouse_id' name='m_warehouse_id' value='{$data['m_warehouse_id']}'>";
    $html .= "<table width='100%'>";
    $html .= "<tr>";
    $html .= "<td width='12%'>Item Number {$mandatory}</td>";
    //$html .= "<td width='33%'><input id='product_code_pipa' type='text' size='20' value=\"{$data['product_code_pipa']}\" readonly='readonly'>". ($line_no ? "" : "<img onclick=\"popupReferenceAmbil('work-order-pipa-detail','&p1=' + document.getElementById('m_wo_pipa_id').value);\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'>") ."</td>";
    //$html .= "<td width='33%'><input id='product_code_pipa' type='text' size='20' value=\"{$data['product_code_pipa']}\" readonly='readonly'><img onclick=\"popupReferenceAmbil('work-order-pipa-detail','&p1=' + document.getElementById('m_wo_pipa_id').value);\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'></td>";
    $html .= "<td width='33%'><input id='product_code_pipa' type='text' size='20' value=\"{$data['product_code_pipa']}\" readonly='readonly'><img onclick=\"popupReferenceAmbil('work-order-pipa-detail','&p1=' + document.getElementById('m_wo_pipa_id').value);\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'></td>";
    $html .= "<td width='10%'></td>";
    $html .= "<td width='12%'>No. Coil {$mandatory}</td>";
    $html .= "<td width='33%'><input readonly='readonly' type='text' size='25' id='no_coil' name='no_coil' value=\"{$data['no_coil']}\" style='text-align: left;'><img onclick=\"popupReferenceAmbil('work-order-pipa-nocoil','&p1=' + document.getElementById('m_wo_pipa_id').value);\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Barang</td>";
    $html .= "<td><input type='text' name='product_name_pipa' id='product_name_pipa' size='40' value=\"{$data['product_name_pipa']}\" readonly='readonly'></td>";
    $html .= "<td></td>";
    $html .= "<td>Kode Coil</td>";
    $html .= "<td><input readonly='readonly' type='text' size='25' id='no_lot' name='no_lot' value=\"{$data['no_lot']}\" style='text-align: left;'></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Description</td>";
    $html .= "<td><input type='text' size='30' id='description_pipa' value=\"{$data['description_pipa']}\" name='description_pipa' readonly='readonly'></td>";
    $html .= "<td></td>";
    $html .= "<td>Jumlah Barang {$mandatory}</td>";
    $html .= "<td><input type='text' size='10' name='good' value=\"{$data['good']}\" style='text-align: right;'></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Gudang {$mandatory}</td>";
    $html .= "<td>" . cgx_filter('m_warehouse_id', "SELECT m_warehouse_id, warehouse_name FROM m_warehouse WHERE app_org_id = '".org()."' ORDER BY warehouse_name", $data['m_warehouse_id'], FALSE, $line_no ? ' disabled' : '') . "</td>";
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
    
    $rwop = $_SESSION[$APP_ID]['rwop'];
    foreach ($rwop['lines'] as $k => $line) {
        if ($line['line'] == $line_no) {
            $del = $k;
            if ($line['m_prod_slit_line_id']) {
                if ($rwop['delete']) {
                    $rwop['delete'][] = $line['m_prod_slit_line_id'];
                } else {
                    $rwop['delete'] = array($line['m_prod_slit_line_id']);
                }
            }
        }
    }
    unset($rwop['lines'][$del]);
    $_SESSION[$APP_ID]['rwop'] = $rwop;

    $res = new xajaxResponse();
    $res->script("xajax_showLines('{$rwop['m_prod_slit_id']}', 'edit');");
    $res->assign('area-edit', 'style.display', 'none');
    $res->assign('master-button', 'style.display', '');
    return $res;
}

function saverwop($data) {
    global $APP_ID, $APP_CONNECTION;
    
    $rwop = $_SESSION[$APP_ID]['rwop'];
    $res = new xajaxResponse();
    if (empty($data['m_wo_pipa_id'])) {
        $error = "Work Order tidak boleh kosong";
    } elseif (cgx_emptydate($data['production_date'])) {
        $error = "Tanggal tidak boleh kosong";
    } elseif (count($rwop['lines']) == 0) {
        $error = "Detail item barang tidak boleh kosong, harus ada minimal satu baris.";
    }
    if ($error) {
        $res->alert($error);
        return $res;
    }
    if ($data['m_prod_slit_id']) {
        $sql = "UPDATE m_prod_slit SET production_date = '" . cgx_dmy2ymd($data['production_date']) . "', update_date = NOW(), update_user = '" . user() . "' WHERE m_prod_slit_id = '{$data['m_prod_slit_id']}'";
    } else {
        require_once 'lib/class.Penomoran.php';
        $nomor = new Penomoran();
        $document_no = $nomor->urut('PP', org());
        $sql =
            "INSERT INTO m_prod_slit (document_no, m_wo_slit_id, production_date, production_type, create_user, create_date)
        VALUES ('{$document_no}', '{$data['m_wo_pipa_id']}', '" . cgx_dmy2ymd($data['production_date']) . "', 2, '". user() ."', NOW())";
    }    
    $rsx = mysql_query($sql, $APP_CONNECTION);
    if (!$rsx) {
        $error = mysql_error($APP_CONNECTION);
        $res->alert($error);
        return $res;
    }
    if ($data['m_prod_slit_id']) {
        $rwop_id = $data['m_prod_slit_id'];
    } else {
        $rwop_id = mysql_insert_id($APP_CONNECTION);
    }
    foreach ($rwop['lines'] as $line) {
        if ($line['m_prod_slit_line_id']) {  // JIKA EDIT
            $awal = cgx_fetch_table("SELECT * FROM m_prod_slit_line WHERE m_prod_slit_line_id = '{$line['m_prod_slit_line_id']}'");
            $sql = "UPDATE m_prod_slit_line SET m_warehouse_id = '{$line['m_warehouse_id']}', m_coil_id = '{$line['m_coil_id']}', 
                good = '{$line['good']}' WHERE m_prod_slit_line_id = '{$line['m_prod_slit_line_id']}'";
            $rsx = mysql_query($sql, $APP_CONNECTION);
            //====================================================================================== update stock on hand return pipa
            $qty_a_min = $awal['good'] * -1 ;
            stock_onhand(org(), user(), $line['m_product_pipa'], $data['production_date_a'], $qty_a_min, 0);
            //=================================================================================================== update balance pipa (+-)
            $qty = $line['good'] - $awal['good'];
            inout(org(),$line['m_product_pipa'], $line['m_warehouse_id'], $qty);
        } else {  // JIKA BARU
            $sql = "INSERT INTO m_prod_slit_line (m_prod_slit_id, m_wo_slit_line_id, good, m_warehouse_id, m_coil_id)
                    VALUES ('{$rwop_id}', '{$line['m_wo_pipa_line_id']}', '{$line['good']}', '{$line['m_warehouse_id']}', '{$line['m_coil_id']}')";
            $rsx = mysql_query($sql, $APP_CONNECTION);
            //=================================================================================================== update balance pipa (+)
            inout(org(),$line['m_product_pipa'], $line['m_warehouse_id'], $line['good']);
            //=================================================================================================== update stock balance coil slit (-)
            $awal_coil_slit = cgx_fetch_table("SELECT * FROM m_coil_slit WHERE m_coil_slit_id = '{$line['m_coil_slit_id']}'");
            if ($awal_coil_slit['out_qty'] == 0){
                mysql_query("UPDATE m_coil_slit SET out_qty = '1' WHERE m_coil_slit_id = '{$line['m_coil_slit_id']}'", $APP_CONNECTION);
                $total_weight = $awal_coil_slit['quantity'] * $awal_coil_slit['weight'];
                stock_onhand(org(), user(), $awal_coil_slit['m_product_id'], $data['production_date'], 0, $awal_coil_slit['quantity']);
                inout(org(), $awal_coil_slit['m_product_id'], 283, 0, $awal_coil_slit['quantity'], FALSE);
                stock_weight(org(), user(), $awal_coil_slit['m_product_id'], $data['production_date'], 0, $total_weight);
            }
        }
        //====================================================================================== update stock on hand pipa (+)
        stock_onhand(org(), user(), $line['m_product_pipa'], $data['production_date'], $line['good'], 0);
    }   
    $_SESSION[$APP_ID]['trx.rwop']['info'] = "Dokumen sudah berhasil disimpan";
    $res->script("window.location = 'module.php?m=trx.rwop&pkey[m_prod_slit_id]={$rwop_id}';");
    return $res;
}

function showLines($m_prod_slit_id, $mode = NULL) {
    global $APP_CONNECTION, $APP_ID, $cgx_TableAttribs, $cgx_HeaderAttribs,
        $cgx_EvenRowAttribs, $cgx_OddRowAttribs, $cgx_RendererOptions;
    
    $data = $_SESSION[$APP_ID]['rwop'];
    if ($data['m_prod_slit_id'] != $m_prod_slit_id) return;
    
    if (is_array($data['lines'])) {
        $n = 0;
        foreach ($data['lines'] as $k => $d) {
            $n++;
            $data['lines'][$k]['line'] = $n;
        }
    }
    $_SESSION[$APP_ID]['rwop'] = $data;

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
    $datagrid->addColumn(new Structures_DataGrid_Column('No. Coil', 'no_coil', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Kode Coil', 'no_lot', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Jumlah', 'good', NULL, array('align' => 'right'), NULL, "cgx_format_3digit"));
    //if ($mode == 'edit') $datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '20'), NULL, 'ctl_edit_rwop()'));
    if ($mode == 'edit') $datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, 'ctl_delete_rwop()'));

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

function updateLine($data) {
    global $APP_ID;
    
    $rwop = $_SESSION[$APP_ID]['rwop'];
    $res = new xajaxResponse();
    if (empty($data['good']) OR (int) $data['good'] < 0) {
        $error = "Jumlah barang tidak boleh kosong";
    } elseif (empty($data['m_wo_pipa_line_id'])) {
        $error = "error, line WO";
    } elseif (empty($data['m_product_pipa'])) {
        $error = "Kode barang tidak boleh kosong";
    } elseif (empty($data['m_coil_id'])) {
        $error = "Nomor Coil tidak boleh kosong";
    }

    if ($error) {
        $res->alert($error);
        return $res;
    }
    
    if (is_array($rwop['lines'])) {
        //$res->alert("1");
        foreach ($rwop['lines'] as $k => $d) {
            if ($d['line'] == $data['line']) {
                $rwop['lines'][$k]['good'] = $data['good'];
                $rwop['lines'][$k]['m_wo_pipa_line_id'] = $data['m_wo_pipa_line_id'];
                $rwop['lines'][$k]['m_product_pipa'] = $data['m_product_pipa'];
                $rwop['lines'][$k]['m_warehouse_id'] = $data['m_warehouse_id'];
                $rwop['lines'][$k]['m_coil_id'] = $data['m_coil_id'];
                $rwop['lines'][$k]['m_coil_slit_id'] = $data['m_coil_slit_id'];
                $rwop['lines'][$k]['no_coil'] = $data['no_coil'];
                $rwop['lines'][$k]['no_lot'] = $data['no_lot'];
                $product = cgx_fetch_table("SELECT * FROM m_product WHERE m_product_id = '{$data['m_product_pipa']}'");
                if (is_array($product)) foreach ($product as $pk => $pv) if (!is_numeric($pk)) $rwop['lines'][$k][$pk] = $pv;
                $warehouse = cgx_fetch_table("SELECT * FROM m_warehouse WHERE m_warehouse_id = '{$data['m_warehouse_id']}'");
                if (is_array($warehouse)) foreach ($warehouse as $pk => $pv) if (!is_numeric($pk)) $rwop['lines'][$k][$pk] = $pv;
                $line_updated = TRUE;
                break;
            }
        }
    }
    if (!$line_updated) {
        //$res->alert("2");
        $new_line['good'] = $data['good'];
        $new_line['m_wo_pipa_line_id'] = $data['m_wo_pipa_line_id'];
        $new_line['m_product_pipa'] = $data['m_product_pipa'];
        $new_line['m_warehouse_id'] = $data['m_warehouse_id'];
        $new_line['m_coil_id'] = $data['m_coil_id'];
        $new_line['m_coil_slit_id'] = $data['m_coil_slit_id'];
        $new_line['no_coil'] = $data['no_coil'];
        $new_line['no_lot'] = $data['no_lot'];
        $product = cgx_fetch_table("SELECT * FROM m_product WHERE m_product_id = '{$data['m_product_pipa']}'");
        if (is_array($product)) foreach ($product as $pk => $pv) if (!is_numeric($pk)) $new_line[$pk] = $pv;
        $warehouse = cgx_fetch_table("SELECT * FROM m_warehouse WHERE m_warehouse_id = '{$data['m_warehouse_id']}'");
        if (is_array($warehouse)) foreach ($warehouse as $pk => $pv) if (!is_numeric($pk)) $new_line[$pk] = $pv;
        $rwop['lines'][] = $new_line;
    }
    $_SESSION[$APP_ID]['rwop'] = $rwop;
    
    $res->script("xajax_showLines('{$rwop['m_prod_slit_id']}', 'edit');");
    $res->assign('area-edit', 'style.display', 'none');
    $res->assign('master-button', 'style.display', '');
    return $res;
}

$xajax->register(XAJAX_FUNCTION, 'showLines');
$xajax->register(XAJAX_FUNCTION, 'editForm');
$xajax->register(XAJAX_FUNCTION, 'updateLine');
$xajax->register(XAJAX_FUNCTION, 'deleteLine');
$xajax->register(XAJAX_FUNCTION, 'saverwop');

?>