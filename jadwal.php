<?php
require_once('db_connect.php');
require_once('db_tentor_connect.php');
// Fungsi untuk mendapatkan data ruangan

function get_data_ruangan($conn)
{
    // Query untuk mendapatkan data ruangan
    $sql = 'SELECT * FROM ruangan ORDER BY `ruangan`.`ruangan` ASC';
    $result = $conn->query($sql);

    // Cek hasil query
    if ($result->num_rows > 0) {
        // Inisialisasi array untuk menyimpan data ruangan
        $data_ruangan = array();

        // Loop untuk mengambil data ruangan dari hasil query
        while ($row = $result->fetch_assoc()) {
            // Masukkan data ruangan ke array
            $data_ruangan[] = $row;
        }

        // Kembalikan array data ruangan
        return $data_ruangan;
    } else {
        // Jika tidak ada data ruangan, kembalikan array kosong
        return array();
    }
}

// Fungsi untuk mendapatkan data pengajar

function get_data_pengajar($tentorDB)
{
    // Query untuk mendapatkan data pengajar

    $sql = 'SELECT nama AS pengajar FROM `users` ORDER BY `users`.`nama` ASC';
    $result = $tentorDB->query($sql);
    // Cek hasil query
    if ($result->num_rows > 0) {
        // Inisialisasi array untuk menyimpan data pengajar
        $data_pengajar = array();

        // Loop untuk mengambil data pengajar dari hasil query
        while ($row = $result->fetch_assoc()) {
            // Masukkan data pengajar ke array
            $data_pengajar[] = $row;
        }

        // Kembalikan array data pengajar
        return $data_pengajar;
    } else {
        // Jika tidak ada data pengajar, kembalikan array kosong
        return array();
    }
}

// Fungsi untuk mendapatkan data mata pelajaran

function get_data_mapel($conn)
{
    // Query untuk mendapatkan data mata pelajaran
    $sql = 'SELECT DISTINCT mapel FROM mapel';
    $result = $conn->query($sql);

    // Cek hasil query
    if ($result->num_rows > 0) {
        // Inisialisasi array untuk menyimpan data mata pelajaran
        $data_mapel = array();

        // Loop untuk mengambil data mata pelajaran dari hasil query
        while ($row = $result->fetch_assoc()) {
            // Masukkan data mata pelajaran ke array
            $data_mapel[] = $row;
        }

        // Kembalikan array data mata pelajaran
        return $data_mapel;
    } else {
        // Jika tidak ada data mata pelajaran, kembalikan array kosong
        return array();
    }
}

// Fungsi untuk mendapatkan data jadwal

function get_data_jadwal($conn, $hari_tanggal)
{
    // Query untuk mendapatkan data jadwal berdasarkan hari dan tanggal
    $sql = "SELECT *, DATE_FORMAT(STR_TO_DATE(jam, '%H:%i' ), '%H:%i' ) AS new_jam FROM jadwal WHERE hari_tanggal = '$hari_tanggal' ORDER BY new_jam ASC";
    $result = $conn->query($sql);

    // Cek hasil query
    if ($result->num_rows > 0) {
        // Inisialisasi array untuk menyimpan data jadwal
        $data_jadwal = array();

        // Loop untuk mengambil data jadwal dari hasil query
        while ($row = $result->fetch_assoc()) {
            // Masukkan data jadwal ke array
            $data_jadwal[] = $row;
        }

        // Kembalikan array data jadwal
        return $data_jadwal;
    } else {
        // Jika tidak ada data jadwal, kembalikan array kosong
        return array();
    }
}

// Fungsi untuk mendapatkan ruangan yang dapat dipakai

