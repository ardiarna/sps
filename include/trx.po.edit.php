<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.org>
 * Nov 23, 2013 11:35:10 PM
 */

// load data to session
if ($_REQUEST['mode'] == 'edit' && $_SESSION[$APP_ID]['po']['c_po_id'] == $_REQUEST['pkey']['c_po_id']) {
    $data = $_SESSION[$APP_ID]['po'];
} else {
    $data = npl_fetch_table(
            "SELECT c_po.*, partner_name, period_start, period_end, m_forecast.document_no fc_number
            FROM c_po
            JOIN c_bpartner USING (c_bpartner_id)
            LEFT JOIN m_forecast USING (m_forecast_id)
            WHERE c_po_id = '{$_REQUEST['pkey']['c_po_id']}'");
    $rsx = mysql_query(
            "SELECT *
            FROM c_po_line
            JOIN m_product USING (m_product_id)
            WHERE c_po_id = '{$_REQUEST['pkey']['c_po_id']}'",
            $APP_CONNECTION);
    $data['lines'] = array();
    while ($dtx = mysql_fetch_array($rsx, MYSQL_ASSOC)) $data['lines'][] = $dtx;
    mysql_free_result($rsx);
    $_SESSION[$APP_ID]['po'] = $data;
}

if ($_REQUEST['mode'] != 'edit') {
    $readonly = ' readonly="readonly"';
} else {
    $select_partner = "<img onclick=\"popupReference('business-partner');\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'>";
}


if ($_SESSION[$GLOBALS['APP_ID']]['trx.po']['error']) {
    echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['trx.po']['error']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['trx.po']['error']);
}

if ($_SESSION[$GLOBALS['APP_ID']]['trx.po']['info']) {
    echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['trx.po']['info']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['trx.po']['info']);
}

$data['po_date'] = empty($data['po_date']) ? date($APP_DATE_FORMAT) : $data['po_date'];
        
echo "<div class='data_box'>";
echo "<form id='frmPO'>";
echo "<input type='hidden' id='c_po_id' name='c_po_id' value='{$data['c_po_id']}'>";
echo "<input type='hidden' id='c_bpartner_id' name='c_bpartner_id' value='{$data['c_bpartner_id']}'>";
echo "<table width='100%'>";
echo "<tr>";
echo "<td width='12%'>Nomor Dokumen</td>";
echo "<td width='36%'><input " . ($data['document_no'] ? 'readonly="readonly"' : 'disabled') . " name='document_no' type='text' size='10' value=\"" . ($data['document_no'] ? $data['document_no'] : $autonumber) . "\"></td>";
echo "<td width='4%'></td>";
echo "<td width='12%'>Status</td>";
echo "<td width='36%'><input type='text' value=\"" . ($data['allocated'] == 'Y' ? 'Sudah Dialokasikan' : 'Belum Dialokasikan') . "\" style='width: 160px;' readonly='readonly'></td>";
echo "</tr>";
echo "<tr>";
echo "<td>Customer {$mandatory}</td>";
echo "<td><input readonly='readonly' type='text' id='partner_name' size='30' value=\"{$data['partner_name']}\">{$select_partner}</td>";
echo "<td></td>";
if ($data['allocated'] == 'Y') {
    $rsx = mysql_query("SELECT * FROM c_order WHERE c_po_id = '{$data['c_po_id']}'", $APP_CONNECTION);
    $no_so = '';
    while ($dtx = mysql_fetch_array($rsx)) {
        if ($no_so) $no_so .= ", ";
        $no_so .= $dtx['document_no'];
    }
    mysql_free_result($rsx);
}
echo "<td>Nomor SO</td>";
echo "<td><input type='text' value='{$no_so}' readonly='readonly' style='width: 320px;'></td>";
echo "</tr>";
echo "<tr>";
echo "<td>Tanggal PO {$mandatory}</td>";
echo "<td><input{$readonly} style='text-align: center;' name='po_date' id='po_date' type='text' size='10' value=\"" . (cgx_emptydate($data['po_date']) ? '' : date($APP_DATE_FORMAT, strtotime($data['po_date']))) . "\"></td>";
echo "<td></td>";
echo "<td>Nomor Forecast</td>";
echo "<td><input type='text' value='{$data['fc_number']}' readonly='readonly' style='width: 160px;'></td>";
echo "</tr>";

if ($_REQUEST['pkey']['c_po_id'] > 0) {
    $hide_forecast = " style='display: none;'";
}

