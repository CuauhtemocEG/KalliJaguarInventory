<div class="request-products-container">
    <?php
    session_start();
    require_once "./controllers/mainController.php";
    ?>
    <div class="page-header">
        <div class="header-content">
            <div class="header-title-section">
                <i class="fas fa-shopping-cart header-icon"></i>
                <div>
                    <h1 class="page-title">Solicitud de Insumos</h1>
                    <p class="page-subtitle">Gestiona y solicita productos del almacén</p>
                </div>
            </div>
            <div class="header-stats">
                <div class="stat-card">
                    <div class="stat-icon bg-primary">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-number" id="totalProducts">-</span>
                        <span class="stat-label">Productos</span>
                    </div>
                </div>
                
            </div>
        </div>
    </div>

    <div class="search-section">
        <div class="search-container">
            <div class="search-box">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="search-input" id="searchInput" placeholder="Buscar por nombre, UPC o descripción...">
                <div class="search-actions">
                    <button class="search-clear-btn" id="searchClear" title="Limpiar búsqueda">
                        <i class="fas fa-times"></i>
                    </button>
                    <button class="search-btn" id="searchButton" title="Buscar">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            <div class="search-filters">
                <button class="filter-btn active" data-filter="all">
                    <i class="fas fa-th-large"></i>
                    Todos
                </button>
                <button class="filter-btn" data-filter="Unidad">
                    <i class="fas fa-cube"></i>
                    Unidades
                </button>
                <button class="filter-btn" data-filter="Pesable">
                    <i class="fas fa-balance-scale"></i>
                    Pesables
                </button>
                <button class="filter-btn" data-filter="disponible">
                    <i class="fas fa-check-circle"></i>
                    Disponibles
                </button>
            </div>
        </div>
    </div>

    <div class="products-section">
        <div class="section-header">
            <div class="section-title">
                <i class="fas fa-inventory"></i>
                <span>Catálogo de Productos</span>
            </div>
            <div class="section-actions">
                <button class="action-btn" id="refreshProducts" title="Actualizar catálogo">
                    <i class="fas fa-sync-alt"></i>
                </button>
                <button class="action-btn" id="viewGrid" title="Vista de cuadrícula" class="active">
                    <i class="fas fa-th-large"></i>
                </button>
                <button class="action-btn" id="viewList" title="Vista de lista">
                    <i class="fas fa-list"></i>
                </button>
                <button class="action-btn" id="forceLoad" title="Forzar carga de productos" style="background: #dc3545; color: white; border-color: #dc3545;">
                    <i class="fas fa-bolt"></i>
                </button>
            </div>
        </div>
        
        <div class="loading-state" id="loadingProducts" style="display: block;">
            <div class="loading-spinner">
                <div class="spinner"></div>
                <p>Cargando productos...</p>
            </div>
        </div>

        <div class="products-container" id="productList" style="display: none;">
        </div>

        <div class="empty-state" id="emptyState" style="display: none;">
            <div class="empty-icon">
                <i class="fas fa-search"></i>
            </div>
            <h3>No se encontraron productos</h3>
            <p>Intenta con otros términos de búsqueda o revisa los filtros aplicados.</p>
            <button class="btn-reset-search" onclick="resetSearch()">
                <i class="fas fa-undo"></i>
                Mostrar todos los productos
            </button>
        </div>
    </div>
</div>

<style>
.request-products-container {
    min-height: 100vh;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    padding: 0;
    margin: 0;
}

.page-header {
    background: #ffffff;
    color: #2c3e50;
    padding: 30px 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    border-bottom: 1px solid #e8ecf0;
}

.header-content {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 30px;
    position: relative;
    z-index: 2;
}

.header-title-section {
    display: flex;
    align-items: center;
    gap: 25px;
}

.header-icon {
    font-size: 2.5rem;
    color: #6c757d;
}

