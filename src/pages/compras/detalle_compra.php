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
    SELECT c.id, c.fecha, c.cliente_id, GROUP_CONCAT(DISTINCT m.nombre SEPARATOR ', ') AS marca, u.nombre AS usuario, c.total,
           f.numero_factura, f.numero_control, f.fecha AS fecha_factura
    FROM compras c
    JOIN detalles_compra dc ON dc.compra_id = c.id
    JOIN marcas m ON dc.marca_id = m.id
    JOIN usuarios u ON c.usuario_id = u.id
    LEFT JOIN facturas_compras f ON f.compra_id = c.id
    WHERE c.id = ?
    GROUP BY c.id, c.fecha, c.cliente_id, u.nombre, c.total, f.numero_factura, f.numero_control, f.fecha
");
$stmt->execute([$compra_id]);
$compra = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtener información del cliente si existe
$cliente = null;
$cliente_emails = [];
$cliente_telefonos = [];

if ($compra && !empty($compra['cliente_id'])) {
    $stmt_cliente = $pdo->prepare("SELECT id, nombre, identificacion, direccion, creado_en FROM clientes WHERE id = ?");
    $stmt_cliente->execute([$compra['cliente_id']]);
    $cliente = $stmt_cliente->fetch(PDO::FETCH_ASSOC);
    
    if ($cliente) {
        // Obtener emails del cliente
        $stmt_emails = $pdo->prepare("SELECT email FROM clientes_emails WHERE cliente_id = ?");
        $stmt_emails->execute([$cliente['id']]);
        $cliente_emails = $stmt_emails->fetchAll(PDO::FETCH_COLUMN);
        
        // Obtener teléfonos del cliente
        $stmt_telefonos = $pdo->prepare("SELECT telefono FROM clientes_telefonos WHERE cliente_id = ?");
        $stmt_telefonos->execute([$cliente['id']]);
        $cliente_telefonos = $stmt_telefonos->fetchAll(PDO::FETCH_COLUMN);
    }
}

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
    SELECT metodo, numero_referencia
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
            <i class="bi bi-receipt me-2"></i>Detalle de Compra <?= $compra['numero_factura'] ? 'N° ' . htmlspecialchars($compra['numero_factura']) : '# ' . $compra['id'] ?>
        </h2>
        <a href="historial_compras.php" class="btn btn-outline-secondary">
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
                <div class="col-md-3"><strong>N° Factura:</strong> <?= $compra['numero_factura'] ? htmlspecialchars($compra['numero_factura']) : 'Sin factura' ?></div>
                <?php if ($compra['numero_control']): ?>
                <div class="col-md-3"><strong>N° Control:</strong> <?= htmlspecialchars($compra['numero_control']) ?></div>
                <?php endif; ?>
                <div class="col-md-3"><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($compra['fecha'])) ?></div>
                <?php if ($compra['fecha_factura']): ?>
                <div class="col-md-3"><strong>Fecha Factura:</strong> <?= date('d/m/Y', strtotime($compra['fecha_factura'])) ?></div>
                <?php endif; ?>
            </div>
            <div class="row mb-3">
                <div class="col-md-3"><strong>Marca/s:</strong> <?= htmlspecialchars($compra['marca']) ?></div>
                <div class="col-md-3"><strong>Usuario:</strong> <?= htmlspecialchars($compra['usuario']) ?></div>
                <div class="col-md-3"><strong>Total:</strong> $<?= number_format($compra['total'], 2) ?></div>
            </div>
            <?php if ($metodo_pago): ?>
            <div class="row mb-0">
                <div class="col-md-6"><strong>Método de Pago:</strong> <?= htmlspecialchars($metodo_pago['metodo']) ?></div>
                <div class="col-md-6">
                    <strong>Número de Referencia:</strong> 
                    <?= $metodo_pago['numero_referencia'] ? htmlspecialchars($metodo_pago['numero_referencia']) : 'N/A' ?>
                </div>
            </div>
            <?php endif; ?>
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
                <?php if (!empty($cliente_telefonos)): ?>
                <div class="col-md-6 mb-3">
                    <strong>Teléfono(s):</strong>
                    <ul class="list-unstyled mb-0 ms-3">
                        <?php foreach ($cliente_telefonos as $telefono): ?>
                            <li><i class="bi bi-telephone me-2"></i><?= htmlspecialchars($telefono) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                <?php if (!empty($cliente_emails)): ?>
                <div class="col-md-6 mb-3">
                    <strong>Email(s):</strong>
                    <ul class="list-unstyled mb-0 ms-3">
                        <?php foreach ($cliente_emails as $email): ?>
                            <li><i class="bi bi-envelope me-2"></i><?= htmlspecialchars($email) ?></li>
                        <?php endforeach; ?>
                    </ul>
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
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="3" class="text-end fw-bold">Total:</td>
                            <td class="fw-bold">$<?= number_format($compra['total'], 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>