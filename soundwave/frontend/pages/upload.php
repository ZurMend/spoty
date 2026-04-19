<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: home.php");
    exit;
}
?>


<?php
require_once __DIR__ . '/../../backend/config.php';
$user = requireAuth();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Subir canción — Soundwave</title>
  <link rel="stylesheet" href="../css/style.css">
  <style>
    .upload-drop {
      border: 2px dashed var(--border);
      border-radius: var(--radius);
      padding: 2.5rem;
      text-align: center;
      cursor: pointer;
      transition: border-color .2s, background .2s;
    }
    .upload-drop:hover, .upload-drop.over { border-color: var(--accent); background: var(--accent-glow); }
    .upload-drop .drop-icon { font-size: 2.5rem; margin-bottom: .75rem; }
    .upload-drop p { color: var(--text2); font-size: .9rem; }
    .upload-drop strong { color: var(--accent2); }
    .upload-card {
      background: var(--surface);
      border-radius: var(--radius);
      border: 1px solid var(--border);
      padding: 2rem;
      max-width: 640px;
      margin: 0 auto;
    }
    .progress-upload { height: 6px; background: var(--surface2); border-radius: 3px; margin-top: 1rem; overflow: hidden; display:none; }
    .progress-upload-fill { height: 100%; background: var(--accent); width: 0%; transition: width .3s; }
  </style>
</head>
<body>
<div class="app-layout">

  <aside class="sidebar">
    <div class="sidebar-logo">Sound<span>wave</span></div>
    <nav class="sidebar-nav">
      <a class="nav-link" href="home.php">🏠 Inicio</a>
      <a class="nav-link active" href="upload.php">⬆️ Subir canción</a>
      <a class="nav-link" href="playlists.php">🎵 Mis playlists</a>
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
        <h1 class="page-title">Subir canción</h1>
        <p class="page-subtitle">Agrega música a la biblioteca compartida</p>
      </div>
    </div>

    <div class="upload-card">

      <!-- Drop zone -->
      <div class="upload-drop" id="drop-zone" onclick="document.getElementById('file-audio').click()">
        <div class="drop-icon">🎵</div>
        <p><strong>Arrastra tu archivo aquí</strong> o haz clic para seleccionar</p>
        <p style="margin-top:.4rem;font-size:.8rem">MP3, WAV, OGG, M4A — máx. 50 MB</p>
        <p id="selected-file" style="margin-top:.75rem;color:var(--accent2);font-weight:500"></p>
      </div>
      <input type="file" id="file-audio" accept=".mp3,.wav,.ogg,.m4a" style="display:none" onchange="handleAudioSelect(this)">

      <div class="progress-upload" id="progress-upload">
        <div class="progress-upload-fill" id="progress-fill-up"></div>
      </div>

      <form id="upload-form" style="margin-top:1.5rem">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Nombre de la canción *</label>
            <input type="text" name="nombre" placeholder="Ej. Blinding Lights" required>
          </div>
          <div class="form-group">
            <label class="form-label">Artista *</label>
            <input type="text" name="artista" placeholder="Ej. The Weeknd" required>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Género *</label>
            <select name="genero" required>
              <option value="">— Selecciona —</option>
              <option value="pop">Pop</option>
              <option value="rock">Rock</option>
              <option value="hip-hop">Hip-Hop</option>
              <option value="electronica">Electrónica</option>
              <option value="jazz">Jazz</option>
              <option value="reggaeton">Reggaeton</option>
              <option value="latin">Latin</option>
              <option value="r&b">R&B / Soul</option>
              <option value="metal">Metal</option>
              <option value="country">Country</option>
              <option value="blues">Blues</option>
              <option value="indie">Indie</option>
              <option value="clasica">Clásica</option>
              <option value="otro">Otro</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Álbum</label>
            <input type="text" name="album" placeholder="Ej. After Hours">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Fecha de lanzamiento</label>
            <input type="date" name="fecha_lanzamiento">
          </div>
          <div class="form-group">
            <label class="form-label">Duración (segundos)</label>
            <input type="number" name="duracion" id="duracion-input" placeholder="Ej. 200" min="0">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Portada del álbum (imagen, opcional)</label>
          <input type="file" name="imagen" accept="image/*" style="padding:.5rem">
        </div>

        <div style="display:flex;justify-content:flex-end;margin-top:1rem">
          <button type="submit" class="btn btn-primary" id="btn-upload">
            ⬆️ Subir canción
          </button>
        </div>
      </form>
    </div>
  </main>

  <!-- Reproductor mínimo (para poder reproducir desde aquí) -->
  <footer class="player">
    <div class="player-song-info">
      <div class="player-thumb">
        <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:1.4rem;background:var(--bg3)">🎵</div>
      </div>
      <div>
        <div class="player-song-name" id="player-song-name">—</div>
        <div class="player-song-artist" id="player-song-artist">Sin reproducción</div>
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

