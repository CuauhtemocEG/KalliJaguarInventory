<div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-indigo-100">
  <div class="container mx-auto px-4 py-8">
    <div class="text-center mb-8 md:mb-10">
      <div class="inline-flex items-center justify-center w-12 h-12 md:w-16 md:h-16 bg-gradient-to-r from-blue-600 to-purple-600 rounded-full mb-3 md:mb-4">
        <i class="fas fa-chart-bar text-white text-lg md:text-2xl"></i>
      </div>
      <h1 class="text-2xl md:text-4xl font-bold text-gray-900 mb-2 px-2">Generación de Reportes</h1>
      <p class="text-sm md:text-lg text-gray-600 max-w-2xl mx-auto px-4">
        Genera reportes detallados y análisis de inventario con solo unos clics
      </p>
    </div>

    <div class="flex flex-col sm:flex-row flex-wrap justify-center gap-3 md:gap-4 mb-8 md:mb-10 px-2">
      <button id="btnStockBajoPDF" class="group bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white px-4 md:px-8 py-3 md:py-4 rounded-2xl font-semibold shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 w-full sm:w-auto">
        <div class="flex items-center justify-center sm:justify-start space-x-3">
          <div class="w-8 h-8 md:w-10 md:h-10 bg-white/20 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
            <i class="fas fa-file-pdf text-sm md:text-lg"></i>
          </div>
          <span class="text-sm md:text-base">PDF Stock Bajo</span>
        </div>
      </button>
      <button id="btnStockBajoTagPDF" class="group bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white px-4 md:px-8 py-3 md:py-4 rounded-2xl font-semibold shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 w-full sm:w-auto">
        <div class="flex items-center justify-center sm:justify-start space-x-3">
          <div class="w-8 h-8 md:w-10 md:h-10 bg-white/20 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
            <i class="fas fa-tags text-sm md:text-lg"></i>
          </div>
          <span class="text-sm md:text-base">PDF Stock por Tag</span>
        </div>
      </button>
    </div>

    <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-2xl md:rounded-3xl shadow-xl border-2 border-purple-200 overflow-hidden mb-8 md:mb-10">
      <div class="bg-gradient-to-r from-purple-600 to-pink-600 p-4 md:p-6">
        <div class="flex items-center space-x-3 md:space-x-4">
          <div class="w-10 h-10 md:w-12 md:h-12 bg-white/20 rounded-full flex items-center justify-center">
            <i class="fas fa-file-invoice text-white text-lg md:text-xl"></i>
          </div>
          <div>
            <h3 class="text-lg md:text-xl font-bold text-white flex items-center gap-2">
              Orden de Solicitud de Compra v2.0
              <span class="px-2 py-1 bg-yellow-400 text-purple-900 text-xs font-bold rounded-full">PROFESIONAL</span>
            </h3>
            <p class="text-xs md:text-sm text-purple-100">Documento formal para solicitud de productos | Formato empresarial con firmas</p>
          </div>
        </div>
      </div>
      
      <div class="p-4 md:p-8 bg-white">
        <form id="formReporteStockTagV2" class="space-y-4 md:space-y-6">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 md:gap-6">
            <div class="space-y-2">
              <label for="tag_v2_filter" class="block text-xs md:text-sm font-semibold text-gray-700">
                <i class="fas fa-tag text-purple-500 mr-2"></i>Seleccionar Tag/Categoría
              </label>
              <select name="tag" id="tag_v2_filter"
                class="w-full px-3 md:px-4 py-2 md:py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all duration-200 text-sm md:text-base">
                <option value="">Todos los Tags</option>
              </select>
            </div>
            
            <div class="space-y-2">
              <label for="tipo_v2_filter" class="block text-xs md:text-sm font-semibold text-gray-700">
                <i class="fas fa-cube text-purple-500 mr-2"></i>Filtrar por Tipo
              </label>
              <select name="tipo" id="tipo_v2_filter"
                class="w-full px-3 md:px-4 py-2 md:py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all duration-200 text-sm md:text-base">
                <option value="">Todos los tipos</option>
                <option value="Unidad">Unidad</option>
                <option value="Pesable">Pesable</option>
              </select>
            </div>
          </div>
          
          <button type="submit" id="btnGenerarStockV2"
            class="w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-bold py-3 md:py-4 px-4 md:px-6 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-center space-x-3 group text-sm md:text-base">
            <i class="fas fa-file-invoice group-hover:scale-110 transition-transform"></i>
            <span>Generar Orden de Solicitud Profesional</span>
          </button>
        </form>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 md:gap-8 mb-8 md:mb-10">
      <div class="group bg-white rounded-2xl md:rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-300 overflow-hidden border border-blue-100">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-4 md:p-6">
          <div class="flex items-center space-x-3 md:space-x-4">
            <div class="w-10 h-10 md:w-12 md:h-12 bg-white/20 rounded-full flex items-center justify-center">
              <i class="fas fa-clipboard-list text-white text-lg md:text-xl"></i>
            </div>
            <div>
              <h3 class="text-lg md:text-xl font-bold text-white">Reporte por Comandas</h3>
              <p class="text-xs md:text-sm text-blue-100">Análisis detallado por sucursal</p>
            </div>
          </div>
        </div>
        
        <div class="p-4 md:p-8">
          <form id="formReporteComandas" class="space-y-4 md:space-y-6">
            <div class="space-y-2">
              <label for="fecha_desde_comanda" class="block text-xs md:text-sm font-semibold text-gray-700">
                <i class="fas fa-calendar-alt text-blue-500 mr-2"></i>Fecha y hora desde:
              </label>
              <input type="datetime-local" name="fecha_desde" id="fecha_desde_comanda" required
                class="w-full px-3 md:px-4 py-2 md:py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200 text-sm md:text-base">
            </div>
            
            <div class="space-y-2">
              <label for="fecha_hasta_comanda" class="block text-xs md:text-sm font-semibold text-gray-700">
                <i class="fas fa-calendar-alt text-blue-500 mr-2"></i>Fecha y hora hasta:
              </label>
              <input type="datetime-local" name="fecha_hasta" id="fecha_hasta_comanda" required
                class="w-full px-3 md:px-4 py-2 md:py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200 text-sm md:text-base">
            </div>
            
            <button type="submit" id="btnGenerarComandas" 
              class="w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-bold py-3 md:py-4 px-4 md:px-6 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-center space-x-3 group text-sm md:text-base mb-2">
              <i class="fas fa-file-pdf group-hover:scale-110 transition-transform"></i>
              <span>Generar Reporte PDF</span>
            </button>
          </form>
        </div>
      </div>

      <div class="group bg-white rounded-2xl md:rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-300 overflow-hidden border border-green-100">
        <div class="bg-gradient-to-r from-green-500 to-green-600 p-4 md:p-6">
          <div class="flex items-center space-x-3 md:space-x-4">
            <div class="w-10 h-10 md:w-12 md:h-12 bg-white/20 rounded-full flex items-center justify-center">
              <i class="fas fa-box text-white text-lg md:text-xl"></i>
            </div>
            <div>
              <h3 class="text-lg md:text-xl font-bold text-white">Reporte Consolidado</h3>
              <p class="text-xs md:text-sm text-green-100">Productos y movimientos</p>
            </div>
          </div>
        </div>
        
        <div class="p-4 md:p-8">
          <form id="formReporteProductos" class="space-y-4 md:space-y-6">
            <div class="space-y-2">
              <label for="fecha_desde_productos" class="block text-xs md:text-sm font-semibold text-gray-700">
                <i class="fas fa-calendar-alt text-green-500 mr-2"></i>Fecha y hora desde:
              </label>
              <input type="datetime-local" name="fecha_desde" id="fecha_desde_productos" required
                class="w-full px-3 md:px-4 py-2 md:py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all duration-200 text-sm md:text-base">
            </div>
            
            <div class="space-y-2">
              <label for="fecha_hasta_productos" class="block text-xs md:text-sm font-semibold text-gray-700">
                <i class="fas fa-calendar-alt text-green-500 mr-2"></i>Fecha y hora hasta:
              </label>
              <input type="datetime-local" name="fecha_hasta" id="fecha_hasta_productos" required
                class="w-full px-3 md:px-4 py-2 md:py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all duration-200 text-sm md:text-base">
            </div>
            
            <button type="submit" id="btnGenerarProductos"
              class="w-full bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-bold py-3 md:py-4 px-4 md:px-6 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-center space-x-3 group text-sm md:text-base mb-2">
              <i class="fas fa-file-pdf group-hover:scale-110 transition-transform"></i>
              <span>Generar Reporte PDF</span>
            </button>
          </form>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-2xl md:rounded-3xl shadow-xl border border-yellow-100 overflow-hidden mb-8 md:mb-10">
      <div class="bg-gradient-to-r from-yellow-500 to-orange-500 p-4 md:p-6">
        <div class="flex items-center space-x-3 md:space-x-4">
          <div class="w-10 h-10 md:w-12 md:h-12 bg-white/20 rounded-full flex items-center justify-center">
            <i class="fas fa-chart-line text-white text-lg md:text-xl"></i>
          </div>
          <div>
            <h3 class="text-lg md:text-xl font-bold text-white">Productos Más Solicitados</h3>
            <p class="text-xs md:text-sm text-yellow-100">Análisis avanzado de demanda por tag</p>
          </div>
        </div>
      </div>
      
      <div class="p-4 md:p-8">
        <form id="formReporteProductosSolicitados" class="space-y-4 md:space-y-6">
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 md:gap-6">
            <div class="lg:col-span-2 space-y-2">
              <label for="tag_filter" class="block text-xs md:text-sm font-semibold text-gray-700">
                <i class="fas fa-tag text-yellow-500 mr-2"></i>Tag <span class="text-red-500">*</span>
              </label>
              <select name="tag" id="tag_filter" required
                class="w-full px-3 md:px-4 py-2 md:py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 transition-all duration-200 text-sm md:text-base">
                <option value="">Seleccionar Tag...</option>
              </select>
            </div>
            
            <div class="space-y-2">
              <label for="tipo_filter" class="block text-xs md:text-sm font-semibold text-gray-700">
                <i class="fas fa-cube text-yellow-500 mr-2"></i>Tipo
              </label>
              <select name="tipo" id="tipo_filter"
                class="w-full px-3 md:px-4 py-2 md:py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 transition-all duration-200 text-sm md:text-base">
                <option value="">Todos</option>
                <option value="Unidad">Unidad</option>
                <option value="Pesable">Pesable</option>
              </select>
            </div>
            
            <div class="space-y-2">
              <label for="fecha_desde_solicitados" class="block text-xs md:text-sm font-semibold text-gray-700">
                <i class="fas fa-calendar-alt text-yellow-500 mr-2"></i>Fecha y hora desde <span class="text-red-500">*</span>
              </label>
              <input type="datetime-local" name="fecha_desde" id="fecha_desde_solicitados" required
                class="w-full px-3 md:px-4 py-2 md:py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 transition-all duration-200 text-sm md:text-base">
            </div>
            
            <div class="space-y-2">
              <label for="fecha_hasta_solicitados" class="block text-xs md:text-sm font-semibold text-gray-700">
                <i class="fas fa-calendar-alt text-yellow-500 mr-2"></i>Fecha y hora hasta <span class="text-red-500">*</span>
              </label>
              <input type="datetime-local" name="fecha_hasta" id="fecha_hasta_solicitados" required
                class="w-full px-3 md:px-4 py-2 md:py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 transition-all duration-200 text-sm md:text-base">
            </div>
          </div>
          
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 md:gap-6">
            <div class="sm:col-span-2 lg:col-span-1 space-y-2">
              <label for="limite_productos" class="block text-xs md:text-sm font-semibold text-gray-700">
                <i class="fas fa-sort-numeric-up text-yellow-500 mr-2"></i>Límite de resultados
              </label>
              <select name="limite" id="limite_productos"
                class="w-full px-3 md:px-4 py-2 md:py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 transition-all duration-200 text-sm md:text-base">
                <option value="10">Top 10</option>
                <option value="20">Top 20</option>
                <option value="50">Top 50</option>
                <option value="100">Top 100</option>
                <option value="">Todos</option>
              </select>
            </div>
          </div>
          
          <button type="submit" id="btnGenerarSolicitados"
            class="w-full bg-gradient-to-r from-yellow-500 to-orange-500 hover:from-yellow-600 hover:to-orange-600 text-white font-bold py-3 md:py-4 px-4 md:px-6 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-center space-x-3 group text-sm md:text-base">
            <i class="fas fa-chart-line group-hover:scale-110 transition-transform"></i>
            <span class="hidden sm:inline">Generar Reporte Avanzado</span>
            <span class="sm:hidden">Generar Reporte</span>
          </button>
        </form>
      </div>
    </div>

    <div id="loader" class="hidden">
      <div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl p-6 md:p-8 shadow-2xl text-center max-w-xs md:max-w-sm mx-auto">
          <div class="w-12 h-12 md:w-16 md:h-16 mx-auto mb-3 md:mb-4 relative">
            <div class="absolute inset-0 border-4 border-blue-200 rounded-full"></div>
            <div class="absolute inset-0 border-4 border-blue-600 rounded-full animate-spin border-t-transparent"></div>
          </div>
          <h3 class="text-base md:text-lg font-semibold text-gray-900 mb-2">Generando PDF...</h3>
          <p class="text-sm md:text-base text-gray-600">Por favor espera un momento</p>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function getBasePath() {
  const origin = window.location.origin;
  const path = window.location.pathname;
  
  const pathArray = path.split('/').filter(p => p);
  
  const pagesIndex = pathArray.indexOf('pages');
  
  if (pagesIndex > -1) {
    const basePath = pathArray.slice(0, pagesIndex).join('/');
    return basePath ? '/' + basePath : '';
  }
  
  pathArray.pop();
  return pathArray.length > 0 ? '/' + pathArray.join('/') : '';
}

