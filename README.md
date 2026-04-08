# Kalli Jaguar Inventory

Sistema de Gestión de Inventario para restaurantes y negocios. Desarrollado por Kalli Development Team.

## Requisitos

- PHP >= 7.4
- MySQL >= 5.7 / MariaDB >= 10.3
- Servidor web: Apache o Nginx con soporte PHP-FPM
- Extensiones PHP: `pdo_mysql`, `mbstring`, `gd`, `curl`

## Instalación

### 1. Clonar el repositorio

```bash
git clone https://github.com/CuauhtemocEG/KalliJaguarInventory.git
cd KalliJaguarInventory
```

### 2. Configurar variables de entorno

```bash
cp .env.example .env
```

Editar `.env` y completar los valores:

```env
APP_ENV=production
APP_DEBUG=false
DB_HOST=localhost:3306
DB_NAME=kallijag_inventory
DB_USER=kallijag_admin
DB_PASS=tu_contraseña_segura
```

> **⚠️ Importante:** El archivo `.env` contiene credenciales y **nunca** debe subirse al repositorio.

### 3. Instalar dependencias PHP (opcional, si tienes Composer)

```bash
composer install --no-dev --optimize-autoloader
```

### 4. Configurar la base de datos

Importar el esquema SQL desde `database/` (si existe) o solicitar el esquema al equipo de desarrollo.

### 5. Permisos de directorios

```bash
chmod 755 img/
chmod 755 img/producto/
chmod 755 logs/
```

## Estructura de Carpetas

```
/
├── api/                  Endpoints de la API REST
│   ├── AuthController/   Autenticación
│   ├── requestInsumos/   Gestión de solicitudes
│   ├── picking/          Sistema de picking
│   └── ...
├── controllers/          Lógica de negocio (CRUD)
├── pages/                Vistas PHP (presentación)
├── includes/             Partials reutilizables (navbar, session)
├── helpers/              Funciones auxiliares
├── js/                   Assets JavaScript
├── css/                  Assets CSS
├── img/                  Imágenes y recursos
├── config.php            Configuración global (lee de .env)
├── index.php             Entrypoint principal
└── .env.example          Plantilla de variables de entorno
```

## Roles del Sistema

| Rol | Descripción |
|---|---|
| `Administrador` | Acceso completo: usuarios, sucursales, catálogo, órdenes, reportes |
| `Supervisor` | Gestión de órdenes, solicitudes, reportes |
| `Logistica` | Solicitud de insumos y gestión de picking |

## Seguridad

- Las credenciales de base de datos se configuran únicamente a través de variables de entorno (`.env`)
- Todas las consultas SQL usan prepared statements con parámetros enlazados
- Las contraseñas se almacenan con `password_hash()` + bcrypt
- Las sesiones usan regeneración de ID en login y configuración segura de cookies

## Despliegue

Ver las instrucciones de despliegue en la documentación interna del equipo.

Para migraciones a VPS/Cloud con Nginx + PHP-FPM, consultar el análisis de arquitectura en la wiki del proyecto.

---

© 2025 Kalli Jaguar. Todos los derechos reservados.
