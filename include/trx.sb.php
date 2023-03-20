<?php

/**
 * (c) Azwari Nugraha <nugraha@pt-gai.com>
 * 22/10/2013 03:04:11
 */


echo "<div class='title'>Product Stock Balance</div>";

    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';

    if (is_array($_SESSION[$GLOBALS['APP_ID']]['trx.sb']['columns'])) {
        $cgx_def_columns = $_SESSION[$GLOBALS['APP_ID']]['trx.sb']['columns'];
    } else {
        $cgx_def_columns = array(
            'm_stock_balance_id' => 1,
            'm_product_id' => 1,
            'product_code' => 1,
            'product_name' => 1,
            'balance_date' => 1,
            'prev_quantity' => 1,
            'in_quantity' => 1,
            'out_quantity' => 1,
            'balance_quantity' => 1,
            
        );
        $_SESSION[$GLOBALS['APP_ID']]['trx.sb']['columns'] = $cgx_def_columns;
    }

    $cgx_sql = "SELECT * FROM m_stock_balance_2 inner join m_product on m_stock_balance_2.m_product_id = m_product.m_product_id WHERE m_stock_balance_2.latest = 'Y' and 1 = 1";
    

    $cgx_datagrid = new Structures_DataGrid($cgx_max_rows);
    $cgx_options = array('dsn' => $cgx_dsn);

    $cgx_search = $_REQUEST['q'];

    echo "<form name='frmFILTER' action='{$_SERVER['SCRIPT_NAME']}'>\n";
    echo "<input type='hidden' name='m' value='{$_REQUEST['m']}'>\n";
    echo "<table id='bar' class='datagrid_bar' width='100%'><tr>\n";
    echo "<td align='right'><input type='text' size='20' name='q' value=\"{$cgx_search}\"></td>\n";
    echo "<td width='1'><input title='Search' type='image' src='images/icon_search.png' border='0' style='padding-right: 20px;'></td>\n";
    echo "<td></td>\n";
    echo "<td width='20'></td>\n";
        
    echo "<td width='1' class='datagrid_bar_icon'><a title='Export all (CSV)' href='action/trx.sb.php?mode=export-all'><img border='0' src='images/icon_csv.png'></a></td>\n";
    echo "<td width='1' class='datagrid_bar_icon'><a title='Customize columns' href='javascript:customizeColumn(true);'><img border='0' src='images/icon_columns.png'></a></td>\n";
    echo "</tr></table>\n";
    echo "</form>\n";

    echo "<form name='frmCUSTOMIZE' action='action/cgx_customize.php' method='post'>\n";
    echo "<input type='hidden' name='back' value='" . urlencode($_SERVER['REQUEST_URI']) . "'>\n";
    echo "<input type='hidden' name='dg_name' value='trx.sb'>\n";
    echo "<input type='hidden' name='col[m_stock_balance_id]' value='on'>\n";
    echo "<input type='hidden' name='col[m_product_id]' value='on'>\n";
    echo "<table id='columns' class='datagrid_bar' style='display: none;'><tr>\n";
    echo "<td width='99%' valign='top'>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_m_stock_balance_id' name='col[m_stock_balance_id]' type='checkbox'></td><td width='99%'><label for='col_m_stock_balance_id'>ID</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['product_code'] == 1 ? ' checked' : '') . " id='col_product_code' name='col[product_code]' type='checkbox'></td><td width='99%'><label for='col_product_code'>Item Number</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['product_name'] == 1 ? ' checked' : '') . " id='col_product_name' name='col[product_name]' type='checkbox'></td><td width='99%'><label for='col_product_name'>Product Name</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['balance_date'] == 1 ? ' checked' : '') . " id='col_balance_date' name='col[balance_date]' type='checkbox'></td><td width='99%'><label for='col_balance_date'>Last Date</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['prev_quantity'] == 1 ? ' checked' : '') . " id='col_prev_quantity' name='col[prev_quantity]' type='checkbox'></td><td width='99%'><label for='col_prev_quantity'>Prev Qty</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['in_quantity'] == 1 ? ' checked' : '') . " id='col_in_quantity' name='col[in_quantity]' type='checkbox'></td><td width='99%'><label for='col_in_quantity'>In Qty</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['out_quantity'] == 1 ? ' checked' : '') . " id='col_out_quantity' name='col[out_quantity]' type='checkbox'></td><td width='99%'><label for='col_out_quantity'>Out Qty</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['balance_quantity'] == 1 ? ' checked' : '') . " id='col_balance_quantity' name='col[balance_quantity]' type='checkbox'></td><td width='99%'><label for='col_balance_quantity'>Balance Qty</label></td></tr></table>\n";
    echo "</td>\n";
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
    $cgx_sql .= " and ( m_stock_balance_2.m_stock_balance_id LIKE '%{$cgx_search}%' OR m_stock_balance_2.m_product_id LIKE '%{$cgx_search}%' OR m_stock_balance_2.balance_quantity LIKE '%{$cgx_search}%' OR m_product.product_name LIKE '%{$cgx_search}%' OR m_product.product_code LIKE '%{$cgx_search}%')";
    if ($_SESSION[$GLOBALS['APP_ID']]['trx.sb']['error']) {
        echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['trx.sb']['error']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['trx.sb']['error']);
    }

    if ($_SESSION[$GLOBALS['APP_ID']]['trx.sb']['info']) {
        echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['trx.sb']['info']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['trx.sb']['info']);
    }


    $cgx_test = $cgx_datagrid->bind($cgx_sql, $cgx_options);
    if (PEAR::isError($cgx_test)) {
        echo $cgx_test->getMessage();
    }

    if ($cgx_def_columns['m_stock_balance_id'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('ID', 'm_stock_balance_id', 'm_stock_balance_id', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['product_code'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Item Number', 'product_code', 'product_code', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['product_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Product Name', 'product_name', 'product_name', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['balance_date'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Last Date', 'balance_date', 'balance_date', array('align' => 'center'), NULL, NULL));
    if ($cgx_def_columns['prev_quantity'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Prev Qty', 'prev_quantity', 'prev_quantity', array('align' => 'right'), NULL, NULL));
    if ($cgx_def_columns['in_quantity'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('In Qty', 'in_quantity', 'in_quantity', array('align' => 'right'), NULL, NULL));
    if ($cgx_def_columns['out_quantity'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Out Qty', 'out_quantity', 'out_quantity', array('align' => 'right'), NULL, NULL));
    if ($cgx_def_columns['balance_quantity'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Balance Qty', 'balance_quantity', 'balance_quantity', array('align' => 'right'), NULL, NULL));
    
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
    echo "<td class='datagrid_pager'>Found " . number_format($cgx_datagrid->getRecordCount()) . " record(s)</td>\n";
    echo "<td align='right' class='datagrid_pager'>\n";
    $cgx_test = $cgx_datagrid->render(DATAGRID_RENDER_PAGER);
    if (PEAR::isError($cgx_test)) {
        echo $cgx_test->getMessage();
    }
    echo "</td></tr></table>\n";

?>