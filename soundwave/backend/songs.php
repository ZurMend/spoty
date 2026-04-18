<?php
// ============================================================
//  songs.php — CRUD canciones (API JSON)
// ============================================================
ob_start();
require_once __DIR__ . '/config.php';

$user   = requireAuth(true);
$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : 'list');
$db     = getDB();

// ── LISTAR ────────────────────────────────────────────────────
if ($action === 'list') {
    $genre  = isset($_GET['genre'])  ? $_GET['genre']  : '';
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $limit  = max(1, min(200, (int)(isset($_GET['limit'])  ? $_GET['limit']  : 50)));
    $offset = max(0, (int)(isset($_GET['offset']) ? $_GET['offset'] : 0));

    $where = array(); $params = array();
    if ($genre)  { $where[] = 'genero = ?'; $params[] = $genre; }
    if ($search) {
        $where[] = '(nombre LIKE ? OR artista LIKE ? OR album LIKE ?)';
        $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%";
    }

    $sql = 'SELECT id, nombre, artista, genero, duracion, imagen, album, fecha_lanzamiento, archivo FROM songs';
    if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
    $sql .= ' ORDER BY created_at DESC LIMIT ? OFFSET ?';
    $params[] = $limit; $params[] = $offset;

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $songs = $stmt->fetchAll();

    foreach ($songs as &$s) {
        $s['imagen_url']  = $s['imagen']  ? BASE_URL . '/' . $s['imagen']  : null;
        $s['archivo_url'] = BASE_URL . '/' . $s['archivo'];
    }
    jsonResponse(array('songs' => $songs));
}

// ── DETALLE ───────────────────────────────────────────────────
if ($action === 'get') {
    $id   = (int)(isset($_GET['id']) ? $_GET['id'] : 0);
    $stmt = $db->prepare('SELECT * FROM songs WHERE id = ?');
    $stmt->execute(array($id));
    $song = $stmt->fetch();
    if (!$song) jsonResponse(array('error' => 'Cancion no encontrada'), 404);
    $song['imagen_url']  = $song['imagen']  ? BASE_URL . '/' . $song['imagen']  : null;
    $song['archivo_url'] = BASE_URL . '/' . $song['archivo'];
    jsonResponse(array('song' => $song));
}

// ── SUBIR (solo admin) ────────────────────────────────────────
if ($action === 'upload') {
    if (($user['role'] ?? 'user') !== 'admin') {
        jsonResponse(array('error' => 'Solo el administrador puede subir canciones.'), 403);
    }

    $nombre   = trim(isset($_POST['nombre'])   ? $_POST['nombre']   : '');
    $artista  = trim(isset($_POST['artista'])  ? $_POST['artista']  : '');
    $genero   = trim(isset($_POST['genero'])   ? $_POST['genero']   : '');
    $album    = trim(isset($_POST['album'])    ? $_POST['album']    : '');
    $fecha    = trim(isset($_POST['fecha_lanzamiento']) ? $_POST['fecha_lanzamiento'] : '');
    $duracion = (int)(isset($_POST['duracion']) ? $_POST['duracion'] : 0);

    if (!$nombre || !$artista || !$genero) {
        jsonResponse(array('error' => 'Nombre, artista y genero son obligatorios.'), 422);
    }
    if (empty($_FILES['archivo']['tmp_name'])) {
        jsonResponse(array('error' => 'Debes subir un archivo de audio.'), 422);
    }

    $ext = strtolower(pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_AUDIO, true)) {
        jsonResponse(array('error' => 'Formato no permitido. Usa: mp3, wav, ogg, m4a.'), 422);
    }
    if ($_FILES['archivo']['size'] > MAX_FILE_SIZE) {
        jsonResponse(array('error' => 'El archivo supera el limite de 50 MB.'), 422);
    }

    $audioDir  = ROOT_PATH . '/uploads/songs/';
    $audioName = uniqid('song_', true) . '.' . $ext;
    $audioPath = 'uploads/songs/' . $audioName;

    if (!move_uploaded_file($_FILES['archivo']['tmp_name'], $audioDir . $audioName)) {
        jsonResponse(array('error' => 'No se pudo guardar el audio. Verifica permisos de la carpeta uploads/songs/'), 500);
    }

    $imagePath = null;
    if (!empty($_FILES['imagen']['tmp_name'])) {
        $imgExt = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
        if (in_array($imgExt, ALLOWED_IMAGE, true)) {
            $imgName = uniqid('cover_', true) . '.' . $imgExt;
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], ROOT_PATH . '/uploads/covers/' . $imgName)) {
                $imagePath = 'uploads/covers/' . $imgName;
            }
        }
    }

    $stmt = $db->prepare('INSERT INTO songs (nombre, artista, genero, duracion, imagen, album, fecha_lanzamiento, archivo, subido_por) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute(array($nombre, $artista, $genero, $duracion, $imagePath, $album ? $album : null, $fecha ? $fecha : null, $audioPath, $user['id']));

    jsonResponse(array('success' => true, 'id' => $db->lastInsertId(), 'message' => 'Cancion subida correctamente.'));
}

// ── ELIMINAR (solo admin) ─────────────────────────────────────
if ($action === 'delete') {
    if (($user['role'] ?? 'user') !== 'admin') {
        jsonResponse(array('error' => 'Solo el administrador puede eliminar canciones.'), 403);
    }
    $id   = (int)(isset($_POST['id']) ? $_POST['id'] : 0);
    $stmt = $db->prepare('SELECT archivo, imagen FROM songs WHERE id = ?');
    $stmt->execute(array($id));
    $song = $stmt->fetch();
    if (!$song) jsonResponse(array('error' => 'Cancion no encontrada'), 404);

    foreach (array($song['archivo'], $song['imagen']) as $f) {
        if ($f && file_exists(ROOT_PATH . '/' . $f)) unlink(ROOT_PATH . '/' . $f);
    }
    $db->prepare('DELETE FROM songs WHERE id = ?')->execute(array($id));
    jsonResponse(array('success' => true, 'message' => 'Cancion eliminada.'));
}

// ── HISTORIAL ─────────────────────────────────────────────────
if ($action === 'history') {
    $song_id = (int)(isset($_POST['song_id']) ? $_POST['song_id'] : 0);
    if ($song_id) {
        $db->prepare('INSERT INTO play_history (user_id, song_id) VALUES (?, ?)')->execute(array($user['id'], $song_id));
    }
    jsonResponse(array('success' => true));
}

jsonResponse(array('error' => 'Accion no valida'), 400);
