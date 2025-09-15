<!-- Cart Panel -->
        </div>
    </div>
<div id="cartPanel" class="fixed right-0 top-0 h-full w-80 bg-white shadow-2xl transform translate-x-full transition-transform duration-300 ease-in-out z-50 flex flex-col">
    <div class="bg-gradient-to-r from-accent-yellow-dark to-accent-yellow p-4 text-black">
        <div class="flex items-center justify-between">
            <h4 class="text-lg font-bold">Lista de Solicitud</h4>
            <button id="closeCart" class="p-1 hover:bg-black hover:bg-opacity-10 rounded transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    <div id="cartBody" class="flex-1 p-4 overflow-y-auto">
        <div class="text-center text-gray-500 py-8">
            <i class="fas fa-shopping-cart text-4xl mb-4"></i>
            <p>No hay productos en la lista</p>
        </div>
    </div>
</div>

<!-- Logout Modal -->
<div id="logoutModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl max-w-md mx-4 w-full">
        <div class="p-6 border-b border-gray-200">
            <h5 class="text-lg font-semibold text-gray-900">¿Estás Seguro?</h5>
        </div>
        <div class="p-6">
            <p class="text-gray-600">Presiona <span class="font-semibold">"Cerrar Sesión"</span> para ser redirigido al Login.</p>
        </div>
        <div class="flex justify-end space-x-3 p-6 border-t border-gray-200">
            <button onclick="hideLogoutModal()" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                Cancelar
            </button>
            <a href="index.php?page=logout" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                Cerrar Sesión
            </a>
        </div>
    </div>
</div>

<!-- Scroll to top button -->
<button id="scrollToTop" class="fixed bottom-6 right-6 bg-accent-yellow hover:bg-accent-yellow-dark text-black p-3 rounded-full shadow-lg transition-all transform scale-0 hover:scale-105">
    <i class="fas fa-chevron-up"></i>
</button>

<script>
// Variables globales
let sidebarOpen = false;
let cartOpen = false;

// DOM Elements
const sidebar = document.getElementById('sidebar');
const sidebarOverlay = document.getElementById('sidebarOverlay');
const sidebarToggle = document.getElementById('sidebarToggle');
const closeSidebar = document.getElementById('closeSidebar');
const userDropdown = document.getElementById('userDropdown');
const userMenu = document.getElementById('userMenu');
const cartToggle = document.getElementById('cartToggle');
const cartPanel = document.getElementById('cartPanel');
const closeCart = document.getElementById('closeCart');
const logoutModal = document.getElementById('logoutModal');
const scrollToTop = document.getElementById('scrollToTop');

// Función para manejar estados activos del navbar
function setActiveNavItem() {
    const currentPage = new URLSearchParams(window.location.search).get('page') || 'home';
    const navItems = document.querySelectorAll('.nav-item, .nav-item-hover');
    
    // Remover clases active y selected de todos los elementos
    navItems.forEach(item => {
        item.classList.remove('active', 'selected');
    });
    
    // Agregar clase active al elemento actual
    const activeItem = document.querySelector(`a[href*="page=${currentPage}"], button[onclick*="${currentPage}"]`);
    if (activeItem && (activeItem.classList.contains('nav-item') || activeItem.classList.contains('nav-item-hover'))) {
        activeItem.classList.add('active', 'selected');
    }
}

// Sidebar Functions
function openSidebar() {
    sidebar.classList.remove('-translate-x-full');
    sidebar.classList.add('slide-enter-active');
    sidebarOverlay.classList.remove('hidden');
    sidebarOpen = true;
    document.body.style.overflow = 'hidden';
}

function closeSidebarFunc() {
    sidebar.classList.add('-translate-x-full');
    sidebar.classList.remove('slide-enter-active');
    sidebarOverlay.classList.add('hidden');
    sidebarOpen = false;
    document.body.style.overflow = 'auto';
}

// Toggle Dropdown menus
function toggleDropdown(menuId) {
    const dropdown = document.getElementById(menuId + '-dropdown');
    const icon = document.getElementById(menuId + '-icon');
    
    const isHidden = dropdown.classList.contains('hidden');
    
    // Cerrar todos los otros dropdowns
    const allDropdowns = document.querySelectorAll('[id$="-dropdown"]');
    const allIcons = document.querySelectorAll('[id$="-icon"]');
    
    allDropdowns.forEach(dd => {
        if (dd.id !== menuId + '-dropdown') {
            dd.classList.add('hidden');
        }
    });
    
    allIcons.forEach(ic => {
        if (ic.id !== menuId + '-icon') {
            ic.classList.remove('rotate-90');
        }
    });
    
    // Toggle current dropdown
    if (isHidden) {
        dropdown.classList.remove('hidden');
        icon.classList.add('rotate-90');
    } else {
        dropdown.classList.add('hidden');
        icon.classList.remove('rotate-90');
    }
}

// User dropdown functions
function toggleUserMenu() {
    userMenu.classList.toggle('hidden');
}

