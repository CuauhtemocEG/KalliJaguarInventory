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
                <button type="submit" class="btn btn-primary btn-block">Generar PDF</button>
            </div>
        </div>
    </form>
</div>

<script>
document.getElementById("formReportePDF").addEventListener("submit", function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('https://stagging.kallijaguar-inventory.com/api/generarReportePDF.php', {
        method: 'POST',
        body: formData
    })
    .then(res => {
        if(!res.ok) throw new Error('Error en la respuesta');
        return res.blob();
    })
    .then(blob => {
        const url = URL.createObjectURL(blob);
        window.open(url, '_blank');
    })
    .catch(err => {
        console.error('Error al generar PDF:', err);
        alert('Error al generar el PDF');
    });
});
</script>
