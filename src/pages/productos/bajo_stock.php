<?php
require_once __DIR__ . '/../../includes/config.php';
include __DIR__ . '/../../includes/auth.php';
include __DIR__ . '/../../includes/conexion.php';
verificarAutenticacion();

// Consulta productos con bajo stock
$productosBajoStock = $pdo->query("
    SELECT p.id, p.nombre, p.color, p.stock, p.stock_minimo, pr.id AS proveedor_id, pr.nombre AS proveedor
    FROM productos p
    LEFT JOIN proveedores pr ON p.proveedor_id = pr.id
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
            <form id="formReposicion" method="GET" action="<?= PAGES_URL ?>/compras/registrar_compra.php">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th></th>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Color</th>
                                <th>Stock Actual</th>
                                <th>Stock Mínimo</th>
                                <th>Proveedor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productosBajoStock as $producto): ?>
                            <tr class="table-warning">
                                <td>
                                    <input type="checkbox" class="producto-checkbox" name="productos[]" value="<?= $producto['id'] ?>" data-proveedor="<?= $producto['proveedor_id'] ?>">
                                </td>
                                <td><?= $producto['id'] ?></td>
                                <td><?= htmlspecialchars($producto['nombre']) ?></td>
                                <td><?= htmlspecialchars($producto['color']) ?></td>
                                <td><?= $producto['stock'] ?></td>
                                <td><?= $producto['stock_minimo'] ?></td>
                                <td><?= $producto['proveedor'] ?: 'N/A' ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
            </form>
            <?php else: ?>
            <div class="alert alert-success m-4 text-center">
                <i class="bi bi-check-circle-fill me-2"></i>
                ¡Excelente! No hay productos con bajo stock.
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.producto-checkbox');
    const btnReposicion = document.getElementById('btn-reposicion');
    let proveedorSeleccionado = null;

    function validarSeleccion() {
        const seleccionados = Array.from(checkboxes).filter(cb => cb.checked);
        if (seleccionados.length === 0) {
            btnReposicion.disabled = true;
            proveedorSeleccionado = null;
            return;
        }
        const proveedores = seleccionados.map(cb => cb.getAttribute('data-proveedor'));
        const todosIguales = proveedores.every(p => p === proveedores[0]);
        btnReposicion.disabled = !todosIguales;
        proveedorSeleccionado = todosIguales ? proveedores[0] : null;
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', validarSeleccion);
    });

    // Selección inicial
    validarSeleccion();

    // Al enviar el formulario, agrega el proveedor_id como parámetro GET
    document.getElementById('formReposicion').addEventListener('submit', function(e) {
        if (!proveedorSeleccionado) {
            e.preventDefault();
            alert('Solo puedes seleccionar productos del mismo proveedor para la compra por reposición.');
            return;
        }
        // Agrega proveedor_id al action
        this.action = '<?= PAGES_URL ?>/compras/registrar_compra.php?proveedor_id=' + proveedorSeleccionado + '&' + new URLSearchParams(new FormData(this)).toString();
    });
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>