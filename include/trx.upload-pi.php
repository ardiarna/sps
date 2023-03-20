<?php

/*
 * Upload SO
 * Azwari Nugraha <nugraha@pt-gai.org>
 * Oct 31, 2013 8:49:07 AM
 */

echo "<div class='title'>Upload Physical Inventory...</div>";

echo "<div style='background: #eeeeee; padding: 18px; font-size: 13px;'>";

if ($_SESSION[$APP_ID]['upload-pi']['error']) {
    echo "<div style='color: red;'>";
    echo "<b>Error:</b><br/>";
    echo $_SESSION[$APP_ID]['upload-pi']['error'];
    echo "<br/><br/>";
    echo "</div>";
}

if ($_REQUEST['action'] == 'go') {
    echo "<div align='center' style='padding: 100px 0px;'>";
    echo "<img src='images/ajax-loader.gif'>";
    echo "<div style='padding-top: 20px; color: #666;'>Loading data, silahkan tunggu...</div>";
    echo "</div>";
    echo "<script type='text/javascript'>";
    echo "window.location = 'action/trx.upload-pi.php?step=4';";
    echo "</script>";
} elseif ($_SESSION[$APP_ID]['upload-pi']['step'] == 4) {
    echo "<div style='margin-bottom: 10px;'>Status upload file <b>{$_SESSION[$APP_ID]['upload-pi']['name']}</b> sheet <b>{$_SESSION[$APP_ID]['upload-pi']['sheetname']}</b></div>";
    
    if (is_array($_SESSION[$APP_ID]['upload-pi']['data'])) {
        foreach ($_SESSION[$APP_ID]['upload-pi']['data'] as $pi_id => $pi_data) {
            echo "<table width='100%' border='0'><tr>";
            echo "<td width='10%' align='center'>{$pi_id}</td>";
            if ($pi_data['exec-status']) {
                echo "<td style='color: red;'>{$pi_data['exec-status']}</td>";
            } else {
                echo "<td style='color: darkgreen;'>Berhasil</td>";
            }
            echo "</tr></table>";
        }
    }
    
    unset($_SESSION[$APP_ID]['upload-pi']);
    
} elseif ($_SESSION[$APP_ID]['upload-pi']['step'] == 3) {
    echo "<div style='margin-bottom: 10px;'>Data dari file <b>{$_SESSION[$APP_ID]['upload-pi']['name']}</b> sheet <b>{$_SESSION[$APP_ID]['upload-pi']['sheetname']}</b></div>";

    if (is_array($_SESSION[$APP_ID]['upload-pi']['data'])) {
        foreach ($_SESSION[$APP_ID]['upload-pi']['data'] as $pi_id => $pi_data) {
            echo "<div style='padding: 2px; background: #7CA6D9; color: #fff;'>";
            echo "<table width='100%' border='0'><tr>";
            echo "<td width='8%'>{$pi_id}</td>";
            echo "<td width='20%' align='center'>Date: " . date($APP_DATE_FORMAT, strtotime($pi_data['pi-date'])) . "</td>";
            echo "<td width='15%'>{$pi_data['customer']}</td>";
            echo "</tr></table>";
            echo "</div>";
            
            echo "<div style='padding: 2px; background: #B5CCEA; margin: 0px 0px 10px 0px;'>";
            echo "<table width='100%' border='0' cellspacing='1'>";
            echo "<tr style='color: #444;'>";
            echo "<th>Line</th>";
            echo "<th>Item Number</th>";
            echo "<th>Spec</th>";
            echo "<th>OD</th>";
            echo "<th>Thicknessmm</th>";
            echo "<th>Length</th>";
            echo "<th>Quantity</th>";
            echo "<th>Warehouse Code</th>";
            echo "<th>No Coil</th>";
            echo "<th>Lot Number</th>";
            echo "<th>Remark</th>";
            echo "<th>No Box</th>";
            echo "<th>Isi Box</th>";
            echo "</tr>";
            foreach ($pi_data['line'] as $line_id => $d) {
                echo "<tr style='background: #fff; color: #444;'>";
                echo "<td align='right' style='padding: 2px 8px;'>" . ($line_id + 1) . "</td>";
                echo "<td align='left' style='padding: 2px 8px;'>{$d[0]}</td>";
                echo "<td align='left' style='padding: 2px 8px;'>{$d[1]}</td>";
                echo "<td align='right' style='padding: 2px 8px;'>" . number_format($d[2], 2) . "</td>";
                echo "<td align='right' style='padding: 2px 8px;'>" . number_format($d[3], 2) . "</td>";
                echo "<td align='right' style='padding: 2px 8px;'>" . number_format($d[4], 2) . "</td>";
                echo "<td align='right' style='padding: 2px 8px;'>" . number_format($d[5], 2) . "</td>";
                echo "<td align='left' style='padding: 2px 8px;'>{$d[6]}</td>";
                echo "<td align='left' style='padding: 2px 8px;'>{$d[7]}</td>";
                echo "<td align='left' style='padding: 2px 8px;'>{$d[8]}</td>";
                echo "<td align='left' style='padding: 2px 8px;'>{$d[9]}</td>";
                echo "<td align='left' style='padding: 2px 8px;'>{$d[10]}</td>";
                echo "<td align='left' style='padding: 2px 8px;'>{$d[11]}</td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "</div>";
        }
    }
    
    echo "<br/>";
    echo "<div align='center' style='background: #fff;'>";
    echo "<input style='margin: 8px;' type='button' value='Pilih Sheet Lain' onclick=\"window.location = 'action/trx.upload-pi.php?step=2&mode=reselect';\">";
    echo "<input style='margin: 8px;' type='button' value='Ulangi Upload File' onclick=\"window.location = 'action/trx.upload-pi.php?step=reset';\">";
    echo "<input style='margin: 8px;' type='button' value='Simpan Ke Database' onclick=\"window.location = 'module.php?m=trx.upload-pi&action=go';\">";
    echo "<input style='margin: 8px;' type='button' value='Batalkan' onclick=\"window.location = 'action/trx.upload-pi.php?step=cancel';\">";
    echo "</div>";
} elseif ($_SESSION[$APP_ID]['upload-pi']['step'] == 2) {
    echo "Pilih Sheet dari file <b>{$_SESSION[$APP_ID]['upload-pi']['name']}</b>";
    echo "<ul>";
    foreach ($_SESSION[$APP_ID]['upload-pi']['sheets'] as $k => $s) {
        echo "<li><a href='action/trx.upload-pi.php?step=2&sheet={$k}'>{$s}</a></li>";
    }
    echo "</ul>";
    echo "<div align='center' style='background: #fff;'>";
    echo "<input style='margin: 8px;' type='button' value='Ulangi Upload File' onclick=\"window.location = 'action/trx.upload-pi.php?step=reset';\">";
    echo "</div>";
} else {
    echo "<div style='padding: 4px; background: #fff;'>";
    echo "<form action='action/trx.upload-pi.php' enctype='multipart/form-data' method='POST'>";
    echo "<input type='hidden' name='step' value='1'>";
    echo "<table border='0' cellspacing='2' cellpadding='2'>";
    echo "<tr><td><b>Organisasi</b></td>";
    echo "<td>". cgx_form_select('app_org_id', "SELECT app_org_id, organization FROM app_org", $data['app_org_id'], FALSE, "id='app_org_id'") ."</td></tr>";
    echo "<tr><td><b>File PI (.XLS)</b></td>";
    echo "<td><input type='file' name='pi' accept='application/vnd.ms-excel'></td></tr>";
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
    <li>Physical Inventory ID</li>
    <li>Date</li>
    <li>customer</li>
    <li>Spec</li>
    <li>OD</li>
    <li>Thickness</li>
    <li>Length</li>
    <li>Quantity</li>
    <li>Warehouse Code</li>
    <li>No Coil</li>
    <li>Lot Number</li>
    </ol>
ENDINFO;
}

echo "</div>";

?>