<?php require('src/vista/includes/header.php'); ?>

<main class="panel-main">

    <!-- ===== CABECERA ===== -->
    <div class="panel-header">
        <div class="panel-header-info">
            <h2>PANEL DE <span>ADMINISTRACIÓN</span></h2>
            <p><?php echo htmlspecialchars($_SESSION['usuario_email']); ?></p>
        </div>
        <a href="index.php?action=logout" class="panel-logout">CERRAR SESIÓN</a>
    </div>

    <!-- ===== MENSAJES FLASH ===== -->
    <?php if (isset($error)): ?>
        <div class="alert-error" style="margin-bottom:1rem;"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if (isset($mensaje_exito)): ?>
        <div class="alert-success" style="margin-bottom:1rem;"><?php echo htmlspecialchars($mensaje_exito); ?></div>
    <?php endif; ?>

    <!-- ===== ESTADÍSTICAS GLOBALES ===== -->
    <div class="panel-card">
        <span class="panel-card-titulo">Estadísticas globales</span>
        <div class="panel-stat-grid">
            <div class="panel-stat-item">
                <span class="panel-stat-num" style="color:#FF0000;"><?php echo (int)($totalCancionesAdmin ?? 0); ?></span>
                <span class="panel-stat-label">Canciones</span>
            </div>
            <div class="panel-stat-item">
                <span class="panel-stat-num"><?php echo (int)($totalArtistasAdmin ?? 0); ?></span>
                <span class="panel-stat-label">Artistas</span>
            </div>
        </div>
    </div>

    <!-- ===== GESTIÓN DE CUENTAS ===== -->
    <div class="panel-card">
        <span class="panel-card-titulo">Gestión de cuentas</span>

        <!-- Formulario banear / desbanear — lógica intacta -->
        <p style="font-size:0.82rem; color:#888; margin:0 0 0.75rem; font-weight:600;">
            Banear / Desbanear cuenta
        </p>
        <form action="index.php?action=banearUsuario" method="POST" class="auth-form" style="gap:0.75rem; max-width:560px;">
            <?php echo csrf_campo(); ?>
            <div style="display:flex; gap:0.5rem; flex-wrap:wrap; align-items:flex-end;">
                <div class="form-group" style="flex:2; min-width:180px; margin:0;">
                    <input type="email" name="email" required placeholder="Correo del usuario">
                </div>
                <div class="form-group" style="flex:1; min-width:110px; margin:0;">
                    <select name="estado_ban" required>
                        <option value="1">Banear</option>
                        <option value="0">Desbanear</option>
                    </select>
                </div>
                <button type="submit" class="btn-primary" style="margin:0; padding:12px 20px; white-space:nowrap;">
                    APLICAR
                </button>
            </div>
        </form>

        <hr class="panel-divisor">

        <!-- Formulario resetear contraseña — lógica intacta -->
        <p style="font-size:0.82rem; color:#888; margin:0 0 0.75rem; font-weight:600;">
            Restablecer contraseña
        </p>
        <form action="index.php?action=resetPassword" method="POST" class="auth-form" style="gap:0.75rem; max-width:560px;">
            <?php echo csrf_campo(); ?>
            <div style="display:flex; gap:0.5rem; flex-wrap:wrap; align-items:flex-end;">
                <div class="form-group" style="flex:1; min-width:180px; margin:0;">
                    <input type="email" name="email" required placeholder="Correo del usuario">
                </div>
                <div class="form-group" style="flex:1; min-width:180px; margin:0;">
                    <input type="password" name="nueva_contrasena" required placeholder="Nueva contraseña temporal">
                </div>
                <button type="submit" class="btn-primary" style="margin:0; padding:12px 20px; white-space:nowrap;">
                    RESTABLECER
                </button>
            </div>
        </form>
    </div>

    <!-- ===== CONTROL DE ALMACENAMIENTO ===== -->
    <div class="panel-card">
        <span class="panel-card-titulo">Control de almacenamiento</span>
        <p style="font-size:0.85rem; color:#666; margin:0 0 1rem;">
            Límites de almacenamiento por artista registrado.
        </p>
        <div style="overflow-x:auto;">
            <table class="panel-tabla">
                <thead>
                    <tr>
                        <th>Artista</th>
                        <th>Espacio usado</th>
                        <th>Máximo</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Alberto Marquez</td>
                        <td>15.4 MB</td>
                        <td>100 MB</td>
                        <td><span class="panel-tag panel-tag-ok">Aceptable</span></td>
                    </tr>
                    <tr>
                        <td>Luna &amp; Sira</td>
                        <td>29.8 MB</td>
                        <td>100 MB</td>
                        <td><span class="panel-tag panel-tag-ok">Aceptable</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</main>

<?php require('src/vista/includes/footer.php'); ?>
