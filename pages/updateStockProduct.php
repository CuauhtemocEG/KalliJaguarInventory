<?php
require './controllers/mainController.php';
$pdo = conexion();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    ob_start();

    $codigo = limpiar_cadena($_POST['codigo'] ?? '');
    $nuevo_stock = intval($_POST['nuevo_stock'] ?? -1);

    if ($codigo && $nuevo_stock >= 0) {
        $stmt = $pdo->prepare("SELECT Cantidad FROM Productos WHERE UPC = :codigo");
        $stmt->execute([':codigo' => $codigo]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$producto) {
            ob_end_clean();
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
            ob_end_clean();
            echo json_encode(['status' => 'ok', 'message' => '¡Stock actualizado exitosamente!']);
        } else {
            ob_end_clean();
            echo json_encode(['status' => 'error', 'message' => 'Error al actualizar stock.']);
        }
    } else {
        ob_end_clean();
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

$etiquetaPesable = '';

if ($producto['Tipo'] === 'Pesable' && $producto['Cantidad'] < 1.0) {
    $etiquetaPesable = 'gr';
} else {
    $etiquetaPesable = 'Kg';
}

$etiquetaUnit = '';

if ($producto['Tipo'] === 'Unidad') {
    $etiquetaUnit = 'Unidad(es)';
}
?>

<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <i class="fas fa-boxes me-2"></i>Actualizar Stock
        </div>
        <div class="card-body">
            <p><strong>Producto:</strong> <?= htmlspecialchars($producto['Nombre']) ?></p>
            <p><strong>Código de barras (UPC):</strong> <?= htmlspecialchars($producto['UPC']) ?></p>
            <?php if ($producto['Tipo'] === 'Pesable') { ?>
                <p><strong>Tipo de Inventario:</strong> <?= htmlspecialchars($producto['Tipo']) ?></p>
                <p><strong>Stock actual:</strong> <span id="stock-actual" class="badge bg-info text-dark"><?= number_format($producto['Cantidad'], 3) . ' ' . $etiquetaPesable ?></span></p>
            <?php } else { ?>
                <p><strong>Tipo de Inventario:</strong> <?= htmlspecialchars($producto['Tipo']) ?></p>
                <p><strong>Stock actual:</strong> <span id="stock-actual" class="badge bg-info text-dark"><?= number_format($producto['Cantidad'], 0) . ' ' . $etiquetaUnit ?></span></p>
            <? } ?>
            <form id="form-actualizar" class="row g-3 mt-3">
                <div class="col-md-6">
                    <label for="nuevo_stock" class="form-label">Nuevo stock</label>
                    <?php if ($producto['Tipo'] === 'Pesable') { ?>
                        <input type="number" class="form-control" id="nuevo_stock" name="nuevo_stock" min="0" step="0.250" required>
                    <?php } else { ?>
                        <input type="number" class="form-control" id="nuevo_stock" name="nuevo_stock" min="0" step="1" required>
                    <? } ?>
                    <input type="hidden" name="codigo" value="<?= htmlspecialchars($producto['UPC']) ?>">
                </div>
                <hr>
                <div class="col-12">
                    <button type="submit" class="btn btn-success w-100 mt-2">
                        <i class="fas fa-save me-2"></i> Actualizar stock
                    </button>
                </div>
            </form>
            <div id="boton-nuevo-escanear" class="mt-4" style="display: none;">
                <a href="index.php?page=scanProducts" class="btn btn-primary w-100">
                    <i class="fas fa-barcode me-2"></i> Escanear nuevo producto
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById("form-actualizar").addEventListener("submit", function(e) {
        e.preventDefault();

        const datos = new FormData(this);

        fetch('../api/updateStock.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new FormData(this)
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
                    document.getElementById("boton-nuevo-escanear").style.display = "block";
                }
            })
            .catch(err => {
                Swal.fire("Error", "No se pudo actualizar el stock. Intenta nuevamente.", "error");
                console.error(err);
            });
    });
</script>