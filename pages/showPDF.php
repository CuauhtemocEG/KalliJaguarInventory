<?php
$comandaID = $_GET['ComandaID'];
$pathPDF = './documents/' . $comandaID . '.pdf';

if (file_exists($pathPDF)) {
    echo '
    <div class="min-h-screen bg-white">
        <!-- Header profesional -->
        <div class="bg-white shadow-lg border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-14 h-14 bg-gradient-to-r from-red-600 to-pink-600 rounded-xl flex items-center justify-center shadow-lg">
                                <i class="fas fa-file-pdf text-white text-2xl"></i>
                            </div>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">
                                Documento PDF
                            </h1>
                            <div class="flex items-center mt-2">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-800">
                                    <i class="fas fa-hashtag mr-1 text-xs"></i>
                                    ' . htmlspecialchars($comandaID) . '
                                </span>
                                <span class="ml-3 text-sm text-gray-500">
                                    <i class="fas fa-calendar-alt mr-1"></i>
                                    ' . date('d/m/Y H:i') . '
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Botones de acción -->
                    <div class="flex items-center space-x-3">
                        <button onclick="window.print()" 
                                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-medium rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                            <i class="fas fa-print mr-2"></i>
                            Imprimir
                        </button>
                        
                        <a href="' . $pathPDF . '" download="' . htmlspecialchars($comandaID) . '.pdf"
                           class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-medium rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                            <i class="fas fa-download mr-2"></i>
                            Descargar
                        </a>
                        
                        <button onclick="history.back()" 
                                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white font-medium rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Regresar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenido principal -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden">
                
                <!-- Toolbar del PDF -->
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="flex items-center text-sm text-gray-700">
                                <i class="fas fa-file-alt mr-2 text-gray-500"></i>
                                <span class="font-medium">Visualizador de PDF</span>
                            </div>
                            
                            <div class="hidden sm:flex items-center space-x-4">
                                <button onclick="zoomOut()" 
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium text-gray-700 hover:text-blue-600 transition-colors">
                                    <i class="fas fa-search-minus mr-1"></i>
                                    Reducir
                                </button>
                                <span class="text-xs text-gray-500">Zoom</span>
                                <button onclick="zoomIn()" 
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium text-gray-700 hover:text-blue-600 transition-colors">
                                    <i class="fas fa-search-plus mr-1"></i>
                                    Ampliar
                                </button>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            <div class="flex items-center text-xs text-gray-500">
                                <i class="fas fa-info-circle mr-1"></i>
                                <span class="hidden sm:inline">Usa Ctrl+Rueda para zoom</span>
                                <span class="sm:hidden">Pellizca para zoom</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contenedor del PDF -->
                <div class="relative">
                    <!-- Loading overlay -->
                    <div id="pdf-loading" class="absolute inset-0 bg-white flex items-center justify-center z-10">
                        <div class="text-center">
                            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-4">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                            </div>
                            <p class="text-gray-700 font-medium">Cargando documento PDF...</p>
                            <p class="text-sm text-gray-500 mt-1">Por favor espera un momento</p>
                        </div>
                    </div>
                    
                    <!-- Visor PDF -->
                    <div id="pdf-container" class="relative bg-white" style="height: 80vh;">
                        <iframe id="pdf-frame"
                                src="' . $pathPDF . '#toolbar=1&navpanes=1&scrollbar=1" 
                                class="w-full h-full border-0 rounded-b-2xl"
                                onload="hidePdfLoading()"
                                title="Documento PDF - ' . htmlspecialchars($comandaID) . '">
                        </iframe>
                    </div>
                </div>
                
                <!-- Footer informativo -->
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                    <div class="flex flex-col sm:flex-row items-center justify-between space-y-2 sm:space-y-0">
                        <div class="flex items-center text-sm text-gray-700">
                            <i class="fas fa-shield-alt mr-2 text-green-500"></i>
                            <span>Documento verificado y seguro</span>
                        </div>
                        
                        <div class="flex items-center space-x-4 text-xs text-gray-600">
                            <div class="flex items-center">
                                <i class="fas fa-file-pdf mr-1 text-red-500"></i>
                                <span>Formato PDF</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-eye mr-1 text-blue-500"></i>
                                <span>Solo lectura</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Estilos adicionales para mejor experiencia */
        @media print {
            .no-print { display: none !important; }
            #pdf-container { height: auto !important; }
        }
        
        /* Animaciones suaves */
        .transition-all {
            transition-property: all;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 150ms;
        }
        
        /* Hover effects mejorados */
        button:hover, a:hover {
            transform: translateY(-1px);
        }
        
        /* Responsive iframe */
        #pdf-container iframe {
            transition: opacity 0.3s ease;
        }
        
        @media (max-width: 640px) {
            #pdf-container {
                height: 70vh !important;
            }
        }
    </style>

    <script>
        // Función para ocultar loading
        function hidePdfLoading() {
            setTimeout(() => {
                const loading = document.getElementById("pdf-loading");
                if (loading) {
                    loading.style.opacity = "0";
                    loading.style.transition = "opacity 0.5s ease";
                    setTimeout(() => {
                        loading.style.display = "none";
                    }, 500);
                }
            }, 1000);
        }

        // Funciones de zoom (si el navegador las soporta)
        function zoomIn() {
            const iframe = document.getElementById("pdf-frame");
            if (iframe && iframe.contentWindow) {
                try {
                    iframe.contentWindow.postMessage({action: "zoom", direction: "in"}, "*");
                } catch(e) {
                    console.log("Zoom no disponible en este navegador");
                }
            }
        }

        function zoomOut() {
            const iframe = document.getElementById("pdf-frame");
            if (iframe && iframe.contentWindow) {
                try {
                    iframe.contentWindow.postMessage({action: "zoom", direction: "out"}, "*");
                } catch(e) {
                    console.log("Zoom no disponible en este navegador");
                }
            }
        }

        // Mejorar experiencia táctil en móviles
        document.addEventListener("DOMContentLoaded", function() {
            const pdfContainer = document.getElementById("pdf-container");
            if (pdfContainer) {
                pdfContainer.addEventListener("touchstart", function() {
                    pdfContainer.style.overflow = "auto";
                });
            }
        });

        // Keyboard shortcuts
        document.addEventListener("keydown", function(e) {
            if (e.ctrlKey && e.key === "p") {
                e.preventDefault();
                window.print();
            }
            if (e.key === "Escape") {
                history.back();
            }
        });
    </script>';
} else {
    echo '
    <div class="min-h-screen bg-white">
        <!-- Header con error -->
        <div class="bg-white shadow-lg border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                        <div class="w-14 h-14 bg-gradient-to-r from-red-600 to-red-700 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-exclamation-triangle text-white text-2xl"></i>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">
                            Documento No Encontrado
                        </h1>
                        <p class="text-sm text-gray-500 mt-1">
                            El archivo PDF solicitado no está disponible
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenido de error -->
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="text-center">
                <div class="bg-white rounded-2xl shadow-xl p-12 border border-gray-200">
                    <div class="w-24 h-24 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-file-excel text-red-600 text-3xl"></i>
                    </div>
                    
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">
                        Archivo PDF No Encontrado
                    </h2>
                    
                    <div class="max-w-md mx-auto">
                        <p class="text-gray-700 mb-6">
                            El documento PDF con ID <strong>' . htmlspecialchars($comandaID) . '</strong> no existe o ha sido movido.
                        </p>
                        
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-8">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-lightbulb text-yellow-600"></i>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-semibold text-yellow-800">
                                        Posibles soluciones:
                                    </h3>
                                    <ul class="mt-2 text-sm text-yellow-700 space-y-1">
                                        <li>• Verificar que el documento se haya generado correctamente</li>
                                        <li>• Contactar al administrador del sistema</li>
                                        <li>• Intentar generar el documento nuevamente</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row items-center justify-center space-y-3 sm:space-y-0 sm:space-x-4">
                        <button onclick="history.back()" 
                                class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-medium rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Regresar
                        </button>
                        
                        <button onclick="location.reload()" 
                                class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white font-medium rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                            <i class="fas fa-redo mr-2"></i>
                            Reintentar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>';
}
?>
