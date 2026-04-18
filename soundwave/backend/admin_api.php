<?php
// ============================================================
//  admin_api.php — Endpoints exclusivos para administradores
// ============================================================
ob_start();
require_once __DIR__ . '/config.php';

$user   = requireAdmin(true);
$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');
$db     = getDB();

if ($action === 'stats') {
    $songs     = $db->query('SELECT COUNT(*) FROM songs')->fetchColumn();
    $users     = $db->query('SELECT COUNT(*) FROM users')->fetchColumn();
    $plays     = $db->query('SELECT COUNT(*) FROM play_history')->fetchColumn();
    $playlists = $db->query('SELECT COUNT(*) FROM playlists')->fetchColumn();
    jsonResponse(array('songs' => $songs, 'users' => $users, 'plays' => $plays, 'playlists' => $playlists));
}

if ($action === 'users') {
    $stmt = $db->query('SELECT id, nombre, email, role, created_at FROM users ORDER BY created_at DESC');
    jsonResponse(array('users' => $stmt->fetchAll()));
}

if ($action === 'delete_user') {
    $id = (int)(isset($_POST['id']) ? $_POST['id'] : 0);
    if ($id === (int)$user['id']) jsonResponse(array('error' => 'No puedes eliminarte a ti mismo.'), 422);
    $stmt = $db->prepare('SELECT id, role FROM users WHERE id = ?');
    $stmt->execute(array($id));
    $target = $stmt->fetch();
    if (!$target) jsonResponse(array('error' => 'Usuario no encontrado'), 404);
    if ($target['role'] === 'admin') jsonResponse(array('error' => 'No puedes eliminar a otro administrador.'), 403);
    $db->prepare('DELETE FROM users WHERE id = ?')->execute(array($id));
    jsonResponse(array('success' => true));
}

if ($action === 'change_role') {
    $id   = (int)(isset($_POST['id'])   ? $_POST['id']   : 0);
    $role = trim(isset($_POST['role'])  ? $_POST['role'] : '');
    if (!in_array($role, array('admin','user'), true)) jsonResponse(array('error' => 'Rol no valido'), 422);
    if ($id === (int)$user['id']) jsonResponse(array('error' => 'No puedes cambiar tu propio rol.'), 422);
    $db->prepare('UPDATE users SET role = ? WHERE id = ?')->execute(array($role, $id));
    jsonResponse(array('success' => true));
}

jsonResponse(array('error' => 'Accion no valida'), 400);
