<?php
require_once __DIR__ . '/../../backend/config.php';
$user    = requireAuth();
$esAdmin = isAdmin();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Inicio — Soundwave</title>
  <link rel="stylesheet" href="../css/style.css">
  <style>
    /* ── Barra superior de usuario ── */
    .topbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: var(--bg2);
      border-bottom: 1px solid var(--border);
      padding: .65rem 1.5rem;
      gap: 1rem;
      flex-shrink: 0;
    }
    .topbar-left  { display:flex; align-items:center; gap:.75rem; }
    .topbar-right { display:flex; align-items:center; gap:.6rem; }
    .topbar-avatar {
      width:34px; height:34px; border-radius:50%;
      background: var(--accent);
      display:flex; align-items:center; justify-content:center;
      font-weight:700; font-size:.88rem; color:#fff; flex-shrink:0;
    }
    .topbar-name  { font-size:.9rem; font-weight:500; }
    .topbar-role  { font-size:.72rem; color:var(--text3); }
    .topbar-badge {
      font-size:.65rem; font-weight:600;
      background:rgba(124,106,255,.2); color:var(--accent2);
      border:1px solid rgba(124,106,255,.3);
      padding:.15rem .5rem; border-radius:10px;
    }
    .btn-logout {
      display:flex; align-items:center; gap:.4rem;
      padding:.45rem .9rem; border-radius:var(--radius-sm);
      background:rgba(255,92,106,.1); color:var(--danger);
      border:1px solid rgba(255,92,106,.25);
      font-size:.82rem; font-weight:500;
      transition:background .15s, border-color .15s;
      cursor:pointer; text-decoration:none;
    }
    .btn-logout:hover { background:rgba(255,92,106,.2); border-color:rgba(255,92,106,.5); }
    .btn-playlists {
      display:flex; align-items:center; gap:.4rem;
      padding:.45rem .9rem; border-radius:var(--radius-sm);
      background:var(--accent-glow); color:var(--accent2);
      border:1px solid var(--accent);
      font-size:.82rem; font-weight:500;
      transition:background .15s; cursor:pointer;
    }
    .btn-playlists:hover { background:rgba(124,106,255,.35); }

    /* ── Layout con topbar ── */
    .app-layout {
      grid-template-rows: auto 1fr var(--player-h);
      grid-template-areas: "sidebar topbar" "sidebar main" "player player";
    }
    .topbar { grid-area: topbar; }

    /* ── Panel lateral de playlists ── */
    .playlist-drawer {
      position: fixed;
      top: 0; right: -360px;
      width: 340px; height: 100vh;
      background: var(--bg2);
      border-left: 1px solid var(--border);
      z-index: 500;
      display: flex; flex-direction: column;
      transition: right .3s cubic-bezier(.4,0,.2,1);
      box-shadow: -8px 0 32px rgba(0,0,0,.4);
    }
    .playlist-drawer.open { right: 0; }
    .drawer-backdrop {
      position: fixed; inset: 0;
      background: rgba(0,0,0,.5);
      z-index: 499;
      opacity: 0; pointer-events: none;
      transition: opacity .3s;
    }
    .drawer-backdrop.open { opacity: 1; pointer-events: all; }
    .drawer-header {
      display: flex; align-items: center; justify-content: space-between;
      padding: 1.25rem 1.5rem;
      border-bottom: 1px solid var(--border);
      flex-shrink: 0;
    }
    .drawer-title { font-family:var(--font-display); font-size:1.1rem; font-weight:700; }
    .drawer-close {
      width:30px; height:30px; border-radius:50%;
      background:var(--surface); color:var(--text2);
      display:flex; align-items:center; justify-content:center;
      font-size:1rem; cursor:pointer; border:none;
      transition:background .15s, color .15s;
    }
    .drawer-close:hover { background:var(--surface2); color:var(--text); }
    .drawer-body { flex:1; overflow-y:auto; padding:1rem; }
    .drawer-footer { padding:1rem 1.5rem; border-top:1px solid var(--border); }

    /* Tarjeta de playlist en drawer */
    .drawer-playlist-card {
      display:flex; align-items:center; gap:.85rem;
      padding:.75rem; border-radius:var(--radius-sm);
      cursor:pointer; transition:background .15s;
      border: 1px solid transparent;
      margin-bottom:.4rem;
    }
    .drawer-playlist-card:hover { background:var(--surface); border-color:var(--border); }
    .drawer-playlist-thumb {
      width:48px; height:48px; border-radius:8px;
      background:var(--bg3); flex-shrink:0;
      display:flex; align-items:center; justify-content:center;
      font-size:1.4rem; overflow:hidden;
    }
    .drawer-playlist-thumb img { width:100%; height:100%; object-fit:cover; }
    .drawer-playlist-name { font-size:.9rem; font-weight:500; }
    .drawer-playlist-count { font-size:.75rem; color:var(--text3); margin-top:.15rem; }

    /* ── Indicador autoplay ── */
    .autoplay-bar {
      display:flex; align-items:center; gap:.75rem;
      padding:.5rem 1rem;
      background:var(--surface);
      border-top:1px solid var(--border);
      font-size:.8rem; color:var(--text2);
    }
    .autoplay-bar label { display:flex; align-items:center; gap:.4rem; cursor:pointer; }
    .autoplay-bar input[type=checkbox] { accent-color:var(--accent); width:15px; height:15px; cursor:pointer; }
    .autoplay-status { margin-left:auto; font-size:.72rem; color:var(--text3); }
    #now-playing-label { color:var(--accent2); font-size:.75rem; font-weight:500; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:200px; }
  </style>
