<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.org>
 * Nov 23, 2013 11:35:10 PM
 */

// load data to session
if ($_REQUEST['mode'] == 'edit' && $_SESSION[$APP_ID]['ppcwo']['c_wo_id'] == $_REQUEST['pkey']['c_wo_id']) {
    $data = $_SESSION[$APP_ID]['ppcwo'];
} else {
    $data = npl_fetch_table(
            "SELECT c_wo.c_wo_id, c_wo.document_no wo
            FROM c_wo WHERE c_wo_id = '{$_REQUEST['pkey']['c_wo_id']}'");
    
    $sql = "SELECT DISTINCT working_date FROM c_wo_line JOIN c_wo USING (c_wo_id) WHERE c_wo.c_wo_id = {$_REQUEST['pkey']['c_wo_id']} ORDER BY working_date";
    $rsq = mysql_query($sql , $APP_CONNECTION);
    $n = 0;
    while ($dtq = mysql_fetch_array($rsq, MYSQL_ASSOC)) {
         $n++;
         $datax[$n] = $dtq['working_date'];    
    }
    for ($i=1; $i<=7 ; $i++) {
        if ($datax[$i]) {
            $datay[$i] = $datax[$i];
        }else{
            $datay[$i] = "";
        }
    }
    $rsx = mysql_query(
            "SELECT c_wo_id, m_machine.machine_name, partner_name, m_product.product_code, m_product.spec, m_product.od, m_product.thickness, m_product.length, mate.od od_mat, mate.thickness thickness_mat, mate.length length_mat, SUM(c_wo_line.quantity) qty,
                SUM( CASE WHEN c_wo_line.working_date = '{$datay[1]}' THEN c_wo_line.quantity ELSE 0 END) as day1,
                SUM( CASE WHEN c_wo_line.working_date = '{$datay[2]}' THEN c_wo_line.quantity ELSE 0 END) as day2,
                SUM( CASE WHEN c_wo_line.working_date = '{$datay[3]}' THEN c_wo_line.quantity ELSE 0 END) as day3,
                SUM( CASE WHEN c_wo_line.working_date = '{$datay[4]}' THEN c_wo_line.quantity ELSE 0 END) as day4,
                SUM( CASE WHEN c_wo_line.working_date = '{$datay[5]}' THEN c_wo_line.quantity ELSE 0 END) as day5,
                SUM( CASE WHEN c_wo_line.working_date = '{$datay[6]}' THEN c_wo_line.quantity ELSE 0 END) as day6,
                SUM( CASE WHEN c_wo_line.working_date = '{$datay[7]}' THEN c_wo_line.quantity ELSE 0 END) as day7
                FROM c_wo_line
                JOIN c_production_plan ON (c_wo_line.c_production_plan_id=c_production_plan.c_production_plan_id)
                JOIN c_delivery_plan ON (c_production_plan.plan_ref = c_delivery_plan.c_delivery_plan_id)
                JOIN c_order_line ON (c_delivery_plan.order_line_ref = c_order_line.c_order_line_id)
                JOIN c_order USING (c_order_id)
                LEFT JOIN c_bpartner ON (c_order.c_bpartner_id = c_bpartner.c_bpartner_id)
                JOIN m_product ON ( c_wo_line.m_product_id=m_product.m_product_id) 
                LEFT JOIN m_material_requirement ON(c_wo_line.m_product_id=m_material_requirement.m_product_fg)
                LEFT JOIN m_product mate ON(m_material_requirement.m_product_material=mate.m_product_id)
                LEFT JOIN m_machine USING (m_machine_id)
                WHERE c_wo_id = '{$_REQUEST['pkey']['c_wo_id']}'
                GROUP BY m_product.product_code
                ORDER BY m_machine.machine_name",
            $APP_CONNECTION);
    $data['lines'] = array();
    while ($dtx = mysql_fetch_array($rsx, MYSQL_ASSOC)) $data['lines'][] = $dtx;
    mysql_free_result($rsx);
    $_SESSION[$APP_ID]['ppcwo'] = $data;
}

if ($_SESSION[$GLOBALS['APP_ID']]['trx.ppc-wo-rc1']['error']) {
    echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['trx.ppc-wo-rc1']['error']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['trx.ppc-wo-rc1']['error']);
}

