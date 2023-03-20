<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.org>
 * Dec 18, 2013 8:53:40 PM
 */

function ctl_delete_rr($data) {
    $href = "xajax_deleteLine('{$data['record']['m_box_id']}');";
    $out = "<img onclick=\"{$href}\" style='cursor: pointer;' src='images/icon_delete.png' border='0'>";
    return $out;
}

function addBox($box_id, $box_no, $box_code) {
    global $APP_ID;
    $data = $_SESSION[$APP_ID]['xk'];
    $data['lines'][$box_id] = array('m_box_id' => $box_id, 'box_number' => $box_no, 'box_code'=>$box_code);
    $_SESSION[$APP_ID]['xk'] = $data;
    
    $res = new xajaxResponse();
    $res->script("xajax_showLines('{$data['m_box_inout_id']}', 'edit');");
    return $res;
}

function showLines($m_box_inout_id, $mode = NULL) {
    global $APP_CONNECTION, $APP_ID, $cgx_TableAttribs, $cgx_HeaderAttribs,
        $cgx_EvenRowAttribs, $cgx_OddRowAttribs, $cgx_RendererOptions;
    
    $data = $_SESSION[$APP_ID]['xk'];
    if ($data['m_box_inout_id'] != $m_box_inout_id) return;
    
    if (is_array($data['lines'])) {
        $n = 0;
        foreach ($data['lines'] as $k => $d) {
            $n++;
            $data['lines'][$k]['line'] = $n;
        }
    }
    $_SESSION[$APP_ID]['xk'] = $data;

    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';
    
    $datagrid = new Structures_DataGrid(9999);
    $datagrid->bind($data['lines'], array(), 'Array');
    $cgx_table = new HTML_Table($cgx_TableAttribs);
    $cgx_tableHeader = & $cgx_table->getHeader();
    $cgx_tableBody = & $cgx_table->getBody();
    
    $datagrid->addColumn(new Structures_DataGrid_Column('Baris', 'line', NULL, array('align' => 'center'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Nomor Box', 'box_number', NULL, array('align' => 'center'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Kode Box', 'box_code', NULL, array('align' => 'center'), NULL, NULL));
    if ($mode == 'edit') $datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, 'ctl_delete_rr()'));

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

function deleteLine($m_box_id) {
    global $APP_ID;
    $data = $_SESSION[$APP_ID]['xk'];
    unset($data['lines'][$m_box_id]);
    $_SESSION[$APP_ID]['xk'] = $data;
    
    $res = new xajaxResponse();
    $res->script("xajax_showLines('{$data['m_box_inout_id']}', 'edit');");
    return $res;
}

function saveXK($data) {
    global $APP_ID, $APP_CONNECTION;
    $xk = $_SESSION[$APP_ID]['xk'];

    $res = new xajaxResponse();
    if (cgx_emptydate($data['m_box_inout_date'])) {
        $error = "Tanggal transaksi tidak boleh kosong";
    } elseif (count($xk['lines']) == 0) {
        $error = "Detail item box tidak boleh kosong, harus ada minimal satu baris.";
    }
    if ($error) {
        $res->alert($error);
        return $res;
    }
    
    require_once 'lib/class.Penomoran.php';
    $nomor = new Penomoran();
    $document_no = $nomor->urut('XK', org());
    $sql =
        "INSERT INTO m_box_inout (app_org_id, document_no, m_box_inout_date, m_transaction_type_id, c_bpartner_id, no_truck, nama_supir)
        VALUES ('" . org() . "', '{$document_no}', '" . cgx_dmy2ymd($data['m_box_inout_date']) . "', 8,'".mysql_escape_string($data['c_bpartner_id'])."','".mysql_escape_string($data['no_truck'])."','".mysql_escape_string($data['nama_supir'])."')";
    $rsx = mysql_query($sql, $APP_CONNECTION);
    if (!$rsx) {
        $error = mysql_error($APP_CONNECTION);
        $res->alert($error);
        return $res;
    }
    $xk_id = mysql_insert_id($APP_CONNECTION);
    
    foreach ($xk['lines'] as $line) {
        $sql = "INSERT INTO m_box_inout_line (m_box_inout_id, m_box_id)
            VALUES ('{$xk_id}', '{$line['m_box_id']}')";
        $rsx = mysql_query($sql, $APP_CONNECTION);
        $sql = "UPDATE m_box SET location = 'O' WHERE m_box_id = '{$line['m_box_id']}'";
        $rsx = mysql_query($sql, $APP_CONNECTION);
    }

    $_SESSION[$APP_ID]['trx.xk']['info'] = "Dokumen sudah berhasil disimpan";
    $res->script("window.location = 'module.php?m=trx.xk&pkey[m_box_inout_id]={$xk_id}';");
    return $res;
}

$xajax->register(XAJAX_FUNCTION, 'addBox');
$xajax->register(XAJAX_FUNCTION, 'showLines');
$xajax->register(XAJAX_FUNCTION, 'deleteLine');
$xajax->register(XAJAX_FUNCTION, 'saveXK');

?>