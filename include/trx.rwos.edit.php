<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.org>
 * Nov 23, 2013 11:35:10 PM
 */

if ($_REQUEST['mode'] == 'edit' && $_SESSION[$APP_ID]['rwos']['m_prod_slit_id'] == $_REQUEST['pkey']['m_prod_slit_id']) {
    $data = $_SESSION[$APP_ID]['rwos'];
} else {
    /*
    $data = npl_fetch_table(
        "SELECT m_prod_slit.*, m_wo_slit.document_no wo, m_wo_slit.m_product_id, product_name
        FROM m_prod_slit
        JOIN m_warehouse USING(m_warehouse_id)
        JOIN m_wo_slit ON(m_prod_slit.m_wo_slit_id = m_wo_slit.m_wo_slit_id)
        JOIN m_product ON(m_wo_slit.m_product_id=m_product.m_product_id)
        JOIN c_bpartner ON(m_wo_slit.c_bpartner_id=c_bpartner.c_bpartner_id) 
        WHERE m_prod_slit_id = '{$_REQUEST['pkey']['m_prod_slit_id']}'");
    */
     $data = npl_fetch_table(
        "SELECT 
            m_prod_slit.*, 
            m_wo_slit.document_no AS wo, 
            m_wo_slit.m_product_id, 
            product_name
        FROM 
            m_prod_slit JOIN m_warehouse USING(m_warehouse_id)
                        JOIN m_wo_slit ON(m_prod_slit.m_wo_slit_id = m_wo_slit.m_wo_slit_id)
                        JOIN m_product ON(m_wo_slit.m_product_id=m_product.m_product_id)
        WHERE 
            m_prod_slit_id = '{$_REQUEST['pkey']['m_prod_slit_id']}'");   
        
    $rsx = mysql_query(
            "SELECT 
                m_prod_slit_line.* 
                ,no_coil
                ,m_wo_slit_line_id
                ,m_product.*
                ,m_warehouse.*
                ,m_wo_slit.partner AS partner
                -- ,MID(partner_name,1,25) AS partner_name
            FROM 
                m_prod_slit_line JOIN m_warehouse USING (m_warehouse_id)
                                 JOIN m_coil ON(m_prod_slit_line.m_coil_id=m_coil.m_coil_id)
                                 JOIN m_wo_slit_line USING(m_wo_slit_line_id)
                                 JOIN m_product ON(m_wo_slit_line.m_product_id=m_product.m_product_id)
                                 JOIN m_wo_slit ON (m_wo_slit_line.m_wo_slit_id = m_wo_slit.m_wo_slit_id)
                            -- LEFT JOIN c_bpartner ON(m_wo_slit_line.c_bpartner_id=c_bpartner.c_bpartner_id)
            WHERE 
                m_prod_slit_id = '{$_REQUEST['pkey']['m_prod_slit_id']}'",
            $APP_CONNECTION);
    $data['lines'] = array();
    while ($dtx = mysql_fetch_array($rsx, MYSQL_ASSOC)) $data['lines'][] = $dtx;
    mysql_free_result($rsx);
    $_SESSION[$APP_ID]['rwos'] = $data;

    $rsy = mysql_query(
        "SELECT m_coil.*
        FROM m_coil
        WHERE m_out_id = '{$_REQUEST['pkey']['m_prod_slit_id']}'
        ORDER BY m_coil_id",
        $APP_CONNECTION);
    $data['linesdua'] = array();
    while ($dty = mysql_fetch_array($rsy, MYSQL_ASSOC)) $data['linesdua'][] = $dty;
    mysql_free_result($rsy);
    $_SESSION[$APP_ID]['rwos'] = $data;
}

if ($_REQUEST['mode'] != 'edit' AND $_REQUEST['mode'] != 'editH') {
    $readonly = ' readonly="readonly"';
    $disabled = ' disabled';
} else {
    $select_wo = "<img onclick=\"popupReference('work-order-slitting');\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'>";
}

if ($_REQUEST['mode'] == 'editH') {
    $select_wo = "";
    $disabled = ' disabled';
}

if ($_SESSION[$GLOBALS['APP_ID']]['trx.rwos']['error']) {
    echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['trx.rwos']['error']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['trx.rwos']['error']);
}

