<div class="scroll-principal" style="display:flex; flex-direction:column; justify-content:center; align-items:center; height:100%;">
    <div style="background:var(--bg-card); padding: 40px; border-radius: 15px; text-align:center; width: 100%; max-width: 400px; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
        <i class="fa-solid fa-compact-disc" style="font-size: 3rem; color: var(--primary-red); margin-bottom: 20px;"></i>
        <h2 style="margin-bottom: 10px;">Iniciar Sesión</h2>
        <p style="color:var(--text-gray); margin-bottom: 30px; font-size: 0.9rem;">Accede a tu panel para gestionar tu contenido y estadísticas.</p>
        
        <?php if(isset($_GET['error'])): ?>
            <p style="color:var(--soft-red); font-size:0.85rem; padding: 10px; border:1px solid var(--soft-red); border-radius:6px; margin-bottom:20px;">Credenciales incorrectas.</p>
        <?php endif; ?>

        <form action="src/controlador/AuthController.php" method="POST" style="display:flex; flex-direction:column; gap: 20px;">
            <div style="text-align:left;">
                <label style="font-size:0.8rem; color:var(--text-gray); font-weight:bold;">CORREO ELECTRÓNICO</label>
                <input type="email" name="email" value="nova@eclipse.com" required style="width:100%; padding: 12px; background:#111; border:1px solid #333; border-radius:8px; color:white; margin-top:5px;">
            </div>
            <div style="text-align:left;">
                <label style="font-size:0.8rem; color:var(--text-gray); font-weight:bold;">CONTRASEÑA</label>
                <input type="password" name="password" value="123456" required style="width:100%; padding: 12px; background:#111; border:1px solid #333; border-radius:8px; color:white; margin-top:5px;">
            </div>
            
            <button type="submit" style="background:var(--primary-red); color:white; border:none; padding:15px; border-radius:30px; font-weight:bold; font-size:1rem; cursor:pointer; margin-top: 10px; transition:0.3s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">ENTRAR</button>
        </form>
    </div>
</div>
