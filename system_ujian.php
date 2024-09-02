<?php
require_once("db_connect.php");
// error_reporting(0);

if(isset($_POST["dir"])){
    $dir_path = "json/jawaban/".$_POST["dir"];
} 

function validasiJawabanEsay($kunci, $jawaban) {
    $kataKunci = strtolower($kunci);
    $jawabanKata = strtolower($jawaban);
    $kunciKata = explode(" ", $kataKunci);
    $jawabanKata = explode(" ", $jawabanKata);
    $kunciLen = count($kunciKata);
    $jawabanLen = count($jawabanKata);
    
   // Jika jumlah kata pada kunci dan jawaban tidak sama
   if ($kunciLen !== $jawabanLen) {
        // Menghitung jumlah kata yang benar
        $benar = 0;
        for ($i = 0; $i < min($kunciLen, $jawabanLen); $i++) {
            $kataKunci = $kunciKata[$i];
            $kataJawaban = $jawabanKata[$i];
            similar_text($kataKunci, $kataJawaban, $percentage);
            if ($percentage >= 70) {
                $benar++;
            }
        }
        $persentaseKesamaan = $benar / max($kunciLen, $jawabanLen) * 100;
        return ($persentaseKesamaan >= 70) ? "benar" : "salah";
    }
    
    // Loop melalui setiap kata dalam kunci dan jawaban
    for ($i = 0; $i < $kunciLen; $i++) {
        $kataKunci = $kunciKata[$i];
        $kataJawaban = $jawabanKata[$i];
        
        // Membandingkan kata dalam kunci dan jawaban
        similar_text($kataKunci, $kataJawaban, $percentage);
        
        // Jika persentase kesamaan kurang dari 70%, dianggap "salah"
        if ($percentage <= 70) {
            return "salah";
        }
    }
    
    return "benar";
}

function create_dir($dir_path){
    mkdir($dir_path, 0777, true);
    return create_file($dir_path);
}

