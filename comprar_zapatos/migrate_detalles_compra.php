<?php
include 'includes/conexion.php';

try {
    // Cambiar el nombre de la columna proveedor_id a marca_id
    $pdo->exec("ALTER TABLE detalles_compra CHANGE proveedor_id marca_id INT(11) NOT NULL");

    echo "Columna proveedor_id cambiada exitosamente a marca_id en la tabla detalles_compra\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>