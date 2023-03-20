<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.org>
 * Nov 23, 2013 11:35:10 PM
 */

// load data to session
if ($_REQUEST['mode'] == 'edit' && $_SESSION[$APP_ID]['rwop']['m_prod_slit_id'] == $_REQUEST['pkey']['m_prod_slit_id']) {
    $data = $_SESSION[$APP_ID]['rwop'];
} else {
    $data = npl_fetch_table(
            "SELECT m_prod_slit.*, m_wo_pipa_id, m_wo_pipa.document_no wo, CONCAT(spec, ' - ', thickness, ' - ', od) ukuran_coil
            FROM m_prod_slit 
            JOIN m_wo_pipa ON (m_prod_slit.m_wo_slit_id = m_wo_pipa.m_wo_pipa_id)
            WHERE m_prod_slit_id = '{$_REQUEST['pkey']['m_prod_slit_id']}'");
    $rsx = mysql_query(
            "SELECT m_prod_slit_line.* , m_product.* , warehouse_name, no_coil, no_lot, m_wo_pipa_line_id, m_wo_pipa_line.m_product_id m_product_pipa, 
            m_product.product_code product_code_pipa, m_product.product_name product_name_pipa, m_product.description description_pipa  
            FROM m_prod_slit_line
            JOIN m_warehouse USING (m_warehouse_id)
            JOIN m_coil USING (m_coil_id)
            JOIN m_wo_pipa_line ON (m_prod_slit_line.m_wo_slit_line_id = m_wo_pipa_line.m_wo_pipa_line_id)
            JOIN m_product ON (m_wo_pipa_line.m_product_id = m_product.m_product_id)
            WHERE m_prod_slit_id = '{$_REQUEST['pkey']['m_prod_slit_id']}'",
            $APP_CONNECTION);
    $data['lines'] = array();
    while ($dtx = mysql_fetch_array($rsx, MYSQL_ASSOC)) $data['lines'][] = $dtx;
    mysql_free_result($rsx);
    $_SESSION[$APP_ID]['rwop'] = $data;
}

if ($_REQUEST['mode'] != 'edit' AND $_REQUEST['mode'] != 'editH') {
    $readonly = ' readonly="readonly"';
}else {
    $select_partner = "<img onclick=\"popupReference('business-partner');\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'>";
    $select_wo = "<img onclick=\"popupReference('work-order-pipa');\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'>";
}


if ($_SESSION[$GLOBALS['APP_ID']]['trx.rwop']['error']) {
    echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['trx.rwop']['error']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['trx.rwop']['error']);
}

if ($_SESSION[$GLOBALS['APP_ID']]['trx.rwop']['info']) {
    echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['trx.rwop']['info']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['trx.rwop']['info']);
}

$data['production_date'] = empty($data['production_date']) ? date($APP_DATE_FORMAT) : $data['production_date'];

echo "<div class='data_box'>";
echo "<form id='frmrwop'>";
echo "<input type='hidden' id='m_prod_slit_id' name='m_prod_slit_id' value='{$data['m_prod_slit_id']}'>";
echo "<input type='hidden' id='m_wo_pipa_id' name='m_wo_pipa_id' value='{$data['m_wo_pipa_id']}'>";
echo "<input type='hidden' id='production_date_a' name='production_date_a' value=\"" . (cgx_emptydate($data['production_date']) ? '' : date($APP_DATE_FORMAT, strtotime($data['production_date']))) . "\">";
echo "<table width='100%'>";
echo "<tr>";
echo "<td width='12%'>Nomor Dokumen</td>";
echo "<td width='33%'><input " . ($data['document_no'] ? 'readonly="readonly"' : 'disabled') . " name='document_no' type='text' size='15' value=\"" . ($data['document_no'] ? $data['document_no'] : $autonumber) . "\"></td>";
echo "<td width='10%'></td>";
echo "<td width='12%'>Ukuran Coil</td>";
echo "<td width='33%'><input readonly='readonly' type='text' id='ukuran_coil' size='40' value=\"{$data['ukuran_coil']}\"></td>";
echo "</tr><tr>";
echo "<td>No. W/O</td>";
echo "<td><input readonly='readonly' type='text' id='wo' size='25' value=\"{$data['wo']}\">{$select_wo}</td>";
echo "<td></td>";
echo "<td>Tanggal {$mandatory}</td>";
echo "<td><input{$readonly} name='production_date' id='production_date' type='text' size='10' value=\"" . (cgx_emptydate($data['production_date']) ? '' : date($APP_DATE_FORMAT, strtotime($data['production_date']))) . "\"></td>";
echo "</tr>";
echo "</table>";
echo "</form>";
echo "</div>";

