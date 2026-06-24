/* 
   main.js — SoundPlay
   Contiene toda la lógica JavaScript de la aplicación:
   1. Menú hamburguesa
   2. Reproductor global estilo Spotify (funciona en género, perfil y búsqueda)
   3. Accordion de álbumes (páginas de género y perfil de artista)*/


/* --------------------------------------------------------------------------
   1. MENÚ HAMBURGUESA
   Abre y cierra el menú lateral de navegación
   -------------------------------------------------------------------------- */
(function () {
    var btn = document.querySelector('.menu-hamburguesa');
    var nav = document.querySelector('.nav-menu');
    if (!btn || !nav) return;

    btn.addEventListener('click', function () {
        nav.classList.toggle('active');
        btn.classList.toggle('open');
    });
})();


/* --------------------------------------------------------------------------
   2. REPRODUCTOR GLOBAL
   Gestiona la reproducción de audio en todas las páginas que tengan
   el elemento #sp-player en el DOM.

   Tipos de elementos reproducibles (selector unificado):
     - .track-row           → filas de tracklist en páginas de género y perfil
     - .buscar-resultado-card → tarjetas de resultados de búsqueda

   En ambos casos los datos están en atributos data-*:
     data-src, data-titulo, data-artista, data-portada
   -------------------------------------------------------------------------- */
