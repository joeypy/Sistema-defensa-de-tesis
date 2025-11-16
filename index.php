<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/src/includes/config.php';

// Si ya está autenticado, redirigir al dashboard
if (isset($_SESSION['usuario_id'])) {
    header("Location: " . PAGES_URL . "/index.php");
    exit();
}

// Si no está autenticado, mostrar el login
include __DIR__ . '/src/pages/auth/login.php';
?>

