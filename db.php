<?php
$host     = 'nozomi.proxy.rlwy.net';
$port     = '24362';
$dbname   = 'railway';
$username = 'root';
$password = 'fkWNfWnPjwctYNIubwdLnZYirsTPOylS';
$charset  = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $username, $password, $options);
} catch (\PDOException $e) {
    die("Connection failed: " . $e->getMessage()); // was: throw, which leaves $pdo unset
}
?>