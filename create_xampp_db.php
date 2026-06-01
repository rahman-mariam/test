<?php
$dsn = 'mysql:host=127.0.0.1;port=3306';
$user = 'root';
$pass = '';
try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->exec('CREATE DATABASE IF NOT EXISTS cluadeDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    echo "DB_CREATED\n";
} catch (PDOException $e) {
    echo 'ERROR: ' . $e->getMessage() . "\n";
}
