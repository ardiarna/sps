<?php

/*
 * Upload SO
 * Azwari Nugraha <nugraha@pt-gai.org>
 * Oct 31, 2013 8:49:07 AM
 */

echo "<div class='title'>Upload Stock Coil SLit</div>";

echo "<div style='background: #eeeeee; padding: 18px; font-size: 13px;'>";

if ($_SESSION[$APP_ID]['upload-data']['error']) {
    echo "<div style='color: red;'>";
    echo "<b>Error:</b><br/>";
    echo $_SESSION[$APP_ID]['upload-data']['error'];
    echo "<br/><br/>";
    echo "</div>";
}

if ($_REQUEST['action'] == 'go') {
    echo "<div align='center' style='padding: 100px 0px;'>";
    echo "<img src='images/ajax-loader.gif'>";
    echo "<div style='padding-top: 20px; color: #666;'>Loading data, silahkan tunggu...</div>";
    echo "</div>";
    echo "<script type='text/javascript'>";
    echo "window.location = 'action/trx.upload-data.php?step=4';";
    echo "</script>";
} elseif ($_SESSION[$APP_ID]['upload-data']['step'] == 4) {
    echo "<div style='margin-bottom: 10px;'>Status upload file <b>{$_SESSION[$APP_ID]['upload-data']['name']}</b> sheet <b>{$_SESSION[$APP_ID]['upload-data']['sheetname']}</b></div>";
    
    if (is_array($_SESSION[$APP_ID]['upload-data']['data'])) {
        foreach ($_SESSION[$APP_ID]['upload-data']['data'] as $pi_id => $pi_data) {
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
    
    unset($_SESSION[$APP_ID]['upload-data']);
    
} elseif ($_SESSION[$APP_ID]['upload-data']['step'] == 3) {
    echo "<div style='margin-bottom: 10px;'>Data dari file <b>{$_SESSION[$APP_ID]['upload-data']['name']}</b> sheet <b>{$_SESSION[$APP_ID]['upload-data']['sheetname']}</b></div>";

    if (is_array($_SESSION[$APP_ID]['upload-data']['data'])) {
        foreach ($_SESSION[$APP_ID]['upload-data']['data'] as $pi_id => $pi_data) {
            echo "<div style='padding: 2px; background: #7CA6D9; color: #fff;'>";
            echo "<table width='100%' border='0'><tr>";
            echo "<td width='8%'>{$pi_id}</td>";
            echo "</tr></table>";
            echo "</div>";
            
            echo "<div style='padding: 2px; background: #B5CCEA; margin: 0px 0px 10px 0px;'>";
            echo "<table width='100%' border='0' cellspacing='1'>";
            echo "<tr style='color: #444;'>";
            echo "<th width='5%'>LINE</th>";
            echo "<th>FIL BANTU</th>";
            echo "<th>SPEC</th>";
            echo "<th>TEBAL</th>";
            echo "<th>LEBAR</th>";
            echo "<th>NO COIL</th>";
            echo "<th>KODE COIL</th>";
            echo "<th>BERAT PER SLIT</th>";
            echo "<th>JUMLAH SLIT</th>";
            echo "</tr>";
            foreach ($pi_data['line'] as $line_id => $d) {
                echo "<tr style='background: #fff; color: #444;'>";
                echo "<td align='right' style='padding: 2px 8px;'>" . ($line_id + 1) . "</td>";
                echo "<td align='left' style='padding: 2px 8px;'>{$d[0]}</td>";
                echo "<td align='left' style='padding: 2px 8px;'>{$d[1]}</td>";
                echo "<td align='right' style='padding: 2px 8px;'>" . number_format($d[2], 2) . "</td>";
                echo "<td align='right' style='padding: 2px 8px;'>" . number_format($d[3], 2) . "</td>";
                echo "<td align='left' style='padding: 2px 8px;'>{$d[4]}</td>";
                echo "<td align='left' style='padding: 2px 8px;'>{$d[5]}</td>";
                echo "<td align='right' style='padding: 2px 8px;'>" . number_format($d[6], 2) . "</td>";
                echo "<td align='right' style='padding: 2px 8px;'>" . number_format($d[7], 2) . "</td>";
                
                echo "</tr>";
            }
            echo "</table>";
            echo "</div>";
        }
    }
    
    echo "<br/>";
    echo "<div align='center' style='background: #fff;'>";
    echo "<input style='margin: 8px;' type='button' value='Pilih Sheet Lain' onclick=\"window.location = 'action/trx.upload-data.php?step=2&mode=reselect';\">";
    echo "<input style='margin: 8px;' type='button' value='Ulangi Upload File' onclick=\"window.location = 'action/trx.upload-data.php?step=reset';\">";
    echo "<input style='margin: 8px;' type='button' value='Simpan Ke Database' onclick=\"window.location = 'module.php?m=trx.upload-data&action=go';\">";
    echo "<input style='margin: 8px;' type='button' value='Batalkan' onclick=\"window.location = 'action/trx.upload-data.php?step=cancel';\">";
    echo "</div>";
} elseif ($_SESSION[$APP_ID]['upload-data']['step'] == 2) {
    echo "Pilih Sheet dari file <b>{$_SESSION[$APP_ID]['upload-data']['name']}</b>";
    echo "<ul>";
    foreach ($_SESSION[$APP_ID]['upload-data']['sheets'] as $k => $s) {
        echo "<li><a href='action/trx.upload-data.php?step=2&sheet={$k}'>{$s}</a></li>";
    }
    echo "</ul>";
    echo "<div align='center' style='background: #fff;'>";
    echo "<input style='margin: 8px;' type='button' value='Ulangi Upload File' onclick=\"window.location = 'action/trx.upload-data.php?step=reset';\">";
    echo "</div>";
} else {
    echo "<div style='padding: 4px; background: #fff;'>";
    echo "<form action='action/trx.upload-data.php' enctype='multipart/form-data' method='POST'>";
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
    <li>A</li>
    <li>FIL BANTU</li>
    <li>SPEC</li>
    <li>TEBAL</li>
    <li>LEBAR</li>
    <li>NO COIL</li>
    <li>KODE COIL</li>
    <li>BERAT PER SLIT</li>
    <li>JUMLAH SLIT</li>
    </ol>
ENDINFO;
}

echo "</div>";

?>