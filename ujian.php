<?php
require_once("db_connect.php");
if(isset($_POST['ujian'])){
    $dir_soal = "json/soal/".str_replace(" ", "_", $_POST['ujian'])."/";
}
if(isset($_GET['create_ujian']) || isset($_GET['update_ujian'])){
    if (!file_exists($dir_soal) && !is_dir($dir_soal)) {
        mkdir($dir_soal);
    }
}
function AddTabelNilai($conn, $ujian, $kelas, $mapel){
    $tableTryout = $ujian."_".$kelas;
    $queryCekTabel = "SHOW TABLES LIKE '$tableTryout'";
    $sql = mysqli_query($conn, $queryCekTabel);
    $callBack = false;
    if($sql){
        $sumTable = mysqli_num_rows($sql);
        if($sumTable == 0){
            $queryCreateTable = "CREATE TABLE $tableTryout (username VARCHAR(10), nama TEXT, kelas VARCHAR(12), `$mapel` VARCHAR(5))";
        }else{
            $queryCreateTable = "ALTER TABLE `$tableTryout` ADD `$mapel` VARCHAR(5)";
        }
        $sqlTable = mysqli_query($conn, $queryCreateTable);
        if($sqlTable){
            $callBack = true;
        } else {
            // Tampilkan pesan error jika gagal membuat atau mengubah tabel
            die("Error Add Table: " . mysqli_error($conn));
        }
    }

    return $callBack;
}

function EditTabelNilai($conn, $data){
    $callBack = false;
    $tableTryout = $data['ujian']."_".$data['kelas'];
    $tableTryoutLama = $data['ujian_lama']."_".$data['kelas_lama'];
    if($data['kelas'] == $data['kelas_lama'] && $data['ujian'] == $data['ujian_lama']){
        $queryUpdateTable = "ALTER TABLE `$tableTryout` CHANGE `$data[mapel_lama]` `$data[mapel]` VARCHAR(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL; ";
    }else{
        $queryCekTabel = mysqli_query($conn, "SHOW TABLES LIKE '$tableTryout'");
        $sumTable = mysqli_num_rows($queryCekTabel);
        if($sumTable == 0){
            $queryUpdateTable = "CREATE TABLE $tableTryout (username VARCHAR(10), nama TEXT, kelas VARCHAR(12), $data[mapel] VARCHAR(5))";
        }else{
            $queryUpdateTable = "ALTER TABLE `$tableTryout` ADD `$data[mapel]` VARCHAR(5)";
        }
    }
    try { 
        $sql = mysqli_query($conn, $queryUpdateTable);  
        if($sql){
            if($data['kelas'] == $data['kelas_lama'] && $data['ujian'] == $data['ujian_lama']){
                $callBack = true;
            }else{
                $sqlCekBaris = mysqli_query($conn, "DESCRIBE $tableTryoutLama;");
                if(mysqli_num_rows($sqlCekBaris) == 3){
                    $queryDeleteTable =  "DROP TABLE `$tableTryoutLama`";
                }else{
                    $queryDeleteTable =  "ALTER TABLE `$tableTryoutLama` DROP `$data[mapel_lama]`;";
                }
    
                $sqlDelTable = mysqli_query($conn, $queryDeleteTable); 
                if($sqlDelTable){
                    $callBack = true;
                }
            }
        }
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage();
    }

    return $callBack;
}

function create_dir($dir_path){
    mkdir($dir_path, 0777, true);
}

function create_file($file, $jumlahSoal){
    $data = [];

    for ($i = 1; $i <= $jumlahSoal; $i++) {
        // Create an array representing each item
        $item = [
            "No" => $i,
            "Jawaban" => ""
        ];
        // Add the item to the data array
        $data[] = $item;
    }

    $json_data = json_encode($data, JSON_PRETTY_PRINT);

    if(file_exists($file)){
        return true;
    }else{
        if (file_put_contents($file, $json_data)) {
            return true;
        } else {
            return false;
        }
    }
}

function compareNumbers($a, $b) {
    return $a['Number'] - $b['Number'];
}