</head>
<body>
<div class="app-layout">

  <!-- ── Sidebar ──────────────────────────────────────────── -->
  <aside class="sidebar">
    <div class="sidebar-logo">Sound<span>wave</span></div>
    <nav class="sidebar-nav">
      <a class="nav-link active" href="home.php">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        Inicio
      </a>
      <?php if ($esAdmin): ?>
      <a class="nav-link" href="admin.php">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        Panel Admin
      </a>
      <?php endif; ?>
      <a class="nav-link" href="playlists.php">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
        Mis playlists
      </a>
      <span class="nav-label">Playlists rápidas</span>
      <div class="sidebar-playlists" id="sidebar-playlists"></div>
    </nav>
    <!-- Usuario en sidebar (solo desktop) -->
    <div class="sidebar-user">
      <div class="sidebar-user-avatar" style="<?= $esAdmin ? 'background:var(--accent)' : '' ?>">
        <?= strtoupper(substr($user['nombre'],0,1)) ?>
      </div>
      <div class="sidebar-user-info">
        <div class="sidebar-user-name"><?= htmlspecialchars($user['nombre']) ?></div>
        <div class="sidebar-user-role" style="<?= $esAdmin ? 'color:var(--accent2)' : '' ?>">
          <?= $esAdmin ? 'Administrador' : 'Oyente' ?>
        </div>
      </div>
      <a class="logout-btn" href="<?= BASE_URL ?>/backend/auth.php?action=logout" title="Cerrar sesión">✕</a>
    </div>
  </aside>

  <!-- ── Barra superior con usuario + botones ─────────────── -->
  <div class="topbar">
    <div class="topbar-left">
      <div class="topbar-avatar" style="<?= $esAdmin ? 'background:var(--accent)' : 'background:var(--accent2)' ?>">
        <?= strtoupper(substr($user['nombre'],0,1)) ?>
      </div>
      <div>
        <div class="topbar-name"><?= htmlspecialchars($user['nombre']) ?></div>
        <div class="topbar-role"><?= htmlspecialchars($user['email']) ?></div>
      </div>
      <?php if ($esAdmin): ?>
        <span class="topbar-badge">Admin</span>
      <?php endif; ?>
    </div>
    <div class="topbar-right">
      <!-- Botón Mis Playlists -->
      <button class="btn-playlists" onclick="openPlaylistDrawer()">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
        Mis playlists
      </button>
      <!-- Botón Cerrar sesión -->
      <a class="btn-logout" href="<?= BASE_URL ?>/backend/auth.php?action=logout">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        Cerrar sesión
      </a>
    </div>
  </div>

  <!-- ── Contenido principal ──────────────────────────────── -->
  <main class="main-content">
    <div class="main-header">
      <div>
        <h1 class="page-title"><?= $esAdmin ? 'Biblioteca completa' : 'Descubre música' ?></h1>
        <p class="page-subtitle"><?= $esAdmin ? 'Vista de administrador' : 'Escucha y crea tus playlists' ?></p>
      </div>
      <div style="display:flex;gap:.75rem;align-items:center;flex-wrap:wrap">
        <div class="search-wrap">
          <span class="search-icon">🔍</span>
          <input type="search" id="search-input" placeholder="Buscar…" style="width:210px" oninput="handleSearch(this.value)">
        </div>
        <button id="view-toggle" class="btn btn-secondary btn-sm" data-mode="grid" onclick="toggleView()">☰ Lista</button>
      </div>
    </div>

    <!-- Filtros de género -->
    <div class="genre-tabs">
      <button class="genre-tab active" onclick="filterGenre(this,'')">Todos</button>
      <button class="genre-tab" onclick="filterGenre(this,'pop')">Pop</button>
      <button class="genre-tab" onclick="filterGenre(this,'rock')">Rock</button>
      <button class="genre-tab" onclick="filterGenre(this,'hip-hop')">Hip-Hop</button>
      <button class="genre-tab" onclick="filterGenre(this,'electronica')">Electrónica</button>
      <button class="genre-tab" onclick="filterGenre(this,'jazz')">Jazz</button>
      <button class="genre-tab" onclick="filterGenre(this,'reggaeton')">Reggaeton</button>
      <button class="genre-tab" onclick="filterGenre(this,'latin')">Latin</button>
    </div>

    <!-- Lista de canciones -->
    <div id="songs-container"><div class="loading-center"><div class="spinner"></div></div></div>

    <!-- Recomendaciones -->
    <div class="recommendations-panel" id="recommendations-panel" style="display:none">
      <div class="rec-title">Sugerido para ti <span class="badge">Last.fm</span></div>
      <div class="rec-list" id="rec-list"></div>
    </div>
  </main>

  <!-- ── Reproductor ───────────────────────────────────────── -->
  <footer class="player" style="flex-direction:column;height:auto;min-height:var(--player-h)">
    <!-- Barra de autoplay -->
    <div class="autoplay-bar">
      <label>
        <input type="checkbox" id="chk-autoplay" checked>
        Reproducción automática
      </label>
      <label>
        <input type="checkbox" id="chk-autorand">
        Orden aleatorio
      </label>
      <span class="autoplay-status" id="now-playing-label">Sin reproducción</span>
    </div>
    <!-- Controles principales -->
    <div style="display:grid;grid-template-columns:280px 1fr 280px;align-items:center;padding:0 1.5rem;gap:1rem;flex:1;min-height:var(--player-h)">
      <div class="player-song-info">
        <div class="player-thumb">
          <img id="player-thumb-img" src="" alt="" style="display:none">
          <div id="player-no-thumb" style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:1.4rem;background:var(--bg3)">🎵</div>
        </div>
        <div>
          <div class="player-song-name"   id="player-song-name">—</div>
          <div class="player-song-artist" id="player-song-artist">Selecciona una canción</div>
        </div>
      </div>
      <div class="player-controls">
        <div class="player-buttons">
          <button class="ctrl-btn" id="btn-shuffle" title="Aleatorio">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 3 21 3 21 8"/><line x1="4" y1="20" x2="21" y2="3"/><polyline points="21 16 21 21 16 21"/><line x1="15" y1="15" x2="21" y2="21"/></svg>
          </button>
          <button class="ctrl-btn" id="btn-prev">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M6 6h2v12H6zm3.5 6l8.5 6V6z"/></svg>
          </button>
          <button class="play-pause-btn" id="btn-play-pause">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
          </button>
          <button class="ctrl-btn" id="btn-next">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M6 18l8.5-6L6 6v12zm2.5-6l5.5 4V8l-5.5 4z"/><path d="M16 6h2v12h-2z"/></svg>
          </button>
          <button class="ctrl-btn" id="btn-repeat">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 014-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 01-4 4H3"/></svg>
          </button>
        </div>
        <div class="player-progress">
          <span class="progress-time" id="time-current">0:00</span>
          <div class="progress-bar-wrap" id="progress-wrap"><div class="progress-bar-fill" id="progress-fill"></div></div>
          <span class="progress-time end" id="time-end">0:00</span>
        </div>
      </div>
      <div class="player-volume">
        <span class="volume-icon">🔊</span>
        <input type="range" class="volume-slider" id="volume-slider" min="0" max="100" value="80">
      </div>
    </div>
  </footer>
