<?php
require 'vendor/autoload.php';
use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/.env');

$url = $_ENV['DATABASE_URL'];
$parts = parse_url($url);
$host = $parts['host'];
$db   = substr($parts['path'], 1);
$user = $parts['user'];
$pass = $parts['pass'] ?? '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $tables = ['appointment', 'patologia', 'odontograma', 'radiografia', 'primera_visita', 'clinical_history', 'patient', 'dentist', 'box', 'stock_material', 'user'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
        $count = $stmt->fetchColumn();
        echo "$table: $count\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
