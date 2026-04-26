<style>
.ajustes-header { font-size: 2.2rem; font-weight: 900; font-style: italic; margin-bottom: 5px; }
.ajustes-sub { font-size: 0.85rem; color: #888; margin-bottom: 30px; }

.set-group { background: #151515; border-radius: 12px; padding: 25px; margin-bottom: 20px; }
.set-title { font-size: 0.8rem; font-weight: 800; color: var(--soft-red); letter-spacing: 1px; display: flex; align-items: center; gap: 10px; margin-bottom: 25px; }

.set-item { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.set-item:last-child { margin-bottom: 0; padding-bottom: 0; border: none; }
.set-item h4 { font-size: 0.95rem; font-weight: 700; margin-bottom: 3px; }
.set-item p { font-size: 0.7rem; color: #888; }
.set-icon { color: #888; font-size: 1.1rem; }

/* CSS TOGGLE SWITCH */
.toggle-switch { position: relative; width: 45px; height: 24px; }
.toggle-switch input { opacity: 0; width: 0; height: 0; }
.slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #333; transition: .4s; border-radius: 34px; }
.slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
input:checked + .slider { background-color: var(--soft-red); }
input:checked + .slider.blue { background-color: #2196F3; }
input:checked + .slider:before { transform: translateX(21px); }
</style>

<div style="padding-bottom: 80px;">
    <h1 class="ajustes-header">Ajustes y Privacidad</h1>
    <p class="ajustes-sub">Gestiona tu experiencia sonora y seguridad.</p>

    <!-- CUENTA -->
    <div class="set-group">
        <h3 class="set-title"><i class="fa-solid fa-user"></i> CUENTA</h3>
        <div class="set-item">
            <div>
                <h4>Perfil de Usuario</h4>
                <p>Editar nombre, foto y biografía</p>
            </div>
            <i class="fa-solid fa-chevron-right set-icon"></i>
        </div>
        <div class="set-item">
            <div>
                <h4>Suscripción Premium</h4>
                <p style="color: var(--soft-red);">Vence en 24 días</p>
            </div>
            <i class="fa-solid fa-award set-icon"></i>
        </div>
        <a href="src/controlador/AuthController.php?action=logout" style="text-decoration:none; display:block; margin-top:20px; border-top:1px solid #222; padding-top:20px;">
            <div class="set-item" style="margin-bottom:0;">
                <h4 style="color: var(--soft-red);">Cerrar Sesión</h4>
                <i class="fa-solid fa-arrow-right-from-bracket" style="color: var(--soft-red);"></i>
            </div>
        </a>
    </div>

    <!-- CALIDAD DE AUDIO -->
    <div class="set-group">
        <h3 class="set-title"><i class="fa-solid fa-sliders"></i> CALIDAD DE AUDIO</h3>
        <div class="set-item">
            <div>
                <h4>Streaming en alta fidelidad</h4>
                <p>FLAC 24-bit/192kHz (Usa más datos)</p>
            </div>
            <label class="toggle-switch">
                <input type="checkbox" checked>
                <span class="slider blue"></span>
            </label>
        </div>
        <div class="set-item">
            <div>
                <h4>Normalización de volumen</h4>
                <p>Mantiene el nivel constante</p>
            </div>
            <label class="toggle-switch">
                <input type="checkbox">
                <span class="slider"></span>
            </label>
        </div>
        <div class="set-item">
            <div>
                <h4>Ecualizador</h4>
                <p>Personaliza tu firma sonora</p>
            </div>
            <i class="fa-solid fa-sliders set-icon"></i>
        </div>
    </div>

    <!-- PRIVACIDAD -->
    <div class="set-group">
        <h3 class="set-title"><i class="fa-solid fa-shield-halved"></i> PRIVACIDAD</h3>
        <div class="set-item">
            <div>
                <h4>Sesión Privada</h4>
                <p>Oculta tu actividad reciente</p>
            </div>
            <label class="toggle-switch">
                <input type="checkbox">
                <span class="slider"></span>
            </label>
        </div>
        <div class="set-item">
            <div>
                <h4>Gestión de Datos</h4>
                <p>Descargar o eliminar tus datos</p>
            </div>
            <i class="fa-solid fa-database set-icon"></i>
        </div>
    </div>

    <!-- NOTIFICACIONES -->
    <div class="set-group">
        <h3 class="set-title"><i class="fa-solid fa-bell"></i> NOTIFICACIONES</h3>
        <div class="set-item">
            <div>
                <h4>Nuevos lanzamientos</h4>
                <p>Alertas de tus artistas favoritos</p>
            </div>
            <label class="toggle-switch">
                <input type="checkbox" checked>
                <span class="slider"></span>
            </label>
        </div>
        <div class="set-item">
            <div>
                <h4>Recomendaciones</h4>
                <p>Curaduría basada en tus gustos</p>
            </div>
            <label class="toggle-switch">
                <input type="checkbox" checked>
                <span class="slider blue"></span>
            </label>
        </div>
    </div>

</div>