if(isset($_GET['create_ujian'])){
    // Cek apakah variabel POST ada dan valid
    if(isset($_POST['ujian']) && isset($_POST['mapel']) && isset($_POST['kelas']) && isset($_POST['tanggal']) && isset($_POST['jam_mulai']) && isset($_POST['jam_selesai'])){
        // Filter variabel POST untuk menghapus spasi dan karakter khusus
        $ujian = filter_var($_POST['ujian'], FILTER_SANITIZE_STRING);
        $ujian = str_replace(' ', '_', $ujian);
        $mapel = filter_var($_POST['mapel'], FILTER_SANITIZE_STRING);
        $mapel = str_replace(' ', '_', $mapel);
        $kelas = filter_var($_POST['kelas'], FILTER_SANITIZE_STRING);
        $tanggal = filter_var($_POST['tanggal'], FILTER_SANITIZE_STRING);
        $jam_mulai = filter_var($_POST['jam_mulai'], FILTER_SANITIZE_STRING);
        $jam_selesai = filter_var($_POST['jam_selesai'], FILTER_SANITIZE_STRING);
        $status = 'hidden';

        $tabelNilai = AddTabelNilai($conn, $ujian, $kelas, $mapel);
        if($tabelNilai){
            // Gunakan prepared statement untuk mencegah SQL injection
            $query = "INSERT INTO `db_ujian`(`nama_ujian`, `mapel`, `kelas`, `tanggal`, `jam_mulai`, `jam_selesai`, `status`) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $query);
            if($stmt){
                mysqli_stmt_bind_param($stmt, "sssssss", $ujian, $mapel, $kelas, $tanggal, $jam_mulai, $jam_selesai, $status);
                // Eksekusi query
                if ( mysqli_stmt_execute($stmt) ) {
                    $file_json = $ujian."_".$mapel."_".$kelas.".json";
                    $dataAwalJson = array();
                    $jsonText = json_encode($dataAwalJson, JSON_PRETTY_PRINT);
                    file_put_contents($dir_soal.$file_json, $jsonText);
                    if(is_file($dir_soal.$file_json)){
                        echo "Berhasil";
                    }else{
                        $tableName = $ujian."_".$kelas;
                        $sqlCekTabel = mysqli_query($conn, "DESCRIBE $tableName;");
                        if(mysqli_num_rows($sqlCekTabel) == 1){
                            $sqlDelete = mysqli_query($conn, "DROP TABLE `$tableName`");
                        }else{
                            $sqlDelete = mysqli_query($conn, "ALTER TABLE `$tableName` DROP `$mapel`;");
                        }
                        if($sqlDelete){
                            $sqlDeleteData = mysqli_query($conn, "DELETE FROM `db_ujian` WHERE nama_ujian = '$ujian' && mapel = '$mapel' && kelas = '$kelas'");
                            if($sqlDeleteData){
                                echo "Gagal Membuat File Ujian";
                            }
                        }
                    }

                } else {
                    // Jika gagal, tampilkan pesan error
                    echo 'Error: ' . mysqli_stmt_error($stmt);
                }
                // Tutup statement
                mysqli_stmt_close($stmt);
            } else {
                // Jika gagal, tampilkan pesan error
                echo 'Error: ' . mysqli_error($conn);
            }
        
            // Tutup koneksi
            $conn->close();
        }else{
            echo "Tabel Nilai Gagal dibuat";
        }
    } else {
        // Jika variabel POST tidak ada atau tidak valid, tampilkan pesan error
        echo "Data tidak valid";
    }

}

