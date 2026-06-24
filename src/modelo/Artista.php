<?php
// Modelo que representa a un Artista en el sistema
class Artista {
    // Atributos que coinciden con las columnas de la tabla 'artistas'
    protected $id_artista; // Se actualizó de 'id' a 'id_artista' para coincidir con la DB
    protected $nombre_artistico;
    protected $bio_extended;
    protected $localidad;
    protected $foto_perfil;
    protected $espacio_maximo;
    private $db; // Conexión a la base de datos

    // Constructor que recibe la conexión PDO
    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    // Método para validar los datos básicos del artista antes de guardarlos
    public function validarDatos($nombre_artistico, $localidad) {
        // Validación básica de campos requeridos (elimina espacios en blanco extra)
        if (empty(trim($nombre_artistico))) {
            return "El nombre artístico es obligatorio.";
        }
        
        if (empty(trim($localidad))) {
            return "La localidad es obligatoria.";
        }

        return true; // Pasa la validación
    }

    // Método para guardar un nuevo perfil de artista en la base de datos de manera segura
    // Nota: El 'espacio_maximo' podría tener un valor por defecto en bytes, ej: 100MB = 104857600
    public function guardarPerfil($id_artista, $nombre_artistico, $bio_extended, $localidad, $foto_perfil, $espacio_maximo) {
        // Sentencia SQL preparada para prevenir inyección SQL
        $sql = "INSERT INTO artistas (id_artista, nombre_artistico, bio_extended, localidad, foto_perfil, espacio_maximo) 
                VALUES (:id_artista, :nombre_artistico, :bio, :localidad, :foto, :espacio)";

        try {
            $stmt = $this->db->prepare($sql);
            
            // Asignar parámetros
            $stmt->bindParam(':id_artista', $id_artista, PDO::PARAM_INT);
            $stmt->bindParam(':nombre_artistico', $nombre_artistico);
            $stmt->bindParam(':bio', $bio_extended);
            $stmt->bindParam(':localidad', $localidad);
            $stmt->bindParam(':foto', $foto_perfil);
            $stmt->bindParam(':espacio', $espacio_maximo, PDO::PARAM_INT);
            
            // Ejecuta la consulta
            return $stmt->execute();
        } catch (\PDOException $e) {
            // Manejo de error silencioso (registrado en log del servidor)
            error_log("Error al guardar perfil de artista: " . $e->getMessage());
            return false;
        }
    }

    // Método para obtener la información de perfil de un artista por su ID
    public function obtenerPorId($id_artista) {
        // Consulta preparada para evitar Inyección SQL
        $sql = "SELECT * FROM artistas WHERE id_artista = :id_artista";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_artista', $id_artista, PDO::PARAM_INT);
            $stmt->execute();
            // Retorna la fila con los datos del artista o false si no tiene perfil creado
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error al obtener perfil de artista: " . $e->getMessage());
            return false;
        }
    }

    // Devuelve todos los artistas activos (no baneados) para mostrar en la página de inicio.
    // Solo incluye artistas cuyo nombre artístico no sea el provisional del email.
    public function obtenerTodos($limit = 8) {
        $sql = "SELECT ar.id_artista, ar.nombre_artistico, ar.foto_perfil, ar.localidad
                FROM artistas ar
                INNER JOIN usuarios u ON ar.id_artista = u.id_usuario
                WHERE u.banned = 0
                ORDER BY ar.id_artista DESC
                LIMIT :limit";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error al obtener todos los artistas: " . $e->getMessage());
            return [];
        }
    }

    public function contarTodos() {
        try {
            return (int)$this->db->query("SELECT COUNT(*) FROM artistas")->fetchColumn();
        } catch (\PDOException $e) {
            error_log("Error al contar artistas: " . $e->getMessage());
            return 0;
        }
    }

    // Método para actualizar los datos de perfil de un artista
    // Si se sube una nueva foto_perfil se actualiza la ruta, de lo contrario se conserva la anterior
    public function actualizarPerfil($id_artista, $nombre_artistico, $bio_extended, $localidad, $foto_perfil = null) {
        if ($foto_perfil) {
            // Consulta de actualización incluyendo la ruta de la nueva foto de perfil
            $sql = "UPDATE artistas 
                    SET nombre_artistico = :nombre, bio_extended = :bio, localidad = :localidad, foto_perfil = :foto 
                    WHERE id_artista = :id";
        } else {
            // Consulta de actualización sin modificar la foto de perfil actual
            $sql = "UPDATE artistas 
                    SET nombre_artistico = :nombre, bio_extended = :bio, localidad = :localidad 
                    WHERE id_artista = :id";
        }

        try {
            $stmt = $this->db->prepare($sql);
            
            // Vinculación de parámetros
            $stmt->bindParam(':nombre', $nombre_artistico);
            $stmt->bindParam(':bio', $bio_extended);
            $stmt->bindParam(':localidad', $localidad);
            $stmt->bindParam(':id', $id_artista, PDO::PARAM_INT);
            
            if ($foto_perfil) {
                $stmt->bindParam(':foto', $foto_perfil);
            }
            
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Error al actualizar perfil de artista: " . $e->getMessage());
            return false;
        }
    }
}
?>