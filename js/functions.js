let urlAPI = 'https://www.kallijaguar-inventory.com/js/productsRequested/';

$(document).ready(function () {
    actualizarPanelCarrito();

    $('#searchButton').click(function () {
        let query = $('#searchInput').val();
        $.ajax({
            url: urlAPI + 'searchProducts.php',
            method: 'GET',
            data: { query: query },
            success: function (response) {
                $('#productList').html(response);
            },
            error: function () {
                Swal.fire('Error', 'Ocurrió un error en la búsqueda.', 'error');
            }
        });
    });

    $('#searchInput').on('input', function () {
        let query = $(this).val();
        $.ajax({
            url: urlAPI + 'searchProducts.php',
            method: 'GET',
            data: { query: query },
            success: function (response) {
                $('#productList').html(response);
            },
            error: function () {
                Swal.fire('Error', 'Ocurrió un error en la búsqueda.', 'error');
            }
        });
    });

});

function fetchProducts(query) {
    $.ajax({
        url: 'https://kallijaguar-inventory.com/js/productsRequested/searchProducts.php',
        method: 'GET',
        data: { query: query },
        success: function (response) {
            $('#productList').html(response);
        },
        error: function () {
            Swal.fire('Error', 'Ocurrió un error en la búsqueda.', 'error');
        }
    });
}

$(document).on('submit', '.add-product-form', function (e) {
    e.preventDefault();

    let form = $(this);
    let formData = form.serialize();

    $.ajax({
        url: urlAPI + 'addProductToSession.php',
        method: 'POST',
        data: formData,
        success: function (response) {
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
                url: urlAPI + 'deleteProductFromCart.php',
                method: 'POST',
                data: { id: id },
                success: function (response) {
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
        url: urlAPI + 'renderCartPanel.php',
        method: 'GET',
        success: function (response) {
            $('#cartBody').html(response);
        },
        error: function () {
            console.error("No se pudo actualizar el carrito.");
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const confirmForm = document.getElementById('confirmForm');

    confirmForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(confirmForm);
        for (let [key, value] of formData.entries()) {
            console.log(key + ': ' + value);
        }
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

        fetch('https://www.kallijaguar-inventory.com/js/productsRequested/confirmRequest.php', {
            method: 'POST',
            body: formData,
            credentials: 'include'
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error HTTP: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log("Respuesta del servidor:", data);

                if (data.status === 'error') {
                    Swal.fire({
                        title: 'Error',
                        text: data.message || 'Algo salió mal al procesar la solicitud.',
                        icon: 'error'
                    });
                    return;
                }

                $('#confirmModal').modal('hide');
                Swal.fire({
                    title: '¡Solicitud enviada!',
                    text: data.message || 'La comanda fue procesada correctamente.',
                    icon: 'success'
                }).then(() => {
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
});