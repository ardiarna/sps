<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.org>
 * Nov 23, 2013 11:35:10 PM
 */

if ($_REQUEST['mode'] == 'edit' && $_SESSION[$APP_ID]['bk']['m_inout_id'] == $_REQUEST['pkey']['m_inout_id']) {
    $data = $_SESSION[$APP_ID]['bk'];
} else {
    $data = npl_fetch_table(
        "SELECT m_inout.*, c_order.document_no so, partner_name, order_date, remark
        FROM m_inout
        JOIN c_order USING (c_order_id)
        JOIN c_bpartner USING (c_bpartner_id)
        WHERE m_inout_id = '{$_REQUEST['pkey']['m_inout_id']}'");
    $rsx = mysql_query(
            "SELECT m_inout_line.* , m_product.* , m_warehouse.*
            FROM m_inout_line
            JOIN m_product USING (m_product_id)
            JOIN m_warehouse USING (m_warehouse_id)
            WHERE m_inout_id = '{$_REQUEST['pkey']['m_inout_id']}'
            ORDER BY warehouse_code, warehouse_name, product_name",
            $APP_CONNECTION);
    $data['lines'] = array();
    while ($dtx = mysql_fetch_array($rsx, MYSQL_ASSOC)) $data['lines'][] = $dtx;
    mysql_free_result($rsx);
    $_SESSION[$APP_ID]['bk'] = $data;
}

if ($_REQUEST['mode'] != 'edit' AND $_REQUEST['mode'] != 'editH') {
    $readonly = ' readonly="readonly"';
} else {
    $select_so = "<img onclick=\"popupReference('sales-order');\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'>";
}

if ($_REQUEST['mode'] == 'editH') {
    $select_so = "";
}

if ($_SESSION[$GLOBALS['APP_ID']]['trx.bk']['error']) {
    echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['trx.bk']['error']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['trx.bk']['error']);
}

if ($_SESSION[$GLOBALS['APP_ID']]['trx.bk']['info']) {
    echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['trx.bk']['info']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['trx.bk']['info']);
}

$data['m_inout_date'] = empty($data['m_inout_date']) ? date($APP_DATE_FORMAT) : $data['m_inout_date'];
    
