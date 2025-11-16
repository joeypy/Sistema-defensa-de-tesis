<?php
// Configuración de base de datos - usa variables de entorno si están disponibles (Docker)
// o valores por defecto para desarrollo local

// En Docker, las variables de entorno están disponibles
$host = getenv('DB_HOST') ?: "db";
$dbname = getenv('DB_NAME') ?: "sistema_compras_zapatos";
$user = getenv('DB_USER') ?: "root";
$pass = getenv('DB_PASS') ?: "rootpassword";

// Si no hay variables de entorno (desarrollo local), usar valores por defecto
if (empty(getenv('DB_HOST'))) {
    $host = "localhost";
    $pass = "";
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>