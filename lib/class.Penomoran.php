<?php

/*
 * Class Penomoran
 * Azwari Nugraha <nugraha@duabelas.org>
 * Sep 20, 2013 3:58:35 PM
 */

class Penomoran {
    
    private $connection;
    
    public function __construct() {
        $this->connection = $GLOBALS['APP_CONNECTION'];
    }
    
    public function urut($id, $org_id) {
        if (empty($org_id)) $org_id = $_SESSION[$GLOBALS['APP_ID']]['role']['app_org_id'];
        if ($org_id == -1) {
            $rsx = mysql_query(
                "SELECT c_nomor.*, '' doc_no_prefix " .
                "FROM c_nomor " .
                "WHERE c_nomor_id = '{$id}'",
                $this->connection);
        } else {
            $rsx = mysql_query(
                "SELECT c_nomor.*, doc_no_prefix " .
                "FROM c_nomor " .
                "JOIN app_org USING (app_org_id) " .
                "WHERE c_nomor_id = '{$id}' AND app_org_id = '{$org_id}'",
                $this->connection);
        }
        if (!($data = mysql_fetch_array($rsx))) {
            mysql_query(
                "INSERT INTO c_nomor (c_nomor_id, app_org_id, nomor, step, reset_tiap_tahun, tahun_aktif, pad) "
                    . "VALUES ('{$id}', '{$org_id}', 0, 1, 'Y', " . date('Y') . ", 6)",
                    $this->connection);
            if ($org_id == -1) {
                $rsx = mysql_query(
                    "SELECT c_nomor.*, '' doc_no_prefix " .
                    "FROM c_nomor " .
                    "WHERE c_nomor_id = '{$id}'",
                    $this->connection);
            } else {
                $rsx = mysql_query(
                    "SELECT c_nomor.*, doc_no_prefix " .
                    "FROM c_nomor " .
                    "JOIN app_org USING (app_org_id) " .
                    "WHERE c_nomor_id = '{$id}' AND app_org_id = '{$org_id}'",
                    $this->connection);
            }
            $data = mysql_fetch_array($rsx);
        }
        mysql_free_result($rsx);
        
        $next = $data['nomor'] + $data['step'];
        mysql_query(
            "UPDATE c_nomor SET " .
            "nomor = '{$next}', " .
            "tgl_naik = NOW(), " .
            "tgl_update = NOW() " .
            "WHERE c_nomor_id = '{$id}' AND app_org_id = '{$org_id}'",
            $this->connection);
        
        if ($data['reset_tiap_tahun'] == 'Y') {
            if ($data['tahun_aktif'] != date('Y')) {
                $data['tahun_aktif'] = date('Y');
                mysql_query(
                    "UPDATE c_nomor SET " .
                    "nomor = 1, " .
                    "tahun_aktif = '{$data['tahun_aktif']}', " .
                    "tgl_naik = NOW(), " .
                    "tgl_reset = NOW(), " .
                    "tgl_update = NOW() " .
                    "WHERE c_nomor_id = '{$id}' AND app_org_id = '{$org_id}'",
                    $this->connection);
            }
            
            if ($org_id == -1) {
                $rsx = mysql_query(
                    "SELECT c_nomor.*, '' doc_no_prefix " .
                    "FROM c_nomor " .
                    "WHERE c_nomor_id = '{$id}'",
                    $this->connection);
            } else {
                $rsx = mysql_query(
                    "SELECT c_nomor.*, doc_no_prefix " .
                    "FROM c_nomor " .
                    "JOIN app_org USING (app_org_id) " .
                    "WHERE c_nomor_id = '{$id}' AND app_org_id = '{$org_id}'",
                    $this->connection);
            }
            if (!($data = mysql_fetch_array($rsx))) return FALSE;
            mysql_free_result($rsx);
            
            if ($data['pad'] > 0) {
                return $data['doc_no_prefix'] . $id . '-' . $data['tahun_aktif'] . '-' . str_pad($data['nomor'], $data['pad'], '0', STR_PAD_LEFT);
            } else {
                return $data['doc_no_prefix'] . $id . '-' . $data['tahun_aktif'] . '-' . $data['nomor'];
            }
        } else {
            if ($data['pad'] > 0) {
                return $data['doc_no_prefix'] . $id . '-' .str_pad($next, $data['pad'], '0', STR_PAD_LEFT);
            } else {
                return $data['doc_no_prefix'] . $id . '-' .$next;
            }
        }
    }
    
}


?>