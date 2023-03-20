<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * 2015/01/20
 */

echo "<div class='title'>Stok Coil</div>";

function cgx_format_category($data) {
    $arr = $format_category = array(
        'S' => 'Slitting',
        'L' => 'Long Pipe',
        'R' => 'Coil Material',
        'C' => 'Cutting',
    );
    return $arr[$data['record'][$data['fieldName']]];
}

function cgx_detail($data) {
    $href = "module.php?m=view.scoil.balance&id_product={$data['record']['m_product_id']}&product_code={$data['record']['product_code']}&spec={$data['record']['spec']}&thickness={$data['record']['thickness']}&od={$data['record']['od']}&m_inout_date={$data['record']['m_inout_date']}";
    $out = "<a href='{$href}'><span>Daftar Coil</span></a>";
    return $out;
}

function cgx_edit($data) {
    $href = "module.php?&m={$_REQUEST['m']}&pkey[m_product_id]={$data['record']['m_product_id']}";
    $out = "<a href='{$href}'><img src='images/icon_detail.png' border='0'></a>";
    return $out;
}

    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';

    if (is_array($_SESSION[$GLOBALS['APP_ID']]['view.sraw']['columns'])) {
        $cgx_def_columns = $_SESSION[$GLOBALS['APP_ID']]['view.sraw']['columns'];
    } else {
        $cgx_def_columns = array(
            'm_product_id' => 1,
            'product_code' => 1,
            'spec' => 1,
            'thickness' => 1,
            // 'length' => 1,
            'prev_weight' => 1,
            'in_weight' => 1,
            'out_weight' => 1,
            'barang_masuk' => 1,
            'umur' => 1,
            'balance_weight' => 1
        );
        $_SESSION[$GLOBALS['APP_ID']]['view.sraw']['columns'] = $cgx_def_columns;
    }

    $cgx_sql = "
        SELECT
        A.m_stock_weight_id
        ,A.app_org_id
        ,A.m_product_id
        ,A.prev_weight
        ,A.in_weight
        ,A.out_weight
        ,A.balance_weight
        ,C.product_code
        ,C.spec
        ,C.thickness
        ,C.od
        ,C.category
        ,C.app_org_id AS org_id
        ,D.m_inout_date AS barang_masuk
        ,CONCAT(DATEDIFF(CURDATE(),D.m_inout_date),' Hari') AS umur
        ,CONCAT(C.thickness,'%',C.length) AS descripsi
        
        FROM
        m_stock_weight AS A LEFT JOIN m_product AS C ON (A.m_product_id = C.m_product_id)
                            LEFT JOIN m_stock_onhand AS D ON (A.m_product_id = D.m_product_id)
        WHERE
        A.m_inout_date = (SELECT MAX(B.m_inout_date) FROM m_stock_weight AS B WHERE A.m_product_id = B.m_product_id )
        AND C.category = 'R'

        
        ";
    
    $cgx_sql_sum = "
        SELECT
        A.m_product_id
        ,SUM(A.balance_weight) AS total_berat
        
        FROM
        m_stock_weight AS A LEFT JOIN m_product AS C ON (A.m_product_id = C.m_product_id)
                            LEFT JOIN m_stock_onhand AS D ON (A.m_product_id = D.m_product_id)
        WHERE
        A.m_inout_date = (SELECT MAX(B.m_inout_date) FROM m_stock_weight AS B WHERE A.m_product_id = B.m_product_id )
        AND C.category = 'R'

        GROUP BY
        A.m_product_id

        ";
    /*
    $cgx_data_sum = cgx_fetch_table($cgx_sql_sum);
    $a = array($cgx_data_sum['total_berat']) ;
    echo array_sum($a);
    
    $c = array(1,2,3);
    echo "<br>".array_sum($c);
    */
    
    $cgx_datagrid = new Structures_DataGrid($cgx_max_rows);
    $cgx_options = array('dsn' => $cgx_dsn);

    $cgx_search = $_REQUEST['q'];

    echo "<form name='frmFILTER' action='{$_SERVER['SCRIPT_NAME']}'>\n";
    echo "<input type='hidden' name='m' value='{$_REQUEST['m']}'>\n";
    echo "<table id='bar' class='datagrid_bar' width='100%'><tr>\n";
    echo "<td align='left'>Cari&nbsp&nbsp<input type='text' size='20' name='q' value=\"{$cgx_search}\">&nbsp<input title='Cari' type='image' src='images/icon_search.png' border='0' style='padding-right: 20px;'></td>\n";
    echo "<td width='20'></td>\n";
    //echo "<td width='1' class='datagrid_bar_icon'><a title='Lihat Semua Coil' href='module.php?m=view.daftar.coil'><img border='0' src='images/icon_detail.png'></a></td>\n";
    //echo "<td width='1' class='datagrid_bar_icon'><a title='Export semua data ke CSV' href='action/view.sraw.php?mode=export-all'><img border='0' src='images/icon_csv.png'></a></td>\n";
    echo "<td width='1' class='datagrid_bar_icon'><a title='Kustomisasi kolom' href='javascript:customizeColumn(true);'><img border='0' src='images/icon_columns.png'></a></td>\n";
    echo "</tr></table>\n";
    echo "</form>\n";

    echo "<form name='frmCUSTOMIZE' action='action/cgx_customize.php' method='post'>\n";
    echo "<input type='hidden' name='back' value='" . urlencode($_SERVER['REQUEST_URI']) . "'>\n";
    echo "<input type='hidden' name='dg_name' value='view.sraw'>\n"; // penyebab ga bisa di pilih
    echo "<table id='columns' class='datagrid_bar' style='display: none;'><tr>\n";
   
    echo "<td width='99%' valign='top'>\n";
    
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['m_product_id'] == 1 ? ' checked' : '') . " id='col_m_product_id' name='col[m_product_id]' type='checkbox'></td><td width='99%'><label for='col_m_product_id'>ID Barang</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['product_code'] == 1 ? ' checked' : '') . " id='col_product_code' name='col[product_code]' type='checkbox'></td><td width='99%'><label for='col_product_code'>Item Number</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['spec'] == 1 ? ' checked' : '') . " id='col_spec' name='col[spec]' type='checkbox'></td><td width='99%'><label for='col_spec'>Spec</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['thickness'] == 1 ? ' checked' : '') . " id='col_thickness' name='col[thickness]' type='checkbox'></td><td width='99%'><label for='col_thickness'>Tebal</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['od'] == 1 ? ' checked' : '') . " id='col_od' name='col[od]' type='checkbox'></td><td width='99%'><label for='col_od'>Panjang</label></td></tr></table>\n";
    
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['barang_masuk'] == 1 ? ' checked' : '') . " id='col_barang_masuk' name='col[barang_masuk]' type='checkbox'></td><td width='99%'><label for='col_barang_masuk'>Barang Masuk</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['umur'] == 1 ? ' checked' : '') . " id='col_umur' name='col[umur]' type='checkbox'></td><td width='99%'><label for='col_umur'>Umur</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['prev_weight'] == 1 ? ' checked' : '') . " id='col_prev_weight' name='col[prev_weight]' type='checkbox'></td><td width='99%'><label for='col_prev_weight'>Prev. Weight</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['in_weight'] == 1 ? ' checked' : '') . " id='col_in_weight' name='col[in_weight]' type='checkbox'></td><td width='99%'><label for='col_in_weight'>In. Weight</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['out_weight'] == 1 ? ' checked' : '') . " id='col_out_weight' name='col[out_weight]' type='checkbox'></td><td width='99%'><label for='col_out_weight'>Out. Weight</label></td></tr></table>\n";
    
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['balance_weight'] == 1 ? ' checked' : '') . " id='col_balance_weight' name='col[balance_weight]' type='checkbox'></td><td width='99%'><label for='col_balance_weight'>Balance Weight</label></td></tr></table>\n";
    
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
            . "A.m_product_id LIKE '%{$cgx_search}%' "
            . "OR C.product_code LIKE '%{$cgx_search}%' "
            . "OR C.spec LIKE '%{$cgx_search}%' "
            . "OR C.thickness LIKE '%{$cgx_search}%' "
            . "OR C.length LIKE '%{$cgx_search}%' "
            . "OR D.m_inout_date LIKE '%{$cgx_search}%' "
            . ")";
    
    $cgx_sql .= " GROUP BY m_product_id
                  ORDER BY m_product_id ASC ";        
    
            
    if ($_SESSION[$GLOBALS['APP_ID']]['view.sraw']['error']) {
        echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['view.sraw']['error']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['view.sraw']['error']);
    }

    if ($_SESSION[$GLOBALS['APP_ID']]['view.sraw']['info']) {
        echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['view.sraw']['info']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['view.sraw']['info']);
    }
    
    //print_r($cgx_sql);
    $cgx_test = $cgx_datagrid->bind($cgx_sql, $cgx_options);
    if (PEAR::isError($cgx_test)) {
        echo $cgx_test->getMessage();
    }

    if ($cgx_def_columns['m_product_id'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('ID', 'm_product_id', 'm_product_id', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['product_code'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Item Number', 'product_code', 'product_code', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['spec'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Spec', 'spec', 'spec', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['thickness'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Tebal', 'thickness', 'thickness', array('align' => 'right'), NULL, NULL));
    if ($cgx_def_columns['od'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Lebar', 'od', 'od', array('align' => 'right'), NULL, NULL));
    
    if ($cgx_def_columns['barang_masuk'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Tgl Masuk Gudang', 'barang_masuk', 'barang_masuk', array('align' => 'center'), NULL, "cgx_format_date()"));
    if ($cgx_def_columns['umur'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Umur', 'umur', 'umur', array('align' => 'right'), NULL, NULL));
    
    if ($cgx_def_columns['prev_weight'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Berat Awal (Kg)', 'prev_weight', 'prev_weight', array('align' => 'right'), NULL, "cgx_format_3digit()"));
    if ($cgx_def_columns['in_weight'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Berat Masuk (Kg)', 'in_weight', 'in_weight', array('align' => 'right', 'width' => 100), NULL, "cgx_format_3digit()"));
    if ($cgx_def_columns['out_weight'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Berat Keluar (Kg)', 'out_weight', 'out_weight', array('align' => 'right', 'width' => 100), NULL, "cgx_format_3digit()"));
    if ($cgx_def_columns['balance_weight'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Berat Balance (Kg)', 'balance_weight', 'balance_weight', array('align' => 'right', 'width' => 100), NULL, "cgx_format_3digit()"));
    
    
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
    
    $cgx_data_sum = cgx_fetch_table($cgx_sql_sum);

    echo "<div class='tbl-header-box' style='font-size: 12px; border: 1px solid #ccc; margin-top: 10px;'>";
    echo "  <table class=''>";
    echo "  <tr>";
    echo "      <td><b>Total Berat Coil</b></td>";
    echo "      <td width='10' align='center'>:</td>";
    echo "      <td align='right'><b>".  number_format($cgx_data_sum["total_berat"], 2)." (KG)</b></td>";
    echo "      <td width='100px;'>&nbsp;</td>";
    echo "  </tr>";
    echo "  </table>";
    echo "</div>";

    
?>