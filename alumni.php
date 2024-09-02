<?php
date_default_timezone_set('Asia/Jakarta');
require_once("db_connect.php");

if (isset($_POST["add"])) {
    $username = $_POST["username"];
    $query = "INSERT INTO db_alumni (nama, profile, tanggal_lahir, asal_sekolah, no_hp, kelas, tipeKelas, secondary_kelas, orang_tua, program, tlp_ortu, pekerjaan, alamat, username, password)
                SELECT nama, profile, tanggal_lahir, asal_sekolah, no_hp, kelas, tipeKelas, secondary_kelas, orang_tua, program, tlp_ortu, pekerjaan, alamat, username, password 
                FROM db_siswa 
                WHERE username = '$username'";
    $result = $conn->query($query);
    if ($result === TRUE) {
        echo "Berhasil";
    } else {
        echo "Data Gagal Dipindahkan";
    }
}

if (isset($_GET["delete"])) {
    $username = $_POST["user"];
    $query = "DELETE FROM `db_alumni` WHERE username = '$username'";
    $result = $conn->query($query);
    if ($result === TRUE) {
        echo "Berhasil";
    } else {
        echo "Data Gagal Dihapus";
    }
}

if (isset($_GET["read"])) {
    $query = "SELECT * FROM `db_alumni`";
    $result = $conn->query($query);
    $data = array();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data, true);
}

if (isset($_GET["readAllSiswa"])) {
    $querySiswa = "SELECT 'Siswa' AS status, nama, profile, tanggal_lahir, asal_sekolah, no_hp, kelas, tipeKelas, secondary_kelas, orang_tua, program, tlp_ortu, pekerjaan, alamat, username, password FROM db_siswa";
    $queryAlumni = "SELECT 'Alumni' AS status, nama, profile, tanggal_lahir, asal_sekolah, no_hp, kelas, tipeKelas, secondary_kelas, orang_tua, program, tlp_ortu, pekerjaan, alamat, username, password FROM db_alumni";

    $resultSiswa = $conn->query($querySiswa);
    $resultAlumni = $conn->query($queryAlumni);

    $data = array();

    while ($row = $resultSiswa->fetch_assoc()) {
        $data[] = $row;
    }

    while ($row = $resultAlumni->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode($data, true);
}

if (isset($_GET["readAlumni"])) {
    // Mengambil input dari user
    $page = isset($_GET["page"]) ? $_GET["page"] : 1;
    $search = isset($_GET["search"]) ? $_GET["search"] : "";

    $parts = explode(" ", $search, 2);
    $kelas = $parts[0];
    if (count($parts) > 1) {
        $tipeKelas = $parts[1];
        $tipeKelas = "%$tipeKelas%";
    }

    // Menghitung offset dan limit
    $limit = 25;
    $offset = ($page - 1) * $limit;

    // Membuat query untuk mengambil data dari database berdasarkan kelas dan tipeKelas
    $sql = "SELECT * FROM db_alumni WHERE kelas = ? AND tipeKelas LIKE ? ORDER BY kelas, SUBSTRING_INDEX(tipeKelas, ' ', 1), SUBSTRING_INDEX(tipeKelas, ' ', -1) + 0, nama ASC LIMIT ?, ?";
    $sql_total = "SELECT COUNT(*) AS total FROM db_alumni";
    // Membuat prepared statement
    $stmt = $conn->prepare($sql);

    // Mengikat parameter input dengan nilai kelas dan tipeKelas dan tipe data yang sesuai
    $stmt->bind_param("ssii", $kelas, $tipeKelas, $offset, $limit);

    // Menjalankan prepared statement
    $stmt->execute();

    // Mendapatkan hasil query
    $result = $stmt->get_result();
    $data = array();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    // Mengecek apakah data kosong atau tidak
    if (empty($data)) {
        // Menggunakan query alternatif berdasarkan nama, asal sekolah, atau username
        $sql = "SELECT * FROM db_alumni WHERE nama LIKE ? || asal_sekolah LIKE ? || username LIKE ? ORDER BY kelas, SUBSTRING_INDEX(tipeKelas, ' ', 1), SUBSTRING_INDEX(tipeKelas, ' ', -1) + 0, nama ASC LIMIT ?, ?";
        $stmt = $conn->prepare($sql);
        $search = "%$search%";
        $stmt->bind_param("sssii", $search, $search, $search, $offset, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = array();
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }

    // Membuat query untuk menghitung jumlah data di database berdasarkan kelas dan tipeKelas
    $sql_count = "SELECT COUNT(*) AS total FROM db_alumni WHERE kelas = ? AND tipeKelas LIKE ?";

    // Membuat prepared statement
    $stmt_count = $conn->prepare($sql_count);

    // Mengikat parameter input dengan nilai kelas dan tipeKelas dan tipe data yang sesuai
    $stmt_count->bind_param("ss", $kelas, $tipeKelas);

    // Menjalankan prepared statement
    $stmt_count->execute();

    // Mendapatkan hasil query
    $result_count = $stmt_count->get_result();
    $row_count = $result_count->fetch_assoc();
    $total = $row_count["total"];
    $total_pages = ceil($total / $limit);

    // Mengecek apakah jumlah data kosong atau tidak
    if ($total == 0) {
        // Menggunakan query alternatif untuk menghitung jumlah data berdasarkan nama, asal sekolah, atau username
        $sql_count = "SELECT COUNT(*) AS total FROM db_alumni WHERE nama LIKE ? || asal_sekolah LIKE ? || username LIKE ?";
        $stmt_count = $conn->prepare($sql_count);
        $stmt_count->bind_param("sss", $search, $search, $search);
        $stmt_count->execute();
        $result_count = $stmt_count->get_result();
        $row_count = $result_count->fetch_assoc();
        $total = $row_count["total"];
        $total_pages = ceil($total / $limit);
    }

    $stmt_total = $conn->prepare($sql_total);
    $stmt_total->execute();
    $result_total = $stmt_total->get_result();
    $row_total = $result_total->fetch_assoc();
    $total_records = $row_total["total"];
    // Menutup prepared statement dan koneksi
    $stmt->close();
    $stmt_count->close();
    $conn->close();

    // Mengirimkan output dalam format JSON
    header("Content-Type: application/json");

    $key = array_search('qwerty', array_column($data, 'username'));

    $output = array();
    $output["total_records"] = $key ? $total_records - 1 : $total_records;
    $output["total_pages"] = $total_pages;
    $output["current_page"] = $page;
    $output["data_records"] = count($data);
    $output["data"] = $data;
    echo json_encode($output);
}
