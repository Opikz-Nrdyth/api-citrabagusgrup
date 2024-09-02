<?php
require_once("db_connect.php");

if (isset($_GET["read_ujian"])) {
    $id = 1;
    $dataArray = array();
    $kelas = $_GET["kelas"];
    $tipeKelas = $_GET["tipeKelas"];
    $kelasBuku = (int)$kelas . " " . $tipeKelas;
    $queryUjian = "SELECT mapel AS nama_buku, tanggal AS tanggal_terbit, kelas, nama_ujian AS tipe FROM db_ujian WHERE status = 'preview' && kelas = '$kelas';";
    $queryBuku = "SELECT mapel AS nama_buku, tanggal AS tanggal_terbit, kelas, tipe, url_materi FROM `db_materi` WHERE kelas = '$kelasBuku'";
    $sqlUjian = mysqli_query($conn, $queryUjian);
    $sqlBuku = mysqli_query($conn, $queryBuku);

    if (mysqli_num_rows($sqlUjian) > 0) {
        while ($data = mysqli_fetch_assoc($sqlUjian)) {
            $tanggal_terbit = date('l, j F Y', strtotime($data['tanggal_terbit']));
            $tanggal_terbit = str_replace(
                array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),
                array('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'),
                $tanggal_terbit
            );
            $tanggal_terbit = str_replace(
                array('January', 'February', 'March', 'May', 'June', 'July', 'August', 'October', 'December'),
                array('Januari', 'Februari', 'Maret', 'Mei', 'Juni', 'Juli', 'Agustus', 'Oktober', 'Desember'),
                $tanggal_terbit
            );
            $tanggal_terbit = preg_replace('/(\d+) (\w+) (\d+)/', '$1 $2 $3', $tanggal_terbit);
            $data['tanggal_terbit'] = $tanggal_terbit;
            $data['id'] = "test";
            $dataArray[] = $data;
        }
    }
    if (mysqli_num_rows($sqlBuku)) {
        while ($data = mysqli_fetch_assoc($sqlBuku)) {
            $tanggal_terbit = date('l, j F Y', strtotime($data['tanggal_terbit']));
            $tanggal_terbit = str_replace(
                array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),
                array('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'),
                $tanggal_terbit
            );
            $tanggal_terbit = str_replace(
                array('January', 'February', 'March', 'May', 'June', 'July', 'August', 'October', 'December'),
                array('Januari', 'Februari', 'Maret', 'Mei', 'Juni', 'Juli', 'Agustus', 'Oktober', 'Desember'),
                $tanggal_terbit
            );
            $tanggal_terbit = preg_replace('/(\d+) (\w+) (\d+)/', '$1 $2 $3', $tanggal_terbit);
            $data['tanggal_terbit'] = $tanggal_terbit;
            $dataArray[] = $data;
        }
    }

    usort($dataArray, function ($a, $b) {
        return strtotime($b['tanggal_terbit']) - strtotime($a['tanggal_terbit']);
    });

    foreach ($dataArray as $key => $d) {
        if ($d['tipe'] != "PDF") {
            $dataArray[$key]['kelas'] = $kelas;
        }
        $dataArray[$key]["id"] = $id;
        $id++;
    }
    $json_result = json_encode($dataArray);

    header("Content-Type: application/json");
    echo $json_result;
}

if (isset($_POST["add_materi"])) {
    $file = $_FILES["file"]["name"];
    $nama = $_POST["nama"];
    $kelas = $_POST["kelas"];

    $target_dir = "images/";

    $ext = strtolower(pathinfo($foto, PATHINFO_EXTENSION));

    $allowed_ext = array('pdf');

    if (!in_array($ext, $allowed_ext)) {
        echo "Invalid Extention";
        exit;
    } else {
        $hash = hash('sha256', $foto . time());

        $newFileName = $hash . '.' . $ext;

        $target_file = $target_dir . $newFileName;

        if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
            $query = "INSERT INTO `foto_webutama`(`foto`, `deskripsi`) VALUES ('$target_file','$deskripsi')";
            $sql = mysqli_query($conn, $query);
            if ($sql) {
                echo "Berhasil";
            } else {
                unlink($target_file);
                echo "Error Uploading Image";
            }
        } else {
            echo "Error Uploading Image";
        }
    }
}
