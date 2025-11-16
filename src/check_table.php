<?php
include __DIR__ . '/includes/conexion.php';
$result = $pdo->query('DESCRIBE detalles_compra');
$columns = $result->fetchAll(PDO::FETCH_ASSOC);
echo 'Columnas de detalles_compra:' . PHP_EOL;
foreach ($columns as $col) {
    echo '- ' . $col['Field'] . ' (' . $col['Type'] . ')' . PHP_EOL;
}
?>