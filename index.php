<?php
session_start();

// --- INICIALIZACIÓN AUTOCURATIVA: CREAR ADMINISTRADOR POR DEFECTO ---
require_once 'config/conexion.php';
require_once 'src/modelo/Usuario.php';
$dbTemp   = new Database();
$connTemp = $dbTemp->conectar();
if ($connTemp) {
    $uTemp = new Usuario($connTemp);
    $uTemp->asegurarColumnaBanned();
    $uTemp->asegurarColumnaRecomendaciones();
    if (!$uTemp->obtenerPorEmail('admin@soundplay.com')) {
        $uTemp->registrar('admin@soundplay.com', 'admin12345', 'admin', 1);
    }
}
// -------------------------------------------------------------------

// --- ENRUTADOR ---
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    // Acciones de usuario y administración
    if (in_array($action, ['login','registro','procesarLogin','procesarRegistro','logout','panel','banearUsuario','resetPassword'])) {
        require_once 'src/controlador/UsuarioController.php';
        $uc = new UsuarioController();
        if      ($action === 'login')             $uc->mostrarLogin();
        elseif  ($action === 'registro')          $uc->mostrarRegistro();
        elseif  ($action === 'procesarLogin')     $uc->procesarLogin();
        elseif  ($action === 'procesarRegistro')  $uc->procesarRegistro();
        elseif  ($action === 'logout')            $uc->logout();
        elseif  ($action === 'panel')             $uc->mostrarPanel();
        elseif  ($action === 'banearUsuario')     $uc->banearUsuario();
        elseif  ($action === 'resetPassword')     $uc->resetPassword();
        exit();
    }

    // Acciones de canciones, álbumes, exploración y perfil de artista
    if (in_array($action, ['crearAlbum','subirCancion','explorar','verGenero','buscar','verArtista'])) {
        require_once 'src/controlador/CancionController.php';
        $cc = new CancionController();
        if      ($action === 'crearAlbum')  $cc->crearAlbum();
        elseif  ($action === 'subirCancion') $cc->subirCancion();
        elseif  ($action === 'explorar')    $cc->mostrarExplorar();
        elseif  ($action === 'verGenero')   $cc->verGenero();
        elseif  ($action === 'buscar')      $cc->buscar();
        elseif  ($action === 'verArtista')  $cc->verArtista();
        exit();
    }

    // Acciones de ajustes de cuenta
    if (in_array($action, ['guardarPreferenciasAjustes','guardarPerfilArtistaAjustes','eliminarCuenta'], true)) {
        require_once 'src/controlador/AjustesController.php';
        $ac = new AjustesController();
        if      ($action === 'guardarPreferenciasAjustes')  $ac->guardarPreferencias();
        elseif  ($action === 'guardarPerfilArtistaAjustes') $ac->guardarPerfilArtista();
        elseif  ($action === 'eliminarCuenta')              $ac->eliminarCuenta();
        exit();
    }
}

// --- PÁGINA PRINCIPAL (HOME) ---
// Cargamos los datos dinámicos necesarios para las secciones del home
require_once 'src/modelo/Artista.php';
require_once 'src/modelo/Cancion.php';
$artistaModel     = new Artista($connTemp);
$cancionModelHome = new Cancion($connTemp);

// Artistas registrados para la sección "Artistas cerca de ti"
$artistasHome = $artistaModel->obtenerTodos(6);

// Canciones más recientes para la sección "Nuevos lanzamientos"
$cancionesRecientes = $cancionModelHome->obtenerRecientes(4);

require('src/vista/includes/header.php');
?>

<!-- ===== CURANDO LA ESCENA LOCAL ===== -->
<!-- Las tres tarjetas son enlaces que redirigen a la sección de explorar -->
<main class="main-header">
    <h2>CURANDO LA <span>ESCENA LOCAL</span></h2>
    <div class="apartados-main">

        <!-- Portada principal → redirige a Explorar -->
        <a href="<?php echo BASE_URL; ?>/index.php?action=explorar" class="portada-principal" style="text-decoration:none;">
            <p><span>Radar Urbano</span><br>Descubre las canciones que están redefiniendo las calles esta semana</p>
        </a>

        <div class="main-hijos">
            <!-- Tarjeta Underground RAP → redirige a la sección de género RAP -->
            <a href="<?php echo BASE_URL; ?>/index.php?action=verGenero&genero=RAP" class="tarjeta-lateral" style="text-decoration:none;">
                <p>Underground RAP</p>
            </a>
            <!-- Tarjeta Artistas emergentes → redirige a Explorar -->
            <a href="<?php echo BASE_URL; ?>/index.php?action=explorar" class="tarjeta-lateral" style="text-decoration:none;">
                <p>Artistas emergentes</p>
            </a>
        </div>
    </div>
</main>

