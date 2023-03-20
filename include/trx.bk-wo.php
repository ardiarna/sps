<?php

/**
 * (c) Azwari Nugraha <nugraha@pt-gai.com>
 * 25/11/2013 00:52:21
 */


echo "<div class='title'>Pengiriman Barang</div>";

echo "<div class='data_box'>";
echo "<table>";
echo "<tr>";
echo "<td style='padding-right: 20px;'>Pengiriman Barang Ke : </td>";
echo "<td colspan='2'><input type='button' value='Customer' style='width: 120px;' onclick=\"window.location='module.php?m=trx.bk'\"></td>";
echo "</tr>";
if (org()==4){
	echo "<tr>";
	echo "<td>&nbsp;</td>";
	echo "<td colspan='2'><input type='button' value='Recutting FG' style='width: 120px;' onclick=\"window.location='module.php?m=trx.bki&t=3'\"></td>";
	echo "</tr>";
}
if (org()==5 or org()==6){
echo "<tr>";
echo "<td>&nbsp;</td>";
echo "<td colspan='2'><input type='button' value='Recutting LP' style='width: 120px;' onclick=\"window.location='module.php?m=trx.bki&t=4'\"></td>";
echo "</tr>";
}
//echo "<tr>";
//echo "<td>&nbsp;</td>";
//echo "<td colspan='2'><input type='button' value='ERW' style='width: 120px;' onclick=\"window.location='module.php?m=trx.bki&t=5'\"></td>";
//echo "</tr>";
//echo "<tr>";
//echo "<td>&nbsp;</td>";
//echo "<td colspan='2'><input type='button' value='DRW' style='width: 120px;' onclick=\"window.location='module.php?m=trx.bki&t=6'\"></td>";
//echo "</tr>";
echo "<tr>";
echo "<td colspan='3'><hr noshade size='1'></td>";
echo "</tr>";
echo "</table>";
echo "</div>";

?>