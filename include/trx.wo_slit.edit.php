<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.org>
 * Nov 23, 2013 11:35:10 PM
 */

if ($_REQUEST['mode'] == 'edit' && $_SESSION[$APP_ID]['wo_slit']['m_wo_slit_id'] == $_REQUEST['pkey']['m_wo_slit_id']) {
    $data = $_SESSION[$APP_ID]['wo_slit'];
} else {
    /*
    $data = npl_fetch_table(
        "SELECT m_wo_slit.*, product_code, spec, thickness, CONCAT(thickness, ' - ', od) ukuran_mat, partner_name  
            FROM m_wo_slit 
            JOIN m_product ON (m_wo_slit.m_product_id=m_product.m_product_id) 
            JOIN c_bpartner ON (m_wo_slit.c_bpartner_id=c_bpartner.c_bpartner_id)
            WHERE m_wo_slit_id = '{$_REQUEST['pkey']['m_wo_slit_id']}'");
    $rsx = mysql_query(
            "SELECT m_wo_slit_line.*, m_product.m_product_id m_product_slit, m_product.product_code product_code_slit,  m_product.spec spec_slit, 
            CONCAT(m_product.thickness, ' - ', m_product.od) ukuran_slit
            FROM m_wo_slit_line
            JOIN m_product USING (m_product_id)
            WHERE m_wo_slit_id = '{$_REQUEST['pkey']['m_wo_slit_id']}'
            ORDER BY m_wo_slit_line_id",
            $APP_CONNECTION);
    */
    $data = npl_fetch_table(
        "SELECT m_wo_slit.*, product_code, spec, thickness, CONCAT(thickness, ' - ', od) ukuran_mat, m_bkpc.document_no no_bkpc  
            FROM m_wo_slit 
            LEFT JOIN m_bkpc ON(m_wo_slit.m_bkpc_id=m_bkpc.m_bkpc_id)
            JOIN m_product ON (m_wo_slit.m_product_id=m_product.m_product_id) 
            WHERE m_wo_slit_id = '{$_REQUEST['pkey']['m_wo_slit_id']}'");
    $rsx = mysql_query(
            "SELECT m_wo_slit_line.*, m_product.m_product_id m_product_slit, m_product.product_code product_code_slit,  m_product.spec spec_slit, 
            CONCAT(m_product.thickness, ' - ', m_product.od) ukuran_slit
            FROM m_wo_slit_line
            JOIN m_product USING (m_product_id)
            WHERE m_wo_slit_id = '{$_REQUEST['pkey']['m_wo_slit_id']}'
            ORDER BY m_wo_slit_line_id",
            $APP_CONNECTION);        
    $data['lines'] = array();
    while ($dtx = mysql_fetch_array($rsx, MYSQL_ASSOC)) $data['lines'][] = $dtx;
    mysql_free_result($rsx);
    $_SESSION[$APP_ID]['wo_slit'] = $data;

     $rsy = mysql_query(
            "SELECT m_coil.*
            FROM m_coil
            WHERE m_wo_slit_id = '{$_REQUEST['pkey']['m_wo_slit_id']}'
            ORDER BY m_coil_id",
            $APP_CONNECTION);
    $data['linesdua'] = array();
    while ($dty = mysql_fetch_array($rsy, MYSQL_ASSOC)) $data['linesdua'][] = $dty;
    mysql_free_result($rsy);
    $_SESSION[$APP_ID]['wo_slit'] = $data;
}

if ($_REQUEST['mode'] != 'edit' AND $_REQUEST['mode'] != 'editH') {
    $readonly = ' readonly="readonly"';
    $disabled = ' disabled';
} else {
    $select_product = "<img onclick=\"popupReferenceAmbil('bkpc','&p1=1');\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'>";    
    $select_partner = "<img onclick=\"popupReference('business-partner-c');\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'>";
}

if ($_REQUEST['mode'] == 'editH') {
    $select_product = "";
}

if ($_SESSION[$GLOBALS['APP_ID']]['trx.wo_slit']['error']) {
    echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['trx.wo_slit']['error']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['trx.wo_slit']['error']);
}

if ($_SESSION[$GLOBALS['APP_ID']]['trx.wo_slit']['info']) {
    echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['trx.wo_slit']['info']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['trx.wo_slit']['info']);
}

