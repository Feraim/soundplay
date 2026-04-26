<?php
$path_to_db = __DIR__ . '/../../config/db.php';
if (file_exists($path_to_db)) {
    require_once $path_to_db;
    $database = new Database();
    $db = $database->getConnection();
    
    // Obtener los artistas más recientes
    $query = "SELECT id_artista, nombre_artistico, localidad, foto_perfil FROM artistas ORDER BY id_artista DESC LIMIT 4";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $artistas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $artistas = [];
}
?>
<!-- Vista de Inicio (Home) -->
<style>
/* Estilos en línea específicos de la vista de inicio */
.contenedor-seccion { margin-bottom: 30px; }
.cabecera-seccion { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
.cabecera-seccion h3 { font-size: 1.1rem; font-weight: 900; }
.texto-boton { color: var(--soft-red); font-size: 0.7rem; font-weight: 800; text-decoration: none; }
.resaltado { color: var(--primary-red); }
.etiqueta { background: var(--primary-red); color: white; padding: 4px 8px; font-size: 0.65rem; font-weight: 900; border-radius: 4px; margin-bottom: 10px; display: inline-block; }
.etiquetas-genero { display: flex; gap: 10px; flex-wrap: wrap; }
.chip { padding: 8px 15px; background: #222; border-radius: 20px; font-size: 0.75rem; font-weight: 800; cursor: pointer; color: #aaa; transition: 0.3s; }
.chip:hover, .chip.activo { background: var(--primary-red); color: white; }
</style>

<div class="scroll-principal">
    <!-- Banner de héroe superior -->
    <header class="banner-principal">
        <div class="sombra-banner"></div>
        <div class="texto-banner">
            <span class="etiqueta">EN TENDENCIA</span>
            <h2>Radar Urbano: <span class="resaltado">Ciudad de México</span></h2>
            <p>Descubre los beats que están redefiniendo las calles.</p>
        </div>
    </header>

    <!-- Sección de recomendados -->
    <section class="contenedor-seccion">
        <div class="cabecera-seccion">
            <h3>ARTISTAS <span class="resaltado">CERCA DE TI</span></h3>
            <a href="#" class="texto-boton">VER TODOS</a>
        </div>
        <!-- Rejilla que carga tarjetas desde BD -->
        <div id="contenedor-artistas" class="rejilla-artistas">
            <?php if (!empty($artistas)): ?>
                <?php foreach ($artistas as $artista): ?>
                    <div class="tarjeta-artista" onclick="navegar('detalle_artista&id=<?= $artista['id_artista'] ?>')">
                        <div class="contenedor-avatar">
                            <?php 
                                $foto = !empty($artista['foto_perfil']) ? $artista['foto_perfil'] : 'assets/img/default-album.jpg';
                            ?>
                            <img src="<?= htmlspecialchars($foto) ?>" alt="Artista">
                        </div>
                        <div class="info-etiqueta">
                            <h3><?= htmlspecialchars($artista['nombre_artistico']) ?></h3>
                            <p><?= htmlspecialchars($artista['localidad'] ?: 'Desconocida') ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color:var(--text-gray); font-size:0.9rem;">Aún no hay artistas registrados.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Filtros de géneros de moda -->
    <section class="contenedor-seccion">
        <h3>GÉNEROS <span class="resaltado">POPULARES</span></h3>
        <div class="etiquetas-genero" style="margin-top:15px;">
            <span class="chip activo">TECHNO</span>
            <span class="chip">NEO-SOUL</span>
            <span class="chip">TRAP MX</span>
            <span class="chip">INDIE-POP</span>
        </div>
    </section>
</div>