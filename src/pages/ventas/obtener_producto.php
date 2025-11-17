<?php
require_once __DIR__ . '/../../includes/conexion.php';

header('Content-Type: application/json');

// Log del lado del servidor
error_log("🔍 [obtener_producto.php] Solicitud recibida. GET: " . print_r($_GET, true));

if (!isset($_GET['id']) || empty($_GET['id'])) {
    error_log("❌ [obtener_producto.php] ID de producto no proporcionado");
    echo json_encode(['success' => false, 'error' => 'ID de producto no proporcionado']);
    exit;
}

$producto_id = (int)$_GET['id'];
error_log("🔍 [obtener_producto.php] Buscando producto con ID: " . $producto_id);

try {
    $stmt = $pdo->prepare("
        SELECT 
            p.id,
            p.nombre,
            p.descripcion,
            p.precio,
            p.stock,
            p.stock_minimo
        FROM productos p
        WHERE p.id = ?
    ");
    $stmt->execute([$producto_id]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$producto) {
        error_log("❌ [obtener_producto.php] Producto con ID $producto_id no encontrado");
        echo json_encode(['success' => false, 'error' => 'Producto no encontrado']);
        exit;
    }
    
    error_log("✅ [obtener_producto.php] Producto encontrado: " . json_encode($producto));
    echo json_encode(['success' => true, 'producto' => $producto]);
} catch (Exception $e) {
    error_log("❌ [obtener_producto.php] Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>

