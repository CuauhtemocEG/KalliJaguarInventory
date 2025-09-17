<div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50 py-8">
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

    <div id="scanner-section" class="bg-white rounded-2xl shadow-xl overflow-hidden">
      <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
        <div class="flex items-center justify-between">
          <h2 class="text-xl font-semibold text-white flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            Cámara del Scanner
          </h2>
          <div class="flex items-center space-x-2">
            <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
            <span class="text-white text-sm">En línea</span>
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

    <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
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
  const scanner = new Html5Qrcode("reader");
  const config = {
    fps: 10,
    qrbox: {
      width: 250,
      height: 250
    }
  };

  const resultadoDisplay = document.getElementById("resultado-display");
  const statusIndicator = document.getElementById("status-indicator");
  const statusText = document.getElementById("status-text");

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
    
    if (scanner && scanner.getState() === Html5QrcodeScannerState.SCANNING) {
      scanner.stop().then(() => {
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
        ).catch(err => {
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

  const input = document.getElementById("lector-input");
  input.focus();
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
    if (e.target !== input) {
      setTimeout(() => input.focus(), 100);
    }
  });

  document.addEventListener('DOMContentLoaded', () => {
    iniciarCamara();
  });

  window.addEventListener('focus', () => {
    input.focus();
  });
</script>
