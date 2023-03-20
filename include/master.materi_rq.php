<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 07/01/2014 13:47:15
 */


echo "<div class='title'>Master Data Material Requirement</div>";

function cgx_edit($data) {
    $href = "module.php?&m={$_REQUEST['m']}&pkey[m_material_requirement_id]={$data['record']['m_material_requirement_id']}";
    $out = "<a href='{$href}'><img title='Edit baris ini' src='images/icon_edit.png' border='0'></a>";
    return $out;
}

function cgx_delete($data) {
    $href  = "javascript:if(confirm('Menghapus baris ini?')){window.location='action/master.materi_rq.php";
    $href .= "?backvar=module.php%253F%2526m%253Dmaster.materi_rq&mode=delete&pkey[m_material_requirement_id]={$data['record']['m_material_requirement_id']}';}";
    $out = "<a href=\"{$href}\"><img title='Hapus baris ini' src='images/icon_delete.png' border='0'></a>";
    return $out;
}

if (strlen($_REQUEST['pkey']['m_material_requirement_id']) > 0) {
    $cgx_id = $_REQUEST['id'];
    $cgx_data = cgx_fetch_table("SELECT mmr.*, mp1.product_name as name_product_fg, mp2.product_name as name_product_material FROM m_material_requirement mmr JOIN m_product mp1 ON(mmr.m_product_fg=mp1.m_product_id) JOIN m_product mp2 ON(mmr.m_product_material=mp2.m_product_id) WHERE mmr.m_material_requirement_id = '" . mysql_escape_string($_REQUEST['pkey']['m_material_requirement_id']) . "'");
    
    echo "<form action='action/master.materi_rq.php' method='post'>\n";
    echo "<input type='hidden' name='backvar' value='" . urlencode("module.php?&m={$_REQUEST['m']}") . "'>\n";
    echo "<input type='hidden' name='mode' value='" . ($_REQUEST['pkey']['m_material_requirement_id'] == '0' ? 'new' : 'update') . "'>\n";
    echo "<input type='hidden' name='pkey[m_material_requirement_id]' value=\"{$_REQUEST['pkey']['m_material_requirement_id']}\">\n";
    echo "<input type='hidden' name='table' value='m_material_requirement'>\n";
    echo "<input type='hidden' name='data[m_product_fg]' id='m_product_fg' value='{$cgx_data['m_product_fg']}'>";
    echo "<input type='hidden' name='data[m_product_material]' id='m_product_material' value='{$cgx_data['m_product_material']}'>";

    if ($_SESSION[$GLOBALS['APP_ID']]['master.materi_rq']['error']) {
        echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['master.materi_rq']['error']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['master.materi_rq']['error']);
    }

    if ($_SESSION[$GLOBALS['APP_ID']]['master.materi_rq']['info']) {
        echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['master.materi_rq']['info']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['master.materi_rq']['info']);
    }

    $cgx_data['calendar_date'] = empty($cgx_data['calendar_date']) ? date($APP_DATE_FORMAT) : date($APP_DATE_FORMAT, strtotime($cgx_data['calendar_date']));

    echo "<ul class='cgx_form'>\n";
    
    echo "    <input type='hidden' name='type[name_product_fg]' value='T'>";
    echo "    <li class='even'>\n";
    echo "        <label class='cgx_form' for='data_name_product_fg'>Product Finish Good</label>\n";
    echo "        <input id='name_product_fg' type='text' value=\"{$cgx_data['name_product_fg']}\" size='50' maxlength='100' style='text-align: left;' /><img onclick=\"popupReference('product');\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'>\n";
    echo "    </li>\n";
    echo "    <input type='hidden' name='type[name_product_material]' value='T'>";
    echo "    <li class='odd'>\n";
    echo "        <label class='cgx_form' for='data_name_product_material'>Product Material</label>\n";
    echo "        <input id='name_product_material' type='text' value=\"{$cgx_data['name_product_material']}\" size='50' maxlength='100' style='text-align: left;' /><img onclick=\"popupReference('product-lp');\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'>\n";
    echo "    </li>\n";
    echo "    <input type='hidden' name='type[divqty]' value='T'>";
    echo "    <li class='even'>\n";
    echo "        <label class='cgx_form' for='data_divqty'>Div Quntity</label>\n";
    echo "        <input id='data_divqty' name='data[divqty]' type='text' value=\"{$cgx_data['divqty']}\" size='10' maxlength='10' style='text-align: right;' />\n";
    echo "    </li>\n";
    echo "    <input type='hidden' name='type[multipleqty]' value='T'>";
    echo "    <li class='odd'>\n";
    echo "        <label class='cgx_form' for='data_multipleqty'>Multiple Quntity (Pcs)</label>\n";
    echo "        <input id='data_multipleqty' name='data[multipleqty]' type='text' value=\"{$cgx_data['multipleqty']}\" size='10' maxlength='10' style='text-align: right;' />\n";
    echo "    </li>\n";
    echo "    <input type='hidden' name='type[bundle]' value='T'>";
    echo "    <li class='odd'>\n";
    echo "        <label class='cgx_form' for='data_bundle'>Isi per Bundle (Pcs)</label>\n";
    echo "        <input id='data_bundle' name='data[bundle]' type='text' value=\"{$cgx_data['bundle']}\" size='10' maxlength='10' style='text-align: right;' />\n";
    echo "    </li>\n";
    echo "    <li class='even'>\n";
    echo "        <label class='cgx_form'></label>\n";
    echo "        <input type='submit' value='Simpan'>\n";
    echo "        <input type='button' value='Kembali' onclick=\"window.location = 'module.php?&m=master.materi_rq';\">\n";
    echo "    </li>\n";
    echo "</ul>\n";
    echo "</form>\n";