function get_ruangan_tersedia($data_ruangan, $data_jadwal, $jam)
{
    // Inisialisasi array untuk menyimpan ruangan yang dapat dipakai
    $ruangan_tersedia = array();

    // Loop untuk setiap ruangan
    foreach ($data_ruangan as $ruangan) {
        // Inisialisasi variabel untuk menandai apakah ruangan dapat dipakai
        $ruangan_bisa_dipakai = true;

        // Loop untuk setiap jadwal
        foreach ($data_jadwal as $jadwal) {
            // Cek apakah ruangan sama dengan ruangan yang sedang dipakai
            $jam_timestamp = strtotime($jam);
            $jadwal_timestamp = strtotime($jadwal['jam']);
            $selisih = abs($jam_timestamp - $jadwal_timestamp);

            if ($ruangan['ruangan'] == $jadwal['ruangan']) {
                // Cek apakah jam sama dengan jam yang sedang dipakai
                if ($selisih < 5400) {
                    // Jika ya, maka ruangan tidak dapat dipakai
                    $ruangan_bisa_dipakai = false;
                    break;
                }
            }
        }

        // Jika ruangan dapat dipakai, masukkan ke array ruangan yang dapat dipakai
        if ($ruangan_bisa_dipakai) {
            $ruangan_tersedia[] = $ruangan;
        }
    }

    // Kembalikan array ruangan yang dapat dipakai
    return $ruangan_tersedia;
}

// Fungsi untuk mendapatkan ruangan yang sedang dipakai

function get_ruangan_dipakai($data_ruangan, $data_jadwal, $jam)
{
    // Inisialisasi array untuk menyimpan ruangan yang sedang dipakai
    $ruangan_dipakai = array();

    // Loop untuk setiap ruangan
    foreach ($data_ruangan as $ruangan) {
        // Inisialisasi variabel untuk menandai apakah ruangan sedang dipakai
        $ruangan_sedang_dipakai = false;

        // Loop untuk setiap jadwal
        foreach ($data_jadwal as $jadwal) {
            // Cek apakah ruangan sama dengan ruangan yang sedang dipakai
            $jam_timestamp = strtotime($jam);
            $jadwal_timestamp = strtotime($jadwal['jam']);
            $selisih = abs($jam_timestamp - $jadwal_timestamp);

            // Cek apakah ruangan sama dengan ruangan yang sedang dipakai
            if ($ruangan['ruangan'] == $jadwal['ruangan']) {
                // Cek apakah jam sama dengan jam yang sedang dipakai
                if ($selisih < 5400) {
                    // Jika ya, maka ruangan sedang dipakai
                    $ruangan_sedang_dipakai = true;
                    break;
                }
            }
        }

        // Jika ruangan sedang dipakai, masukkan ke array ruangan yang sedang dipakai
        if ($ruangan_sedang_dipakai) {
            $ruangan_dipakai[] = $ruangan;
        }
    }

    // Kembalikan array ruangan yang sedang dipakai
    return $ruangan_dipakai;
}

// Fungsi untuk mendapatkan pengajar yang dapat dipakai

function get_pengajar_tersedia($data_pengajar, $data_jadwal, $jam)
{
    // Inisialisasi array untuk menyimpan pengajar yang dapat dipakai
    $pengajar_tersedia = array();

    // Loop untuk setiap pengajar
    foreach ($data_pengajar as $pengajar) {
        // Inisialisasi variabel untuk menandai apakah pengajar dapat dipakai
        $pengajar_bisa_dipakai = true;

        // Loop untuk setiap jadwal
        foreach ($data_jadwal as $jadwal) {
            // Cek apakah pengajar sama dengan pengajar yang sedang dipakai
            if ($pengajar['pengajar'] == $jadwal['pengajar']) {
                // Cek apakah jam sama dengan jam yang sedang dipakai
                $jam_timestamp = strtotime($jam);
                $jadwal_timestamp = strtotime($jadwal['jam']);
                $selisih = abs($jam_timestamp - $jadwal_timestamp);

                if ($selisih < 5400) {
                    // Jika ya, maka pengajar tidak dapat dipakai
                    $pengajar_bisa_dipakai = false;
                    break;
                }
            }
        }

        // Jika pengajar dapat dipakai, masukkan ke array pengajar yang dapat dipakai
        if ($pengajar_bisa_dipakai) {
            $pengajar_tersedia[] = $pengajar;
        }
    }

    // Kembalikan array pengajar yang dapat dipakai
    return $pengajar_tersedia;
}

