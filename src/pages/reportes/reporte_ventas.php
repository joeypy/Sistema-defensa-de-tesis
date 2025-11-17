<?php
include __DIR__ . '/../../includes/auth.php';
include __DIR__ . '/../../includes/conexion.php';
verificarAutenticacion();

// Obtener parámetros de filtro
$reporteSeleccionado = $_GET['reporte'] ?? 'resumen_ventas';
$fechaInicio = $_GET['fecha_inicio'] ?? '';
$fechaFin = $_GET['fecha_fin'] ?? '';
$clienteId = $_GET['cliente_id'] ?? '';

// Array para resultados
$reportData = [];

// Obtener listas para filtros
$clientes = $pdo->query("SELECT id, nombre FROM clientes ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);

$params = [];
$conditions = [];

// Solo agregar estos filtros si el reporte lo permite (no para stock_inventario)
$puedeFiltrarPorProducto = in_array($reporteSeleccionado, [
    'resumen_ventas',
    'ventas_cliente',
    'stock_inventario'
]);

// Los filtros de fecha y cliente no aplican para stock_inventario
if ($reporteSeleccionado != 'stock_inventario') {
    if (!empty($fechaInicio)) {
        $conditions[] = "v.fecha >= :fecha_inicio";
        $params[':fecha_inicio'] = $fechaInicio . ' 00:00:00';
    }
    if (!empty($fechaFin)) {
        $conditions[] = "v.fecha <= :fecha_fin";
        $params[':fecha_fin'] = $fechaFin . ' 23:59:59';
    }
    if (!empty($clienteId)) {
        $conditions[] = "v.cliente_id = :cliente_id";
        $params[':cliente_id'] = $clienteId;
    }
}

// Convertir condiciones a cadena WHERE
$whereClause = empty($conditions) ? '' : 'WHERE ' . implode(' AND ', $conditions);

// Lógica para reportes
switch ($reporteSeleccionado) {
    case 'resumen_ventas':
        // Productos más vendidos
        $query = "SELECT p.id, p.nombre, SUM(dv.cantidad) as total_vendido, 
                 SUM(dv.cantidad * dv.precio_unitario) as total_ingresado
                 FROM detalles_venta dv
                 JOIN productos p ON dv.producto_id = p.id
                 JOIN ventas v ON dv.venta_id = v.id
                 $whereClause
                 GROUP BY p.id
                 ORDER BY total_vendido DESC
                 LIMIT 10";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $reportData['productosMasVendidos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        break;

    case 'ventas_cliente':
        // Ventas por cliente
        $query = "SELECT cl.id, cl.nombre as cliente, 
                 COUNT(DISTINCT v.id) as total_ventas, 
                 SUM(v.total_dolares) as monto_total,
                 SUM(dv.cantidad) as total_unidades
                 FROM ventas v
                 JOIN clientes cl ON v.cliente_id = cl.id
                 JOIN detalles_venta dv ON dv.venta_id = v.id
                 JOIN productos p ON dv.producto_id = p.id  
                 $whereClause
                 GROUP BY cl.id, cl.nombre
                 ORDER BY monto_total DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $reportData['ventasPorCliente'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        break;

    case 'stock_inventario':
        // Productos con stock bajo
        $stockParams = [];
        $stockConditions = ["p.stock <= p.stock_minimo"];
        
        $stockWhere = 'WHERE ' . implode(' AND ', $stockConditions);
        
        $query = "SELECT p.id, p.nombre, p.stock, p.stock_minimo
                 FROM productos p
                 $stockWhere
                 ORDER BY p.stock ASC";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($stockParams);
        $reportData['productosStockBajo'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        break;


}

include __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid py-4">
    <h1 class="report-title">Reportes de Ventas</h1>

    <!-- Filtros Avanzados -->
    <div class="filter-section mb-5">
    <h3 class="filter-title"><i class="fas fa-sliders-h me-2"></i>Filtros de Reporte</h3>
    <form method="GET" action="reporte_ventas.php">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="reporte_selector" class="form-label fw-bold">Tipo de Reporte</label>
                <select class="form-select border-primary" id="reporte_selector" name="reporte" onchange="this.form.submit()">
                    <option value="resumen_ventas" <?= $reporteSeleccionado == 'resumen_ventas' ? 'selected' : '' ?>>Resumen de Ventas</option>
                    <option value="ventas_cliente" <?= $reporteSeleccionado == 'ventas_cliente' ? 'selected' : '' ?>>Ventas por Cliente</option>
                    <option value="stock_inventario" <?= $reporteSeleccionado == 'stock_inventario' ? 'selected' : '' ?>>Stock de Inventario</option>
                </select>
            </div>
            <?php if ($reporteSeleccionado != 'stock_inventario'): ?>
            <div class="col-md-2">
                <label for="fecha_inicio" class="form-label fw-bold">Fecha Inicio</label>
                    <input type="date" class="form-control border-primary" id="fecha_inicio" name="fecha_inicio"
                    value="<?= htmlspecialchars($fechaInicio) ?>" max="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-md-2">
                <label for="fecha_fin" class="form-label fw-bold">Fecha Fin</label>
                <input type="date" class="form-control border-primary" id="fecha_fin" name="fecha_fin"
                 value="<?= htmlspecialchars($fechaFin) ?>" max="<?= date('Y-m-d') ?>">
            </div>
            <?php endif; ?>
            <?php if ($reporteSeleccionado != 'stock_inventario'): ?>
            <div class="col-md-2">
                <label for="cliente_id" class="form-label fw-bold">Cliente</label>
                <select class="form-select border-primary" id="cliente_id" name="cliente_id">
                    <option value="">Todos</option>
                    <?php foreach ($clientes as $cli): ?>
                        <option value="<?= $cli['id'] ?>" <?= $clienteId == $cli['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cli['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="col-md-<?= $reporteSeleccionado == 'stock_inventario' ? '12' : '12' ?> text-end mt-2">
                <center><button type="submit" class="btn btn-primary btn-action">
                    <i class="fas fa-play me-2"></i> Generar
                </button></center>
            </div>
        </div>
    </form>
</div>

    <!-- Contenido del Reporte -->
    <div id="report-content">
        <?php if (isset($_GET['reporte'])): ?>
            <?php if ($reporteSeleccionado == 'resumen_ventas'): ?>
                <!-- Resumen de Ventas -->
                <div class="row">
                    <div class="col-lg-12 mb-4">
                        <div class="card">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 text-primary"><i class="fas fa-chart-pie mr-2"></i>Productos Más Vendidos</h5>
                                <span class="badge badge-primary badge-indicator">Top 10</span>
                            </div>
                            <div class="card-body">
                                <?php if (empty($reportData['productosMasVendidos'])): ?>
                                    <div class="no-data">
                                        <i class="fas fa-info-circle"></i>
                                        <h5>No hay datos para mostrar</h5>
                                        <p class="text-muted">Intenta con otros filtros o fechas</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr class="bg-light">
                                                    <th>Producto</th>
                                                    <th class="text-right">Cantidad Vendida</th>
                                                    <th class="text-right">Total Ingresado</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($reportData['productosMasVendidos'] as $producto): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($producto['nombre']) ?></td>
                                                        <td class="text-right"><?= number_format($producto['total_vendido'], 0) ?></td>
                                                        <td class="text-right text-success font-weight-bold">$<?= number_format($producto['total_ingresado'], 2) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

            <?php elseif ($reporteSeleccionado == 'ventas_cliente'): ?>
                <!-- Ventas por Cliente -->
                <div class="row">
                    <div class="col-lg-12 mb-4">
                        <div class="card">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 text-primary"><i class="fas fa-users mr-2"></i>Ventas por Cliente</h5>
                                <span class="badge badge-primary badge-indicator">Resumen</span>
                            </div>
                            <div class="card-body">
                                <?php if (empty($reportData['ventasPorCliente'])): ?>
                                    <div class="no-data">
                                        <i class="fas fa-info-circle"></i>
                                        <h5>No hay datos para mostrar</h5>
                                        <p class="text-muted">Intenta con otros filtros o fechas</p>
                                    </div>
                                <?php else: ?>
                                    <div class="chart-container">
                                        <canvas id="chartCliente"></canvas>
                                    </div>
                                    <div class="table-responsive mt-4">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr class="bg-light">
                                                    <th>Cliente</th>
                                                    <th class="text-right">Total Ventas</th>
                                                    <th class="text-right">Unidades</th>
                                                    <th class="text-right">Monto Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($reportData['ventasPorCliente'] as $cliente): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($cliente['cliente'] ?? 'Sin cliente') ?></td>
                                                        <td class="text-right"><?= $cliente['total_ventas'] ?></td>
                                                        <td class="text-right"><?= number_format($cliente['total_unidades'], 0) ?></td>
                                                        <td class="text-right text-success font-weight-bold">$<?= number_format($cliente['monto_total'], 2) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

            <?php elseif ($reporteSeleccionado == 'stock_inventario'): ?>
                <!-- Stock e Inventario -->
                <div class="row">
                    <div class="col-lg-12 mb-4">
                        <div class="card">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 text-primary"><i class="fas fa-boxes mr-2"></i>Productos con Stock Bajo</h5>
                                <span class="badge badge-warning badge-indicator">Alerta</span>
                            </div>
                            <div class="card-body">
                                <?php if (empty($reportData['productosStockBajo'])): ?>
                                    <div class="no-data">
                                        <i class="fas fa-check-circle text-success"></i>
                                        <h5>¡No hay productos con stock bajo!</h5>
                                        <p class="text-muted">Todos los productos cumplen con el stock mínimo</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr class="bg-light">
                                                    <th>Producto</th>
                                                    <th class="text-right">Stock Actual</th>
                                                    <th class="text-right">Stock Mínimo</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($reportData['productosStockBajo'] as $producto): ?>
                                                    <tr class="<?= $producto['stock'] <= $producto['stock_minimo'] ? 'bg-danger-light' : 'bg-warning-light' ?>">
                                                        <td><?= htmlspecialchars($producto['nombre']) ?></td>
                                                        <td class="text-right font-weight-bold"><?= $producto['stock'] ?></td>
                                                        <td class="text-right"><?= $producto['stock_minimo'] ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

            <?php endif; ?>
        <?php else: ?>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="no-data py-5">
                                <i class="fas fa-chart-bar fa-4x text-primary mb-4"></i>
                                <center><h3 class="mb-3">Generar Reporte</h3>
                                <p class="text-muted mb-4">Selecciona un tipo de reporte y los filtros deseados para comenzar</p></center>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Botones de Acción -->
<div class="fixed-bottom d-flex justify-content-end">
    <button class="btn btn-success btn-action" onclick="exportToPDF()">
        <i class="fas fa-file-pdf mr-2"></i> Exportar PDF
    </button>
    <button class="btn btn-primary btn-action" onclick="printReportContent()">
    <i class="fas fa-print mr-2"></i> Imprimir
</button>

<script>
function printReportContent() {
    var content = document.getElementById('report-content').innerHTML;
    var printWindow = window.open('', '', 'height=800,width=1000');
    printWindow.document.write('<html><head><title>Reporte de Ventas</title>');
    // Puedes agregar tus estilos aquí si quieres que se vea igual que en pantalla:
    printWindow.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">');
    printWindow.document.write('<style>body{font-family:Nunito,Arial,sans-serif;background:#fff;} .table{font-size:0.9rem;} .card{border:none;box-shadow:none;} .no-data{padding:2rem;text-align:center;color:#858796;} .badge{font-size:0.8rem;}</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write(content);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.focus();
    setTimeout(function() {
        printWindow.print();
        printWindow.close();
    }, 500);
}
</script>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>
// Configuración de gráficos
document.addEventListener('DOMContentLoaded', function() {
    // Función para generar colores aleatorios
    function getRandomColor() {
        const letters = '0123456789ABCDEF';
        let color = '#';
        for (let i = 0; i < 6; i++) {
            color += letters[Math.floor(Math.random() * 16)];
        }
        return color;
    }

    <?php if ($reporteSeleccionado == 'ventas_cliente' && !empty($reportData['ventasPorCliente'])): ?>
        // Gráfico de ventas por cliente
        const ctxCliente = document.getElementById('chartCliente').getContext('2d');
        new Chart(ctxCliente, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($reportData['ventasPorCliente'], 'cliente')) ?>,
                datasets: [{
                    label: 'Monto Total ($)',
                    data: <?= json_encode(array_column($reportData['ventasPorCliente'], 'monto_total')) ?>,
                    backgroundColor: '#4e73df',
                    borderWidth: 1,
                    borderRadius: 4,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
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
                            display: false,
                            drawBorder: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '$' + context.raw.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    <?php endif; ?>
});

// Función para exportar a PDF
function exportToPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('p', 'pt', 'a4');
    const title = "Reporte de Ventas - <?= date('d/m/Y') ?>";
    const filters = [];
    
    <?php if (!empty($fechaInicio) || !empty($fechaFin)): ?>
        filters.push(`Fechas: <?= $fechaInicio ? $fechaInicio : 'Inicio' ?> a <?= $fechaFin ? $fechaFin : 'Fin' ?>`);
    <?php endif; ?>
    <?php if (!empty($clienteId)): ?>
        filters.push(`Cliente: <?= $clientes[array_search($clienteId, array_column($clientes, 'id'))]['nombre'] ?? '' ?>`);
    <?php endif; ?>
    <?php if (!empty($colorFiltro)): ?>
        filters.push(`Color: <?= $colorFiltro ?>`);
    <?php endif; ?>
    
    // Agregar título y filtros
    doc.setFontSize(18);
    doc.setTextColor(40);
    doc.setFont('helvetica', 'bold');
    doc.text(title, doc.internal.pageSize.getWidth() / 2, 40, { align: 'center' });
    
    if (filters.length > 0) {
        doc.setFontSize(12);
        doc.setTextColor(100);
        doc.setFont('helvetica', 'normal');
        doc.text(`Filtros aplicados: ${filters.join(', ')}`, 40, 70);
    }
    
    // Capturar contenido del reporte
    html2canvas(document.getElementById('report-content'), {
        scale: 2,
        logging: false,
        useCORS: true
    }).then(canvas => {
        const imgData = canvas.toDataURL('image/png', 1.0);
        const imgWidth = doc.internal.pageSize.getWidth() - 40;
        const pageHeight = doc.internal.pageSize.getHeight();
        const imgHeight = (canvas.height * imgWidth) / canvas.width;
        let heightLeft = imgHeight;
        let position = 90;
        
        doc.addImage(imgData, 'PNG', 20, position, imgWidth, imgHeight);
        heightLeft -= pageHeight;
        
        while (heightLeft >= 0) {
            position = heightLeft - imgHeight;
            doc.addPage();
            doc.addImage(imgData, 'PNG', 20, position, imgWidth, imgHeight);
            heightLeft -= pageHeight;
        }
        
        doc.save(`Reporte_Ventas_<?= date('YmdHis') ?>.pdf`);
    });
}

// Configuración para impresión
window.onbeforeprint = function() {
    document.querySelector('.fixed-bottom').style.display = 'none';
};

window.onafterprint = function() {
    document.querySelector('.fixed-bottom').style.display = 'flex';
};
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>