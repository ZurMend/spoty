<?php
require_once __DIR__ . '/../../backend/config.php';
session_start();

// Si ya está logueado, redirigir
if (!empty($_SESSION['user'])) {
    header('Location: ' . BASE_URL . '/frontend/pages/home.php');
    exit;
}

$flash_error   = $_SESSION['flash_error']   ?? null;
$flash_success = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_error'], $_SESSION['flash_success']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Iniciar sesión — Soundwave</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="auth-page">
  <div class="auth-card">
    <div class="auth-logo">Sound<span>wave</span></div>
    <p class="auth-tagline">Tu música, tu ritmo.</p>

    <?php if ($flash_error):   ?><div class="flash-error"><?= $flash_error ?></div><?php endif; ?>
    <?php if ($flash_success): ?><div class="flash-success"><?= $flash_success ?></div><?php endif; ?>

    <h2 class="auth-title">Bienvenido de vuelta</h2>

    <form method="POST" action="<?= BASE_URL ?>/backend/auth.php">
      <input type="hidden" name="action" value="login">

      <div class="form-group">
        <label class="form-label">Correo electrónico</label>
        <input type="email" name="email" placeholder="tu@correo.com" required autofocus>
      </div>

      <div class="form-group">
        <label class="form-label">Contraseña</label>
        <input type="password" name="password" placeholder="••••••••" required>
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:.5rem">
        Iniciar sesión
      </button>
    </form>

    <div class="auth-footer">
      ¿No tienes cuenta? <a href="<?= BASE_URL ?>/frontend/pages/register.php">Regístrate gratis</a>
    </div>
  </div>
</div>
</body>
</html>
