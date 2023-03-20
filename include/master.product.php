<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 03/01/2014 20:12:20
 */


echo "<div class='title'>Master Data Produk Pipa</div>";

function cgx_format_category($data) {
    $arr = array('L' => 'Long Pipe', 'C' => 'Cutting Size');
    return $arr[$data['record'][$data['fieldName']]];
}

function cgx_edit($data) {
    $href = "module.php?&m={$_REQUEST['m']}&pkey[m_product_id]={$data['record']['m_product_id']}";
    $out = "<a href='{$href}'><img title='Edit baris ini' src='images/icon_edit.png' border='0'></a>";
    return $out;
}

function cgx_delete($data) {
    $href  = "javascript:if(confirm('Menghapus baris ini?')){window.location='action/master.product.php";
    $href .= "?backvar=module.php%253F%2526m%253Dmaster.product&mode=delete&pkey[m_product_id]={$data['record']['m_product_id']}';}";
    $out = "<a href=\"{$href}\"><img title='Hapus baris ini' src='images/icon_delete.png' border='0'></a>";
    return $out;
}

if (strlen($_REQUEST['pkey']['m_product_id']) > 0) {
    $cgx_id = $_REQUEST['id'];
    $cgx_data = cgx_fetch_table(
            "SELECT m_product.*, organization, partner_name "
            . "FROM m_product "
            . "JOIN app_org USING (app_org_id) "
            . "LEFT JOIN c_bpartner USING (c_bpartner_id) "
            . "WHERE m_product.m_product_id = '" . mysql_escape_string($_REQUEST['pkey']['m_product_id']) . "'");
    $cgx_data['app_org_id'] = empty($cgx_data['app_org_id']) ? org() : $cgx_data['app_org_id'];

    echo "<form action='action/master.product.php' method='post'>\n";
    echo "<input type='hidden' name='backvar' value='" . urlencode("module.php?&m={$_REQUEST['m']}") . "'>\n";
    echo "<input type='hidden' name='mode' value='" . ($_REQUEST['pkey']['m_product_id'] == '0' ? 'new' : 'update') . "'>\n";
    echo "<input type='hidden' name='pkey[m_product_id]' value=\"{$_REQUEST['pkey']['m_product_id']}\">\n";
    echo "<input type='hidden' name='table' value='m_product'>\n";
    echo "<input type='hidden' name='data[c_bpartner_id]' id='c_bpartner_id' value='{$cgx_data['c_bpartner_id']}'>";

    if ($_SESSION[$GLOBALS['APP_ID']]['master.product']['error']) {
        echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['master.product']['error']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['master.product']['error']);
    }

    if ($_SESSION[$GLOBALS['APP_ID']]['master.product']['info']) {
        echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['master.product']['info']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['master.product']['info']);
    }
    //form untuk INPUT DATA dan EDIT
    echo "<ul class='cgx_form'>\n";
    echo "    <input type='hidden' name='type[m_product_id]' value='N'>";
    echo "    <li class='even'>\n";
    echo "        <label class='cgx_form' for='data_m_product_id'>ID</label>\n";
    echo "        <input id='data_m_product_id' name='data[m_product_id]' type='text' value=\"{$cgx_data['m_product_id']}\" size='8' maxlength='8' style='text-align: right;' disabled />\n";
    echo "    </li>\n";
    //========================================================================================
    echo "    <input type='hidden' name='type[app_org_id]' value='N'>";
    echo "    <li class='even'>\n";
    echo "        <label class='cgx_form' for='data_app_org_id'>Organization</label>\n";
    echo cgx_form_select('data[app_org_id]', "SELECT app_org_id, organization FROM app_org", $cgx_data['app_org_id'], FALSE, "id='data_app_org_id'");
    echo "    </li>\n";
    //========================================================================================
    echo "    <li class='even'>\n";
    echo "        <label class='cgx_form' for='partner_name'>Customer</label>\n";
    echo "        <input id='partner_name' type='text' value=\"{$cgx_data['partner_name']}\" size='30' maxlength='40' style='text-align: left;' /><img onclick=\"popupReference('business-partner');\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'>\n";
    echo "    </li>\n";
    //========================================================================================
    echo "    <input type='hidden' name='type[product_code]' value='T'>";
    echo "    <li class='odd'>\n";
    echo "        <label class='cgx_form' for='data_product_code'>Kode Produk</label>\n";
    echo "        <input id='data_product_code' name='data[product_code]' type='text' value=\"{$cgx_data['product_code']}\" size='30' maxlength='40' style='text-align: left;' />\n";
    echo "    </li>\n";
    //========================================================================================
    echo "    <input type='hidden' name='type[spec]' value='T'>";
    echo "    <li class='even'>\n";
    //========================================================================================
    echo "        <label class='cgx_form' for='data_spec'>Spec</label>\n";
    echo "        <input id='data_spec' name='data[spec]' type='text' value=\"{$cgx_data['spec']}\" size='10' maxlength='10' style='text-align: left;' />\n";
    echo "    </li>\n";
    
    //========================================================================================
    echo "    <input type='hidden' name='type[od]' value='N'>";
    echo "    <li class='even'>\n";
    echo "        <label class='cgx_form' for='data_weight'>OD</label>\n";
    echo "        <input id='data_weight' name='data[od]' type='text' value=\"{$cgx_data['od']}\" size='16' maxlength='16' style='text-align: right;' />\n";
    echo "    </li>\n";
    //========================================================================================
    echo "    <input type='hidden' name='type[thickness]' value='N'>";
    echo "    <li class='odd'>\n";
    echo "        <label class='cgx_form' for='data_thickness'>Thickness</label>\n";
    echo "        <input id='data_thickness' name='data[thickness]' type='text' value=\"{$cgx_data['thickness']}\" size='16' maxlength='16' style='text-align: right;' />\n";
    echo "    </li>\n";
    //========================================================================================
    echo "    <input type='hidden' name='type[length]' value='N'>";
    echo "    <li class='odd'>\n";
    echo "        <label class='cgx_form' for='data_length'>Length</label>\n";
    echo "        <input id='data_length' name='data[length]' type='text' value=\"{$cgx_data['length']}\" size='16' maxlength='16' style='text-align: right;' />\n";
    echo "    </li>\n";
    //========================================================================================
    echo "    <input type='hidden' name='type[product_name]' value='T'>";
    echo "    <li class='even'>\n";
    echo "        <label class='cgx_form' for='data_product_name'>Nama Produk</label>\n";
    echo "        <input id='data_product_name' name='data[product_name]' type='text' value=\"{$cgx_data['product_name']}\" size='30' maxlength='100' style='text-align: left;' />\n";
    echo "    </li>\n";
    //========================================================================================
    echo "    <input type='hidden' name='type[description]' value='T'>";
    echo "    <li class='odd'>\n";
    echo "        <label class='cgx_form' for='data_description'>Description</label>\n";
    echo "        <input id='data_description' name='data[description]' type='text' value=\"{$cgx_data['description']}\" size='30' maxlength='50' style='text-align: left;' />\n";
    echo "    </li>\n";
    //========================================================================================
    echo "    <li class='even'>\n";
    echo "        <label class='cgx_form' for='data_model'>Model</label>\n";
    echo "        <input id='data_model' name='data[model]' type='text' value=\"{$cgx_data['model']}\" size='20' maxlength='20' style='text-align: left;' />\n";
    echo "    </li>\n";
    //======================================================================================    
    echo "    <li class='even'>\n";
    echo "        <label class='cgx_form' for='data_minimum_qty'>Minimum Quantity</label>\n";
    echo "        <input id='data_minimum_qty' name='data[minimum_qty]' type='text' value=\"{$cgx_data['minimum_qty']}\" size='20' maxlength='20' style='text-align: left;' />\n";
    echo "    </li>\n";
    //============================================================================================
    echo "    <input type='hidden' name='type[category]' value='T'>";
    echo "<li class='even'>\n";
    echo "        <label class='cgx_form' for='data_category'>Kategory</label>\n";
    echo cgx_form_select('data[category]', array('L' => 'Long Pipe', 'C' => 'Cutting Size'), $cgx_data['category'], FALSE, "id='data_category'");
    echo "    </li>\n";
    //=============================================================================================
    echo "    <li class='odd'>\n";
    echo "        <label class='cgx_form' for='data_active'>Status</label>\n";
    echo "          <table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'>";
    echo "      <tr><td width='1'>";        
    echo "          <input type='checkbox' name='data[purchase]' value='Y' " . ($cgx_data['purchase'] == 'Y' ? ' checked' : '') . "> Purchase\n";           
    echo "          <input type='checkbox' name='data[sale]' value='Y' ". ($cgx_data['sale'] == 'Y' ? ' checked' : '') . "> Sale\n";            
    echo "      </td></tr></table>\n";        
    echo "   </li>\n";     
         echo "<br><br>";
    //========================================================================================
    echo "    <input type='hidden' name='type[active]' value='T'>";
    echo "    <li class='odd'>\n";
    echo "        <label class='cgx_form' for='data_active'>Active</label>\n";
    echo cgx_form_select('data[active]', array('Y' => 'Ya', 'N' => 'Tidak'), $cgx_data['active'], FALSE, "id='data_active'");
    echo "    </li>\n";
    echo "    <li class='even'>\n";
    echo "        <label class='cgx_form'></label>\n";
    echo "        <input type='submit' value='Simpan'>\n";
    echo "        <input type='button' value='Kembali' onclick=\"window.location = 'module.php?&m=master.product';\">\n";
    echo "    </li>\n";
    echo "</ul>\n";
    echo "</form>\n";
 