if(isset($_GET['update_ujian']) && !empty($_POST)){
    $id = mysqli_real_escape_string($conn, $_POST['id']);

    $ujian = mysqli_real_escape_string($conn, $_POST['ujian']);
    $ujian = str_replace(' ', '_', $ujian);

    $ujian_lama = mysqli_real_escape_string($conn, $_POST['ujian_lama']);
    $ujian_lama = str_replace(' ', '_', $ujian_lama);

    $mapel = mysqli_real_escape_string($conn, $_POST['mapel']);
    $mapel = str_replace(' ', '_', $mapel);

    $mapel_lama = mysqli_real_escape_string($conn, $_POST['mapel_lama']);
    $mapel_lama = str_replace(' ', '_', $mapel_lama);

    $kelas = mysqli_real_escape_string($conn, $_POST['kelas']);

    $kelas_lama = mysqli_real_escape_string($conn, $_POST['kelas_lama']);

    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $jam_mulai = mysqli_real_escape_string($conn, $_POST['jam_mulai']);
    $jam_selesai = mysqli_real_escape_string($conn, $_POST['jam_selesai']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $dir_soal_lama = "json/soal/$ujian_lama/";

    $data = array(
        'id' => $id,
        'ujian' => $ujian,
        'ujian_lama' => $ujian_lama,
        'mapel' => $mapel,
        'mapel_lama' => $mapel_lama,
        'kelas' => $kelas,
        'kelas_lama' => $kelas_lama,
        'tanggal' => $tanggal,
        'jam_mulai' => $jam_mulai,
        'jam_selesai' => $jam_selesai,
        'status' => $status
    );

    $tabelNilai = EditTabelNilai($conn, $data);

    if($tabelNilai){
        $query = "UPDATE `db_ujian` SET `nama_ujian`=?,`mapel`=?,`kelas`=?,`tanggal`=?,`jam_mulai`=?,`jam_selesai`=?,`status`=? WHERE `id`=?";
        // Eksekusi query
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssssss", $ujian, $mapel, $kelas, $tanggal, $jam_mulai, $jam_selesai, $status, $id);
        if ($stmt->execute()) {
            // Jika berhasil, tampilkan pesan sukses
            $file_json = $ujian."_".$mapel."_".$kelas.".json";
            $file_json_lama = $ujian_lama."_".$mapel_lama."_".$kelas_lama.".json";
            if($ujian == $ujian_lama && $mapel == $mapel_lama && $kelas == $kelas_lama){
                echo 'Berhasil';
            }else{
                rename($dir_soal_lama.$file_json_lama, $dir_soal.$file_json);
                if(is_file($dir_soal.$file_json)){
                    echo "Berhasil";
                }else{
                    $queryBack = "UPDATE `db_ujian` SET `nama_ujian`=?,`mapel`=?,`kelas`=?,`tanggal`=?,`jam_mulai`=?,`jam_selesai`=?,`status`=? WHERE `id`=?";
                    $stmt = $conn->prepare($queryBack);
                    $stmt->bind_param("ssssssss", $ujian_lama, $mapel_lama, $kelas_lama, $tanggal, $jam_mulai, $jam_selesai, $status, $id);
                    if($stmt->execute()){
                        echo "Gagal Update nama File";
                    }else{
                        echo "Sistem Gagal, Hubungi Developer";
                    }
                }
            }
        } else {
            // Jika gagal, tampilkan pesan error
            echo 'Error: ' . $stmt->error;
        }

        // Tutup koneksi
        $conn->close();
    }else{
        echo "Tabel Nilai Gagal dibuat";
    }
}

if(isset($_GET['update_status'])){
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $query = "UPDATE `db_ujian` SET `status`=? WHERE `id`=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $status, $id);
    if ($stmt->execute()) {
        echo "Berhasil";
    }else {
        // Jika gagal, tampilkan pesan error
        echo 'Error: ' . $stmt->error;
    }
}

if(isset($_GET['read_ujian'])){
    header("Content-Type: application/json");
    $dataArray = array();

    if(!isset($_GET['search'])){
        $query = 'SELECT `nama_ujian` FROM `db_ujian`';
        $sql = mysqli_query($conn, $query);
        if(mysqli_num_rows($sql)>0){
            while($data = mysqli_fetch_assoc($sql)){
                $dataArray[] = $data;
            }
            $dataArray = array_values(array_unique($dataArray, SORT_REGULAR));
            sort($dataArray);
        }
        foreach ($dataArray as &$item) {
            $item['nama_ujian'] = str_replace('_', ' ', $item['nama_ujian']);
        }
    }else{
        $search = $_GET['search'];
        $search = str_replace(' ', '_', $search);
        $query = "SELECT * FROM `db_ujian` WHERE nama_ujian LIKE '%$search%' || mapel LIKE '%$search%' || kelas = '$search'";
        $sql = mysqli_query($conn, $query);
        if(mysqli_num_rows($sql)>0){
            while($data = mysqli_fetch_assoc($sql)){
                $dataArray[] = $data;
            }
            usort($dataArray, function($a, $b) {
                return intval($a['kelas']) - intval($b['kelas']);
            });
        }
        foreach ($dataArray as &$item) {
            $item['nama_ujian'] = str_replace('_', ' ', $item['nama_ujian']);
            $item['mapel'] = str_replace('_', ' ', $item['mapel']);
        }
    }
    echo json_encode($dataArray);


}

if(isset($_GET['delete_ujian'])){
    $ujian = $_POST['ujian'];
    $ujian = str_replace(' ', '_', $ujian);
    $mapel = $_POST['mapel'];
    $mapel = str_replace(' ', '_', $mapel);
    $kelas = $_POST['kelas'];
    $id = $_POST['id'];

    $nama_tabel = $ujian."_".$kelas;
    $nama_file = $ujian."_".$mapel."_".$kelas.".json";
    if(unlink($dir_soal.$nama_file)){
        $sql = mysqli_query($conn, "DESCRIBE $nama_tabel;");
        if(mysqli_num_rows($sql) == 3){
            $sqlDelete = mysqli_query($conn, "DROP TABLE `$nama_tabel`");
        }else{
            $sqlDelete = mysqli_query($conn, "ALTER TABLE `$nama_tabel` DROP `$mapel`;");
        }

        if($sqlDelete){
            $sqlDeleteTabel = mysqli_query($conn, "DELETE FROM `db_ujian` WHERE id = $id");
            if($sqlDeleteTabel){
                echo "Berhasil";
            }else{
                echo "Gagal menghapus tabel, Hubungi developer";
            }
        }
    }else{
        echo "File gagal dihapus";
    }
}

// Soal

if(isset($_GET['input_soal'])){
    $ujian = str_replace(" ", "_", $_POST['ujian']);
    $mapel = str_replace(" ", "_", $_POST['mapel']);
    $kelas = $_POST['kelas'];

    // Data Soal
    $Number = $_POST['number'];
    $Soal = $_POST['soal'];
    $A = $_POST['A'];
    $B = $_POST['B'];
    $C = $_POST['C'];
    $D = $_POST['D'];
    $E = $_POST['E'];
    $Kunci = $_POST['kunci'];
    $tipeJawaban = $_POST['tipe'];

    $file_json = $ujian."_".$mapel."_".$kelas.".json";

    $number_duplicate = false;
    // Cek apakah file JSON ada
    if(file_exists($dir_soal.$file_json)){
        // Baca file JSON
        $anggota = file_get_contents($dir_soal.$file_json);
        // Decode data JSON
        $data = json_decode($anggota, true);
        // Cek apakah data JSON valid
        if(is_array($data) && $Number != "" && $Soal != ""){
            foreach ($data as $key => $d) {
                // Perbarui data kedua
                if ($d['Number'] == $Number) {
                    $number_duplicate = true;
                }
            }
            if(!$number_duplicate){
                // Tambah data baru ke array
                $data[] = array(
                    "Number"    => $Number,
                    "Soal"      => $Soal,
                    "A"         => $A,
                    "B"         => $B,
                    "C"         => $C,
                    "D"         => $D,
                    "E"         => $E,
                    "Kunci"     => $Kunci,
                    "tipe"      => $tipeJawaban
                );
                // Encode array ke JSON
                usort($data, 'compareNumbers');
                $jsonFile = json_encode($data, JSON_PRETTY_PRINT);
                // Tulis data JSON ke file
                $anggota = file_put_contents($dir_soal.$file_json, $jsonFile);
                // Cek apakah penulisan berhasil
                if(is_int($anggota)){
                    // Validasi jika data berhasil ditambah
                    echo "Berhasil";
                } else {
                    // Tampilkan pesan error jika gagal menulis file
                    echo "Gagal menulis file JSON";
                }
            }else{
                echo "Nomer Sudah Ada";
            }
        } else {
            // Tampilkan pesan error jika data JSON tidak valid
            echo "Data JSON tidak valid";
        }
    } else {
        // Tampilkan pesan error jika file JSON tidak ada
        echo "File JSON tidak ada";
    }
}

if(isset($_GET['update_soal'])){
    $ujian = str_replace(" ", "_", $_POST['ujian']);
    $mapel = str_replace(" ", "_", $_POST['mapel']);
    $kelas = $_POST['kelas'];

    // Data Soal
    $Number = $_POST['number'];
    $Number_old = $_POST['number_old'];
    $Soal = $_POST['soal'];
    $A = $_POST['A'];
    $B = $_POST['B'];
    $C = $_POST['C'];
    $D = $_POST['D'];
    $E = $_POST['E'];
    $Kunci = $_POST['kunci'];
    $tipeJawaban = $_POST['tipe'];
    

    $file_json = $ujian."_".$mapel."_".$kelas.".json";

    if(file_exists($dir_soal.$file_json)){
        // Baca file JSON
        $anggota = file_get_contents($dir_soal.$file_json);
        // Decode data JSON
        $data = json_decode($anggota, true);
        // Cek apakah data JSON valid
        if(is_array($data) && $Number != "" && $Soal != ""){
            foreach ($data as $key => $d) {
                // Perbarui data kedua
                if ($d['Number'] === $Number_old) {
                    $data[$key]['Number'] = $Number;
                    $data[$key]['Number'] = $Number;
                    $data[$key]['Soal'] = $Soal;
                    $data[$key]['A'] = $A;
                    $data[$key]['B'] = $B;
                    $data[$key]['C'] = $C;
                    $data[$key]['D'] = $D;
                    $data[$key]['E'] = $E;
                    $data[$key]['Kunci'] = $Kunci;
                    $data[$key]['tipe'] = $tipeJawaban;
                }
            }

            // Encode array ke JSON
            usort($data, 'compareNumbers');
            $jsonFile = json_encode($data, JSON_PRETTY_PRINT);
            // Cek apakah encoding berhasil
            if(json_last_error() == JSON_ERROR_NONE){
                // Tulis data JSON ke file
                $anggota = file_put_contents($dir_soal.$file_json, $jsonFile);
                // Cek apakah penulisan berhasil
                if(is_int($anggota)){
                    // Validasi jika data berhasil ditambah
                    echo "Berhasil";
                } else {
                    // Tampilkan pesan error jika gagal menulis file
                    echo "Gagal menulis file JSON";
                }
            } else {
                // Tampilkan pesan error jika gagal encoding JSON
                echo "Gagal encoding data JSON";
            }
        } else {
            // Tampilkan pesan error jika data JSON tidak valid
            echo "Data JSON tidak valid";
        }
    } else {
        // Tampilkan pesan error jika file JSON tidak ada
        echo "File JSON tidak ada";
    }
}

if(isset($_GET['update_kunci'])){
    $newData = json_decode(file_get_contents("php://input"), true); // tambahkan parameter true untuk mendapatkan array asosiatif
    $dir_soal = "json/soal/".str_replace(" ", "_", $newData['Get_Ujian'])."/";
    $ujian = str_replace(" ", "_", $newData['Get_Ujian']);
    $mapel = str_replace(" ", "_", $newData['Get_Mapel']);
    $kelas = $newData['Get_Kelas'];
    $file_json = $ujian."_".$mapel."_".$kelas.".json";
    if(file_exists($dir_soal.$file_json)){
        // Baca file JSON
        $anggota = file_get_contents($dir_soal.$file_json);
        // Decode data JSON
        $data = json_decode($anggota, true);
        // Cek apakah data JSON valid
        if(is_array($data) && is_array($newData['data'])){ // gunakan $newData['data'] bukan $newData
            foreach ($data as $key => $d) {
                foreach ($newData['data'] as $n){ // gunakan $n bukan $new_key dan $n
                    // Perbarui data kedua
                    if ($d['Number'] === $n['Number']) {
                        $data[$key]['Kunci'] = $n['Kunci'];
                        $data[$key]['tipe'] = $n['Tipe'];
                    }
                }
            }
    
            // Encode array ke JSON
            usort($data, 'compareNumbers');
            $jsonFile = json_encode($data, JSON_PRETTY_PRINT);
            // Cek apakah encoding berhasil
            if(json_last_error() == JSON_ERROR_NONE){
                // Tulis data JSON ke file
                $anggota = file_put_contents($dir_soal.$file_json, $jsonFile);
                // Cek apakah penulisan berhasil
                if(is_int($anggota)){
                    // Validasi jika data berhasil ditambah
                    echo "Berhasil";
                } else {
                    // Tampilkan pesan error jika gagal menulis file
                    echo "Gagal menulis file JSON";
                }
            } else {
                // Tampilkan pesan error jika gagal encoding JSON
                echo "Gagal encoding data JSON";
            }
        } else {
            // Tampilkan pesan error jika data JSON tidak valid
            echo "Data JSON tidak valid";
        }
    } else {
        // Tampilkan pesan error jika file JSON tidak ada
        echo "File JSON tidak ada";
        echo $dir_soal.$file_json;
    }

}

if(isset($_GET['read_soal'])){
    $ujian = str_replace(" ", "_", $_POST['ujian']);
    $mapel = str_replace(" ", "_", $_POST['mapel']);
    $kelas = $_POST['kelas'];

    $file_json = $ujian."_".$mapel."_".$kelas.".json";

    if (isset($_GET["jawaban"])) {
        $nama = $_POST["nama"];
        $file_jawaban = "json/jawaban/".$ujian."_".$mapel."_".$kelas."/".$nama.".json";
        if(!file_exists($file_jawaban)){
            $dir_path = "json/jawaban/".$ujian."_".$mapel."_".$kelas;
            $anggotaSoal = file_get_contents("json/soal/".$ujian."/".$file_json);
            $dataSoal = json_decode($anggotaSoal, true);
            if (!is_dir($dir_path)) {
                $systemFiles = create_dir($dir_path);
                $systemFiles = create_file($file_jawaban, count($dataSoal));
            } else {
                $systemFiles = create_file($file_jawaban, count($dataSoal));
            }
        }
        $anggotaJawaban = file_get_contents($file_jawaban);
        $dataJawaban = json_decode($anggotaJawaban, true);
    }

    if (file_exists($dir_soal.$file_json)) {
        header("Content-Type: application/json");
        $anggota = file_get_contents($dir_soal.$file_json);
        $data = json_decode($anggota, true);

        if (isset($_GET["jawaban"]) && file_exists($file_jawaban)) {

            foreach ($data as $key => $d) {
                foreach ($dataJawaban as $keyJ => $dj) {
                    if ($d['Number'] == $dj['No']) { 
                        $data[$key]["Jawaban"] = $dj["Jawaban"];
                        break; 
                    }
                }
            }
        }else{
            foreach ($data as $key => $d) {
                $data[$key]["Jawaban"] = "";
                break;
            }
        }

        echo json_encode($data);
    } else {
        echo "Data Tidak ada ";
    }

}

if(isset($_GET['delete_soal'])){
    $ujian = str_replace(" ", "_", $_POST['ujian']);
    $mapel = str_replace(" ", "_", $_POST['mapel']);
    $kelas = $_POST['kelas'];
    $Number = $_POST['number'];
    $file_json = $ujian."_".$mapel."_".$kelas.".json";

    if(file_exists($dir_soal.$file_json)){
        // Baca file JSON
        $anggota = file_get_contents($dir_soal.$file_json);
        // Decode data JSON
        $data = json_decode($anggota, true);
        // Cek apakah data JSON valid
        if(is_array($data)){
            foreach ($data as $key => $d) {
                // Perbarui data kedua
                if ($d['Number'] === $Number) {
                    array_splice($data, $key, 1);
                }
            }

            // Encode array ke JSON
            $jsonFile = json_encode($data, JSON_PRETTY_PRINT);
            // Cek apakah encoding berhasil
            if(json_last_error() == JSON_ERROR_NONE){
                // Tulis data JSON ke file
                $anggota = file_put_contents($dir_soal.$file_json, $jsonFile);
                // Cek apakah penulisan berhasil
                if(is_int($anggota)){
                    // Validasi jika data berhasil ditambah
                    echo "Berhasil";
                } else {
                    // Tampilkan pesan error jika gagal menulis file
                    echo "Gagal menulis file JSON";
                }
            } else {
                // Tampilkan pesan error jika gagal encoding JSON
                echo "Gagal encoding data JSON";
            }
        } else {
            // Tampilkan pesan error jika data JSON tidak valid
            echo "Data JSON tidak valid";
        }
    } else {
        // Tampilkan pesan error jika file JSON tidak ada
        echo "File JSON tidak ada";
    }   
}
?>