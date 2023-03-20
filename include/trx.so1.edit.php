<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.org>
 * Nov 23, 2013 11:35:10 PM
 */

// load data to session
if ($_REQUEST['mode'] == 'edit' && $_SESSION[$APP_ID]['order1']['c_order_id'] == $_REQUEST['pkey']['c_order_id']) {
    $data = $_SESSION[$APP_ID]['order1'];
} else {
    $data = npl_fetch_table(
            "SELECT *
            FROM c_order
            JOIN c_bpartner USING (c_bpartner_id)
            JOIN app_org ON(c_order.app_org_id=app_org.app_org_id)
            WHERE c_order_id = '{$_REQUEST['pkey']['c_order_id']}'");
    $rsx = mysql_query(
            "SELECT * 
            FROM c_order_line
            JOIN m_product USING (m_product_id)
            WHERE c_order_id = '{$_REQUEST['pkey']['c_order_id']}'
            ORDER BY schedule_delivery_date",
            $APP_CONNECTION);
    $data['lines'] = array();
    while ($dtx = mysql_fetch_array($rsx, MYSQL_ASSOC)) $data['lines'][] = $dtx;
    mysql_free_result($rsx);
    $_SESSION[$APP_ID]['order1'] = $data;
}

if ($_REQUEST['mode'] != 'edit') {
    $readonly = ' readonly="readonly"';
} else {
    $select_partner = "<img onclick=\"popupReference('business-partner');\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'>";
}


if ($_SESSION[$GLOBALS['APP_ID']]['trx.so1']['error']) {
    echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['trx.so1']['error']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['trx.so1']['error']);
}

if ($_SESSION[$GLOBALS['APP_ID']]['trx.so1']['info']) {
    echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['trx.so1']['info']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['trx.so1']['info']);
}

$data['order_date'] = empty($data['order_date']) ? date($APP_DATE_FORMAT) : $data['order_date'];
        
echo "<div class='data_box'>";
echo "<form id='frmSO'>";
echo "<input type='hidden' id='c_order_id' name='c_order_id' value='{$data['c_order_id']}'>";
echo "<input type='hidden' id='c_bpartner_id' name='c_bpartner_id' value='{$data['c_bpartner_id']}'>";
echo "<table width='100%'>";
echo "<tr>";
echo "<td width='12%'>Nomor Dokumen</td>";
echo "<td width='36%'><input " . ($data['document_no'] ? 'readonly="readonly"' : 'disabled') . " name='document_no' type='text' size='15' value=\"" . ($data['document_no'] ? $data['document_no'] : $autonumber) . "\"></td>";
echo "<td width='4%'></td>";
echo "<td width='12%'>PO Number</td>";
echo "<td width='36%'><input{$readonly} name='reference' type='text' size='30' value=\"{$data['reference_no']}\"></td>";
echo "</tr>";
echo "<tr>";
echo "<td>Customer {$mandatory}</td>";
echo "<td><input readonly='readonly' type='text' id='partner_name' size='30' value=\"{$data['partner_name']}\">{$select_partner}</td>";
echo "<td></td>";
echo "<td>Remark</td>";
echo "<td><input{$readonly} type='text' name='remark' size='30' value=\"{$data['remark']}\"></td>";
echo "</tr>";
echo "<tr>";
echo "<td>Tgl Order {$mandatory}</td>";
echo "<td><input{$readonly} name='order_date' id='order_date' type='text' size='10' value=\"" . (cgx_emptydate($data['order_date']) ? '' : date($APP_DATE_FORMAT, strtotime($data['order_date']))) . "\"></td>";
echo "<td></td>";
echo "<td>Status</td>";
echo "<td><input readonly='readonly' type='text' size='10' value=\"" . ($data['status'] == 'C' ? 'Close' : 'Open') . "\"></td>";
echo "</tr>";
echo "<tr>";
echo "<td>Organization</td>";
echo "<td>". cgx_form_select('app_org_id', "SELECT app_org_id, organization FROM app_org", $data['app_org_id'], FALSE, "id='app_org_id'") ."</td>";
echo "</tr>";
echo "</table>";
echo "</form>";
echo "</div>";

echo "<div id='area-lines' style='margin-top: 4px;'></div>";
if ($_REQUEST['mode'] == 'edit') {
    echo "<div id='area-edit' class='data_box' style='margin-top: 4px; display: none;'></div>";
    echo "<div class='area-button' id='master-button'>";
    echo "<input type='button' value='Simpan Dokumen' onclick=\"xajax_saveSO(xajax.getFormValues('frmSO'));\">";
    echo "<input type='button' value='Tambah Baris' onclick=\"xajax_editForm();\">";
    echo "<input type='button' value='Batal' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}&pkey[c_order_id]={$data['c_order_id']}'\">";
    echo "</div>";
    echo "<div style='text-align: right; margin-top: 10px;'>{$mandatory} = Wajib diisi</div>";
} else {
    echo "<div class='area-button'>";
    echo "<input type='button' value='Kembali' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}'\">";
    echo "<input" . ($data['status'] == 'C' ? ' disabled' : '')  . " type='button' value='Edit' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}&pkey[c_order_id]={$data['c_order_id']}&mode=edit'\">";
    echo "<input" . ($data['status'] == 'C' ? ' disabled' : '')  . " type='button' value='Close SO' onclick=\"xajax_closeSO('{$data['c_order_id']}');\">";
    echo "<input" . ($data['status'] == 'O' ? ' disabled' : '')  . " type='button' value='Open SO' onclick=\"xajax_openSO('{$data['c_order_id']}');\">";
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

function setProduct(id, code, name, desc) {
    var txt_code = document.getElementById('product_code');
    var txt_name = document.getElementById('product_name');
    var txt_desc = document.getElementById('item_description');
    var hid_id = document.getElementById('m_product_id');
    txt_code.value = code;
    txt_name.value = name;
    txt_desc.value = desc;
    hid_id.value = id;
}

<?php if ($_REQUEST['mode'] == 'edit') { ?>
$(function() {
    $("#order_date").datepicker({dateFormat: '<?php echo $APP_DATE_FORMAT_JAVA; ?>'});
});
<?php } ?>

xajax_showLines('<?php echo $data['c_order_id'] ?>', '<?php echo $_REQUEST['mode']; ?>');

//-->
</script>