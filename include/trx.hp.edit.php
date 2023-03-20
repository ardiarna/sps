<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.org>
 * Nov 23, 2013 11:35:10 PM
 */

$data = npl_fetch_table(
        "SELECT m_inout.*, c_spk.document_no spk, machine_name, spk_date
        FROM m_inout
        JOIN c_spk USING (c_spk_id)
        JOIN m_machine USING (m_machine_id)
        WHERE m_inout_id = '{$_REQUEST['pkey']['m_inout_id']}'");

if ($_REQUEST['mode'] != 'edit') {
    $readonly = ' readonly="readonly"';
} else {
    $select_spk = "<img onclick=\"popupReference('c-spk');\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'>";
}

if ($_SESSION[$GLOBALS['APP_ID']]['trx.hp']['error']) {
    echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['trx.hp']['error']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['trx.hp']['error']);
}

if ($_SESSION[$GLOBALS['APP_ID']]['trx.hp']['info']) {
    echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['trx.hp']['info']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['trx.hp']['info']);
}

$data['m_inout_date'] = empty($data['m_inout_date']) ? date($APP_DATE_FORMAT) : $data['m_inout_date'];
    
echo "<form id='frmBK'>";
echo "<div class='data_box'>";
echo "<input type='hidden' id='c_spk_id' name='c_spk_id' value='{$data['c_spk_id']}'>";
echo "<table width='100%'>";
echo "<tr>";
echo "<td width='13%'>Nomor Dokumen</td>";
echo "<td width='32%'><input " . ($data['document_no'] ? 'readonly="readonly"' : 'disabled') . " name='document_no' type='text' size='15' value=\"" . ($data['document_no'] ? $data['document_no'] : $autonumber) . "\"></td>";
echo "<td width='10%'></td>";
echo "<td width='13%'>Tanggal Produksi {$mandatory}</td>";
echo "<td width='32%'><input{$readonly} name='m_inout_date' id='m_inout_date' type='text' size='10' value=\"" . (cgx_emptydate($data['m_inout_date']) ? '' : date($APP_DATE_FORMAT, strtotime($data['m_inout_date']))) . "\"></td>";
echo "</tr>";
echo "<tr>";
echo "<td>Surat Perintah Kerja {$mandatory}</td>";
echo "<td><input readonly='readonly' type='text' id='surat_pk' size='15' value=\"{$data['spk']}\">{$select_spk}</td>";
echo "<td></td>";
echo "<td>Tanggal SPK</td>";
echo "<td><input readonly='readonly' id='spk_date' type='text' size='10' value=\"" . (cgx_emptydate($data['spk_date']) ? '' : date($APP_DATE_FORMAT, strtotime($data['spk_date']))) . "\"></td>";
echo "</tr>";
echo "<tr>";
echo "<td>Mesin</td>";
echo "<td><input readonly='readonly' type='text' id='machine_name' size='30' value=\"{$data['machine_name']}\"></td>";
echo "</tr>";
echo "</table>";
echo "</div>";


if ($_REQUEST['mode'] == 'edit') {
    echo "<div id='area-lines' style='margin-top: 4px;'></div>";
} else {
    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';
    
    $datagrid = new Structures_DataGrid(9999);
    $cgx_options = array('dsn' => $cgx_dsn);
    $cgx_sql = "SELECT @curRow := @curRow + 1 AS line, m_inout_line_id, m_product.*, warehouse_name, m_inout_line.quantity, machine_name
        FROM m_inout_line
        JOIN m_product USING (m_product_id)
        JOIN m_warehouse USING (m_warehouse_id)
        JOIN c_spk_line USING (c_spk_line_id)
        JOIN c_wo_line USING (c_wo_line_id)
        LEFT JOIN m_machine USING (m_machine_id)
        JOIN (SELECT @curRow := 0) r
        WHERE m_inout_id = '{$_REQUEST['pkey']['m_inout_id']}'";
    $datagrid->bind($cgx_sql, $cgx_options);
    
    $cgx_table = new HTML_Table($cgx_TableAttribs);
    $cgx_tableHeader = & $cgx_table->getHeader();
    $cgx_tableBody = & $cgx_table->getBody();
    
    $datagrid->addColumn(new Structures_DataGrid_Column('Baris', 'line', NULL, array('align' => 'center'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Item Number', 'product_code', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Spec', 'spec', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('OD', 'od', NULL, array('align' => 'right', 'width' => '8%'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Tebal', 'thickness', NULL, array('align' => 'right', 'width' => '8%'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Panjang', 'length', NULL, array('align' => 'right', 'width' => '8%'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Mesin', 'machine_name', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Gudang', 'warehouse_name', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Jumlah Produki', 'quantity', NULL, array('align' => 'right'), NULL, "cgx_format_3digit"));

    $datagrid->fill($cgx_table, $cgx_RendererOptions);
    $cgx_tableHeader->setRowAttributes(0, $cgx_HeaderAttribs);
    $cgx_tableBody->altRowAttributes(0, $cgx_EvenRowAttribs, $cgx_OddRowAttribs, TRUE);

    echo "<div class='datagrid_background' style='margin-top: 4px;'>\n";
    echo $cgx_table->toHtml();
    echo "</div>\n";
    
}

echo "</form>";
if ($_REQUEST['mode'] == 'edit') {
    echo "<div id='area-edit' class='data_box' style='margin-top: 4px; display: none;'></div>";
    echo "<div class='area-button' id='master-button'>";
    echo "<input type='button' value='Simpan Dokumen' onclick=\"xajax_saveBK(xajax.getFormValues('frmBK'));\">";
    echo "<input type='button' value='Batal' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}&pkey[m_inout_id]={$data['m_inout_id']}'\">";
    echo "</div>";
    echo "<div style='text-align: right; margin-top: 10px;'>{$mandatory} = Wajib diisi</div>";
} else {
    echo "<div class='area-button'>";
    echo "<input type='button' value='Kembali' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}'\">";
    //echo "<input type='button' value='Cetak' onclick=\"window.location = 'report.php?path=/reports/SPS/Bukti_Barang_Keluar&param[REPORT_ID_PENGIRIMAN]={$data['m_inout_id']}&type=pdf&fname={$data['document_no']}'\">";
    echo "</div>";
}

?>
<script type="text/javascript">
<!--
function setSPK(c_spk_id, document_no, c_spk_line_id, spk_date, machine_name) {
    var txt_document_no = document.getElementById('surat_pk');
    var txt_spk_date = document.getElementById('spk_date');
    var txt_machine_name = document.getElementById('machine_name');
    var hid_c_spk_id = document.getElementById('c_spk_id');
    txt_document_no.value = document_no;
    txt_spk_date.value = ymd2dmy(spk_date);
    txt_machine_name.value = machine_name;
    hid_c_spk_id.value = c_spk_id;
    xajax_spkLinesForm(c_spk_id);
}
<?php if ($_REQUEST['mode'] == 'edit') { ?>
$(function() {
    $("#m_inout_date").datepicker({dateFormat: '<?php echo $APP_DATE_FORMAT_JAVA; ?>'});
});
<?php } ?>
//-->
</script>