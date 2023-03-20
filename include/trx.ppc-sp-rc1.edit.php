<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.org>
 * Nov 23, 2013 11:35:10 PM
 */

// load data to session
if ($_REQUEST['mode'] == 'edit' && $_SESSION[$APP_ID]['ppcsp']['c_spk_id'] == $_REQUEST['pkey']['c_spk_id']) {
    $data = $_SESSION[$APP_ID]['ppcsp'];
} else {
    $data = npl_fetch_table(
            "SELECT c_spk.c_spk_id, c_spk.document_no spk, spk_date, c_wo.document_no wo, m_machine.machine_name 
            FROM c_spk JOIN c_wo USING(c_wo_id) LEFT JOIN m_machine USING(m_machine_id)  
            WHERE c_spk_id = '{$_REQUEST['pkey']['c_spk_id']}'");
    $rsx = mysql_query(
            "SELECT c_spk_line.quantity, partner_name, fg.product_code, fg.spec, fg.od, fg.thickness, fg.length,  
            mat.od od_mat, mat.thickness thickness_mat, mat.length length_mat, (c_spk_line.quantity / m_material_requirement.multipleqty) quantity_mat 
            FROM c_spk_line 
            JOIN c_wo_line USING(c_wo_line_id) 
            JOIN c_production_plan ON (c_wo_line.c_production_plan_id=c_production_plan.c_production_plan_id)
            JOIN c_delivery_plan ON (c_production_plan.plan_ref = c_delivery_plan.c_delivery_plan_id)
            JOIN c_order_line ON (c_delivery_plan.order_line_ref = c_order_line.c_order_line_id)
            JOIN c_order USING (c_order_id)
            LEFT JOIN c_bpartner ON (c_order.c_bpartner_id = c_bpartner.c_bpartner_id)
            JOIN m_product fg ON (c_wo_line.m_product_id=fg.m_product_id)
            LEFT JOIN m_material_requirement ON (c_wo_line.m_product_id=m_material_requirement.m_product_fg)
            LEFT JOIN m_product mat ON (m_material_requirement.m_product_material=mat.m_product_id)
            WHERE c_spk_id = '{$_REQUEST['pkey']['c_spk_id']}'",
            $APP_CONNECTION);
    $data['lines'] = array();
    while ($dtx = mysql_fetch_array($rsx, MYSQL_ASSOC)) $data['lines'][] = $dtx;
    mysql_free_result($rsx);
    $_SESSION[$APP_ID]['ppcsp'] = $data;
}

if ($_SESSION[$GLOBALS['APP_ID']]['trx.ppc-sp-rc1']['error']) {
    echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['trx.ppc-sp-rc1']['error']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['trx.ppc-sp-rc1']['error']);
}

if ($_SESSION[$GLOBALS['APP_ID']]['trx.ppc-sp-rc1']['info']) {
    echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['trx.ppc-sp-rc1']['info']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['trx.ppc-sp-rc1']['info']);
}

$data['m_inout_date'] = empty($data['m_inout_date']) ? date($APP_DATE_FORMAT) : $data['m_inout_date'];

echo "<div class='data_box'>";
echo "<form id='frmRR'>";
echo "<input type='hidden' id='c_spk_id' name='c_spk_id' value='{$data['c_spk_id']}'>";
echo "<table width='100%'>";
echo "<tr>";
echo "<td width='10%'>NO. SPK</td>";
echo "<td><input readonly='readonly' type='text' name='spk' size='15' value=\"{$data['spk']}\"></td>";
echo "<td width='10%'></td>";
echo "<td width='10%'>Tanggal</td>";
echo "<td width='33%'><input readonly='readonly' type='text' name='spk_date' size='10' value=\"" . (cgx_emptydate($data['spk_date']) ? '' : date($APP_DATE_FORMAT, strtotime($data['spk_date']))) . "\"></td>";
echo "</tr><tr>";
echo "<td>No. WO</td>";
echo "<td><input readonly='readonly' type='text' name='wo' size='15' value=\"{$data['wo']}\"></td>";
echo "<td></td>";
echo "<td>Mesin</td>";
echo "<td><input readonly='readonly' type='text' name='machine_name' size='30' value=\"{$data['machine_name']}\"></td>";
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
    //echo "<input type='button' value='Edit' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}&pkey[m_inout_id]={$data['m_inout_id']}&mode=editH'\">";
    echo "<input type='button' value='Cetak' onclick=\"window.location = 'report.php?path=/reports/SPS/Perintah_Kerja_No&param[REPORT_SPK]={$data['c_spk_id']}&type=pdf&param[REPORT_USER]=".user('user_fullname')."&param[REPORT_ORG_NAME]=".role('organization')."&fname={$data['spk']}'\">";
    echo "</div>";
}

?>
<script type="text/javascript">
<!--

xajax_showLines('<?php echo $data['c_spk_id'] ?>', '<?php echo $_REQUEST['mode']; ?>');

//-->
</script>