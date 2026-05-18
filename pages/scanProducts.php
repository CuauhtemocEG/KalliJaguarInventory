<div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50 py-8">
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

    <!-- Pantalla de selección de método -->
    <div id="method-selection" class="bg-white rounded-2xl shadow-xl overflow-hidden mb-8">
      <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 py-8 text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-full mb-4">
          <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
          </svg>
        </div>
        <h1 class="text-3xl font-bold text-white mb-2">Actualizar Productos</h1>
        <p class="text-lg text-indigo-100">¿Cómo deseas ingresar el producto?</p>
      </div>

      <div class="p-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Opción: Scanner/Cámara -->
          <button id="btn-usar-scanner" class="group bg-gradient-to-br from-blue-50 to-blue-100 hover:from-blue-100 hover:to-blue-200 border-2 border-blue-300 hover:border-blue-500 rounded-xl p-8 transition-all duration-300 transform hover:scale-105 hover:shadow-xl">
            <div class="flex flex-col items-center text-center">
              <div class="w-20 h-20 bg-blue-600 group-hover:bg-blue-700 rounded-full flex items-center justify-center mb-4 transition-colors">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
              </div>
              <h3 class="text-xl font-bold text-gray-900 mb-2">Escanear Código</h3>
              <p class="text-sm text-gray-600 mb-3">Usa la cámara o un scanner USB para leer códigos de barras</p>
              <div class="flex items-center space-x-2 text-xs text-blue-600 font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                <span>Rápido y preciso</span>
              </div>
            </div>
          </button>

          <!-- Opción: Búsqueda Manual -->
          <button id="btn-buscar-manual" class="group bg-gradient-to-br from-purple-50 to-purple-100 hover:from-purple-100 hover:to-purple-200 border-2 border-purple-300 hover:border-purple-500 rounded-xl p-8 transition-all duration-300 transform hover:scale-105 hover:shadow-xl">
            <div class="flex flex-col items-center text-center">
              <div class="w-20 h-20 bg-purple-600 group-hover:bg-purple-700 rounded-full flex items-center justify-center mb-4 transition-colors">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
              </div>
              <h3 class="text-xl font-bold text-gray-900 mb-2">Buscar Producto</h3>
              <p class="text-sm text-gray-600 mb-3">Busca productos por nombre o código manualmente</p>
              <div class="flex items-center space-x-2 text-xs text-purple-600 font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                <span>Sin scanner necesario</span>
              </div>
            </div>
          </button>
        </div>

        <div class="mt-6 text-center">
          <p class="text-sm text-gray-500">
            <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Puedes cambiar de método en cualquier momento
          </p>
        </div>
      </div>
    </div>

    <div id="scanner-section" class="bg-white rounded-2xl shadow-xl overflow-hidden hidden">
      <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
        <div class="flex items-center justify-between">
          <h2 class="text-xl font-semibold text-white flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            Cámara del Scanner
          </h2>
          <div class="flex items-center space-x-4">
            <button id="btn-volver-scanner" type="button" class="text-white hover:text-blue-100 transition-colors flex items-center text-sm">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
              </svg>
              Volver
            </button>
            <div class="flex items-center space-x-2">
              <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
              <span class="text-white text-sm">En línea</span>
            </div>
          </div>
        </div>
      </div>

      <div class="p-6">
        <div class="mb-6">
          <div id="reader" class="relative bg-gray-900 rounded-xl overflow-hidden shadow-inner border-2 border-gray-200"></div>
          <p class="text-sm text-gray-500 mt-2 text-center">
            <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Apunta la cámara hacia el código de barras
          </p>
        </div>

        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
          <div class="flex items-center justify-between mb-2">
            <label class="text-sm font-medium text-gray-700">Código detectado:</label>
            <div class="flex items-center">
              <div id="status-indicator" class="w-2 h-2 bg-gray-400 rounded-full mr-2"></div>
              <span id="status-text" class="text-xs text-gray-500">Esperando código...</span>
            </div>
          </div>
          <div class="relative">
            <input type="text" id="resultado-display" readonly 
                   class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg font-mono text-lg text-center text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                   placeholder="Esperando código de barras...">
            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
              <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Buscador de Productos -->
    <div id="search-section" class="mt-8 bg-white rounded-2xl shadow-xl overflow-hidden hidden">
      <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-4">
        <div class="flex items-center justify-between">
          <h2 class="text-xl font-semibold text-white flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            Buscar Producto Manualmente
          </h2>
          <button id="btn-volver-inicio" type="button" class="text-white hover:text-purple-100 transition-colors flex items-center text-sm">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Volver
          </button>
        </div>
      </div>

      <div class="p-6">
        <div class="mb-4">
          <label for="search-input" class="block text-sm font-medium text-gray-700 mb-2">
            Buscar por nombre o código
          </label>
          <div class="relative">
            <input 
              type="text" 
              id="search-input" 
              class="w-full px-4 py-3 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-lg" 
              placeholder="Escribe el nombre del producto...">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
            </div>
            <div id="search-loading" class="absolute inset-y-0 right-0 pr-3 flex items-center hidden">
              <svg class="animate-spin h-5 w-5 text-purple-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </div>
          </div>
        </div>

        <!-- Resultados de búsqueda -->
        <div id="search-results" class="space-y-2 max-h-96 overflow-y-auto">
          <div id="search-empty" class="text-center py-8 text-gray-500">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
            </svg>
            <p>Escribe para buscar productos</p>
          </div>
        </div>
      </div>
    </div>

    <div id="info-section" class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6 hidden">
      <div class="bg-white rounded-xl shadow-md p-6 border border-gray-200">
        <div class="flex items-center mb-4">
          <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
            </svg>
          </div>
          <h3 class="text-lg font-semibold text-gray-900">Usar Cámara</h3>
        </div>
        <ul class="space-y-2 text-sm text-gray-600">
          <li class="flex items-start">
            <span class="w-2 h-2 bg-blue-500 rounded-full mt-2 mr-3 flex-shrink-0"></span>
            Permite el acceso a la cámara cuando se solicite
          </li>
          <li class="flex items-start">
            <span class="w-2 h-2 bg-blue-500 rounded-full mt-2 mr-3 flex-shrink-0"></span>
            Apunta la cámara hacia el código de barras
          </li>
          <li class="flex items-start">
            <span class="w-2 h-2 bg-blue-500 rounded-full mt-2 mr-3 flex-shrink-0"></span>
            Mantén el código centrado en el área de escaneado
          </li>
        </ul>
      </div>

      <div class="bg-white rounded-xl shadow-md p-6 border border-gray-200">
        <div class="flex items-center mb-4">
          <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
          </div>
          <h3 class="text-lg font-semibold text-gray-900">Scanner Manual</h3>
        </div>
        <ul class="space-y-2 text-sm text-gray-600">
          <li class="flex items-start">
            <span class="w-2 h-2 bg-green-500 rounded-full mt-2 mr-3 flex-shrink-0"></span>
            Usa un scanner de pistola USB
          </li>
          <li class="flex items-start">
            <span class="w-2 h-2 bg-green-500 rounded-full mt-2 mr-3 flex-shrink-0"></span>
            El código se detectará automáticamente
          </li>
          <li class="flex items-start">
            <span class="w-2 h-2 bg-green-500 rounded-full mt-2 mr-3 flex-shrink-0"></span>
            Presiona Enter después de escanear
          </li>
        </ul>
      </div>
    </div>

    <input type="text" id="lector-input" class="sr-only" autofocus />
  </div>
