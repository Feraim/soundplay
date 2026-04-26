<?php
require_once __DIR__ . '/config/env.php';
loadEnv(__DIR__ . '/.env');

$host = getenv('DB_HOST') ?: 'localhost';
$db = getenv('DB_NAME') ?: 'soundplay_db';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Nueva contraseña para todos: 123456
    $new_hash = password_hash('123456', PASSWORD_BCRYPT);
    
    $stmt = $conn->prepare("UPDATE usuarios SET password = :hash");
    $stmt->bindParam(':hash', $new_hash);
    $stmt->execute();
    
    echo "Todas las contraseñas han sido reseteadas a '123456' exitosamente.\n";
} catch (PDOException $e) {
    echo "Error de DB: " . $e->getMessage() . "\n";
}
?>
