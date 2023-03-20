<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.org>
 * Nov 23, 2013 11:35:10 PM
 */

if ($_REQUEST['mode'] == 'edit' && $_SESSION[$APP_ID]['bkpc']['m_bkpc_id'] == $_REQUEST['pkey']['m_bkpc_id']) {
    $data = $_SESSION[$APP_ID]['bkpc'];
} else {
    $data = npl_fetch_table("SELECT * FROM m_bkpc WHERE m_bkpc_id = '{$_REQUEST['pkey']['m_bkpc_id']}' ");
    $rsx = mysql_query(" SELECT m_bkpc_line.*, no_coil, no_lot, product_code, spec, CONCAT(thickness, ' x ', od, ' x C') as ukuran_mat 
        FROM m_bkpc_line 
        JOIN m_coil ON(m_bkpc_line.m_coil_id = m_coil.m_coil_id)
        JOIN m_product ON (m_coil.m_product_id = m_product.m_product_id)    
        WHERE m_bkpc_line.m_bkpc_id = '{$_REQUEST['pkey']['m_bkpc_id']}'
        ORDER BY m_bkpc_line_id",
        $APP_CONNECTION);        
    $data['lines'] = array();
    while ($dtx = mysql_fetch_array($rsx, MYSQL_ASSOC)) $data['lines'][] = $dtx;
    mysql_free_result($rsx);
    $_SESSION[$APP_ID]['bkpc'] = $data;
}

if ($_REQUEST['mode'] != 'edit') {
    $readonly = ' readonly="readonly"';
    $disabled = ' disabled';
} else {
    $select_product = "<img onclick=\"popupReferenceAmbil('product_category','&p1=R');\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'>";
    $select_partner = "<img onclick=\"popupReference('business-partner-c');\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'>";
}


if ($_SESSION[$GLOBALS['APP_ID']]['trx.bkpc']['error']) {
    echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['trx.bkpc']['error']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['trx.bkpc']['error']);
}

if ($_SESSION[$GLOBALS['APP_ID']]['trx.bkpc']['info']) {
    echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['trx.bkpc']['info']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['trx.bkpc']['info']);
}

$data['bkpc_date'] = empty($data['bkpc_date']) ? date($APP_DATE_FORMAT) : $data['bkpc_date'];

echo "<form id='frmBKPC'>";
echo "<div class='data_box'>";
echo "<input type='hidden' id='m_bkpc_id' name='m_bkpc_id' value='{$data['m_bkpc_id']}'>";
echo "<input type='hidden' id='bkpc_date_a' name='bkpc_date_a' value=\"" . (cgx_emptydate($data['bkpc_date']) ? '' : date($APP_DATE_FORMAT, strtotime($data['bkpc_date']))) . "\">";
echo "<table border='0' width='100%'>";

echo "<tr>";
echo    "<td width='12%'>D / O</td>";
echo    "<td width='30%'><input{$readonly} name='do_no' type='text' size='30' value=\"{$data['do_no']}\"></td>";
echo    "<td width='6%'></td>";
echo    "<td width='15%'>NO. {$mandatory}</td>";
echo    "<td width='30%'><input{$readonly} name='document_no' type='text' size='20' value=\"{$data['document_no']}\"></td>";
echo "</tr>";
echo "<tr>";
echo    "<td>S / J {$mandatory}</td>";
echo    "<td><input{$readonly} name='sj_no' type='text' size='30' value=\"{$data['sj_no']}\"></td>";
echo    "<td></td>";
echo    "<td>Tanggal {$mandatory}</td>";
echo    "<td><input{$readonly} name='bkpc_date' id='bkpc_date' type='text' size='10' value=\"" . (cgx_emptydate($data['bkpc_date']) ? '' : date($APP_DATE_FORMAT, strtotime($data['bkpc_date']))) . "\"></td>";
echo "</tr>";
echo "<tr>";
echo    "<td>Order Muat</td>";
echo    "<td><input{$readonly} type='text' name='order_muat' size='30' value=\"{$data['order_muat']}\"></td>";
echo    "<td></td>";
echo    "<td>Customer {$mandatory}</td>";
echo    "<td><input{$readonly} type='text' id='partner_name' name='partner_name' size='50' value=\"{$data['partner_name']}\"></td>";
echo "</tr>";
echo "<tr>";
echo    "<td>Kendaraan</td>";
echo    "<td><input{$readonly} type='text' name='kendaraan_no' size='30' value=\"{$data['kendaraan_no']}\" style='text-align: right;'></td>";
echo    "<td></td>";
echo    "<td>Jumlah (Pcs)</td>";
echo    "<td>"
.    "<input readonly='readonly' type='text' id='quantity' name='quantity' size='10' value=\"{$data['quantity']}\">&nbsp&nbsp&nbsp&nbsp"
.    "Berat (Kg) &nbsp&nbsp&nbsp&nbsp"
.    "<input readonly='readonly' type='text' id='weight_raw' name='weight_raw' size='10' value=\"{$data['weight']}\">"
. "</td>";
echo "</tr>";
echo "</table>";
echo "</div>";


