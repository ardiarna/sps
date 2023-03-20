<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 05/12/2013 14:35:39
 */


echo "<div class='title'>Work Order vs Realisasi</div>";

function grid_so($data) {
    $href = "module.php?m=trx.so&back_to=view.wo_realisasi&mode=view&pkey[c_order_id]={$data['record']['c_order_id']}";
    $out = "<a href='{$href}'>{$data['record']['remark']}</a>";
    return $out;
}

require_once 'Structures/DataGrid.php';
require_once 'HTML/Table.php';

if (is_array($_SESSION[$GLOBALS['APP_ID']]['view.wo_realisasi']['columns'])) {
    $cgx_def_columns = $_SESSION[$GLOBALS['APP_ID']]['view.wo_realisasi']['columns'];
} else {
    $cgx_def_columns = array(
        'document_no' => 1,
        'order_date' => 1,
        'remark' => 1,
        'reference_no' => 1,
        'partner_name' => 1,
        'machine_name' => 1,
        'product_name' => 1,
        'order_quantity' => 1,
        'qty_production' => 1,
        // 'producted_quantity' => 1,
        // 'ch_quantity' => 1,
        // 'sk_quantity' => 1,
        // 'pl_quantity' => 1,
        // 'bd_quantity' => 1,
        // 'qc_quantity' => 1,
    );
    $_SESSION[$GLOBALS['APP_ID']]['view.wo_realisasi']['columns'] = $cgx_def_columns;
}

//$mesin = $_REQUEST['mesin'];
$cgx_filter1 = urldecode($_REQUEST['cgx_filter1']);
$date_f = $_REQUEST['date_f'];
$date_t = $_REQUEST['date_t'];
$item_number = $_REQUEST['item_number'];
$document_no = $_REQUEST['document_no'];
$sc_number = $_REQUEST['sc_number'];
$customer = $_REQUEST['customer'];
//$cgx_search = $_REQUEST['q'];
$tgl_f = $_REQUEST['tgl_f'];
$tgl_t = $_REQUEST['tgl_t'];

$tgl_param = "";
if ($tgl_f) $tgl_param .= " AND production_date >= '" . npl_dmy2ymd($tgl_f) . "'";
if ($tgl_t) $tgl_param .= " AND production_date <= '" . npl_dmy2ymd($tgl_t) . "'";

$cgx_sql = "SELECT m_work_order.document_no, m_work_order.order_date,
c_order.document_no so_number, c_order.order_date s_order_date, COALESCE(remark,c_forecast.document_no) remark, reference_no, machine_name, proces_name, 
COALESCE(c_bpartner.partner_code,cb2.partner_code) partner_code, COALESCE(c_bpartner.partner_name,cb2.partner_name) partner_name, rec.product_code, rec.product_name, rec.spec, rec.od, rec.thickness, rec.length,
mat.product_code product_codem, mat.product_name product_namem, mat.od odm, mat.thickness thicknessm, mat.length lengthm,
m_work_order_line.*, COALESCE(qty_production,0) qty_production, (m_work_order_line.order_quantity - COALESCE(qty_production,0)) as outstanding_wo, m_work_order.create_date, m_work_order.update_date, auc.user_fullname, auu.user_fullname user_fullname_u
FROM m_work_order
JOIN m_machine USING (m_machine_id)
JOIN c_proces USING (c_proces_id)
JOIN m_work_order_line USING (m_work_order_id)
LEFT JOIN c_order USING (c_order_id)
LEFT JOIN c_bpartner USING (c_bpartner_id)
LEFT JOIN c_forecast ON (m_work_order_line.c_forecast_id=c_forecast.c_forecast_id)
LEFT JOIN c_bpartner cb2 ON (c_forecast.c_bpartner_id=cb2.c_bpartner_id)
LEFT JOIN  ( SELECT m_production_line.m_work_order_line_id, SUM(good) qty_production 
    FROM m_production
    JOIN m_production_line ON(m_production.m_production_id=m_production_line.m_production_id)
    JOIN m_work_order_line ON(m_production_line.m_work_order_line_id=m_work_order_line.m_work_order_line_id)
    WHERE 1 = 1 ". $tgl_param ." GROUP BY m_work_order_line_id ) col 