// Fungsi untuk mendapatkan pengajar yang sedang dipakai

function get_pengajar_dipakai($data_pengajar, $data_jadwal, $jam)
{
    // Inisialisasi array untuk menyimpan pengajar yang sedang dipakai
    $pengajar_dipakai = array();

    // Loop untuk setiap pengajar
    // Loop untuk setiap pengajar
    foreach ($data_pengajar as $pengajar) {
        // Inisialisasi variabel untuk menandai apakah pengajar sedang dipakai
        $pengajar_sedang_dipakai = false;

        // Loop untuk setiap jadwal
        foreach ($data_jadwal as $jadwal) {
            // Cek apakah pengajar sama dengan pengajar yang sedang dipakai
            if ($pengajar['pengajar'] == $jadwal['pengajar']) {
                // Cek apakah ruangan sama dengan ruangan yang sedang dipakai
                $jam_timestamp = strtotime($jam);
                $jadwal_timestamp = strtotime($jadwal['jam']);
                $selisih = abs($jam_timestamp - $jadwal_timestamp);

                // Cek apakah jam sama dengan jam yang sedang dipakai
                if ($selisih < 5400) {
                    // Jika ya, maka pengajar sedang dipakai
                    $pengajar_sedang_dipakai = true;
                    break;
                }
            }
        }

        // Jika pengajar sedang dipakai, masukkan ke array pengajar yang sedang dipakai
        if ($pengajar_sedang_dipakai) {
            $pengajar_dipakai[] = $pengajar;
        }
    }

    // Kembalikan array pengajar yang sedang dipakai
    return $pengajar_dipakai;
}

// Fungsi untuk membuat output API 

function create_api_output($tentorDB, $conn, $hari_tanggal, $jam)
{
    // Inisialisasi array untuk menyimpan output API
    $api_output = array();

    // Masukkan hari dan tanggal ke output API
    $api_output['Hari'] = $hari_tanggal;

    // Inisialisasi array untuk menyimpan data
    $data = array();

    // Mendapatkan data ruangan, pengajar, mata pelajaran, dan jadwal dari database
    $data_ruangan = get_data_ruangan($conn);
    $data_pengajar = get_data_pengajar($tentorDB);
    $data_mapel = get_data_mapel($conn);
    $data_jadwal = get_data_jadwal($conn, $hari_tanggal);

    // Mendapatkan ruangan dan pengajar yang dapat dan sedang dipakai
    $ruangan_tersedia = get_ruangan_tersedia($data_ruangan, $data_jadwal, $jam);
    $ruangan_dipakai = get_ruangan_dipakai($data_ruangan, $data_jadwal, $jam);
    $pengajar_tersedia = get_pengajar_tersedia($data_pengajar, $data_jadwal, $jam);
    $pengajar_dipakai = get_pengajar_dipakai($data_pengajar, $data_jadwal, $jam);

    // Masukkan data ke array data
    $data['SemuaRuangan'] = $data_ruangan;
    $data['SemuaTentor'] = $data_pengajar;
    $data['SemuaMapel'] = $data_mapel;
    $data['RuanganTersedia'] = $ruangan_tersedia;
    $data['RuanganTidakTersedia'] = $ruangan_dipakai;
    $data['PengajarTersedia'] = $pengajar_tersedia;
    $data['PengajarTidakTersedia'] = $pengajar_dipakai;

    // Masukkan data ke output API
    $api_output['Data'] = $data;

    // Kembalikan output API
    return $api_output;
}

if (isset($_GET['jadwal'])) {
    // Tentukan hari dan tanggal yang ingin ditampilkan
    $hari_tanggal = $_GET['tanggal'];

    // Tentukan jam yang ingin ditampilkan
    $jam = $_GET['jam'];

    // Panggil fungsi untuk membuat output API
    $output = create_api_output($tentorDB, $conn, $hari_tanggal, $jam);

    // Tampilkan output API dalam format JSON
    echo json_encode($output, JSON_PRETTY_PRINT);
}

