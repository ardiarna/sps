<?php

/*
 * rpt
 * Azwari Nugraha <nugraha@duabelas.org>
 * Dec 8, 2013 3:46:32 PM
 */

$def_fr =  date('d-m-Y', mktime(0, 0, 0, date('n') - 1, 1));
$def_to =  date('d-m-Y', mktime(0, 0, 0, date('n') + 1, 0));

echo "<div class='title'>Laporan Schedule Delivery vs Stock</div>";

echo "<div class='data_box'>";
echo "<input type='hidden' id='c_bpartner_id' name='c_bpartner_id'>";
echo "<table>";
echo "<tr>";
echo "<td style='padding-right: 40px;'>Customer</td>";
echo "<td><input id='c1' type='radio' checked name='cust' value='1' onclick=\"setCus(this.value);\"></td>";
echo "<td valign='bottom'><label for='c1'>All Customer</label></td>";
echo "</tr>";

echo "<tr>";
echo "<td></td>";
echo "<td><input id='c2' type='radio' name='cust' value='2' onclick=\"setCus(this.value);\"></td>";
echo "<td><input disabled type='text' id='partner_name' onclick=\"popupReference('business-partner');\" size='40'></td>";
echo "</tr><tr><td colspan='3'>&nbsp;</td></tr>";

echo "<tr>";
echo "<td style='padding-right: 40px;'>Rencana Tgl Kirim</td>";
echo "<td><input id='r1' type='radio' checked name='mode' value='1' onclick=\"setMode(this.value);\"></td>";
echo "<td><select id='mm'>";
for ($m = 1; $m <= 12; $m++) {
    $mo = date('F', mktime(0, 0, 0, $m));
    $selected = date('F', mktime(0, 0, 0, $m)) == date('F') ? ' selected' : '';
    echo "<option{$selected} value='{$m}'>{$mo}</option>";
}
echo "</select>";
echo "<input id='yy' type='text' maxlength='4' style='width: 50px; text-align: center;' value='" . date('Y') . "'></td>";
echo "</tr>";

echo "<tr>";
echo "<td></td>";
echo "<td><input id='r2' type='radio' name='mode' value='2' onclick=\"setMode(this.value);\"></td>";
echo "<td><input id='fr' disabled type='text' style='width: 120px; text-align: center;' maxlength='10' value='{$def_fr}'>";
echo " s/d <input id='to' disabled type='text' style='width: 120px; text-align: center;' maxlength='10' value='{$def_to}'>";
echo "</td>";
echo "</tr>";

echo "<tr>";
echo "<td style='height: 20px;'></td>";
echo "<td><input id='r3' type='radio' name='mode' value='3' onclick=\"setMode(this.value);\"></td>";
echo "<td valign='bottom'><label for='r3'>Hari ini (" . date("d-m-Y") . ")</label></td>";
echo "</tr>";

echo "<tr>";
echo "<td>Output</td>";
echo "<td colspan='2'><select id='rtype'>";
foreach ($JASPER_MIME as $type => $mime) {
    echo "<option" . ($type == 'pdf' ? ' selected' : '') . " value='{$type}'>{$type}</option>";
}
echo "</select></td>";
echo "</tr>";

echo "<tr>";
echo "<td colspan='3'><hr noshade size='1'></td>";
echo "</tr>";

echo "<tr>";
echo "<td>&nbsp;</td>";
echo "<td colspan='2'><input type='button' value='Submit' style='width: 120px;' onclick=\"getReport();\"></td>";
echo "</tr>";

echo "</table>";
echo "</div>";

?>
<script type="text/javascript">
<!--

function setBusinessPartner(id, name) {
    var txt_name = document.getElementById('partner_name');
    var hid_id = document.getElementById('c_bpartner_id');
    txt_name.value = id + " - " + name;
    hid_id.value = id;
}

function setCus(m) {
    document.getElementById('partner_name').disabled = m != 2;
}


function setMode(m) {
    document.getElementById('mm').disabled = m != 1;
    document.getElementById('yy').disabled = m != 1;
    document.getElementById('fr').disabled = m != 2;
    document.getElementById('to').disabled = m != 2;
}

function getReport() {
    if (document.getElementById('r1').checked) {
        fr = new Date(document.getElementById('yy').value, document.getElementById('mm').value - 1, 1);
        to = new Date(document.getElementById('yy').value, document.getElementById('mm').value, 0);
    } else if (document.getElementById('r3').checked) {
        fr = new Date();
        to = new Date();
    } else {
        afr = document.getElementById('fr').value.split('-');
        ato = document.getElementById('to').value.split('-');
        fr = new Date(afr[2], afr[1] - 1, afr[0]);
        to = new Date(ato[2], ato[1] - 1, ato[0]);
    }
    jasperfr = [fr.getFullYear(), fr.getMonth()+1, fr.getDate()].join('-');
    jasperto = [to.getFullYear(), to.getMonth()+1, to.getDate()].join('-');
    
    form = document.createElement("form");
    form.setAttribute("method", "post");
    form.setAttribute("action", "report.php");
    
    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "path");
    if (document.getElementById('c1').checked) {
        hiddenField.setAttribute("value", "/reports/SPS/Sch_Delivery_Stock");
    }else{
        hiddenField.setAttribute("value", "/reports/SPS/Sch_Delivery_Stock_F");
    }
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "param[REPORT_DATE_START]");
    hiddenField.setAttribute("value", jasperfr);
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "param[REPORT_DATE_END]");
    hiddenField.setAttribute("value", jasperto);
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "param[REPORT_USER]");
    hiddenField.setAttribute("value", "<?php echo user('user_fullname'); ?>");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "type");
    hiddenField.setAttribute("value", document.getElementById('rtype').value);
    form.appendChild(hiddenField);
    
    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "param[REPORT_ORG]");
    hiddenField.setAttribute("value", "<?php echo org(); ?>");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "param[REPORT_ORG_NAME]");
    hiddenField.setAttribute("value", "<?php echo role('organization'); ?>");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "param[REPORT_PARTNER]");
    hiddenField.setAttribute("value", document.getElementById('c_bpartner_id').value);
    form.appendChild(hiddenField);    

    document.body.appendChild(form);
    form.submit();    
}

$(function() {$("#fr").datepicker({dateFormat: 'dd-mm-yy'});});
$(function() {$("#to").datepicker({dateFormat: 'dd-mm-yy'});});

//-->
</script>