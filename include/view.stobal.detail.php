<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.org>
 * Nov 23, 2013 11:35:10 PM
 */

// load data to session
if($_REQUEST['whouse'] == 'y') {
    if ($_REQUEST['mode'] == 'edit' && $_SESSION[$APP_ID]['k']['m_product_id'] == $_REQUEST['pkey']['m_product_id']) {
        $data = $_SESSION[$APP_ID]['stobal'];
    } else {
        $data = npl_fetch_table(
            "SELECT * FROM m_stock_warehouse_2
            JOIN m_product USING (m_product_id)
            JOIN m_warehouse ON m_stock_warehouse_2.m_warehouse_id = m_warehouse.m_warehouse_id 
            LEFT JOIN c_bpartner ON(m_product.c_bpartner_id = c_bpartner.c_bpartner_id)
            WHERE latest = 'Y' AND m_stock_warehouse_2.m_product_id = '{$_REQUEST['pkey']['m_product_id']}' AND m_stock_warehouse_2.app_org_id = ".org());
        $rsx = mysql_query(
            "SELECT * FROM m_stock_warehouse_2
            JOIN m_product USING (m_product_id)
            JOIN m_warehouse ON m_stock_warehouse_2.m_warehouse_id = m_warehouse.m_warehouse_id 
            LEFT JOIN c_bpartner ON(m_product.c_bpartner_id = c_bpartner.c_bpartner_id)
            WHERE latest = 'Y' AND m_stock_warehouse_2.m_product_id = '{$_REQUEST['pkey']['m_product_id']}' AND m_stock_warehouse_2.app_org_id = ".org()."  
            ORDER BY m_stock_warehouse_2.m_warehouse_id",
            $APP_CONNECTION);
        $data['lines'] = array();
        while ($dtx = mysql_fetch_array($rsx, MYSQL_ASSOC)) $data['lines'][] = $dtx;
        mysql_free_result($rsx);
        $_SESSION[$APP_ID]['stobal'] = $data;
    }
    if ($_SESSION[$GLOBALS['APP_ID']]['view.stobal']['error']) {
        echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['view.stobal']['error']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['view.stobal']['error']);
    }
    if ($_SESSION[$GLOBALS['APP_ID']]['view.stobal']['info']) {
        echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['view.stobal']['info']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['view.stobal']['info']);
    }
    echo "<div id='whouse-lines' style='margin-top: 4px;'></div>";
    echo "<div id='daerah-total' class='area-button'></div>";
    if ($_REQUEST['mode'] == 'edit') {
        echo "<div id='area-editWH' class='data_box' style='margin-top: 4px; display: none;'></div>";
        echo "<div class='area-button' id='master-button'>";
        echo "<input type='button' value='Simpan Dokumen' onclick=\"xajax_saveWH({$data['m_product_id']});\">";
        echo "<input type='button' value='Tambah Baris' onclick=\"xajax_editFormWH();\">";
        echo "<input type='button' value='Batal' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}&pkey[m_product_id]={$data['m_product_id']}&whouse=y'\">";
        echo "</div>";
        echo "<div style='text-align: right; margin-top: 10px;'>{$mandatory} = Wajib diisi</div>";
    } else {
        echo "<div class='area-button'>";
        echo "<input type='button' value='Kembali' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}'\">";
        if (user() == 2 OR user() == 46 OR user() == 51 OR user() == 55 OR user() == 37 OR user() == 34) {
            echo "<input type='button' value='Edit' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}&pkey[m_product_id]={$data['m_product_id']}&whouse=y&mode=edit'\">";
        }
        echo "</div>";
    }
} else {
    if ($_REQUEST['mode'] == 'edit' && $_SESSION[$APP_ID]['k']['m_product_id'] == $_REQUEST['pkey']['m_product_id']) {
        $data = $_SESSION[$APP_ID]['stobal'];
    } else {
        $data = npl_fetch_table(
                "SELECT *
                    FROM m_stock_onhand
                    JOIN m_product ON ( m_stock_onhand.m_product_id = m_product.m_product_id )
                    WHERE m_stock_onhand.m_product_id ='{$_REQUEST['pkey']['m_product_id']}' AND m_stock_onhand.app_org_id = ".org());
        $rsx = mysql_query(
                "SELECT *
                    FROM m_stock_onhand
                    JOIN m_product ON ( m_stock_onhand.m_product_id = m_product.m_product_id )
                    WHERE m_stock_onhand.m_product_id ='{$_REQUEST['pkey']['m_product_id']}' AND m_stock_onhand.app_org_id = ".org()."  
                    ORDER BY m_stock_onhand.m_inout_date",
                $APP_CONNECTION);
        $data['lines'] = array();
        while ($dtx = mysql_fetch_array($rsx, MYSQL_ASSOC)) $data['lines'][] = $dtx;
        mysql_free_result($rsx);
        $_SESSION[$APP_ID]['stobal'] = $data;
    }
    if ($_SESSION[$GLOBALS['APP_ID']]['view.stobal']['error']) {
        echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['view.stobal']['error']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['view.stobal']['error']);
    }
    if ($_SESSION[$GLOBALS['APP_ID']]['view.stobal']['info']) {
        echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['view.stobal']['info']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['view.stobal']['info']);
    }
    echo "<form name='frmFILTER' action='{$_SERVER['SCRIPT_NAME']}'>\n";
    echo "<table id='bar' class='datagrid_bar' width='100%'><tr>\n";
    echo "<td align='right'></td>";
    echo "<td width='1'></td>\n";
    echo "<td width='20'></td>\n";
    echo "<td align='right'></td>";
    echo "<td width='1'></td>\n";
    echo "<td width='1'></td>\n";
    echo "<td></td>\n";
    echo "<td width='20'></td>\n";
    echo "<td width='1'></td>\n";
    echo "<td width='1' class='datagrid_bar_icon'><a title='Export semua data ke CSV' href='action/view.stobal.php?mode=export-all-d&pc=". $_REQUEST['pkey']['m_product_id'] ."'><img border='0' src='images/icon_csv.png'></a></td>\n";
    echo "</tr></table>\n";
    echo "</form>\n";
    echo "<div id='area-lines' style='margin-top: 4px;'></div>";
    echo "<div class='area-button'>";
    echo "<input type='button' value='Kembali' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}'\">";
    echo "</div>";
}

?>

<script type="text/javascript">
<!--
<?php if ($_REQUEST['mode'] == 'edit') { ?>
$(function() {
    $("#order_date").datepicker({dateFormat: '<?php echo $APP_DATE_FORMAT_JAVA; ?>'});
});
<?php } ?>
xajax_showLines('<?php echo $data['m_product_id'] ?>', '<?php echo $_REQUEST['mode']; ?>');
xajax_showLinesWH('<?php echo $data['m_product_id'] ?>', '<?php echo $_REQUEST['mode']; ?>');
//-->
</script>
