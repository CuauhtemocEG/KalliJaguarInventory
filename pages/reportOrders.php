<div class="container py-4">
    <h2 class="mb-4">Reportes por Rango de Fechas</h2>

    <button id="btnStockBajoPDF" class="btn btn-sm btn-danger">
        <i class="fas fa-file-pdf"></i> Descargar PDF de Stock Bajo
    </button>

    <form id="formReporteComandas">
        <div class="form-row">
            <div class="form-group col-md-5">
                <label for="fecha_desde_comanda">Desde:</label>
                <input type="date" class="form-control" name="fecha_desde" id="fecha_desde_comanda" required>
            </div>
            <div class="form-group col-md-5">
                <label for="fecha_hasta_comanda">Hasta:</label>
                <input type="date" class="form-control" name="fecha_hasta" id="fecha_hasta_comanda" required>
            </div>
            <div class="form-group col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary btn-block" id="btnGenerarComandas">PDF Comandas</button>
            </div>
        </div>
    </form>

    <form id="formReporteProductos">
        <div class="form-row">
            <div class="form-group col-md-5">
                <label for="fecha_desde_productos">Desde:</label>
                <input type="date" class="form-control" name="fecha_desde" id="fecha_desde_productos" required>
            </div>
            <div class="form-group col-md-5">
                <label for="fecha_hasta_productos">Hasta:</label>
                <input type="date" class="form-control" name="fecha_hasta" id="fecha_hasta_productos" required>
            </div>
            <div class="form-group col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-success btn-block" id="btnGenerarProductos">PDF Productos</button>
            </div>
        </div>
    </form>

    <div id="loader" style="display: none; text-align:center;">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Generando PDF...</span>
        </div>
        <p>Generando PDF, por favor espera...</p>
    </div>
</div>

<script>
    function generarPDF(formId, btnId, urlAPI, fileNamePrefix) {
        const form = document.getElementById(formId);
        const btn = document.getElementById(btnId);
        const loader = document.getElementById("loader");

        form.addEventListener("submit", function(e) {
            e.preventDefault();
            const formData = new FormData(form);
            const fechaDesde = formData.get("fecha_desde");
            const fechaHasta = formData.get("fecha_hasta");

            const fileName = `${fileNamePrefix}-${fechaDesde}_${fechaHasta}.pdf`;

            loader.style.display = "block";
            btn.disabled = true;
            btn.textContent = "Generando...";

            fetch(urlAPI, {
                    method: "POST",
                    body: formData
                })
                .then(res => {
                    if (!res.ok) throw new Error("Error en la respuesta del servidor");
                    return res.blob();
                })
                .then(blob => {
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement("a");
                    a.href = url;
                    a.download = fileName;
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
                    loader.style.display = "none";
                    btn.disabled = false;
                    btn.textContent = "Generar PDF";
                });
        });
    }

    document.getElementById("btnStockBajoPDF").addEventListener("click", function() {
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

    generarPDF("formReporteComandas", "btnGenerarComandas", "https://www.kallijaguar-inventory.com/api/generarReportePDF.php", "reporteComandas");
    generarPDF("formReporteProductos", "btnGenerarProductos", "https://www.kallijaguar-inventory.com/api/generarReporteProductosPDF.php", "reporteProductos");
</script>