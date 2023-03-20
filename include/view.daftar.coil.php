<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Indra Firmansyah M
 * 2015/01/20
 */
//echo "test";
//exit();

//echo user('user_fullname');

echo "<div class='title'>DAFTAR COIL</div>";

    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';

function cgx_detail($data) {
    $href = "module.php?m=view.scoil&q={$data['record']['product_code']}";
    $out = "<a href='{$href}'><b>{$data['record']['product_code']}</b></a>";
    return $out;
}    
    
    if (is_array($_SESSION[$GLOBALS['APP_ID']]['view.daftar.scoil']['columns'])) {
        $cgx_def_columns = $_SESSION[$GLOBALS['APP_ID']]['view.daftar.scoil']['columns'];
    } else {
        $cgx_def_columns = array(
           'c_order_id' => 1,
           'document_no' => 1,
           'remark' => 1,
           'spec' => 1,
           'od' => 1,
           'thickness' => 1,
           'partner_name' => 1,
           'm_inout_date' => 1,
           'no_kendaraan' => 1,
           'no_lot' => 1,
           'weight' => 1,
           'no_coil' => 1,
           'product_code' => 1
        );
        $_SESSION[$GLOBALS['APP_ID']]['view.daftar.scoil']['columns'] = $cgx_def_columns;
    }



    $cgx_sql = "
        SELECT 
        A.m_product_id
       ,F.spec
       ,F.od
       ,F.thickness 
       ,A.no_lot 
       ,A.weight 
       ,A.no_coil 
       ,C.document_no 
       ,C.m_inout_date 
       ,C.sj_date 
       ,C.no_kendaraan 
       ,D.c_order_id 
       ,D.remark ,E.partner_name 
       ,A.m_in_id
       ,F.product_code

       FROM 
       m_coil AS A LEFT JOIN m_inout_line AS B ON (A.m_in_id = B.m_inout_line_id) 
                   LEFT JOIN m_inout AS C ON (C.m_inout_id = B.m_inout_id) 
                   LEFT JOIN c_order AS D ON (C.c_order_id = D.c_order_id) 
                   LEFT JOIN c_bpartner AS E ON (D.c_bpartner_id = E.c_bpartner_id) 
                   LEFT JOIN m_product AS F ON (A.m_product_id = F.m_product_id)

        ";
    
//-- mp.Length,
    
    $cgx_datagrid = new Structures_DataGrid($cgx_max_rows);
    $cgx_options = array('dsn' => $cgx_dsn);

    $cgx_search = $_REQUEST['q'];
    $cgx_search1 = $_REQUEST['z'];
    
    echo "<form name='frmFILTER' action='{$_SERVER['SCRIPT_NAME']}'>\n";
    echo "<input type='hidden' name='m' value='{$_REQUEST['m']}'>\n";
    echo "<table id='bar' class='datagrid_bar' width='100%'><tr>\n";
    echo "<td colspan='2' align='left'>"
    . "Kode Coil&nbsp<input type='text' size='20' name='q' value=\"{$cgx_search}\">"
    . "&nbsp&nbspNo Coil&nbsp<input type='text' size='20' name='z' value=\"{$cgx_search1}\">"
    . "&nbsp&nbsp<input title='Cari' type='image' src='images/icon_search.png' border='0' style='padding-right: 20px;'></td>\n";
    //echo "<td style='left'></td>\n";
    //echo "<td align='right' width='1'><input type='button' value='Kembali' onclick=\"window.location = 'module.php?m=view.scoil'\"></td>\n";
    //echo "<td align='right' width='1' class='datagrid_bar_icon'><a title='Export semua data ke CSV' href='action/view.scoil.php?mode=export-all'><img border='0' src='images/icon_csv.png'></a></td>\n";
    //echo "<td align='right' width='1' class='datagrid_bar_icon'><a title='Kustomisasi kolom' href='javascript:customizeColumn(true);'><img border='0' src='images/icon_columns.png'></a></td>\n";
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
    </script>
    <?php
    
    if ($cgx_search) $cgx_sql .= " WHERE A.no_lot = '" . mysql_escape_string($cgx_search) . "'";
    if ($cgx_search1) $cgx_sql .= " WHERE A.no_coil = '" . mysql_escape_string($cgx_search1) . "'";
    
    //$cgx_sql .= " AND ( A.no_lot LIKE '%{$cgx_search}%' OR A.no_coil LIKE '%{$cgx_search}%')";
    
    if ($_SESSION[$GLOBALS['APP_ID']]['view.daftar.scoil']['error']) {
        echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['view.daftar.scoil']['error']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['view.scoil']['error']);
    }

    if ($_SESSION[$GLOBALS['APP_ID']]['view.daftar.coil']['info']) {
        echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['view.daftar.scoil']['info']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['view.daftar.scoil']['info']);
    }
    
    //print_r($cgx_sql);
    $cgx_test = $cgx_datagrid->bind($cgx_sql, $cgx_options);
    if (PEAR::isError($cgx_test)) {
        echo $cgx_test->getMessage();
    }

    if ($cgx_def_columns['no_lot'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Kode Coil', 'no_lot', 'product_code', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['no_coil'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('No. Coil', 'no_coil', 'no_coil', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['weight'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Berat', 'weight', 'weight', array('align' => 'left'), NULL, NULL));    
    if ($cgx_def_columns['spec'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Spec', 'spec', 'spec', array('align' => 'left'), NULL, NULL));
    
    if ($cgx_def_columns['od'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Lebar', 'od', 'od', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['thickness'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Tebal', 'thickness', 'thickness', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['document_no'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('No. Receipt', 'document_no', 'document_no', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['m_inout_date'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Tgl. Masuk', 'm_inout_date', 'm_inout_date', array('align' => 'left'), NULL, "cgx_format_date()"));
    if ($cgx_def_columns['no_kendaraan'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('No. Surat Jalan', 'no_kendaraan', 'no_kendaraan', array('align' => 'left'), NULL, NULL));
    
    if ($cgx_def_columns['sj_date'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Tgl. Surat Jalan', 'sj_date', 'sj_date', array('align' => 'right'), NULL, "cgx_format_date()"));
    if ($cgx_def_columns['remark'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('No. Contract', 'remark', 'remark', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['partner_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Vendor', 'partner_name', 'partner_name', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['product_code'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Item Number', 'product_code', 'product_code', array('align' => 'left'), NULL, "cgx_detail"));

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

?>