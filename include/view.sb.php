<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 12/01/2014 23:59:15
 */


echo "<div class='title'>Kartu Stock <div style='font-size: 12px;'>As of " . date($APP_DATETIME_FORMAT) . "</div></div>";

function cgx_edit($data) {
    $href = "module.php?&m={$_REQUEST['m']}&pkey[m_product_id]={$data['record']['m_product_id']}";
    $out = "<a href='{$href}'><img src='images/icon_detail.png' border='0'></a>";
    return $out;
}

if (strlen($_REQUEST['pkey']['m_product_id']) > 0) {
    include_once 'view.sb.detail.php';
} else {
    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';

    if (is_array($_SESSION[$GLOBALS['APP_ID']]['view.sb']['columns'])) {
        $cgx_def_columns = $_SESSION[$GLOBALS['APP_ID']]['view.sb']['columns'];
    } else {
        $cgx_def_columns = array(
            //'product_code' => 1,
            'product_name' => 1,
            'balance_date' => 1,
            'prev_quantity' => 1,
            'in_quantity' => 1,
            'out_quantity' => 1,
            'balance_quantity' => 1,
        );
        $_SESSION[$GLOBALS['APP_ID']]['view.sb']['columns'] = $cgx_def_columns;
    }

    $cgx_sql = "
        SELECT 
        A.*
        ,B.product_code
        ,B.product_name
        ,B.spec
        ,B.od
        ,B.thickness
        ,B.length
        ,B.m_product_id
        ,B.c_bpartner_id 
        ,C.partner_name

        FROM 
        m_stock_balance_d_2 AS A JOIN m_product AS B ON (A.m_product_id = B.m_product_id) 
                            LEFT JOIN c_bpartner AS C ON (B.c_bpartner_id = C.c_bpartner_id)


        WHERE 1=1 AND A.latest = 'Y' AND A.app_org_id = " . org();
    
    $cgx_sum_berat = " 
        SELECT
        SUM(A.balance_quantity) as balance 

        FROM 
        m_stock_balance_d_2 AS A JOIN m_product AS B ON (A.m_product_id = B.m_product_id)
                            LEFT JOIN c_bpartner AS C ON (B.c_bpartner_id = C.c_bpartner_id)
                            
        WHERE 1=1 AND A.latest = 'Y' AND A.app_org_id = " . org();
    
    $cgx_datagrid = new Structures_DataGrid($cgx_max_rows);
    $cgx_options = array('dsn' => $cgx_dsn);
    
    $product_name = $_REQUEST['product_name'];
    $product_code = $_REQUEST['product_code'];
    $od = $_REQUEST['od'];
    $thickness = $_REQUEST['thickness'];
    $length = $_REQUEST['length'];
    $partner_name = $_REQUEST['partner_name'];
    
    echo "<form name='frmFILTER' action='{$_SERVER['SCRIPT_NAME']}'>\n";
    echo "<input type='hidden' name='m' value='{$_REQUEST['m']}'>\n";
    echo "<table id='bar' class='datagrid_bar' width='100%'><tr>\n";
    echo "<td align='left'>"
    . "Product Name&nbsp&nbsp&nbsp"
    . "<input type='text' style='width: 100px;' name='product_name' value=\"{$product_name}\">&nbsp&nbsp&nbsp"
    . "Item Number&nbsp&nbsp&nbsp"
    . "<input type='text' style='width: 100px;' name='product_code' value=\"{$product_code}\">&nbsp&nbsp&nbsp"
    . "Od&nbsp&nbsp&nbsp"
    . "<input type='text' style='width: 50px;' name='od' value=\"{$od}\">&nbsp&nbsp&nbsp"
    . "Tebal&nbsp&nbsp&nbsp"
    . "<input type='text' style='width: 50px;' name='thickness' value=\"{$_REQUEST['thickness']}\">&nbsp&nbsp&nbsp"
    . "Panjang&nbsp&nbsp&nbsp"
    . "<input type='text' style='width: 50px;' name='length' value=\"{$length}\">&nbsp&nbsp&nbsp</td>\n"
    . "</tr>"
    . "<td align='left'>"
    . "Customer&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp"
    . "<input type='text' style='width: 300px;' name='partner_name' value=\"{$partner_name}\"></td>\n"
    ;
    echo "<td width='1'><input title='Cari' type='image' src='images/icon_search.png' border='0' style='padding-right: 20px;'></td>\n";
    echo "<td width='1' class='datagrid_bar_icon'><a title='Export semua data ke CSV' href='action/view.sb.php?mode=export-all'><img border='0' src='images/icon_csv.png'></a></td>\n";
    echo "<td width='1' class='datagrid_bar_icon'><a title='Kustomisasi kolom' href='javascript:customizeColumn(true);'><img border='0' src='images/icon_columns.png'></a></td>\n";
    echo "</tr></table>\n";
    echo "</form>\n";

    echo "<form name='frmCUSTOMIZE' action='action/cgx_customize.php' method='post'>\n";
    echo "<input type='hidden' name='back' value='" . urlencode($_SERVER['REQUEST_URI']) . "'>\n";
    echo "<input type='hidden' name='dg_name' value='view.sb'>\n";
    echo "<table id='columns' class='datagrid_bar' style='display: none;'><tr>\n";
    echo "<td width='99%' valign='top'>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['m_product_id'] == 1 ? ' checked' : '') . " id='col_m_product_id' name='col[m_product_id]' type='checkbox'></td><td width='99%'><label for='col_m_product_id'>ID Barang</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['product_code'] == 1 ? ' checked' : '') . " id='col_product_code' name='col[product_code]' type='checkbox'></td><td width='99%'><label for='col_product_code'>Kode Barang</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['product_name'] == 1 ? ' checked' : '') . " id='col_product_name' name='col[product_name]' type='checkbox'></td><td width='99%'><label for='col_product_name'>Nama Barang</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['spec'] == 1 ? ' checked' : '') . " id='col_spec' name='col[spec]' type='checkbox'></td><td width='99%'><label for='col_spec'>Spec</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['od'] == 1 ? ' checked' : '') . " id='col_od' name='col[od]' type='checkbox'></td><td width='99%'><label for='col_od'>Od</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['thickness'] == 1 ? ' checked' : '') . " id='col_thickness' name='col[thickness]' type='checkbox'></td><td width='99%'><label for='col_thickness'>Tebal</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['length'] == 1 ? ' checked' : '') . " id='col_length' name='col[length]' type='checkbox'></td><td width='99%'><label for='col_length'>Panjang</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['partner_name'] == 1 ? ' checked' : '') . " id='col_partner_name' name='col[partner_name]' type='checkbox'></td><td width='99%'><label for='col_partner_name'>Customer</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['prev_quantity'] == 1 ? ' checked' : '') . " id='col_prev_quantity' name='col[prev_quantity]' type='checkbox'></td><td width='99%'><label for='col_prev_quantity'>Stok Awal</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['in_quantity'] == 1 ? ' checked' : '') . " id='col_in_quantity' name='col[in_quantity]' type='checkbox'></td><td width='99%'><label for='col_in_quantity'>Masuk</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['out_quantity'] == 1 ? ' checked' : '') . " id='col_out_quantity' name='col[out_quantity]' type='checkbox'></td><td width='99%'><label for='col_out_quantity'>Keluar</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['balance_quantity'] == 1 ? ' checked' : '') . " id='col_balance_quantity' name='col[balance_quantity]' type='checkbox'></td><td width='99%'><label for='col_balance_quantity'>Balance</label></td></tr></table>\n";
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
    //$cgx_sql .= " and ( m_product.product_code LIKE '%{$cgx_search}%' OR m_product.product_name LIKE '%{$cgx_search}%')";

    if ($product_code) $cgx_sql .= " AND B.product_code LIKE '%".mysql_escape_string($product_code)."%'";
    if ($product_name) $cgx_sql .= " AND B.product_name LIKE '%".mysql_escape_string($product_name)."%'";
    if ($od) $cgx_sql .= " AND B.od LIKE '%".mysql_escape_string($od)."%'";
    if ($thickness) $cgx_sql .= " AND B.thickness LIKE '%".mysql_escape_string($thickness)."%'";
    if ($length) $cgx_sql .= " AND B.length LIKE '%".mysql_escape_string($length)."%'";
    if ($partner_name) $cgx_sql .= " AND C.partner_name LIKE '%".mysql_escape_string($partner_name)."%'";
    
    if ($product_code) $cgx_sum_berat .= " AND B.product_code LIKE '%".mysql_escape_string($product_code)."%'";
    if ($product_name) $cgx_sum_berat .= " AND B.product_name LIKE '%".mysql_escape_string($product_name)."%'";
    if ($od) $cgx_sum_berat .= " AND B.od LIKE '%".mysql_escape_string($od)."%'";
    if ($thickness) $cgx_sum_berat .= " AND B.thickness LIKE '%".mysql_escape_string($thickness)."%'";
    if ($length) $cgx_sum_berat .= " AND B.length LIKE '%".mysql_escape_string($length)."%'";
    if ($partner_name) $cgx_sum_berat .= " AND C.partner_name LIKE '%".mysql_escape_string($partner_name)."%'";
    
    
    if ($_SESSION[$GLOBALS['APP_ID']]['view.sb']['error']) {
        echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['view.sb']['error']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['view.sb']['error']);
    }

    if ($_SESSION[$GLOBALS['APP_ID']]['view.sb']['info']) {
        echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['view.sb']['info']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['view.sb']['info']);
    }
    
    //print_r($cgx_sum_berat);
    $cgx_test = $cgx_datagrid->bind($cgx_sql, $cgx_options);
    if (PEAR::isError($cgx_test)) {
        echo $cgx_test->getMessage();
    }

    if ($cgx_def_columns['m_product_id'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('ID Barang', 'm_product_id', 'm_product_id', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['product_code'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Kode Barang', 'product_code', 'product_code', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['product_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Nama Barang', 'product_name', 'product_name', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['spec'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Spec', 'spec', 'spec', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['od'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('OD', 'od', 'od', array('align' => 'right', 'width' => 100), NULL, NULL));
    if ($cgx_def_columns['thickness'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Tebal', 'thickness', 'thickness', array('align' => 'right', 'width' => 100), NULL, NULL));
    if ($cgx_def_columns['length'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Panjang', 'length', 'length', array('align' => 'right', 'width' => 100), NULL, NULL)); 
    if ($cgx_def_columns['partner_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Customer', 'partner_name', 'partner_name', array('align' => 'center'), NULL, NULL));
    if ($cgx_def_columns['prev_quantity'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Stok Awal', 'prev_quantity', 'prev_quantity', array('align' => 'right', 'width' => 100), NULL, "cgx_format_3digit()"));
    if ($cgx_def_columns['in_quantity'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Masuk', 'in_quantity', 'in_quantity', array('align' => 'right', 'width' => 100), NULL, "cgx_format_3digit()"));
    if ($cgx_def_columns['out_quantity'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Keluar', 'out_quantity', 'out_quantity', array('align' => 'right', 'width' => 100), NULL, "cgx_format_3digit()"));
    if ($cgx_def_columns['balance_quantity'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Balance', 'balance_quantity', 'balance_quantity', array('align' => 'right', 'width' => 100), NULL, "cgx_format_3digit()"));
    if (has_privilege('view.stock-card')) $cgx_datagrid->addColumn(new Structures_DataGrid_Column(NULL, NULL, NULL, array('align' => 'center', 'width' => '1'), NULL, 'cgx_edit()'));

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

    $cgx_data_sum = cgx_fetch_table($cgx_sum_berat);
    
    echo "<div class='tbl-header-box' style='font-size: 12px; border: 1px solid #ccc; margin-top: 10px;'>";
    echo "  <table class=''>";
    echo "  <tr>";
    echo "      <td><b>Total Balance</b></td>";
    echo "      <td width='10' align='center'>:</td>";
    echo "      <td align='right'><b>".$cgx_data_sum["balance"]." (Pcs)</b></td>";
    echo "      <td width='100px;'>&nbsp;</td>";
    echo "  </tr>";

    
}
?>