</div>

<style>
  #reader {
    width: 100%;
    max-width: 500px;
    min-height: 350px;
    margin: 0 auto;
    border-radius: 12px;
  }

  #reader video {
    border-radius: 12px;
  }

  @keyframes pulse-blue {
    0%, 100% {
      opacity: 1;
    }
    50% {
      opacity: 0.7;
    }
  }

  .animate-pulse-blue {
    animation: pulse-blue 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
  }

  #reader__scan_region {
    position: relative;
  }

  #reader__scan_region::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 250px;
    height: 250px;
    border: 2px solid #3B82F6;
    border-radius: 12px;
    background: rgba(59, 130, 246, 0.1);
    animation: pulse 2s infinite;
    z-index: 10;
    pointer-events: none;
  }
</style>

<script src="https://unpkg.com/html5-qrcode"></script>
<script>
  // Variables globales
  let scanner = null;
  let scannerIniciado = false;
  
  const methodSelection = document.getElementById('method-selection');
  const scannerSection = document.getElementById('scanner-section');
  const searchSection = document.getElementById('search-section');
  const infoSection = document.getElementById('info-section');
  const btnUsarScanner = document.getElementById('btn-usar-scanner');
  const btnBuscarManual = document.getElementById('btn-buscar-manual');
  const btnVolverInicio = document.getElementById('btn-volver-inicio');
  const btnVolverScanner = document.getElementById('btn-volver-scanner');
  
  const resultadoDisplay = document.getElementById("resultado-display");
  const statusIndicator = document.getElementById("status-indicator");
  const statusText = document.getElementById("status-text");
  const input = document.getElementById("lector-input");

  // Función para mostrar la sección de scanner
  btnUsarScanner.addEventListener('click', () => {
    methodSelection.classList.add('hidden');
    scannerSection.classList.remove('hidden');
    infoSection.classList.remove('hidden');
    
    // Iniciar cámara y focus en input
    setTimeout(() => {
      iniciarCamara();
      input.focus();
    }, 300);
  });

  // Función para mostrar la sección de búsqueda
  btnBuscarManual.addEventListener('click', () => {
    methodSelection.classList.add('hidden');
    searchSection.classList.remove('hidden');
    
    // Focus en el input de búsqueda
    setTimeout(() => {
      document.getElementById('search-input').focus();
    }, 300);
  });

  // Función para volver al inicio desde búsqueda
  btnVolverInicio.addEventListener('click', () => {
    searchSection.classList.add('hidden');
    methodSelection.classList.remove('hidden');
    
    // Limpiar búsqueda
    document.getElementById('search-input').value = '';
    document.getElementById('search-results').innerHTML = '<div id="search-empty" class="text-center py-8 text-gray-500"><svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg><p>Escribe para buscar productos</p></div>';
  });

  // Función para volver al inicio desde scanner
  btnVolverScanner.addEventListener('click', () => {
    // Detener la cámara si está activa
    if (scanner && scannerIniciado) {
      scanner.stop().then(() => {
        scannerIniciado = false;
        volverAInicio();
      }).catch(err => {
        console.error('Error al detener el scanner:', err);
        volverAInicio();
      });
    } else {
      volverAInicio();
    }
  });

  function volverAInicio() {
    scannerSection.classList.add('hidden');
    infoSection.classList.add('hidden');
    methodSelection.classList.remove('hidden');
    
    // Limpiar campos
    resultadoDisplay.value = '';
    resultadoDisplay.className = 'w-full px-4 py-3 bg-white border border-gray-300 rounded-lg font-mono text-lg text-center text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500';
    actualizarEstado('idle', 'Esperando código...');
  }

  function actualizarEstado(estado, mensaje) {
    switch(estado) {
      case 'success':
        statusIndicator.className = 'w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse';
        statusText.textContent = mensaje;
        statusText.className = 'text-xs text-green-600 font-medium';
        break;
      case 'scanning':
        statusIndicator.className = 'w-2 h-2 bg-blue-500 rounded-full mr-2 animate-pulse';
        statusText.textContent = mensaje;
        statusText.className = 'text-xs text-blue-600';
        break;
      case 'error':
        statusIndicator.className = 'w-2 h-2 bg-red-500 rounded-full mr-2';
        statusText.textContent = mensaje;
        statusText.className = 'text-xs text-red-600';
        break;
      default:
        statusIndicator.className = 'w-2 h-2 bg-gray-400 rounded-full mr-2';
        statusText.textContent = mensaje;
        statusText.className = 'text-xs text-gray-500';
    }
  }

  function procesarCodigo(code) {
    resultadoDisplay.value = code;
    actualizarEstado('success', 'Código detectado correctamente');
    
    resultadoDisplay.className += ' ring-2 ring-green-500 border-green-500';
    
    if (scanner && scannerIniciado) {
      scanner.stop().then(() => {
        scannerIniciado = false;
        setTimeout(() => {
          window.location.href = 'index.php?page=updateStockProduct&codigo=' + encodeURIComponent(code);
        }, 1500);
      }).catch(err => {
        console.error('Error al detener el scanner:', err);
        setTimeout(() => {
          window.location.href = 'index.php?page=updateStockProduct&codigo=' + encodeURIComponent(code);
        }, 1500);
      });
    } else {
      setTimeout(() => {
        window.location.href = 'index.php?page=updateStockProduct&codigo=' + encodeURIComponent(code);
      }, 1500);
    }
  }

  function iniciarCamara() {
    if (scannerIniciado) return;
    
    scanner = new Html5Qrcode("reader");
    const config = {
      fps: 10,
      qrbox: {
        width: 250,
        height: 250
      }
    };
    
    actualizarEstado('scanning', 'Iniciando cámara...');
    
    Html5Qrcode.getCameras().then(cameras => {
      if (cameras && cameras.length) {
        actualizarEstado('scanning', 'Buscando códigos...');
        
        scanner.start(
          { facingMode: "environment" }, 
          config, 
          (decodedText) => {
            procesarCodigo(decodedText);
          },
          (errorMessage) => {
          }
        ).then(() => {
          scannerIniciado = true;
        }).catch(err => {
          console.error('Error al iniciar la cámara:', err);
          actualizarEstado('error', 'Error al acceder a la cámara');
        });
      } else {
        actualizarEstado('error', 'No se encontró cámara');
        alert("No se encontró cámara disponible.");
      }
    }).catch(err => {
      console.error('Error al obtener cámaras:', err);
      actualizarEstado('error', 'Error al acceder a la cámara');
    });
  }

  // Scanner USB
  let buffer = "";
  let bufferTimeout;

  input.addEventListener("input", (e) => {
    buffer = e.target.value;
    
    if (bufferTimeout) {
      clearTimeout(bufferTimeout);
    }
    
    bufferTimeout = setTimeout(() => {
      if (buffer.trim().length > 3) {
        procesarCodigo(buffer.trim());
        buffer = "";
        input.value = "";
      }
    }, 300);
  });

  input.addEventListener("keydown", (e) => {
    if (e.key === "Enter" && buffer.trim().length > 0) {
      e.preventDefault();
      if (bufferTimeout) {
        clearTimeout(bufferTimeout);
      }
      procesarCodigo(buffer.trim());
      buffer = "";
      input.value = "";
    }
  });

  document.addEventListener("click", (e) => {
    // Solo poner foco en el input del scanner si la sección del scanner está visible
    if (!scannerSection.classList.contains('hidden') && e.target !== input) {
      setTimeout(() => input.focus(), 100);
    }
  });

  // Funcionalidad del buscador de productos
  const searchInput = document.getElementById('search-input');
  const searchResults = document.getElementById('search-results');
  const searchLoading = document.getElementById('search-loading');
  let searchTimeout;

  // Función para buscar productos
  function buscarProductos(query) {
    if (query.length < 2) {
      searchResults.innerHTML = '<div id="search-empty" class="text-center py-8 text-gray-500"><svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg><p>Escribe al menos 2 caracteres</p></div>';
      return;
    }

    searchLoading.classList.remove('hidden');

    fetch(`api/searchProducts.php?q=${encodeURIComponent(query)}`)
      .then(res => res.json())
      .then(data => {
        searchLoading.classList.add('hidden');
        
        if (data.status === 'ok' && data.productos.length > 0) {
          let html = '';
          
          data.productos.forEach(producto => {
            const precioConIVA = (parseFloat(producto.PrecioUnitario) * 1.16).toFixed(2);
            const stockLabel = producto.Tipo === 'Pesable' 
              ? `${parseFloat(producto.Cantidad).toFixed(3)} Kg`
              : `${parseInt(producto.Cantidad)} unidades`;
            
            const stockColor = producto.Cantidad > 10 ? 'text-green-600' : (producto.Cantidad > 0 ? 'text-yellow-600' : 'text-red-600');
            
            html += `
              <div class="producto-item bg-gray-50 hover:bg-purple-50 border border-gray-200 hover:border-purple-300 rounded-lg p-4 cursor-pointer transition-all" data-upc="${producto.UPC}">
                <div class="flex items-center">
                  <div class="flex-shrink-0 w-12 h-12 bg-white rounded-lg border border-gray-200 flex items-center justify-center overflow-hidden">
                    ${producto.Imagen ? `<img src="../img/producto/${producto.Imagen}" alt="${producto.Nombre}" class="w-full h-full object-cover">` : `<svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>`}
                  </div>
                  <div class="ml-4 flex-1">
                    <div class="flex items-start justify-between">
                      <div>
                        <h4 class="text-sm font-semibold text-gray-900">${producto.Nombre}</h4>
                        <p class="text-xs text-gray-500 font-mono mt-1">UPC: ${producto.UPC}</p>
                      </div>
                      <div class="text-right ml-4">
                        <p class="text-sm font-bold text-gray-900">$${precioConIVA}</p>
                        <p class="text-xs ${stockColor} font-medium">${stockLabel}</p>
                      </div>
                    </div>
                    <div class="mt-2 flex items-center space-x-2">
                      <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${producto.Tipo === 'Pesable' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'}">
                        ${producto.Tipo}
                      </span>
                    </div>
                  </div>
                  <div class="ml-4">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                  </div>
                </div>
              </div>
            `;
          });
          
          searchResults.innerHTML = html;
          
          // Agregar event listeners a los productos
          document.querySelectorAll('.producto-item').forEach(item => {
            item.addEventListener('click', () => {
              const upc = item.getAttribute('data-upc');
              window.location.href = 'index.php?page=updateStockProduct&codigo=' + encodeURIComponent(upc);
            });
          });
          
        } else {
          searchResults.innerHTML = `
            <div class="text-center py-8 text-gray-500">
              <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <p>No se encontraron productos</p>
            </div>
          `;
        }
      })
      .catch(err => {
        console.error('Error al buscar productos:', err);
        searchLoading.classList.add('hidden');
        searchResults.innerHTML = `
          <div class="text-center py-8 text-red-500">
            <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p>Error al buscar productos</p>
          </div>
        `;
      });
  }

  // Event listener para el input de búsqueda con debounce
  searchInput.addEventListener('input', (e) => {
    if (searchTimeout) {
      clearTimeout(searchTimeout);
    }
    
    const query = e.target.value.trim();
    
    if (query.length === 0) {
      searchResults.innerHTML = '<div id="search-empty" class="text-center py-8 text-gray-500"><svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg><p>Escribe para buscar productos</p></div>';
      return;
    }
    
    searchTimeout = setTimeout(() => {
      buscarProductos(query);
    }, 500);
  });

</script>