if (isset($_GET['jadwalAll'])) {
    // Query untuk mendapatkan data jadwal berdasarkan hari dan tanggal
    if (isset($_GET['tanggal'])) {
        $sql = 'SELECT *, DATE_FORMAT(STR_TO_DATE(jam, "%H:%i" ), "%H:%i" ) AS new_jam FROM jadwal WHERE hari_tanggal = "' . $_GET['tanggal'] . '" ORDER BY new_jam ASC';
    } else {
        $sql = 'SELECT *, DATE_FORMAT(STR_TO_DATE(jam, "%H:%i" ), "%H:%i" ) AS new_jam FROM jadwal ORDER BY new_jam ASC';
    }
    $result = $conn->query($sql);

    // Cek hasil query
    if ($result->num_rows > 0) {
        // Inisialisasi array untuk menyimpan data jadwal
        $data_jadwal = array();

        // Loop untuk mengambil data jadwal dari hasil query
        while ($row = $result->fetch_assoc()) {
            // Masukkan data jadwal ke array
            $data_jadwal[] = $row;
        }

        // Kembalikan array data jadwal
        echo json_encode($data_jadwal);
    } else {
        echo json_encode(array());
    }
}

if (isset($_GET['listKelas'])) {
    $sql = "SELECT `kelas`, `tipeKelas` FROM db_siswa WHERE kelas BETWEEN 1 AND 12 AND (tipeKelas LIKE '%A%' OR tipeKelas LIKE '%B%' OR tipeKelas LIKE '%C%' OR tipeKelas LIKE '%D%' OR tipeKelas LIKE '%E%' OR tipeKelas LIKE '%F%' OR tipeKelas LIKE '%G%' OR tipeKelas LIKE 'IPA %' OR tipeKelas LIKE 'IPS %') GROUP BY kelas, tipeKelas ORDER BY kelas, tipeKelas;";
    $result = $conn->query($sql);

    // Cek hasil query
    if ($result->num_rows > 0) {
        // Inisialisasi array untuk menyimpan data jadwal
        $data_jadwal = array();

        // Loop untuk mengambil data jadwal dari hasil query
        while ($row = $result->fetch_assoc()) {
            // Masukkan data jadwal ke array
            $data_jadwal[] = $row;
        }
    }

    $jsonKelasTambahan = 'json/jadwal/listKelasTambahan.json';
    $dataKelasTambahan = file_get_contents($jsonKelasTambahan);
    $data_tambahan = json_decode($dataKelasTambahan, true);
    // Urutkan array data jadwal berdasarkan kelas dan tipeKelas
    usort($data_tambahan, function ($a, $b) {
        // Bandingkan kelas terlebih dahulu
        if ($a['kelas'] < $b['kelas']) {
            return -1;
        } elseif ($a['kelas'] > $b['kelas']) {
            return 1;
        } else {
            // Jika kelas sama, bandingkan tipeKelas
            return strcmp($a['tipeKelas'], $b['tipeKelas']);
        }
    });
    foreach ($data_tambahan as $data) {
        // Cek apakah data sudah ada di array data jadwal
        $found = false;
        foreach ($data_jadwal as $jadwal) {
            if ($data['kelas'] == $jadwal['kelas'] && $data['tipeKelas'] == $jadwal['tipeKelas']) {
                // Data sudah ada, tidak perlu ditambahkan
                $found = true;
                break;
            }
        }
        // Jika data belum ada, tambahkan ke array data jadwal
        if (!$found) {
            $data_jadwal[] = $data;
        }
    }

    foreach ($data_jadwal as $key => $d) {
        $jumlahSiswa = mysqli_query($conn, "SELECT COUNT(*) FROM db_siswa WHERE kelas = '" . $d['kelas'] . "' && (asal_sekolah = '" . $d['tipeKelas'] . "' || tipeKelas = '" . $d['tipeKelas'] . "');");
        $dataJumlah = mysqli_fetch_assoc($jumlahSiswa);
        $data_jadwal[$key]['jumlahSiswa'] = $dataJumlah['COUNT(*)'];
    }
    // Kembalikan array data jadwal yang sudah ditambahkan dan diurutkan dalam format JSON
    echo json_encode($data_jadwal);
}

