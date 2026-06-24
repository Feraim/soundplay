<?php
// Iniciar la sesión PHP para poder leer y escribir variables de sesión ($_SESSION)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cargar la configuración SIEMPRE, antes de cualquier otra cosa.
// Esto define la constante BASE_URL de forma dinámica según la ruta real del servidor,
// evitando rutas hardcodeadas que se rompen al cambiar de entorno (local, producción, etc.)
require_once __DIR__ . '/config/conexion.php';

// Si el usuario NO ha iniciado sesión, redirigirlo al login directamente.
// No tiene sentido mostrar la página de ajustes a un usuario no identificado.
if (!isset($_SESSION['usuario_id'])) {
    // header() envía una cabecera HTTP de redirección; exit() detiene el resto del script
    header('Location: ' . BASE_URL . '/index.php?action=login');
    exit();
}

// Leer el mensaje de éxito de la sesión (si existe) y borrarlo para que no se repita
$mensajeExito = null;
if (isset($_SESSION['mensaje_exito'])) {
    $mensajeExito = $_SESSION['mensaje_exito']; // Guardar el mensaje en variable local
    unset($_SESSION['mensaje_exito']);           // Eliminar de la sesión para no mostrarlo de nuevo
}

// Leer el mensaje de error de la sesión (si existe) y borrarlo igual que el de éxito
$mensajeError = null;
if (isset($_SESSION['mensaje_error'])) {
    $mensajeError = $_SESSION['mensaje_error']; // Guardar el mensaje en variable local
    unset($_SESSION['mensaje_error']);           // Eliminar de la sesión
}

// Variables para almacenar los datos del usuario y del artista (si aplica)
$filaUsuario            = null; // Fila completa del usuario en la tabla 'usuarios'
$perfilArtista          = null; // Fila del artista en la tabla 'artistas' (solo si el rol es artista)
$recomendacionesActivas = 1;    // Valor por defecto: recomendaciones activas

// Cargar los modelos necesarios para obtener los datos del usuario conectado
require_once __DIR__ . '/src/modelo/Usuario.php';
require_once __DIR__ . '/src/modelo/Artista.php';

// Abrir conexión a la base de datos usando el Singleton de Database
$db   = new Database();
$conn = $db->conectar();

// Instanciar el modelo de usuario con la conexión PDO
$usuarioModel = new Usuario($conn);

// Asegurar que la columna 'recomendaciones_activas' existe en la BD (mecanismo autocurativo)
$usuarioModel->asegurarColumnaRecomendaciones();

// Obtener todos los datos del usuario conectado usando su ID guardado en la sesión
$filaUsuario = $usuarioModel->obtenerPorId((int) $_SESSION['usuario_id']);

// Leer la preferencia de recomendaciones; si la columna no devuelve valor, usar 1 (activo)
if ($filaUsuario) {
    $recomendacionesActivas = (int) ($filaUsuario['recomendaciones_activas'] ?? 1);
}

// Si el usuario es artista, cargar también su perfil de artista para mostrar en el formulario
if (($_SESSION['usuario_rol'] ?? '') === 'artista') {
    $artistaModel  = new Artista($conn);
    // Obtener el perfil del artista usando el mismo ID que el usuario (FK entre tablas)
    $perfilArtista = $artistaModel->obtenerPorId((int) $_SESSION['usuario_id']);
}

// Incluir la cabecera HTML común (menú, logo, etc.)
require __DIR__ . '/src/vista/includes/header.php';
?>

