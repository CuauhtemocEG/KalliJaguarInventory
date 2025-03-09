<?php
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Elimina el producto del carrito
    unset($_SESSION['INV'][$id]);
    
    // Reindexar el array para evitar huecos
    $_SESSION['INV'] = array_values($_SESSION['INV']);
}

//header("Location: index.php?vista=solicitarInsumos");
echo "<script>window.setTimeout(function() { window.location = 'index.php?page=requestProducts' }, 100);</script>";
exit();
?>