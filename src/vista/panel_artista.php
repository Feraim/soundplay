<?php
// Incluir la cabecera HTML con el menú de navegación.
// La cabecera ya inicia la sesión y tiene acceso a BASE_URL.
require('src/vista/includes/header.php');
?>

<main class="panel-main">

    <!-- ===== CABECERA DEL PANEL ===== -->
    <div class="panel-header">
        <div class="panel-header-info">
            <!-- Título del panel con el rol destacado en rojo -->
            <h2>PANEL DE <span>ARTISTA</span></h2>
            <!-- Correo del artista conectado para que sepa que está en su cuenta -->
            <p><?php echo htmlspecialchars($_SESSION['usuario_email']); ?></p>
        </div>
        <!-- Enlace para cerrar la sesión de forma segura -->
        <a href="index.php?action=logout" class="panel-logout">CERRAR SESIÓN</a>
    </div>

    <!-- ===== MENSAJES FLASH ===== -->
    <!-- Los mensajes flash se guardan en la sesión y se eliminan tras mostrarse una vez -->
    <?php if (isset($error)): ?>
        <!-- Cuadro rojo de error: contraseña incorrecta, archivo no válido, etc. -->
        <div class="alert-error" style="margin-bottom:1rem;"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if (isset($mensaje_exito)): ?>
        <!-- Cuadro verde de éxito: álbum creado, canción subida, etc. -->
        <div class="alert-success" style="margin-bottom:1rem;"><?php echo htmlspecialchars($mensaje_exito); ?></div>
    <?php endif; ?>

    <!-- ===== ESTADÍSTICAS ===== -->
    <!-- Resumen rápido de la actividad del artista en la plataforma -->
    <div class="panel-card">
        <span class="panel-card-titulo">Estadísticas</span>
        <div class="panel-stat-grid">
            <!-- Total de reproducciones de todas las canciones del artista -->
            <div class="panel-stat-item">
                <span class="panel-stat-num"><?php echo (int)($totalReproducciones ?? 0); ?></span>
                <span class="panel-stat-label">Reproducciones</span>
            </div>
            <!-- Número de álbumes creados por el artista -->
            <div class="panel-stat-item">
                <span class="panel-stat-num"><?php echo count($albumes ?? []); ?></span>
                <span class="panel-stat-label">Álbumes</span>
            </div>
            <!-- Número total de canciones subidas por el artista -->
            <div class="panel-stat-item">
                <span class="panel-stat-num"><?php echo (int)($totalCanciones ?? 0); ?></span>
                <span class="panel-stat-label">Canciones</span>
            </div>
        </div>
    </div>

    <!-- ===== CREAR ÁLBUM ===== -->
    <!-- Formulario para que el artista cree un nuevo álbum con portada -->
    <div class="panel-card">
        <span class="panel-card-titulo">Crear nuevo álbum</span>
        <!-- enctype="multipart/form-data" es obligatorio para subir archivos -->
        <form action="index.php?action=crearAlbum" method="POST" enctype="multipart/form-data" class="auth-form" style="gap:1rem; max-width:520px;">
            <!-- Campo CSRF oculto para proteger el formulario contra ataques externos -->
            <?php echo csrf_campo(); ?>
            <div class="form-group">
                <label for="album_titulo">Título del álbum</label>
                <!-- Campo de texto requerido; el HTML impide enviar si está vacío -->
                <input type="text" id="album_titulo" name="titulo" required placeholder="Ej: Infinito Carmesí">
            </div>
            <div class="form-group">
                <label for="album_portada">Portada (imagen)</label>
                <!-- Solo acepta imágenes; el servidor también valida el MIME real -->
                <input type="file" id="album_portada" name="portada" accept="image/*" required
                       style="background:#111; border:1px solid #333; color:#fff; padding:10px; border-radius:8px;">
            </div>
            <!-- Botón de envío del formulario -->
            <button type="submit" class="btn-primary" style="margin-top:0; max-width:200px;">CREAR ÁLBUM</button>
        </form>
    </div>

    <!-- ===== SUBIR CANCIÓN ===== -->
    <!-- Formulario para subir un archivo MP3 y asociarlo a un álbum existente -->
    <div class="panel-card">
        <span class="panel-card-titulo">Subir canción</span>
        <form action="index.php?action=subirCancion" method="POST" enctype="multipart/form-data" class="auth-form" style="gap:1rem; max-width:520px;">
            <!-- Token CSRF para proteger contra falsificación de solicitudes -->
            <?php echo csrf_campo(); ?>

            <div class="form-group">
                <label for="cancion_album">Álbum</label>
                <!-- Dropdown con los álbumes del artista; debe existir al menos uno para subir canciones -->
                <select id="cancion_album" name="id_album" required>
                    <option value="">— Elige un álbum —</option>
                    <?php if (!empty($albumes)): ?>
                        <?php foreach ($albumes as $album): ?>
                            <!-- Cada opción tiene como valor el ID del álbum -->
                            <option value="<?php echo (int)$album['id_album']; ?>">
                                <?php echo htmlspecialchars($album['titulo']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Si no hay álbumes, el artista debe crear uno primero -->
                        <option value="" disabled>Crea un álbum primero</option>
                    <?php endif; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="cancion_titulo">Título de la canción</label>
                <!-- Campo de texto para el nombre de la canción -->
                <input type="text" id="cancion_titulo" name="titulo" required placeholder="Ej: Radar Urbano">
            </div>

            <div class="form-group">
                <label for="cancion_genero">Género</label>
                <!-- Dropdown con los géneros disponibles en la plataforma -->
                <select id="cancion_genero" name="genero" required>
                    <option value="">— Selecciona un género —</option>
                    <option value="RAP">RAP</option>
                    <option value="TRAP">TRAP</option>
                    <option value="DRILL">DRILL</option>
                    <option value="TECHNO">TECHNO</option>
                    <option value="REGUETON">REGUETON</option>
                    <option value="INDIE">INDIE</option>
                    <option value="METAL ALTERNATIVO">METAL ALTERNATIVO</option>
                    <option value="POP URBANO">POP URBANO</option>
                    <option value="OTRO">OTRO</option>
                </select>
            </div>

            <div class="form-group">
                <label for="cancion_archivo">Archivo MP3</label>
                <!-- Solo acepta MP3; el servidor también valida la extensión -->
                <input type="file" id="cancion_archivo" name="archivo" accept="audio/mpeg" required
                       style="background:#111; border:1px solid #333; color:#fff; padding:10px; border-radius:8px;">
            </div>

            <!-- Sección opcional de créditos técnicos (productor, técnico de mezcla, etc.) -->
            <div style="border-top:1px solid #1e1e1e; padding-top:1rem; margin-top:0.25rem;">
                <p style="font-size:0.78rem; text-transform:uppercase; letter-spacing:0.08em; color:#555; margin:0 0 0.75rem;">
                    Créditos técnicos <span style="color:#333;">(opcional)</span>
                </p>
                <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
                    <div class="form-group" style="flex:2; min-width:160px;">
                        <!-- Nombre del profesional que participó en la producción -->
                        <label style="font-size:0.8rem;">Nombre del profesional</label>
                        <input type="text" name="credito_nombre" placeholder="Ej: Alberto Marquez">
                    </div>
                    <div class="form-group" style="flex:1; min-width:120px;">
                        <!-- Rol que desempeñó ese profesional (debe coincidir con el enum de la BD) -->
                        <label style="font-size:0.8rem;">Rol</label>
                        <select name="credito_rol">
                            <option value="productor">Productor</option>
                            <option value="ingeniero_mezcla">Mezcla</option>
                            <option value="masterización">Masterización</option>
                            <option value="compositor">Compositor</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Botón de envío del formulario -->
            <button type="submit" class="btn-primary" style="margin-top:0; max-width:200px;">SUBIR CANCIÓN</button>
        </form>
    </div>

    <!-- ===== MIS CANCIONES ===== -->
    <!-- Lista de todas las canciones subidas por el artista, con reproductor integrado -->
    <div class="panel-card">
        <span class="panel-card-titulo">Mis canciones</span>

        <?php if (empty($cancionesArtista)): ?>
            <!-- Mensaje cuando el artista todavía no ha subido ninguna canción -->
            <p style="color:#555; font-size:0.9rem; margin:0.5rem 0;">
                Aún no has subido ninguna canción. Crea un álbum y sube tu primera pista.
            </p>
        <?php else: ?>
            <!-- Lista de canciones con reproductor; funciona igual que en la página de género -->
            <div class="track-list" style="margin-top:0.5rem;">
                <?php foreach ($cancionesArtista as $i => $c): ?>
                    <!-- Cada fila es clicable; el JS usa data-* para cargar la canción en el reproductor -->
                    <div class="track-row"
                         data-src="<?php echo BASE_URL; ?>/<?php echo htmlspecialchars(ltrim($c['archivo_ruta'] ?? '', '/')); ?>"
                         data-titulo="<?php echo htmlspecialchars($c['titulo']); ?>"
                         data-artista="<?php echo htmlspecialchars($_SESSION['usuario_email']); ?>"
                         data-portada="<?php echo !empty($c['portada_ruta'])
                             ? BASE_URL . '/' . htmlspecialchars(ltrim($c['portada_ruta'], '/'))
                             : ''; ?>">

                        <!-- Número de pista en la lista -->
                        <span class="track-num"><?php echo $i + 1; ?></span>

                        <!-- Botón play/pause: el JS alterna los dos iconos -->
                        <button class="track-play-btn" aria-label="Reproducir">
                            <!-- Icono play (triángulo): visible cuando no está sonando -->
                            <svg class="icon-play" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M10.804 8 5 4.633v6.734zm.792-.696a.802.802 0 0 1 0 1.392l-6.363 3.692C4.713 12.69 4 12.345 4 11.692V4.308c0-.653.713-.998 1.233-.696z"/>
                            </svg>
                            <!-- Icono pausa (dos barras): visible mientras suena -->
                            <svg class="icon-pause" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="display:none">
                                <path d="M5.5 3.5A1.5 1.5 0 0 1 7 5v6a1.5 1.5 0 0 1-3 0V5a1.5 1.5 0 0 1 1.5-1.5m5 0A1.5 1.5 0 0 1 12 5v6a1.5 1.5 0 0 1-3 0V5a1.5 1.5 0 0 1 1.5-1.5"/>
                            </svg>
                        </button>

                        <!-- Bloque central: título, álbum al que pertenece y créditos técnicos -->
                        <div class="track-info">
                            <!-- Nombre de la canción -->
                            <span class="track-titulo"><?php echo htmlspecialchars($c['titulo']); ?></span>
                            <!-- Título del álbum al que pertenece esta canción -->
                            <span class="track-artista"><?php echo htmlspecialchars($c['titulo_album'] ?? ''); ?></span>

                            <?php
                            // Obtener los créditos de esta canción desde el mapa precargado.
                            // Si no hay créditos registrados, el array estará vacío y no se renderiza nada.
                            $creditosCancion = $creditosMap[$c['id_cancion']] ?? [];
                            ?>
                            <?php if (!empty($creditosCancion)): ?>
                                <!-- Mostrar créditos técnicos solo si la canción tiene alguno registrado -->
                                <div class="track-creditos">
                                    <?php foreach ($creditosCancion as $cr): ?>
                                        <!-- Etiqueta pill: nombre del profesional + rol formateado -->
                                        <span class="track-credito-badge">
                                            <?php echo htmlspecialchars($cr['nombre_profesional']); ?>
                                            <!-- Convertir guión bajo a espacio y capitalizar la primera letra -->
                                            <span class="track-credito-rol"><?php echo ucfirst(str_replace('_', ' ', $cr['rol'])); ?></span>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Duración: mostrar solo mm:ss si las horas son 00 -->
                        <span class="track-duracion"><?php
                            // Separar la duración almacenada como TIME en la BD
                            $dur   = $c['duracion'] ?? '00:00:00';
                            $parts = explode(':', $dur);
                            // Si las horas son '00', omitirlas para un formato más limpio
                            echo ($parts[0] === '00') ? $parts[1] . ':' . $parts[2] : $dur;
                        ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- ===== AVISO LEGAL ===== -->
    <!-- Recordatorio de que la música publicada en la plataforma es propiedad de sus autores -->
    <div class="panel-card aviso-derechos">
        <!-- Título del aviso con el icono de advertencia -->
        <span class="panel-card-titulo">⚠ Aviso sobre derechos de autor</span>
        <!-- Texto explicativo del aviso -->
        <p>
            Toda la música que publicas en SoundPlay es de tu propiedad y permanece bajo tus derechos de autor.
            SoundPlay actúa únicamente como plataforma de distribución y no adquiere ningún derecho sobre tu obra.
        </p>
        <p>
            Al subir contenido confirmas que eres el titular de los derechos o tienes permiso expreso para publicarlo.
            La distribución de música sin licencia puede infringir la ley de propiedad intelectual.
        </p>
        <!-- Enlace a la sección de explorar donde se puede ver toda la música -->
        <a href="<?php echo BASE_URL; ?>/index.php?action=explorar" class="aviso-enlace">Ver toda la música en la plataforma →</a>
    </div>

</main>

<!-- ===================================================================
     REPRODUCTOR GLOBAL ESTILO SPOTIFY
     Se reutiliza el mismo bloque de HTML que en la página de género.
     El JS de main.js lo activa automáticamente al hacer clic en una pista.
     =================================================================== -->
<div class="sp-player" id="sp-player">
    <!-- Elemento de audio nativo oculto que gestiona la reproducción real -->
    <audio id="sp-audio"></audio>

    <!-- Zona izquierda: miniatura de portada + título y artista en curso -->
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
            <!-- Título de la canción activa (actualizado por JS) -->
            <span class="sp-song-titulo" id="sp-titulo">—</span>
            <!-- Artista de la canción activa (actualizado por JS) -->
            <span class="sp-song-artista" id="sp-artista">—</span>
        </div>
    </div>

    <!-- Zona central: botones de control + barra de progreso -->
    <div class="sp-player-center">
        <div class="sp-controls">
            <!-- Botón pista anterior -->
            <button class="sp-btn-prev" id="sp-prev" aria-label="Anterior">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M4 4a.5.5 0 0 1 1 0v3.248l6.267-3.636A.5.5 0 0 1 12 4v8a.5.5 0 0 1-.733.44L5 8.752V12a.5.5 0 0 1-1 0z"/>
                </svg>
            </button>
            <!-- Botón principal play/pause: alterna entre los dos iconos según el estado -->
            <button class="sp-btn-play" id="sp-play-pause" aria-label="Play/Pause">
                <svg class="icon-play" xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M6.79 5.093A.5.5 0 0 0 6 5.5v5a.5.5 0 0 0 .79.407l3.5-2.5a.5.5 0 0 0 0-.814z"/>
                </svg>
                <svg class="icon-pause" xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" viewBox="0 0 16 16" style="display:none">
                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M6.25 5C5.56 5 5 5.56 5 6.25v3.5a1.25 1.25 0 1 0 2.5 0v-3.5C7.5 5.56 6.94 5 6.25 5m3.5 0c-.69 0-1.25.56-1.25 1.25v3.5a1.25 1.25 0 1 0 2.5 0v-3.5C11 5.56 10.44 5 9.75 5"/>
                </svg>
            </button>
            <!-- Botón pista siguiente -->
            <button class="sp-btn-next" id="sp-next" aria-label="Siguiente">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M12.5 4a.5.5 0 0 0-1 0v3.248L5.233 3.612A.5.5 0 0 0 4.5 4v8a.5.5 0 0 0 .733.44L11 8.752V12a.5.5 0 0 0 1 0z"/>
                </svg>
            </button>
        </div>
        <!-- Barra de progreso: tiempo actual | barra de seekbar | duración total -->
        <div class="sp-progress-wrap">
            <span class="sp-time" id="sp-current">0:00</span>
            <div class="sp-progress-bar" id="sp-progress-bar">
                <div class="sp-progress-fill" id="sp-progress-fill"></div>
                <div class="sp-progress-thumb" id="sp-progress-thumb"></div>
            </div>
            <span class="sp-time" id="sp-duration">0:00</span>
        </div>
    </div>

    <!-- Zona derecha: control deslizante de volumen -->
    <div class="sp-player-right">
        <div class="sp-volume-wrap">
            <!-- Icono de altavoz decorativo -->
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#b3b3b3" viewBox="0 0 16 16">
                <path d="M9 4a.5.5 0 0 0-.812-.39L5.825 5.5H3.5A.5.5 0 0 0 3 6v4a.5.5 0 0 0 .5.5h2.325l2.363 1.89A.5.5 0 0 0 9 12zm3.025 4a4.5 4.5 0 0 1-1.318 3.182L10 10.475A3.5 3.5 0 0 0 10 5.525l.707-.707A4.5 4.5 0 0 1 12.025 8"/>
            </svg>
            <!-- Slider de volumen: va de 0 (silencio) a 1 (máximo), con pasos de 0.05 -->
            <input type="range" class="sp-volume" id="sp-volume" min="0" max="1" step="0.05" value="1">
        </div>
    </div>
</div>

<?php require('src/vista/includes/footer.php'); ?>