$data['order_date'] = empty($data['order_date']) ? date($APP_DATE_FORMAT) : $data['order_date'];

echo "<form id='frmWO'>";
echo "<div class='data_box'>";
echo "<input type='hidden' id='m_product_id' name='m_product_id' value='{$data['m_product_id']}'>";
echo "<input type='hidden' id='thickness' name='thickness' value='{$data['thickness']}'>";
echo "<input type='hidden' id='m_bkpc_id' name='m_bkpc_id' value='{$data['m_bkpc_id']}'>";
echo "<input type='hidden' id='c_bpartner_id' name='c_bpartner_id' value='{$data['c_bpartner_id']}'>";
echo "<input type='hidden' id='m_wo_slit_id' name='m_wo_slit_id' value='{$data['m_wo_slit_id']}'>";
echo "<input type='hidden' id='order_date_a' name='order_date_a' value=\"" . (cgx_emptydate($data['order_date']) ? '' : date($APP_DATE_FORMAT, strtotime($data['order_date']))) . "\">";
echo "<table width='100%'>";
echo "<tr>";
echo "<td width='12%'>Nomor Dokumen</td>";
echo "<td width='30%'><input{$readonly} name='document_no' type='text' size='20' value=\"{$data['document_no']}\"></td>";
echo "<td width='6%'></td>";
echo "<td width='15%'>No. BKPC {$mandatory}</td>";
echo "<td width='37%'><input readonly='readonly' type='text' id='no_bkpc' size='20' value=\"{$data['no_bkpc']}\">{$select_product}</td>";
echo "</tr>";
echo "<tr>";
echo "<td>Tanggal {$mandatory}</td>";
echo "<td><input{$readonly} name='order_date' id='order_date' type='text' size='10' value=\"" . (cgx_emptydate($data['order_date']) ? '' : date($APP_DATE_FORMAT, strtotime($data['order_date']))) . "\"></td>";
echo "<td></td>";
echo "<td>Ukuran Material</td>";
echo "<td><input readonly='readonly' type='text' id='ukuran_mat' size='30' value=\"{$data['ukuran_mat']}\"></td>";
echo "</tr>";
echo "<tr>";
echo "<td>Jumlah (Pcs)</td>";
echo "<td><input readonly='readonly' type='text' id='quantity' name='quantity' size='10' value=\"{$data['quantity']}\" style='text-align: right;'></td>";
echo "<td></td>";
echo "<td>Spec Material</td>";
echo "<td><input readonly='readonly' type='text' id='spec' name='spec' size='30' value=\"{$data['spec']}\"></td>";
echo "</tr>";
echo "<tr>";
echo "<td>Berat (Kg)</td>";
echo "<td><input readonly='readonly' type='text' id='weight_raw' name='weight_raw' size='10' value=\"{$data['weight']}\" style='text-align: right;'></td>";
echo "<td></td>";
echo "<td>Lebar Aktual {$mandatory}</td>";
echo "<td><input{$readonly} id='width_actual' name='width_actual' type='text' size='10' value=\"{$data['width_actual']}\" style='text-align: right;'></td>";
echo "</tr>";
echo "<tr>";
echo "<td>Customer {$mandatory}</td>";
//echo "<td><input readonly='readonly' type='text' id='partner_name' name='partner_name' size='30' value=\"{$data['partner_name']}\">{$select_partner}</td>";
echo "<td><input {$readonly} type='text' id='partner_name' name='partner_name' size='50' value=\"{$data['partner']}\"></td>";
echo "<td></td>";
echo "<td>Scrap {$mandatory}</td>";
echo "<td><input {$readonly} name='scrap' type='text' size='10' value=\"{$data['scrap']}\" style='text-align: right;'></td>";
echo "</tr>";
echo "</table>";
echo "</div>";
echo "<div id='area-lines' style='margin-top: 4px;'></div>";
echo "<div id='area-lines-dua' style='margin-top: 4px;'></div>";
echo "</form>";
if ($_REQUEST['mode'] == 'edit') {
    echo "<div id='area-edit' class='data_box' style='margin-top: 4px; display: none;'></div>";
    echo "<div class='area-button' id='master-button'>";
    echo "<input type='button' value='Simpan Dokumen' onclick=\"xajax_saveWO(xajax.getFormValues('frmWO'));\">";
    echo "<input type='button' value='Tambah Baris' onclick=\"xajax_editForm();\">";
    echo "<input type='button' value='Batal' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}&pkey[m_wo_slit_id]={$data['m_wo_slit_id']}'\">";
    echo "</div>";
    echo "<div style='text-align: right; margin-top: 10px;'>{$mandatory} = Wajib diisi</div>";
} elseif ($_REQUEST['mode'] == 'editH') {
    echo "<div id='area-edit' class='data_box' style='margin-top: 4px; display: none;'></div>";
    echo "<div class='area-button' id='master-button'>";
    echo "<input type='button' value='Simpan Dokumenn' onclick=\"xajax_updateWO(xajax.getFormValues('frmWO'));\">";
    echo "<input type='button' value='Tambah Baris' onclick=\"xajax_editForm();\">";
    echo "<input type='button' value='Input Nomor Coil' onclick=\"xajax_editFormDua();\"></td>";
    echo "<input type='button' value='Batal' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}&pkey[m_wo_slit_id]={$data['m_wo_slit_id']}'\">";
    echo "</div>";
    echo "<div style='text-align: right; margin-top: 10px;'>{$mandatory} = Wajib diisi</div>";
} else {
    echo "<div class='area-button'>";
    echo "<input type='button' value='Kembali' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}'\">";
    echo "<input type='button' value='Edit' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}&pkey[m_wo_slit_id]={$data['m_wo_slit_id']}&mode=editH'\">";
    echo "<input type='button' value='Cetak' onclick=\"window.location = 'report.php?path=/reports/SPS/WO_Slit&param[REPORT_ID_WO_SLIT]={$data['m_wo_slit_id']}&type=docx&fname={$data['document_no']}'\">";
    echo "</div>";
}

