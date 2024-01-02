<?php

function connect_oracle($username ,  $password ,  $host_port ,  $code){
    $conn = oci_connect('KWW','DXN90OPQ1','120.77.181.148:1521/KWWALYDB', 'utf8'); // 建立连接
    if (!$conn) {
        $e = oci_error();
        print htmlentities($e['message']);
        exit;
    }
    return $conn;
}

function connect_mysql($host,$user,$pass,$db){

    $conn = new mysqli($host,$user,$pass,$db);
    if (mysqli_connect_error()) {
        die('Connect Error (' . mysqli_connect_errno() . ') ' . mysqli_connect_error());
    }
    $conn->set_charset('utf8');

    return $conn;
}

function select_mysql($sql , $conn ,  &$all){
    $query = mysqli_query($conn ,  $sql);
if (!$query) {
    printf("Error: %s\n", mysqli_error($conn));
    exit();
}
    
    while($r = mysqli_fetch_assoc($query)){
        $all[] = $r; 
    }
}

function select_oracle($sql , $conn, &$result){
    $stid = oci_parse($conn, $sql); // 配置SQL语句，准备执行
    if (!$stid) {
        $e = oci_error($conn);
        print htmlentities($e['message']);
        exit;
    }
    $r = oci_execute($stid, OCI_DEFAULT); // 执行SQL。OCI_DEFAULT表示不要自动commit
    if(!$r) {
        $e = oci_error($stid);
        echo htmlentities($e['message']);
        exit;
    }

    // 打印执行结果
    while($row = oci_fetch_assoc($stid)) {
        $result[] = $row;
    }
    return $result;
}

$conn = connect_oracle('KWW','DXN90OPQ1','120.77.181.148:1521/KWWALYDB', 'utf8'); // 建立连接

$query = 'select * from pbi_bom_info where rownum <= 3 order by pf_id asc '; // 查询语句
$result = []; 
//select_oracle($query , $conn , $result); 
//echo '<pre>';print_r($result);exit;

//$connect = connect_mysql('220.168.154.86:28080' , 'root' ,  'kww@123456' ,  'kww_trace'); 
$connect = connect_mysql('10.254.30.19', 'root' , 'kww@123456' , 'kww_trace'); 

$sql = "select * from pbi_bom_info"; 

$all = []; 
select_mysql($query , $connect , $all); 

echo '<pre>';print_r($all);exit;

mysqli_close($connect);
oci_close($conn);

?>
