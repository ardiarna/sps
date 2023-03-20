<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.org>
 * Nov 24, 2013 8:18:31 AM
 */

function ctl_edit_bkpc($data) {
    $href = "xajax_editForm('{$data['record']['line']}');";
    $out = "<img onclick=\"{$href}\" style='cursor: pointer;' title='Edit' src='images/icon_edit.png' border='0'>";
    return $out;
}

function ctl_delete_bkpc($data) {    
    $href = "xajax_deleteLine('{$data['record']['line']}');";
    $out = "<img onclick=\"{$href}\" style='cursor: pointer;' title='Hapus' src='images/icon_delete.png' border='0'>";
    return $out;
}

function showLines($m_bkpc_id, $mode = NULL) {
    global $APP_CONNECTION, $APP_ID, $cgx_TableAttribs, $cgx_HeaderAttribs,
        $cgx_EvenRowAttribs, $cgx_OddRowAttribs, $cgx_RendererOptions;
    
    $data = $_SESSION[$APP_ID]['bkpc'];
    if ($data['m_bkpc_id'] != $m_bkpc_id) return;
    
    if (is_array($data['lines'])) {
        $n = 0;
        foreach ($data['lines'] as $k => $d) {
            $n++;
            $data['lines'][$k]['line'] = $n;
        }
    }
    $_SESSION[$APP_ID]['bkpc'] = $data;

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
    $datagrid->addColumn(new Structures_DataGrid_Column('Ukuran', 'ukuran_mat', NULL, array('align' => 'right'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('No. Coil', 'no_coil', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Kode Coil', 'no_lot', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Berat (Kg)', 'weight', NULL, array('align' => 'right'), NULL, "cgx_format_money"));
    $datagrid->addColumn(new Structures_DataGrid_Column('Keterangan', 'keterangan', NULL, array('align' => 'left'), NULL, NULL));
    if ($mode == 'edit')  $datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '20'), NULL, 'ctl_edit_bkpc()'));
    //if ($mode == 'edit') $datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '20'), NULL, 'ctl_delete_bkpc()'));

    $datagrid->fill($cgx_table, $cgx_RendererOptions);
    $cgx_tableHeader->setRowAttributes(0, $cgx_HeaderAttribs);
    $cgx_tableBody->altRowAttributes(0, $cgx_EvenRowAttribs, $cgx_OddRowAttribs, TRUE);

    $html  = "<div class='datagrid_background'>\n";
    $html .= $cgx_table->toHtml();
    $html .= "</div>\n";
    $res = new xajaxResponse();
        $nilai = 0 ;
    $qty = 0;
    foreach ($data['lines'] as $line) {
        $nilai += $line['weight'];
        $qty += 1;
    }
    $res->script("document.getElementById('quantity').value=$qty");
    $res->script("document.getElementById('weight_raw').value=$nilai");
    $res->assign('area-lines', 'innerHTML', $html);
    return $res;
}

function editForm($line_no) {
    global $APP_CONNECTION, $APP_ID, $APP_DATE_FORMAT, $APP_DATE_FORMAT_JAVA;
    global $mandatory;
    
    foreach ($_SESSION[$APP_ID]['bkpc']['lines'] as $line) {
        if ($line['line'] == $line_no) {
            $data = $line;
            break;
        }
    }
    
    $html .= "<form id='frmLine'>";
    $html .= "<input type='hidden' name='line' value='{$data['line']}'>";
    $html .= "<input type='hidden' id='m_coil_id' name='m_coil_id' value='{$data['m_coil_id']}'>";
    $html .= "<table width='100%'>";
    $html .= "<tr>";
    $html .= "<td width='12%'>Nomor Coil {$mandatory}</td>";
    $html .= "<td width='32%'><input type='text' size='26' id='no_coil' value=\"{$data['no_coil']}\" style='text-align: left;' readonly='readonly'><img onclick=\"popupReferenceAmbil('m_coil','&p1=' + document.getElementById('m_product_id_head').value);\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'></td>";
    $html .= "<td width='12%'></td>";
    $html .= "<td width='13%'>Berat per Coil (Kg) {$mandatory}</td>";
    $html .= "<td width='32%'><input type='text' size='10' id='weight' name='weight' value=\"{$data['weight']}\" style='text-align: right;'></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Kode Coil</td>";
    $html .= "<td><input type='text' size='30' id='no_lot' value=\"{$data['no_lot']}\" style='text-align: left;' readonly='readonly'></td>";    
    $html .= "<td></td>";
    $html .= "<td>Keterangan</td>";
    $html .= "<td><input type='text' size='20' name='keterangan' value=\"{$data['keterangan']}\" style='text-align: left;'></td>";
    $html .= "</tr>";
    $html .= "<tr>";
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
    
    return $res;
}

