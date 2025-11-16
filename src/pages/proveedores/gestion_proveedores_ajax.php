<?php

include __DIR__ . '/../../includes/conexion.php';

// Editar proveedor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && !isset($_POST['eliminar'])) {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $contacto = $_POST['contacto'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];

    try {
        $stmt = $pdo->prepare("UPDATE proveedores SET nombre=?, contacto=?, telefono=?, email=? WHERE id=?");
        $stmt->execute([$nombre, $contacto, $telefono, $email, $id]);

        $stmt = $pdo->prepare("SELECT * FROM proveedores WHERE id=?");
        $stmt->execute([$id]);
        $proveedor = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'proveedor' => $proveedor]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// Eliminar proveedor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar']) && isset($_POST['id'])) {
    $id = $_POST['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM proveedores WHERE id=?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}