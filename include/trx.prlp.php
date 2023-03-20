<?php

/**
 * (c) Azwari Nugraha <nugraha@pt-gai.com>
 * 23/11/2013 12:40:39
 */


echo "<div class='title'>Penerimaan Request LP</div>";

function cgx_edit($data) {
    $href = "module.php?m={$_REQUEST['m']}&pkey[m_receipt_longpipe_id]={$data['record']['m_receipt_longpipe_id']}";
    $out = "<a href='{$href}'><img title='Edit' src='images/icon_edit.png' border='0'></a>";
    return $out;
}

function cgx_delete($data) {
    $href  = "javascript:if(confirm('Hapus baris ini?')){window.location='action/trx.prlp.php";
    $href .= "?backvar=module.php%253F%2526m%253Dtrx.prlp&mode=delete&pkey[m_receipt_longpipe_id]={$data['record']['m_receipt_longpipe_id']}';}";
    $out = "<a href=\"{$href}\"><img title='Delete this row' src='images/icon_delete.png' border='0'></a>";
    return $out;
}

function grid_so($data) {
    $href = "module.php?m={$_REQUEST['m']}&pkey[m_receipt_longpipe_id]={$data['record']['m_receipt_longpipe_id']}";
    $out = "<a href='{$href}'>{$data['record']['document_no']}</a>";
    return $out;
}

function grid_status1($data) {
    return $GLOBALS['SO_STATUS'][$data['record']['status']];
}

