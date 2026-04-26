<?php
// src/controlador/CancionController.php
require_once '../../config/db.php';
require_once '../modelo/Cancion.php';

$database = new Database();
$db = $database->getConnection();
$cancion = new Cancion($db);

// Verbo POST para creación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'] ?? '';
    $id_album = $_POST['id_album'] ?? 1;
    
    // Validación robusta de archivo
    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === 0) {
        $allowed = ['audio/mpeg', 'audio/wav', 'audio/x-wav'];
        $filename = $_FILES['archivo']['name'];
        $filetype = $_FILES['archivo']['type'];
        $filesize = $_FILES['archivo']['size'];

        if (!in_array($filetype, $allowed)) {
            echo json_encode(["error" => "Formato no permitido"]);
            exit;
        }

        // Regla de negocio: Límite de 500MB
        if ($filesize > 524288000) { 
            echo json_encode(["error" => "Archivo demasiado grande"]);
            exit;
        }

        $target = "../../uploads/" . basename($filename);
        if (move_uploaded_file($_FILES['archivo']['tmp_name'], $target)) {
            $cancion->crear($titulo, $target, $id_album);
            echo json_encode(["status" => "success", "message" => "Subida completada"]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["error" => "Campos obligatorios vacíos"]);
    }
}