if (isset($_POST['addJadwal'])) {

    $hari_tanggal = $_POST['tanggal'];
    // Hari dan tanggal jadwal, misalnya Jumat, 8 Desember 2023
    $jam = $_POST['jam'];
    // Jam jadwal, misalnya 08:00
    $kelas = $_POST['kelas'];
    // Nama kelas, misalnya 7D
    $mapel = $_POST['mapel'];
    // Mata pelajaran yang diajarkan, misalnya Bahasa Indonesia
    $pengajar = $_POST['tentor'];
    // Nama pengajar yang mengajar, misalnya Andi
    $ruangan = $_POST['ruangan'];
    // Nama ruangan yang dipakai, misalnya 26
    $jum_siswa = $_POST['jumlah_siswa'];
    $jum_kursi = $_POST['jumlah_kursi'];

    // Query untuk input data ke tabel jadwal
    $sql = "INSERT INTO jadwal (hari_tanggal, jam, kelas, mapel, pengajar, ruangan, jumlah_siswa, jumlah_kursi ) VALUES ('$hari_tanggal', '$jam', '$kelas', '$mapel', '$pengajar', '$ruangan','$jum_siswa','$jum_kursi')";

    // Eksekusi query
    if ($conn->query($sql) === TRUE) {
        // Jika berhasil, tampilkan pesan sukses
        echo 'Berhasil';
    } else {
        // Jika gagal, tampilkan pesan error
        echo 'Error: ' . $sql . '<br>' . $conn->error;
    }

    // Tutup koneksi
    $conn->close();
}

if (isset($_POST['updateJadwal'])) {
    $id = $_POST['id'];
    // Jam jadwal, misalnya 08:00
    $kelas = $_POST['kelas'];
    // Nama kelas, misalnya 7D
    $mapel = $_POST['mapel'];
    // Mata pelajaran yang diajarkan, misalnya Bahasa Indonesia
    $pengajar = $_POST['tentor'];
    // Nama pengajar yang mengajar, misalnya Andi
    $ruangan = $_POST['ruangan'];
    // Nama ruangan yang dipakai, misalnya 26

    $jum_siswa = $_POST['jumlah_siswa'];
    $jum_kursi = $_POST['jumlah_kursi'];

    // Query untuk input data ke tabel jadwal
    $sql = "UPDATE `jadwal` SET `kelas`='" . $kelas . "',`mapel`='" . $mapel . "',`pengajar`='" . $pengajar . "',`ruangan`='" . $ruangan . "', `jumlah_siswa`='" . $jum_siswa . "', `jumlah_kursi`='" . $jum_kursi . "' WHERE id = '" . $id . "'";

    // Eksekusi query
    if ($conn->query($sql) === TRUE) {
        // Jika berhasil, tampilkan pesan sukses
        echo 'Berhasil';
    } else {
        // Jika gagal, tampilkan pesan error
        echo 'Error: ' . $sql . '<br>' . $conn->error;
    }

    // Tutup koneksi
    $conn->close();
}

if (isset($_POST['deleteJadwal'])) {
    $id = $_POST['id'];

    $sql = "DELETE FROM `jadwal` WHERE id='$id'";

    // Eksekusi query
    if ($conn->query($sql) === TRUE) {
        // Jika berhasil, tampilkan pesan sukses
        echo 'Berhasil';
    } else {
        // Jika gagal, tampilkan pesan error
        echo 'Error: ' . $sql . '<br>' . $conn->error;
    }

    // Tutup koneksi
    $conn->close();
}

if (isset($_POST['addMapel'])) {

    $mapel = $_POST['mapel'];

    // Query untuk input data ke tabel jadwal
    $sql = "INSERT INTO `mapel`(`mapel`) VALUES ('" . $mapel . "')";

    // Eksekusi query
    if ($conn->query($sql) === TRUE) {
        // Jika berhasil, tampilkan pesan sukses
        echo 'Berhasil';
    } else {
        // Jika gagal, tampilkan pesan error
        echo 'Error: ' . $sql . '<br>' . $conn->error;
    }

    // Tutup koneksi
    $conn->close();
}

