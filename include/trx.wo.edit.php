<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.org>
 * Nov 23, 2013 11:35:10 PM
 */

// load data to session
if ($_REQUEST['mode'] == 'edit' && $_SESSION[$APP_ID]['wo']['m_work_order_id'] == $_REQUEST['pkey']['m_work_order_id']) {
    $data = $_SESSION[$APP_ID]['wo'];
} else {
    $data = npl_fetch_table(
        "SELECT m_work_order.*, machine_name, proces_name
        FROM m_work_order
        LEFT JOIN m_machine USING (m_machine_id)
        LEFT JOIN c_proces USING (c_proces_id)
        WHERE m_work_order_id = '{$_REQUEST['pkey']['m_work_order_id']}'");
    $rsx = mysql_query(
            "SELECT m_work_order_line.*, c_order.document_no so, COALESCE(remark,c_forecast.document_no) remark , COALESCE(cb.partner_name,cb2.partner_name) partner_name , m_product.*, CONCAT(mat.od, ' x ', mat.thickness, ' x ', mat.length) as size_lp
            FROM m_work_order_line
            LEFT JOIN c_order USING (c_order_id)
            LEFT JOIN c_bpartner cb ON (c_order.c_bpartner_id=cb.c_bpartner_id)
            LEFT JOIN c_forecast ON (m_work_order_line.c_forecast_id=c_forecast.c_forecast_id)
            LEFT JOIN c_bpartner cb2 ON (c_forecast.c_bpartner_id=cb2.c_bpartner_id)
            JOIN m_product ON (m_work_order_line.m_product_id=m_product.m_product_id)
            JOIN m_product mat ON (m_work_order_line.m_product_material=mat.m_product_id)
            WHERE m_work_order_id = '{$_REQUEST['pkey']['m_work_order_id']}'",
            $APP_CONNECTION);
    $data['lines'] = array();
    while ($dtx = mysql_fetch_array($rsx, MYSQL_ASSOC)) $data['lines'][] = $dtx;
    mysql_free_result($rsx);
    $_SESSION[$APP_ID]['wo'] = $data;
}

if ($_REQUEST['mode'] != 'edit') {
    $readonly = ' readonly="readonly"';
    $disabled = ' disabled';
}

if ($_SESSION[$GLOBALS['APP_ID']]['trx.wo']['error']) {
    echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['trx.wo']['error']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['trx.wo']['error']);
}

if ($_SESSION[$GLOBALS['APP_ID']]['trx.wo']['info']) {
    echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['trx.wo']['info']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['trx.wo']['info']);
}

$data['order_date'] = empty($data['order_date']) ? date($APP_DATE_FORMAT) : $data['order_date'];
$data['delivery_from'] = empty($data['delivery_from']) ? date($APP_DATE_FORMAT) : $data['delivery_from'];
$data['delivery_end'] = empty($data['delivery_end']) ? date($APP_DATE_FORMAT) : $data['delivery_end'];

echo "<div class='data_box'>";
echo "<form id='frmWO'>";
echo "<input type='hidden' id='m_work_order_id' name='m_work_order_id' value='{$data['m_work_order_id']}'>";
echo "<input type='hidden' id='order_date_a' name='order_date_a' value=\"" . (cgx_emptydate($data['order_date']) ? '' : date($APP_DATE_FORMAT, strtotime($data['order_date']))) . "\">";
echo "<table width='100%'>";
echo "<tr>";
echo "<td width='12%'>Nomor Dokumen</td>";
echo "<td width='36%'><input " . ($data['document_no'] ? 'readonly="readonly"' : 'disabled') . " name='document_no' type='text' size='20' value=\"" . ($data['document_no'] ? $data['document_no'] : $autonumber) . "\"></td>";
echo "<td width='4%'></td>";
echo "<td width='12%'>Mesin {$mandatory}</td>";
echo "<td width='36%'>" . cgx_filter('m_machine_id', "SELECT m_machine_id, machine_name FROM m_machine WHERE " . org_filter_master() . " ORDER BY machine_name", $data['m_machine_id'], FALSE, $disabled) . "</td>";
echo "</tr>";
echo "<tr>";
echo "<td>Tanggal WO {$mandatory}</td>";
echo "<td><input{$readonly} name='order_date' id='order_date' type='text' size='10' value=\"" . (cgx_emptydate($data['order_date']) ? '' : date($APP_DATE_FORMAT, strtotime($data['order_date']))) . "\"></td>";
echo "<td></td>";
echo "<td>Next Proces {$mandatory}</td>";
echo "<td>" . cgx_filter('c_proces_id', "SELECT c_proces_id, CONCAT(proces_code, ' - ', proces_name) FROM c_proces ORDER BY c_proces_id", $data['c_proces_id'], FALSE, $disabled) . "</td>";
echo "</tr>";
echo "<tr>";
echo "<td>Delivery Date</td>";
echo "<td><input{$readonly} name='delivery_from' id='delivery_from' type='text' size='10' value=\"" . (cgx_emptydate($data['delivery_from']) ? '' : date($APP_DATE_FORMAT, strtotime($data['delivery_from']))) . "\"> s.d <input{$readonly} name='delivery_end' id='delivery_end' type='text' size='10' value=\"" . (cgx_emptydate($data['delivery_end']) ? '' : date($APP_DATE_FORMAT, strtotime($data['delivery_end']))) . "\"></td>";
echo "<td></td>";
echo "<td></td>";
echo "<td></td>";
echo "</tr>";
echo "</table>";
echo "</form>";
echo "</div>";

