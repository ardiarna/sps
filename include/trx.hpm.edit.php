<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.org>
 * Nov 23, 2013 11:35:10 PM
 */

if ($_REQUEST['mode'] == 'edit' && $_SESSION[$APP_ID]['hpm']['m_production_id'] == $_REQUEST['pkey']['m_production_id']) {
    $data = $_SESSION[$APP_ID]['hpm'];
} else {
    $data = npl_fetch_table(
        "SELECT m_production.*, m_work_order.document_no wo, c_order.document_no so, remark, reference_no, partner_name, machine_name
        FROM m_production
        JOIN m_work_order USING (m_work_order_id) 
        LEFT JOIN m_machine ON (m_production.m_machine_id = m_machine.m_machine_id)
        LEFT JOIN (SELECT DISTINCT m_work_order_id, c_order_id FROM m_work_order_line) wol USING (m_work_order_id) 
        LEFT JOIN c_order USING (c_order_id) 
        LEFT JOIN c_bpartner USING (c_bpartner_id)
        WHERE m_production_id = '{$_REQUEST['pkey']['m_production_id']}'");
    $rsx = mysql_query(
            "SELECT m_production_line.* , m_product.*, COALESCE(remark,c_forecast.document_no) remark, reference_no
            FROM m_production_line
            JOIN m_work_order_line USING (m_work_order_line_id)
            LEFT JOIN c_order USING (c_order_id)
            LEFT JOIN c_bpartner USING (c_bpartner_id)
            LEFT JOIN c_forecast ON (m_work_order_line.c_forecast_id=c_forecast.c_forecast_id)
            JOIN m_product ON (m_work_order_line.m_product_id=m_product.m_product_id)
            JOIN m_product mat ON (m_work_order_line.m_product_material=mat.m_product_id)
            WHERE m_production_id = '{$_REQUEST['pkey']['m_production_id']}'",
            $APP_CONNECTION);
    $data['lines'] = array();
    while ($dtx = mysql_fetch_array($rsx, MYSQL_ASSOC)) $data['lines'][] = $dtx;
    mysql_free_result($rsx);
    $_SESSION[$APP_ID]['hpm'] = $data;
}

if ($_REQUEST['mode'] != 'edit' AND $_REQUEST['mode'] != 'editH') {
    $readonly = ' readonly="readonly"';
    $disabled = ' disabled';
} else {
    $select_wo = "<img onclick=\"popupReference('work-order');\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'>";
}

if ($_REQUEST['mode'] == 'editH') {
    $select_wo = "";
}

if ($_SESSION[$GLOBALS['APP_ID']]['trx.hpm']['error']) {
    echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['trx.hpm']['error']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['trx.hpm']['error']);
}

if ($_SESSION[$GLOBALS['APP_ID']]['trx.hpm']['info']) {
    echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['trx.hpm']['info']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['trx.hpm']['info']);
}

$data['m_production_date'] = empty($data['m_production_date']) ? date($APP_DATE_FORMAT) : $data['m_production_date'];
    
