# 📋 TABLAS Y CAMPOS NECESARIOS - BASADO EN LA INTERFAZ ACTUAL

## 🎯 RESUMEN

Este documento lista todas las tablas y campos que realmente se usan en la interfaz actual del sistema, basado en el análisis del código PHP.

---

## ✅ TABLAS NECESARIAS (Sistema de Ventas)

### 1. **`usuarios`** - Autenticación y gestión de usuarios

**Campos necesarios:**

- `id` (INT, PK, AUTO_INCREMENT) - Identificador único
- `username` (VARCHAR(50), UNIQUE, NOT NULL) - Nombre de usuario para login
- `password` (VARCHAR(255), NOT NULL) - Contraseña hasheada
- `nombre` (VARCHAR(100), NOT NULL) - Nombre completo del usuario
- `email` (VARCHAR(255), UNIQUE, NULLABLE) - Email del usuario
- `direccion` (VARCHAR(255), NULLABLE) - Dirección del usuario
- `telefono` (VARCHAR(50), NULLABLE) - Teléfono del usuario

**Uso en la interfaz:**

- Login/autenticación
- Mostrar nombre del usuario en el header
- Validar permisos según rol
- Identificar quién registra ventas (`usuario_id`)

---

### 2. **`clientes`** - Gestión de clientes

**Campos necesarios:**

- `id` (INT, PK, AUTO_INCREMENT) - Identificador único
- `nombre` (VARCHAR(100), NOT NULL) - Nombre del cliente
- `identificacion` (VARCHAR(20), UNIQUE, NOT NULL) - Cédula/RIF
- `direccion` (TEXT, NULLABLE) - Dirección del cliente
- `telefono` (VARCHAR(20), NULLABLE) - Teléfono del cliente
- `email` (VARCHAR(255), NULLABLE) - Email del cliente
- `creado_en` (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP) - Fecha de registro

**Uso en la interfaz:**

- Lista de clientes (`gestion_clientes.php`)
- Formulario nuevo cliente (`nuevo_cliente.php`)
- Edición de clientes (modal)
- Selección de cliente al registrar venta
- Mostrar información del cliente en detalle de venta
- Filtros y búsquedas

**Campos eliminados (ya unificados):**

- ❌ `clientes_emails` (tabla eliminada)
- ❌ `clientes_telefonos` (tabla eliminada)

---

### 3. **`productos`** - Catálogo de productos

**Campos necesarios:**

- `id` (INT, PK, AUTO_INCREMENT) - Identificador único
- `nombre` (VARCHAR(50), NOT NULL) - Nombre del producto
- `descripcion` (VARCHAR(50), NULLABLE) - Descripción breve
- `precio` (DECIMAL(10,2), NOT NULL) - Precio único (unificado)
- `stock` (INT, DEFAULT 0) - Cantidad en inventario

**Campos eliminados:**

- ❌ `proveedor_id` - Ya no se usa proveedores
- ❌ `imagen_path` - No se usan imágenes
- ❌ `imagen` - No se usan imágenes
- ❌ `foto` - No se usan imágenes
- ❌ `precio_compra` - Unificado en `precio`
- ❌ `precio_venta` - Unificado en `precio`

**Uso en la interfaz:**

- Lista de productos (`gestion_productos.php`)
- Formulario nuevo producto (`nuevo_producto.php`)
- Edición de productos (AJAX)
- Selección de productos al registrar venta
- Cálculo de subtotales (precio × cantidad)
- Validación de stock antes de vender
- Alertas de stock bajo
- Reportes de productos más vendidos

---

### 4. **`ventas`** - Registro de ventas (antes `compras`)

**Campos necesarios:**

- `id` (INT, PK, AUTO_INCREMENT) - Identificador único
- `cliente_id` (INT, NOT NULL, FK → `clientes.id`) - Cliente que compra
- `fecha` (DATETIME, DEFAULT CURRENT_TIMESTAMP) - Fecha y hora de la venta
- `total_dolares` (DECIMAL(10,2), NOT NULL) - Total de la venta en USD
- `total_bs` (DECIMAL(10,2), NOT NULL) - Total de la venta en USD
  asociada
- `numero_factura` (VARCHAR(30), NOT NULL) - Número de factura fiscal
- `numero_control` (VARCHAR(30), NOT NULL) - Número de control fiscal
- `metodo_pago_id` (INT, NOT NULL, FK → `metodo_pago.id`) - Tabla relacionada

**Uso en la interfaz:**

- Registro de nueva venta (`registrar_venta.php`)
- Historial de ventas (`historial_ventas.php`)
- Detalle de venta (`detalle_venta.php`)
- Dashboard (últimas ventas, totales)
- Reportes (ventas por mes, por cliente)
- Gráficos y estadísticas

**Cambios respecto a `compras`:**

- ✅ `cliente_id` es OBLIGATORIO (NOT NULL) - antes podía ser NULL
- ✅ Se usa para DESCONTAR stock (no aumentar)

---

### 5. **`detalles_venta`** - Detalles de cada venta (antes `detalles_compra`)

**Campos necesarios:**

- `id` (INT, PK, AUTO_INCREMENT) - Identificador único
- `venta_id` (INT, NOT NULL, FK → `ventas.id`) - Venta a la que pertenece
- `producto_id` (INT, NOT NULL, FK → `productos.id`) - Producto vendido
- `cantidad` (INT(3), NOT NULL) - Cantidad vendida
- `precio_unitario` (DECIMAL(10,2), NOT NULL) - Precio al momento de la venta
- `subtotal` (DECIMAL(10,2), NOT NULL) - Subtotal (precio × cantidad)
- `descuento` (TINYINT(1), DEFAULT 0) - 1 si se aplicó descuento del 10%, 0 si no

**Uso en la interfaz:**

- Registro de productos en una venta
- Detalle de venta (lista de productos)
- Cálculo de totales
- Reportes de productos más vendidos
- **Trigger automático:** Descuenta stock al insertar

**Campos eliminados:**

- ❌ `marca_id` - Ya no se usan marcas

---

### 7. **`metodo_pago`** - Métodos de pago de ventas (antes `metodo_pago`)

**Campos necesarios:**

- `id` (INT, PK, AUTO_INCREMENT) - Identificador único
- `venta_id` (INT, NOT NULL, FK → `ventas.id`, UNIQUE) - Venta asociada
- `metodo` (VARCHAR(50), NOT NULL) - Método de pago (Efectivo, Pago Móvil, Punto de Venta)
- `numero_referencia` (VARCHAR(50), NULLABLE) - Número de referencia (obligatorio si no es Efectivo)

**Uso en la interfaz:**

- Modal de método de pago al registrar venta
- Validación: Efectivo no requiere referencia, otros métodos sí
- Detalle de venta (mostrar método de pago)
- Reportes

**Relación:**

- 1 venta = 1 método de pago (relación 1:1)

---

### 8. **`tasa_diaria`** - Tasa de cambio USD/VES

**Campos necesarios:**

- `id` (INT, PK, AUTO_INCREMENT) - Identificador único
- `fecha` (DATE, UNIQUE, NOT NULL) - Fecha de la tasa
- `tasa` (DECIMAL(10,2), NOT NULL) - Tasa de cambio (VES por USD)
- `descripcion` (VARCHAR(255), NULLABLE) - Descripción/notas

**Uso en la interfaz:**

- Gestión de tasa (`gestion_tasa.php`)
- Integración con API externa (dolarapi.com)
- Cálculo de subtotales en BS al registrar venta
- Mostrar tasa actual en el formulario de venta
