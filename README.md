# Arte y Vision - Sitio informativo con modulos internos

## Stack
- PHP 8+
- MySQL/MariaDB
- HTML, CSS y JavaScript separados por responsabilidades

## Estructura
- `public/`: pagina informativa publica y formulario de donaciones
- `auth/`: login, proceso de autenticacion y logout
- `admin/`: modulos internos (contenido, donaciones y archivo privado)
- `api/`: endpoints para donaciones, contenido y archivo interno
- `config/`: conexion a base de datos
- `includes/`: utilidades compartidas (sesion, auth, csrf)
- `assets/`: css, js e imagenes (`uploads/archive` es privado)
- `sql/`: script de base de datos

## Instalacion rapida
1. Crear la base de datos ejecutando `sql/arteyvision_db.sql`.
2. Si la DB ya existia, ejecuta tambien `sql/foundation_files.sql`.
3. Verificar credenciales en `config/database.php`.
4. Entrar a `http://localhost/arteyvision/`.

## Credenciales iniciales
- Correo: `admin@arteyvision.org`
- Clave: `Admin1234`

## Produccion
- Sitio: https://arteyvision.org/
- Publicar siempre por FTP. Instrucciones y credenciales locales: `DEPLOY-FTP.md`
- Comando: `python _deploy_ftp.py upload`

## Notas
- Reemplaza la imagen principal colocando tu logo en `assets/img/logo-arteyvision.png`.
- El formulario de donaciones registra aportes en la tabla `donations`.
