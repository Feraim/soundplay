<?php
// Rutas absolutas: dirname(__DIR__, 2) sube dos niveles desde src/controlador/ hasta la raíz del proyecto.
// Esto garantiza que PHP encuentre el archivo sin importar el directorio de trabajo del servidor.
require_once dirname(__DIR__, 2) . '/config/conexion.php';
require_once dirname(__DIR__, 2) . '/src/modelo/Usuario.php';

// Controlador para gestionar el registro y login de usuarios
class UsuarioController {
    
    // Muestra la vista del formulario de Login
    public function mostrarLogin() {
        require 'src/vista/login.php';
    }

    // Muestra la vista del formulario de Registro
    public function mostrarRegistro() {
        require 'src/vista/registro.php';
    }

    // Procesa los datos enviados desde el formulario de registro
    public function procesarRegistro() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verificar();
            $email = $_POST['email'] ?? '';
            $contrasena = $_POST['contrasena'] ?? '';
            $confirmar_contrasena = $_POST['confirmar_contrasena'] ?? '';
            $rol = $_POST['rol'] ?? 'user';
            // El checkbox de RGPD manda 'on' si está marcado
            $consentimiento = isset($_POST['rgpd']) ? 1 : 0; 

            // 1. Validaciones básicas del controlador
            if ($contrasena !== $confirmar_contrasena) {
                // Si las contraseñas no coinciden, volvemos a mostrar el formulario con error
                $error = "Las contraseñas no coinciden.";
                require 'src/vista/registro.php';
                return;
            }

            // 2. Conectar a la base de datos e instanciar el modelo
            $db = new Database();
            $conexion = $db->conectar();
            $usuarioModel = new Usuario($conexion);

            // 3. Validar con el modelo
            $validacion = $usuarioModel->validarRegistro($email, $contrasena, $rol, $consentimiento);
            
            if ($validacion !== true) {
                // Mostramos el error devuelto por el modelo
                $error = $validacion;
                require 'src/vista/registro.php';
                return;
            }

