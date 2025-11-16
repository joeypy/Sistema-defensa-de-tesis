<?php

include __DIR__ . '/../../includes/conexion.php';

// Editar cliente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && !isset($_POST['eliminar'])) {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $identificacion = $_POST['identificacion'];
    $direccion = $_POST['direccion'];

    try {
        $stmt = $pdo->prepare("UPDATE clientes SET nombre=?, identificacion=?, direccion=? WHERE id=?");
        $stmt->execute([$nombre, $identificacion, $direccion, $id]);

        $stmt = $pdo->prepare("SELECT id, nombre, identificacion, direccion, creado_en FROM clientes WHERE id=?");
        $stmt->execute([$id]);
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'cliente' => $cliente]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// Eliminar cliente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar']) && isset($_POST['id'])) {
    $id = $_POST['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM clientes WHERE id=?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}
?>