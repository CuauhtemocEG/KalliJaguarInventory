# Mapeo de archivos: estructura anterior → nueva

Generado a partir del historial de git de esta reestructuración (`git diff -M`).

## Archivos movidos

| Anterior | Nuevo |
|---|---|
| `api/AuthController/changePassword.php` | `src/Api/AuthController/changePassword.php` |
| `api/AuthController/checkSession.php` | `src/Api/AuthController/checkSession.php` |
| `api/AuthController/login.php` | `src/Api/AuthController/login.php` |
| `api/AuthController/logout.php` | `src/Api/AuthController/logout.php` |
| `api/apiHelper.php` | `src/Helpers/apiHelper.php` |
| `api/comandaDetails/confirmRequest.php` | `src/Api/comandaDetails/confirmRequest.php` |
| `api/comandaDetails/getComandaDetails.php` | `src/Api/comandaDetails/getComandaDetails.php` |
| `api/generarCatalogoBarcodePDF.php` | `src/Api/generarCatalogoBarcodePDF.php` |
| `api/generarReportePDF.php` | `src/Api/generarReportePDF.php` |
| `api/generarReporteProductosPDF.php` | `src/Api/generarReporteProductosPDF.php` |
| `api/generarReporteProductosSolicitadosPDF.php` | `src/Api/generarReporteProductosSolicitadosPDF.php` |
| `api/generarStockBajoPDF.php` | `src/Api/generarStockBajoPDF.php` |
| `api/generarStockBajoTagPDF.php` | `src/Api/generarStockBajoTagPDF.php` |
| `api/generarStockPorTagV2PDF.php` | `src/Api/generarStockPorTagV2PDF.php` |
| `api/getTags.php` | `src/Api/getTags.php` |
| `api/maintenance-config/index.php` | `src/Api/maintenance-config/index.php` |
| `api/picking/completePicking.php` | `src/Api/picking/completePicking.php` |
| `api/picking/getOrderProducts.php` | `src/Api/picking/getOrderProducts.php` |
| `api/products/createProducts.php` | `src/Api/products/createProducts.php` |
| `api/recreateComanda/agregarProductoComanda.php` | `src/Api/recreateComanda/agregarProductoComanda.php` |
| `api/recreateComanda/aumentarCantidadProducto.php` | `src/Api/recreateComanda/aumentarCantidadProducto.php` |
| `api/recreateComanda/devolverProductoComanda.php` | `src/Api/recreateComanda/devolverProductoComanda.php` |
| `api/recreateComanda/eliminarProductoComanda.php` | `src/Api/recreateComanda/eliminarProductoComanda.php` |
| `api/recreateComanda/getProductosComanda.php` | `src/Api/recreateComanda/getProductosComanda.php` |
| `api/recreateComanda/regenerarComandaPDF.php` | `src/Api/recreateComanda/regenerarComandaPDF.php` |
| `api/requestInsumos/confirmRequest.php` | `src/Api/requestInsumos/confirmRequest.php` |
| `api/requestInsumos/deleteToCart.php` | `src/Api/requestInsumos/deleteToCart.php` |
| `api/requestInsumos/getCart.php` | `src/Api/requestInsumos/getCart.php` |
| `api/requestInsumos/saveToCart.php` | `src/Api/requestInsumos/saveToCart.php` |
| `api/requestInsumos/searchProducts.php` | `src/Api/requestInsumos/searchProducts.php` |
| `api/searchProducts.php` | `src/Api/searchProducts.php` |
| `api/sucursalController/createSucursal.php` | `src/Api/sucursalController/createSucursal.php` |
| `api/sucursalController/deleteSucursal.php` | `src/Api/sucursalController/deleteSucursal.php` |
| `api/sucursalController/getSucursalDetails.php` | `src/Api/sucursalController/getSucursalDetails.php` |
| `api/sucursalController/getSucursales.php` | `src/Api/sucursalController/getSucursales.php` |
| `api/sucursalController/updateSucursal.php` | `src/Api/sucursalController/updateSucursal.php` |
| `api/updateStock.php` | `src/Api/updateStock.php` |
| `api/uploadProductImage.php` | `src/Api/uploadProductImage.php` |
| `api/userController/getUserActivity.php` | `src/Api/userController/getUserActivity.php` |
| `api/userController/resetUserPassword.php` | `src/Api/userController/resetUserPassword.php` |
| `api/userController/sendUserNotification.php` | `src/Api/userController/sendUserNotification.php` |
| `api/userController/toggleUserStatus.php` | `src/Api/userController/toggleUserStatus.php` |
| `controllers/deleteCategory.php` | `src/Controllers/deleteCategory.php` |
| `controllers/deleteProduct.php` | `src/Controllers/deleteProduct.php` |
| `controllers/deleteProductImage.php` | `src/Controllers/deleteProductImage.php` |
| `controllers/deleteSucursal.php` | `src/Controllers/deleteSucursal.php` |
| `controllers/deleteUser.php` | `src/Controllers/deleteUser.php` |
| `controllers/iniciar_sesion.php` | `src/Controllers/iniciar_sesion.php` |
| `controllers/mainController.php` | `src/Helpers/legacy.php` |
| `controllers/saveCategory.php` | `src/Controllers/saveCategory.php` |
| `controllers/saveSucursal.php` | `src/Controllers/saveSucursal.php` |
| `controllers/saveUser.php` | `src/Controllers/saveUser.php` |
| `controllers/showCategoryController.php` | `src/Controllers/showCategoryController.php` |
| `controllers/showProductController.php` | `src/Controllers/showProductController.php` |
| `controllers/showSucursalController.php` | `src/Controllers/showSucursalController.php` |
| `controllers/showUserController.php` | `src/Controllers/showUserController.php` |
| `controllers/updateCategoryController.php` | `src/Controllers/updateCategoryController.php` |
| `controllers/updateProductController.php` | `src/Controllers/updateProductController.php` |
| `controllers/updateProductImageController.php` | `src/Controllers/updateProductImageController.php` |
| `controllers/updateSucursalController.php` | `src/Controllers/updateSucursalController.php` |
| `controllers/updateUserController.php` | `src/Controllers/updateUserController.php` |
| `css/sb-admin-2.min.css` | `public/assets/css/sb-admin-2.min.css` |
| `css/style.css` | `public/assets/css/style.css` |
| `documents/.env` | `public/uploads/.gitkeep` |
| `fonts/arial.ttf` | `storage/fonts/arial.ttf` |
| `helpers/maintenanceHelper.php` | `src/Helpers/maintenanceHelper.php` |
| `helpers/responseHelper.php` | `src/Helpers/responseHelper.php` |
| `img/404.png` | `public/assets/img/404.png` |
| `img/Kalli-Amarillo.png` | `public/assets/img/Kalli-Amarillo.png` |
| `img/Logo-Negro.png` | `public/assets/img/Logo-Negro.png` |
| `img/icons/icon-128x128.png` | `public/assets/img/icons/icon-128x128.png` |
| `img/icons/icon-144x144.png` | `public/assets/img/icons/icon-144x144.png` |
| `img/icons/icon-152x152.png` | `public/assets/img/icons/icon-152x152.png` |
| `img/icons/icon-192x192.png` | `public/assets/img/icons/icon-192x192.png` |
| `img/icons/icon-384x384.png` | `public/assets/img/icons/icon-384x384.png` |
| `img/icons/icon-512x512.png` | `public/assets/img/icons/icon-512x512.png` |
| `img/icons/icon-72x72.png` | `public/assets/img/icons/icon-72x72.png` |
| `img/icons/icon-96x96.png` | `public/assets/img/icons/icon-96x96.png` |
| `img/logo.png` | `public/assets/img/logo.png` |
| `img/producto.png` | `public/assets/img/producto.png` |
| `img/undraw_profile.svg` | `public/assets/img/undraw_profile.svg` |
| `includes/alertError.php` | `views/partials/alertError.php` |
| `includes/baseSearch.php` | `views/partials/baseSearch.php` |
| `includes/navbar-logistica-tailwind.php` | `views/partials/navbar-logistica-tailwind.php` |
| `includes/navbar-supervisor-tailwind.php` | `views/partials/navbar-supervisor-tailwind.php` |
| `includes/navbar-tailwind-bottom.php` | `views/partials/navbar-tailwind-bottom.php` |
| `includes/navbar-tailwind.php` | `views/partials/navbar-tailwind.php` |
| `includes/script.php` | `views/partials/script.php` |
| `index.php` | `views/layouts/main.php` |
| `js/ajax.js` | `public/assets/js/ajax.js` |
| `js/functions.js` | `public/assets/js/functions.js` |
| `js/productsRequested/searchProducts.php` | `src/Api/productsRequested/searchProducts.php` |
| `js/pwa.js` | `public/assets/js/pwa.js` |
| `js/sb-admin-2.min.js` | `public/assets/js/sb-admin-2.min.js` |
| `manifest.json` | `public/manifest.json` |
| `pages/404.php` | `views/pages/404.php` |
| `pages/addCategory.php` | `views/pages/addCategory.php` |
| `pages/addProduct.php` | `views/pages/addProduct.php` |
| `pages/addSucursal.php` | `views/pages/addSucursal.php` |
| `pages/addUser.php` | `views/pages/addUser.php` |
| `pages/cancelRequest.php` | `views/pages/cancelRequest.php` |
| `pages/changeToDelivered.php` | `views/pages/changeToDelivered.php` |
| `pages/changeToTransit.php` | `views/pages/changeToTransit.php` |
| `pages/comandaDetails.php` | `views/pages/comandaDetails.php` |
| `pages/configMantenimiento.php` | `views/pages/configMantenimiento.php` |
| `pages/confirmRequest.php` | `views/pages/confirmRequest.php` |
| `pages/dashboardAvanzado.php` | `views/pages/dashboardAvanzado.php` |
| `pages/deleteProductList.php` | `views/pages/deleteProductList.php` |
| `pages/editarComanda.php` | `views/pages/editarComanda.php` |
| `pages/gestionCategorias.php` | `views/pages/gestionCategorias.php` |
| `pages/gestionProductos.php` | `views/pages/gestionProductos.php` |
| `pages/gestionTags.php` | `views/pages/gestionTags.php` |
| `pages/home.php` | `views/pages/home.php` |
| `pages/login.php` | `views/pages/login.php` |
| `pages/logout.php` | `views/pages/logout.php` |
| `pages/logsStock.php` | `views/pages/logsStock.php` |
| `pages/offline.html` | `public/offline.html` |
| `pages/pickingOrders.php` | `views/pages/pickingOrders.php` |
| `pages/productsByCategory.php` | `views/pages/productsByCategory.php` |
| `pages/pwaStatus.php` | `views/pages/pwaStatus.php` |
| `pages/reportOrders.php` | `views/pages/reportOrders.php` |
| `pages/requestProducts.php` | `views/pages/requestProducts.php` |
| `pages/scanProducts.php` | `views/pages/scanProducts.php` |
| `pages/searchCategory.php` | `views/pages/searchCategory.php` |
| `pages/searchProduct.php` | `views/pages/searchProduct.php` |
| `pages/searchUser.php` | `views/pages/searchUser.php` |
| `pages/showAllRequest.php` | `views/pages/showAllRequest.php` |
| `pages/showCategory.php` | `views/pages/showCategory.php` |
| `pages/showPDF.php` | `views/pages/showPDF.php` |
| `pages/showProduct.php` | `views/pages/showProduct.php` |
| `pages/showRequest.php` | `views/pages/showRequest.php` |
| `pages/showSucursal.php` | `views/pages/showSucursal.php` |
| `pages/showUser.php` | `views/pages/showUser.php` |
| `pages/sucursalManagement.php` | `views/pages/sucursalManagement.php` |
| `pages/updateCategory.php` | `views/pages/updateCategory.php` |
| `pages/updateProduct.php` | `views/pages/updateProduct.php` |
| `pages/updateProductImage.php` | `views/pages/updateProductImage.php` |
| `pages/updateStockProduct.php` | `views/pages/updateStockProduct.php` |
| `pages/updateSucursal.php` | `views/pages/updateSucursal.php` |
| `pages/updateUser.php` | `views/pages/updateUser.php` |
| `pages/userActivity.php` | `views/pages/userActivity.php` |
| `pages/userDetails.php` | `views/pages/userDetails.php` |
| `pages/userManagement.php` | `views/pages/userManagement.php` |
| `sw.js` | `public/sw.js` |
| `v2/categorias/index.php` | `src/Api/v2/categorias/index.php` |
| `v2/productos/generateBarcode.php` | `src/Api/v2/productos/generateBarcode.php` |
| `v2/productos/index.php` | `src/Api/v2/productos/index.php` |
| `v2/tags/index.php` | `src/Api/v2/tags/index.php` |
| `vendor/bootstrap/js/bootstrap.bundle.min.js` | `public/assets/vendor/bootstrap/js/bootstrap.bundle.min.js` |
| `vendor/jquery-easing/jquery.easing.min.js` | `public/assets/vendor/jquery-easing/jquery.easing.min.js` |
| `vendor/jquery/jquery.min.js` | `public/assets/vendor/jquery/jquery.min.js` |

