<?php
declare(strict_types=1);
session_start();

$host = 'localhost:4306';
$port = '4306';
$db   = 'ceylon_spice_hub';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    http_response_code(500);
    die('<div style="font-family:Arial;padding:40px;max-width:700px;margin:auto">
        <h2>Ceylon Spice Hub - Database Connection Error</h2>
        <p>Make sure <b>MySQL is running in XAMPP</b>, then import <b>database.sql</b> in phpMyAdmin.</p>
        <p>Default settings: host <b>localhost</b>, port <b>3306</b>, user <b>root</b>, empty password.</p>
        </div>');
}

if (empty($_SESSION['cart_token'])) {
    $_SESSION['cart_token'] = bin2hex(random_bytes(24));
}
$cartToken = $_SESSION['cart_token'];

function e(?string $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function money(float $value): string {
    return 'Rs. ' . number_format($value, 2);
}
?>