if ($_SESSION[$GLOBALS['APP_ID']]['trx.ppc-wo-rc1']['info']) {
    echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['trx.ppc-wo-rc1']['info']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['trx.ppc-wo-rc1']['info']);
}

$data['m_inout_date'] = empty($data['m_inout_date']) ? date($APP_DATE_FORMAT) : $data['m_inout_date'];
echo "<div class='data_box'>";
echo "<form id='frmRR'>";
echo "<input type='hidden' id='c_wo_id' name='c_wo_id' value='{$data['c_wo_id']}'>";
echo "<table width='100%'>";
echo "<tr>";
echo "<td width='10%'>NO. WO</td>";
echo "<td><input readonly='readonly' type='text' name='wo' size='15' value=\"{$data['wo']}\"></td>";
echo "<td></td>";
echo "<td width='10%'>Mesin</td>";
echo "<td>" . cgx_filter('m_machine_id', "SELECT m_machine_id, machine_name FROM m_machine WHERE " . org_filter_master() . " ORDER BY machine_name", $data['m_machine_id'], FALSE) . "</td>";
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
}else {
    echo "<div class='area-button'>";
    echo "<input type='button' value='Kembali' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}'\">";
    //echo cgx_form_select('data[m_machine_id]', "SELECT m_machine_id, machine_name FROM m_machine WHERE  app_org_id = '3' ORDER BY machine_name", $cgx_data['m_machine_id'], FALSE, "id='data_m_machine_id'");
    echo "<input type='button' value='Cetak' style='width: 120px;' onclick=\"getReport();\">";   
    //echo "<input type='button' value='Edit' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}&pkey[m_inout_id]={$data['m_inout_id']}&mode=editH'\">";
    //echo "<input type='button' value='Cetak' onclick=\"window.location = 'report.php?path=/reports/SPS/Perintah_Kerja_No&param[REPORT_SPK]={$data['c_spk_id']}&type=pdf&param[REPORT_USER]=".user('user_fullname')."&param[REPORT_ORG_NAME]=".role('organization')."&fname={$data['spk']}'\">";
    echo "</div>";
}

?>
<script type="text/javascript">
<!--

function getReport() {
    form = document.createElement("form");
    form.setAttribute("method", "post");
    form.setAttribute("action", "report.php");
    
    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "path");
    hiddenField.setAttribute("value", "/reports/SPS/Work_Order_Permesin");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "param[REPORT_WO]");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['pkey']['c_wo_id']; ?>");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "fname");
    hiddenField.setAttribute("value", "<?php echo $data['wo']; ?>");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "param[REPORT_USER]");
    hiddenField.setAttribute("value", "<?php echo user('user_fullname'); ?>");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "type");
    hiddenField.setAttribute("value", 'pdf');
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
    hiddenField.setAttribute("name", "param[DAY_1]");
    hiddenField.setAttribute("value", "<?php echo $datay[1]; ?>");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "param[DAY_2]");
    hiddenField.setAttribute("value", "<?php echo $datay[2]; ?>");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "param[DAY_3]");
    hiddenField.setAttribute("value", "<?php echo $datay[3]; ?>");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "param[DAY_4]");
    hiddenField.setAttribute("value", "<?php echo $datay[4]; ?>");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "param[DAY_5]");
    hiddenField.setAttribute("value", "<?php echo $datay[5]; ?>");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "param[DAY_6]");
    hiddenField.setAttribute("value", "<?php echo $datay[6]; ?>");
    form.appendChild(hiddenField);


    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "param[MESIN]");
    hiddenField.setAttribute("value", document.getElementById('m_machine_id').value);
    form.appendChild(hiddenField);

    document.body.appendChild(form);
    form.submit();    
}

xajax_showLines('<?php echo $data['c_wo_id'] ?>', '<?php echo $_REQUEST['mode']; ?>');

//-->
</script>