            // 4. Intentar guardar en la base de datos
            if ($usuarioModel->registrar($email, $contrasena, $rol, $consentimiento)) {
                // Registro exitoso, redirigimos al login con mensaje de éxito
                $mensaje_exito = "Registro exitoso. Ya puedes iniciar sesión.";
                require 'src/vista/login.php';
            } else {
                // Error al guardar (ej. email ya existe)
                $error = "Hubo un problema al registrar la cuenta. Es posible que el email ya esté en uso.";
                require 'src/vista/registro.php';
            }
        } else {
            // Si intentan entrar por GET a procesar, redirigir al formulario
            $this->mostrarRegistro();
        }
    }

    // Procesa el login verificando contra la base de datos de manera segura
    public function procesarLogin() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verificar();
            $email = $_POST['email'] ?? '';
            $contrasena = $_POST['contrasena'] ?? '';

            // 1. Validar campos requeridos
            if (empty(trim($email)) || empty(trim($contrasena))) {
                $error = "Por favor, completa todos los campos.";
                require 'src/vista/login.php';
                return;
            }

            // 2. Conectar a la base de datos e instanciar el modelo
            $db = new Database();
            $conexion = $db->conectar();
            $usuarioModel = new Usuario($conexion);

            // 3. Buscar el usuario por email
            $usuario = $usuarioModel->obtenerPorEmail($email);

            // 4. Verificar la contraseña usando password_verify
            if ($usuario && password_verify($contrasena, $usuario['contrasena'])) {
                // Verificar de forma segura si la cuenta ha sido suspendida (baneada)
                if (isset($usuario['banned']) && (int)$usuario['banned'] === 1) {
                    $error = "Tu cuenta ha sido suspendida temporal o permanentemente por el administrador.";
                    require 'src/vista/login.php';
                    return;
                }

                // Login exitoso: regenerar ID para prevenir session fixation
                session_regenerate_id(true);
                $_SESSION['usuario_id'] = $usuario['id_usuario'];
                $_SESSION['usuario_email'] = $usuario['email'];
                $_SESSION['usuario_rol'] = $usuario['rol'];

                // Redirigir al home (o al respectivo panel)
                header("Location: index.php");
                exit();
            } else {
                // Email o contraseña incorrectos
                $error = "El correo electrónico o la contraseña son incorrectos.";
                require 'src/vista/login.php';
            }
            
        } else {
            $this->mostrarLogin();
        }
    }

    // Muestra el panel correspondiente según el rol del usuario conectado
    public function mostrarPanel() {
        // Verificar si el usuario ha iniciado sesión
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: index.php?action=login");
            exit();
        }

        // Recuperar mensajes flash de éxito o error almacenados temporalmente en la sesión
        $mensaje_exito = null;
        $error = null;
        if (isset($_SESSION['mensaje_exito'])) {
            $mensaje_exito = $_SESSION['mensaje_exito'];
            unset($_SESSION['mensaje_exito']); // Se elimina para que no se muestre al refrescar
        }
        if (isset($_SESSION['mensaje_error'])) {
            $error = $_SESSION['mensaje_error'];
            unset($_SESSION['mensaje_error']); // Se elimina para que no se muestre al refrescar
        }

        // Obtener el rol del usuario
        $rol = $_SESSION['usuario_rol'] ?? 'user';

        // Redirigir a la vista correspondiente
        if ($rol === 'artista') {
            // Requerir los modelos de base de datos necesarios para rellenar los datos dinámicos
            require_once 'src/modelo/Artista.php';
            require_once 'src/modelo/Album.php';
            require_once 'src/modelo/Cancion.php';

            // Conectar a la base de datos
            $db = new Database();
            $conexion = $db->conectar();

            // Instanciar modelos
            $artistaModel = new Artista($conexion);
            $albumModel = new Album($conexion);
            $cancionModel = new Cancion($conexion);

            // Obtener el perfil del artista para validar que tenga fila en la tabla 'artistas'
            $perfilArtista = $artistaModel->obtenerPorId($_SESSION['usuario_id']);
            
            // Si el perfil no existe en la base de datos (por ejemplo, tras el registro inicial),
            // creamos una fila de artista por defecto para evitar errores de clave foránea al crear álbumes.
            if (!$perfilArtista) {
                // Obtener el nombre artístico provisional usando la primera parte de su email
                $nombreArtistico = explode('@', $_SESSION['usuario_email'])[0];
                // Guardar perfil inicial con 100 MB de espacio máximo por defecto
                $artistaModel->guardarPerfil($_SESSION['usuario_id'], $nombreArtistico, 'Sin biografía.', 'Sin especificar', 'assets/img/default-profile.png', 104857600);
                $perfilArtista = $artistaModel->obtenerPorId($_SESSION['usuario_id']);
            }

            // Obtener lista de álbumes creados por el artista
            $albumes = $albumModel->obtenerAlbumesPorArtista($_SESSION['usuario_id']);

            // Obtener la suma total de reproducciones de todas las canciones del artista
            $totalReproducciones = $cancionModel->obtenerTotalReproduccionesArtista($_SESSION['usuario_id']);

            // Obtener el array completo de canciones del artista (con datos del álbum incluidos)
            // Antes solo se contaban; ahora guardamos el array entero para mostrarlo en el panel
            $cancionesArtista = $cancionModel->obtenerCancionesConAlbumPorArtista($_SESSION['usuario_id']);

            // Contar cuántas canciones tiene el artista para el widget de estadísticas
            $totalCanciones = count($cancionesArtista);

            // Cargar el modelo de créditos técnicos para mostrarlos junto a cada canción
            require_once 'src/modelo/creditotecnico.php';
            $creditoModel = new CreditoTecnico($conexion);

            // Extraer los IDs de todas las canciones del artista en un array plano
            $idsCanciones = array_column($cancionesArtista, 'id_cancion');

            // Obtener todos los créditos agrupados por id_cancion de una sola consulta
            $creditosMap = $creditoModel->obtenerMapaPorCanciones($idsCanciones);

            require 'src/vista/panel_artista.php';
        } elseif ($rol === 'admin') {
            require_once 'src/modelo/Cancion.php';
            require_once 'src/modelo/Artista.php';
            $db = new Database();
            $conexion = $db->conectar();
            $cancionModelAdmin = new Cancion($conexion);
            $artistaModelAdmin = new Artista($conexion);
            $totalCancionesAdmin = $cancionModelAdmin->contarTodas();
            $totalArtistasAdmin = $artistaModelAdmin->contarTodos();
            require 'src/vista/panel_admin.php';
        } else {
            require 'src/vista/panel_user.php';
        }
    }

    // Verifica que el usuario conectado tenga el rol de administrador 'admin'
    private function verificarAdmin() {
        if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
            $_SESSION['mensaje_error'] = "Acceso denegado. Debes ser un administrador para realizar esta acción.";
            header("Location: index.php?action=login");
            exit();
        }
    }

    // Procesa la suspensión o reactivación de cuentas de usuario desde el panel
    public function banearUsuario() {
        $this->verificarAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verificar();
            $email = trim($_POST['email'] ?? '');
            $estado_ban = (int)($_POST['estado_ban'] ?? 0);

            if (empty($email)) {
                $_SESSION['mensaje_error'] = "El correo electrónico es obligatorio.";
                header("Location: index.php?action=panel");
                exit();
            }

            // Conectar a base de datos e instanciar modelo de Usuario
            $db = new Database();
            $conexion = $db->conectar();
            $usuarioModel = new Usuario($conexion);

            // Impedir que un administrador se autobanee
            if ($email === $_SESSION['usuario_email']) {
                $_SESSION['mensaje_error'] = "No puedes suspender tu propia cuenta de administrador.";
                header("Location: index.php?action=panel");
                exit();
            }

            if ($usuarioModel->banearUsuario($email, $estado_ban)) {
                $accion = $estado_ban === 1 ? "suspendida (baneada)" : "reactivada (desbaneada)";
                $_SESSION['mensaje_exito'] = "La cuenta $email ha sido $accion con éxito.";
            } else {
                $_SESSION['mensaje_error'] = "Error al intentar cambiar el estado de la cuenta $email.";
            }

            header("Location: index.php?action=panel");
            exit();
        }
    }

    // Procesa el restablecimiento de contraseñas de los usuarios desde el panel
    public function resetPassword() {
        $this->verificarAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verificar();
            $email = trim($_POST['email'] ?? '');
            $nueva_contrasena = trim($_POST['nueva_contrasena'] ?? '');

            if (empty($email) || empty($nueva_contrasena)) {
                $_SESSION['mensaje_error'] = "El correo electrónico y la nueva contraseña son obligatorios.";
                header("Location: index.php?action=panel");
                exit();
            }

            // Conectar a base de datos e instanciar modelo de Usuario
            $db = new Database();
            $conexion = $db->conectar();
            $usuarioModel = new Usuario($conexion);

            // Generar hash seguro de la contraseña temporal
            $nueva_contrasena_hash = password_hash($nueva_contrasena, PASSWORD_BCRYPT);

            if ($usuarioModel->actualizarContrasena($email, $nueva_contrasena_hash)) {
                $_SESSION['mensaje_exito'] = "La contraseña de $email ha sido restablecida correctamente.";
            } else {
                $_SESSION['mensaje_error'] = "Error al actualizar la contraseña del usuario en la base de datos.";
            }

            header("Location: index.php?action=panel");
            exit();
        }
    }

    // Cierra la sesión de PHP de forma segura
    public function logout() {
        // Limpiar todas las variables de sesión
        $_SESSION = array();

        // Destruir la cookie de sesión si existe
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Destruir la sesión
        session_destroy();

        header("Location: index.php");
        exit();
    }
}
?>