(function () {
    /* -- Referencias al DOM del reproductor -- */
    var audio = document.getElementById('sp-audio'); //etiqueta audio
    if (!audio) return; // No existe reproductor en esta página

    var player        = document.getElementById('sp-player');
    var playPauseBtn  = document.getElementById('sp-play-pause');
    var prevBtn       = document.getElementById('sp-prev');
    var nextBtn       = document.getElementById('sp-next');
    var progressFill  = document.getElementById('sp-progress-fill');
    var progressThumb = document.getElementById('sp-progress-thumb');
    var progressBar   = document.getElementById('sp-progress-bar');
    var currentTimeEl = document.getElementById('sp-current');
    var durationEl    = document.getElementById('sp-duration');
    var spTitulo      = document.getElementById('sp-titulo');
    var spArtista     = document.getElementById('sp-artista');
    var spCover       = document.getElementById('sp-cover');
    var volumeSlider  = document.getElementById('sp-volume');

    /* Selector unificado: abarca los dos tipos de elementos reproducibles */
    var ITEM_SEL = '.track-row, .buscar-resultado-card'; //constantes
    var BTN_SEL  = '.track-play-btn, .buscar-play-btn'; //constantes

    var items        = []; // Cola de reproducción actual
    var currentIndex = -1; // Índice del ítem en reproducción (-1 = ninguno)
    var currentBtn   = null; // Botón play del ítem activo

    /* Reconstruye la cola leyendo el DOM (se llama tras abrir un tracklist) */
    function buildQueue() {
        items = Array.from(document.querySelectorAll(ITEM_SEL));
        //busca todos los elementos que coincidan con el selector y devuelve un NodeList, 
        //pero con Array.from lo conviertes en un array 
    }
    buildQueue();

    /* Convierte segundos a formato m:ss */
    function fmt(s) {
        if (isNaN(s) || !isFinite(s)) return '0:00';
        var m = Math.floor(s / 60);
        var sec = Math.floor(s % 60);
        return m + ':' + (sec < 10 ? '0' : '') + sec;
    }

    /* Restablece visualmente el ítem activo al estado "parado" */
    function resetCurrent() {
        if (!currentBtn) return; 
        //al principio estará vacío, cunado escuhemos alguna cancion
        //si funcionaŕa
        currentBtn.querySelector('.icon-play').style.display  = ''; 
        //busca el icono play dentro del botón activo, lo hace visible (quita el display:none)
        currentBtn.querySelector('.icon-pause').style.display = 'none';
        //oculta el icono pause
        var item = currentBtn.closest(ITEM_SEL);
        //sube hasta truck.row
        if (item) item.classList.remove('playing');
        //si item existe borra la clase llamada playing
    }

    /* Carga un ítem en el reproductor por su índice en la cola.
       Si autoplay es true, comienza la reproducción inmediatamente. */
    function loadTrack(index, autoplay) {
        if (index < 0 || index >= items.length) return;
        //si el indice de la cancion es menor que o es mayor que el total de canciones 
        //no hace nada
        var item = items[index];
        //En esta variable guardo

        resetCurrent(); // Limpiar estado del ítem anterior
        currentIndex = index; //La lista empezará por este índice
        currentBtn   = item.querySelector(BTN_SEL);
        //Saca el elemento de la cola, limpia el anterior, guarda el nuevo índice y botón
        /* Asignar el archivo de audio al elemento <audio> nativo */
        audio.src = item.dataset.src;
        //audio es una referencia a la etiqueta audio
        //src define qué archivo va a sonar

        /* Actualizar información en el reproductor inferior */
        spTitulo.textContent  = item.dataset.titulo  || '—'; //lee el data-titulo del HTML 
        spArtista.textContent = item.dataset.artista || '—'; //cambia el texto del <span id="sp-titulo">
        //Por defecto muestran — (guión). Cuando el JavaScript carga una canción, reemplaza ese guión con el título y artista reales

        /* Actualizar la miniatura de portada */
        spCover.innerHTML = '';
        if (item.dataset.portada) {
            var img = document.createElement('img');
            img.src = item.dataset.portada;
            img.alt = 'Portada';
            spCover.appendChild(img);
        } else {
            /* Icono musical genérico cuando no hay portada */
            spCover.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="#555" viewBox="0 0 16 16"><path d="M9 13c0 1.105-1.12 2-2.5 2S4 14.105 4 13s1.12-2 2.5-2 2.5.895 2.5 2"/><path fill-rule="evenodd" d="M9 3v10H8V3z"/><path d="M8 2.82a1 1 0 0 1 .804-.98l3-.6A1 1 0 0 1 13 2.22V4L8 5z"/></svg>';
        }

        /* Mostrar la barra del reproductor con animación (.visible en CSS) */
        player.classList.add('visible');

        if (autoplay) {
            audio.play().catch(function () {});
            setPlaying(true);
            item.classList.add('playing');
            currentBtn.querySelector('.icon-play').style.display  = 'none';
            currentBtn.querySelector('.icon-pause').style.display = '';
        }
    }

    /* Sincroniza los iconos play/pause del botón central del reproductor */
    function setPlaying(playing) {
        playPauseBtn.querySelector('.icon-play').style.display  = playing ? 'none' : '';
        playPauseBtn.querySelector('.icon-pause').style.display = playing ? ''     : 'none';
    }

    /* -- Delegación de eventos: clic en cualquier botón de reproducción -- */
    document.addEventListener('click', function (e) {
        var btn = e.target.closest(BTN_SEL);
        if (!btn) return;

        buildQueue(); // Actualizar la cola por si el DOM ha cambiado
        var item  = btn.closest(ITEM_SEL);
        var index = items.indexOf(item);
        if (index === -1) return;

        if (currentIndex === index) {
            /* Mismo ítem → alternar play/pause */
            if (audio.paused) {
                audio.play().catch(function () {});
                setPlaying(true);
                item.classList.add('playing');
                btn.querySelector('.icon-play').style.display  = 'none';
                btn.querySelector('.icon-pause').style.display = '';
            } else {
                audio.pause();
                setPlaying(false);
                item.classList.remove('playing');
                btn.querySelector('.icon-play').style.display  = '';
                btn.querySelector('.icon-pause').style.display = 'none';
            }
        } else {
            /* Ítem diferente → cargar y reproducir */
            loadTrack(index, true);
        }
    });

    /* Botón play/pause central del reproductor inferior */
    playPauseBtn.addEventListener('click', function () {
        if (audio.paused) {
            audio.play().catch(function () {});
            setPlaying(true);
            if (currentIndex >= 0) {
                items[currentIndex].classList.add('playing');
                var b = items[currentIndex].querySelector(BTN_SEL);
                b.querySelector('.icon-play').style.display  = 'none';
                b.querySelector('.icon-pause').style.display = '';
            }
        } else {
            audio.pause();
            setPlaying(false);
            if (currentIndex >= 0) {
                items[currentIndex].classList.remove('playing');
                var b = items[currentIndex].querySelector(BTN_SEL);
                b.querySelector('.icon-play').style.display  = '';
                b.querySelector('.icon-pause').style.display = 'none';
            }
        }
    });

    /* Botón anterior: si la canción lleva más de 3s reinicia, si no va a la pista previa */
    prevBtn.addEventListener('click', function () {
        if (audio.currentTime > 3) {
            audio.currentTime = 0;
        } else {
            loadTrack(currentIndex - 1, true);
        }
    });

    /* Botón siguiente: carga la pista siguiente en la cola */
    nextBtn.addEventListener('click', function () {
        loadTrack(currentIndex + 1, true);
    });

    /* Al terminar una canción, avanza automáticamente a la siguiente */
    audio.addEventListener('ended', function () {
        resetCurrent();
        setPlaying(false);
        loadTrack(currentIndex + 1, true);
    });

    /* Actualizar barra de progreso y tiempo transcurrido en cada tick */
    audio.addEventListener('timeupdate', function () {
        if (!audio.duration) return;
        var pct = (audio.currentTime / audio.duration) * 100;
        progressFill.style.width  = pct + '%';
        progressThumb.style.left  = pct + '%';
        currentTimeEl.textContent = fmt(audio.currentTime);
    });

    /* Mostrar la duración total al cargar los metadatos del archivo */
    audio.addEventListener('loadedmetadata', function () {
        durationEl.textContent = fmt(audio.duration);
    });

    /* Clic en la barra de progreso para saltar a ese punto del audio */
    progressBar.addEventListener('click', function (e) {
        var rect = progressBar.getBoundingClientRect();
        audio.currentTime = ((e.clientX - rect.left) / rect.width) * audio.duration;
    });

    /* Slider de volumen */
    volumeSlider.addEventListener('input', function () {
        audio.volume = volumeSlider.value;
    });

    /* Botón X: se crea dinámicamente y se añade al reproductor.
       Al hacer clic pausa el audio y oculta la barra deslizándola hacia abajo. */
    var closeBtn = document.createElement('button');
    closeBtn.className = 'sp-close-btn';
    closeBtn.setAttribute('aria-label', 'Cerrar reproductor');
    closeBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/></svg>';
    player.appendChild(closeBtn);

    closeBtn.addEventListener('click', function () {
        audio.pause();           // Detener la reproducción
        setPlaying(false);       // Cambiar icono del botón central a "play"
        resetCurrent();          // Quitar clase 'playing' y restaurar icono de la fila
        currentIndex = -1;       // Resetear índice para que no quede ninguna pista activa
        player.classList.remove('visible'); // Ocultar el reproductor con animación
    });
})();


