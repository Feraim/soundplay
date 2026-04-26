<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <!-- Título descriptivo para SEO -->
    <title>SoundPlay - Panel Local</title>
    <!-- Vincula la hoja que acabamos de refactorizar al español -->
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <!-- ========================================== -->
    <!-- BARRA SUPERIOR FIJA DE LA APLICACIÓN -->
    <!-- ========================================== -->
    <header class="barra-superior-app">
        <!-- Ícono del menú (Llama a toggleMenu en JavaScript) -->
        <i class="fa-solid fa-bars icono-menu" onclick="toggleMenu()"></i>
        
        <!-- Nombre de tu plataforma -->
        <h1 class="texto-logo">SoundPlay</h1>
        
        <!-- Iconos a la derecha: Búsqueda dinámica y Perfil/Auth -->
        <div class="iconos-derecha">
            <i class="fa-solid fa-magnifying-glass"></i>
            <?php if(isset($_SESSION['rol'])): ?>
                <!-- Panel seguro habilitado si ya has iniciado sesión -->
                <i class="fa-solid fa-user-check" style="color:var(--primary-red);" onclick="navegar('panel')"></i>
            <?php else: ?>
                <!-- Dispara el registro si no posees ID temporal de sesión -->
                <i class="fa-regular fa-circle-user" onclick="navegar('registro')"></i>
            <?php endif; ?>
        </div>
    </header>

    <!-- ========================================== -->
    <!-- OVERLAYS Y MENÚS LATERALES OCULTOS DE BASE -->
    <!-- ========================================== -->
    <!-- Capa negra traslúcida bloqueante cuando el panel se abre -->
    <div class="capa-oscura-menu" id="capa-oscura-menu" onclick="toggleMenu()"></div>
    
    <!-- Contenedor matriz del drawer menú deslizante -->
    <aside class="menu-deslizante" id="menu-deslizante">
        
        <div class="cabecera-menu">
            <h2>SoundPlay</h2>
            <i class="fa-solid fa-xmark boton-cerrar" onclick="toggleMenu()"></i>
        </div>

        <div class="perfil-menu">
            <img src="assets/img/artist1.jpg" alt="Avatar">
            <div>
                <h3>Nova Eclipse</h3>
                <p>Verificado</p>
            </div>
        </div>

        <!-- Cajón de texto lateral interactivo simulado -->
        <div class="busqueda-menu">
            <i class="fa-solid fa-magnifying-glass" style="color:var(--text-gray);"></i>
            <input type="text" placeholder="Encuentra tu onda...">
        </div>

        <div class="filtros-menu">
            <!-- Botoncitos de clasificación -->
            <span class="filtro-m">NOMBRE</span>
            <span class="filtro-m">LOCALIDAD</span>
            <span class="filtro-m">GÉNERO</span>
        </div>

        <!-- Opciones maestras de enrutamiento del cajón lateral -->
        <div class="enlaces-menu">
            <a onclick="toggleMenu(); navegar('explorar');"><div class="icono-cuadrado"><i class="fa-regular fa-compass"></i></div> Explorar Géneros</a>
            <a onclick="toggleMenu(); navegar('biblioteca');"><i class="fa-solid fa-compact-disc"></i> Mi Biblioteca</a>
            <a onclick="toggleMenu(); navegar('panel');"><i class="fa-solid fa-microphone-lines"></i> Panel de Artista</a>
            <hr style="border-color:#222; margin: 10px 0;">
            <a onclick="toggleMenu(); navegar('ajustes');"><i class="fa-solid fa-gear"></i> Ajustes</a>
            <a onclick="toggleMenu(); navegar('ajustes');"><i class="fa-solid fa-shield-halved"></i> Privacidad</a>
        </div>

        <div class="pie-menu">
            <a href="src/controlador/AuthController.php?action=logout" style="color:var(--soft-red); font-weight:800; text-decoration:none;"><i class="fa-solid fa-arrow-right-from-bracket"></i> Cerrar Sesión</a>
            <p style="margin-top:20px; font-size:0.6rem; color:#444; letter-spacing:1px; font-weight:800;">V2.4.0-STABLE <span style="float:right; color:var(--primary-red);"><i class="fa-solid fa-circle"></i> <i class="fa-solid fa-circle" style="color:#555;"></i> <i class="fa-solid fa-circle" style="color:#555;"></i></span></p>
        </div>
    </aside>

    <!-- ========================================== -->
    <!-- MATRIZ PRINCIPAL CON CARGA EN LÍNEA (PHP RENDER) -->
    <!-- ========================================== -->
    <!-- Contenedor sobre el que JS incide mediante .innerHTML -->
    <main id="contenedor-app">
        <?php 
            $page = $_GET['page'] ?? 'inicio';
            // Array protector de listas de ruteo seguras
            $allowed_pages = ['inicio', 'buscar', 'biblioteca', 'panel', 'detalle_artista', 'login', 'explorar', 'ajustes', 'registro'];
            
            if (!in_array($page, $allowed_pages)) {
                $page = 'inicio';
            }
            if (file_exists("src/vista/$page.php")) {
                include "src/vista/$page.php"; 
            } else {
                echo "<p style='color:white;'>Página interna no disponible o no mapeada.</p>";
            }
        ?>
    </main>

    <!-- ========================================== -->
    <!-- ELEMENTOS FLOTANTES INFERIORES: REPRODUCTOR -->
    <!-- ========================================== -->
    <footer class="reproductor">
        <div class="info-cancion">
            <img src="assets/img/default-album.jpg" id="player-img" alt="Track">
            <div class="texto">
                <h4 id="player-title">The End Has No...</h4>
                <p id="player-artist">The Strokes</p>
            </div>
        </div>
        <div class="controles-movil">
            <i class="fa-solid fa-backward-step"></i>
            <i class="fa-solid fa-circle-pause" id="master-play" style="color:var(--primary-red);"></i>
            <i class="fa-solid fa-forward-step"></i>
        </div>
    </footer>

    <!-- ========================================== -->
    <!-- BOTONERA FIJA INFERIOR (BOTTOM NAV BAR) -->
    <!-- ========================================== -->
    <nav class="navegacion-inferior">
        <div class="item-nav <?php echo ($page=='inicio')?'activo':''; ?>" onclick="navegar('inicio')">
            <i class="fa-solid fa-house"></i>
        </div>
        <div class="item-nav <?php echo ($page=='explorar')?'activo':''; ?>" onclick="navegar('explorar')">
            <i class="fa-regular fa-compass"></i>
        </div>
        <div class="item-nav <?php echo ($page=='buscar' || $page=='local')?'activo':''; ?>" onclick="navegar('inicio')">
            <!-- Ojo: aquí rediriges por defecto a inicio, según el flujo viejo -->
            <i class="fa-solid fa-location-dot"></i>
        </div>
        <div class="item-nav <?php echo ($page=='biblioteca')?'activo':''; ?>" onclick="navegar('biblioteca')">
            <i class="fa-solid fa-compact-disc"></i>
        </div>
    </nav>

    <!-- Carga del JS principal -->
    <script src="assets/main.js"></script>
</body>
</html>