ON(m_work_order_line.m_work_order_line_id=col.m_work_order_line_id)
JOIN m_product rec ON (m_work_order_line.m_product_id=rec.m_product_id)
JOIN m_product mat ON (m_work_order_line.m_product_material=mat.m_product_id)
LEFT JOIN app_user auc ON (m_work_order.create_user=auc.user_id)
LEFT JOIN app_user auu ON (m_work_order.update_user=auu.user_id) WHERE m_work_order.type_id = 'W' ";
$cgx_sql .= " AND " . org_filter_trx('m_work_order.app_org_id');

$cgx_sqltotal = "SELECT sum(order_quantity) as order_quantity, sum(producted_quantity) as producted_quantity, sum(ch_quantity) as ch_quantity, 
sum(sk_quantity) as sk_quantity, sum(pl_quantity) as pl_quantity, sum(bd_quantity) as bd_quantity, sum(qc_quantity) as qc_quantity, sum(pc_quantity) as pc_quantity, 
sum(qty_production) as qty_production, SUM(m_work_order_line.order_quantity - COALESCE(qty_production,0)) as outstanding_wo
FROM m_work_order
JOIN m_machine USING (m_machine_id)
JOIN c_proces USING (c_proces_id)
JOIN m_work_order_line USING (m_work_order_id)
LEFT JOIN c_order USING (c_order_id)
LEFT JOIN c_bpartner USING (c_bpartner_id)
LEFT JOIN c_forecast ON (m_work_order_line.c_forecast_id=c_forecast.c_forecast_id)
LEFT JOIN c_bpartner cb2 ON (c_forecast.c_bpartner_id=cb2.c_bpartner_id)
LEFT JOIN  ( SELECT m_production_line.m_work_order_line_id, SUM(good) qty_production 
    FROM m_production
    JOIN m_production_line ON(m_production.m_production_id=m_production_line.m_production_id)
    JOIN m_work_order_line ON(m_production_line.m_work_order_line_id=m_work_order_line.m_work_order_line_id)
    WHERE 1 = 1 ". $tgl_param ."GROUP BY m_work_order_line_id ) col 
ON(m_work_order_line.m_work_order_line_id=col.m_work_order_line_id)
JOIN m_product rec ON (m_work_order_line.m_product_id=rec.m_product_id)
JOIN m_product mat ON (m_work_order_line.m_product_material=mat.m_product_id)
LEFT JOIN app_user auc ON (m_work_order.create_user=auc.user_id)
LEFT JOIN app_user auu ON (m_work_order.update_user=auu.user_id) WHERE m_work_order.type_id = 'W' ";
$cgx_sqltotal .= " AND " . org_filter_trx('m_work_order.app_org_id');

$cgx_datagrid = new Structures_DataGrid($cgx_max_rows);
$cgx_options = array('dsn' => $cgx_dsn);
$cgx_datagrid->setDefaultSort(array('order_date' => 'DESC'));


