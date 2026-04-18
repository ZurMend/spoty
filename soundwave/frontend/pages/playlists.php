<?php
require_once __DIR__ . '/../../backend/config.php';
$user = requireAuth();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Mis playlists — Soundwave</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="app-layout">

  <!-- Sidebar (mismo en todas las páginas) -->
  <aside class="sidebar">
    <div class="sidebar-logo">Sound<span>wave</span></div>
    <nav class="sidebar-nav">
      <a class="nav-link" href="home.php">🏠 Inicio</a>
      <a class="nav-link" href="upload.php">⬆️ Subir canción</a>
      <a class="nav-link active" href="playlists.php">🎵 Mis playlists</a>
    </nav>
    <div class="sidebar-user">
      <div class="sidebar-user-avatar"><?= strtoupper(substr($user['nombre'], 0, 1)) ?></div>
      <div class="sidebar-user-info">
        <div class="sidebar-user-name"><?= htmlspecialchars($user['nombre']) ?></div>
      </div>
      <a class="logout-btn" href="<?= BASE_URL ?>/backend/auth.php?action=logout">✕</a>
    </div>
  </aside>

  <main class="main-content">
    <div class="main-header">
      <div>
        <h1 class="page-title">Mis playlists</h1>
        <p class="page-subtitle">Organiza tu música favorita</p>
      </div>
      <button class="btn btn-primary" onclick="openCreateModal()">+ Nueva playlist</button>
    </div>

    <!-- Grid de playlists -->
    <div id="playlists-container">
      <div class="loading-center"><div class="spinner"></div></div>
    </div>

    <!-- Detalle de playlist seleccionada -->
    <div id="playlist-detail" style="display:none;margin-top:2rem">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem">
        <h2 class="page-title" id="detail-title" style="font-size:1.3rem"></h2>
        <div style="display:flex;gap:.5rem">
          <button class="btn btn-secondary btn-sm" onclick="closeDetail()">✕ Cerrar</button>
          <button class="btn btn-danger btn-sm" id="detail-delete-btn">🗑 Eliminar</button>
        </div>
      </div>
      <div id="detail-songs-container"></div>
    </div>
  </main>

  <!-- Reproductor -->
  <footer class="player">
    <div class="player-song-info">
      <div class="player-thumb">
        <img id="player-thumb-img" src="" alt="" style="display:none">
        <div id="player-no-thumb" style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:1.4rem;background:var(--bg3)">🎵</div>
      </div>
      <div>
        <div class="player-song-name" id="player-song-name">—</div>
        <div class="player-song-artist" id="player-song-artist">Selecciona una canción</div>
      </div>
    </div>
    <div class="player-controls">
      <div class="player-buttons">
        <button class="ctrl-btn" id="btn-shuffle">⇄</button>
        <button class="ctrl-btn" id="btn-prev">⏮</button>
        <button class="play-pause-btn" id="btn-play-pause">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
        </button>
        <button class="ctrl-btn" id="btn-next">⏭</button>
        <button class="ctrl-btn" id="btn-repeat">⟳</button>
      </div>
      <div class="player-progress">
        <span class="progress-time" id="time-current">0:00</span>
        <div class="progress-bar-wrap" id="progress-wrap">
          <div class="progress-bar-fill" id="progress-fill"></div>
        </div>
        <span class="progress-time end" id="time-end">0:00</span>
      </div>
    </div>
    <div class="player-volume">
      <span class="volume-icon">🔊</span>
      <input type="range" class="volume-slider" id="volume-slider" min="0" max="100" value="80">
    </div>
  </footer>
</div>

<!-- Modal: crear playlist -->
<div class="modal-backdrop" id="modal-create">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Nueva playlist</span>
      <button class="modal-close" onclick="closeModal('modal-create')">✕</button>
    </div>
    <form id="form-create-playlist" enctype="multipart/form-data">
      <div class="form-group">
        <label class="form-label">Nombre *</label>
        <input type="text" name="nombre" placeholder="Mi playlist favorita" required>
      </div>
      <div class="form-group">
        <label class="form-label">Descripción</label>
        <textarea name="descripcion" rows="3" placeholder="Describe tu playlist…" style="resize:vertical"></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">Portada (opcional)</label>
        <input type="file" name="portada" accept="image/*" style="padding:.5rem">
      </div>
      <div style="display:flex;gap:.75rem;justify-content:flex-end;margin-top:1rem">
        <button type="button" class="btn btn-secondary" onclick="closeModal('modal-create')">Cancelar</button>
        <button type="submit" class="btn btn-primary">Crear playlist</button>
      </div>
    </form>
  </div>
</div>

<div id="toast-container"></div>

<script src="../js/app.js"></script>
<script>
loadPlaylists();

