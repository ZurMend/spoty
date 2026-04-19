<?php
// Punto de entrada raíz: redirige al login o al home según sesión
require_once __DIR__ . '/backend/config.php';
session_start();

if (!empty($_SESSION['user'])) {
    header('Location: ' . BASE_URL . '/frontend/pages/home.php');
} else {
    header('Location: ' . BASE_URL . '/frontend/pages/login.php');
}
exit;