// Cart functions
function openCart() {
    cartPanel.classList.remove('translate-x-full');
    cartOpen = true;
}

function closeCartFunc() {
    cartPanel.classList.add('translate-x-full');
    cartOpen = false;
}

// Modal functions
function showLogoutModal() {
    logoutModal.classList.remove('hidden');
    userMenu.classList.add('hidden');
}

function hideLogoutModal() {
    logoutModal.classList.add('hidden');
}

// Scroll to top function
function scrollToTopFunc() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    // Sidebar events
    sidebarToggle?.addEventListener('click', openSidebar);
    closeSidebar?.addEventListener('click', closeSidebarFunc);
    sidebarOverlay?.addEventListener('click', closeSidebarFunc);
    
    // User dropdown events
    userDropdown?.addEventListener('click', function(e) {
        e.stopPropagation();
        toggleUserMenu();
    });
    
    // Cart events
    cartToggle?.addEventListener('click', function(e) {
        e.stopPropagation();
        openCart();
    });
    closeCart?.addEventListener('click', closeCartFunc);
    
    // Scroll to top
    scrollToTop?.addEventListener('click', scrollToTopFunc);
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!userDropdown?.contains(e.target)) {
            userMenu?.classList.add('hidden');
        }
    });
    
    // Keyboard events
    document.addEventListener('keydown', function(e) {
        // Escape key closes modals and sidebars
        if (e.key === 'Escape') {
            if (sidebarOpen) closeSidebarFunc();
            if (cartOpen) closeCartFunc();
            if (!logoutModal?.classList.contains('hidden')) hideLogoutModal();
            userMenu?.classList.add('hidden');
        }
    });
    
    // Scroll events for scroll-to-top button
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 100) {
            scrollToTop?.classList.remove('scale-0');
        } else {
            scrollToTop?.classList.add('scale-0');
        }
    });
    
    // Handle responsive behavior
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 1024) {
            // Desktop: ensure sidebar is visible and overlay is hidden
            sidebarOverlay?.classList.add('hidden');
            document.body.style.overflow = 'auto';
        } else if (sidebarOpen) {
            // Mobile: if sidebar is open, ensure overlay is visible
            sidebarOverlay?.classList.remove('hidden');
        }
    });
});

// Auto-close dropdowns after some time of inactivity
let dropdownTimeout;
function resetDropdownTimeout() {
    clearTimeout(dropdownTimeout);
    dropdownTimeout = setTimeout(() => {
        userMenu?.classList.add('hidden');
    }, 5000);
}

// Reset timeout on user interaction
document.addEventListener('mousemove', resetDropdownTimeout);
document.addEventListener('keydown', resetDropdownTimeout);

// Cart functionality (placeholder for actual cart logic)
function addToCart(productId, productName, quantity = 1) {
    // This would integrate with your existing cart system
    console.log('Adding to cart:', productId, productName, quantity);
    
    // Update cart badge
    const cartBadge = document.getElementById('cartBadge');
    if (cartBadge) {
        cartBadge.classList.remove('hidden');
        // Update count logic here
    }
}

// Search functionality (placeholder)
function initializeSearch() {
    const searchInput = document.querySelector('input[placeholder="Buscar..."]');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const query = e.target.value;
            if (query.length > 2) {
                // Implement search logic here
                console.log('Searching for:', query);
            }
        });
        
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                // Trigger search
                console.log('Search triggered');
            }
        });
    }
}

// Initialize search when DOM is loaded
document.addEventListener('DOMContentLoaded', initializeSearch);

// Utility function to highlight active nav item
function setActiveNavItem() {
    const currentPage = new URLSearchParams(window.location.search).get('page');
    const navLinks = document.querySelectorAll('nav a[href*="page="]');
    
    navLinks.forEach(link => {
        const linkPage = new URL(link.href).searchParams.get('page');
        if (linkPage === currentPage) {
            link.classList.add('bg-white', 'bg-opacity-10', 'text-accent-yellow');
            // Find the parent dropdown if it exists
            let parent = link.closest('[id$="-dropdown"]');
            if (parent) {
                const parentId = parent.id.replace('-dropdown', '');
                toggleDropdown(parentId);
            }
        }
    });
}

// Set active nav item when page loads
document.addEventListener('DOMContentLoaded', setActiveNavItem);

// Show loading states
function showLoading() {
    const content = document.getElementById('content');
    if (content) {
        content.innerHTML = `
            <div class="flex items-center justify-center h-64">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-accent-yellow"></div>
                <span class="ml-3 text-gray-600">Cargando...</span>
            </div>
        `;
    }
}

// Handle navigation with loading states
document.addEventListener('DOMContentLoaded', function() {
    const navLinks = document.querySelectorAll('a[href^="index.php"]');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Show loading state for navigation
            setTimeout(showLoading, 100);
        });
    });
});
</script>

<!-- Cerrar divs del layout principal -->
        </div>
    </div>
</div>

</body>
</html>
