<?php
include __DIR__ . '/../../includes/conexion.php';

$marca_id = $_GET['marca_id'] ?? '';
if (!$marca_id) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("SELECT id, nombre, precio_compra FROM productos WHERE marca_id = ? ORDER BY nombre");
$stmt->execute([$marca_id]);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($productos);