function deleteLine($line_no) {
    global $APP_ID, $APP_CONNECTION;
    $bkpc = $_SESSION[$APP_ID]['bkpc'];
    foreach ($bkpc['lines'] as $k => $line) {
        if ($line['line'] == $line_no) {
            $del = $k;
            if ($line['m_bkpc_line_id']) {
                if ($bkpc['delete']) {
                    $bkpc['delete'][] = $line['m_bkpc_line_id'];
                } else {
                    $bkpc['delete'] = array($line['m_bkpc_line_id']);
                }
            }
        }
    }
    unset($bkpc['lines'][$del]);
    $_SESSION[$APP_ID]['bkpc'] = $bkpc;

    $res = new xajaxResponse();
    $res->script("xajax_showLines('{$bkpc['m_bkpc_id']}', 'edit');");
    $res->assign('area-edit', 'style.display', 'none');
    $res->assign('master-button', 'style.display', '');
    return $res;
}

function saveBKPC($data) {
    global $APP_ID, $APP_CONNECTION;
    
    $bkpc = $_SESSION[$APP_ID]['bkpc'];
    $res = new xajaxResponse();
    
    if (empty($data['document_no'])) {
        $error = "Nomor BKPC tidak boleh kosong";
    } elseif (cgx_emptydate($data['bkpc_date'])) {
        $error = "Tanggal BKPC tidak boleh kosong";
    } elseif (count($bkpc['lines']) == 0) {
        $error = "Detail item slitting tidak boleh kosong, harus ada minimal satu baris.";
    }
    
    if ($error) {
        $res->alert($error);
        return $res;
    }
    
    if ($data['m_bkpc_id']) {
        $sql = "UPDATE m_bkpc SET bkpc_date = '" . cgx_dmy2ymd($data['bkpc_date']) . "', document_no = '{$data['document_no']}', do_no = '{$data['do_no']}',
          sj_no = '{$data['sj_no']}', order_muat = '{$data['order_muat']}', kendaraan_no = '{$data['kendaraan_no']}', update_date = NOW(), 
          update_user = '" . user() . "' WHERE m_bkpc_id = '{$data['m_bkpc_id']}'";
    } else {
        $sql = "INSERT INTO m_bkpc (app_org_id, document_no, partner_name , do_no, sj_no, order_muat, kendaraan_no, bkpc_date, bkpc_type, create_user, create_date )
        VALUES ('". org() . "', '{$data['document_no']}', '{$data['partner_name']}', '{$data['do_no']}', '{$data['sj_no']}', '{$data['order_muat']}', 
        '{$data['kendaraan_no']}', '" . cgx_dmy2ymd($data['bkpc_date']) . "', '2', '". user()  ."', NOW())";
    }
    $rsx = mysql_query($sql, $APP_CONNECTION);
    if (!$rsx) {
        $error = mysql_error($APP_CONNECTION);
        $res->alert($error);
        return $res;
    }
    
    if ($data['m_bkpc_id']) {
        $bkpc_id = $data['m_bkpc_id'];
    } else {
        $bkpc_id = mysql_insert_id($APP_CONNECTION);
    }
    
    foreach ($bkpc['lines'] as $line) {
        if ($line['m_bkpc_line_id']) {
            $awal = cgx_fetch_table("SELECT * FROM m_bkpc_line WHERE m_bkpc_line_id = '{$line['m_bkpc_line_id']}'");
            $sql = "UPDATE m_bkpc_line SET m_coil_id = '{$line['m_coil_id']}', weight = '{$line['weight']}', keterangan = '{$line['keterangan']}' WHERE m_bkpc_line_id = '{$line['m_bkpc_line_id']}'";
            $rsx = mysql_query($sql, $APP_CONNECTION);
            //================================================================================= upsate status coil
            $sql = "UPDATE m_coil SET status = 'I' , m_bkpc_id = null WHERE m_coil_id = '{$awal['m_coil_id']}'";
            $rsx = mysql_query($sql, $APP_CONNECTION);
            //================================================================================= update status coil
            $sql = "UPDATE m_coil SET status = 'O' , m_bkpc_id = '{$bkpc_id}' WHERE m_coil_id = '{$line['m_coil_id']}'";
            $rsx = mysql_query($sql, $APP_CONNECTION);
            //===================================================================================================== return stock weight
            $weight_awal_min = $awal['weight'] * -1;
            stock_weight(org(), user(), $line['m_product_id'], $data['bkpc_date_a'], 0, $weight_awal_min);
            //=================================================================================== update stock weight (-)
            stock_weight(org(), user(), $line['m_product_id'], $data['bkpc_date'], 0, $line['weight']);
        } else {
            $sql = "INSERT INTO m_bkpc_line (m_bkpc_id,  m_coil_id, weight, keterangan) VALUES('{$bkpc_id}', '{$line['m_coil_id']}', '{$line['weight']}', '{$line['keterangan']}')";
            $rsx = mysql_query($sql, $APP_CONNECTION);
            //================================================================================= update status coil
            $sql = "UPDATE m_coil SET status = 'O' , m_bkpc_id = '{$bkpc_id}' WHERE m_coil_id = '{$line['m_coil_id']}'";
            $rsx = mysql_query($sql, $APP_CONNECTION);
            //================================================================================== update stock on hand (-)
            stock_onhand(org(), user(), $line['m_product_id'], $data['bkpc_date'], 0, 1);
            //=================================================================================== update stock weight (-)
            stock_weight(org(), user(), $line['m_product_id'], $data['bkpc_date'], 0, $line['weight']);
            //==================================================================================== update stock balance (-)
            inout(org(), $line['m_product_id'], 282, 0, 1, FALSE);
        }
    }
    
    $_SESSION[$APP_ID]['trx.bkpc']['info'] = "Dokumen sudah berhasil disimpan";
    $res->script("window.location = 'module.php?m=trx.bkpc&pkey[m_bkpc_id]={$bkpc_id}';");
    return $res;
}

