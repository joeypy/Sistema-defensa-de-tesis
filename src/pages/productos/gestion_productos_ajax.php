<?php

include __DIR__ . '/../../includes/conexion.php';

// Editar producto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && !isset($_POST['eliminar'])) {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $precio_compra = $_POST['precio_compra'];
    $stock = (int)$_POST['stock'];
    $stock_minimo = (int)$_POST['stock_minimo'];
    $proveedor_id = $_POST['proveedor_id'];

    try {
        // Validar que el stock no sea negativo
        if ($stock < 0) {
            echo json_encode(['success' => false, 'error' => 'El stock no puede ser negativo. El valor mínimo permitido es 0.']);
            exit;
        }
        
        // Validar que el stock_minimo no sea negativo
        if ($stock_minimo < 0) {
            echo json_encode(['success' => false, 'error' => 'El stock mínimo no puede ser negativo. El valor mínimo permitido es 0.']);
            exit;
        }
        
        // Obtener el stock actual del producto antes de actualizar
        $stmt = $pdo->prepare("SELECT stock, nombre FROM productos WHERE id = ?");
        $stmt->execute([$id]);
        $producto_actual = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$producto_actual) {
            echo json_encode(['success' => false, 'error' => 'Producto no encontrado.']);
            exit;
        }
        
        $stock_actual = (int)$producto_actual['stock'];
        $nombre_producto = $producto_actual['nombre'];
        
        // Validar que si se está reduciendo el stock, no quede negativo
        // Si el nuevo stock es menor que el actual, validar que no sea negativo
        if ($stock < $stock_actual) {
            // Se está reduciendo el stock, validar que el nuevo valor no sea negativo
            if ($stock < 0) {
                echo json_encode([
                    'success' => false, 
                    'error' => "No se puede reducir el stock del producto '$nombre_producto'. Stock actual: $stock_actual. El stock no puede ser negativo."
                ]);
                exit;
            }
        }
        
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