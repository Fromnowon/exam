<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header('Access-Control-Allow-Headers:x-requested-with,content-type');
header("Content-Type:text/html;charset:utf-8");
// $servername = "172.100.0.10";
// $username = "root";
// $password = "root";
$servername = "47.107.131.235";
$username = "zzh";
$password = "Zzh,./473335";

// 创建连接
$conn = new mysqli($servername, $username, $password, "zzh", '9060');
mysqli_query($conn, "SET NAMES UTF8");
//检测连接
// if ($conn->connect_error) {
//     die("连接失败: " . $conn->connect_error);
// }
// echo "连接成功";
switch ($_GET['action']) {
    case 'newProblem':
        $type = $_POST['type'];
        $subject = $_POST['subject'];
        $tags = mysqli_real_escape_string($conn, json_encode($_POST['tags'], JSON_UNESCAPED_UNICODE));
        $public = $_POST['public'];
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $options = mysqli_real_escape_string($conn, json_encode($_POST['options'], JSON_UNESCAPED_UNICODE));
        $author = $_POST['author'];
        if (isset($_POST['id'])) {
            $id = $_POST['id'];
            $r = "UPDATE `problems` SET type={$type}, subject='{$subject}', tags='{$tags}', public={$public}, description='{$description}', options='{$options}' WHERE id={$id}";
        } else {
            $r = "INSERT into `problems` VALUES(default, {$type}, '{$subject}', '{$tags}', {$public}, '{$description}', '{$options}', '{$author}')";
        }
        $result = mysqli_query($conn, $r);
        if ($result) {
            echo json_encode(array('code' => 200, 'msg' => null));
        } else {
            echo json_encode(array('code' => 401, 'msg' => $r));
        }
        break;
    case 'getProblemsList':
        // sleep(1);
        $page = $_GET['page'];
        $size = $_GET['size'];
        $offset = $page * $size;
        $r = "SELECT * from `problems` limit {$offset},{$size}";

        mysqli_query($conn, "SET NAMES UTF8");
        $result = mysqli_query($conn, $r);
        $arr = array();
        while ($item = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            array_push($arr, $item);
        }
        $r = mysqli_query($conn, 'select count(*) as `total` from `problems`');
        $result = mysqli_fetch_array($r);
        echo json_encode(array('code' => 200, 'msg' => $arr, 'total' => $result['total']));
        // echo json_encode($arr, JSON_UNESCAPED_UNICODE);
        break;
    case 'getProblemsDetail':
        $id = $_GET['id'];
        $r = "SELECT * from `problems` WHERE id={$id}";
        $result = mysqli_query($conn, $r);
        echo json_encode(array('code' => 200, 'msg' => mysqli_fetch_array($result, MYSQLI_ASSOC)));
        break;
    default:
        echo 'unknown action';
}