function create_file($dir_path){
    $file = $dir_path."/".$_POST["file"].".json";
    $jumlahSoal = $_POST["jumlah_soal"];

    $data = [];

    for ($i = 1; $i <= $jumlahSoal; $i++) {
        // Create an array representing each item
        $item = [
            "No" => $i,
            "Jawaban" => "",
            "Ragu"=>false
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

function formatWaktuTelat($created_time, $waktu_masuk) {
    $telat_seconds = strtotime($created_time) - $waktu_masuk;

    $days = floor($telat_seconds / (60 * 60 * 24));
    $telat_seconds -= $days * (60 * 60 * 24);

    $hours = floor($telat_seconds / (60 * 60));
    $telat_seconds -= $hours * (60 * 60);

    $minutes = floor($telat_seconds / 60);
    $telat_seconds -= $minutes * 60;

    return "".$days." Hari ".$hours." Jam ".$minutes." Menit ".$telat_seconds." Detik";
}

if(isset($_POST["read_jawaban"])){
    $systemFiles = false;
    $file = $dir_path."/".$_POST["file"].".json";

    if($_POST["file"] != "" && $_POST["dir"] != ""){
        if (!is_dir($dir_path)) {
            $systemFiles = create_dir($dir_path);
        } else {
            $systemFiles = create_file($dir_path);
        }
    
        if($systemFiles){
            // Mengecek apakah file tidak kosong sebelum membaca
            if(file_exists($file) && filesize($file) > 0){
                $json_data = file_get_contents($file);
                // Mengecek apakah data JSON yang diperoleh tidak null sebelum di-decode
                if($json_data !== false){
                    $arrayData = json_decode($json_data, true);
                    if($arrayData !== null){
                        // Output the JSON data
                        header('Content-Type: application/json');
                        echo json_encode($arrayData);
                        exit(); // Menghentikan eksekusi selanjutnya setelah mengirimkan response
                    }
                }
            } else {
                // Jika file kosong, hapus file tersebut
                unlink($file);
            }
        }            
    }
    echo "[]"; // Output default jika terjadi kesalahan
    exit(); // Menghentikan eksekusi selanjutnya setelah mengirimkan response
}

if(isset($_POST["edit_jawaban"])){
    $file = $dir_path."/".$_POST["file"].".json";
    $json_data = file_get_contents($file);
    $data = json_decode($json_data, true);
    $no_to_edit = $_POST['No'];
    $jawaban_to_edit = $_POST['Jawaban'];
    $ragu_ragu = strtolower($_POST["ragu"]) === "true" ? true : false;

    foreach ($data as $key => $d) {
        // Perbarui data kedua
        if ($d['No'] == $no_to_edit) {
            $data[$key]['Ragu'] = $ragu_ragu;
            $data[$key]['Jawaban'] = $jawaban_to_edit;
        }
    }
    
    $updated_json_data = json_encode($data, JSON_PRETTY_PRINT);

    // Write the updated JSON data back to the file
    if (file_put_contents($file, $updated_json_data)) {
        echo "Berhasil";
    } else {
        echo "Gagal mengupdate file JSON.";
    }
}

if(isset($_POST["kirim_jawaban"])){
    $nama_ujian = $_POST["nama_ujian"];
    $mapel = $_POST["mapel"];
    $kelas = $_POST["kelas"];
    $nama_siswa = $_POST["nama_siswa"];
    $pathJawaban = "json/jawaban/".$nama_ujian."_".$mapel."_".$kelas."/".$nama_siswa.".json";
    $pathSoal = "json/soal/".$nama_ujian."/".$nama_ujian."_".$mapel."_".$kelas.".json";
    $user = $_POST["username"];
    $tipeKelas= $_POST["tipeKelas"];

    $anggotaSoal = [];
    $anggotaJawaban = [];
    $anggotaSetPenilaian = array();

    $fileJawaban = file_get_contents($pathJawaban);
    $arrayJawaban = json_decode($fileJawaban, true);

    $fileSoal = file_get_contents($pathSoal);
    $arraySoal = json_decode($fileSoal, true);
    
    $benar = 0;
    $jumlahSoal = count($arraySoal);

    foreach($arraySoal as $soal){
        $nomorSoal = $soal["Number"];
        $tipeSoal = $soal["tipe"];
        $kunciJawaban = $soal["Kunci"];

        foreach($arrayJawaban as $jawaban){
            if($jawaban["No"] == $nomorSoal && $tipeSoal == "esay"){
                $jawabanSiswa = $jawaban["Jawaban"];

                $status = validasiJawabanEsay($kunciJawaban, $jawabanSiswa);
                if($status == "benar"){
                    $benar++;
                }
                break;
            }elseif($jawaban["No"] == $nomorSoal && $tipeSoal != "esay"){
                $jawabanSiswa = $jawaban["Jawaban"];

                // Memeriksa apakah jawaban siswa sama dengan kunci jawaban
                if($jawabanSiswa == $kunciJawaban){
                    $benar++;
                }
                break;
            }
        }
    }

    $query = "SELECT * FROM `set_penilaian`";
    $sql = mysqli_query($conn, $query);
    if($sql){
        while($data = mysqli_fetch_assoc($sql)){
            $anggotaSetPenilaian[]= $data;
        }
    }

    $kirim = false;
    foreach($anggotaSetPenilaian as $SP){
        if($SP["kelas"] == $kelas){
            if($SP["tipe"] == $nama_ujian."_".$mapel."_".$kelas){
                $setPenilaian = $SP["penilaian"];
                $setPenilaian = str_replace("Benar", $benar, $setPenilaian);
                $setPenilaian = str_replace("Jumlah Soal", $jumlahSoal, $setPenilaian);
                eval("\$result = $setPenilaian;");
                $kirim = true;
            }else{
                if($SP["tipe"] == "All"){
                    $setPenilaian = $SP["penilaian"];
                    $setPenilaian = str_replace("Benar", $benar, $setPenilaian);
                    $setPenilaian = str_replace("Jumlah Soal", $jumlahSoal, $setPenilaian);
                    eval("\$result = $setPenilaian;");
                    $kirim = true;
                }else{
                    echo "Format Penilaian Belum Dibuat";
                }
            }
            if($kirim){
                $namaOri = str_replace("_", " ", $nama_siswa);
                $query = "INSERT INTO `".$nama_ujian."_".$kelas."`(`username`, `nama`, `kelas`, `$mapel`) VALUES ('$user','$namaOri','".$kelas." ".$tipeKelas."','$result')";
                $queryCek = "SELECT * FROM `".$nama_ujian."_".$kelas."` WHERE username = '$user'";
                $sqlCek = mysqli_query($conn, $queryCek);
                if(mysqli_num_rows($sqlCek) > 0){
                    $query = "UPDATE `".$nama_ujian."_".$kelas."` SET `$mapel`='$result' WHERE username = '$user'";
                }
                $queryCek = "SELECT * FROM `".$nama_ujian."_".$kelas."` WHERE username = '$user' && ($mapel!='' && $mapel > 0)";
                $sqlCek = mysqli_query($conn, $queryCek);
                if(mysqli_num_rows($sqlCek) == 0){
                    $sql = mysqli_query($conn, $query);
                    if($sql){
                        echo "Berhasil";
                    }else{
                        echo "Gagal Mengirim Ke database";
                    }
                }elseif(isset($_POST["admin"])){
                    $sql = mysqli_query($conn, $query);
                    if($sql){
                        echo "Berhasil";
                    }else{
                        echo "Gagal Mengirim Ke database";
                    }
                }else{
                    echo "Anda sudah mengirimkan jawaban";
                }
            }else{
                echo "Gagal Menilai Jawaban";
            }
        }
    }
    if(count($anggotaSetPenilaian) == 0){
        echo "Format Penilaian Belum Dibuat";
    }
}

if(isset($_GET["validasiJawaban"])){
    $cek = validasiJawabanEsay($_POST["kunci"], $_POST["jawaban"]);
    echo $cek;
}

if(isset($_GET["update_nilai_manual"])){
    $benar = 0;

    $postData = file_get_contents('php://input');
    $data = json_decode($postData, true);

    $checkedStates = $data['kirimData']['checkedStates'];

    $nama_ujian     = $data['kirimData']['nama_ujian'];
    $mapel          = $data['kirimData']['mapel'];
    $kelas          = $data['kirimData']['kelas'];
    $nama_siswa     = $data['kirimData']['nama_siswa'];
    $user           = $data['kirimData']['username'];
    $tipeKelas      = $data['kirimData']['tipeKelas'];
    $pathJawaban    = "json/jawaban/".$nama_ujian."_".$mapel."_".$kelas."/".$nama_siswa.".json";
    $pathSoal       = "json/soal/".$nama_ujian."/".$nama_ujian."_".$mapel."_".$kelas.".json";

    $jumlahSoal     = count($checkedStates);

    foreach ($checkedStates as $key => $value) {
        if ($value === true) {
            $benar++;
        }
    }

    
    $query = "SELECT * FROM `set_penilaian`";
    $sql = mysqli_query($conn, $query);
    if($sql){
        while($data = mysqli_fetch_assoc($sql)){
            $anggotaSetPenilaian[]= $data;
        }
    }

    $kirim = false;
    foreach($anggotaSetPenilaian as $SP){
        if($SP["kelas"] == $kelas){
            if($SP["tipe"] == $nama_ujian."_".$mapel."_".$kelas){
                $setPenilaian = $SP["penilaian"];
                $setPenilaian = str_replace("Benar", $benar, $setPenilaian);
                $setPenilaian = str_replace("Jumlah Soal", $jumlahSoal, $setPenilaian);
                eval("\$result = $setPenilaian;");
                $kirim = true;
            }else{
                if($SP["tipe"] == "All"){
                    $setPenilaian = $SP["penilaian"];
                    $setPenilaian = str_replace("Benar", $benar, $setPenilaian);
                    $setPenilaian = str_replace("Jumlah Soal", $jumlahSoal, $setPenilaian);
                    eval("\$result = $setPenilaian;");
                    $kirim = true;
                }else{
                    echo "Format Penilaian Belum Dibuat";
                }
            }
            if($kirim){
                $namaOri = str_replace("_", " ", $nama_siswa);
                $query = "INSERT INTO `".$nama_ujian."_".$kelas."`(`username`, `nama`, `kelas`, `$mapel`) VALUES ('$user','$namaOri','".$kelas." ".$tipeKelas."','$result')";
                $queryCek = "SELECT * FROM `".$nama_ujian."_".$kelas."` WHERE username = '$user'";
                $sqlCek = mysqli_query($conn, $queryCek);
                if(mysqli_num_rows($sqlCek) > 0){
                    $query = "UPDATE `".$nama_ujian."_".$kelas."` SET `$mapel`='$result' WHERE username = '$user'";
                }
                $sql = mysqli_query($conn, $query);
                if($sql){
                    echo "Berhasil";
                }else{
                    echo "Gagal Mengirim Ke database";
                }
            }else{
                echo "Gagal Menilai Jawaban";
            }
        }
    }
    if(count($anggotaSetPenilaian) == 0){
        echo "Format Penilaian Belum Dibuat";
    }
}

if(isset($_GET["cek_jawaban"])){
    $ujian = $_POST["ujian"];
    $mapel = $_POST["mapel"];
    $kelas = $_POST["kelas"];
    $waktu_masuk = strtotime($_POST["mulai"]);
    $folder_path = 'json/jawaban/'.$ujian.'_'.$mapel.'_'.$kelas;

    // Inisialisasi variabel untuk menyimpan hasil
    $nama_ujian = str_replace("_", " ", $ujian.'_'.$mapel);
    $results = [
        "nama_ujian"=>$nama_ujian,
        "kelas"=>$kelas,
        "jumlah_data"=>0,
        "data"=>[]
    ];

    // Mendapatkan daftar file dalam folder
    $files = scandir($folder_path);

    // Iterasi melalui setiap file dalam folder
    foreach ($files as $file) {
        // Hanya memproses file dengan ekstensi .json
        if (pathinfo($file, PATHINFO_EXTENSION) === 'json') {
            // Membaca waktu pembuatan file
            $created_time = filectime("$folder_path/$file");
            
            // Membaca waktu terakhir file diedit
            $last_modified_time = filemtime("$folder_path/$file");

            // Membaca isi file JSON
            $json_content = file_get_contents("$folder_path/$file");
            
            // Memeriksa apakah file JSON tidak kosong
            if (!empty($json_content)) {
                // Mendekode isi JSON
                $data = json_decode($json_content, true);
                
                // Jika isi file adalah array dan tidak kosong
                if (is_array($data) && !empty($data)) {
                    $nama = str_replace("_", " ", $file);
                    $nama = str_replace(".json", "", $nama);
                    $result = [
                        'nama' => $nama,
                        'sudah_dikerjakan' => 0,
                        'belum_dikerjakan' => 0,
                        'sudah_dikirim' => false,
                        'file_error' => false,
                        'created_file' => date('Y-m-d H:i:s', $created_time),
                        'terakhir_diedit' => date('Y-m-d H:i:s', $last_modified_time),
                        'jam_mulai'=>$_POST["mulai"]
                        
                    ];
                    
                    // Menghitung jumlah jawaban yang sudah dan belum dikerjakan
                    $answered = false;
                    foreach ($data as $answer) {
                        if (isset($answer['Jawaban']) && !empty($answer['Jawaban'])) {
                            $result['sudah_dikerjakan']++;
                            $answered = true;
                        } else {
                            $result['belum_dikerjakan']++;
                        }
                    }
                    $query = "SELECT * FROM `".$ujian."_".$kelas."` WHERE nama = '$nama'";
                    $sqlCek = mysqli_query($conn, $query);
                    $result['sudah_dikirim'] = mysqli_num_rows($sqlCek) > 0 ? true : false;
                    
                    // Menambahkan status file_error
                    $result['file_error'] = $answered ? false : true;

                    $result['waktu_telat'] = formatWaktuTelat($result['created_file'], $waktu_masuk);
                    
                    // Menambahkan hasil untuk siswa ini ke dalam hasil keseluruhan
                    $results["data"][] = $result;
                } else {

                    $results["data"][] = [
                        'nama' => $file,
                        'sudah_dikerjakan' => 0,
                        'belum_dikerjakan' => 0,
                        'sudah_dikirim' => false,
                        'file_error' => true,
                        'created_file' => date('Y-m-d H:i:s', $created_time),
                        'terakhir_diedit' => date('Y-m-d H:i:s', $last_modified_time),
                        'waktu_telat' => formatWaktuTelat($result['created_file'], $waktu_masuk)
                    ];
                }
            } else {
                // File JSON kosong
                $results["data"][] = [
                    'nama' => $file,
                    'sudah_dikerjakan' => 0,
                    'belum_dikerjakan' => 0,
                    'sudah_dikirim' => false,
                    'file_error' => true,
                    'created_file' => date('Y-m-d H:i:s', $created_time),
                    'terakhir_diedit' => date('Y-m-d H:i:s', $last_modified_time),
                    'waktu_telat' => formatWaktuTelat($result['created_file'], $waktu_masuk)
                ];
            }
        }
    }

    $results["jumlah_data"]=count($results["data"]);

    // Mengubah hasil ke format JSON
    $json_results = json_encode($results, JSON_PRETTY_PRINT);

    header('Content-Type: application/json');
    echo $json_results;
}

if(isset($_GET["backup_jawaban"])){
    $ujian = $_POST["ujian"];
    $mapel = $_POST["mapel"];
    $kelas = $_POST["kelas"];
    $nama = $_POST["nama"];

    $listName = "";

    $file_error = false;

    $dir_backup = "json/backup/".$ujian."_".$mapel."_".$kelas;
    $dir_jawaban = "json/jawaban/".$ujian."_".$mapel."_".$kelas;
    $fileName = $nama.".json"; 
    if(strpos($nama, ".json") !== false){
        $fileName = $nama;
    }
    if (!is_dir($dir_backup)) {
        mkdir($dir_backup, 0777, true);
    }

    if(is_dir($dir_backup)){
        if(isset($_GET["All"])){
            // Mendapatkan daftar file dalam folder
            $files = scandir($dir_jawaban);

            // Iterasi melalui setiap file dalam folder
            foreach ($files as $file) {
                // Hanya memproses file dengan ekstensi .json
                if (pathinfo($file, PATHINFO_EXTENSION) === 'json') {
                    // Membaca isi file JSON
                    $json_content = file_get_contents("$dir_jawaban/$file");
                    
                    // Memeriksa apakah file JSON tidak kosong
                    if (!empty($json_content)) {
                        // Mendekode isi JSON
                        $data = json_decode($json_content, true);
                        
                        // Jika isi file adalah array dan tidak kosong
                        if (is_array($data) && !empty($data)) {
                            if (copy($dir_jawaban."/".$file, $dir_backup."/".$file)) {
                                
                            } else {
                                $file_error = true;
                                $newName = str_replace(".json", "", $file);
                                $newName = str_replace("_", " ", $newName);
                                $listName .= ", ".$newName;
                            }
                        } else {
                            $file_error = true;
                            $newName = str_replace(".json", "", $file);
                            $newName = str_replace("_", " ", $newName);
                            $listName .= ", ".$newName;
                        }
                    } else {
                        $file_error = true;
                        $newName = str_replace(".json", "", $file);
                        $newName = str_replace("_", " ", $newName);
                        $listName .= ", ".$newName;
                    }
                }
            }
        }else{
            $json_content = file_get_contents("$dir_jawaban/$fileName");
            if (!empty($json_content)) {
                $data = json_decode($json_content, true);
                // Jika isi file adalah array dan tidak kosong
                if (is_array($data) && !empty($data)) {
                    if (copy($dir_jawaban."/".$fileName, $dir_backup."/".$fileName)) {
                        
                    } else {
                        $file_error = true;
                    }
                }else{
                    $file_error = true;
                }
            }else{
                $file_error = true;
            }
        }
    }

    if($file_error){
        echo "Gagal Membackup Jawaban".$listName;
    }else{
        echo "Berhasil";
    }

}

if(isset($_GET["restore_jawaban"])){
    $ujian = $_POST["ujian"];
    $mapel = $_POST["mapel"];
    $kelas = $_POST["kelas"];
    $nama = $_POST["nama"];

    $listName = "";

    $file_error = false;

    $dir_jawaban = "json/backup/".$ujian."_".$mapel."_".$kelas;
    $dir_backup = "json/jawaban/".$ujian."_".$mapel."_".$kelas;
    $fileName = $nama.".json";
    if(strpos($nama, ".json") !== false){
        $fileName = $nama;
    } 
    if (!is_dir($dir_backup)) {
        mkdir($dir_backup, 0777, true);
    }

    if(is_dir($dir_backup)){
        if(isset($_GET["All"])){
            // Mendapatkan daftar file dalam folder
            $files = scandir($dir_jawaban);

            // Iterasi melalui setiap file dalam folder
            foreach ($files as $file) {
                // Hanya memproses file dengan ekstensi .json
                if (pathinfo($file, PATHINFO_EXTENSION) === 'json') {
                    // Membaca isi file JSON
                    $json_content = file_get_contents("$dir_jawaban/$file");
                    
                    // Memeriksa apakah file JSON tidak kosong
                    if (!empty($json_content)) {
                        // Mendekode isi JSON
                        $data = json_decode($json_content, true);
                        
                        // Jika isi file adalah array dan tidak kosong
                        if (is_array($data) && !empty($data)) {
                            if (copy($dir_jawaban."/".$file, $dir_backup."/".$file)) {
                                
                            } else {
                                $file_error = true;
                                $newName = str_replace(".json", "", $file);
                                $newName = str_replace("_", " ", $newName);
                                $listName .= ", ".$newName;
                            }
                        } else {
                            $file_error = true;
                            $newName = str_replace(".json", "", $file);
                            $newName = str_replace("_", " ", $newName);
                            $listName .= ", ".$newName;
                        }
                    } else {
                        $file_error = true;
                        $newName = str_replace(".json", "", $file);
                        $newName = str_replace("_", " ", $newName);
                        $listName .= ", ".$newName;
                    }
                }
            }
        }else{
            $json_content = file_get_contents("$dir_jawaban/$fileName");
            if (!empty($json_content)) {
                $data = json_decode($json_content, true);
                // Jika isi file adalah array dan tidak kosong
                if (is_array($data) && !empty($data)) {
                    if (copy($dir_jawaban."/".$fileName, $dir_backup."/".$fileName)) {
                        echo "Berhasil";
                    } else {
                        $file_error = true;
                        $newName = str_replace(".json", "", $fileName);
                        $newName = str_replace("_", " ", $newName);
                        $listName .= $newName;
                    }
                }else{
                    $file_error = true;
                    $newName = str_replace(".json", "", $fileName);
                    $newName = str_replace("_", " ", $newName);
                    $listName .= $newName;
                }
            }else{
                $file_error = true;
                $newName = str_replace(".json", "", $fileName);
                $newName = str_replace("_", " ", $newName);
                $listName .= $newName;
            }
        }
    }

    if($file_error){
        echo "Gagal Merestore Jawaban".$listName;
    }else{
        echo "Berhasil";
    }

}

if(isset($_GET["hapus_jawaban"])){
    $ujian = $_POST["ujian"];
    $mapel = $_POST["mapel"];
    $kelas = $_POST["kelas"];
    $nama = $_POST["nama"];

    $listName = "";

    $file_error = false;

    $dir_jawaban = "json/jawaban/".$ujian."_".$mapel."_".$kelas;
    $dir_backup = "json/backup/".$ujian."_".$mapel."_".$kelas;
    $fileName = $nama.".json";
    if(strpos($nama, ".json") !== false){
        $fileName = $nama;
    }
    if (!is_dir($dir_backup)) {
        mkdir($dir_backup, 0777, true);
    }

    if(is_dir($dir_backup)){
        if(isset($_GET["AllMapel"])){
            // Mendapatkan daftar file dalam folder
            $files = scandir($dir_jawaban);

            // Iterasi melalui setiap file dalam folder
            foreach ($files as $file) {
                // Hanya memproses file dengan ekstensi .json
                if (pathinfo($file, PATHINFO_EXTENSION) === 'json') {
                    if (unlink($dir_jawaban."/".$file)) {
                                
                    } else {
                        $file_error = true;
                        $newName = str_replace(".json", "", $file);
                        $newName = str_replace("_", " ", $newName);
                        $listName .= ", ".$newName;
                    }
                }
            }
        }else{
            if (unlink($dir_jawaban."/".$fileName)) {
                        
            } else {
                $file_error = true;
                $newName = str_replace(".json", "", $fileName);
                $newName = str_replace("_", " ", $newName);
                $listName .= $newName;
            }
        }
    }

    if($file_error){
        echo "Gagal Menghapus Jawaban".$listName;
    }else{
        echo "Berhasil";
    }
}
?>
