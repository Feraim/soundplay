<?php require('src/vista/includes/header.php'); ?>

<main class="panel-main">

    <!-- ===== CABECERA ===== -->
    <div class="panel-header">
        <div class="panel-header-info">
            <h2>PANEL DE <span>OYENTE</span></h2>
            <p><?php echo htmlspecialchars($_SESSION['usuario_email']); ?></p>
        </div>
        <a href="index.php?action=logout" class="panel-logout">CERRAR SESIÓN</a>
    </div>

    <!-- ===== ACCESOS RÁPIDOS ===== -->
    <div class="panel-card">
        <span class="panel-card-titulo">Explorar música</span>
        <div class="panel-stat-grid">
            <a href="index.php?action=explorar" class="panel-stat-item" style="text-decoration:none; cursor:pointer; transition:border-color 0.2s;" onmouseover="this.style.borderColor='#FF0000'" onmouseout="this.style.borderColor='#1e1e1e'">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="#FF0000" viewBox="0 0 16 16" style="margin-bottom:8px;">
                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                </svg>
                <span class="panel-stat-label">Buscar</span>
            </a>
            <a href="index.php?action=verGenero&genero=RAP" class="panel-stat-item" style="text-decoration:none; cursor:pointer; transition:border-color 0.2s;" onmouseover="this.style.borderColor='#FF0000'" onmouseout="this.style.borderColor='#1e1e1e'">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="#FF0000" viewBox="0 0 16 16" style="margin-bottom:8px;">
                    <path d="M9 13c0 1.105-1.12 2-2.5 2S4 14.105 4 13s1.12-2 2.5-2 2.5.895 2.5 2"/>
                    <path fill-rule="evenodd" d="M9 3v10H8V3z"/>
                    <path d="M8 2.82a1 1 0 0 1 .804-.98l3-.6A1 1 0 0 1 13 2.22V4L8 5z"/>
                </svg>
                <span class="panel-stat-label">RAP</span>
            </a>
            <a href="index.php?action=verGenero&genero=TRAP" class="panel-stat-item" style="text-decoration:none; cursor:pointer; transition:border-color 0.2s;" onmouseover="this.style.borderColor='#FF0000'" onmouseout="this.style.borderColor='#1e1e1e'">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="#FF0000" viewBox="0 0 16 16" style="margin-bottom:8px;">
                    <path d="M9 13c0 1.105-1.12 2-2.5 2S4 14.105 4 13s1.12-2 2.5-2 2.5.895 2.5 2"/>
                    <path fill-rule="evenodd" d="M9 3v10H8V3z"/>
                    <path d="M8 2.82a1 1 0 0 1 .804-.98l3-.6A1 1 0 0 1 13 2.22V4L8 5z"/>
                </svg>
                <span class="panel-stat-label">TRAP</span>
            </a>
            <a href="biblioteca.php" class="panel-stat-item" style="text-decoration:none; cursor:pointer; transition:border-color 0.2s;" onmouseover="this.style.borderColor='#FF0000'" onmouseout="this.style.borderColor='#1e1e1e'">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="#FF0000" viewBox="0 0 16 16" style="margin-bottom:8px;">
                    <path d="M2 3a.5.5 0 0 0 .5.5h11a.5.5 0 0 0 0-1h-11A.5.5 0 0 0 2 3m2-2a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 0-1h-7A.5.5 0 0 0 4 1m2.765 5.576A.5.5 0 0 0 6 7v5a.5.5 0 0 0 .765.424l4-2.5a.5.5 0 0 0 0-.848z"/>
                    <path d="M1.5 14.5A1.5 1.5 0 0 1 0 13V6a1.5 1.5 0 0 1 1.5-1.5h13A1.5 1.5 0 0 1 16 6v7a1.5 1.5 0 0 1-1.5 1.5zm13-1a.5.5 0 0 0 .5-.5V6a.5.5 0 0 0-.5-.5h-13A.5.5 0 0 0 1 6v7a.5.5 0 0 0 .5.5z"/>
                </svg>
                <span class="panel-stat-label">Biblioteca</span>
            </a>
        </div>
    </div>

    <!-- ===== SECCIONES PRÓXIMAMENTE ===== -->
    <div class="panel-card">
        <span class="panel-card-titulo">Artistas más escuchados</span>
        <div class="panel-placeholder">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="#333" viewBox="0 0 16 16">
                <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0"/>
                <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1"/>
            </svg>
            Aquí aparecerán los artistas locales que más reproduces.
        </div>
    </div>

    <div class="panel-card">
        <span class="panel-card-titulo">Historial de reproducción</span>
        <div class="panel-placeholder">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="#333" viewBox="0 0 16 16">
                <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71z"/>
                <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0"/>
            </svg>
            Últimas canciones reproducidas recientemente.
        </div>
    </div>

    <div class="panel-card">
        <span class="panel-card-titulo">Mis canciones favoritas</span>
        <div class="panel-placeholder">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="#333" viewBox="0 0 16 16">
                <path d="m8 2.748-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385.92 1.815 2.834 3.989 6.286 6.357 3.452-2.368 5.365-4.542 6.286-6.357.955-1.886.838-3.362.314-4.385C13.486.878 10.4.28 8.717 2.01z"/>
            </svg>
            Canciones guardadas con Me Gusta.
        </div>
    </div>

    <!-- Novedades de los artistas que el usuario sigue (función futura) -->
    <div class="panel-card">
        <span class="panel-card-titulo">Novedades de tus artistas</span>
        <div class="panel-placeholder">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="#333" viewBox="0 0 16 16">
                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/>
            </svg>
            Lanzamientos recomendados basados en tus gustos.
        </div>
    </div>

    <!-- ===== AVISO LEGAL ===== -->
    <!-- Recordatorio de que la música de la plataforma pertenece a sus artistas, no al oyente -->
    <div class="panel-card aviso-derechos">
        <!-- Título con icono de advertencia para llamar la atención -->
        <span class="panel-card-titulo">⚠ Aviso sobre derechos de autor</span>
        <!-- Explicación breve y clara del aviso -->
        <p>
            Toda la música disponible en SoundPlay pertenece a sus respectivos artistas y creadores.
            Como oyente, tienes acceso para escuchar las obras, pero no para descargarlas, redistribuirlas
            ni reclamar su autoría.
        </p>
        <p>
            Respeta el trabajo de los artistas locales. SoundPlay es una plataforma de apoyo a la música independiente.
        </p>
        <!-- Enlace para descubrir más música y apoyar a los artistas -->
        <a href="<?php echo BASE_URL; ?>/index.php?action=explorar" class="aviso-enlace">Descubrir música en la plataforma →</a>
    </div>

</main>

<?php require('src/vista/includes/footer.php'); ?>
