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

        $cliente_id = $_POST['cliente_id'];
        $usuario_id = $_SESSION['usuario_id'];
        $marcas_ids = $_POST['marca_id'];
        $productos_ids = $_POST['producto_id'];
        $cantidades = $_POST['cantidad'];
        $descuentos = $_POST['descuento'];

        $total = 0;
        $detalles = [];

        for ($i = 0; $i < count($productos_ids); $i++) {
            $marca_id = $marcas_ids[$i];
            $producto_id = $productos_ids[$i];
            $cantidad = $cantidades[$i];
            $descuento = $descuentos[$i];

            // Obtener precio del producto
            $stmt = $pdo->prepare("SELECT precio_compra FROM productos WHERE id = ?");
            $stmt->execute([$producto_id]);
            $producto = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$producto) {
                throw new Exception("Producto con ID $producto_id no encontrado.");
            }

            $precio_unitario = $producto['precio_compra'];

            $subtotal = $precio_unitario * $cantidad;
            if ($descuento == "1") {
                $subtotal *= 0.9; // Aplicar descuento del 10%
            }
            $total += $subtotal;

            $detalles[] = [
                'marca_id' => $marca_id,
                'producto_id' => $producto_id,
                'cantidad' => $cantidad,
                'precio_unitario' => $precio_unitario,
                'subtotal' => $subtotal
            ];
        }

        $stmt = $pdo->prepare("INSERT INTO compras (cliente_id, usuario_id, total) VALUES (?, ?, ?)");
        $stmt->execute([$cliente_id, $usuario_id, $total]);
        $compra_id = $pdo->lastInsertId();

        $numero_factura = $_POST['numero_factura'];
        $numero_control = $_POST['numero_control'];
        $fecha_factura = $_POST['fecha_factura'];

        $stmt = $pdo->prepare("INSERT INTO facturas_compras (compra_id, numero_factura, numero_control, fecha) VALUES (?, ?, ?, ?)");
        $stmt->execute([$compra_id, $numero_factura, $numero_control, $fecha_factura]);

        foreach ($detalles as $detalle) {
            $stmt = $pdo->prepare("INSERT INTO detalles_compra (compra_id, marca_id, producto_id, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$compra_id, $detalle['marca_id'], $detalle['producto_id'], $detalle['cantidad'], $detalle['precio_unitario'], $detalle['subtotal']]);

            $stmt = $pdo->prepare("UPDATE productos SET stock = stock + ? WHERE id = ?");
            $stmt->execute([$detalle['cantidad'], $detalle['producto_id']]);
        }

        // Insertar método de pago si se proporcionó
        if (isset($_POST['metodo_pago']) && isset($_POST['numero_referencia']) && isset($_POST['monto_pago'])) {
            $stmt = $pdo->prepare("INSERT INTO metodo_pago (compra_id, metodo, numero_referencia, monto) VALUES (?, ?, ?, ?)");
            $stmt->execute([$compra_id, $_POST['metodo_pago'], $_POST['numero_referencia'], $_POST['monto_pago']]);
        }

        $pdo->commit();
        header("Location: " . PAGES_URL . "/compras/registrar_compra.php?success=1");
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error al registrar la compra: " . $e->getMessage();
    }
}

$marcas = $pdo->query("SELECT id, nombre FROM marcas")->fetchAll(PDO::FETCH_ASSOC);
$clientes = $pdo->query("SELECT id, nombre FROM clientes")->fetchAll(PDO::FETCH_ASSOC);

// Obtener el siguiente número de control
$ultimo_control = $pdo->query("SELECT numero_control FROM facturas_compras ORDER BY id DESC LIMIT 1")->fetchColumn();
if ($ultimo_control) {
    $parts = explode('-', $ultimo_control);
    $numero = intval($parts[1]);
    $siguiente_numero = $numero + 1;
    $numero_control = '00-' . str_pad($siguiente_numero, 7, '0', STR_PAD_LEFT);
} else {
    $numero_control = '00-0000001';
}

