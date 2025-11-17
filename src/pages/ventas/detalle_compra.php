<?php
require_once __DIR__ . '/../../includes/config.php';
include __DIR__ . '/../../includes/auth.php';
include __DIR__ . '/../../includes/conexion.php';
verificarAutenticacion();

if (!isset($_GET['id'])) {
    header("Location: " . PAGES_URL . "/ventas/historial_ventas.php");
    exit();
}

$venta_id = $_GET['id'];

// Obtener información de la venta
$stmt = $pdo->prepare("
    SELECT v.id, v.fecha, v.cliente_id, v.total_dolares, v.total_bs,
           v.numero_factura, v.numero_control, v.numero_referencia,
           cl.nombre AS cliente_nombre,
           mp.nombre AS metodo_pago_nombre
    FROM ventas v
    JOIN clientes cl ON v.cliente_id = cl.id
    JOIN metodo_pago mp ON v.metodo_pago_id = mp.id
    WHERE v.id = ?
");
$stmt->execute([$venta_id]);
$venta = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtener información del cliente si existe
$cliente = null;

if ($venta && !empty($venta['cliente_id'])) {
    $stmt_cliente = $pdo->prepare("SELECT id, nombre, identificacion, direccion, telefono, email, creado_en FROM clientes WHERE id = ?");
    $stmt_cliente->execute([$venta['cliente_id']]);
    $cliente = $stmt_cliente->fetch(PDO::FETCH_ASSOC);
}

if (!$venta) {
    header("Location: " . PAGES_URL . "/ventas/historial_ventas.php");
    exit();
}

// Obtener detalles de la venta
$detalles = $pdo->prepare("
    SELECT pr.nombre AS producto, dv.cantidad, dv.precio_unitario, dv.subtotal, dv.descuento
    FROM detalles_venta dv
    JOIN productos pr ON dv.producto_id = pr.id
    WHERE dv.venta_id = ?
");
$detalles->execute([$venta_id]);
$detalles = $detalles->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../../includes/header.php';
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary mb-0">
            <i class="bi bi-receipt me-2"></i>Detalle de Venta <?= $venta['numero_factura'] ? 'N° ' . htmlspecialchars($venta['numero_factura']) : '# ' . $venta['id'] ?>
        </h2>
        <a href="historial_ventas.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver al Historial
        </a>
    </div>

    <!-- Información de la Operación -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="bi bi-receipt-cutoff me-2"></i>Información de la Operación
            </h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3"><strong>N° Factura:</strong> <?= $venta['numero_factura'] ? htmlspecialchars($venta['numero_factura']) : 'Sin factura' ?></div>
                <?php if ($venta['numero_control']): ?>
                <div class="col-md-3"><strong>N° Control:</strong> <?= htmlspecialchars($venta['numero_control']) ?></div>
                <?php endif; ?>
                <div class="col-md-3"><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($venta['fecha'])) ?></div>
                <div class="col-md-3"><strong>Método de Pago:</strong> <?= htmlspecialchars($venta['metodo_pago_nombre']) ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-md-3"><strong>Cliente:</strong> <?= $venta['cliente_nombre'] ? htmlspecialchars($venta['cliente_nombre']) : 'Sin cliente' ?></div>
                <div class="col-md-3"><strong>Total USD:</strong> $<?= number_format($venta['total_dolares'], 2) ?></div>
                <div class="col-md-3"><strong>Total BS:</strong> <?= number_format($venta['total_bs'], 2) ?> BS</div>
                <?php if ($venta['numero_referencia']): ?>
                <div class="col-md-3"><strong>N° Referencia:</strong> <?= htmlspecialchars($venta['numero_referencia']) ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Información del Cliente -->
    <?php if ($cliente): ?>
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">
                <i class="bi bi-person me-2"></i>Información del Cliente
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <strong>Nombre:</strong> <?= htmlspecialchars($cliente['nombre']) ?>
                </div>
                <div class="col-md-6 mb-3">
                    <strong>Identificación:</strong> <?= htmlspecialchars($cliente['identificacion']) ?>
                </div>
                <?php if (!empty($cliente['direccion'])): ?>
                <div class="col-md-12 mb-3">
                    <strong>Dirección:</strong> <?= htmlspecialchars($cliente['direccion']) ?>
                </div>
                <?php endif; ?>
                <?php if (!empty($cliente['telefono'])): ?>
                <div class="col-md-6 mb-3">
                    <strong>Teléfono:</strong>
                    <div class="ms-3">
                        <i class="bi bi-telephone me-2"></i><?= htmlspecialchars($cliente['telefono']) ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php if (!empty($cliente['email'])): ?>
                <div class="col-md-6 mb-3">
                    <strong>Email:</strong>
                    <div class="ms-3">
                        <i class="bi bi-envelope me-2"></i><?= htmlspecialchars($cliente['email']) ?>
                    </div>
                </div>
                <?php endif; ?>
                <div class="col-md-12">
                    <small class="text-muted">
                        <i class="bi bi-calendar me-1"></i>Cliente registrado el: <?= date('d/m/Y', strtotime($cliente['creado_en'])) ?>
                    </small>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Productos Comprados -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">
                <i class="bi bi-box-seam me-2"></i>Productos Comprados
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Precio Unitario</th>
                                <th>Descuento</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detalles as $detalle): ?>
                                <tr>
                                    <td><?= htmlspecialchars($detalle['producto']) ?></td>
                                    <td><?= $detalle['cantidad'] ?></td>
                                    <td>$<?= number_format($detalle['precio_unitario'], 2) ?></td>
                                    <td class="text-center">
                                        <?php if ($detalle['descuento'] == 1): ?>
                                            <span class="badge bg-success">10%</span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>$<?= number_format($detalle['subtotal'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php if (empty($detalles)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="bi bi-exclamation-circle me-2"></i>No hay productos en esta compra.
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="4" class="text-end fw-bold">Total USD:</td>
                                <td class="fw-bold">$<?= number_format($venta['total_dolares'], 2) ?></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end fw-bold">Total BS:</td>
                                <td class="fw-bold"><?= number_format($venta['total_bs'], 2) ?> BS</td>
                            </tr>
                        </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>