echo "<tr{$hide_forecast}>";
echo "<td></td>";
echo "<td><table cellspacing='0' cellpadding='0'><tr><td><input" . ($data['m_forecast_id'] ? ' checked' : '') . " type='checkbox' id='chkFC' name='chkFC' onclick=\"toggleFC(this.checked);\" " . ($_REQUEST['mode'] == 'edit' ? '' : ' disabled') . "></td><td><label for='chkFC'>Input forecast</label></td></tr></table></td>";
echo "<td></td>";
echo "<td></td>";
echo "<td></td>";
echo "</tr>";

if ($data['period_start']) {
    $data['period_start'] = npl_format_period($data['period_start']);
} else {
    $data['period_start'] = npl_format_period(date('Y-m-d', mktime(NULL, NULL, NULL, date('n') + 1)));
}
if ($data['period_end']) {
    $data['period_end'] = npl_format_period($data['period_end']);
} else {
    $data['period_end'] = npl_format_period(date('Y-m-d', mktime(NULL, NULL, NULL, date('n') + 1)));
}

echo "<tr{$hide_forecast}>";
echo "<td></td>";
echo "<td><table cellspacing='0' cellpadding='0'><tr><td><input id='txtFC1' type='text' size='10' style='text-align: center;' disabled value=\"{$data['period_start']}\"></td><td align='center' width='24'> s/d </td><td><input id='txtFC2' type='text' size='10' style='text-align: center;' disabled value=\"{$data['period_end']}\"></td></tr></table></td>";
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
    echo "<input type='button' value='Simpan Dokumen' onclick=\"xajax_savePO(xajax.getFormValues('frmPO'));\">";
    echo "<input type='button' value='Tambah Baris' onclick=\"xajax_editForm();\">";
    echo "<input type='button' value='Batal' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}&pkey[c_po_id]={$data['c_po_id']}'\">";
    echo "</div>";
    echo "<div style='text-align: right; margin-top: 10px;'>{$mandatory} = Wajib diisi</div>";
} else {
    echo "<div class='area-button'>";
    echo "<input type='button' value='Kembali' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}'\">";
    echo "<input" . ($data['allocated'] == 'Y' ? ' disabled' : '')  . " type='button' value='Edit' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}&pkey[c_po_id]={$data['c_po_id']}&mode=edit'\">";
    echo "<input" . ($data['allocated'] == 'Y' ? ' disabled' : '')  . " type='button' value='Alokasikan ke Sales Order' onclick=\"window.location = 'module.php?m=trx.po.allocate&c_po_id={$data['c_po_id']}'\">";
    echo "</div>";
}

?>

<style>
.ui-datepicker-calendar { display: none; }
</style>

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
    var hid_id = document.getElementById('m_product_id');
    txt_code.value = code;
    txt_name.value = name;
    hid_id.value = id;
}

function toggleFC(e) {
    if (!e) {
        document.getElementById('area-edit').style.display = 'none';
        document.getElementById('master-button').style.display = '';
    }
    document.getElementById('txtFC1').disabled = !e;
    document.getElementById('txtFC2').disabled = !e;
    xajax_showLines('<?php echo $data['c_po_id'] ?>', '<?php echo $_REQUEST['mode']; ?>', document.getElementById('chkFC').checked, document.getElementById('txtFC1').value, document.getElementById('txtFC2').value);
}

<?php if ($_REQUEST['mode'] == 'edit') { ?>
$(function() {
    $("#po_date").datepicker({dateFormat: '<?php echo $APP_DATE_FORMAT_JAVA; ?>'});
    $("#txtFC1").datepicker(
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
            xajax_showLines('<?php echo $data['c_po_id'] ?>', '<?php echo $_REQUEST['mode']; ?>', document.getElementById('chkFC').checked, document.getElementById('txtFC1').value, document.getElementById('txtFC2').value);            
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
    $("#txtFC2").datepicker(
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
            xajax_showLines('<?php echo $data['c_po_id'] ?>', '<?php echo $_REQUEST['mode']; ?>', document.getElementById('chkFC').checked, document.getElementById('txtFC1').value, document.getElementById('txtFC2').value);            
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

xajax_showLines('<?php echo $data['c_po_id'] ?>', '<?php echo $_REQUEST['mode']; ?>', document.getElementById('chkFC').checked, document.getElementById('txtFC1').value, document.getElementById('txtFC2').value);

//-->
</script>