// Obtener el siguiente número de factura
$ultimo_factura = $pdo->query("SELECT numero_factura FROM facturas_compras ORDER BY id DESC LIMIT 1")->fetchColumn();
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

$tasa_info = $pdo->query("SELECT tasa, fecha FROM tasa_diaria ORDER BY fecha DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$tasa_actual = $tasa_info['tasa'] ?? null;
$fecha_tasa = $tasa_info['fecha'] ?? null;

include __DIR__ . '/../../includes/header.php';
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary mb-0">
            <i class="bi bi-cart-plus me-2"></i>Registrar Nueva Compra
        </h2>
        <a href="<?= PAGES_URL ?>/compras/historial_compras.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success d-flex align-items-center" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <div>Compra registrada exitosamente!</div>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <div><?= $error ?></div>
                </div>
            <?php endif; ?>

            <form id="form-compra" method="POST" autocomplete="off">
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
                                    Tasa del día <?php echo $tasa_actual ? 'BCV= ' . number_format($tasa_actual, 2, '.', '') . ' VES (' . ($fecha_tasa ? date('d/m/Y', strtotime($fecha_tasa)) : 'sin fecha') . ')' : 'No disponible'; ?>
                                </span>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="18%" class="ps-4">Marca</th>
                                                <th width="22%" class="ps-4">Modelo</th>
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
                                                            <select class="form-select border-0 select2-marca-producto marca-select" name="marca_id[]" required>
                                                                <option value="">Seleccione una Marca</option>
                                                                <?php foreach ($marcas as $marca): ?>
                                                                    <option value="<?= $marca['id'] ?>" <?= ($producto['marca_id'] == $marca['id']) ? 'selected' : '' ?>><?= $marca['nombre'] ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </td>
                                                        <td class="ps-4">
                                                            <select class="form-select border-0 select2-producto producto-select" name="producto_id[]" required>
                                                                <option value="<?= $producto['id'] ?>" data-precio="<?= $producto['precio_compra'] ?>"><?= htmlspecialchars($producto['nombre']) ?> - $<?= number_format($producto['precio_compra'], 2, '.', '') ?></option>
                                                            </select>
                                                        </td>
                                                        <td class="text-center">
                                                            <input type="number" name="cantidad[]" min="1" max="999" maxlength="3" value="1" class="form-control border-0 text-center cantidad" required>
                                                        </td>
                                                        <td class="text-end">
                                                            <span class="fw-semibold subtotal-display-usd">$ <?= number_format($producto['precio_compra'] * 1, 2, '.', '') ?></span>
                                                        </td>
                                                        <td class="text-end">
                                                            <span class="fw-semibold subtotal-display-bs">BS <?= number_format($producto['precio_compra'] * ($tasa_actual ?: 1), 2, '.', '') ?></span>
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
                                                        <select class="form-select border-0 select2-marca-producto marca-select" name="marca_id[]" required>
                                                            <option value="">Seleccione una Marca</option>
                                                            <?php foreach ($marcas as $marca): ?>
                                                                <option value="<?= $marca['id'] ?>"><?= $marca['nombre'] ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </td>
                                                    <td class="ps-4">
                                                        <select class="form-select border-0 select2-producto producto-select" name="producto_id[]" required>
                                                            <option value="">Seleccione una Marca primero</option>
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
                                                <td colspan="5" class="text-end fw-bold ps-4">Total:</td>
                                                <td class="text-end fw-bold">
                                                    <span id="total-usd">$ 0.00</span>
                                                </td>
                                                <td class="text-end fw-bold">
                                                    <span id="total-bs">BS 0.00</span>
                                                </td>
                                                <td></td>
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
                    <button type="button" class="btn btn-success px-4 py-2" id="btn-registrar-compra">
                        <i class="bi bi-save me-1"></i> Registrar Compra
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const tasa = <?php echo $tasa_actual ? $tasa_actual : 1; ?>;

// Función para calcular subtotal
function calcularSubtotal(row) {
    const select = row.querySelector('.producto-select');
    const cantidadInput = row.querySelector('.cantidad');
    const subtotalDisplayUSD = row.querySelector('.subtotal-display-usd');
    const subtotalDisplayBS = row.querySelector('.subtotal-display-bs');
    const descuentoCheckbox = row.querySelector('.descuento-checkbox');
    const descuentoHidden = row.querySelector('input[name="descuento[]"]');
    
    const selectedOption = select.options[select.selectedIndex];
    if (selectedOption && selectedOption.dataset.precio) {
        const precio = parseFloat(selectedOption.dataset.precio);
        const cantidad = parseInt(cantidadInput.value) || 0;
        let subtotalUSD = precio * cantidad;
        
        if (descuentoCheckbox.checked) {
            subtotalUSD *= 0.9; // Aplicar descuento del 10%
            descuentoHidden.value = "1";
        } else {
            descuentoHidden.value = "0";
        }
        
        const subtotalBS = subtotalUSD * tasa;

        subtotalDisplayUSD.textContent = '$ ' + subtotalUSD.toFixed(2);
        subtotalDisplayBS.textContent = 'BS ' + subtotalBS.toFixed(2);
    } else {
        subtotalDisplayUSD.textContent = '$ 0.00';
        subtotalDisplayBS.textContent = 'BS 0.00';
        descuentoHidden.value = "0";
    }
    calcularTotales();
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

    // Cargar productos por marca
    function cargarProductos(marcaId, selectElement) {
        selectElement.innerHTML = '<option value="">Cargando productos...</option>';
        fetch('productos_por_marca.php?marca_id=' + marcaId)
            .then(response => response.json())
            .then(productos => {
                let options = '<option value="">Seleccione un producto</option>';
                productos.forEach(producto => {
                    options += `<option value="${producto.id}" data-precio="${producto.precio_compra}">
                        ${producto.nombre} - $${parseFloat(producto.precio_compra).toFixed(2)}
                    </option>`;
                });
                selectElement.innerHTML = options;
                // Inicializar Select2 en el select cargado
                $(selectElement).select2({
                    placeholder: 'Buscar modelo...',
                    allowClear: true,
                    width: '100%'
                });
                // No seleccionar automáticamente el primer producto
                const row = selectElement.closest('.producto-item');
                calcularSubtotal(row);
            });
    }

    // Evento para cambio de marca en cada fila de producto
    $(document).on('select2:select select2:clear', '.marca-select', function (e) {
        const marcaId = $(this).val();
        const row = $(this).closest('.producto-item');
        const productoSelect = row.find('.producto-select');
        
        if (marcaId) {
            cargarProductos(marcaId, productoSelect[0]);
        } else {
            // Limpiar productos si no hay marca seleccionada
            productoSelect.html('<option value="" data-precio="0">Seleccione una Marca primero</option>');
            $(productoSelect).select2({
                placeholder: 'Buscar modelo...',
                allowClear: true,
                width: '100%'
            });
            calcularSubtotal(row[0]);
        }
    });

    // Evento para cambio de producto en cada fila
    $(document).on('select2:select select2:clear', '.producto-select', function (e) {
        const row = $(this).closest('.producto-item');
        calcularSubtotal(row[0]);
    });

    const productoTemplate = () => `
        <tr class="producto-item">
            <td class="ps-4">
                <select class="form-select border-0 select2-marca-producto marca-select" name="marca_id[]" required>
                    <option value="">Seleccione una Marca</option>
                    <?php foreach ($marcas as $marca): ?>
                        <option value="<?= $marca['id'] ?>"><?= $marca['nombre'] ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td class="ps-4">
                <select class="form-select border-0 select2-producto producto-select" name="producto_id[]" required>
                    <option value="" data-precio="0">Seleccione una Marca primero</option>
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

    btnAgregar.addEventListener('click', function() {
        const nuevoProducto = document.createElement('tr');
        nuevoProducto.classList.add('producto-item');
        nuevoProducto.innerHTML = productoTemplate();
        productosContainer.appendChild(nuevoProducto);

        // Inicializar Select2 para los nuevos selects
        const marcaSelect = nuevoProducto.querySelector('.marca-select');
        const productoSelect = nuevoProducto.querySelector('.producto-select');
        
        $(marcaSelect).select2({
            placeholder: 'Buscar marca...',
            allowClear: true,
            width: '100%'
        });
        
        $(productoSelect).select2({
            placeholder: 'Buscar modelo...',
            allowClear: true,
            width: '100%'
        });

        // Agregar event listeners al nuevo select y cantidad
        const cantidadInput = nuevoProducto.querySelector('.cantidad');
        cantidadInput.addEventListener('input', function() {
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
        // Inicializar Select2 para marca y producto en filas existentes
        const marcaSelect = row.querySelector('.marca-select');
        const productoSelect = row.querySelector('.producto-select');
        
        if (marcaSelect) {
            $(marcaSelect).select2({
                placeholder: 'Buscar marca...',
                allowClear: true,
                width: '100%'
            });
        }
        
        if (productoSelect) {
            $(productoSelect).select2({
                placeholder: 'Buscar modelo...',
                allowClear: true,
                width: '100%'
            });
        }
        
        calcularSubtotal(row);
        
        // Agregar event listeners
        const cantidadInput = row.querySelector('.cantidad');
        if (cantidadInput) {
            cantidadInput.addEventListener('input', function() {
                const row = this.closest('.producto-item');
                calcularSubtotal(row);
            });
        }
        
        const descuentoCheckbox = row.querySelector('.descuento-checkbox');
        if (descuentoCheckbox) {
            descuentoCheckbox.addEventListener('change', function() {
                const row = this.closest('.producto-item');
                calcularSubtotal(row);
            });
        }
    });
    calcularTotales();

    // Inicializar Select2 para búsqueda inteligente
    $('.select2-cliente').select2({
        placeholder: 'Buscar cliente...',
        allowClear: true,
        width: '100%'
    });

    // Inicializar Select2 para los selects de marca y productos existentes
    $('.select2-marca-producto').select2({
        placeholder: 'Buscar marca...',
        allowClear: true,
        width: '100%'
    });

    $('.select2-producto').select2({
        placeholder: 'Buscar modelo...',
        allowClear: true,
        width: '100%'
    });

    // Manejar el botón de registrar compra
    document.getElementById('btn-registrar-compra').addEventListener('click', function() {
        const modal = new bootstrap.Modal(document.getElementById('modalMetodoPago'));
        modal.show();
    });

    // Manejar la confirmación del pago
    document.getElementById('btn-confirmar-pago').addEventListener('click', function() {
        const metodo = document.getElementById('metodo_pago').value;
        const referencia = document.getElementById('numero_referencia').value;
        const monto = document.getElementById('monto_pago').value;
        
        if (!metodo || !referencia || !monto) {
            alert('Por favor complete todos los campos del método de pago.');
            return;
        }
        
        // Agregar campos hidden al formulario principal
        const form = document.getElementById('form-compra');
        const hiddenMetodo = document.createElement('input');
        hiddenMetodo.type = 'hidden';
        hiddenMetodo.name = 'metodo_pago';
        hiddenMetodo.value = metodo;
        form.appendChild(hiddenMetodo);
        
        const hiddenReferencia = document.createElement('input');
        hiddenReferencia.type = 'hidden';
        hiddenReferencia.name = 'numero_referencia';
        hiddenReferencia.value = referencia;
        form.appendChild(hiddenReferencia);
        
        const hiddenMonto = document.createElement('input');
        hiddenMonto.type = 'hidden';
        hiddenMonto.name = 'monto_pago';
        hiddenMonto.value = monto;
        form.appendChild(hiddenMonto);
        
        // Cerrar modal y enviar formulario
        const modal = bootstrap.Modal.getInstance(document.getElementById('modalMetodoPago'));
        modal.hide();
        form.submit();
    });
});
</script>

<?php include 'modal_metodopago.php'; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>