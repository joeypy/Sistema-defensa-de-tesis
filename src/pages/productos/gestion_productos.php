<?php
require_once __DIR__ . '/../../includes/config.php';

include __DIR__ . '/../../includes/auth.php';
include __DIR__ . '/../../includes/conexion.php';
verificarAutenticacion();

// Obtener proveedores para el modal y filtro
$proveedores = $pdo->query("SELECT id, nombre FROM proveedores ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);

// Consulta con orden dinámico y filtros
$allowedSort = ['id', 'nombre', 'precio_compra', 'stock', 'proveedor_nombre', 'stock_minimo'];
$sort = isset($_GET['sort']) && in_array($_GET['sort'], $allowedSort) ? $_GET['sort'] : 'id';
$order = isset($_GET['order']) && $_GET['order'] === 'asc' ? 'ASC' : 'DESC';

// Filtros de búsqueda
$filtros = [];
$params = [];

if (!empty($_GET['id'])) {
    $filtros[] = "p.id LIKE :id";
    $params[':id'] = '%' . $_GET['id'] . '%';
}

if (!empty($_GET['nombre'])) {
    $filtros[] = "p.nombre LIKE :nombre";
    $params[':nombre'] = '%' . $_GET['nombre'] . '%';
}

if (!empty($_GET['precio_compra'])) {
    $filtros[] = "p.precio_compra LIKE :precio_compra";
    $params[':precio_compra'] = '%' . $_GET['precio_compra'] . '%';
}

if (!empty($_GET['stock'])) {
    $filtros[] = "p.stock LIKE :stock";
    $params[':stock'] = '%' . $_GET['stock'] . '%';
}

if (!empty($_GET['stock_minimo'])) {
    $filtros[] = "p.stock_minimo LIKE :stock_minimo";
    $params[':stock_minimo'] = '%' . $_GET['stock_minimo'] . '%';
}

if (!empty($_GET['proveedor'])) {
    $filtros[] = "pr.nombre LIKE :proveedor";
    $params[':proveedor'] = '%' . $_GET['proveedor'] . '%';
}

$whereClause = !empty($filtros) ? 'WHERE ' . implode(' AND ', $filtros) : '';

// Paginación
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) && in_array($_GET['limit'], [10, 30, 50, 100]) ? (int)$_GET['limit'] : 10;
$offset = ($page - 1) * $limit;

// Consulta para contar total de registros
$sqlCount = "
    SELECT COUNT(*) as total
    FROM productos p
    LEFT JOIN proveedores pr ON p.proveedor_id = pr.id
    $whereClause
";
$totalRecords = $pdo->prepare($sqlCount);
$totalRecords->execute($params);
$total = $totalRecords->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($total / $limit);

// Consulta con orden dinámico, filtros y paginación
$sql = "
    SELECT p.*, pr.nombre AS proveedor_nombre
    FROM productos p
    LEFT JOIN proveedores pr ON p.proveedor_id = pr.id
    $whereClause
    ORDER BY $sort $order
    LIMIT $limit OFFSET $offset
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../../includes/header.php';
?>

