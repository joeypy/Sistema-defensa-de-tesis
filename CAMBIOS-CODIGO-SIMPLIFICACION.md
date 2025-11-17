# 📋 CAMBIOS NECESARIOS EN EL CÓDIGO PARA SIMPLIFICACIÓN

## 🗑️ ARCHIVOS A ELIMINAR COMPLETAMENTE

### Carpeta `src/pages/compras/` (TODA LA CARPETA)
- `registrar_compra.php`
- `detalle_compra.php`
- `historial_compras.php`
- `modal_metodopago.php`
- `obtener_producto.php`
- `productos_todos.php`

### Carpeta `src/pages/proveedores/` (TODA LA CARPETA)
- `gestion_proveedores.php`
- `gestion_proveedores_ajax.php`
- `nuevo_proveedor.php`
- `sincronizar_productos.php`
- `api_proveedor.php`

---

## ✏️ ARCHIVOS A MODIFICAR

### 1. `src/includes/header.php`
**Cambios:**
- Cambiar "Nueva Compra" → "Nueva Venta"
- Cambiar "Historial" → "Historial de Ventas"
- Cambiar enlaces de `/compras/` → `/ventas/`
- Eliminar referencias a proveedores si existen

### 2. `src/pages/index.php`
**Cambios:**
- Cambiar todas las queries de `compras` → `ventas`
- Cambiar `detalles_compra` → `detalles_venta`
- Cambiar `facturas_compras` → `facturas_ventas`
- Cambiar "Compras Totales" → "Ventas Totales"
- Cambiar "Últimas Compras" → "Últimas Ventas"
- Cambiar "Productos Más Comprados" → "Productos Más Vendidos"
- Cambiar "Compras por Mes" → "Ventas por Mes"
- Actualizar enlaces de `/compras/` → `/ventas/`

### 3. `src/pages/productos/gestion_productos.php`
**Cambios:**
- Eliminar JOIN con `proveedores`
- Eliminar columna "Proveedor" de la tabla
- Eliminar filtro por proveedor
- Eliminar campo `proveedor_id` de queries
- Eliminar campo `imagen_path` de queries y visualización

### 4. `src/pages/productos/gestion_productos_ajax.php`
**Cambios:**
- Eliminar campo `proveedor_id` del UPDATE
- Eliminar campo `imagen_path` del UPDATE
- Eliminar referencias a proveedores

### 5. `src/pages/productos/nuevo_producto.php`
**Cambios:**
- Eliminar select de proveedor
- Eliminar campo `proveedor_id` del INSERT
- Eliminar campo `imagen_path` del INSERT
- Eliminar query de proveedores

### 6. `src/pages/productos/bajo_stock.php`
**Cambios:**
- Eliminar JOIN con `proveedores` si existe
- Eliminar columna "Proveedor" si existe
- Eliminar campo `proveedor_id` de queries

### 7. `src/pages/reportes/reporte_ventas.php`
**Cambios:**
- Cambiar todas las queries de `compras` → `ventas`
- Cambiar `detalles_compra` → `detalles_venta`
- Cambiar "Reportes de Compras" → "Reportes de Ventas"
- Cambiar "Resumen de Compras" → "Resumen de Ventas"
- Cambiar "Compras por Cliente" → "Ventas por Cliente"
- Cambiar "Productos Más Comprados" → "Productos Más Vendidos"
- Eliminar JOIN con `proveedores`
- Eliminar filtro por proveedor si existe

### 8. `src/pages/compras/obtener_producto.php` (si se mantiene para ventas)
**Cambios:**
- Renombrar a `src/pages/ventas/obtener_producto.php`
- Eliminar campo `proveedor_nombre` del SELECT
- Eliminar JOIN con `proveedores`

---

## 🆕 ARCHIVOS A CREAR (Nueva estructura de ventas)

### Carpeta `src/pages/ventas/` (NUEVA)

1. **`registrar_venta.php`**
   - Similar a `registrar_compra.php` pero:
     - Usa tabla `ventas` en lugar de `compras`
     - Usa `detalles_venta` en lugar de `detalles_compra`
     - Usa `facturas_ventas` en lugar de `facturas_compras`
     - Usa `metodo_pago_ventas` en lugar de `metodo_pago`
     - **DESCUENTA stock** en lugar de aumentarlo
     - Validar que el stock sea suficiente antes de vender

2. **`detalle_venta.php`**
   - Similar a `detalle_compra.php` pero con tablas de ventas

3. **`historial_ventas.php`**
   - Similar a `historial_compras.php` pero con tablas de ventas

