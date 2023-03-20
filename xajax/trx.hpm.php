<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.org>
 * Nov 25, 2013 1:06:48 PM
 */

function ctl_edit_hpm($data) {
    if ($data['record']['m_production_line_id']) {
        $href = "xajax_editForm('{$data['record']['line']}');";
        $out = "<img onclick=\"{$href}\" style='cursor: pointer;' src='images/icon_edit.png' border='0'>";
    return $out;
    }
}

function saveHPM($data) {
    global $APP_ID, $APP_CONNECTION;
    $res = new xajaxResponse();
    if (empty($data['m_work_order_id'])) {
        $error = "Work Order tidak boleh kosong";
    } elseif (cgx_emptydate($data['production_date'])) {
        $error = "Tanggal tidak boleh kosong";
    } elseif (count($data['lines']) == 0) {
        $error = "Detail item barang tidak boleh kosong, silahkan pilih minimal satu barang.";
    }
    if ($error) {
        $res->alert($error);
        return $res;
    }
    $n = 0;
    foreach ($data['lines'] as $m_work_order_line_id => $quantity) {
        $n++;
        if ($quantity <= 0) $line_error .= " * Baris ke {$n}: jumlah barang tidak boleh kosong.\n";
    }   
    // if any error, display it and cancel operation
    if ($line_error) {
        $res->alert("Tidak bisa memproses:\n" . $line_error);
        return $res;
    }
    // everything should be ok start the process
    require_once 'lib/class.Penomoran.php';
    $nomor = new Penomoran();
    $document_no = $nomor->urut('HP', org());
    $sql =
        "INSERT INTO m_production (document_no, m_work_order_id, production_date, m_machine_id, nik, create_user, create_date)
        VALUES ('{$document_no}', '{$data['m_work_order_id']}', '" . cgx_dmy2ymd($data['production_date']) . "', '{$data['m_machine_id']}', '{$data['nik']}', '". user() ."', NOW())";
    $rsx = mysql_query($sql, $APP_CONNECTION);
    if (!$rsx) {
        $error = mysql_error($APP_CONNECTION);
        $res->alert($error);
        return $res;
    }
    $hpm_id = mysql_insert_id($APP_CONNECTION);
    foreach ($data['lines'] as $m_work_order_line_id => $quantity) {
        $sql = "INSERT INTO m_production_line (m_production_id, m_work_order_line_id, good, nogood, no_coil, no_lot, ket)
            VALUES ('{$hpm_id}', '{$m_work_order_line_id}', '{$quantity}', '{$data['linesnogood'][$m_work_order_line_id]}',
            '{$data['linesnocoil'][$m_work_order_line_id]}', '{$data['linesnolot'][$m_work_order_line_id]}', '{$data['linesket'][$m_work_order_line_id]}')";
        $rsx = mysql_query($sql, $APP_CONNECTION);
        $quantity0 = (double) $quantity;
        mysql_query("UPDATE m_work_order_line SET producted_quantity = producted_quantity + {$quantity0} 
            WHERE m_work_order_line_id = '{$m_work_order_line_id}'",$APP_CONNECTION);
    }
    $_SESSION[$APP_ID]['trx.hpm']['info'] = "Dokumen sudah berhasil disimpan";
    $res->script("window.location = 'module.php?m=trx.hpm&pkey[m_production_id]={$hpm_id}';");
    return $res;
}

function updateHPM($data) {
    global $APP_ID, $APP_CONNECTION;
    $hpm = $_SESSION[$APP_ID]['hpm'];
    $res = new xajaxResponse();
    if (cgx_emptydate($data['production_date'])) {
        $error = "Tanggal tidak boleh kosong";
    } 
    if ($error) {
        $res->alert($error);
        return $res;
    }
    $sql = "UPDATE m_production SET production_date = '" . cgx_dmy2ymd($data['production_date']) . "', nik = '{$data['nik']}', 
        m_machine_id = '{$data['m_machine_id']}', update_date = NOW(), update_user = '" . user() . "' WHERE m_production_id = '{$data['m_production_id']}'";
    $rsx = mysql_query($sql, $APP_CONNECTION);
    if (!$rsx) {
        $error = mysql_error($APP_CONNECTION);
        $res->alert($error);
        return $res;
    }
    foreach ($hpm['lines'] as $line) {
        $awal = cgx_fetch_table("SELECT * FROM m_production_line WHERE m_production_line_id = '{$line['m_production_line_id']}'");
        $sql = "UPDATE m_production_line SET no_coil = '{$line['no_coil']}', no_lot = '{$line['no_lot']}', 
            good = '{$line['good']}', nogood = '{$line['nogood']}', ket = '{$line['ket']}',
            good_ch = '{$line['good_ch']}', nogood_ch = '{$line['nogood_ch']}', ket_ch = '{$line['ket_ch']}', nik_ch = '{$line['nik_ch']}', date_ch = '{$line['date_ch']}',  
            good_sk = '{$line['good_sk']}', nogood_sk = '{$line['nogood_sk']}', ket_sk = '{$line['ket_sk']}', nik_sk = '{$line['nik_sk']}', date_sk = '{$line['date_sk']}', 
            good_pl = '{$line['good_pl']}', nogood_pl = '{$line['nogood_pl']}', ket_pl = '{$line['ket_pl']}', nik_pl = '{$line['nik_pl']}', date_pl = '{$line['date_pl']}', 
            good_bd = '{$line['good_bd']}', nogood_bd = '{$line['nogood_bd']}', ket_bd = '{$line['ket_bd']}', nik_bd = '{$line['nik_bd']}', date_bd = '{$line['date_bd']}', 
            good_qc = '{$line['good_qc']}', nogood_qc = '{$line['nogood_qc']}', ket_qc = '{$line['ket_qc']}', nik_qc = '{$line['nik_qc']}', date_qc = '{$line['date_qc']}', 
            good_pc = '{$line['good_pc']}', nogood_pc = '{$line['nogood_pc']}', ket_pc = '{$line['ket_pc']}', nik_pc = '{$line['nik_pc']}', date_pc = '{$line['date_pc']}'  
            WHERE m_production_line_id = '{$line['m_production_line_id']}'";
        $rsx = mysql_query($sql, $APP_CONNECTION);
        $good = $line['good'] - $awal['good'];
        $good_ch = $line['good_ch'] - $awal['good_ch'];
        $good_sk = $line['good_sk'] - $awal['good_sk'];
        $good_pl = $line['good_pl'] - $awal['good_pl'];
        $good_bd = $line['good_bd'] - $awal['good_bd'];
        $good_qc = $line['good_qc'] - $awal['good_qc'];
        $good_pc = $line['good_pc'] - $awal['good_pc'];
        mysql_query("UPDATE m_work_order_line SET producted_quantity = producted_quantity + {$good},
            ch_quantity = ch_quantity + {$good_ch}, sk_quantity = sk_quantity + {$good_sk},
            pl_quantity = pl_quantity + {$good_pl}, bd_quantity = bd_quantity + {$good_bd},
            qc_quantity = qc_quantity + {$good_qc}, pc_quantity = pc_quantity + {$good_pc} 
            WHERE m_work_order_line_id = '{$line['m_work_order_line_id']}'",$APP_CONNECTION);   
    }
    $_SESSION[$APP_ID]['trx.hpm']['info'] = "Dokumen sudah berhasil diperbaharui";
    $res->script("window.location = 'module.php?m=trx.hpm&pkey[m_production_id]={$data['m_production_id']}';");
    return $res;
}

function editForm($line_no) {
    global $APP_CONNECTION, $APP_ID, $APP_DATE_FORMAT, $APP_DATE_FORMAT_JAVA;
    global $mandatory;
    
    foreach ($_SESSION[$APP_ID]['hpm']['lines'] as $line) {
        if ($line['line'] == $line_no) {
            $data = $line;
            break;
        }
    }
    $html .= "<form id='frmLine'>";
    $html .= "<input type='hidden' name='line' value='{$data['line']}'>";
    $html .= "<input type='hidden' id='m_work_order_line_id' name='m_work_order_line_id' value='{$data['m_work_order_line_id']}'>";
    $html .= "<table width='100%'>";
    $html .= "<tr>";
    $html .= "<td><b><u>HASIL CUTTING</u></b></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td width='12%'>Item Number {$mandatory}</td>";
    $html .= "<td width='33%'><input id='product_code' type='text' size='20' value=\"{$data['product_code']}\" readonly='readonly'></td>";
    $html .= "<td width='6%'></td>";
    $html .= "<td width='12%'>Kode Coil</td>";
    $html .= "<td width='37%'><input type='text' size='30' name='no_coil' value=\"{$data['no_coil']}\" style='text-align: left;'></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Barang</td>";
    $html .= "<td><input type='text' name='product_name' id='product_name' size='30' value=\"{$data['product_name']}\" readonly='readonly'></td>";
    $html .= "<td></td>";
    $html .= "<td>Lot Number</td>";
    $html .= "<td><input type='text' size='30' name='no_lot' value=\"{$data['no_lot']}\" style='text-align: left;'></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Description</td>";
    $html .= "<td><input type='text' size='30' id='description' value=\"{$data['description']}\" name='description' readonly='readonly'></td>";
    $html .= "<td></td>";
    $html .= "<td>Good Qty {$mandatory}</td>";
    $html .= "<td><input type='text' size='10' name='good' value=\"{$data['good']}\" style='text-align: right;'></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Keterangan</td>";
    $html .= "<td><input type='text' size='42' name='ket' value=\"{$data['ket']}\" style='text-align: left;'></td>";
    $html .= "<td></td>";
    $html .= "<td>No Good Qty</td>";
    $html .= "<td><input type='text' size='10' name='nogood' value=\"{$data['nogood']}\" style='text-align: right;'></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td colspan='5'><hr></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td><b><u>HASIL CHAMPER</u></b></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Tanggal {$mandatory}</td>";
    $html .= "<td><input{$readonly} name='date_ch' id='date_ch' type='text' size='10' value=\"" . (cgx_emptydate($data['date_ch']) ? '' : date($APP_DATE_FORMAT, strtotime($data['date_ch']))) . "\"></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>NIK</td>";
    $html .= "<td><input type='text' size='25' name='nik_ch' value=\"{$data['nik_ch']}\" style='text-align: left;'></td>";
    $html .= "<td></td>";
    $html .= "<td>Good Qty</td>";
    $html .= "<td><input type='text' size='10' name='good_ch' value=\"{$data['good_ch']}\" style='text-align: right;'></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Keterangan</td>";
    $html .= "<td><input type='text' size='42' name='ket_ch' value=\"{$data['ket_ch']}\" style='text-align: left;'></td>";
    $html .= "<td></td>";
    $html .= "<td>No Good Qty</td>";
    $html .= "<td><input type='text' size='10' name='nogood_ch' value=\"{$data['nogood_ch']}\" style='text-align: right;'></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td colspan='5'><hr></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td><b><u>HASIL SIKAT</u></b></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Tanggal {$mandatory}</td>";
    $html .= "<td><input{$readonly} name='date_sk' id='date_sk' type='text' size='10' value=\"" . (cgx_emptydate($data['date_sk']) ? '' : date($APP_DATE_FORMAT, strtotime($data['date_sk']))) . "\"></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>NIK</td>";
    $html .= "<td><input type='text' size='25' name='nik_sk' value=\"{$data['nik_sk']}\" style='text-align: left;'></td>";
    $html .= "<td></td>";
    $html .= "<td>Good Qty</td>";
    $html .= "<td><input type='text' size='10' name='good_sk' value=\"{$data['good_sk']}\" style='text-align: right;'></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Keterangan</td>";
    $html .= "<td><input type='text' size='42' name='ket_sk' value=\"{$data['ket_sk']}\" style='text-align: left;'></td>";
    $html .= "<td></td>";
    $html .= "<td>No Good Qty</td>";
    $html .= "<td><input type='text' size='10' name='nogood_sk' value=\"{$data['nogood_sk']}\" style='text-align: right;'></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td colspan='5'><hr></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td><b><u>HASIL POLESING</u></b></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Tanggal {$mandatory}</td>";
    $html .= "<td><input{$readonly} name='date_pl' id='date_pl' type='text' size='10' value=\"" . (cgx_emptydate($data['date_pl']) ? '' : date($APP_DATE_FORMAT, strtotime($data['date_pl']))) . "\"></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>NIK</td>";
    $html .= "<td><input type='text' size='25' name='nik_pl' value=\"{$data['nik_pl']}\" style='text-align: left;'></td>";
    $html .= "<td></td>";
    $html .= "<td>Good Qty</td>";
    $html .= "<td><input type='text' size='10' name='good_pl' value=\"{$data['good_pl']}\" style='text-align: right;'></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Keterangan</td>";
    $html .= "<td><input type='text' size='42' name='ket_pl' value=\"{$data['ket_pl']}\" style='text-align: left;'></td>";
    $html .= "<td></td>";
    $html .= "<td>No Good Qty</td>";
    $html .= "<td><input type='text' size='10' name='nogood_pl' value=\"{$data['nogood_pl']}\" style='text-align: right;'></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td colspan='5'><hr></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td><b><u>HASIL BENDING</u></b></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Tanggal {$mandatory}</td>";
    $html .= "<td><input{$readonly} name='date_bd' id='date_bd' type='text' size='10' value=\"" . (cgx_emptydate($data['date_bd']) ? '' : date($APP_DATE_FORMAT, strtotime($data['date_bd']))) . "\"></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>NIK</td>";
    $html .= "<td><input type='text' size='25' name='nik_bd' value=\"{$data['nik_bd']}\" style='text-align: left;'></td>";
    $html .= "<td></td>";
    $html .= "<td>Good Qty</td>";
    $html .= "<td><input type='text' size='10' name='good_bd' value=\"{$data['good_bd']}\" style='text-align: right;'></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Keterangan</td>";
    $html .= "<td><input type='text' size='42' name='ket_bd' value=\"{$data['ket_bd']}\" style='text-align: left;'></td>";
    $html .= "<td></td>";
    $html .= "<td>No Good Qty</td>";
    $html .= "<td><input type='text' size='10' name='nogood_bd' value=\"{$data['nogood_bd']}\" style='text-align: right;'></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td colspan='5'><hr></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td><b><u>HASIL QUENCING</u></b></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Tanggal {$mandatory}</td>";
    $html .= "<td><input{$readonly} name='date_qc' id='date_qc' type='text' size='10' value=\"" . (cgx_emptydate($data['date_qc']) ? '' : date($APP_DATE_FORMAT, strtotime($data['date_qc']))) . "\"></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>NIK</td>";
    $html .= "<td><input type='text' size='25' name='nik_qc' value=\"{$data['nik_qc']}\" style='text-align: left;'></td>";
    $html .= "<td></td>";
    $html .= "<td>Good Qty</td>";
    $html .= "<td><input type='text' size='10' name='good_qc' value=\"{$data['good_qc']}\" style='text-align: right;'></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Keterangan</td>";
    $html .= "<td><input type='text' size='42' name='ket_qc' value=\"{$data['ket_qc']}\" style='text-align: left;'></td>";
    $html .= "<td></td>";
    $html .= "<td>No Good Qty</td>";
    $html .= "<td><input type='text' size='10' name='nogood_qc' value=\"{$data['nogood_qc']}\" style='text-align: right;'></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td colspan='5'><hr></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td><b><u>HASIL PACKING</u></b></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Tanggal {$mandatory}</td>";
    $html .= "<td><input{$readonly} name='date_pc' id='date_pc' type='text' size='10' value=\"" . (cgx_emptydate($data['date_pc']) ? '' : date($APP_DATE_FORMAT, strtotime($data['date_pc']))) . "\"></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>NIK</td>";
    $html .= "<td><input type='text' size='25' name='nik_pc' value=\"{$data['nik_pc']}\" style='text-align: left;'></td>";
    $html .= "<td></td>";
    $html .= "<td>Good Qty</td>";
    $html .= "<td><input type='text' size='10' name='good_pc' value=\"{$data['good_pc']}\" style='text-align: right;'></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>Keterangan</td>";
    $html .= "<td><input type='text' size='42' name='ket_pc' value=\"{$data['ket_pc']}\" style='text-align: left;'></td>";
    $html .= "<td></td>";
    $html .= "<td>No Good Qty</td>";
    $html .= "<td><input type='text' size='10' name='nogood_pc' value=\"{$data['nogood_pc']}\" style='text-align: right;'></td>";
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
    $res->script("\$(function() { \$('#date_ch').datepicker({dateFormat: '{$APP_DATE_FORMAT_JAVA}'}); });");
    $res->script("\$(function() { \$('#date_sk').datepicker({dateFormat: '{$APP_DATE_FORMAT_JAVA}'}); });");
    $res->script("\$(function() { \$('#date_pl').datepicker({dateFormat: '{$APP_DATE_FORMAT_JAVA}'}); });");
    $res->script("\$(function() { \$('#date_bd').datepicker({dateFormat: '{$APP_DATE_FORMAT_JAVA}'}); });");
    $res->script("\$(function() { \$('#date_qc').datepicker({dateFormat: '{$APP_DATE_FORMAT_JAVA}'}); });");
    $res->script("\$(function() { \$('#date_pc').datepicker({dateFormat: '{$APP_DATE_FORMAT_JAVA}'}); });");
    return $res;
}

function updateLine($data) {
    global $APP_ID;    
    $hpm = $_SESSION[$APP_ID]['hpm'];
    $res = new xajaxResponse();
    if ((int) $data['good'] < 0) {
        $error = "Jumlah barang (good) hasil cutting tidak boleh kosong";
    }
    if ($error) {
        $res->alert($error);
        return $res;
    }
    if (is_array($hpm['lines'])) {
        foreach ($hpm['lines'] as $k => $d) {
            if ($d['line'] == $data['line']) {
                $hpm['lines'][$k]['no_coil'] = $data['no_coil'];
                $hpm['lines'][$k]['no_lot'] = $data['no_lot'];
                $hpm['lines'][$k]['ket'] = $data['ket'];
                $hpm['lines'][$k]['good'] = $data['good'];
                $hpm['lines'][$k]['nogood'] = $data['nogood'];
                $hpm['lines'][$k]['nik_ch'] = $data['nik_ch'];
                $hpm['lines'][$k]['ket_ch'] = $data['ket_ch'];
                $hpm['lines'][$k]['good_ch'] = $data['good_ch'];
                $hpm['lines'][$k]['nogood_ch'] = $data['nogood_ch'];
                $hpm['lines'][$k]['nik_sk'] = $data['nik_sk'];
                $hpm['lines'][$k]['ket_sk'] = $data['ket_sk'];
                $hpm['lines'][$k]['good_sk'] = $data['good_sk'];
                $hpm['lines'][$k]['nogood_sk'] = $data['nogood_sk'];
                $hpm['lines'][$k]['nik_pl'] = $data['nik_pl'];
                $hpm['lines'][$k]['ket_pl'] = $data['ket_pl'];
                $hpm['lines'][$k]['good_pl'] = $data['good_pl'];
                $hpm['lines'][$k]['nogood_pl'] = $data['nogood_pl'];
                $hpm['lines'][$k]['nik_bd'] = $data['nik_bd'];
                $hpm['lines'][$k]['ket_bd'] = $data['ket_bd'];
                $hpm['lines'][$k]['good_bd'] = $data['good_bd'];
                $hpm['lines'][$k]['nogood_bd'] = $data['nogood_bd'];
                $hpm['lines'][$k]['nik_qc'] = $data['nik_qc'];
                $hpm['lines'][$k]['ket_qc'] = $data['ket_qc'];
                $hpm['lines'][$k]['good_qc'] = $data['good_qc'];
                $hpm['lines'][$k]['nogood_qc'] = $data['nogood_qc'];
                $hpm['lines'][$k]['nik_pc'] = $data['nik_pc'];
                $hpm['lines'][$k]['ket_pc'] = $data['ket_pc'];
                $hpm['lines'][$k]['good_pc'] = $data['good_pc'];
                $hpm['lines'][$k]['nogood_pc'] = $data['nogood_pc'];
                $hpm['lines'][$k]['date_ch'] = cgx_dmy2ymd($data['date_ch']);
                $hpm['lines'][$k]['date_sk'] = cgx_dmy2ymd($data['date_sk']);
                $hpm['lines'][$k]['date_pl'] = cgx_dmy2ymd($data['date_pl']);
                $hpm['lines'][$k]['date_bd'] = cgx_dmy2ymd($data['date_bd']);
                $hpm['lines'][$k]['date_qc'] = cgx_dmy2ymd($data['date_qc']);
                $hpm['lines'][$k]['date_pc'] = cgx_dmy2ymd($data['date_pc']);
                $product = cgx_fetch_table("SELECT * FROM m_product WHERE m_product_id = '{$data['m_product_id']}'");
                if (is_array($product)) foreach ($product as $pk => $pv) if (!is_numeric($pk)) $hpm['lines'][$k][$pk] = $pv;
                $line_updated = TRUE;
                break;
            }
        }
    }
    $_SESSION[$APP_ID]['hpm'] = $hpm;
    $res->script("xajax_showLines('{$hpm['m_production_id']}', 'editH');");
    $res->assign('area-edit', 'style.display', 'none');
    $res->assign('master-button', 'style.display', '');
    return $res;
}

function grid_ket_form($data) {
    $row_id = $data['record']['m_work_order_line_id'];
    $html .= "<input id='txtket{$row_id}' name='linesket[{$row_id}]' type='text' size='8' style='text-align: left;' disabled>";
    return $html;
}

function grid_nocoil_form($data) {
    $row_id = $data['record']['m_work_order_line_id'];
    $html .= "<input id='txtnocoil{$row_id}' name='linesnocoil[{$row_id}]' type='text' size='8' style='text-align: left;' disabled>";
    return $html;
}

function grid_nolot_form($data) {
    $row_id = $data['record']['m_work_order_line_id'];
    $html .= "<input id='txtnolot{$row_id}' name='linesnolot[{$row_id}]' type='text' size='8' style='text-align: left;' disabled>";
    return $html;
}

function grid_good_form($data) {
    $row_id = $data['record']['m_work_order_line_id'];
    $html .= "<input id='txt{$row_id}' name='lines[{$row_id}]' type='text' value ='0' size='8' style='text-align: right;' disabled>";
    return $html;
}

function grid_nogood_form($data) {
    $row_id = $data['record']['m_work_order_line_id'];
    $html .= "<input id='txtnogood{$row_id}' name='linesnogood[{$row_id}]' type='text' value ='0' size='8' style='text-align: right;' disabled>";
    return $html;
}

function grid_chk_form($data) {
    $row_id = $data['record']['m_work_order_line_id'];
    $html .= "<input type='checkbox' onclick=\"document.getElementById('txtnocoil{$row_id}').disabled = !this.checked;document.getElementById('txtnolot{$row_id}').disabled = !this.checked;document.getElementById('txt{$row_id}').disabled = !this.checked;document.getElementById('txtnogood{$row_id}').disabled = !this.checked;document.getElementById('txtket{$row_id}').disabled = !this.checked;\">";
    return $html;
}

function showLines($m_production_id, $mode = NULL) {
    global $APP_CONNECTION, $APP_ID, $cgx_TableAttribs, $cgx_HeaderAttribs,
        $cgx_EvenRowAttribs, $cgx_OddRowAttribs, $cgx_RendererOptions;
    
    $data = $_SESSION[$APP_ID]['hpm'];
    if ($data['m_production_id'] != $m_production_id) return;
    
    if (is_array($data['lines'])) {
        $n = 0;
        foreach ($data['lines'] as $k => $d) {
            $n++;
            $data['lines'][$k]['line'] = $n;
        }
    }
    $_SESSION[$APP_ID]['hpm'] = $data;
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
    $datagrid->addColumn(new Structures_DataGrid_Column('Remark', 'remark', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('PO Number', 'reference_no', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Kode Coil', 'no_coil', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Lot Number', 'no_lot', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Good Cutting', 'good', NULL, array('align' => 'right'), NULL, "cgx_format_3digit"));
    $datagrid->addColumn(new Structures_DataGrid_Column('No Good Cutting', 'nogood', NULL, array('align' => 'right'), NULL, "cgx_format_3digit"));
    //$datagrid->addColumn(new Structures_DataGrid_Column('ket', 'ket', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Good CH', 'good_ch', NULL, array('align' => 'right'), NULL, "cgx_format_3digit"));
    $datagrid->addColumn(new Structures_DataGrid_Column('Good SK', 'good_sk', NULL, array('align' => 'right'), NULL, "cgx_format_3digit"));
    $datagrid->addColumn(new Structures_DataGrid_Column('Good PL', 'good_pl', NULL, array('align' => 'right'), NULL, "cgx_format_3digit"));
    $datagrid->addColumn(new Structures_DataGrid_Column('Good BD', 'good_bd', NULL, array('align' => 'right'), NULL, "cgx_format_3digit"));
    $datagrid->addColumn(new Structures_DataGrid_Column('Good QC', 'good_qc', NULL, array('align' => 'right'), NULL, "cgx_format_3digit"));
    if ($mode == 'editH') $datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '20'), NULL, 'ctl_edit_hpm()'));

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

function workOrderLinesForm($m_work_order_id) {
    global $APP_CONNECTION, $cgx_TableAttribs, $cgx_HeaderAttribs, $cgx_dsn,
        $cgx_EvenRowAttribs, $cgx_OddRowAttribs, $cgx_RendererOptions;
    
    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';
    $datagrid = new Structures_DataGrid(9999);
    $cgx_options = array('dsn' => $cgx_dsn);
    $cgx_sql = "SELECT @curRow := @curRow + 1 AS line, m_work_order_line.*, COALESCE(remark,c_forecast.document_no) remark, reference_no, m_product.* 
            FROM m_work_order_line 
            LEFT JOIN c_order USING (c_order_id)
            LEFT JOIN c_forecast ON (m_work_order_line.c_forecast_id=c_forecast.c_forecast_id)
            JOIN m_product USING (m_product_id) 
            JOIN (SELECT @curRow := 0) r 
            WHERE m_work_order_id = '{$m_work_order_id}'";
    $datagrid->bind($cgx_sql, $cgx_options);
    
    $cgx_table = new HTML_Table($cgx_TableAttribs);
    $cgx_tableHeader = & $cgx_table->getHeader();
    $cgx_tableBody = & $cgx_table->getBody();
    
    $datagrid->addColumn(new Structures_DataGrid_Column('Baris', 'line', NULL, array('align' => 'center'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Item<br>Number', 'product_code', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Produk Name', 'product_name', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Remark', 'remark', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('PO Number', 'reference_no', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Qty WO', 'order_quantity', NULL, array('align' => 'right'), NULL, "cgx_format_3digit"));
    $datagrid->addColumn(new Structures_DataGrid_Column('Qty Cutting', 'producted_quantity', NULL, array('align' => 'right'), NULL, "cgx_format_3digit"));
    $datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, "grid_chk_form"));
    $datagrid->addColumn(new Structures_DataGrid_Column('Kode Coil', NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, "grid_nocoil_form"));
    $datagrid->addColumn(new Structures_DataGrid_Column('Lot Number', NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, "grid_nolot_form"));
    $datagrid->addColumn(new Structures_DataGrid_Column('Good', NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, "grid_good_form"));
    $datagrid->addColumn(new Structures_DataGrid_Column('No Good', NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, "grid_nogood_form"));
    $datagrid->addColumn(new Structures_DataGrid_Column('Ket', NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, "grid_ket_form"));

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

$xajax->register(XAJAX_FUNCTION, "workOrderLinesForm");
$xajax->register(XAJAX_FUNCTION, "saveHPM");
$xajax->register(XAJAX_FUNCTION, "updateHPM");
$xajax->register(XAJAX_FUNCTION, 'editForm');
$xajax->register(XAJAX_FUNCTION, 'updateLine');
$xajax->register(XAJAX_FUNCTION, 'showLines');

?>