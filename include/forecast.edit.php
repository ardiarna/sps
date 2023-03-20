<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.org>
 * Nov 23, 2013 11:35:10 PM
 */

if ($_REQUEST['mode'] != 'edit') {
    $readonly = ' readonly="readonly"';
} else {
    $select_partner = "<img onclick=\"popupReference('business-partner');\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'>";
}

$data = npl_fetch_table(
        "SELECT m_forecast.*, partner_name "
        . "FROM m_forecast "
        . "JOIN c_bpartner USING (c_bpartner_id) "
        . "WHERE m_forecast_id = '{$_REQUEST['pkey']['m_forecast_id']}'");
$period_s = npl_format_period($data['period_start']);
$period_e = npl_format_period($data['period_end']);

echo "<form id='frmFC'>";
echo "<div class='data_box'>";
echo "<input type='hidden' id='m_forecast_id' name='m_forecast_id' value='{$data['m_forecast_id']}'>";
echo "<input type='hidden' id='c_bpartner_id' name='c_bpartner_id' value='{$data['c_bpartner_id']}'>";
echo "<table width='100%'>";
echo "<tr>";
echo "<td width='12%'>Nomor Dokumen</td>";
echo "<td width='36%'><input " . ($data['document_no'] ? 'readonly="readonly"' : 'disabled') . " name='document_no' type='text' size='15' value=\"" . ($data['document_no'] ? $data['document_no'] : $autonumber) . "\"></td>";
echo "<td width='4%'></td>";
echo "<td width='12%'>Periode {$mandatory}</td>";
echo "<td width='36%'><input{$readonly} id='period-s' name='period-s' type='text' maxlength='10' style='text-align: center; width: 110px;' value='{$period_s}'> s/d <input{$readonly} id='period-e' name='period-e' type='text' maxlength='10' style='text-align: center; width: 110px;' value='{$period_e}'></td>";
echo "</tr>";
echo "<tr>";
echo "<td>Customer {$mandatory}</td>";
echo "<td><input readonly='readonly' type='text' id='partner_name' size='30' value=\"{$data['partner_name']}\">{$select_partner}</td>";
echo "<td></td>";
echo "<td>Notes</td>";
echo "<td><input{$readonly} type='text' name='notes' size='30' value=\"{$data['notes']}\"></td>";
echo "</tr>";
echo "</table>";
echo "</div>";

echo "<div id='area-lines' style='margin-top: 4px;'></div>";

echo "</form>";
?>
<script type="text/javascript">
<!--
xajax_showLines(<?php echo $_REQUEST['pkey']['m_forecast_id']; ?>, '<?php echo $_REQUEST['mode']; ?>');
//-->
</script>
<?php

if ($_REQUEST['mode'] == 'edit') {
?>
<style>
.ui-datepicker-calendar { display: none; }
</style>
<script type="text/javascript">
<!--
$(function() {
    $("#period-s").datepicker(
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
    $("#period-e").datepicker(
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
    
function setBusinessPartner(id, name) {
    var txt_name = document.getElementById('partner_name');
    var hid_id = document.getElementById('c_bpartner_id');
    txt_name.value = name;
    hid_id.value = id;
}

function addProduct(id) {
    xajax_addProduct(<?php echo $_REQUEST['pkey']['m_forecast_id']; ?>, id);
}
    
//-->
</script>
<?php
    echo "<div id='area-edit' class='data_box' style='margin-top: 4px; display: none;'></div>";
    echo "<div class='area-button' id='master-button'>";
    echo "<input type='button' value='Simpan Dokumen' onclick=\"xajax_saveFC(xajax.getFormValues('frmFC'));\">";
    if ($_REQUEST['pkey']['m_forecast_id'] > 0) echo "<input type='button' value='Tambah Item' onclick=\"popupReference('product-fc');\">";
    if ($_REQUEST['pkey']['m_forecast_id'] > 0) {
        echo "<input type='button' value='Batal' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}&pkey[m_forecast_id]={$_REQUEST['pkey']['m_forecast_id']}'\">";
    } else {
        echo "<input type='button' value='Batal' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}'\">";
    }
    echo "</div>";
    echo "<div style='text-align: right; margin-top: 10px;'>{$mandatory} = Wajib diisi</div>";
} else {
    echo "<div class='area-button'>";
    echo "<input type='button' value='Kembali' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}'\">";
    echo "<input" . ($data['status'] == 'C' ? ' disabled' : '')  . " type='button' value='Edit' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}&pkey[m_forecast_id]={$_REQUEST['pkey']['m_forecast_id']}&mode=edit'\">";
    echo "</div>";
}

?>