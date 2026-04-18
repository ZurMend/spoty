// ============================================================
//  app.js — Soundwave Frontend v5
// ============================================================

// ── Calcular BASE URL automáticamente ────────────────────────
(function() {
    var path = window.location.pathname;
    var idx  = path.indexOf('/frontend/');
    if (idx !== -1) {
        window.SW_BASE = window.location.origin + path.substring(0, idx);
    } else if (window.SW_BASE === undefined) {
        window.SW_BASE = window.location.origin;
    }
})();
var BASE = window.SW_BASE;

// ── Estado global del reproductor ────────────────────────────
var Player = {
    audio:      new Audio(),
    queue:      [],       // canciones en cola
    index:      -1,       // índice actual
    shuffle:    false,    // orden aleatorio
    repeatMode: 'none',   // 'none' | 'one' | 'all'
    autoplay:   true,     // pasar a siguiente automáticamente
};

// ── Toast ─────────────────────────────────────────────────────
function toast(msg, type, ms) {
    type = type || 'info'; ms = ms || 3000;
    var c = document.getElementById('toast-container');
    if (!c) return;
    var el = document.createElement('div');
    el.className = 'toast ' + type;
    el.textContent = msg;
    c.appendChild(el);
    setTimeout(function(){ if(el.parentNode) el.parentNode.removeChild(el); }, ms);
}

// ── Tiempo mm:ss ──────────────────────────────────────────────
function fmtTime(secs) {
    secs = Math.floor(secs || 0);
    return Math.floor(secs/60) + ':' + String(secs%60).padStart(2,'0');
}

