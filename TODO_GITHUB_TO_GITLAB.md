# Plan: Clonar proyecto de GitHub a GitLab

## Información del Proyecto Actual
- **Repositorio GitHub**: https://github.com/amyjubi18/intranet-v1.2.git
- **Directorio actual**: /var/www/html/intranet-v1.2
- **Remote actual**: origin → https://github.com/amyjubi18/intranet-v1.2.git

## Pasos para Migrar a GitLab

### 1. Preparación en GitLab
- [ ] Crear un nuevo proyecto vacío en GitLab
- [ ] Obtener la URL del repositorio GitLab (formato: https://gitlab.com/usuario/proyecto.git)

### 2. Opción A: Crear nuevo repositorio GitLab con el código actual
- [ ] git remote add gitlab <URL_GITLAB>
- [ ] git push gitlab --all (para todas las ramas)
- [ ] git push gitlab --tags (para todas las etiquetas)

### 3. Opción B: Clonar directamente desde GitHub a GitLab
- [ ] En GitLab: New Project → Import project → From GitHub
- [ ] Seleccionar el repositorio: amyjubi18/intranet-v1.2
- [ ] Configurar visibilidad (privado/público)
- [ ] Iniciar importación

### 4. Sincronización futura (si es necesario)
- [ ] git remote set-url origin <NUEVA_URL_GITLAB>
- [ ] Verificar con: git remote -v

## Notas Importantes
- Verificar que el historial de commits se preserve correctamente
- Revisar si hay tokens OAuth de GitHub configurados en GitLab
- Considerar actualizar la documentación del proyecto con la nueva URL

## Comandos útiles
```bash
# Ver remotos actuales
git remote -v

# Ver historial de commits
git log --oneline

# Ver ramas
git branch -a
```

