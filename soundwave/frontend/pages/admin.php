<?php
require_once __DIR__ . '/../../backend/config.php';
$user = requireAdmin(); // Solo admins
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Panel Admin — Soundwave</title>
  <link rel="stylesheet" href="../css/style.css">
  <style>
    .admin-stats { display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:1rem; margin-bottom:2rem; }
    .stat-card { background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); padding:1.25rem; }
    .stat-num  { font-family:var(--font-display); font-size:2rem; font-weight:700; color:var(--accent2); }
    .stat-label{ font-size:.82rem; color:var(--text2); margin-top:.25rem; }
    .admin-tabs { display:flex; gap:.5rem; margin-bottom:1.5rem; border-bottom:1px solid var(--border); padding-bottom:.75rem; }
    .admin-tab  { padding:.5rem 1.25rem; border-radius:var(--radius-sm) var(--radius-sm) 0 0; font-size:.9rem; font-weight:500; color:var(--text2); cursor:pointer; transition:all .15s; border:1px solid transparent; }
    .admin-tab.active { background:var(--surface); border-color:var(--border); border-bottom-color:var(--bg); color:var(--accent2); }
    .admin-panel { display:none; } .admin-panel.active { display:block; }
    .users-table { width:100%; border-collapse:collapse; font-size:.88rem; }
    .users-table th { text-align:left; padding:.6rem .75rem; color:var(--text3); font-size:.75rem; text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid var(--border); }
    .users-table td { padding:.65rem .75rem; border-bottom:1px solid rgba(255,255,255,0.04); }
    .users-table tr:hover td { background:var(--surface); }
    .role-badge { display:inline-block; padding:.15rem .6rem; border-radius:10px; font-size:.72rem; font-weight:600; }
    .role-badge.admin { background:rgba(124,106,255,.2); color:var(--accent2); border:1px solid rgba(124,106,255,.3); }
    .role-badge.user  { background:rgba(34,211,165,.12); color:var(--green); border:1px solid rgba(34,211,165,.25); }
    .upload-drop { border:2px dashed var(--border); border-radius:var(--radius); padding:2rem; text-align:center; cursor:pointer; transition:border-color .2s,background .2s; }
    .upload-drop:hover,.upload-drop.over { border-color:var(--accent); background:var(--accent-glow); }
    .progress-upload { height:6px; background:var(--surface2); border-radius:3px; margin-top:1rem; overflow:hidden; display:none; }
    .progress-upload-fill { height:100%; background:var(--accent); width:0%; transition:width .3s; }
  </style>