<div class="container-xl px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary mb-0">
            <i class="bi bi-box-seam me-2"></i>Gestión de Productos
        </h2>
        <a href="nuevo_producto.php" class="btn btn-success">
            <i class="bi bi-plus-circle me-1"></i> Nuevo Producto
        </a>
    </div>

    <div class="card shadow-sm" style="max-width: 100%; margin: auto;">
        <div class="card-body p-3">
            <form method="GET" action="" id="filtro-form">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="tabla-productos" style="min-width:1200px;">
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
                                <th><?= sortLink('CÓDIGO', 'id', $sort, $order) ?></th>
                                <th><?= sortLink('Nombre', 'nombre', $sort, $order) ?></th>
                                <th><?= sortLink('Precio Compra', 'precio_compra', $sort, $order) ?></th>
                                <th><?= sortLink('Stock', 'stock', $sort, $order) ?></th>
                                <th><?= sortLink('Stock Mínimo', 'stock_minimo', $sort, $order) ?></th>
                                <th><?= sortLink('Marca', 'proveedor_nombre', $sort, $order) ?></th>
                                <th class="text-center">Acciones</th>
                            </tr>
                            <!-- Filtros inteligentes -->
                            <tr>
                                <th><input type="text" class="form-control form-control-sm" placeholder="Buscar Código" name="id" value="<?= htmlspecialchars($_GET['id'] ?? '') ?>"></th>
                                <th><input type="text" class="form-control form-control-sm" placeholder="Buscar Nombre" name="nombre" value="<?= htmlspecialchars($_GET['nombre'] ?? '') ?>"></th>
                                <th><input type="text" class="form-control form-control-sm" placeholder="Buscar Precio" name="precio_compra" value="<?= htmlspecialchars($_GET['precio_compra'] ?? '') ?>"></th>
                                <th><input type="text" class="form-control form-control-sm" placeholder="Buscar Stock" name="stock" value="<?= htmlspecialchars($_GET['stock'] ?? '') ?>"></th>
                                <th><input type="text" class="form-control form-control-sm" placeholder="Buscar Stock Mínimo" name="stock_minimo" value="<?= htmlspecialchars($_GET['stock_minimo'] ?? '') ?>"></th>
                                <th><input type="text" class="form-control form-control-sm" placeholder="Buscar Marca" name="proveedor" value="<?= htmlspecialchars($_GET['proveedor'] ?? '') ?>"></th>
                                <th class="text-center">
                                    <button type="submit" class="btn btn-sm btn-primary">Buscar</button>
                                    <a href="gestion_productos.php" class="btn btn-sm btn-outline-secondary ms-1">Limpiar</a>
                                </th>
                            </tr>
                        </thead>
                    <tbody>
                        <?php foreach ($productos as $producto): ?>
                        <tr data-producto='<?= json_encode($producto) ?>' id="producto-row-<?= $producto['id'] ?>">
                            <td><?= $producto['id'] ?></td>
                            <td><?= htmlspecialchars($producto['nombre']) ?></td>
                            <td>$<?= number_format($producto['precio_compra'], 2, ',', '.') ?></td>
                            <td><?= $producto['stock'] ?></td>
                            <td><?= $producto['stock_minimo'] ?></td>
                            <td><?= $producto['proveedor_nombre'] ?: 'N/A' ?></td>
                            <td class="text-center">
                                <div class="btn-group acciones-btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-primary btn-editar" data-id="<?= $producto['id'] ?>" title="Editar">
                                        <i class="bi bi-pencil-square"></i> Editar
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger btn-eliminar" data-id="<?= $producto['id'] ?>" title="Eliminar">
                                        <i class="bi bi-trash"></i> Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($productos)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-exclamation-circle me-2"></i>No hay productos registrados.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </form>
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

