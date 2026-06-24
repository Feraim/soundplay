<?php
// Vista de la página de género musical.
// Recibe del controlador: $genero (string) y $albumes (array agrupado por álbum).
// Si $albumes está vacío, muestra un estado diferente según el rol del usuario.
require 'src/vista/includes/header.php';
?>

<main class="contenido-pagina">

    <!-- Cabecera: título del género y botón para volver a explorar -->
    <section class="genero-header">
        <div>
            <h2 class="genero-titulo">Género: <span><?php echo htmlspecialchars($genero); ?></span></h2>
            <p class="genero-subtitulo">Explora los últimos lanzamientos de la calle en esta categoría.</p>
        </div>
        <a href="index.php?action=explorar" class="btn-primary btn-volver">VOLVER A EXPLORAR</a>
    </section>

    <?php if (empty($albumes)): ?>
        <!-- Estado vacío: no hay canciones en este género -->
        <section class="biblioteca-vacia" style="min-height: 40vh;">
            <div class="biblioteca-vacia-contenido">
                <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'artista'): ?>
                    <!-- Si el usuario es artista, se le invita a subir una canción -->
                    <h2>Aún no hay música aquí</h2>
                    <p style="margin-bottom: 1.5rem; color:#b8b8b8;">
                        Sé el primero en subir una canción del género
                        <strong><?php echo htmlspecialchars($genero); ?></strong>.
                    </p>
                    <a class="btn-primary"
                       style="display:inline-block; text-decoration:none; padding: 12px 24px;"
                       href="index.php?action=panel">SUBIR CANCIÓN</a>
                <?php else: ?>
                    <!-- Si el usuario es oyente o no ha iniciado sesión, solo se muestra el mensaje -->
                    <h2 style="color:#b8b8b8;">Sin música aún</h2>
                    <p style="color:#666;">
                        No hay canciones disponibles en el género
                        <strong style="color:#b8b8b8;"><?php echo htmlspecialchars($genero); ?></strong>
                        por el momento.
                    </p>
                <?php endif; ?>
            </div>
        </section>

    <?php else: ?>

        <!-- Grid de tarjetas de álbum -->
        <!-- Cada tarjeta muestra la portada del álbum; al hacer clic se despliega la lista de canciones -->
        <section class="genero-albumes-grid">
            <?php foreach ($albumes as $album): ?>
                <!-- data-album-id se usa en JavaScript para identificar qué tracklist abrir -->
                <div class="album-card" data-album-id="<?php echo $album['id_album']; ?>">

                    <!-- Contenedor de la portada con overlay de play al hacer hover -->
                    <div class="album-card-portada">
                        <?php if (!empty($album['portada_ruta'])): ?>
                            <img src="<?php echo BASE_URL; ?>/<?php echo htmlspecialchars(ltrim($album['portada_ruta'], '/')); ?>"
                                 alt="Portada <?php echo htmlspecialchars($album['titulo_album']); ?>">
                        <?php else: ?>
                            <!-- Placeholder cuando el álbum no tiene portada subida -->
                            <div class="album-card-sin-portada">
                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="#555" viewBox="0 0 16 16">
                                    <path d="M9 13c0 1.105-1.12 2-2.5 2S4 14.105 4 13s1.12-2 2.5-2 2.5.895 2.5 2"/>
                                    <path fill-rule="evenodd" d="M9 3v10H8V3z"/>
                                    <path d="M8 2.82a1 1 0 0 1 .804-.98l3-.6A1 1 0 0 1 13 2.22V4L8 5z"/>
                                </svg>
                            </div>
                        <?php endif; ?>
                        <!-- Capa oscura con icono de play que aparece al hacer hover -->
                        <div class="album-card-overlay">
                            <div class="album-card-play-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="#fff" viewBox="0 0 16 16">
                                    <path d="M5.25 3.065a.5.5 0 0 1 .5 0l8 4.5a.5.5 0 0 1 0 .87l-8 4.5a.5.5 0 0 1-.75-.435v-9a.5.5 0 0 1 .25-.435"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Información textual bajo la portada: título, artista y número de canciones -->
                    <div class="album-card-info">
                        <p class="album-card-titulo"><?php echo htmlspecialchars($album['titulo_album']); ?></p>
                        <p class="album-card-artista"><?php echo htmlspecialchars($album['nombre_artistico']); ?></p>
                        <p class="album-card-tracks">
                            <?php echo count($album['canciones']); ?>
                            canción<?php echo count($album['canciones']) !== 1 ? 'es' : ''; ?>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>

        <!-- Paneles de tracklist (lista de canciones) por álbum -->
        <!-- Cada panel está oculto por defecto; JavaScript lo muestra al hacer clic en la tarjeta -->
        <?php foreach ($albumes as $album): ?>
            <section class="album-tracklist" id="tracklist-<?php echo $album['id_album']; ?>">

                <!-- Cabecera del panel: portada pequeña, título del álbum y botón de cerrar -->
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
                        <h3 class="album-tracklist-titulo"><?php echo htmlspecialchars($album['titulo_album']); ?></h3>
                        <p class="album-tracklist-artista"><?php echo htmlspecialchars($album['nombre_artistico']); ?></p>
                    </div>
                    <!-- Botón X para cerrar el panel de canciones -->
                    <button class="album-tracklist-cerrar"
                            data-album-id="<?php echo $album['id_album']; ?>"
                            aria-label="Cerrar">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/>
                        </svg>
                    </button>
                </div>

                <!-- Lista de canciones del álbum -->
                <!-- Cada fila guarda en data-* los datos que necesita el reproductor JavaScript -->
                <div class="track-list">
                    <?php foreach ($album['canciones'] as $i => $c): ?>
                        <!-- Fila de canción; el JS usa los atributos data-* para cargarla en el reproductor -->
                        <div class="track-row"
                             data-src="<?php echo BASE_URL; ?>/<?php echo htmlspecialchars(ltrim($c['archivo_ruta'] ?? '', '/')); ?>"
                             data-titulo="<?php echo htmlspecialchars($c['titulo']); ?>"
                             data-artista="<?php echo htmlspecialchars($album['nombre_artistico']); ?>"
                             data-portada="<?php echo !empty($album['portada_ruta'])
                                 ? BASE_URL . '/' . htmlspecialchars(ltrim($album['portada_ruta'], '/'))
                                 : ''; ?>">

                            <!-- Número de pista dentro del álbum (empieza en 1) -->
                            <span class="track-num"><?php echo $i + 1; ?></span>

                            <!-- Botón play/pause individual de esta fila -->
                            <button class="track-play-btn" aria-label="Reproducir">
                                <!-- Icono de play: visible cuando la canción no está activa -->
                                <svg class="icon-play" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M10.804 8 5 4.633v6.734zm.792-.696a.802.802 0 0 1 0 1.392l-6.363 3.692C4.713 12.69 4 12.345 4 11.692V4.308c0-.653.713-.998 1.233-.696z"/>
                                </svg>
                                <!-- Icono de pausa: visible mientras la canción está sonando -->
                                <svg class="icon-pause" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="display:none">
                                    <path d="M5.5 3.5A1.5 1.5 0 0 1 7 5v6a1.5 1.5 0 0 1-3 0V5a1.5 1.5 0 0 1 1.5-1.5m5 0A1.5 1.5 0 0 1 12 5v6a1.5 1.5 0 0 1-3 0V5a1.5 1.5 0 0 1 1.5-1.5"/>
                                </svg>
                            </button>

                            <!-- Bloque central: título de la canción, nombre del artista y créditos técnicos -->
                            <div class="track-info">
                                <!-- Título de la canción -->
                                <span class="track-titulo"><?php echo htmlspecialchars($c['titulo']); ?></span>
                                <!-- Nombre artístico del autor del álbum -->
                                <span class="track-artista"><?php echo htmlspecialchars($album['nombre_artistico']); ?></span>

                                <?php
                                // Buscar en el mapa precargado los créditos de esta canción concreta.
                                // Si no tiene créditos, $creditosCancion será un array vacío y no se muestra nada.
                                $creditosCancion = $creditosMap[$c['id_cancion']] ?? [];
                                ?>
                                <?php if (!empty($creditosCancion)): ?>
                                    <!-- Contenedor de etiquetas de crédito (solo aparece si hay créditos) -->
                                    <div class="track-creditos">
                                        <?php foreach ($creditosCancion as $cr): ?>
                                            <!-- Etiqueta pill con el nombre del profesional y su rol -->
                                            <span class="track-credito-badge">
                                                <!-- Nombre del profesional (productor, técnico, etc.) -->
                                                <?php echo htmlspecialchars($cr['nombre_profesional']); ?>
                                                <!-- Rol formateado: guiones bajos → espacios, primera letra en mayúscula -->
                                                <span class="track-credito-rol"><?php echo ucfirst(str_replace('_', ' ', $cr['rol'])); ?></span>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Duración de la canción; si las horas son '00', se omiten para mayor limpieza -->
                            <span class="track-duracion"><?php
                                // Separar en partes: [horas, minutos, segundos]
                                $dur   = $c['duracion'] ?? '00:00:00';
                                $parts = explode(':', $dur);
                                // Mostrar solo mm:ss cuando las horas son cero
                                echo ($parts[0] === '00') ? $parts[1] . ':' . $parts[2] : $dur;
                            ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>

    <?php endif; ?>