const BASE_PATH = getBasePath();

console.log('🔍 Información de debug:');
console.log('  - Origin:', window.location.origin);
console.log('  - Pathname:', window.location.pathname);
console.log('  - BASE_PATH:', BASE_PATH);
console.log('  - URL completa API:', window.location.origin + BASE_PATH + '/api/getTags.php');

document.addEventListener('DOMContentLoaded', function() {
  cargarTags();
  cargarTagsV2();
});

function cargarTags() {
  const tagsUrl = BASE_PATH + '/api/getTags.php';
  console.log('Cargando tags desde:', tagsUrl);
  fetch(tagsUrl)
    .then(response => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
    })
    .then(data => {
      const select = document.getElementById('tag_filter');
      if (data.success && data.tags) {
        data.tags.forEach(tag => {
          const option = document.createElement('option');
          option.value = tag;
          option.textContent = tag;
          select.appendChild(option);
        });
      }
    })
    .catch(error => {
      console.error('Error al cargar tags:', error);
      console.error('URL intentada:', tagsUrl);
    });
}

function cargarTagsV2() {
  const tagsUrl = BASE_PATH + '/api/getTags.php';
  console.log('Cargando tags v2 desde:', tagsUrl);
  fetch(tagsUrl)
    .then(response => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
    })
    .then(data => {
      const select = document.getElementById('tag_v2_filter');
      if (data.success && data.tags) {
        data.tags.forEach(tag => {
          const option = document.createElement('option');
          option.value = tag;
          option.textContent = tag;
          select.appendChild(option);
        });
      }
    })
    .catch(error => {
      console.error('Error al cargar tags v2:', error);
      console.error('URL intentada:', tagsUrl);
    });
}

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
        btn.innerHTML = `<i class="fas fa-spinner fa-spin group-hover:scale-110 transition-transform"></i><span>Generando...</span>`;

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
                btn.innerHTML = '<i class="fas fa-file-pdf group-hover:scale-110 transition-transform"></i><span>Generar Reporte PDF</span>';
            });
    });
}

