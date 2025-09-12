<?php
session_start();
function conexion()
{
    $pdo = new PDO('mysql:host=localhost:3306
;dbname=kallijag_inventory', 'kallijag_admin', 'uNtiL.horSe@5');
    return $pdo;
}

$query = isset($_GET['query']) ? $_GET['query'] : '';

$campos = "Productos.ProductoID, Productos.Descripcion,Productos.UPC,Productos.Nombre as nombreProducto,Productos.PrecioUnitario,Productos.Cantidad,Productos.Tipo,Productos.image,Productos.CategoriaID as productCategory,Productos.UsuarioID,Categorias.CategoriaID,Categorias.Nombre as categoryName,Usuarios.UsuarioID,Usuarios.Nombre as userName";

$consulta_datos = "SELECT $campos FROM Productos INNER JOIN Categorias ON Productos.CategoriaID=Categorias.CategoriaID INNER JOIN Usuarios ON Productos.UsuarioID=Usuarios.UsuarioID WHERE Productos.UPC LIKE '%$query%' OR Productos.Nombre LIKE '%$query%' ORDER BY Productos.Nombre";

$conexion = conexion();
$datos = $conexion->query($consulta_datos);
$datos = $datos->fetchAll();

$tabla = '';
$tabla .= '<div class="products-grid-container">';
foreach ($datos as $row) {
    $result = "";
    $txtDisponibilidad = "";

    if ($row['Cantidad'] >= 1 && $row['Tipo'] == 'Unidad' ) {
        $txtDisponibilidad = '<span class="badge badge-success">Disponible</span>';
    } elseif ($row['Cantidad'] > 0 && $row['Tipo'] == 'Pesable') {
        $txtDisponibilidad = '<span class="badge badge-success">Disponible</span>';
    } else {
        $txtDisponibilidad = '<span class="badge badge-danger">No disponible</span>';
    }

    $tipoProducto = $row['Tipo'];
    $idProducto = $row['ProductoID'];

    $value = isset($_SESSION['INV'][$idProducto]) ? $_SESSION['INV'][$idProducto]['cantidad'] : 0;

    if ($tipoProducto === 'Pesable') {
        $cantidadVisible = ($value < 1 && $value > 0) ? number_format($value * 1000, 0) . ' gr' : number_format($value, 2) . ' Kg';
    } else {
        $cantidadVisible = (int)$value . ' Un';
    }

    if ($row['Tipo'] == "Pesable") {
        $result = "<i class='fas fa-balance-scale'></i> Kg";
        $unidades = number_format($row['Cantidad'], 2, '.', '');
        $tipoClass = 'text-success';
        $step = '0.25'; // Usar step consistente de 0.25 para pesables
    } else {
        $result = "Unidades";
        $unidades = (int) $row['Cantidad'];
        $result = "<i class='fas fa-cube'></i> Unidades";
        $tipoClass = 'text-warning';
        $step = '1'; // Step de 1 para unidades
    }

    $cantidadRequested = '';
    if ($row['Cantidad'] > 0) {
        $cantidadRequested = '
        <form class="add-product-form">
            <input type="hidden" name="idProduct" value="' . $row['ProductoID'] . '">
            <input type="hidden" name="precioProduct" value="' . $row['PrecioUnitario'] . '">
            <input type="hidden" name="nameProduct" value="' . $row['nombreProducto'] . '">
            <input type="hidden" name="typeProduct" value="' . $row['Tipo'] . '">
            <div class="quantity-section">
                <label class="quantity-label">
                    <i class="fas fa-calculator text-primary"></i>
                    <small class="font-weight-bold">Cantidad a solicitar</small>
                </label>
                <div class="quantity-controls">
                    <button type="button" class="btn-quantity btn-decrease" data-action="decrease" data-product="' . $row['ProductoID'] . '">
                        <i class="fas fa-minus"></i>
                    </button>
                    <div class="quantity-display">
                        <input class="quantity-input" type="text" id="cantidadVisible_' . $row['ProductoID'] . '" value="' . $cantidadVisible . '" readonly>
                        <input type="hidden" name="cantidadProduct" id="cantidad_' . $row['ProductoID'] . '" value="' . $value . '" step="' . $step . '">
                    </div>
                    <button type="button" class="btn-quantity btn-increase" data-action="increase" data-product="' . $row['ProductoID'] . '">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="add-to-cart-section">
                <button type="submit" class="btn-add-product btn-add-to-cart" name="agregar" disabled>
                    <i class="fas fa-cart-plus"></i>
                    <span>Agregar al Carrito</span>
                </button>
            </div>
        </form>';
    }

    $imagen = is_file("./img/producto/" . $row['image']) ? $row['image'] : 'producto.png';

    $tabla .= '
        <div class="product-card shadow-sm">
            <div class="product-image-container">
                <img class="product-image" src="./img/producto/' . $imagen . '" alt="' . htmlspecialchars($row['nombreProducto']) . '">
                <div class="availability-badge">
                    ' . $txtDisponibilidad . '
                </div>
            </div>
            
            <div class="product-info">
                <div class="product-header">
                    <h5 class="product-title">' . htmlspecialchars($row['nombreProducto']) . '</h5>
                    <span class="product-type ' . $tipoClass . '">
                        ' . $result . '
                    </span>
                </div>
                
                <div class="product-details">
                    <div class="detail-item">
                        <i class="fas fa-info-circle text-muted"></i>
                        <span class="detail-label">Descripción:</span>
                        <span class="detail-value">' . htmlspecialchars($row['Descripcion']) . '</span>
                    </div>
                    
                    <div class="product-meta">
                        <div class="meta-item">
                            <i class="fas fa-barcode text-muted"></i>
                            <span class="meta-label">UPC:</span>
                            <span class="meta-value">' . $row['UPC'] . '</span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-dollar-sign text-success"></i>
                            <span class="meta-label">Precio:</span>
                            <span class="meta-value font-weight-bold">$' . number_format($row['PrecioUnitario'], 2) . '</span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-boxes text-info"></i>
                            <span class="meta-label">Stock:</span>
                            <span class="meta-value">' . $unidades . ' ' . (($row['Tipo'] == "Pesable") ? "Kg" : "Un") . '</span>
                        </div>
                    </div>
                </div>
                
                <div class="product-actions">
                    ' . $cantidadRequested . '
                </div>
            </div>
        </div>';
}
$tabla .= '</div>';

echo $tabla;
?>

<script>
    // Prevenir zoom en dispositivos móviles al hacer doble tap
    let lastTouchEnd = 0;
    document.addEventListener('touchend', function (event) {
        const now = (new Date()).getTime();
        if (now - lastTouchEnd <= 300) {
            event.preventDefault();
        }
        lastTouchEnd = now;
    }, false);

    // Variables para optimizar clics rápidos y prevenir duplicación
    let clickTimeout = {};
    let isProcessing = {};
    let lastClickTime = {};

    // Funciones para manejar la cantidad (optimizadas para clics rápidos)
    function increaseQuantity(productId) {
        // Protección contra clics múltiples rápidos
        const now = Date.now();
        if (lastClickTime[productId] && now - lastClickTime[productId] < 50) {
            console.log('Click too fast, ignoring');
            return;
        }
        lastClickTime[productId] = now;
        
        // Evitar procesamiento múltiple simultáneo
        if (isProcessing[productId]) {
            console.log('Already processing, ignoring');
            return;
        }
        
        isProcessing[productId] = true;
        
        console.log('Increasing quantity for product:', productId);
        const input = document.getElementById('cantidad_' + productId);
        if (!input) {
            isProcessing[productId] = false;
            return;
        }
        
        let value = parseFloat(input.value) || 0;
        const step = parseFloat(input.getAttribute('step')) || 1;

        console.log('Current value:', value, 'Step:', step);
        
        value += step;
        input.value = value.toFixed(2);

        console.log('New value:', value);
        
        // Actualizar display inmediatamente
        updateVisibleImmediate(productId, value, step);
        toggleButtonImmediate(input, value);
        
        // Liberar el lock después de un pequeño delay
        setTimeout(() => {
            isProcessing[productId] = false;
        }, 10);
    }

    function decreaseQuantity(productId) {
        // Protección contra clics múltiples rápidos
        const now = Date.now();
        if (lastClickTime[productId] && now - lastClickTime[productId] < 50) {
            console.log('Click too fast, ignoring');
            return;
        }
        lastClickTime[productId] = now;
        
        // Evitar procesamiento múltiple simultáneo
        if (isProcessing[productId]) {
            console.log('Already processing, ignoring');
            return;
        }
        
        isProcessing[productId] = true;
        
        console.log('Decreasing quantity for product:', productId);
        const input = document.getElementById('cantidad_' + productId);
        if (!input) {
            isProcessing[productId] = false;
            return;
        }
        
        let value = parseFloat(input.value) || 0;
        const step = parseFloat(input.getAttribute('step')) || 1;

        console.log('Current value:', value, 'Step:', step);
        
        value -= step;
        if (value < 0) value = 0;

        input.value = value.toFixed(2);

        console.log('New value:', value);
        
        // Actualizar display inmediatamente
        updateVisibleImmediate(productId, value, step);
        toggleButtonImmediate(input, value);
        
        // Liberar el lock después de un pequeño delay
        setTimeout(() => {
            isProcessing[productId] = false;
        }, 10);
    }

    // Función optimizada para actualizar display sin búsquedas DOM repetidas
    function updateVisibleImmediate(productId, value, step) {
        const visible = document.getElementById('cantidadVisible_' + productId);
        
        // Para productos pesables (step 0.25)
        if (step === 0.25) {
            if (value < 1 && value > 0) {
                visible.value = Math.round(value * 1000) + ' gr';
            } else {
                visible.value = value.toFixed(2) + ' Kg';
            }
        } else {
            // Para productos por unidad (step 1)
            visible.value = parseInt(value) + ' Un';
        }
    }

    // Función optimizada para toggle del botón
    function toggleButtonImmediate(input, value) {
        const form = input.closest('form');
        const btn = form.querySelector('.btn-add-to-cart');

        if (!isNaN(value) && value > 0) {
            btn.removeAttribute('disabled');
            btn.classList.add('active');
        } else {
            btn.setAttribute('disabled', true);
            btn.classList.remove('active');
        }
    }

    // Funciones legacy para compatibilidad
    function updateVisible(productId) {
        const input = document.getElementById('cantidad_' + productId);
        const visible = document.getElementById('cantidadVisible_' + productId);
        const value = parseFloat(input.value);
        const step = parseFloat(input.getAttribute('step'));

        updateVisibleImmediate(productId, value, step);
    }

    function toggleButton(input) {
        const value = parseFloat(input.value);
        toggleButtonImmediate(input, value);
    }

    document.addEventListener('DOMContentLoaded', function() {
        initializeProducts();
    });

    // Función para inicializar productos (se llama cada vez que se cargan nuevos productos)
    function initializeProducts() {
        // Inicializar botones
        document.querySelectorAll('input[name="cantidadProduct"]').forEach(input => {
            toggleButton(input);
            input.addEventListener('input', () => toggleButton(input));
        });

        // Prevenir zoom adicional en inputs
        document.querySelectorAll('.quantity-input').forEach(input => {
            input.style.fontSize = '16px'; // Previene zoom automático en iOS
        });
    }

    // Event delegation optimizado para clics rápidos (UN SOLO EVENT LISTENER)
    document.addEventListener('click', function(e) {
        // Buscar el botón más cercano, ya sea que se haga clic en el botón o en el icono
        const button = e.target.closest('.btn-quantity');
        if (button) {
            e.preventDefault();
            e.stopPropagation(); // Evitar propagación y eventos duplicados
            
            const productId = button.dataset.product;
            const action = button.dataset.action;
            
            if (!productId || !action) return;
            
            console.log('Button clicked:', action, 'Product ID:', productId);
            
            // Procesamiento inmediato sin delays
            if (action === 'increase') {
                increaseQuantity(productId);
            } else if (action === 'decrease') {
                decreaseQuantity(productId);
            }
        }
    });

    // Llamar a inicialización cuando se carga contenido dinámico
    window.initializeProducts = initializeProducts;
</script>

<style>
/* Contenedor principal con CSS Grid */
.products-grid-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    padding: 20px 10px;
    max-width: 100%;
    margin: 0 auto;
}

