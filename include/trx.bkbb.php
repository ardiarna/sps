<?php

/**
 * (c) Azwari Nugraha <nugraha@pt-gai.com>
 * 25/11/2013 00:52:21
 */

//keterangan
//tanggal           = m_inout.m_inout_date
//mesin             = m_inout.tuj_org_id
//no WO recutting   = m_inout.dokumen
//no lot number     = m_inout.no_kendaraan
//kode koil         = m_inout_line.no_box

echo "<div class='title'>BKBB Recutting</div>";

function cgx_edit($data) {
    $href = "module.php?&m={$_REQUEST['m']}&pkey[m_inout_id]={$data['record']['m_inout_id']}";
    $out = "<a href='{$href}'><img title='Edit' src='images/icon_edit.png' border='0'></a>";
    return $out;
}

if (strlen($_REQUEST['pkey']['m_inout_id']) > 0) {
    include_once 'trx.bkbb.edit.php';
} else {
    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';

    $cgx_sql = "SELECT m_inout.*, m_inout.tuj_org_id as m_machine_id, 
                machine_name, reference_no, partner_name, spec, od, thickness, length, no_box, m_inout_line.quantity FROM m_inout
                LEFT JOIN m_machine ON(m_inout.tuj_org_id=m_machine.m_machine_id) 
                LEFT JOIN c_order ON (m_inout.c_order_id=c_order.c_order_id)
                LEFT JOIN c_bpartner ON(c_order.c_bpartner_id=c_bpartner.c_bpartner_id)
                JOIN m_inout_line ON(m_inout.m_inout_id=m_inout_line.m_inout_id)
                JOIN m_product ON(m_inout_line.m_product_id=m_product.m_product_id)
                WHERE m_inout.m_transaction_type_id = 4 ";
    $cgx_sql .= " AND " . org_filter_trx('m_inout.app_org_id');
    
    $cgx_datagrid = new Structures_DataGrid($cgx_max_rows);
    $cgx_options = array('dsn' => $cgx_dsn);
    $cgx_datagrid->setDefaultSort(array('m_inout_date' => 'DESC'));

    $cgx_search = $_REQUEST['q'];

    echo "<form name='frmFILTER' action='{$_SERVER['SCRIPT_NAME']}'>\n";
    echo "<input type='hidden' name='m' value='{$_REQUEST['m']}'>\n";
    echo "<table id='bar' class='datagrid_bar' width='100%'><tr>\n";
    echo "<td><input type='button' value='Dokumen Baru' onclick=\"window.location = 'module.php?&m={$_REQUEST['m']}&pkey[m_inout_id]=0&mode=edit';\"></td>";
    echo "<td align='right'><input type='text' size='20' name='q' value=\"{$cgx_search}\"></td>\n";
    echo "<td width='1'><input title='Cari' type='image' src='images/icon_search.png' border='0' style='padding-right: 20px;'></td>\n";
    echo "<td></td>\n";
    echo "<td width='20'></td>\n";
    if (has_privilege('trx.bkbb')) {
        echo "<td width='1' class='datagrid_bar_icon'><a title='New record' href='module.php?&m={$_REQUEST['m']}&pkey[m_inout_id]=0&mode=edit'><img border='0' src='images/icon_add.png'></a></td>\n";
    } else {
        echo "<td width='1' class='datagrid_bar_icon'><img border='0' src='images/icon_add_dis.png'></td>\n";
    }
    echo "</tr></table>\n";
    echo "</form>\n";

    $cgx_sql .= " and ( m_inout.m_inout_id LIKE '%{$cgx_search}%' OR m_inout.document_no LIKE '%{$cgx_search}%' OR m_inout.no_kendaraan LIKE '%{$cgx_search}%' OR reference_no LIKE '%{$cgx_search}%' OR partner_name LIKE '%{$cgx_search}%')";
    
    if ($_SESSION[$GLOBALS['APP_ID']]['trx.bkbb']['error']) {
        echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['trx.bkbb']['error']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['trx.bkbb']['error']);
    }

    if ($_SESSION[$GLOBALS['APP_ID']]['trx.bkbb']['info']) {
        echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['trx.bkbb']['info']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['trx.bkbb']['info']);
    }


    $cgx_test = $cgx_datagrid->bind($cgx_sql, $cgx_options);
    if (PEAR::isError($cgx_test)) {
        echo $cgx_test->getMessage();
    }

    $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Nomor', 'document_no', 'document_no', array('align' => 'left'), NULL, NULL));
    $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Tanggal', 'm_inout_date', 'm_inout_date', array('align' => 'left'), NULL, "cgx_format_date()"));
    $cgx_datagrid->addColumn(new Structures_DataGrid_Column('No WO Recutt', 'dokumen', 'dokumen', array('align' => 'left'), NULL, NULL));
    $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Mesin', 'machine_name', 'machine_name', array('align' => 'left'), NULL, NULL));
    $cgx_datagrid->addColumn(new Structures_DataGrid_Column('No Lot Number', 'no_kendaraan', 'no_kendaraan', array('align' => 'left'), NULL, NULL));
    $cgx_datagrid->addColumn(new Structures_DataGrid_Column('PO Number', 'reference_no', 'reference_no', array('align' => 'left'), NULL, NULL));
    $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Customer', 'partner_name', 'partner_name', array('align' => 'left'), NULL, NULL));
    $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Spec', 'spec', 'spec', array('align' => 'left'), NULL, NULL));
    $cgx_datagrid->addColumn(new Structures_DataGrid_Column('OD', 'od', 'od', array('align' => 'right'), NULL, NULL));
    $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Thick', 'thickness', 'thickness', array('align' => 'right'), NULL, NULL));
    $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Length', 'length', 'length', array('align' => 'right'), NULL, NULL));
    $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Kode Coil', 'no_box', 'no_box', array('align' => 'left'), NULL, NULL));
    $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Ket', 'ket', 'ket', array('align' => 'left'), NULL, NULL));
    $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Quantity', 'quantity', 'quantity', array('align' => 'right'), NULL, "cgx_format_3digit()"));
    if (has_privilege('trx.bkbb')) $cgx_datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, 'cgx_edit()'));

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
}

?>