document.getElementById("btnStockBajoPDF").addEventListener("click", function () {
  const btn = this;
  btn.disabled = true;
  btn.innerHTML = '<div class="flex items-center space-x-3"><div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center"><i class="fas fa-spinner fa-spin text-lg"></i></div><span>Generando...</span></div>';

  Swal.fire({
    title: "Generando PDF de stock bajo...",
    didOpen: () => Swal.showLoading(),
    allowOutsideClick: false,
  });

  fetch(BASE_PATH + "/api/generarStockBajoPDF.php", {
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
      btn.innerHTML = '<div class="flex items-center space-x-3"><div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform"><i class="fas fa-file-pdf text-lg"></i></div><span>PDF Stock Bajo</span></div>';
    });
});

document.getElementById("btnStockBajoTagPDF").addEventListener("click", function () {
  const btn = this;
  btn.disabled = true;
  btn.innerHTML = '<div class="flex items-center space-x-3"><div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center"><i class="fas fa-spinner fa-spin text-lg"></i></div><span>Generando...</span></div>';

  Swal.fire({
    title: "Generando PDF por Tag...",
    didOpen: () => Swal.showLoading(),
    allowOutsideClick: false,
  });

  const stockUrl = BASE_PATH + "/api/generarStockBajoTagPDF.php";
  fetch(stockUrl, {
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
      btn.innerHTML = '<div class="flex items-center space-x-3"><div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform"><i class="fas fa-tags text-lg"></i></div><span>PDF Stock por Tag</span></div>';
    });
});

