<?php
require_once __DIR__ . '/../../includes/config.php';
include __DIR__ . '/../../includes/auth.php';
include __DIR__ . '/../../includes/conexion.php';
verificarAutenticacion();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fecha = $_POST['fecha'];
    $tasa = $_POST['tasa'];
    $descripcion = $_POST['descripcion'];

    try {
        $stmt = $pdo->prepare("INSERT INTO tasa_diaria (fecha, tasa, descripcion) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE tasa = ?, descripcion = ?");
        $stmt->execute([$fecha, $tasa, $descripcion, $tasa, $descripcion]);
        header("Location: " . PAGES_URL . "/tasa/gestion_tasa.php?success=1");
        exit();
    } catch (Exception $e) {
        $error = 'Error al guardar la tasa: ' . $e->getMessage();
    }
}

// Obtener tasa actual
$tasa_actual = $pdo->query("SELECT * FROM tasa_diaria WHERE fecha = CURDATE()")->fetch(PDO::FETCH_ASSOC);

// Obtener últimas tasas
$ultimas_tasas = $pdo->query("SELECT * FROM tasa_diaria ORDER BY fecha DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../../includes/header.php';
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary mb-0">
            <i class="bi bi-cash me-2"></i>Gestión de Tasa Diaria
        </h2>
        <a href="<?= PAGES_URL ?>/index.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Establecer Tasa Diaria</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($_GET['success'])): ?>
                        <div class="alert alert-success">Tasa diaria actualizada exitosamente.</div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label for="fecha" class="form-label">Fecha:</label>
                            <input type="date" id="fecha" name="fecha" class="form-control" value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="tasa" class="form-label">Tasa (1 USD = X VES):</label>
                            <input type="number" id="tasa" name="tasa" class="form-control" step="0.01" min="0" value="<?php echo $tasa_actual['tasa'] ?? ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción (opcional):</label>
                            <textarea id="descripcion" name="descripcion" class="form-control" rows="3"><?php echo $tasa_actual['descripcion'] ?? ''; ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>Guardar Tasa
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Tasa Actual</h5>
                </div>
                <div class="card-body">
                    <?php if ($tasa_actual): ?>
                        <p><strong>Fecha:</strong> <?php echo $tasa_actual['fecha']; ?></p>
                        <p><strong>Tasa:</strong> 1 USD = <?php echo number_format($tasa_actual['tasa'], 2); ?> VES</p>
                        <?php if ($tasa_actual['descripcion']): ?>
                            <p><strong>Descripción:</strong> <?php echo htmlspecialchars($tasa_actual['descripcion']); ?></p>
                        <?php endif; ?>
                    <?php else: ?>
                        <p>No hay tasa definida para hoy.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow-sm mt-3">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Últimas Tasas</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tasa</th>
                                    <th>Descripción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ultimas_tasas as $tasa): ?>
                                    <tr>
                                        <td><?php echo $tasa['fecha']; ?></td>
                                        <td><?php echo number_format($tasa['tasa'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($tasa['descripcion'] ?? ''); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>