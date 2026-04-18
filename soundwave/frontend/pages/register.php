<?php
require_once __DIR__ . '/../../backend/config.php';
session_start();

if (!empty($_SESSION['user'])) {
    header('Location: ' . BASE_URL . '/frontend/pages/home.php');
    exit;
}

$flash_error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Registro — Soundwave</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="auth-page">
  <div class="auth-card">
    <div class="auth-logo">Sound<span>wave</span></div>
    <p class="auth-tagline">Empieza a escuchar gratis.</p>

    <?php if ($flash_error): ?>
      <div class="flash-error"><?= $flash_error ?></div>
    <?php endif; ?>

    <h2 class="auth-title">Crear cuenta</h2>

    <form method="POST" action="<?= BASE_URL ?>/backend/auth.php">
      <input type="hidden" name="action" value="register">

      <div class="form-group">
        <label class="form-label">Tu nombre</label>
        <input type="text" name="nombre" placeholder="Ej. Juan García" required minlength="2">
      </div>

      <div class="form-group">
        <label class="form-label">Correo electrónico</label>
        <input type="email" name="email" placeholder="tu@correo.com" required>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Contraseña</label>
          <input type="password" name="password" placeholder="Mín. 6 caracteres" required minlength="6">
        </div>
        <div class="form-group">
          <label class="form-label">Confirmar</label>
          <input type="password" name="confirm" placeholder="Repite la contraseña" required>
        </div>
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:.5rem">
        Crear cuenta
      </button>
    </form>

    <div class="auth-footer">
      ¿Ya tienes cuenta? <a href="<?= BASE_URL ?>/frontend/pages/login.php">Inicia sesión</a>
    </div>
  </div>
</div>
</body>
</html>
