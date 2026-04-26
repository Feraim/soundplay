<?php
header("Content-Type: application/json");
require_once '../../config/db.php';
require_once '../modelo/Artista.php';

$db = (new Database())->getConnection();
$artista = new Artista($db);

if (isset($_GET['action']) && $_GET['action'] === 'list') {
    try {
        $resultados = $artista->obtenerCercanos();
        if (empty($resultados)) {
            // Mock data fallback if DB table is empty
            $resultados = [
                ["id_artista" => 1, "nombre_artistico" => "ELENA VOX", "localidad" => "1.2 KM", "foto_perfil" => "assets/img/artist1.jpg"],
                ["id_artista" => 2, "nombre_artistico" => "MARCO DISTINTO", "localidad" => "3.5 KM", "foto_perfil" => "assets/img/default-album.jpg"]
            ];
        }
        echo json_encode($resultados);
    } catch(Exception $e) {
        $resultados = [
            ["id_artista" => 1, "nombre_artistico" => "ELENA VOX", "localidad" => "1.2 KM", "foto_perfil" => "assets/img/artist1.jpg"],
            ["id_artista" => 2, "nombre_artistico" => "MARCO DISTINTO", "localidad" => "3.5 KM", "foto_perfil" => "assets/img/default-album.jpg"]
        ];
        echo json_encode($resultados);
    }
}
?>