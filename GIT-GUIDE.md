# 📤 Guía para Subir Código al Fork

## Verificar Estado Actual

```bash
# Ver el estado del repositorio
git status

# Ver qué archivos han cambiado
git status --short

# Ver commits locales que no se han subido
git log origin/main..HEAD --oneline
```

## Subir Cambios al Fork

### Paso 1: Agregar Archivos al Staging

```bash
# Agregar todos los archivos modificados y nuevos
git add .

# O agregar archivos específicos
git add archivo1.php archivo2.php
```

### Paso 2: Hacer Commit

```bash
# Commit con mensaje descriptivo
git commit -m "Descripción de los cambios realizados"

# Ejemplo:
git commit -m "feat: agregar configuración para Railway y mejoras de despliegue"
```

### Paso 3: Subir al Fork (origin)

```bash
# Subir a la rama main de tu fork
git push origin main

# Si estás en otra rama, por ejemplo clean-implementation:
git push origin clean-implementation
```

### Paso 4: Verificar que se Subió Correctamente

```bash
# Verificar el estado
git status

# Ver los últimos commits
git log --oneline -5
```

## Comandos Útiles

### Ver Remotes Configurados

```bash
git remote -v
```

Esto mostrará:

- `origin`: Tu fork (donde subes tus cambios)
- `upstream`: Repositorio original (si lo tienes configurado)

### Ver Diferencias

```bash
# Ver qué archivos cambiaron
git diff --name-only

# Ver cambios específicos en un archivo
git diff archivo.php
```

### Crear una Nueva Rama

Si quieres trabajar en una rama separada:

```bash
# Crear y cambiar a una nueva rama
git checkout -b nombre-de-rama

# Hacer cambios, commit, y push
git add .
git commit -m "Mensaje"
git push origin nombre-de-rama
```

### Sincronizar con el Repositorio Original (Opcional)

Si quieres mantener tu fork actualizado con el repositorio original:

```bash
# Traer cambios del repositorio original
git fetch upstream

# Fusionar cambios a tu rama main
git checkout main
git merge upstream/main

# Subir los cambios fusionados a tu fork
git push origin main
```

## Flujo Completo de Trabajo

```bash
# 1. Verificar estado
git status

# 2. Agregar cambios
git add .

# 3. Hacer commit
git commit -m "Descripción de cambios"

# 4. Subir al fork
git push origin main

# 5. Verificar
git status
```

## Solución de Problemas

### Error: "Updates were rejected"

Si ves este error, significa que hay cambios en el remoto que no tienes localmente:

```bash
# Traer cambios del remoto
git pull origin main

# Resolver conflictos si los hay, luego:
git push origin main
```

### Error: "Permission denied"

Verifica que tengas permisos de escritura en el repositorio y que tu SSH key esté configurada correctamente.

### Verificar que los Cambios se Subieron

Visita tu repositorio en GitHub:

```
https://github.com/dakeishyperez29-droid/Sistema-defensa-de-tesis
```

Los cambios deberían aparecer allí.
