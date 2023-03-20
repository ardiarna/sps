<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.org>
 * Nov 23, 2013 11:35:10 PM
 */

if ($_REQUEST['mode'] == 'edit' && $_SESSION[$APP_ID]['wo_pipa']['m_wo_pipa_id'] == $_REQUEST['pkey']['m_wo_pipa_id']) {
    $data = $_SESSION[$APP_ID]['wo_pipa'];
} else {
    $data = npl_fetch_table(
        "SELECT m_wo_pipa.*, CONCAT(od, ' x ', thickness) ukuran  
            FROM m_wo_pipa 
            WHERE m_wo_pipa_id = '{$_REQUEST['pkey']['m_wo_pipa_id']}'");
    $rsx = mysql_query(
            "SELECT 
                m_wo_pipa_line.* 
                ,product_code
                ,m_product.length AS length
                ,partner_name
                ,customer_code
                ,m_code_prod_lp.id AS id
                
            FROM 
            m_wo_pipa_line JOIN m_product ON (m_wo_pipa_line.m_product_id=m_product.m_product_id)
                      LEFT JOIN c_bpartner ON (m_wo_pipa_line.c_bpartner_id=c_bpartner.c_bpartner_id)
                      LEFT JOIN m_code_prod_lp ON (m_wo_pipa_line.no_product = m_code_prod_lp.id)
                      
            WHERE 
            m_wo_pipa_id = '{$_REQUEST['pkey']['m_wo_pipa_id']}'
            
            ORDER BY 
            m_wo_pipa_line_id",
            
                    $APP_CONNECTION);
    $data['lines'] = array();
    while ($dtx = mysql_fetch_array($rsx, MYSQL_ASSOC)) $data['lines'][] = $dtx;
    mysql_free_result($rsx);
    $_SESSION[$APP_ID]['wo_pipa'] = $data;

    $rsy = mysql_query(
            "SELECT m_wo_pipa_line_2.*, no_coil, no_lot, (m_wo_pipa_line_2.weight * m_wo_pipa_line_2.quantity) as weight_total 
            FROM m_wo_pipa_line_2
            JOIN m_coil_slit USING(m_coil_slit_id) 
            JOIN m_coil USING(m_coil_id) 
            WHERE m_wo_pipa_id = '{$_REQUEST['pkey']['m_wo_pipa_id']}'
            ORDER BY m_wo_pipa_line_2_id",
            $APP_CONNECTION);    $data['linesdua'] = array();
    while ($dty = mysql_fetch_array($rsy, MYSQL_ASSOC)) $data['linesdua'][] = $dty;
    mysql_free_result($rsy);
    $_SESSION[$APP_ID]['wo_pipa'] = $data;
}

if ($_REQUEST['mode'] != 'edit') {
    $readonly = ' readonly="readonly"';
    $disabled = ' disabled';
}

if ($_SESSION[$GLOBALS['APP_ID']]['trx.wo_pipa']['error']) {
    echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['trx.wo_pipa']['error']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['trx.wo_pipa']['error']);
}

if ($_SESSION[$GLOBALS['APP_ID']]['trx.wo_pipa']['info']) {
    echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['trx.wo_pipa']['info']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['trx.wo_pipa']['info']);
}

$data['order_date'] = empty($data['order_date']) ? date($APP_DATE_FORMAT) : $data['order_date'];