generarPDF(
  "formReporteComandas",
  "btnGenerarComandas",
  BASE_PATH + "/api/generarReportePDF.php",
  "reporteComandas"
);
generarPDF(
  "formReporteProductos",
  "btnGenerarProductos",
  BASE_PATH + "/api/generarReporteProductosPDF.php",
  "reporteProductos"
);

document.getElementById("formReporteProductosSolicitados").addEventListener("submit", async function (e) {
    e.preventDefault();
    const formData = new FormData(this);
    const fechaDesde = formData.get("fecha_desde");
    const fechaHasta = formData.get("fecha_hasta");
    const tag = formData.get("tag");
    const tipo = formData.get("tipo");
    const limite = formData.get("limite");

    if (!fechaDesde || !fechaHasta || !tag) {
        return Swal.fire({
            icon: "warning",
            title: "Campos requeridos",
            text: "Por favor, completa todos los campos obligatorios.",
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
        text: `Tag: ${tag}${tipo ? ` - Tipo: ${tipo}` : ''} - Desde: ${fechaDesde} - Hasta: ${fechaHasta}`,
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Sí, generar",
        cancelButtonText: "Cancelar",
    });

    if (!result.isConfirmed) return;

    const fileName = `reporte_productos_solicitados_${tag}_${fechaDesde}_${fechaHasta}.pdf`;
    const btn = document.getElementById("btnGenerarSolicitados");

    Swal.fire({
        title: "Generando PDF...",
        text: "Por favor espera un momento",
        didOpen: () => Swal.showLoading(),
        allowOutsideClick: false,
    });

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin group-hover:scale-110 transition-transform"></i><span>Generando...</span>';

    const reportUrl = BASE_PATH + "/api/generarReporteProductosSolicitadosPDF.php";
    fetch(reportUrl, {
            method: "POST",
            body: formData
        })
        .then((res) => {
            console.log('Response status:', res.status);
            console.log('Response headers:', res.headers);
            
            if (!res.ok) {
                return res.text().then(text => {
                    console.error('Error response text:', text);
                    throw new Error(`Error ${res.status}: ${text}`);
                });
            }
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
            btn.innerHTML = '<i class="fas fa-chart-line group-hover:scale-110 transition-transform"></i><span>Generar Reporte Avanzado</span>';
        });
});

// Nuevo manejador para Reporte Stock v2.0
document.getElementById("formReporteStockTagV2").addEventListener("submit", async function (e) {
    e.preventDefault();
    const formData = new FormData(this);
    const tag = formData.get("tag");
    const tipo = formData.get("tipo");

    let confirmText = "Se generará una orden de solicitud profesional";
    if (tag) confirmText += ` para el tag: ${tag}`;
    if (tipo) confirmText += ` (Tipo: ${tipo})`;
    confirmText += ". El documento incluirá logo, folio único, resumen ejecutivo y sección de firmas.";

    const result = await Swal.fire({
        title: "¿Generar Orden de Solicitud?",
        text: confirmText,
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Sí, generar documento",
        cancelButtonText: "Cancelar",
        confirmButtonColor: '#9333ea',
    });

    if (!result.isConfirmed) return;

    const fileName = `orden_solicitud_${tag || 'todos'}_${new Date().toISOString().slice(0,10)}.pdf`;
    const btn = document.getElementById("btnGenerarStockV2");

    Swal.fire({
        title: "Generando Orden de Solicitud Profesional...",
        html: "Por favor espera. Se está creando el documento con formato empresarial.",
        didOpen: () => Swal.showLoading(),
        allowOutsideClick: false,
    });

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin group-hover:scale-110 transition-transform"></i><span>Generando documento...</span>';

    const reportUrl = BASE_PATH + "/api/generarStockPorTagV2PDF.php";
    fetch(reportUrl, {
            method: "POST",
            body: formData
        })
        .then((res) => {
            console.log('Response status:', res.status);
            if (!res.ok) {
                return res.text().then(text => {
                    console.error('Error response text:', text);
                    throw new Error(`Error ${res.status}: ${text}`);
                });
            }
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
                title: "¡Orden de Solicitud Generada!",
                html: "Tu documento profesional se ha descargado correctamente.<br><small>El documento incluye logo, folio y sección de firmas.</small>",
                timer: 4000,
                showConfirmButton: true,
                confirmButtonColor: '#9333ea',
            });
        })
        .catch((err) => {
            console.error("Error al generar Orden de Solicitud:", err);
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Ocurrió un error al generar la orden de solicitud.",
                confirmButtonColor: '#dc2626',
            });
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-file-invoice group-hover:scale-110 transition-transform"></i><span>Generar Orden de Solicitud Profesional</span>';
        });
});
</script>