?>

<script>
<!--
function setProduct(id, code, name, desc) {
    var txt_name = document.getElementById('name_product_fg');
    var hid_id = document.getElementById('m_product_fg');
    txt_name.value = name;
    hid_id.value = id;
}

function setProduct_LP(id, code, name, desc) {
    var txt_name = document.getElementById('name_product_material');
    var hid_id = document.getElementById('m_product_material');
    txt_name.value = name;
    hid_id.value = id;
}
-->    
</script>

<?php

} else {
    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';

    $_REQUEST['f1'] = empty($_REQUEST['f1']) ? org() : $_REQUEST['f1'];
    
    if (is_array($_SESSION[$GLOBALS['APP_ID']]['master.materi_rq']['columns'])) {
        $cgx_def_columns = $_SESSION[$GLOBALS['APP_ID']]['master.materi_rq']['columns'];
    } else {
        $cgx_def_columns = array(
            'product_code_fg' => 1,
            'name_product_fg' => 1,
            'product_code_mat' => 1,            
            'name_product_material' => 1,
            'multipleqty' => 1,
            'bundle' => 1,
        );
        $_SESSION[$GLOBALS['APP_ID']]['master.materi_rq']['columns'] = $cgx_def_columns;
    }

    $cgx_sql = "SELECT mmr.*, mp1.product_code as product_code_fg, mp2.product_code as product_code_mat, mp1.product_name as name_product_fg, mp2.product_name as name_product_material FROM m_material_requirement mmr JOIN m_product mp1 ON(mmr.m_product_fg=mp1.m_product_id) JOIN m_product mp2 ON(mmr.m_product_material=mp2.m_product_id) WHERE 1 = 1";
    $cgx_datagrid = new Structures_DataGrid($cgx_max_rows);
    $cgx_options = array('dsn' => $cgx_dsn);

    $cgx_filter2 = urldecode($_REQUEST['f2']);
    $cgx_search = $_REQUEST['q'];

    echo "<form name='frmFILTER' action='{$_SERVER['SCRIPT_NAME']}'>\n";
    echo "<input type='hidden' name='m' value='{$_REQUEST['m']}'>\n";
    echo "<table id='bar' class='datagrid_bar' width='100%'><tr>\n";
    echo "<td align='right'><input type='text' size='20' name='q' value=\"{$cgx_search}\"></td>\n";
    echo "<td width='1'><input title='Cari' type='image' src='images/icon_search.png' border='0' style='padding-right: 20px;'></td>\n";
    echo "<td></td>\n";
    echo "<td width='20'></td>\n";
    if (has_privilege('master.wh')) {
        echo "<td width='1' class='datagrid_bar_icon'><a title='Tambah data' href='module.php?&m={$_REQUEST['m']}&pkey[m_material_requirement_id]=0'><img border='0' src='images/icon_add.png'></a></td>\n";
    } else {
        echo "<td width='1' class='datagrid_bar_icon'><img border='0' src='images/icon_add_dis.png'></td>\n";
    }
    echo "<td width='1' class='datagrid_bar_icon'><a title='Export semua data ke CSV' href='action/master.materi_rq.php?mode=export-all'><img border='0' src='images/icon_csv.png'></a></td>\n";
    echo "<td width='1' class='datagrid_bar_icon'><a title='Kustomisasi kolom' href='javascript:customizeColumn(true);'><img border='0' src='images/icon_columns.png'></a></td>\n";
    echo "</tr></table>\n";
    echo "</form>\n";

    echo "<form name='frmCUSTOMIZE' action='action/cgx_customize.php' method='post'>\n";
    echo "<input type='hidden' name='back' value='" . urlencode($_SERVER['REQUEST_URI']) . "'>\n";
    echo "<input type='hidden' name='dg_name' value='master.materi_rq'>\n";
    echo "<table id='columns' class='datagrid_bar' style='display: none;'><tr>\n";
    echo "<td width='99%' valign='top'>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['m_material_requirement_id'] == 1 ? ' checked' : '') . " id='col_m_material_requirement_id' name='col[m_material_requirement_id]' type='checkbox'></td><td width='99%'><label for='col_m_material_requirement_id'>ID Material Req</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['m_product_fg'] == 1 ? ' checked' : '') . " id='col_m_product_fg' name='col[m_product_fg]' type='checkbox'></td><td width='99%'><label for='col_m_product_fg'>ID Product FG</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['product_code_fg'] == 1 ? ' checked' : '') . " id='col_product_code_fg' name='col[product_code_fg]' type='checkbox'></td><td width='99%'><label for='col_product_code_fg'>Item Number FG</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['name_product_fg'] == 1 ? ' checked' : '') . " id='col_name_product_fg' name='col[name_product_fg]' type='checkbox'></td><td width='99%'><label for='col_name_product_fg'>Name Product FG</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['m_product_material'] == 1 ? ' checked' : '') . " id='col_m_product_material' name='col[m_product_material]' type='checkbox'></td><td width='99%'><label for='col_m_product_material'>ID Product Material</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['product_code_mat'] == 1 ? ' checked' : '') . " id='col_product_code_mat' name='col[product_code_mat]' type='checkbox'></td><td width='99%'><label for='col_product_code_mat'>Item Number Material</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['name_product_material'] == 1 ? ' checked' : '') . " id='col_name_product_material' name='col[name_product_material]' type='checkbox'></td><td width='99%'><label for='col_name_product_material'>Name Product Material</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['divqty'] == 1 ? ' checked' : '') . " id='col_divqty' name='col[divqty]' type='checkbox'></td><td width='99%'><label for='col_divqty'>Div Qty</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['multipleqty'] == 1 ? ' checked' : '') . " id='col_multipleqty' name='col[multipleqty]' type='checkbox'></td><td width='99%'><label for='col_multipleqty'>Multiple Qty</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['bundle'] == 1 ? ' checked' : '') . " id='col_bundle' name='col[bundle]' type='checkbox'></td><td width='99%'><label for='col_bundle'>Isi per Bundle</label></td></tr></table>\n";
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

    $cgx_sql .= " and ( mp1.product_code LIKE '%{$cgx_search}%' OR mp1.product_name LIKE '%{$cgx_search}%' OR mp2.product_code LIKE '%{$cgx_search}%' OR mp2.product_name LIKE '%{$cgx_search}%' )";
    if ($_SESSION[$GLOBALS['APP_ID']]['master.materi_rq']['error']) {
        echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['master.materi_rq']['error']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['master.materi_rq']['error']);
    }

    if ($_SESSION[$GLOBALS['APP_ID']]['master.materi_rq']['info']) {
        echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['master.materi_rq']['info']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['master.materi_rq']['info']);
    }


    $cgx_test = $cgx_datagrid->bind($cgx_sql, $cgx_options);
    if (PEAR::isError($cgx_test)) {
        echo $cgx_test->getMessage();
    }

    if ($cgx_def_columns['m_material_requirement_id'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('ID Material Req', 'm_material_requirement_id', 'm_material_requirement_id', array('align' => 'right'), NULL, NULL));
    if ($cgx_def_columns['m_product_fg'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('ID Product Finish Good', 'm_product_fg', 'm_product_fg', array('align' => 'right'), NULL, NULL));
    if ($cgx_def_columns['product_code_fg'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Item Number FG', 'product_code_fg', 'product_code_fg', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['name_product_fg'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Name Product Finish Good', 'name_product_fg', 'name_product_fg', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['m_product_material'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('ID Product Material', 'm_product_material', 'm_product_material', array('align' => 'right'), NULL, NULL));
    if ($cgx_def_columns['product_code_mat'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Item Number material', 'product_code_mat', 'product_code_mat', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['name_product_material'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Name Product Material', 'name_product_material', 'name_product_material', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['divqty'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Div Qty', 'divqty', 'divqty', array('align' => 'right', 'width' => 100), NULL, NULL));
    if ($cgx_def_columns['multipleqty'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Multiple Qty', 'multipleqty', 'multipleqty', array('align' => 'right', 'width' => 100), NULL, "cgx_format_3digit()"));
    if ($cgx_def_columns['bundle'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Isi per Bundle', 'bundle', 'bundle', array('align' => 'right', 'width' => 100), NULL, "cgx_format_3digit()"));
    if (has_privilege('master.materi_rq')) $cgx_datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, 'cgx_edit()'));
    // if (has_privilege('master.materi_rq')) $cgx_datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, 'cgx_delete()'));

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