echo "<form id='frmHPM'>";
echo "<div class='data_box'>";
echo "<input type='hidden' id='m_work_order_id' name='m_work_order_id' value='{$data['m_work_order_id']}'>";
echo "<input type='hidden' id='m_production_id' name='m_production_id' value='{$data['m_production_id']}'>";
echo "<table width='100%'>";
echo "<tr>";
echo "<td width='10%'>Nomor Dokumen</td>";
echo "<td width='32%'><input " . ($data['document_no'] ? 'readonly="readonly"' : 'disabled') . " name='document_no' type='text' size='20' value=\"" . ($data['document_no'] ? $data['document_no'] : $autonumber) . "\"></td>";
echo "<td width='3%'></td>";
echo "<td width='10%'>No. SC</td>";
echo "<td width='38%'><input readonly='readonly' type='text' id='remark' size='20' value=\"{$data['remark']}\"></td>";
echo "</tr>";
echo "<tr>";
echo "<td>Work Order</td>";
echo "<td><input readonly='readonly' type='text' id='work_order' size='20' value=\"{$data['wo']}\">{$select_wo}</td>";
echo "<td></td>";
echo "<td>No. PO</td>";
echo "<td><input readonly='readonly' type='text' id='reference_no' size='30' value=\"{$data['reference_no']}\"></td>";
echo "</tr>";
echo "<tr>";
echo "<td>Mesin {$mandatory}</td>";
echo "<td>" . cgx_filter('m_machine_id', "SELECT m_machine_id, machine_name FROM m_machine WHERE " . org_filter_master() . " ORDER BY machine_name", $data['m_machine_id'], FALSE, $disabled) . "</td>";
echo "<td></td>";
echo "<td>Customer</td>";
echo "<td><input readonly='readonly' type='text' id='partner_name' size='35' value=\"{$data['partner_name']}\"></td>";
echo "</tr>";
echo "<tr>";
echo "<td>Tanggal {$mandatory}</td>";
echo "<td><input{$readonly} name='production_date' id='production_date' type='text' size='10' value=\"" . (cgx_emptydate($data['production_date']) ? '' : date($APP_DATE_FORMAT, strtotime($data['production_date']))) . "\"></td>";
echo "<td></td>";
echo "<td>NIK</td>";
echo "<td><input{$readonly} type='text' name='nik' size='15' value=\"{$data['nik']}\"></td>";
echo "</tr>";
echo "</table>";
echo "</div>";
echo "<div id='area-lines' style='margin-top: 4px;'></div>";
echo "</form>";
if ($_REQUEST['mode'] == 'edit') {
    echo "<div id='area-edit' class='data_box' style='margin-top: 4px; display: none;'></div>";
    echo "<div class='area-button' id='master-button'>";
    echo "<input type='button' value='Simpan Dokumen' onclick=\"xajax_saveHPM(xajax.getFormValues('frmHPM'));\">";
    echo "<input type='button' value='Batal' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}&pkey[m_production_id]={$data['m_production_id']}'\">";
    echo "</div>";
    echo "<div style='text-align: right; margin-top: 10px;'>{$mandatory} = Wajib diisi</div>";
} elseif ($_REQUEST['mode'] == 'editH') {
    echo "<div id='area-edit' class='data_box' style='margin-top: 4px; display: none;'></div>";
    echo "<div class='area-button' id='master-button'>";
    echo "<input type='button' value='Simpan Dokumenn' onclick=\"xajax_updateHPM(xajax.getFormValues('frmHPM'));\">";
    echo "<input type='button' value='Batal' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}&pkey[m_production_id]={$data['m_production_id']}'\">";
    echo "</div>";
    echo "<div style='text-align: right; margin-top: 10px;'>{$mandatory} = Wajib diisi</div>";
}else {
    echo "<div class='area-button'>";
    echo "<input type='button' value='Kembali' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}'\">";
    echo "<input type='button' value='Edit' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}&pkey[m_production_id]={$data['m_production_id']}&mode=editH'\">";
    echo "<input type='button' value='Cetak' onclick=\"window.location = 'report.php?path=/reports/SPS/Bmbj&param[REPORT_ID_PENERIMAAN]={$data['m_production_id']}&type=docx&fname={$data['document_no']}'\">";
    echo "</div>";
}

?>
<script type="text/javascript">
<!--
function setWorkOrder(m_work_order_id, document_no, remark, reference_no, partner_name) {
    var hid_m_work_order_id = document.getElementById('m_work_order_id');
    var txt_document_no = document.getElementById('work_order');
    var txt_remark = document.getElementById('remark');
    var txt_reference_no = document.getElementById('reference_no');
    var txt_partner_name = document.getElementById('partner_name');
    hid_m_work_order_id.value = m_work_order_id;
    txt_document_no.value = document_no;
    txt_remark.value = remark;
    txt_reference_no.value = reference_no;
    txt_partner_name.value = partner_name;
    xajax_workOrderLinesForm(m_work_order_id);
}

<?php if ($_REQUEST['mode'] == 'edit' OR $_REQUEST['mode'] == 'editH') { ?>
$(function() {
    $("#production_date").datepicker({dateFormat: '<?php echo $APP_DATE_FORMAT_JAVA; ?>'});
});
<?php } ?>

xajax_showLines('<?php echo $data['m_production_id'] ?>', '<?php echo $_REQUEST['mode']; ?>');

//-->
</script>