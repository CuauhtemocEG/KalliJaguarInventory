<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

function conexion() {
    try {
        $conexion = new PDO("mysql:host=localhost:3306;dbname=kallijag_inventory", "kallijag_admin", "uNtiL.horSe@5");
        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conexion;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        return null;
    }
}

// Ruta para obtener productos
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['producto_id'])) {
        // Obtener producto por ID
        $producto_id = $_GET['producto_id'];
        $conexion = conexion();
        $consulta = "SELECT * FROM Productos WHERE ProductoID = :producto_id";
        $stmt = $conexion->prepare($consulta);
        $stmt->bindParam(':producto_id', $producto_id, PDO::PARAM_INT);
        $stmt->execute();
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($producto) {
            echo json_encode($producto); // Devuelve el producto en formato JSON
        } else {
            echo json_encode(['error' => 'Producto no encontrado']);
        }
    } else {
        // Obtener todos los productos
        $conexion = conexion();
        $consulta = "SELECT * FROM Productos";
        $stmt = $conexion->query($consulta);
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($productos); // Devuelve todos los productos en formato JSON
    }
}

// Ruta para actualizar cantidad de un producto (incrementar o decrementar)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $conexion = conexion();

    $producto_id = $_POST['producto_id'];
    $cantidad = $_POST['cantidad'];
    $action = $_POST['action']; // 'increase' o 'decrease'

    // Verificar que el producto existe
    $consulta = "SELECT Cantidad FROM Productos WHERE ProductoID = :producto_id";
    $stmt = $conexion->prepare($consulta);
    $stmt->bindParam(':producto_id', $producto_id, PDO::PARAM_INT);
    $stmt->execute();
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($producto) {
        // Determinar nueva cantidad
        $nuevaCantidad = $producto['Cantidad'];

        if ($action == 'increase') {
            $nuevaCantidad += $cantidad;
        } elseif ($action == 'decrease') {
            $nuevaCantidad -= $cantidad;
        }

        // Asegurarse de que la cantidad no sea negativa
        if ($nuevaCantidad < 0) {
            echo json_encode(['error' => 'La cantidad no puede ser negativa']);
            exit;
        }

        // Actualizar la cantidad en la base de datos
        $consulta_update = "UPDATE Productos SET Cantidad = :nuevaCantidad WHERE ProductoID = :producto_id";
        $stmt_update = $conexion->prepare($consulta_update);
        $stmt_update->bindParam(':nuevaCantidad', $nuevaCantidad, PDO::PARAM_INT);
        $stmt_update->bindParam(':producto_id', $producto_id, PDO::PARAM_INT);
        $stmt_update->execute();

        echo json_encode(['success' => 'Cantidad actualizada correctamente']);
    } else {
        echo json_encode(['error' => 'Producto no encontrado']);
    }
}

?>
