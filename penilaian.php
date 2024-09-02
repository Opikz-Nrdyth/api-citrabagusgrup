<?php
require_once("db_connect.php");
if(isset($_GET['read_ujian'])){
    header("Content-Type: application/json");
    $dataArray = array();
    $get_kelas = $_GET['kelas'];
    $query = "SELECT * FROM `db_ujian` WHERE kelas = '$get_kelas'";
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
    echo json_encode($dataArray);
}
if(isset($_GET['read_nilai'])){
    $post_data = json_decode(file_get_contents("php://input"), true);
    $query = "SELECT db_siswa.username, db_siswa.nama, db_siswa.kelas, db_siswa.tipeKelas";
    $ujianUnique = array();
    $kelas = "";
    
    $data_array = array();
    
    // membuat data mana aja yang ditampilkan. disini data username, nama, kelas, tipeKelas. dan mapel pada setiap table Try Out
    foreach($post_data as $q){
        $nama_ujian = str_replace(' ', '_', $q['nama_ujian']);
        $mapel = str_replace(' ', '_', $q['mapel']);
        $kelas = $q['kelas'];
        $query .= ", COALESCE(".$nama_ujian."_".$q['kelas'].".".$mapel.", 0) AS ".$nama_ujian."_".$mapel;
        // $query .= " ,".$nama_ujian."_".$q['kelas'].".".$mapel;
        $ujianUnique[] = array(
            "nama_ujian"=>$nama_ujian,
            "kelas"=>$q['kelas']
            );
    }
    $query .= " FROM db_siswa";
    // Membuat array uniq agar tidak berulang seperti try out 1 hanya di munculkan 1 kali
    $ujianUnique = array_values(array_unique($ujianUnique, SORT_REGULAR));
    // membuat left join agar table yang di inginkan pada try out hanya di tampilkan 1 kali
    foreach ($ujianUnique as $q){
        $query .= " LEFT JOIN ".$q['nama_ujian']."_".$q['kelas']." ON db_siswa.username = ".$q['nama_ujian']."_".$q['kelas'].".username";
    }
    // mengintruksikan yang di tampilkan hanya kelas yang ingin ditampilkan
    $query .= " WHERE db_siswa.kelas = $kelas";
    
    $sql = mysqli_query($conn, $query);
    if($sql && mysqli_num_rows($sql) > 0){
        while($data = mysqli_fetch_assoc($sql)){
            $data_array[] = $data;
        }
    }
    header("Content-Type: application/json");
    echo json_encode($data_array);
    // echo $query;
}

if(isset($_GET["read_setpenilaian"])){
    $query = "SELECT * FROM `set_penilaian`";
    $sql = mysqli_query($conn, $query);
    $arrayData = array();
    if($sql){
        while($data = mysqli_fetch_assoc($sql)){
            $arrayData[]=$data;
        }
    }
    header("Content-Type: application/json");
    echo json_encode($arrayData);
}

if(isset($_POST["delete_penilaian"])){
    $id = $_POST["id"];
    $query = "DELETE FROM `set_penilaian` WHERE id = $id";
    $sql = mysqli_query($conn, $query);
    if($sql){
        echo "Berhasil";
    }
}

if(isset($_POST["tambah_penilaian"])){
    $kelas = $_POST["kelas"];
    $penilaian = $_POST["penilaian"];
    $tipe = $_POST["tipe"];

    $query="INSERT INTO `set_penilaian`(`kelas`, `penilaian`, `tipe`) VALUES ('$kelas','$penilaian','$tipe')";
    $sql = mysqli_query($conn, $query);
    if($sql){
        echo "Berhasil";
    }
}