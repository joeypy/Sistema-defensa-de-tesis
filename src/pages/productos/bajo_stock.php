<?php
require_once __DIR__ . '/../../includes/config.php';
include __DIR__ . '/../../includes/auth.php';
include __DIR__ . '/../../includes/conexion.php';
verificarAutenticacion();

// Consulta productos con bajo stock (stock <= stock_minimo)
$productosBajoStock = $pdo->query("
    SELECT p.id, p.nombre, p.stock, p.stock_minimo
    FROM productos p
    WHERE p.stock <= p.stock_minimo
    ORDER BY p.stock ASC
")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../../includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-warning mb-0">
            <i class="bi bi-exclamation-triangle me-2"></i>Productos con Bajo Stock
        </h2>
        <a href="gestion_productos.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver a Productos
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <?php if (count($productosBajoStock) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Stock Actual</th>
                            <th>Stock Mínimo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productosBajoStock as $producto): ?>
                        <tr class="table-warning">
                            <td><?= $producto['id'] ?></td>
                            <td><?= htmlspecialchars($producto['nombre']) ?></td>
                            <td><?= $producto['stock'] ?></td>
                            <td><?= $producto['stock_minimo'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="alert alert-success m-4 text-center">
                <i class="bi bi-check-circle-fill me-2"></i>
                ¡Excelente! No hay productos con bajo stock.
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>


<?php include __DIR__ . '/../../includes/footer.php'; ?>