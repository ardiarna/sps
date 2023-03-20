<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.org>
 * Nov 23, 2013 11:35:10 PM
 */

// load data to session
if ($_REQUEST['mode'] == 'edit' && $_SESSION[$APP_ID]['forecast']['c_forecast_id'] == $_REQUEST['pkey']['c_forecast_id']) {
    $data = $_SESSION[$APP_ID]['forecast'];
} else {
    //ambil data dari c_forecast
    $data = npl_fetch_table(
            "SELECT *
            FROM c_forecast
            JOIN c_forecast_line USING (c_forecast_id)
            JOIN c_bpartner USING (c_bpartner_id)
            JOIN app_org ON(c_forecast.app_org_id=app_org.app_org_id)
            WHERE c_forecast_id = '{$_REQUEST['pkey']['c_forecast_id']}'");
            
    //ambil data dari c_forecast_line
    $rsx = mysql_query(
            "SELECT * 
            FROM c_forecast_line
            JOIN m_product USING (m_product_id)
            WHERE c_forecast_id = '{$_REQUEST['pkey']['c_forecast_id']}'
            ORDER BY quantity",
            $APP_CONNECTION);
            //$periode = npl_format_period($rsx['periode']);
    $data['lines'] = array();
    
    while ($dtx = mysql_fetch_array($rsx, MYSQL_ASSOC)) $data['lines'][] = $dtx;
    mysql_free_result($rsx);
    $_SESSION[$APP_ID]['forecast'] = $data;
}

if ($_REQUEST['mode'] != 'edit') {
    $readonly = ' readonly="readonly"';
} else {
    $select_partner = "<img onclick=\"popupReference('business-partner-c');\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'>";
}


if ($_SESSION[$GLOBALS['APP_ID']]['trx.forecast']['error']) {
    echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['trx.forecast']['error']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['trx.forecast']['error']);
}

if ($_SESSION[$GLOBALS['APP_ID']]['trx.forecast']['info']) {
    echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['trx.forecast']['info']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['trx.forecast']['info']);
}

//$data['periode'] = empty($data['periode']) ? date($APP_DATE_FORMAT) : $data['periode'];
$periode = npl_format_period($data['periode']);        
echo "<div class='data_box'>";
echo "<form id='frmFC'>";
echo "<input type='hidden' id='c_forecast_id' name='c_forecast_id' value='{$data['c_forecast_id']}'>";
echo "<input type='hidden' id='c_bpartner_id' name='c_bpartner_id' value='{$data['c_bpartner_id']}'>";
echo "<table width='100%'>";
echo "<tr>";
echo "<td width='12%'>Nomor Dokumen</td>";
echo "<td width='36%'><input " . ($data['document_no'] ? 'readonly="readonly"' : 'disabled') . " name='document_no' type='text' size='15' value=\"" . ($data['document_no'] ? $data['document_no'] : $autonumber) . "\"></td>";
echo "<td width='4%'></td>";
echo "<td width='12%'>Periode{$mandatory}</td>";
echo "<td width='36%'><input{$readonly} id='periode' name='periode' type='text' size='15' value='{$periode}' style='text-align: center; width: 110px;'></td>";
echo "</tr>";
echo "<tr>";
echo "<td>Customer {$mandatory}</td>";
echo "<td><input readonly='readonly' type='text' id='partner_name' size='30' value=\"{$data['partner_name']}\">{$select_partner}</td>";
echo "<td></td>";
echo "<td>Notes</td>";
echo "<td><input{$readonly} type='text' name='notes' size='30' value=\"{$data['notes']}\"></td>";
echo "</tr>";
/*echo "<tr>";
echo "<td>Tgl Order {$mandatory}</td>";
echo "<td><input{$readonly} name='order_date' id='order_date' type='text' size='10' value=\"" . (cgx_emptydate($data['order_date']) ? '' : date($APP_DATE_FORMAT, strtotime($data['order_date']))) . "\"></td>";
echo "<td></td>";
echo "<td>Status</td>";
echo "<td><input readonly='readonly' type='text' size='10' value=\"" . ($data['status'] == 'C' ? 'Close' : 'Open') . "\"></td>";
*/echo "</tr>";
echo "<tr>";
//echo "<td>Organization</td>";
//echo "<td>". cgx_form_select('app_org_id', "SELECT app_org_id, organization FROM app_org", $data['app_org_id'], FALSE, "id='app_org_id'") ."</td>";
echo "</tr>";
echo "</table>";
echo "</form>";
echo "</div>";

echo "<div id='area-lines' style='margin-top: 4px;'></div>";
if ($_REQUEST['mode'] == 'edit') {
    echo "<div id='area-edit' class='data_box' style='margin-top: 4px; display: none;'></div>";
    echo "<div class='area-button' id='master-button'>";
    echo "<input type='button' value='Simpan Dokumen' onclick=\"xajax_saveFC(xajax.getFormValues('frmFC'));\">";
    echo "<input type='button' value='Tambah Baris' onclick=\"xajax_editForm();\">";
    echo "<input type='button' value='Batal' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}&pkey[c_forecast_id]={$data['c_forecast_id']}'\">";
    echo "</div>";
    echo "<div style='text-align: right; margin-top: 10px;'>{$mandatory} = Wajib diisi</div>";
} else {
    echo "<div class='area-button'>";
    echo "<input type='button' value='Kembali' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}'\">";
    echo "<input type='button' value='Edit' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}&pkey[c_forecast_id]={$data['c_forecast_id']}&mode=edit'\">";
//    echo "<input" . ($data['status'] == 'C' ? ' disabled' : '')  . " type='button' value='Close SO' onclick=\"xajax_closeSO('{$data['c_forecast_id']}');\">";
    echo "</div>";
}

//$periode = npl_format_period($data['periode']);
//print_r($periode);
//exit;

?>
<?php if ($_REQUEST['mode'] == 'edit') { ?>
<style>
.ui-datepicker-calendar { display: none; }
</style>
<?php } ?>

<script type="text/javascript">
<!--
<?php if ($_REQUEST['mode'] == 'edit') { ?>
$(function() {
    $("#periode").datepicker(
        {
        changeMonth: true,
        changeYear: true,
        showButtonPanel: true,
        dateFormat: 'mm-yy',
        onClose: function(dateText, inst) {
            var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
            var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
            $(this).datepicker('setDate', new Date(year, month, 1));
            $(this).datepicker('refresh');
        },
        beforeShow: function() { 
            if ($(this).val().length > 0) {
                var parts = $(this).val().split('-');
                var month = parts[0] - 1;
                var year = parts[1];
                $(this).datepicker('option', 'defaultDate', new Date(year, month, 1));
                $(this).datepicker('setDate', new Date(year, month, 1));
            }
        }
    });
});
<?php } ?>
function setBusinessPartner(id, name) {
    var txt_name = document.getElementById('partner_name');
    var hid_id = document.getElementById('c_bpartner_id');
    txt_name.value = name;
    hid_id.value = id;
}

function setProduct(id, code, name, desc) {
    var txt_code = document.getElementById('product_code');
    var txt_name = document.getElementById('product_name');
    var txt_desc = document.getElementById('description');
    var hid_id = document.getElementById('m_product_id');
    txt_code.value = code;
    txt_name.value = name;
    txt_desc.value = desc;
    hid_id.value = id;
}



xajax_showLines('<?php echo $data['c_forecast_id'] ?>', '<?php echo $_REQUEST['mode']; ?>');

//-->
</script>
