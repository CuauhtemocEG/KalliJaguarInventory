<?php

/**
 * Reglas de enrutamiento, autenticación y roles (Administrador, Logistica, Supervisor).
 * Las rutas de API son relativas a src/Api/ (URL: /api/<ruta>).
 */
return [
    'default_page' => 'login',

    // Páginas accesibles sin sesión
    'public_pages' => ['login', '404'],

    // Páginas restringidas por rol (el resto solo requiere sesión)
    'page_roles' => (static function (): array {
        $soloAdmin = ['Administrador'];
        $paginas = [
            'configMantenimiento', 'dashboardAvanzado', 'editarComanda', 'gestionCategorias',
            'gestionProductos', 'gestionTags', 'logsStock', 'pickingOrders', 'reportOrders',
            'sucursalManagement', 'userActivity', 'userManagement',
            'addUser', 'userDetails', 'searchUser', 'showUser',
            'addSucursal', 'showSucursal', 'updateSucursal',
            'addCategory', 'searchCategory', 'showCategory', 'updateCategory',
        ];
        return array_fill_keys($paginas, $soloAdmin) + [
            'showAllRequest' => ['Administrador', 'Logistica'],
            'changeToTransit' => ['Administrador', 'Logistica'],
            'changeToDelivered' => ['Administrador', 'Logistica'],
        ];
    })(),

    // Endpoints de API sin sesión
    'public_api' => [
        'AuthController/login.php',
        'AuthController/logout.php',
        'AuthController/checkSession.php',
    ],

    // Endpoints de API públicos solo para GET (consulta de modo mantenimiento)
    'public_api_get' => [
        'maintenance-config/index.php',
    ],

    // Restricciones por prefijo de ruta (y métodos opcionales)
    'api_roles' => [
        ['prefix' => 'userController/', 'roles' => ['Administrador']],
        ['prefix' => 'sucursalController/createSucursal', 'roles' => ['Administrador']],
        ['prefix' => 'sucursalController/updateSucursal', 'roles' => ['Administrador']],
        ['prefix' => 'sucursalController/deleteSucursal', 'roles' => ['Administrador']],
        ['prefix' => 'maintenance-config/', 'roles' => ['Administrador'], 'methods' => ['POST', 'PUT', 'DELETE', 'PATCH']],
        ['prefix' => 'v2/', 'roles' => ['Administrador'], 'methods' => ['POST', 'PUT', 'DELETE', 'PATCH']],
    ],
];
