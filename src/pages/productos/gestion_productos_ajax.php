<?php

include __DIR__ . '/../../includes/conexion.php';

// Editar producto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && !isset($_POST['eliminar'])) {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $precio_compra = $_POST['precio_compra'];
    $stock = $_POST['stock'];
    $stock_minimo = $_POST['stock_minimo'];
    $proveedor_id = $_POST['proveedor_id'];

    try {
        $stmt = $pdo->prepare("UPDATE productos SET nombre=?, precio_compra=?, stock=?, stock_minimo=?, proveedor_id=? WHERE id=?");
        $stmt->execute([$nombre, $precio_compra, $stock, $stock_minimo, $proveedor_id, $id]);

        $stmtProv = $pdo->prepare("SELECT nombre FROM proveedores WHERE id=?");
        $stmtProv->execute([$proveedor_id]);
        $proveedor_nombre = $stmtProv->fetchColumn();

        echo json_encode([
            'success' => true,
            'producto' => [
                'id' => $id,
                'nombre' => $nombre,
                'precio_compra' => $precio_compra,
                'stock' => $stock,
                'stock_minimo' => $stock_minimo,
                'proveedor_nombre' => $proveedor_nombre
            ]
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar']) && isset($_POST['id'])) {
    $id = $_POST['id'];
    try {
        // Elimina primero los precios históricos relacionados
        $stmt = $pdo->prepare("DELETE FROM historial_precios WHERE producto_id=?");
        $stmt->execute([$id]);

        // Ahora elimina el producto
        $stmt = $pdo->prepare("DELETE FROM productos WHERE id=?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}
// Eliminar producto (y sus detalles de compra)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar']) && isset($_POST['id'])) {
    $id = $_POST['id'];
    try {
        // Eliminar detalles de compra relacionados
        $stmt = $pdo->prepare("DELETE FROM detalles_compra WHERE producto_id=?");
        $stmt->execute([$id]);

        // Eliminar producto
        $stmt = $pdo->prepare("DELETE FROM productos WHERE id=?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}