<!-- ===== ARTISTAS CERCA DE TI ===== -->
<!-- Muestra los artistas reales registrados en la plataforma -->
<div class="artistas-cerca">
    <div style="width:100%;">
        <div class="artistas-cerca-cabecera">
            <h2>ARTISTAS <span>EN LA PLATAFORMA</span></h2>
            <a href="<?php echo BASE_URL; ?>/index.php?action=explorar" class="artistas-ver-todos">VER TODOS</a>
        </div>

        <?php if (empty($artistasHome)): ?>
            <!-- Mensaje cuando todavía no hay artistas registrados -->
            <p style="color:#666; margin-top:1rem; font-size:0.9rem;">Aún no hay artistas en la plataforma.</p>
        <?php else: ?>
            <!-- Scroll horizontal de tarjetas de artista -->
            <div class="artistas-scroll">
                <?php foreach ($artistasHome as $artista): ?>
                    <!-- Cada tarjeta enlaza al perfil público del artista -->
                    <a href="<?php echo BASE_URL; ?>/index.php?action=verArtista&id=<?php echo (int)$artista['id_artista']; ?>"
                       class="artista-card">
                        <?php if (!empty($artista['foto_perfil']) && $artista['foto_perfil'] !== 'assets/img/default-profile.png'): ?>
                            <img src="<?php echo BASE_URL; ?>/<?php echo htmlspecialchars(ltrim($artista['foto_perfil'], '/')); ?>"
                                 alt="<?php echo htmlspecialchars($artista['nombre_artistico']); ?>"
                                 loading="lazy">
                                 //loading=>lazy: la imagen no se carga hasta que el usuario la ve en la pantalla
                        <?php else: ?>
                            <!-- Avatar genérico cuando el artista no ha subido foto -->
                            <div class="artista-card-avatar">
                                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="#555" viewBox="0 0 16 16">
                                    <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0"/>
                                    <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1"/>
                                </svg>
                            </div>
                        <?php endif; ?>
                        <p class="artista-card-nombre"><?php echo htmlspecialchars($artista['nombre_artistico']); ?></p>
                        <?php if (!empty($artista['localidad']) && $artista['localidad'] !== 'Sin especificar'): ?>
                            <p class="artista-card-localidad"><?php echo htmlspecialchars($artista['localidad']); ?></p>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ===== NUEVOS LANZAMIENTOS ===== -->
<!-- Muestra las canciones subidas más recientemente -->
<div class="nuevos-lanzamientos">
    <h2>NUEVOS <span>LANZAMIENTOS</span></h2>

    <?php if (empty($cancionesRecientes)): ?>
        <p style="color:#666; margin-top:1rem; font-size:0.9rem;">Aún no hay lanzamientos disponibles.</p>
    <?php else: ?>
        <div class="lanzamientos-lista">
            <?php foreach ($cancionesRecientes as $c): ?>
                <!-- Cada lanzamiento enlaza al género de la canción -->
                <a href="<?php echo BASE_URL; ?>/index.php?action=verGenero&genero=<?php echo urlencode($c['genero']); ?>"
                   class="lanzamiento-item">
                    <!-- Portada del álbum -->
                    <div class="lanzamiento-portada">
                        <?php if (!empty($c['portada_ruta'])): ?>
                            <img src="<?php echo BASE_URL; ?>/<?php echo htmlspecialchars(ltrim($c['portada_ruta'], '/')); ?>"
                                 alt="Portada" loading="lazy">
                        <?php else: ?>
                            <div class="lanzamiento-sin-portada">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="#555" viewBox="0 0 16 16">
                                    <path d="M9 13c0 1.105-1.12 2-2.5 2S4 14.105 4 13s1.12-2 2.5-2 2.5.895 2.5 2"/>
                                    <path fill-rule="evenodd" d="M9 3v10H8V3z"/>
                                    <path d="M8 2.82a1 1 0 0 1 .804-.98l3-.6A1 1 0 0 1 13 2.22V4L8 5z"/>
                                </svg>
                            </div>
                        <?php endif; ?>
                    </div>
                    <!-- Información del lanzamiento -->
                    <div class="lanzamiento-info">
                        <p class="lanzamiento-titulo"><?php echo htmlspecialchars($c['titulo']); ?></p>
                        <p class="lanzamiento-meta"><?php echo htmlspecialchars($c['nombre_artistico']); ?> &mdash; <sup><?php echo htmlspecialchars($c['genero']); ?></sup></p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- ===== GÉNEROS POPULARES ===== -->
<!-- Solo se muestran géneros que tienen imagen en explorar. Cada botón enlaza a su sección. -->
<div class="generos-populares">
    <h2>GÉNEROS <span>POPULARES</span></h2>
    <a href="<?php echo BASE_URL; ?>/index.php?action=verGenero&genero=TRAP" class="genero-btn"><b>TRAP</b></a>
    <a href="<?php echo BASE_URL; ?>/index.php?action=verGenero&genero=RAP" class="genero-btn"><b>RAP</b></a>
    <a href="<?php echo BASE_URL; ?>/index.php?action=verGenero&genero=DRILL" class="genero-btn"><b>DRILL</b></a>
    <a href="<?php echo BASE_URL; ?>/index.php?action=verGenero&genero=TECHNO" class="genero-btn"><b>TECHNO</b></a>
    <a href="<?php echo BASE_URL; ?>/index.php?action=verGenero&genero=REGUETON" class="genero-btn"><b>REGUETÓN</b></a>
</div>

<?php require('src/vista/includes/footer.php'); ?>
