<?php
require_once "./controllers/mainController.php";

$campos = "Productos.ProductoID,Productos.UPC,Productos.Nombre as productName,Productos.Descripcion,Productos.PrecioUnitario,Productos.Cantidad,Productos.image,Productos.CategoriaID,Productos.UsuarioID,Productos.Tipo,Categorias.CategoriaID,Categorias.Nombre as CatName,Usuarios.UsuarioID,Usuarios.Nombre,Usuarios.Username";

$checkInventory = conexion();
$checkInventory = $checkInventory->query("SELECT $campos FROM Productos INNER JOIN Categorias ON Productos.CategoriaID=Categorias.CategoriaID INNER JOIN Usuarios ON Productos.UsuarioID=Usuarios.UsuarioID");
$datos = $checkInventory->fetchAll();

$total = conexion();
$total = $total->query("SELECT COUNT(*) FROM Productos WHERE Cantidad < 5");
$totalCount = (int) $total->fetchColumn();

$productsDown = conexion();
$productsDown = $productsDown->query("SELECT * FROM Productos WHERE Cantidad < 5");
$products = $productsDown->fetchAll();

$totalProd = conexion();
$totalProd = $totalProd->query("SELECT COUNT(*) FROM Productos");
$totalCountProd = (int) $totalProd->fetchColumn();

?>
<div class="container-fluid">
	<div class="d-flex justify-content-center row">
		<div class="col-md-12">
			<div class="row">

				<div class="col-xl-3 col-md-6 mb-4">
					<div class="card border-left-success shadow h-100 py-2">
						<div class="card-body">
							<div class="row no-gutters align-items-center">
								<div class="col mr-2">
									<div class="text-xs font-weight-bold text-success text-uppercase mb-1">
										Total de productos en Sistema</div>
									<div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalCountProd; ?></div>
								</div>
								<div class="col-auto">
									<i class="fas fa-archive fa-2x text-gray-300"></i>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="col-xl-3 col-md-6 mb-4">
					<div class="card border-left-danger shadow h-100 py-2">
						<div class="card-body">
							<div class="row no-gutters align-items-center">
								<div class="col mr-2">
									<div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
										Productos con Inventario menor a 5 de stock </div>
									<div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalCount; ?></div>
								</div>
								<div class="col-auto">
									<i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
								</div>
							</div>
						</div>
					</div>
				</div>

				<button id="btnStockBajoPDF" class="btn btn-danger">
					<i class="fas fa-file-pdf"></i> Descargar PDF de Stock Bajo
				</button>

			</div>
		</div>
	</div>
</div>
<script>
document.getElementById("btnStockBajoPDF").addEventListener("click", function () {
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando...';

    fetch("api/generarStockBajoPDF.php", {
        method: "POST"
    })
    .then(res => {
        if (!res.ok) throw new Error("Error en la respuesta del servidor");
        return res.blob();
    })
    .then(blob => {
        const url = URL.createObjectURL(blob);
        const a = document.createElement("a");
        a.href = url;
        a.download = "stock_bajo.pdf";
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(url);
    })
    .catch(err => {
        console.error("Error al generar PDF:", err);
        alert("Ocurrió un error al generar el PDF.");
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-file-pdf"></i> Descargar PDF de Stock Bajo';
    });
});
</script>
