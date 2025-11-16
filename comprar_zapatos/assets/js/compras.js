document.addEventListener('DOMContentLoaded', function() {
    const detallesCompra = document.getElementById('detalles-compra');
    const btnAgregar = document.getElementById('agregar-item');
    
    btnAgregar.addEventListener('click', function() {
        const nuevoItem = document.createElement('div');
        nuevoItem.className = 'detalle-item';
        nuevoItem.innerHTML = `
            <select name="producto_id[]" class="select-producto" required>
                <option value="">Seleccionar producto</option>
                <?php foreach ($productos as $prod): ?>
                    <option value="<?= $prod['id'] ?>" data-precio="<?= $prod['precio_compra'] ?>">
                        <?= $prod['nombre'] ?> - $<?= $prod['precio_compra'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="number" name="cantidad[]" min="1" value="1" required>
            <button type="button" class="eliminar-item">Eliminar</button>
        `;
        detallesCompra.appendChild(nuevoItem);
        
        // Agregar evento al nuevo botón eliminar
        nuevoItem.querySelector('.eliminar-item').addEventListener('click', function() {
            if(document.querySelectorAll('.detalle-item').length > 1) {
                nuevoItem.remove();
            }
        });
    });
    
    // Delegación de eventos para eliminar
    detallesCompra.addEventListener('click', function(e) {
        if(e.target.classList.contains('eliminar-item')) {
            if(document.querySelectorAll('.detalle-item').length > 1) {
                e.target.closest('.detalle-item').remove();
            }
        }
    });
});