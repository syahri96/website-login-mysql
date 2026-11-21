<?php
declare(strict_types=1);

$configFile = dirname(__DIR__) . '/config.php';
if (!is_file($configFile)) {
    http_response_code(500);
    exit('Konfigurasi belum tersedia. Salin config.example.php menjadi config.php dan isi pengaturannya.');
}
$config = require $configFile;

session_name($config['session_name'] ?? 'nusantara_auth');
$secureCookie = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
session_set_cookie_params([
    'lifetime' => 0, 'path' => '/', 'secure' => $secureCookie,
    'httponly' => true, 'samesite' => 'Lax'
]);
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once __DIR__ . '/helpers.php';

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
    $config['db']['host'], $config['db']['port'] ?? '3306',
    $config['db']['database'], $config['db']['charset'] ?? 'utf8mb4'
);
try {
    $pdo = new PDO($dsn, $config['db']['username'], $config['db']['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    exit('Tidak dapat terhubung ke database. Periksa konfigurasi MySQL.');
}
