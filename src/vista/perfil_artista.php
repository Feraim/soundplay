<?php
// Vista del perfil público de un artista.
// Recibe del controlador:
//   $artista             (array)  - datos del artista (nombre, bio, foto, localidad)
//   $albumesConCanciones (array)  - álbumes con sus canciones anidadas
require 'src/vista/includes/header.php';
?>

<main class="contenido-pagina perfil-main">

    <!-- ===== HERO DEL ARTISTA ===== -->
    <!-- Sección superior con foto de fondo, nombre y datos del artista -->
    <section class="perfil-hero" style="<?php
        // Si el artista tiene foto de perfil válida, se usa como fondo del hero
        if (!empty($artista['foto_perfil']) && $artista['foto_perfil'] !== 'assets/img/default-profile.png') {
            echo 'background-image: linear-gradient(to bottom, rgba(16,16,16,0.4) 0%, rgba(16,16,16,0.95) 100%), url("' . BASE_URL . '/' . htmlspecialchars(ltrim($artista['foto_perfil'], '/')) . '");';
        }
    ?>">
        <div class="perfil-hero-contenido">
            <!-- Foto de perfil circular -->
            <div class="perfil-avatar">
                <?php if (!empty($artista['foto_perfil']) && $artista['foto_perfil'] !== 'assets/img/default-profile.png'): ?>
                    <img src="<?php echo BASE_URL; ?>/<?php echo htmlspecialchars(ltrim($artista['foto_perfil'], '/')); ?>"
                         alt="<?php echo htmlspecialchars($artista['nombre_artistico']); ?>">
                <?php else: ?>
                    <!-- Avatar genérico cuando no hay foto de perfil -->
                    <div class="perfil-avatar-placeholder">
                        <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" fill="#555" viewBox="0 0 16 16">
                            <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0"/>
                            <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1"/>
                        </svg>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Datos del artista -->
            <div class="perfil-hero-info">
                <span class="perfil-tipo">Artista</span>
                <h1 class="perfil-nombre"><?php echo htmlspecialchars($artista['nombre_artistico']); ?></h1>

                <?php if (!empty($artista['localidad']) && $artista['localidad'] !== 'Sin especificar'): ?>
                    <p class="perfil-localidad">
                        <!-- Icono de ubicación -->
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10m0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6"/>
                        </svg>
                        <?php echo htmlspecialchars($artista['localidad']); ?>
                    </p>
                <?php endif; ?>

                <!-- Estadísticas básicas del artista -->
                <div class="perfil-stats">
                    <div class="perfil-stat">
                        <span class="perfil-stat-num"><?php echo count($albumesConCanciones); ?></span>
                        <span class="perfil-stat-label">Álbum<?php echo count($albumesConCanciones) !== 1 ? 'es' : ''; ?></span>
                    </div>
                    <div class="perfil-stat">
                        <?php
                        // Calcular el total de canciones sumando las de todos los álbumes
                        $totalCanciones = 0;
                        foreach ($albumesConCanciones as $a) {
                            $totalCanciones += count($a['canciones']);
                        }
                        ?>
                        <span class="perfil-stat-num"><?php echo $totalCanciones; ?></span>
                        <span class="perfil-stat-label">Canción<?php echo $totalCanciones !== 1 ? 'es' : ''; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== BIOGRAFÍA ===== -->
    <?php
    // Mostrar bio solo si el artista la ha rellenado (no es el texto provisional)
    $bioValida = !empty($artista['bio_extended'])
        && $artista['bio_extended'] !== 'Sin biografía.'
        && $artista['bio_extended'] !== 'Sin biografia.';
    ?>
    <?php if ($bioValida): ?>
        <section class="perfil-bio">
            <h2 class="perfil-seccion-titulo">Sobre el artista</h2>
            <p class="perfil-bio-texto"><?php echo nl2br(htmlspecialchars($artista['bio_extended'])); ?></p>
        </section>
    <?php endif; ?>

    <!-- ===== DISCOGRAFÍA ===== -->
    <section class="perfil-discografia">
        <h2 class="perfil-seccion-titulo">Discografía</h2>

        <?php if (empty($albumesConCanciones)): ?>
            <!-- El artista todavía no ha subido ningún álbum -->
            <div class="biblioteca-vacia-contenido" style="margin-top:1rem;">
                <p style="color:#666;">Este artista aún no ha subido música.</p>
            </div>

        <?php else: ?>
            <!-- Grid de álbumes del artista: portada como botón que despliega las canciones -->
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
                            <p class="album-card-artista"><?php echo htmlspecialchars($artista['nombre_artistico']); ?></p>
                            <p class="album-card-tracks">
                                <?php echo count($album['canciones']); ?>
                                canción<?php echo count($album['canciones']) !== 1 ? 'es' : ''; ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Paneles de tracklist (se despliegan al hacer clic en la tarjeta del álbum) -->
            <?php foreach ($albumesConCanciones as $album): ?>
                <section class="album-tracklist" id="tracklist-<?php echo $album['id_album']; ?>">
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
                            <p class="album-tracklist-artista"><?php echo htmlspecialchars($artista['nombre_artistico']); ?></p>
                        </div>
                        <!-- Botón para cerrar el tracklist -->
                        <button class="album-tracklist-cerrar"
                                data-album-id="<?php echo $album['id_album']; ?>"
                                aria-label="Cerrar">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Lista de canciones del álbum; cada fila tiene atributos data-* para el reproductor -->
                    <div class="track-list">
                        <?php foreach ($album['canciones'] as $i => $c): ?>
                            <!-- Fila de canción: el JS lee data-src, data-titulo, data-artista y data-portada -->
                            <div class="track-row"
                                 data-src="<?php echo BASE_URL; ?>/<?php echo htmlspecialchars(ltrim($c['archivo_ruta'] ?? '', '/')); ?>"
                                 data-titulo="<?php echo htmlspecialchars($c['titulo']); ?>"
                                 data-artista="<?php echo htmlspecialchars($artista['nombre_artistico']); ?>"
                                 data-portada="<?php echo !empty($album['portada_ruta'])
                                     ? BASE_URL . '/' . htmlspecialchars(ltrim($album['portada_ruta'], '/'))
                                     : ''; ?>">
                                <!-- Número de pista (empieza en 1) -->
                                <span class="track-num"><?php echo $i + 1; ?></span>
                                <!-- Botón play/pause: el JS alterna los iconos según el estado -->
                                <button class="track-play-btn" aria-label="Reproducir">
                                    <!-- Icono play: visible cuando la canción no está activa -->
                                    <svg class="icon-play" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M10.804 8 5 4.633v6.734zm.792-.696a.802.802 0 0 1 0 1.392l-6.363 3.692C4.713 12.69 4 12.345 4 11.692V4.308c0-.653.713-.998 1.233-.696z"/>
                                    </svg>
                                    <!-- Icono pausa: visible mientras la canción está sonando -->
                                    <svg class="icon-pause" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="display:none">
                                        <path d="M5.5 3.5A1.5 1.5 0 0 1 7 5v6a1.5 1.5 0 0 1-3 0V5a1.5 1.5 0 0 1 1.5-1.5m5 0A1.5 1.5 0 0 1 12 5v6a1.5 1.5 0 0 1-3 0V5a1.5 1.5 0 0 1 1.5-1.5"/>
                                    </svg>
                                </button>
                                <!-- Bloque central: título, artista y créditos técnicos -->
                                <div class="track-info">
                                    <!-- Título de la canción -->
                                    <span class="track-titulo"><?php echo htmlspecialchars($c['titulo']); ?></span>
                                    <!-- Nombre artístico del artista -->
                                    <span class="track-artista"><?php echo htmlspecialchars($artista['nombre_artistico']); ?></span>

                                    <?php
                                    // Buscar los créditos de esta canción en el mapa precargado por el controlador.
                                    // El operador ?? devuelve array vacío si no hay créditos para este id_cancion.
                                    $creditosCancion = $creditosMap[$c['id_cancion']] ?? [];
                                    ?>
                                    <?php if (!empty($creditosCancion)): ?>
                                        <!-- Bloque de créditos técnicos (solo si la canción tiene alguno) -->
                                        <div class="track-creditos">
                                            <?php foreach ($creditosCancion as $cr): ?>
                                                <!-- Etiqueta pill con nombre y rol del profesional -->
                                                <span class="track-credito-badge">
                                                    <!-- Nombre del profesional -->
                                                    <?php echo htmlspecialchars($cr['nombre_profesional']); ?>
                                                    <!-- Rol: convertir guiones bajos a espacios y capitalizar -->
                                                    <span class="track-credito-rol"><?php echo ucfirst(str_replace('_', ' ', $cr['rol'])); ?></span>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <!-- Duración: ocultar horas si son '00' para mayor limpieza visual -->
                                <span class="track-duracion"><?php
                                    // Separar el campo time de MySQL en sus tres partes
                                    $dur   = $c['duracion'] ?? '00:00:00';
                                    $parts = explode(':', $dur);
                                    // Si las horas son cero, mostrar solo minutos:segundos
                                    echo ($parts[0] === '00') ? $parts[1] . ':' . $parts[2] : $dur;
                                ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>

        <?php endif; ?>
    </section>

    <!-- Botón para volver a explorar -->
    <div style="margin-top:2rem; padding-bottom:8rem;">
        <a href="javascript:history.back()" class="btn-primary"
           style="display:inline-block; text-decoration:none; padding:10px 22px; font-size:0.9rem;">
            ← VOLVER
        </a>
    </div>

</main>

<!-- Reproductor Spotify (reutilizado desde la página de género) -->
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


<?php require 'src/vista/includes/footer.php'; ?>
