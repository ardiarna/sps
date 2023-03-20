<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 03/01/2014 20:12:20
 */


echo "<div class='title'>Master Code Product Customer</div>";

function cgx_edit($data) {
    $href = "module.php?&m={$_REQUEST['m']}&pkey[id]={$data['record']['id']}";
    $out = "<a href='{$href}'><img title='Edit baris ini' src='images/icon_edit.png' border='0'></a>";
    return $out;
}

function cgx_delete($data) {
    $href  = "javascript:if(confirm('Menghapus baris ini?')){window.location='action/master.code_cust.php";
    $href .= "?backvar=module.php%253F%2526m%253Dmaster.product_c&mode=delete&pkey[id]={$data['record']['id']}';}";
    $out = "<a href=\"{$href}\"><img title='Hapus baris ini' src='images/icon_delete.png' border='0'></a>";
    return $out;
}

if (strlen($_REQUEST['pkey']['id']) > 0) {
    
    $cgx_id = $_REQUEST['id'];
    
    $cgx_data = cgx_fetch_table(
            "SELECT *"
            . "FROM m_code_prod_lp "            
            . "WHERE id = '" . mysql_escape_string($_REQUEST['pkey']['id']) . "'");
    
    echo "<form action='action/master.code_cust.php' method='post'>\n";
    echo "<input type='hidden' name='backvar' value='" . urlencode("module.php?&m={$_REQUEST['m']}") . "'>\n";
    echo "<input type='hidden' name='mode' value='" . ($_REQUEST['pkey']['id'] == '0' ? 'new' : 'update') . "'>\n";
    echo "<input type='hidden' name='pkey[id]' value=\"{$_REQUEST['pkey']['id']}\">\n";
    echo "<input type='hidden' name='table' value='m_product'>\n";
    
    if ($_SESSION[$GLOBALS['APP_ID']]['master.code_cust']['error']) {
        echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['master.code_cust']['error']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['master.code_cust']['error']);
    }

    if ($_SESSION[$GLOBALS['APP_ID']]['master.code_cust']['info']) {
        echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['master.code_cust']['info']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['master.code_cust']['info']);
    }
    //form untuk INPUT DATA dan EDIT
    echo "<ul class='cgx_form'>\n";
    
    echo "    <input type='hidden' name='type[id]' value='N'>";
    echo "    <li class='even'>\n";
    echo "        <label class='cgx_form' for='data_id'>ID</label>\n";
    echo "        <input id='data_id' name='data[id]' type='text' value=\"{$cgx_data['id']}\" size='8' maxlength='8' style='text-align: right;' disabled />\n";
    echo "    </li>\n";
    
    echo "    <input type='hidden' name='type[customer_code]' value='T'>";
    echo "    <li class='odd'>\n";
    echo "        <label class='cgx_form' for='data_customer_code'>Customer Code</label>\n";
    echo "        <input id='data_customer_code' name='data[customer_code]' type='text' value=\"{$cgx_data['customer_code']}\" size='30' maxlength='40' style='text-align: left;' />\n";
    echo "    </li>\n";
    
    echo "    <input type='hidden' name='type[od]' value='T'>";
    echo "    <li class='even'>\n";
    echo "        <label class='cgx_form' for='data_od'>OD</label>\n";
    echo "        <input id='data_od' name='data[od]' type='text' value=\"{$cgx_data['od']}\" size='20' maxlength='20' style='text-align: right;' />\n";
    echo "    </li>\n";
    
    echo "    <input type='hidden' name='type[thickness]' value='N'>";
    echo "    <li class='odd'>\n";
    echo "        <label class='cgx_form' for='data_thickness'>Thickness</label>\n";
    echo "        <input id='data_thickness' name='data[thickness]' type='text' value=\"{$cgx_data['thickness']}\" size='16' maxlength='16' style='text-align: right;' />\n";
    echo "    </li>\n";
    
    echo "    <input type='hidden' name='type[length]' value='N'>";
    echo "    <li class='even'>\n";
    echo "        <label class='cgx_form' for='data_length'>Length</label>\n";
    echo "        <input id='data_length' name='data[length]' type='text' value=\"{$cgx_data['length']}\" size='16' maxlength='16' style='text-align: right;' />\n";
    echo "    </li>\n";
    
    echo "    <input type='hidden' name='type[spec]' value='N'>";
    echo "    <li class='odd'>\n";
    echo "        <label class='cgx_form' for='data_spec'>Spec</label>\n";
    echo "        <input id='data_spec' name='data[spec]' type='text' value=\"{$cgx_data['spec']}\" size='16' maxlength='16' style='text-align: left;' />\n";
    echo "    </li>\n";
    
    echo "    <li class='even'>\n";
    echo "        <label class='cgx_form'></label>\n";
    echo "        <input type='submit' value='Simpan'>\n";
    echo "        <input type='button' value='Kembali' onclick=\"window.location = 'module.php?&m=master.code_cust';\">\n";
    echo "    </li>\n";
    echo "</ul>\n";
    echo "</form>\n";
 
} else {
    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';

    //$_REQUEST['f1'] = empty($_REQUEST['f1']) ? org() : $_REQUEST['f1'];
    
    if (is_array($_SESSION[$GLOBALS['APP_ID']]['master.code_cust']['columns'])) {
        $cgx_def_columns = $_SESSION[$GLOBALS['APP_ID']]['master.code_cust']['columns'];
    } else {
        $cgx_def_columns = array(
            'id' => 1,
            'customer_code' => 1,
            'od' => 1,
            'thickness' => 1,
            'length' => 1,
            'spec' => 1                      
        );
        $_SESSION[$GLOBALS['APP_ID']]['master.code_cust']['columns'] = $cgx_def_columns;
    }

    $cgx_sql = "SELECT * FROM m_code_prod_lp WHERE 1=1";
    $cgx_datagrid = new Structures_DataGrid($cgx_max_rows);
    $cgx_options = array('dsn' => $cgx_dsn);
    
    $cgx_search = $_REQUEST['cgx_search'];

    echo "<form name='frmFILTER' action='{$_SERVER['SCRIPT_NAME']}'>\n";
    echo "<input type='hidden' name='m' value='{$_REQUEST['m']}'>\n";
    echo "<table id='bar' class='datagrid_bar' width='100%'><tr>\n";
    echo "<td align='right'><input type='text' size='20' name='cgx_search' value=\"{$cgx_search}\"></td>\n";
    echo "<td width='1'><input title='Cari' type='image' src='images/icon_search.png' border='0' style='padding-right: 20px;'></td>\n";
    echo "<td></td>\n";
    echo "<td width='20'></td>\n";
    if (has_privilege('master.code_cust')) {
        echo "<td width='1' class='datagrid_bar_icon'><a title='Tambah data' href='module.php?&m={$_REQUEST['m']}&pkey[id]=0'><img border='0' src='images/icon_add.png'></a></td>\n";
    } else {
        echo "<td width='1' class='datagrid_bar_icon'><img border='0' src='images/icon_add_dis.png'></td>\n";
    }
    //echo "<td width='1' class='datagrid_bar_icon'><a title='Export semua data ke CSV' href='action/master.product_c.php?mode=export-all'><img border='0' src='images/icon_csv.png'></a></td>\n";
    echo "<td width='1' class='datagrid_bar_icon'><a title='Kustomisasi kolom' href='javascript:customizeColumn(true);'><img border='0' src='images/icon_columns.png'></a></td>\n";
    echo "</tr></table>\n";
    echo "</form>\n";

    echo "<form name='frmCUSTOMIZE' action='action/cgx_customize.php' method='post'>\n";
    echo "<input type='hidden' name='back' value='" . urlencode($_SERVER['REQUEST_URI']) . "'>\n";
    echo "<input type='hidden' name='dg_name' value='master.code_cust'>\n";
    echo "<input type='hidden' name='col[id]' value='on'>\n";
    echo "<table id='columns' class='datagrid_bar' style='display: none;'><tr>\n";
    echo "<td width='99%' valign='top'>\n";
    
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_id' name='col[id]' type='checkbox'></td><td width='99%'><label for='col_id'>ID</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['customer_code'] == 1 ? ' checked' : '') . " id='col_customer_code' name='col[customer_code]' type='checkbox'></td><td width='99%'><label for='col_customer_code'>Customer Code</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['od'] == 1 ? ' checked' : '') . " id='col_od' name='col[od]' type='checkbox'></td><td width='99%'><label for='col_od'>OD</label></td></tr></table>\n";   
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['thickness'] == 1 ? ' checked' : '') . " id='col_thickness' name='col[thickness]' type='checkbox'></td><td width='99%'><label for='col_thickness'>Thickness</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['length'] == 1 ? ' checked' : '') . " id='col_length' name='col[length]' type='checkbox'></td><td width='99%'><label for='col_length'>Length</label></td></tr></table>\n";   
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['thickness'] == 1 ? ' checked' : '') . " id='col_spec' name='col[spec]' type='checkbox'></td><td width='99%'><label for='col_spec'>Spec</label></td></tr></table>\n";
    
    echo "</td>\n";
    echo "<td width='1' valign='top'><input type='submit' value='Simpan'></td>\n";
    echo "<td width='1' valign='top'><input type='button' value='Batalkan' onclick='customizeColumn(false);'></td>\n";
    echo "</tr></table>\n";
    echo "</form>\n";