?>
<script>
<!--
function setBusinessPartner(id, name) {
    var txt_name = document.getElementById('partner_name');
    var hid_id = document.getElementById('c_bpartner_id');
    txt_name.value = name;
    hid_id.value = id;
}
-->    
</script>
<?php

} else {
    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';

    //$_REQUEST['f1'] = empty($_REQUEST['f1']) ? org() : $_REQUEST['f1'];
    
    if (is_array($_SESSION[$GLOBALS['APP_ID']]['master.product']['columns'])) {
        $cgx_def_columns = $_SESSION[$GLOBALS['APP_ID']]['master.product']['columns'];
    } else {
        $cgx_def_columns = array(
            'm_product_id' => 1,
            'product_code' => 1,
            'spec' => 1,
            'thickness' => 1,
            'od' => 1,
            'length' => 1,
            'product_name' => 1,
            //'purchase' => 1,
            //'sale' => 1, 
            //'category' => 1,                       
        );
        $_SESSION[$GLOBALS['APP_ID']]['master.product']['columns'] = $cgx_def_columns;
    }

    $cgx_sql = "SELECT m_product.*, organization FROM m_product JOIN app_org USING (app_org_id) WHERE 1 = 1";
    $cgx_datagrid = new Structures_DataGrid($cgx_max_rows);
    $cgx_options = array('dsn' => $cgx_dsn);

    $cgx_filter1 = urldecode($_REQUEST['f1']);
    $cgx_filter2 = urldecode($_REQUEST['f2']);
    $cgx_filter3 = urldecode($_REQUEST['f3']);    
    $cgx_filter4 = urldecode($_REQUEST['f4']);
    $cgx_search = $_REQUEST['cgx_search'];

    echo "<form name='frmFILTER' action='{$_SERVER['SCRIPT_NAME']}'>\n";
    echo "<input type='hidden' name='m' value='{$_REQUEST['m']}'>\n";
    echo "<table id='bar' class='datagrid_bar' width='100%'><tr>\n";
    
    echo "<td align='right'>Organization</td>\n"; 
    echo "<td>" . cgx_filter('f1', "SELECT app_org_id, organization FROM app_org", $cgx_filter1, TRUE) . "</td>\n";
    
    echo "<td align='right'>Status</td>\n";
    echo "<td>" . cgx_filter('f2', array('P' => 'Purchase', 'S' => 'Sale'), $cgx_filter2, TRUE) . "</td>\n";
    
    echo "<td align='right'>Active</td>\n";
    echo "<td>" . cgx_filter('f3', array('Y' => 'Ya', 'N' => 'Tidak'), $cgx_filter3, TRUE) . "</td>\n";
    
    echo "<td align='right'>Kategori</td>\n";
    echo "<td>" . cgx_filter('f4', array('L' => 'Long Pipe', 'C' => 'Cutting Size'), $cgx_filter4, TRUE) . "</td>\n";
    
    echo "<td align='right'><input type='text' size='20' name='cgx_search' value=\"{$cgx_search}\"></td>\n";
    echo "<td width='1'><input title='Cari' type='image' src='images/icon_search.png' border='0' style='padding-right: 20px;'></td>\n";
    echo "<td></td>\n";
    echo "<td width='20'></td>\n";
    if (has_privilege('master.product')) {
        echo "<td width='1' class='datagrid_bar_icon'><a title='Tambah data' href='module.php?&m={$_REQUEST['m']}&pkey[m_product_id]=0'><img border='0' src='images/icon_add.png'></a></td>\n";
    } else {
        echo "<td width='1' class='datagrid_bar_icon'><img border='0' src='images/icon_add_dis.png'></td>\n";
    }
    echo "<td width='1' class='datagrid_bar_icon'><a title='Export semua data ke CSV' href='action/master.product.php?mode=export-all'><img border='0' src='images/icon_csv.png'></a></td>\n";
    echo "<td width='1' class='datagrid_bar_icon'><a title='Kustomisasi kolom' href='javascript:customizeColumn(true);'><img border='0' src='images/icon_columns.png'></a></td>\n";
    echo "</tr></table>\n";
    echo "</form>\n";

    echo "<form name='frmCUSTOMIZE' action='action/cgx_customize.php' method='post'>\n";
    echo "<input type='hidden' name='back' value='" . urlencode($_SERVER['REQUEST_URI']) . "'>\n";
    echo "<input type='hidden' name='dg_name' value='master.product'>\n";
    echo "<input type='hidden' name='col[m_product_id]' value='on'>\n";
    echo "<input type='hidden' name='col[product_code]' value='on'>\n";
    echo "<input type='hidden' name='col[product_name]' value='on'>\n";
    echo "<table id='columns' class='datagrid_bar' style='display: none;'><tr>\n";
    echo "<td width='99%' valign='top'>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_m_product_id' name='col[m_product_id]' type='checkbox'></td><td width='99%'><label for='col_m_product_id'>ID</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['organization'] == 1 ? ' checked' : '') . " id='col_organization' name='col[organization]' type='checkbox'></td><td width='99%'><label for='col_organization'>Organization</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_product_code' name='col[product_code]' type='checkbox'></td><td width='99%'><label for='col_product_code'>Kode Produk</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['spec'] == 1 ? ' checked' : '') . " id='col_spec' name='col[spec]' type='checkbox'></td><td width='99%'><label for='col_spec'>Spec</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['od'] == 1 ? ' checked' : '') . " id='col_weight' name='col[od]' type='checkbox'></td><td width='99%'><label for='col_weight'>OD</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['thickness'] == 1 ? ' checked' : '') . " id='col_thickness' name='col[thickness]' type='checkbox'></td><td width='99%'><label for='col_thickness'>Thickness</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['length'] == 1 ? ' checked' : '') . " id='col_length' name='col[length]' type='checkbox'></td><td width='99%'><label for='col_length'>Length</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_product_name' name='col[product_name]' type='checkbox'></td><td width='99%'><label for='col_product_name'>Nama Produk</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['description'] == 1 ? ' checked' : '') . " id='col_description' name='col[description]' type='checkbox'></td><td width='99%'><label for='col_description'>Description</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['minimum_qty'] == 1 ? ' checked' : '') . " id='col_minimum_qty' name='col[minimum_qty]' type='checkbox'></td><td width='99%'><label for='col_minimum_qty'>Minimum Quantity</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['purchase'] == 1 ? ' checked' : '') . " id='col_purchase' name='col[purchase]' type='checkbox'></td><td width='99%'><label for='col_purchase'>Purchase</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['sale'] == 1 ? ' checked' : '') . " id='col_sale' name='col[sale]' type='checkbox'></td><td width='99%'><label for='col_sale'>Sale</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['category'] == 1 ? ' checked' : '') . " id='col_category' name='col[category]' type='checkbox'></td><td width='99%'><label for='col_category'>Kategori</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['active'] == 1 ? ' checked' : '') . " id='col_active' name='col[active]' type='checkbox'></td><td width='99%'><label for='col_active'>Active</label></td></tr></table>\n";
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

    if (strlen($cgx_filter1) > 0) $cgx_sql .= " AND m_product.app_org_id = '" . mysql_escape_string($cgx_filter1) . "'";
    if (strlen($cgx_filter2) > 0){
        switch ($cgx_filter2) {
            case 'P':
                $cgx_sql .= " AND m_product.purchase='Y'";            
                break;
            case 'S':
                $cgx_sql .= " AND m_product.sale ='Y'";            
                break;
        }
    }
    
    if (strlen($cgx_filter3) > 0) $cgx_sql .= " AND m_product.active = '" . mysql_escape_string($cgx_filter3) . "'";
    
    if (strlen($cgx_filter4) > 0){
        $cgx_sql .= " AND m_product.category = '" . mysql_escape_string($cgx_filter4) . "'";   
    } 
    else{
        $cgx_sql .= " AND (category = 'L' OR category = 'C')";
    }
    
    $cgx_sql .= " and ( m_product.m_product_id LIKE '%{$cgx_search}%' OR m_product.product_code LIKE '%{$cgx_search}%' OR m_product.product_name LIKE '%{$cgx_search}%' OR m_product.description LIKE '%{$cgx_search}%')";
    
    //print_r($cgx_sql);
    //exit;
    
    if ($_SESSION[$GLOBALS['APP_ID']]['master.product']['error']) {
        echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['master.product']['error']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['master.product']['error']);
    }

    if ($_SESSION[$GLOBALS['APP_ID']]['master.product']['info']) {
        echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['master.product']['info']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['master.product']['info']);
    }


    $cgx_test = $cgx_datagrid->bind($cgx_sql, $cgx_options);
    if (PEAR::isError($cgx_test)) {
        echo $cgx_test->getMessage();
    }

    if ($cgx_def_columns['m_product_id'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('ID', 'm_product_id', 'm_product_id', array('align' => 'right'), NULL, NULL));
    if ($cgx_def_columns['organization'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Organization', 'organization', 'organization', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['product_code'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Kode Produk', 'product_code', 'product_code', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['spec'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Spec', 'spec', 'spec', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['od'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('OD', 'od', 'od', array('align' => 'right'), NULL, NULL));
    if ($cgx_def_columns['thickness'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Thickness', 'thickness', 'thickness', array('align' => 'right'), NULL, NULL));
    if ($cgx_def_columns['length'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Length', 'length', 'length', array('align' => 'right'), NULL, NULL));
    if ($cgx_def_columns['product_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Nama Produk', 'product_name', 'product_name', array('align' => 'left'), NULL, NULL)); 
    if ($cgx_def_columns['description'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Description', 'description', 'description', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['minimum_qty'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Minimum Qty', 'minimum_qty', 'minimum_qty', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['purchase'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Purchase', 'purchase', 'purchase', array('align' => 'left'), NULL, "cgx_format_yesno()"));
    if ($cgx_def_columns['sale'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Sale', 'sale', 'sale', array('align' => 'left'), NULL, "cgx_format_yesno()"));
    if ($cgx_def_columns['category'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Kategori', 'category', 'category', array('align' => 'center'), NULL, "cgx_format_category()"));
    if ($cgx_def_columns['active'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Active', 'active', 'active', array('align' => 'center'), NULL, "cgx_format_yesno()"));
    if (has_privilege('master.product')) $cgx_datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, 'cgx_edit()'));
    if (has_privilege('master.product')) $cgx_datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, 'cgx_delete()'));

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