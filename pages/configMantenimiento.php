<?php
require_once "./includes/session_start.php";
require_once "./controllers/mainController.php";

if ($_SESSION['rol'] != 'Administrador') {
    header('Location: home.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración de Mantenimiento - Kalli System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .config-card {
            transition: all 0.3s ease;
        }
        .config-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        input:checked + .slider {
            background-color: #10b981;
        }
        input:checked + .slider:before {
            transform: translateX(26px);
        }
        .day-checkbox {
            cursor: pointer;
            padding: 12px 20px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            transition: all 0.3s ease;
            background: white;
        }
        .day-checkbox:hover {
            border-color: #667eea;
            background: #f3f4f6;
        }
        .day-checkbox.selected {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-color: #667eea;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-6xl">
        <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-cog text-white text-3xl"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Configuración de Mantenimiento</h1>
                        <p class="text-gray-600 mt-1">Gestiona el sistema de cortina para solicitud de productos</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <button onclick="testMaintenance()" class="px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition flex items-center gap-2">
                        <i class="fas fa-vial"></i>
                        Probar Cortina
                    </button>
                    <button onclick="saveAll()" id="saveAllBtn" class="px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg hover:from-purple-700 hover:to-indigo-700 transition flex items-center gap-2">
                        <i class="fas fa-save"></i>
                        Guardar Todo
                    </button>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-lg p-8 mb-8 config-card">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-power-off text-green-600 text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Estado del Sistema</h2>
                        <p class="text-gray-600 mt-1">Activar o desactivar el sistema de cortina</p>
                    </div>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" id="maintenanceEnabled" onchange="updateStatus()">
                    <span class="slider"></span>
                </label>
            </div>
            <div id="statusIndicator" class="mt-4 p-4 rounded-lg hidden">
                <p class="font-semibold"></p>
            </div>
        </div>

        <!-- Días Bloqueados -->
        <div class="bg-white rounded-2xl shadow-lg p-8 mb-8 config-card">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-calendar-week text-purple-600 text-xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Días Bloqueados</h2>
                    <p class="text-gray-600 mt-1">Selecciona los días de la semana en los que no se permiten pedidos</p>
                </div>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3" id="daysContainer">
                <div class="day-checkbox text-center" data-day="0" onclick="toggleDay(0)">
                    <div class="text-2xl mb-2">🌞</div>
                    <div class="font-semibold">DOM</div>
                </div>
                <div class="day-checkbox text-center" data-day="1" onclick="toggleDay(1)">
                    <div class="text-2xl mb-2">🌙</div>
                    <div class="font-semibold">LUN</div>
                </div>
                <div class="day-checkbox text-center" data-day="2" onclick="toggleDay(2)">
                    <div class="text-2xl mb-2">🌙</div>
                    <div class="font-semibold">MAR</div>
                </div>
                <div class="day-checkbox text-center" data-day="3" onclick="toggleDay(3)">
                    <div class="text-2xl mb-2">🌙</div>
                    <div class="font-semibold">MIÉ</div>
                </div>
                <div class="day-checkbox text-center" data-day="4" onclick="toggleDay(4)">
                    <div class="text-2xl mb-2">🌙</div>
                    <div class="font-semibold">JUE</div>
                </div>
                <div class="day-checkbox text-center" data-day="5" onclick="toggleDay(5)">
                    <div class="text-2xl mb-2">🌙</div>
                    <div class="font-semibold">VIE</div>
                </div>
                <div class="day-checkbox text-center" data-day="6" onclick="toggleDay(6)">
                    <div class="text-2xl mb-2">🌞</div>
                    <div class="font-semibold">SÁB</div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-lg p-8 mb-8 config-card">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-calendar-day text-yellow-600 text-xl"></i>
                </div>
                <div class="flex-1">
                    <h2 class="text-2xl font-bold text-gray-800">Fechas Especiales Bloqueadas</h2>
                    <p class="text-gray-600 mt-1">Días festivos y fechas específicas donde no se permiten pedidos</p>
                </div>
            </div>
            <div class="flex gap-3 mb-4">
                <input type="date" id="newDate" class="flex-1 px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-purple-500 focus:outline-none">
                <button onclick="addDate()" class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition flex items-center gap-2">
                    <i class="fas fa-plus"></i>
                    Agregar Fecha
                </button>
            </div>
            <div id="datesList" class="space-y-2"></div>
        </div>

        <div class="bg-white rounded-2xl shadow-lg p-8 mb-8 config-card">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-clock text-blue-600 text-xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Horarios Bloqueados</h2>
                    <p class="text-gray-600 mt-1">Define el rango de horas en las que no se permiten pedidos</p>
                </div>
            </div>
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-moon mr-2"></i>Hora de Inicio
                    </label>
                    <input type="time" id="timeStart" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none">
                    <p class="text-xs text-gray-500 mt-1">Ejemplo: 22:00 (10:00 PM)</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-sun mr-2"></i>Hora de Fin
                    </label>
                    <input type="time" id="timeEnd" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none">
                    <p class="text-xs text-gray-500 mt-1">Ejemplo: 06:00 (6:00 AM)</p>
                </div>
            </div>
            <div class="mt-4 p-4 bg-blue-50 border-l-4 border-blue-500 rounded">
                <p class="text-sm text-blue-800">
                    <i class="fas fa-info-circle mr-2"></i>
                    Si la hora de inicio es mayor que la de fin, se asume que cruza medianoche (ej: 22:00 - 06:00).
                </p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-lg p-8 mb-8 config-card">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 bg-pink-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-comment-dots text-pink-600 text-xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Mensajes de la Cortina</h2>
                    <p class="text-gray-600 mt-1">Personaliza los mensajes que verán los usuarios</p>
                </div>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Mensaje Principal</label>
                    <input type="text" id="mainMessage" placeholder="Lo sentimos, no se pueden realizar pedidos en este momento" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-pink-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Mensaje Secundario</label>
                    <input type="text" id="subMessage" placeholder="Por favor, intenta en otro horario o día" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-pink-500 focus:outline-none">
                </div>
            </div>
        </div>

        <div class="flex gap-4 justify-end">
            <button onclick="window.location.href='home.php'" class="px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition">
                <i class="fas fa-times mr-2"></i>Cancelar
            </button>
            <button onclick="resetToDefaults()" class="px-6 py-3 bg-red-500 text-white rounded-lg hover:bg-red-600 transition">
                <i class="fas fa-undo mr-2"></i>Restablecer Valores
            </button>
            <button onclick="saveAll()" class="px-8 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg hover:from-purple-700 hover:to-indigo-700 transition font-semibold">
                <i class="fas fa-save mr-2"></i>Guardar Cambios
            </button>
        </div>
    </div>

    <div id="toast" class="fixed bottom-4 right-4 bg-white rounded-lg shadow-2xl p-4 transform translate-y-full transition-transform duration-300 hidden">
        <div class="flex items-center gap-3">
            <i class="text-2xl"></i>
            <span class="font-semibold"></span>
        </div>
    </div>

    <script>
        let selectedDays = [];
        let blockedDates = [];

        document.addEventListener('DOMContentLoaded', loadConfiguration);

        async function loadConfiguration() {
            try {
                const apiUrl = './api/maintenance-config/index.php';
                console.log('🔍 Cargando configuración desde:', apiUrl);
                console.log('🌐 URL completa:', window.location.origin + window.location.pathname.replace('index.php', '') + apiUrl);
                
                const response = await fetch(apiUrl);
                console.log('📡 Respuesta API:', response.status, response.statusText);
                
                const data = await response.json();
                
                if (data.success) {
                    const configs = data.data.configuraciones;
                    
                    configs.forEach(config => {
                        switch(config.key) {
                            case 'MAINTENANCE_MODE_ENABLED':
                                document.getElementById('maintenanceEnabled').checked = config.value;
                                updateStatus();
                                break;
                            case 'BLOCKED_WEEKDAYS':
                                selectedDays = config.value || [];
                                updateDaysDisplay();
                                break;
                            case 'BLOCKED_DATES':
                                blockedDates = config.value || [];
                                updateDatesDisplay();
                                break;
                            case 'BLOCKED_TIME_START':
                                document.getElementById('timeStart').value = config.value || '';
                                break;
                            case 'BLOCKED_TIME_END':
                                document.getElementById('timeEnd').value = config.value || '';
                                break;
                            case 'MAINTENANCE_MESSAGE':
                                document.getElementById('mainMessage').value = config.value || '';
                                break;
                            case 'MAINTENANCE_SUBMESSAGE':
                                document.getElementById('subMessage').value = config.value || '';
                                break;
                        }
                    });
                }
            } catch (error) {
                console.error('❌ Error al cargar configuración:', error);
                showToast('Error al cargar configuración: ' + error.message, 'error');
            }
        }

        function updateStatus() {
            const enabled = document.getElementById('maintenanceEnabled').checked;
            const indicator = document.getElementById('statusIndicator');
            indicator.classList.remove('hidden');
            
            if (enabled) {
                indicator.className = 'mt-4 p-4 rounded-lg bg-green-50 border-l-4 border-green-500';
                indicator.querySelector('p').innerHTML = '<i class="fas fa-check-circle mr-2 text-green-600"></i><span class="text-green-800 font-semibold">Sistema de Cortina ACTIVADO</span>';
            } else {
                indicator.className = 'mt-4 p-4 rounded-lg bg-gray-50 border-l-4 border-gray-500';
                indicator.querySelector('p').innerHTML = '<i class="fas fa-times-circle mr-2 text-gray-600"></i><span class="text-gray-800 font-semibold">Sistema de Cortina DESACTIVADO</span>';
            }
        }

        function toggleDay(day) {
            const index = selectedDays.indexOf(day);
            if (index > -1) {
                selectedDays.splice(index, 1);
            } else {
                selectedDays.push(day);
            }
            updateDaysDisplay();
        }

        function updateDaysDisplay() {
            document.querySelectorAll('.day-checkbox').forEach(el => {
                const day = parseInt(el.dataset.day);
                if (selectedDays.includes(day)) {
                    el.classList.add('selected');
                } else {
                    el.classList.remove('selected');
                }
            });
        }

        function addDate() {
            const dateInput = document.getElementById('newDate');
            const date = dateInput.value;
            
            if (!date) {
                showToast('Por favor selecciona una fecha', 'warning');
                return;
            }
            
            if (blockedDates.includes(date)) {
                showToast('Esta fecha ya está bloqueada', 'warning');
                return;
            }
            
            blockedDates.push(date);
            blockedDates.sort();
            updateDatesDisplay();
            dateInput.value = '';
            showToast('Fecha agregada', 'success');
        }

        function removeDate(date) {
            blockedDates = blockedDates.filter(d => d !== date);
            updateDatesDisplay();
            showToast('Fecha eliminada', 'success');
        }

        function updateDatesDisplay() {
            const container = document.getElementById('datesList');
            
            if (blockedDates.length === 0) {
                container.innerHTML = '<p class="text-gray-500 text-center py-4">No hay fechas bloqueadas</p>';
                return;
            }
            
            container.innerHTML = blockedDates.map(date => `
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-calendar text-yellow-600"></i>
                        <span class="font-semibold">${formatDate(date)}</span>
                        <span class="text-sm text-gray-500">(${getDayName(date)})</span>
                    </div>
                    <button onclick="removeDate('${date}')" class="text-red-500 hover:text-red-700 transition">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `).join('');
        }

        function formatDate(dateStr) {
            const date = new Date(dateStr + 'T00:00:00');
            return date.toLocaleDateString('es-MX', { year: 'numeric', month: 'long', day: 'numeric' });
        }

        function getDayName(dateStr) {
            const date = new Date(dateStr + 'T00:00:00');
            return date.toLocaleDateString('es-MX', { weekday: 'long' });
        }

        async function saveAll() {
            const btn = document.getElementById('saveAllBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Guardando...';
            
            const configs = [
                { key: 'MAINTENANCE_MODE_ENABLED', value: document.getElementById('maintenanceEnabled').checked },
                { key: 'BLOCKED_WEEKDAYS', value: selectedDays },
                { key: 'BLOCKED_DATES', value: blockedDates },
                { key: 'BLOCKED_TIME_START', value: document.getElementById('timeStart').value },
                { key: 'BLOCKED_TIME_END', value: document.getElementById('timeEnd').value },
                { key: 'MAINTENANCE_MESSAGE', value: document.getElementById('mainMessage').value },
                { key: 'MAINTENANCE_SUBMESSAGE', value: document.getElementById('subMessage').value }
            ];
            
            try {
                for (const config of configs) {
                    await fetch('./api/maintenance-config/index.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(config)
                    });
                }
                
                showToast('Configuración guardada correctamente', 'success');
            } catch (error) {
                showToast('Error al guardar configuración', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save mr-2"></i>Guardar Todo';
            }
        }

        function testMaintenance() {
            window.open('index.php?page=test_cortina_v2', '_blank');
        }

        function resetToDefaults() {
            if (!confirm('¿Estás seguro de restablecer todos los valores a los predeterminados?')) {
                return;
            }
            
            document.getElementById('maintenanceEnabled').checked = false;
            selectedDays = [0, 6];
            blockedDates = ['2025-12-25', '2025-01-01', '2025-12-31'];
            document.getElementById('timeStart').value = '22:00';
            document.getElementById('timeEnd').value = '06:00';
            document.getElementById('mainMessage').value = 'Lo sentimos, no se pueden realizar pedidos en este momento';
            document.getElementById('subMessage').value = 'Por favor, intenta en otro horario o día';
            
            updateStatus();
            updateDaysDisplay();
            updateDatesDisplay();
            showToast('Valores restablecidos', 'success');
        }

        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            const icon = toast.querySelector('i');
            const text = toast.querySelector('span');
            
            const icons = {
                success: { class: 'fas fa-check-circle text-green-500', bg: 'bg-green-50' },
                error: { class: 'fas fa-times-circle text-red-500', bg: 'bg-red-50' },
                warning: { class: 'fas fa-exclamation-circle text-yellow-500', bg: 'bg-yellow-50' }
            };
            
            icon.className = icons[type].class + ' text-2xl';
            toast.className = `fixed bottom-4 right-4 ${icons[type].bg} rounded-lg shadow-2xl p-4 transform transition-transform duration-300`;
            text.textContent = message;
            
            toast.classList.remove('translate-y-full', 'hidden');
            
            setTimeout(() => {
                toast.classList.add('translate-y-full');
                setTimeout(() => toast.classList.add('hidden'), 300);
            }, 3000);
        }
    </script>
</body>
</html>
