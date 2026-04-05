<?php

declare(strict_types=1);

$host = getenv('DB_HOST') ?: '127.0.0.1';
$dbName = getenv('DB_NAME') ?: 'simple_tasks_app';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPassword = getenv('DB_PASS') ?: '';
$charset = 'utf8mb4';

$dsn = "mysql:host={$host};dbname={$dbName};charset={$charset}";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $dbUser, $dbPassword, $options);
} catch (PDOException $e) {
    die('فشل الاتصال بقاعدة البيانات. تأكد من إعدادات DB_* في البيئة.');
}
