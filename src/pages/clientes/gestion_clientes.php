<?php

include __DIR__ . '/../../includes/auth.php';
include __DIR__ . '/../../includes/conexion.php';
verificarAutenticacion();

// Obtener todos los clientes
$clientes = $pdo->query("SELECT id, nombre, identificacion, direccion, creado_en FROM clientes ORDER BY creado_en DESC")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../../includes/header.php';
?>

<div class="container-xl px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary mb-0">
            <i class="bi bi-person me-2"></i>Gestión de Clientes
        </h2>
        <a href="nuevo_cliente.php" class="btn btn-success">
            <i class="bi bi-plus-circle me-1"></i> Nuevo Cliente
        </a>
    </div>

    <div class="card shadow-sm" style="max-width: 100%; margin: auto;">
        <div class="card-body p-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tabla-clientes">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Identificación</th>
                            <th>Dirección</th>
                            <th>Creado En</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clientes as $cliente): ?>
                        <tr data-cliente='<?= json_encode($cliente) ?>' id="cliente-row-<?= $cliente['id'] ?>">
                            <td><?= $cliente['id'] ?></td>
                            <td><?= htmlspecialchars($cliente['nombre']) ?></td>
                            <td><?= htmlspecialchars($cliente['identificacion']) ?></td>
                            <td><?= htmlspecialchars($cliente['direccion']) ?></td>
                            <td><?= htmlspecialchars($cliente['creado_en']) ?></td>
                            <td class="text-center">
                                <div class="btn-group acciones-btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-primary btn-editar" data-id="<?= $cliente['id'] ?>" title="Editar">
                                        <i class="bi bi-pencil-square"></i> Editar
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger btn-eliminar" data-id="<?= $cliente['id'] ?>" title="Eliminar">
                                        <i class="bi bi-trash"></i> Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($clientes)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-exclamation-circle me-2"></i>No hay clientes registrados.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Cliente -->
<div class="modal fade" id="modalEditarCliente" tabindex="-1" aria-labelledby="modalEditarClienteLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formEditarCliente" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarClienteLabel">Editar Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="edit-id">
                <div class="mb-3">
                    <label for="edit-nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control" name="nombre" id="edit-nombre" required>
                </div>
                <div class="mb-3">
                    <label for="edit-identificacion" class="form-label">Identificación</label>
                    <input type="text" class="form-control" name="identificacion" id="edit-identificacion" required>
                </div>
                <div class="mb-3">
                    <label for="edit-direccion" class="form-label">Dirección</label>
                    <input type="text" class="form-control" name="direccion" id="edit-direccion">
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
    // Editar cliente: abrir modal y cargar datos
    document.querySelectorAll('.btn-editar').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const tr = btn.closest('tr');
            const cliente = JSON.parse(tr.getAttribute('data-cliente'));
            document.getElementById('edit-id').value = cliente.id;
            document.getElementById('edit-nombre').value = cliente.nombre;
            document.getElementById('edit-identificacion').value = cliente.identificacion;
            document.getElementById('edit-direccion').value = cliente.direccion;

            var modal = new bootstrap.Modal(document.getElementById('modalEditarCliente'));
            modal.show();
        });
    });

    // Guardar cambios del cliente
    document.getElementById('formEditarCliente').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = e.target;
        const datos = new FormData(form);

        fetch('gestion_clientes_ajax.php', {
            method: 'POST',
            body: datos
        })
        .then(response => response.json())
        .then(res => {
            if (res.success) {
                const tr = document.getElementById('cliente-row-' + res.cliente.id);
                tr.setAttribute('data-cliente', JSON.stringify(res.cliente));
                tr.children[1].textContent = res.cliente.nombre;
                tr.children[2].textContent = res.cliente.identificacion;
                tr.children[3].textContent = res.cliente.direccion;
                var modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarCliente'));
                modal.hide();
            } else {
                alert('Error al guardar: ' + res.error);
            }
        })
        .catch(() => alert('Error de conexión.'));
    });

    // Eliminar cliente
    document.querySelectorAll('.btn-eliminar').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (!confirm('¿Seguro que deseas eliminar este cliente?')) return;
            const id = btn.getAttribute('data-id');
            fetch('gestion_clientes_ajax.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'eliminar=1&id=' + encodeURIComponent(id)
            })
            .then(response => response.json())
            .then(res => {
                if (res.success) {
                    document.getElementById('cliente-row-' + id).remove();
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