if ($_SESSION[$GLOBALS['APP_ID']]['trx.rwos']['info']) {
    echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['trx.rwos']['info']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['trx.rwos']['info']);
}

$data['production_date'] = empty($data['production_date']) ? date($APP_DATE_FORMAT) : $data['production_date'];
    
echo "<form id='frmRWOS'>";
echo "<div class='data_box'>";
echo "<input type='hidden' id='m_wo_slit_id' name='m_wo_slit_id' value='{$data['m_wo_slit_id']}'>";
echo "<input type='hidden' id='m_product_id' name='m_product_id' value='{$data['m_product_id']}'>";
echo "<input type='hidden' id='m_prod_slit_id' name='m_prod_slit_id' value='{$data['m_prod_slit_id']}'>";
echo "<input type='hidden' id='production_date_a' name='production_date_a' value=\"" . (cgx_emptydate($data['production_date']) ? '' : date($APP_DATE_FORMAT, strtotime($data['production_date']))) . "\">";
echo "<table width='100%'>";
echo "<tr>";
echo "<td width='12%'>Nomor Dokumen</td>";
echo "<td width='30%'><input " . ($data['document_no'] ? 'readonly="readonly"' : 'disabled') . " name='document_no' type='text' size='20' value=\"" . ($data['document_no'] ? $data['document_no'] : $autonumber) . "\"></td>";
echo "<td width='5%'></td>";
echo "<td width='15%'>Ukuran Coil RAW</td>";
echo "<td width='37%'><input readonly='readonly' type='text' id='product_name' size='40' value=\"{$data['product_name']}\"></td>";
echo "</tr>";
echo "<tr>";
echo "<td>Work Order {$mandatory}</td>";
echo "<td><input readonly='readonly' type='text' id='work_order' size='20' value=\"{$data['wo']}\">{$select_wo}</td>";
echo "<td></td>";
echo "<td>Jumlah Coil terpakai {$mandatory}</td>";
echo "<td><input readonly='readonly' type='text' id='quantity_raw' name='quantity_raw' size='10' value=\"{$data['quantity_raw']}\" style='text-align: right;'></td>";
echo "</tr>";
echo "<tr>";
echo "<td>Tanggal {$mandatory}</td>";
echo "<td><input{$readonly} name='production_date' id='production_date' type='text' size='10' value=\"" . (cgx_emptydate($data['production_date']) ? '' : date($APP_DATE_FORMAT, strtotime($data['production_date']))) . "\"></td>";
echo "<td></td>";
echo "<td>Gudang Asal Coil{$mandatory}</td>";
echo "<td>" . cgx_filter('m_warehouse_id', "SELECT m_warehouse_id, warehouse_name FROM m_warehouse WHERE app_org_id='" . org() . "' AND (m_warehouse_id = 282 OR m_warehouse_id = 283) ORDER BY warehouse_name", $data['m_warehouse_id'], FALSE, $disabled) . "</td>";
echo "</tr>";
// echo "<td>Mesin {$mandatory}</td>";
// echo "<td>" . cgx_filter('m_machine_id', "SELECT m_machine_id, machine_name FROM m_machine WHERE " . org_filter_master() . " ORDER BY machine_name", $data['m_machine_id'], FALSE, $disabled) . "</td>";
echo "</table>";
echo "</div>";
echo "<div id='area-lines' style='margin-top: 4px;'></div>";
echo "<div id='area-lines-dua' style='margin-top: 4px;'></div>";
echo "</form>";
if ($_REQUEST['mode'] == 'edit') {
    echo "<div id='area-edit' class='data_box' style='margin-top: 4px; display: none;'></div>";
    echo "<div class='area-button' id='master-button'>";
    echo "<input type='button' value='Simpan Dokumen' onclick=\"xajax_saveRWOS(xajax.getFormValues('frmRWOS'));\">";
    echo "<input type='button' value='Batal' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}&pkey[m_prod_slit_id]={$data['m_prod_slit_id']}'\">";
    echo "</div>";
    echo "<div style='text-align: right; margin-top: 10px;'>{$mandatory} = Wajib diisi</div>";
} elseif ($_REQUEST['mode'] == 'editH') {
    echo "<div id='area-edit' class='data_box' style='margin-top: 4px; display: none;'></div>";
    echo "<div class='area-button' id='master-button'><table width='100%'><tr>";
    echo "<td width='50%'><input type='button' value='Simpan Dokumen' onclick=\"xajax_updateRWOS(xajax.getFormValues('frmRWOS'));\">";
    echo "<input type='button' value='Batal' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}&pkey[m_prod_slit_id]={$data['m_prod_slit_id']}'\"></td>";
    //echo "<td width='50%' align='right'><input type='button' value='Input Nomor Coil RAW' onclick=\"xajax_editFormDua();\"></td>";
    echo "</tr></table></div>";
    echo "<div style='text-align: right; margin-top: 10px;'>{$mandatory} = Wajib diisi</div>";
}else {
    echo "<div class='area-button'>";
    echo "<input type='button' value='Kembali' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}'\">";
    echo "<input type='button' value='Edit' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}&pkey[m_prod_slit_id]={$data['m_prod_slit_id']}&mode=editH'\">";
    echo "<input type='button' value='Cetak' onclick=\"window.location = 'report.php?path=/reports/SPS/Bmbj&param[REPORT_ID_PENERIMAAN]={$data['m_prod_slit_id']}&type=docx&fname={$data['document_no']}'\">";
    echo "</div>";
}

