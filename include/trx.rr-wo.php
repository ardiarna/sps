<?php

/**
 * (c) Azwari Nugraha <nugraha@pt-gai.com>
 * 25/11/2013 00:52:21
 */


echo "<div class='title'>Penerimaan Barang</div>";

echo "<div class='data_box'>";
echo "<table>";
echo "<tr>";
echo "<td style='padding-right: 20px;'>Penerimaan Barang dari : </td>";
echo "<td colspan='2'><input type='button' value='Barang Jadi' style='width: 120px;' onclick=\"window.location='module.php?m=trx.rr'\"></td>";
echo "</tr>";
echo "<tr>";
echo "<td>&nbsp;</td>";
echo "<td colspan='2'><input type='button' value='Bahan Baku' style='width: 120px;' onclick=\"window.location='module.php?m=trx.rri'\"></td>";
echo "</tr>";
echo "<tr>";
echo "<td colspan='3'><hr noshade size='1'></td>";
echo "</tr>";
echo "</table>";
echo "</div>";

?>