echo "<div id='area-lines' style='margin-top: 4px;'></div>";
if ($_REQUEST['mode'] == 'edit') {
    echo "<div id='area-edit' class='data_box' style='margin-top: 4px; display: none;'></div>";
    echo "<div class='area-button' id='master-button'>";
    echo "<input type='button' value='Simpan Dokumen' onclick=\"xajax_saverwop(xajax.getFormValues('frmrwop'));\">";
    echo "<input type='button' value='Tambah Baris' onclick=\"xajax_editForm();\">";
    echo "<input type='button' value='Batal' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}&pkey[m_prod_slit_id]={$data['m_prod_slit_id']}'\">";
    echo "</div>";
    echo "<div style='text-align: right; margin-top: 10px;'>{$mandatory} = Wajib diisi</div>";
}elseif ($_REQUEST['mode'] == 'editH') {
    echo "<div id='area-edit' class='data_box' style='margin-top: 4px; display: none;'></div>";
    echo "<div class='area-button' id='master-button'>";
    echo "<input type='button' value='Simpan Dokumen' onclick=\"xajax_saverwop(xajax.getFormValues('frmrwop'));\">";
    echo "<input type='button' value='Batal' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}&pkey[m_prod_slit_id]={$data['m_prod_slit_id']}'\">";
    echo "</div>";
    echo "<div style='text-align: right; margin-top: 10px;'>{$mandatory} = Wajib diisi</div>";
}elseif (user() == 2 OR user() == 46 OR user() == 51 OR user() == 55) {
    echo "<div class='area-button'>";
    echo "<input type='button' value='Kembali' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}'\">";
    echo "<input type='button' value='Edit' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}&pkey[m_prod_slit_id]={$data['m_prod_slit_id']}&mode=editH'\">";
    echo "<input type='button' value='Cetak' onclick=\"window.location = 'report.php?path=/reports/SPS/Bukti_Barang_Masuk&param[REPORT_ID_PENERIMAAN]={$data['m_prod_slit_id']}&type=docx&fname={$data['document_no']}'\">";
    echo "</div>";
}else {
    echo "<div class='area-button'>";
    echo "<input type='button' value='Kembali' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}'\">";
    echo "<input type='button' value='Cetak' onclick=\"window.location = 'report.php?path=/reports/SPS/Bukti_Barang_Masuk&param[REPORT_ID_PENERIMAAN]={$data['m_prod_slit_id']}&type=docx&fname={$data['document_no']}'\">";
    echo "</div>";
}

?>
<script type="text/javascript">
<!--

function setWorkOrder(m_wo_pipa_id, document_no, order_date, spec, thickness, od) {
    var hid_m_wo_pipa_id = document.getElementById('m_wo_pipa_id');
    var txt_wo = document.getElementById('wo');
    var txt_ukuran_coil = document.getElementById('ukuran_coil');
    hid_m_wo_pipa_id.value = m_wo_pipa_id;
    txt_wo.value = document_no;
    txt_ukuran_coil.value = spec + ' - ' + thickness + ' - ' + od; 
}

function setWorkOrderDetail(m_wo_pipa_line_id, m_product_id, product_code, product_name, description) {
    var txt_code = document.getElementById('product_code_pipa');
    var txt_name = document.getElementById('product_name_pipa');
    var txt_desc = document.getElementById('description_pipa');
    var hid_id = document.getElementById('m_product_pipa');
    var hid_wo_id = document.getElementById('m_wo_pipa_line_id');
    txt_code.value = product_code;
    txt_name.value = product_name;
    txt_desc.value = description;
    hid_id.value = m_product_id;
    hid_wo_id.value = m_wo_pipa_line_id;
}

function setWorkOrderNocoil(m_coil_slit_id, m_coil_id, no_coil, no_lot){
    var txt_no_coil = document.getElementById('no_coil');
    var txt_no_lot = document.getElementById('no_lot');
    var hid_coil_slit_id = document.getElementById('m_coil_slit_id');
    var hid_coil_id = document.getElementById('m_coil_id');
    txt_no_coil.value = no_coil;
    txt_no_lot.value = no_lot;
    hid_coil_slit_id.value = m_coil_slit_id;
    hid_coil_id.value = m_coil_id;
}

<?php if ($_REQUEST['mode'] == 'edit' OR $_REQUEST['mode'] == 'editH') { ?>
$(function() {
    $("#production_date").datepicker({dateFormat: '<?php echo $APP_DATE_FORMAT_JAVA; ?>'});
});
<?php } ?>

xajax_showLines('<?php echo $data['m_prod_slit_id'] ?>', '<?php echo $_REQUEST['mode']; ?>');

//-->
</script>