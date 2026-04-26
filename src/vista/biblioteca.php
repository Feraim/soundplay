<style>
.biblio-header { font-size: 2.2rem; font-weight: 900; margin-bottom: 20px; }
.b-filters { display: flex; gap: 15px; margin-bottom: 25px; }
.b-filter { background: #222; padding: 10px 20px; border-radius: 8px; font-size: 0.8rem; font-weight: 800; color: #aaa; cursor: pointer; }
.b-filter.active { background: var(--soft-red); color: white; }

.liked-card { background: var(--soft-red); border-radius: 12px; padding: 30px 25px; margin-bottom: 20px; position: relative; overflow: hidden; }
.liked-card h2 { font-size: 1.8rem; font-weight: 900; margin-bottom: 5px; color: white; }
.liked-card p { font-size: 0.85rem; color: rgba(255,255,255,0.8); }
.liked-icon { background: rgba(255,255,255,0.2); width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 10px; position:absolute; top: 20px; right: 20px; }
.liked-icon i { color: white; font-size: 1.2rem; }

.create-p { background: #151515; border-radius: 12px; padding: 25px; display: flex; align-items: center; gap: 20px; margin-bottom: 40px; position: relative; }
.plus-icon { background: var(--primary-red); color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
.create-p h4 { font-size: 1.2rem; font-weight: 800; margin-bottom: 5px; }
.n-badge { position: absolute; top: 15px; right: 20px; font-size: 0.6rem; color: var(--soft-red); font-weight: 900; letter-spacing: 1px; }

.horiz-scroll { display: flex; gap: 20px; overflow-x: auto; padding-bottom: 20px; margin-bottom: 10px; }
.horiz-scroll::-webkit-scrollbar { display: none; }
.a-circle { width: 90px; text-align: center; flex-shrink: 0; }
.a-circle img { width: 90px; height: 90px; border-radius: 12px; object-fit: cover; margin-bottom: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.5); }
.a-circle p { font-size: 0.8rem; font-weight: 800; }

.p-item { background: #151515; border-radius: 12px; padding: 15px; display: flex; align-items: center; gap: 15px; margin-bottom: 12px; }
.p-item img { width: 60px; height: 60px; border-radius: 8px; }
.p-item h4 { font-size: 1rem; font-weight: 800; margin-bottom: 5px; }
.p-item p { font-size: 0.7rem; color: #888; }
</style>

<div style="padding-bottom: 80px;">
    <h1 class="biblio-header">Mi Biblioteca</h1>

    <div class="b-filters">
        <div class="b-filter active">Playlists</div>
        <div class="b-filter">Artistas</div>
        <div class="b-filter">Álbumes</div>
    </div>

    <!-- TUS ME GUSTA CARD -->
    <div class="liked-card">
        <div class="liked-icon"><i class="fa-solid fa-heart"></i></div>
        <div style="margin-top: 50px;">
            <h2>Tus Me gusta</h2>
            <p>1,248 canciones guardadas</p>
        </div>
    </div>

    <div class="create-p">
        <span class="n-badge">NUEVO</span>
        <div class="plus-icon"><i class="fa-solid fa-plus"></i></div>
        <div>
            <h4>Crear Playlist</h4>
            <p style="font-size:0.75rem; color:#888;">Empieza algo nuevo</p>
        </div>
    </div>

    <!-- ARTISTAS SEGUIDOS -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3 style="font-size: 1.2rem; font-weight: 800;">Artistas seguidos</h3>
        <a href="#" style="color: var(--soft-red); font-size: 0.7rem; font-weight: 800; text-decoration: none;">Ver todos</a>
    </div>

    <div class="horiz-scroll">
        <div class="a-circle">
            <img src="assets/img/default-album.jpg">
            <p>Jazz Core</p>
        </div>
        <div class="a-circle">
            <img src="assets/img/artist1.jpg">
            <p>Neo-Soul</p>
        </div>
        <div class="a-circle">
            <img src="assets/img/default-album.jpg">
            <p>Velvet Vox</p>
        </div>
        <div class="a-circle">
            <img src="assets/img/artist1.jpg">
            <p>Siro Luna</p>
        </div>
    </div>

    <!-- TUS PLAYLISTS -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3 style="font-size: 1.2rem; font-weight: 800;">Tus Playlists</h3>
        <div style="color: #aaa; font-size: 1rem; display:flex; gap:15px;">
            <i class="fa-solid fa-magnifying-glass"></i>
            <i class="fa-solid fa-arrow-down-wide-short"></i>
        </div>
    </div>

    <div class="p-item">
        <img src="assets/img/artist1.jpg">
        <div>
            <h4>Energía Nocturna</h4>
            <p>Playlist • SoundPlay • 48 canciones</p>
        </div>
    </div>
    
    <div class="p-item">
        <img src="assets/img/default-album.jpg">
        <div>
            <h4>Concierto en Vivo</h4>
            <p>Playlist • Tú • 12 canciones</p>
        </div>
    </div>

    <div class="p-item">
        <img src="assets/img/artist1.jpg" style="filter: hue-rotate(90deg);">
        <div>
            <h4>Lo-Fi Crimson</h4>
            <p>Playlist • SoundPlay • 120 canciones</p>
        </div>
    </div>

</div>
