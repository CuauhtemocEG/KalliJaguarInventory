# KalliJaguarInventory

Sistema de gestión de inventario (PHP 8.1+, MySQL/MariaDB). Gestiona productos, categorías, tags, sucursales,
comandas (solicitudes de insumos), picking, reportes PDF y usuarios con roles **Administrador**, **Logistica** y **Supervisor**.

## Requisitos

- PHP ≥ 8.1 con extensiones `pdo_mysql`, `mbstring`, `gd`, `curl`, `fileinfo`
- MySQL 8.0.16+ (necesario para las restricciones `CHECK`) o MariaDB 10.5+
- [Composer](https://getcomposer.org/)
- Apache (mod_rewrite) o Nginx apuntando a `public/`

## Instalación

```bash
git clone <repositorio> && cd KalliJaguarInventory
composer install --no-dev --optimize-autoloader
cp .env.example .env          # edite .env con sus credenciales (NUNCA se versiona)
```

1. Cree la base de datos y el usuario MySQL.
2. Complete `.env` (`DB_*`, `MAIL_*`, `APP_URL`, `ADMIN_*`…).
3. **Instalación nueva (esquema nuevo):**
   ```bash
   php database/migrate.php     # aplica database/migrations/*.sql (o: mysql BD < database/schema.sql)
   php database/seed.php        # roles, estados y el administrador inicial definido en .env
   ```
4. Configure el servidor web con **DocumentRoot = `public/`** (ver abajo). Para desarrollo:
   ```bash
   PHP_CLI_SERVER_WORKERS=4 php -S localhost:8000 -t public public/index.php
   ```
5. Asegúrese de que `storage/` y `public/uploads/` sean escribibles por el usuario del servidor web.

> **Importante – estado de la migración de código.** Las pantallas y APIs heredadas todavía consultan el
> **esquema anterior** (tablas `Usuarios`, `Productos`, …) y siguen usando `DB_NAME`. El esquema nuevo se entrega
> en `database/` junto con el script de migración de datos; portar las consultas al esquema nuevo es la fase siguiente.
> Mientras tanto, mantenga `DB_NAME` apuntando a la base anterior y use `DB_NAME_NEW` para la base nueva.

### Apache

```apache
<VirtualHost *:80>
    DocumentRoot /var/www/KalliJaguarInventory/public
    <Directory /var/www/KalliJaguarInventory/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### Nginx

```nginx
server {
    root /var/www/KalliJaguarInventory/public;
    index index.php;
    location / { try_files $uri /index.php?$query_string; }
    location ~ \.php$ { include fastcgi_params; fastcgi_param SCRIPT_FILENAME $document_root/index.php; fastcgi_pass unix:/run/php/php-fpm.sock; }
    location ~ /\.(?!well-known) { deny all; }
}
```

Si la aplicación se publica en una subcarpeta, defina `APP_URL` con esa ruta (p. ej. `https://dominio.com/inventario`).

## Configuración (`.env`)

Todas las credenciales y valores constantes viven en `.env` (plantilla en `.env.example`): nombre de la app, URLs,
zona horaria, SMTP, base de datos, sesión, límites de subida, rutas de uploads/logs, tasa de IVA (`TAX_RATE`), etc.
Se leen con `vlucas/phpdotenv` y se exponen mediante `App\Core\Config`, `env('CLAVE', 'defecto')` y `config('app.name')`.

## Estructura

```
public/            Único directorio expuesto: index.php (front controller), assets/, uploads/, sw.js, manifest.json
src/
  Core/            Config, Database (PDO), Session, Csrf, Auth, Router
  Middleware/      AuthMiddleware, RoleMiddleware, CsrfMiddleware
  Services/        Mailer (PHPMailer configurado desde .env)
  Helpers/         functions.php (env, config, asset, e…), legacy.php (utilidades heredadas)
  Controllers/     Acciones heredadas (guardar/actualizar/eliminar…)
  Api/             Endpoints JSON/PDF (URL: /api/<ruta>.php)
  Models/, Repositories/   Reservados para la fase de migración al nuevo esquema
config/            app, database, mail, paths, session, routes (roles y rutas públicas), bootstrap
views/             layouts/main.php, pages/ (pantallas), partials/ (navbars, scripts)
database/          migrations/, seeds/, schema.sql, migrate.php, seed.php, migrate_legacy.php
storage/           logs/, cache/, documents/ (PDF generados), fonts/
docs/              ER.md (diagrama Mermaid y mapeo de tablas), MAPEO_ARCHIVOS.md
tests/             run.php (pruebas ligeras), fixtures/ (esquema anterior de ejemplo)
```

Ver [`docs/MAPEO_ARCHIVOS.md`](docs/MAPEO_ARCHIVOS.md) para el mapeo archivo por archivo.

## Enrutamiento, autenticación y seguridad

- **Front controller** `public/index.php` + `App\Core\Router`:
  - `/?page=<pantalla>` → `views/pages/<pantalla>.php` dentro de `views/layouts/main.php`.
  - `/api/<ruta>.php` → `src/Api/<ruta>.php` (solo nombres seguros dentro de `src/Api`; sin traversal).
- **Roles** (`config/routes.php`): páginas y endpoints restringidos por rol; el resto requiere sesión.
  Los endpoints públicos se limitan a login/logout/checkSession y consulta de mantenimiento (GET).
- **CSRF**: toda petición no-GET exige token (`_csrf` o cabecera `X-CSRF-Token`); `assets/js/csrf.js` lo añade a
  formularios, `fetch` y XHR automáticamente.
- **Contraseñas** con `password_hash` / `password_verify`; bloqueo tras intentos fallidos.
- Cookies de sesión `HttpOnly` + `SameSite=Lax` (+ `Secure` en HTTPS), cabeceras de seguridad, subidas validadas por MIME real
  y almacenadas en `public/uploads/products`; PDFs en `storage/documents` servidos por `/api/documents/download.php`.
- Acceso a datos con **PDO**; las consultas con parámetros del usuario usan sentencias preparadas.

## Base de datos (rediseño)

Esquema en `database/migrations/*.sql` (consolidado en `database/schema.sql`), diagrama en [`docs/ER.md`](docs/ER.md).

```bash
php database/migrate.php --status   # ver migraciones aplicadas/pendientes
php database/migrate.php            # aplicar pendientes
php database/seed.php               # catálogos + admin inicial (.env)
```

### Migrar datos del esquema anterior

1. **Respalde** la base anterior (`mysqldump`).
2. Cree una base nueva vacía y defina en `.env`: `LEGACY_DB_NAME=<base anterior>` y `DB_NAME_NEW=<base nueva>`
   (mismo servidor; el usuario necesita lectura en la anterior y escritura en la nueva).
3. `php database/migrate.php` (crea el esquema nuevo).
4. `php database/migrate_legacy.php --dry-run` (revisa el reporte de conteos sin escribir) y luego sin `--dry-run`.
   Se ejecuta en una sola transacción; aborta si detecta UPC/usuarios/sucursales/tags duplicados.
5. Verifique el reporte (categorías duplicadas por nombre se fusionan; filas huérfanas se omiten).
6. Las contraseñas en texto plano heredadas se re-hashean automáticamente y los tokens de reset pasan a `password_resets` (SHA-256).

## Pruebas

```bash
php tests/run.php        # Config/env, escape, CSRF, router/roles, consistencia de rutas (sin BD)
```

## Credenciales expuestas

Las credenciales que estuvieron hardcodeadas en versiones anteriores (BD y SMTP) permanecen en el **historial de git**.
Deben **rotarse** (cambiar contraseña de BD y de la cuenta SMTP) aunque ya no estén en el código.
