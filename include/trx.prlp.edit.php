<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.org>
 * Nov 23, 2013 11:35:10 PM
 */

// load data to session
if ($_REQUEST['mode'] == 'edit' && $_SESSION[$APP_ID]['prlp']['m_receipt_longpipe_id'] == $_REQUEST['pkey']['m_receipt_longpipe_id']) {
    $data = $_SESSION[$APP_ID]['prlp'];
} else {

    $data = npl_fetch_table(
            "SELECT m_receipt_longpipe.*, m_work_order.document_no wo, 
             remark, 
             reference_no, partner_name

             FROM m_receipt_longpipe

             JOIN m_work_order USING (m_work_order_id)
             JOIN m_receipt_longpipe_line USING (m_receipt_longpipe_id)
             JOIN m_work_order_line USING (m_work_order_line_id)
             JOIN c_order ON (c_order.c_order_id = m_work_order_line.c_order_id)
             LEFT JOIN c_bpartner ON (c_order.c_bpartner_id = c_bpartner.c_bpartner_id) 
             WHERE m_receipt_longpipe_id = '{$_REQUEST['pkey']['m_receipt_longpipe_id']}'");
            
    $rsx = mysql_query(
            "SELECT c_order.remark,
             c_bpartner.partner_name, m_receipt_longpipe_line.* , m_product.*, material_quantity
             FROM m_receipt_longpipe_line
						 
             JOIN m_work_order_line ON (m_receipt_longpipe_line.m_work_order_line_id = m_work_order_line.m_work_order_line_id)
             JOIN c_order ON (m_work_order_line.c_order_id = c_order.c_order_id)
             JOIN c_bpartner USING (c_bpartner_id)
             JOIN m_product ON (m_work_order_line.m_product_material=m_product.m_product_id)
             WHERE m_receipt_longpipe_id = '{$_REQUEST['pkey']['m_receipt_longpipe_id']}'",
            $APP_CONNECTION);
    $data['lines'] = array();
    
    while ($dtx = mysql_fetch_array($rsx, MYSQL_ASSOC)) $data['lines'][] = $dtx;
    mysql_free_result($rsx);
    $_SESSION[$APP_ID]['prlp'] = $data;
}

if ($_REQUEST['mode'] != 'edit') {
    $readonly = ' readonly="readonly"';
} else {
    $select_wo = "<img onclick=\"popupReference('work-order-request');\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'>";
}


if ($_SESSION[$GLOBALS['APP_ID']]['trx.prlp']['error']) {
    echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['trx.prlp']['error']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['trx.prlp']['error']);
}

if ($_SESSION[$GLOBALS['APP_ID']]['trx.prlp']['info']) {
    echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['trx.prlp']['info']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['trx.prlp']['info']);
}

$data['receipt_date'] = empty($data['receipt_date']) ? date($APP_DATE_FORMAT) : $data['receipt_date'];

echo "<div class='data_box'>";
echo "<form id='frmPRLP'>";

echo "  <input type='hidden' id='m_receipt_longpipe_id' name='m_receipt_longpipe_id' value='{$data['m_receipt_longpipe_id']}'>";
echo "  <input type='hidden' id='m_work_order_id' name='m_work_order_id' value='{$data['m_work_order_id']}'>";

echo "  <table width='100%'>";
echo "      <tr>";
echo "          <td width='10%'>Nomor Dokumen</td>";
echo "          <td width='32%'><input " . ($data['document_no'] ? 'readonly="readonly"' : 'disabled') . " name='document_no' type='text' size='20' value=\"" . ($data['document_no'] ? $data['document_no'] : $autonumber) . "\"></td>";
echo "          <td width='10%'></td>";
echo "          <td width='10%'>Tanggal {$mandatory}</td>";
echo "          <td><input{$readonly} name='receipt_date' id='receipt_date' type='text' size='10' 
value=\"" . (cgx_emptydate($data['receipt_date']) ? '' : date($APP_DATE_FORMAT, strtotime($data['receipt_date']))) . "\"></td>";
echo "      </tr>";
echo "      <tr>";
echo "          <td>Request LP {$mandatory}</td>";
echo "          <td><input readonly='readonly' type='text' name='wo' id='wo' size='20' value=\"{$data['wo']}\">{$select_wo}</td>";
echo "          <td></td>";
echo "      </tr>";
echo "  </table>";
echo "</div>";
echo "<div id='area-lines' style='margin-top: 4px;'></div>";
echo "</form>";

