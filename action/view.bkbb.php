<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 05/12/2013 14:35:39
 */

require_once '../init.php';

if (!authenticated()) {
    header("Location: ../index.php");
    exit;
}

$cgx_sql = "SELECT m_inout.document_no, m_inout.m_inout_date, m_inout.dokumen, m_inout.no_kendaraan, 
            c_order.document_no so_number, c_order.order_date, partner_code, product_code, product_name, warehouse_name,
            m_inout.tuj_org_id as m_machine_id, 
            machine_name, reference_no, partner_name, spec, od, thickness, length, no_box, m_inout_line.quantity FROM m_inout
            LEFT JOIN m_machine ON(m_inout.tuj_org_id=m_machine.m_machine_id) 
            LEFT JOIN c_order ON (m_inout.c_order_id=c_order.c_order_id)
            LEFT JOIN c_bpartner ON(c_order.c_bpartner_id=c_bpartner.c_bpartner_id)
            JOIN m_inout_line ON(m_inout.m_inout_id=m_inout_line.m_inout_id)
            JOIN m_product ON(m_inout_line.m_product_id=m_product.m_product_id)
            JOIN m_warehouse ON(m_inout_line.m_warehouse_id=m_warehouse.m_warehouse_id)
            WHERE m_inout.m_transaction_type_id = 4 ";
$cgx_sql .= " AND " . org_filter_trx('m_inout.app_org_id');

$gudang = $_REQUEST['gudang'];
$date_f = $_REQUEST['date_f'];
$date_t = $_REQUEST['date_t'];
$item_number = $_REQUEST['item_number'];
$document_no = $_REQUEST['document_no'];
$sc_number = $_REQUEST['sc_number'];
$customer = $_REQUEST['customer'];

if ($gudang) $cgx_sql .= " AND m_inout_line.m_warehouse_id = '" . mysql_escape_string($gudang) . "'";
if ($document_no) $cgx_sql .= " AND m_inout.document_no LIKE '%" . mysql_escape_string($document_no) . "%'";
if ($item_number) $cgx_sql .= " AND (m_product.product_code LIKE '%" . mysql_escape_string($item_number) . "%' OR m_product.product_name LIKE '%" . mysql_escape_string($item_number) . "%' OR m_product.description LIKE '%" . mysql_escape_string($item_number) . "%')";
if ($date_f) $cgx_sql .= " AND m_inout_date >= '" . npl_dmy2ymd($date_f) . "'";
if ($date_t) $cgx_sql .= " AND m_inout_date <= '" . npl_dmy2ymd($date_t) . "'";
if ($sc_number) $cgx_sql .= " AND c_order.document_no LIKE '%" . mysql_escape_string($sc_number) . "%'";
if ($customer) $cgx_sql .= " AND c_bpartner.partner_name LIKE '%" . mysql_escape_string($customer) . "%'";

if ($_REQUEST['mode'] == 'export-all') {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=\"view.bkbb-" . date("Y-m-d") . ".csv\"");
    echo "\"No. Pengiriman\",\"Tgl Order\",\"No. WO Recutt\",\"No. Lot Number\",\"Tgl Pengiriman\",\"SC Number\",\"Kode Customer\",\"Nama Customer\",\"Item Number\",\"Nama Barang\",\"Spec\",\"OD\",\"Thickness\",\"Length\",\"Kode Koil\",\"Gudang\",\"Mesin\",\"Jumlah Barang\"\n";
    $cgx_rs_export = mysql_query($cgx_sql, $cgx_connection);
    while (($cgx_dt_export = mysql_fetch_array($cgx_rs_export, MYSQL_ASSOC)) !== FALSE) {
        echo "\"" . str_replace("\"", "\"\"", $cgx_dt_export['document_no']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['order_date']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['dokumen']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['no_kendaraan']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['m_inout_date']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['so_number']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['partner_code']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['partner_name']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['product_code']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['product_name']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['spec']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['od']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['thickness']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['length']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['no_box']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['warehouse_name']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['machine_name']) . "\"";
        echo ",\"" . str_replace("\"", "\"\"", $cgx_dt_export['quantity']) . "\"";
        echo "\n";
    }
    mysql_free_result($cgx_rs_export);
    exit;
} elseif ($_REQUEST['mode'] == 'update') {
    $cgx_sql = "UPDATE  SET";
    $cgx_sql .= " WHERE";
} elseif ($_REQUEST['mode'] == 'new') {
    $cgx_sql = "INSERT INTO  (";
    $cgx_sql .= "INSERT INTO  (";
    $cgx_sql .= ") values (";
    $cgx_sql .= ")";
} elseif ($_REQUEST['mode'] == 'delete') {
    $cgx_sql = "DELETE FROM  ";
    $cgx_sql .= " WHERE";
}

if (@mysql_query($cgx_sql, $cgx_connection)) {
    $_SESSION[$GLOBALS['APP_ID']]['view.bkbb']['error'] = FALSE;
    $_SESSION[$GLOBALS['APP_ID']]['view.bkbb']['info'] = 'Data anda sudah berhasil diperbarui';
    if ($_REQUEST['mode'] == 'new') $cgx_new_id = $_REQUEST['data']['Array'];
} else {
    $_SESSION[$GLOBALS['APP_ID']]['view.bkbb']['error'] = mysql_error($cgx_connection);
}

if ($_REQUEST['mode'] == 'update') {
    header("Location: ../index.php?" . urldecode($_REQUEST['backvar']) . "&");
} elseif ($_REQUEST['mode'] == 'new') {
    header("Location: ../index.php?" . urldecode($_REQUEST['backvar']) . "&");
} elseif ($_REQUEST['mode'] == 'delete') {
    header("Location: ../index.php?" . urldecode($_REQUEST['backvar']));
}
exit;

?>