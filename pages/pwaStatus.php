<?php
if(!isset($_SESSION['id'])){
    include "./pages/login.php";
    exit();
}
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-mobile-alt text-primary"></i>
            Estado de la PWA
        </h1>
    </div>

    <!-- PWA Status Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Service Worker
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="sw-status">
                                Verificando...
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-cogs fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Instalación PWA
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="pwa-install-status">
                                Verificando...
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-download fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Estado de Conexión
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="connection-status">
                                <i class="fas fa-wifi"></i> Online
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-globe fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Cache Size
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="cache-size">
                                Calculando...
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-database fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PWA Actions -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Acciones PWA</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <button class="btn btn-primary btn-block" onclick="installPWA()" id="install-btn" disabled>
                            <i class="fas fa-download"></i> Instalar como App
                        </button>
                    </div>
                    <div class="mb-3">
                        <button class="btn btn-success btn-block" onclick="updateCache()">
                            <i class="fas fa-sync"></i> Actualizar Cache
                        </button>
                    </div>
                    <div class="mb-3">
                        <button class="btn btn-info btn-block" onclick="checkUpdates()">
                            <i class="fas fa-search"></i> Buscar Actualizaciones
                        </button>
                    </div>
                    <div class="mb-3">
                        <button class="btn btn-danger btn-block" onclick="clearCache()">
                            <i class="fas fa-trash"></i> Limpiar Cache
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">Información del Dispositivo</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <tbody id="device-info">
                                <tr>
                                    <td><strong>Navegador:</strong></td>
                                    <td id="browser-info">Detectando...</td>
                                </tr>
                                <tr>
                                    <td><strong>Plataforma:</strong></td>
                                    <td id="platform-info">Detectando...</td>
                                </tr>
                                <tr>
                                    <td><strong>Pantalla:</strong></td>
                                    <td id="screen-info">Detectando...</td>
                                </tr>
                                <tr>
                                    <td><strong>Modo Display:</strong></td>
                                    <td id="display-mode">Detectando...</td>
                                </tr>
                                <tr>
                                    <td><strong>Orientación:</strong></td>
                                    <td id="orientation-info">Detectando...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cache Details -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">Detalles del Cache</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="cache-table">
                            <thead>
                                <tr>
                                    <th>URL</th>
                                    <th>Tipo</th>
                                    <th>Tamaño</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody id="cache-details">
                                <tr>
                                    <td colspan="4" class="text-center">Cargando información del cache...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
class PWAStatus {
    constructor() {
        this.init();
    }

    async init() {
        await this.checkServiceWorker();
        this.checkInstallability();
        this.updateDeviceInfo();
        this.updateConnectionStatus();
        this.getCacheInfo();
        this.setupEventListeners();
    }

    async checkServiceWorker() {
        const statusElement = document.getElementById('sw-status');
        
        if ('serviceWorker' in navigator) {
            try {
                const registration = await navigator.serviceWorker.getRegistration();
                if (registration) {
                    statusElement.innerHTML = '<i class="fas fa-check text-success"></i> Activo';
                    statusElement.className = 'h5 mb-0 font-weight-bold text-success';
                } else {
                    statusElement.innerHTML = '<i class="fas fa-times text-danger"></i> No registrado';
                    statusElement.className = 'h5 mb-0 font-weight-bold text-danger';
                }
            } catch (error) {
                statusElement.innerHTML = '<i class="fas fa-exclamation text-warning"></i> Error';
                statusElement.className = 'h5 mb-0 font-weight-bold text-warning';
            }
        } else {
            statusElement.innerHTML = '<i class="fas fa-times text-danger"></i> No soportado';
            statusElement.className = 'h5 mb-0 font-weight-bold text-danger';
        }
    }

    checkInstallability() {
        const statusElement = document.getElementById('pwa-install-status');
        const installBtn = document.getElementById('install-btn');
        
        if (this.isStandalone()) {
            statusElement.innerHTML = '<i class="fas fa-check text-success"></i> Instalada';
            statusElement.className = 'h5 mb-0 font-weight-bold text-success';
            installBtn.textContent = 'Ya instalada';
        } else if (window.pwaManager && window.pwaManager.deferredPrompt) {
            statusElement.innerHTML = '<i class="fas fa-download text-info"></i> Disponible';
            statusElement.className = 'h5 mb-0 font-weight-bold text-info';
            installBtn.disabled = false;
        } else {
            statusElement.innerHTML = '<i class="fas fa-times text-warning"></i> No disponible';
            statusElement.className = 'h5 mb-0 font-weight-bold text-warning';
        }
    }

