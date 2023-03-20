<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.org>
 * Nov 23, 2013 11:35:10 PM
 */

if ($_REQUEST['mode'] == 'edit' && $_SESSION[$APP_ID]['good_rcp']['m_inout_id'] == $_REQUEST['pkey']['m_inout_id']) {
    $data = $_SESSION[$APP_ID]['good_rcp'];
} else {
    $data = npl_fetch_table(
        "SELECT m_inout.*, c_order.document_no so, partner_name, order_date, remark
        FROM m_inout
        JOIN c_order USING (c_order_id)
        JOIN c_bpartner USING (c_bpartner_id)
        WHERE m_inout_id = '{$_REQUEST['pkey']['m_inout_id']}'");
        
        
    $rsx = mysql_query(
            "SELECT m_inout_line_id, m_inout_line.m_product_id, m_inout_line.c_order_line_id, m_inout_line.m_warehouse_id, m_inout_line.quantity, 
            product_code, product_name, spec, thickness, od , warehouse_name, m_coil_id, m_coil.no_coil, m_coil.no_lot, (m_coil.weight / m_inout_line.quantity) AS weight 
            FROM m_inout_line
            JOIN m_product USING (m_product_id)
            JOIN m_warehouse USING (m_warehouse_id)
            LEFT JOIN m_coil ON(m_inout_line.m_inout_line_id = m_coil.m_in_id)
            WHERE m_inout_id = '{$_REQUEST['pkey']['m_inout_id']}'",
            $APP_CONNECTION);
    $data['lines'] = array();
    while ($dtx = mysql_fetch_array($rsx, MYSQL_ASSOC)) $data['lines'][] = $dtx;
    mysql_free_result($rsx);
    $_SESSION[$APP_ID]['good_rcp'] = $data;

    $rsy = mysql_query(
            "SELECT m_coil.*
            FROM m_coil
            WHERE m_in_id = '{$_REQUEST['pkey']['m_inout_id']}'
            ORDER BY m_coil_id",
            $APP_CONNECTION);
    $data['linesdua'] = array();
    while ($dty = mysql_fetch_array($rsy, MYSQL_ASSOC)) $data['linesdua'][] = $dty;
    mysql_free_result($rsy);
    $_SESSION[$APP_ID]['good_rcp'] = $data;
}

if ($_REQUEST['mode'] != 'edit' AND $_REQUEST['mode'] != 'editH') {
    $readonly = ' readonly="readonly"';
} else {
    $select_po = "<img onclick=\"popupReference('purchase-order');\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'>";
    $cek_nomor = "<img onclick=\"xajax_cekNomor(document.getElementById('document_no').value, document.getElementById('m_inout_id').value);\" style='cursor: pointer; margin: -2px 5px;' src='images/icon_check.png'>";
}

if ($_REQUEST['mode'] == 'editH') {
    $select_po = "";
}

if ($_SESSION[$GLOBALS['APP_ID']]['trx.good_rcp']['error']) {
    echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['trx.good_rcp']['error']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['trx.good_rcp']['error']);
}

if ($_SESSION[$GLOBALS['APP_ID']]['trx.good_rcp']['info']) {
    echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['trx.good_rcp']['info']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['trx.good_rcp']['info']);
}

$data['m_inout_date'] = empty($data['m_inout_date']) ? date($APP_DATE_FORMAT) : $data['m_inout_date'];
//$data['sj_date'] = empty($data['sj_date']) ? date($APP_DATE_FORMAT) : $data['sj_date'];


