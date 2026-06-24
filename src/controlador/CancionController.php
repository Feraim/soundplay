<?php
// Rutas absolutas desde la raíz del proyecto para garantizar la carga correcta en cualquier entorno.
require_once dirname(__DIR__, 2) . '/config/conexion.php';
require_once dirname(__DIR__, 2) . '/src/modelo/Album.php';
require_once dirname(__DIR__, 2) . '/src/modelo/Cancion.php';
require_once dirname(__DIR__, 2) . '/src/modelo/creditotecnico.php';

// Controlador encargado de gestionar las operaciones con Canciones, Álbumes y Créditos Técnicos
class CancionController {

    // Constructor que inicia la sesión si no se ha iniciado antes
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Verifica que el usuario tenga sesión activa y sea de tipo 'artista'
    private function verificarArtista() {
        if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'artista') {
            $_SESSION['mensaje_error'] = "Acceso denegado. Debes ser un artista para acceder.";
            header("Location: index.php?action=login");
            exit();
        }
    }

    // Procesa el formulario de creación de álbumes
    public function crearAlbum() {
        $this->verificarArtista();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verificar();
            $titulo = trim($_POST['titulo'] ?? '');

            if (empty($titulo)) {
                $_SESSION['mensaje_error'] = "El título del álbum es obligatorio.";
                header("Location: index.php?action=panel");
                exit();
            }

            if (!isset($_FILES['portada']) || $_FILES['portada']['error'] !== UPLOAD_ERR_OK) {
                $_SESSION['mensaje_error'] = "Error al subir la imagen de portada.";
                header("Location: index.php?action=panel");
                exit();
            }

            // Validar tipo real con finfo (no se puede falsificar desde el cliente)
            $tiposPermitidos = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeReal = finfo_file($finfo, $_FILES['portada']['tmp_name']);
            /*
            finfo_open: crea una herramienta para analizar archivos
            FILEINFO_MIME_TYPE: quiero que detectes el tipo MIME
            finfo_file: la herramiento que abri para leer los bytes del archivo
            lee los bytes del archivo para detectar que tipo de archivo es
            */
            finfo_close($finfo);
            if (!in_array($mimeReal, $tiposPermitidos, true)) {
                $_SESSION['mensaje_error'] = "El archivo de portada debe ser una imagen JPG, PNG, WebP o GIF.";
                header("Location: index.php?action=panel");
                exit();
            }

            // Crear directorio de subida si no existe
            $uploadDir = 'uploads/portadas/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Generar nombre de archivo único para evitar colisiones
            $fileExtension = pathinfo($_FILES['portada']['name'], PATHINFO_EXTENSION);
            /*
            pathinfo: Es una función de PHP que extrae información de una ruta de archivo
            PATHINFO_EXTENSION: Es una constante de PHP que le dice a pathinfo() que 
            solo devuelva la extensión del archivo (lo que va después del último punto)
            */
            $fileName = uniqid('portada_', true) . '.' . $fileExtension;
            $destinationPath = $uploadDir . $fileName;

            // Mover archivo a su destino
            if (move_uploaded_file($_FILES['portada']['tmp_name'], $destinationPath)) {
                // Registrar el álbum en la base de datos
                $db = new Database();
                $conexion = $db->conectar();
                $albumModel = new Album($conexion);

                $id_artista = $_SESSION['usuario_id'];

                if ($albumModel->crearAlbum($id_artista, $titulo, $destinationPath)) {
                    $_SESSION['mensaje_exito'] = "¡Álbum creado exitosamente!";
                } else {
                    $_SESSION['mensaje_error'] = "Error al registrar el álbum en la base de datos.";
                    // Limpiar archivo si falló la inserción
                    unlink($destinationPath);
                }
            } else {
                $_SESSION['mensaje_error'] = "No se pudo guardar la imagen de portada.";
            }

            header("Location: index.php?action=panel");
            exit();
        }
    }

    // Procesa el formulario de subida de canciones y asignación de créditos
    public function subirCancion() {
        $this->verificarArtista();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verificar();
            $id_album = (int)($_POST['id_album'] ?? 0);
            $titulo = trim($_POST['titulo'] ?? '');
            $genero = trim($_POST['genero'] ?? '');
            
            // Créditos técnicos opcionales
            $credito_nombre = trim($_POST['credito_nombre'] ?? '');
            $credito_rol = trim($_POST['credito_rol'] ?? 'otro');

            // Validar campos obligatorios
            if (empty($id_album) || empty($titulo) || empty($genero)) {
                $_SESSION['mensaje_error'] = "Por favor, rellene todos los campos obligatorios.";
                header("Location: index.php?action=panel");
                exit();
            }

            // Validar que se ha subido el archivo MP3
            if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
                $_SESSION['mensaje_error'] = "Error al subir el archivo de audio.";
                header("Location: index.php?action=panel");
                exit();
            }

            // Validar tipo de archivo (audio MP3)
            $fileNameOriginal = $_FILES['archivo']['name'];
            $fileExtension = strtolower(pathinfo($fileNameOriginal, PATHINFO_EXTENSION));
            if ($fileExtension !== 'mp3') {
                $_SESSION['mensaje_error'] = "Únicamente se permiten archivos de formato MP3.";
                header("Location: index.php?action=panel");
                exit();
            }

            // Crear directorio de subida si no existe
            $uploadDir = 'uploads/canciones/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Generar nombre de archivo único
            $fileName = uniqid('cancion_', true) . '.mp3';
            $destinationPath = $uploadDir . $fileName;

            // Mover archivo a su destino
            if (move_uploaded_file($_FILES['archivo']['tmp_name'], $destinationPath)) {
                
                // Calcular de forma automática la duración del MP3
                $duracion = $this->obtenerDuracionMP3($destinationPath);

                // Conectar a BD
                $db = new Database();
                $conexion = $db->conectar();
                
                $cancionModel = new Cancion($conexion);

                // Guardar la canción en la BD
                if ($cancionModel->guardarCancion($id_album, $titulo, $destinationPath, $genero, $duracion)) {
                    // Obtener el ID de la canción recién insertada para vincular los créditos
                    $id_cancion = $conexion->lastInsertId();

                    // Si el usuario proporcionó un crédito técnico, lo registramos
                    if (!empty($credito_nombre)) {
                        $creditoModel = new CreditoTecnico($conexion);
                        // Validar y guardar el crédito técnico
                        $valida = $creditoModel->validarDatos($credito_nombre, $credito_rol);
                        if ($valida === true) {
                            $creditoModel->agregarCredito($id_cancion, $credito_nombre, $credito_rol);
                        } else {
                            // Guardamos aviso secundario pero no cancelamos la subida de la canción
                            $_SESSION['mensaje_error'] = "Canción subida, pero hubo un problema con el crédito técnico: " . $valida;
                        }
                    }

                    if (!isset($_SESSION['mensaje_error'])) {
                        $_SESSION['mensaje_exito'] = "¡Canción subida y guardada exitosamente!";
                    }
                } else {
                    $_SESSION['mensaje_error'] = "Error al guardar la canción en la base de datos.";
                    unlink($destinationPath); // Limpiar archivo
                }
            } else {
                $_SESSION['mensaje_error'] = "No se pudo guardar el archivo de audio.";
            }

            header("Location: index.php?action=panel");
            exit();
        }
    }

    // Función auxiliar para leer los metadatos y calcular la duración de un archivo MP3 de forma automática.
    // Retorna la duración en formato de tiempo MySQL 'hh:mm:ss'.
    private function obtenerDuracionMP3($filePath) {
        $fd = fopen($filePath, "rb");
        if (!$fd) {
            return "00:03:00"; // Fallback de seguridad
        }
        
        // 1. Saltar cabecera ID3v2 si está presente en el archivo
        $header = fread($fd, 10);
        if (substr($header, 0, 3) === 'ID3') {
            // El tamaño de la cabecera ID3v2 está en los bytes 6 a 9 codificado en synchsafe (7 bits útiles)
            $size = (ord($header[6]) & 0x7F) << 21 |
                    (ord($header[7]) & 0x7F) << 14 |
                    (ord($header[8]) & 0x7F) << 7  |
                    (ord($header[9]) & 0x7F);
            fseek($fd, $size + 10); // Avanzar el puntero hasta el inicio del flujo de audio real
        } else {
            fseek($fd, 0); // No hay cabecera ID3v2, volver al inicio del archivo
        }
        
        $audioStart = ftell($fd);
        $fileSize = filesize($filePath);
        $audioSize = $fileSize - $audioStart;
        
        // Tablas de tasa de bits (bitrate) según la versión y capa de MPEG (Layer 3)
        $bitrates = [
            1 => [0, 32, 40, 48, 56, 64, 80, 96, 112, 128, 160, 192, 224, 256, 320, 0], // MPEG-1
            2 => [0, 8, 16, 24, 32, 40, 48, 56, 64, 80, 96, 112, 128, 144, 160, 0]     // MPEG-2
        ];
        
        // 2. Buscar la primera cabecera de trama (frame) de audio MP3
        // Una trama MP3 empieza con 11 bits consecutivos a 1 (sincronización: 0xFF más los primeros 3 bits de 0xE0)
        while (!feof($fd)) {
            $byte = fread($fd, 1);
            if (ord($byte) === 0xFF) {
                $nextByte = fread($fd, 1);
                if ((ord($nextByte) & 0xE0) === 0xE0) {
                    // Hemos encontrado un frame. Analizamos el tercer byte para extraer la tasa de bits.
                    $thirdByte = fread($fd, 1);
                    $version = (ord($nextByte) & 0x08) ? 1 : 2; // MPEG-1 (1) o MPEG-2 (2)
                    $bitrateIndex = (ord($thirdByte) & 0xF0) >> 4;
                    
                    $bitrate = $bitrates[$version][$bitrateIndex] ?? 128; // fallback a 128 kbps
                    
                    if ($bitrate > 0) {
                        // Duración (en segundos) = tamaño del audio en bytes / (bitrate en bytes por segundo)
                        // Bitrate en bytes/seg = (bitrate en kbps * 1000) / 8
                        $seconds = $audioSize / (($bitrate * 1000) / 8);
                        fclose($fd);
                        
                        // Formatear segundos en formato TIME ('hh:mm:ss')
                        $hours = floor($seconds / 3600);
                        $mins = floor(($seconds % 3600) / 60);
                        $secs = floor($seconds % 60);
                        return sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                    }
                    break;
                }
            }
        }
        
        fclose($fd);
        return "00:03:00"; // Fallback por defecto si no se pudo determinar
    }

    // Muestra la página de exploración (géneros) sin ningún término de búsqueda activo.
    // Carga también las canciones recientes para la sección "Tendencias".
    public function mostrarExplorar() {
        $query      = '';   // Término de búsqueda vacío
        $resultados = null; // null indica que no se ha buscado todavía

        // Cargar tendencias: últimas canciones subidas para mostrar en la sección inferior
        $db          = new Database();
        $conexion    = $db->conectar();
        $cancionModel = new Cancion($conexion);
        $tendencias  = $cancionModel->obtenerRecientes(8);

        require 'src/vista/explorar.php';
    }

    // Muestra el perfil público de un artista con su foto, biografía y discografía.
    // Recibe el ID del artista por GET. Si no existe, redirige a explorar.
    public function verArtista() {
        // Obtener el ID del artista desde la URL; asegurarlo como entero (0 si no viene)
        $id = (int)($_GET['id'] ?? 0);

        // Si el ID no es válido (0 o negativo), redirigir a explorar
        if ($id <= 0) {
            header("Location: index.php?action=explorar");
            exit();
        }

        // Abrir conexión a la base de datos
        $db      = new Database();
        $conexion = $db->conectar();

        // Cargar modelos necesarios para construir el perfil completo del artista
        require_once 'src/modelo/Artista.php';
        require_once 'src/modelo/Album.php';

        // Instanciar los modelos necesarios
        $artistaModel = new Artista($conexion);
        $albumModel   = new Album($conexion);
        $cancionModel = new Cancion($conexion);

        // Buscar los datos del artista; si no existe, redirigir con mensaje de error
        $artista = $artistaModel->obtenerPorId($id);
        if (!$artista) {
            $_SESSION['mensaje_error'] = "Artista no encontrado.";
            header("Location: index.php?action=explorar");
            exit();
        }

        // Obtener todos los álbumes del artista ordenados del más reciente al más antiguo
        $albumes  = $albumModel->obtenerAlbumesPorArtista($id);

        // Obtener todas las canciones del artista junto con datos de su álbum
        $canciones = $cancionModel->obtenerCancionesConAlbumPorArtista($id);

        // Construir la estructura anidada: cada álbum contiene su array de canciones
        $albumesConCanciones = [];
        foreach ($albumes as $alb) {
            // Indexar el álbum por su ID y añadir el sub-array de canciones vacío
            $albumesConCanciones[$alb['id_album']] = $alb;
            $albumesConCanciones[$alb['id_album']]['canciones'] = [];
        }
        foreach ($canciones as $c) {
            // Solo añadir la canción si su álbum padre existe en el mapa
            if (isset($albumesConCanciones[$c['id_album']])) {
                $albumesConCanciones[$c['id_album']]['canciones'][] = $c;
            }
        }

        // Recoger todos los IDs de canciones para la consulta de créditos
        $idsCanciones = array_column($canciones, 'id_cancion');

        // Instanciar el modelo de créditos técnicos
        $creditoModel = new CreditoTecnico($conexion);

        // Obtener el mapa de créditos indexado por id_cancion
        $creditosMap = $creditoModel->obtenerMapaPorCanciones($idsCanciones);

        // Cargar la vista del perfil del artista; tiene acceso a $artista, $albumesConCanciones y $creditosMap
        require 'src/vista/perfil_artista.php';
    }

    // Procesa una búsqueda de canciones y álbumes y muestra los resultados en la vista de exploración.
    // Recibe el término a través del parámetro GET 'q'. Si el término está vacío, no lanza ninguna consulta.
    public function buscar() {
        $query     = trim($_GET['q'] ?? '');
        $tendencias = []; // No se muestran tendencias en la vista de resultados

        if (!empty($query)) {
            $db           = new Database();
            $conexion     = $db->conectar();
            $cancionModel = new Cancion($conexion);
            $resultados   = $cancionModel->buscar($query);
        } else {
            $resultados = null;
        }

        require 'src/vista/explorar.php';
    }

    // Muestra todas las canciones de un género agrupadas por álbum.
    // Los álbumes se construyen aquí en el controlador para que la vista solo tenga que iterar.
    public function verGenero() {
        // Obtener el género desde la URL y validar que no esté vacío
        $genero = isset($_GET['genero']) ? trim($_GET['genero']) : '';

        // Si no se pasó género, redirigir a explorar con un mensaje de error
        if (empty($genero)) {
            $_SESSION['mensaje_error'] = "Debe especificar un género para explorar.";
            header("Location: index.php?action=explorar");
            exit();
        }

        // Abrir conexión a la base de datos
        $db = new Database();
        $conexion = $db->conectar();

        // Instanciar el modelo de Cancion para las consultas
        $cancionModel = new Cancion($conexion);

        // Obtener todas las canciones del género especificado
        $canciones = $cancionModel->obtenerCancionesPorGenero($genero);

        // Agrupar el array plano de canciones en una estructura anidada por álbum.
        // Cada entrada de $albumes contiene los datos del álbum y un sub-array 'canciones'.
        $albumes = [];
        foreach ($canciones as $c) {
            // Obtener el ID del álbum de la canción actual
            $aid = $c['id_album'];
            // Si es la primera canción de este álbum, crear la entrada del álbum
            if (!isset($albumes[$aid])) {
                $albumes[$aid] = [
                    'id_album'         => $aid,          // ID único del álbum
                    'titulo_album'     => $c['titulo_album'],    // Título del álbum
                    'portada_ruta'     => $c['portada_ruta'],    // Ruta de la imagen de portada
                    'nombre_artistico' => $c['nombre_artistico'], // Nombre artístico del autor
                    'canciones'        => [],             // Sub-array vacío que se irá llenando
                ];
            }
            // Añadir la canción al sub-array de su álbum correspondiente
            $albumes[$aid]['canciones'][] = $c;
        }

        // Recoger todos los IDs de canciones para obtener sus créditos técnicos de una sola vez
        $idsCanciones = array_column($canciones, 'id_cancion');

        // Instanciar el modelo de créditos técnicos
        $creditoModel = new CreditoTecnico($conexion);

        // Obtener todos los créditos indexados por id_cancion: [ id => [credito1, ...] ]
        $creditosMap = $creditoModel->obtenerMapaPorCanciones($idsCanciones);

        // Cargar la vista de género; tendrá acceso a $genero, $albumes y $creditosMap
        require 'src/vista/genero.php';
    }
}
?>