/* Estilos para las cards de productos */
.product-card {
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e3e6f0;
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
    height: 100%; /* Asegura que todas las cards tengan la misma altura */
    min-height: 520px; /* Altura mínima consistente */
    overflow: hidden;
    position: relative;
}

.product-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
    border-color: #4e73df;
}

.product-image-container {
    position: relative;
    height: 180px; /* Altura fija para todas las imágenes */
    background: linear-gradient(135deg, #f8f9fc 0%, #eaecf4 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 15px;
    flex-shrink: 0; /* No se encoge */
}

.product-image {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    border-radius: 8px;
    transition: transform 0.3s ease;
}

.product-card:hover .product-image {
    transform: scale(1.05);
}

.availability-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 2;
}

.availability-badge .badge {
    font-size: 0.75rem;
    padding: 4px 8px;
    border-radius: 15px;
    font-weight: 600;
}

.product-info {
    padding: 20px;
    display: flex;
    flex-direction: column;
    flex-grow: 1; /* Ocupa el espacio disponible */
    min-height: 0; /* Permite que flexbox funcione correctamente */
}

.product-header {
    margin-bottom: 15px;
    flex-shrink: 0; /* No se encoge */
}

.product-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #2c3e50;
    margin: 0 0 8px 0;
    line-height: 1.3;
    height: 50px; /* Altura fija para consistencia */
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.product-type {
    font-size: 0.8rem;
    font-weight: 500;
    padding: 3px 8px;
    border-radius: 12px;
    background: rgba(52, 144, 220, 0.1);
    display: inline-block;
}

