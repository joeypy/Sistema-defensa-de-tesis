<?php
session_start();

// Si ya está autenticado, redirigir al dashboard
if (isset($_SESSION['usuario_id'])) {
    header("Location: /src/pages/index.php");
    exit();
}

// Si no está autenticado, mostrar el login
include __DIR__ . '/src/pages/auth/login.php';
?>