if (isset($_POST['deleteMapel'])) {
    $sql = mysqli_query($conn, "DELETE FROM `mapel` WHERE mapel = '" . $_POST['mapel'] . "'");
    if ($sql) {
        echo "Berhasil";
    }
}

if (isset($_POST['addPengajar'])) {

    $pengajar = $_POST['pengajar'];

    // Query untuk input data ke tabel jadwal
    $sql = "INSERT INTO `pengajar`(`pengajar`) VALUES ('" . $pengajar . "')";

    // Eksekusi query
    if ($conn->query($sql) === TRUE) {
        // Jika berhasil, tampilkan pesan sukses
        echo 'Data berhasil diinput ke tabel pengajar';
    } else {
        // Jika gagal, tampilkan pesan error
        echo 'Error: ' . $sql . '<br>' . $conn->error;
    }

    // Tutup koneksi
    $conn->close();
}

if (isset($_GET['readPengajar'])) {

    // Query untuk input data ke tabel jadwal
    $query = "SELECT `nama` FROM `users` ORDER BY `users`.`nama` ASC";

    // Eksekusi query
    $data_pengajar = array();
    $sql = mysqli_query($tentorDB, $query);
    while ($row = mysqli_fetch_assoc($sql)) {
        $data_pengajar[] = $row;
    }
    echo json_encode($data_pengajar);
    // Tutup koneksi
    $tentorDB->close();
}

if (isset($_POST['addRuangan'])) {

    $ruangan = $_POST['ruangan'];
    $kapasitas = $_POST['kapasitas'];

    // Query untuk input data ke tabel jadwal
    $sql = "INSERT INTO `ruangan`(`ruangan`, `kapasitas`) VALUES ('" . $ruangan . "','" . $kapasitas . "')";

    // Eksekusi query
    if ($conn->query($sql) === TRUE) {
        // Jika berhasil, tampilkan pesan sukses
        echo 'Berhasil';
    } else {
        // Jika gagal, tampilkan pesan error
        echo 'Error: ' . $sql . '<br>' . $conn->error;
    }

    // Tutup koneksi
    $conn->close();
}

if (isset($_POST['deleteRuangan'])) {
    $sql = mysqli_query($conn, "DELETE FROM `ruangan` WHERE ruangan = '" . $_POST['ruangan'] . "'");
    if ($sql) {
        echo "Berhasil";
    }
}

if (isset($_POST['addKelas'])) {
    // File json yang akan dibaca
    $file = "json/jadwal/listKelasTambahan.json";

    // Mendapatkan file json
    $anggota = file_get_contents($file);

    // Mendecode anggota.json
    $data = json_decode($anggota, true);

    // Data array baru
    $data[] = array(
        'kelas'     => $_POST['kelas'],
        'tipeKelas'   => $_POST['tipeKelas'],
        'tipe' => 'tambahan'
    );

    // Mengencode data menjadi json
    $jsonfile = json_encode($data, JSON_PRETTY_PRINT);

    // Menyimpan data ke dalam anggota.json
    $anggota = file_put_contents($file, $jsonfile);
    if ($anggota) {
        echo "Berhasil";
    }
}

if (isset($_POST['deleteKelas'])) {
    // File json yang akan dibaca
    $file = "json/jadwal/listKelasTambahan.json";

    // Mendapatkan file json
    $anggota = file_get_contents($file);

    // Mendecode anggota.json
    $data = json_decode($anggota, true);

    // Membaca data array menggunakan foreach
    foreach ($data as $key => $d) {
        // Hapus data kedua
        if ($d['kelas'] == $_POST['kelas'] && $d['tipeKelas'] == $_POST['tipeKelas']) {
            // Menghapus data array sesuai dengan index
            // Menggantinya dengan elemen baru
            array_splice($data, $key, 1);
        }
    }

    // Mengencode data menjadi json
    $jsonfile = json_encode($data, JSON_PRETTY_PRINT);

    // Menyimpan data ke dalam anggota.json
    $anggota = file_put_contents($file, $jsonfile);
    if ($anggota) {
        echo "Berhasil";
    }
}
