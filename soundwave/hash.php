<?php
// Herramienta para generar hash de contraseña
// Accede a: http://localhost/soundwave/hash.php
// ELIMINA este archivo en producción

$hash = '';
if (!empty($_POST['pass'])) {
    $hash = password_hash($_POST['pass'], PASSWORD_BCRYPT, ['cost' => 12]);
}
?>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><title>Generar hash</title>
<style>body{font-family:sans-serif;max-width:500px;margin:2rem auto;padding:1rem}
input,button{padding:.5rem;font-size:1rem}input{width:100%;margin:.5rem 0}
.hash{background:#f0f0f0;padding:1rem;border-radius:6px;word-break:break-all;margin-top:1rem}
</style></head>
<body>
<h2>Generador de hash — Soundwave</h2>
<form method="POST">
  <label>Contraseña:</label>
  <input type="text" name="pass" placeholder="Escribe la contraseña" required>
  <button type="submit">Generar hash</button>
</form>
<?php if ($hash): ?>
  <div class="hash">
    <strong>Hash generado:</strong><br>
    <code><?= htmlspecialchars($hash) ?></code>
  </div>
  <p><small>Copia este hash y pégalo en el campo <code>password</code> directamente en la base de datos (phpMyAdmin).</small></p>
<?php endif; ?>
<hr>
<p style="color:red"><strong>⚠️ Elimina este archivo cuando termines de usarlo.</strong></p>
</body>
</html>
