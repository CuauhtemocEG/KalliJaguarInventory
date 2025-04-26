let urlAPI = "https://stagging.kallijaguar-inventory.com/js/productsRequested/";

$(document).ready(function () {
    actualizarPanelCarrito();

    $('#searchButton').click(function() {
        let query = $('#searchInput').val();
        fetchProducts(query);
    });

    $('#searchInput').keyup(function() {
        let query = $(this).val();
        fetchProducts(query);
    });

});

function fetchProducts(query) {
    $.ajax({
        url: urlAPI+'searchProducts.php',
        method: 'GET',
        data: { query: query },
        success: function(response) {
            $('#productList').html(response);
        },
        error: function() {
            Swal.fire('Error', 'Ocurrió un error en la búsqueda.', 'error');
        }
    });
}

$(document).on('submit', '.add-product-form', function(e) {
    e.preventDefault();

    let form = $(this);
    let formData = form.serialize();

    $.ajax({
        url: urlAPI+'addProductToSession.php',
        method: 'POST',
        data: formData,
        success: function(response) {
            let res = JSON.parse(response);
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
                    text: 'No se pudo agregar el producto.'
                });
            }
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Ocurrió un error de red.'
            });
        }
    });
});

$(document).on('click', '.btn-delete-item', function(e) {
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
                url: urlAPI+'deleteProductFromCart.php',
                method: 'POST',
                data: { id: id },
                success: function(response) {
                    let res = JSON.parse(response);
                    if (res.status === 'Success') {
                        Swal.fire(
                            'Eliminado',
                            'El producto fue eliminado del carrito.',
                            'Success'
                        );
                        actualizarPanelCarrito();
                    } else {
                        Swal.fire(
                            'Error',
                            'No se pudo eliminar el producto.',
                            'error'
                        );
                    }
                },
                error: function() {
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
        url: urlAPI +'renderCartPanel.php',
        method: 'GET',
        success: function (response) {
            $('#cartBody').html(response);
        },
        error: function () {
            console.error("No se pudo actualizar el carrito.");
        }
    });
}