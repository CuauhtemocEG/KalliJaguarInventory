<?php
require './controllers/mainController.php';
$pdo = conexion();
header('Content-Type: application/json');

// Si es una solicitud POST (AJAX para actualizar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = limpiar_cadena($_POST['codigo'] ?? '');
    $nuevo_stock = intval($_POST['nuevo_stock'] ?? -1);

    if ($codigo && $nuevo_stock >= 0) {
        // Obtener stock actual
        $stmt = $pdo->prepare("SELECT Cantidad FROM Productos WHERE UPC = :codigo");
        $stmt->execute([':codigo' => $codigo]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$producto) {
            echo json_encode(['status' => 'error', 'message' => 'Producto no encontrado.']);
            exit();
        }

        $stock_actual = $producto['Cantidad'];

        // Actualizar stock
        $upd = $pdo->prepare("UPDATE Productos SET Cantidad = :nuevo WHERE UPC = :codigo");
        if ($upd->execute([':nuevo' => $nuevo_stock, ':codigo' => $codigo])) {
            // Insertar en logs
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

// Si es GET (carga del formulario)
$codigo = $_GET['codigo'] ?? '';
if (!$codigo) {
    echo "Código no proporcionado.";
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM productos WHERE codigo_barras = :codigo");
$stmt->execute([':codigo' => $codigo]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$producto) {
    echo "Producto no encontrado.";
    exit();
}
?>
  <div class="container mt-5">
    <div class="card shadow-sm">
      <div class="card-header bg-primary text-white">Actualizar Stock</div>
      <div class="card-body">
        <p><strong>Producto:</strong> <?= htmlspecialchars($producto['nombre']) ?></p>
        <p><strong>Código de barras:</strong> <?= htmlspecialchars($producto['codigo_barras']) ?></p>
        <p><strong>Stock actual:</strong> <span id="stock-actual"><?= $producto['stock'] ?></span></p>

        <form id="form-actualizar" class="row g-3 mt-3">
          <div class="col-md-6">
            <label for="nuevo_stock" class="form-label">Nuevo stock</label>
            <input type="number" class="form-control" id="nuevo_stock" name="nuevo_stock" required>
            <input type="hidden" name="codigo" value="<?= htmlspecialchars($producto['codigo_barras']) ?>">
          </div>
          <div class="col-12">
            <button type="submit" class="btn btn-success w-100">Actualizar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    document.getElementById("form-actualizar").addEventListener("submit", function(e) {
      e.preventDefault();
      const datos = new FormData(this);

      fetch("updateStockProduct.php", {
        method: "POST",
        body: datos
      })
      .then(res => res.json())
      .then(data => {
        Swal.fire({
          title: data.status === "ok" ? "¡Éxito!" : "Error",
          text: data.message,
          icon: data.status === "ok" ? "success" : "error",
          confirmButtonText: "Aceptar"
        });

        if (data.status === "ok") {
          document.getElementById("stock-actual").textContent = document.getElementById("nuevo_stock").value;
        }
      })
      .catch(() => {
        Swal.fire("Error", "No se pudo procesar la solicitud.", "error");
      });
    });
  </script>
</body>