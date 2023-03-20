<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 03/01/2014 20:12:20
 */


echo "<div class='title'>Penerimaan Coil Slitting</div>";


function cgx_edit($data) {
    $href = "module.php?&m={$_REQUEST['m']}&pkey[m_coil_slit_id]={$data['record']['m_coil_slit_id']}";
    $out = "<a href='{$href}'><img title='Edit baris ini' src='images/icon_edit.png' border='0'></a>";
    return $out;
}

function cgx_delete($data) {
    $href  = "javascript:if(confirm('Menghapus baris ini?')){window.location='action/trx.slit_rcp.php";
    $href .= "?backvar=module.php%253F%2526m%253Dtrx.slit_rcp&mode=delete&pkey[m_coil_slit_id]={$data['record']['m_coil_slit_id']}';}";
    $out = "<a href=\"{$href}\"><img title='Hapus baris ini' src='images/icon_delete.png' border='0'></a>";
    return $out;
}

if (strlen($_REQUEST['pkey']['m_coil_slit_id']) > 0) {
    $cgx_id = $_REQUEST['id'];
    $cgx_data = cgx_fetch_table(
            "SELECT m_coil_slit_id, m_coil_slit.m_product_id, m_coil_slit.m_coil_id, m_coil_slit.weight, m_coil_slit.quantity,
            no_coil, no_lot, product_code, spec, od, thickness, length 
            FROM m_coil_slit
            JOIN m_product ON (m_coil_slit.m_product_id=m_product.m_product_id) 
            JOIN m_coil ON(m_coil_slit.m_coil_id=m_coil.m_coil_id) 
            WHERE m_coil_slit_id = '" . mysql_escape_string($_REQUEST['pkey']['m_coil_slit_id']) . "'");

    echo "<form action='action/trx.slit_rcp.php' method='post'>\n";
    echo "<input type='hidden' name='backvar' value='" . urlencode("module.php?&m={$_REQUEST['m']}") . "'>\n";
    echo "<input type='hidden' name='mode' value='" . ($_REQUEST['pkey']['m_coil_slit_id'] == '0' ? 'new' : 'update') . "'>\n";
    echo "<input type='hidden' name='pkey[m_coil_slit_id]' value=\"{$_REQUEST['pkey']['m_coil_slit_id']}\">\n";
    echo "<input type='hidden' name='table' value='m_coil_slit'>\n";
    echo "<input type='hidden' name='m_coil_slit_id' id='m_coil_slit_id' value='{$cgx_data['m_coil_slit_id']}'>";
    echo "<input type='hidden' name='m_product_id' id='m_product_id' value='{$cgx_data['m_product_id']}'>";
    echo "<input type='hidden' name='m_coil_id' id='m_coil_id' value='{$cgx_data['m_coil_id']}'>";

    if ($_SESSION[$GLOBALS['APP_ID']]['trx.slit_rcp']['error']) {
        echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['trx.slit_rcp']['error']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['trx.slit_rcp']['error']);
    }

    if ($_SESSION[$GLOBALS['APP_ID']]['trx.slit_rcp']['info']) {
        echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['trx.slit_rcp']['info']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['trx.slit_rcp']['info']);
    }
    //form untuk INPUT DATA dan EDIT
    echo "<ul class='cgx_form'>\n";
    echo "    <li class='odd'>\n";
    echo "        <label class='cgx_form' for='product_code'>Item Number</label>\n";
    echo "        <input id='product_code' type='text' value=\"{$cgx_data['product_code']}\" size='30' maxlength='40' style='text-align: left;' disabled/><img onclick=\"popupReferenceAmbil('product_category','&p1=S');\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'>\n";
    echo "    </li>\n";
    //========================================================================================
    echo "    <li class='even'>\n";
    echo "        <label class='cgx_form' for='data_spec'>Spec</label>\n";
    echo "        <input id='spec' type='text' value=\"{$cgx_data['spec']}\" size='20' maxlength='20' style='text-align: left;' disabled/>\n";
    echo "    </li>\n";
    //========================================================================================
    echo "    <li class='odd'>\n";
    echo "        <label class='cgx_form' for='data_thickness'>Thickness</label>\n";
    echo "        <input id='thickness' type='text' value=\"{$cgx_data['thickness']}\" size='16' maxlength='16' style='text-align: right;' disabled/>\n";
    echo "    </li>\n";
    //========================================================================================
    echo "    <li class='even'>\n";
    echo "        <label class='cgx_form' for='data_weight'>Width</label>\n";
    echo "        <input id='od' type='text' value=\"{$cgx_data['od']}\" size='16' maxlength='16' style='text-align: right;' disabled/>\n";
    echo "    </li>\n";
    //========================================================================================
    echo "    <li class='even'>\n";
    echo "        <label class='cgx_form' for='data_no_coil'>No. Coil</label>\n";
    echo "        <input name='no_coil' type='text' value=\"{$cgx_data['no_coil']}\" size='30' maxlength='100' style='text-align: left;' />\n";
    echo "    </li>\n";
    //========================================================================================
    echo "    <li class='even'>\n";
    echo "        <label class='cgx_form' for='data_no_lot'>Kode Coil</label>\n";
    echo "        <input name='no_lot' type='text' value=\"{$cgx_data['no_lot']}\" size='30' maxlength='100' style='text-align: left;' />\n";
    echo "    </li>\n";
    //========================================================================================
    echo "    <li class='even'>\n";
    echo "        <label class='cgx_form' for='data_weight'>Berat per Slit</label>\n";
    echo "        <input name='weight' type='text' value=\"{$cgx_data['weight']}\" size='16' maxlength='20' style='text-align: right;' />\n";
    echo "    </li>\n";
    //============================================================================================
    echo "    <li class='even'>\n";
    echo "        <label class='cgx_form' for='data_quantity'>Jumlah Slitted</label>\n";
    echo "        <input name='quantity' type='text' value=\"{$cgx_data['quantity']}\" size='16' maxlength='20' style='text-align: right;' />\n";
    echo "    </li>\n";
    //========================================================================================
    echo "    <li class='even'>\n";
    echo "        <label class='cgx_form'></label>\n";
    echo "        <input type='submit' value='Simpan'>\n";
    echo "        <input type='button' value='Kembali' onclick=\"window.location = 'module.php?&m=trx.slit_rcp';\">\n";
    echo "    </li>\n";
    echo "</ul>\n";
    echo "</form>\n";
 