echo "<form id='frmgood_rcp'>";
echo "<div class='data_box'>";
echo "<input type='hidden' id='c_order_id' name='c_order_id' value='{$data['c_order_id']}'>";
echo "<input type='hidden' id='m_inout_id' name='m_inout_id' value='{$data['m_inout_id']}'>";
echo "<input type='hidden' id='m_inout_date_a' name='m_inout_date_a' value=\"" . (cgx_emptydate($data['m_inout_date']) ? '' : date($APP_DATE_FORMAT, strtotime($data['m_inout_date']))) . "\">";
echo "<table width='100%'>";
echo "<tr>";
echo "<td width='13%'>Nomor Receipt {$mandatory}</td>";
echo "<td width='32%'><input <input{$readonly} id='document_no' name='document_no' type='text' size='20' value=\"{$data['document_no']}\">{$cek_nomor}</td>";
echo "<td width='10%'></td>";
echo "<td width='13%'>Vendor</td>";
echo "<td width='32%'><input readonly='readonly' type='text' id='partner_name' size='30' value=\"{$data['partner_name']}\"></td>";
echo "</tr>";
echo "<tr>";
echo "<td>Purchase Order {$mandatory}</td>";
echo "<td><input readonly='readonly' type='text' id='sales_order' size='20' value=\"{$data['so']}\">{$select_po}</td>";
echo "<td></td>";
echo "<td>Tanggal Order</td>";
echo "<td><input readonly='readonly' id='order_date' type='text' size='10' value=\"" . (cgx_emptydate($data['order_date']) ? '' : date($APP_DATE_FORMAT, strtotime($data['order_date']))) . "\"></td>";
echo "</tr>";
echo "<tr>";
echo "<td>Contract No</td>";
echo "<td><input readonly='readonly' type='text' id='remark' size='20' value=\"{$data['remark']}\"></td>";
echo "<td></td>";
echo "<td>Tanggal Surat Jalan</td>";
echo "<td><input{$readonly} name='sj_date' id='sj_date' type='text' size='10' value=\"" . (cgx_emptydate($data['sj_date']) ? '' : date($APP_DATE_FORMAT, strtotime($data['sj_date']))) . "\"></td>";
echo "</tr>";
echo "<tr>";
echo "<td>Jumlah (Pcs)</td>";
echo "<td><input readonly='readonly' type='text' id='total_quantity' name='total_quantity' size='10' value=\"{$data['total_quantity']}\" style='text-align: right;'></td>";
echo "<td></td>";
echo "<td>No. Surat Jalan</td>";
echo "<td><input{$readonly} type='text' name='no_kendaraan' size='20' value=\"{$data['no_kendaraan']}\"></td>";
echo "</tr>";
echo "<tr>";
echo "<td>Berat (Kg)</td>";
echo "<td><input readonly='readonly' type='text' id='total_weight' name='total_weight' size='10' value=\"{$data['total_weight']}\" style='text-align: right;'></td>";
echo "<td></td>";
echo "<td>Tanggal Masuk {$mandatory}</td>";
echo "<td><input{$readonly} name='m_inout_date' id='m_inout_date' type='text' size='10' value=\"" . (cgx_emptydate($data['m_inout_date']) ? '' : date($APP_DATE_FORMAT, strtotime($data['m_inout_date']))) . "\"></td>";
echo "</tr>";
echo "</table>";
echo "</div>";
echo "<div class='data_box'>";
echo "<input type='hidden' id='c_order_line_id_head'>";
echo "<input type='hidden' id='m_product_id_head'>";
echo "<table width='100%'>";
echo "<tr>";
echo "<td width='13%'>Produk Coil {$mandatory}</td>";
echo "<td width='32%'><input id='product_name_head' type='text' size='35' readonly='readonly'><img onclick=\"popupReferenceAmbil('purchase-order-detail','&p1=' + document.getElementById('c_order_id').value);\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'></td>";
echo "<td width='10%'></td>";
echo "<td width='13%'></td>";
echo "<td width='32%'></td>";
echo "</tr>";
echo "</table>";
echo "</div>";
echo "<div id='area-lines' style='margin-top: 4px;'></div>";
echo "<div id='area-lines-dua' style='margin-top: 4px;'></div>";
echo "</form>";
if ($_REQUEST['mode'] == 'edit') {
    echo "<div id='area-edit' class='data_box' style='margin-top: 4px; display: none;'></div>";
    echo "<div class='area-button' id='master-button'>";
    echo "<input type='button' value='Simpan Dokumen' onclick=\"xajax_savegood_rcp(xajax.getFormValues('frmgood_rcp'));\">";
    echo "<input type='button' value='Tambah Baris' onclick=\"xajax_editForm(0, document.getElementById('c_order_line_id_head').value, document.getElementById('m_product_id_head').value, document.getElementById('product_name_head').value);\">";
    echo "<input type='button' value='Batal' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}&pkey[m_inout_id]={$data['m_inout_id']}'\">";
    echo "</div>";
    echo "<div style='text-align: right; margin-top: 10px;'>{$mandatory} = Wajib diisi</div>";
} elseif ($_REQUEST['mode'] == 'editH') {
    echo "<div id='area-edit' class='data_box' style='margin-top: 4px; display: none;'></div>";
    echo "<div class='area-button' id='master-button'><table width='100%'><tr>";
    echo "<input type='button' value='Simpan Dokumenn' onclick=\"xajax_savegood_rcp(xajax.getFormValues('frmgood_rcp'));\">";
    echo "<input type='button' value='Tambah Baris' onclick=\"xajax_editForm(0, document.getElementById('c_order_line_id_head').value, document.getElementById('m_product_id_head').value, document.getElementById('product_name_head').value);\">";
    echo "<input type='button' value='Batal' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}&pkey[m_inout_id]={$data['m_inout_id']}'\">";
    echo "</tr></table></div>";
    echo "<div style='text-align: right; margin-top: 10px;'>{$mandatory} = Wajib diisi</div>";
}else {
    echo "<div class='area-button'>";
    echo "<input type='button' value='Kembali' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}'\">";
    echo "<input type='button' value='Edit' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}&pkey[m_inout_id]={$data['m_inout_id']}&mode=editH'\">";
    echo "<input type='button' value='Cetak' onclick=\"window.location = 'report.php?path=/reports/SPS/Bpbb&param[REPORT_ID_PENERIMAAN]={$data['m_inout_id']}&type=docx&fname={$data['document_no']}'\">";
    echo "</div>";
}

