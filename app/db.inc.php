<?php

$conf = parse_ini_file("config.ini");
$db_host = $conf['host'];
$db_database = $conf['dbname'].';charset=utf8';
$db_username = $conf['user'];
$db_password = $conf['pass'];


try {
    $conn = new PDO('mysql:host=' .$db_host.'; dbname=' .$db_database, $db_username, $db_password);

} catch (PDOException $e) {
    echo("Erreur ! " . $e-> getMessage() . "<br>");
    die();
}
