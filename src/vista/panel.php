<?php
if(!isset($_SESSION['rol'])) {
    include 'login.php';
    return;
}
?>

<div style="padding: 20px; padding-bottom: 80px;">
    
    <div style="text-align: center; margin-bottom: 25px;">
        <p style="color: var(--primary-red); font-weight: 800; font-size: 0.7rem; letter-spacing: 2px; margin-bottom: 5px;">ARTIST DASHBOARD</p>
        <h2 style="font-size: 1.8rem; line-height: 1.1; font-weight: 900;">
            BIENVENIDO,<br> <span style="color: var(--primary-red);">NOVA ECLIPSE</span>
        </h2>
    </div>

    <!-- IMAGEN CENTRAL -->
    <div style="height: 250px; background: url('assets/img/artist1.jpg') center/cover; border-radius: 12px; margin-bottom: 30px; box-shadow: 0 10px 20px rgba(0,0,0,0.8);"></div>

    <!-- ESTADÍSTICAS -->
    <div style="background: var(--bg-card); border-radius: 12px; padding: 25px; margin-bottom: 25px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 1.2rem; font-weight: 800;">ESTADÍSTICAS</h3>
            <span style="background: #222; font-size: 0.6rem; padding: 4px 10px; border-radius: 20px; color: #888;">ÚLTIMOS 30 DÍAS</span>
        </div>
        
        <div style="margin-bottom: 20px;">
            <p style="font-size: 0.7rem; color: var(--text-gray); font-weight: 600;">ESCUCHAS TOTALES</p>
            <p style="font-size: 2rem; font-weight: 900; margin: 5px 0;">1.2M</p>
            <p style="font-size: 0.7rem; color: var(--primary-red);"><i class="fa-solid fa-arrow-trend-up"></i> +12%</p>
        </div>
        
        <div style="margin-bottom: 20px;">
            <p style="font-size: 0.7rem; color: var(--text-gray); font-weight: 600;">OYENTES MENSUALES</p>
            <p style="font-size: 2rem; font-weight: 900; margin: 5px 0;">84.5K</p>
            <p style="font-size: 0.7rem; color: var(--primary-red);"><i class="fa-solid fa-arrow-trend-up"></i> +6.2%</p>
        </div>

        <div style="margin-bottom: 25px;">
            <p style="font-size: 0.7rem; color: var(--text-gray); font-weight: 600;">GUARDADOS</p>
            <p style="font-size: 2rem; font-weight: 900; margin: 5px 0;">12.8K</p>
            <p style="font-size: 0.7rem; color: var(--primary-red);"><i class="fa-solid fa-arrow-trend-up"></i> +22%</p>
        </div>

        <!-- SIMULACION GRAFICA BARRAS -->
        <div style="display: flex; align-items: flex-end; justify-content: space-between; height: 60px; border-bottom: 1px solid #333;">
            <div style="width: 12%; background: linear-gradient(to top, rgba(255,0,0,0.8), rgba(255,0,0,0.1)); height: 40%;"></div>
            <div style="width: 12%; background: linear-gradient(to top, rgba(255,0,0,0.8), rgba(255,0,0,0.1)); height: 60%;"></div>
            <div style="width: 12%; background: linear-gradient(to top, rgba(255,0,0,0.8), rgba(255,0,0,0.1)); height: 30%;"></div>
            <div style="width: 12%; background: linear-gradient(to top, rgba(255,0,0,0.8), rgba(255,0,0,0.1)); height: 80%;"></div>
            <div style="width: 12%; background: linear-gradient(to top, rgba(255,0,0,0.8), rgba(255,0,0,0.1)); height: 100%;"></div>
            <div style="width: 12%; background: linear-gradient(to top, rgba(255,0,0,0.8), rgba(255,0,0,0.1)); height: 45%;"></div>
            <div style="width: 12%; background: linear-gradient(to top, rgba(255,0,0,0.8), rgba(255,0,0,0.1)); height: 65%;"></div>
        </div>
    </div>

    <!-- ALMACENAMIENTO -->
    <div style="background: var(--bg-card); border-radius: 12px; padding: 25px; margin-bottom: 25px;">
        <h3 style="font-size: 1.2rem; font-weight: 800; margin-bottom: 20px;">ALMACENAMIENTO</h3>
        
        <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
            <span style="font-size: 0.7rem; color: var(--text-gray);">ESPACIO EN USO</span>
            <span style="font-size: 0.7rem; font-weight: 800;">8.4 GB / 15 GB</span>
        </div>

        <div style="width: 100%; background: #222; height: 6px; border-radius: 4px; margin-bottom: 20px; overflow: hidden; position: relative;">
            <div style="position: absolute; left: 0; top: 0; height: 100%; width: 55%; background: var(--primary-red);"></div>
        </div>

        <ul style="list-style: none; padding: 0; font-size: 0.65rem; color: var(--text-gray); display: flex; flex-direction: column; gap: 8px; margin-bottom: 25px;">
            <li style="display: flex; align-items: center; gap: 8px;"><div style="width: 6px; height: 6px; border-radius: 50%; background: var(--primary-red);"></div> AUDIO MASTER (WAV) - 5.2 GB</li>
            <li style="display: flex; align-items: center; gap: 8px;"><div style="width: 6px; height: 6px; border-radius: 50%; background: #ffaaaa;"></div> ARTE Y GRÁFICOS - 1.2 GB</li>
            <li style="display: flex; align-items: center; gap: 8px;"><div style="width: 6px; height: 6px; border-radius: 50%; background: #888;"></div> OTROS - 1.0 GB</li>
        </ul>

        <button style="width: 100%; padding: 12px; background: #000; border: none; font-weight: 800; color: #ffaaaa; letter-spacing: 1px; font-size: 0.8rem; border-radius: 4px;">MEJORAR PLAN</button>
    </div>

    <!-- SUBIDA DE MÚSICA -->
    <h3 style="font-size: 1.2rem; font-weight: 800; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;"><i class="fa-solid fa-cloud-arrow-up" style="color:var(--primary-red)"></i> SUBIDA DE MÚSICA</h3>
    <form id="upload-form" action="src/controlador/CancionController.php" method="POST" enctype="multipart/form-data">
        <div style="border: 2px dashed #333; padding: 40px 20px; text-align: center; border-radius: 12px; margin-bottom: 25px; transition: 0.3s;" id="drop-zone">
            <div style="background: #222; width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px auto;">
                <i class="fa-solid fa-file-audio" style="color: var(--primary-red); font-size: 1.5rem;"></i>
            </div>
            <h4 style="font-weight: 800; font-size: 0.9rem; margin-bottom: 5px;">ARRASTRA TU ARCHIVO MASTER</h4>
            <p style="font-size: 0.7rem; color: #777; margin-bottom: 20px;">Formatos aceptados: WAV, FLAC, AIFF (Máx. 500MB)</p>
            
            <label for="archivo" style="background: var(--soft-red); color: white; padding: 10px 25px; font-size: 0.8rem; font-weight: 800; display: inline-block; cursor: pointer; border-radius: 4px;">EXPLORAR ARCHIVOS</label>
            <input type="file" id="archivo" name="archivo" accept="audio/*" style="display: none;">
        </div>
        <p id="upload-feedback" style="font-size: 0.8rem; text-align:center; font-weight: bold; margin-bottom:10px;"></p>

        <!-- CRÉDITOS TÉCNICOS -->
        <h3 style="font-size: 1.2rem; font-weight: 800; margin-bottom: 20px;">CRÉDITOS TÉCNICOS</h3>
        <div style="margin-bottom: 15px;">
            <label style="font-size: 0.65rem; color: #888; font-weight: 600; display: block; margin-bottom: 8px;">NOMBRE COMPLETO</label>
            <input type="text" name="titulo" placeholder="Ej. Julian Casablancas" style="width: 100%; background: #181818; border: 1px solid #222; padding: 15px; color: white; border-radius: 6px;">
        </div>
        <div style="margin-bottom: 25px;">
            <label style="font-size: 0.65rem; color: #888; font-weight: 600; display: block; margin-bottom: 8px;">ROL / FUNCIÓN</label>
            <select name="rol" style="width: 100%; background: #181818; border: 1px solid #222; padding: 15px; color: white; border-radius: 6px; appearance: none;">
                <option>Productor Principal</option>
                <option>Ingeniero de Mezcla</option>
            </select>
        </div>

        <div style="display: flex; gap: 10px;">
            <button type="button" style="flex: 1; padding: 15px; background: #222; color: #ddd; border: none; font-weight: 800; border-radius: 6px; font-size: 0.8rem;">AÑADIR</button>
            <button type="submit" style="flex: 1; padding: 15px; background: var(--primary-red); color: white; border: none; font-weight: 800; border-radius: 6px; font-size: 0.8rem;">GUARDAR CRÉDITOS</button>
        </div>
        
        <div style="text-align: center; margin-top: 20px;">
            <a href="src/controlador/AuthController.php?action=logout" style="color: #666; font-size:0.8rem; font-weight:bold; text-decoration:none;"><i class="fa-solid fa-power-off"></i> CERRAR SESIÓN</a>
        </div>
    </form>
</div>

<script>
// Mock de Drag and drop file select visualization
document.getElementById('archivo').addEventListener('change', function() {
    if(this.files.length > 0) {
        document.getElementById('upload-feedback').innerText = "Archivo seleccionado: " + this.files[0].name;
        document.getElementById('upload-feedback').style.color = "#4caf50";
    }
});
</script>
