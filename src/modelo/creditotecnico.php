<?php
// Modelo que representa un Crédito Técnico de una canción
class CreditoTecnico {
    protected $id_credito;
    protected $id_cancion;
    protected $nombre_profesional;
    protected $rol; // enum('productor','ingeniero_mezcla','masterización','compositor','otro')
    private $db;

    // Constructor que recibe conexión a BD
    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    // Valida nombre y asegura que el rol es uno de los permitidos por la DB
    public function validarDatos($nombre_profesional, $rol) {
        if (empty(trim($nombre_profesional))) {
            return "El nombre del profesional es obligatorio.";
        }
        
        $roles_permitidos = ['productor', 'ingeniero_mezcla', 'masterización', 'compositor', 'otro'];
        if (!in_array($rol, $roles_permitidos)) {
            return "El rol especificado no es válido.";
        }
        
        return true;
    }

    // Inserta un crédito técnico vinculado a una canción (id_cancion)
    public function agregarCredito($id_cancion, $nombre_profesional, $rol) {
        // Consulta preparada para insertar el crédito en la base de datos
        $sql = "INSERT INTO creditos_tecnicos (id_cancion, nombre_profesional, rol)
                VALUES (:id_cancion, :nombre, :rol)";

        try {
            // Preparar la sentencia para evitar inyección SQL
            $stmt = $this->db->prepare($sql);
            // Vincular el ID de la canción como entero
            $stmt->bindParam(':id_cancion', $id_cancion, PDO::PARAM_INT);
            // Vincular el nombre del profesional
            $stmt->bindParam(':nombre', $nombre_profesional);
            // Vincular el rol (productor, ingeniero, etc.)
            $stmt->bindParam(':rol', $rol);
            // Ejecutar e insertar el crédito; retorna true si fue correcto
            return $stmt->execute();
        } catch (\PDOException $e) {
            // Registrar el error sin mostrárselo al usuario
            error_log("Error al agregar crédito técnico: " . $e->getMessage());
            return false;
        }
    }

    // Devuelve un array asociativo indexado por id_cancion con todos los créditos
    // de una lista de canciones. Así evitamos hacer una consulta por cada canción.
    // Ejemplo de resultado: [ 5 => [['nombre_profesional'=>'Juan','rol'=>'productor']], ... ]
    public function obtenerMapaPorCanciones(array $ids_canciones): array {
        // Si no hay ninguna canción, devolvemos un array vacío directamente
        if (empty($ids_canciones)) {
            return [];
        }

        // Construir dinámicamente los placeholders para el IN (:id0, :id1, :id2...)
        $placeholders = [];
        foreach ($ids_canciones as $i => $id) {
            // Cada placeholder tiene un nombre único basado en su posición
            $placeholders[] = ':id' . $i;
        }
        // Unir los placeholders separados por coma para incluirlos en el SQL
        $placeholderStr = implode(',', $placeholders);

        // Consulta que recupera todos los créditos de las canciones solicitadas
        $sql = "SELECT id_cancion, nombre_profesional, rol
                FROM creditos_tecnicos
                WHERE id_cancion IN ({$placeholderStr})";

        try {
            // Preparar la consulta con los placeholders dinámicos
            $stmt = $this->db->prepare($sql);

            // Vincular cada ID de canción al placeholder correspondiente
            foreach ($ids_canciones as $i => $id) {
                $stmt->bindValue(':id' . $i, (int)$id, PDO::PARAM_INT);
            }

            // Ejecutar la consulta
            $stmt->execute();

            // Obtener todas las filas de resultado
            $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Agrupar los créditos por id_cancion para acceso rápido desde las vistas
            $mapa = [];
            foreach ($filas as $fila) {
                // Añadir el crédito al array de su canción
                $mapa[$fila['id_cancion']][] = $fila;
            }

            // Devolver el mapa: [ id_cancion => [ credito1, credito2, ... ] ]
            return $mapa;
        } catch (\PDOException $e) {
            // Registrar el error de base de datos sin interrumpir la carga de página
            error_log("Error al obtener mapa de créditos: " . $e->getMessage());
            // En caso de error devolvemos vacío: las vistas lo manejan mostrando nada
            return [];
        }
    }
}
?>