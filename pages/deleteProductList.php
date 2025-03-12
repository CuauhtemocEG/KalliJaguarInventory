<?php
if (isset($_GET['page']) && $_GET['page'] == 'deleteProductList' && isset($_GET['id'])) {
    $idProducto = $_GET['id'];
    unset($_SESSION['INV'][$idProducto]);  // Elimina el producto del carrito
    // Redirecciona a la misma página para actualizar el carrito
    header("Location: index.php?page=requestProducts&category_id=" . $categoria_id);
    exit();
}