echo "<form id='frmBK'>";
echo "<div class='data_box'>";
echo "<input type='hidden' id='c_order_id' name='c_order_id' value='{$data['c_order_id']}'>";
echo "<input type='hidden' id='m_inout_id' name='m_inout_id' value='{$data['m_inout_id']}'>";
echo "<input type='hidden' id='m_inout_date_a' name='m_inout_date_a' value=\"" . (cgx_emptydate($data['m_inout_date']) ? '' : date($APP_DATE_FORMAT, strtotime($data['m_inout_date']))) . "\">";
echo "<table width='100%'>";
echo "<tr>";
echo "<td width='13%'>Nomor Dokumen</td>";
echo "<td width='32%'><input " . ($data['document_no'] ? 'readonly="readonly"' : 'disabled') . " name='document_no' type='text' size='20' value=\"" . ($data['document_no'] ? $data['document_no'] : $autonumber) . "\"></td>";
echo "<td width='10%'></td>";
echo "<td width='13%'>Customer</td>";
echo "<td width='32%'><input readonly='readonly' type='text' id='partner_name' size='30' value=\"{$data['partner_name']}\"></td>";
echo "</tr>";
echo "<tr>";
echo "<td>Sales Order {$mandatory}</td>";
echo "<td><input readonly='readonly' type='text' id='sales_order' size='20' value=\"{$data['so']}\">{$select_so}</td>";
echo "<td></td>";
echo "<td>Tanggal Pengiriman {$mandatory}</td>";
echo "<td><input{$readonly} name='m_inout_date' id='m_inout_date' type='text' size='10' value=\"" . (cgx_emptydate($data['m_inout_date']) ? '' : date($APP_DATE_FORMAT, strtotime($data['m_inout_date']))) . "\"></td>";
echo "</tr>";
echo "<tr>";
echo "<td>Remark</td>";
echo "<td><input readonly='readonly' type='text' id='remark' size='20' value=\"{$data['remark']}\"></td>";
echo "<td></td>";
echo "<td>No. Kendaraan</td>";
echo "<td><input{$readonly} type='text' name='no_kendaraan' size='15' value=\"{$data['no_kendaraan']}\"></td>";
echo "</tr>";
echo "<tr>";
echo "<td>Tanggal Order</td>";
echo "<td><input readonly='readonly' id='order_date' type='text' size='10' value=\"" . (cgx_emptydate($data['order_date']) ? '' : date($APP_DATE_FORMAT, strtotime($data['order_date']))) . "\"></td>";
echo "<td></td>";
echo "<td>No. BKBJ</td>";
echo "<td><input{$readonly} type='text' name='dokumen' size='15' value=\"{$data['dokumen']}\"></td>";
echo "</tr>";
echo "</table>";
echo "</div>";
echo "<div id='area-lines' style='margin-top: 4px;'></div>";
echo "</form>";
if ($_REQUEST['mode'] == 'edit') {
    echo "<div id='area-edit' class='data_box' style='margin-top: 4px; display: none;'></div>";
    echo "<div class='area-button' id='master-button'>";
    echo "<input type='button' value='Simpan Dokumen' onclick=\"xajax_saveBK(xajax.getFormValues('frmBK'));\">";
    echo "<input type='button' value='Batal' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}&pkey[m_inout_id]={$data['m_inout_id']}'\">";
    echo "</div>";
    echo "<div style='text-align: right; margin-top: 10px;'>{$mandatory} = Wajib diisi</div>";
} elseif ($_REQUEST['mode'] == 'editH') {
    echo "<div id='area-edit' class='data_box' style='margin-top: 4px; display: none;'></div>";
    echo "<div class='area-button' id='master-button'>";
    echo "<input type='button' value='Simpan Dokumenn' onclick=\"xajax_updateBK(xajax.getFormValues('frmBK'));\">";
    echo "<input type='button' value='Batal' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}&pkey[m_inout_id]={$data['m_inout_id']}'\">";
    echo "</div>";
    echo "<div style='text-align: right; margin-top: 10px;'>{$mandatory} = Wajib diisi</div>";
}elseif ($_REQUEST['mode'] == 'view') {
    echo "<div class='area-button'>";
    echo "<input type='button' value='Kembali' onclick=\"window.location = 'module.php?m={$_REQUEST['back_to']}'\">";
    echo "</div>";
}elseif (user() == 2 OR user() == 46 OR user() == 51 OR user() == 55 OR user() == 67 OR user() == 29) {
    echo "<div class='area-button'>";
    echo "<input type='button' value='Kembali' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}'\">";
    echo "<input type='button' value='Edit' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}&pkey[m_inout_id]={$data['m_inout_id']}&mode=editH'\">";
    echo "<input type='button' value='Cetak' onclick=\"window.location = 'report.php?path=/reports/SPS/Bukti_Barang_Keluar&param[REPORT_ID_PENGIRIMAN]={$data['m_inout_id']}&type=docx&fname={$data['document_no']}'\">";
    echo "</div>";
}else {
    echo "<div class='area-button'>";
    echo "<input type='button' value='Kembali' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}'\">";
    echo "<input type='button' value='Cetak' onclick=\"window.location = 'report.php?path=/reports/SPS/Bukti_Barang_Keluar&param[REPORT_ID_PENGIRIMAN]={$data['m_inout_id']}&type=docx&fname={$data['document_no']}'\">";
    echo "</div>";
}

?>
<script type="text/javascript">
<!--
function setSalesOrder(c_order_id, document_no, order_date, partner_name, remark) {
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
    xajax_salesOrderLinesForm(c_order_id);
}
<?php if ($_REQUEST['mode'] == 'edit' OR $_REQUEST['mode'] == 'editH') { ?>
$(function() {
    $("#m_inout_date").datepicker({dateFormat: '<?php echo $APP_DATE_FORMAT_JAVA; ?>'});
});
<?php } ?>

xajax_showLines('<?php echo $data['m_inout_id'] ?>', '<?php echo $_REQUEST['mode']; ?>');

//-->
</script>