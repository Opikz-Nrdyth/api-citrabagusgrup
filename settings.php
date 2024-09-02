<?php
require_once("db_connect.php");
$file = "json/settings.json";

function deleteDir($dirPath)
{
    $deletedFolders = [];
    if (!is_dir($dirPath)) {
        throw new InvalidArgumentException("$dirPath tidak ditemukan atau bukan sebuah direktori");
    }
    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
        $dirPath .= '/';
    }
    $files = glob($dirPath . '*', GLOB_MARK);
    foreach ($files as $file) {
        if (is_dir($file)) {
            deleteDir($file);
            $deletedFolders[] = $file;
        } else {
            unlink($file);
        }
    }
    if (is_dir($dirPath)) {
        rmdir($dirPath);
        $deletedFolders[] = $dirPath;
    }
    return $deletedFolders;
}

if (isset($_GET["read"])) {
    $anggota = file_get_contents($file);
    $data = json_decode($anggota, true);
    $jsonfile = json_encode($data, JSON_PRETTY_PRINT);
    echo $jsonfile;
}

if (isset($_GET["update"])) {
    $keyData = $_POST["key"];
    $value = $_POST["value"];

    if ($value == "true") {
        $value = true;
    } elseif ($value == "false") {
        $value = false;
    }

    $anggota = file_get_contents($file);
    $data = json_decode($anggota, true);
    foreach ($data as $key => $d) {
        // Perbarui data kedua
        if ($d['nama'] == $keyData) {
            $data[$key]['value'] = $value;
        }
    }
    $jsonfile = json_encode($data, JSON_PRETTY_PRINT);
    $anggota = file_put_contents($file, $jsonfile);
    echo "Berhasil";
}

if (isset($_POST["remove_siswa"])) {
    $query = "TRUNCATE `db_siswa`";
    $sql = mysqli_query($conn, $query);
    if ($sql) {
        echo "Berhasil";
    }
}

if (isset($_POST["remove_jadwal"])) {
    $query = "TRUNCATE `jadwal`";
    $sql = mysqli_query($conn, $query);
    if ($sql) {
        echo "Berhasil";
    }
}

if (isset($_POST["remove_jawaban"])) {
    $path = "json/jawaban/";
    $deletedFolders = deleteDir($path);
    $array = [];
    $data = "";
    if (!empty($deletedFolders)) {
        $data = "Folder yang berhasil dihapus:<br>";
        foreach ($deletedFolders as $folder) {
            $data .= ", " . str_replace("json/jawaban/", "", $folder);
        }
    } else {
        $data =  "Tidak ada folder yang dihapus.\n";
    }
    mkdir($path);
    $array["status"] = "success";
    $array["data"] = $data;

    echo json_encode($array);
}

if (isset($_POST["remove_backup"])) {
    $path = "json/backup/";
    $deletedFolders = deleteDir($path);
    $array = [];
    $data = "";
    if (!empty($deletedFolders)) {
        $data = "Folder yang berhasil dihapus: <br>";
        foreach ($deletedFolders as $folder) {
            $data .= ", " . str_replace("json/backup/", "", $folder);
        }
    } else {
        $data =  "Tidak ada folder yang dihapus. <br>";
    }
    mkdir($path);
    $array["status"] = "success";
    $array["data"] = $data;

    echo json_encode($array);
}

if (isset($_POST["jadikan_alumni"])) {
    // Memindahkan data dari db_siswa ke db_alumni
    $moveDataSql = "INSERT INTO db_alumni (nama, profile, tanggal_lahir, asal_sekolah, no_hp, kelas, tipeKelas, secondary_kelas, orang_tua, program, tlp_ortu, pekerjaan, alamat, username, password) SELECT nama, profile, tanggal_lahir, asal_sekolah, no_hp, kelas, tipeKelas, secondary_kelas, orang_tua, program, tlp_ortu, pekerjaan, alamat, username, password FROM db_siswa";

    if ($conn->query($moveDataSql) === TRUE) {
        // Mengosongkan tabel db_siswa
        $truncateSql = "TRUNCATE TABLE db_siswa";

        if ($conn->query($truncateSql) === TRUE) {
            echo "success";
        } else {
            echo "Error saat TRUNCATE tabel db_siswa: " . $conn->error;
        }
    } else {
        echo "Error saat memindahkan data: " . $conn->error;
    }
}
