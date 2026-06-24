<?php
// Rutas absolutas basadas en __DIR__ del propio archivo (src/controlador/).
// dirname(__DIR__) sube un nivel a src/, dirname(__DIR__, 2) sube dos niveles a la raíz.
require_once dirname(__DIR__, 2) . '/config/conexion.php';
require_once dirname(__DIR__, 2) . '/src/modelo/Usuario.php';
require_once dirname(__DIR__, 2) . '/src/modelo/Artista.php';

class AjustesController
{
    private function urlBase()
    {
        return BASE_URL;
    }

    private function requerirSesion()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: ' . $this->urlBase() . '/index.php?action=login');
            exit();
        }
    }

    
    public function guardarPerfilArtista()
    {
        $this->requerirSesion();
        if (($_SESSION['usuario_rol'] ?? '') !== 'artista') {
            $_SESSION['mensaje_error'] = 'Solo los artistas pueden actualizar este perfil.';
            header('Location: ' . $this->urlBase() . '/ajustes.php');
            exit();
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $this->urlBase() . '/ajustes.php');
            exit();
        }
        csrf_verificar();

        $nombre = trim($_POST['nombre_artistico'] ?? '');
        $bio = trim($_POST['bio_extended'] ?? '');
        $localidad = trim($_POST['localidad'] ?? '');

        $db = new Database();
        $conn = $db->conectar();
        $artistaModel = new Artista($conn);
        $valida = $artistaModel->validarDatos($nombre, $localidad);
        if ($valida !== true) {
            $_SESSION['mensaje_error'] = $valida;
            header('Location: ' . $this->urlBase() . '/ajustes.php');
            exit();
        }

        $fotoRuta = null;
        if (!empty($_FILES['foto_perfil']['name']) && (int) ($_FILES['foto_perfil']['error'] ?? 0) === UPLOAD_ERR_OK) {
            $tiposPermitidos = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeReal = finfo_file($finfo, $_FILES['foto_perfil']['tmp_name']);
            finfo_close($finfo);
            if (!in_array($mimeReal, $tiposPermitidos, true)) {
                $_SESSION['mensaje_error'] = 'La foto debe ser una imagen JPG, PNG, WebP o GIF.';
                header('Location: ' . $this->urlBase() . '/ajustes.php');
                exit();
            }
            $dirSubida = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'perfiles' . DIRECTORY_SEPARATOR;
            /*
            DIRECTORY_SEPARATOR
            Es una constante de PHP que contiene el separador de carpetas del sistema operativo:           
            En Windows: \
            En Linux/Mac: /
            */
            if (!is_dir($dirSubida)) {
                mkdir($dirSubida, 0777, true);
            }
            $extension = pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION);
            $nombreArchivo = 'perfil_' . uniqid('', true) . '.' . $extension;
            $rutaCompleta = $dirSubida . $nombreArchivo;
            if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $rutaCompleta)) {
                $fotoRuta = 'uploads/perfiles/' . $nombreArchivo;
            }
        }

        $idArtista = (int) $_SESSION['usuario_id'];
        $perfil = $artistaModel->obtenerPorId($idArtista);
        $espacio = 104857600;
        if ($perfil && isset($perfil['espacio_maximo'])) {
            $espacio = (int) $perfil['espacio_maximo'];
        }

        $bioFinal = $bio !== '' ? $bio : ($perfil['bio_extended'] ?? 'Sin biografia.');

        if (!$perfil) {
            $fotoFinal = $fotoRuta ?: 'assets/img/default-profile.png';
            $ok = $artistaModel->guardarPerfil($idArtista, $nombre, $bioFinal, $localidad, $fotoFinal, $espacio);
        } else {
            $ok = $artistaModel->actualizarPerfil($idArtista, $nombre, $bioFinal, $localidad, $fotoRuta);
        }

        if ($ok) {
            $_SESSION['mensaje_exito'] = 'Perfil de artista actualizado.';
        } else {
            $_SESSION['mensaje_error'] = 'No se pudo guardar el perfil.';
        }
        header('Location: ' . $this->urlBase() . '/ajustes.php');
        exit();
    }

    public function eliminarCuenta()
    {
        $this->requerirSesion();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $this->urlBase() . '/ajustes.php');
            exit();
        }
        csrf_verificar();
        if (($_SESSION['usuario_rol'] ?? '') === 'admin') {
            $_SESSION['mensaje_error'] = 'No puedes eliminar una cuenta de administrador desde aqui.';
            header('Location: ' . $this->urlBase() . '/ajustes.php');
            exit();
        }

        $confirmar = trim($_POST['confirmar_eliminar'] ?? '');
        if ($confirmar !== 'ELIMINAR') {
            $_SESSION['mensaje_error'] = 'Debes escribir ELIMINAR en mayusculas para confirmar.';
            header('Location: ' . $this->urlBase() . '/ajustes.php');
            exit();
        }

        $id = (int) $_SESSION['usuario_id'];
        $db = new Database();
        $conn = $db->conectar();
        $usuarioModel = new Usuario($conn);
        if ($usuarioModel->eliminarPorId($id)) {
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params['path'], $params['domain'],
                    $params['secure'], $params['httponly']
                );
            }
            session_destroy();
            header('Location: ' . $this->urlBase() . '/index.php');
            exit();
        }

        $_SESSION['mensaje_error'] = 'No se pudo eliminar la cuenta.';
        header('Location: ' . $this->urlBase() . '/ajustes.php');
        exit();
    }
}
