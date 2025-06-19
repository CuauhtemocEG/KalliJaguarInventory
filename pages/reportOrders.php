<div class="container py-5">
  <div class="card shadow-lg">
    <div class="card-header bg-primary text-white text-center">
      <h3 class="mb-0">Generación de Reportes</h3>
    </div>
    <div class="card-body">
      <div class="mb-4 text-center">
        <button id="btnStockBajoPDF" class="btn btn-danger mx-1">
          <i class="fas fa-file-pdf"></i> PDF Stock Bajo
        </button>
        <button id="btnStockBajoTagPDF" class="btn btn-secondary mx-1">
          <i class="fas fa-tags"></i> PDF Stock Bajo por Tag
        </button>
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="card mb-4 border-left-primary shadow">
            <div class="card-header bg-light font-weight-bold">
              Reporte por Comandas y Sucursal
            </div>
            <div class="card-body">
              <form id="formReporteComandas">
                <div class="form-group">
                  <label for="fecha_desde_comanda">Desde:</label>
                  <input type="date" class="form-control" name="fecha_desde" id="fecha_desde_comanda" required>
                </div>
                <div class="form-group">
                  <label for="fecha_hasta_comanda">Hasta:</label>
                  <input type="date" class="form-control" name="fecha_hasta" id="fecha_hasta_comanda" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block" id="btnGenerarComandas">
                  <i class="fas fa-file-pdf"></i> Generar PDF
                </button>
              </form>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="card mb-4 border-left-success shadow">
            <div class="card-header bg-light font-weight-bold">
              Reporte Consolidado de Productos
            </div>
            <div class="card-body">
              <form id="formReporteProductos">
                <div class="form-group">
                  <label for="fecha_desde_productos">Desde:</label>
                  <input type="date" class="form-control" name="fecha_desde" id="fecha_desde_productos" required>
                </div>
                <div class="form-group">
                  <label for="fecha_hasta_productos">Hasta:</label>
                  <input type="date" class="form-control" name="fecha_hasta" id="fecha_hasta_productos" required>
                </div>
                <button type="submit" class="btn btn-success btn-block" id="btnGenerarProductos">
                  <i class="fas fa-file-pdf"></i> Generar PDF
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>

      <div id="loader" style="display: none; text-align:center;">
        <div class="spinner-border text-primary" role="status">
          <span class="sr-only">Generando PDF...</span>
        </div>
        <p class="mt-2">Generando PDF, por favor espera...</p>
      </div>
    </div>
  </div>
</div>

<script>
function generarPDF(formId, btnId, urlAPI, fileNamePrefix) {
    const form = document.getElementById(formId);
    const btn = document.getElementById(btnId);

    form.addEventListener("submit", async function (e) {
        e.preventDefault();
        const formData = new FormData(form);
        const fechaDesde = formData.get("fecha_desde");
        const fechaHasta = formData.get("fecha_hasta");

        if (!fechaDesde || !fechaHasta) {
            return Swal.fire({
                icon: "warning",
                title: "Fechas requeridas",
                text: "Por favor, selecciona ambas fechas.",
            });
        }

        if (fechaDesde > fechaHasta) {
            return Swal.fire({
                icon: "error",
                title: "Rango inválido",
                text: "La fecha 'Desde' no puede ser mayor que la fecha 'Hasta'.",
            });
        }

        const result = await Swal.fire({
            title: "¿Deseas generar el reporte?",
            text: `Desde: ${fechaDesde} - Hasta: ${fechaHasta}`,
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Sí, generar",
            cancelButtonText: "Cancelar",
        });

        if (!result.isConfirmed) return;

        const fileName = `${fileNamePrefix}-${fechaDesde}_${fechaHasta}.pdf`;

        Swal.fire({
            title: "Generando PDF...",
            text: "Por favor espera un momento",
            didOpen: () => Swal.showLoading(),
            allowOutsideClick: false,
        });

        btn.disabled = true;

        fetch(urlAPI, {
                method: "POST",
                body: formData
            })
            .then((res) => {
                if (!res.ok) throw new Error("Error en el servidor");
                return res.blob();
            })
            .then((blob) => {
                Swal.close();
                const url = URL.createObjectURL(blob);
                const a = document.createElement("a");
                a.href = url;
                a.download = fileName;
                document.body.appendChild(a);
                a.click();
                a.remove();
                URL.revokeObjectURL(url);

                Swal.fire({
                    icon: "success",
                    title: "¡Reporte generado!",
                    text: "Tu archivo PDF se ha descargado correctamente.",
                    timer: 3000,
                    showConfirmButton: false,
                });
            })
            .catch((err) => {
                console.error("Error al generar PDF:", err);
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Ocurrió un error al generar el PDF.",
                });
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-file-pdf"></i> Generar PDF';
            });
    });
}

document.getElementById("btnStockBajoPDF").addEventListener("click", function () {
  const btn = this;
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando...';

  Swal.fire({
    title: "Generando PDF de stock bajo...",
    didOpen: () => Swal.showLoading(),
    allowOutsideClick: false,
  });

  fetch("api/generarStockBajoPDF.php", {
    method: "POST",
  })
    .then((res) => {
      if (!res.ok) throw new Error("Error en el servidor");
      return res.blob();
    })
    .then((blob) => {
      Swal.close();
      const url = URL.createObjectURL(blob);
      const a = document.createElement("a");
      a.href = url;
      a.download = "stock_bajo.pdf";
      document.body.appendChild(a);
      a.click();
      a.remove();
      URL.revokeObjectURL(url);

      Swal.fire({
        icon: "success",
        title: "PDF generado",
        text: "Archivo descargado exitosamente.",
        timer: 3000,
        showConfirmButton: false,
      });
    })
    .catch((err) => {
      console.error("Error:", err);
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "Ocurrió un error al generar el PDF.",
      });
    })
    .finally(() => {
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-file-pdf"></i> PDF Stock Bajo';
    });
});

document.getElementById("btnStockBajoTagPDF").addEventListener("click", function () {
  const btn = this;
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando...';

  Swal.fire({
    title: "Generando PDF por Tag...",
    didOpen: () => Swal.showLoading(),
    allowOutsideClick: false,
  });

  fetch("api/generarStockBajoTagPDF.php", {
    method: "POST",
  })
    .then((res) => {
      if (!res.ok) throw new Error("Error en el servidor");
      return res.blob();
    })
    .then((blob) => {
      Swal.close();
      const url = URL.createObjectURL(blob);
      const a = document.createElement("a");
      a.href = url;
      a.download = "stock_bajo_por_tag.pdf";
      document.body.appendChild(a);
      a.click();
      a.remove();
      URL.revokeObjectURL(url);

      Swal.fire({
        icon: "success",
        title: "PDF generado",
        text: "Archivo descargado correctamente.",
        timer: 3000,
        showConfirmButton: false,
      });
    })
    .catch((err) => {
      console.error("Error:", err);
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "Ocurrió un error al generar el PDF.",
      });
    })
    .finally(() => {
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-tags"></i> PDF Stock Bajo por Tag';
    });
});

generarPDF(
  "formReporteComandas",
  "btnGenerarComandas",
  "https://www.kallijaguar-inventory.com/api/generarReportePDF.php",
  "reporteComandas"
);
generarPDF(
  "formReporteProductos",
  "btnGenerarProductos",
  "https://www.kallijaguar-inventory.com/api/generarReporteProductosPDF.php",
  "reporteProductos"
);
</script>
