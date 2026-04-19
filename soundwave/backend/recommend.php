<?php
// ============================================================
//  recommend.php — Recomendaciones via Last.fm
// ============================================================
ob_start();
require_once __DIR__ . '/config.php';

$user   = requireAuth(true);
$action = isset($_GET['action']) ? $_GET['action'] : 'by_genre';
$db     = getDB();

function lastfm($method, $params) {
    $params['method']  = $method;
    $params['api_key'] = LASTFM_API_KEY;
    $params['format']  = 'json';
    if (!isset($params['limit'])) $params['limit'] = 10;
    $url = LASTFM_BASE . '?' . http_build_query($params);
    $ctx = stream_context_create(array('http' => array('timeout' => 8, 'header' => 'User-Agent: Soundwave/1.0')));
    $raw = @file_get_contents($url, false, $ctx);
    if (!$raw) return array();
    return json_decode($raw, true) ?: array();
}

function formatTracks($tracks) {
    $result = array();
    foreach ($tracks as $t) {
        $result[] = array(
            'nombre'  => isset($t['name'])   ? $t['name']   : '',
            'artista' => is_array($t['artist']) ? (isset($t['artist']['name']) ? $t['artist']['name'] : '') : (isset($t['artist']) ? $t['artist'] : ''),
            'url'     => isset($t['url'])    ? $t['url']    : '',
            'imagen'  => isset($t['image'][2]['#text']) ? $t['image'][2]['#text'] : (isset($t['image'][1]['#text']) ? $t['image'][1]['#text'] : null),
        );
    }
    return $result;
}

if ($action === 'by_genre') {
    $genre = trim(isset($_GET['genre']) ? $_GET['genre'] : '');
    if (!$genre) jsonResponse(array('error' => 'Falta genre'), 422);
    $tagMap = array('pop'=>'pop','rock'=>'rock','hip-hop'=>'hip-hop','electronica'=>'electronic',
               'jazz'=>'jazz','clasica'=>'classical','reggaeton'=>'reggaeton','metal'=>'metal',
               'latin'=>'latin','r&b'=>'rnb','soul'=>'soul','country'=>'country','blues'=>'blues',
               'indie'=>'indie');
    $tag  = isset($tagMap[strtolower($genre)]) ? $tagMap[strtolower($genre)] : strtolower($genre);
    $data = lastfm('tag.gettoptracks', array('tag' => $tag));
    $tracks = isset($data['tracks']['track']) ? $data['tracks']['track'] : array();
    jsonResponse(array('recommendations' => formatTracks($tracks), 'genre' => $genre));
}

if ($action === 'next') {
    $song_id = (int)(isset($_GET['song_id']) ? $_GET['song_id'] : 0);
    if (!$song_id) jsonResponse(array('error' => 'Falta song_id'), 422);

    $stmt = $db->prepare('SELECT genero, artista, nombre FROM songs WHERE id = ?');
    $stmt->execute(array($song_id));
    $song = $stmt->fetch();
    if (!$song) jsonResponse(array('error' => 'Cancion no encontrada'), 404);

    try {
        $db->prepare('INSERT INTO play_history (user_id, song_id) VALUES (?, ?)')->execute(array($user['id'], $song_id));
    } catch (Exception $e) {}

    $data   = lastfm('track.getsimilar', array('artist' => $song['artista'], 'track' => $song['nombre'], 'limit' => 5));
    $tracks = isset($data['similartracks']['track']) ? $data['similartracks']['track'] : array();
    if (empty($tracks)) {
        $data   = lastfm('tag.gettoptracks', array('tag' => strtolower($song['genero']), 'limit' => 5));
        $tracks = isset($data['tracks']['track']) ? $data['tracks']['track'] : array();
    }
    jsonResponse(array(
        'recommendations'  => formatTracks($tracks),
        'based_on_song'    => $song['nombre'],
        'based_on_genre'   => $song['genero']
    ));
}

if ($action === 'similar') {
    $artist = trim(isset($_GET['artist']) ? $_GET['artist'] : '');
    $track  = trim(isset($_GET['track'])  ? $_GET['track']  : '');
    if (!$artist || !$track) jsonResponse(array('error' => 'Se requieren artist y track'), 422);
    $data = lastfm('track.getsimilar', array('artist' => $artist, 'track' => $track));
    $tracks = isset($data['similartracks']['track']) ? $data['similartracks']['track'] : array();
    jsonResponse(array('recommendations' => formatTracks($tracks)));
}

jsonResponse(array('error' => 'Accion no valida'), 400);