</head>
<body>
<div class="app-layout">

  <aside class="sidebar">
    <div class="sidebar-logo">Sound<span>wave</span></div>
    <nav class="sidebar-nav">
      <a class="nav-link active" href="admin.php">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        Panel Admin
      </a>
      <a class="nav-link" href="home.php">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
        Ver biblioteca
      </a>
    </nav>
    <div class="sidebar-user">
      <div class="sidebar-user-avatar" style="background:var(--accent)"><?= strtoupper(substr($user['nombre'],0,1)) ?></div>
      <div class="sidebar-user-info">
        <div class="sidebar-user-name"><?= htmlspecialchars($user['nombre']) ?></div>
        <div class="sidebar-user-role" style="color:var(--accent2)">Administrador</div>
      </div>
      <a class="logout-btn" href="<?= BASE_URL ?>/backend/auth.php?action=logout">✕</a>
    </div>
  </aside>

  <main class="main-content">
    <div class="main-header">
      <div>
        <h1 class="page-title">Panel de administración</h1>
        <p class="page-subtitle">Gestiona canciones y usuarios</p>
      </div>
    </div>

    <!-- Stats -->
    <div class="admin-stats" id="admin-stats">
      <div class="stat-card"><div class="stat-num" id="stat-songs">…</div><div class="stat-label">Canciones totales</div></div>
      <div class="stat-card"><div class="stat-num" id="stat-users">…</div><div class="stat-label">Usuarios registrados</div></div>
      <div class="stat-card"><div class="stat-num" id="stat-plays">…</div><div class="stat-label">Reproducciones totales</div></div>
      <div class="stat-card"><div class="stat-num" id="stat-playlists">…</div><div class="stat-label">Playlists creadas</div></div>
    </div>

    <!-- Tabs -->
    <div class="admin-tabs">
      <div class="admin-tab active" onclick="switchTab('tab-upload',this)">⬆️ Subir canción</div>
      <div class="admin-tab" onclick="switchTab('tab-songs',this)">🎵 Gestionar canciones</div>
      <div class="admin-tab" onclick="switchTab('tab-users',this)">👥 Usuarios</div>
    </div>

    <!-- Tab: Subir canción -->
    <div class="admin-panel active" id="tab-upload">
      <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:2rem;max-width:640px">
        <div class="upload-drop" id="drop-zone" onclick="document.getElementById('file-audio').click()">
          <div style="font-size:2rem;margin-bottom:.5rem">🎵</div>
          <p style="color:var(--text2)"><strong style="color:var(--accent2)">Arrastra el archivo aquí</strong> o haz clic</p>
          <p style="font-size:.8rem;color:var(--text3);margin-top:.4rem">MP3, WAV, OGG, M4A — máx. 50 MB</p>
          <p id="selected-file" style="color:var(--accent2);font-weight:500;margin-top:.5rem"></p>
        </div>
        <input type="file" id="file-audio" accept=".mp3,.wav,.ogg,.m4a" style="display:none" onchange="handleAudioSelect(this)">
        <div class="progress-upload" id="progress-upload"><div class="progress-upload-fill" id="progress-fill-up"></div></div>

        <form id="upload-form" style="margin-top:1.5rem">
          <div class="form-row">
            <div class="form-group"><label class="form-label">Nombre *</label><input type="text" name="nombre" required></div>
            <div class="form-group"><label class="form-label">Artista *</label><input type="text" name="artista" required></div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Género *</label>
              <select name="genero" required>
                <option value="">— Selecciona —</option>
                <option value="pop">Pop</option><option value="rock">Rock</option>
                <option value="hip-hop">Hip-Hop</option><option value="electronica">Electrónica</option>
                <option value="jazz">Jazz</option><option value="reggaeton">Reggaeton</option>
                <option value="latin">Latin</option><option value="r&b">R&B / Soul</option>
                <option value="metal">Metal</option><option value="country">Country</option>
                <option value="blues">Blues</option><option value="indie">Indie</option>
                <option value="clasica">Clásica</option><option value="otro">Otro</option>
              </select>
            </div>
            <div class="form-group"><label class="form-label">Álbum</label><input type="text" name="album"></div>
          </div>
          <div class="form-row">
            <div class="form-group"><label class="form-label">Fecha lanzamiento</label><input type="date" name="fecha_lanzamiento"></div>
            <div class="form-group"><label class="form-label">Duración (seg)</label><input type="number" name="duracion" id="duracion-input" min="0"></div>
          </div>
          <div class="form-group"><label class="form-label">Portada (imagen)</label><input type="file" name="imagen" accept="image/*" style="padding:.5rem"></div>
          <div style="display:flex;justify-content:flex-end;margin-top:1rem">
            <button type="submit" class="btn btn-primary" id="btn-upload">⬆️ Subir canción</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Tab: Gestionar canciones -->
    <div class="admin-panel" id="tab-songs">
      <div id="admin-songs-container"><div class="loading-center"><div class="spinner"></div></div></div>
    </div>

    <!-- Tab: Usuarios -->
    <div class="admin-panel" id="tab-users">
      <div id="admin-users-container"><div class="loading-center"><div class="spinner"></div></div></div>
    </div>
  </main>

  <footer class="player">
    <div class="player-song-info">
      <div class="player-thumb">
        <img id="player-thumb-img" src="" alt="" style="display:none">
        <div id="player-no-thumb" style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:1.4rem;background:var(--bg3)">🎵</div>
      </div>
      <div><div class="player-song-name" id="player-song-name">—</div><div class="player-song-artist" id="player-song-artist">Sin reproducción</div></div>
    </div>
    <div class="player-controls">
      <div class="player-buttons">
        <button class="ctrl-btn" id="btn-shuffle">⇄</button>
        <button class="ctrl-btn" id="btn-prev">⏮</button>
        <button class="play-pause-btn" id="btn-play-pause"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg></button>
        <button class="ctrl-btn" id="btn-next">⏭</button>
        <button class="ctrl-btn" id="btn-repeat">⟳</button>
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
  </footer>
