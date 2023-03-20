<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.org>
 * Nov 23, 2013 11:35:10 PM
 */

// load data to session
if ($_REQUEST['mode'] == 'edit' && $_SESSION[$APP_ID]['view.posisi.stok']['product_code'] == $_REQUEST['pkey']['product_code']) {
    $data = $_SESSION[$APP_ID]['view.posisi.stok'];
} else {
    //$data = npl_fetch_table(
      //      "SELECT *
        //        FROM m_stock_balance_2
          //      JOIN m_product ON ( m_stock_balance_2.m_product_id = m_product.m_product_id )
            //    WHERE m_product.product_code ='{$_REQUEST['pkey']['product_code']}'");
    $rsx = mysql_query(
            "SELECT m_stock_balance_2.*, m_product.product_name as product_name, app_org.organization as organization 
                FROM m_stock_balance_2
                JOIN m_product ON ( m_stock_balance_2.m_product_id = m_product.m_product_id )
                JOIN app_org on (m_stock_balance_2.app_org_id = app_org.app_org_id)
                WHERE m_product.product_code ='{$_REQUEST['pkey']['product_code']}'   
                ORDER BY m_stock_balance_2.m_stock_balance_id",
            $APP_CONNECTION);
//$cgx_sql .= " AND m_stock_balance_d_2.app_org_id = " . org(); AND m_stock_balance_2.latest='Y'
    $data['lines'] = array();
    while ($dtx = mysql_fetch_array($rsx, MYSQL_ASSOC)) $data['lines'][] = $dtx;
    mysql_free_result($rsx);
    $_SESSION[$APP_ID]['view.posisi.stok'] = $data;
}


if ($_SESSION[$GLOBALS['APP_ID']]['view.posisi.stok']['error']) {
    echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['view.sb']['error']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['view.posisi.stok']['error']);
}

if ($_SESSION[$GLOBALS['APP_ID']]['view.posisi.stok']['info']) {
    echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['view.sb']['info']}</div>";
    unset($_SESSION[$GLOBALS['APP_ID']]['view.posisi.stok']['info']);
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
echo "<td width='1' class='datagrid_bar_icon'><a title='Export semua data ke CSV' href='action/view.posisi.stok.php?mode=export-all-d&pc=". $_REQUEST['pkey']['product_code'] ."'><img border='0' src='images/icon_csv.png'></a></td>\n";
echo "</tr></table>\n";
echo "</form>\n";

echo "<div id='area-lines' style='margin-top: 4px;'></div>";
echo "<div class='area-button'>";
echo "<input type='button' value='Kembali' onclick=\"window.location = 'module.php?m={$_REQUEST['m']}'\">";
echo "</div>";

?>
<script type="text/javascript">
<!--

<?php if ($_REQUEST['mode'] == 'edit') { ?>
$(function() {
    $("#order_date").datepicker({dateFormat: '<?php echo $APP_DATE_FORMAT_JAVA; ?>'});
});
<?php } ?>

xajax_showLines('<?php echo $data['product_code'] ?>', '<?php echo $_REQUEST['mode']; ?>');

//-->
</script>