?>
<script type="text/javascript">

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

</script>
<?php

    $cgx_sql .= " AND ( "
            . "id LIKE '%{$cgx_search}%' "
            . "OR customer_code LIKE '%{$cgx_search}%' "
            . "OR od LIKE '%{$cgx_search}%'"
            . "OR thickness LIKE '%{$cgx_search}%'"
            . "OR length LIKE '%{$cgx_search}%'"
            . "OR spec LIKE '%{$cgx_search}%'"        
            . ")";
    
    
    if ($_SESSION[$GLOBALS['APP_ID']]['master.code_cust']['error']) {
        echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['master.code_cust']['error']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['master.code_cust']['error']);
    }

    if ($_SESSION[$GLOBALS['APP_ID']]['master.code_cust']['info']) {
        echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['master.code_cust']['info']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['master.code_cust']['info']);
    }

    //print_r($cgx_sql);
    $cgx_test = $cgx_datagrid->bind($cgx_sql, $cgx_options);
    if (PEAR::isError($cgx_test)) {
        echo $cgx_test->getMessage();
    }

    if ($cgx_def_columns['id'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('ID', 'id', 'id', array('align' => 'right'), NULL, NULL));
    if ($cgx_def_columns['customer_code'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Kode Produk', 'customer_code', 'customer_code', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['od'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Width', 'od', 'od', array('align' => 'right'), NULL, NULL));
    if ($cgx_def_columns['thickness'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Thickness', 'thickness', 'thickness', array('align' => 'right'), NULL, NULL));
    if ($cgx_def_columns['length'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Length', 'length', 'length', array('align' => 'right'), NULL, NULL));
    if ($cgx_def_columns['spec'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Spec', 'spec', 'spec', array('align' => 'left'), NULL, NULL));
    
    if (has_privilege('master.product_c')) $cgx_datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, 'cgx_edit()'));
    if (has_privilege('master.product_c')) $cgx_datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, 'cgx_delete()'));

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