</div>

<div id="toast-container"></div>
<script src="../js/app.js"></script>
<script>
// ── Stats ───────────────────────────────────────────────────
async function loadStats() {
  try {
    const data = await api(`${SW_BASE}/backend/admin_api.php?action=stats`);
    document.getElementById('stat-songs').textContent     = data.songs     ?? 0;
    document.getElementById('stat-users').textContent     = data.users     ?? 0;
    document.getElementById('stat-plays').textContent     = data.plays     ?? 0;
    document.getElementById('stat-playlists').textContent = data.playlists ?? 0;
  } catch {}
}

// ── Tabs ────────────────────────────────────────────────────
function switchTab(id, btn) {
  document.querySelectorAll('.admin-panel').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.admin-tab').forEach(b  => b.classList.remove('active'));
  document.getElementById(id).classList.add('active');
  btn.classList.add('active');
  if (id === 'tab-songs') loadAdminSongs();
  if (id === 'tab-users') loadAdminUsers();
}

// ── Canciones (admin) ───────────────────────────────────────
async function loadAdminSongs() {
  const c = document.getElementById('admin-songs-container');
  c.innerHTML = '<div class="loading-center"><div class="spinner"></div></div>';
  try {
    const data  = await api(`${SW_BASE}/backend/songs.php?action=list&limit=200`);
    const songs = data.songs || [];
    if (!songs.length) { c.innerHTML = '<div class="empty-state"><div class="empty-icon">🎵</div><p>Sin canciones aún</p></div>'; return; }

    c.innerHTML = `<table class="users-table">
      <thead><tr><th></th><th>Nombre</th><th>Artista</th><th>Género</th><th>Duración</th><th>Acciones</th></tr></thead>
      <tbody>${songs.map(s => `
        <tr>
          <td><div style="width:36px;height:36px;border-radius:6px;background:var(--bg3);overflow:hidden;display:flex;align-items:center;justify-content:center">
            ${s.imagen_url ? `<img src="${s.imagen_url}" style="width:100%;height:100%;object-fit:cover">` : '🎵'}
          </div></td>
          <td><strong>${esc(s.nombre)}</strong></td>
          <td style="color:var(--text2)">${esc(s.artista)}</td>
          <td><span class="role-badge user">${esc(s.genero)}</span></td>
          <td style="color:var(--text3)">${fmtTime(s.duracion)}</td>
          <td>
            <button class="btn btn-sm btn-secondary" onclick="playSong(${JSON.stringify(s).replace(/"/g,'&quot;')})">▶</button>
            <button class="btn btn-sm btn-danger" onclick="adminDeleteSong(${s.id})">🗑 Eliminar</button>
          </td>
        </tr>`).join('')}
      </tbody></table>`;
  } catch {}
}

async function adminDeleteSong(id) {
  if (!confirm('¿Eliminar esta canción? No se puede deshacer.')) return;
  const fd = new FormData(); fd.append('action','delete'); fd.append('id', id);
  try {
    await api(`${SW_BASE}/backend/songs.php`, { method:'POST', body:fd });
    toast('Canción eliminada', 'success');
    loadAdminSongs();
    loadStats();
  } catch {}
}