.page-title {
    font-size: 1.8rem;
    font-weight: 600;
    margin: 0;
    line-height: 1.2;
    color: #2c3e50;
}

.page-subtitle {
    font-size: 0.95rem;
    margin: 5px 0 0 0;
    color: #6c757d;
    font-weight: 400;
}

.header-stats {
    display: flex;
    gap: 20px;
}

.stat-card {
    background: #ffffff;
    border: 1px solid #e8ecf0;
    border-radius: 12px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    min-width: 120px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}

.stat-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    transform: translateY(-1px);
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    background: #f8f9fc;
    color: #6c757d;
}

.stat-icon.bg-primary {
    background: #f8f9fc;
    color: #495057;
}

.stat-icon.bg-success {
    background: #f8f9fc;
    color: #495057;
}

.stat-info {
    display: flex;
    flex-direction: column;
}

.stat-number {
    font-size: 1.5rem;
    font-weight: 600;
    line-height: 1;
    color: #2c3e50;
}

.stat-label {
    font-size: 0.85rem;
    color: #6c757d;
}

.search-section {
    background: #ffffff;
    padding: 25px 20px;
    border-bottom: 1px solid #e8ecf0;
}

.search-container {
    max-width: 1200px;
    margin: 0 auto;
}

.search-box {
    position: relative;
    margin-bottom: 20px;
}

.search-icon {
    position: absolute;
    left: 20px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
    font-size: 1rem;
    z-index: 2;
}

.search-input {
    width: 100%;
    padding: 15px 20px 15px 55px;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    font-size: 1rem;
    background: #ffffff;
    transition: all 0.3s ease;
    padding-right: 120px;
}

.search-input:focus {
    outline: none;
    border-color: #495057;
    box-shadow: 0 0 0 3px rgba(73, 80, 87, 0.1);
}

.search-actions {
    position: absolute;
    right: 5px;
    top: 50%;
    transform: translateY(-50%);
    display: flex;
    gap: 5px;
}

