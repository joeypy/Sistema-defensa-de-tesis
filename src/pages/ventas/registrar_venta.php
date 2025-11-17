<?php
require_once __DIR__ . '/../../includes/config.php';
include __DIR__ . '/../../includes/auth.php';
include __DIR__ . '/../../includes/conexion.php';
verificarAutenticacion();

$cliente_id = $_GET['cliente_id'] ?? $_POST['cliente_id'] ?? null;
$productos_seleccionados = $_GET['productos'] ?? $_POST['productos'] ?? [];

$error = '';

// Si vienen productos seleccionados, consultarlos
$productos = [];
if (!empty($productos_seleccionados)) {
    $in = str_repeat('?,', count($productos_seleccionados) - 1) . '?';
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id IN ($in)");
    $stmt->execute($productos_seleccionados);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        // Validar y convertir cliente_id a entero
        $cliente_id_raw = $_POST['cliente_id'] ?? '';
        $cliente_id = (trim($cliente_id_raw) !== '' && is_numeric($cliente_id_raw)) ? (int)$cliente_id_raw : null;
        
        if (empty($cliente_id) || $cliente_id <= 0) {
            throw new Exception("Debe seleccionar un cliente válido.");
        }
        
        $usuario_id = $_SESSION['usuario_id'];
        if (empty($usuario_id)) {
            throw new Exception("Error de sesión. Por favor, inicie sesión nuevamente.");
        }
        
        $productos_ids = $_POST['producto_id'] ?? [];
        $cantidades = $_POST['cantidad'] ?? [];
        $descuentos = $_POST['descuento'] ?? [];
        
        // Validar que haya al menos un producto
        if (empty($productos_ids) || count($productos_ids) === 0) {
            throw new Exception("Debe agregar al menos un producto a la venta.");
        }

        // Obtener tasa del día para calcular total_bs
        $tasa_info = $pdo->query("SELECT tasa FROM tasa_diaria ORDER BY fecha DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        $tasa_actual = $tasa_info['tasa'] ?? 1;
        
        $total_dolares = 0;
        $detalles = [];

        for ($i = 0; $i < count($productos_ids); $i++) {
            // Validar y convertir IDs a enteros
            $producto_id = !empty($productos_ids[$i]) ? (int)$productos_ids[$i] : null;
            $cantidad = !empty($cantidades[$i]) ? (int)$cantidades[$i] : 0;
            $descuento = $descuentos[$i] ?? '0';
            
            // Validar que el ID sea válido
            if (empty($producto_id) || $producto_id <= 0) {
                throw new Exception("Debe seleccionar un producto válido en la fila " . ($i + 1) . ".");
            }

            // Validar que la cantidad sea positiva
            if ($cantidad <= 0) {
                throw new Exception("La cantidad debe ser mayor a 0 para el producto seleccionado.");
            }

            // Obtener precio y stock del producto
            $stmt = $pdo->prepare("SELECT precio, stock, nombre FROM productos WHERE id = ?");
            $stmt->execute([$producto_id]);
            $producto = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$producto) {
                throw new Exception("Producto con ID $producto_id no encontrado.");
            }

            $precio_unitario = $producto['precio'];
            $stock_actual = (int)$producto['stock'];
            $nombre_producto = $producto['nombre'];

            // Validar stock disponible
            if ($stock_actual < $cantidad) {
                throw new Exception("Stock insuficiente para el producto '$nombre_producto'. Stock disponible: $stock_actual, solicitado: $cantidad");
            }

            $subtotal = $precio_unitario * $cantidad;
            if ($descuento == "1") {
                $subtotal *= 0.9; // Aplicar descuento del 10%
            }
            $total_dolares += $subtotal;

            $detalles[] = [
                'producto_id' => $producto_id,
                'cantidad' => $cantidad,
                'precio_unitario' => $precio_unitario,
                'subtotal' => $subtotal,
                'descuento' => ($descuento == "1") ? 1 : 0
            ];
        }

        // Calcular total en BS
        $total_bs = $total_dolares * $tasa_actual;

        // Obtener datos de factura y método de pago
        $numero_factura = $_POST['numero_factura'];
        $numero_control = $_POST['numero_control'];
        $metodo_pago_id = (int)($_POST['metodo_pago_id'] ?? 0);
        $numero_referencia = $_POST['numero_referencia'] ?? null;
        
        // Obtener fecha de la factura (si no viene, usar fecha actual)
        $fecha_factura = $_POST['fecha_factura'] ?? date('Y-m-d');
        // Convertir fecha a DATETIME (agregar hora actual)
        $fecha_factura_datetime = date('Y-m-d H:i:s', strtotime($fecha_factura . ' ' . date('H:i:s')));

        // Validar método de pago
        if (empty($metodo_pago_id) || $metodo_pago_id <= 0) {
            throw new Exception("Debe seleccionar un método de pago válido.");
        }

        // Verificar si el método requiere referencia
        $stmt = $pdo->prepare("SELECT nombre, requiere_referencia FROM metodo_pago WHERE id = ?");
        $stmt->execute([$metodo_pago_id]);
        $metodo_pago = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$metodo_pago) {
            throw new Exception("Método de pago no encontrado.");
        }

        // Validar número de referencia si es requerido
        if ($metodo_pago['requiere_referencia'] == 1 && empty($numero_referencia)) {
            throw new Exception("El número de referencia es obligatorio para el método de pago: " . $metodo_pago['nombre']);
        }

        // Si es Efectivo, no requiere referencia
        if ($metodo_pago['requiere_referencia'] == 0) {
            $numero_referencia = null;
        }

        // Insertar venta con todos los datos (incluyendo fecha)
        $stmt = $pdo->prepare("INSERT INTO ventas (cliente_id, fecha, total_dolares, total_bs, numero_factura, numero_control, metodo_pago_id, numero_referencia) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$cliente_id, $fecha_factura_datetime, $total_dolares, $total_bs, $numero_factura, $numero_control, $metodo_pago_id, $numero_referencia]);
        $venta_id = $pdo->lastInsertId();

        // Insertar detalles de venta (el trigger descontará el stock automáticamente)
        foreach ($detalles as $detalle) {
            $stmt = $pdo->prepare("INSERT INTO detalles_venta (venta_id, producto_id, cantidad, precio_unitario, subtotal, descuento) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$venta_id, $detalle['producto_id'], $detalle['cantidad'], $detalle['precio_unitario'], $detalle['subtotal'], $detalle['descuento']]);
        }

        $pdo->commit();
        header("Location: " . PAGES_URL . "/ventas/registrar_venta.php?success=1");
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error al registrar la venta: " . $e->getMessage();
    }
}

