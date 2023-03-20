<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 12/01/2014 23:59:15
 */


echo "<div class='title'>Stock Balance <div style='font-size: 12px;'>As of " . date($APP_DATETIME_FORMAT) . "</div></div>";

function cgx_edit($data) {
    $href = "module.php?&m={$_REQUEST['m']}&pkey[m_product_id]={$data['record']['m_product_id']}";
    $out = "<a href='{$href}'><img src='images/icon_detail.png' border='0'></a>";
    return $out;
}

if (strlen($_REQUEST['pkey']['m_product_id']) > 0) {
    include_once 'view.stobal.detail.php';
} else {
    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';

    if (is_array($_SESSION[$GLOBALS['APP_ID']]['view.stobal']['columns'])) {
        $cgx_def_columns = $_SESSION[$GLOBALS['APP_ID']]['view.stobal']['columns'];
    } else {
        $cgx_def_columns = array(
            'product_code' => 1,
            'spec' => 1,
            'od' => 1,
            'thickness' => 1,
            'length' => 1,
            //'product_name' => 1,
            'm_inout_date' => 1,
            'prev_quantity' => 1,
            'in_quantity' => 1,
            'out_quantity' => 1,
            'balance_quantity' => 1,
        );
        $_SESSION[$GLOBALS['APP_ID']]['view.stobal']['columns'] = $cgx_def_columns;
    }



    $cgx_sql = "SELECT mso1.*, mp.product_code, mp.product_name, mp.spec, mp.od, mp.Thickness, mp.Length, cb.partner_code, cb.partner_name, 
        ((((mp.od - mp.thickness) * mp.thickness * 0.02466 * mp.length) / 1000) * balance_quantity) as weight, CONCAT(mp.od, '%', mp.Thickness, '%', mp.Length) as decrip
        FROM m_stock_onhand mso1 
            INNER JOIN (
                SELECT m_product_id, MAX(m_inout_date) m_inout_date
                FROM m_stock_onhand WHERE app_org_id = '". org() ."' GROUP BY m_product_id
            ) mso2
        ON(mso1.m_product_id = mso2.m_product_id AND mso1.m_inout_date = mso2.m_inout_date)
        JOIN m_product mp ON(mso1.m_product_id = mp.m_product_id)
        LEFT JOIN c_bpartner cb ON(mp.c_bpartner_id=cb.c_bpartner_id) 
        WHERE mso1.app_org_id='". org() ."'";
    $cgx_datagrid = new Structures_DataGrid($cgx_max_rows);
    $cgx_options = array('dsn' => $cgx_dsn);

    $cgx_search = $_REQUEST['q'];
    $spec = urldecode($_REQUEST['spec']);
    $od = urldecode($_REQUEST['od']);
    $thickness = urldecode($_REQUEST['thickness']);
    $length = urldecode($_REQUEST['length']);

    echo "<form name='frmFILTER' action='{$_SERVER['SCRIPT_NAME']}'>\n";
    echo "<input type='hidden' name='m' value='{$_REQUEST['m']}'>\n";
    echo "<table id='bar' class='datagrid_bar' width='100%'><tr>\n";
    echo "<td align='right'>Spec</td>\n";
    echo "<td align='left'>" . cgx_filter('spec', "SELECT DISTINCT spec, spec FROM m_stock_onhand JOIN m_product ON(m_stock_onhand.m_product_id=m_product.m_product_id) WHERE m_stock_onhand.app_org_id = " . org() . " ORDER BY spec", $spec, TRUE) . "</td>\n";
    echo "<td align='right'>OD</td>\n";
    echo "<td align='left'>" . cgx_filter('od', "SELECT DISTINCT od, od FROM m_stock_onhand JOIN m_product ON(m_stock_onhand.m_product_id=m_product.m_product_id) WHERE m_stock_onhand.app_org_id = " . org() . " ORDER BY od", $od, TRUE) . "</td>\n";
    
        echo "<td align='right'>Thickness</td>\n";
    echo "<td align='left'>" . cgx_filter('thickness', "SELECT DISTINCT thickness, thickness FROM m_stock_onhand JOIN m_product ON(m_stock_onhand.m_product_id=m_product.m_product_id) WHERE m_stock_onhand.app_org_id = " . org() . " ORDER BY thickness", $thickness, TRUE) . "</td>\n";
    echo "<td align='right'>Length</td>\n";
    echo "<td align='left'>" . cgx_filter('length', "SELECT DISTINCT length, length FROM m_stock_onhand JOIN m_product ON(m_stock_onhand.m_product_id=m_product.m_product_id) WHERE m_stock_onhand.app_org_id = " . org() . " ORDER BY length", $length, TRUE) . "</td>\n";
    
    echo "<td align='right'><input type='text' size='20' name='q' value=\"{$cgx_search}\"></td>\n";
    echo "<td width='1'><input title='Cari' type='image' src='images/icon_search.png' border='0' style='padding-right: 20px;'></td>\n";
    echo "<td></td>\n";
    echo "<td width='20'></td>\n";
    echo "<td width='1' class='datagrid_bar_icon'><a title='Export semua data ke CSV' href='action/view.stobal.php?mode=export-all'><img border='0' src='images/icon_csv.png'></a></td>\n";
    echo "<td width='1' class='datagrid_bar_icon'><a title='Kustomisasi kolom' href='javascript:customizeColumn(true);'><img border='0' src='images/icon_columns.png'></a></td>\n";
    echo "</tr><tr>";

    echo "</tr></table>\n";
    echo "</form>\n";

    echo "<form name='frmCUSTOMIZE' action='action/cgx_customize.php' method='post'>\n";
    echo "<input type='hidden' name='back' value='" . urlencode($_SERVER['REQUEST_URI']) . "'>\n";
    echo "<input type='hidden' name='dg_name' value='view.stobal'>\n";
    echo "<table id='columns' class='datagrid_bar' style='display: none;'><tr>\n";
    echo "<td width='99%' valign='top'>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['m_product_id'] == 1 ? ' checked' : '') . " id='col_m_product_id' name='col[m_product_id]' type='checkbox'></td><td width='99%'><label for='col_m_product_id'>ID Barang</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['product_code'] == 1 ? ' checked' : '') . " id='col_product_code' name='col[product_code]' type='checkbox'></td><td width='99%'><label for='col_product_code'>Item Number</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['product_name'] == 1 ? ' checked' : '') . " id='col_product_name' name='col[product_name]' type='checkbox'></td><td width='99%'><label for='col_product_name'>Nama Barang</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['partner_code'] == 1 ? ' checked' : '') . " id='col_partner_code' name='col[partner_code]' type='checkbox'></td><td width='99%'><label for='col_partner_code'>Kode Customer</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['partner_name'] == 1 ? ' checked' : '') . " id='col_partner_name' name='col[partner_name]' type='checkbox'></td><td width='99%'><label for='col_partner_name'>Nama Customer</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['spec'] == 1 ? ' checked' : '') . " id='col_spec' name='col[spec]' type='checkbox'></td><td width='99%'><label for='col_spec'>Spec</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['od'] == 1 ? ' checked' : '') . " id='col_od' name='col[od]' type='checkbox'></td><td width='99%'><label for='col_od'>OD</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['thickness'] == 1 ? ' checked' : '') . " id='col_thickness' name='col[thickness]' type='checkbox'></td><td width='99%'><label for='col_thickness'>Tebal</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['length'] == 1 ? ' checked' : '') . " id='col_length' name='col[length]' type='checkbox'></td><td width='99%'><label for='col_length'>Panjang</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['m_inout_date'] == 1 ? ' checked' : '') . " id='col_m_inout_date' name='col[m_inout_date]' type='checkbox'></td><td width='99%'><label for='col_m_inout_date'>Tanggal</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['prev_quantity'] == 1 ? ' checked' : '') . " id='col_prev_quantity' name='col[prev_quantity]' type='checkbox'></td><td width='99%'><label for='col_prev_quantity'>Stok Awal</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['in_quantity'] == 1 ? ' checked' : '') . " id='col_in_quantity' name='col[in_quantity]' type='checkbox'></td><td width='99%'><label for='col_in_quantity'>Masuk</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['out_quantity'] == 1 ? ' checked' : '') . " id='col_out_quantity' name='col[out_quantity]' type='checkbox'></td><td width='99%'><label for='col_out_quantity'>Keluar</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['balance_quantity'] == 1 ? ' checked' : '') . " id='col_balance_quantity' name='col[balance_quantity]' type='checkbox'></td><td width='99%'><label for='col_balance_quantity'>Balance (Pcs)</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['weight'] == 1 ? ' checked' : '') . " id='col_weight' name='col[weight]' type='checkbox'></td><td width='99%'><label for='col_weight'>Weight (Kg)</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['decrip'] == 1 ? ' checked' : '') . " id='col_decrip' name='col[decrip]' type='checkbox'></td><td width='99%'><label for='col_decrip'>Decrip</label></td></tr></table>\n";
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

    $(function(){
        $(".hasDatePicker").datepicker({
            showOn: "button",
            buttonImage: "images/calendar.png",
            buttonImageOnly: true,
            changeMonth: true,
            changeYear: true,
            yearRange: '1950:2050',
            dateFormat: 'dd-M-yy'
        });
    });
    //-->
    </script>
    <?php
    
    $cgx_sql .= " and ( mp.m_product_id LIKE '%{$cgx_search}%' OR mp.product_code LIKE '%{$cgx_search}%' OR mp.product_name LIKE '%{$cgx_search}%' OR mp.description LIKE '%{$cgx_search}%' OR cb.partner_code LIKE '%{$cgx_search}%' OR cb.partner_name LIKE '%{$cgx_search}%')";
    if (strlen($spec) > 0) $cgx_sql .= " AND mp.spec = '" . mysql_escape_string($spec) . "'";
    if (strlen($od) > 0) $cgx_sql .= " AND mp.od = '" . mysql_escape_string($od) . "'";
    if (strlen($thickness) > 0) $cgx_sql .= " AND mp.thickness = '" . mysql_escape_string($thickness) . "'";
    if (strlen($length) > 0) $cgx_sql .= " AND mp.length = '" . mysql_escape_string($length) . "'";

    if ($_SESSION[$GLOBALS['APP_ID']]['view.stobal']['error']) {
        echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['view.stobal']['error']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['view.stobal']['error']);
    }

    if ($_SESSION[$GLOBALS['APP_ID']]['view.stobal']['info']) {
        echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['view.stobal']['info']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['view.stobal']['info']);
    }

    $cgx_test = $cgx_datagrid->bind($cgx_sql, $cgx_options);
    if (PEAR::isError($cgx_test)) {
        echo $cgx_test->getMessage();
    }

    if ($cgx_def_columns['m_product_id'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('ID Barang', 'm_product_id', 'm_product_id', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['product_code'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Item Number', 'product_code', 'product_code', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['product_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Nama Barang', 'product_name', 'product_name', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['partner_code'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Kode Customer', 'partner_code', 'partner_code', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['partner_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Nama Customer', 'partner_name', 'partner_name', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['spec'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Spec', 'spec', 'spec', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['od'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('OD', 'od', 'od', array('align' => 'right'), NULL, NULL));
    if ($cgx_def_columns['thickness'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Tebal', 'thickness', 'thickness', array('align' => 'right'), NULL, NULL));
    if ($cgx_def_columns['length'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Panjang', 'length', 'length', array('align' => 'right'), NULL, NULL));
    if ($cgx_def_columns['m_inout_date'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Tanggal', 'm_inout_date', 'm_inout_date', array('align' => 'center'), NULL, "cgx_format_date()"));
    if ($cgx_def_columns['prev_quantity'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Stok Awal', 'prev_quantity', 'prev_quantity', array('align' => 'right', 'width' => 100), NULL, "cgx_format_3digit()"));
    if ($cgx_def_columns['in_quantity'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Masuk', 'in_quantity', 'in_quantity', array('align' => 'right', 'width' => 100), NULL, "cgx_format_3digit()"));
    if ($cgx_def_columns['out_quantity'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Keluar', 'out_quantity', 'out_quantity', array('align' => 'right', 'width' => 100), NULL, "cgx_format_3digit()"));
    if ($cgx_def_columns['balance_quantity'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Balance (Pcs)', 'balance_quantity', 'balance_quantity', array('align' => 'right', 'width' => 100), NULL, "cgx_format_3digit()"));
    if ($cgx_def_columns['weight'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Weight (Kg)', 'weight', 'weight', array('align' => 'right', 'width' => 100), NULL, "cgx_format_money()"));
    if ($cgx_def_columns['decrip'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Decrip', 'decrip', 'decrip', array('align' => 'left', 'width' => 100), NULL, NULL));
    if (has_privilege('view.stobal')) $cgx_datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, 'cgx_edit()'));

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