# Guía de Deploy a GitHub - Comandos Listos

**Copia y pega estos comandos para subir tu proyecto a GitHub**

---

## Paso 1: Crear Repositorio en GitHub

```
1. Ve a https://github.com/new
2. Nombre: gestion-tickets
3. Descripción: Sistema de Gestión de Tickets
4. Privado o Público (según necesidad)
5. NO selecciones "Add a README.md"
6. Click "Create repository"
```

---

## Paso 2: Configuración Local

Copia y ejecuta EXACTAMENTE estos comandos en tu terminal:

### A. Inicializar Git (PRIMERA VEZ)

```bash
cd c:\xampp\htdocs\gestion-tickets

git init

git config user.name "Tu Nombre Completo"
git config user.email "tu@email.com"
```

### B. Crear .gitignore

```bash
# Windows PowerShell
cat > .gitignore << 'EOF'
app/config/database.php
storage/logs/*
assets/uploads/*
!assets/uploads/.gitkeep
.DS_Store
*.log
node_modules/
vendor/
.vscode/
.idea/
test-upload.php
test-file.txt
EOF
```

Si usas Git Bash en Windows:
```bash
echo 'app/config/database.php' > .gitignore
echo 'storage/logs/*' >> .gitignore
echo 'assets/uploads/*' >> .gitignore
echo '.DS_Store' >> .gitignore
```

### C. Hacer Commit Inicial

```bash
# Ver qué se va a subir
git status

# Agregar todos los archivos (excepto .gitignore)
git add .

# Verificar que database.php NO está en el listado
git status

# Hacer commit
git commit -m "Initial commit: Sistema de Gestión de Tickets"

# Verificar commit fue creado
git log --oneline
```

---

## Paso 3: Conectar con GitHub

**Reemplaza USER con tu usuario de GitHub:**

```bash
# Agregar repositorio remoto
git remote add origin https://github.com/ingenieroaxelgutierrez-art/GESTION-TICKETS.git

# Renombrar rama a 'main'
git branch -M main

# Enviar código a GitHub
git push -u origin main
```

---

## Paso 4: Verificación

```bash
# Ver que está conectado
git remote -v

# Debería mostrar:
origin  https://github.com/ingenieroaxelgutierrez-art/GESTION-TICKETS.git (fetch)
origin  https://github.com/ingenieroaxelgutierrez-art/GESTION-TICKETS.git (push)

# Ver el historial
git log --oneline
```

**Si todo funcionó:** ¡Abre https://github.com/USER/gestion-tickets y verás tu código!

---

## Comandos Diarios de Git

### Después de hacer cambios:

```bash
# Ver cambios
git status

# Agregar cambios
git add .

# Hacer commit con descripción
git commit -m "Descripción de los cambios"

# Enviar a GitHub
git push origin main
```

### En una sola línea:

```bash
git add . && git commit -m "Mi cambio" && git push origin main
```

### Descargar cambios (si trabajas desde otro lugar):

```bash
git pull origin main
```

### Ver historial:

```bash
git log --oneline -10
```

---

## Rama Nueva (para Features)

Si quieres crear una rama nueva para una feature:

```bash
# Crear y cambiar a nueva rama
git checkout -b nueva-feature

# Hacer cambios... git add, git commit

# Enviar la rama a GitHub
git push origin nueva-feature

# En GitHub: crear Pull Request
# Después mergear a main
```

---

## Deshacer Cambios

```bash
# Descartar cambios locales de un archivo
git checkout -- nombre-archivo.php

# Descartar todos los cambios locales
git reset --hard

# Deshacer el último commit (SIN perder cambios)
git reset --soft HEAD~1

# Deshacer el último commit (PERDIENDO cambios)
git reset --hard HEAD~1
```

---

## Clonar desde GitHub (para otros usuarios)

```bash
# En la carpeta donde quieras
git clone https://github.com/ingenieroaxelgutierrez-art/GESTION-TICKETS.git
cd gestion-tickets

# Configurar BD local (si es necesario)
cp app/config/database.example.php app/config/database.php
# Editar database.php con credenciales locales

# Importar BD
mysql -u root -p < sql/production.sql
```

---

## Cheat Sheet Rápido

```bash
# Configuración inicial
git init
git config user.name "Nombre"
git config user.email "email@example.com"

# Conectar con GitHub
git remote add origin https://github.com/USER/repo.git
git branch -M main

# Flujo básico
git add .
git commit -m "Mensaje"
git push origin main

# Verificar
git status
git log --oneline
git remote -v

# Descargar
git pull origin main

# Nueva rama
git checkout -b rama-nueva
git push origin rama-nueva
```

---

## Notas Importantes

 **NUNCA COMITTEAR:**
- `app/config/database.php` (credenciales)
- Archivos en `assets/uploads/` (datos sensibles)
- Archivos en `storage/logs/` (logs)

 **SIEMPRE COMMITEAR:**
- Código PHP/JavaScript
- Archivos de vista HTML
- SQL schema (pero no datos sensibles)
- Documentación

---

## Ayuda Rápida

```bash
# Ver ayuda de Git
git --help

# Ver cambios sin agregar
git diff

# Ver cambios ya agregados
git diff --cached

# Cambiar mensaje del último commit
git commit --amend

# Listar ramas
git branch -a
```

---

## Error Común: "Permission denied"

```bash
# Solución: Usar HTTPS en lugar de SSH
git remote set-url origin https://github.com/ingenieroaxelgutierrez-art/GESTION-TICKETS.git

# Verificar
git remote -v
```

---

## Error: "Already exists"

```bash
# Si la rama ya existe
git branch -D main  # Elimina rama local
git checkout -b main  # Crea de nuevo
```

---

**¡Listo para subir a GitHub!** 

Ejecuta los comandos en orden y tendrás tu proyecto en GitHub.

---

*Para más info: https://docs.github.com/en/get-started*

