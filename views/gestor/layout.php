<?php
/** Layout del Gestor (administrativo). Variable: $page (views/gestor/pages/<page>.php) */

$menu = [
    ['dashboard', 'Panel', 'fa-chart-pie', ['Administrador', 'Logistica']],
    ['pedidos', 'Pedidos', 'fa-truck-ramp-box', ['Administrador', 'Logistica']],
    ['requisiciones', 'Requisiciones', 'fa-clipboard-list', ['Administrador', 'Logistica']],
    ['productos', 'Productos', 'fa-boxes-stacked', ['Administrador', 'Logistica']],
    ['categorias', 'Categorías', 'fa-layer-group', ['Administrador']],
    ['etiquetas', 'Etiquetas', 'fa-tags', ['Administrador']],
    ['reportes', 'Reportes', 'fa-chart-line', ['Administrador', 'Logistica']],
    ['inventario', 'Movimientos', 'fa-clock-rotate-left', ['Administrador', 'Logistica']],
    ['sucursales', 'Sucursales', 'fa-store', ['Administrador']],
    ['usuarios', 'Usuarios', 'fa-users', ['Administrador']],
    ['configuracion', 'Configuración', 'fa-sliders', ['Administrador']],
];
$rol = $_SESSION['rol'] ?? '';
$menu = array_filter($menu, static fn ($m) => in_array($rol, $m[3], true));
$titulos = array_column($menu, 1, 0);
$titulo = $titulos[$page] ?? 'Gestor';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php include base_path('views/shared/head.php'); ?>
<style>
@media (max-width: 639px) {
    [data-modal] { align-items: flex-end !important; padding: 0 !important; }
    [data-modal] > * { max-width: none !important; border-bottom-left-radius: 0 !important; border-bottom-right-radius: 0 !important; }
}
</style>
</head>
<body class="bg-slate-100 text-slate-800 font-sans antialiased">
<div class="min-h-screen lg:flex">
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-64 -translate-x-full lg:translate-x-0 lg:sticky lg:top-0 lg:h-screen transition-transform bg-ink text-slate-300 flex flex-col">
        <div class="h-16 flex items-center gap-3 px-5 border-b border-white/10">
            <img src="<?= asset('img/Kalli-Amarillo.png') ?>" alt="" class="h-8">
            <div class="leading-tight">
                <p class="text-white font-bold text-sm"><?= e(config('app.short_name')) ?></p>
                <p class="text-[11px] text-brand-400 uppercase tracking-wider">Gestor</p>
            </div>
        </div>
        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
            <?php foreach ($menu as [$slug, $label, $icon]): ?>
                <a href="<?= app_url("gestor/{$slug}") ?>"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition <?= $page === $slug ? 'bg-brand-400 text-ink' : 'hover:bg-white/10 hover:text-white' ?>">
                    <i class="fa-solid <?= $icon ?> w-5 text-center"></i><?= e($label) ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="p-3 border-t border-white/10 space-y-1">
            <a href="<?= app_url('solicitar') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm hover:bg-white/10 hover:text-white">
                <i class="fa-solid fa-cart-shopping w-5 text-center"></i>Solicitar insumos
            </a>
            <button id="btn-logout" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm hover:bg-red-500/20 hover:text-red-300">
                <i class="fa-solid fa-right-from-bracket w-5 text-center"></i>Cerrar sesión
            </button>
        </div>
    </aside>
    <div id="backdrop" class="fixed inset-0 z-30 bg-black/50 hidden lg:hidden"></div>

    <div class="flex-1 min-w-0 flex flex-col">
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 sticky top-0 z-20">
            <div class="flex items-center gap-3">
                <button id="btn-menu" class="lg:hidden w-10 h-10 rounded-lg hover:bg-slate-100"><i class="fa-solid fa-bars"></i></button>
                <h1 class="text-lg font-bold"><?= e($titulo) ?></h1>
            </div>
            <div class="flex items-center gap-3 text-right">
                <div class="leading-tight hidden sm:block">
                    <p class="text-sm font-semibold"><?= e($_SESSION['nombre'] ?? '') ?></p>
                    <p class="text-xs text-slate-500"><?= e($rol) ?></p>
                </div>
                <div class="w-9 h-9 rounded-full bg-brand-400 text-ink font-bold flex items-center justify-center">
                    <?= e(mb_strtoupper(mb_substr((string) ($_SESSION['nombre'] ?? '?'), 0, 1))) ?>
                </div>
            </div>
        </header>
        <main class="flex-1 p-4 sm:p-6">
            <?php include base_path("views/gestor/pages/{$page}.php"); ?>
        </main>
    </div>
</div>
<script>
    const sidebar = document.getElementById('sidebar'), backdrop = document.getElementById('backdrop');
    const toggleMenu = (open) => { sidebar.classList.toggle('-translate-x-full', !open); backdrop.classList.toggle('hidden', !open); };
    document.getElementById('btn-menu').onclick = () => toggleMenu(true);
    backdrop.onclick = () => toggleMenu(false);
    document.getElementById('btn-logout').onclick = async () => {
        try { await U.api('AuthController/logout.php', { method: 'POST' }); } catch (e) { /* se redirige igualmente */ }
        window.location.href = U.url('index.php?page=login');
    };
</script>
</body>
</html>