$productos_todos = $pdo->query("SELECT id, nombre, precio FROM productos ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
$clientes = $pdo->query("SELECT id, nombre FROM clientes")->fetchAll(PDO::FETCH_ASSOC);

// Obtener el siguiente número de control
$ultimo_control = $pdo->query("SELECT numero_control FROM ventas ORDER BY id DESC LIMIT 1")->fetchColumn();
if ($ultimo_control) {
    $parts = explode('-', $ultimo_control);
    $numero = intval($parts[1]);
    $siguiente_numero = $numero + 1;
    $numero_control = '00-' . str_pad($siguiente_numero, 7, '0', STR_PAD_LEFT);
} else {
    $numero_control = '00-0000001';
}

// Obtener el siguiente número de factura
$ultimo_factura = $pdo->query("SELECT numero_factura FROM ventas ORDER BY id DESC LIMIT 1")->fetchColumn();
if ($ultimo_factura) {
    if (strpos($ultimo_factura, '-') !== false) {
        $parts = explode('-', $ultimo_factura);
        $numero_str = array_pop($parts);
        $prefijo = implode('-', $parts) . '-';
        $numero = intval($numero_str);
        $siguiente_numero = $numero + 1;
        $numero_factura = $prefijo . $siguiente_numero;
    } else {
        $numero = intval($ultimo_factura);
        $siguiente_numero = $numero + 1;
        $ancho = strlen($ultimo_factura);
        $numero_factura = str_pad($siguiente_numero, $ancho, '0', STR_PAD_LEFT);
    }
} else {
    $numero_factura = '0000001'; // Valor inicial si no hay registros
}

// Obtener métodos de pago disponibles
$metodos_pago = $pdo->query("SELECT id, nombre, requiere_referencia FROM metodo_pago ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

// Obtener tasa del día (la más reciente)
$tasa_info = $pdo->query("SELECT tasa, fecha FROM tasa_diaria ORDER BY fecha DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$tasa_actual = $tasa_info['tasa'] ?? null;
$fecha_tasa = $tasa_info['fecha'] ?? null;

// Si no hay tasa, usar 1 como valor por defecto para evitar errores
if (!$tasa_actual || $tasa_actual <= 0) {
    $tasa_actual = 1;
}

include __DIR__ . '/../../includes/header.php';
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary mb-0">
            <i class="bi bi-cart-plus me-2"></i>Registrar Nueva Venta
        </h2>
        <a href="<?= PAGES_URL ?>/ventas/historial_ventas.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success d-flex align-items-center" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <div>Venta registrada exitosamente!</div>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <div><?= $error ?></div>
                </div>
            <?php endif; ?>

            <form id="form-venta" method="POST" autocomplete="off">
                <!-- Información de la Factura -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card bg-light-subtle border-0">
                            <div class="card-header bg-transparent border-0 py-3">
                                <h5 class="mb-0 fw-bold text-primary">
                                    <i class="bi bi-receipt me-2"></i>Información de Factura
                                </h5>
                            </div>
                            <div class="card-body pt-2">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label for="cliente" class="form-label fw-semibold">Cliente:</label>
                                        <select id="cliente" name="cliente_id" class="form-select border-0 shadow-sm select2-cliente" required>
                                            <option value="">Seleccione un cliente</option>
                                            <?php foreach ($clientes as $cliente): ?>
                                                <option value="<?= $cliente['id'] ?>" <?= ($cliente_id == $cliente['id']) ? 'selected' : '' ?>><?= $cliente['nombre'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label fw-semibold">N° Factura:</label>
                                        <div class="border rounded p-2 bg-white">
                                            <span class="fw-bold text-primary" id="numero_factura_display"><?php echo $numero_factura; ?></span>
                                        </div>
                                        <input type="hidden" name="numero_factura" value="<?php echo $numero_factura; ?>">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label fw-semibold">N° Control:</label>
                                        <div class="border rounded p-2 bg-white">
                                            <span class="fw-bold text-primary" id="numero_control_display"><?php echo $numero_control; ?></span>
                                        </div>
                                        <input type="hidden" name="numero_control" value="<?php echo $numero_control; ?>">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="fecha_factura" class="form-label fw-semibold">Fecha de la factura:</label>
                                        <input type="date" id="fecha_factura" name="fecha_factura" class="form-control border-0 shadow-sm" value="<?= date('Y-m-d') ?>" required max="<?= date('Y-m-d') ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Productos -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card border-0">
                            <div class="card-header bg-light-subtle border-0 py-3 d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 fw-bold text-primary">
                                    <i class="bi bi-box-seam me-2"></i>Productos
                                </h5>
                                <span>
                                    <i class="bi bi-currency-exchange me-1"></i>
                                    Tasa del día: <?php echo number_format($tasa_actual, 2, '.', ',') . ' VES'; ?>
                                    <?php if ($fecha_tasa): ?>
                                        <small>(<?= date('d/m/Y', strtotime($fecha_tasa)) ?>)</small>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="40%" class="ps-4">Producto</th>
                                                <th width="10%" class="text-center">Cantidad</th>
                                                <th width="13%" class="text-end">Subtotal ($)</th>
                                                <th width="13%" class="text-end">Subtotal (BS)</th>
                                                <th width="12%" class="text-center">Descuento 10%</th>
                                                <th width="12%" class="text-center pe-4">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="productos-container">
                                            <?php if (!empty($productos)): ?>
                                                <?php foreach ($productos as $producto): ?>
                                                    <tr class="producto-item">
                                                        <td class="ps-4">
                                                            <select class="form-select border-0 producto-select" name="producto_id[]" required>
                                                                <option value="<?= $producto['id'] ?>" selected><?= htmlspecialchars($producto['nombre']) ?></option>
                                                                <?php foreach ($productos_todos as $prod): ?>
                                                                    <?php if ($prod['id'] != $producto['id']): ?>
                                                                        <option value="<?= $prod['id'] ?>"><?= htmlspecialchars($prod['nombre']) ?></option>
                                                                    <?php endif; ?>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </td>
                                                        <td class="text-center">
                                                            <input type="number" name="cantidad[]" min="1" max="999" maxlength="3" value="1" class="form-control border-0 text-center cantidad" required>
                                                        </td>
                                                        <td class="text-end">
                                                            <span class="fw-semibold subtotal-display-usd">$ 0.00</span>
                                                        </td>
                                                        <td class="text-end">
                                                            <span class="fw-semibold subtotal-display-bs">BS 0.00</span>
                                                        </td>
                                                        <td class="text-center pe-4">
                                                            <input type="checkbox" class="form-check-input descuento-checkbox">
                                                            <input type="hidden" name="descuento[]" value="0">
                                                        </td>
                                                        <td class="text-center">
                                                            <button type="button" class="btn btn-sm btn-outline-danger btn-eliminar" title="Eliminar producto">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr class="producto-item">
                                                    <td class="ps-4">
                                                        <select class="form-select border-0 producto-select" name="producto_id[]" required>
                                                            <option value="">Seleccione un producto</option>
                                                            <?php foreach ($productos_todos as $prod): ?>
                                                                <option value="<?= $prod['id'] ?>"><?= htmlspecialchars($prod['nombre']) ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="number" name="cantidad[]" min="1" max="999" maxlength="3" value="1" class="form-control border-0 text-center cantidad" required>
                                                    </td>
                                                    <td class="text-end">
                                                        <span class="fw-semibold subtotal-display-usd">$ 0.00</span>
                                                    </td>
                                                    <td class="text-end">
                                                        <span class="fw-semibold subtotal-display-bs">BS 0.00</span>
                                                    </td>
                                                    <td class="text-center pe-4">
                                                        <input type="checkbox" class="form-check-input descuento-checkbox">
                                                        <input type="hidden" name="descuento[]" value="0">
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-outline-danger btn-eliminar" title="Eliminar producto">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <td colspan="2" class="text-end fw-bold ps-4">Total:</td>
                                                <td class="text-end fw-bold">
                                                    <span id="total-usd">$ 0.00</span>
                                                </td>
                                                <td class="text-end fw-bold">
                                                    <span id="total-bs">BS 0.00</span>
                                                </td>
                                                <td colspan="2"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <div class="card-footer bg-transparent border-0 pt-3">
                                    <button type="button" id="btn-agregar-producto" class="btn btn-outline-primary">
                                        <i class="bi bi-plus-circle me-1"></i> Agregar Producto
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-end pt-3">
                    <button type="button" class="btn btn-success px-4 py-2" id="btn-registrar-venta">
                        <i class="bi bi-save me-1"></i> Registrar Venta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Variable global para la tasa del día
const tasaDelDia = <?= $tasa_actual ?>;

// Función para calcular subtotal
function calcularSubtotal(row) {
    if (!row) {
        return;
    }
    
    const productoSelect = row.querySelector('.producto-select');
    const cantidadInput = row.querySelector('.cantidad');
    const subtotalDisplayUSD = row.querySelector('.subtotal-display-usd');
    const subtotalDisplayBS = row.querySelector('.subtotal-display-bs');
    const descuentoCheckbox = row.querySelector('.descuento-checkbox');
    const descuentoHidden = row.querySelector('input[name="descuento[]"]');
    
    if (!productoSelect || !cantidadInput || !subtotalDisplayUSD || !subtotalDisplayBS) {
        return;
    }
    
    const selectedValue = productoSelect.value;
    
    if (!selectedValue || selectedValue === '' || selectedValue === null) {
        subtotalDisplayUSD.textContent = '$ 0.00';
        subtotalDisplayBS.textContent = 'BS 0.00';
        if (descuentoHidden) descuentoHidden.value = "0";
        calcularTotales();
        return;
    }
    
    // Obtener datos del producto desde el servidor
    fetch('obtener_producto.php?id=' + encodeURIComponent(selectedValue))
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.producto) {
                const producto = data.producto;
                const precio = parseFloat(producto.precio) || 0;
                let cantidad = parseInt(cantidadInput.value) || 0;
                
                // Validar cantidad
                if (cantidad <= 0) {
                    cantidad = 1;
                    cantidadInput.value = 1;
                }
                
                // Calcular subtotal en USD: precio * cantidad
                let subtotalUSD = precio * cantidad;
                
                // Aplicar descuento del 10% si está marcado
                if (descuentoCheckbox && descuentoCheckbox.checked) {
                    subtotalUSD *= 0.9;
                    if (descuentoHidden) descuentoHidden.value = "1";
                } else {
                    if (descuentoHidden) descuentoHidden.value = "0";
                }
                
                // Calcular subtotal en BS: subtotal USD * tasa del día
                const subtotalBS = subtotalUSD * tasaDelDia;

                subtotalDisplayUSD.textContent = '$ ' + subtotalUSD.toFixed(2);
                subtotalDisplayBS.textContent = 'BS ' + subtotalBS.toFixed(2);
            } else {
                subtotalDisplayUSD.textContent = '$ 0.00';
                subtotalDisplayBS.textContent = 'BS 0.00';
                if (descuentoHidden) descuentoHidden.value = "0";
            }
            calcularTotales();
        })
        .catch(error => {
            console.error('Error al obtener producto:', error);
            subtotalDisplayUSD.textContent = '$ 0.00';
            subtotalDisplayBS.textContent = 'BS 0.00';
            if (descuentoHidden) descuentoHidden.value = "0";
            calcularTotales();
        });
}

// Función para calcular totales
function calcularTotales() {
    let totalUSD = 0;
    let totalBS = 0;
    
    document.querySelectorAll('.subtotal-display-usd').forEach(span => {
        const value = parseFloat(span.textContent.replace('$ ', '')) || 0;
        totalUSD += value;
    });
    
    document.querySelectorAll('.subtotal-display-bs').forEach(span => {
        const value = parseFloat(span.textContent.replace('BS ', '')) || 0;
        totalBS += value;
    });
    
    document.getElementById('total-usd').textContent = '$ ' + totalUSD.toFixed(2);
    document.getElementById('total-bs').textContent = 'BS ' + totalBS.toFixed(2);
}

document.addEventListener('DOMContentLoaded', function() {
    const productosContainer = document.getElementById('productos-container');
    const btnAgregar = document.getElementById('btn-agregar-producto');

    // Cargar todos los productos
    let productosDisponibles = [];
    fetch('productos_todos.php')
        .then(response => response.json())
        .then(productos => {
            productosDisponibles = productos;
            
            // Calcular subtotales iniciales después de cargar productos
            document.querySelectorAll('.producto-item').forEach(function(row) {
                const productoSelect = row.querySelector('.producto-select');
                if (productoSelect && productoSelect.value) {
                    calcularSubtotal(row);
                }
            });
            calcularTotales();
        })
        .catch(error => {
            console.error('Error al cargar productos:', error);
        });

    // Evento para cambio de producto en cada fila (select nativo)
    document.addEventListener('change', function(e) {
        if (e.target && e.target.classList.contains('producto-select')) {
            const select = e.target;
            const row = select.closest('.producto-item');
            if (row) {
                calcularSubtotal(row);
            }
        }
    });

    const productoTemplate = () => {
        let options = '<option value="">Seleccione un producto</option>';
        productosDisponibles.forEach(producto => {
            options += `<option value="${producto.id}">${producto.nombre}</option>`;
        });
        return `
        <tr class="producto-item">
            <td class="ps-4">
                <select class="form-select border-0 producto-select" name="producto_id[]" required>
                    ${options}
                </select>
            </td>
            <td class="text-center">
                <input type="number" name="cantidad[]" min="1" max="999" maxlength="3" value="1" class="form-control border-0 text-center cantidad" required>
            </td>
            <td class="text-end">
                <span class="fw-semibold subtotal-display-usd">$ 0.00</span>
            </td>
            <td class="text-end">
                <span class="fw-semibold subtotal-display-bs">BS 0.00</span>
            </td>
            <td class="text-center pe-4">
                <input type="checkbox" class="form-check-input descuento-checkbox">
                <input type="hidden" name="descuento[]" value="0">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger btn-eliminar" title="Eliminar producto">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
    `;
    };

    btnAgregar.addEventListener('click', function() {
        const nuevoProducto = document.createElement('tr');
        nuevoProducto.classList.add('producto-item');
        nuevoProducto.innerHTML = productoTemplate();
        productosContainer.appendChild(nuevoProducto);

        // Agregar event listeners al nuevo select y cantidad
        const productoSelect = nuevoProducto.querySelector('.producto-select');
        productoSelect.addEventListener('change', function() {
            const row = this.closest('.producto-item');
            calcularSubtotal(row);
        });
        
        const cantidadInput = nuevoProducto.querySelector('.cantidad');
        cantidadInput.addEventListener('input', function() {
            const row = this.closest('.producto-item');
            calcularSubtotal(row);
        });
        cantidadInput.addEventListener('blur', function() {
            const row = this.closest('.producto-item');
            calcularSubtotal(row);
        });
        
        const descuentoCheckbox = nuevoProducto.querySelector('.descuento-checkbox');
        descuentoCheckbox.addEventListener('change', function() {
            const row = this.closest('.producto-item');
            calcularSubtotal(row);
        });

        const btnEliminar = nuevoProducto.querySelector('.btn-eliminar');
        btnEliminar.addEventListener('click', function() {
            if (productosContainer.children.length > 1) {
                nuevoProducto.remove();
                calcularTotales();
            }
        });
    });

    productosContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-eliminar') || e.target.closest('.btn-eliminar')) {
            const btn = e.target.classList.contains('btn-eliminar') ? e.target : e.target.closest('.btn-eliminar');
            if (productosContainer.children.length > 1) {
                btn.closest('tr').remove();
                calcularTotales();
            }
        }
    });

    // Calcular subtotales iniciales y agregar event listeners
    document.querySelectorAll('.producto-item').forEach(function(row) {
        // Agregar event listener al select nativo
        const productoSelect = row.querySelector('.producto-select');
        if (productoSelect) {
            productoSelect.addEventListener('change', function() {
                const row = this.closest('.producto-item');
                calcularSubtotal(row);
            });
            
            // Si ya tiene un valor seleccionado, calcular subtotal
            if (productoSelect.value) {
                calcularSubtotal(row);
            }
        }
        
        // Agregar event listeners a cantidad
        const cantidadInput = row.querySelector('.cantidad');
        if (cantidadInput) {
            cantidadInput.addEventListener('input', function() {
                const row = this.closest('.producto-item');
                calcularSubtotal(row);
            });
            cantidadInput.addEventListener('blur', function() {
                const row = this.closest('.producto-item');
                calcularSubtotal(row);
            });
        }
        
        // Agregar event listener a descuento
        const descuentoCheckbox = row.querySelector('.descuento-checkbox');
        if (descuentoCheckbox) {
            descuentoCheckbox.addEventListener('change', function() {
                const row = this.closest('.producto-item');
                calcularSubtotal(row);
            });
        }
    });

    // Inicializar Select2 solo para el select de cliente
    $('.select2-cliente').select2({
        placeholder: 'Buscar cliente...',
        allowClear: true,
        width: '100%'
    });

    // Manejar el botón de registrar venta
    document.getElementById('btn-registrar-venta').addEventListener('click', function() {
        // Validar que el cliente esté seleccionado antes de abrir el modal
        const clienteSelect = document.getElementById('cliente');
        const clienteId = clienteSelect ? clienteSelect.value : null;
        
        if (!clienteId || clienteId === '' || clienteId === '0') {
            alert('Por favor seleccione un cliente antes de registrar la venta.');
            if (clienteSelect) {
                clienteSelect.focus();
                // Si está usando Select2, abrir el dropdown
                if (typeof $(clienteSelect).select2 !== 'undefined') {
                    $(clienteSelect).select2('open');
                }
            }
            return;
        }
        
        // Validar que haya al menos un producto
        const productosContainer = document.getElementById('productos-container');
        const productosRows = productosContainer ? productosContainer.querySelectorAll('.producto-item') : [];
        let productosValidos = 0;
        
        productosRows.forEach(function(row) {
            const productoSelect = row.querySelector('.producto-select');
            const cantidadInput = row.querySelector('.cantidad');
            const productoId = productoSelect ? productoSelect.value : null;
            const cantidad = parseInt(cantidadInput ? cantidadInput.value : 0) || 0;
            
            if (productoId && productoId !== '' && cantidad > 0) {
                productosValidos++;
            }
        });
        
        if (productosValidos === 0) {
            alert('Debe agregar al menos un producto a la venta.');
            return;
        }
        
        const modal = new bootstrap.Modal(document.getElementById('modalMetodoPago'));
        modal.show();
    });

    // Inicializar Select2 para método de pago
    $('#metodo_pago_id').select2({
        placeholder: 'Seleccione un método...',
        allowClear: false,
        width: '100%',
        dropdownParent: $('#modalMetodoPago')
    });
    
    // Manejar cambio de método de pago
    $('#metodo_pago_id').on('change', function() {
        const metodoId = $(this).val();
        const referenciaContainer = document.getElementById('numero_referencia_container');
        const referenciaInput = document.getElementById('numero_referencia');
        const selectedOption = $(this).find('option:selected');
        const requiereReferencia = selectedOption.data('requiere-referencia') == 1;
        
        if (!requiereReferencia) {
            // Ocultar y limpiar el campo de referencia si no lo requiere
            referenciaContainer.style.display = 'none';
            referenciaInput.value = '';
            referenciaInput.removeAttribute('required');
        } else {
            // Mostrar y hacer obligatorio si requiere referencia
            referenciaContainer.style.display = 'block';
            referenciaInput.setAttribute('required', 'required');
        }
    });

    // Manejar la confirmación del pago
    document.getElementById('btn-confirmar-pago').addEventListener('click', function() {
        const metodoId = $('#metodo_pago_id').val();
        const referenciaInput = document.getElementById('numero_referencia');
        const referencia = referenciaInput.value;
        const selectedOption = $('#metodo_pago_id').find('option:selected');
        const requiereReferencia = selectedOption.data('requiere-referencia') == 1;
        const metodoNombre = selectedOption.text();
        
        if (!metodoId) {
            alert('Por favor seleccione un método de pago.');
            return;
        }
        
        // Validar número de referencia solo si es requerido
        if (requiereReferencia && !referencia) {
            alert('El número de referencia es obligatorio para ' + metodoNombre + '.');
            referenciaInput.focus();
            return;
        }
        
        // Agregar campos hidden al formulario principal
        const form = document.getElementById('form-venta');
        
        // Eliminar campos hidden anteriores si existen
        const existingMetodo = form.querySelector('input[name="metodo_pago_id"]');
        const existingReferencia = form.querySelector('input[name="numero_referencia"]');
        if (existingMetodo) existingMetodo.remove();
        if (existingReferencia) existingReferencia.remove();
        
        const hiddenMetodo = document.createElement('input');
        hiddenMetodo.type = 'hidden';
        hiddenMetodo.name = 'metodo_pago_id';
        hiddenMetodo.value = metodoId;
        form.appendChild(hiddenMetodo);
        
        if (referencia) {
            const hiddenReferencia = document.createElement('input');
            hiddenReferencia.type = 'hidden';
            hiddenReferencia.name = 'numero_referencia';
            hiddenReferencia.value = referencia;
            form.appendChild(hiddenReferencia);
        }
        
        // Cerrar modal y enviar formulario
        const modal = bootstrap.Modal.getInstance(document.getElementById('modalMetodoPago'));
        modal.hide();
        form.submit();
    });
});
</script>

<?php include 'modal_metodopago.php'; ?>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
