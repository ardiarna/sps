<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.org>
 * Nov 23, 2013 11:35:10 PM
 */

// load data to session
if ($_REQUEST['mode'] == 'edit' && $_SESSION[$APP_ID]['machine']['m_machine_id'] == $_REQUEST['pkey']['m_machine_id']) {
    $data = $_SESSION[$APP_ID]['machine'];
} else {
    $data = npl_fetch_table(
            "SELECT m_machine.*, organization FROM m_machine JOIN app_org USING (app_org_id) 
            WHERE m_machine_id = '{$_REQUEST['pkey']['m_machine_id']}'");
    $rsx = mysql_query(
            "SELECT * 
            FROM m_machine_item 
            JOIN m_product USING (m_product_id) 
            LEFT JOIN c_bpartner ON (m_product.c_bpartner_id=c_bpartner.c_bpartner_id) 
            WHERE m_machine_id = '{$_REQUEST['pkey']['m_machine_id']}'",
            $APP_CONNECTION);
    $data['lines'] = array();
    while ($dtx = mysql_fetch_array($rsx, MYSQL_ASSOC)) $data['lines'][] = $dtx;
    mysql_free_result($rsx);
    $_SESSION[$APP_ID]['machine'] = $data;
}

if ($_REQUEST['mode'] != 'edit') {
    $readonly = ' readonly="readonly"';
}


if ($_SESSION[$GLOBALS['APP_ID']]['master.machine']['error']) {
    echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['master.machine']['error']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['master.machine']['error']);
}

if ($_SESSION[$GLOBALS['APP_ID']]['master.machine']['info']) {
    echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['master.machine']['info']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['master.machine']['info']);
}

//$data['order_date'] = empty($data['order_date']) ? date($APP_DATE_FORMAT) : $data['order_date'];
        
echo "<div class='data_box'>";
echo "<form id='frmMachine'>";
echo "<input type='hidden' id='m_machine_id' name='m_machine_id' value='{$data['m_machine_id']}'>";
echo "<table width='100%'>";
echo "<tr>";
echo "<td width='12%'>Organization</td>";
echo "<td width='36%'>". cgx_form_select('app_org_id', "SELECT app_org_id, organization FROM app_org", $data['app_org_id'], FALSE, "id='app_org_id'") ."</td>";
echo "<td width='4%'></td>";
echo "<td width='22%'>Hasil Produksi Perhari (Pcs)</td>";
echo "<td width='26%'><input{$readonly} name='resultperday' type='text' size='10' value=\"{$data['resultperday']}\"></td>";
echo "</tr>";
echo "<tr>";
echo "<td>Kode Mesin {$mandatory}</td>";
echo "<td><input{$readonly} name='machine_code' type='text' size='20' value=\"{$data['machine_code']}\"></td>";
echo "<td></td>";
echo "<td>Aktif</td>";
echo "<td>". cgx_form_select('active', array('Y' => 'Ya', 'N' => 'Tidak'), $data['active'], FALSE, "id='active'");
echo "</tr>";
echo "<tr>";
echo "<td>Nama Mesin</td>";
echo "<td><input{$readonly} name='machine_name' type='text' size='30' value=\"{$data['machine_name']}\"></td>";
echo "</tr>";
echo "</table>";
echo "</form>";
echo "</div>";

echo "<div id='area-lines' style='margin-top: 4px;'></div>";
if ($_REQUEST['mode'] == 'edit') {
    echo "<div id='area-edit' class='data_box' style='margin-top: 4px; display: none;'></div>";
    echo "<div class='area-button' id='master-button'>";
    echo "<input type='button' value='Simpan' onclick=\"xajax_saveSO(xajax.getFormValues('frmMachine'));\">";
    echo "<input type='button' value='Tambah Baris' onclick=\"xajax_editForm();\">";
    echo "<input type='button' value='Batal' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}&pkey[m_machine_id]={$data['m_machine_id']}'\">";
    echo "</div>";
    echo "<div style='text-align: right; margin-top: 10px;'>{$mandatory} = Wajib diisi</div>";
} else {
    echo "<div class='area-button'>";
    echo "<input type='button' value='Kembali' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}'\">";
    echo "<input" . ($data['status'] == 'C' ? ' disabled' : '')  . " type='button' value='Edit' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}&pkey[m_machine_id]={$data['m_machine_id']}&mode=edit'\">";
    echo "</div>";
}

?>
<script type="text/javascript">
<!--

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

xajax_showLines('<?php echo $data['m_machine_id'] ?>', '<?php echo $_REQUEST['mode']; ?>');

//-->
</script>