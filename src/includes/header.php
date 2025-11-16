<?php
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/config.php';
}
$productosBajoStockHeader = $pdo->query("
    SELECT COUNT(*) as total 
    FROM productos
    WHERE stock <= stock_minimo
")->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión de Compras</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/main.css">
</head>
<body class="d-flex flex-column min-vh-100">
    <!-- Barra superior con usuario -->
    <div class="user-bar bg-light">
    <div class="container d-flex justify-content-between align-items-center py-2">
        <div class="user-info text-muted d-flex align-items-center">
            <i class="bi bi-person me-1"></i>
            <?php if (isset($_SESSION['usuario_nombre'])): ?>
                <span class="fw-medium"><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></span>
            <?php else: ?>
                Usuario invitado
            <?php endif; ?>
        </div>
        <div class="flex-grow-1 d-flex justify-content-center">
            <img src="<?= ASSETS_URL ?>/images/logo1.png" alt="X Sales" style="height:50px;">
        </div>
        <a href="<?= PAGES_URL ?>/auth/logout.php" class="btn-logout text-decoration-none">
            <i class="bi bi-box-arrow-right me-1"></i>Cerrar sesión
        </a>
    </div>
</div>
    
    <header class="border-bottom">
        <nav class="navbar navbar-expand-lg navbar-light bg-white">
            <div class="container">
                <a class="navbar-brand fw-bold text-primary" href="<?= PAGES_URL ?>/index.php">
                    <i class="bi bi-box-seam me-2"></i>Sistema Compras
                </a>
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link" href="<?= PAGES_URL ?>/index.php">
                                <i class="bi bi-house-door me-1"></i>Inicio
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= PAGES_URL ?>/compras/registrar_compra.php">
                                <i class="bi bi-cart-plus me-1"></i>Nueva Compra
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= PAGES_URL ?>/compras/historial_compras.php">
                                <i class="bi bi-clock-history me-1"></i>Historial
                            </a>
                        </li>
                        <li class="nav-item position-relative">
                            <a class="nav-link" href="<?= PAGES_URL ?>/productos/gestion_productos.php">
                                <i class="bi bi-box-seam me-1"></i>Productos
                                <?php if ($productosBajoStockHeader['total'] > 0): ?>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        <?= $productosBajoStockHeader['total'] ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= PAGES_URL ?>/clientes/gestion_clientes.php">
                                <i class="bi bi-person me-1"></i>Clientes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= PAGES_URL ?>/reportes/reporte_ventas.php">
                                <i class="bi bi-graph-up me-1"></i>Reportes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= PAGES_URL ?>/tasa/gestion_tasa.php">
                                <i class="bi bi-cash me-1"></i>Tasa Diaria
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    
    <main class="container mt-4 flex-grow-1">
       