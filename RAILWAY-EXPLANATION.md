# 🔍 Explicación: Por qué solo 2 contenedores y Healthcheck Failed

## 📊 Por qué solo se despliegan 2 contenedores

### Cómo funciona Railway con Dockerfile

Cuando Railway usa un `Dockerfile` directamente:

1. **Solo despliega el servicio del Dockerfile**: Railway construye y despliega SOLO el contenedor definido en el Dockerfile (tu aplicación web PHP/Apache).

2. **NO despliega docker-compose.yml completo**: Railway NO lee automáticamente tu `docker-compose.yml` cuando usas Dockerfile. Son dos formas diferentes de desplegar:
   - **Dockerfile**: Un solo servicio
   - **docker-compose.yml**: Múltiples servicios (requiere configuración especial)

3. **Los 2 contenedores que ves son**:
   - ✅ **Servicio Web** (desde tu Dockerfile) - PHP/Apache
   - ✅ **Base de Datos MySQL** (creado como servicio separado en Railway) - MySQL

4. **phpMyAdmin NO se despliega** porque:
   - No está en el Dockerfile
   - Railway no lee docker-compose.yml cuando usas Dockerfile
   - phpMyAdmin es solo para desarrollo local, no es necesario en producción

### ¿Cómo desplegar todos los servicios?

**Opción 1: Usar docker-compose en Railway** (Recomendado)
- Railway puede usar docker-compose, pero necesitas configurarlo explícitamente
- Ve a Settings → Build & Deploy → Cambia a "Docker Compose"

**Opción 2: Crear servicios separados en Railway**
- Crear servicio web (Dockerfile)
- Crear servicio MySQL (Database)
- phpMyAdmin no es necesario en producción

## ❌ Por qué falla el Healthcheck

### Problema Principal: Puertos Dinámicos de Railway

Railway usa **puertos dinámicos**:
- Tu contenedor escucha en el puerto **80** internamente
- Railway asigna un puerto **externo dinámico** (ej: 3000, 5000, etc.)
- Railway mapea el puerto externo → puerto 80 del contenedor

**El problema**: Apache está configurado para escuchar SOLO en el puerto 80, pero Railway puede necesitar que escuche en el puerto que asigna dinámicamente.

### Otras causas posibles:

1. **Apache no inicia a tiempo**: El healthcheck se ejecuta antes de que Apache esté listo
2. **El archivo healthcheck.php no es accesible**: Puede haber un problema de rutas
3. **Error en PHP**: Algún error en el código PHP impide que responda
4. **Variables de entorno no configuradas**: Si el código intenta conectarse a la BD antes de tiempo

## 🔧 Solución Implementada

### 1. Configurar Apache para puertos dinámicos

He modificado el Dockerfile para que:
- Detecte la variable de entorno `PORT` que Railway proporciona
- Configure Apache para escuchar en ese puerto dinámicamente
- Si no hay `PORT`, use el puerto 80 por defecto

### 2. Mejorar el healthcheck

- El `healthcheck.php` ya está optimizado (no requiere BD)
- Aumentado el timeout a 300 segundos
- Configurado para usar `/healthcheck.php` en lugar de `/`

### 3. Manejo de errores mejorado

- El código ahora maneja errores de conexión a BD sin morir
- La aplicación puede iniciar aunque la BD no esté disponible inicialmente

## 📝 Resumen

**2 contenedores es CORRECTO**:
- ✅ Servicio Web (tu aplicación)
- ✅ Base de Datos MySQL
- ❌ phpMyAdmin (no necesario en producción)

**Healthcheck fallando**:
- ❌ Apache no está configurado para puertos dinámicos de Railway
- ✅ Solución: Script de inicio que detecta y usa el puerto de Railway

