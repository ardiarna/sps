<?php

/**
 * (c) Azwari Nugraha <nugraha@pt-gai.com>
 * 25/11/2013 00:52:21
 */


echo "<div class='title'>Realisasi Work Order Slitting</div>";

function cgx_edit($data) {
    $href = "module.php?&m={$_REQUEST['m']}&pkey[m_prod_slit_id]={$data['record']['m_prod_slit_id']}";
    $out = "<a href='{$href}'><img title='Edit' src='images/icon_edit.png' border='0'></a>";
    return $out;
}

function cgx_delete($data) {
    $href  = "javascript:if(confirm('Hapus baris ini?')){window.location='action/trx.rwos.php";
    $href .= "?backvar=module.php%253F%2526m%253Dtrx.rwos&mode=delete&pkey[m_prod_slit_id]={$data['record']['m_prod_slit_id']}';}";
    $out = "<a href=\"{$href}\"><img title='Delete this row' src='images/icon_delete.png' border='0'></a>";
    return $out;
}

if (strlen($_REQUEST['pkey']['m_prod_slit_id']) > 0) {
    include_once 'trx.rwos.edit.php';
} else {
    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';

    if (is_array($_SESSION[$GLOBALS['APP_ID']]['trx.rwos']['columns'])) {
        $cgx_def_columns = $_SESSION[$GLOBALS['APP_ID']]['trx.rwos']['columns'];
    } else {
        $cgx_def_columns = array(
            'm_prod_slit_id' => 1,
            'production_date' => 1,
            'document_no' => 1,
            'wo' => 1,
            'product_name' => 1,
            'partner' => 1
            //'partner_name' => 1,
        );
        $_SESSION[$GLOBALS['APP_ID']]['trx.rwos']['columns'] = $cgx_def_columns;
    }
    
    /*
    $cgx_sql = "SELECT m_prod_slit.*, m_wo_slit.document_no wo, product_name, partner_name, auc.user_fullname, auu.user_fullname user_fullname_u
            FROM m_prod_slit
            JOIN m_wo_slit ON (m_prod_slit.m_wo_slit_id = m_wo_slit.m_wo_slit_id)
            JOIN m_product ON(m_wo_slit.m_product_id=m_product.m_product_id)
            JOIN c_bpartner ON(m_wo_slit.c_bpartner_id=c_bpartner.c_bpartner_id)
            LEFT JOIN app_user auc ON (m_prod_slit.create_user=auc.user_id) 
            LEFT JOIN app_user auu ON (m_prod_slit.update_user=auu.user_id) 
            WHERE m_prod_slit.production_type = 1 ";
    
    */
    
    $cgx_sql = "
        SELECT 
            m_prod_slit.*
            ,m_wo_slit.document_no AS wo
            ,m_wo_slit.partner AS partner 
            ,product_name
            ,auc.user_fullname
            ,auu.user_fullname AS user_fullname_u
        
        FROM 
            m_prod_slit JOIN m_wo_slit ON (m_prod_slit.m_wo_slit_id = m_wo_slit.m_wo_slit_id)
                        JOIN m_product ON(m_wo_slit.m_product_id=m_product.m_product_id)
                        -- JOIN c_bpartner ON(m_wo_slit.c_bpartner_id=c_bpartner.c_bpartner_id)
                   LEFT JOIN app_user auc ON (m_prod_slit.create_user=auc.user_id) 
                   LEFT JOIN app_user auu ON (m_prod_slit.update_user=auu.user_id) 
            
        WHERE 
            m_prod_slit.production_type = 1 ";
    
    $cgx_sql .= " AND " . org_filter_trx('m_wo_slit.app_org_id');
    $cgx_datagrid = new Structures_DataGrid($cgx_max_rows);
    $cgx_options = array('dsn' => $cgx_dsn);
    $cgx_datagrid->setDefaultSort(array('production_date' => 'DESC'));
    $cgx_search = $_REQUEST['q'];

    echo "<form name='frmFILTER' action='{$_SERVER['SCRIPT_NAME']}'>\n";
    echo "<input type='hidden' name='m' value='{$_REQUEST['m']}'>\n";
    echo "<table id='bar' class='datagrid_bar' width='100%'><tr>\n";
    echo "<td><input type='button' value='Dokumen Baru' onclick=\"window.location = 'module.php?&m={$_REQUEST['m']}&pkey[m_prod_slit_id]=0&mode=edit';\"></td>";
    echo "<td align='right'><input type='text' size='20' name='q' value=\"{$cgx_search}\"></td>\n";
    echo "<td width='1'><input title='Cari' type='image' src='images/icon_search.png' border='0' style='padding-right: 20px;'></td>\n";
    echo "<td></td>\n";
    echo "<td width='20'></td>\n";
    if (has_privilege('trx.rwos')) {
        echo "<td width='1' class='datagrid_bar_icon'><a title='Dokumen Baru' href='module.php?&m={$_REQUEST['m']}&pkey[m_prod_slit_id]=0&mode=edit'><img border='0' src='images/icon_add.png'></a></td>\n";
    } else {
        echo "<td width='1' class='datagrid_bar_icon'><img border='0' src='images/icon_add_dis.png'></td>\n";
    }
    echo "<td width='1' class='datagrid_bar_icon'><a title='Kustomisasi kolom' href='javascript:customizeColumn(true);'><img border='0' src='images/icon_columns.png'></a></td>\n";
    echo "</tr></table>\n";
    echo "</form>\n";

    echo "<form name='frmCUSTOMIZE' action='action/cgx_customize.php' method='post'>\n";
    echo "<input type='hidden' name='back' value='" . urlencode($_SERVER['REQUEST_URI']) . "'>\n";
    echo "<input type='hidden' name='dg_name' value='trx.rwos'>\n";
    echo "<input type='hidden' name='col[m_prod_slit_id]' value='on'>\n";
    echo "<input type='hidden' name='col[production_date]' value='on'>\n";
    echo "<input type='hidden' name='col[document_no]' value='on'>\n";
    echo "<input type='hidden' name='col[wo]' value='on'>\n";
    echo "<table id='columns' class='datagrid_bar' style='display: none;'><tr>\n";
    echo "<td width='99%' valign='top'>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_m_prod_slit_id' name='col[m_prod_slit_id]' type='checkbox'></td><td width='99%'><label for='col_m_prod_slit_id'>ID</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_production_date' name='col[production_date]' type='checkbox'></td><td width='99%'><label for='col_production_date'>Tanggal</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_document_no' name='col[document_no]' type='checkbox'></td><td width='99%'><label for='col_document_no'>Nomor Dokumen</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_wo' name='col[wo]' type='checkbox'></td><td width='99%'><label for='col_wo'>No W/O</label></td></tr></table>\n";
    
    //echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['partner_name'] == 1 ? ' checked' : '') . " id='col_partner_name' name='col[partner_name]' type='checkbox'></td><td width='99%'><label for='col_partner_name'>Customer</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['partner'] == 1 ? ' checked' : '') . " id='col_partner' name='col[partner]' type='checkbox'></td><td width='99%'><label for='col_partner'>Customer</label></td></tr></table>\n";
    
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['product_name'] == 1 ? ' checked' : '') . " id='col_product_name' name='col[product_name]' type='checkbox'></td><td width='99%'><label for='col_product_name'>Coil RAW</label></td></tr></table>\n";
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

    $cgx_sql .= " AND ( "
            . "m_prod_slit.m_prod_slit_id LIKE '%{$cgx_search}%' "
            . "OR m_prod_slit.document_no LIKE '%{$cgx_search}%' "
            . "OR m_prod_slit.production_date LIKE '%{$cgx_search}%' "
            . "OR m_wo_slit.document_no LIKE '%{$cgx_search}%' "
            . "OR m_product.product_name LIKE '%{$cgx_search}%')";
            
    if ($_SESSION[$GLOBALS['APP_ID']]['trx.rwos']['error']) {
        echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['trx.rwos']['error']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['trx.rwos']['error']);
    }

    if ($_SESSION[$GLOBALS['APP_ID']]['trx.rwos']['info']) {
        echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['trx.rwos']['info']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['trx.rwos']['info']);
    }


    $cgx_test = $cgx_datagrid->bind($cgx_sql, $cgx_options);
    if (PEAR::isError($cgx_test)) {
        echo $cgx_test->getMessage();
    }

    if ($cgx_def_columns['m_prod_slit_id'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('ID', 'm_prod_slit_id', 'm_prod_slit_id', array('align' => 'right'), NULL, NULL));
    if ($cgx_def_columns['production_date'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Tanggal', 'production_date', 'production_date', array('align' => 'center'), NULL, "cgx_format_date()"));
    if ($cgx_def_columns['document_no'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Nomor Dokumen', 'document_no', 'document_no', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['wo'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('No W/O', 'wo', 'wo', array('align' => 'left'), NULL, NULL));
    
    //if ($cgx_def_columns['partner_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Customer', 'partner_name', 'partner_name', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['partner'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Customer', 'partner', 'partner', array('align' => 'left'), NULL, NULL));
    
    if ($cgx_def_columns['product_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Coil RAW', 'product_name', 'product_name', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['user_fullname'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Create User', 'user_fullname', 'user_fullname', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['user_fullname_u'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Update User', 'user_fullname_u', 'user_fullname_u', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['create_date'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Create Date', 'create_date', 'create_date', array('align' => 'center'), NULL, "cgx_format_timestamp()"));
    if ($cgx_def_columns['update_date'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Update Date', 'update_date', 'update_date', array('align' => 'center'), NULL, "cgx_format_date()"));
    if (has_privilege('trx.rwos')) $cgx_datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, 'cgx_edit()'));
    //if (has_privilege('trx.rwos') AND (user() == 2 OR user() == 46 OR user() == 51)) $cgx_datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, 'cgx_delete()'));

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