</div>

<!-- ── Panel lateral de playlists ────────────────────────── -->
<div class="drawer-backdrop" id="drawer-backdrop" onclick="closePlaylistDrawer()"></div>
<div class="playlist-drawer" id="playlist-drawer">
  <div class="drawer-header">
    <span class="drawer-title">🎵 Mis playlists</span>
    <button class="drawer-close" onclick="closePlaylistDrawer()">✕</button>
  </div>
  <div class="drawer-body" id="drawer-body">
    <div class="loading-center"><div class="spinner"></div></div>
  </div>
  <div class="drawer-footer">
    <a href="playlists.php" class="btn btn-primary" style="width:100%;justify-content:center">
      + Gestionar playlists
    </a>
  </div>
</div>

<!-- Modal: agregar a playlist -->
<div class="modal-backdrop" id="modal-add-playlist">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Agregar a playlist</span>
      <button class="modal-close" onclick="closeModal('modal-add-playlist')">✕</button>
    </div>
    <div id="modal-playlist-list"><div class="loading-center"><div class="spinner"></div></div></div>
    <div style="margin-top:1rem"><a href="playlists.php" class="btn btn-secondary btn-sm">+ Nueva playlist</a></div>
  </div>
</div>

<div id="toast-container"></div>
<script src="../js/app.js"></script>
<script>
var _currentGenre = '', _searchTimer = null;

