<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — SoundPlay' : 'SoundPlay'; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/style.css?v=4">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/responsive.css?v=1">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
</head>
<body>
    <header>
        <button class="menu-hamburguesa"  aria-label="Abrir menu">
<svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" class="bi bi-list" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5"/>
</svg>
        </button>
        <!-- Menú de navegación lateral (Hamburguesa) con los mismos enlaces del footer para consistencia -->
        <nav class="nav-menu">
            <?php if (!isset($_SESSION['usuario_id'])): ?>
                <!-- Emoji de iniciar sesión arriba de las opciones del menú si no hay sesión activa -->
                <div class="menu-login-container" style="padding: 0 0 1.5rem 2.5rem; border-bottom: 1px solid #262626; margin-bottom: 1.5rem;">
                    <a href="<?php echo BASE_URL; ?>/index.php?action=login" style="display: flex; align-items: center; gap: 12px; text-decoration: none;">
                        <img src="<?php echo BASE_URL; ?>/assets/img/image.png" alt="Iniciar Sesión" style="height: 2.5rem; border-radius: 50%;">
                        <span style="color: #FF0000; font-weight: bold; font-size: 0.95rem; font-family: 'Space Grotesk', sans-serif;">CONECTAR</span>
                    </a>
                </div>
            <?php endif; ?>
        <ul>
            <!-- Enlace a la página de Inicio -->
            <li><a href="<?php echo BASE_URL; ?>/index.php">INICIO</a></li>
            <!-- Enlace a la sección de Buscar -->
            <li><a href="<?php echo BASE_URL; ?>/index.php?action=explorar">BUSCAR</a></li>
            <!-- Enlace a la Biblioteca del usuario -->
            <li><a href="<?php echo BASE_URL; ?>/biblioteca.php">BIBLIOTECA</a></li>
            <!-- Enlace al Panel Personal (redirigirá según rol o inicio de sesión) -->
            <li><a href="<?php echo BASE_URL; ?>/index.php?action=panel">MI PANEL</a></li>
            <!-- Enlace a Ajustes de la cuenta / Configuración -->
            <li><a href="<?php echo BASE_URL; ?>/ajustes.php">AJUSTES</a></li>
        </ul>
        </nav>
        <h2>SOUNDPLAY</h2>
        <div class="emoji-sesion">
            <?php if (isset($_SESSION['usuario_id'])): ?>
                <!-- Enlace al panel del usuario si ya está logueado (con borde rojo distintivo) -->
                <a href="<?php echo BASE_URL; ?>/index.php?action=panel" title="Ir a mi panel">
                    <img src="<?php echo BASE_URL; ?>/assets/img/image.png" alt="emoji-sesion" style="border: 2px solid #FF0000;">
                </a>
            <?php else: ?>
                <!-- Enlace al login si no ha iniciado sesión -->
                <a href="<?php echo BASE_URL; ?>/index.php?action=login" title="Iniciar Sesión">
                    <img src="<?php echo BASE_URL; ?>/assets/img/image.png" alt="emoji-sesion">
                </a>
            <?php endif; ?>
        </div>
    </header>