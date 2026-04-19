<?php
// ============================================================
//  config.php — Configuración central de Soundwave
// ============================================================

define('DB_HOST',     'localhost');
define('DB_NAME',     'soundwave');
define('DB_USER',     'root');
define('DB_PASS',     '');
define('DB_CHARSET',  'utf8mb4');
define('BASE_URL',    'http://localhost/soundwave');
define('ROOT_PATH',   __DIR__ . '/..');
define('LASTFM_API_KEY', '59b5712647eac21fcc1dd1a181128013');
define('LASTFM_BASE',    'https://ws.audioscrobbler.com/2.0/');
define('MAX_FILE_SIZE',  52428800);
define('ALLOWED_AUDIO',  array('mp3', 'wav', 'ogg', 'm4a'));
define('ALLOWED_IMAGE',  array('jpg', 'jpeg', 'png', 'webp'));

function getDB() {
    static $pdo = null;
    if ($pdo !== null) return $pdo;
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, array(
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ));
    return $pdo;
}

function jsonResponse($data, $status = 200) {
    // Limpiar cualquier output previo que rompa el JSON
    if (ob_get_level()) ob_clean();
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function isApiRequest() {
    // Detectar si es llamada AJAX/API
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        return true;
    }
    // Si espera JSON
    if (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false) {
        return true;
    }
    // Si la URL tiene action= (llamada directa a API)
    if (isset($_GET['action']) || isset($_POST['action'])) {
        return true;
    }
    return false;
}

function requireAuth($api = false) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['user'])) {
        if ($api || isApiRequest()) {
            jsonResponse(array('error' => 'No autenticado', 'redirect' => BASE_URL . '/frontend/pages/login.php'), 401);
        }
        header('Location: ' . BASE_URL . '/frontend/pages/login.php');
        exit;
    }
    return $_SESSION['user'];
}

function requireAdmin($api = false) {
    $user = requireAuth($api);
    if (($user['role'] ?? 'user') !== 'admin') {
        if ($api || isApiRequest()) {
            jsonResponse(array('error' => 'Acceso denegado. Solo administradores.'), 403);
        }
        header('Location: ' . BASE_URL . '/frontend/pages/home.php');
        exit;
    }
    return $user;
}

function isAdmin() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return (($_SESSION['user']['role'] ?? '') === 'admin');
}