</main>

<!-- ===================================================================
     REPRODUCTOR GLOBAL ESTILO SPOTIFY
     Aparece fijo en la parte inferior (encima del footer) al reproducir
     cualquier canción. Se controla completamente con JavaScript.
     =================================================================== -->
<div class="sp-player" id="sp-player">
    <!-- Elemento audio nativo oculto que gestiona la reproducción real -->
    <audio id="sp-audio"></audio>

    <!-- Zona izquierda: miniatura de portada + título y artista de la canción activa -->
    <div class="sp-player-left">
        <div class="sp-cover" id="sp-cover">
            <!-- Icono musical por defecto mientras no hay canción cargada -->
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

    <!-- Zona central: botones de control + barra de progreso -->
    <div class="sp-player-center">
        <div class="sp-controls">
            <!-- Botón pista anterior (si la canción lleva >3s, vuelve al principio) -->
            <button class="sp-btn-prev" id="sp-prev" aria-label="Anterior">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M4 4a.5.5 0 0 1 1 0v3.248l6.267-3.636A.5.5 0 0 1 12 4v8a.5.5 0 0 1-.733.44L5 8.752V12a.5.5 0 0 1-1 0z"/>
                </svg>
            </button>
            <!-- Botón principal play/pause (alterna entre los dos iconos SVG) -->
            <button class="sp-btn-play" id="sp-play-pause" aria-label="Play/Pause">
                <svg class="icon-play" xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M6.79 5.093A.5.5 0 0 0 6 5.5v5a.5.5 0 0 0 .79.407l3.5-2.5a.5.5 0 0 0 0-.814z"/>
                </svg>
                <svg class="icon-pause" xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" viewBox="0 0 16 16" style="display:none">
                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M6.25 5C5.56 5 5 5.56 5 6.25v3.5a1.25 1.25 0 1 0 2.5 0v-3.5C7.5 5.56 6.94 5 6.25 5m3.5 0c-.69 0-1.25.56-1.25 1.25v3.5a1.25 1.25 0 1 0 2.5 0v-3.5C11 5.56 10.44 5 9.75 5"/>
                </svg>
            </button>
            <!-- Botón siguiente pista -->
            <button class="sp-btn-next" id="sp-next" aria-label="Siguiente">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M12.5 4a.5.5 0 0 0-1 0v3.248L5.233 3.612A.5.5 0 0 0 4.5 4v8a.5.5 0 0 0 .733.44L11 8.752V12a.5.5 0 0 0 1 0z"/>
                </svg>
            </button>
        </div>
        <!-- Barra de progreso: tiempo actual | barra seekable | duración total -->
        <div class="sp-progress-wrap">
            <span class="sp-time" id="sp-current">0:00</span>
            <div class="sp-progress-bar" id="sp-progress-bar">
                <div class="sp-progress-fill" id="sp-progress-fill"></div>
                <div class="sp-progress-thumb" id="sp-progress-thumb"></div>
            </div>
            <span class="sp-time" id="sp-duration">0:00</span>
        </div>
    </div>

    <!-- Zona derecha: control de volumen con slider -->
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
