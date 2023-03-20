<?php

/**
 * Dikembangkan oleh
 * PT. GLOBAL ANUGERAH INDONESIA
 * Azwari Nugraha <nugraha@pt-gai.org>
 * 03/01/2014 20:12:20
 */


echo "<div class='title'>Buat Produk Coil Slitting</div>";
    $cgx_id = $_REQUEST['id'];
    /*
    $cgx_data = cgx_fetch_table("SELECT m_product.*, organization, partner_name FROM m_product JOIN app_org USING (app_org_id)
        LEFT JOIN c_bpartner USING (c_bpartner_id) WHERE m_product.m_product_id = '{$cgx_id}'");
    */
     $cgx_data = cgx_fetch_table("
         SELECT 
         m_product.*, 
         organization 
         
        FROM 
        m_product JOIN app_org USING (app_org_id)
             
        WHERE 
        m_product.m_product_id = '{$cgx_id}'");
       
        
    echo "<form action='action/form.master.product_c.php' method='post'>\n";
    echo "<input type='hidden' name='backvar' value='" . urlencode("module.php?&m={$_REQUEST['m']}") . "'>\n";
    echo "<input type='hidden' name='mode' value='new'>\n";
    echo "<input type='hidden' name='data[app_org_id]' id='data_app_org_id' value='{$cgx_data['app_org_id']}'>";
    if ($_SESSION[$GLOBALS['APP_ID']]['form.master.product_c']['error']) {
        echo "<div class='error'>{$_SESSION[$GLOBALS['APP_ID']]['form.master.product_c']['error']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['form.master.product_c']['error']);
    }

    if ($_SESSION[$GLOBALS['APP_ID']]['form.master.product_c']['info']) {
        echo "<div class='info'>{$_SESSION[$GLOBALS['APP_ID']]['form.master.product_c']['info']}</div>";
        unset($_SESSION[$GLOBALS['APP_ID']]['form.master.product_c']['info']);
    }

    echo "<ul class='cgx_form'>\n";
    echo "        <input id='data_product_code' name='data[product_code]' type='hidden' value=\"{$cgx_data['product_code']}\" size='30' maxlength='40' style='text-align: left;' />\n";
    echo "        <input id='data_spec' name='data[spec]' type='hidden' value=\"{$cgx_data['spec']}\" size='10' maxlength='10' style='text-align: left;' />\n";
    echo "        <input id='data_thickness' name='data[thickness]' type='hidden' value=\"{$cgx_data['thickness']}\" size='16' maxlength='16' style='text-align: right;' />\n";
    echo "        <input id='data_description' name='data[description]' type='hidden' value=\"{$cgx_data['description1']}\" size='30' maxlength='100' style='text-align: left;' />\n";
    
    //========================================================================================
    /*
    echo "    <li class='even'>\n";
    echo "        <label class='cgx_form' for='data_product_code'>Item Number</label>\n";
    echo "        <input id='data_product_code' readonly='readonly' name='data[product_code]' type='text' value=\"{$cgx_data['product_code1']}\" size='30' maxlength='40' style='text-align: left;' />\n";
    echo "    </li>\n";
    
    */
    //========================================================================================
    echo "    <li class='odd'>\n";
    echo "        <label class='cgx_form' for='data_product_code'>Code</label>\n";
    echo      cgx_form_select('data[kode]', array('SH' => 'SH - Slit Hitam', 'SP' => 'SP - Slit Putih', 'SS'=> 'SS - Slit Stanless'),$cgx_data['kode'], false,  "id='data_kode' ");    
    echo "    </li>\n";
    //========================================================================================
    /*
    echo "    <li class='even'>\n";
    echo "        <label class='cgx_form' for='data_spec'>Spec</label>\n";
    echo "        <input id='data_spec' name='data[spec]' type='text' value=\"{$cgx_data['spec']}\" size='10' maxlength='10' style='text-align: left;' />\n";
    echo "    </li>\n";
    */
    //========================================================================================
    /*
    echo "    <li class='odd'>\n";
    echo "        <label class='cgx_form' for='data_thickness'>Thickness</label>\n";
    echo "        <input id='data_thickness' name='data[thickness]' type='text' value=\"{$cgx_data['thickness']}\" size='16' maxlength='16' style='text-align: right;' />\n";
    echo "    </li>\n";
    */
    //========================================================================================
    echo "    <li class='even'>\n";
    echo "        <label class='cgx_form' for='data_weight'>Width</label>\n";
    echo "        <input id='data_weight' name='data[od]' type='text' value=\"{$cgx_data['od']}\" size='16' maxlength='16' style='text-align: right;' />\n";
    echo "    </li>\n";
    //========================================================================================
    /*
    echo "    <li class='odd'>\n";
    echo "        <label class='cgx_form' for='data_product_name'>Nama Produk</label>\n";
    echo "        <input id='data_product_name' name='data[product_name]' type='text' value=\"{$cgx_data['product_name1']}\" size='30' maxlength='100' style='text-align: left;' />\n";
    echo "    </li>\n";
    */
    //========================================================================================
    /*
    echo "    <li class='even'>\n";
    echo "        <label class='cgx_form' for='data_description_2'>Description</label>\n";
    echo "        <input id='data_description_2' name='data[description_2]' type='text' value=\"{$cgx_data['description_21']}\" size='30' maxlength='20' style='text-align: left;' />\n";
    echo "    </li>\n";
    */
    //========================================================================================
    /*
    echo "    <li class='odd'>\n";
    echo "        <label class='cgx_form' for='data_description'>Description 2</label>\n";
    echo "        <input id='data_description' name='data[description]' type='text' value=\"{$cgx_data['description1']}\" size='30' maxlength='100' style='text-align: left;' />\n";
    echo "    </li>\n";
    */
    //======================================================================================    
    /*
    echo "    <li class='even'>\n";
    echo "        <label class='cgx_form' for='data_minimum_qty'>Minimum Quantity</label>\n";
    echo "        <input id='data_minimum_qty' name='data[minimum_qty]' type='text' value=\"{$cgx_data['minimum_qty']}\" size='20' maxlength='20' style='text-align: left;' />\n";
    echo "    </li>\n";
     
     */
    //=============================================================================================
    
    echo "    <li class='odd'>\n";
    echo "        <label class='cgx_form' for='data_active'>Status</label>\n";
    echo "          <input type='checkbox' name='data[purchase]' value='Y' " . ($cgx_data['purchase'] == 'Y' ? ' checked' : '') . "> Purchase\n";           
    echo "          <input type='checkbox' name='data[sale]' value='Y' ". ($cgx_data['sale'] == 'Y' ? ' checked' : '') . "> Sale\n";            
    echo "   </li>\n";     
         //echo "<br><br>";
    
    //========================================================================================
    /* 
    echo "    <li class='even'>\n";
    echo "        <label class='cgx_form' for='data_active'>Active</label>\n";
    echo cgx_form_select('data[active]', array('Y' => 'Ya', 'N' => 'Tidak'), $cgx_data['active'], FALSE, "id='data_active'");
    echo "    </li>\n";
    */
    echo "    <li class='even'>\n";
    echo "        <label class='cgx_form'></label>\n";
    echo "        <input type='submit' value='Simpan'>\n";
    echo "        <input type='button' value='Batal' onclick=\"window.close();\">\n";
    echo "    </li>\n";
    
         
    echo "</ul>\n";
    echo "</form>\n";
 
?>
<script>
function setBusinessPartner(id, name) {
    var txt_name = document.getElementById('partner_name');
    var hid_id = document.getElementById('c_bpartner_id');
    txt_name.value = name;
    hid_id.value = id;
}
</script>
