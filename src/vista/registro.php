<style>
/* Estilos del contenedor principal del registro */
.contenedor-acceso { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: url('assets/img/artist1.jpg') center/cover no-repeat; position: fixed; top: 0; left: 0; width: 100%; z-index: 2000; }
.capa-acceso { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(to bottom, rgba(10,10,10,0.8), rgba(0,0,0,1)); z-index: 1; }
.caja-formulario { position: relative; z-index: 2; background: #111; padding: 40px 30px; border-radius: 15px; width: 90%; max-width: 400px; box-shadow: 0 10px 30px rgba(0,0,0,0.9); border: 1px solid #333; }
/* Logotipo interno */
.logo-acceso { text-align: center; margin-bottom: 30px; }
.logo-acceso h2 { color: var(--primary-red); font-weight: 900; font-style: italic; font-size: 2rem; }
/* Cajas de inputs */
.grupo-formulario { margin-bottom: 20px; }
.grupo-formulario label { display: block; color: var(--text-gray); font-size: 0.8rem; font-weight: 800; margin-bottom: 8px; letter-spacing: 1px; }
.grupo-formulario input, .grupo-formulario select { width: 100%; background: #1a1a1a; border: 1px solid #333; color: white; padding: 12px 15px; border-radius: 8px; font-size: 1rem; outline: none; transition: 0.3s; }
.grupo-formulario input:focus, .grupo-formulario select:focus { border-color: var(--primary-red); box-shadow: 0 0 10px rgba(255,0,0,0.2); }
/* Botón de envío */
.boton-acceso { width: 100%; background: var(--primary-red); color: white; border: none; padding: 15px; border-radius: 8px; font-weight: 800; font-size: 1rem; cursor: pointer; transition: 0.3s; margin-top: 10px; }
.boton-acceso:hover { background: var(--soft-red); transform: translateY(-2px); }
/* Letra pequeña */
.pie-acceso { text-align: center; margin-top: 20px; font-size: 0.85rem; }
.pie-acceso a { color: var(--soft-red); font-weight: 800; text-decoration: none; }
.error-acceso { background: rgba(255,0,0,0.1); color: var(--soft-red); padding: 10px; border-radius: 6px; font-size: 0.85rem; font-weight: 600; margin-bottom: 20px; border-left: 3px solid var(--primary-red); display: none; }

/* Pestañas (Oyente / Artista) */
.pestanas-rol { display: flex; background: #222; border-radius: 8px; margin-bottom: 20px; overflow: hidden; }
.pestana-rol { flex: 1; text-align: center; padding: 12px; font-size: 0.8rem; font-weight: 800; color: #888; cursor: pointer; transition: 0.3s; }
.pestana-rol.activa { background: var(--primary-red); color: white; }
#campos-artista { display: none; }
</style>

<div class="contenedor-acceso">
    <div class="capa-acceso"></div>
    <div class="caja-formulario">
        <div class="logo-acceso">
            <h2>SoundPlay</h2>
            <p style="color: #aaa; font-size: 0.85rem;">Crea tu nueva cuenta</p>
        </div>

        <div id="error-cliente" class="error-acceso"></div>
        
        <?php if(isset($_GET['error'])): ?>
            <div class="error-acceso" style="display:block;">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <?php 
                    if($_GET['error'] == 'email_taken') echo "Ese email ya está registrado.";
                    else if($_GET['error'] == 'db') echo "Error de servidor al crear la cuenta.";
                    else echo "Ocurrió un error inesperado al registrar.";
                ?>
            </div>
        <?php endif; ?>

        <form action="src/controlador/AuthController.php?action=registro" method="POST" id="formulario-registro">
            
            <div class="pestanas-rol">
                <div class="pestana-rol activa" onclick="asignarRol('user')"><i class="fa-solid fa-headphones"></i> SOY OYENTE</div>
                <div class="pestana-rol" onclick="asignarRol('artista')"><i class="fa-solid fa-microphone"></i> SOY ARTISTA</div>
            </div>
            <input type="hidden" name="rol" id="rol_input" value="user">

            <div class="grupo-formulario">
                <label>CORREO ELECTRÓNICO</label>
                <input type="email" name="email" id="email" required placeholder="tu@email.com">
            </div>

            <div class="grupo-formulario">
                <label>CONTRASEÑA</label>
                <input type="password" name="password" id="password" required placeholder="Mínimo 6 caracteres">
            </div>
            
            <div class="grupo-formulario">
                <input type="password" id="confirmar_password" required placeholder="Repetir contraseña">
            </div>

            <!-- Campos Extras para Artistas -->
            <div id="campos-artista">
                <div class="grupo-formulario">
                    <label>NOMBRE ARTÍSTICO</label>
                    <input type="text" name="nombre_artistico" id="nombre_artistico" placeholder="Ej. Nova Eclipse">
                </div>
                <div class="grupo-formulario">
                    <label>LOCALIDAD</label>
                    <input type="text" name="localidad" id="localidad" placeholder="Ej. Madrid, España">
                </div>
            </div>

            <button type="submit" class="boton-acceso">REGISTRARSE <i class="fa-solid fa-arrow-right"></i></button>
        </form>

        <div class="pie-acceso">
            <p>¿Ya tienes cuenta? <a href="?page=login">Entrar ahora</a></p>
            <p style="margin-top:20px; font-size: 0.65rem; color:#666;">Al registrarte aceptas los T&C y la Política de RGPD.</p>
        </div>
        
        <div style="position: absolute; top:15px; left:20px; z-index:100; font-size: 1.5rem; color:#aaa; cursor:pointer;" onclick="window.history.back();"><i class="fa-solid fa-xmark"></i></div>
    </div>
</div>

<script>
// Manejador JS del cambio de pestaña en Español
function asignarRol(role) {
    document.getElementById('rol_input').value = role;
    const tabs = document.querySelectorAll('.pestana-rol');
    tabs[0].classList.remove('activa');
    tabs[1].classList.remove('activa');
    
    if(role === 'user') {
        tabs[0].classList.add('activa');
        document.getElementById('campos-artista').style.display = 'none';
        document.getElementById('nombre_artistico').removeAttribute('required');
    } else {
        tabs[1].classList.add('activa');
        document.getElementById('campos-artista').style.display = 'block';
        document.getElementById('nombre_artistico').setAttribute('required', 'true');
    }
}

// VALIDACIONES FRONTEND (CLIENTE)
document.getElementById('formulario-registro').addEventListener('submit', function(e) {
    const errorBox = document.getElementById('error-cliente');
    const pwdConfirm = document.getElementById('confirmar_password').value;
    const pwd = document.getElementById('password').value;
    const email = document.getElementById('email').value;
    const rol = document.getElementById('rol_input').value;
    
    errorBox.style.display = 'none';
    let errorMsg = '';

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        errorMsg = 'Formato de correo inválido.';
    } else if (pwd.length < 6) {
        errorMsg = 'La contraseña debe tener mínimo 6 caracteres.';
    } else if (pwd !== pwdConfirm) {
        errorMsg = 'Las contraseñas no coinciden.';
    } else if (rol === 'artista' && document.getElementById('nombre_artistico').value.trim() === '') {
        errorMsg = 'El nombre artístico es obligatorio para artistas.';
    }

    if (errorMsg !== '') {
        e.preventDefault(); // Detiene el flujo de envío del DOM
        errorBox.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> ' + errorMsg;
        errorBox.style.display = 'block';
    }
});
</script>