<main class="contenido-pagina">
    <!-- Título de la sección de ajustes -->
    <section style="margin-top: 5rem; margin-bottom: 1.5rem;">
        <h2 style="font-size: 2.4rem; margin: 0;">Ajustes de cuenta</h2>
        <p style="color:#b8b8b8;">Configura tu experiencia dentro de SoundPlay.</p>
    </section>

    <!-- Mostrar mensaje de error si el controlador guardó uno en la sesión -->
    <?php if ($mensajeError): ?>
        <div class="alert-error"><?php echo htmlspecialchars($mensajeError); ?></div>
    <?php endif; ?>

    <!-- Mostrar mensaje de éxito si el controlador guardó uno en la sesión -->
    <?php if ($mensajeExito): ?>
        <div class="alert-success"><?php echo htmlspecialchars($mensajeExito); ?></div>
    <?php endif; ?>

    <!-- ===== PREFERENCIAS ===== -->
    <!-- Formulario para activar o desactivar las recomendaciones musicales -->
    <section class="biblioteca-panel">
        <h3>Preferencias</h3>
        <!-- El action apunta al controlador a través del enrutador de index.php -->
        <form class="auth-form" action="<?php echo BASE_URL; ?>/index.php?action=guardarPreferenciasAjustes" method="POST" style="gap: 1rem;">
            <!-- Token CSRF para proteger el formulario -->
            <?php echo csrf_campo(); ?>
            <div class="form-group checkbox-group">
                <!-- El checkbox envía el valor '1' cuando está marcado; nada cuando no -->
                <input type="checkbox" id="recomendaciones" name="recomendaciones" value="1"
                       <?php echo $recomendacionesActivas === 1 ? 'checked' : ''; ?>>
                <label for="recomendaciones">Activar recomendaciones segun mis gustos</label>
            </div>
            <!-- Botón de guardar: width fit-content para que no ocupe todo el ancho -->
            <button type="submit" class="btn-primary" style="width: fit-content;">GUARDAR PREFERENCIAS</button>
        </form>
    </section>

    <!-- ===== ZONA DE RIESGO ===== -->
    <!-- Formulario para eliminar la cuenta de forma permanente -->
    <section class="biblioteca-panel">
        <h3>Zona de riesgo</h3>
        <!-- Advertencia clara sobre la irreversibilidad de la acción -->
        <p style="color:#d0d0d0;">Esta accion eliminara tu cuenta de forma permanente. Escribe <strong>ELIMINAR</strong> para confirmar.</p>
        <form class="auth-form" action="<?php echo BASE_URL; ?>/index.php?action=eliminarCuenta" method="POST" style="gap: 1rem;">
            <!-- Token CSRF obligatorio también en acciones destructivas -->
            <?php echo csrf_campo(); ?>
            <div class="form-group">
                <label for="confirmar_eliminar">Confirmacion</label>
                <!-- autocomplete="off" para que el navegador no rellene este campo automáticamente -->
                <input type="text" id="confirmar_eliminar" name="confirmar_eliminar" placeholder="ELIMINAR" autocomplete="off">
            </div>
            <!-- Botón rojo para reforzar visualmente que es una acción peligrosa -->
            <button type="submit" class="btn-primary" style="width: fit-content; background-color:#7d1010;">ELIMINAR CUENTA</button>
        </form>
    </section>

    <!-- ===== PERFIL DE ARTISTA ===== -->
    <!-- Solo se muestra si el usuario conectado tiene el rol 'artista' -->
    <?php if (($_SESSION['usuario_rol'] ?? 'user') === 'artista'): ?>
        <section class="biblioteca-panel">
            <h3>Perfil de artista</h3>

            <!-- Mostrar la foto actual si el artista tiene una subida -->
            <?php if ($perfilArtista && !empty($perfilArtista['foto_perfil'])): ?>
                <p style="color:#b8b8b8;">Foto actual</p>
                <!-- Construir la URL de la foto usando BASE_URL para que funcione en cualquier entorno -->
                <img src="<?php echo BASE_URL . '/' . htmlspecialchars(ltrim($perfilArtista['foto_perfil'], '/')); ?>"
                     alt="Foto de perfil"
                     style="max-width:120px; border-radius:8px; border:1px solid #333; margin-bottom:1rem;">
            <?php endif; ?>

            <!-- Formulario de edición del perfil de artista -->
            <!-- enctype="multipart/form-data" es imprescindible para que PHP reciba el archivo de foto -->
            <form class="auth-form"
                  action="<?php echo BASE_URL; ?>/index.php?action=guardarPerfilArtistaAjustes"
                  method="POST"
                  enctype="multipart/form-data"
                  style="gap: 1rem;">
                <!-- Token CSRF para proteger la subida de archivos -->
                <?php echo csrf_campo(); ?>

                <div class="form-group">
                    <label for="nombre_artistico">Nombre artistico</label>
                    <!-- El value precarga el nombre actual para que el artista no tenga que reescribirlo -->
                    <input type="text" id="nombre_artistico" name="nombre_artistico" required
                           value="<?php echo htmlspecialchars($perfilArtista['nombre_artistico'] ?? ''); ?>"
                           placeholder="Tu nombre artistico">
                </div>

                <div class="form-group">
                    <label for="bio_extended">Bio extendida</label>
                    <!-- Textarea para la biografía; el contenido actual se precarga como texto del elemento -->
                    <textarea id="bio_extended" name="bio_extended" rows="4"
                              placeholder="Presentate en pocas palabras"><?php echo htmlspecialchars($perfilArtista['bio_extended'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="localidad">Localidad</label>
                    <!-- Campo de ciudad o región; también se precarga con el valor guardado -->
                    <input type="text" id="localidad" name="localidad" required
                           value="<?php echo htmlspecialchars($perfilArtista['localidad'] ?? ''); ?>"
                           placeholder="Ciudad o region">
                </div>

                <div class="form-group">
                    <label for="foto_perfil">Foto de perfil (opcional)</label>
                    <!-- Campo de archivo para cambiar la foto; accept filtra en el explorador de archivos -->
                    <!-- El servidor valida también el MIME real con finfo para mayor seguridad -->
                    <input type="file" id="foto_perfil" name="foto_perfil" accept="image/*">
                </div>

                <!-- Botón de guardar cambios del perfil -->
                <button type="submit" class="btn-primary" style="width: fit-content;">GUARDAR PERFIL</button>
            </form>
        </section>
    <?php endif; ?>
</main>

<?php
// Incluir el pie de página HTML común (barra de navegación inferior, script JS, etc.)
require __DIR__ . '/src/vista/includes/footer.php';
?>