async function loadPlaylists() {
  const container = document.getElementById('playlists-container');
  try {
    const data = await api(`${SW_BASE}/backend/playlists.php?action=list`);
    const pls  = data.playlists || [];

    if (!pls.length) {
      container.innerHTML = `<div class="empty-state"><div class="empty-icon">🎵</div><p>Aún no tienes playlists.<br>¡Crea la primera!</p></div>`;
      return;
    }

    container.innerHTML = `<div class="playlists-grid">
      ${pls.map(pl => `
        <div class="playlist-card" id="pl-${pl.id}" onclick="openPlaylistDetail(${pl.id}, '${esc(pl.nombre)}')">
          <div class="playlist-card-cover">
            ${pl.portada_url ? `<img src="${pl.portada_url}" alt="">` : '🎵'}
          </div>
          <div class="playlist-card-info">
            <div class="playlist-card-name">${esc(pl.nombre)}</div>
            <div class="playlist-card-count">${pl.total_canciones} canción(es)</div>
          </div>
        </div>`).join('')}
    </div>`;
  } catch {}
}

async function openPlaylistDetail(id, nombre) {
  const detail    = document.getElementById('playlist-detail');
  const title     = document.getElementById('detail-title');
  const container = document.getElementById('detail-songs-container');
  const deleteBtn = document.getElementById('detail-delete-btn');

  detail.style.display = '';
  title.textContent    = nombre;
  deleteBtn.onclick    = () => deletePlaylist(id);
  container.innerHTML  = '<div class="loading-center"><div class="spinner"></div></div>';

  detail.scrollIntoView({ behavior: 'smooth' });

  try {
    const data  = await api(`${SW_BASE}/backend/playlists.php?action=get&id=${id}`);
    const songs = data.playlist.songs || [];

    if (!songs.length) {
      container.innerHTML = `<div class="empty-state"><div class="empty-icon">🎵</div><p>Esta playlist está vacía.<br>Agrega canciones desde la pantalla principal.</p></div>`;
      return;
    }

    window._plSongs = songs;

    container.innerHTML = `<div class="songs-list">
      <div class="songs-list-header">
        <span>#</span><span></span><span>Título</span><span>Artista</span>
        <span>Género</span><span>Dur.</span><span></span>
      </div>
      ${songs.map((s, i) => `
        <div class="song-row" data-id="${s.id}" onclick="playSong(window._plSongs[${i}], window._plSongs, ${i})">
          <span class="song-row-num">${i + 1}</span>
          <div class="song-row-thumb">
            ${s.imagen_url ? `<img src="${s.imagen_url}" alt="">` : '🎵'}
          </div>
          <span class="song-row-title">${esc(s.nombre)}</span>
          <span class="song-row-artist">${esc(s.artista)}</span>
          <span class="song-row-genre">${esc(s.genero)}</span>
          <span class="song-row-dur">${fmtTime(s.duracion)}</span>
          <button class="btn btn-sm btn-danger" onclick="event.stopPropagation();removeSong(${id}, ${s.id})">✕</button>
        </div>`).join('')}
    </div>`;
  } catch {}
}

async function removeSong(playlistId, songId) {
  if (!confirm('¿Quitar esta canción de la playlist?')) return;
  const fd = new FormData();
  fd.append('action', 'remove_song');
  fd.append('playlist_id', playlistId);
  fd.append('song_id', songId);
  try {
    await api(`${SW_BASE}/backend/playlists.php`, { method: 'POST', body: fd });
    toast('Canción eliminada ✓', 'success');
    openPlaylistDetail(playlistId, document.getElementById('detail-title').textContent);
  } catch {}
}

async function deletePlaylist(id) {
  if (!confirm('¿Eliminar esta playlist? No se puede deshacer.')) return;
  const fd = new FormData();
  fd.append('action', 'delete');
  fd.append('id', id);
  try {
    await api(`${SW_BASE}/backend/playlists.php`, { method: 'POST', body: fd });
    toast('Playlist eliminada', 'info');
    closeDetail();
    loadPlaylists();
  } catch {}
}

function closeDetail() {
  document.getElementById('playlist-detail').style.display = 'none';
}

function openCreateModal() {
  document.getElementById('modal-create').classList.add('open');
}

// Enviar form crear playlist
document.getElementById('form-create-playlist').addEventListener('submit', async e => {
  e.preventDefault();
  const fd = new FormData(e.target);
  fd.append('action', 'create');
  try {
    await api(`${SW_BASE}/backend/playlists.php`, { method: 'POST', body: fd });
    toast('Playlist creada ✓', 'success');
    closeModal('modal-create');
    e.target.reset();
    loadPlaylists();
  } catch {}
});
</script>
</body>
</html>
