<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.org>
 * Nov 24, 2013 8:18:31 AM
 */


function showLines($wo_id, $mode = NULL) {
    global $APP_CONNECTION, $APP_ID, $cgx_TableAttribs, $cgx_HeaderAttribs,
        $cgx_EvenRowAttribs, $cgx_OddRowAttribs, $cgx_RendererOptions;

    $data = $_SESSION[$APP_ID]['ppcwo'];
    if ($data['c_wo_id'] != $wo_id) return;
    
    if (is_array($data['lines'])) {
        $n = 0;
        foreach ($data['lines'] as $k => $d) {
            $n++;
            $data['lines'][$k]['line'] = $n;
            $data['lines'][$k]['ukuranrc'] = $data['lines'][$k]['od'] .' x ' .$data['lines'][$k]['thickness'] .' x ' .$data['lines'][$k]['length'];
            $data['lines'][$k]['ukuranlp'] = $data['lines'][$k]['od_mat'] .' x ' .$data['lines'][$k]['thickness_mat'] .' x ' .$data['lines'][$k]['length_mat'];
        }
    }

    $sql = "SELECT DISTINCT working_date FROM c_wo_line JOIN c_wo USING (c_wo_id) WHERE c_wo.c_wo_id = {$data['c_wo_id']} ORDER BY working_date";
    $rsq = mysql_query($sql , $APP_CONNECTION);
    $m = 0;
    while ($dtq = mysql_fetch_array($rsq, MYSQL_ASSOC)) {
         $m++;
         $datax[$m] = $dtq['working_date'];    
    }
    for ($i=1; $i<=7 ; $i++) {
        if ($datax[$i]) {
            $datay[$i] = $datax[$i];
        }else{
            $datay[$i] = "";
        }
    }

    $_SESSION[$APP_ID]['ppcwo'] = $data;

    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';
    
    $datagrid = new Structures_DataGrid(9999);
    $datagrid->bind($data['lines'], array(), 'Array');
    $cgx_table = new HTML_Table($cgx_TableAttribs);
    $cgx_tableHeader = & $cgx_table->getHeader();
    $cgx_tableBody = & $cgx_table->getBody();
    
    $datagrid->addColumn(new Structures_DataGrid_Column('Baris', 'line', NULL, array('align' => 'center'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Mesin', 'machine_name', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Item Number', 'product_code', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Customer', 'partner_name', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Spec', 'spec', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Ukuran<br>Long Pipe', 'ukuranlp', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Ukuran<br>Recutting', 'ukuranrc', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Quantity<br>Recutting', 'qty', NULL, array('align' => 'right'), NULL, "cgx_format_3digit()"));
    if ($datay[1] != '') $datagrid->addColumn(new Structures_DataGrid_Column("$datay[1]", 'day1', NULL, array('align' => 'right'), NULL, "cgx_format_3digit()"));
    if ($datay[2] != '') $datagrid->addColumn(new Structures_DataGrid_Column("$datay[2]", 'day2', NULL, array('align' => 'right'), NULL, "cgx_format_3digit()"));
    if ($datay[3] != '') $datagrid->addColumn(new Structures_DataGrid_Column("$datay[3]", 'day3', NULL, array('align' => 'right'), NULL, "cgx_format_3digit()"));
    if ($datay[4] != '') $datagrid->addColumn(new Structures_DataGrid_Column("$datay[4]", 'day4', NULL, array('align' => 'right'), NULL, "cgx_format_3digit()"));
    if ($datay[5] != '') $datagrid->addColumn(new Structures_DataGrid_Column("$datay[5]", 'day5', NULL, array('align' => 'right'), NULL, "cgx_format_3digit()"));
    if ($datay[6] != '') $datagrid->addColumn(new Structures_DataGrid_Column("$datay[6]", 'day6', NULL, array('align' => 'right'), NULL, "cgx_format_3digit()"));
    if ($datay[7] != '') $datagrid->addColumn(new Structures_DataGrid_Column("$datay[7]", 'day7', NULL, array('align' => 'right'), NULL, "cgx_format_3digit()"));
    $datagrid->fill($cgx_table, $cgx_RendererOptions);
    $cgx_tableHeader->setRowAttributes(0, $cgx_HeaderAttribs);
    $cgx_tableBody->altRowAttributes(0, $cgx_EvenRowAttribs, $cgx_OddRowAttribs, TRUE);

    $html  = "<div class='datagrid_background'>\n";
    $html .= $cgx_table->toHtml();
    $html .= "</div>\n";

    $res = new xajaxResponse();
    $res->assign('area-lines', 'innerHTML', $html);
    return $res;
}

$xajax->register(XAJAX_FUNCTION, 'showLines');

?>