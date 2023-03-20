<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 05/12/2013 14:06:29
 */


echo "<div class='title'>Forecast vs SO<div style='font-size: 12px;'>As of " . date($APP_DATETIME_FORMAT) . "</div></div>";

require_once 'Structures/DataGrid.php';
require_once 'HTML/Table.php';

$cgx_datagrid = new Structures_DataGrid($cgx_max_rows);
$cgx_options = array('dsn' => $cgx_dsn);

$period_1 = $_REQUEST['period-1'];
$period_2 = $_REQUEST['period-2'];
$period_3 = $_REQUEST['period-3'];
$_REQUEST['c_bpartner_id']==NULL || $_REQUEST['c_bpartner_id']=='' ? $partner=0:$partner=$_REQUEST['c_bpartner_id'];

$cgx_data['c_bpartner_id'] = $_REQUEST['c_bpartner_id'];
$cgx_data['partner_name'] = $_REQUEST['partner_name'];

echo "<form name='frmFILTER' action='{$_SERVER['SCRIPT_NAME']}'>";
echo "<input type='hidden' name='m' value='{$_REQUEST['m']}'>";
echo "<input id='c_bpartner_id' name='c_bpartner_id' type='hidden' value=\"{$cgx_data['c_bpartner_id']}\" style='text-align: left;' />";
echo "<table id='bar' class='datagrid_bar' width='100%' border=0><tr>";
echo "<tr>";
echo "<td>Customer</td>";
echo "<td>Bulan Pertama</td>";
echo "<td>Bulan Kedua</td>";
echo "<td>Bulan Ketiga</td>";
echo "<td></td>";
echo "</tr><tr>";
echo "<td><input id='partner_name' name='partner_name' type='text' value=\"{$cgx_data['partner_name']}\" size='40' maxlength='40' style='text-align: left;' /><img onclick=\"popupReference('business-partner');\" style='cursor: pointer; vertical-align: bottom;' src='images/icon_reference.png'></td>";
echo "<td><input{$readonly} id='period-1' name='period-1' type='text' maxlength='10' style='text-align: center; width: 110px;' value='{$period_1}'></td>";
echo "<td><input{$readonly} id='period-2' name='period-2' type='text' maxlength='10' style='text-align: center; width: 110px;' value='{$period_2}'></td>";
echo "<td><input{$readonly} id='period-3' name='period-3' type='text' maxlength='10' style='text-align: center; width: 110px;' value='{$period_3}'></td>";
echo "<td width='1'><input title='Cari' type='image' src='images/icon_search.png' border='0' style='padding-right: 20px;'></td>";
echo "</tr></table>\n";
echo "</form>\n";


?>

<style>
    .ui-datepicker-calendar { display: none; }
</style>

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

function setBusinessPartner(id, name) {
    var txt_name = document.getElementById('partner_name');
    var hid_id = document.getElementById('c_bpartner_id');
    txt_name.value = name;
    hid_id.value = id;
}