echo "<form name='frmFILTER' action='{$_SERVER['SCRIPT_NAME']}'>\n";
echo "<input type='hidden' name='m' value='{$_REQUEST['m']}'>\n";
echo "<table id='bar' class='datagrid_bar' width='100%'><tr>\n";
echo "<td align='right'>No. WO</td>\n";
echo "<td align='left'><input type='text'style='width: 150px;' name='document_no' value=\"{$document_no}\"></td>\n";
echo "<td align='right'>Item Number</td>\n";
echo "<td align='left'><input type='text'style='width: 150px;' name='item_number' value=\"{$item_number}\"></td>\n";
echo "<td align='right'>Tgl. WO</td>\n";
echo "<td align='left' width='1'><input type='text'style='width: 120px; text-align: center;' id='date_f' name='date_f' value=\"{$date_f}\"></td>\n";
echo "<td align='center'>s/d</td>\n";
echo "<td align='left' width='1'><input type='text'style='width: 120px; text-align: center;' id='date_t' name='date_t' value=\"{$date_t}\"></td>\n";
echo "<td></td>\n";
echo "<td width='1' class='datagrid_bar_icon'><a title='Export data ke CSV' href='javascript:exportCSV();'><img border='0' src='images/icon_csv.png'></a></td>\n";
echo "<td width='1' class='datagrid_bar_icon'><a title='Kustomisasi kolom' href='javascript:customizeColumn(true);'><img border='0' src='images/icon_columns.png'></a></td>\n";
echo "</tr><tr>";
echo "<td align='right'>Remark</td>\n";
echo "<td align='left'><input type='text'style='width: 150px;' name='sc_number' value=\"{$sc_number}\"></td>\n";
echo "<td align='right'>Customer</td>\n";
echo "<td align='left'><input type='text'style='width: 150px;' name='customer' value=\"{$customer}\"></td>\n";
// echo "<td align='right'>Mesin</td>\n";
// echo "<td colspan='2'>" . cgx_filter('mesin', "SELECT m_machine_id, machine_name FROM m_machine WHERE " . org_filter_master() . " ORDER BY machine_name", $mesin, TRUE) . "</td>\n";
echo "<td align='right'>Tgl. Realisasi</td>\n";
echo "<td align='left' width='1'><input type='text'style='width: 120px; text-align: center;' id='tgl_f' name='tgl_f' value=\"{$tgl_f}\"></td>\n";
echo "<td align='center'>s/d</td>\n";
echo "<td align='left' width='1'><input type='text'style='width: 120px; text-align: center;' id='tgl_t' name='tgl_t' value=\"{$tgl_t}\"></td>\n";
echo "<td></td>\n";
echo "<td colspan='2'>" . cgx_filter('cgx_filter1', array('O' => 'Outstanding'), $cgx_filter1, TRUE) . "</td>\n";
echo "<td width='1'><input title='Cari' type='image' src='images/icon_search.png' border='0' style='padding-right: 20px;'></td>\n";
//echo "<td align='right'><input type='text' size='27' name='q' value=\"{$cgx_search}\"></td>\n";
echo "</tr></table>\n";
echo "</form>\n";

