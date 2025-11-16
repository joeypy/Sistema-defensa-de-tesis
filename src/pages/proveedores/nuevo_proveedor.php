<?php

include __DIR__ . '/../../includes/auth.php';
include __DIR__ . '/../../includes/conexion.php';
verificarAutenticacion();

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO proveedores (nombre, contacto, telefono, email, direccion)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_POST['nombre'],
            $_POST['contacto'],
            $_POST['telefono'],
            $_POST['email'],
            $_POST['direccion']
        ]);
        $mensaje = 'Proveedor creado exitosamente!';
    } catch (PDOException $e) {
        $error = 'Error al crear el proveedor: ' . $e->getMessage();
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary mb-0">
            <i class="bi bi-person-plus me-2"></i>Nuevo Proveedor
        </h2>
        <a href="gestion_proveedores.php" class="btn btn-outline-secondary">
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
                        <input type="text" id="nombre" name="nombre" class="form-control" required maxlength="30">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="contacto" class="form-label">Contacto:</label>
                        <input type="text" id="contacto" name="contacto" class="form-control" maxlength="30">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6 mb-3">
                        <label for="telefono" class="form-label">Teléfono:</label>
                        <input type="text" id="telefono" name="telefono" class="form-control" maxlength="20">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email:</label>
                        <input type="email" id="email" name="email" class="form-control" maxlength="70">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="direccion" class="form-label">Dirección:</label>
<textarea id="direccion" name="direccion" class="form-control" rows="2" maxlength="100"></textarea>
                </div>
                <div class="text-end">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-save me-1"></i> Guardar Proveedor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>