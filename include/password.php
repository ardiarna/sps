<?php

/*
 * password
 * Azwari Nugraha <nugraha@duabelas.org>
 * Jan 7, 2014 11:38:26 PM
 */

echo "<div class='title'>Ubah Password</div>";
$cgx_data = cgx_fetch_table("SELECT * FROM app_user WHERE app_user.user_id = '" . user() . "'");

echo "<form id='password' name='password'>";
echo "<fieldset>";
echo "<legend>Pengguna</legend>";
echo "<table width='100%'>";
echo "<tr>";
echo "<td width='15%'><label for='data_user_id'>ID</label></td>";
echo "<td width='30%'><input id='data_user_id' name='data[user_id]' type='text' value=\"{$cgx_data['user_id']}\" size='8' maxlength='8' style='text-align: right;' disabled /></td>";
echo "<td width='10%'></td>";
echo "<td width='15%'></td>";
echo "<td width='30%'></td>";
echo "</tr>";
echo "<tr>";
echo "<td><label for='data_user_name'>Nama Login</label></td>";
echo "<td><input disabled id='data_user_name' name='data[user_name]' type='text' value=\"{$cgx_data['user_name']}\" size='16' maxlength='16' style='text-align: left;' /></td>";
echo "<td></td>";
echo "<td><label for='data_user_email'>Email</label></td>";
echo "<td><input disabled id='data_user_email' name='data[user_email]' type='text' value=\"{$cgx_data['user_email']}\" size='20' maxlength='50' style='text-align: left;' /></td>";
echo "</tr>";
echo "<tr>";
echo "<td><label for='data_user_fullname'>Nama Lengkap</label></td>";
echo "<td><input id='data_user_fullname' name='name' type='text' value=\"{$cgx_data['user_fullname']}\" size='20' maxlength='25' style='text-align: left;' /></td>";
echo "<td></td>";
echo "<td><label for='data_user_active'>Aktif</label></td>";
echo "<td>" . cgx_form_select('data[user_active]', array('Y' => 'Ya', 'N' => 'Tidak'), $cgx_data['user_active'], FALSE, "id='data_user_active' disabled") . "</td>";
echo "</tr>";
echo "</table>";
echo "</fieldset>";

echo "<fieldset>";
echo "<legend>Password</legend>";
echo "<table width='100%'>";
echo "<tr>";
echo "<td colspan='2'><table><tr><td><input onclick=\"document.getElementById('password1').disabled = !this.checked; document.getElementById('password2').disabled = !this.checked; document.getElementById('password3').disabled = !this.checked;\" type='checkbox' name='reset-password' id='reset-passsword'></td><td><label for='reset-passsword'>Reset Password</label></td></tr></table></td>";
echo "</tr>";
echo "<tr>";
echo "<td width='15%'>Password Lama</td>";
echo "<td><input id='password1' name='password1' type='password' size='10' disabled></td>";
echo "</tr>";
echo "<tr>";
echo "<td>Password Baru</td>";
echo "<td><input id='password2' name='password2' type='password' size='10' disabled></td>";
echo "</tr>";
echo "<tr>";
echo "<td>Ulangi Password Baru</td>";
echo "<td><input id='password3' name='password3' type='password' size='10' disabled></td>";
echo "</tr>";
echo "</table>";
echo "</fieldset>";

echo "<input type='button' value='Simpan' onclick=\"xajax_changePassword(xajax.getFormValues('password'));\">\n";
echo "</form>\n";

?>