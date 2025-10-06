// Smart PWA Controller - Sistema Inteligente de PWA
class SmartPWAController {
    constructor() {
        this.isEnabled = this.checkPWAEnabled();
        this.currentPage = this.getCurrentPage();
        this.init();
    }
    
    getCurrentPage() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('page') || 'home';
    }
    
    checkPWAEnabled() {
        // Verificar configuración manual
        const manualSetting = localStorage.getItem('pwa-enabled');
        if (manualSetting === 'false') return false;
        if (manualSetting === 'true') return true;
        
        // Verificar URL params
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('pwa') === 'false') return false;
        if (urlParams.get('pwa') === 'true') return true;
        
        // Por defecto: PWA desactivada para páginas críticas
        const criticalPages = ['requestProducts', 'showRequest', 'editarComanda'];
        return !criticalPages.includes(this.currentPage);
    }
    
    async init() {
        console.log('🤖 Smart PWA Controller iniciando...');
        console.log('🤖 Página actual:', this.currentPage);
        console.log('🤖 PWA habilitada:', this.isEnabled);
        
        if (this.isEnabled) {
            await this.enablePWA();
        } else {
            await this.disablePWA();
            this.showPWAControls();
        }
        
        this.setupEventListeners();
    }
    
    async enablePWA() {
        try {
            console.log('🟢 Activando PWA...');
            
            if ('serviceWorker' in navigator) {
                const registration = await navigator.serviceWorker.register('sw.js');
                console.log('✅ Service Worker registrado:', registration.scope);
                
                // Mostrar notificación de PWA activa
                this.showPWAStatus('enabled');
                
                return registration;
            }
        } catch (error) {
            console.error('❌ Error registrando SW:', error);
            this.showPWAStatus('error', error.message);
        }
    }
    
    async disablePWA() {
        try {
            console.log('🔴 Desactivando PWA...');
            
            if ('serviceWorker' in navigator) {
                const registrations = await navigator.serviceWorker.getRegistrations();
                
                for (let registration of registrations) {
                    await registration.unregister();
                    console.log('✅ SW desregistrado:', registration.scope);
                }
                
                // Limpiar cachés
                if ('caches' in window) {
                    const cacheNames = await caches.keys();
                    for (let name of cacheNames) {
                        await caches.delete(name);
                        console.log('✅ Caché eliminado:', name);
                    }
                }
            }
            
            this.showPWAStatus('disabled');
            
        } catch (error) {
            console.error('❌ Error desactivando PWA:', error);
        }
    }
    
    showPWAStatus(status, message = '') {
        // Remover status anterior
        const existingStatus = document.getElementById('pwa-smart-status');
        if (existingStatus) existingStatus.remove();
        
        let statusHTML = '';
        let statusClass = '';
        let statusIcon = '';
        
        switch (status) {
            case 'enabled':
                statusClass = 'alert-success';
                statusIcon = '🟢';
                statusHTML = `PWA Activada - Funcionalidades offline disponibles`;
                break;
            case 'disabled':
                statusClass = 'alert-info';
                statusIcon = '🔴';
                statusHTML = `PWA Desactivada - Funcionamiento completo garantizado`;
                break;
            case 'error':
                statusClass = 'alert-danger';
                statusIcon = '❌';
                statusHTML = `Error PWA: ${message}`;
                break;
        }
        
        const statusDiv = document.createElement('div');
        statusDiv.id = 'pwa-smart-status';
        statusDiv.className = `alert ${statusClass} alert-dismissible fade show`;
        statusDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 350px;
            font-size: 0.85rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        `;
        
        statusDiv.innerHTML = `
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 1.1rem;">${statusIcon}</span>
                <span>${statusHTML}</span>
            </div>
            <button type="button" class="close" onclick="this.parentElement.remove()">
                <span>&times;</span>
            </button>
        `;
        
        document.body.appendChild(statusDiv);
        
        // Auto-hide después de 5 segundos (excepto errores)
        if (status !== 'error') {
            setTimeout(() => {
                if (statusDiv.parentElement) {
                    statusDiv.remove();
                }
            }, 5000);
        }
    }
    
    showPWAControls() {
        // Solo mostrar controles en páginas críticas
        const criticalPages = ['requestProducts', 'showRequest', 'editarComanda'];
        if (!criticalPages.includes(this.currentPage)) return;
        
        const existingControls = document.getElementById('pwa-smart-controls');
        if (existingControls) return; // Ya existe
        
        const controlsDiv = document.createElement('div');
        controlsDiv.id = 'pwa-smart-controls';
        controlsDiv.style.cssText = `
            position: fixed;
            bottom: 20px;
            left: 20px;
            background: #fff;
            border: 2px solid #007bff;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 9998;
            max-width: 280px;
            font-size: 0.85rem;
        `;
        
        controlsDiv.innerHTML = `
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                <span style="font-size: 1.2rem;">⚙️</span>
                <strong>Control PWA</strong>
            </div>
            <p style="margin: 0 0 10px 0; color: #666;">
                PWA desactivada para esta página crítica
            </p>
            <div style="display: flex; gap: 8px;">
                <button class="btn btn-sm btn-success" onclick="smartPWA.enablePWAForced()">
                    🟢 Activar PWA
                </button>
                <button class="btn btn-sm btn-secondary" onclick="this.parentElement.parentElement.remove()">
                    ✕
                </button>
            </div>
        `;
        
        document.body.appendChild(controlsDiv);
    }
    
    async enablePWAForced() {
        localStorage.setItem('pwa-enabled', 'true');
        this.isEnabled = true;
        await this.enablePWA();
        
        // Remover controles
        const controls = document.getElementById('pwa-smart-controls');
        if (controls) controls.remove();
    }
    
    async disablePWAForced() {
        localStorage.setItem('pwa-enabled', 'false');
        this.isEnabled = false;
        await this.disablePWA();
    }
    
    setupEventListeners() {
        // Escuchar cambios de página
        window.addEventListener('popstate', () => {
            setTimeout(() => {
                this.currentPage = this.getCurrentPage();
                this.init();
            }, 100);
        });
        
        // Funciones globales
        window.enablePWA = () => this.enablePWAForced();
        window.disablePWA = () => this.disablePWAForced();
    }
}

// Inicializar automáticamente
document.addEventListener('DOMContentLoaded', () => {
    window.smartPWA = new SmartPWAController();
});

console.log('🤖 Smart PWA Controller cargado');