if (strlen($_REQUEST['pkey']['m_receipt_longpipe_id']) > 0) {
    include_once 'trx.prlp.edit.php';
} else {
    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';

    if (is_array($_SESSION[$GLOBALS['APP_ID']]['trx.prlp']['columns'])) {
        $cgx_def_columns = $_SESSION[$GLOBALS['APP_ID']]['trx.prlp']['columns'];
    } else {
        $cgx_def_columns = array(
           'm_receipt_longpipe_id' => 1,
           'receipt_date' => 1,
           'nomordokumen' => 1,
           'requestlp' => 1,
           'nosc' => 1,
           'nopo' => 1,
           'partner_name' => 1,
        );
        $_SESSION[$GLOBALS['APP_ID']]['trx.prlp']['columns'] = $cgx_def_columns;
    }
    
    $cgx_sql = "SELECT 
                m_receipt_longpipe.m_receipt_longpipe_id, 
                m_receipt_longpipe.receipt_date, 
                m_receipt_longpipe.document_no nomordokumen, 
                m_work_order.document_no requestlp, 
                remark nosc, 
                reference_no nopo,
                partner_name, 
                producted_quantity

                FROM m_receipt_longpipe

                JOIN m_work_order USING (m_work_order_id)
                JOIN m_receipt_longpipe_line USING (m_receipt_longpipe_id)
                JOIN m_work_order_line USING (m_work_order_line_id)
 
                LEFT JOIN c_order USING (c_order_id) 
                LEFT JOIN c_bpartner ON (c_order.c_bpartner_id = c_bpartner.c_bpartner_id)
                JOIN m_product ON(m_work_order_line.m_product_id=m_product.m_product_id)
                WHERE 1=1";
    
    $cgx_datagrid = new Structures_DataGrid($cgx_max_rows);
    $cgx_options = array('dsn' => $cgx_dsn);
    $cgx_datagrid->setDefaultSort(array('receipt_date' => 'DESC'));
    $cgx_search = $_REQUEST['cgx_search'];
    
    echo "<form name='frmFILTER' action='{$_SERVER['SCRIPT_NAME']}'>\n";
    echo "<input type='hidden' name='m' value='{$_REQUEST['m']}'>\n";
    echo "<table id='bar' class='datagrid_bar' width='100%' border='0'><tr>\n";
    
    echo "<td><input type='button' value='Dokumen Baru' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}&pkey[m_receipt_longpipe_id]=0&mode=edit';\"></td>";
    
    echo "<td align='right'><input type='text' size='20' name='cgx_search' value=\"{$cgx_search}\"></td>\n";
    echo "<td width='1'><input title='Cari' type='image' src='images/icon_search.png' border='0' style='padding-right: 20px;'></td>\n";
    echo "<td></td>\n";
    
    echo "<td width='20'></td>\n";
    
    if (has_privilege('trx.prlp')) {
        echo "<td width='1' class='datagrid_bar_icon'><a title='Dokumen Baru' href='module.php?m={$_REQUEST['m']}&pkey[m_receipt_longpipe_id]=0&mode=edit'><img border='0' src='images/icon_add.png'></a></td>\n";
    } else {
        echo "<td width='1' class='datagrid_bar_icon'><img border='0' src='images/icon_add_dis.png'></td>\n";
    }
    
    echo "<td width='1' class='datagrid_bar_icon'><a title='Customize columns' href='javascript:customizeColumn(true);'><img border='0' src='images/icon_columns.png'></a></td>\n";
    echo "</tr></table>\n";
    echo "</form>\n";

    echo "<form name='frmCUSTOMIZE' action='action/cgx_customize.php' method='post'>\n";
    echo "<input type='hidden' name='back' value='" . urlencode($_SERVER['REQUEST_URI']) . "'>\n";
    echo "<input type='hidden' name='dg_name' value='trx.prlp'>\n";
    echo "<input type='hidden' name='col[m_receipt_longpipe_id]' value='on'>\n";
    //echo "<input type='hidden' name='col[document_no]' value='on'>\n";
    echo "<table id='columns' class='datagrid_bar' style='display: none;'><tr>\n";
    echo "<td width='99%' valign='top'>\n";
    //echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_c_order_id' name='col[c_order_id]' type='checkbox'></td><td width='99%'><label for='col_c_order_id'>ID</label></td></tr></table>\n";
    //echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_document_no' name='col[document_no]' type='checkbox'></td><td width='99%'><label for='col_document_no'>Nomor Dokumen</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['m_receipt_longpipe_id'] == 1 ? ' checked' : '') . " id='col_m_receipt_longpipe_id' name='col[m_receipt_longpipe_id]' type='checkbox'></td><td width='99%'><label for='col_m_receipt_longpipe_id'>ID</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['receipt_date'] == 1 ? ' checked' : '') . " id='col_receipt_date' name='col[receipt_date]' type='checkbox'></td><td width='99%'><label for='col_receipt_date'>Tanggal</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['nomordokumen'] == 1 ? ' checked' : '') . " id='col_nomordokumen' name='col[nomordokumen]' type='checkbox'></td><td width='99%'><label for='col_nomordokumen'>Nomor Dokumen</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['requestlp'] == 1 ? ' checked' : '') . " id='col_requestlp' name='col[requestlp]' type='checkbox'></td><td width='99%'><label for='col_requestlp'>Request LP</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['nosc'] == 1 ? ' checked' : '') . " id='col_nosc' name='col[nosc]' type='checkbox'></td><td width='99%'><label for='col_nosc'>No. SC</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['nopo'] == 1 ? ' checked' : '') . " id='col_nopo' name='col[nopo]' type='checkbox'></td><td width='99%'><label for='col_nopo'>No. PO</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['partner_name'] == 1 ? ' checked' : '') . " id='col_partner_name' name='col[partner_name]' type='checkbox'></td><td width='99%'><label for='col_partner_name'>Customer</label></td></tr></table>\n";
    //echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['producted_quantity'] == 1 ? ' checked' : '') . " id='col_producted_quantity' name='col[producted_quantity]' type='checkbox'></td><td width='99%'><label for='producted_quantity'>Product Quantity</label></td></tr></table>\n";
 
    echo "<td width='1' valign='top'><input type='submit' value='Update'></td>\n";
    echo "<td width='1' valign='top'><input type='button' value='Cancel' onclick='customizeColumn(false);'></td>\n";
    echo "</tr></table>\n";
    echo "</form>\n";
?>
<script type="text/javascript">
<!--
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
    
    //if (strlen($cgx_filter1) > 0) $cgx_sql .= " AND c_forecast.app_org_id = '" . mysql_escape_string($cgx_filter1) . "'";
    if($cgx_search) $cgx_sql .= " AND (m_receipt_longpipe.m_receipt_longpipe_id LIKE '%{$cgx_search}%' 
    OR m_receipt_longpipe.document_no LIKE '%{$cgx_search}%' 
    OR m_work_order.document_no LIKE '%{$cgx_search}%' 
    OR c_order.remark LIKE '%{$cgx_search}%' 
    OR c_order.reference_no LIKE '%{$cgx_search}%' 
    OR c_bpartner.partner_name LIKE '%{$cgx_search}%')";
     
    if ($_SESSION[$GLOBALS['APP_ID']]['trx.prlp']['error']) {
        echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['trx.prlp']['error']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['trx.prlp']['error']);
    }

    if ($_SESSION[$GLOBALS['APP_ID']]['trx.prlp']['info']) {
        echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['trx.prlp']['info']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['trx.prlp']['info']);
    }

    $cgx_test = $cgx_datagrid->bind($cgx_sql, $cgx_options);
    if (PEAR::isError($cgx_test)) {
        echo $cgx_test->getMessage();
    }

    if ($cgx_def_columns['m_receipt_longpipe_id'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('ID', 'm_receipt_longpipe_id', 'm_receipt_longpipe_id', array('align' => 'right'), NULL, NULL));
    if ($cgx_def_columns['receipt_date'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Tanggal', 'receipt_date', 'receipt_date', array('align' => 'center'), NULL, "cgx_format_date()"));    
    if ($cgx_def_columns['nomordokumen'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Nomor Dokumen', 'nomordokumen', 'nomordokumen', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['requestlp'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Request LP', 'requestlp', 'requestlp', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['nosc'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('No. SC', 'nosc', 'nosc', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['nopo'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('No. PO', 'nopo', 'nopo', array('align' => 'left'), NULL, NULL)); 
    if ($cgx_def_columns['partner_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Customer', 'partner_name', 'partner_name', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['producted_quantity'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Product Quantity', 'producted_quantity', 'producted_quantity', array('align' => 'left'), NULL, NULL)); 
    if (trx) $cgx_datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, 'cgx_edit()'));
    if (trx) $cgx_datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, 'cgx_delete()'));

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