echo "<form name='frmCUSTOMIZE' action='action/cgx_customize.php' method='post'>\n";
echo "<input type='hidden' name='back' value='" . urlencode($_SERVER['REQUEST_URI']) . "'>\n";
echo "<input type='hidden' name='dg_name' value='view.wo_realisasi'>\n";
echo "<input type='hidden' name='col[remark]' value='on'>\n";
echo "<input type='hidden' name='col[schedule_delivery_date]' value='on'>\n";
echo "<input type='hidden' name='col[document_no]' value='on'>\n";
echo "<input type='hidden' name='col[order_date]' value='on'>\n";
echo "<table id='columns' class='datagrid_bar' style='display: none;'><tr>\n";
echo "<td width='99%' valign='top'>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_document_no' name='col[document_no]' type='checkbox'></td><td width='99%'><label for='col_document_no'>No. WO</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_order_date' name='col[order_date]' type='checkbox'></td><td width='99%'><label for='col_order_date'>Tgl WO</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_remark' name='col[remark]' type='checkbox'></td><td width='99%'><label for='col_remark'>Remark/Forecast</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['so_number'] == 1 ? ' checked' : '') . " id='col_so_number' name='col[so_number]' type='checkbox'></td><td width='99%'><label for='col_so_number'>SO Number</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['reference_no'] == 1 ? ' checked' : '') . " id='col_reference_no' name='col[reference_no]' type='checkbox'></td><td width='99%'><label for='col_reference_no'>No. PO</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['s_order_date'] == 1 ? ' checked' : '') . " id='col_s_order_date' name='col[s_order_date]' type='checkbox'></td><td width='99%'><label for='col_s_order_date'>Tgl SO</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['partner_code'] == 1 ? ' checked' : '') . " id='col_partner_code' name='col[partner_code]' type='checkbox'></td><td width='99%'><label for='col_partner_code'>Kode Customer</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['partner_name'] == 1 ? ' checked' : '') . " id='col_partner_name' name='col[partner_name]' type='checkbox'></td><td width='99%'><label for='col_partner_name'>Nama Customer</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['machine_name'] == 1 ? ' checked' : '') . " id='col_machine_name' name='col[machine_name]' type='checkbox'></td><td width='99%'><label for='col_machine_name'>Mesin</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['m_product_id'] == 1 ? ' checked' : '') . " id='col_m_product_id' name='col[m_product_id]' type='checkbox'></td><td width='99%'><label for='col_m_product_id'>Product ID</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['product_code'] == 1 ? ' checked' : '') . " id='col_product_code' name='col[product_code]' type='checkbox'></td><td width='99%'><label for='col_product_code'>Item Number</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['product_name'] == 1 ? ' checked' : '') . " id='col_product_name' name='col[product_name]' type='checkbox'></td><td width='99%'><label for='col_product_name'>Nama Produk</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['spec'] == 1 ? ' checked' : '') . " id='col_spec' name='col[spec]' type='checkbox'></td><td width='99%'><label for='col_spec'>Spec</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['od'] == 1 ? ' checked' : '') . " id='col_od' name='col[od]' type='checkbox'></td><td width='99%'><label for='col_od'>OD</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['thickness'] == 1 ? ' checked' : '') . " id='col_thickness' name='col[thickness]' type='checkbox'></td><td width='99%'><label for='col_thickness'>Thickness</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['length'] == 1 ? ' checked' : '') . " id='col_length' name='col[length]' type='checkbox'></td><td width='99%'><label for='col_length'>Length</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['m_product_material'] == 1 ? ' checked' : '') . " id='col_m_product_material' name='col[m_product_material]' type='checkbox'></td><td width='99%'><label for='col_m_product_material'>Product ID LP</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['product_codem'] == 1 ? ' checked' : '') . " id='col_product_codem' name='col[product_codem]' type='checkbox'></td><td width='99%'><label for='col_product_codem'>Item Number LP</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['product_namem'] == 1 ? ' checked' : '') . " id='col_product_namem' name='col[product_namem]' type='checkbox'></td><td width='99%'><label for='col_product_namem'>Nama Produk LP</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['lengthm'] == 1 ? ' checked' : '') . " id='col_lengthm' name='col[lengthm]' type='checkbox'></td><td width='99%'><label for='col_lengthm'>Length LP</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['tolerance_size'] == 1 ? ' checked' : '') . " id='col_tolerance_size' name='col[tolerance_size]' type='checkbox'></td><td width='99%'><label for='col_tolerance_size'>Tolerance Size</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['proces_name'] == 1 ? ' checked' : '') . " id='col_proces_name' name='col[proces_name]' type='checkbox'></td><td width='99%'><label for='col_proces_name'>Next Proces</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['material_quantity'] == 1 ? ' checked' : '') . " id='col_material_quantity' name='col[material_quantity]' type='checkbox'></td><td width='99%'><label for='col_material_quantity'>Long Pipe Qty</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['order_quantity'] == 1 ? ' checked' : '') . " id='col_order_quantity' name='col[order_quantity]' type='checkbox'></td><td width='99%'><label for='col_order_quantity'>Qty WO</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['qty_production'] == 1 ? ' checked' : '') . " id='col_qty_production' name='col[qty_production]' type='checkbox'></td><td width='99%'><label for='col_qty_production'>Qty Realisasi</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['outstanding_wo'] == 1 ? ' checked' : '') . " id='col_outstanding_wo' name='col[outstanding_wo]' type='checkbox'></td><td width='99%'><label for='col_outstanding_wo'>Qty Outstanding</label></td></tr></table>\n";
// echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['producted_quantity'] == 1 ? ' checked' : '') . " id='col_producted_quantity' name='col[producted_quantity]' type='checkbox'></td><td width='99%'><label for='col_producted_quantity'>Qty CUT</label></td></tr></table>\n";
// echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['ch_quantity'] == 1 ? ' checked' : '') . " id='col_ch_quantity' name='col[ch_quantity]' type='checkbox'></td><td width='99%'><label for='col_ch_quantity'>Qty CH</label></td></tr></table>\n";
// echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['sk_quantity'] == 1 ? ' checked' : '') . " id='col_sk_quantity' name='col[sk_quantity]' type='checkbox'></td><td width='99%'><label for='col_sk_quantity'>Qty SK</label></td></tr></table>\n";
// echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['pl_quantity'] == 1 ? ' checked' : '') . " id='col_pl_quantity' name='col[pl_quantity]' type='checkbox'></td><td width='99%'><label for='col_pl_quantity'>Qty PL</label></td></tr></table>\n";
// echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['bd_quantity'] == 1 ? ' checked' : '') . " id='col_bd_quantity' name='col[bd_quantity]' type='checkbox'></td><td width='99%'><label for='col_bd_quantity'>Qty BD</label></td></tr></table>\n";
// echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['qc_quantity'] == 1 ? ' checked' : '') . " id='col_qc_quantity' name='col[qc_quantity]' type='checkbox'></td><td width='99%'><label for='col_qc_quantity'>Qty QC</label></td></tr></table>\n";
// echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['pc_quantity'] == 1 ? ' checked' : '') . " id='col_pc_quantity' name='col[pc_quantity]' type='checkbox'></td><td width='99%'><label for='col_pc_quantity'>Qty PC</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['user_fullname'] == 1 ? ' checked' : '') . " id='col_user_fullname' name='col[user_fullname]' type='checkbox'></td><td width='99%'><label for='col_user_fullname'>Create User</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['user_fullname_u'] == 1 ? ' checked' : '') . " id='col_user_fullname_u' name='col[user_fullname_u]' type='checkbox'></td><td width='99%'><label for='col_user_fullname_u'>Update User</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['create_date'] == 1 ? ' checked' : '') . " id='col_create_date' name='col[create_date]' type='checkbox'></td><td width='99%'><label for='col_create_date'>Create Date</label></td></tr></table>\n";
echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['update_date'] == 1 ? ' checked' : '') . " id='col_update_date' name='col[update_date]' type='checkbox'></td><td width='99%'><label for='col_update_date'>Update Date</label></td></tr></table>\n";
echo "</td>\n";
echo "<td width='1' valign='top'><input type='submit' value='Simpan'></td>\n";
echo "<td width='1' valign='top'><input type='button' value='Batalkan' onclick='customizeColumn(false);'></td>\n";
echo "</tr></table>\n";
echo "</form>\n";
?>
<script type="text/javascript">
<!--

