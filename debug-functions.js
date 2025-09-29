// DEBUG VERSION de actualizarPanelCarrito
function actualizarPanelCarritoDebug() {
    console.log('🔍 [DEBUG] Iniciando actualizarPanelCarrito');
    console.log('🔍 [DEBUG] v2urlAPI:', typeof v2urlAPI !== 'undefined' ? v2urlAPI : 'NO DEFINIDA');
    
    const url = (typeof v2urlAPI !== 'undefined' ? v2urlAPI : 'api/requestInsumos/') + 'getCart.php';
    console.log('🔍 [DEBUG] URL completa:', url);
    
    $.ajax({
        url: url,
        method: 'GET',
        cache: false, // Forzar no cache
        headers: {
            'Cache-Control': 'no-cache, no-store, must-revalidate',
            'Pragma': 'no-cache',
            'Expires': '0'
        },
        beforeSend: function() {
            console.log('🔍 [DEBUG] Enviando petición AJAX...');
        },
        success: function (response) {
            console.log('🔍 [DEBUG] Respuesta recibida:', response);
            console.log('🔍 [DEBUG] Tipo de respuesta:', typeof response);
            
            try {
                let res = typeof response === 'string' ? JSON.parse(response) : response;
                console.log('🔍 [DEBUG] JSON parseado:', res);
                
                if (res.status === 'success') {
                    console.log('🔍 [DEBUG] Carrito encontrado:', res.cart);
                    let cartHTML = generarHTMLCarrito(res.cart);
                    $('#cartBody').html(cartHTML);
                    console.log('🔍 [DEBUG] HTML del carrito actualizado');
                } else {
                    console.log('🔍 [DEBUG] Error en respuesta:', res.message);
                    $('#cartBody').html('<p class="text-center text-muted">Error al cargar el carrito: ' + (res.message || 'Unknown') + '</p>');
                }
            } catch (e) {
                console.error("🔍 [DEBUG] Error parsing cart response:", e);
                console.log("🔍 [DEBUG] Raw response:", response);
                $('#cartBody').html('<p class="text-center text-muted">Error parseando respuesta del carrito</p>');
            }
        },
        error: function (xhr, status, error) {
            console.error("🔍 [DEBUG] Error AJAX:");
            console.error("🔍 [DEBUG] - xhr:", xhr);
            console.error("🔍 [DEBUG] - status:", status);
            console.error("🔍 [DEBUG] - error:", error);
            console.error("🔍 [DEBUG] - responseText:", xhr.responseText);
            $('#cartBody').html('<p class="text-center text-muted">Error de red: ' + error + '</p>');
        },
        complete: function() {
            console.log('🔍 [DEBUG] Petición AJAX completada');
        }
    });
}

// DEBUG VERSION de fetchProducts
function fetchProductsDebug(query) {
    console.log('🔍 [DEBUG] Iniciando fetchProducts con query:', query);
    console.log('🔍 [DEBUG] urlAPI:', typeof urlAPI !== 'undefined' ? urlAPI : 'NO DEFINIDA');
    
    const url = (typeof urlAPI !== 'undefined' ? urlAPI : 'js/productsRequested/') + 'searchProducts.php';
    console.log('🔍 [DEBUG] URL completa:', url);
    
    $.ajax({
        url: url,
        method: 'GET',
        data: { query: query },
        cache: false,
        headers: {
            'Cache-Control': 'no-cache, no-store, must-revalidate',
            'Pragma': 'no-cache',
            'Expires': '0'
        },
        beforeSend: function() {
            console.log('🔍 [DEBUG] Enviando petición de productos...');
        },
        success: function (response) {
            console.log('🔍 [DEBUG] Productos recibidos:', response.length, 'caracteres');
            console.log('🔍 [DEBUG] Contiene product-card:', response.includes('product-card'));
            
            $('#productList').html(response);
            if (typeof window.initializeProducts === 'function') {
                setTimeout(function() {
                    window.initializeProducts();
                }, 100);
            }
            console.log('🔍 [DEBUG] Lista de productos actualizada');
        },
        error: function (xhr, status, error) {
            console.error('🔍 [DEBUG] Error en fetchProducts:');
            console.error('🔍 [DEBUG] - xhr:', xhr);
            console.error('🔍 [DEBUG] - status:', status);
            console.error('🔍 [DEBUG] - error:', error);
            Swal.fire('Error', 'Error en búsqueda: ' + error, 'error');
        },
        complete: function() {
            console.log('🔍 [DEBUG] fetchProducts completado');
        }
    });
}

// Funciones para probar desde consola
window.testActualizarCarrito = actualizarPanelCarritoDebug;
window.testFetchProducts = fetchProductsDebug;

console.log('🔍 [DEBUG] Funciones debug cargadas. Usa:');
console.log('🔍 [DEBUG] - testActualizarCarrito() para probar el carrito');
console.log('🔍 [DEBUG] - testFetchProducts("") para probar productos');
