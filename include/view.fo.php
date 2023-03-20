<?php

/**
 * (c) Azwari Nugraha <nugraha@pt-gai.com>
 * 23/11/2013 12:40:39
 */


echo "<div class='title'>Forecast Detail</div>";

    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';

    if (is_array($_SESSION[$GLOBALS['APP_ID']]['view.fo']['columns'])) {
        $cgx_def_columns = $_SESSION[$GLOBALS['APP_ID']]['view.fo']['columns'];
    } else {
        $cgx_def_columns = array(
           'c_forecast_id' => 1,
           'document_no' => 1,
           'partner_name' => 1,
           'periode' => 1,
           'notes' => 1,
           'product_code' => 1,
           'product_name' => 1,
           'quantity' => 1,
        );
        $_SESSION[$GLOBALS['APP_ID']]['view.fo']['columns'] = $cgx_def_columns;
    }
    
    $cgx_sql = "SELECT 
                c_forecast_id,
                document_no,
                partner_name,
                periode,
                notes,
                product_code,
                product_name,
                quantity,
                create_date,
                buat.user_name as createuser,
                update_date,
                edit.user_name as updateuser
                
                FROM c_forecast
                
                JOIN c_forecast_line USING (c_forecast_id)
                JOIN c_bpartner USING (c_bpartner_id) 
                JOIN m_product USING (m_product_id)
                JOIN app_org ON(c_forecast.app_org_id=app_org.app_org_id)
                JOIN app_user buat ON (c_forecast.create_user = buat.user_id)
                JOIN app_user edit ON (c_forecast.update_user = edit.user_id)
                
                WHERE 1=1 AND ". org_filter_trx('c_forecast.app_org_id');
                

    $cgx_datagrid = new Structures_DataGrid($cgx_max_rows);
    $cgx_options = array('dsn' => $cgx_dsn);
    
    $periode = npl_format_period($data['periode']);

    $document_no = $_REQUEST['document_no'];   
    $partner_name = $_REQUEST['partner_name'];
    $periode= $_REQUEST['periode'];

    echo "<form name='frmFILTER' action='{$_SERVER['SCRIPT_NAME']}'>\n";
    echo "<input type='hidden' name='m' value='{$_REQUEST['m']}'>\n";
    echo "<table id='bar' class='datagrid_bar' width='100%' border='0'><tr>\n";
    
 
    echo "<td align='right'>Item Number</td>\n";
    echo "<td align='left'><input type='text' size='20' name='document_no' value=\"{$document_no}\"></td>\n";
    //echo "<td width='1'><input title='Cari' type='image' src='images/icon_search.png' border='0' style='padding-right: 20px;'></td>\n";
    echo "<td width='1'></td>\n";
    
    echo "<td align='right'>Customer</td>\n";
    echo "<td align='left'><input type='text' size='20' name='partner_name' value=\"{$partner_name}\"></td>\n";
    echo "<td width='1'></td>\n";
    
    echo "<td align='right'>Periode</td>";
    echo "<td width='1' align='left'><input type='text' id='periode' name='periode' type='text' size='15' value='{$periode}' style='text-align: center; width: 110px;'></td>";
    echo "<td width='1'><input title='Cari' type='image' src='images/icon_search.png' border='0' style='padding-right: 20px;'></td>\n";
    echo "<td width='1'></td>\n";
    
    echo "<td width='1' class='datagrid_bar_icon'><a title='Export semua data ke CSV' href='javascript:exportCSV();'><img border='0' src='images/icon_csv.png'></a></td>\n";
    echo "<td width='1' class='datagrid_bar_icon'><a title='Customize columns' href='javascript:customizeColumn(true);'><img border='0' src='images/icon_columns.png'></a></td>\n";
    echo "</tr></table>\n";
    echo "</form>\n";

    echo "<form name='frmCUSTOMIZE' action='action/cgx_customize.php' method='post'>\n";
    echo "<input type='hidden' name='back' value='" . urlencode($_SERVER['REQUEST_URI']) . "'>\n";
    echo "<input type='hidden' name='dg_name' value='view.fo'>\n";
    echo "<input type='hidden' name='col[c_forecast_id]' value='on'>\n";
    echo "<input type='hidden' name='col[document_no]' value='on'>\n";
    echo "<table id='columns' class='datagrid_bar' style='display: none;'><tr>\n";
    echo "<td width='99%' valign='top'>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_c_order_id' name='col[c_order_id]' type='checkbox'></td><td width='99%'><label for='col_c_order_id'>ID</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input disabled checked id='col_document_no' name='col[document_no]' type='checkbox'></td><td width='99%'><label for='col_document_no'>Nomor Dokumen</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['partner_name'] == 1 ? ' checked' : '') . " id='col_partner_name' name='col[partner_name]' type='checkbox'></td><td width='99%'><label for='col_partner_name'>Customer</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['periode'] == 1 ? ' checked' : '') . " id='col_periode' name='col[periode]' type='checkbox'></td><td width='99%'><label for='col_periode'>Periode</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['notes'] == 1 ? ' checked' : '') . " id='col_notes' name='col[notes]' type='checkbox'></td><td width='99%'><label for='col_notes'>Notes</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['product_code'] == 1 ? ' checked' : '') . " id='col_product_code' name='col[product_code]' type='checkbox'></td><td width='99%'><label for='col_product_code'>Item Number</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['product_name'] == 1 ? ' checked' : '') . " id='col_product_name' name='col[product_name]' type='checkbox'></td><td width='99%'><label for='col_product_name'>Product Name</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['quantity'] == 1 ? ' checked' : '') . " id='col_quantity' name='col[quantity]' type='checkbox'></td><td width='99%'><label for='col_quantity'>Quantity</label></td></tr></table>\n";
   
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['create_date'] == 1 ? ' checked' : '') . " id='col_create_date' name='col[create_date]' type='checkbox'></td><td width='99%'><label for='col_create_date'>Create Date</label></td></tr></table>\n"; 
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['createuser'] == 1 ? ' checked' : '') . " id='col_createuser' name='col[createuser]' type='checkbox'></td><td width='99%'><label for='col_createuser'>Create User</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['update_date'] == 1 ? ' checked' : '') . " id='col_update_date' name='col[update_date]' type='checkbox'></td><td width='99%'><label for='col_update_date'>Update Date</label></td></tr></table>\n";
    echo "<table cellspacing='0' cellpadding='0' class='datagrid_bar_customize' align='left' width='160'><tr><td width='1'><input" . ($cgx_def_columns['updateuser'] == 1 ? ' checked' : '') . " id='col_updateuser' name='col[updateuser]' type='checkbox'></td><td width='99%'><label for='col_updateuser'>Update User</label></td></tr></table>\n";
    
    echo "<td width='1' valign='top'><input type='submit' value='Update'></td>\n";
    echo "<td width='1' valign='top'><input type='button' value='Cancel' onclick='customizeColumn(false);'></td>\n";
    echo "</tr></table>\n";
    echo "</form>\n";
