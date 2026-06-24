<?php
// Modelo que representa una Canción
class Cancion {
    // Atributos de la tabla canciones
    protected $id_cancion;
    protected $id_album;
    protected $titulo;
    protected $archivo_ruta; // Ruta en la carpeta 'uploads'
    protected $duracion; // Formato time ej: '00:03:45'
    protected $genero;
    private $db;

    // Constructor que recibe conexión PDO
    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    // Valida que los datos obligatorios estén presentes
    public function validarDatos($titulo, $archivo_ruta, $genero) {
        if (empty(trim($titulo)) || empty(trim($archivo_ruta)) || empty(trim($genero))) {
            return "El título, el archivo de la canción y el género son obligatorios.";
        }
        return true;
    }

    // Método para guardar en DB.
    // Nota sobre la duración: De momento se pide que se envíe o usa '00:03:00' por defecto. 
    // Para que sea 100% automático, en el controlador se debería leer el MP3 con una librería como getID3() antes de llamar a esto.
    public function guardarCancion($id_album, $titulo, $archivo_ruta, $genero, $duracion_calculada = '00:03:00') {
        $sql = "INSERT INTO canciones (id_album, titulo, archivo_ruta, duracion, genero) 
                VALUES (:id_album, :titulo, :ruta, :duracion, :genero)";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_album', $id_album, PDO::PARAM_INT);
            $stmt->bindParam(':titulo', $titulo);
            $stmt->bindParam(':ruta', $archivo_ruta);
            $stmt->bindParam(':duracion', $duracion_calculada);
            $stmt->bindParam(':genero', $genero);
            
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Error al guardar canción: " . $e->getMessage());
            return false;
        }
    }

    // Método para obtener la suma total de reproducciones de todas las canciones de un artista
    // Se ejecuta de forma segura: si la columna 'reproducciones' no existe en la base de datos, 
    // captura la excepción y retorna 0 para evitar que la aplicación falle.
    public function obtenerTotalReproduccionesArtista($id_artista) {
        $sql = "SELECT SUM(c.reproducciones) AS total 
                FROM canciones c 
                INNER JOIN albumes a ON c.id_album = a.id_album 
                WHERE a.id_artista = :id_artista";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_artista', $id_artista, PDO::PARAM_INT);
            $stmt->execute();
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($res['total'] ?? 0);
        } catch (\PDOException $e) {
            // Loguear error silencioso en caso de columna inexistente
            error_log("Error al obtener reproducciones del artista (columna inexistente o error SQL): " . $e->getMessage());
            return 0; // Fallback seguro
        }
    }

    public function obtenerCancionesConAlbumPorArtista($id_artista) {
        $sql = "SELECT c.id_cancion, c.id_album, c.titulo, c.archivo_ruta, c.duracion, c.genero,
                       a.titulo AS titulo_album, a.portada_ruta
                FROM canciones c
                INNER JOIN albumes a ON c.id_album = a.id_album
                WHERE a.id_artista = :id_artista
                ORDER BY c.id_cancion DESC";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_artista', $id_artista, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error al listar canciones por artista: " . $e->getMessage());
            return [];
        }
    }

    // Devuelve las N canciones más recientes para la sección de tendencias y nuevos lanzamientos.
    public function obtenerRecientes($limit = 6) {
        $sql = "SELECT c.id_cancion, c.id_album, c.titulo, c.archivo_ruta, c.duracion, c.genero,
                       a.titulo AS titulo_album, a.portada_ruta, a.id_album AS album_id,
                       ar.nombre_artistico, ar.id_artista
                FROM canciones c
                INNER JOIN albumes a ON c.id_album = a.id_album
                INNER JOIN artistas ar ON a.id_artista = ar.id_artista
                INNER JOIN usuarios u ON ar.id_artista = u.id_usuario
                WHERE u.banned = 0
                ORDER BY c.id_cancion DESC
                LIMIT :limit";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error al obtener canciones recientes: " . $e->getMessage());
            return [];
        }
    }

    // Obtiene todas las canciones de un género específico de artistas activos (no baneados).
    // Ahora incluye c.id_album para poder agrupar las canciones por álbum en la vista de género.
    // El orden es: álbumes más recientes primero y, dentro de cada álbum, canciones en orden ascendente.
    public function obtenerCancionesPorGenero($genero) {
        $sql = "SELECT c.id_cancion, c.id_album, c.titulo, c.archivo_ruta, c.duracion, c.genero,
                       a.titulo AS titulo_album, a.portada_ruta,
                       ar.nombre_artistico
                FROM canciones c
                INNER JOIN albumes a ON c.id_album = a.id_album
                INNER JOIN artistas ar ON a.id_artista = ar.id_artista
                INNER JOIN usuarios u ON ar.id_artista = u.id_usuario
                WHERE c.genero = :genero AND u.banned = 0
                ORDER BY c.id_album DESC, c.id_cancion ASC";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':genero', $genero);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error al listar canciones por genero: " . $e->getMessage());
            return [];
        }
    }

    public function contarTodas() {
        try {
            return (int)$this->db->query("SELECT COUNT(*) FROM canciones")->fetchColumn();
        } catch (\PDOException $e) {
            error_log("Error al contar canciones: " . $e->getMessage());
            return 0;
        }
    }

    // Busca canciones que coincidan con el término proporcionado.
    // Compara el término contra el título de la canción, el título del álbum y el nombre artístico.
    // Solo devuelve resultados de artistas que no estén baneados. Máximo 60 resultados.
    public function buscar($query) {
        // Envolver el término en comodines para búsqueda parcial (LIKE)
        $like = '%' . $query . '%';

        $sql = "SELECT c.id_cancion, c.id_album, c.titulo, c.archivo_ruta, c.duracion, c.genero,
                       a.titulo AS titulo_album, a.portada_ruta,
                       ar.nombre_artistico
                FROM canciones c
                INNER JOIN albumes a ON c.id_album = a.id_album
                INNER JOIN artistas ar ON a.id_artista = ar.id_artista
                INNER JOIN usuarios u ON ar.id_artista = u.id_usuario
                WHERE u.banned = 0
                  AND (c.titulo LIKE :like1 OR a.titulo LIKE :like2 OR ar.nombre_artistico LIKE :like3)
                ORDER BY c.id_cancion DESC
                LIMIT 60";
        try {
            $stmt = $this->db->prepare($sql);
            // Se usan tres parámetros distintos (:like1, :like2, :like3) porque PDO no permite
            // reutilizar el mismo marcador de posición más de una vez en la misma consulta
            $stmt->bindParam(':like1', $like);
            $stmt->bindParam(':like2', $like);
            $stmt->bindParam(':like3', $like);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error al buscar: " . $e->getMessage());
            return [];
        }
    }
}
?>