<?php

/*
 * rpt
 * Azwari Nugraha <nugraha@duabelas.org>
 * Dec 8, 2013 3:46:32 PM
 */

echo "<div class='title'>Laporan Forecast vs SO</div>";

echo "<div class='data_box'>";
echo "<input id='c_bpartner_id' name='c_bpartner_id' type='hidden' value=\"{$cgx_data['c_bpartner_id']}\" style='text-align: left;' />";
echo "<table>";
echo "<tr>";
echo "<td style='padding-right: 70px;'>Customer</td>";
echo "<td><input id='partner_name' name='partner_name' type='text' value=\"{$cgx_data['partner_name']}\" size='40' maxlength='40' style='text-align: left;' /><img onclick=\"popupReference('business-partner');\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'></td>";
echo "</tr><tr>";
echo "</tr><td colspan='2'>&nbsp;</td><tr>";
echo "<td>Bulan Pertama</td>";
echo "<td><input{$readonly} id='period-1' name='period-1' type='text' maxlength='10' style='text-align: center; width: 110px;'></td>";
echo "</tr><tr>";
echo "<td>Bulan Kedua</td>";
echo "<td><input{$readonly} id='period-2' name='period-2' type='text' maxlength='10' style='text-align: center; width: 110px;'></td>";
echo "</tr><tr>";
echo "<td>Bulan Ketiga</td>";
echo "<td><input{$readonly} id='period-3' name='period-3' type='text' maxlength='10' style='text-align: center; width: 110px;'></td>";
echo "</tr><tr>";
echo "</tr><td colspan='2'>&nbsp;</td><tr>";
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

function getReport() {

    var periode_1 = document.getElementById('period-1').value;
    month_1 = periode_1.substring(0,2);
    year_1 = periode_1.substring(3,7);
    periode_1 = year_1 + "-" + month_1 + "-01";

    var periode_2 = document.getElementById('period-2').value;
    month_2 = periode_2.substring(0,2);
    year_2 = periode_2.substring(3,7);
    periode_2 = year_2 + "-" + month_2 + "-01";

    var periode_3 = document.getElementById('period-3').value;
    month_3 = periode_3.substring(0,2);
    year_3 = periode_3.substring(3,7);
    periode_3 = year_3 + "-" + month_3 + "-01";

    form = document.createElement("form");
    form.setAttribute("method", "post");
    form.setAttribute("action", "report.php");
    
    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "path");
    hiddenField.setAttribute("value", "/reports/SPS/Forecast_So");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "type");
    hiddenField.setAttribute("value", document.getElementById('rtype').value);
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "param[REPORT_PARTNER]");
    hiddenField.setAttribute("value", document.getElementById('c_bpartner_id').value);
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "param[REPORT_PERIOD_1]");
    hiddenField.setAttribute("value", periode_1);
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "param[REPORT_PERIOD_2]");
    hiddenField.setAttribute("value", periode_2);
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "param[REPORT_PERIOD_3]");
    hiddenField.setAttribute("value", periode_3);
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "param[REPORT_MONTH_1]");
    hiddenField.setAttribute("value", month_1);
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "param[REPORT_MONTH_2]");
    hiddenField.setAttribute("value", month_2);
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "param[REPORT_MONTH_3]");
    hiddenField.setAttribute("value", month_3);
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "param[REPORT_YEAR_1]");
    hiddenField.setAttribute("value", year_1);
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "param[REPORT_YEAR_2]");
    hiddenField.setAttribute("value", year_2);
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "param[REPORT_YEAR_3]");
    hiddenField.setAttribute("value", year_3);
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "param[REPORT_USER]");
    hiddenField.setAttribute("value", "<?php echo user('user_fullname'); ?>");
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

    document.body.appendChild(form);
    form.submit();    
}

$(function() {
    $("#period-1").datepicker(
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
    $("#period-2").datepicker(
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
    $("#period-3").datepicker(
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

//-->
</script>