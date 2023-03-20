<?php

/*
 * trx
 * Azwari Nugraha <nugraha@duabelas.org>
 * Nov 24, 2013 8:18:31 AM
 */


function showLines($product_code, $mode = NULL) {
    global $APP_CONNECTION, $APP_ID, $cgx_TableAttribs, $cgx_HeaderAttribs,
        $cgx_EvenRowAttribs, $cgx_OddRowAttribs, $cgx_RendererOptions;
    
    $data = $_SESSION[$APP_ID]['view.posisi.stok'];
    if ($data['product_code'] != $product_code) return;
    
    if (is_array($data['lines'])) {
        $n = 0;
        foreach ($data['lines'] as $k => $d) {
            $n++;
            $data['lines'][$k]['line'] = $n;
        }
    }
    $_SESSION[$APP_ID]['view.posisi.stok'] = $data;

    require_once 'Structures/DataGrid.php';
    require_once 'HTML/Table.php';
    
    $datagrid = new Structures_DataGrid(9999);
    $datagrid->bind($data['lines'], array(), 'Array');
    $cgx_table = new HTML_Table($cgx_TableAttribs);
    $cgx_tableHeader = & $cgx_table->getHeader();
    $cgx_tableBody = & $cgx_table->getBody();
    
    $datagrid->addColumn(new Structures_DataGrid_Column('Organisasi', 'organization', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Item Number', 'product_name', NULL, array('align' => 'left'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Balance Date', 'balance_date', NULL, array('align' => 'center'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Prev Quantity', 'prev_quantity', NULL, array('align' => 'right'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('In Quantity', 'in_quantity', NULL, array('align' => 'right'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Out Quantity', 'out_quantity', NULL, array('align' => 'right'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Balance Quantity', 'balance_quantity', NULL, array('align' => 'right'), NULL, NULL));
    $datagrid->addColumn(new Structures_DataGrid_Column('Lates', 'latest', NULL, array('align' => 'center'), NULL, NULL));

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
