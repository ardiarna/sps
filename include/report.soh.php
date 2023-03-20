<?php

/**
 * (c) Azwari Nugraha <nugraha@pt-gai.com>
 * 22/10/2013 02:43:05
 */


echo "<div class='title'>Laporan Stock On Hand</div>";

    //require_once 'Structures/DataGrid.php';
    //require_once 'HTML/Table.php';

    include_once('/lib/tcpdf/tcpdf.php');
    include_once("/lib/PHPJasperXML.inc.php");
    $version="0.8d";
    $pgport=5432;
    $pchartfolder="./lib/pchart2";  

    $xml =  simplexml_load_file('./reports/irStockOnHand.jrxml');
    
    /*if($xml == FALSE){
        echo 'Gagal';
    }else{
        echo 'berhasil';
    }
    */

    $PHPJasperXML = new PHPJasperXML();
    //$PHPJasperXML->debugsql=FALSE;
    $PHPJasperXML->xml_dismantle($xml);

    $PHPJasperXML->transferDBtoArray($APP_DB_HOST,$APP_DB_USER,$APP_DB_PASSWORD,$APP_DB_NAME);
    $PHPJasperXML->outpage("I");    //page output method I:standard output  D:Download file

?>