// Cargar al iniciar
loadSongs();
loadSidebarPlaylists();

// ── Filtro género ─────────────────────────────────────────
function filterGenre(btn, genre) {
  document.querySelectorAll('.genre-tab').forEach(function(b){ b.classList.remove('active'); });
  btn.classList.add('active');
  _currentGenre = genre;
  loadSongs(genre, document.getElementById('search-input').value);
}

function handleSearch(val) {
  clearTimeout(_searchTimer);
  _searchTimer = setTimeout(function(){ loadSongs(_currentGenre, val); }, 350);
}

function toggleView() {
  var btn  = document.getElementById('view-toggle');
  var mode = btn.dataset.mode === 'grid' ? 'list' : 'grid';
  btn.dataset.mode = mode;
  btn.textContent  = mode === 'grid' ? '☰ Lista' : '⊞ Cuadrícula';
  if (window._allSongs) renderSongs(window._allSongs, document.getElementById('songs-container'), mode);
}

// ── Sidebar playlists (mini lista) ────────────────────────
async function loadSidebarPlaylists() {
  try {
    var data = await api(SW_BASE + '/backend/playlists.php?action=list');
    var el   = document.getElementById('sidebar-playlists');
    if (!data || !data.playlists || !data.playlists.length) {
      el.innerHTML = '<div class="sidebar-playlist-item" style="font-style:italic;color:var(--text3)">Sin playlists aún</div>';
      return;
    }
    el.innerHTML = data.playlists.map(function(pl){
      return '<div class="sidebar-playlist-item" onclick="openPlaylistDrawer()"><span class="dot"></span>' + esc(pl.nombre) + '</div>';
    }).join('');
  } catch(e){}
}

