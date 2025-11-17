# Documentación de Módulos del Sistema

Este documento describe los diferentes módulos y páginas que componen el Sistema de Compras de Zapatos.

## Estructura General

El sistema está organizado en módulos funcionales ubicados en `src/pages/`. Cada módulo contiene las páginas relacionadas con una funcionalidad específica.

---

## 📁 Módulos del Sistema

### 🔐 Autenticación (`src/pages/auth/`)

#### `login.php`
- **Descripción**: Página de inicio de sesión del sistema
- **Funcionalidad**: 
  - Permite a los usuarios autenticarse con username y password
  - Valida credenciales contra la base de datos
  - Inicia sesión y redirige al dashboard
- **Acceso**: Público (no requiere autenticación)

#### `logout.php`
- **Descripción**: Cierre de sesión
- **Funcionalidad**: 
  - Destruye la sesión del usuario
  - Limpia cookies y variables de sesión
  - Redirige al login
- **Acceso**: Requiere autenticación

---

### 🏠 Dashboard (`src/pages/`)

#### `index.php`
- **Descripción**: Página principal del sistema (dashboard)
- **Funcionalidad**: 
  - Muestra estadísticas generales (compras totales, productos, clientes)
  - Lista las últimas compras realizadas
  - Panel de resumen con métricas clave
- **Acceso**: Requiere autenticación

---

### 👥 Clientes (`src/pages/clientes/`)

#### `gestion_clientes.php`
- **Descripción**: Gestión completa de clientes
- **Funcionalidad**: 
  - Lista todos los clientes con filtros y búsqueda
  - Permite editar y eliminar clientes
  - Muestra información de contacto (teléfonos, emails)
- **Acceso**: Requiere autenticación

#### `gestion_clientes_ajax.php`
- **Descripción**: Endpoint AJAX para operaciones CRUD de clientes
- **Funcionalidad**: 
  - Crear, actualizar y eliminar clientes
  - Gestionar teléfonos y emails asociados
  - Retorna respuestas JSON
- **Acceso**: Requiere autenticación (AJAX)

#### `nuevo_cliente.php`
- **Descripción**: Formulario para crear nuevos clientes
- **Funcionalidad**: 
  - Permite registrar clientes con nombre, identificación, dirección
  - Agregar múltiples teléfonos y emails
  - Validación de datos antes de guardar
- **Acceso**: Requiere autenticación

---

### 🛒 Compras (`src/pages/compras/`)

#### `registrar_compra.php`
- **Descripción**: Registro de nuevas compras
- **Funcionalidad**: 
  - Selección de cliente y productos
  - Cálculo automático de subtotales y totales (USD y BS)
  - Aplicación de descuentos del 10%
  - Registro de información fiscal (N° Factura, N° Control)
  - Selección de método de pago (Efectivo, Pago Móvil, Punto de Venta)
  - Actualización automática de stock
- **Acceso**: Requiere autenticación

#### `historial_compras.php`
- **Descripción**: Historial y listado de todas las compras
- **Funcionalidad**: 
  - Lista compras con filtros por fecha, cliente, factura
  - Ordenamiento por columnas
  - Paginación de resultados
  - Enlace a detalle de cada compra
- **Acceso**: Requiere autenticación

#### `detalle_compra.php`
- **Descripción**: Vista detallada de una compra específica
- **Funcionalidad**: 
  - Muestra información completa de la compra
  - Información del cliente asociado
  - Lista de productos comprados con cantidades y precios
  - Información fiscal y método de pago
- **Acceso**: Requiere autenticación

#### `obtener_producto.php`
- **Descripción**: Endpoint AJAX para obtener datos de un producto
- **Funcionalidad**: 
  - Retorna información completa del producto (precio, stock, proveedor)
  - Usado por `registrar_compra.php` para cálculos en tiempo real
- **Acceso**: Requiere autenticación (AJAX)

#### `productos_todos.php`
- **Descripción**: Endpoint AJAX que lista todos los productos
- **Funcionalidad**: 
  - Retorna lista JSON de todos los productos disponibles
  - Usado para poblar selects en formularios
- **Acceso**: Requiere autenticación (AJAX)

#### `modal_metodopago.php`
- **Descripción**: Modal para seleccionar método de pago
- **Funcionalidad**: 
  - Permite seleccionar método de pago (Efectivo, Pago Móvil, Punto de Venta)
  - Campo condicional para número de referencia (no requerido para Efectivo)
- **Acceso**: Requiere autenticación (usado por `registrar_compra.php`)

---

### 📦 Productos (`src/pages/productos/`)

#### `gestion_productos.php`
- **Descripción**: Gestión completa de productos
- **Funcionalidad**: 
  - Lista todos los productos con filtros avanzados
  - Búsqueda por código, nombre, precio, stock, proveedor
  - Ordenamiento por columnas
  - Edición y eliminación de productos
  - Paginación de resultados
- **Acceso**: Requiere autenticación

#### `gestion_productos_ajax.php`
- **Descripción**: Endpoint AJAX para operaciones CRUD de productos
- **Funcionalidad**: 
  - Actualizar productos (nombre, precio, stock, stock mínimo, proveedor)
  - Eliminar productos
  - Validaciones de stock (no permitir valores negativos)
  - Retorna respuestas JSON
