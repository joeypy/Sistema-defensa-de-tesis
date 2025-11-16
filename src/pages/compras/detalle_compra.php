<?php
require_once __DIR__ . '/../../includes/config.php';
include __DIR__ . '/../../includes/auth.php';
include __DIR__ . '/../../includes/conexion.php';
verificarAutenticacion();

if (!isset($_GET['id'])) {
    header("Location: " . PAGES_URL . "/compras/historial_compras.php");
    exit();
}

$compra_id = $_GET['id'];

// Obtener información de la compra
$stmt = $pdo->prepare("
    SELECT c.id, c.fecha, GROUP_CONCAT(DISTINCT m.nombre SEPARATOR ', ') AS marca, u.nombre AS usuario, c.total
    FROM compras c
    JOIN detalles_compra dc ON dc.compra_id = c.id
    JOIN marcas m ON dc.marca_id = m.id
    JOIN usuarios u ON c.usuario_id = u.id
    WHERE c.id = ?
    GROUP BY c.id, c.fecha, u.nombre, c.total
");
$stmt->execute([$compra_id]);
$compra = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$compra) {
    header("Location: " . PAGES_URL . "/compras/historial_compras.php");
    exit();
}

// Obtener detalles de la compra
$detalles = $pdo->prepare("
    SELECT pr.nombre, dc.cantidad, dc.precio_unitario, dc.subtotal
    FROM detalles_compra dc
    JOIN productos pr ON dc.producto_id = pr.id
    WHERE dc.compra_id = ?
");
$detalles->execute([$compra_id]);
$detalles = $detalles->fetchAll(PDO::FETCH_ASSOC);

// Obtener método de pago
$metodo_pago = $pdo->prepare("
    SELECT metodo, numero_referencia, monto
    FROM metodo_pago
    WHERE compra_id = ?
");
$metodo_pago->execute([$compra_id]);
$metodo_pago = $metodo_pago->fetch(PDO::FETCH_ASSOC);

include __DIR__ . '/../../includes/header.php';
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary mb-0">
            <i class="bi bi-receipt me-2"></i>Detalle de Compra #<?= $compra['id'] ?>
        </h2>
        <a href="historial_compras.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver al Historial
        </a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3"><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($compra['fecha'])) ?></div>
                <div class="col-md-3"><strong>Marca/s:</strong> <?= htmlspecialchars($compra['marca']) ?></div>
                <div class="col-md-3"><strong>Usuario:</strong> <?= htmlspecialchars($compra['usuario']) ?></div>
                <div class="col-md-3"><strong>Total:</strong> $<?= number_format($compra['total'], 2) ?></div>
            </div>
            <?php if ($metodo_pago): ?>
            <div class="row mb-3">
                <div class="col-md-4"><strong>Método de Pago:</strong> <?= htmlspecialchars($metodo_pago['metodo']) ?></div>
                <div class="col-md-4"><strong>Número de Referencia:</strong> <?= htmlspecialchars($metodo_pago['numero_referencia']) ?></div>
                <div class="col-md-4"><strong>Monto Pagado:</strong> $<?= number_format($metodo_pago['monto'], 2) ?></div>
            </div>
            <?php endif; ?>
            <h4 class="fw-semibold mb-3 text-secondary">Productos Comprados</h4>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio Unitario</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($detalles as $detalle): ?>
                            <tr>
                                <td><?= htmlspecialchars($detalle['nombre']) ?></td>
                                <td><?= $detalle['cantidad'] ?></td>
                                <td>$<?= number_format($detalle['precio_unitario'], 2) ?></td>
                                <td>$<?= number_format($detalle['subtotal'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($detalles)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="bi bi-exclamation-circle me-2"></i>No hay productos en esta compra.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>