?>
<style>
.ui-datepicker-calendar { display: none; }
</style>
<script type="text/javascript">
<!--
$(function() {
    $("#periode").datepicker(
        {
        changeMonth: true,
        changeYear: true,
        showButtonPanel: true,
        dateFormat: 'mm-yy',
        onClose: function(dateText, inst) {
            var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
            var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
            $(this).datepicker('setDate', new Date(year, month, 1));
            $(this).datepicker('refresh');
        },
        beforeShow: function() { 
            if ($(this).val().length > 0) {
                var parts = $(this).val().split('-');
                var month = parts[0] - 1;
                var year = parts[1];
                $(this).datepicker('option', 'defaultDate', new Date(year, month, 1));
                $(this).datepicker('setDate', new Date(year, month, 1));
            }
        }
    });
});

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

function exportCSV() {
    form = document.createElement("form");
    form.setAttribute("method", "post");
    form.setAttribute("action", "action/view.fo.php");

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "mode");
    hiddenField.setAttribute("value", "export-all");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "document_no");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['document_no']; ?>");
    form.appendChild(hiddenField);

    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "partner_name");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['partner_name']; ?>");
    form.appendChild(hiddenField);
    
    hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "periode");
    hiddenField.setAttribute("value", "<?php echo $_REQUEST['periode']; ?>");
    form.appendChild(hiddenField);
    
    document.body.appendChild(form);
    form.submit();    
}
//-->
</script>
<?php
    
    if($document_no) $cgx_sql .=" AND c_forecast.document_no  LIKE '%{$document_no}%'"; 
    if($partner_name) $cgx_sql .= " AND  c_bpartner.partner_name LIKE '%{$partner_name}%'";
    if ($periode) $cgx_sql .= " AND c_forecast.periode = '" . npl_period2mysqldate($periode) . "'"; 
     
    //print_r($cgx_sql);
    //exit;
    
   // $cgx_sql .= "GROUP BY c_forecast_id";
    if ($_SESSION[$GLOBALS['APP_ID']]['view.fo']['error']) {
        echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['view.fo']['error']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['view.fo']['error']);
    }

    if ($_SESSION[$GLOBALS['APP_ID']]['view.fo']['info']) {
        echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['view.fo']['info']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['view.fo']['info']);
    }


    $cgx_test = $cgx_datagrid->bind($cgx_sql, $cgx_options);
    if (PEAR::isError($cgx_test)) {
        echo $cgx_test->getMessage();
    }

    if ($cgx_def_columns['c_forecast_id'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('ID', 'c_forecast_id', 'c_forecast_id', array('align' => 'right'), NULL, NULL));
    if ($cgx_def_columns['document_no'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Nomor Dokumen', 'document_no', 'document_no', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['partner_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Customer', 'partner_name', 'partner_name', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['periode'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Periode', 'periode', 'periode', array('align' => 'center'), NULL, "cgx_format_date()"));
    if ($cgx_def_columns['notes'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Notes', 'notes', 'notes', array('align' => 'left'), NULL, NULL)); 
    if ($cgx_def_columns['product_code'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Item Number', 'product_code', 'product_code', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['product_name'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Product Name', 'product_name', 'product_name', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['quantity'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Quantity', 'quantity', 'quantity', array('align' => 'left'), NULL, NULL));
    
    if ($cgx_def_columns['create_date'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Create Date', 'create_date', 'create_date', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['createuser'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Create User', 'createuser', 'createuser', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['update_date'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Update Date', 'update_date', 'update_date', array('align' => 'left'), NULL, NULL));
    if ($cgx_def_columns['updateuser'] == 1) $cgx_datagrid->addColumn(new Structures_DataGrid_Column('Update User', 'updateuser', 'updateuser', array('align' => 'left'), NULL, NULL));
    
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