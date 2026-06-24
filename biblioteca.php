<?php
// Página de biblioteca personal del artista.
// Muestra álbumes como tarjetas clicables (igual que la página de género).
// Al hacer clic en un álbum se despliega la lista de canciones con el reproductor.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cargar conexión (define también BASE_URL de forma dinámica)
require_once __DIR__ . '/config/conexion.php';

$albumes   = [];
$canciones = [];

// Solo cargamos datos si el usuario es un artista con sesión activa
if (isset($_SESSION['usuario_id']) && ($_SESSION['usuario_rol'] ?? '') === 'artista') {
    require_once __DIR__ . '/src/modelo/Album.php';
    require_once __DIR__ . '/src/modelo/Cancion.php';

    $db           = new Database();
    $conn         = $db->conectar();
    $idArtista    = (int) $_SESSION['usuario_id'];
    $albumModel   = new Album($conn);
    $cancionModel = new Cancion($conn);

    $albumes   = $albumModel->obtenerAlbumesPorArtista($idArtista);
    $canciones = $cancionModel->obtenerCancionesConAlbumPorArtista($idArtista);

    // Agrupar canciones dentro de cada álbum (igual que el controlador de género)
    $albumesConCanciones = [];
    foreach ($albumes as $alb) {
        $albumesConCanciones[$alb['id_album']] = $alb;
        $albumesConCanciones[$alb['id_album']]['canciones'] = [];
    }
    foreach ($canciones as $c) {
        if (isset($albumesConCanciones[$c['id_album']])) {
            $albumesConCanciones[$c['id_album']]['canciones'][] = $c;
        }
    }
}

require __DIR__ . '/src/vista/includes/header.php';
?>