<!-- Modal Editar Producto -->
<div class="modal fade" id="modalEditarProducto" tabindex="-1" aria-labelledby="modalEditarProductoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form id="formEditarProducto" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarProductoLabel">Editar Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="edit-id">
                <div class="mb-3">
                    <label for="edit-nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control" name="nombre" id="edit-nombre" required maxlength="50">
                </div>
                <div class="mb-3">
                    <label for="edit-precio-compra" class="form-label">Precio Compra</label>
                    <input type="number" step="0.01" class="form-control" name="precio_compra" id="edit-precio-compra" required min="0" max="99999.99" maxlength="8">
                </div>
                <div class="mb-3">
                    <label for="edit-stock" class="form-label">Stock</label>
                    <input type="number" class="form-control" name="stock" id="edit-stock" required min="0" max="999">
                </div>
                <div class="mb-3">
                    <label for="edit-stock-minimo" class="form-label">Stock Mínimo</label>
                    <input type="number" class="form-control" name="stock_minimo" id="edit-stock-minimo" required min="0" max="9">
                </div>
                <div class="mb-3">
                    <label for="edit-proveedor" class="form-label">Marca</label>
                    <select class="form-select" name="proveedor_id" id="edit-proveedor" required>
                        <?php foreach ($proveedores as $prov): ?>
                            <option value="<?= $prov['id'] ?>"><?= htmlspecialchars($prov['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<!-- Bootstrap JS (asegúrate de tenerlo) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Editar producto: abrir modal y cargar datos
    document.querySelectorAll('.btn-editar').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const tr = btn.closest('tr');
            const producto = JSON.parse(tr.getAttribute('data-producto'));
            document.getElementById('edit-id').value = producto.id;
            document.getElementById('edit-nombre').value = producto.nombre;
            document.getElementById('edit-precio-compra').value = producto.precio_compra;
            document.getElementById('edit-stock').value = producto.stock;
            document.getElementById('edit-stock-minimo').value = producto.stock_minimo;
            document.getElementById('edit-proveedor').value = producto.proveedor_id;

            var modal = new bootstrap.Modal(document.getElementById('modalEditarProducto'));
            modal.show();
        });
    });

    // Validaciones JS para campos del modal
    document.getElementById('edit-nombre').addEventListener('input', function(e) {
        if (e.target.value.length > 50) {
            e.target.value = e.target.value.slice(0, 50);
        }
    });
    document.getElementById('edit-precio-compra').addEventListener('input', function(e) {
        // Limita a 8 caracteres
        if (e.target.value.length > 8) {
            e.target.value = e.target.value.slice(0, 8);
        }
        // Limita el valor máximo a 99999.99
        if (parseFloat(e.target.value) > 99999.99) {
            e.target.value = 99999.99;
        }
        // Limita a dos decimales
        if (e.target.value.includes('.')) {
            let [ent, dec] = e.target.value.split('.');
            if (dec && dec.length > 2) {
                e.target.value = ent + '.' + dec.slice(0, 2);
            }
        }
    });
    document.getElementById('edit-stock').addEventListener('input', function(e) {
        if (e.target.value.length > 3) {
            e.target.value = e.target.value.slice(0, 3);
        }
        if (parseInt(e.target.value) > 999) {
            e.target.value = 999;
        }
    });
    document.getElementById('edit-stock-minimo').addEventListener('input', function(e) {
        if (e.target.value.length > 1) {
            e.target.value = e.target.value.slice(0, 1);
        }
        if (parseInt(e.target.value) > 9) {
            e.target.value = 9;
        }
    });
    // Guardar cambios del producto
    document.getElementById('formEditarProducto').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = e.target;
        const datos = new FormData(form);

        fetch('gestion_productos_ajax.php', {
            method: 'POST',
            body: datos
        })
        .then(response => response.json())
        .then(res => {
            if (res.success) {
                const tr = document.getElementById('producto-row-' + res.producto.id);
                tr.setAttribute('data-producto', JSON.stringify(res.producto));
                tr.children[1].textContent = res.producto.nombre;
                tr.children[2].textContent = '$' + parseFloat(res.producto.precio_compra).toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                tr.children[3].textContent = res.producto.stock;
                tr.children[4].textContent = res.producto.stock_minimo;
                tr.children[5].textContent = res.producto.proveedor_nombre || 'N/A';
                var modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarProducto'));
                modal.hide();
            } else {
                alert('Error al guardar: ' + res.error);
            }
        })
        .catch(() => alert('Error de conexión.'));
    });

    // Eliminar producto
    document.querySelectorAll('.btn-eliminar').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (!confirm('¿Seguro que deseas eliminar este producto?')) return;
            const id = btn.getAttribute('data-id');
            fetch('gestion_productos_ajax.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'eliminar=1&id=' + encodeURIComponent(id)
            })
            .then(response => response.json())
            .then(res => {
                if (res.success) {
                    document.getElementById('producto-row-' + id).remove();
                } else {
                    alert('Error al eliminar: ' + res.error);
                }
            })
            .catch(() => alert('Error de conexión.'));
        });
    });
});
</script>

<?php
include __DIR__ . '/../../includes/footer.php';
?>