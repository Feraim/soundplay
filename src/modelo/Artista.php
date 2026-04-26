<?php
class Artista {
    private $conn;
    public function __construct($db) { $this->conn = $db; }

    public function obtenerCercanos() {
        $query = "SELECT id_artista, nombre_artistico, localidad, foto_perfil FROM Artistas LIMIT 6";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>