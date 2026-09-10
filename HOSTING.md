# Hosting Hostinger — puesta en marcha (5 min cuando tengas acceso)

## 1. Requisitos en hPanel
- Sitio web → Panel → PHP: versión **8.2+**, extensiones `gd` y `fileinfo` activas.
- `file_uploads=On`, `upload_max_filesize` y `post_max_size` ≥ 32M.
- SSL → forzar HTTPS. Luego descomenta el bloque HTTPS de `public/.htaccess` y redespliega.

## 2. Primera subida (una vez)
1. `npm run build:hosting` (genera `dist/` y elimina `api/config.php` de prueba automáticamente)
2. File Manager → `public_html/` → sube el **contenido** de `dist/` (no la carpeta `dist`).
3. En el servidor, crea `public_html/proyectos-data/` (755) si no existe.
   Sube SOLO la primera vez: `proyectos.json` (seed) + `.htaccess`.
   Después **nunca** sobrescribas `proyectos-data/` (ahí viven los datos del cliente).
4. Crea la pass real: en tu PC `php -r "echo password_hash('LA-CLAVE-DEL-CLIENTE', PASSWORD_ARGON2ID), PHP_EOL;"`
   y guárdala en el servidor como `domains/tudominio/config-fer.php`:
   ```php
   <?php
   define('ADMIN_PASS_HASH', '$argon2id$...');
   ```
   Alternativa si no ves la carpeta superior: `public_html/api/config.php` con el mismo contenido
   (ese archivo está denegado por HTTP en `api/.htaccess`, pero fuera de `public_html` es mejor).
5. Permisos: carpetas `755`, archivos `644`.

## 3. Comprobación
- Web OK, `/fotografia`, `/video`, `/arquitectura` muestran el seed.
- `tudominio.com/admin` pide **solo contraseña** → entra → crea 1 proyecto mixto
  (marca Foto+Video) → debe aparecer en AMBOS apartados, con galería + video en su ficha.
- Borra el proyecto de prueba desde el propio admin.

## 4. Deploys de diseño (tus cambios)
- `npm run build` → sube `dist/` **excluyendo `proyectos-data/`**.
- Con FTP: salta esa carpeta. Con hPanel: no la selecciones.

## 5. Desarrollo en local
- `npm run dev` → lee el seed de `public/proyectos-data/proyectos.json`.
- `npm run php:api` (otra terminal) → prueba el admin+PHP reales en `localhost:8001/admin`
  (pass de prueba: `admin123`, definida en `public/api/config.php`, no se sube al repo).
- `FTP_* node scripts/sync.mjs down` → trae datos reales para depurar.
- `... up` → SOLO para el seed inicial.

## 6. Reglas del modelo (no tocar sin avisar al cliente)
- `tags`: al menos `fotografia` o `video`. `arquitectura` sola es inválida.
- Foto → galería obligatoria. Video → URL YouTube/Vimeo obligatoria.
- Portada siempre obligatoria. Max 8MB/foto, 20 fotos/proyecto (config en `api/upload.php`).
- Backup automático: `proyectos-YYYY-MM-DD.json.bak` junto al JSON.
