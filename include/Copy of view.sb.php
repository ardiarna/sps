<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 12/01/2014 23:59:15
 */


echo "<div class='title'>Kartu Stock <div style='font-size: 12px;'>As of " . date($APP_DATETIME_FORMAT) . "</div></div>";

function cgx_edit($data) {
    $href = "module.php?&m={$_REQUEST['m']}&pkey[product_code]={$data['record']['product_code']}";
    $out = "<a href='{$href}'><img src='images/icon_detail.png' border='0'></a>";
    return $out;
}

if (strlen($_REQUEST['pkey']['product_code']) > 0) {
    include_once 'view.sb.detail.php';
} else {
    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';

    if (is_array($_SESSION[$GLOBALS['APP_ID']]['view.sb']['columns'])) {
        $cgx_def_columns = $_SESSION[$GLOBALS['APP_ID']]['view.sb']['columns'];
    } else {
        $cgx_def_columns = array(
            'product_code' => 1,
            'product_name' => 1,
            'balance_date' => 1,
            'prev_quantity' => 1,
            'in_quantity' => 1,
            'out_quantity' => 1,
            'balance_quantity' => 1,
        );
        $_SESSION[$GLOBALS['APP_ID']]['view.sb']['columns'] = $cgx_def_columns;
    }

    $cgx_sql = "SELECT * FROM m_stock_balance_d JOIN m_product ON (m_stock_balance_d.m_product_id = m_product.m_product_id) WHERE m_stock_balance_d.latest = 'Y' ";
    $cgx_sql .= " AND " . org_filter_master("m_product.app_org_id");

    $cgx_datagrid = new Structures_DataGrid($cgx_max_rows);
    $cgx_options = array('dsn' => $cgx_dsn);

    $cgx_search = $_REQUEST['q'];

    echo "<form name='frmFILTER' action='{$_SERVER['SCRIPT_NAME']}'>\n";
    echo "<input type='hidden' name='m' value='{$_REQUEST['m']}'>\n";
    echo "<table id='bar' class='datagrid_bar' width='100%'><tr>\n";
    echo "<td align='right'>Product Name</td>";
    echo "<td width='1'><input type='text' style='width: 160px;' name='product_name' value=\"{$_REQUEST['product_name']}\"></td>\n";
    echo "<td width='20'></td>\n";
    echo "<td align='right'>Item Number</td>";
    echo "<td width='1'><input type='text' style='width: 160px;' name='product_code' value=\"{$_REQUEST['product_code']}\"></td>\n";
    echo "<td width='1'><input title='Cari' type='image' src='images/icon_search.png' border='0' style='padding-right: 20px;'></td>\n";
    echo "<td></td>\n";
    echo "<td width='20'></td>\n";
    echo "<td width='1' class='datagrid_bar_icon'><a title='Export semua data ke CSV' href='action/view.sb.php?mode=export-all'><img border='0' src='images/icon_csv.png'></a></td>\n";
    echo "<td width='1' class='datagrid_bar_icon'><a title='Kustomisasi kolom' href='javascript:customizeColumn(true);'><img border='0' src='images/icon_columns.png'></a></td>\n";
    echo "</tr></table>\n";
    echo "</form>\n";

    echo "<form name='frmCUSTOMIZE' action='action/cgx_customize.php' method='post'>\n";
    echo "<input type='hidden' name='back' value='" . urlencode($_SERVER['REQUEST_URI']) . "'>\n";
    echo "<input type='hidden' name='dg_name' value='view.sb'>\n";
    echo "<table id='columns' class='datagrid_bar' style='display: none;'><tr>\n";
    echo "<td width='99%' valign='top'>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['product_code'] == 1 ? ' checked' : '') . " id='col_product_code' name='col[product_code]' type='checkbox'></td><td width='99%'><label for='col_product_code'>Kode Barang</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['product_name'] == 1 ? ' checked' : '') . " id='col_product_name' name='col[product_name]' type='checkbox'></td><td width='99%'><label for='col_product_name'>Nama Barang</label></td></tr></table>\n";
    //echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['balance_date'] == 1 ? ' checked' : '') . " id='col_balance_date' name='col[balance_date]' type='checkbox'></td><td width='99%'><label for='col_balance_date'>Tanggal</label></td></tr></table>\n";
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
    //$cgx_sql .= " and ( m_product.product_code LIKE '%{$cgx_search}%' OR m_product.product_name LIKE '%{$cgx_search}%')";

    if ($_REQUEST['product_code']) $cgx_sql .= " AND product_code LIKE '%{$_REQUEST['product_code']}%'";
    if ($_REQUEST['product_name']) $cgx_sql .= " AND product_name LIKE '%{$_REQUEST['product_name']}%'";

    if ($_SESSION[$GLOBALS['APP_ID']]['view.sb']['error']) {
        echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['view.sb']['error']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['view.sb']['error']);
    }

    if ($_SESSION[$GLOBALS['APP_ID']]['view.sb']['info']) {
        echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['view.sb']['info']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['view.sb']['info']);
    }

    $cgx_test = $cgx_datagrid->bind($cgx_sql, $cgx_options);
    if (PEAR::isError($cgx_test)) {
        echo $cgx_test->getMessage();
    }

    if ($cgx_def_columns['product_code'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Kode Barang', 'product_code', 'product_code', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['product_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Nama Barang', 'product_name', 'product_name', array('align' => 'left'), NULL, NULL));
    //if ($cgx_def_columns['balance_date'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Tanggal', 'balance_date', 'balance_date', array('align' => 'center'), NULL, "cgx_format_date()"));
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
}
?>