echo "<div id='area-lines' style='margin-top: 4px;'></div>";
if ($_REQUEST['mode'] == 'edit') {
    echo "<div id='area-edit' class='data_box' style='margin-top: 4px; display: none;'></div>";
    echo "<div class='area-button' id='master-button'>";
    echo "<input type='button' value='Simpan Dokumen' onclick=\"xajax_saveWO(xajax.getFormValues('frmWO'));\">";
    echo "<input type='button' value='Tambah Baris' onclick=\"xajax_editForm();\">";
    echo "<input type='button' value='Batal' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}&pkey[m_work_order_id]={$data['m_work_order_id']}'\">";
    echo "</div>";
    echo "<div style='text-align: right; margin-top: 10px;'>{$mandatory} = Wajib diisi</div>";
} else {
    echo "<div class='area-button'>";
    echo "<input type='button' value='Kembali' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}'\">";
    echo "<input type='button' value='Edit' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}&pkey[m_work_order_id]={$data['m_work_order_id']}&mode=edit'\">";
    echo "<input type='button' value='Cetak' onclick=\"window.location = 'report.php?path=/reports/SPS/Work_Order_Manual&param[REPORT_ID_WO]={$data['m_work_order_id']}&type=docx&fname={$data['document_no']}'\">";
    echo "</div>";
}

?>
<script type="text/javascript">
<!--

function setSalesOrder(c_order_id, document_no, s_order_date, partner_name, remark, m_product_id, product_name, order_qty_so) {
    var txt_document_no = document.getElementById('sales_order');
    var txt_partner_name = document.getElementById('partner_name');
    var txt_product_name = document.getElementById('product_name');
    var txt_remark = document.getElementById('remark');
    var hid_c_order_id = document.getElementById('c_order_id');
    var hid_m_product_id = document.getElementById('m_product_id');
    var txt_order_qty_so = document.getElementById('order_qty_so');
    var hid_c_forecast_id = document.getElementById('c_forecast_id');
    txt_document_no.value = document_no;
    txt_partner_name.value = partner_name;
    txt_product_name.value = product_name;
    txt_remark.value = remark;
    hid_c_order_id.value = c_order_id;
    hid_m_product_id.value = m_product_id;
    txt_order_qty_so.value = order_qty_so;
    hid_c_forecast_id.value = null;
}

function setMaterial(id, size_lp) {
    var txt_size = document.getElementById('size_lp');
    var hid_id = document.getElementById('m_product_material');
    txt_size.value = size_lp;
    hid_id.value = id;
}

function setForecast(c_forecast_id, document_no, partner_name, m_product_id, product_name, order_qty_so) {
    var txt_forecast_no = document.getElementById('remark');
    var txt_partner_name = document.getElementById('partner_name');
    var txt_product_name = document.getElementById('product_name');
    var hid_c_forecast_id = document.getElementById('c_forecast_id');
    var hid_m_product_id = document.getElementById('m_product_id');
    var txt_order_qty_so = document.getElementById('order_qty_so');
    var txt_document_no = document.getElementById('sales_order');
    var hid_c_order_id = document.getElementById('c_order_id');
    txt_forecast_no.value = document_no;
    txt_partner_name.value = partner_name;
    txt_product_name.value = product_name;
    hid_c_forecast_id.value = c_forecast_id;
    hid_m_product_id.value = m_product_id;
    txt_order_qty_so.value = order_qty_so;
    txt_document_no.value = null;
    hid_c_order_id.value = null;
}

<?php if ($_REQUEST['mode'] == 'edit') { ?>
$(function() {
    $("#order_date").datepicker({dateFormat: '<?php echo $APP_DATE_FORMAT_JAVA; ?>'});
    $("#delivery_from").datepicker({dateFormat: '<?php echo $APP_DATE_FORMAT_JAVA; ?>'});
    $("#delivery_end").datepicker({dateFormat: '<?php echo $APP_DATE_FORMAT_JAVA; ?>'});
});
<?php } ?>

xajax_showLines('<?php echo $data['m_work_order_id'] ?>', '<?php echo $_REQUEST['mode']; ?>');

//-->
</script>