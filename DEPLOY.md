# Guía de Despliegue en Render

Esta guía explica cómo desplegar el Sistema de Gestión de Compras en Render.

## 📋 Prerrequisitos

1. Cuenta en [Render](https://render.com)
2. Repositorio Git (GitHub, GitLab, o Bitbucket)
3. El código debe estar en el repositorio

## 🚀 Opción 1: Despliegue con Docker (Recomendado)

### Paso 1: Preparar el Repositorio

Asegúrate de que tu repositorio contenga:
- `Dockerfile`
- `docker-compose.yml` o `docker-compose.prod.yml`
- Carpeta `database/` con los scripts SQL
- Archivo `.env.example` (opcional, para documentación)

### Paso 2: Crear Base de Datos en Render

1. Ve a tu dashboard de Render
2. Click en "New +" → "PostgreSQL" (o MySQL si está disponible)
3. Configura:
   - **Name**: `sistema-compras-db`
   - **Database**: `sistema_compras_zapatos`
   - **User**: `app_user`
   - **Plan**: Free (o el que prefieras)
4. Guarda las credenciales de conexión

### Paso 3: Crear Servicio Web

1. Click en "New +" → "Web Service"
2. Conecta tu repositorio
3. Configura:
   - **Name**: `sistema-compras-web`
   - **Environment**: `Docker`
   - **Dockerfile Path**: `./Dockerfile`
   - **Docker Context**: `.`
   - **Plan**: Free (o el que prefieras)

### Paso 4: Configurar Variables de Entorno

En la sección "Environment" del servicio web, agrega:

```
DB_HOST=<host-de-tu-base-de-datos>
DB_NAME=sistema_compras_zapatos
DB_USER=<usuario-de-tu-base-de-datos>
DB_PASS=<contraseña-de-tu-base-de-datos>
PHP_ENV=production
```

**Nota**: Render proporciona estas variables automáticamente si usas la integración de base de datos.

### Paso 5: Ejecutar Scripts SQL

Después de crear la base de datos, necesitas ejecutar los scripts SQL:

1. Ve a tu base de datos en Render
2. Click en "Connect" → "External Connection"
3. Usa un cliente MySQL (como MySQL Workbench o DBeaver) para conectarte
4. Ejecuta los scripts en este orden:
   - `database/init.sql`
   - `database/01-schema.sql`
   - `database/02-data.sql`

**Alternativa**: Puedes usar el shell de Render para ejecutar los scripts:

```bash
# Conectarse al shell de la base de datos
mysql -h <host> -u <user> -p <database> < database/01-schema.sql
```

## 🐳 Opción 2: Usar docker-compose en Render

Render no soporta docker-compose directamente, pero puedes:

1. Usar el servicio web con Dockerfile (como en la Opción 1)
2. Usar una base de datos gestionada de Render
3. Configurar las variables de entorno manualmente

## 📝 Notas Importantes

### Variables de Entorno

En producción, las variables de entorno deben configurarse en Render:
- `DB_HOST`: Host de la base de datos
- `DB_NAME`: Nombre de la base de datos
- `DB_USER`: Usuario de la base de datos
- `DB_PASS`: Contraseña de la base de datos

### Scripts de Base de Datos

Los scripts SQL se ejecutan en este orden:
1. `init.sql` - Crea la base de datos
2. `01-schema.sql` - Crea las tablas
3. `02-data.sql` - Inserta datos iniciales

### Credenciales por Defecto

Después del despliegue, las credenciales de acceso son:
- **Usuario**: `admin`
- **Contraseña**: `admin123`

**⚠️ IMPORTANTE**: Cambia estas credenciales en producción.

## 🔧 Solución de Problemas

### La base de datos no se inicializa

1. Verifica que los scripts SQL estén en `database/`
2. Revisa los logs de la base de datos en Render
3. Ejecuta los scripts manualmente si es necesario

### Error de conexión a la base de datos

1. Verifica las variables de entorno
2. Asegúrate de que la base de datos esté en la misma región
3. Revisa los logs del servicio web

### El servicio no inicia

1. Revisa los logs en Render
2. Verifica que el Dockerfile esté correcto
3. Asegúrate de que todas las dependencias estén instaladas

## 📚 Recursos Adicionales

- [Documentación de Render](https://render.com/docs)
- [Docker en Render](https://render.com/docs/docker)
- [Bases de datos en Render](https://render.com/docs/databases)