/* --------------------------------------------------------------------------
   3. ACCORDION DE ÁLBUMES
   Muestra y oculta la lista de canciones al hacer clic en la portada del álbum.
   Solo se activa si existen tarjetas .album-card en el DOM (género y perfil).
   -------------------------------------------------------------------------- */
(function () {
    var cards      = document.querySelectorAll('.album-card');
    var cerrarBtns = document.querySelectorAll('.album-tracklist-cerrar');
    if (!cards.length) return; // No hay álbumes en esta página

    /* Clic en la tarjeta del álbum: abre su tracklist y cierra los demás */
    cards.forEach(function (card) {
        card.addEventListener('click', function () {
            var id     = card.dataset.albumId;
            var tl     = document.getElementById('tracklist-' + id);
            var isOpen = tl.classList.contains('open');

            /* Cerrar todos los tracklists y desactivar todas las tarjetas */
            document.querySelectorAll('.album-tracklist').forEach(function (t) {
                t.classList.remove('open');
            });
            document.querySelectorAll('.album-card').forEach(function (c) {
                c.classList.remove('active');
            });

            /* Si estaba cerrado, abrirlo y hacer scroll hacia él */
            if (!isOpen) {
                tl.classList.add('open');
                card.classList.add('active');
                tl.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    /* Botón X dentro del tracklist: cierra ese panel específico */
    cerrarBtns.forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.stopPropagation(); // Evitar que el clic llegue a la tarjeta del álbum
            var id   = btn.dataset.albumId;
            var tl   = document.getElementById('tracklist-' + id);
            var card = document.querySelector('.album-card[data-album-id="' + id + '"]');
            if (tl)   tl.classList.remove('open');
            if (card) card.classList.remove('active');
        });
    });
})();
