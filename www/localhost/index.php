<?php
// echo phpinfo();
// die();
echo "Hello: ";
try {
    $dbh = new PDO('mysql:host=mysql;dbname=my', 'root', '123456');
    $sql = "SELECT * FROM users";
    $res = $dbh->query($sql);
    echo $res->fetchColumn(3);
} catch (PDOException $e) {
    echo "数据库连接失败：". $e->getMessage();
}

//连接本地的 Redis 服务
$redis = new Redis();
$redis->connect('redis', 6379);
echo "Connection to server successfully";
      //查看服务是否运行
echo "Server is running: " . $redis->ping();