function updateLine($data) {
    global $APP_ID;
    $bkpc = $_SESSION[$APP_ID]['bkpc'];
    $res = new xajaxResponse();
    if (empty($data['m_coil_id'])) {
        $error = "No. Coil tidak boleh kosong";
    }
    if ($error) {
        $res->alert($error);
        return $res;
    }
    if (is_array($bkpc['lines'])) {
        foreach ($bkpc['lines'] as $k => $d) {
            if ($d['line'] == $data['line']) {
                $bkpc['lines'][$k]['m_coil_id'] = $data['m_coil_id'];
                $bkpc['lines'][$k]['weight'] = $data['weight'];
                $bkpc['lines'][$k]['keterangan'] = $data['keterangan'];
                $coil = cgx_fetch_table("SELECT no_coil, no_lot, m_coil.m_product_id, product_code, spec, CONCAT(thickness, ' x ', od, ' x C') as ukuran_mat FROM m_coil JOIN m_product ON (m_coil.m_product_id = m_product.m_product_id) WHERE m_coil_id = '{$data['m_coil_id']}'");
                if (is_array($coil)) foreach ($coil as $pk => $pv) if (!is_numeric($pk)) $bkpc['lines'][$k][$pk] = $pv;
                $line_updated = TRUE;
                break;
            }
        }
    }
    if (!$line_updated) {
        $new_line['m_coil_id'] = $data['m_coil_id'];
        $new_line['weight'] = $data['weight'];
        $new_line['keterangan'] = $data['keterangan'];
        $coil = cgx_fetch_table("SELECT no_coil, no_lot, m_coil.m_product_id, product_code, spec, CONCAT(thickness, ' x ', od, ' x C') as ukuran_mat FROM m_coil JOIN m_product ON (m_coil.m_product_id = m_product.m_product_id) WHERE m_coil_id = '{$data['m_coil_id']}'");
        if (is_array($coil)) foreach ($coil as $pk => $pv) if (!is_numeric($pk)) $new_line[$pk] = $pv;
        $bkpc['lines'][] = $new_line;
    }
    $_SESSION[$APP_ID]['bkpc'] = $bkpc;
    $res->script("xajax_showLines('{$bkpc['m_bkpc_id']}', 'edit');");
    $res->assign('area-edit', 'style.display', 'none');
    $res->assign('master-button', 'style.display', '');
    return $res;
}

$xajax->register(XAJAX_FUNCTION, 'showLines');
$xajax->register(XAJAX_FUNCTION, 'editForm');
$xajax->register(XAJAX_FUNCTION, 'updateLine');
$xajax->register(XAJAX_FUNCTION, 'deleteLine');
$xajax->register(XAJAX_FUNCTION, 'saveBKPC');

?>