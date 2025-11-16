<?php

include __DIR__ . '/../../includes/auth.php';
include __DIR__ . '/../../includes/conexion.php';
verificarAutenticacion();

// Obtener marcas
$marcas = $pdo->query("SELECT id, nombre FROM marcas ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validaciones backend
    $nombre = substr($_POST['nombre'], 0, 50);
    $descripcion = substr($_POST['descripcion'], 0, 50);
    $color = isset($_POST['color']) ? substr($_POST['color'], 0, 50) : '';
    $precio_compra = preg_replace('/[^0-9.,]/', '', $_POST['precio_compra']);
    $precio_venta = preg_replace('/[^0-9.,]/', '', $_POST['precio_venta']);
    $stock_minimo = isset($_POST['stock_minimo']) ? (int)$_POST['stock_minimo'] : 0;

    try {
        $stmt = $pdo->prepare("
            INSERT INTO productos (nombre, descripcion, color, precio_compra, precio_venta, stock, stock_minimo, marca_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $nombre,
            $descripcion,
            $color,
            $precio_compra,
            $precio_venta,
            0,
            $stock_minimo,
            $_POST['marca_id'] ?: null
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
                        <label for="color" class="form-label">Color:</label>
                        <input type="text" id="color" name="color" class="form-control" required maxlength="20">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="stock_minimo" class="form-label">Stock Mínimo:</label>
                        <input type="number" id="stock_minimo" name="stock_minimo" class="form-control" required min="0" max="9">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6 mb-3">
                        <label for="precio_compra" class="form-label">Precio Compra:</label>
                        <input type="text" id="precio_compra" name="precio_compra" class="form-control" required maxlength="8" pattern="^\d{1,5}(,\d{1,2})?$" inputmode="decimal">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="precio_venta" class="form-label">Precio Venta:</label>
                        <input type="text" id="precio_venta" name="precio_venta" class="form-control" required maxlength="8" pattern="^\d{1,5}(,\d{1,2})?$" inputmode="decimal">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="marca_id" class="form-label">Marca:</label>
                    <select id="marca_id" name="marca_id" class="form-select" required>
                        <option value="">Seleccione una marca</option>
                        <?php foreach ($marcas as $marca): ?>
                            <option value="<?= $marca['id'] ?>"><?= $marca['nombre'] ?></option>
                        <?php endforeach; ?>
                    </select>
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
    // Inicializar Select2 para Marca
    $('#marca_id').select2({
        placeholder: 'Buscar marca...',
        allowClear: true,
        width: '100%'
    });

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
    // Color
    document.getElementById('color').addEventListener('input', function(e) {
        if (e.target.value.length > 50) {
            e.target.value = e.target.value.slice(0, 50);
        }
    });
    // Stock Mínimo
    document.getElementById('stock_minimo').addEventListener('input', function(e) {
        if (e.target.value.length > 1) {
            e.target.value = e.target.value.slice(0, 1);
        }
        if (parseInt(e.target.value) > 9) {
            e.target.value = 9;
        }
    });
    // Precio compra
    document.getElementById('precio_compra').addEventListener('input', function(e) {
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
    // Precio venta
    document.getElementById('precio_venta').addEventListener('input', function(e) {
        let val = e.target.value.replace(/[^0-9,]/g, '');
        if (val.length > 8) val = val.slice(0, 8);
        let parts = val.split(',');
        if (parts.length > 2) val = parts[0] + ',' + parts[1];
        if (parts[0].length > 5) parts[0] = parts[0].slice(0, 5);
        if (parts[1]) parts[1] = parts[1].slice(0, 2);
        e.target.value = parts.join(',');
    });
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>