echo "<div class='data_box'>";
echo "<input type='hidden' id='m_product_id_head' name='m_product_id' value='{$data['m_product_id']}'>";
echo "<table border='0' width='100%'>";
echo "<tr>";
echo    "<td width='12%'>Item Number</td>";
echo    "<td><input readonly='readonly' type='text' id='product_code' size='26' name='product_code' value=\"{$data['product_code']}\">{$select_product}</td>";
echo "</tr>";
echo "<tr>";
echo    "<td>Ukuran</td>";
echo    "<td><input readonly='readonly' type='text' id='ukuran_mat' size='30' value=\"{$data['ukuran_mat']}\"></td>";
echo "</tr>";
echo "</table>";
echo "</div>";

echo "<div id='area-lines' style='margin-top: 4px;'></div>";
echo "</form>";
if ($_REQUEST['mode'] == 'edit') {
    echo "<div id='area-edit' class='data_box' style='margin-top: 4px; display: none;'></div>";
    echo "<div class='area-button' id='master-button'>";
    echo "<input type='button' value='Simpan Dokumen' onclick=\"xajax_saveBKPC(xajax.getFormValues('frmBKPC'));\">";
    echo "<input type='button' value='Tambah Baris' onclick=\"xajax_editForm();\">";
    echo "<input type='button' value='Batal' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}&pkey[m_bkpc_id]={$data['m_bkpc_id']}'\">";
    echo "</div>";
    echo "<div style='text-align: right; margin-top: 10px;'>{$mandatory} = Wajib diisi</div>";
} else {
    echo "<div class='area-button'>";
    echo "<input type='button' value='Kembali' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}'\">";
    echo "<input type='button' value='Edit' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}&pkey[m_bkpc_id]={$data['m_bkpc_id']}&mode=edit'\">";
    echo "<input type='button' value='Cetak' onclick=\"window.location = 'report.php?path=/reports/SPS/BKPC_Slit&param[REPORT_ID_BKPC_SLIT]={$data['m_bkpc_id']}&type=docx&fname={$data['document_no']}'\">";
    echo "</div>";
}

?>
<script type="text/javascript">

function setBusinessPartner(id, name) {
    var txt_name = document.getElementById('partner_name');
    var hid_id = document.getElementById('c_bpartner_id');
    txt_name.value = name;
    hid_id.value = id;
}

function setProduct(id, code, name, desc, category, spec, thick, od) {
    var txt_product_code = document.getElementById('product_code');
    var txt_ukuran = document.getElementById('ukuran_mat');
    var hid_id = document.getElementById('m_product_id_head');
    txt_product_code.value = code;
    txt_ukuran.value = spec +' - ' + thick +' x ' + od + ' x C';
    hid_id.value = id;
}

function setCoil(m_coil_id, no_coil, no_lot, weight) {
    var hid_m_coil_id = document.getElementById('m_coil_id');
    var txt_no_coil = document.getElementById('no_coil');
    var txt_no_lot = document.getElementById('no_lot');
    var txt_weight = document.getElementById('weight');
    hid_m_coil_id.value = m_coil_id;
    txt_no_coil.value = no_coil;
    txt_no_lot.value = no_lot;
    txt_weight.value = weight;
}

<?php if ($_REQUEST['mode'] == 'edit') { ?>
$(function() {
    $("#bkpc_date").datepicker({dateFormat: '<?php echo $APP_DATE_FORMAT_JAVA; ?>'});
});
<?php } ?>

xajax_showLines('<?php echo $data['m_bkpc_id'] ?>', '<?php echo $_REQUEST['mode']; ?>');

//-->
</script>