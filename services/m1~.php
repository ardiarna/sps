<?php

$skip_ajax = TRUE;
require_once '../init.php';

$menu = array(
    array(
        'id'    => 'dashboard',
        'text'  => 'Home',
        'image' => array('home.png')
    ),
    array(
        'id'        => 'sysadmin',
        'text'      => 'System Administration',
        'open'      => FALSE,
        'priv'      => 'user',
        'sub'       => array(
            array(
                'id'    => 'user',
                'text'  => 'Pengaturan Pengguna',
                'priv'  => 'user',
                'image' => array('master-data.png')
            ),
            array(
                'id'    => 'role',
                'text'  => 'Konfigurasi Role',
                'priv'  => 'role',
                'image' => array('master-data.png')
            )
        )
    ),
    array(
        'id'        => 'master',
        'text'      => 'Master Data',
        'open'      => FALSE,
        'sub'       => array(
            array(
                'id'    => 'master.product',
                'text'  => 'Master Produk',
                'priv'  => 'master.product',
                'image' => array('master-data.png')
            ),
            array(
                'id'    => 'master.box',
                'text'  => 'Master Box',
                'priv'  => 'master.box',
                'image' => array('master-data.png')
            ),
            array(
                'id'    => 'master.warehouse',
                'text'  => 'Master Gudang',
                'priv'  => 'master.wh',
                'image' => array('master-data.png')
            ),
            array(
                'id'    => 'master.mitra',
                'text'  => 'Customer',
                'priv'  => 'master.mitra',
                'image' => array('master-data.png')
            )
        )
    ),
    array(
        'id'    => 'trx',
        'text'  => 'Transaksi',
        'open'  => TRUE,
//        'priv'  => 'trx',
        'sub'   => array(
            array(
                'id'    => 'forecast',
                'text'  => 'Forecast',
                'priv'  => 'forecast',
				'image' => array('application.png')
            ),
            array(
                'id'    => 'trx.so',
                'text'  => 'Sales Order',
                'priv'  => 'trx.so',
				'image' => array('application.png')
            ),
            array(
                'id'    => 'trx.upload-so',
                'text'  => 'Upload Sales Order',
                'priv'  => 'trx.so-import',
				'image' => array('application.png')
            ),
            array(
                'id'    => 'trx.rr',
                'text'  => 'Penerimaan Barang',
                'priv'  => 'trx.barang-masuk',
				'image' => array('application.png')
            ),
            array(
                'id'    => 'trx.bk',
                'text'  => 'Pengiriman Barang',
                'priv'  => 'trx.barang-keluar',
				'image' => array('application.png')
            ),
            array(
                'id'    => 'trx.rm',
                'text'  => 'Koreksi Penerimaan Barang',
                'priv'  => 'trx.barang-masuk-koreksi',
                'image' => array('application.png')
            ),
            array(
                'id'    => 'trx.rk',
                'text'  => 'Return Barang',
                'priv'  => 'trx.return',
                'image' => array('application.png')
            ),
            array(
                'id'    => 'trx.xm',
                'text'  => 'Box Masuk',
                'priv'  => 'trx.box-masuk',
                'image' => array('application.png')
            ),
            array(
                'id'    => 'trx.xk',
                'text'  => 'Box Keluar',
                'priv'  => 'trx.box-keluar',
                'image' => array('application.png')
            )
        )
    ),
    array(
        'id'    => 'view',
        'text'  => 'Preview Output',
        'open'  => TRUE,
        'sub'   => array(
            array(
                'id'    => 'view.psb',
                'text'  => 'Product Stock Balance',
                'priv'  => 'view.stock-balance',
                'image' => array('application.png')
            ),
            array(
                'id'    => 'view.sb',
                'text'  => 'Kartu Stock',
                'priv'  => 'view.stock-card',
                'image' => array('application.png')
            ),
            array(
                'id'    => 'view.so',
                'text'  => 'Sales Order Detail',
                'priv'  => 'view.so-detail',
                'image' => array('application.png')
            ),
            array(
                'id'    => 'view.oso',
                'text'  => 'Outstanding Sales Order',
                'priv'  => 'view.so-outstanding',
                'image' => array('application.png')
            ),
            array(
                'id'    => 'view.rr',
                'text'  => 'Penerimaan Barang',
                'priv'  => 'view.barang-masuk',
                'image' => array('application.png')
            ),
            array(
                'id'    => 'view.sh',
                'text'  => 'Pengiriman Barang',
                'priv'  => 'view.barang-keluar',
                'image' => array('application.png')
            ),
            array(
                'id'    => 'view.box',
                'text'  => 'Posisi Box',
                'priv'  => 'view.box',
                'image' => array('application.png')
            )
        )
    ),
    array(
        'id'    => 'rpt',
        'text'  => 'Laporan',
        'open'  => TRUE,
//        'priv'  => 'report',
        'sub'   => array(
            array(
                'id'    => 'rpt.so',
                'text'  => 'Sales Order',
                'priv'  => 'rpt.so',
				'image' => array('report.png')
            ),
            array(
                'id'    => 'rpt.bm',
                'text'  => 'Barang Masuk',
                'priv'  => 'rpt.barang-masuk',
				'image' => array('report.png')
            ),
            array(
                'id'    => 'rpt.bk',
                'text'  => 'Barang Keluar',
                'priv'  => 'rpt.barang-keluar',
				'image' => array('report.png')
            ),
            array(
                'id'    => 'rpt.os',
                'text'  => 'Outstanding Sales Order',
                'priv'  => 'rpt.so-outstanding',
                'image' => array('report.png')
            ),
            array(
                'id'    => 'rpt.oh',
                'text'  => 'Stock On Hand',
                'priv'  => 'rpt.stock',
                'image' => array('report.png')
            )
        )
    ),
    array(
        'id'    => 'logout',
        'text'  => 'Logout',
        'image' => array('logout.png')
    )
);

function printRecursive($menu, $parent = NULL, $level = 1, $base_sort) {
    if (!is_array($menu)) return;
    foreach ($menu as $menu0) {
        if ($level == 1) {
            $base_sort += 1000;
        } else {
            $base_sort += 10;
        }
        if (sizeof($menu0['sub']) > 0) {
            echo "INSERT INTO app_menu (app_menu_id, parent_menu_id, app_priv_id, title, image_0, sort_order) " .
                    "VALUES ('{$menu0['id']}', '{$parent}', '{$menu0['priv']}', '{$menu0['text']}', '{$menu0['image'][0]}', '{$base_sort}');";
            echo "\n";
            printRecursive($menu0['sub'], $menu0['id'], $level + 1, $base_sort);
        } else {
            echo "INSERT INTO app_menu (app_menu_id, parent_menu_id, app_priv_id, title, image_0, sort_order) " .
                    "VALUES ('{$menu0['id']}', '{$parent}', '{$menu0['priv']}', '{$menu0['text']}', '{$menu0['image'][0]}', '{$base_sort}');";
            echo "\n";
        }
    }
}

echo "<pre>";
printRecursive($menu);

?>