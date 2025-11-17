# 🚀 Guía de Despliegue en Render

## ⚠️ Nota Importante sobre Render

Render **no soporta MySQL nativamente** en su plan gratuito. Tienes dos opciones:

### Opción A: Usar PostgreSQL (Requiere cambios en el código)

- Render ofrece PostgreSQL gratis
- Necesitarías adaptar las consultas SQL a PostgreSQL

### Opción B: Usar MySQL Externo (Recomendado)

- Usar un servicio MySQL externo (como [PlanetScale](https://planetscale.com), [Aiven](https://aiven.io), o [Railway](https://railway.app))
- Configurar las variables de entorno en Render apuntando a ese servicio

### Opción C: Usar Railway o Fly.io

- Estos servicios soportan MySQL nativamente
- Puedes usar docker-compose directamente

## 📋 Para Desplegar en Render con MySQL Externo

### 1. Preparar el Repositorio

Asegúrate de tener estos archivos:

- ✅ `Dockerfile`
- ✅ `database/01-schema.sql`
- ✅ `database/02-data.sql`
- ✅ `.env.example`

### 2. Crear Base de Datos MySQL Externa

Usa un servicio como:

- **PlanetScale**: https://planetscale.com (MySQL gratuito)
- **Aiven**: https://aiven.io (MySQL con plan gratuito)
- **Railway**: https://railway.app (MySQL con plan gratuito)

### 3. Crear Servicio Web en Render

1. Ve a Render Dashboard
2. Click "New +" → "Web Service"
3. Conecta tu repositorio
4. Configura:
   - **Name**: `sistema-compras-web`
   - **Environment**: `Docker`
   - **Dockerfile Path**: `./Dockerfile`
   - **Docker Context**: `.`
   - **Plan**: Free

### 4. Configurar Variables de Entorno

En el servicio web, agrega estas variables:

```
DB_HOST=<tu-host-mysql-externo>
DB_NAME=sistema_admin
DB_USER=<tu-usuario-mysql>
DB_PASS=<tu-contraseña-mysql>
PHP_ENV=production
```

### 5. Inicializar la Base de Datos

Después del despliegue, ejecuta los scripts SQL:

**Opción 1: Desde tu máquina local**

```bash
mysql -h <host> -u <user> -p <database> < database/init.sql
mysql -h <host> -u <user> -p <database> < database/01-schema.sql
mysql -h <host> -u <user> -p <database> < database/02-data.sql
```

**Opción 2: Desde Render Shell**

1. Ve a tu servicio web en Render
2. Click en "Shell"
3. Ejecuta:

```bash
mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME < database/init.sql
mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME < database/01-schema.sql
mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME < database/02-data.sql
```

## 🐳 Alternativa: Usar Railway (Recomendado para MySQL)

Railway soporta MySQL nativamente y docker-compose:

1. Ve a https://railway.app
2. Click "New Project" → "Deploy from GitHub repo"
3. Selecciona tu repositorio
4. Railway detectará automáticamente `docker-compose.yml`
5. Configura las variables de entorno
6. ¡Listo! Railway ejecutará los scripts SQL automáticamente

## 📝 Checklist de Despliegue

- [ ] Repositorio preparado con todos los archivos
- [ ] Base de datos MySQL creada (externa o en Railway)
- [ ] Variables de entorno configuradas
- [ ] Scripts SQL ejecutados
- [ ] Aplicación accesible
- [ ] Credenciales por defecto cambiadas

## 🔐 Seguridad

**IMPORTANTE**: Después del despliegue:

1. Cambia la contraseña del usuario `admin`
2. Usa variables de entorno para credenciales sensibles
3. Habilita HTTPS (Render lo hace automáticamente)
4. Revisa los logs regularmente

## 📚 Recursos

- [Documentación de Render](https://render.com/docs)
- [PlanetScale (MySQL gratuito)](https://planetscale.com)
- [Railway (soporta MySQL)](https://railway.app)
