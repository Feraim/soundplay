<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=soundplay_db;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Verificamos tablas y columnas
    $stmt = $pdo->query("SELECT * FROM artistas LIMIT 1");
    print_r($stmt->fetch(PDO::FETCH_ASSOC));
    
} catch(Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