$(function() {
    $("#period-1").datepicker(
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
    $("#period-2").datepicker(
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
    $("#period-3").datepicker(
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

//-->
</script>
<?php

function judulBulan($periodnya){
    switch (substr($periodnya, 0, 2)) {
        case '01':
            $jdl = "Jan";
            break;
        case '02':
            $jdl = "Feb";
            break;
        case '03':
            $jdl = "Mar";
            break;
        case '04':
            $jdl = "Apr";
            break;
        case '05':
            $jdl = "Mei";
            break;
        case '06':
            $jdl = "Jun";
            break;
        case '07':
            $jdl = "Jul";
            break;
        case '08':
            $jdl = "Agst";
            break;
        case '09':
            $jdl = "Sep";
            break;
        case '10':
            $jdl = "Okt";
            break;
        case '11':
            $jdl = "Nov";
            break;
        case '12':
            $jdl = "Des";
            break;
        default:
            $jdl = "No Bulan";
            break;
    }
    $jdl .= " ".substr($periodnya, -2); 
    return $jdl;    
}

$jdl_1 = judulBulan($period_1);
$jdl_2 = judulBulan($period_2);
$jdl_3 = judulBulan($period_3);

$cgx_sql = "SELECT mp.product_code,
mp.product_name,
mp.spec,
mp.od,
mp.thickness,
mp.length,
sum(cfl.quantity),
max( CASE WHEN cf.periode='".npl_period2mysqldate($period_1)."' THEN cfl.quantity ELSE 0 end ) AS bulansatu, 
(SELECT sum( col.order_quantity )
FROM `c_order` co, c_order_line col
WHERE co.c_order_id = col.c_order_id 
AND col.m_product_id = mp.m_product_id 
AND month( co.order_date ) = '".substr($period_1, 0, 2)."'
AND year( co.order_date ) = '".substr($period_1, -4)."'
AND co.c_bpartner_id =189) AS sobulan1,

(SELECT sum( col.order_quantity ) 
FROM c_order co, c_order_line col 
WHERE co.c_order_id = col.c_order_id 
AND col.m_product_id = mp.m_product_id 
AND month( co.order_date ) = '".substr($period_2, 0, 2)."'
AND year( co.order_date ) = '".substr($period_2, -4)."'
AND co.c_bpartner_id =189) AS sobulan2,

(SELECT sum( col.order_quantity )
FROM `c_order` co, c_order_line col 
WHERE co.c_order_id = col.c_order_id 
AND col.m_product_id = mp.m_product_id
AND month( co.order_date ) = '".substr($period_3, 0, 2)."' 
AND year( co.order_date ) = '".substr($period_3, -4)."' 
AND co.c_bpartner_id =189) AS sobulan3,

max( CASE WHEN cf.periode='".npl_period2mysqldate($period_2)."' THEN cfl.quantity ELSE 0 end ) AS bulandua,
max( CASE WHEN cf.periode='".npl_period2mysqldate($period_3)."' THEN cfl.quantity ELSE 0 end ) AS bulantiga

FROM c_forecast_line cfl JOIN m_product mp USING (m_product_id)
JOIN c_forecast cf USING (c_forecast_id)
            WHERE cf.c_bpartner_id=$partner
            GROUP BY mp.product_code ";

//echo $cgx_sql;

function selisih_satu($data) {
    $so = $data['record']['sobulan1'];
    $fc = $data['record']['bulansatu'];
    $sel = $so - $fc;
    $sel = number_format($sel);
    $out = "$sel";
    return $out;
}

function selisih_dua($data) {
    $so = $data['record']['sobulan2'];
    $fc = $data['record']['bulandua'];
    $sel = $so - $fc;
    $sel = number_format($sel);
    $out = "$sel";
    return $out;
}

function selisih_tiga($data) {
    $so = $data['record']['sobulan3'];
    $fc = $data['record']['bulantiga'];
    $sel = $so - $fc;
    $sel = number_format($sel);
    $out = "$sel";
    return $out;
}

$cgx_test = $cgx_datagrid->bind($cgx_sql, $cgx_options);
if (PEAR::isError($cgx_test)) {
    echo $cgx_test->getMessage();
}

$cgx_datagrid->addColumn(new Structures_DataGrid_Column('Item Number', 'product_code', 'product_code', array('align' => 'left'), NULL, NULL));
$cgx_datagrid->addColumn(new Structures_DataGrid_Column('Nama Barang', 'product_name', 'product_name', array('align' => 'left'), NULL, NULL));
$cgx_datagrid->addColumn(new Structures_DataGrid_Column('Spec', 'spec', 'spec', array('align' => 'left'), NULL, NULL));
$cgx_datagrid->addColumn(new Structures_DataGrid_Column('OD', 'od', 'od', array('align' => 'right'), NULL, NULL));
$cgx_datagrid->addColumn(new Structures_DataGrid_Column('Thickness', 'thickness', 'thickness', array('align' => 'right'), NULL, NULL));
$cgx_datagrid->addColumn(new Structures_DataGrid_Column('Length', 'length', 'length', array('align' => 'right'), NULL, NULL));
$cgx_datagrid->addColumn(new Structures_DataGrid_Column("SO<br>$jdl_1", 'sobulan1', 'sobulan1', array('align' => 'right'), NULL, "cgx_format_3digit()"));
$cgx_datagrid->addColumn(new Structures_DataGrid_Column("FRC<br>$jdl_1", 'bulansatu', 'bulansatu', array('align' => 'right'), NULL, "cgx_format_3digit()"));
$cgx_datagrid->addColumn(new Structures_DataGrid_Column("&nabla;<br>$jdl_1", NULL, NULL, array('align' => 'right'), NULL, 'selisih_satu()'));
$cgx_datagrid->addColumn(new Structures_DataGrid_Column("SO<br>$jdl_2", 'sobulan2', 'sobulan2', array('align' => 'right'), NULL, "cgx_format_3digit()"));
$cgx_datagrid->addColumn(new Structures_DataGrid_Column("FRC<br>$jdl_2", 'bulandua', 'bulandua', array('align' => 'right'), NULL, "cgx_format_3digit()"));
$cgx_datagrid->addColumn(new Structures_DataGrid_Column("&nabla;<br>$jdl_2", NULL, NULL, array('align' => 'right'), NULL, 'selisih_dua()'));
$cgx_datagrid->addColumn(new Structures_DataGrid_Column("SO<br>$jdl_3", 'sobulan3', 'sobulan3', array('align' => 'right'), NULL, "cgx_format_3digit()"));
$cgx_datagrid->addColumn(new Structures_DataGrid_Column("FRC<br>$jdl_3", 'bulantiga', 'bulantiga', array('align' => 'right'), NULL, "cgx_format_3digit()"));
$cgx_datagrid->addColumn(new Structures_DataGrid_Column("&nabla;<br>$jdl_3", NULL, NULL, array('align' => 'right'), NULL, 'selisih_tiga()'));

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
