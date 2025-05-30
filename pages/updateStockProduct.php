<?php
require './controllers/mainController.php';
$pdo = conexion();
header('Content-Type: ' . ($_SERVER['REQUEST_METHOD'] === 'POST' ? 'application/json' : 'text/html'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = limpiar_cadena($_POST['codigo'] ?? '');
    $nuevo_stock = intval($_POST['nuevo_stock'] ?? -1);

    if ($codigo && $nuevo_stock >= 0) {
        $stmt = $pdo->prepare("SELECT Cantidad FROM Productos WHERE UPC = :codigo");
        $stmt->execute([':codigo' => $codigo]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$producto) {
            echo json_encode(['status' => 'error', 'message' => 'Producto no encontrado.']);
            exit();
        }

        $stock_actual = $producto['Cantidad'];

        $upd = $pdo->prepare("UPDATE Productos SET Cantidad = :nuevo WHERE UPC = :codigo");
        if ($upd->execute([':nuevo' => $nuevo_stock, ':codigo' => $codigo])) {
            $log = $pdo->prepare("INSERT INTO Logs_stock (UPC, StockBefore, StockAfter) VALUES (:codigo, :anterior, :nuevo)");
            $log->execute([
                ':codigo' => $codigo,
                ':anterior' => $stock_actual,
                ':nuevo' => $nuevo_stock
            ]);
            echo json_encode(['status' => 'ok', 'message' => '¡Stock actualizado exitosamente!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al actualizar stock.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Datos inválidos.']);
    }
    exit();
}

// Solicitud GET: mostrar el formulario
$codigo = $_GET['codigo'] ?? '';
if (!$codigo) {
    echo "<div class='alert alert-danger'>Código no proporcionado.</div>";
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM Productos WHERE UPC = :codigo");
$stmt->execute([':codigo' => $codigo]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$producto) {
    echo "<div class='alert alert-danger'>Producto no encontrado.</div>";
    exit();
}
?>

<div class="card shadow-sm">
  <div class="card-header bg-primary text-white">Actualizar Stock</div>
  <div class="card-body">
    <p><strong>Producto:</strong> <?= htmlspecialchars($producto['nombre']) ?></p>
    <p><strong>Código de barras:</strong> <?= htmlspecialchars($producto['UPC']) ?></p>
    <p><strong>Stock actual:</strong> <span id="stock-actual"><?= $producto['Cantidad'] ?></span></p>

    <form id="form-actualizar" class="row g-3 mt-3">
      <div class="col-md-6">
        <label for="nuevo_stock" class="form-label">Nuevo stock</label>
        <input type="number" class="form-control" id="nuevo_stock" name="nuevo_stock" required>
        <input type="hidden" name="codigo" value="<?= htmlspecialchars($producto['UPC']) ?>">
      </div>
      <div class="col-12">
        <button type="submit" class="btn btn-success w-100">Actualizar</button>
      </div>
    </form>
  </div>
</div>
