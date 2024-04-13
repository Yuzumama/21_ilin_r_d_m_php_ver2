<?php

include 'db_info_localhost.php';
//include 'db_info_sakura.php';

function connect_to_db(){
    try {
        // host, 'root', '*****': Sakura server
    //    $pdo = new PDO('mysql:dbname=second_php_db;charset=utf8;host=localhost', 'root', '');
        $pdo = new PDO('mysql:dbname=' . getDbName() . ';charset=utf8;host=' . getDbHost(), getDbId(), getDbPw());
    } catch (PDOException $e) {
        exit('DB_CONNECT: ' . $e->getMessage());
        return null;
    }
    return $pdo;    
}

?>