//echo "<div id='area-lines' style='margin-top: 4px;'></div>";

if ($_REQUEST['mode'] == 'edit') {
    echo "<div id='area-edit' class='data_box' style='margin-top: 4px; display: none;'></div>";
    echo "<div class='area-button' id='master-button'>";
    echo "<input type='button' value='Simpan Dokumen' onclick=\"xajax_savePRLP(xajax.getFormValues('frmPRLP'));\">";
    echo "<input type='button' value='Tambah Baris' onclick=\"xajax_editForm();\">";
    echo "<input type='button' value='Batal' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}&pkey[m_request_longpipe_id]={$data['m_receipt_longpipe_id']}'\">";
    echo "</div>";
    echo "<div style='text-align: right; margin-top: 10px;'>{$mandatory} = Wajib diisi</div>";
} else {
    echo "<div class='area-button'>";
    echo "<input type='button' value='Kembali' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}'\">";
    echo "<input type='button' value='Edit' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}&pkey[m_receipt_longpipe_id]={$data['m_receipt_longpipe_id']}&mode=edit'\">";
//    echo "<input" . ($data['status'] == 'C' ? ' disabled' : '')  . " type='button' value='Close SO' onclick=\"xajax_closeSO('{$data['c_forecast_id']}');\">";
    echo "</div>";
}

//$periode = npl_format_period($data['periode']);
//print_r($periode);
//exit;

?>
<?php if ($_REQUEST['mode'] == 'edit') { ?>

<?php } ?>

<script type="text/javascript">
<!--
<?php if ($_REQUEST['mode'] == 'edit') { ?>
$(function() {
    $("#receipt_date").datepicker({dateFormat: '<?php echo $APP_DATE_FORMAT_JAVA; ?>'});
});
<?php } ?>
function setWorkOrder(m_work_order_id, document_no) {
    var hid_m_work_order_id = document.getElementById('m_work_order_id');
    var txt_document_no = document.getElementById('wo');
    hid_m_work_order_id.value = m_work_order_id;
    txt_document_no.value = document_no;
}

function setWorkOrder_id(m_work_order_line_id, m_product_id, c_order_id, product_code, product_name, material_quantity) {
    var hid_m_work_order_line_id = document.getElementById('m_work_order_line_id');
    var hid_m_product_id = document.getElementById('m_product_id');
    var hid_c_order_id = document.getElementById('c_order_id');
    var txt_product_code = document.getElementById('product_code');
    var txt_product_name = document.getElementById('product_name');
    var txt_material_quantity = document.getElementById('material_quantity');
    hid_m_work_order_line_id.value = m_work_order_line_id;
    hid_m_product_id.value = m_product_id;
    hid_c_order_id.value = c_order_id;
    txt_product_code.value = product_code;
    txt_product_name.value = product_name;
    txt_material_quantity.value = material_quantity;
}

function setbmbj(m_inout_id, document_no, quantity){
    var txt_m_inout_id = document.getElementById('m_inout_id');
    var txt_document_no = document.getElementById('document_no_bmbj');
    var txt_quantity = document.getElementById('quantity_bmbj')
    
    txt_m_inout_id.value = m_inout_id;
    txt_document_no.value = document_no;
    txt_quantity.value = quantity;
}


xajax_showLines('<?php echo $data['m_receipt_longpipe_id'] ?>', '<?php echo $_REQUEST['mode']; ?>');

//-->
</script>

