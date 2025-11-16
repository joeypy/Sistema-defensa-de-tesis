<?php
require_once __DIR__ . '/../../includes/config.php';
include __DIR__ . '/../../includes/auth.php';
include __DIR__ . '/../../includes/conexion.php';
require __DIR__ . '/api_proveedor.php';
verificarAutenticacion();

// Obtener proveedores con API configurada
$proveedores = $pdo->query("
    SELECT id, nombre, api_key, api_endpoint 
    FROM proveedores 
    WHERE api_key IS NOT NULL AND api_endpoint IS NOT NULL
")->fetchAll(PDO::FETCH_ASSOC);

foreach ($proveedores as $proveedor) {
    try {
        $api = new ApiProveedor($proveedor['api_key'], $proveedor['api_endpoint']);
        $productosApi = $api->obtenerProductos();
        
        foreach ($productosApi as $productoApi) {
            // Verificar si el producto ya existe
            $stmt = $pdo->prepare("SELECT id FROM productos WHERE codigo_externo = ?");
            $stmt->execute([$productoApi['codigo']]);
            $productoExistente = $stmt->fetch();
            
            if ($productoExistente) {
                // Actualizar producto existente
                $stmt = $pdo->prepare("
                    UPDATE productos 
                    SET precio_compra = ?, stock = stock + ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $productoApi['precio'],
                    $productoApi['stock'],
                    $productoExistente['id']
                ]);
            } else {
                // Crear nuevo producto
                $stmt = $pdo->prepare("
                    INSERT INTO productos 
                    (nombre, precio_compra, color, stock, proveedor_id, codigo_externo)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $productoApi['nombre'],
                    $productoApi['precio'],
                    $productoApi['color'],
                    $productoApi['stock'],
                    $proveedor['id'],
                    $productoApi['codigo']
                ]);
            }
        }
        
        $_SESSION['mensaje'] = "Productos sincronizados exitosamente!";
    } catch (Exception $e) {
        $_SESSION['error'] = "Error al sincronizar con " . $proveedor['nombre'] . ": " . $e->getMessage();
    }
}

header("Location: " . PAGES_URL . "/proveedores/gestion_proveedores.php");
exit();
?>