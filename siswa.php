<?php
require_once("db_connect.php");
if (isset($_GET['read'])) {
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
    $sql = "SELECT * FROM db_siswa WHERE kelas = ? AND tipeKelas LIKE ? ORDER BY kelas, SUBSTRING_INDEX(tipeKelas, ' ', 1), SUBSTRING_INDEX(tipeKelas, ' ', -1) + 0, nama ASC LIMIT ?, ?";
    $sql_total = "SELECT COUNT(*) AS total FROM db_siswa";
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
        $sql = "SELECT * FROM db_siswa WHERE nama LIKE ? || asal_sekolah LIKE ? || username LIKE ? ORDER BY kelas, SUBSTRING_INDEX(tipeKelas, ' ', 1), SUBSTRING_INDEX(tipeKelas, ' ', -1) + 0, nama ASC LIMIT ?, ?";
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
    $sql_count = "SELECT COUNT(*) AS total FROM db_siswa WHERE kelas = ? AND tipeKelas LIKE ?";

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
        $sql_count = "SELECT COUNT(*) AS total FROM db_siswa WHERE nama LIKE ? || asal_sekolah LIKE ? || username LIKE ?";
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

if (isset($_GET['update'])) {
    // Mengambil input dari user
    $user               = $_POST['user'];
    $nama               = $_POST['nama'];
    $kelas              = $_POST['kelas'];
    $tipekelas          = $_POST['tipekelas'];
    $sekolah            = $_POST['sekolah'];
    $lahir              = $_POST['lahir'];
    $ortu               = $_POST['ortu'];
    $alamat             = $_POST['alamat'];
    $hp                 = $_POST['nohp'];
    $pass               = $_POST['pass'];
    $secondary_kelas    = $_POST['secondary_kelas'];
    $program            = $_POST['program'];
    $tlp_ortu           = $_POST['tlp_ortu'];
    $pekerjaan          = $_POST['pekerjaan'];
    $userOld            = $_POST['userOld'];

    $query = "UPDATE `db_siswa` SET `nama`='$nama',`Tanggal_lahir`='$lahir',`asal_sekolah`='$sekolah',`no_hp`='$hp',`kelas`='$kelas',`tipeKelas`='$tipekelas',`secondary_kelas`='$secondary_kelas',`orang_tua`='$ortu',`program`='$program',`tlp_ortu`='$tlp_ortu',`pekerjaan`='$pekerjaan',`alamat`='$alamat',`username`='$user',`password`='$pass' WHERE username = '$userOld'";
    $sql = mysqli_query($conn, $query);
    if ($sql) {
        echo "Berhasil";
    } else {
        echo "Gagal " . $query;
    }
}

if (isset($_GET['add'])) {
    $user               = $_POST['user'];
    $nama               = $_POST['nama'];
    $kelas              = $_POST['kelas'];
    $tipeKelas          = $_POST['tipekelas'];
    $sekolah            = $_POST['sekolah'];
    $lahir              = $_POST['lahir'];
    $ortu               = $_POST['ortu'];
    $alamat             = $_POST['alamat'];
    $hp                 = $_POST['nohp'];
    $pass               = $_POST['pass'];
    $secondary_kelas    = $_POST['secondary_kelas'];
    $program            = $_POST['program'];
    $tlp_ortu           = $_POST['tlp_ortu'];
    $pekerjaan          = $_POST['pekerjaan'];
    $profile            = "https://apiserver.bimbel-citrabagusgrup.com/karakter/karakter (" . rand(1, 15) . ").png";

    $query = "INSERT INTO `db_siswa`(`nama`, `profile`, `Tanggal_lahir`, `asal_sekolah`, `no_hp`, `kelas`, `tipeKelas`, `secondary_kelas`, `orang_tua`, `program`, `tlp_ortu`, `pekerjaan`, `alamat`, `username`, `password`) VALUES ('$nama','$profile','$lahir','$sekolah','$hp','$kelas','$tipeKelas','$secondary_kelas','$ortu','$program','$tlp_ortu','$pekerjaan','$alamat','$user','$pass')";
    $sql = mysqli_query($conn, $query);
    if ($sql) {
        echo "Berhasil";
    } else {
        echo "Gagal";
    }
}

if (isset($_GET['delete'])) {
    $user = $_POST['user'];
    $sql = "DELETE FROM `db_siswa` WHERE username=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user);
    if ($stmt->execute()) {
        echo "Berhasil";
    } else {
        echo "Gagal " . $stmt->error;
    }
    // Menutup prepared statement dan koneksi
    $stmt->close();
    $conn->close();
}

if (isset($_GET['login'])) {
    $username = $_GET['username'];
    $password = $_GET['password'];

    $sql = mysqli_query($conn, "SELECT * FROM db_siswa WHERE username = '$username' AND `password` = '$password'");
    $rows = mysqli_num_rows($sql);
    $dataArray = array();
    if ($rows == 1 && $sql) {
        while ($data = mysqli_fetch_assoc($sql)) {
            $dataArray[] = $data;
        }
        header("Content-Type: application/json");
        echo json_encode($dataArray);
    } else {
        echo "Gagal";
    }
}

if (isset($_GET['read_praregist'])) {
    $query = "SELECT * FROM `db_siswa`";
    $sql = mysqli_query($conn, $query);
    $array = array();
    if ($sql) {
        while ($data = mysqli_fetch_assoc($sql)) {
            if (!is_numeric($data["username"])) {
                $array[] = $data;
            }
        }
    }
    echo json_encode($array, true);
}

if (isset($_GET["addExcell"])) {
    $input = json_decode(file_get_contents('php://input'), true);
    foreach ($input as $key => $item) {
        if ($key === 0) {
            continue; // Melewati iterasi index pertama
        }

        $nama = $item[1];
        $profile = $item[14];
        $Tanggal_lahir = $item[2];
        $asal_sekolah = $item[3];
        $no_hp = $item[4];
        $kelas = $item[5];
        $tipeKelas = $item[6];
        $secondary_kelas = $item[7];
        $orang_tua = $item[8];
        $program = $item[9];
        $tlp_ortu = $item[10];
        $pekerjaan = $item[11];
        $alamat = $item[12];
        $username = $item[0];
        $password = $item[13];

        // Query untuk memasukkan data ke dalam tabel db_siswa
        $sql = "INSERT INTO db_siswa (nama, profile, Tanggal_lahir, asal_sekolah, no_hp, kelas, tipeKelas, secondary_kelas, orang_tua, program, tlp_ortu, pekerjaan, alamat, username, password)
                VALUES ('$nama', '$profile', '$Tanggal_lahir', '$asal_sekolah', '$no_hp', '$kelas', '$tipeKelas', '$secondary_kelas', '$orang_tua', '$program', '$tlp_ortu', '$pekerjaan', '$alamat', '$username', '$password')";

        if ($conn->query($sql) === TRUE) {
            echo "Berhasil";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}