// ── Panel lateral de playlists ────────────────────────────
async function openPlaylistDrawer() {
  document.getElementById('playlist-drawer').classList.add('open');
  document.getElementById('drawer-backdrop').classList.add('open');
  var body = document.getElementById('drawer-body');
  body.innerHTML = '<div class="loading-center"><div class="spinner"></div></div>';

  try {
    var data = await api(SW_BASE + '/backend/playlists.php?action=list');
    if (!data || !data.playlists || !data.playlists.length) {
      body.innerHTML = '<div class="empty-state"><div class="empty-icon">🎵</div><p>Aún no tienes playlists.<br>¡Crea la primera!</p></div>';
      return;
    }
    body.innerHTML = data.playlists.map(function(pl){
      return '<div class="drawer-playlist-card" onclick="playPlaylist(' + pl.id + ')">' +
        '<div class="drawer-playlist-thumb">' +
          (pl.portada_url ? '<img src="' + esc(pl.portada_url) + '" alt="">' : '🎵') +
        '</div>' +
        '<div>' +
          '<div class="drawer-playlist-name">'  + esc(pl.nombre) + '</div>' +
          '<div class="drawer-playlist-count">' + pl.total_canciones + ' canción(es)</div>' +
        '</div>' +
        '<svg style="margin-left:auto;flex-shrink:0;color:var(--text3)" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>' +
      '</div>';
    }).join('');
  } catch(e) {
    body.innerHTML = '<p style="color:var(--text3);padding:1rem">Error al cargar playlists</p>';
  }
}

function closePlaylistDrawer() {
  document.getElementById('playlist-drawer').classList.remove('open');
  document.getElementById('drawer-backdrop').classList.remove('open');
}

// Reproducir todas las canciones de una playlist desde el drawer
async function playPlaylist(playlistId) {
  try {
    var data = await api(SW_BASE + '/backend/playlists.php?action=get&id=' + playlistId);
    if (!data || !data.playlist || !data.playlist.songs || !data.playlist.songs.length) {
      toast('Esta playlist no tiene canciones', 'info'); return;
    }
    var songs = data.playlist.songs;
    closePlaylistDrawer();
    // Si aleatorio está activo, mezclar
    var chkRand = document.getElementById('chk-autorand');
    if (chkRand && chkRand.checked) songs = shuffleArray(songs.slice());
    playSong(songs[0], songs, 0);
    toast('Reproduciendo: ' + esc(data.playlist.nombre), 'success', 2000);
  } catch(e) {}
}

// Mezclar array
function shuffleArray(arr) {
  for (var i = arr.length - 1; i > 0; i--) {
    var j = Math.floor(Math.random() * (i + 1));
    var tmp = arr[i]; arr[i] = arr[j]; arr[j] = tmp;
  }
  return arr;
}

// ── Sincronizar checkboxes con el reproductor ─────────────
document.addEventListener('DOMContentLoaded', function(){
  var chkAuto = document.getElementById('chk-autoplay');
  var chkRand = document.getElementById('chk-autorand');
  var btnShuf = document.getElementById('btn-shuffle');

  // Autoplay: activar/desactivar en Player
  chkAuto.addEventListener('change', function(){
    Player.autoplay = this.checked;
    toast(this.checked ? 'Autoplay activado' : 'Autoplay desactivado', 'info', 1500);
  });

  // Aleatorio: sincronizar con botón shuffle del reproductor
  chkRand.addEventListener('change', function(){
    Player.shuffle = this.checked;
    if (btnShuf) btnShuf.classList.toggle('active', this.checked);
    toast(this.checked ? 'Orden aleatorio activado' : 'Orden normal', 'info', 1500);
  });

  // Si el botón shuffle del player se toca, sincronizar con el checkbox
  if (btnShuf) {
    var originalClick = btnShuf.onclick;
    btnShuf.addEventListener('click', function(){
      setTimeout(function(){ chkRand.checked = Player.shuffle; }, 50);
    });
  }

  // Activar autoplay por defecto
  Player.autoplay = true;
});
</script>
</body>
</html>