$(function() {
    $("#date_f").datepicker({dateFormat: '<?php echo $APP_DATE_FORMAT_JAVA; ?>'});
    $("#date_t").datepicker({dateFormat: '<?php echo $APP_DATE_FORMAT_JAVA; ?>'});
    $("#tgl_f").datepicker({dateFormat: '<?php echo $APP_DATE_FORMAT_JAVA; ?>'});
    $("#tgl_t").datepicker({dateFormat: '<?php echo $APP_DATE_FORMAT_JAVA; ?>'});

});

function exportCSV() {
    form = document.createElement("form");
    form.setAttribute("method", "post");
    form.setAttribute("action", "action/view.wo_realisasi.php");

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "mode");
    hiddenField.setAttribute("value", "export-all");
    form.appendChild(hiddenField);

    // hiddenField = document.createElement("input");
    // hiddenField.setAttribute("type", "hidden");
    // hiddenField.setAttribute("name", "mesin");
    // hiddenField.setAttribute("value", "<?php echo $_REQUEST['mesin']; ?>");
    // form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "document_no");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['document_no']; ?>");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "item_number");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['item_number']; ?>");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "date_f");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['date_f']; ?>");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "date_t");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['date_t']; ?>");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "tgl_f");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['tgl_f']; ?>");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "tgl_t");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['tgl_t']; ?>");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "sc_number");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['sc_number']; ?>");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "customer");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['customer']; ?>");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "cgx_filter1");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['cgx_filter1']; ?>");
    form.appendChild(hiddenField);

    document.body.appendChild(form);
    form.submit();    
}

function customizeColumn(s) {
    var divCols = document.getElementById('columns');
    var divBar = document.getElementById('bar');
    if (s) {
        divCols.style.display = 'block';
        divBar.style.display = 'none';
    } else {
        window.location = window.location;
    }
}
//-->
</script>
<?php

