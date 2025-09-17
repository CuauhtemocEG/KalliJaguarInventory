<?php
require_once "./controllers/mainController.php";
$userID = $_SESSION["id"];
$nameUser = $_SESSION["nombre"];

$showComanda = conexion();
$showComanda = $showComanda->query("
    SELECT MAX(FechaMovimiento) AS FechaMovimiento, Status, ComandaID, 
           MAX(SucursalID) AS SucursalID, MAX(MovimientoID) AS MovimientoID, 
           COUNT(DISTINCT ProductoID) AS TotalProductos, 
           SUM(Cantidad) AS TotalCantidad 
    FROM MovimientosInventario 
    WHERE TipoMovimiento = 'Salida' AND UsuarioID = $userID 
    GROUP BY ComandaID, Status, UsuarioID 
    ORDER BY MovimientoID DESC
");
$datos = $showComanda->fetchAll();
$num = 0;
?>
<div class="min-h-screen bg-white dark:bg-gray-900">
    <div class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-clipboard-list text-white text-xl"></i>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                            Mis Solicitudes
                        </h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Gestiona y visualiza tus solicitudes de productos
                        </p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="index.php?page=requestProducts" 
                       class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-medium rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                        <i class="fas fa-plus mr-2"></i>
                        Nueva Solicitud
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?php if (empty($datos)): ?>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-12 text-center">
                <div class="flex flex-col items-center">
                    <div class="w-20 h-20 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-clipboard-list text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                        No tienes solicitudes
                    </h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">
                        Crea tu primera solicitud de productos para comenzar
                    </p>
                    <a href="index.php?page=requestProducts" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                        <i class="fas fa-plus mr-2"></i>
                        Crear Solicitud
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <?php foreach ($datos as $row): 
                    $num += 1;
                    $dataSucursal = conexion();
                    $dataSucursal = $dataSucursal->query("SELECT nombre FROM Sucursales WHERE SucursalID = " . $row['SucursalID']);
                    $nameSucursal = $dataSucursal->fetchColumn();
                ?>
                
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-md transition-all duration-200 border border-gray-200 dark:border-gray-700 group overflow-hidden">
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center shadow-md">
                                        <i class="fas fa-file-alt text-white text-lg"></i>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                        Solicitud #<?php echo $row['ComandaID']; ?>
                                    </h3>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-300">
                                        <?php echo date('d/m/Y H:i', strtotime($row['FechaMovimiento'])); ?>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex items-center">
                                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold text-white shadow-sm
                                    <?php
                                        switch ($row['Status']) {
                                            case 'Abierto': 
                                                echo 'bg-blue-600'; 
                                                break;
                                            case 'En transito': 
                                                echo 'bg-yellow-600'; 
                                                break;
                                            case 'Cerrado': 
                                                echo 'bg-green-600'; 
                                                break;
                                            case 'Cancelado': 
                                                echo 'bg-red-600'; 
                                                break;
                                            default: 
                                                echo 'bg-gray-600'; 
                                                break;
                                    } ?>">
                                    <div class="w-2 h-2 rounded-full mr-2 bg-white opacity-80"></div>
                                    <?php echo $row['Status']; ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-700">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-box text-blue-600 dark:text-blue-400 text-lg"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-xs font-medium text-blue-600 dark:text-blue-400 uppercase tracking-wide">
                                            Productos
                                        </p>
                                        <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">
                                            <?php echo $row['TotalProductos']; ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4 border border-green-200 dark:border-green-700">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-calculator text-green-600 dark:text-green-400 text-lg"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-xs font-medium text-green-600 dark:text-green-400 uppercase tracking-wide">
                                            Cantidad Total
                                        </p>
                                        <p class="text-2xl font-bold text-green-900 dark:text-green-100">
                                            <?php echo $row['TotalCantidad']; ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-center p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg mb-6 border border-indigo-200 dark:border-indigo-700">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-800 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-store text-indigo-600 dark:text-indigo-400"></i>
                                </div>
                            </div>
                            <div class="ml-3">
                                <p class="text-xs font-medium text-indigo-600 dark:text-indigo-400 uppercase tracking-wide">
                                    Sucursal Destino
                                </p>
                                <p class="text-sm font-semibold text-indigo-900 dark:text-indigo-100">
                                    <?php echo $nameSucursal; ?>
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <a href="index.php?page=showPDF&ComandaID=<?php echo $row['ComandaID']; ?>"
                               class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition-all duration-200">
                                <i class="fas fa-download mr-2"></i>
                                Ver PDF
                            </a>
                            
                            <button type="button" 
                                    class="toggle-comanda inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition-all duration-200" 
                                    data-comanda-id="<?php echo $row['ComandaID']; ?>">
                                <i class="fas fa-eye mr-2"></i>
                                Ver Detalles
                            </button>
                            
                            <?php if ($row['Status'] === 'Abierto'): ?>
                                <button type="button"
                                        class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg text-white bg-red-600 hover:bg-red-700 shadow-sm transition-all duration-200" 
                                        data-toggle="modal" 
                                        data-target="#deleteModal_<?php echo $row['ComandaID']; ?>">
                                    <i class="fas fa-trash mr-2"></i>
                                    Cancelar
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="comanda-collapse col-span-full" 
                     id="collapseComanda<?php echo $row['ComandaID']; ?>" 
                     style="display: none;">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 animate-slideIn">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Detalles de la Solicitud #<?php echo $row['ComandaID']; ?>
                            </h4>
                            <button type="button" 
                                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                                    onclick="document.getElementById('collapseComanda<?php echo $row['ComandaID']; ?>').style.display='none'">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div id="detalleComanda_<?php echo $row['ComandaID']; ?>">
                            <div class="flex items-center justify-center p-8">
                                <div class="text-center">
                                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-4"></div>
                                    <p class="text-gray-500 dark:text-gray-400">
                                        Cargando información...
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="deleteModal_<?php echo $row['ComandaID']; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content bg-white dark:bg-gray-800 rounded-xl shadow-xl border-0">
                            <div class="modal-header border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
                                            <i class="fas fa-exclamation-triangle text-red-600 dark:text-red-400"></i>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <h5 class="modal-title text-lg font-semibold text-gray-900 dark:text-white">
                                            Cancelar Solicitud
                                        </h5>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                            Solicitud #<?php echo $row['ComandaID']; ?>
                                        </p>
                                    </div>
                                </div>
                                <button class="close ml-auto text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors" type="button" data-dismiss="modal" aria-label="Cerrar">
                                    <span aria-hidden="true"><i class="fas fa-times"></i></span>
                                </button>
                            </div>
                            <div class="modal-body px-6 py-4">
                                <div class="flex items-start space-x-4">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-info-circle text-blue-500 text-xl"></i>
                                    </div>
                                    <div>
                                        <p class="text-gray-700 dark:text-gray-300 mb-2">
                                            ¿Estás seguro de que deseas cancelar esta solicitud?
                                        </p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            Esta acción eliminará permanentemente la solicitud y devolverá el stock al inventario. Esta acción no se puede deshacer.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer border-t border-gray-200 dark:border-gray-700 px-6 py-4 bg-gray-50 dark:bg-gray-700 rounded-b-xl">
                                <div class="flex items-center justify-end space-x-3">
                                    <button class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors" type="button" data-dismiss="modal">
                                        Cancelar
                                    </button>
                                    <a class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 border border-transparent rounded-lg transition-colors" href="index.php?page=cancelRequest&ComandaID=<?php echo $row['ComandaID']; ?>">
                                        Cancelar Solicitud
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<style>
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .animate-slideIn {
        animation: slideIn 0.3s ease-out;
    }
    
    .animate-fadeIn {
        animation: fadeIn 0.5s ease-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
</style>

<script>
    if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
        document.documentElement.classList.add('dark');
    }

    document.addEventListener("DOMContentLoaded", function () {
        const toggleButtons = document.querySelectorAll(".toggle-comanda");

        toggleButtons.forEach(button => {
            button.addEventListener("click", function () {
                const comandaID = this.dataset.comandaId;
                const collapseID = "collapseComanda" + comandaID;
                const target = document.getElementById(collapseID);
                const container = document.getElementById("detalleComanda_" + comandaID);

                document.querySelectorAll(".comanda-collapse").forEach(div => {
                    if (div.id !== collapseID && div.style.display === "block") {
                        div.style.display = "none";
                        div.classList.remove('animate-slideIn');
                    }
                });

                if (target.style.display === "none" || target.style.display === "") {
                    target.style.display = "block";
                    target.classList.add('animate-slideIn');

                    this.innerHTML = '<i class="fas fa-eye-slash mr-2"></i>Ocultar Detalles';
                    this.classList.remove('bg-indigo-600', 'hover:bg-indigo-700');
                    this.classList.add('bg-gray-600', 'hover:bg-gray-700');

                    if (!container.dataset.loaded) {
                        Swal.fire({
                            title: 'Cargando Detalles',
                            text: 'Obteniendo información detallada de la solicitud...',
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        fetch(`api/comandaDetails/getComandaDetails.php?ComandaID=${comandaID}&v=${Date.now()}`)
                            .then(res => {
                                console.log('Fetch Response Status:', res.status);
                                console.log('Fetch Response URL:', res.url);
                                if (!res.ok) throw new Error(`Error ${res.status}: ${res.statusText}`);
                                return res.text();
                            })
                            .then(html => {
                                console.log('✅ Contenido cargado exitosamente');
                                console.log('📝 Primer fragmento del HTML:', html.substring(0, 200));
                                container.innerHTML = html;
                                container.dataset.loaded = "true";
                                Swal.close();
                                
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Detalles Cargados!',
                                    text: 'La información se ha cargado correctamente.',
                                    timer: 2000,
                                    showConfirmButton: false,
                                    position: 'top-end',
                                    toast: true
                                });
                            })
                            .catch(err => {
                                console.error("Error al cargar detalles:", err);
                                
                                container.innerHTML = `
                                    <div class="text-center py-8">
                                        <div class="flex flex-col items-center">
                                            <div class="w-16 h-16 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center mb-4">
                                                <i class="fas fa-exclamation-triangle text-red-600 dark:text-red-400 text-xl"></i>
                                            </div>
                                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                                                Error al cargar
                                            </h3>
                                            <p class="text-gray-500 dark:text-gray-400 mb-4">
                                                No se pudo obtener la información de la solicitud.
                                            </p>
                                            <button onclick="location.reload()" 
                                                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                                                <i class="fas fa-redo mr-2"></i>
                                                Reintentar
                                            </button>
                                        </div>
                                    </div>
                                `;
                                
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error de Conexión',
                                    text: 'No se pudo cargar la información. Verifica tu conexión a internet.',
                                    confirmButtonText: 'Entendido',
                                    confirmButtonColor: '#3B82F6'
                                });
                            });
                    }

                } else {
                    target.style.display = "none";
                    target.classList.remove('animate-slideIn');
                    
                    this.innerHTML = '<i class="fas fa-eye mr-2"></i>Ver Detalles';
                    this.classList.remove('bg-gray-600', 'hover:bg-gray-700');
                    this.classList.add('bg-indigo-600', 'hover:bg-indigo-700');
                }
            });
        });

        const cards = document.querySelectorAll('.bg-white.dark\\:bg-gray-800.rounded-xl');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.5s ease-out';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
    });
</script>