<main class="contenido-pagina">

    <?php if (!isset($_SESSION['usuario_id'])): ?>
        <!-- Usuario no autenticado: invitación a iniciar sesión -->
        <section class="biblioteca-vacia">
            <div class="biblioteca-vacia-contenido">
                <h2>Biblioteca</h2>
                <p>Inicia sesión para acceder a tu biblioteca personal.</p>
                <a class="btn-primary"
                   style="display:inline-block; text-decoration:none; margin-top:1rem;"
                   href="<?php echo BASE_URL; ?>/index.php?action=login">INICIAR SESIÓN</a>
            </div>
        </section>

    <?php else: ?>

        <!-- Cabecera de la biblioteca -->
        <section style="margin-top:5rem; margin-bottom:2rem;">
            <h2 style="font-size:2.4rem; margin:0 0 6px;">Biblioteca</h2>
            <p style="color:#b8b8b8; margin:0;">Gestión central de tu contenido en SoundPlay.</p>
        </section>

        <?php if (($_SESSION['usuario_rol'] ?? 'user') === 'artista'): ?>

            <?php if (empty($albumesConCanciones)): ?>
                <!-- Sin álbumes todavía -->
                <section class="biblioteca-vacia" style="min-height:35vh;">
                    <div class="biblioteca-vacia-contenido">
                        <h2>Sin álbumes aún</h2>
                        <p style="color:#b8b8b8; margin-bottom:1.5rem;">
                            Crea tu primer álbum desde el panel de artista y luego sube canciones.
                        </p>
                        <a class="btn-primary"
                           style="display:inline-block; text-decoration:none; padding:12px 24px;"
                           href="<?php echo BASE_URL; ?>/index.php?action=panel">IR AL PANEL</a>
                    </div>
                </section>

            <?php else: ?>

                <!-- ===== GRID DE ÁLBUMES ===== -->
                <!-- Misma estructura que la página de género: portada clicable → tracklist -->
                <section>
                    <h3 style="font-size:1rem; color:#888; text-transform:uppercase;
                                letter-spacing:0.1em; margin:0 0 1.25rem; font-weight:700;">
                        Mis Álbumes
                    </h3>
                    <div class="genero-albumes-grid">
                        <?php foreach ($albumesConCanciones as $album): ?>
                            <div class="album-card" data-album-id="<?php echo $album['id_album']; ?>">
                                <div class="album-card-portada">
                                    <?php if (!empty($album['portada_ruta'])): ?>
                                        <img src="<?php echo BASE_URL; ?>/<?php echo htmlspecialchars(ltrim($album['portada_ruta'], '/')); ?>"
                                             alt="<?php echo htmlspecialchars($album['titulo']); ?>">
                                    <?php else: ?>
                                        <div class="album-card-sin-portada">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="#555" viewBox="0 0 16 16">
                                                <path d="M9 13c0 1.105-1.12 2-2.5 2S4 14.105 4 13s1.12-2 2.5-2 2.5.895 2.5 2"/>
                                                <path fill-rule="evenodd" d="M9 3v10H8V3z"/>
                                                <path d="M8 2.82a1 1 0 0 1 .804-.98l3-.6A1 1 0 0 1 13 2.22V4L8 5z"/>
                                            </svg>
                                        </div>
                                    <?php endif; ?>
                                    <div class="album-card-overlay">
                                        <div class="album-card-play-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="#fff" viewBox="0 0 16 16">
                                                <path d="M5.25 3.065a.5.5 0 0 1 .5 0l8 4.5a.5.5 0 0 1 0 .87l-8 4.5a.5.5 0 0 1-.75-.435v-9a.5.5 0 0 1 .25-.435"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                                <div class="album-card-info">
                                    <p class="album-card-titulo"><?php echo htmlspecialchars($album['titulo']); ?></p>
                                    <p class="album-card-tracks">
                                        <?php echo count($album['canciones']); ?>
                                        canción<?php echo count($album['canciones']) !== 1 ? 'es' : ''; ?>
                                    </p>
                                    <?php if (!empty($album['fecha_publicacion'])): ?>
                                        <p class="album-card-artista" style="font-size:0.72rem; color:#555;">
                                            <?php echo htmlspecialchars(substr($album['fecha_publicacion'], 0, 4)); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <!-- ===== TRACKLISTS (ocultos, se abren al clicar el álbum) ===== -->
                <?php foreach ($albumesConCanciones as $album): ?>
                    <section class="album-tracklist" id="tracklist-<?php echo $album['id_album']; ?>">

                        <!-- Cabecera del panel con portada, título y botón cerrar -->
                        <div class="album-tracklist-header">
                            <div class="album-tracklist-cover">
                                <?php if (!empty($album['portada_ruta'])): ?>
                                    <img src="<?php echo BASE_URL; ?>/<?php echo htmlspecialchars(ltrim($album['portada_ruta'], '/')); ?>"
                                         alt="Portada">
                                <?php else: ?>
                                    <div class="album-card-sin-portada" style="width:64px;height:64px;border-radius:8px;"></div>
                                <?php endif; ?>
                            </div>
                            <div>
                                <h3 class="album-tracklist-titulo"><?php echo htmlspecialchars($album['titulo']); ?></h3>
                                <p class="album-tracklist-artista">
                                    <?php echo count($album['canciones']); ?> canción<?php echo count($album['canciones']) !== 1 ? 'es' : ''; ?>
                                    <?php if (!empty($album['fecha_publicacion'])): ?>
                                        · <?php echo htmlspecialchars(substr($album['fecha_publicacion'], 0, 4)); ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <button class="album-tracklist-cerrar"
                                    data-album-id="<?php echo $album['id_album']; ?>"
                                    aria-label="Cerrar">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Lista de canciones con duración y botón de reproducción -->
                        <div class="track-list">
                            <?php if (empty($album['canciones'])): ?>
                                <p style="padding:1rem 1.25rem; color:#555; font-size:0.9rem;">
                                    Este álbum no tiene canciones aún.
                                </p>
                            <?php else: ?>
                                <?php foreach ($album['canciones'] as $i => $c): ?>
                                    <div class="track-row"
                                         data-src="<?php echo BASE_URL; ?>/<?php echo htmlspecialchars(ltrim($c['archivo_ruta'] ?? '', '/')); ?>"
                                         data-titulo="<?php echo htmlspecialchars($c['titulo']); ?>"
                                         data-artista="<?php echo htmlspecialchars($_SESSION['usuario_email'] ?? ''); ?>"
                                         data-portada="<?php echo !empty($album['portada_ruta'])
                                             ? BASE_URL . '/' . htmlspecialchars(ltrim($album['portada_ruta'], '/'))
                                             : ''; ?>">

                                        <span class="track-num"><?php echo $i + 1; ?></span>

                                        <!-- Botón play/pause de la fila -->
                                        <button class="track-play-btn" aria-label="Reproducir">
                                            <svg class="icon-play" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                <path d="M10.804 8 5 4.633v6.734zm.792-.696a.802.802 0 0 1 0 1.392l-6.363 3.692C4.713 12.69 4 12.345 4 11.692V4.308c0-.653.713-.998 1.233-.696z"/>
                                            </svg>
                                            <svg class="icon-pause" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="display:none">
                                                <path d="M5.5 3.5A1.5 1.5 0 0 1 7 5v6a1.5 1.5 0 0 1-3 0V5a1.5 1.5 0 0 1 1.5-1.5m5 0A1.5 1.5 0 0 1 12 5v6a1.5 1.5 0 0 1-3 0V5a1.5 1.5 0 0 1 1.5-1.5"/>
                                            </svg>
                                        </button>

                                        <!-- Título y álbum de la canción -->
                                        <div class="track-info">
                                            <span class="track-titulo"><?php echo htmlspecialchars($c['titulo']); ?></span>
                                            <span class="track-artista"><?php echo htmlspecialchars($c['genero'] ?? ''); ?></span>
                                        </div>

                                        <!-- Duración formateada mm:ss -->
                                        <span class="track-duracion"><?php
                                            $dur   = $c['duracion'] ?? '00:00:00';
                                            $parts = explode(':', $dur);
                                            echo ($parts[0] === '00') ? $parts[1] . ':' . $parts[2] : $dur;
                                        ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </section>
                <?php endforeach; ?>

            <?php endif; ?>

        <?php else: ?>
            <!-- Vista del oyente: secciones pendientes de implementación -->
            <section class="biblioteca-panel">
                <h3>Canciones guardadas</h3>
                <p style="color:#b8b8b8;">Próximamente: canciones que guardes con Me Gusta.</p>
            </section>
            <section class="biblioteca-panel">
                <h3>Historial de reproducción</h3>
                <p style="color:#b8b8b8;">Próximamente: últimas canciones reproducidas.</p>
            </section>
        <?php endif; ?>

    <?php endif; ?>
