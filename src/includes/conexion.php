<?php
// Configuración de base de datos - usa variables de entorno si están disponibles (Docker)
// o valores por defecto para desarrollo local

// En Docker, las variables de entorno están disponibles
$host = getenv('DB_HOST') ?: "db";
$dbname = getenv('DB_NAME') ?: "sistema_admin";
$user = getenv('DB_USER') ?: "root";
$pass = getenv('DB_PASS') ?: "rootpassword";

// Si no hay variables de entorno (desarrollo local), usar valores por defecto
if (empty(getenv('DB_HOST'))) {
    $host = "localhost";
    $pass = "";
}

try {
    // Timeout de conexión más corto para evitar que el healthcheck se cuelgue
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 3, // Timeout de 3 segundos
        PDO::ATTR_PERSISTENT => false
    ]);
} catch (PDOException $e) {
    // En producción, no mostrar el error completo por seguridad
    // En desarrollo local, mostrar el error para facilitar debugging
    $isProduction = getenv('PHP_ENV') === 'production' || getenv('RAILWAY_ENVIRONMENT') !== false;
    
    if ($isProduction) {
        error_log("Error de conexión a BD: " . $e->getMessage());
        throw new Exception("Error de conexión a la base de datos");
    } else {
        // En desarrollo local, mostrar el error
        die("Error de conexión: " . $e->getMessage());
    }
}
?>