echo "<div class='data_box'>";
echo "<form id='frmWO'>";
echo "<input type='hidden' id='m_wo_pipa_id' name='m_wo_pipa_id' value='{$data['m_wo_pipa_id']}'>";
echo "<input type='hidden' id='order_date_a' name='order_date_a' value=\"" . (cgx_emptydate($data['order_date']) ? '' : date($APP_DATE_FORMAT, strtotime($data['order_date']))) . "\">";
echo "<table width='100%'>";
echo "<tr>";
echo "<td width='12%'>Nomor Dokumen</td>";
echo "<td width='30%'><input{$readonly} name='document_no' type='text' size='20' value=\"{$data['document_no']}\"></td>";
echo "<td width='6%'></td>";
echo "<td width='15%'>Size (OD x T) {$mandatory}</td>";
echo "<td width='37%'><input{$readonly} type='text' id='od' name='od' size='7' value=\"{$data['od']}\" style='text-align: right;'> x <input{$readonly} type='text' id='thickness' name='thickness' size='5' value=\"{$data['thickness']}\" style='text-align: right;'></td>";
echo "</tr>";
echo "<tr>";
echo "<td>Tanggal {$mandatory}</td>";
echo "<td><input{$readonly} name='order_date' id='order_date' type='text' size='10' value=\"" . (cgx_emptydate($data['order_date']) ? '' : date($APP_DATE_FORMAT, strtotime($data['order_date']))) . "\"></td>";
echo "<td></td>";
echo "<td>Spec</td>";
echo "<td><input{$readonly} type='text' name='spec' size='30' value=\"{$data['spec']}\"></td>";
echo "</tr>";
echo "<tr>";
echo "<td>No. Bon Pesanan </td>";
echo "<td><input{$readonly} type='text' name='no_bon' size='20' value=\"{$data['no_bon']}\"></td>";
echo "<td></td>";
echo "<td>Yield</td>";
echo "<td><input{$readonly} type='text' name='yield' size='7' value=\"{$data['yield']}\" style='text-align: right;'></td>";
echo "</tr>";
echo "<tr>";
echo "</table>";
echo "</form>";
echo "</div>";
echo "<div id='area-lines' style='margin-top: 4px;'></div>";
echo "<div id='area-lines-dua' style='margin-top: 4px;'></div>";
if ($_REQUEST['mode'] == 'edit') {
    echo "<div id='area-edit' class='data_box' style='margin-top: 4px; display: none;'></div>";
    echo "<div class='area-button' id='master-button'>";
    echo "<input type='button' value='Simpan Dokumen' onclick=\"xajax_saveWO(xajax.getFormValues('frmWO'));\">";
    echo "<input type='button' value='Tambah Baris' onclick=\"xajax_editForm();\">";
    echo "<input type='button' value='Input Nomor Coil' onclick=\"xajax_editFormDua();\"></td>";
    echo "<input type='button' value='Batal' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}&pkey[m_wo_pipa_id]={$data['m_wo_pipa_id']}'\">";
    echo "</div>";
    echo "<div style='text-align: right; margin-top: 10px;'>{$mandatory} = Wajib diisi</div>";
} else {
    echo "<div class='area-button'>";
    echo "<input type='button' value='Kembali' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}'\">";
    echo "<input type='button' value='Edit' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}&pkey[m_wo_pipa_id]={$data['m_wo_pipa_id']}&mode=edit'\">";
    echo "<input type='button' value='Cetak' onclick=\"window.location = 'report.php?path=/reports/SPS/WO_Pipa&param[REPORT_ID_WO_pipa]={$data['m_wo_pipa_id']}&type=docx&fname={$data['document_no']}'\">";
    echo "</div>";
}

?>
<script type="text/javascript">

function popupReferenceCustomerCodePipe(){
        var w = 1300;
        var h = 500;
        var l = (screen.width - w) / 2 ;
        var t = (screen.height - h) / 2;
        
        oWindow = window.open('reference.php?s=customer-code-pipe', 'winRef', 'directories=no, titlebar=no, toolbar=no, location=no, status=no, menubar=no, width=' + w + ', height=' + h + ', left=' + l + ', top=' + t);     
        oWindow.focus();
    }

function setCustomerCode(id, customer_code) {
    var txt_customer_code = document.getElementById('customer_code');
    var hid_id = document.getElementById('id');
    txt_customer_code.value = customer_code;
    hid_id.value = id;
}

function setHasilPipa(id, code, name, spec, od, thick, length) {
    var txt_code = document.getElementById('product_code');
    var txt_ukuran = document.getElementById('length');
    var hid_id = document.getElementById('m_product_id');
    txt_code.value = code;
    txt_ukuran.value = length;
    hid_id.value = id;
}

function setBusinessPartner(id, name) {
    var txt_name = document.getElementById('partner_name');
    var hid_id = document.getElementById('c_bpartner_id');
    txt_name.value = name;
    hid_id.value = id;
}

function setCoil(m_coil_slit_id, no_coil, no_lot, weight) {
    var hid_m_prod_slit_line_id = document.getElementById('m_coil_slit_id');
    var txt_no_coil = document.getElementById('no_coil');
    var txt_no_lot = document.getElementById('no_lot');
    var txt_weight = document.getElementById('weight');
    hid_m_prod_slit_line_id.value = m_coil_slit_id;
    txt_no_coil.value = no_coil;
    txt_no_lot.value = no_lot;
    txt_weight.value = weight;
}

<?php if ($_REQUEST['mode'] == 'edit') { ?>
$(function() {
    $("#order_date").datepicker({dateFormat: '<?php echo $APP_DATE_FORMAT_JAVA; ?>'});
});
<?php } ?>

xajax_showLines('<?php echo $data['m_wo_pipa_id'] ?>', '<?php echo $_REQUEST['mode']; ?>');
xajax_showLinesDua('<?php echo $data['m_wo_pipa_id'] ?>', '<?php echo $_REQUEST['mode']; ?>');

//-->
</script>