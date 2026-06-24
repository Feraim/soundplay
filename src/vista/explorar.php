<?php
// Vista de la página de búsqueda / exploración.
// Recibe del controlador:
//   $query     (string)      - término de búsqueda introducido por el usuario, o cadena vacía
//   $resultados (array|null) - array de canciones encontradas, o null si no se ha buscado aún
require 'src/vista/includes/header.php';
?>

<main class="contenido-pagina">

    <!-- ===== BUSCADOR ===== -->
    <!-- Formulario de búsqueda siempre visible en la parte superior de la página -->
    <section class="buscar-hero">
        <h2 class="buscar-titulo">Buscar</h2>
        <form class="buscar-form" action="index.php" method="GET">
            <!-- El campo oculto 'action' dirige al controlador de búsqueda -->
            <input type="hidden" name="action" value="buscar">
            <div class="buscar-input-wrap">
                <!-- Icono de lupa decorativo dentro del input -->
                <svg class="buscar-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                </svg>
                <!-- Campo de texto: muestra el término buscado previamente si existe -->
                <input
                    type="text"
                    name="q"
                    class="buscar-input"
                    placeholder="Artistas, canciones, álbumes..."
                    value="<?php echo htmlspecialchars($query ?? ''); ?>"
                    autofocus>
                <?php if (!empty($query)): ?>
                    <!-- Enlace para limpiar la búsqueda y volver a la vista de géneros -->
                    <a href="index.php?action=explorar" class="buscar-clear" aria-label="Limpiar búsqueda">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/>
                        </svg>
                    </a>
                <?php endif; ?>
            </div>
            <button type="submit" class="btn-primary buscar-submit">BUSCAR</button>
        </form>
    </section>

    <?php if ($resultados !== null): ?>
        <!-- ===== RESULTADOS DE BÚSQUEDA ===== -->
        <!-- Se muestra solo cuando el usuario ha enviado el formulario -->
        <section>
            <h3 class="buscar-resultados-titulo">
                <?php if (!empty($query)): ?>
                    Resultados para "<span style="color:#FF0000;"><?php echo htmlspecialchars($query); ?></span>"
                <?php endif; ?>
            </h3>

            <?php if (empty($resultados)): ?>
                <!-- Sin resultados: mensaje informativo -->
                <div class="buscar-sin-resultados">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="#444" viewBox="0 0 16 16">
                        <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                    </svg>
                    <p>No se encontraron canciones ni álbumes para
                        <strong><?php echo htmlspecialchars($query); ?></strong>.
                    </p>
                </div>

            <?php else: ?>
                <!-- Grid de tarjetas de resultado -->
                <!-- Cada tarjeta guarda en data-* los datos que necesita el reproductor -->
                <div class="buscar-resultados-grid">
                    <?php foreach ($resultados as $c): ?>
                        <div class="buscar-resultado-card"
                             data-src="<?php echo BASE_URL; ?>/<?php echo htmlspecialchars(ltrim($c['archivo_ruta'] ?? '', '/')); ?>"
                             data-titulo="<?php echo htmlspecialchars($c['titulo']); ?>"
                             data-artista="<?php echo htmlspecialchars($c['nombre_artistico']); ?>"
                             data-portada="<?php echo !empty($c['portada_ruta'])
                                 ? BASE_URL . '/' . htmlspecialchars(ltrim($c['portada_ruta'], '/'))
                                 : ''; ?>">

                            <!-- Portada de la canción con overlay de play al hacer hover -->
                            <div class="buscar-resultado-portada">
                                <?php if (!empty($c['portada_ruta'])): ?>
                                    <img src="<?php echo BASE_URL; ?>/<?php echo htmlspecialchars(ltrim($c['portada_ruta'], '/')); ?>"
                                         alt="Portada">
                                <?php else: ?>
                                    <!-- Placeholder cuando no hay portada -->
                                    <div class="buscar-resultado-sin-portada">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="#555" viewBox="0 0 16 16">
                                            <path d="M9 13c0 1.105-1.12 2-2.5 2S4 14.105 4 13s1.12-2 2.5-2 2.5.895 2.5 2"/>
                                            <path fill-rule="evenodd" d="M9 3v10H8V3z"/>
                                            <path d="M8 2.82a1 1 0 0 1 .804-.98l3-.6A1 1 0 0 1 13 2.22V4L8 5z"/>
                                        </svg>
                                    </div>
                                <?php endif; ?>
                                <!-- Capa con botón play que aparece al hacer hover sobre la tarjeta -->
                                <div class="buscar-resultado-overlay">
                                    <button class="buscar-play-btn" aria-label="Reproducir">
                                        <svg class="icon-play" xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="#fff" viewBox="0 0 16 16">
                                            <path d="M5.25 3.065a.5.5 0 0 1 .5 0l8 4.5a.5.5 0 0 1 0 .87l-8 4.5a.5.5 0 0 1-.75-.435v-9a.5.5 0 0 1 .25-.435"/>
                                        </svg>
                                        <!-- Icono de pausa (visible solo cuando la tarjeta está reproduciéndose) -->
                                        <svg class="icon-pause" xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="#fff" viewBox="0 0 16 16" style="display:none">
                                            <path d="M5.5 3.5A1.5 1.5 0 0 1 7 5v6a1.5 1.5 0 0 1-3 0V5a1.5 1.5 0 0 1 1.5-1.5m5 0A1.5 1.5 0 0 1 12 5v6a1.5 1.5 0 0 1-3 0V5a1.5 1.5 0 0 1 1.5-1.5"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Información textual de la canción: título, artista y álbum -->
                            <div class="buscar-resultado-info">
                                <p class="buscar-resultado-titulo"><?php echo htmlspecialchars($c['titulo']); ?></p>
                                <p class="buscar-resultado-artista"><?php echo htmlspecialchars($c['nombre_artistico']); ?></p>
                                <p class="buscar-resultado-album"><?php echo htmlspecialchars($c['titulo_album'] ?? ''); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

    <?php else: ?>
        <!-- ===== VISTA POR DEFECTO: GRID DE GÉNEROS ===== -->
        <!-- 4 tarjetas iguales en una sola fila: RAP | TRAP | DRILL | REGUETÓN -->
        <div class="generos">
            <h2>Explorar géneros</h2>

            <!-- RAP: tarjeta normal, misma altura que el resto -->
            <a href="<?php echo BASE_URL; ?>/index.php?action=verGenero&genero=RAP" style="text-decoration:none;color:inherit;">
                <article class="tarjeta-genero">
                    <img src="<?php echo BASE_URL; ?>/assets/img/rap.webp" alt="RAP">
                    <p>RAP</p>
                </article>
            </a>

            <!-- TRAP: tarjeta normal -->
            <a href="<?php echo BASE_URL; ?>/index.php?action=verGenero&genero=TRAP" style="text-decoration:none;color:inherit;">
                <article class="tarjeta-genero">
                    <img src="<?php echo BASE_URL; ?>/assets/img/trap.webp" alt="TRAP">
                    <p>TRAP</p>
                </article>
            </a>

            <!-- DRILL: tarjeta normal -->
            <a href="<?php echo BASE_URL; ?>/index.php?action=verGenero&genero=DRILL" style="text-decoration:none;color:inherit;">
                <article class="tarjeta-genero">
                    <img src="<?php echo BASE_URL; ?>/assets/img/drill.webp" alt="DRILL">
                    <p>DRILL</p>
                </article>
            </a>

            <!-- REGUETÓN: tarjeta normal -->
            <a href="<?php echo BASE_URL; ?>/index.php?action=verGenero&genero=REGUETON" style="text-decoration:none;color:inherit;">
                <article class="tarjeta-genero">
                    <img src="<?php echo BASE_URL; ?>/assets/img/regueton.webp" alt="REGUETON">
                    <p>REGUETÓN</p>
                </article>
            </a>
        </div>

        <!-- Sección de tendencias: últimas canciones subidas a la plataforma -->
        <div class="tendencias">
            <h2>Tendencias</h2>
            <p style="color:#b8b8b8; font-size:0.9rem; margin: 0.5rem 0 1.5rem;">Nuevos lanzamientos y tendencias locales de la calle.</p>

            <?php if (!empty($tendencias)): ?>
                <!-- Lista horizontal de las canciones más recientes -->
                <div class="tendencias-grid">
                    <?php foreach ($tendencias as $t): ?>
                        <!-- Tarjeta de tendencia: enlaza al género de la canción -->
                        <a href="<?php echo BASE_URL; ?>/index.php?action=verGenero&genero=<?php echo urlencode($t['genero']); ?>"
                           class="tendencia-card">
                            <div class="tendencia-portada">
                                <?php if (!empty($t['portada_ruta'])): ?>
                                    <img src="<?php echo BASE_URL; ?>/<?php echo htmlspecialchars(ltrim($t['portada_ruta'], '/')); ?>"
                                         alt="Portada">
                                <?php else: ?>
                                    <div class="tendencia-sin-portada">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="#555" viewBox="0 0 16 16">
                                            <path d="M9 13c0 1.105-1.12 2-2.5 2S4 14.105 4 13s1.12-2 2.5-2 2.5.895 2.5 2"/>
                                            <path fill-rule="evenodd" d="M9 3v10H8V3z"/>
                                            <path d="M8 2.82a1 1 0 0 1 .804-.98l3-.6A1 1 0 0 1 13 2.22V4L8 5z"/>
                                        </svg>
                                    </div>
                                <?php endif; ?>
                                <div class="tendencia-overlay">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#fff" viewBox="0 0 16 16">
                                        <path d="M5.25 3.065a.5.5 0 0 1 .5 0l8 4.5a.5.5 0 0 1 0 .87l-8 4.5a.5.5 0 0 1-.75-.435v-9a.5.5 0 0 1 .25-.435"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="tendencia-info">
                                <p class="tendencia-titulo"><?php echo htmlspecialchars($t['titulo']); ?></p>
                                <p class="tendencia-artista"><?php echo htmlspecialchars($t['nombre_artistico']); ?></p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="color:#555; font-size:0.9rem;">Aún no hay canciones disponibles.</p>
            <?php endif; ?>
        </div>

    <?php endif; ?>

</main>

<!-- ===================================================================
     REPRODUCTOR GLOBAL ESTILO SPOTIFY (para resultados de búsqueda)
     Misma estructura que en genero.php. Aparece al reproducir cualquier
     resultado de búsqueda y se posiciona encima del footer fijo.
     =================================================================== -->
<div class="sp-player" id="sp-player">
    <audio id="sp-audio"></audio>

    <!-- Zona izquierda: portada miniatura + nombre de canción y artista -->
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

    <!-- Zona central: controles de reproducción + barra de progreso -->
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

    <!-- Zona derecha: control de volumen -->
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
