<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.org>
 * Nov 23, 2013 11:35:10 PM
 */

// load data to session
if ($_REQUEST['mode'] == 'edit' && $_SESSION[$APP_ID]['bkbj']['m_inout_id'] == $_REQUEST['pkey']['m_inout_id']) {
    $data = $_SESSION[$APP_ID]['bkbj'];
} else {
    $data = npl_fetch_table(
            "SELECT m_inout.*, c_order.document_no so, partner_name, remark, c_bpartner.c_bpartner_id
            FROM m_inout join c_bpartner ON(m_inout.tuj_org_id=c_bpartner.c_bpartner_id)
            LEFT JOIN c_order ON(m_inout.c_order_id=c_order.c_order_id)
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
    $_SESSION[$APP_ID]['bkbj'] = $data;
}

if ($_REQUEST['mode'] != 'edit' AND $_REQUEST['mode'] != 'editH') {
    $readonly = ' readonly="readonly"';
}else {
    $select_partner = "<img onclick=\"popupReference('business-partner');\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'>";
    $select_so = "<img onclick=\"popupReference('sales-order');\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'>";
}


if ($_SESSION[$GLOBALS['APP_ID']]['trx.bkbj']['error']) {
    echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['trx.bkbj']['error']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['trx.bkbj']['error']);
}

if ($_SESSION[$GLOBALS['APP_ID']]['trx.bkbj']['info']) {
    echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['trx.bkbj']['info']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['trx.bkbj']['info']);
}

$data['m_inout_date'] = empty($data['m_inout_date']) ? date($APP_DATE_FORMAT) : $data['m_inout_date'];

echo "<div class='data_box'>";
echo "<form id='frmRR'>";
echo "<input type='hidden' id='m_inout_id' name='m_inout_id' value='{$data['m_inout_id']}'>";
echo "<input type='hidden' id='c_bpartner_id' name='c_bpartner_id' value='{$data['c_bpartner_id']}'>";
echo "<input type='hidden' id='m_inout_date_a' name='m_inout_date_a' value=\"" . (cgx_emptydate($data['m_inout_date']) ? '' : date($APP_DATE_FORMAT, strtotime($data['m_inout_date']))) . "\">";
echo "<input type='hidden' id='c_order_id' name='c_order_id' value='{$data['c_order_id']}'>";
echo "<table width='100%'>";
echo "<tr>";
echo "<td width='12%'>Nomor Dokumen</td>";
echo "<td width='33%'><input " . ($data['document_no'] ? 'readonly="readonly"' : 'disabled') . " name='document_no' type='text' size='20' value=\"" . ($data['document_no'] ? $data['document_no'] : $autonumber) . "\"></td>";
echo "<td width='10%'></td>";
echo "<td width='12%'>No. Kendaraan</td>";
echo "<td width='33%'><input{$readonly} type='text' name='no_kendaraan' size='25' value=\"{$data['no_kendaraan']}\"></td>";
echo "</tr><tr>";
echo "<td>Customer {$mandatory}</td>";
echo "<td><input readonly='readonly' type='text' id='partner_name' size='30' value=\"{$data['partner_name']}\">{$select_partner}</td>";
echo "<td></td>";
echo "<td>No. Surat Jalan</td>";
echo "<td><input{$readonly} type='text' name='dokumen' size='25' value=\"{$data['dokumen']}\"></td>";
echo "</tr><tr>";
echo "<td>Tanggal {$mandatory}</td>";
echo "<td><input{$readonly} name='m_inout_date' id='m_inout_date' type='text' size='10' value=\"" . (cgx_emptydate($data['m_inout_date']) ? '' : date($APP_DATE_FORMAT, strtotime($data['m_inout_date']))) . "\"></td>";
echo "<td></td>";
// echo "<td>Sales Order</td>"; //Sales Order
// echo "<td><input readonly='readonly' type='text' id='sales_order' size='20' value=\"{$data['so']}\">{$select_so}</td>";
// echo "</tr><tr>";
// echo "<td></td>";
// echo "<td></td>";
// echo "<td></td>";
// echo "<td>Remark</td>";  //Remark
// echo "<td><input readonly='readonly' type='text' id='remark' size='20' value=\"{$data['remark']}\"></td>";
echo "</tr>";
echo "</table>";
echo "</form>";
echo "</div>";