?>
<script type="text/javascript">
<!--
function setPurchaseOrder(c_order_id, document_no, order_date, partner_name, remark, c_order_line_id, m_product_id) {
    var txt_document_no = document.getElementById('sales_order');
    var txt_order_date = document.getElementById('order_date');
    var txt_partner_name = document.getElementById('partner_name');
    var txt_remark = document.getElementById('remark');
    var hid_c_order_id = document.getElementById('c_order_id');
    txt_document_no.value = document_no;    
    txt_order_date.value = ymd2dmy(order_date);
    txt_partner_name.value = partner_name;
    txt_remark.value = remark;
    hid_c_order_id.value = c_order_id;
}

function setPurchaseOrderDetail(c_order_line_id, m_product_id, product_name) {
    var txt_product_name = document.getElementById('product_name_head');
    var hid_c_order_line_id = document.getElementById('c_order_line_id_head');
    var hid_m_product_id = document.getElementById('m_product_id_head');
    txt_product_name.value = product_name;
    hid_c_order_line_id.value = c_order_line_id;
    hid_m_product_id.value = m_product_id;
}

function setPurchaseOrderDetailEdit(c_order_line_id, m_product_id, product_name) {
    var txt_product_name = document.getElementById('product_name');
    var hid_c_order_line_id = document.getElementById('c_order_line_id');
    var hid_m_product_id = document.getElementById('m_product_id');
    txt_product_name.value = product_name;
    hid_c_order_line_id.value = c_order_line_id;
    hid_m_product_id.value = m_product_id;
}


<?php if ($_REQUEST['mode'] == 'edit' OR $_REQUEST['mode'] == 'editH') { ?>
$(function() {
    $("#m_inout_date").datepicker({dateFormat: '<?php echo $APP_DATE_FORMAT_JAVA; ?>'});
    $("#sj_date").datepicker({dateFormat: '<?php echo $APP_DATE_FORMAT_JAVA; ?>'});
});
<?php } ?>

xajax_showLines('<?php echo $data['m_inout_id'] ?>', '<?php echo $_REQUEST['mode']; ?>');
xajax_showLinesDua('<?php echo $data['m_inout_id'] ?>', '<?php echo $_REQUEST['mode']; ?>');

//-->
</script>