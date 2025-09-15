let baseURL = window.location.pathname.includes('index.php') 
    ? window.location.pathname.replace('index.php', '') 
    : window.location.pathname.endsWith('/') 
        ? window.location.pathname 
        : window.location.pathname + '/';

let urlAPI = baseURL + "js/productsRequested/";
let v2urlAPI = baseURL + "api/requestInsumos/";

$(document).ready(function () {
    actualizarPanelCarrito();

    $('#searchButton').click(function () {
        let query = $('#searchInput').val();
        fetchProducts(query);
    });

    $('#searchInput').keyup(function () {
        let query = $(this).val();
        fetchProducts(query);
    });

});

function fetchProducts(query) {
    $.ajax({
        url: urlAPI + 'searchProducts.php',
        method: 'GET',
        data: { query: query },
        success: function (response) {
            $('#productList').html(response);
            if (typeof window.initializeProducts === 'function') {
                setTimeout(function() {
                    window.initializeProducts();
                }, 100);
            }
        },
        error: function () {
            Swal.fire('Error', 'Ocurrió un error en la búsqueda.', 'error');
        }
    });
}

$(document).on('submit', '.add-product-form', function (e) {
    e.preventDefault();

    let form = $(this);
    let formData = new FormData(form[0]);
    
    let jsonData = {};
    for (let [key, value] of formData.entries()) {
        jsonData[key] = value;
    }

    $.ajax({
        url: v2urlAPI + 'saveToCart.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(jsonData),
        success: function (response) {
            try {
                let res = typeof response === 'string' ? JSON.parse(response) : response;
                
                if (res.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Producto agregado',
                        text: 'La cantidad fue actualizada en el carrito.',
                        timer: 1500,
                        showConfirmButton: false
                    });

                    actualizarPanelCarrito();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: res.message || 'No se pudo agregar el producto.'
                    });
                }
            } catch (e) {
                console.error('Error parsing JSON:', e);
                console.log('Raw response:', response);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Respuesta inválida del servidor.'
                });
            }
        },
        error: function () {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Ocurrió un error de red.'
            });
        }
    });
});

$(document).on('click', '.btn-delete-item', function (e) {
    e.preventDefault();

    let id = $(this).data('id');

    Swal.fire({
        title: '¿Eliminar producto?',
        text: "Esta acción no se puede deshacer",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#aaa',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: v2urlAPI + 'deleteToCart.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ idProduct: id }),
                success: function (response) {
                    try {
                        let res = typeof response === 'string' ? JSON.parse(response) : response;
                        
                        if (res.status === 'success') {
                            Swal.fire(
                                'Eliminado',
                                res.message || 'El producto fue eliminado del carrito.',
                                'success'
                            );
                            actualizarPanelCarrito();
                        } else {
                            Swal.fire(
                                'Error',
                                res.message || 'No se pudo eliminar el producto.',
                                'error'
                            );
                        }
                    } catch (e) {
                        console.error('Error parsing delete response:', e);
                        Swal.fire(
                            'Error',
                            'Respuesta inválida del servidor.',
                            'error'
                        );
                    }
                },
                error: function () {
                    Swal.fire(
                        'Error',
                        'Ocurrió un error al intentar eliminar el producto.',
                        'error'
                    );
                }
            });
        }
    });
});


function actualizarPanelCarrito() {
    $.ajax({
        url: v2urlAPI + 'getCart.php',
        method: 'GET',
        success: function (response) {
            try {
                let res = typeof response === 'string' ? JSON.parse(response) : response;
                
                if (res.status === 'success') {
                    let cartHTML = generarHTMLCarrito(res.cart);
                    $('#cartBody').html(cartHTML);
                } else {
                    $('#cartBody').html('<p class="text-center text-muted">Error al cargar el carrito</p>');
                }
            } catch (e) {
                console.error("Error parsing cart response:", e);
                $('#cartBody').html('<p class="text-center text-muted">Error al cargar el carrito</p>');
            }
        },
        error: function () {
            console.error("No se pudo actualizar el carrito.");
            $('#cartBody').html('<p class="text-center text-muted">No se pudo cargar el carrito</p>');
        }
    });
}