//if ($mesin) $cgx_sql .= " AND m_work_order.m_machine_id = '" . mysql_escape_string($mesin) . "'";
if ($document_no) {
    $cgx_sql .= " AND m_work_order.document_no LIKE '%" . mysql_escape_string($document_no) . "%'";
    $cgx_sqltotal .= " AND m_work_order.document_no LIKE '%" . mysql_escape_string($document_no) . "%'";
} 
if ($item_number) {
    $cgx_sql .= " AND (rec.product_code LIKE '%" . mysql_escape_string($item_number) . "%' OR rec.product_name LIKE '%" . mysql_escape_string($item_number) . "%' OR rec.description LIKE '%" . mysql_escape_string($item_number) . "%')";
    $cgx_sqltotal .= " AND (rec.product_code LIKE '%" . mysql_escape_string($item_number) . "%' OR rec.product_name LIKE '%" . mysql_escape_string($item_number) . "%' OR rec.description LIKE '%" . mysql_escape_string($item_number) . "%')";
}
if ($date_f) {
    $cgx_sql .= " AND m_work_order.order_date >= '" . npl_dmy2ymd($date_f) . "'";
    $cgx_sqltotal .= " AND m_work_order.order_date >= '" . npl_dmy2ymd($date_f) . "'";
}
if ($date_t) {
    $cgx_sql .= " AND m_work_order.order_date <= '" . npl_dmy2ymd($date_t) . "'";
    $cgx_sqltotal .= " AND m_work_order.order_date <= '" . npl_dmy2ymd($date_t) . "'";
}
if ($sc_number) {
    $cgx_sql .= " AND c_order.remark LIKE '%" . mysql_escape_string($sc_number) . "%'";
    $cgx_sqltotal .= " AND c_order.remark LIKE '%" . mysql_escape_string($sc_number) . "%'";
}
if ($customer) {
    $cgx_sql .= " AND (c_bpartner.partner_name LIKE '%" . mysql_escape_string($customer) . "%' OR cb2.partner_name LIKE '%" . mysql_escape_string($customer) . "%')";
    $cgx_sqltotal .= " AND (c_bpartner.partner_name LIKE '%" . mysql_escape_string($customer) . "%' OR cb2.partner_name LIKE '%" . mysql_escape_string($customer) . "%')";
}
//$cgx_sql .= " AND ( auc.user_name LIKE '%{$cgx_search}%' OR auc.user_fullname LIKE '%{$cgx_search}%')";
if (strlen($cgx_filter1) > 0){
    switch ($cgx_filter1) {
        case 'O':
            $cgx_sql .= " AND (m_work_order_line.order_quantity - COALESCE(qty_production,0)) > 0";            
            $cgx_sqltotal .= " AND (m_work_order_line.order_quantity - COALESCE(qty_production,0)) > 0";            
            break;
    }
}

$cgx_test = $cgx_datagrid->bind($cgx_sql, $cgx_options);
if (PEAR::isError($cgx_test)) {
    echo $cgx_test->getMessage();
}

