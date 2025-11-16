<?php
function verificarAutenticacion() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: login.php");
        exit();
    }
}

function esAdmin() {
    return (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin');
}

// Opcional: Verificación adicional de seguridad
if (basename($_SERVER['SCRIPT_NAME']) === 'auth.php') {
    die('Acceso prohibido');
}
?>