function generarHTMLCarrito(cartItems) {
    if (!cartItems || cartItems.length === 0) {
        return '<p>No hay productos en el carrito.</p>';
    }
    
    let html = '';
    let total = 0;
    let totalItems = 0;
    
    cartItems.forEach(function(item, index) {
        let unidades = '';
        let cantidadFormateada = '';
        
        if (item.Tipo === "Pesable") {
            if (parseFloat(item.Cantidad) >= 1.0) {
                unidades = 'Kg';
                cantidadFormateada = parseFloat(item.Cantidad).toFixed(3);
            } else {
                unidades = 'grs';
                cantidadFormateada = parseFloat(item.Cantidad).toFixed(3);
            }
        } else {
            unidades = 'Un';
            cantidadFormateada = parseInt(item.Cantidad).toString();
        }
        
        totalItems += parseFloat(item.Cantidad);
        let percentage = parseFloat(item.PrecioUnitario) * (1 + 0.16);
        let itemTotal = parseFloat(item.Cantidad) * percentage;
        total += itemTotal;
        
        html += `
<div class="col-md-12 mb-3">
    <div class="card mb-3 shadow-sm border-light">
        <div class="card-body d-flex flex-column position-relative">
            
            <span class="badge badge-danger badge-counter position-absolute" style="top: 0; right: 0;">
                ${cantidadFormateada} ${unidades}
            </span>
            
            <div class="text-md font-weight-bold text-dark mb-2">
                ${item.NombreProducto}
            </div>
            
            <div class="d-flex justify-content-between text-muted mb-3">
                <small>Precio unitario: $${parseFloat(item.PrecioUnitario).toFixed(2)}</small>
                <small>Total: $${(parseFloat(item.PrecioUnitario) * parseFloat(item.Cantidad)).toFixed(2)}</small>
            </div>
            
            <div class="d-flex justify-content-end">
                <button class="btn btn-danger btn-sm btn-delete-item" data-id="${item.ProductoID}">
                    <i class="fas fa-trash-alt"></i> Eliminar
                </button>
            </div>
        </div>
    </div>
</div>`;
    });
    
    html += `
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card shadow-lg p-4">
                <h6 class="card-title text-center mb-4">Resumen de la Solicitud</h6>
                
                <div class="d-flex justify-content-between mb-3">
                    <strong>Total de productos:</strong>
                    <span class="h5 text-primary">${totalItems}</span>
                </div>
                
                <div class="d-flex justify-content-between mb-3">
                    <strong>Total a pagar:</strong>
                    <span class="h5 text-success">$${total.toFixed(2)}</span>
                </div>
                <div class="d-flex justify-content-center">
                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#confirmModal">
                        <i class="fas fa-check-circle"></i> Enviar Solicitud
                    </button>
                </div>
            </div>
        </div>
    </div>`;
    
    return html;
}

document.addEventListener('DOMContentLoaded', function () {
    const confirmForm = document.getElementById('confirmForm');

    if (confirmForm) {
        confirmForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(confirmForm);

            const selectedSucursal = formData.get('idSucursal');

            if (!selectedSucursal) {
                Swal.fire({
                    title: 'Sucursal no seleccionada',
                    text: 'Por favor, elige una sucursal antes de confirmar.',
                    icon: 'warning'
                });
                return;
            }

            Swal.fire({
                title: 'Procesando...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            fetch(baseURL + 'api/comandaDetails/confirmRequest.php', {
                method: 'POST',
                body: formData,
                credentials: 'include'
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error HTTP: ' + response.status);
                    }
                    return response.text(); // Cambiar a text() para manejar texto mixto
                })
                .then(responseText => {
                    console.log("Respuesta cruda del servidor:", responseText);
                    
                    // Extraer el JSON del final de la respuesta (después del debug SMTP)
                    let jsonMatch = responseText.match(/\{.*\}$/);
                    let data;
                    
                    if (jsonMatch) {
                        try {
                            data = JSON.parse(jsonMatch[0]);
                        } catch (e) {
                            throw new Error('No se pudo parsear el JSON de la respuesta');
                        }
                    } else {
                        throw new Error('No se encontró JSON válido en la respuesta');
                    }
                    
                    console.log("JSON parseado:", data);

                    if (data.status === 'error') {
                        Swal.fire({
                            title: 'Error',
                            text: data.message || 'Algo salió mal al procesar la solicitud.',
                            icon: 'error'
                        });
                        return;
                    }

                    if (typeof $('#confirmModal').modal === 'function') {
                        $('#confirmModal').modal('hide');
                    }
                    
                    Swal.fire({
                        title: '¡Solicitud enviada!',
                        text: data.message || 'La comanda fue procesada correctamente.',
                        icon: 'success'
                    }).then(() => {
                        actualizarPanelCarrito();
                        window.location.href = 'index.php?page=showRequest';
                    });
                })
                .catch(error => {
                    console.error('Error al hacer fetch:', error);
                    Swal.fire({
                        title: 'Error de conexión',
                        text: 'No se pudo completar la solicitud. Revisa tu conexión o vuelve a intentar.',
                        icon: 'error'
                    });
                });
        });
    }
});