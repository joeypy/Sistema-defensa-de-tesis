<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../includes/config.php';

$error = null;
$pdo = null;

// Solo incluir conexión si no estamos en el healthcheck
if (basename($_SERVER['PHP_SELF']) !== 'healthcheck.php') {
    try {
        include __DIR__ . '/../../includes/conexion.php';
    } catch (Exception $e) {
        // Si no hay conexión, mostrar error pero no morir
        $error = "Error de conexión a la base de datos. Por favor, verifica la configuración.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$pdo) {
        $error = "Error de conexión a la base de datos. Por favor, verifica la configuración.";
    } else {
        $username = $_POST['username'];
        $password = $_POST['password'];
        
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE username = ?");
        $stmt->execute([$username]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario) {
            if (password_verify($password, $usuario['password'])) {
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                header("Location: " . PAGES_URL . "/index.php");
                exit();
            } else {
                $error = "Credenciales incorrecta";
            }
        } else {
            $error = "Usuario no encontrado";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - X Sales</title>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    
</head>
<body>
    <div class="container">
        <div class="left-panel">
            <img src="<?= ASSETS_URL ?>/images/logo1.png" alt="X Sales" style="max-width:320px; width:100%; margin-bottom:30px; border-radius:5px;">            <h1>XSALES</h1>
            <p>Nuestro portal de gestión de compras</p>
        </div>
        <div class="right-panel">
            <h2>Iniciar Sesión</h2>
            <?php if ($error): ?>
                <div class="error"><?= $error ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label for="username">Usuario:</label>
                    <input type="text" id="username" name="username" required autocomplete="username">
                </div>
                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                </div>
                <button type="submit">Ingresar</button>
            </form>
        </div>
    </div>
</body>
</html>