- **Acceso**: Requiere autenticación (AJAX)

#### `nuevo_producto.php`
- **Descripción**: Formulario para crear nuevos productos
- **Funcionalidad**: 
  - Registro de productos con nombre, descripción, color, precio
  - Asignación de proveedor
  - Configuración de stock mínimo
  - Validación de campos
- **Acceso**: Requiere autenticación

#### `bajo_stock.php`
- **Descripción**: Lista de productos con stock bajo
- **Funcionalidad**: 
  - Muestra productos cuyo stock está por debajo del mínimo
  - Alertas visuales para productos críticos
  - Información de proveedor para reabastecimiento
- **Acceso**: Requiere autenticación

---

### 🏢 Proveedores (`src/pages/proveedores/`)

#### `gestion_proveedores.php`
- **Descripción**: Gestión de proveedores
- **Funcionalidad**: 
  - Lista todos los proveedores
  - Crear, editar y eliminar proveedores
  - Configuración de API para sincronización
- **Acceso**: Requiere autenticación

#### `gestion_proveedores_ajax.php`
- **Descripción**: Endpoint AJAX para operaciones CRUD de proveedores
- **Funcionalidad**: 
  - Operaciones de creación, actualización y eliminación
  - Retorna respuestas JSON
- **Acceso**: Requiere autenticación (AJAX)

#### `nuevo_proveedor.php`
- **Descripción**: Formulario para crear nuevos proveedores
- **Funcionalidad**: 
  - Registro de proveedores con nombre
  - Configuración opcional de API (API Key, Endpoint)
- **Acceso**: Requiere autenticación

#### `sincronizar_productos.php`
- **Descripción**: Sincronización de productos desde API de proveedores
- **Funcionalidad**: 
  - Conecta con APIs de proveedores configurados
  - Actualiza precios y stock de productos existentes
  - Crea nuevos productos si no existen
  - Registra sincronizaciones en la base de datos
- **Acceso**: Requiere autenticación

#### `api_proveedor.php`
- **Descripción**: Clase PHP para interactuar con APIs de proveedores
- **Funcionalidad**: 
  - Clase `ApiProveedor` para manejar conexiones API
  - Métodos para obtener productos y realizar pedidos
  - Normalización de datos de diferentes proveedores
- **Acceso**: Clase interna (no es una página web)

---

### 📊 Reportes (`src/pages/reportes/`)

#### `reporte_ventas.php`
- **Descripción**: Generación de reportes de compras y ventas
- **Funcionalidad**: 
  - Resumen de compras por período
  - Compras por cliente
  - Stock de inventario
  - Filtros por fecha, cliente, color
  - Exportación de datos
- **Acceso**: Requiere autenticación

---

### 💱 Tasa de Cambio (`src/pages/tasa/`)

#### `gestion_tasa.php`
- **Descripción**: Gestión de tasa de cambio diaria
- **Funcionalidad**: 
  - Establecer tasa de cambio del día (USD a VES)
  - Integración con API externa (ve.dolarapi.com) para obtener tasa oficial
  - Historial de tasas registradas
  - Funciona sin conexión a internet (permite entrada manual)
- **Acceso**: Requiere autenticación

---

## 🔧 Archivos de Soporte

### `src/includes/`
- **`config.php`**: Configuración de rutas base (BASE_URL, ASSETS_URL, PAGES_URL)
- **`conexion.php`**: Conexión a la base de datos usando PDO
- **`auth.php`**: Funciones de autenticación (`verificarAutenticacion()`, `esAdmin()`)
- **`header.php`**: Encabezado común con navegación
- **`footer.php`**: Pie de página común con scripts

### `src/assets/`
- **`css/`**: Estilos CSS del sistema
- **`js/`**: Scripts JavaScript
- **`images/`**: Imágenes y recursos visuales

---

## 🔐 Control de Acceso

Todas las páginas (excepto `login.php`) requieren autenticación mediante la función `verificarAutenticacion()` que verifica la sesión del usuario.

Los roles disponibles son:
- **admin**: Acceso completo al sistema
- **gerente**: Acceso a gestión y reportes
- **vendedor**: Acceso limitado a operaciones básicas

---

## 📝 Notas Importantes

1. **Eliminación de Marcas**: El sistema ya no utiliza el concepto de "marcas". Los productos están asociados directamente a proveedores.

2. **Precio Único**: El sistema utiliza un único campo `precio` en lugar de `precio_compra` y `precio_venta`.

3. **Cálculos en Tiempo Real**: Los cálculos de subtotales y totales en `registrar_compra.php` se realizan mediante AJAX cuando se selecciona un producto.

4. **Integración con APIs**: El sistema puede sincronizar productos desde APIs externas de proveedores configurados.

---

## 🚀 Flujo de Trabajo Típico

1. **Login** → Usuario se autentica
2. **Dashboard** → Ve resumen del sistema
3. **Registrar Compra** → Selecciona cliente y productos, calcula totales
4. **Historial** → Revisa compras anteriores
5. **Gestión de Productos** → Administra inventario
6. **Reportes** → Genera reportes de actividad

---

*Última actualización: Diciembre 2024*

