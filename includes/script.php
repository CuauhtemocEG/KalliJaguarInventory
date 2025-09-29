<script src="./js/ajax.js"></script>
<script>
// Control de PWA - permite desactivar fácilmente
console.log('Verificando estado de PWA...');

// Función para desactivar PWA de emergencia
window.emergencyDisablePWA = function() {
    localStorage.setItem('pwa-enabled', 'false');
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.getRegistrations().then(registrations => {
            registrations.forEach(registration => registration.unregister());
            location.reload();
        });
    } else {
        location.reload();
    }
};

// Función para reactivar PWA
window.enablePWA = function() {
    localStorage.setItem('pwa-enabled', 'true');
    location.reload();
};

// Detectar problemas de red y mostrar aviso
let requestFailures = 0;
const originalFetch = window.fetch;

window.fetch = function(...args) {
    return originalFetch.apply(this, args)
        .catch(error => {
            requestFailures++;
            console.warn('Fallo de red detectado:', error);
            
            // Si hay muchos fallos, sugerir desactivar PWA
            if (requestFailures >= 3) {
                console.error('Múltiples fallos de red detectados. PWA puede estar interfiriendo.');
                if (confirm('Se detectaron problemas de conectividad. ¿Deseas desactivar temporalmente el PWA para solucionarlo?')) {
                    window.emergencyDisablePWA();
                }
            }
            
            throw error;
        });
};
</script>
<script src="./js/pwa.js"></script>