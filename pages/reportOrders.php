<div class="container py-4">
    <h2 class="mb-4">Generar Reporte por Rango de Fechas</h2>
    <form id="formReportePDF">
        <div class="form-row">
            <div class="form-group col-md-5">
                <label for="fecha_desde">Desde:</label>
                <input type="date" class="form-control" name="fecha_desde" required>
            </div>
            <div class="form-group col-md-5">
                <label for="fecha_hasta">Hasta:</label>
                <input type="date" class="form-control" name="fecha_hasta" required>
            </div>
            <div class="form-group col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary btn-block" id="btnGenerar">Generar PDF</button>
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
document.getElementById("formReportePDF").addEventListener("submit", function(e) {
    e.preventDefault();

    const form = this;
    const formData = new FormData(form);

    const fechaDesde = form.fecha_desde.value;
    const fechaHasta = form.fecha_hasta.value;

    const fileName = `reporteKalli-${fechaDesde}_${fechaHasta}.pdf`;

    const loader = document.getElementById('loader');
    const btn = document.getElementById('btnGenerar');
    loader.style.display = 'block';
    btn.disabled = true;
    btn.textContent = 'Generando...';

    fetch('https://www.kallijaguar-inventory.com/api/generarReportePDF.php', {
        method: 'POST',
        body: formData
    })
    .then(res => {
        if (!res.ok) throw new Error('Error en la respuesta del servidor');
        return res.blob();
    })
    .then(blob => {
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = fileName;
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(url);
    })
    .catch(err => {
        console.error('Error al generar PDF:', err);
        alert('Error al generar el PDF');
    })
    .finally(() => {
        loader.style.display = 'none';
        btn.disabled = false;
        btn.textContent = 'Generar PDF';
    });
});
</script>
