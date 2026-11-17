# Despliegue a produccion por FTP

Siempre publicar Arte y Vision **por FTP** (no Git deploy / no Hostinger CLI salvo que el usuario diga otra cosa).

## Credenciales FTP

| Campo | Valor |
|-------|-------|
| Host | `ftp.arteyvision.org` |
| Usuario | `u694856656.arteyvision` |
| Clave | `Arteyvision2027` |
| Directorio panel | `/home/u694856656/domains/arteyvision.org/public_html` |
| Directorio FTP real | `/` (el chroot ya es `public_html`) |

## MySQL produccion

| Campo | Valor |
|-------|-------|
| Host (desde PHP en Hostinger) | `localhost` |
| Base de datos | `u694856656_arteyvision` |
| Usuario | `u694856656_arteyvision` |
| Clave | `Arteyvision2027*` |

Sitio live: https://arteyvision.org/

## Como subir

Desde `c:\xampp\htdocs\arteyvision`:

```bash
python _deploy_ftp.py list
python _deploy_ftp.py upload
```

El script:

1. Sube el codigo del proyecto
2. **No** sube `_deploy_ftp.py`, `DEPLOY-FTP.md`, ni `.cursor/`
3. **No** pisa `config/database.php` local; al final escribe la version de **produccion**
4. Restaura `.htaccess` de produccion (redirects `/arteyvision` y `/public` → `/`)

## Local vs produccion

- Local XAMPP: `config/database.php` con `127.0.0.1` / `arteyvision_db` / `root`
- Produccion: credenciales de la tabla MySQL arriba
- Rutas: `includes/paths.php` detecta `/arteyvision` en local y `''` en dominio

## Checklist post-deploy

- [ ] https://arteyvision.org/ carga
- [ ] https://arteyvision.org/auth/login.php funciona
- [ ] Dashboard y archivo interno `/admin/files.php` accesibles tras login
- [ ] Si hay SQL nuevo, ejecutarlo en la DB de Hostinger (phpMyAdmin o script temporal)

## Nota

Este archivo y `_deploy_ftp.py` son **solo locales**: no deben publicarse en el sitio.
