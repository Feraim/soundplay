<?php
class Cancion {
    private $conn;
    public function __construct($db) { $this->conn = $db; }

    public function obtenerPorAlbum($id_album) {
        $query = "SELECT * FROM Canciones WHERE id_album = :id ORDER BY id_cancion ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id_album);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>