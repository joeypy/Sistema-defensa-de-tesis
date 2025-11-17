<?php
require_once __DIR__ . '/../../includes/config.php';

include __DIR__ . '/../../includes/auth.php';
include __DIR__ . '/../../includes/conexion.php';
verificarAutenticacion();

// Consulta con orden dinámico y filtros
$allowedSort = ['id', 'nombre', 'precio', 'stock', 'stock_minimo'];
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

if (!empty($_GET['precio'])) {
    $filtros[] = "p.precio LIKE :precio";
    $params[':precio'] = '%' . $_GET['precio'] . '%';
}

if (!empty($_GET['stock'])) {
    $filtros[] = "p.stock LIKE :stock";
    $params[':stock'] = '%' . $_GET['stock'] . '%';
}

if (!empty($_GET['stock_minimo'])) {
    $filtros[] = "p.stock_minimo LIKE :stock_minimo";
    $params[':stock_minimo'] = '%' . $_GET['stock_minimo'] . '%';
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
    $whereClause
";
$totalRecords = $pdo->prepare($sqlCount);
$totalRecords->execute($params);
$total = $totalRecords->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($total / $limit);

// Consulta con orden dinámico, filtros y paginación
$sql = "
    SELECT p.*
    FROM productos p
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
                                <th><?= sortLink('Descripción', 'descripcion', $sort, $order) ?></th>
                                <th><?= sortLink('Precio', 'precio', $sort, $order) ?></th>
                                <th><?= sortLink('Stock', 'stock', $sort, $order) ?></th>
                                <th><?= sortLink('Stock Mínimo', 'stock_minimo', $sort, $order) ?></th>
                                <th class="text-center">Acciones</th>
                            </tr>
                            <!-- Filtros inteligentes -->
                            <tr>
                                <th><input type="text" class="form-control form-control-sm" placeholder="Buscar Código" name="id" value="<?= htmlspecialchars($_GET['id'] ?? '') ?>"></th>
                                <th><input type="text" class="form-control form-control-sm" placeholder="Buscar Nombre" name="nombre" value="<?= htmlspecialchars($_GET['nombre'] ?? '') ?>"></th>
                                <th></th>
                                <th><input type="text" class="form-control form-control-sm" placeholder="Buscar Precio" name="precio" value="<?= htmlspecialchars($_GET['precio'] ?? '') ?>"></th>
                                <th><input type="text" class="form-control form-control-sm" placeholder="Buscar Stock" name="stock" value="<?= htmlspecialchars($_GET['stock'] ?? '') ?>"></th>
                                <th><input type="text" class="form-control form-control-sm" placeholder="Buscar Stock Mínimo" name="stock_minimo" value="<?= htmlspecialchars($_GET['stock_minimo'] ?? '') ?>"></th>
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
                            <td><?= htmlspecialchars($producto['descripcion'] ?? '') ?></td>
                            <td>$<?= number_format($producto['precio'], 2, ',', '.') ?></td>
                            <td><?= $producto['stock'] ?></td>
                            <td><?= $producto['stock_minimo'] ?? 0 ?></td>
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
                    <label for="edit-precio" class="form-label">Precio</label>
                    <input type="number" step="0.01" class="form-control" name="precio" id="edit-precio" required min="0" max="99999.99" maxlength="8">
                </div>
                <div class="mb-3">
                    <label for="edit-descripcion" class="form-label">Descripción</label>
                    <input type="text" class="form-control" name="descripcion" id="edit-descripcion" maxlength="50">
                </div>
                <div class="mb-3">
                    <label for="edit-stock" class="form-label">Stock <small class="text-muted" id="stock-actual-info"></small></label>
                    <input type="number" class="form-control" name="stock" id="edit-stock" required min="0" max="999">
                    <small class="text-muted" id="stock-validation-message"></small>
                </div>
                <div class="mb-3">
                    <label for="edit-stock-minimo" class="form-label">Stock Mínimo</label>
                    <input type="number" class="form-control" name="stock_minimo" id="edit-stock-minimo" required min="0" max="999">
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
            document.getElementById('edit-descripcion').value = producto.descripcion || '';
            document.getElementById('edit-precio').value = producto.precio;
            document.getElementById('edit-stock').value = producto.stock;
            document.getElementById('edit-stock-minimo').value = producto.stock_minimo || 0;
            
            // Mostrar stock actual
            const stockActualInfo = document.getElementById('stock-actual-info');
            if (stockActualInfo) {
                stockActualInfo.textContent = `(Stock actual: ${producto.stock})`;
            }

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
    document.getElementById('edit-precio').addEventListener('input', function(e) {
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
        let value = e.target.value;
        // Remover caracteres no numéricos excepto el signo negativo inicial
        value = value.replace(/[^0-9-]/g, '');
        // No permitir múltiples signos negativos
        if (value.indexOf('-') > 0) {
            value = value.replace(/-/g, '');
        }
        // Si empieza con negativo, mantenerlo pero validar
        if (value.startsWith('-')) {
            // No permitir valores negativos
            value = value.replace(/-/g, '');
        }
        if (value.length > 3) {
            value = value.slice(0, 3);
        }
        const numValue = parseInt(value) || 0;
        if (numValue < 0) {
            value = '0';
        }
        if (numValue > 999) {
            value = '999';
        }
        e.target.value = value;
        
        // Validar stock en tiempo real
        const validationMessage = document.getElementById('stock-validation-message');
        const productoId = document.querySelector('input[name="id"]')?.value;
        if (productoId && validationMessage) {
            const tr = document.getElementById('producto-row-' + productoId);
            if (tr) {
                const productoData = JSON.parse(tr.getAttribute('data-producto') || '{}');
                const stockActual = parseInt(productoData.stock) || 0;
                const nuevoStock = parseInt(value) || 0;
                
                if (nuevoStock < 0) {
                    validationMessage.textContent = 'El stock no puede ser negativo.';
                    validationMessage.className = 'text-danger';
                    e.target.classList.add('is-invalid');
                } else if (nuevoStock < stockActual) {
                    validationMessage.textContent = `Advertencia: Se está reduciendo el stock de ${stockActual} a ${nuevoStock}.`;
                    validationMessage.className = 'text-warning';
                    e.target.classList.remove('is-invalid');
                } else {
                    validationMessage.textContent = '';
                    e.target.classList.remove('is-invalid');
                }
            }
        }
    });
    // Guardar cambios del producto
    document.getElementById('formEditarProducto').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = e.target;
        const datos = new FormData(form);
        
        // Validar stock antes de enviar
        const stockInput = document.getElementById('edit-stock');
        const stockValue = parseInt(stockInput.value) || 0;
        
        if (stockValue < 0) {
            alert('El stock no puede ser negativo. El valor mínimo permitido es 0.');
            stockInput.focus();
            return;
        }
        
        // Obtener el stock actual del producto desde el atributo data
        const productoId = datos.get('id');
        const tr = document.getElementById('producto-row-' + productoId);
        if (tr) {
            const productoData = JSON.parse(tr.getAttribute('data-producto') || '{}');
            const stockActual = parseInt(productoData.stock) || 0;
            
            // Si se está reduciendo el stock, validar que no quede negativo
            if (stockValue < stockActual && stockValue < 0) {
                alert(`No se puede reducir el stock. Stock actual: ${stockActual}. El stock no puede ser negativo.`);
                stockInput.focus();
                return;
            }
        }

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
                tr.children[2].textContent = res.producto.descripcion || '';
                tr.children[3].textContent = '$' + parseFloat(res.producto.precio).toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                tr.children[4].textContent = res.producto.stock;
                tr.children[5].textContent = res.producto.stock_minimo || 0;
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
                    // Mostrar mensaje de éxito
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-success alert-dismissible fade show';
                    alertDiv.innerHTML = '<strong>Éxito:</strong> ' + (res.message || 'Producto eliminado exitosamente.') + 
                        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                    document.querySelector('.container').insertBefore(alertDiv, document.querySelector('.container').firstChild);
                    setTimeout(() => alertDiv.remove(), 5000);
                } else {
                    // Mostrar error con HTML usando modal de Bootstrap
                    const errorModal = document.getElementById('modalErrorEliminar');
                    if (!errorModal) {
                        // Crear modal si no existe
                        const modalHTML = `
                            <div class="modal fade" id="modalErrorEliminar" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title">
                                                <i class="bi bi-exclamation-triangle me-2"></i>Error al Eliminar Producto
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body" id="modalErrorEliminarBody">
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        document.body.insertAdjacentHTML('beforeend', modalHTML);
                    }
                    document.getElementById('modalErrorEliminarBody').innerHTML = res.error;
                    const modal = new bootstrap.Modal(document.getElementById('modalErrorEliminar'));
                    modal.show();
                }
            })
            .catch(() => {
                alert('Error de conexión. Por favor, intente nuevamente.');
            });
        });
    });
});
</script>

<?php
include __DIR__ . '/../../includes/footer.php';
?>