if ($cgx_def_columns['document_no'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('No. WO', 'document_no', 'document_no', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['order_date'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Tgl WO', 'order_date', 'order_date', array('align' => 'center'), NULL, "cgx_format_date()"));
if ($cgx_def_columns['remark'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Remark / Forecast', 'remark', 'remark', array('align' => 'left'), NULL, "grid_so"));
if ($cgx_def_columns['so_number'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('SO Number', 'so_number', 'so_number', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['reference_no'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('No. PO', 'reference_no', 'reference_no', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['s_order_date'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Tgl SO', 's_order_date', 's_order_date', array('align' => 'center'), NULL, "cgx_format_date()"));
if ($cgx_def_columns['partner_code'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Kode Customer', 'partner_code', 'partner_code', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['partner_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Nama Customer', 'partner_name', 'partner_name', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['machine_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Mesin', 'machine_name', 'machine_name', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['m_product_id'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Product ID', 'm_product_id', 'm_product_id', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['product_code'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Item Number', 'product_code', 'product_code', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['product_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Nama Produk', 'product_name', 'product_name', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['spec'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Spec', 'spec', 'spec', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['od'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('OD', 'od', 'od', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['thickness'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Thickness', 'thickness', 'thickness', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['length'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Length', 'length', 'length', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['m_product_material'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Product ID LP', 'm_product_material', 'm_product_material', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['product_codem'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Item Number LP', 'product_codem', 'product_codem', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['product_namem'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Nama Produk LP', 'product_namem', 'product_namem', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['lengthm'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Length LP', 'lengthm', 'lengthm', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['tolerance_size'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Tolerance Size', 'tolerance_size', 'tolerance_size', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['proces_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Next Proces', 'proces_name', 'proces_name', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['material_quantity'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Long Pipe Qty', 'material_quantity', 'material_quantity', array('align' => 'right'), NULL, "cgx_format_3digit()"));
if ($cgx_def_columns['order_quantity'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Qty WO', 'order_quantity', 'order_quantity', array('align' => 'right'), NULL, "cgx_format_3digit()"));
if ($cgx_def_columns['qty_production'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Qty Realisasi', 'qty_production', 'qty_production', array('align' => 'right'), NULL, "cgx_format_3digit()"));
if ($cgx_def_columns['outstanding_wo'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Qty Outstanding', 'outstanding_wo', 'outstanding_wo', array('align' => 'right'), NULL, "cgx_format_3digit()"));
// if ($cgx_def_columns['producted_quantity'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Qty CUT', 'producted_quantity', 'producted_quantity', array('align' => 'right'), NULL, "cgx_format_3digit()"));
// if ($cgx_def_columns['ch_quantity'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Qty CH', 'ch_quantity', 'ch_quantity', array('align' => 'right'), NULL, "cgx_format_3digit()"));
// if ($cgx_def_columns['sk_quantity'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Qty SK', 'sk_quantity', 'sk_quantity', array('align' => 'right'), NULL, "cgx_format_3digit()"));
// if ($cgx_def_columns['pl_quantity'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Qty PL', 'pl_quantity', 'pl_quantity', array('align' => 'right'), NULL, "cgx_format_3digit()"));
// if ($cgx_def_columns['bd_quantity'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Qty BD', 'bd_quantity', 'bd_quantity', array('align' => 'right'), NULL, "cgx_format_3digit()"));
// if ($cgx_def_columns['qc_quantity'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Qty QC', 'qc_quantity', 'qc_quantity', array('align' => 'right'), NULL, "cgx_format_3digit()"));
// if ($cgx_def_columns['pc_quantity'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Qty PC', 'pc_quantity', 'pc_quantity', array('align' => 'right'), NULL, "cgx_format_3digit()"));
if ($cgx_def_columns['user_fullname'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Create User', 'user_fullname', 'user_fullname', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['user_fullname_u'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Update User', 'user_fullname_u', 'user_fullname_u', array('align' => 'left'), NULL, NULL));
if ($cgx_def_columns['create_date'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Create Date', 'create_date', 'create_date', array('align' => 'center'), NULL, "cgx_format_timestamp()"));
if ($cgx_def_columns['update_date'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Update Date', 'update_date', 'update_date', array('align' => 'center'), NULL, "cgx_format_timestamp()"));
$cgx_table = new HTML_Table($cgx_TableAttribs);
$cgx_tableHeader = & $cgx_table->getHeader();
$cgx_tableBody = & $cgx_table->getBody();

$cgx_test = $cgx_datagrid->fill($cgx_table, $cgx_RendererOptions);
if (PEAR::isError($cgx_test)) {
    echo $cgx_test->getMessage();
}

$cgx_tableHeader->setRowAttributes(0, $cgx_HeaderAttribs);
$cgx_tableBody->altRowAttributes(0, $cgx_EvenRowAttribs, $cgx_OddRowAttribs, TRUE);

echo "<div class='datagrid_background'>\n";
echo $cgx_table->toHtml();
echo "</div>\n";

echo "<table width='100%'><tr>\n";
echo "<td class='datagrid_pager'>Data berjumlah " . number_format($cgx_datagrid->getRecordCount()) . " baris</td>\n";
echo "<td align='right' class='datagrid_pager'>\n";
$cgx_test = $cgx_datagrid->render(DATAGRID_RENDER_PAGER);
if (PEAR::isError($cgx_test)) {
    echo $cgx_test->getMessage();
}
echo "</td></tr></table>\n";


    $cgx_data_sum = cgx_fetch_table($cgx_sqltotal);

    echo "<div class='tbl-header-box' style='font-size: 12px; border: 1px solid #ccc; margin-top: 10px;'>";
    echo "  <table class=''>";
    echo "  <tr>";
    echo "      <td><b>Total Quantity WO</b></td>";
    echo "      <td width='10' align='center'>:</td>";
    echo "      <td align='right'><b>".  number_format($cgx_data_sum["order_quantity"], 2)."</b></td>";
    echo "      <td width='100px;'>&nbsp;</td>";
    echo "  </tr>"; 
    echo "  <tr>";
    echo "      <td><b>Total Quantity Realisasi</b></td>";
    echo "      <td width='10' align='center'>:</td>";
    echo "      <td align='right'><b>".  number_format($cgx_data_sum["qty_production"], 2)."</b></td>";
    echo "      <td width='100px;'>&nbsp;</td>";
    echo "  </tr>";
    echo "  <tr>";
    echo "      <td colspan='3'><hr></td>";
    echo "  </tr>";
    echo "  <tr>";
    echo "      <td><b>Total Quantity Outstanding</b></td>";
    echo "      <td width='10' align='center'>:</td>";
    echo "      <td align='right'><b>".  number_format($cgx_data_sum["outstanding_wo"], 2)."</b></td>";
    echo "      <td width='100px;'>&nbsp;</td>";
    echo "  </tr>";
    // echo "  <tr>";
    // echo "      <td><b>QTY Cutting</b></td>";
    // echo "      <td width='10' align='center'>:</td>";
    // echo "      <td align='right'><b>".  number_format($cgx_data_sum["producted_quantity"], 2)."</b></td>";
    // echo "      <td width='100px;'>&nbsp;</td>";
    // echo "  </tr>";
    // echo "  <tr>";
    // echo "      <td><b>QTY Champer</b></td>";
    // echo "      <td width='10' align='center'>:</td>";
    // echo "      <td align='right'><b>".  number_format($cgx_data_sum["ch_quantity"], 2)."</b></td>";
    // echo "      <td width='100px;'>&nbsp;</td>";
    // echo "  </tr>";
    // echo "  <tr>";
    // echo "      <td><b>QTY Sikat</b></td>";
    // echo "      <td width='10' align='center'>:</td>";
    // echo "      <td align='right'><b>".  number_format($cgx_data_sum["sk_quantity"], 2)."</b></td>";
    // echo "      <td width='100px;'>&nbsp;</td>";
    // echo "  </tr>";
    // echo "  <tr>";
    // echo "      <td><b>QTY Polesing</b></td>";
    // echo "      <td width='10' align='center'>:</td>";
    // echo "      <td align='right'><b>".  number_format($cgx_data_sum["pl_quantity"], 2)."</b></td>";
    // echo "      <td width='100px;'>&nbsp;</td>";
    // echo "  </tr>";
    // echo "  <tr>";
    // echo "      <td><b>QTY Bending</b></td>";
    // echo "      <td width='10' align='center'>:</td>";
    // echo "      <td align='right'><b>".  number_format($cgx_data_sum["bd_quantity"], 2)."</b></td>";
    // echo "      <td width='100px;'>&nbsp;</td>";
    // echo "  </tr>";
    // echo "  <tr>";
    // echo "      <td><b>QTY Quencing</b></td>";
    // echo "      <td width='10' align='center'>:</td>";
    // echo "      <td align='right'><b>".  number_format($cgx_data_sum["qc_quantity"], 2)."</b></td>";
    // echo "      <td width='100px;'>&nbsp;</td>";
    // echo "  </tr>";
    // echo "  <tr>";
    // echo "      <td><b>QTY Packing</b></td>";
    // echo "      <td width='10' align='center'>:</td>";
    // echo "      <td align='right'><b>".  number_format($cgx_data_sum["pc_quantity"], 2)."</b></td>";
    // echo "      <td width='100px;'>&nbsp;</td>";
    // echo "  </tr>";      
    echo "  </table>";
    echo "</div>";

?>
