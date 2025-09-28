// PWA Service Worker Registration y funcionalidades
class PWAManager {
    constructor() {
        this.deferredPrompt = null;
        this.isOnline = navigator.onLine;
        this.init();
    }

    async init() {
        // Registrar Service Worker
        await this.registerServiceWorker();
        
        // Configurar evento de instalación PWA
        this.setupInstallPrompt();
        
        // Configurar eventos online/offline
        this.setupConnectionEvents();
        
        // Mostrar botón de instalación si es apropiado
        this.showInstallButton();
        
        // Verificar actualizaciones
        this.checkForUpdates();
    }

    async registerServiceWorker() {
        if ('serviceWorker' in navigator) {
            try {
                const registration = await navigator.serviceWorker.register('/sw.js', {
                    scope: '/'
                });

                console.log('✅ Service Worker registrado correctamente:', registration.scope);

                // Escuchar actualizaciones del Service Worker
                registration.addEventListener('updatefound', () => {
                    const newWorker = registration.installing;
                    console.log('🔄 Nueva versión del Service Worker detectada');
                    
                    newWorker.addEventListener('statechange', () => {
                        if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                            this.showUpdateNotification();
                        }
                    });
                });

            } catch (error) {
                console.error('❌ Error registrando Service Worker:', error);
            }
        } else {
            console.log('⚠️ Service Workers no soportados en este navegador');
        }
    }

    setupInstallPrompt() {
        window.addEventListener('beforeinstallprompt', (e) => {
            console.log('💾 PWA instalable detectada');
            e.preventDefault();
            this.deferredPrompt = e;
            this.showInstallButton();
        });

        window.addEventListener('appinstalled', () => {
            console.log('✅ PWA instalada correctamente');
            this.hideInstallButton();
            this.showInstallSuccessMessage();
        });
    }

    setupConnectionEvents() {
        window.addEventListener('online', () => {
            console.log('🌐 Conexión restaurada');
            this.isOnline = true;
            this.showConnectionStatus('online');
            this.syncOfflineData();
        });

        window.addEventListener('offline', () => {
            console.log('📵 Sin conexión a internet');
            this.isOnline = false;
            this.showConnectionStatus('offline');
        });
    }

    showInstallButton() {
        if (this.deferredPrompt && !this.isStandalone()) {
            let installButton = document.getElementById('pwa-install-button');
            
            if (!installButton) {
                installButton = this.createInstallButton();
                document.body.appendChild(installButton);
            }
            
            installButton.style.display = 'block';
        }
    }

    createInstallButton() {
        const button = document.createElement('button');
        button.id = 'pwa-install-button';
        button.innerHTML = `
            <i class="fas fa-download"></i>
            <span>Instalar App</span>
        `;
        button.className = 'pwa-install-btn';
        
        // Estilos del botón
        button.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #F5C842;
            color: #1e293b;
            border: none;
            padding: 12px 20px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 14px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            cursor: pointer;
            z-index: 1000;
            display: none;
            transition: all 0.3s ease;
            font-family: 'Nunito', Arial, sans-serif;
        `;

        button.addEventListener('click', () => this.installPWA());
        
        return button;
    }

    async installPWA() {
        if (this.deferredPrompt) {
            this.deferredPrompt.prompt();
            const { outcome } = await this.deferredPrompt.userChoice;
            
            if (outcome === 'accepted') {
                console.log('👍 Usuario aceptó instalar la PWA');
            } else {
                console.log('👎 Usuario rechazó instalar la PWA');
            }
            
            this.deferredPrompt = null;
            this.hideInstallButton();
        }
    }

    hideInstallButton() {
        const installButton = document.getElementById('pwa-install-button');
        if (installButton) {
            installButton.style.display = 'none';
        }
    }

    isStandalone() {
        return window.matchMedia('(display-mode: standalone)').matches ||
               window.navigator.standalone === true;
    }

    showConnectionStatus(status) {
        // Mostrar toast de conexión
        this.showToast(
            status === 'online' 
                ? '🌐 Conexión restaurada' 
                : '📵 Sin conexión - Modo offline activo',
            status === 'online' ? 'success' : 'warning'
        );
    }

    showUpdateNotification() {
        this.showToast(
            '🔄 Nueva versión disponible. Recarga la página para actualizar.',
            'info',
            8000,
            () => window.location.reload()
        );
    }

    showInstallSuccessMessage() {
        this.showToast('✅ App instalada correctamente. ¡Ahora puedes usarla desde tu pantalla de inicio!', 'success', 5000);
    }

    showToast(message, type = 'info', duration = 4000, action = null) {
        // Crear toast si no existe
        let toastContainer = document.getElementById('pwa-toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'pwa-toast-container';
            toastContainer.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 10000;
                max-width: 350px;
            `;
            document.body.appendChild(toastContainer);
        }

        const toast = document.createElement('div');
        toast.className = `pwa-toast pwa-toast-${type}`;
        toast.innerHTML = `
            <div class="pwa-toast-content">
                ${message}
                ${action ? '<button class="pwa-toast-action">Actualizar</button>' : ''}
            </div>
        `;

        // Estilos del toast
        toast.style.cssText = `
            background: ${type === 'success' ? '#4CAF50' : type === 'warning' ? '#FF9800' : '#2196F3'};
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transform: translateX(100%);
            transition: transform 0.3s ease;
            font-family: 'Nunito', Arial, sans-serif;
            font-size: 14px;
            line-height: 1.4;
        `;

        toastContainer.appendChild(toast);

        // Animar entrada
        setTimeout(() => {
            toast.style.transform = 'translateX(0)';
        }, 100);

        // Configurar acción si existe
        if (action) {
            const actionButton = toast.querySelector('.pwa-toast-action');
            if (actionButton) {
                actionButton.style.cssText = `
                    background: rgba(255,255,255,0.2);
                    border: 1px solid rgba(255,255,255,0.3);
                    color: white;
                    padding: 5px 10px;
                    border-radius: 4px;
                    margin-left: 10px;
                    cursor: pointer;
                    font-size: 12px;
                `;
                actionButton.addEventListener('click', action);
            }
        }

        // Auto-ocultar
        setTimeout(() => {
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
        }, duration);
    }

    async syncOfflineData() {
        if ('serviceWorker' in navigator && navigator.serviceWorker.controller) {
            navigator.serviceWorker.controller.postMessage({
                type: 'SYNC_DATA'
            });
        }
    }

    async checkForUpdates() {
        if ('serviceWorker' in navigator) {
            const registration = await navigator.serviceWorker.getRegistration();
            if (registration) {
                registration.update();
            }
        }
    }
}

// Inicializar PWA Manager cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 Inicializando PWA Manager...');
    window.pwaManager = new PWAManager();
});

// Configuraciones adicionales para PWA
if ('serviceWorker' in navigator) {
    // Escuchar mensajes del service worker
    navigator.serviceWorker.addEventListener('message', event => {
        if (event.data && event.data.type === 'CACHE_UPDATED') {
            console.log('📦 Cache actualizado:', event.data.url);
        }
    });
}
