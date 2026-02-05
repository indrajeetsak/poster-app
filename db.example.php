<?php
$host = '127.0.0.1';
$db   = 'poster_app';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // First connect without DB selected to create it if needed
    $pdo_root = new PDO("mysql:host=$host;charset=$charset", $user, $pass, $options);
    $pdo_root->exec("CREATE DATABASE IF NOT EXISTS `$db`");
    
    // Now connect to the specific DB
    $dsn_db = "mysql:host=$host;dbname=$db;charset=$charset";
    $pdo = new PDO($dsn_db, $user, $pass, $options);

} catch (\PDOException $e) {
    if (strpos($e->getMessage(), 'Unknown database') !== false) {
       die("Database '$db' does not exist and could not be created.");
    }
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>
