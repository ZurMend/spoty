<?php
// ============================================================
//  playlists.php — CRUD playlists
// ============================================================
ob_start();
require_once __DIR__ . '/config.php';

$user   = requireAuth(true);
$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : 'list');
$db     = getDB();

if ($action === 'list') {
    $stmt = $db->prepare(
        'SELECT p.id, p.nombre, p.descripcion, p.portada, p.created_at,
                COUNT(ps.song_id) AS total_canciones
         FROM playlists p
         LEFT JOIN playlist_songs ps ON ps.playlist_id = p.id
         WHERE p.user_id = ?
         GROUP BY p.id ORDER BY p.created_at DESC'
    );
    $stmt->execute(array($user['id']));
    $playlists = $stmt->fetchAll();
    foreach ($playlists as &$pl) {
        $pl['portada_url'] = $pl['portada'] ? BASE_URL . '/' . $pl['portada'] : null;
    }
    jsonResponse(array('playlists' => $playlists));
}

if ($action === 'create') {
    $nombre      = trim(isset($_POST['nombre'])      ? $_POST['nombre']      : '');
    $descripcion = trim(isset($_POST['descripcion']) ? $_POST['descripcion'] : '');
    if (!$nombre) jsonResponse(array('error' => 'El nombre es obligatorio.'), 422);

    $portada = null;
    if (!empty($_FILES['portada']['tmp_name'])) {
        $ext = strtolower(pathinfo($_FILES['portada']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ALLOWED_IMAGE, true)) {
            $name = uniqid('pl_', true) . '.' . $ext;
            if (move_uploaded_file($_FILES['portada']['tmp_name'], ROOT_PATH . '/uploads/covers/' . $name)) {
                $portada = 'uploads/covers/' . $name;
            }
        }
    }
    $stmt = $db->prepare('INSERT INTO playlists (user_id, nombre, descripcion, portada) VALUES (?, ?, ?, ?)');
    $stmt->execute(array($user['id'], $nombre, $descripcion ? $descripcion : null, $portada));
    jsonResponse(array('success' => true, 'id' => $db->lastInsertId()));
}

if ($action === 'get') {
    $id   = (int)(isset($_GET['id']) ? $_GET['id'] : 0);
    $stmt = $db->prepare('SELECT * FROM playlists WHERE id = ? AND user_id = ?');
    $stmt->execute(array($id, $user['id']));
    $playlist = $stmt->fetch();
    if (!$playlist) jsonResponse(array('error' => 'Playlist no encontrada'), 404);
    $playlist['portada_url'] = $playlist['portada'] ? BASE_URL . '/' . $playlist['portada'] : null;

    $stmt = $db->prepare(
        'SELECT s.id, s.nombre, s.artista, s.genero, s.duracion, s.imagen, s.album, s.archivo, ps.orden
         FROM playlist_songs ps JOIN songs s ON s.id = ps.song_id
         WHERE ps.playlist_id = ? ORDER BY ps.orden ASC, ps.added_at ASC'
    );
    $stmt->execute(array($id));
    $songs = $stmt->fetchAll();
    foreach ($songs as &$s) {
        $s['imagen_url']  = $s['imagen']  ? BASE_URL . '/' . $s['imagen']  : null;
        $s['archivo_url'] = BASE_URL . '/' . $s['archivo'];
    }
    $playlist['songs'] = $songs;
    jsonResponse(array('playlist' => $playlist));
}

if ($action === 'update') {
    $id     = (int)(isset($_POST['id']) ? $_POST['id'] : 0);
    $nombre = trim(isset($_POST['nombre']) ? $_POST['nombre'] : '');
    $stmt   = $db->prepare('SELECT id FROM playlists WHERE id = ? AND user_id = ?');
    $stmt->execute(array($id, $user['id']));
    if (!$stmt->fetch()) jsonResponse(array('error' => 'Playlist no encontrada'), 404);
    if (!$nombre) jsonResponse(array('error' => 'El nombre no puede estar vacio.'), 422);
    $desc = trim(isset($_POST['descripcion']) ? $_POST['descripcion'] : '');
    $db->prepare('UPDATE playlists SET nombre = ?, descripcion = ? WHERE id = ?')
       ->execute(array($nombre, $desc ? $desc : null, $id));
    jsonResponse(array('success' => true));
}

if ($action === 'add_song') {
    $playlist_id = (int)(isset($_POST['playlist_id']) ? $_POST['playlist_id'] : 0);
    $song_id     = (int)(isset($_POST['song_id'])     ? $_POST['song_id']     : 0);
    $stmt = $db->prepare('SELECT id FROM playlists WHERE id = ? AND user_id = ?');
    $stmt->execute(array($playlist_id, $user['id']));
    if (!$stmt->fetch()) jsonResponse(array('error' => 'Playlist no encontrada'), 404);
    $stmt = $db->prepare('SELECT COALESCE(MAX(orden),0)+1 AS sig FROM playlist_songs WHERE playlist_id = ?');
    $stmt->execute(array($playlist_id));
    $orden = $stmt->fetchColumn();
    try {
        $db->prepare('INSERT INTO playlist_songs (playlist_id, song_id, orden) VALUES (?, ?, ?)')->execute(array($playlist_id, $song_id, $orden));
        jsonResponse(array('success' => true, 'message' => 'Cancion agregada.'));
    } catch (PDOException $e) {
        jsonResponse(array('error' => 'Esa cancion ya esta en la playlist.'), 409);
    }
}

if ($action === 'remove_song') {
    $playlist_id = (int)(isset($_POST['playlist_id']) ? $_POST['playlist_id'] : 0);
    $song_id     = (int)(isset($_POST['song_id'])     ? $_POST['song_id']     : 0);
    $stmt = $db->prepare('SELECT id FROM playlists WHERE id = ? AND user_id = ?');
    $stmt->execute(array($playlist_id, $user['id']));
    if (!$stmt->fetch()) jsonResponse(array('error' => 'Playlist no encontrada'), 404);
    $db->prepare('DELETE FROM playlist_songs WHERE playlist_id = ? AND song_id = ?')->execute(array($playlist_id, $song_id));
    jsonResponse(array('success' => true));
}

if ($action === 'delete') {
    $id   = (int)(isset($_POST['id']) ? $_POST['id'] : 0);
    $stmt = $db->prepare('SELECT id, portada FROM playlists WHERE id = ? AND user_id = ?');
    $stmt->execute(array($id, $user['id']));
    $playlist = $stmt->fetch();
    if (!$playlist) jsonResponse(array('error' => 'Playlist no encontrada'), 404);
    if ($playlist['portada'] && file_exists(ROOT_PATH . '/' . $playlist['portada'])) {
        unlink(ROOT_PATH . '/' . $playlist['portada']);
    }
    $db->prepare('DELETE FROM playlists WHERE id = ?')->execute(array($id));
    jsonResponse(array('success' => true));
}

jsonResponse(array('error' => 'Accion no valida'), 400);