// ── Usuarios (admin) ─────────────────────────────────────────
async function loadAdminUsers() {
  const c = document.getElementById('admin-users-container');
  c.innerHTML = '<div class="loading-center"><div class="spinner"></div></div>';
  try {
    const data  = await api(`${SW_BASE}/backend/admin_api.php?action=users`);
    const users = data.users || [];
    if (!users.length) { c.innerHTML = '<div class="empty-state"><p>Sin usuarios</p></div>'; return; }

    c.innerHTML = `<table class="users-table">
      <thead><tr><th>#</th><th>Nombre</th><th>Email</th><th>Rol</th><th>Registro</th><th>Acción</th></tr></thead>
      <tbody>${users.map(u => `
        <tr>
          <td style="color:var(--text3)">${u.id}</td>
          <td><strong>${esc(u.nombre)}</strong></td>
          <td style="color:var(--text2)">${esc(u.email)}</td>
          <td><span class="role-badge ${u.role}">${u.role}</span></td>
          <td style="color:var(--text3);font-size:.8rem">${u.created_at?.split('T')[0] ?? ''}</td>
          <td>
            ${u.role !== 'admin' ? `<button class="btn btn-sm btn-danger" onclick="deleteUser(${u.id})">🗑 Eliminar</button>` : '<span style="color:var(--text3);font-size:.8rem">—</span>'}
          </td>
        </tr>`).join('')}
      </tbody></table>`;
  } catch {}
}

async function deleteUser(id) {
  if (!confirm('¿Eliminar este usuario? Se borrarán también sus playlists.')) return;
  const fd = new FormData(); fd.append('action','delete_user'); fd.append('id', id);
  try {
    await api(`${SW_BASE}/backend/admin_api.php`, { method:'POST', body:fd });
    toast('Usuario eliminado', 'success');
    loadAdminUsers();
    loadStats();
  } catch {}
}

// ── Upload con progress ──────────────────────────────────────
let _selectedAudio = null;
const dropZone = document.getElementById('drop-zone');
dropZone.addEventListener('dragover',  e => { e.preventDefault(); dropZone.classList.add('over'); });
dropZone.addEventListener('dragleave', () => dropZone.classList.remove('over'));
dropZone.addEventListener('drop', e => { e.preventDefault(); dropZone.classList.remove('over'); const f = e.dataTransfer.files[0]; if(f) setAudioFile(f); });
function handleAudioSelect(input) { if(input.files[0]) setAudioFile(input.files[0]); }
function setAudioFile(file) {
  _selectedAudio = file;
  document.getElementById('selected-file').textContent = '✓ ' + file.name;
  const url = URL.createObjectURL(file);
  const tmp = new Audio(url);
  tmp.addEventListener('loadedmetadata', () => { document.getElementById('duracion-input').value = Math.round(tmp.duration); URL.revokeObjectURL(url); });
}

document.getElementById('upload-form').addEventListener('submit', e => {
  e.preventDefault();
  if (!_selectedAudio) { toast('Selecciona un archivo de audio primero', 'error'); return; }
  const fd = new FormData(e.target);
  fd.append('action', 'upload');
  fd.append('archivo', _selectedAudio, _selectedAudio.name);

  const btn  = document.getElementById('btn-upload');
  const prog = document.getElementById('progress-upload');
  const fill = document.getElementById('progress-fill-up');
  btn.disabled = true; btn.textContent = 'Subiendo…'; prog.style.display = '';

  const xhr = new XMLHttpRequest();
  xhr.open('POST', `${SW_BASE}/backend/songs.php`);
  xhr.upload.addEventListener('progress', ev => { if(ev.lengthComputable) fill.style.width = (ev.loaded/ev.total*100)+'%'; });
  xhr.addEventListener('load', () => {
    try {
      const data = JSON.parse(xhr.responseText);
      if (data.error) throw new Error(data.error);
      toast('¡Canción subida! ✓', 'success');
      e.target.reset(); _selectedAudio = null;
      document.getElementById('selected-file').textContent = '';
      fill.style.width = '0%'; loadStats();
    } catch(err) { toast(err.message || 'Error al subir', 'error'); }
    btn.disabled = false; btn.textContent = '⬆️ Subir canción';
  });
  xhr.addEventListener('error', () => { toast('Error de red', 'error'); btn.disabled = false; btn.textContent = '⬆️ Subir canción'; });
  xhr.send(fd);
});

loadStats();
</script>
</body>
</html>