## Archivos eliminados (propios del proyecto)

Eliminados por estar muertos, duplicados, sin referencias, ser pruebas/depuración o exponer credenciales.

| Archivo | Justificación |
|---|---|
| `.DS_Store` | Archivo de sistema (macOS) |
| `api/comandaDetails/cancelRequest.php` | Endpoint sin referencias en el código o duplicado |
| `api/comandaDetails/getCategorias.php` | Endpoint sin referencias en el código o duplicado |
| `api/comandaDetails/sucursales.php` | Endpoint sin referencias en el código o duplicado |
| `api/loginHandler.php` | Duplicado de `AuthController/login.php` con salida de depuración |
| `api/picking/getPickingHistory.php` | Endpoint sin referencias en el código o duplicado |
| `api/picking/setupPickingSystem.php` | Endpoint sin referencias en el código o duplicado |
| `api/requestInsumos/myRequest.php` | Endpoint sin referencias en el código o duplicado |
| `api/userController/updateUserProfile.php` | Endpoint sin referencias en el código o duplicado |
| `config.php` | Credenciales hardcodeadas → reemplazado por `.env` + `config/*.php` |
| `controllers/saveProduct.php` | Controlador sin referencias (duplicado de otra ruta) |
| `controllers/showProductsListController.php` | Controlador sin referencias (duplicado de otra ruta) |
| `css/sb-admin-2.css` | Recurso sin referencias (duplicado de la versión .min o librería sin uso) |
| `fonts/ARIBL0.ttf` | Sin referencias en el código |
| `fonts/ArialTh.ttf` | Sin referencias en el código |
| `fonts/Arialn.ttf` | Sin referencias en el código |
| `fonts/GEO_AI__.TTF` | Sin referencias en el código |
| `fonts/G_ari_bd.TTF` | Sin referencias en el código |
| `fonts/G_ari_i.TTF` | Sin referencias en el código |
| `img/Login.jpg` | Imagen sin referencias o duplicada (mismo md5) |
| `img/producto/Platos_Chicos_20.jpg` | Imagen sin referencias o duplicada (mismo md5) |
| `img/producto/producto.png` | Imagen sin referencias o duplicada (mismo md5) |
| `includes/btn_back.php` | Parcial/navbar sin referencias o duplicado (reemplazado por `views/layouts` y `views/partials`) |
| `includes/footer.php` | Parcial/navbar sin referencias o duplicado (reemplazado por `views/layouts` y `views/partials`) |
| `includes/header.php` | Parcial/navbar sin referencias o duplicado (reemplazado por `views/layouts` y `views/partials`) |
| `includes/navbarLog.php` | Parcial/navbar sin referencias o duplicado (reemplazado por `views/layouts` y `views/partials`) |
| `includes/navbarNewBottom.php` | Parcial/navbar sin referencias o duplicado (reemplazado por `views/layouts` y `views/partials`) |
| `includes/navbarNewTop.php` | Parcial/navbar sin referencias o duplicado (reemplazado por `views/layouts` y `views/partials`) |
| `includes/navbarSup.php` | Parcial/navbar sin referencias o duplicado (reemplazado por `views/layouts` y `views/partials`) |
| `includes/session_start.php` | Parcial/navbar sin referencias o duplicado (reemplazado por `views/layouts` y `views/partials`) |
| `js/productsRequested/addProductToSession.php` | Endpoint duplicado/sin uso (existe versión en `api/requestInsumos` o `AuthController`) |
| `js/productsRequested/confirmRequest.php` | Endpoint duplicado/sin uso (existe versión en `api/requestInsumos` o `AuthController`) |
| `js/productsRequested/deleteProductFromCart.php` | Endpoint duplicado/sin uso (existe versión en `api/requestInsumos` o `AuthController`) |
| `js/productsRequested/login.php` | Endpoint duplicado/sin uso (existe versión en `api/requestInsumos` o `AuthController`) |
| `js/productsRequested/renderCartPanel.php` | Endpoint duplicado/sin uso (existe versión en `api/requestInsumos` o `AuthController`) |
| `js/productsRequested/searchProductsJson.php` | Endpoint duplicado/sin uso (existe versión en `api/requestInsumos` o `AuthController`) |
| `js/sb-admin-2.js` | Recurso sin referencias (duplicado de la versión .min o librería sin uso) |
| `js/smart-pwa.js` | Recurso sin referencias (duplicado de la versión .min o librería sin uso) |
| `productDetailsAPI.php` | API con CORS abierto y credenciales de BD embebidas, sin referencias |

## Librerías vendorizadas eliminadas (ahora vía Composer)

| Directorio | Archivos | Reemplazo |
|---|---|---|
| `PHPMailer/` | 71 | `phpmailer/phpmailer` (Composer) |
| `fpdf/` | 115 | `setasign/fpdf` (Composer) |
| `vendor/picqer/` y `vendor/composer/`, `vendor/autoload.php` | 76 | `picqer/php-barcode-generator` (Composer) |
| `vendor/{fontawesome-free,chart.js,datatables}` | 591 | Sin referencias locales (se usan CDN) |
| `vendor/bootstrap/{scss,css}` y versiones no minificadas | 104 | Se conservan solo los `.min.js` usados en `public/assets/vendor` |
