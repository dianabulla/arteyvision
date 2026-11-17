---
name: fundacion-web
description: >-
  Experto en sitios web para fundaciones (PHP/MySQL/XAMPP) con login, panel
  admin, donaciones y contenido editable. Usar cuando se trabaje en Arte y
  Vision, páginas de fundación, auth/login, admin, donaciones, secciones
  públicas, APIs, o despliegue/producción/FTP del proyecto arteyvision.
---

# Experto: sitios web para fundaciones

Eres especialista en sitios institucionales de fundaciones: página pública clara, donaciones, login seguro y panel interno. En este repo el producto base es **Arte y Vision**.

## Stack obligatorio (no inventar otro)

- PHP 8+ con `declare(strict_types=1);`
- MySQL/MariaDB + PDO prepared statements
- HTML/CSS/JS vanilla (sin React/Laravel/WordPress salvo que el usuario lo pida)
- Sesiones PHP + CSRF en todos los POST
- Rutas absolutas bajo `/arteyvision/`

## Estructura del proyecto

```
public/     → sitio público + formulario donaciones
auth/       → login, login_process, logout
admin/      → dashboard y módulos internos
api/        → mutaciones JSON (donaciones, contenido)
config/     → database.php (PDO)
includes/   → session.php, auth.php, csrf.php
assets/     → css, js, img, uploads
sql/        → schema y seed
```

## Login y auth (siempre)

Flujo existente a respetar:

1. `auth/login.php` — form con `csrf_token`
2. `auth/login_process.php` — POST + CSRF + `password_verify` → sesión
3. `auth/logout.php` — limpia sesión/cookie
4. Guards: `require_login()` / `require_admin()` desde `includes/auth.php`

Sesión guarda: `user_id`, `full_name`, `email`, `role` (`admin` | `editor`).

Reglas al tocar auth:

- Nunca quitar CSRF ni `password_verify` / `password_hash`
- Mensajes de error genéricos (“Credenciales invalidas”) — no revelar si el email existe
- Páginas admin: `require_login()` o `require_admin()` al inicio
- APIs admin: mismos guards + CSRF + respuesta JSON `{ success, message }`
- Escapar salida con `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`

## Módulos típicos de una fundación

Priorizar en este orden (el repo ya tiene 1–3):

1. **Pública** — hero, quiénes somos, impacto, CTA
2. **Donaciones** — registro (tabla `donations`); pasarela solo si se pide
3. **Login + admin** — contenido editable, listado donaciones
4. **Archivo interno (privado)** — `admin/files.php` + `foundation_files`; no va al sitio público; servir solo vía `admin/file_view.php` con sesión
5. **Proyectos / programas** — si se pide ampliar
6. **Voluntariado / contacto** — si se pide
7. **Usuarios internos** — CRUD solo si se pide (roles ya existen)

## Base de datos

Tablas actuales: `users`, `site_sections`, `donations`, `foundation_files`.

Al ampliar schema:

- Agregar migraciones/SQL en `sql/`
- Mantener `utf8mb4`, InnoDB, prepared statements
- No hardcodear secretos nuevos en commits; `config/database.php` es local XAMPP

## UI / tono

- Institucional, humano, claro; español neutro
- Un CSS compartido (`assets/css/styles.css`) con variables existentes
- JS por contexto: `main.js` (público), `admin.js` (panel)
- Feedback accesible (`aria-live` donde ya se use)
- Responsive; no introducir frameworks CSS

## Flujo de trabajo al implementar

1. Revisar archivos existentes del área (no duplicar login/auth)
2. Extender patrones actuales (`includes`, `api`, guards)
3. Si hay POST nuevo → CSRF + validación + PDO
4. Si hay página admin → `require_login` / `require_admin`
5. Actualizar README solo si cambia instalación o credenciales de seed
6. Probar mentalmente: público sin sesión, editor vs admin, CSRF inválido

## Gaps conocidos (mejorar solo si el usuario lo pide)

- Rol `editor` sin pantallas propias
- Imágenes about/impact se guardan pero no se muestran en pública
- Donaciones sin pasarela ni cambio de status
- Sin CRUD de usuarios
- Sin `session_regenerate_id` post-login

## Despliegue a producción (siempre FTP)

Cuando el usuario pida subir, publicar o poner en producción:

1. Leer `DEPLOY-FTP.md` (credenciales y checklist)
2. Ejecutar `python _deploy_ftp.py upload` desde la raíz del proyecto
3. Verificar https://arteyvision.org/ y login
4. No usar Git deploy / Hostinger CLI salvo que lo pidan

Datos rápidos FTP: host `ftp.arteyvision.org`, user `u694856656.arteyvision`, remoto `/` (chroot = public_html).  
MySQL prod: `localhost` / `u694856656_arteyvision` / misma user / pass en `DEPLOY-FTP.md`.  
No subir a producción: `_deploy_ftp.py`, `DEPLOY-FTP.md`, `.cursor/`.

## Anti-patrones

- No reemplazar el stack por CMS/framework sin pedirlo
- No exponer credenciales seed en UI pública
- No crear APIs sin CSRF si mutan estado
- No mezclar lógica de mutación en vistas; mutaciones van en `api/`
- No romper paths `/arteyvision/...` sin migrar todo el proyecto
- No borrar `DEPLOY-FTP.md` ni `_deploy_ftp.py` sin pedirlo el usuario
