<?php

Class Server_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    public function get_user_by_nik($nik){
        $res = $this->db->where('nim', $nik)->get('mahasiswa')->row_array();
        $temp = $res['nim'];
        $res = $this->db->where('nim',$res['nim'])->get('user')->row_array();
        $res = array_merge($res, array('nim'=>$temp));
        return $res;
    }

    
    //start get mahasiswa untuk tiap kemungkinan
    public function get_mahasiswa($nim = null, $nip = null) {
        if ($nim !== null) {
            $this->db->where("nim", $nim);
        } else if ($nip != null) {
            $this->db->where('nim', $nip);
        }
        $result = $this->db->get('mahasiswa');
        return $result->result_array();
    }
    
    public function tambah_catatan_pelanggaran($catat_pelanggaran) {
        return $this->db->insert('bukusaku_catatan_pelanggaran', $catat_pelanggaran);
    }

    public function hapus_catatan_pelanggaran($id_catatan) {
        $this->db->where('no', $id_catatan);
        return $this->db->delete('bukusaku_catatan_pelanggaran');
    }

    //get user by username fix
    public function get_user_by_username($uname) {
        $this->db->where('username', $uname);
        $result = $this->db->get('user');
        return $result->row_array();
    }

    //register fix
    public function register($user) {
        $temp = array();
        $keep = array('username', 'password', 'nim',);
        foreach ($user as $key => $value) {
            if (in_array($key, $keep)) {
                $temp[$key] = $value;
            }
        }
        $cross_check = $this->db->get('user')->result_array();
        foreach ($cross_check as $key => $value) {
            if ($value['username'] == $temp['username'] || $value['nim'] == $temp['nim']) {
                return false;
            }
        }
        return $this->db->insert('user', $temp);
    }

}
