<?php

include __DIR__ . '/../../includes/conexion.php';

// Editar producto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && !isset($_POST['eliminar'])) {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio'];
    $stock = (int)$_POST['stock'];
    $stock_minimo = isset($_POST['stock_minimo']) ? (int)$_POST['stock_minimo'] : 0;

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
        
        $stmt = $pdo->prepare("UPDATE productos SET nombre=?, precio=?, stock=?, stock_minimo=? WHERE id=?");
        $stmt->execute([$nombre, $precio, $stock, $stock_minimo, $id]);

        echo json_encode([
            'success' => true,
            'producto' => [
                'id' => $id,
                'nombre' => $nombre,
                'precio' => $precio,
                'stock' => $stock,
                'stock_minimo' => $stock_minimo
            ]
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}
// Eliminar producto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar']) && isset($_POST['id'])) {
    $id = $_POST['id'];
    
    try {
        // Obtener información del producto
        $stmt = $pdo->prepare("SELECT nombre FROM productos WHERE id = ?");
        $stmt->execute([$id]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$producto) {
            echo json_encode(['success' => false, 'error' => 'Producto no encontrado.']);
            exit;
        }
        
        $nombre_producto = $producto['nombre'];
        
        // Verificar si hay registros en detalles_venta
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total 
            FROM detalles_venta 
            WHERE producto_id = ?
        ");
        $stmt->execute([$id]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($resultado['total'] > 0) {
            // Obtener información detallada de las ventas asociadas
            $stmt = $pdo->prepare("
                SELECT DISTINCT
                    v.id as venta_id,
                    v.numero_factura,
                    v.fecha,
                    c.nombre as cliente_nombre,
                    c.identificacion as cliente_identificacion
                FROM detalles_venta dv
                INNER JOIN ventas v ON dv.venta_id = v.id
                INNER JOIN clientes c ON v.cliente_id = c.id
                WHERE dv.producto_id = ?
                ORDER BY v.fecha DESC
                LIMIT 10
            ");
            $stmt->execute([$id]);
            $ventas_asociadas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Construir mensaje de error con información de las ventas
            $mensaje = "<div class='alert alert-warning mb-3'>";
            $mensaje .= "<i class='bi bi-exclamation-triangle me-2'></i>";
            $mensaje .= "No se puede eliminar el producto <strong>'$nombre_producto'</strong> porque está asociado a <strong>" . $resultado['total'] . "</strong> registro(s) de venta(s).";
            $mensaje .= "</div>";
            
            $mensaje .= "<p class='mb-2'><strong>Ventas asociadas:</strong></p>";
            $mensaje .= "<div class='table-responsive'>";
            $mensaje .= "<table class='table table-sm table-bordered'>";
            $mensaje .= "<thead class='table-light'>";
            $mensaje .= "<tr><th>Factura</th><th>Cliente</th><th>Identificación</th><th>Fecha</th></tr>";
            $mensaje .= "</thead>";
            $mensaje .= "<tbody>";
            
            foreach ($ventas_asociadas as $venta) {
                $fecha_formateada = date('d/m/Y H:i', strtotime($venta['fecha']));
                $mensaje .= "<tr>";
                $mensaje .= "<td><strong>#{$venta['numero_factura']}</strong></td>";
                $mensaje .= "<td>" . htmlspecialchars($venta['cliente_nombre']) . "</td>";
                $mensaje .= "<td>" . htmlspecialchars($venta['cliente_identificacion']) . "</td>";
                $mensaje .= "<td>$fecha_formateada</td>";
                $mensaje .= "</tr>";
            }
            
            if ($resultado['total'] > 10) {
                $mensaje .= "<tr><td colspan='4' class='text-center text-muted'><em>... y " . ($resultado['total'] - 10) . " venta(s) más</em></td></tr>";
            }
            
            $mensaje .= "</tbody>";
            $mensaje .= "</table>";
            $mensaje .= "</div>";
            $mensaje .= "<div class='alert alert-info mt-3 mb-0'>";
            $mensaje .= "<i class='bi bi-info-circle me-2'></i>";
            $mensaje .= "<small>Para eliminar este producto, primero debe eliminar o modificar las ventas asociadas.</small>";
            $mensaje .= "</div>";
            
            echo json_encode([
                'success' => false, 
                'error' => $mensaje,
                'ventas_count' => $resultado['total']
            ]);
            exit;
        }
        
        // Si no hay relaciones, proceder con la eliminación
        // Primero eliminar registros de historial_precios si existen (sin restricción)
        try {
            $stmt = $pdo->prepare("DELETE FROM historial_precios WHERE producto_id=?");
            $stmt->execute([$id]);
        } catch (Exception $e) {
            // Si la tabla no existe o no hay registros, continuar
        }
        
        // Eliminar el producto
        $stmt = $pdo->prepare("DELETE FROM productos WHERE id=?");
        $stmt->execute([$id]);
        
        echo json_encode(['success' => true, 'message' => 'Producto eliminado exitosamente.']);
    } catch (PDOException $e) {
        // Capturar errores de foreign key constraint
        if ($e->getCode() == '23000' || strpos($e->getMessage(), 'foreign key constraint') !== false) {
            echo json_encode([
                'success' => false, 
                'error' => "No se puede eliminar el producto porque tiene registros relacionados en otras tablas. Por favor, verifique las ventas asociadas."
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error al eliminar el producto: ' . $e->getMessage()]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Error al eliminar el producto: ' . $e->getMessage()]);
    }
    exit;
}