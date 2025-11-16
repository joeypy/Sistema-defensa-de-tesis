<?php

include __DIR__ . '/../../includes/auth.php';
include __DIR__ . '/../../includes/conexion.php';
verificarAutenticacion();

// Obtener todos los proveedores
$proveedores = $pdo->query("SELECT * FROM proveedores")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../../includes/header.php';
?>

<div class="container-xl px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary mb-0">
            <i class="bi bi-truck me-2"></i>Gestión de Proveedores
        </h2>
        <a href="nuevo_proveedor.php" class="btn btn-success">
            <i class="bi bi-plus-circle me-1"></i> Nuevo Proveedor
        </a>
    </div>

    <div class="card shadow-sm" style="max-width: 100%; margin: auto;">
        <div class="card-body p-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tabla-proveedores">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Contacto</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($proveedores as $proveedor): ?>
                        <tr data-proveedor='<?= json_encode($proveedor) ?>' id="proveedor-row-<?= $proveedor['id'] ?>">
                            <td><?= $proveedor['id'] ?></td>
                            <td><?= htmlspecialchars($proveedor['nombre']) ?></td>
                            <td><?= htmlspecialchars($proveedor['contacto']) ?></td>
                            <td><?= htmlspecialchars($proveedor['telefono']) ?></td>
                            <td><?= htmlspecialchars($proveedor['email']) ?></td>
                            <td class="text-center">
                                <div class="btn-group acciones-btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-primary btn-editar" data-id="<?= $proveedor['id'] ?>" title="Editar">
                                        <i class="bi bi-pencil-square"></i> Editar
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger btn-eliminar" data-id="<?= $proveedor['id'] ?>" title="Eliminar">
                                        <i class="bi bi-trash"></i> Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($proveedores)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-exclamation-circle me-2"></i>No hay proveedores registrados.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Proveedor -->
<div class="modal fade" id="modalEditarProveedor" tabindex="-1" aria-labelledby="modalEditarProveedorLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formEditarProveedor" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarProveedorLabel">Editar Proveedor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="edit-id">
                <div class="mb-3">
                    <label for="edit-nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control" name="nombre" id="edit-nombre" required>
                </div>
                <div class="mb-3">
                    <label for="edit-contacto" class="form-label">Contacto</label>
                    <input type="text" class="form-control" name="contacto" id="edit-contacto">
                </div>
                <div class="mb-3">
                    <label for="edit-telefono" class="form-label">Teléfono</label>
                    <input type="text" class="form-control" name="telefono" id="edit-telefono">
                </div>
                <div class="mb-3">
                    <label for="edit-email" class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" id="edit-email">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Editar proveedor: abrir modal y cargar datos
    document.querySelectorAll('.btn-editar').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const tr = btn.closest('tr');
            const proveedor = JSON.parse(tr.getAttribute('data-proveedor'));
            document.getElementById('edit-id').value = proveedor.id;
            document.getElementById('edit-nombre').value = proveedor.nombre;
            document.getElementById('edit-contacto').value = proveedor.contacto;
            document.getElementById('edit-telefono').value = proveedor.telefono;
            document.getElementById('edit-email').value = proveedor.email;

            var modal = new bootstrap.Modal(document.getElementById('modalEditarProveedor'));
            modal.show();
        });
    });

    // Guardar cambios del proveedor
    document.getElementById('formEditarProveedor').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = e.target;
        const datos = new FormData(form);

        fetch('gestion_proveedores_ajax.php', {
            method: 'POST',
            body: datos
        })
        .then(response => response.json())
        .then(res => {
            if (res.success) {
                const tr = document.getElementById('proveedor-row-' + res.proveedor.id);
                tr.setAttribute('data-proveedor', JSON.stringify(res.proveedor));
                tr.children[1].textContent = res.proveedor.nombre;
                tr.children[2].textContent = res.proveedor.contacto;
                tr.children[3].textContent = res.proveedor.telefono;
                tr.children[4].textContent = res.proveedor.email;
                var modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarProveedor'));
                modal.hide();
            } else {
                alert('Error al guardar: ' + res.error);
            }
        })
        .catch(() => alert('Error de conexión.'));
    });

    // Eliminar proveedor
    document.querySelectorAll('.btn-eliminar').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (!confirm('¿Seguro que deseas eliminar este proveedor?')) return;
            const id = btn.getAttribute('data-id');
            fetch('gestion_proveedores_ajax.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'eliminar=1&id=' + encodeURIComponent(id)
            })
            .then(response => response.json())
            .then(res => {
                if (res.success) {
                    document.getElementById('proveedor-row-' + id).remove();
                } else {
                    alert('Error al eliminar: ' + res.error);
                }
            })
            .catch(() => alert('Error de conexión.'));
        });
    });
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>