    updateDeviceInfo() {
        const userAgent = navigator.userAgent;
        
        // Browser detection
        let browser = 'Desconocido';
        if (userAgent.includes('Chrome')) browser = 'Chrome';
        else if (userAgent.includes('Firefox')) browser = 'Firefox';
        else if (userAgent.includes('Safari')) browser = 'Safari';
        else if (userAgent.includes('Edge')) browser = 'Edge';
        
        document.getElementById('browser-info').textContent = browser;
        
        // Platform detection
        let platform = 'Desconocido';
        if (userAgent.includes('Android')) platform = 'Android';
        else if (userAgent.includes('iPhone')) platform = 'iOS';
        else if (userAgent.includes('Windows')) platform = 'Windows';
        else if (userAgent.includes('Mac')) platform = 'macOS';
        else if (userAgent.includes('Linux')) platform = 'Linux';
        
        document.getElementById('platform-info').textContent = platform;
        
        // Screen info
        document.getElementById('screen-info').textContent = 
            `${screen.width}x${screen.height} (${window.devicePixelRatio}x)`;
        
        // Display mode
        const displayMode = this.isStandalone() ? 'Standalone' : 'Browser';
        document.getElementById('display-mode').textContent = displayMode;
        
        // Orientation
        document.getElementById('orientation-info').textContent = 
            screen.orientation ? screen.orientation.type : 'Desconocido';
    }

    updateConnectionStatus() {
        const statusElement = document.getElementById('connection-status');
        
        const updateStatus = () => {
            if (navigator.onLine) {
                statusElement.innerHTML = '<i class="fas fa-wifi text-success"></i> Online';
            } else {
                statusElement.innerHTML = '<i class="fas fa-wifi-slash text-danger"></i> Offline';
            }
        };
        
        updateStatus();
        window.addEventListener('online', updateStatus);
        window.addEventListener('offline', updateStatus);
    }

    async getCacheInfo() {
        if ('caches' in window) {
            try {
                const cacheNames = await caches.keys();
                let totalSize = 0;
                const cacheDetails = [];
                
                for (const cacheName of cacheNames) {
                    const cache = await caches.open(cacheName);
                    const requests = await cache.keys();
                    
                    for (const request of requests) {
                        try {
                            const response = await cache.match(request);
                            if (response) {
                                const blob = await response.blob();
                                totalSize += blob.size;
                                
                                cacheDetails.push({
                                    url: request.url,
                                    type: response.type,
                                    size: this.formatBytes(blob.size),
                                    date: new Date(response.headers.get('date') || Date.now()).toLocaleDateString()
                                });
                            }
                        } catch (error) {
                            console.error('Error reading cache entry:', error);
                        }
                    }
                }
                
                document.getElementById('cache-size').textContent = this.formatBytes(totalSize);
                this.populateCacheTable(cacheDetails);
                
            } catch (error) {
                document.getElementById('cache-size').textContent = 'Error';
                console.error('Error getting cache info:', error);
            }
        } else {
            document.getElementById('cache-size').textContent = 'No soportado';
        }
    }

    populateCacheTable(cacheDetails) {
        const tbody = document.getElementById('cache-details');
        
        if (cacheDetails.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center">No hay elementos en cache</td></tr>';
            return;
        }
        
        tbody.innerHTML = cacheDetails.map(item => `
            <tr>
                <td class="text-truncate" style="max-width: 300px;" title="${item.url}">
                    ${item.url.replace(window.location.origin, '')}
                </td>
                <td><span class="badge badge-info">${item.type}</span></td>
                <td>${item.size}</td>
                <td>${item.date}</td>
            </tr>
        `).join('');
    }

    formatBytes(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }

    isStandalone() {
        return window.matchMedia('(display-mode: standalone)').matches ||
               window.navigator.standalone === true;
    }

    setupEventListeners() {
        // Actualizar orientación cuando cambie
        window.addEventListener('orientationchange', () => {
            setTimeout(() => {
                document.getElementById('orientation-info').textContent = 
                    screen.orientation ? screen.orientation.type : 'Desconocido';
            }, 100);
        });
    }
}

// Funciones globales para los botones
function installPWA() {
    if (window.pwaManager && window.pwaManager.installPWA) {
        window.pwaManager.installPWA();
    }
}

async function updateCache() {
    if ('serviceWorker' in navigator) {
        const registration = await navigator.serviceWorker.getRegistration();
        if (registration) {
            registration.update();
            alert('✅ Cache actualizado correctamente');
            location.reload();
        }
    }
}

async function checkUpdates() {
    if ('serviceWorker' in navigator) {
        const registration = await navigator.serviceWorker.getRegistration();
        if (registration) {
            await registration.update();
            alert('🔍 Búsqueda de actualizaciones completada');
        }
    }
}

async function clearCache() {
    if (confirm('⚠️ ¿Estás seguro de que quieres limpiar todo el cache?')) {
        if ('caches' in window) {
            const cacheNames = await caches.keys();
            await Promise.all(cacheNames.map(name => caches.delete(name)));
            alert('🗑️ Cache limpiado correctamente');
            location.reload();
        }
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    new PWAStatus();
});
</script>
