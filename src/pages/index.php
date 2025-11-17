<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/conexion.php';
verificarAutenticacion();

// Obtener estadísticas para el dashboard
$totalVentas = $pdo->query("SELECT COUNT(*) as total FROM ventas")->fetch(PDO::FETCH_ASSOC)['total'];
$totalProductos = $pdo->query("SELECT COUNT(DISTINCT nombre) as total FROM productos")->fetch(PDO::FETCH_ASSOC)['total'];
$productosBajoStock = $pdo->query("SELECT COUNT(*) as total FROM productos WHERE stock <= stock_minimo")->fetch(PDO::FETCH_ASSOC)['total'];
$totalClientes = $pdo->query("SELECT COUNT(*) as total FROM clientes")->fetch(PDO::FETCH_ASSOC)['total'];

// Obtener últimas ventas
$ultimasVentas = $pdo->query("
    SELECT v.fecha, cl.nombre as cliente, v.numero_factura, v.total_dolares 
    FROM ventas v
    JOIN clientes cl ON v.cliente_id = cl.id
    ORDER BY v.fecha DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Obtener productos más vendidos
$productosPopulares = $pdo->query("
    SELECT p.nombre, SUM(dv.cantidad) as total
    FROM detalles_venta dv
    JOIN productos p ON dv.producto_id = p.id
    GROUP BY p.id
    ORDER BY total DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Obtener productos con stock bajo
$productosBajo = $pdo->query("
    SELECT nombre, stock, stock_minimo
    FROM productos 
    WHERE stock <= stock_minimo
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Obtener datos para gráfico de ventas por mes
$ventasPorMes = $pdo->query("
    SELECT 
        DATE_FORMAT(fecha, '%Y-%m') as mes,
        SUM(total_dolares) as total
    FROM ventas
    WHERE fecha >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY mes
    ORDER BY mes
")->fetchAll(PDO::FETCH_ASSOC);

// Preparar datos para gráficos
$labelsMeses = [];
$dataMeses = [];
foreach ($ventasPorMes as $mes) {
    $labelsMeses[] = date('M Y', strtotime($mes['mes']));
    $dataMeses[] = $mes['total'];
}

$labelsProductos = [];
$dataProductos = [];
foreach ($productosPopulares as $producto) {
    $labelsProductos[] = $producto['nombre'];
    $dataProductos[] = $producto['total'];
}

include __DIR__ . '/../includes/header.php';
?>


<div class="dashboard-header">
    <div class="container text-center">
        <h1 class="display-6 fw-normal mb-3">Panel de Gestión</h1>
        <p class="lead mb-0">Bienvenido, <strong><?= $_SESSION['usuario_nombre'] ?></strong></p>
    </div>
</div>

<div class="container py-4">
    <!-- Tarjetas de estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3 mb-4">
            <a href="<?= PAGES_URL ?>/compras/historial_compras.php" class="text-decoration-none">
                <div class="stat-card card text-center h-100">
                    <div class="card-body py-4">
                        <div class="stat-card-icon"><i class="fas fa-cart-check"></i></div>
                        <div class="stat-card-value"><?= $totalVentas ?></div>
                        <p class="stat-card-title">Ventas Totales</p>
                    </div>
                </div>
            </a>
        </div>
        
        <div class="col-md-3 mb-4">
            <a href="<?= PAGES_URL ?>/productos/gestion_productos.php" class="text-decoration-none">
                <div class="stat-card card text-center h-100">
                    <div class="card-body py-4">
                        <div class="stat-card-icon"><i class="fas fa-box"></i></div>
                        <div class="stat-card-value"><?= $totalProductos ?></div>
                        <p class="stat-card-title">Productos</p>
                    </div>
                </div>
            </a>
        </div>
        
        <div class="col-md-3 mb-4">
            <a href="<?= PAGES_URL ?>/productos/bajo_stock.php" class="text-decoration-none">
                <div class="stat-card card text-center h-100">
                    <div class="card-body py-4">
                        <div class="stat-card-icon"><i class="fas fa-exclamation-triangle text-warning"></i></div>
                        <div class="stat-card-value"><?= $productosBajoStock ?></div>
                        <p class="stat-card-title">Bajo Stock</p>
                    </div>
                </div>
            </a>
        </div>
        
        <div class="col-md-3 mb-4">
            <a href="<?= PAGES_URL ?>/clientes/gestion_clientes.php" class="text-decoration-none">
                <div class="stat-card card text-center h-100">
                    <div class="card-body py-4">
                        <div class="stat-card-icon"><i class="fas fa-users"></i></div>
                        <div class="stat-card-value"><?= $totalClientes ?></div>
                        <p class="stat-card-title">Clientes</p>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row mb-4">
        <div class="col-md-6 mb-4">
        <div class="chart-container">
            <h3 class="chart-title">Ventas por Mes (Últimos 6 meses)</h3>
            <div class="chart-wrapper">
                <canvas id="ventasChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="chart-container">
            <h3 class="chart-title">Productos Más Vendidos</h3>
            <div class="chart-wrapper">
                <canvas id="productosChart"></canvas>
            </div>
        </div>
    </div>
</div>

    <!-- Tablas -->
    <div class="row mb-4">
        <div class="col-md-6 mb-4">
            <div class="dashboard-section">
                <div class="table-card-header">Últimas Ventas</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Cliente</th>
                                    <th>Nro. Factura</th>
                                    <th class="text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ultimasVentas as $venta): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($venta['fecha'])) ?></td>
                                    <td><?= htmlspecialchars($venta['cliente']) ?></td>
                                    <td><?= htmlspecialchars($venta['numero_factura']) ?></td>
                                    <td class="text-right font-weight-bold text-success">
                                        $<?= number_format($venta['total_dolares'], 2, ',', '.') ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <a href="<?= PAGES_URL ?>/compras/historial_compras.php" class="btn btn-link d-block text-center py-3 text-decoration-none">
                        Ver todo el historial <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="dashboard-section">
                <div class="table-card-header">Productos con Stock Bajo</div>
                <div class="card-body p-0">
                    <?php if (count($productosBajo) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th class="text-right">Stock Actual</th>
                                    <th class="text-right">Stock Mínimo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($productosBajo as $producto): ?>
                                <tr class="<?= $producto['stock'] <= $producto['stock_minimo'] ? 'bg-warning-light' : '' ?>">
                                    <td><?= htmlspecialchars($producto['nombre']) ?></td>
                                    <td class="text-right font-weight-bold"><?= $producto['stock'] ?></td>
                                    <td class="text-right"><?= $producto['stock_minimo'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <a href="<?= PAGES_URL ?>/productos/bajo_stock.php" class="btn btn-link d-block text-center py-3 text-decoration-none">
                        Gestionar productos <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                    <?php else: ?>
                    <div class="alert-minimal">
                        <i class="fas fa-check-circle text-success me-2"></i> 
                        ¡Excelente! No hay productos con stock bajo.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS y Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gráfico de ventas por mes
    const ventasCtx = document.getElementById('ventasChart').getContext('2d');
    new Chart(ventasCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode($labelsMeses) ?>,
            datasets: [{
                label: 'Total Ventas ($)',
                data: <?= json_encode($dataMeses) ?>,
                borderColor: '#4361ee',
                backgroundColor: 'rgba(67, 97, 238, 0.05)',
                borderWidth: 3,
                pointBackgroundColor: '#4361ee',
                pointBorderColor: '#fff',
                pointRadius: 5,
                pointHoverRadius: 7,
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false
                    },
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Gráfico de productos populares
    const productosCtx = document.getElementById('productosChart').getContext('2d');
    new Chart(productosCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($labelsProductos) ?>,
            datasets: [{
                label: 'Unidades Vendidas',
                data: <?= json_encode($dataProductos) ?>,
                backgroundColor: '#4cc9f0',
                borderColor: '#3a86ff',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false
                    },
                    ticks: {
                        precision: 0
                    }
                },
                x: {
                    grid: {
                        display: false,
                        drawBorder: false
                    }
                }
            }
        }
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>