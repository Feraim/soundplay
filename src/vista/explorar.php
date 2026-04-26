<style>
/* CSS EN LINEA TEMPORAL PARA MOCKUP 2. Evitando modificar todo el style.css base demasiado rápido */
.explorar-header { font-size: 1.8rem; font-style: italic; font-weight: 900; margin-bottom: 5px; }
.explorar-sub { font-size: 0.8rem; color: #aaa; margin-bottom: 30px; }
.genre-hero { background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.9)), url('assets/img/artist1.jpg') center/cover; padding: 40px 20px; border-radius: 15px; text-align: center; margin-bottom: 15px; position: relative; overflow: hidden; }
.genre-hero::before { content:''; position:absolute; top:0; left:50%; transform:translateX(-50%); width: 200px; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent); }
.genre-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 40px; }
.g-card { height: 120px; border-radius: 12px; position:relative; overflow:hidden; background: #222; }
.g-card img { width: 100%; height: 100%; object-fit: cover; opacity: 0.6; }
.g-card span { position: absolute; bottom: 15px; left: 15px; font-weight: 900; font-style: italic; font-size: 0.9rem; text-shadow: 2px 2px 5px rgba(0,0,0,0.8); }

.trend-item { background: #151515; border-radius: 12px; padding: 15px 20px; margin-bottom: 10px; display: flex; align-items: center; justify-content: space-between; position: relative; overflow: hidden; }
.trend-item::after { content: ''; position:absolute; left:0; top:0; width:3px; height:100%; background: #333; }
.t-num { font-size: 2rem; font-weight: 900; font-style: italic; color: rgba(255,255,255,0.1); width: 40px; }
.t-text h4 { font-size: 0.95rem; font-weight: 800; }
.t-text p { font-size: 0.65rem; color: #888; }
.trend-item i { color: var(--primary-red); }
</style>

<div style="padding-bottom: 80px;">
    <h1 class="explorar-header">EXPLORAR</h1>
    <p class="explorar-sub">Sumérgete en la vibración de cada género musical.</p>

    <!-- GENRE HERO -->
    <div class="genre-hero">
        <span style="font-size: 0.5rem; letter-spacing: 2px; color: var(--soft-red); font-weight: 800; display:block; margin-bottom:5px;">PULSACIÓN CONSTANTE</span>
        <h2 style="font-size: 2.2rem; font-style: italic; font-weight: 900; letter-spacing: 1px;">TECHNO</h2>
    </div>

    <!-- GENRE GRID -->
    <div class="genre-grid">
        <div class="g-card">
            <img src="assets/img/default-album.jpg">
            <span>NEO-SOUL</span>
        </div>
        <div class="g-card" style="background:#401;">
            <img src="assets/img/artist1.jpg">
            <span>TRAP</span>
        </div>
        <div class="g-card" style="background:#631;">
            <img src="assets/img/default-album.jpg">
            <span>INDIE ROCK</span>
        </div>
        <div class="g-card" style="grid-column: 1 / -1; height: 160px;">
            <img src="assets/img/artist1.jpg" style="object-position: top;">
            <span style="top: 20px; transform:translateY(0);">RELAJACIÓN DIGITAL<br><b style="font-size:1.5rem;">LO-FI HIP HOP</b></span>
        </div>
        <div class="g-card">
            <img src="assets/img/default-album.jpg">
            <span>SYNTHWAVE</span>
        </div>
        <div class="g-card">
            <img src="assets/img/artist1.jpg">
            <span>URBANO</span>
        </div>
    </div>

    <!-- TENDENCIAS AHORA -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h3 style="font-size: 1.2rem; font-weight: 800; font-style:italic;">TENDENCIAS AHORA</h3>
        <a href="#" style="color: var(--soft-red); font-size: 0.65rem; font-weight: 800; text-decoration: none; letter-spacing:1px;">VER TODO</a>
    </div>

    <div class="trend-list">
        <div class="trend-item">
            <div style="display:flex; align-items:center; gap:15px;">
                <span class="t-num">01</span>
                <div class="t-text">
                    <h4>Hyperpop</h4>
                    <p>2.4M oyentes esta semana</p>
                </div>
            </div>
            <i class="fa-solid fa-arrow-trend-up"></i>
        </div>
        <div class="trend-item">
            <div style="display:flex; align-items:center; gap:15px;">
                <span class="t-num">02</span>
                <div class="t-text">
                    <h4>Dark Techno</h4>
                    <p>1.8M oyentes esta semana</p>
                </div>
            </div>
            <i class="fa-solid fa-arrow-trend-up"></i>
        </div>
        <div class="trend-item">
            <div style="display:flex; align-items:center; gap:15px;">
                <span class="t-num">03</span>
                <div class="t-text">
                    <h4>Phonk</h4>
                    <p>900K oyentes esta semana</p>
                </div>
            </div>
            <i class="fa-solid fa-arrow-trend-up"></i>
        </div>
    </div>

</div>
