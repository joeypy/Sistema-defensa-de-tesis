<?php

include __DIR__ . '/../../includes/auth.php';
include __DIR__ . '/../../includes/conexion.php';
verificarAutenticacion();

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validaciones backend
    $nombre = substr($_POST['nombre'], 0, 50);
    $descripcion = substr($_POST['descripcion'], 0, 50);
    $precio = preg_replace('/[^0-9.,]/', '', $_POST['precio']);
    $stock_minimo = isset($_POST['stock_minimo']) ? (int)$_POST['stock_minimo'] : 0;

    try {
        $stmt = $pdo->prepare("
            INSERT INTO productos (nombre, descripcion, precio, stock, stock_minimo)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $nombre,
            $descripcion,
            $precio,
            0,
            $stock_minimo
        ]);
        $mensaje = 'Producto creado exitosamente!';
    } catch (PDOException $e) {
        $error = 'Error al crear el producto: ' . $e->getMessage();
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary mb-0">
            <i class="bi bi-box-seam me-2"></i>Nuevo Producto
        </h2>
        <a href="gestion_productos.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <?php if ($mensaje): ?>
                <div class="alert alert-success"><?= $mensaje ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" autocomplete="off">
                <div class="row mb-3">
                    <div class="col-md-6 mb-3">
                        <label for="nombre" class="form-label">Nombre:</label>
                        <input type="text" id="nombre" name="nombre" class="form-control" required maxlength="50">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="descripcion" class="form-label">Descripción:</label>
                        <input id="descripcion" name="descripcion" class="form-control" maxlength="50">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6 mb-3">
                        <label for="precio" class="form-label">Precio:</label>
                        <input type="text" id="precio" name="precio" class="form-control" required maxlength="8" pattern="^\d{1,5}(,\d{1,2})?$" inputmode="decimal">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="stock_minimo" class="form-label">Stock Mínimo:</label>
                        <input type="number" id="stock_minimo" name="stock_minimo" class="form-control" min="0" value="0" required>
                    </div>
                </div>
                <div class="text-end">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-save me-1"></i> Guardar Producto
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Nombre
    document.getElementById('nombre').addEventListener('input', function(e) {
        if (e.target.value.length > 50) {
            e.target.value = e.target.value.slice(0, 50);
        }
    });
    // Descripción
    document.getElementById('descripcion').addEventListener('input', function(e) {
        if (e.target.value.length > 50) {
            e.target.value = e.target.value.slice(0, 50);
        }
    });
    // Precio
    document.getElementById('precio').addEventListener('input', function(e) {
        let val = e.target.value.replace(/[^0-9,]/g, '');
        if (val.length > 8) val = val.slice(0, 8);
        // Solo una coma permitida
        let parts = val.split(',');
        if (parts.length > 2) val = parts[0] + ',' + parts[1];
        // Máximo 5 enteros y 2 decimales
        if (parts[0].length > 5) parts[0] = parts[0].slice(0, 5);
        if (parts[1]) parts[1] = parts[1].slice(0, 2);
        e.target.value = parts.join(',');
    });
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>