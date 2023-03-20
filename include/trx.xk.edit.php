<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.org>
 * Nov 23, 2013 11:35:10 PM
 */

// load data to session
if ($_REQUEST['mode'] == 'edit' && $_SESSION[$APP_ID]['xk']['m_box_inout_id'] == $_REQUEST['pkey']['m_box_inout_id']) {
    $data = $_SESSION[$APP_ID]['xk'];
} else {
    $data = npl_fetch_table(
            "SELECT *
            FROM m_box_inout, c_bpartner
            WHERE m_box_inout.c_bpartner_id= c_bpartner.c_bpartner_id AND m_box_inout_id = '{$_REQUEST['pkey']['m_box_inout_id']}'");
    $rsx = mysql_query(
            "SELECT * 
            FROM m_box_inout_line
            JOIN m_box USING (m_box_id)
            WHERE m_box_inout_id = '{$_REQUEST['pkey']['m_box_inout_id']}'",
            $APP_CONNECTION);
    $data['lines'] = array();
    while ($dtx = mysql_fetch_array($rsx, MYSQL_ASSOC)) {
        $data['lines'][$dtx['m_box_id']] = $dtx;
    }
    mysql_free_result($rsx);
    $_SESSION[$APP_ID]['xk'] = $data;
}

if ($_REQUEST['mode'] != 'edit') {
    $readonly = ' readonly="readonly"';
}


if ($_SESSION[$GLOBALS['APP_ID']]['trx.xk']['error']) {
    echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['trx.xk']['error']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['trx.xk']['error']);
}

if ($_SESSION[$GLOBALS['APP_ID']]['trx.xk']['info']) {
    echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['trx.xk']['info']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['trx.xk']['info']);
}

$data['m_box_inout_date'] = empty($data['m_box_inout_date']) ? date($APP_DATE_FORMAT) : $data['m_box_inout_date'];

echo "<div class='data_box'>";
echo "<form id='frmXK'>";
echo "<input type='hidden' id='m_box_inout_id' name='m_box_inout_id' value='{$data['m_box_inout_id']}'>";
echo "<input type='hidden' id='c_bpartner_id' name='c_bpartner_id' value='{$data['c_bpartner_id']}'>";
echo "<table width='100%'>";
echo "<tr>";
echo "<td width='12%'>Nomor Dokumen</td>";
echo "<td width='36%'><input " . ($data['document_no'] ? 'readonly="readonly"' : 'disabled') . " name='document_no' type='text' size='15' value=\"" . ($data['document_no'] ? $data['document_no'] : $autonumber) . "\"></td>";
echo "<td width='4%'></td>";
echo "<td width='12%'>Tanggal {$mandatory}</td>";
echo "<td width='36%'><input{$readonly} name='m_box_inout_date' id='m_box_inout_date' type='text' size='10' value=\"" . (cgx_emptydate($data['m_box_inout_date']) ? '' : date($APP_DATE_FORMAT, strtotime($data['m_box_inout_date']))) . "\"></td>";
echo "</tr>";
echo "<tr>";
echo "<td>No Truck</td>";
echo "<td><input type='text' id='no_truck' name='no_truck' size='30' value=\"{$data['no_truck']}\"></td>";
echo "<td></td>";
echo "<td>Nama Supir</td>";
echo "<td><input type='text' id='nama_supir' name='nama_supir' size='30' value=\"{$data['nama_supir']}\"></td>";
echo "<td></td>";
echo "</tr>";
echo "<td>Customer</td>";
echo "<td><input readonly='readonly' type='text' id='partner_name' size='30' value=\"{$data['partner_name']}\">{$select_partner}</td>";
echo "<td></td>";
echo "</tr>";
echo "</table>";
echo "</form>";
echo "</div>";

echo "<div id='area-lines' style='margin-top: 4px;'></div>";
if ($_REQUEST['mode'] == 'edit') {
    echo "<div id='area-edit' class='data_box' style='margin-top: 4px; display: none;'></div>";
    echo "<div class='area-button' id='master-button'>";
    echo "<input type='button' value='Simpan Dokumen' onclick=\"xajax_saveXK(xajax.getFormValues('frmXK'));\">";
    echo "<input type='button' value='Tambah Box' onclick=\"popupReference('box-inside');\">";
    echo "<input type='button' value='Batal' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}&pkey[m_box_inout_id]={$data['m_box_inout_id']}'\">";
    echo "</div>";
    echo "<div style='text-align: right; margin-top: 10px;'>{$mandatory} = Wajib diisi</div>";
} else {
    echo "<div class='area-button'>";
    echo "<input type='button' value='Kembali' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}'\">";
    echo "<input type='button' value='Cetak' onclick=\"window.location = 'report.php?path=/reports/SPS/Surat_Jalan_Box&param[ID_BOX_KELUAR]={$data['m_box_inout_id']}&type=docx&fname={$data['m_box_inout_id']}'\">";    
    echo "</div>";
}

?>
<script type="text/javascript">
<!--

function setBoxOutside(id, box_no, box_code) {
    xajax_addBox(id, box_no, box_code);
}

<?php if ($_REQUEST['mode'] == 'edit') { ?>
$(function() {
    $("#m_box_inout_date").datepicker({dateFormat: '<?php echo $APP_DATE_FORMAT_JAVA; ?>'});
});
<?php } ?>

xajax_showLines('<?php echo $data['m_box_inout_id'] ?>', '<?php echo $_REQUEST['mode']; ?>');

//-->
</script>