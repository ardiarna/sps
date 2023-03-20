<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.org>
 * Feb 20, 2014 2:52:29 PM
 */

echo "<div class='title'>Alokasi Order</div>";

$data = npl_fetch_table(
        "SELECT *
        FROM c_po
        JOIN c_bpartner USING (c_bpartner_id)
        WHERE c_po_id = '{$_REQUEST['c_po_id']}'");

$data['po_date'] = empty($data['po_date']) ? date($APP_DATE_FORMAT) : $data['po_date'];
        
echo "<div class='data_box'>";
echo "<form id='frmPO'>";
echo "<input type='hidden' id='c_po_id' name='c_po_id' value='{$data['c_po_id']}'>";
echo "<input type='hidden' id='c_bpartner_id' name='c_bpartner_id' value='{$data['c_bpartner_id']}'>";
echo "<table width='100%'>";
echo "<tr>";
echo "<td width='15%'>Nomor Dokumen PO</td>";
echo "<td width='33%'><input " . ($data['document_no'] ? 'readonly="readonly"' : 'disabled') . " name='document_no' type='text' size='10' value=\"" . ($data['document_no'] ? $data['document_no'] : $autonumber) . "\"></td>";
echo "<td width='4%'></td>";
echo "<td width='12%'></td>";
echo "<td width='36%'></td>";
echo "</tr>";
echo "<tr>";
echo "<td>Customer</td>";
echo "<td><input readonly='readonly' type='text' id='partner_name' size='30' value=\"{$data['partner_name']}\">{$select_partner}</td>";
echo "<td></td>";
echo "<td></td>";
echo "<td></td>";
echo "</tr>";
echo "<tr>";
echo "<td>Tanggal PO</td>";
echo "<td><input readonly='readonly' name='po_date' id='po_date' type='text' size='10' value=\"" . (cgx_emptydate($data['po_date']) ? '' : date($APP_DATE_FORMAT, strtotime($data['po_date']))) . "\"></td>";
echo "<td></td>";
echo "<td></td>";
echo "<td></td>";
echo "</tr>";
echo "</table>";
echo "</form>";
echo "</div>";

echo "<form id='frmAlloc' name='frmAlloc'>";
echo "<input type='hidden' name='c_po_id' value='{$_REQUEST['c_po_id']}'>";
echo "<div class='datagrid_background' style='margin-top: 4px;'>\n";
echo "<table width='100%' cellspacing='1' cellpadding='2'>";
echo "<tr>";
echo "<th class='datagrid_header'>Line</th>";
echo "<th class='datagrid_header'>Item Number</th>";
echo "<th class='datagrid_header' width='1'>Organization</th>";
echo "<th class='datagrid_header' width='80'>Total Order</th>";
echo "<th class='datagrid_header'>Allocation</th>";
echo "</tr>";

$rsx = mysql_query("SELECT * "
        . "FROM c_po_line "
        . "JOIN m_product USING (m_product_id) "
        . "WHERE c_po_id = '{$_REQUEST['c_po_id']}'", $APP_CONNECTION);
$line = 0;
while ($dtx = mysql_fetch_array($rsx)) {
    $line++;
    echo "<tr style='background-color: #fff;'>";
    echo "<td align='center'>{$line}<input type='hidden' name='line[{$dtx['c_po_line_id']}]' value=\"{$line}\"></td>";
    echo "<td align='left'>{$dtx['product_code']}<br>{$dtx['od']} x {$dtx['thickness']} x {$dtx['length']}</td>";
    echo "<td><select name='org[{$dtx['c_po_line_id']}]'>";
    echo "<option value=''></option>";
    $rsy = mysql_query("SELECT * FROM app_org WHERE active = 'Y' AND is_trx = 'Y'", $APP_CONNECTION);
    while ($dty = mysql_fetch_array($rsy)) {
        if ($dty['app_org_id'] == $dtx['app_org_id']) {
            echo "<option selected value='{$dty['app_org_id']}'>{$dty['organization']}</option>";
        } else {
            echo "<option value='{$dty['app_org_id']}'>{$dty['organization']}</option>";
        }
    }
    mysql_free_result($rsy);
    echo "</select></td>";
    echo "<td align='right'>" . number_format($dtx['quantity']) . "</td>";
    echo "<td id='alloc-{$dtx['c_po_line_id']}' width='1'></td>";
    echo "</tr>";
}
mysql_free_result($rsx);

echo "</table>";
echo "</div>\n";
echo "</form>";

echo "<div class='area-button'>";
echo "<input type='button' value='Simpan' onclick=\"xajax_allocate(xajax.getFormValues('frmAlloc'));\">";
echo "<input type='button' value='Kembali' onclick=\"window.location = 'module.php?m=trx.po&pkey[c_po_id]={$_REQUEST['c_po_id']}'\">";
echo "</div>";


echo "\n<script type='text/javascript'>\n";
echo "<!--\n";
$rsx = mysql_query("SELECT * "
        . "FROM c_po_line "
        . "WHERE c_po_id = '{$_REQUEST['c_po_id']}'", $APP_CONNECTION);
while ($dtx = mysql_fetch_array($rsx)) {
    echo "xajax_allocShow('{$dtx['c_po_line_id']}');\n";
}
mysql_free_result($rsx);
echo "//-->\n";
echo "</script>\n";

?>