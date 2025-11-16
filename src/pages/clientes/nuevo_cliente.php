<?php

include __DIR__ . '/../../includes/auth.php';
include __DIR__ . '/../../includes/conexion.php';
verificarAutenticacion();

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO clientes (nombre, identificacion, direccion)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([
            $_POST['nombre'],
            $_POST['identificacion'],
            $_POST['direccion']
        ]);
        $mensaje = 'Cliente creado exitosamente!';
    } catch (PDOException $e) {
        $error = 'Error al crear el cliente: ' . $e->getMessage();
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary mb-0">
            <i class="bi bi-person-plus me-2"></i>Nuevo Cliente
        </h2>
        <a href="gestion_clientes.php" class="btn btn-outline-secondary">
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
                        <input type="text" id="nombre" name="nombre" class="form-control" required maxlength="255">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="identificacion" class="form-label">Identificación:</label>
                        <input type="text" id="identificacion" name="identificacion" class="form-control" required maxlength="20">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="direccion" class="form-label">Dirección:</label>
                    <textarea id="direccion" name="direccion" class="form-control" rows="2" maxlength="255"></textarea>
                </div>
                <div class="text-end">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-save me-1"></i> Guardar Cliente
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>