?>
<script type="text/javascript">
<!--

function setBusinessPartner(id, name) {
    var txt_name = document.getElementById('partner_name');
    var hid_id = document.getElementById('c_bpartner_id');
    txt_name.value = name;
    hid_id.value = id;
}

function setProduct(id_bkpc, document_no, id_product, code, name, desc, category, spec, thick, od) {
    var txt_no_bkpc = document.getElementById('no_bkpc');
    var txt_ukuran = document.getElementById('ukuran_mat');
    var txt_spec = document.getElementById('spec');
    var txt_width_act = document.getElementById('width_actual');
    var hid_id_bkpc = document.getElementById('m_bkpc_id');
    var hid_id_product = document.getElementById('m_product_id');
    var hid_thic = document.getElementById('thickness');
    txt_no_bkpc.value = document_no;
    txt_ukuran.value = thick +' - ' + od;
    txt_spec.value = spec;
    txt_width_act.value = od;
    hid_id_bkpc.value = id_bkpc;
    hid_id_product.value = id_product;
    hid_thic.value = thick;
    xajax_mCoilLinesForm(id_bkpc);
}

function setHasilSlit(id, code, name, spec, thick, od) {
    var txt_code = document.getElementById('product_code_slit');
    var txt_ukuran = document.getElementById('ukuran_slit');
    var txt_spec = document.getElementById('spec_slit');
    var hid_id = document.getElementById('m_product_slit');
    txt_code.value = code;
    txt_ukuran.value = thick +' - ' + od;
    txt_spec.value = spec;
    hid_id.value = id;
}

function setHasilPipa(id, code, name, spec, od, thick, length) {
    var txt_ukuran = document.getElementById('ukuran_lp');
    var hid_id = document.getElementById('m_product_lp');
    txt_ukuran.value = od +' - ' + thick +' - ' + length;
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

<?php if ($_REQUEST['mode'] == 'edit' OR $_REQUEST['mode'] == 'editH') { ?>
$(function() {
    $("#order_date").datepicker({dateFormat: '<?php echo $APP_DATE_FORMAT_JAVA; ?>'});
});
<?php } ?>

xajax_showLines('<?php echo $data['m_wo_slit_id'] ?>', '<?php echo $_REQUEST['mode']; ?>');
xajax_showLinesDua('<?php echo $data['m_wo_slit_id'] ?>', '<?php echo $_REQUEST['mode']; ?>');

//-->
</script>