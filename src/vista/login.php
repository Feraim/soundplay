<?php require('src/vista/includes/header.php'); ?>

<main class="main-auth">
    <div class="auth-container">
        <h2>INICIAR <span>SESIÓN</span></h2>
        
        <?php if (isset($error)): ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (isset($mensaje_exito)): ?>
            <div class="alert-success"><?php echo htmlspecialchars($mensaje_exito); ?></div>
        <?php endif; ?>

        <form action="index.php?action=procesarLogin" method="POST" class="auth-form">
            <?php echo csrf_campo(); ?>
            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input type="email" id="email" name="email" required placeholder="tu@email.com" autocomplete="email">
            </div>

            <div class="form-group">
                <label for="contrasena">Contraseña</label>
                <input type="password" id="contrasena" name="contrasena" required placeholder="********" autocomplete="current-password">
            </div>

            <button type="submit" class="btn-primary">ENTRAR</button>
        </form>

        <p class="auth-links">
            ¿No tienes cuenta? <a href="index.php?action=registro">Regístrate aquí</a>
        </p>
    </div>
</main>

<?php require('src/vista/includes/footer.php'); ?>
