# Sistema de Gestión de Compras - Defensa de Tesis

Sistema web para la gestión de compras de zapatos desarrollado en PHP.

## 🚀 Inicio Rápido con Docker

### Prerrequisitos

- Docker
- Docker Compose

### Instalación y Ejecución

1. **Clonar el repositorio** (si aplica)

```bash
git clone <url-del-repositorio>
cd Sistema-defensa-de-tesis
```

2. **Levantar los contenedores**

```bash
docker-compose up -d
```

3. **Acceder a la aplicación**

- **Aplicación web**: http://localhost:8082
- **phpMyAdmin**: http://localhost:8081
  - Usuario: `root`
  - Contraseña: `rootpassword`

4. **Inicializar la base de datos**
   La base de datos se inicializará automáticamente con los scripts SQL en la carpeta `database/`.

### Credenciales por defecto

**Credenciales de la aplicación:**

- Usuario: `admin`
- Contraseña: `admin123`

**Base de datos:**

- Nombre: `sistema_compras_zapatos`
- Usuario: `root`
- Contraseña: `rootpassword`

## 📁 Estructura del Proyecto

```
.
├── assets/          # Recursos estáticos (CSS, JS, imágenes)
├── includes/        # Archivos PHP compartidos (auth, conexion, header, footer)
├── src/            # Módulos de la aplicación
│   ├── clientes/
│   ├── compras/
│   ├── productos/
│   ├── proveedores/
│   ├── reportes/
│   └── tasa/
├── database/       # Scripts SQL de la base de datos
├── Dockerfile      # Configuración de la imagen PHP
└── docker-compose.yml  # Configuración de servicios Docker
```

## 🛠️ Desarrollo Local (sin Docker)

### Prerrequisitos

- PHP 8.2 o superior
- MySQL 8.0 o MariaDB 10.4+
- Apache con mod_rewrite habilitado

### Instalación

1. **Configurar la base de datos**

   - Crear una base de datos llamada `sistema_compras_zapatos`
   - Importar el script SQL desde `database/sistema_compras_zapatos (18).sql`

2. **Configurar la conexión**

   - Editar `src/includes/conexion.php` con tus credenciales de base de datos

3. **Configurar el servidor web**
   - Apuntar el DocumentRoot a la raíz del proyecto
   - Asegurarse de que mod_rewrite esté habilitado

## 🐳 Comandos Docker Útiles

```bash
# Levantar los contenedores
docker-compose up -d

# Ver logs
docker-compose logs -f

# Detener los contenedores
docker-compose down

# Reconstruir las imágenes
docker-compose build --no-cache

# Acceder al contenedor PHP
docker exec -it sistema_compras_web bash

# Acceder a MySQL
docker exec -it sistema_compras_db mysql -u root -prootpassword
```

## 📝 Notas

- El puerto **8082** está configurado para la aplicación web (cambió de 8080 porque estaba en uso)
- El puerto **3306** está expuesto para conexiones MySQL externas
- El puerto **8081** está configurado para phpMyAdmin
- Los datos de la base de datos se persisten en un volumen Docker
- Los archivos SQL en `database/` se ejecutan automáticamente al iniciar el contenedor de MySQL
- Al acceder a http://localhost:8082, se mostrará automáticamente la pantalla de login

## 🔧 Configuración

Las variables de entorno se pueden configurar en `docker-compose.yml`:

- `DB_HOST`: Host de la base de datos (por defecto: `db`)
- `DB_NAME`: Nombre de la base de datos
- `DB_USER`: Usuario de la base de datos
- `DB_PASS`: Contraseña de la base de datos

## 📄 Licencia

Este proyecto es parte de una defensa de tesis.
