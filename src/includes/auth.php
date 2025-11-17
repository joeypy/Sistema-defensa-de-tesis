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
    // Como solo hay un usuario en la aplicación, siempre es admin
    return isset($_SESSION['usuario_id']);
}

// Opcional: Verificación adicional de seguridad
if (basename($_SERVER['SCRIPT_NAME']) === 'auth.php') {
    die('Acceso prohibido');
}
?>