echo "<div id='area-lines' style='margin-top: 4px;'></div>";
if ($_REQUEST['mode'] == 'edit') {
    echo "<div id='area-edit' class='data_box' style='margin-top: 4px; display: none;'></div>";
    echo "<div class='area-button' id='master-button'>";
    echo "<input type='button' value='Simpan Dokumen' onclick=\"xajax_saveRR(xajax.getFormValues('frmRR'));\">";
    echo "<input type='button' value='Tambah Baris' onclick=\"xajax_editForm();\">";
    echo "<input type='button' value='Batal' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}&pkey[m_inout_id]={$data['m_inout_id']}'\">";
    echo "</div>";
    echo "<div style='text-align: right; margin-top: 10px;'>{$mandatory} = Wajib diisi</div>";
}elseif ($_REQUEST['mode'] == 'editH') {
    echo "<div id='area-edit' class='data_box' style='margin-top: 4px; display: none;'></div>";
    echo "<div class='area-button' id='master-button'>";
    echo "<input type='button' value='Simpan Dokumen' onclick=\"xajax_saveRR(xajax.getFormValues('frmRR'));\">";
    echo "<input type='button' value='Batal' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}&pkey[m_inout_id]={$data['m_inout_id']}'\">";
    echo "</div>";
    echo "<div style='text-align: right; margin-top: 10px;'>{$mandatory} = Wajib diisi</div>";
}elseif (user() == 2 OR user() == 46 OR user() == 51 OR user() == 55) {
    echo "<div class='area-button'>";
    echo "<input type='button' value='Kembali' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}'\">";
    echo "<input type='button' value='Edit' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}&pkey[m_inout_id]={$data['m_inout_id']}&mode=editH'\">";
    echo "<input type='button' value='Cetak' onclick=\"window.location = 'report.php?path=/reports/SPS/Bkbj&param[REPORT_ID_PENGIRIMAN]={$data['m_inout_id']}&type=docx&fname={$data['document_no']}'\">";
    echo "</div>";
}else {
    echo "<div class='area-button'>";
    echo "<input type='button' value='Kembali' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}'\">";
    echo "<input type='button' value='Cetak' onclick=\"window.location = 'report.php?path=/reports/SPS/Bkbj&param[REPORT_ID_PENGIRIMAN]={$data['m_inout_id']}&type=docx&fname={$data['document_no']}'\">";
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

function setProduct(id, code, name, desc, id_wh, name_wh, bl_qty) {
    var txt_code = document.getElementById('product_code');
    var txt_name = document.getElementById('product_name');
    var txt_name_wh = document.getElementById('warehouse_name');
    var txt_desc = document.getElementById('description');
    var txt_bl_qty = document.getElementById('balance_quantity');
    var hid_id = document.getElementById('m_product_id');
    var hid_id_wh = document.getElementById('m_warehouse_id');
    txt_code.value = code;
    txt_name.value = name;
    txt_name_wh.value = name_wh;
    txt_desc.value = desc;
    txt_bl_qty.value = bl_qty;
    hid_id.value = id;
    hid_id_wh.value = id_wh;
}

function setSalesOrder(c_order_id, document_no, order_date, partner_name, remark) {
    var txt_document_no = document.getElementById('sales_order');
    var txt_remark = document.getElementById('remark');
    var hid_c_order_id = document.getElementById('c_order_id');
    txt_document_no.value = document_no;
    txt_remark.value = remark;
    hid_c_order_id.value = c_order_id;
}

<?php if ($_REQUEST['mode'] == 'edit' OR $_REQUEST['mode'] == 'editH') { ?>
$(function() {
    $("#m_inout_date").datepicker({dateFormat: '<?php echo $APP_DATE_FORMAT_JAVA; ?>'});
});
<?php } ?>

xajax_showLines('<?php echo $data['m_inout_id'] ?>', '<?php echo $_REQUEST['mode']; ?>');

//-->
</script>