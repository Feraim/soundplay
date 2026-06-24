<?php
// Modelo que representa a un Usuario en el sistema
class Usuario {
    // Atributos protegidos que coinciden con las columnas de la base de datos
    protected $id_usuario;
    protected $email;
    protected $contrasena;
    protected $rol; // 'admin', 'artista', 'user'
    protected $consentimiento_rgpd;
    protected $fecha_registro; // Corregido a 'registro' para que coincida con DB
    private $db; // Conexión PDO a la base de datos

    // Constructor que recibe la conexión PDO
    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    // Método para validar los datos antes de insertarlos en la base de datos
    public function validarRegistro($email, $contrasena, $rol, $consentimiento) {
        // Validar formato de email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "El formato del correo electrónico no es válido.";
            //filer_var: filtra y valida
            //FILTER_VALIDATE_EMAIL: constante que dice que tipo de validacion aplicar
        }
        
        // Validar contraseña (min. 8 caracteres para seguridad básica)
        if (strlen($contrasena) < 8) {
            return "La contraseña debe tener al menos 8 caracteres.";
        }

        // Validar roles permitidos según enum de DB
        $roles_permitidos = ['admin', 'artista', 'user'];
        if (!in_array($rol, $roles_permitidos)) {
            return "Rol no válido.";
        }

        // Validar consentimiento RGPD
        if ($consentimiento != 1 && $consentimiento !== true) {
            return "Debe aceptar los términos de privacidad (RGPD).";
        }

        return true; // Si todo está bien, retorna true
    }

    // Método para registrar un usuario de manera segura en la base de datos
    public function registrar($email, $contrasena, $rol, $consentimiento) {
        // Encriptar la contraseña usando el algoritmo por defecto de PHP (Bcrypt)
        $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);
        
        // La fecha de registro puede ser manejada por la base de datos (timestamp por defecto),
        // pero la enviamos explícitamente por seguridad
        $fecha_actual = date('Y-m-d H:i:s');

        // Sentencia SQL preparada para prevenir Inyección SQL
        $sql = "INSERT INTO usuarios (email, contrasena, rol, consentimiento_rgpd, fecha_registro) 
                VALUES (:email, :contrasena, :rol, :consentimiento, :fecha)";

        try {
            $stmt = $this->db->prepare($sql);
            
            // Asignar parámetros de manera segura
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':contrasena', $contrasena_hash);
            $stmt->bindParam(':rol', $rol);
            $stmt->bindParam(':consentimiento', $consentimiento, PDO::PARAM_INT);
            $stmt->bindParam(':fecha', $fecha_actual);
            
            // Ejecutar la inserción y retornar el resultado (true/false)
            return $stmt->execute();
        } catch (\PDOException $e) {
            // En un entorno de producción, es mejor registrar esto en un log de errores, 
            // no mostrarlo al usuario. Por ahora retornamos falso.
            error_log("Error al registrar usuario: " . $e->getMessage());
            return false;
        }
    }

    // Método para obtener un usuario por su correo electrónico (usado en el Login)
    public function obtenerPorEmail($email) {
        $sql = "SELECT * FROM usuarios WHERE email = :email";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            // Retorna la fila asociativa si existe, o false si no
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error al obtener usuario por email: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerPorId($id_usuario) {
        $this->asegurarColumnaRecomendaciones();
        $sql = "SELECT * FROM usuarios WHERE id_usuario = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error al obtener usuario por id: " . $e->getMessage());
            return false;
        }
    }

    public function actualizarRecomendaciones($id_usuario, $activo) {
        $this->asegurarColumnaRecomendaciones();
        $sql = "UPDATE usuarios SET recomendaciones_activas = :activo WHERE id_usuario = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':activo', $activo, PDO::PARAM_INT);
            $stmt->bindParam(':id', $id_usuario, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Error al actualizar recomendaciones: " . $e->getMessage());
            return false;
        }
    }

    public function eliminarPorId($id_usuario) {
        try {
            $stmt = $this->db->prepare("DELETE FROM transacciones WHERE id_usuario_emisor = :id");
            $stmt->bindParam(':id', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Aviso al borrar transacciones por emisor: " . $e->getMessage());
        }
        try {
            $stmt = $this->db->prepare("DELETE FROM transacciones WHERE id_artista_receptor = :id");
            $stmt->bindParam(':id', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Aviso al borrar transacciones por receptor: " . $e->getMessage());
        }
        $sql = "DELETE FROM usuarios WHERE id_usuario = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id_usuario, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Error al eliminar usuario: " . $e->getMessage());
            return false;
        }
    }

    public function asegurarColumnaRecomendaciones() {
        static $checked = false;
        if ($checked) return;
        $checked = true;
        try {
            $this->db->query("SELECT recomendaciones_activas FROM usuarios LIMIT 1");
        } catch (\PDOException $e) {
            try {
                $this->db->exec("ALTER TABLE usuarios ADD COLUMN recomendaciones_activas TINYINT(1) NOT NULL DEFAULT 1");
            } catch (\PDOException $ex) {
                error_log("Error al crear columna recomendaciones_activas: " . $ex->getMessage());
            }
        }
    }

    // Método para restablecer la contraseña de un usuario a partir de su correo
    public function actualizarContrasena($email, $contrasena_hash) {
        $sql = "UPDATE usuarios SET contrasena = :contrasena WHERE email = :email";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':contrasena', $contrasena_hash);
            $stmt->bindParam(':email', $email);
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Error al actualizar contraseña: " . $e->getMessage());
            return false;
        }
    }

    // Método para marcar una cuenta de usuario como baneada (1) o activa (0)
    public function banearUsuario($email, $banned) {
        // Ejecución autocurativa: si la columna 'banned' no existe, se añade al vuelo
        $this->asegurarColumnaBanned();

        $sql = "UPDATE usuarios SET banned = :banned WHERE email = :email";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':banned', $banned, PDO::PARAM_INT);
            $stmt->bindParam(':email', $email);
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Error al banear usuario: " . $e->getMessage());
            return false;
        }
    }

    public function asegurarColumnaBanned() {
        static $checked = false;
        if ($checked) return;
        $checked = true;
        try {
            $this->db->query("SELECT banned FROM usuarios LIMIT 1");
        } catch (\PDOException $e) {
            try {
                $this->db->exec("ALTER TABLE usuarios ADD COLUMN banned TINYINT(1) DEFAULT 0");
            } catch (\PDOException $ex) {
                error_log("Error al crear la columna 'banned' de forma autocurativa: " . $ex->getMessage());
            }
        }
    }
}
?>