<style>
@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}

@keyframes pulse-glow {
    0%, 100% { 
        box-shadow: 0 0 20px rgba(59, 130, 246, 0.3); 
    }
    50% { 
        box-shadow: 0 0 30px rgba(59, 130, 246, 0.5), 0 0 40px rgba(59, 130, 246, 0.3); 
    }
}

@media (min-width: 1024px) {
  .hover-float:hover {
      animation: float 2s ease-in-out infinite;
  }

  .glow-on-hover:hover {
      animation: pulse-glow 2s ease-in-out infinite;
  }
}

.gradient-card {
    background: linear-gradient(145deg, #ffffff, #f8fafc);
    border: 1px solid rgba(59, 130, 246, 0.1);
}

input:focus, select:focus {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px -3px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.05) !important;
}

@media (min-width: 768px) {
  input:focus, select:focus {
    box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
  }
}

button:disabled {
    background: linear-gradient(135deg, #9ca3af, #6b7280) !important;
    transform: none !important;
    box-shadow: none !important;
    cursor: not-allowed;
}

.loading-shimmer {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: shimmer 2s infinite;
}

@keyframes shimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}

@media (max-width: 640px) {
    .container {
        padding-left: 1rem;
        padding-right: 1rem;
    }
    
    form {
        padding: 0;
    }
    
    label {
        font-weight: 600;
        color: #374151;
    }
    
    button {
        min-height: 48px;
        font-size: 0.875rem;
    }
    
    .card-body {
        padding: 1rem !important;
    }
    
    input, select {
        min-height: 44px;
        font-size: 16px;
    }
}

@media (min-width: 641px) and (max-width: 768px) {
    .container {
        padding-left: 1.5rem;
        padding-right: 1.5rem;
    }
}

@media (min-width: 769px) {
    .grid {
        gap: 2rem;
    }
}

html {
    scroll-behavior: smooth;
}

@media (min-width: 768px) {
  ::-webkit-scrollbar {
      width: 8px;
  }

  ::-webkit-scrollbar-track {
      background: #f1f5f9;
  }

  ::-webkit-scrollbar-thumb {
      background: linear-gradient(135deg, #64748b, #475569);
      border-radius: 4px;
  }

  ::-webkit-scrollbar-thumb:hover {
      background: linear-gradient(135deg, #475569, #334155);
  }
}

@media (hover: none) and (pointer: coarse) {
    * {
        transition-duration: 0.1s !important;
    }
    
    button, input, select {
        min-height: 48px;
        padding: 0.75rem 1rem;
    }
    
    .hover\:shadow-xl:hover {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
}

@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}

button:focus-visible, input:focus-visible, select:focus-visible {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
}
</style>
