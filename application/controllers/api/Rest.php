<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
//To Solve File REST_Controller not found
require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

/**
 * This is an example of a few basic user interaction methods you could use
 * all done with a hardcoded array
 *
 * @package         CodeIgniter
 * @subpackage      Rest Server
 * @category        Controller
 * @author          Phil Sturgeon, Chris Kacerguis
 * @license         MIT
 * @link            https://github.com/chriskacerguis/codeigniter-restserver
 */
class Rest extends REST_Controller {

    function __construct() {
        // Construct the parent class
        parent::__construct();
        $this->load->model('server_model');
    }

    public function checkReplacePassword_post() {
        $passCredential = json_decode(file_get_contents("php://input"),TRUE);
        $hasil = $this->server_model->get_user_by_nik($passCredential['nomor_induk']);
        if ($hasil) {
            if ($this->bcrypt->check_password($passCredential['password'], $hasil['password'])) {
                if ($this->server_model->update_password($this->bcrypt->hash_password($passCredential['new_password']), $hasil['nim'])) {
                    $this->response(array('title' => 'Sukses', 'status' => true, 'body' => 'Berhasil mengganti password!'), REST_Controller::HTTP_OK);
                } else {
                    $this->response(array('title' => 'Kesalahan', 'status' => false, 'body' => 'Terjadi kesalahan di sisi server!'), REST_Controller::HTTP_BAD_GATEWAY);
                }
            }else{
                $this->response(array('title' => 'Kesalahan', 'status' => false, 'body' => 'Password lama anda salah!'), REST_Controller::HTTP_BAD_REQUEST);
            }
        } else {
            $this->response(array('title' => 'Kebenaran', 'status' => false, 'body' => 'Anda Siapa?'), REST_Controller::HTTP_FORBIDDEN);
        }
    }

    //mengambil info mahasiswa
    public function mahasiswa_get() {
        $user = $this->server_model->get_mahasiswa();
        $this->response($user[0], REST_Controller::HTTP_OK);
    }
    
    //start method login
    public function login_post() {
        $ps = json_decode(file_get_contents("php://input"), TRUE);
        $credential = $this->server_model->get_user_by_username($ps['username']);
        if (empty($credential)) {
            $pesan = array('title' => 'Login', 'status' => FALSE, 'body' => 'Login gagal!');
            $credential = array('nim' => "", 'nomor_induk' => "", 'nama' => "", 'jabatan' => "", 'no_hp' => "");
            $this->response($pesan + $credential, REST_Controller::HTTP_ACCEPTED);
            return;
        }
        if ($this->bcrypt->check_password($ps['password'], $credential['password'])) {
            $pesan = array('title' => 'Login', 'status' => TRUE, 'body' => 'Login berhasil!');
            $this->response(array_merge($pesan, $credential), REST_Controller::HTTP_ACCEPTED);
            return;
        } else {
            $pesan = array('title' => 'Login', 'status' => FALSE, 'body' => 'Login gagal!');
            $this->response(array_merge($pesan, $credential), REST_Controller::HTTP_FORBIDDEN);
            return;
        }
    }



//register
    public function register_post() {
        $user = json_decode(file_get_contents("php://input"), TRUE);
        $user['password'] = $this->bcrypt->hash_password($user['password']);
        $temp = null;
        if ($user['peran'] == 1) {
            $temp = $this->server_model->get_mahasiswa(null, $user['nim']);
            if (!empty($temp)) {
                $user['nim'] = $temp[0]['nim'];
            }
        } else {
            $temp = $this->server_model->get_mahasiswa(null, $user['nim']);
            if (!empty($temp)) {
                $user['nim'] = $temp[0]['nim'];
            }
        }
        if ($temp == null) {
            $pesan['title'] = 'Error';
            $pesan['status'] = FALSE;
            $pesan['body'] = 'Anda bukan anggota STIS!';
            $this->response($pesan, REST_Controller::HTTP_OK);
        }
        if ($this->server_model->register($user)) {
            $pesan['title'] = 'Sukses';
            $pesan['status'] = TRUE;
            $pesan['body'] = 'Akun anda berhasil terdaftar!';
            $this->response($pesan, REST_Controller::HTTP_ACCEPTED);
        } else {
            $pesan['title'] = 'Error';
            $pesan['status'] = FALSE;
            $pesan['body'] = 'Kesalahan saat berkomunikasi dengan server atau Akun atas nama anda sudah terdaftar.';
            $this->response($pesan, REST_Controller::HTTP_OK);
        }
    }



    public function tambahCatatanPelanggaran_post() {
        $catat_pelanggaran = json_decode(file_get_contents("php://input"), TRUE);
        if ($this->server_model->tambah_catatan_pelanggaran($catat_pelanggaran)) {
            $this->response(array('title' => 'sukses', 'status' => true, 'body' => 'Berhasil menambah pesanan.'), REST_Controller::HTTP_ACCEPTED);
        } else {
            $this->response(array('title' => 'sukses', 'status' => false, 'body' => 'Gagal menambah pesanan!'), REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function hapusCatatanPelanggaran_get() {
        $id = $this->get('no');
        if ($this->server_model->hapus_catatan_pelanggaran($id)) {
            $this->response(array('title' => 'sukses', 'status' => false, 'body' => 'Berhasil menghapus pesanan!'), REST_Controller::HTTP_OK);
        } else {
            $this->response(array('title' => 'sukses', 'status' => false, 'body' => 'Berhasil menghapus pesanan!'), REST_Controller::HTTP_BAD_REQUEST);
        }
    }


}
