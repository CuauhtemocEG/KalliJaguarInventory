<?php
require './controllers/mainController.php';
$pdo = conexion();

// Si es POST, procesamos actualización de stock
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

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

// Si es GET, mostrar formulario
$codigo = $_GET['codigo'] ?? '';
if (!$codigo) {
    echo "<div class='alert alert-danger'>Código no proporcionado.</div>";
    return;
}

$stmt = $pdo->prepare("SELECT * FROM Productos WHERE UPC = :codigo");
$stmt->execute([':codigo' => $codigo]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$producto) {
    echo "<div class='alert alert-danger'>Producto no encontrado.</div>";
    return;
}
?>

<div class="container py-4">
  <div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
      <i class="fas fa-boxes me-2"></i>Actualizar Stock
    </div>
    <div class="card-body">
      <p><strong>Producto:</strong> <?= htmlspecialchars($producto['Nombre']) ?></p>
      <p><strong>Código de barras (UPC):</strong> <?= htmlspecialchars($producto['UPC']) ?></p>
      <p><strong>Stock actual:</strong> <span id="stock-actual" class="badge bg-info text-dark"><?= $producto['Cantidad'] ?></span></p>

      <form id="form-actualizar" class="row g-3 mt-3">
        <div class="col-md-6">
          <label for="nuevo_stock" class="form-label">Nuevo stock</label>
          <input type="number" class="form-control" id="nuevo_stock" name="nuevo_stock" min="0" required>
          <input type="hidden" name="codigo" value="<?= htmlspecialchars($producto['UPC']) ?>">
        </div>
        <div class="col-12">
          <button type="submit" class="btn btn-success w-100">
            <i class="fas fa-save me-2"></i>Actualizar
          </button>
        </div>
      </form>

      <div id="boton-nuevo-escanear" class="mt-4" style="display: none;">
        <a href="index.php?page=scanProducts" class="btn btn-primary w-100">
          <i class="fas fa-barcode me-2"></i>Escanear nuevo producto
        </a>
      </div>
    </div>
  </div>
</div>

<script>
  document.getElementById("form-actualizar").addEventListener("submit", function(e) {
    e.preventDefault();

    const datos = new FormData(this);

    fetch('index.php?page=updateStockProduct', {
      method: 'POST',
      body: datos
    })
    .then(res => res.json())
    .then(data => {
      Swal.fire({
        title: data.status === "ok" ? "¡Éxito!" : "Error",
        text: data.message,
        icon: data.status === "ok" ? "success" : "error"
      });

      if (data.status === "ok") {
        document.getElementById("stock-actual").textContent = document.getElementById("nuevo_stock").value;

        const btn = document.createElement("button");
        btn.textContent = "Escanear nuevo producto";
        btn.className = "btn btn-primary mt-3";
        btn.onclick = () => window.location.href = "index.php?page=scanProducts";
        document.querySelector(".card-body").appendChild(btn);
      }
    });
  });
</script>