.product-details {
    flex-grow: 1; /* Ocupa el espacio disponible */
    margin-bottom: 15px;
    display: flex;
    flex-direction: column;
}

.detail-item {
    margin-bottom: 12px;
    padding: 8px 0;
    border-bottom: 1px solid #f1f3f5;
    flex-shrink: 0;
}

.detail-item:last-child {
    border-bottom: none;
}

.detail-label {
    font-weight: 500;
    color: #6c757d;
    font-size: 0.85rem;
    margin-left: 8px;
}

.detail-value {
    display: block;
    color: #495057;
    font-size: 0.9rem;
    margin-top: 3px;
    margin-left: 24px;
    line-height: 1.4;
    height: 42px; /* Altura fija para consistencia */
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.product-meta {
    display: grid;
    gap: 8px;
    margin-top: auto; /* Se empuja hacia abajo */
    flex-shrink: 0;
}

.meta-item {
    display: flex;
    align-items: center;
    font-size: 0.9rem;
}

.meta-item i {
    width: 16px;
    margin-right: 8px;
    text-align: center;
}

.meta-label {
    color: #6c757d;
    margin-right: 5px;
    font-weight: 500;
}

.meta-value {
    color: #495057;
}

.product-actions {
    border-top: 1px solid #f1f3f5;
    padding-top: 15px;
    margin-top: auto; /* Se empuja hacia abajo */
    flex-shrink: 0; /* No se encoge */
}

/* Estilos para los controles de cantidad */
.quantity-section {
    margin-bottom: 15px;
}

.quantity-label {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
    color: #495057;
}

.quantity-label i {
    margin-right: 8px;
    font-size: 0.9rem;
}

.quantity-controls {
    display: flex;
    align-items: center;
    background: #f8f9fc;
    border-radius: 8px;
    padding: 4px;
    border: 2px solid #e3e6f0;
    transition: border-color 0.3s ease;
}

.quantity-controls:focus-within {
    border-color: #4e73df;
}

.btn-quantity {
    background: #4e73df;
    color: white;
    border: none;
    width: 36px;
    height: 36px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    transition: all 0.15s ease; /* Transición más rápida */
    cursor: pointer;
    touch-action: manipulation;
    user-select: none;
    -webkit-tap-highlight-color: transparent;
    /* Optimizaciones para clics rápidos */
    will-change: transform, background-color;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
}

.btn-quantity:hover {
    background: #375a9d;
    transform: scale(1.02); /* Menor escala para transición más rápida */
}

.btn-quantity:active {
    transform: scale(0.98); /* Feedback visual inmediato */
    transition: all 0.05s ease; /* Transición ultra rápida en active */
}

.btn-decrease {
    background: #e74a3b;
}

.btn-decrease:hover {
    background: #c0392b;
}

/* Optimizaciones adicionales para clics rápidos */
.btn-quantity:focus {
    outline: none;
    box-shadow: 0 0 0 2px rgba(78, 115, 223, 0.3);
}

.btn-decrease:focus {
    box-shadow: 0 0 0 2px rgba(231, 74, 59, 0.3);
}

.quantity-display {
    flex: 1;
    margin: 0 8px;
}

.quantity-input {
    width: 100%;
    background: transparent;
    border: none;
    text-align: center;
    font-weight: 600;
    color: #2c3e50;
    font-size: 16px !important; /* Previene zoom en iOS */
    padding: 8px;
    outline: none;
    /* Optimizaciones para actualizaciones rápidas */
    transition: color 0.1s ease;
    will-change: contents;
}

.quantity-input:focus {
    color: #4e73df;
}

.add-to-cart-section {
    text-align: center;
}

.btn-add-product {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    touch-action: manipulation;
    user-select: none;
    min-height: 44px; /* Altura mínima consistente */
}

.btn-add-product:hover:not(:disabled) {
    background: linear-gradient(135deg, #e67e22 0%, #d35400 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(243, 156, 18, 0.3);
}

.btn-add-product:active:not(:disabled) {
    transform: translateY(0);
}

.btn-add-product:disabled {
    background: #bdc3c7;
    cursor: not-allowed;
    opacity: 0.6;
}

.btn-add-product.active {
    background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(46, 204, 113, 0.4); }
    70% { box-shadow: 0 0 0 10px rgba(46, 204, 113, 0); }
    100% { box-shadow: 0 0 0 0 rgba(46, 204, 113, 0); }
}

/* Responsive Design */
@media (max-width: 1200px) {
    .products-grid-container {
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 15px;
    }
}

@media (max-width: 992px) {
    .products-grid-container {
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 15px;
        padding: 15px 5px;
    }
    
    .product-card {
        min-height: 500px;
    }
}

@media (max-width: 768px) {
    .products-grid-container {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 12px;
        padding: 10px 5px;
    }
    
    .product-card {
        min-height: 480px;
    }
    
    .product-image-container {
        height: 150px;
    }
    
    .product-info {
        padding: 15px;
    }
    
    .product-title {
        font-size: 1rem;
        height: 42px;
    }
    
    .detail-value {
        height: 38px;
    }
    
    .btn-quantity {
        width: 32px;
        height: 32px;
        font-size: 0.8rem;
    }
    
    .quantity-input {
        font-size: 16px !important; /* Crítico para prevenir zoom */
        padding: 6px;
    }
    
    .btn-add-product {
        padding: 10px 15px;
        font-size: 0.85rem;
        min-height: 40px;
    }
}

@media (max-width: 480px) {
    .products-grid-container {
        grid-template-columns: 1fr;
        gap: 10px;
        padding: 10px;
    }
    
    .product-card {
        min-height: 460px;
        max-width: none;
    }
}

/* Prevención adicional de zoom en dispositivos móviles */
@media screen and (max-width: 768px) {
    input, select, textarea, button {
        font-size: 16px !important;
    }
    
    .btn-quantity, .quantity-input, .btn-add-product {
        -webkit-text-size-adjust: 100%;
        -webkit-touch-callout: none;
        -webkit-user-select: none;
        -khtml-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }
}

/* Mejoras adicionales para touch */
.btn-quantity, .btn-add-product {
    -webkit-tap-highlight-color: transparent;
    -webkit-focus-ring-color: transparent;
    outline: none;
}

/* Asegurar que no haya productos disponibles ocupen el mismo espacio */
.product-card .product-actions:empty::after {
    content: "";
    display: block;
    height: 120px; /* Espacio equivalente al de los controles */
}
</style>