<?php
require_once __DIR__ . '/../../includes/config.php';

include __DIR__ . '/../../includes/auth.php';
include __DIR__ . '/../../includes/conexion.php';
verificarAutenticacion();

// Definir columnas permitidas para ordenar
$allowedSort = ['id', 'fecha', 'marca', 'numero_factura', 'total'];
$sort = isset($_GET['sort']) && in_array($_GET['sort'], $allowedSort) ? $_GET['sort'] : 'fecha';
$order = isset($_GET['order']) && $_GET['order'] === 'asc' ? 'ASC' : 'DESC';

// Filtros de búsqueda
$filtros = [];
$params = [];

if (!empty($_GET['fecha'])) {
    $filtros[] = "DATE_FORMAT(c.fecha, '%d/%m/%Y') LIKE :fecha";
    $params[':fecha'] = '%' . $_GET['fecha'] . '%';
}

if (!empty($_GET['marca'])) {
    $filtros[] = "m.nombre LIKE :marca";
    $params[':marca'] = '%' . $_GET['marca'] . '%';
}

if (!empty($_GET['factura'])) {
    $filtros[] = "f.numero_factura LIKE :factura";
    $params[':factura'] = '%' . $_GET['factura'] . '%';
}

if (!empty($_GET['total'])) {
    $filtros[] = "c.total LIKE :total";
    $params[':total'] = '%' . $_GET['total'] . '%';
}

$whereClause = !empty($filtros) ? 'WHERE ' . implode(' AND ', $filtros) : '';

// Paginación
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) && in_array($_GET['limit'], [10, 30, 50, 100]) ? (int)$_GET['limit'] : 10;
$offset = ($page - 1) * $limit;

// Consulta para contar total de registros
$sqlCount = "
    SELECT COUNT(DISTINCT c.id) as total
    FROM compras c
    LEFT JOIN detalles_compra dc ON dc.compra_id = c.id
    LEFT JOIN marcas m ON dc.marca_id = m.id
    LEFT JOIN facturas_compras f ON f.compra_id = c.id
    $whereClause
";
$totalRecords = $pdo->prepare($sqlCount);
$totalRecords->execute($params);
$total = $totalRecords->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($total / $limit);

