<?php require('src/vista/includes/header.php'); ?>

<main class="main-auth">
    <div class="auth-container">
        <h2>ÚNETE A LA <span>ESCENA</span></h2>
        
        <?php if (isset($error)): ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="index.php?action=procesarRegistro" method="POST" class="auth-form">
            <?php echo csrf_campo(); ?>
            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input type="email" id="email" name="email" required placeholder="tu@email.com" autocomplete="email">
            </div>

            <div class="form-group">
                <label for="contrasena">Contraseña</label>
                <input type="password" id="contrasena" name="contrasena" required placeholder="Mín. 8 caracteres" autocomplete="new-password">
            </div>

            <div class="form-group">
                <label for="confirmar_contrasena">Confirmar Contraseña</label>
                <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" required placeholder="Repite la contraseña" autocomplete="new-password">
            </div>

            <div class="form-group">
                <label for="rol">¿Qué tipo de cuenta quieres?</label>
                <select id="rol" name="rol" required>
                    <option value="user">Oyente / Usuario</option>
                    <option value="artista">Artista / Creador</option>
                </select>
            </div>

            <div class="form-group checkbox-group">
                <input type="checkbox" id="rgpd" name="rgpd" required>
                <label for="rgpd">Acepto la política de privacidad y el tratamiento de mis datos (RGPD).</label>
            </div>

            <button type="submit" class="btn-primary">CREAR CUENTA</button>
        </form>

        <p class="auth-links">
            ¿Ya tienes cuenta? <a href="index.php?action=login">Inicia sesión</a>
        </p>
    </div>
</main>

<?php require('src/vista/includes/footer.php'); ?>
