<?php

namespace App\Controllers;

class Login extends BaseController
{
    // Variabel model
    protected $logModel;

    public function __construct()
    {
        // Inisialisasi model
        $this->logModel = new \App\Models\Login_Model;
    }

    public function login()
    {
        // Data dikirim ke view
        $data = [
            'title' => 'Form Login'
        ];

        return view('login/index', $data);
    }

    public function logincek()
    {
        // Variabel untuk menangkap data login
        $username = strtolower($this->request->getPost('username'));
        $password = $this->request->getPost('password');
        $veriv = $this->request->getPost('veriv');
        $veriv = hash('sha256', $veriv);
        $veriv2 = $this->request->getPost('veriv2');

        // Input veriv tidak boleh kosong
        if (empty($veriv) || empty($veriv2)) {
            // Session setFlashdata
            $pesan = '<div class="alert alert-warning text-center" role="alert">
            Hasil penjumlahan harus di isi.
            </div>';
            session()->setFlashdata('pesan', $pesan);

            return redirect()->to('/login');
        }

        // Cek apakah veriv benar atau tidak
        if ($veriv != $veriv2) {
            // Session setFlashdata
            $pesan = '<div class="alert alert-warning text-center" role="alert">
            Hasil penjumlahan salah.
            </div>';
            session()->setFlashdata('pesan', $pesan);

            return redirect()->to('/login');
        }

        // Mengambil data dari DB berdasarkan username
        $user = $this->logModel->getUser($username);

        // Cek data yang dikirim ada di DB atau tidak
        if ($user != null) {
            // Cek is_active sudah 1 belum
            if ($user['is_active'] != 1) {
                $pesan = '<div class="alert alert-warning text-center" role="alert">
                    Username masih dalam moderasi admin.
                    </div>';
                session()->setFlashdata('pesan', $pesan);

                return redirect()->to('/login');
            }

            // Cek username
            if ($user['username'] == $username) {
                // Cek password
                if (password_verify($password, $user['password'])) {
                    // Set session
                    $ses = [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'name' => $user['name'],
                        'role' => $user['role'],
                        'is_active' => $user['is_active']
                    ];
                    session()->set($ses);

                    // Cek role nya apa
                    if ($user['role'] == 'admin') {
                        return redirect()->to('/admin');
                    } else {
                        return redirect()->to('/');
                    }
                }
            }
        }

        // Session setflashdata
        $pesan = '<div class="alert alert-danger text-center" role="alert">
        Username atau password salah
        </div>';
        session()->setFlashdata('pesan', $pesan);

        return redirect()->to('/login');
    }

    public function register()
    {
        // Data yang dikirim ke view
        $data = [
            'title' => 'Form Register'
        ];

        return view('login/register', $data);
    }

    public function registercek()
    {
        // Menangkap semua inputan
        $username = strtolower($this->request->getVar('username'));
        $password = $this->request->getVar('password');
        $password2 = $this->request->getVar('password2');
        $name = $this->request->getVar('name');
        $veriv = $this->request->getVar('veriv');
        $veriv = hash('sha256', $veriv);
        $veriv2 = $this->request->getVar('veriv2');

        // Cek apakah veriv benar atau tidak
        if ($veriv != $veriv2) {
            // Session setFlashdata
            $pesan = '<div class="alert alert-warning text-center" role="alert">
            Hasil penjumlahan salah.
            </div>';
            session()->setFlashdata('pesan', $pesan);

            return redirect()->to('/register');
        }

        // Cek apakah password sama atau tidak
        if ($password != $password2) {
            // Session setFlashdata
            $pesan = '<div class="alert alert-danger text-center" role="alert">
            Password yang dimasukan tidak sama.
            </div>';
            session()->setFlashdata('pesan', $pesan);

            return redirect()->to('/register');
        }

        // Mengambil data dari DB berdasarkan username
        $user = $this->logModel->getUser($username);

        // Cek apakahh username sudah ada atau belum
        if ($user != null) {
            if ($username == $user['username']) {
                // Session setFlashdata
                $pesan = '<div class="alert alert-danger text-center" role="alert">
                Username telah digunakan.
                </div>';
                session()->setFlashdata('pesan', $pesan);

                return redirect()->to('/register');
            }
        }

        // Mengambil data setting dari DB
        $configModel = new \App\Models\Setting_Model();
        $aktivasi = $configModel->getSet('aktivasi_user');

        // Security
        $username = htmlspecialchars($username);
        $password = password_hash($password, PASSWORD_DEFAULT);
        $name = htmlspecialchars($name);
        $role = htmlspecialchars(strtolower('member'));
        $is_active = htmlspecialchars($aktivasi['value']);

        // Perintah save ke DB
        $this->logModel->save([
            'username' => $username,
            'password' => $password,
            'name' => $name,
            'role' => $role,
            'is_active' => $is_active
        ]);

        // Cek is_active
        if ($is_active == 1) {
            // Session setflashdata
            $pesan = '<div class="alert alert-success text-center" role="alert">
            Username berhasil dibuat, silahkan login.
            </div>';
            session()->setFlashdata('pesan', $pesan);

            return redirect()->to('/login');
        } else {
            // Session setflashdata
            $pesan = '<div class="alert alert-success text-center" role="alert">
            Username berhasil dibuat, masih menunggu moderasi dari admin.
            </div>';
            session()->setFlashdata('pesan', $pesan);

            return redirect()->to('/login');
        }
    }

    public function logout()
    {
        // Remove session
        $ses = [
            'id' => '',
            'username' => '',
            'name' => '',
            'role' => '',
            'is_active' => ''
        ];
        session()->set($ses);
        $ses = ['id', 'username', 'name', 'role', 'is_active'];
        session()->remove($ses);

        // Session setflashdata
        $pesan = '<div class="alert alert-info text-center" role="alert">
        Anda berhasil logout.
        </div>';
        session()->setFlashdata('pesan', $pesan);

        return redirect()->to('/login');
    }

    //--------------------------------------------------------------------

}