4. **`modal_metodopago.php`**
   - Puede ser el mismo que el de compras, solo cambiar la tabla destino

5. **`obtener_producto.php`**
   - Similar al actual pero sin proveedor

6. **`productos_todos.php`**
   - Similar al actual pero sin proveedor

---

## 🔍 QUERIES ESPECÍFICAS A CAMBIAR

### En `index.php`:
```php
// ANTES:
$totalCompras = $pdo->query("SELECT COUNT(*) as total FROM compras")->fetch(PDO::FETCH_ASSOC)['total'];

// DESPUÉS:
$totalVentas = $pdo->query("SELECT COUNT(*) as total FROM ventas")->fetch(PDO::FETCH_ASSOC)['total'];
```

```php
// ANTES:
SELECT c.fecha, COALESCE(cl.nombre, 'Sin cliente') as cliente, f.numero_factura, c.total 
FROM compras c
LEFT JOIN clientes cl ON c.cliente_id = cl.id
LEFT JOIN facturas_compras f ON f.compra_id = c.id

// DESPUÉS:
SELECT v.fecha, cl.nombre as cliente, f.numero_factura, v.total 
FROM ventas v
INNER JOIN clientes cl ON v.cliente_id = cl.id
LEFT JOIN facturas_ventas f ON f.venta_id = v.id
```

```php
// ANTES:
SELECT p.nombre, SUM(dc.cantidad) as total
FROM detalles_compra dc
JOIN productos p ON dc.producto_id = p.id
JOIN compras c ON dc.compra_id = c.id

// DESPUÉS:
SELECT p.nombre, SUM(dv.cantidad) as total
FROM detalles_venta dv
JOIN productos p ON dv.producto_id = p.id
JOIN ventas v ON dv.venta_id = v.id
```

### En `gestion_productos.php`:
```php
// ANTES:
SELECT p.*, pr.nombre AS proveedor_nombre
FROM productos p
LEFT JOIN proveedores pr ON p.proveedor_id = pr.id

// DESPUÉS:
SELECT p.*
FROM productos p
```

### En `nuevo_producto.php`:
```php
// ANTES:
INSERT INTO productos (nombre, descripcion, color, precio, stock, stock_minimo, proveedor_id, imagen_path)
VALUES (?, ?, ?, ?, ?, ?, ?, ?)

// DESPUÉS:
INSERT INTO productos (nombre, descripcion, color, precio, stock, stock_minimo)
VALUES (?, ?, ?, ?, ?, ?)
```

---

## ⚠️ VALIDACIONES IMPORTANTES

### En `registrar_venta.php`:
1. **Validar stock suficiente** antes de registrar la venta:
   ```php
   // Verificar que el stock sea suficiente
   $stmt = $pdo->prepare("SELECT stock FROM productos WHERE id = ?");
   $stmt->execute([$producto_id]);
   $producto = $stmt->fetch(PDO::FETCH_ASSOC);
   
   if ($producto['stock'] < $cantidad) {
       throw new Exception("Stock insuficiente para el producto: {$producto['nombre']}");
   }
   ```

2. **El trigger ya descuenta el stock automáticamente**, pero es buena práctica validar antes.

---

## 📝 NOTAS ADICIONALES

1. **El trigger `after_detalle_venta_insert` ya está creado** en el script SQL, así que el stock se descuenta automáticamente.

2. **Cambiar todos los textos** de "Compra" a "Venta" en la interfaz.

3. **Actualizar títulos y encabezados** en todos los archivos.

4. **Verificar que no queden referencias** a `proveedores`, `compras`, `detalles_compra`, `facturas_compras`, `metodo_pago` (el de compras), `fotos_productos`, `oportunidades`, etc.

5. **Actualizar el título del sistema** en `header.php` de "Sistema de Gestión de Compras" a "Sistema de Gestión de Ventas" o similar.

---

## ✅ CHECKLIST DE VERIFICACIÓN

- [ ] Ejecutar script SQL `09-simplificar-solo-ventas.sql`
- [ ] Eliminar carpeta `src/pages/compras/`
- [ ] Eliminar carpeta `src/pages/proveedores/`
- [ ] Crear carpeta `src/pages/ventas/`
- [ ] Crear archivos de ventas (registrar, detalle, historial)
- [ ] Actualizar `header.php`
- [ ] Actualizar `index.php`
- [ ] Actualizar archivos de productos
- [ ] Actualizar `reporte_ventas.php`
- [ ] Verificar que no queden referencias a tablas eliminadas
- [ ] Probar registro de venta
- [ ] Probar descuento de stock
- [ ] Probar validación de stock insuficiente

