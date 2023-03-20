<?php

/*
 * rpt
 * Azwari Nugraha <nugraha@duabelas.org>
 * Dec 8, 2013 3:46:32 PM
 */

echo "<div class='title'>Laporan Delay Delivery</div>";

echo "<div class='data_box'>";
echo "<input id='c_bpartner_id' name='c_bpartner_id' type='hidden' value=\"{$cgx_data['c_bpartner_id']}\" style='text-align: left;' />";
echo "<table>";
echo "<tr>";
echo "<td style='padding-right: 70px;'>Customer</td>";
echo "<td><input id='partner_name' name='partner_name' type='text' value=\"{$cgx_data['partner_name']}\" size='40' maxlength='40' style='text-align: left;' /><img onclick=\"popupReference('business-partner');\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'></td>";
echo "</tr><tr>";
echo "<td>Output</td>";
echo "<td><select id='rtype'>";
foreach ($JASPER_MIME as $type => $mime) {
    echo "<option" . ($type == 'pdf' ? ' selected' : '') . " value='{$type}'>{$type}</option>";
}
echo "</select></td>";
echo "</tr><tr>";
echo "<td colspan='2'><hr noshade size='1'></td>";
echo "</tr><tr>";
echo "<td>&nbsp;</td>";
echo "<td><input type='button' value='Submit' style='width: 120px;' onclick=\"getReport();\"></td>";
echo "</tr></table>\n";
echo "</div>";

?>

<script type="text/javascript">
<!--

function setBusinessPartner(id, name) {
    var txt_name = document.getElementById('partner_name');
    var hid_id = document.getElementById('c_bpartner_id');
    txt_name.value = name;
    hid_id.value = id;
}

function getReport() {

    form = document.createElement("form");
    form.setAttribute("method", "post");
    form.setAttribute("action", "report.php");
    
    textField = document.createElement("input");
    textField.setAttribute("type", "hidden");
    textField.setAttribute("name", "path");
    textField.setAttribute("value", "/reports/SPS/Delay_Delivery");
    form.appendChild(textField);

    textField = document.createElement("input");
    textField.setAttribute("type", "hidden");
    textField.setAttribute("name", "type");
    textField.setAttribute("value", document.getElementById('rtype').value);
    form.appendChild(textField);

    textField = document.createElement("input");
    textField.setAttribute("type", "hidden");
    textField.setAttribute("name", "param[REPORT_PARTNER]");
    textField.setAttribute("value", document.getElementById('c_bpartner_id').value);
    form.appendChild(textField);

    textField = document.createElement("input");
    textField.setAttribute("type", "hidden");
    textField.setAttribute("name", "param[REPORT_USER]");
    textField.setAttribute("value", "<?php echo user('user_fullname'); ?>");
    form.appendChild(textField);
    
    textField = document.createElement("input");
    textField.setAttribute("type", "hidden");
    textField.setAttribute("name", "param[REPORT_ORG]");
    textField.setAttribute("value", "<?php echo org(); ?>");
    form.appendChild(textField);

    textField = document.createElement("input");
    textField.setAttribute("type", "hidden");
    textField.setAttribute("name", "param[REPORT_ORG_NAME]");
    textField.setAttribute("value", "<?php echo role('organization'); ?>");
    form.appendChild(textField);

    document.body.appendChild(form);
    form.submit();    
}

//-->
</script>
