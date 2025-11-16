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

// Obtener tasa actual de la base de datos
$tasa_actual = $pdo->query("SELECT * FROM tasa_diaria WHERE fecha = CURDATE()")->fetch(PDO::FETCH_ASSOC);

// Obtener tasa del día desde la API oficial
$tasa_api = null;
$error_api = null;
$sin_conexion = false;

try {
    $api_url = 'https://ve.dolarapi.com/v1/dolares/oficial';
    
    // Intentar usar cURL si está disponible (mejor detección de errores)
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Sistema-Compras/1.0');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);
        
        if ($response === false || !empty($curl_error)) {
            // Error de conexión
            $sin_conexion = true;
            $error_api = 'Sin conexión a internet, tasa no disponible';
        } elseif ($http_code !== 200) {
            $error_api = 'Error al obtener tasa de la API (HTTP ' . $http_code . ')';
        } else {
            $tasa_api = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $error_api = 'Error al decodificar respuesta de la API';
                $tasa_api = null;
            }
        }
    } else {
        // Fallback a file_get_contents
        $context = stream_context_create([
            'http' => [
                'timeout' => 5,
                'method' => 'GET',
                'header' => 'User-Agent: Sistema-Compras/1.0'
            ]
        ]);
        
        $response = @file_get_contents($api_url, false, $context);
        if ($response === false) {
            // Verificar si es un error de conexión
            $last_error = error_get_last();
            $error_msg = $last_error['message'] ?? '';
            if (strpos($error_msg, 'getaddrinfo') !== false || 
                strpos($error_msg, 'Connection') !== false ||
                strpos($error_msg, 'timed out') !== false ||
                strpos($error_msg, 'Name or service not known') !== false ||
                strpos($error_msg, 'Network is unreachable') !== false) {
                $sin_conexion = true;
                $error_api = 'Sin conexión a internet, tasa no disponible';
            } else {
                $error_api = 'No se pudo conectar con la API del dólar oficial';
            }
        } else {
            $tasa_api = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $error_api = 'Error al decodificar respuesta de la API';
                $tasa_api = null;
            }
        }
    }
} catch (Exception $e) {
    // Verificar si es un error de conexión
    $error_message = $e->getMessage();
    if (strpos($error_message, 'getaddrinfo') !== false || 
        strpos($error_message, 'Connection') !== false ||
        strpos($error_message, 'timed out') !== false ||
        strpos($error_message, 'Name or service not known') !== false ||
        strpos($error_message, 'Network is unreachable') !== false) {
        $sin_conexion = true;
        $error_api = 'Sin conexión a internet, tasa no disponible';
    } else {
        $error_api = 'Error al obtener tasa de la API: ' . $error_message;
    }
}

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
                            <div class="input-group">
                                <input type="number" id="tasa" name="tasa" class="form-control" step="0.01" min="0" value="<?php echo $tasa_actual['tasa'] ?? ''; ?>" required>
                                <?php if ($tasa_api && isset($tasa_api['promedio']) && !$sin_conexion): ?>
                                    <button type="button" class="btn btn-outline-success" onclick="document.getElementById('tasa').value = '<?php echo $tasa_api['promedio']; ?>'; document.getElementById('descripcion').value = 'Tasa oficial del día - <?php echo date('d/m/Y'); ?>';">
                                        <i class="bi bi-arrow-down-circle me-1"></i>Usar API
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="btn btn-outline-secondary" disabled title="No disponible sin conexión a internet">
                                        <i class="bi bi-arrow-down-circle me-1"></i>Usar API
                                    </button>
                                <?php endif; ?>
                            </div>
                            <?php if ($tasa_api && isset($tasa_api['promedio']) && !$sin_conexion): ?>
                                <small class="text-muted">Valor de la API: <?php echo number_format($tasa_api['promedio'], 2); ?> VES</small>
                            <?php elseif ($sin_conexion): ?>
                                <small class="text-danger">
                                    <i class="bi bi-wifi-off me-1"></i>Sin conexión a internet, tasa no disponible
                                </small>
                            <?php endif; ?>
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
                    <!-- Tasa del día desde API oficial -->
                    <div class="mb-4 p-3 bg-light rounded">
                        <h6 class="text-primary mb-2">
                            <i class="bi bi-globe me-2"></i>Tasa del Día (API Oficial)
                        </h6>
                        <?php if ($tasa_api && isset($tasa_api['promedio']) && !$sin_conexion): ?>
                            <p class="mb-1"><strong>Fuente:</strong> <?php echo htmlspecialchars($tasa_api['nombre'] ?? 'Oficial'); ?></p>
                            <p class="mb-1"><strong>Tasa Promedio:</strong> 
                                <span class="fs-6">1 USD = <?php echo number_format($tasa_api['promedio'], 2); ?> VES</span>
                            </p>
                            <?php if (isset($tasa_api['fechaActualizacion'])): ?>
                                <p class="mb-0 text-muted small">
                                    <i class="bi bi-clock me-1"></i>
                                    Actualizado: <?php 
                                        $fecha = new DateTime($tasa_api['fechaActualizacion']);
                                        echo $fecha->format('d/m/Y H:i:s');
                                    ?>
                                </p>
                            <?php endif; ?>
                        <?php elseif ($sin_conexion): ?>
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-wifi-off me-2"></i>
                                <strong>Sin conexión a internet, tasa no disponible</strong>
                                <p class="mb-0 small mt-1">Puede ingresar la tasa manualmente en el formulario.</p>
                            </div>
                        <?php else: ?>
                            <p class="text-danger mb-0">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                <?php echo $error_api ?? 'No se pudo obtener la tasa del día'; ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <!-- Tasa guardada en base de datos -->
                    <div class="border-top pt-3">
                        <h6 class="text-secondary mb-2">
                            <i class="bi bi-database me-2"></i>Tasa Guardada en Sistema
                        </h6>
                        <?php if ($tasa_actual): ?>
                            <p class="mb-1"><strong>Fecha:</strong> <?php echo $tasa_actual['fecha']; ?></p>
                            <p class="mb-1"><strong>Tasa:</strong> 1 USD = <?php echo number_format($tasa_actual['tasa'], 2); ?> VES</p>
                            <?php if ($tasa_actual['descripcion']): ?>
                                <p class="mb-0"><strong>Descripción:</strong> <?php echo htmlspecialchars($tasa_actual['descripcion']); ?></p>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="text-muted mb-0">No hay tasa definida para hoy en el sistema.</p>
                        <?php endif; ?>
                    </div>
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