<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.org>
 * Nov 23, 2013 11:35:10 PM
 */

$data = npl_fetch_table(
        "SELECT m_inout.*
        FROM m_inout
        WHERE m_inout_id = '{$_REQUEST['pkey']['m_inout_id']}'");
$data_ref = npl_fetch_table(
        "SELECT m_inout.*
        FROM m_inout
        WHERE m_inout_id = '{$data['m_inout_id_ref']}'");

if ($_REQUEST['mode'] != 'edit') {
    $readonly = ' readonly="readonly"';
} else {
    $select_bm = "<img onclick=\"popupReference('penerimaan-barang');\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'>";
}

if ($_SESSION[$GLOBALS['APP_ID']]['trx.bk']['error']) {
    echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['trx.bk']['error']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['trx.bk']['error']);
}

if ($_SESSION[$GLOBALS['APP_ID']]['trx.bk']['info']) {
    echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['trx.bk']['info']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['trx.bk']['info']);
}

$data['m_inout_date'] = empty($data['m_inout_date']) ? date($APP_DATE_FORMAT) : $data['m_inout_date'];

echo "<form id='frmRM'>";
echo "<div class='data_box'>";
echo "<input type='hidden' id='bm_m_inout_id' name='bm_m_inout_id' value=''>";
echo "<table width='100%'>";
echo "<tr>";
echo "<td width='13%'>Nomor Dokumen</td>";
echo "<td width='32%'><input " . ($data['document_no'] ? 'readonly="readonly"' : 'disabled') . " name='document_no' type='text' size='15' value=\"" . ($data['document_no'] ? $data['document_no'] : $autonumber) . "\"></td>";
echo "<td width='10%'></td>";
echo "<td width='13%'>Tanggal Koreksi {$mandatory}</td>";
echo "<td width='32%'><input{$readonly} name='m_inout_date' id='m_inout_date' type='text' size='10' value=\"" . (cgx_emptydate($data['m_inout_date']) ? '' : date($APP_DATE_FORMAT, strtotime($data['m_inout_date']))) . "\"></td>";
echo "</tr>";
echo "<tr>";
echo "<td>Penerimaan Barang {$mandatory}</td>";
echo "<td><input readonly='readonly' type='text' id='bm_document_no' size='15' value=\"{$data_ref['document_no']}\">{$select_bm}</td>";
echo "<td></td>";
echo "<td>Tanggal Penerimaan</td>";
echo "<td><input readonly='readonly' name='bm_m_inout_date' id='bm_m_inout_date' type='text' size='10' value=\"" . (cgx_emptydate($data_ref['m_inout_date']) ? '' : date($APP_DATE_FORMAT, strtotime($data_ref['m_inout_date']))) . "\"></td>";
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
    $cgx_sql = "SELECT @curRow := @curRow + 1 AS line, m_inout_line.m_inout_line_id, m_product.*, " .
        "warehouse_name, ref.quantity, m_inout_line.quantity qty_koreksi, ref.quantity + m_inout_line.quantity balance " .
        "FROM m_inout_line JOIN m_inout_line ref ON (m_inout_line.return_ref = ref.m_inout_line_id) " .
        "JOIN m_product ON (m_inout_line.m_product_id = m_product.m_product_id) " .
        "JOIN m_warehouse ON (m_inout_line.m_warehouse_id = m_warehouse.m_warehouse_id) "  .
        "JOIN (SELECT @curRow := 0) r " .
        "WHERE m_inout_line.m_inout_id = '{$_REQUEST['pkey']['m_inout_id']}'";
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
    $datagrid->addColumn(new Structures_DataGrid_Column('Gudang', 'warehouse_name', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Jumah Barang<br>Diterima', 'quantity', NULL, array('align' => 'right', 'width' => '10%'), NULL, "cgx_format_3digit"));
    $datagrid->addColumn(new Structures_DataGrid_Column('Koreksi', 'qty_koreksi', NULL, array('align' => 'right', 'width' => '10%'), NULL, "cgx_format_3digit"));
    $datagrid->addColumn(new Structures_DataGrid_Column('Jumah Barang<br>Setelah Koreksi', 'balance', NULL, array('align' => 'right', 'width' => '10%'), NULL, "cgx_format_3digit"));

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
    echo "<input type='button' value='Simpan Dokumen' onclick=\"xajax_saveRM(xajax.getFormValues('frmRM'));\">";
    echo "<input type='button' value='Batal' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}'\">";
    echo "</div>";
    echo "<div style='text-align: right; margin-top: 10px;'>{$mandatory} = Wajib diisi</div>";
} else {
    echo "<div class='area-button'>";
    echo "<input type='button' value='Kembali' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}'\">";
//    echo "<input type='button' value='Cetak' onclick=\"window.location = 'action/trx.rr.php?pkey[m_inout_id]={$data['m_inout_id']}&mode=print'\">";
    echo "</div>";
}

?>
<script type="text/javascript">
<!--
function setPenerimaanBarang(m_inout_id, document_no, m_inout_date) {
    var txt_bm_document_no = document.getElementById('bm_document_no');
    var txt_bm_m_inout_date = document.getElementById('bm_m_inout_date');
    var hid_bm_m_inout_id = document.getElementById('bm_m_inout_id');
    txt_bm_document_no.value = document_no;
    txt_bm_m_inout_date.value = ymd2dmy(m_inout_date);
    hid_bm_m_inout_id.value = m_inout_id;
    xajax_showLines(m_inout_id);
}
<?php if ($_REQUEST['mode'] == 'edit') { ?>
$(function() {
    $("#m_inout_date").datepicker({dateFormat: '<?php echo $APP_DATE_FORMAT_JAVA; ?>'});
});
<?php } ?>
//-->
</script>