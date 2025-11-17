<?php
include __DIR__ . '/../../includes/conexion.php';

// Obtener todos los productos
$stmt = $pdo->prepare("SELECT id, nombre, precio FROM productos ORDER BY nombre");
$stmt->execute();
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($productos);