<div id="toast-container"></div>

<script src="../js/app.js"></script>
<script>
let _selectedAudio = null;

// Drag & Drop
const dropZone = document.getElementById('drop-zone');
dropZone.addEventListener('dragover',  e => { e.preventDefault(); dropZone.classList.add('over'); });
dropZone.addEventListener('dragleave', () => dropZone.classList.remove('over'));
dropZone.addEventListener('drop', e => {
  e.preventDefault(); dropZone.classList.remove('over');
  const file = e.dataTransfer.files[0];
  if (file) setAudioFile(file);
});

function handleAudioSelect(input) {
  if (input.files[0]) setAudioFile(input.files[0]);
}

function setAudioFile(file) {
  _selectedAudio = file;
  document.getElementById('selected-file').textContent = `✓ ${file.name}`;

  // Intentar obtener duración automáticamente
  const url = URL.createObjectURL(file);
  const tmpAudio = new Audio(url);
  tmpAudio.addEventListener('loadedmetadata', () => {
    document.getElementById('duracion-input').value = Math.round(tmpAudio.duration);
    URL.revokeObjectURL(url);
  });
}

// Submit con XMLHttpRequest para mostrar progreso
document.getElementById('upload-form').addEventListener('submit', async e => {
  e.preventDefault();
  if (!_selectedAudio) { toast('Selecciona un archivo de audio primero', 'error'); return; }

  const form = e.target;
  const fd   = new FormData(form);
  fd.append('action',  'upload');
  fd.append('archivo', _selectedAudio, _selectedAudio.name);

  const btn      = document.getElementById('btn-upload');
  const progressWrap = document.getElementById('progress-upload');
  const fill     = document.getElementById('progress-fill-up');

  btn.disabled = true;
  btn.textContent = 'Subiendo…';
  progressWrap.style.display = '';

  const xhr = new XMLHttpRequest();
  xhr.open('POST', `${SW_BASE}/backend/songs.php`);

  xhr.upload.addEventListener('progress', ev => {
    if (ev.lengthComputable) {
      fill.style.width = (ev.loaded / ev.total * 100) + '%';
    }
  });

  xhr.addEventListener('load', () => {
    try {
      const data = JSON.parse(xhr.responseText);
      if (data.error) throw new Error(data.error);
      toast('¡Canción subida exitosamente! ✓', 'success');
      form.reset();
      _selectedAudio = null;
      document.getElementById('selected-file').textContent = '';
      fill.style.width = '0%';
    } catch (err) {
      toast(err.message || 'Error al subir', 'error');
    }
    btn.disabled = false;
    btn.textContent = '⬆️ Subir canción';
  });

  xhr.addEventListener('error', () => {
    toast('Error de red', 'error');
    btn.disabled = false;
    btn.textContent = '⬆️ Subir canción';
  });

  xhr.send(fd);
});
</script>
</body>
</html>
