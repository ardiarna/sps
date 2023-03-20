<?php

/*
 * Upload po
 * Azwari Nugraha <nugraha@pt-gai.org>
 * Oct 31, 2013 8:49:07 AM
 */

echo "<div class='title'>Upload Purchase Order</div>";

echo "<div style='background: #eeeeee; padding: 18px; font-size: 13px;'>";

if ($_SESSION[$APP_ID]['upload-po']['error']) {
    echo "<div style='color: red;'>";
    echo "<b>Error:</b><br/>";
    echo $_SESSION[$APP_ID]['upload-po']['error'];
    echo "<br/><br/>";
    echo "</div>";
}

if ($_REQUEST['action'] == 'go') {
    echo "<div align='center' style='padding: 100px 0px;'>";
    echo "<img src='images/ajax-loader.gif'>";
    echo "<div style='padding-top: 20px; color: #666;'>Loading data, silahkan tunggu...</div>";
    echo "</div>";
    echo "<script type='text/javascript'>";
    echo "window.location = 'action/trx.upload-po.php?step=4';";
    echo "</script>";
} elseif ($_SESSION[$APP_ID]['upload-po']['step'] == 4) {
    echo "<div style='margin-bottom: 10px;'>Status upload file <b>{$_SESSION[$APP_ID]['upload-po']['name']}</b> sheet <b>{$_SESSION[$APP_ID]['upload-po']['sheetname']}</b></div>";
    
    if (is_array($_SESSION[$APP_ID]['upload-po']['data'])) {
        foreach ($_SESSION[$APP_ID]['upload-po']['data'] as $po_id => $po_data) {
            echo "<table width='100%' border='0'><tr>";
            echo "<td width='10%' align='center'>{$po_id}</td>";
            if ($po_data['exec-status']) {
                echo "<td style='color: red;'>{$po_data['exec-status']}</td>";
            } else {
                echo "<td style='color: darkgreen;'>Berhasil</td>";
            }
            echo "</tr></table>";
        }
    }
    
    unset($_SESSION[$APP_ID]['upload-po']);
    
} elseif ($_SESSION[$APP_ID]['upload-po']['step'] == 3) {
    echo "<div style='margin-bottom: 10px;'>Data dari file <b>{$_SESSION[$APP_ID]['upload-po']['name']}</b> sheet <b>{$_SESSION[$APP_ID]['upload-po']['sheetname']}</b></div>";

    if (is_array($_SESSION[$APP_ID]['upload-po']['data'])) {
        foreach ($_SESSION[$APP_ID]['upload-po']['data'] as $po_id => $po_data) {
            echo "<div style='padding: 2px; background: #7CA6D9; color: #fff;'>";
            echo "<table width='100%' border='0'><tr>";
            echo "<td width='8%'>{$po_id}</td>";
            echo "<td width='30%'>{$po_data['company-code']} {$po_data['company']}</td>";
            echo "<td width='20%' align='center'>Order Date: " . date($APP_DATE_FORMAT, strtotime($po_data['order-date'])) . "</td>";
            echo "<td width='20%'>No. PPB: {$po_data['po_number']}</td>";
            echo "<td align='right'>{$po_data['remark']}</td>";
            echo "</tr></table>";
            echo "</div>";
            
            echo "<div style='padding: 2px; background: #B5CCEA; margin: 0px 0px 10px 0px;'>";
            echo "<table width='100%' border='0' cellspacing='1'>";
            echo "<tr style='color: #444;'>";
            echo "<th width='5%'>Line</th>";
            echo "<th width='12%'>Scheduled Date</th>";
            echo "<th width='10%'>Quantity</th>";
            echo "<th width='14%'>Spec</th>";
            echo "<th width='14%'>Item No</th>";
            echo "<th width='8%'>OD</th>";
            echo "<th width='8%'>Tebal</th>";
            echo "<th width='8%'>Description</th>";
            echo "<th>Description 2</th>";
            echo "</tr>";
            foreach ($po_data['line'] as $line_id => $d) {
                echo "<tr style='background: #fff; color: #444;'>";
                echo "<td align='right' style='padding: 2px 8px;'>" . ($line_id + 1) . "</td>";
                echo "<td align='center' style='padding: 2px 8px;'>" . date($APP_DATE_FORMAT, strtotime($d[0])) . "</td>";
                echo "<td align='right' style='padding: 2px 8px;'>" . number_format($d[1], 1) . "</td>";
                echo "<td style='padding: 2px 8px;'>{$d[2]}</td>";
                echo "<td align='left'>{$d[3]}</td>";
                echo "<td align='right' style='padding: 2px 8px;'>" . number_format($d[4], 2) . "</td>";
                echo "<td align='right' style='padding: 2px 8px;'>" . number_format($d[5], 3) . "</td>";
                echo "<td align='right' style='padding: 2px 8px;'>" . number_format($d[6], 2) . "</td>";
                echo "<td style='padding: 2px 8px;'>{$d[7]}</td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "</div>";
        }
    }
    
    echo "<br/>";
    echo "<div align='center' style='background: #fff;'>";
    echo "<input style='margin: 8px;' type='button' value='Pilih Sheet Lain' onclick=\"window.location = 'action/trx.upload-po.php?step=2&mode=reselect';\">";
    echo "<input style='margin: 8px;' type='button' value='Ulangi Upload File' onclick=\"window.location = 'action/trx.upload-po.php?step=reset';\">";
    echo "<input style='margin: 8px;' type='button' value='Simpan Ke Database' onclick=\"window.location = 'module.php?m=trx.upload-po&action=go';\">";
    echo "<input style='margin: 8px;' type='button' value='Batalkan' onclick=\"window.location = 'action/trx.upload-po.php?step=cancel';\">";
    echo "</div>";
} elseif ($_SESSION[$APP_ID]['upload-po']['step'] == 2) {
    echo "Pilih Sheet dari file <b>{$_SESSION[$APP_ID]['upload-po']['name']}</b>";
    echo "<ul>";
    foreach ($_SESSION[$APP_ID]['upload-po']['sheets'] as $k => $s) {
        echo "<li><a href='action/trx.upload-po.php?step=2&sheet={$k}'>{$s}</a></li>";
    }
    echo "</ul>";
    echo "<div align='center' style='background: #fff;'>";
    echo "<input style='margin: 8px;' type='button' value='Ulangi Upload File' onclick=\"window.location = 'action/trx.upload-po.php?step=reset';\">";
    echo "</div>";
} else {
    echo "<div style='padding: 4px; background: #fff;'>";
    echo "<form action='action/trx.upload-po.php' enctype='multipart/form-data' method='POST'>";
    echo "<input type='hidden' name='step' value='1'>";
    echo "<table border='0' cellspacing='2' cellpadding='2'>";
    echo "<tr><td><b>Organisasi</b></td>";
    echo "<td>". cgx_form_select('app_org_id', "SELECT app_org_id, organization FROM app_org", $data['app_org_id'], FALSE, "id='app_org_id'") ."</td></tr>";
    echo "<tr><td><b>File po (.XLS)</b></td>";
    echo "<td><input type='file' name='po' accept='application/vnd.ms-excel'></td></tr>";
    echo "<tr><td colspan='2'>&nbsp;</td></tr>";
    echo "<tr><td></td>";
    echo "<td><input type='submit' value=' Upload '></td></tr>";
    echo "</table>";
    echo "</form>";
    echo "</div>";

    echo <<< ENDINFO
    <br/><br/>
    File yang di-upload adalah file dengan format XLS (Microsoft Excel)
    dengan susunan kolom sebagai berikut:
    <ol>
    <li>Purchase Order</li>
    <li>Contract No</li>
    <li>No. PBB</li>
    <li>Order Date</li>
    <li>Partner Code</li>
    <li>Partner Name</li>
    <li>Scheduled Date</li>
    <li>Quantity Order</li>
    <li>Spec</li>
    <li>Item Number</li>
    <li>Lebar</li>
    <li>Tebal</li>
    <li>Description</li>
    <li>Description 2</li>
    </ol>
ENDINFO;
}

echo "</div>";

?>