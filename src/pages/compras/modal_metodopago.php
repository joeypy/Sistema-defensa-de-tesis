<!-- Modal para Método de Pago -->
<?php
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../../includes/config.php';
}
?>
<div class="modal fade" id="modalMetodoPago" tabindex="-1" aria-labelledby="modalMetodoPagoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalMetodoPagoLabel">Método de Pago</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="metodo_pago" class="form-label">Método de Pago:</label>
                    <select id="metodo_pago" class="form-select" required>
                        <option value="">Seleccione un método</option>
                        <option value="Efectivo">Efectivo</option>
                        <option value="Pago Móvil">Pago Móvil</option>
                        <option value="Punto de Venta">Punto de Venta</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="numero_referencia" class="form-label">Número de Referencia:</label>
                    <input type="text" id="numero_referencia" class="form-control" pattern="[0-9]{4}" maxlength="4" placeholder="4 dígitos" required>
                </div>
                <div class="mb-3">
                    <label for="monto_pago" class="form-label">Monto del Pago:</label>
                    <input type="number" id="monto_pago" class="form-control" step="0.01" min="0" placeholder="Ingrese el monto" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-confirmar-pago">Confirmar Pago</button>
            </div>
        </div>
    </div>
</div>