// Consulta con orden dinámico, filtros y paginación
$sql = "
    SELECT c.id, c.fecha, COALESCE(GROUP_CONCAT(DISTINCT m.nombre SEPARATOR ', '), 'Sin marca') AS marca, c.total, COALESCE(MAX(f.numero_factura), 'Sin factura') AS numero_factura
    FROM compras c
    LEFT JOIN detalles_compra dc ON dc.compra_id = c.id
    LEFT JOIN marcas m ON dc.marca_id = m.id
    LEFT JOIN facturas_compras f ON f.compra_id = c.id
    $whereClause
    GROUP BY c.id, c.fecha, c.total
    ORDER BY $sort $order
    LIMIT $limit OFFSET $offset
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$compras = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../../includes/header.php';
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary mb-0">
            <i class="bi bi-clock-history me-2"></i>Historial de Compras
        </h2>
    </div>

    <!-- Formulario de filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="" id="filtro-form">
                <div class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label for="fecha" class="form-label">Fecha</label>
                        <input type="text" class="form-control" id="fecha" name="fecha" 
                               value="<?= htmlspecialchars($_GET['fecha'] ?? '') ?>" 
                               placeholder="dd/mm/yyyy">
                    </div>
                    <div class="col-md-2">
                        <label for="marca" class="form-label">Marca</label>
                        <input type="text" class="form-control" id="marca" name="marca" 
                               value="<?= htmlspecialchars($_GET['marca'] ?? '') ?>" 
                               placeholder="Buscar marca">
                    </div>
                    <div class="col-md-2">
                        <label for="factura" class="form-label">Factura</label>
                        <input type="text" class="form-control" id="factura" name="factura" 
                               value="<?= htmlspecialchars($_GET['factura'] ?? '') ?>" 
                               placeholder="Número de factura">
                    </div>
                    <div class="col-md-2">
                        <label for="total" class="form-label">Total</label>
                        <input type="text" class="form-control" id="total" name="total" 
                               value="<?= htmlspecialchars($_GET['total'] ?? '') ?>" 
                               placeholder="Monto total">
                    </div>
                    <div class="col-md-2">
                        <label for="limit" class="form-label">Mostrar</label>
                        <select class="form-select" id="limit" name="limit" onchange="this.form.submit()">
                            <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10</option>
                            <option value="30" <?= $limit == 30 ? 'selected' : '' ?>>30</option>
                            <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50</option>
                            <option value="100" <?= $limit == 100 ? 'selected' : '' ?>>100</option>
                        </select>
                    </div>
                    <input type="hidden" name="sort" value="<?= $sort ?>">
                    <input type="hidden" name="order" value="<?= strtolower($order) ?>">
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-search me-1"></i>Buscar
                        </button>
                        <a href="<?= PAGES_URL ?>/compras/historial_compras.php" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i>Limpiar Filtros
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tabla-compras">
                    <thead class="table-light">
                        <tr>
                            <?php
                            function sortLink($label, $column, $sort, $order) {
                                $nextOrder = ($sort === $column && $order === 'ASC') ? 'desc' : 'asc';
                                $icon = '';
                                if ($sort === $column) {
                                    $icon = $order === 'ASC' ? '▲' : '▼';
                                }
                                // Mantener los filtros actuales en la URL
                                $currentFilters = $_GET;
                                unset($currentFilters['sort'], $currentFilters['order']);
                                $queryString = http_build_query(array_merge($currentFilters, [
                                    'sort' => $column,
                                    'order' => $nextOrder
                                ]));
                                $url = "?" . $queryString;
                                return "<a href=\"$url\" class=\"text-decoration-none text-dark\">$label $icon</a>";
                            }
                            ?>
                            <th><?= sortLink('Fecha', 'fecha', $sort, $order) ?></th>
                                                        <th><?= sortLink('Marca', 'marca', $sort, $order) ?></th>
                            <th><?= sortLink('Factura', 'numero_factura', $sort, $order) ?></th>
                            <th><?= sortLink('Total', 'total', $sort, $order) ?></th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($compras as $compra): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($compra['fecha'])) ?></td>
                            <td><?= htmlspecialchars($compra['marca']) ?></td>
                            <td><?= htmlspecialchars($compra['numero_factura']) ?></td>
                            <td>$<?= number_format($compra['total'], 2, ',', '.') ?></td>
                            <td class="text-center">
                                <a href="<?= PAGES_URL ?>/compras/detalle_compra.php?id=<?= $compra['id'] ?>" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Ver Detalle">
                                    <i class="bi bi-eye"></i> Ver Detalle
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($compras)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="bi bi-exclamation-circle me-2"></i>No hay compras registradas.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Información de paginación -->
    <p class="text-muted text-center mt-3">
        Mostrando <?= ($offset + 1) ?> a <?= min($offset + $limit, $total) ?> de <?= $total ?> registros
    </p>

    <!-- Controles de paginación -->
    <?php if ($totalPages > 1): ?>
    <nav aria-label="Navegación de páginas">
        <ul class="pagination justify-content-center">
            <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">Anterior</a>
                </li>
            <?php endif; ?>
            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
                <li class="page-item">
                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Siguiente</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let searchTimeout;
    
    // Función para aplicar filtros con delay
    function aplicarFiltros() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            document.getElementById('filtro-form').submit();
        }, 800); // Esperar 800ms después de que el usuario deje de escribir
    }
    
    // Agregar event listeners a los campos de filtro
    document.getElementById('fecha').addEventListener('input', aplicarFiltros);
    document.getElementById('marca').addEventListener('input', aplicarFiltros);
    document.getElementById('factura').addEventListener('input', aplicarFiltros);
    document.getElementById('total').addEventListener('input', aplicarFiltros);
    
    // También aplicar filtros al presionar Enter
    document.getElementById('filtro-form').addEventListener('submit', function(e) {
        // El formulario se enviará normalmente
    });
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>