?>
<script>
<!--
function setProduct(id, code, name, desc, category, spec, thick, od) {
    var txt_product_code = document.getElementById('product_code');
    var txt_spec = document.getElementById('spec');
    var txt_thic = document.getElementById('thickness');
    var txt_od = document.getElementById('od'); 
    var hid_id = document.getElementById('m_product_id');
    txt_product_code.value = code;
    txt_spec.value = spec;
    txt_thic.value = thick;
    txt_od.value = od;
    hid_id.value = id;
}
-->    
</script>
<?php

} else {
    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';

    if (is_array($_SESSION[$GLOBALS['APP_ID']]['trx.slit_rcp']['columns'])) {
        $cgx_def_columns = $_SESSION[$GLOBALS['APP_ID']]['trx.slit_rcp']['columns'];
    } else {
        $cgx_def_columns = array(
            'm_coil_slit_id' => 1,
            'product_code' => 1,
            'spec' => 1,
            'thickness' => 1,
            'od' => 1,
            'no_coil' => 1,
            'no_lot' => 1,
            'weight' => 1,
            'quantity' => 1,         
        );
        $_SESSION[$GLOBALS['APP_ID']]['trx.slit_rcp']['columns'] = $cgx_def_columns;
    }

    $cgx_sql = "SELECT m_coil_slit_id, m_coil_slit.m_product_id, m_coil_slit.m_coil_id, m_coil_slit.weight, m_coil_slit.quantity,
            no_coil, no_lot, product_code, spec, od, thickness, length 
            FROM m_coil_slit
            JOIN m_product ON (m_coil_slit.m_product_id=m_product.m_product_id) 
            JOIN m_coil ON(m_coil_slit.m_coil_id=m_coil.m_coil_id) 
            WHERE 1 = 1";
    $cgx_datagrid = new Structures_DataGrid($cgx_max_rows);
    $cgx_options = array('dsn' => $cgx_dsn);

    $spec = urldecode($_REQUEST['spec']);
    $od = urldecode($_REQUEST['od']);
    $thickness = urldecode($_REQUEST['thickness']);
    $length = urldecode($_REQUEST['length']);
    $cgx_search = $_REQUEST['cgx_search'];

    echo "<form name='frmFILTER' action='{$_SERVER['SCRIPT_NAME']}'>\n";
    echo "<input type='hidden' name='m' value='{$_REQUEST['m']}'>\n";
    echo "<table id='bar' class='datagrid_bar' width='100%'><tr>\n";
    
    echo "<td align='right'>Spec</td>\n";
    echo "<td align='left'>" . cgx_filter('spec', "SELECT DISTINCT spec, spec FROM m_coil_slit JOIN m_product ON(m_coil_slit.m_product_id=m_product.m_product_id) ORDER BY spec", $spec, TRUE) . "</td>\n";
    echo "<td align='right'>Thickness</td>\n";
    echo "<td align='left'>" . cgx_filter('thickness', "SELECT DISTINCT thickness, thickness FROM m_coil_slit JOIN m_product ON(m_coil_slit.m_product_id=m_product.m_product_id) ORDER BY thickness", $thickness, TRUE) . "</td>\n";
    echo "<td align='right'>OD</td>\n";
    echo "<td align='left'>" . cgx_filter('length', "SELECT DISTINCT length, length FROM m_coil_slit JOIN m_product ON(m_coil_slit.m_product_id=m_product.m_product_id) ORDER BY length", $length, TRUE) . "</td>\n";
    echo "<td align='right'>Width</td>\n";
    echo "<td align='left'>" . cgx_filter('od', "SELECT DISTINCT od, od FROM m_coil_slit JOIN m_product ON(m_coil_slit.m_product_id=m_product.m_product_id) ORDER BY od", $od, TRUE) . "</td>\n";
    
    echo "<td align='right'><input type='text' size='20' name='cgx_search' value=\"{$cgx_search}\"></td>\n";
    echo "<td width='1'><input title='Cari' type='image' src='images/icon_search.png' border='0' style='padding-right: 20px;'></td>\n";
    echo "<td></td>\n";
    echo "<td width='20'></td>\n";
    if (has_privilege('trx.slit_rcp')) {
        echo "<td width='1' class='datagrid_bar_icon'><a title='Tambah data' href='module.php?&m={$_REQUEST['m']}&pkey[m_coil_slit_id]=0'><img border='0' src='images/icon_add.png'></a></td>\n";
    } else {
        echo "<td width='1' class='datagrid_bar_icon'><img border='0' src='images/icon_add_dis.png'></td>\n";
    }
    echo "<td width='1' class='datagrid_bar_icon'><a title='Kustomisasi kolom' href='javascript:customizeColumn(true);'><img border='0' src='images/icon_columns.png'></a></td>\n";
    echo "</tr></table>\n";
    echo "</form>\n";

    echo "<form name='frmCUSTOMIZE' action='action/cgx_customize.php' method='post'>\n";
    echo "<input type='hidden' name='back' value='" . urlencode($_SERVER['REQUEST_URI']) . "'>\n";
    echo "<input type='hidden' name='dg_name' value='trx.slit_rcp'>\n";
    echo "<input type='hidden' name='col[m_coil_slit_id]' value='on'>\n";
    echo "<input type='hidden' name='col[product_code]' value='on'>\n";
    echo "<input type='hidden' name='col[product_name]' value='on'>\n";
    echo "<table id='columns' class='datagrid_bar' style='display: none;'><tr>\n";
    echo "<td width='99%' valign='top'>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_m_coil_slit_id' name='col[m_coil_slit_id]' type='checkbox'></td><td width='99%'><label for='col_m_coil_slit_id'>ID</label></td></tr></table>\n";    
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_product_code' name='col[product_code]' type='checkbox'></td><td width='99%'><label for='col_product_code'>Item Number</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['spec'] == 1 ? ' checked' : '') . " id='col_spec' name='col[spec]' type='checkbox'></td><td width='99%'><label for='col_spec'>Spec</label></td></tr></table>\n";   
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['thickness'] == 1 ? ' checked' : '') . " id='col_thickness' name='col[thickness]' type='checkbox'></td><td width='99%'><label for='col_thickness'>Thickness</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['length'] == 1 ? ' checked' : '') . " id='col_length' name='col[length]' type='checkbox'></td><td width='99%'><label for='col_length'>OD</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['od'] == 1 ? ' checked' : '') . " id='col_weight' name='col[od]' type='checkbox'></td><td width='99%'><label for='col_weight'>Width</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['no_coil'] == 1 ? ' checked' : '') . " id='col_no_coil' name='col[no_coil]' type='checkbox'></td><td width='99%'><label for='col_no_coil'>No. Coil</label></td></tr></table>\n";   
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['no_lot'] == 1 ? ' checked' : '') . " id='col_no_lot' name='col[no_lot]' type='checkbox'></td><td width='99%'><label for='col_no_lot'>Kode Coil</label></td></tr></table>\n";   
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['weight'] == 1 ? ' checked' : '') . " id='col_weight' name='col[weight]' type='checkbox'></td><td width='99%'><label for='col_weight'>Berat per Slit</label></td></tr></table>\n";    
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['quantity'] == 1 ? ' checked' : '') . " id='col_quantity' name='col[quantity]' type='checkbox'></td><td width='99%'><label for='col_quantity'>Jumlah Slitted</label></td></tr></table>\n";
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

    $cgx_sql .= " AND ( product_code LIKE '%{$cgx_search}%' OR no_coil LIKE '%{$cgx_search}%' OR no_lot LIKE '%{$cgx_search}%')";
    if (strlen($spec) > 0) $cgx_sql .= " AND m_product.spec = '" . mysql_escape_string($spec) . "'";
    if (strlen($od) > 0) $cgx_sql .= " AND m_product.od = '" . mysql_escape_string($od) . "'";
    if (strlen($thickness) > 0) $cgx_sql .= " AND m_product.thickness = '" . mysql_escape_string($thickness) . "'";
    if (strlen($length) > 0) $cgx_sql .= " AND m_product.length = '" . mysql_escape_string($length) . "'";
    
    
    
    if ($_SESSION[$GLOBALS['APP_ID']]['trx.slit_rcp']['error']) {
        echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['trx.slit_rcp']['error']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['trx.slit_rcp']['error']);
    }

    if ($_SESSION[$GLOBALS['APP_ID']]['trx.slit_rcp']['info']) {
        echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['trx.slit_rcp']['info']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['trx.slit_rcp']['info']);
    }


    $cgx_test = $cgx_datagrid->bind($cgx_sql, $cgx_options);
    if (PEAR::isError($cgx_test)) {
        echo $cgx_test->getMessage();
    }

    if ($cgx_def_columns['m_coil_slit_id'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('ID', 'm_coil_slit_id', 'm_coil_slit_id', array('align' => 'right'), NULL, NULL));
    if ($cgx_def_columns['product_code'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Item Number', 'product_code', 'product_code', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['spec'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Spec', 'spec', 'spec', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['thickness'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Thickness', 'thickness', 'thickness', array('align' => 'right'), NULL, NULL));
    if ($cgx_def_columns['length'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('OD', 'length', 'length', array('align' => 'right'), NULL, NULL));
    if ($cgx_def_columns['od'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Width', 'od', 'od', array('align' => 'right'), NULL, NULL));    
    if ($cgx_def_columns['no_coil'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('No. Coil', 'no_coil', 'no_coil', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['no_lot'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Kode Coil', 'no_lot', 'no_lot', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['weight'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Berat per Slit', 'weight', 'weight', array('align' => 'right'), NULL, NULL));
    if ($cgx_def_columns['quantity'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Jumlah Slitted', 'quantity', 'quantity', array('align' => 'right'), NULL, NULL));
    if (has_privilege('trx.slit_rcp')) $cgx_datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, 'cgx_edit()'));
    //if (has_privilege('trx.slit_rcp')) $cgx_datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, 'cgx_delete()'));

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