?>
<script type="text/javascript">
<!--
function setWorkOrder(m_wo_slit_id, document_no, raw_id, raw_name) {
    var hid_m_wo_slit_id = document.getElementById('m_wo_slit_id');
    var hid_m_product_id = document.getElementById('m_product_id');
    var txt_document_no = document.getElementById('work_order');
    var txt_product_name = document.getElementById('product_name');
    var txt_qty_raw = document.getElementById('quantity_raw');
    hid_m_wo_slit_id.value = m_wo_slit_id;
    hid_m_product_id.value = raw_id;
    txt_document_no.value = document_no;
    txt_product_name.value = raw_name;
    txt_qty_raw.value = 0;
    xajax_workOrderLinesForm(m_wo_slit_id);
    //xajax_workOrderCoilForm(m_wo_slit_id);
}

// function setCoil(m_coil_id, no_coil, no_lot, weight) {
//     var hid_m_coil_id = document.getElementById('m_coil_id');
//     var txt_no_coil = document.getElementById('no_coil');
//     var txt_no_lot = document.getElementById('no_lot');
//     var txt_weight = document.getElementById('weight');
//     hid_m_coil_id.value = m_coil_id;
//     txt_no_coil.value = no_coil;
//     txt_no_lot.value = no_lot;
//     txt_weight.value = weight;
// }

<?php if ($_REQUEST['mode'] == 'edit' OR $_REQUEST['mode'] == 'editH') { ?>
$(function() {
    $("#production_date").datepicker({dateFormat: '<?php echo $APP_DATE_FORMAT_JAVA; ?>'});
});
<?php } ?>

xajax_showLines('<?php echo $data['m_prod_slit_id'] ?>', '<?php echo $_REQUEST['mode']; ?>');
//xajax_showLinesDua('<?php echo $data['m_prod_slit_id'] ?>', '<?php echo $_REQUEST['mode']; ?>');

//-->
</script>