.search-clear-btn, .search-btn {
    width: 38px;
    height: 38px;
    border: none;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.search-clear-btn {
    background: #6c757d;
    color: white;
}

.search-clear-btn:hover {
    background: #545b62;
}

.search-btn {
    background: #495057;
    color: white;
}

.search-btn:hover {
    background: #343a40;
}

.search-filters {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    justify-content: center;
}

.filter-btn {
    padding: 10px 20px;
    border: 1px solid #dee2e6;
    background: #ffffff;
    border-radius: 6px;
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 500;
    color: #495057;
}

.filter-btn:hover {
    border-color: #adb5bd;
    background: #f8f9fa;
}

.filter-btn.active {
    background: #495057;
    color: white;
    border-color: #495057;
}

.products-section {
    padding: 30px 20px;
    max-width: 1200px;
    margin: 0 auto;
    background: #fafbfc;
    min-height: 70vh;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding: 15px;
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    border: 1px solid #e8ecf0;
}

.section-title {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 1.2rem;
    font-weight: 600;
    color: #2c3e50;
}

.section-title i {
    color: #6c757d;
    font-size: 1.3rem;
}

.section-actions {
    display: flex;
    gap: 8px;
}

.action-btn {
    width: 40px;
    height: 40px;
    border: 1px solid #dee2e6;
    background: #ffffff;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    color: #6c757d;
}

.action-btn:hover {
    border-color: #adb5bd;
    background: #f8f9fa;
    color: #495057;
}

.action-btn.active {
    background: #495057;
    color: white;
    border-color: #495057;
}

.loading-state {
    text-align: center;
    padding: 80px 20px;
    background: #ffffff;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    margin: 20px;
}

.loading-spinner {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 25px;
}

.spinner {
    width: 60px;
    height: 60px;
    border: 4px solid #f3f4f6;
    border-top: 4px solid #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    position: relative;
}

.spinner::after {
    content: '';
    position: absolute;
    top: 2px;
    left: 2px;
    right: 2px;
    bottom: 2px;
    border-radius: 50%;
    border: 2px solid transparent;
    border-top: 2px solid #2c3e50;
    animation: spin 0.8s linear infinite reverse;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.loading-spinner p {
    font-size: 1.2rem;
    color: #2c3e50;
    margin: 0;
    font-weight: 500;
    background: linear-gradient(45deg, #3498db, #2c3e50, #9b59b6);
    background-size: 300% 300%;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    animation: gradient 2s ease infinite;
}

@keyframes gradient {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.empty-state {
    text-align: center;
    padding: 100px 20px;
    background: #ffffff;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    margin: 20px;
}

.empty-icon {
    font-size: 5rem;
    color: #bdc3c7;
    margin-bottom: 25px;
    background: linear-gradient(135deg, #bdc3c7 0%, #95a5a6 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
}

.empty-state h3 {
    font-size: 1.8rem;
    color: #2c3e50;
    margin-bottom: 15px;
    font-weight: 600;
}

.empty-state p {
    color: #7f8c8d;
    font-size: 1.1rem;
    margin-bottom: 35px;
    line-height: 1.6;
}

.btn-reset-search {
    background: linear-gradient(135deg, #3498db 0%, #2c3e50 100%);
    color: white;
    border: none;
    padding: 15px 30px;
    border-radius: 30px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
    position: relative;
    overflow: hidden;
}

.btn-reset-search::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.6s ease;
}

.btn-reset-search:hover {
    background: linear-gradient(135deg, #2980b9 0%, #34495e 100%);
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(52, 152, 219, 0.4);
}

.btn-reset-search:hover::before {
    left: 100%;
}

@media (max-width: 768px) {
    .page-header {
        padding: 25px 15px;
    }
    
    .header-content {
        flex-direction: column;
        text-align: center;
        gap: 20px;
    }
    
    .header-stats {
        justify-content: center;
        width: 100%;
    }
    
    .page-title {
        font-size: 1.5rem;
    }
    
    .page-subtitle {
        font-size: 0.9rem;
    }
    
    .search-section {
        padding: 20px 15px;
    }
    
    .search-input {
        padding: 12px 15px 12px 45px;
        padding-right: 100px;
        font-size: 1rem;
    }
    
    .search-actions {
        right: 5px;
    }
    
    .search-clear-btn, .search-btn {
        width: 35px;
        height: 35px;
    }
    
    .search-filters {
        justify-content: center;
        gap: 8px;
    }
    
    .filter-btn {
        padding: 8px 15px;
        font-size: 0.9rem;
    }
    
    .products-section {
        padding: 20px 15px;
    }
    
    .section-header {
        flex-direction: column;
        gap: 15px;
        align-items: stretch;
        padding: 12px;
    }
    
    .section-actions {
        justify-content: center;
    }
    
    .loading-state, .empty-state {
        margin: 10px;
        padding: 50px 15px;
    }
    
    .spinner {
        width: 40px;
        height: 40px;
    }
}

@media (max-width: 480px) {
    .page-header {
        padding: 20px 10px;
    }
    
    .header-icon {
        font-size: 2rem;
    }
    
    .page-title {
        font-size: 1.3rem;
    }
    
    .page-subtitle {
        font-size: 0.85rem;
    }
    
    .header-stats {
        flex-direction: column;
        width: 100%;
        gap: 12px;
    }
    
    .stat-card {
        justify-content: center;
        min-width: auto;
        padding: 15px;
    }
    
    .stat-icon {
        width: 45px;
        height: 45px;
        font-size: 1.3rem;
    }
    
    .search-section {
        padding: 15px 10px;
    }
    
    .search-input {
        font-size: 16px !important;
        padding: 10px 12px 10px 40px;
        padding-right: 90px;
    }
    
    .search-icon {
        left: 15px;
        font-size: 0.9rem;
    }
    
    .search-actions {
        right: 4px;
    }
    
    .search-clear-btn, .search-btn {
        width: 32px;
        height: 32px;
        font-size: 0.8rem;
    }
    
    .search-filters {
        gap: 6px;
    }
    
    .filter-btn {
        padding: 6px 12px;
        font-size: 0.8rem;
        gap: 6px;
    }
    
    .products-section {
        padding: 15px 10px;
    }
    
    .section-header {
        padding: 10px;
    }
    
    .section-title {
        font-size: 1.1rem;
    }
    
    .action-btn {
        width: 35px;
        height: 35px;
    }
    
    .loading-state, .empty-state {
        margin: 5px;
        padding: 40px 10px;
    }
    
    .empty-icon {
        font-size: 3rem;
    }
    
    .empty-state h3 {
        font-size: 1.3rem;
    }
    
    .empty-state p {
        font-size: 0.9rem;
    }
    
    .btn-reset-search {
        padding: 10px 18px;
        font-size: 0.85rem;
    }
}
</style>

<style>
.products-grid-container.list-view {
    grid-template-columns: 1fr !important;
    gap: 15px !important;
}

.product-card.list-mode {
    display: flex !important;
    flex-direction: row !important;
    min-height: 160px !important;
    max-width: none !important;
    align-items: stretch !important;
    padding: 0 !important;
    overflow: hidden;
}

.product-card.list-mode .product-image-container {
    width: 140px !important;
    height: 140px !important;
    flex-shrink: 0 !important;
    margin: 10px !important;
    border-radius: 12px !important;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.product-card.list-mode .product-image-container img {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
}

.product-card.list-mode .product-content {
    flex: 1 !important;
    display: flex !important;
    flex-direction: column !important;
    justify-content: space-between !important;
    padding: 15px 20px 15px 0 !important;
    min-width: 0;
}

.product-card.list-mode .product-header {
    margin-bottom: 8px !important;
}

.product-card.list-mode .product-title {
    font-size: 1.1rem !important;
    font-weight: 600 !important;
    margin-bottom: 5px !important;
    line-height: 1.3 !important;
    display: -webkit-box !important;
    -webkit-line-clamp: 2 !important;
    line-clamp: 2 !important;
    -webkit-box-orient: vertical !important;
    overflow: hidden !important;
}

.product-card.list-mode .product-availability {
    margin-bottom: 8px !important;
}

.product-card.list-mode .product-details {
    margin-bottom: 12px !important;
    flex: 1;
}

.product-card.list-mode .detail-item {
    margin-bottom: 6px !important;
    font-size: 0.85rem !important;
}

.product-card.list-mode .detail-value {
    display: -webkit-box !important;
    -webkit-line-clamp: 2 !important;
    line-clamp: 2 !important;
    -webkit-box-orient: vertical !important;
    overflow: hidden !important;
}

.product-card.list-mode .product-meta {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    flex-wrap: wrap !important;
    gap: 10px !important;
    margin-bottom: 12px !important;
    padding: 8px 0 !important;
    border-top: 1px solid #eee !important;
    border-bottom: 1px solid #eee !important;
}

.product-card.list-mode .meta-item {
    display: flex !important;
    align-items: center !important;
    gap: 5px !important;
    font-size: 0.85rem !important;
}

.product-card.list-mode .meta-value {
    font-weight: 600 !important;
}

.product-card.list-mode .product-actions {
    margin-top: auto !important;
}

.product-card.list-mode .quantity-section {
    margin-bottom: 0 !important;
}

.product-card.list-mode .quantity-label {
    font-size: 0.8rem !important;
    margin-bottom: 6px !important;
}

.product-card.list-mode .quantity-controls {
    justify-content: flex-start !important;
    max-width: 200px !important;
}

.product-card.list-mode .btn-quantity {
    width: 30px !important;
    height: 30px !important;
    font-size: 0.8rem !important;
}

.product-card.list-mode .quantity-display {
    min-width: 80px !important;
}

.product-card.list-mode .quantity-input {
    font-size: 0.85rem !important;
    padding: 5px 8px !important;
}

.product-card.list-mode .btn-add-cart {
    padding: 6px 12px !important;
    font-size: 0.85rem !important;
    margin-top: 8px !important;
    align-self: flex-start !important;
    min-width: 120px !important;
}

@media (max-width: 768px) {
    .product-card.list-mode {
        flex-direction: column !important;
        min-height: auto !important;
    }
    
    .product-card.list-mode .product-image-container {
        width: 100% !important;
        height: 160px !important;
        margin: 0 0 10px 0 !important;
        border-radius: 12px 12px 0 0 !important;
    }
    
    .product-card.list-mode .product-content {
        padding: 15px !important;
    }
    
    .product-card.list-mode .product-meta {
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 8px !important;
    }
    
    .product-card.list-mode .quantity-controls {
        max-width: 100% !important;
    }
    
    .product-card.list-mode .btn-add-cart {
        width: 100% !important;
    }
}

@media (max-width: 480px) {
    .product-card.list-mode .product-meta {
        gap: 6px !important;
    }
    
    .product-card.list-mode .meta-item {
        font-size: 0.8rem !important;
    }
}
</style>

<script>
let currentProducts = [];
let currentFilter = 'all';
let currentView = 'grid';

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM cargado, iniciando aplicación');
    console.log('URL API:', typeof urlAPI !== 'undefined' ? urlAPI : 'NO DEFINIDA');
    
    const currentPath = window.location.pathname;
    let baseUrl = '';
    
    if (currentPath.includes('/pages/')) {
        baseUrl = '../js/productsRequested/';
    } else {
        baseUrl = './js/productsRequested/';
    }
    
    if (typeof urlAPI === 'undefined') {
        window.urlAPI = baseUrl;
        console.log('URL API definida automáticamente como:', window.urlAPI);
    } else {
        if (urlAPI.includes('localhost') || urlAPI.includes('http://') || urlAPI.includes('https://')) {
            window.urlAPI = baseUrl;
            console.log('URL API cambiada de absoluta a relativa:', window.urlAPI);
        }
    }
    
    console.log('Iniciando carga forzada de productos...');
    loadProductsNow('');
    
    $('#searchInput').on('input', function() {
        const query = $(this).val();
        window.fetchProducts(query);
        updateClearButton(query);
    });
    
    $('#searchButton').click(function() {
        const query = $('#searchInput').val();
        window.fetchProducts(query);
    });
    
    $('#searchClear').click(function() {
        $('#searchInput').val('');
        window.fetchProducts('');
        updateClearButton('');
    });
    
    $('.filter-btn').click(function() {
        $('.filter-btn').removeClass('active');
        $(this).addClass('active');
        
        currentFilter = $(this).data('filter');
        applyCurrentFilters();
    });
    
    $('#viewGrid').click(function() {
        $('.action-btn').removeClass('active');
        $(this).addClass('active');
        currentView = 'grid';
        applyViewMode();
    });
    
    $('#viewList').click(function() {
        $('.action-btn').removeClass('active');
        $(this).addClass('active');
        currentView = 'list';
        applyViewMode();
    });
    
    $('#refreshProducts').click(function() {
        $(this).find('i').addClass('fa-spin');
        window.fetchProducts($('#searchInput').val());
        setTimeout(() => {
            $(this).find('i').removeClass('fa-spin');
        }, 1000);
    });
    
    $('#forceLoad').click(function() {
        loadProductsNow('');
    });
});

function updateClearButton(query) {
    const clearBtn = $('#searchClear');
    if (query.length > 0) {
        clearBtn.show().css('opacity', '1');
    } else {
        clearBtn.hide();
    }
}

function applyCurrentFilters() {
    const productCards = document.querySelectorAll('.product-card');
    let visibleCount = 0;
    
    productCards.forEach(card => {
        let shouldShow = false;
        
        switch(currentFilter) {
            case 'all':
                shouldShow = true;
                break;
                
            case 'Unidad':
                const unitIcon = card.querySelector('.fa-cube');
                shouldShow = unitIcon !== null;
                break;
                
            case 'Pesable':
                const scaleIcon = card.querySelector('.fa-balance-scale');
                shouldShow = scaleIcon !== null;
                break;
                
            case 'disponible':
                const availableBadge = card.querySelector('.badge-success');
                shouldShow = availableBadge !== null;
                break;
        }
        
        if (shouldShow) {
            card.style.display = 'block';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });
    
    $('#totalProducts').text(visibleCount);
    
    if (visibleCount === 0 && productCards.length > 0) {
        showEmptyState();
    } else if (visibleCount > 0) {
        hideEmptyState();
    }
}

function applyViewMode() {
    const container = document.getElementById('productList');
    const gridContainer = container.querySelector('.products-grid-container');
    
    if (!gridContainer) return;
    
    if (currentView === 'list') {
        gridContainer.classList.add('list-view');
        
        const cards = gridContainer.querySelectorAll('.product-card');
        cards.forEach(card => {
            card.classList.add('list-mode');
        });
    } else {
        gridContainer.classList.remove('list-view');
        
        const cards = gridContainer.querySelectorAll('.product-card');
        cards.forEach(card => {
            card.classList.remove('list-mode');
        });
    }
}

function resetSearch() {
    $('#searchInput').val('');
    $('.filter-btn').removeClass('active');
    $('.filter-btn[data-filter="all"]').addClass('active');
    currentFilter = 'all';
    window.fetchProducts('');
    updateClearButton('');
}

function showLoadingState() {
    $('#loadingProducts').show();
    $('#productList').hide();
    $('#emptyState').hide();
}

function hideLoadingState() {
    $('#loadingProducts').hide();
    $('#productList').show();
    $('#emptyState').hide();
}

function showEmptyState() {
    $('#loadingProducts').hide();
    $('#emptyState').show();
    $('#productList').hide();
}

function hideEmptyState() {
    $('#emptyState').hide();
    if ($('#productList').html().trim() !== '') {
        $('#productList').show();
    }
}

function loadProductsNow(query) {
    const currentPath = window.location.pathname;
    let apiUrl = window.urlAPI || urlAPI;
    
    if (!apiUrl || apiUrl.includes('localhost')) {
        if (currentPath.includes('/pages/')) {
            apiUrl = '../js/productsRequested/';
        } else {
            apiUrl = './js/productsRequested/';
        }
    }
    
    console.log('URL completa:', apiUrl + 'searchProducts.php');
    console.log('Dominio actual:', window.location.origin);
    
    $('#loadingProducts').show();
    $('#productList').hide();
    $('#emptyState').hide();
    
    $.get(apiUrl + 'searchProducts.php', { query: query })
        .done(function(response) {
            console.log('✅ Respuesta recibida:', response.length, 'caracteres');
            
            if (response && response.indexOf('product-card') !== -1) {
                $('#productList').html(response).show();
                $('#loadingProducts').hide();
                $('#emptyState').hide();
                
                setTimeout(() => {
                    applyCurrentFilters();
                    applyViewMode();
                    
                    if (typeof window.initializeProducts === 'function') {
                        window.initializeProducts();
                    }
                    
                    updateInitialProductStats(response);
                }, 100);
                
            } else {
                $('#loadingProducts').hide();
                $('#emptyState').show();
                $('#productList').hide();
                $('#totalProducts').text('0');
            }
        })
        .fail(function(xhr, status, error) {
            $('#loadingProducts').hide();
            $('#emptyState').show();
            $('#productList').hide();
            $('#totalProducts').text('0');
        });
}

window.fetchProducts = function(query) {
    loadProductsNow(query);
};

function updateInitialProductStats(response) {
    const productCount = (response.match(/product-card/g) || []).length;
}
</script>