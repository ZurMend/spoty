<?php
// ============================================================
//  auth.php — Login, Registro y Logout
// ============================================================
ob_start();
require_once __DIR__ . '/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

// ── LOGOUT ───────────────────────────────────────────────────
if ($action === 'logout') {
    $_SESSION = array();
    session_destroy();
    header('Location: ' . BASE_URL . '/frontend/pages/login.php');
    exit;
}

// ── REGISTRO ─────────────────────────────────────────────────
if ($action === 'register') {
    $nombre   = trim(isset($_POST['nombre'])   ? $_POST['nombre']   : '');
    $email    = trim(isset($_POST['email'])    ? $_POST['email']    : '');
    $password = trim(isset($_POST['password']) ? $_POST['password'] : '');
    $confirm  = trim(isset($_POST['confirm'])  ? $_POST['confirm']  : '');

    $errors = array();
    if (strlen($nombre) < 2)                        $errors[] = 'El nombre debe tener al menos 2 caracteres.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email invalido.';
    if (strlen($password) < 6)                      $errors[] = 'La contrasena debe tener al menos 6 caracteres.';
    if ($password !== $confirm)                     $errors[] = 'Las contrasenas no coinciden.';

    if ($errors) {
        $_SESSION['flash_error'] = implode('<br>', $errors);
        header('Location: ' . BASE_URL . '/frontend/pages/register.php');
        exit;
    }

    $db   = getDB();
    $stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute(array($email));
    if ($stmt->fetch()) {
        $_SESSION['flash_error'] = 'Ese email ya esta registrado.';
        header('Location: ' . BASE_URL . '/frontend/pages/register.php');
        exit;
    }

    $hash = password_hash($password, PASSWORD_BCRYPT, array('cost' => 12));
    $stmt = $db->prepare('INSERT INTO users (nombre, email, password, role) VALUES (?, ?, ?, ?)');
    $stmt->execute(array($nombre, $email, $hash, 'user'));

    $_SESSION['flash_success'] = 'Cuenta creada. Ya puedes iniciar sesion.';
    header('Location: ' . BASE_URL . '/frontend/pages/login.php');
    exit;
}

// ── LOGIN ─────────────────────────────────────────────────────
if ($action === 'login') {
    $email    = trim(isset($_POST['email'])    ? $_POST['email']    : '');
    $password = trim(isset($_POST['password']) ? $_POST['password'] : '');

    if (!$email || !$password) {
        $_SESSION['flash_error'] = 'Completa todos los campos.';
        header('Location: ' . BASE_URL . '/frontend/pages/login.php');
        exit;
    }

    $db   = getDB();
    $stmt = $db->prepare('SELECT id, nombre, email, password, avatar, role FROM users WHERE email = ?');
    $stmt->execute(array($email));
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        $_SESSION['flash_error'] = 'Credenciales incorrectas.';
        header('Location: ' . BASE_URL . '/frontend/pages/login.php');
        exit;
    }

    $_SESSION['user'] = array(
        'id'     => $user['id'],
        'nombre' => $user['nombre'],
        'email'  => $user['email'],
        'avatar' => $user['avatar'],
        'role'   => $user['role'],
    );

    if ($user['role'] === 'admin') {
        header('Location: ' . BASE_URL . '/frontend/pages/admin.php');
    } else {
        header('Location: ' . BASE_URL . '/frontend/pages/home.php');
    }
    exit;
}

header('Location: ' . BASE_URL . '/frontend/pages/login.php');
exit;
