# 🚂 Configuración para Railway

## Problema: Railway usa Nixpacks en lugar de Docker

Si Railway está intentando usar Nixpacks (Railpack) en lugar de Docker, sigue estos pasos:

## Solución 1: Configurar Manualmente en Railway

### Paso 1: Crear el Servicio Web

1. En Railway, crea un nuevo proyecto
2. Click en **"New"** → **"GitHub Repo"** (o el servicio Git que uses)
3. Selecciona tu repositorio

### Paso 2: Configurar el Servicio para Usar Docker

1. Railway creará un servicio automáticamente
2. Ve a la configuración del servicio (click en el servicio)
3. Ve a la pestaña **"Settings"**
4. En la sección **"Build & Deploy"**:
   - **Builder**: Selecciona **"Dockerfile"**
   - **Dockerfile Path**: `./Dockerfile`
   - **Docker Context**: `.` (punto)

### Paso 3: Agregar Base de Datos MySQL

1. En el mismo proyecto, click en **"New"** → **"Database"** → **"MySQL"**
2. Railway creará automáticamente un servicio MySQL
3. Las variables de entorno se configurarán automáticamente

### Paso 4: Conectar el Servicio Web con la Base de Datos

1. Ve al servicio web
2. Ve a la pestaña **"Variables"**
3. Railway debería haber agregado automáticamente:
   - `MYSQL_HOST`
   - `MYSQL_DATABASE`
   - `MYSQL_USER`
   - `MYSQL_PASSWORD`
   - `MYSQL_PORT`

4. Agrega estas variables adicionales para que coincidan con tu código:
   ```
   DB_HOST=${{MySQL.MYSQLHOST}}
   DB_NAME=${{MySQL.MYSQLDATABASE}}
   DB_USER=${{MySQL.MYSQLUSER}}
   DB_PASS=${{MySQL.MYSQLPASSWORD}}
   PHP_ENV=production
   ```

### Paso 5: Configurar el Inicio de la Base de Datos

Para que los scripts SQL se ejecuten automáticamente:

1. Ve al servicio MySQL
2. En **"Settings"** → **"Data"**
3. Agrega un script de inicialización o ejecuta los scripts manualmente después del primer despliegue

**Alternativa**: Ejecuta los scripts manualmente después del despliegue:
```bash
# Desde Railway Shell o tu máquina local
mysql -h $MYSQL_HOST -u $MYSQL_USER -p$MYSQL_PASSWORD $MYSQL_DATABASE < database/init.sql
mysql -h $MYSQL_HOST -u $MYSQL_USER -p$MYSQL_PASSWORD $MYSQL_DATABASE < database/01-schema.sql
mysql -h $MYSQL_HOST -u $MYSQL_USER -p$MYSQL_PASSWORD $MYSQL_DATABASE < database/02-data.sql
```

## Solución 2: Usar docker-compose.yml

Railway también soporta docker-compose directamente:

1. En Railway, crea un nuevo proyecto
2. Click en **"New"** → **"GitHub Repo"**
3. Selecciona tu repositorio
4. Railway detectará el `docker-compose.yml`
5. Configura las variables de entorno necesarias

## Solución 3: Eliminar Archivos que Confunden a Railway

Si Railway está detectando la carpeta `comprar_zapatos`:

1. Asegúrate de que esa carpeta no esté en el repositorio
2. Si existe localmente, elimínala:
   ```bash
   rm -rf comprar_zapatos
   ```
3. Agrega al `.gitignore`:
   ```
   comprar_zapatos/
   ```
4. Haz commit y push de los cambios

## Verificación

Después de configurar:

1. Railway debería mostrar en los logs: "Building Docker image..."
2. No debería mostrar: "Railpack could not determine..."
3. El servicio debería construirse correctamente

## Archivos de Configuración

El proyecto incluye estos archivos para ayudar a Railway:

- `railway.json` - Configuración JSON para Railway
- `railway.toml` - Configuración TOML para Railway
- `nixpacks.toml` - Configuración para forzar Docker (si Railway usa Nixpacks)

Si Railway sigue usando Nixpacks, estos archivos deberían forzarlo a usar Docker.