// ── Escape HTML ───────────────────────────────────────────────
function esc(str) {
    return String(str||'').replace(/&/g,'&amp;').replace(/</g,'&lt;')
           .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ── API fetch ─────────────────────────────────────────────────
async function api(url, opts) {
    opts = opts || {};
    opts.headers = opts.headers || {};
    opts.headers['X-Requested-With'] = 'XMLHttpRequest';
    var res, text;
    try {
        res  = await fetch(url, opts);
        text = await res.text();
    } catch(e) {
        toast('Error de red: ' + e.message, 'error');
        throw e;
    }
    var data;
    try {
        data = JSON.parse(text);
    } catch(e) {
        console.error('No-JSON de:', url, '\n', text.substring(0,500));
        toast('Error del servidor (' + res.status + '). Ver consola.', 'error');
        throw new Error(text.substring(0,200));
    }
    if (data && data.redirect) { window.location.href = data.redirect; return null; }
    if (data && data.error)    { toast(data.error, 'error'); throw new Error(data.error); }
    return data;
}

// ── Reproducir canción ────────────────────────────────────────
async function playSong(song, queue, idx) {
    if (queue !== undefined && queue !== null) {
        Player.queue = queue;
        Player.index = (idx !== undefined && idx !== null) ? idx : 0;
    } else if (!Player.queue.length) {
        Player.queue = [song];
        Player.index = 0;
    }

    Player.audio.src = song.archivo_url || song.url || '';
    try { await Player.audio.play(); } catch(e) {}
    updatePlayerUI(song);
    markPlaying(song.id);
    updateNowPlayingLabel(song);

    // Historial
    var fd = new FormData();
    fd.append('song_id', song.id);
    fetch(BASE + '/backend/songs.php?action=history', {
        method:'POST', body:fd,
        headers:{'X-Requested-With':'XMLHttpRequest'}
    }).catch(function(){});

    // Recomendaciones
    loadRecommendations(song);
}

// ── UI del reproductor ────────────────────────────────────────
function updatePlayerUI(song) {
    var nEl = document.getElementById('player-song-name');
    var aEl = document.getElementById('player-song-artist');
    var img = document.getElementById('player-thumb-img');
    var nth = document.getElementById('player-no-thumb');
    if (nEl) nEl.textContent = song.nombre  || '—';
    if (aEl) aEl.textContent = song.artista || '—';
    if (img) {
        if (song.imagen_url) {
            img.src = song.imagen_url; img.style.display = '';
            if (nth) nth.style.display = 'none';
        } else {
            img.style.display = 'none';
            if (nth) nth.style.display = '';
        }
    }
}

function updateNowPlayingLabel(song) {
    var lbl = document.getElementById('now-playing-label');
    if (lbl) lbl.textContent = '▶ ' + (song.nombre||'') + ' — ' + (song.artista||'');
}

function markPlaying(songId) {
    document.querySelectorAll('[data-id]').forEach(function(el){
        el.classList.toggle('playing', parseInt(el.dataset.id) === parseInt(songId));
    });
}

// ── Controles del reproductor ─────────────────────────────────
document.addEventListener('DOMContentLoaded', function(){
    var audio   = Player.audio;
    var fillEl  = document.getElementById('progress-fill');
    var wrapEl  = document.getElementById('progress-wrap');
    var curEl   = document.getElementById('time-current');
    var endEl   = document.getElementById('time-end');
    var volEl   = document.getElementById('volume-slider');
    var btnPlay = document.getElementById('btn-play-pause');
    var btnPrev = document.getElementById('btn-prev');
    var btnNext = document.getElementById('btn-next');
    var btnShuf = document.getElementById('btn-shuffle');
    var btnRep  = document.getElementById('btn-repeat');

    // Progreso
    audio.addEventListener('timeupdate', function(){
        if (!audio.duration) return;
        var pct = (audio.currentTime / audio.duration) * 100;
        if (fillEl) fillEl.style.width = pct + '%';
        if (curEl)  curEl.textContent  = fmtTime(audio.currentTime);
    });

    audio.addEventListener('loadedmetadata', function(){
        if (endEl) endEl.textContent = fmtTime(audio.duration);
    });

    // Al terminar la canción → autoplay / aleatorio
    audio.addEventListener('ended', function(){
        if (Player.repeatMode === 'one') {
            audio.currentTime = 0;
            audio.play();
            return;
        }
        if (Player.autoplay) {
            playNext();
        } else {
            // Autoplay desactivado: solo actualizar ícono
            updatePlayPauseIcon();
            var lbl = document.getElementById('now-playing-label');
            if (lbl) lbl.textContent = 'Reproducción terminada';
        }
    });

    audio.addEventListener('play',  updatePlayPauseIcon);
    audio.addEventListener('pause', updatePlayPauseIcon);

    // Clic en barra de progreso para saltar
    if (wrapEl) {
        wrapEl.addEventListener('click', function(e){
            if (!audio.duration) return;
            var rect = wrapEl.getBoundingClientRect();
            audio.currentTime = ((e.clientX - rect.left) / rect.width) * audio.duration;
        });
    }

    // Volumen
    if (volEl) volEl.addEventListener('input', function(){ audio.volume = parseInt(volEl.value)/100; });

    // Play/Pause
    if (btnPlay) btnPlay.addEventListener('click', function(){
        if (audio.paused) audio.play(); else audio.pause();
    });

    // Anterior
    if (btnPrev) btnPrev.addEventListener('click', playPrev);

    // Siguiente
    if (btnNext) btnNext.addEventListener('click', function(){
        playNext();
    });

    // Shuffle
    if (btnShuf) btnShuf.addEventListener('click', function(){
        Player.shuffle = !Player.shuffle;
        btnShuf.classList.toggle('active', Player.shuffle);
        // Sincronizar con checkbox si existe
        var chk = document.getElementById('chk-autorand');
        if (chk) chk.checked = Player.shuffle;
        toast(Player.shuffle ? 'Aleatorio activado' : 'Orden normal', 'info', 1500);
    });

    // Repeat
    if (btnRep) btnRep.addEventListener('click', function(){
        var modes = ['none','all','one'];
        Player.repeatMode = modes[(modes.indexOf(Player.repeatMode)+1) % modes.length];
        btnRep.classList.toggle('active', Player.repeatMode !== 'none');
        var labels = { none:'Sin repetición', all:'Repetir todo', one:'Repetir una' };
        toast(labels[Player.repeatMode], 'info', 1500);
    });
});

function updatePlayPauseIcon() {
    var btn = document.getElementById('btn-play-pause');
    if (!btn) return;
    btn.innerHTML = Player.audio.paused
        ? '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>'
        : '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>';
}

// ── Siguiente canción ─────────────────────────────────────────
function playNext() {
    if (!Player.queue.length) return;
    var idx;
    if (Player.shuffle) {
        // Aleatorio: elegir cualquiera excepto la actual
        do { idx = Math.floor(Math.random() * Player.queue.length); }
        while (Player.queue.length > 1 && idx === Player.index);
    } else {
        idx = (Player.index + 1) % Player.queue.length;
    }
    // Si llega al final y no hay repeat all, detener
    if (!Player.shuffle && idx === 0 && Player.repeatMode === 'none') {
        var lbl = document.getElementById('now-playing-label');
        if (lbl) lbl.textContent = 'Cola terminada';
        return;
    }
    Player.index = idx;
    playSong(Player.queue[idx], null, null);
}

// ── Canción anterior ──────────────────────────────────────────
function playPrev() {
    if (!Player.queue.length) return;
    // Si lleva más de 3 seg, reiniciar la misma
    if (Player.audio.currentTime > 3) {
        Player.audio.currentTime = 0;
        return;
    }
    Player.index = (Player.index - 1 + Player.queue.length) % Player.queue.length;
    playSong(Player.queue[Player.index], null, null);
}

// ── Cargar lista de canciones ─────────────────────────────────
async function loadSongs(genre, search) {
    genre  = genre  || '';
    search = search || '';
    var container = document.getElementById('songs-container');
    if (!container) return;
    container.innerHTML = '<div class="loading-center"><div class="spinner"></div></div>';
    try {
        var params = 'action=list';
        if (genre)  params += '&genre='  + encodeURIComponent(genre);
        if (search) params += '&search=' + encodeURIComponent(search);
        var data = await api(BASE + '/backend/songs.php?' + params);
        if (!data) return;
        var songs = data.songs || [];
        window._allSongs = songs;
        if (!songs.length) {
            container.innerHTML = '<div class="empty-state"><div class="empty-icon">🎵</div><p>No hay canciones en esta categoría</p></div>';
            return;
        }
        // Al cargar canciones, llenar la cola del reproductor automáticamente
        Player.queue = songs;
        if (Player.index === -1) Player.index = 0;

        var modeEl = document.getElementById('view-toggle');
        var mode   = modeEl ? (modeEl.dataset.mode || 'grid') : 'grid';
        renderSongs(songs, container, mode);
    } catch(e) {
        container.innerHTML = '<div class="empty-state"><div class="empty-icon">⚠️</div><p>Error al cargar. Revisa la consola.</p></div>';
    }
}

// ── Renderizar canciones (grid o lista) ───────────────────────
function renderSongs(songs, container, mode) {
    mode = mode || 'grid';
    if (mode === 'list') {
        var rows = songs.map(function(s,i){
            return '<div class="song-row" data-id="' + s.id + '" onclick="playSong(window._allSongs['+i+'],window._allSongs,'+i+')">' +
                '<span class="song-row-num">'    + (i+1)           + '</span>' +
                '<div class="song-row-thumb">'   + (s.imagen_url ? '<img src="'+esc(s.imagen_url)+'" alt="">' : '🎵') + '</div>' +
                '<span class="song-row-title">'  + esc(s.nombre)   + '</span>' +
                '<span class="song-row-artist">' + esc(s.artista)  + '</span>' +
                '<span class="song-row-genre">'  + esc(s.genero)   + '</span>' +
                '<span class="song-row-dur">'    + fmtTime(s.duracion) + '</span>' +
                '<button class="btn btn-sm btn-secondary" onclick="event.stopPropagation();openAddToPlaylist('+s.id+')">+</button>' +
                '</div>';
        });
        container.innerHTML = '<div class="songs-list">' +
            '<div class="songs-list-header"><span>#</span><span></span><span>Título</span><span>Artista</span><span>Género</span><span>Dur.</span><span></span></div>' +
            rows.join('') + '</div>';
    } else {
        var cards = songs.map(function(s,i){
            return '<div class="song-card" data-id="' + s.id + '">' +
                '<div class="song-card-cover" onclick="playSong(window._allSongs['+i+'],window._allSongs,'+i+')">' +
                (s.imagen_url ? '<img src="'+esc(s.imagen_url)+'" alt="'+esc(s.nombre)+'">' : '<div class="no-cover">🎵</div>') +
                '<div class="song-card-play-overlay"><div class="play-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="white"><path d="M8 5v14l11-7z"/></svg></div></div>' +
                '</div>' +
                '<div class="song-card-info">' +
                '<div class="song-card-title">'  + esc(s.nombre)  + '</div>' +
                '<div class="song-card-artist">' + esc(s.artista) + '</div>' +
                '<span class="song-card-genre">' + esc(s.genero)  + '</span>' +
                '<button class="add-to-playlist-btn" onclick="openAddToPlaylist('+s.id+')">+ Agregar a playlist</button>' +
                '</div></div>';
        });
        container.innerHTML = '<div class="songs-grid">' + cards.join('') + '</div>';
    }
}

// ── Recomendaciones ───────────────────────────────────────────
async function loadRecommendations(song) {
    var panel = document.getElementById('recommendations-panel');
    var list  = document.getElementById('rec-list');
    if (!panel || !list) return;
    panel.style.display = '';
    list.innerHTML = '<div class="loading-center" style="padding:1rem"><div class="spinner"></div></div>';
    try {
        var data = await api(BASE + '/backend/recommend.php?action=next&song_id=' + song.id);
        if (!data) return;
        var recs = data.recommendations || [];
        if (!recs.length) {
            list.innerHTML = '<p style="color:var(--text3);font-size:.85rem;padding:.5rem">Sin recomendaciones disponibles</p>';
            return;
        }
        list.innerHTML = recs.map(function(r){
            return '<div class="rec-item">' +
                '<div class="rec-thumb">' + (r.imagen ? '<img src="'+esc(r.imagen)+'" alt="">' : '🎵') + '</div>' +
                '<div class="rec-info"><div class="rec-name">'+esc(r.nombre)+'</div><div class="rec-artist">'+esc(r.artista)+'</div></div>' +
                (r.url ? '<a href="'+esc(r.url)+'" target="_blank" class="rec-external">↗</a>' : '') +
                '</div>';
        }).join('');
    } catch(e) {
        list.innerHTML = '<p style="color:var(--text3);font-size:.85rem">No se pudieron cargar recomendaciones</p>';
    }
}

// ── Modal agregar a playlist ──────────────────────────────────
async function openAddToPlaylist(songId) {
    var modal = document.getElementById('modal-add-playlist');
    if (!modal) return;
    var list = document.getElementById('modal-playlist-list');
    if (list) list.innerHTML = '<div class="loading-center"><div class="spinner"></div></div>';
    modal.classList.add('open');
    try {
        var data = await api(BASE + '/backend/playlists.php?action=list');
        if (!data || !list) return;
        if (!data.playlists || !data.playlists.length) {
            list.innerHTML = '<p style="color:var(--text3);padding:.5rem">No tienes playlists aún.</p>';
        } else {
            list.innerHTML = data.playlists.map(function(pl){
                return '<div class="rec-item" style="cursor:pointer" onclick="addSongToPlaylist('+pl.id+','+songId+')">' +
                    '<div class="rec-thumb" style="display:flex;align-items:center;justify-content:center;font-size:1.2rem">🎵</div>' +
                    '<div class="rec-info"><div class="rec-name">'+esc(pl.nombre)+'</div><div class="rec-artist">'+pl.total_canciones+' canciones</div></div>' +
                    '</div>';
            }).join('');
        }
    } catch(e){}
}

async function addSongToPlaylist(playlistId, songId) {
    var fd = new FormData();
    fd.append('action','add_song');
    fd.append('playlist_id', playlistId);
    fd.append('song_id', songId);
    try {
        var data = await api(BASE + '/backend/playlists.php', {method:'POST',body:fd});
        if (!data) return;
        toast('Canción agregada ✓', 'success');
        closeModal('modal-add-playlist');
    } catch(e){}
}

function closeModal(id) {
    var el = document.getElementById(id);
    if (el) el.classList.remove('open');
}

document.addEventListener('click', function(e){
    if (e.target.classList.contains('modal-backdrop')) e.target.classList.remove('open');
});
