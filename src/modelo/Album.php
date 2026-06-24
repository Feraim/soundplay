<?php
// Modelo que representa un Álbum
class Album {
    // Atributos de base de datos
    protected $id_album;
    protected $id_artista;
    protected $titulo;
    protected $portada_ruta;
    protected $fecha_publicacion;
    private $db;

    // Constructor que recibe la conexión PDO
    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    // Método para validar que el título no esté vacío
    public function validarDatos($titulo) {
        if (empty(trim($titulo))) {
            return "El título del álbum es obligatorio.";
        }
        return true;
    }

    // Guarda el álbum en BD. La fecha se establece automáticamente al día de hoy.
    public function crearAlbum($id_artista, $titulo, $portada_ruta) {
        // La fecha de publicación se asigna de manera automática al momento de la creación
        $fecha_actual = date('Y-m-d'); // Formato DATE de MySQL
        
        // Sentencia preparada para prevenir Inyección SQL
        $sql = "INSERT INTO albumes (id_artista, titulo, portada_ruta, fecha_publicacion) 
                VALUES (:id_artista, :titulo, :portada_ruta, :fecha_publicacion)";

        try {
            $stmt = $this->db->prepare($sql);
            // Vincular parámetros de forma segura
            $stmt->bindParam(':id_artista', $id_artista, PDO::PARAM_INT);
            $stmt->bindParam(':titulo', $titulo);
            $stmt->bindParam(':portada_ruta', $portada_ruta);
            $stmt->bindParam(':fecha_publicacion', $fecha_actual);
            
            // Ejecutar la inserción
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Error al crear álbum: " . $e->getMessage());
            return false;
        }
    }

    // Método para obtener todos los álbumes pertenecientes a un artista específico
    // Usado en el panel del artista para listar álbumes y rellenar el dropdown de canciones
    public function obtenerAlbumesPorArtista($id_artista) {
        // Consulta preparada ordenada para mostrar los más recientes primero
        $sql = "SELECT * FROM albumes WHERE id_artista = :id_artista ORDER BY fecha_publicacion DESC, id_album DESC";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_artista', $id_artista, PDO::PARAM_INT);
            $stmt->execute();
            // Retorna un array asociativo con todos los álbumes del artista
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error al obtener álbumes por artista: " . $e->getMessage());
            return [];
        }
    }
}
?>