</main>

<!-- Reproductor global estilo Spotify (mismo que en género y perfil) -->
<div class="sp-player" id="sp-player">
    <audio id="sp-audio"></audio>
    <div class="sp-player-left">
        <div class="sp-cover" id="sp-cover">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="#555" viewBox="0 0 16 16">
                <path d="M9 13c0 1.105-1.12 2-2.5 2S4 14.105 4 13s1.12-2 2.5-2 2.5.895 2.5 2"/>
                <path fill-rule="evenodd" d="M9 3v10H8V3z"/>
                <path d="M8 2.82a1 1 0 0 1 .804-.98l3-.6A1 1 0 0 1 13 2.22V4L8 5z"/>
            </svg>
        </div>
        <div class="sp-song-info">
            <span class="sp-song-titulo" id="sp-titulo">—</span>
            <span class="sp-song-artista" id="sp-artista">—</span>
        </div>
    </div>
    <div class="sp-player-center">
        <div class="sp-controls">
            <button class="sp-btn-prev" id="sp-prev" aria-label="Anterior">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M4 4a.5.5 0 0 1 1 0v3.248l6.267-3.636A.5.5 0 0 1 12 4v8a.5.5 0 0 1-.733.44L5 8.752V12a.5.5 0 0 1-1 0z"/>
                </svg>
            </button>
            <button class="sp-btn-play" id="sp-play-pause" aria-label="Play/Pause">
                <svg class="icon-play" xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M6.79 5.093A.5.5 0 0 0 6 5.5v5a.5.5 0 0 0 .79.407l3.5-2.5a.5.5 0 0 0 0-.814z"/>
                </svg>
                <svg class="icon-pause" xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" viewBox="0 0 16 16" style="display:none">
                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M6.25 5C5.56 5 5 5.56 5 6.25v3.5a1.25 1.25 0 1 0 2.5 0v-3.5C7.5 5.56 6.94 5 6.25 5m3.5 0c-.69 0-1.25.56-1.25 1.25v3.5a1.25 1.25 0 1 0 2.5 0v-3.5C11 5.56 10.44 5 9.75 5"/>
                </svg>
            </button>
            <button class="sp-btn-next" id="sp-next" aria-label="Siguiente">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M12.5 4a.5.5 0 0 0-1 0v3.248L5.233 3.612A.5.5 0 0 0 4.5 4v8a.5.5 0 0 0 .733.44L11 8.752V12a.5.5 0 0 0 1 0z"/>
                </svg>
            </button>
        </div>
        <div class="sp-progress-wrap">
            <span class="sp-time" id="sp-current">0:00</span>
            <div class="sp-progress-bar" id="sp-progress-bar">
                <div class="sp-progress-fill" id="sp-progress-fill"></div>
                <div class="sp-progress-thumb" id="sp-progress-thumb"></div>
            </div>
            <span class="sp-time" id="sp-duration">0:00</span>
        </div>
    </div>
    <div class="sp-player-right">
        <div class="sp-volume-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#b3b3b3" viewBox="0 0 16 16">
                <path d="M9 4a.5.5 0 0 0-.812-.39L5.825 5.5H3.5A.5.5 0 0 0 3 6v4a.5.5 0 0 0 .5.5h2.325l2.363 1.89A.5.5 0 0 0 9 12zm3.025 4a4.5 4.5 0 0 1-1.318 3.182L10 10.475A3.5 3.5 0 0 0 10 5.525l.707-.707A4.5 4.5 0 0 1 12.025 8"/>
            </svg>
            <input type="range" class="sp-volume" id="sp-volume" min="0" max="1" step="0.05" value="1">
        </div>
    </div>
</div>

<?php require __DIR__ . '/src/vista/includes/footer.php'; ?>
