<?php
/** Layout de Solicitar insumos (vista simple para el personal). Variable: $page */

$rol = $_SESSION['rol'] ?? '';
$tabs = [['catalogo', 'Catálogo', 'fa-store'], ['pedidos', 'Mis pedidos', 'fa-receipt'], ['requisiciones', 'Requisiciones', 'fa-clipboard-list']];
$titulo = ['pedidos' => 'Mis pedidos', 'requisiciones' => 'Requisiciones'][$page] ?? 'Solicitar insumos';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php include base_path('views/shared/head.php'); ?>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased pb-20 sm:pb-0">
<header class="bg-ink text-white sticky top-0 z-30">
    <div class="max-w-6xl mx-auto h-16 px-4 flex items-center justify-between gap-4">
        <a href="<?= app_url('solicitar') ?>" class="flex items-center gap-3">
            <img src="<?= asset('img/Kalli-Amarillo.png') ?>" alt="" class="h-8">
            <span class="font-bold hidden sm:block"><?= e(config('app.short_name')) ?></span>
        </a>
        <nav class="hidden sm:flex items-center gap-1">
            <?php foreach ($tabs as [$slug, $label, $icon]): ?>
                <a href="<?= app_url("solicitar/{$slug}") ?>" class="px-4 py-2 rounded-lg text-sm font-medium <?= $page === $slug ? 'bg-brand-400 text-ink' : 'hover:bg-white/10' ?>">
                    <i class="fa-solid <?= $icon ?> mr-2"></i><?= e($label) ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="flex items-center gap-2">
            <?php if (in_array($rol, ['Administrador', 'Logistica'], true)): ?>
                <a href="<?= app_url('gestor') ?>" class="hidden sm:inline-flex px-3 py-2 rounded-lg text-sm hover:bg-white/10"><i class="fa-solid fa-gauge mr-2"></i>Gestor</a>
            <?php endif; ?>
            <span class="text-sm text-slate-300 hidden md:block"><?= e($_SESSION['nombre'] ?? '') ?></span>
            <button id="btn-cart" class="relative w-10 h-10 rounded-lg bg-brand-400 text-ink hover:bg-brand-500">
                <i class="fa-solid fa-cart-shopping"></i>
                <span id="cart-count" class="absolute -top-1.5 -right-1.5 min-w-5 h-5 px-1 rounded-full bg-red-500 text-white text-[11px] font-bold hidden items-center justify-center">0</span>
            </button>
            <button id="btn-logout" title="Cerrar sesión" class="w-10 h-10 rounded-lg hover:bg-white/10"><i class="fa-solid fa-right-from-bracket"></i></button>
        </div>
    </div>
</header>

<main class="max-w-6xl mx-auto p-4">
    <?php include base_path("views/solicitar/pages/{$page}.php"); ?>
</main>

<nav class="sm:hidden fixed bottom-0 inset-x-0 z-30 bg-white border-t border-slate-200 grid grid-cols-3">
    <?php foreach ($tabs as [$slug, $label, $icon]): ?>
        <a href="<?= app_url("solicitar/{$slug}") ?>" class="py-3 text-center text-xs font-medium <?= $page === $slug ? 'text-brand-600' : 'text-slate-500' ?>">
            <i class="fa-solid <?= $icon ?> block text-lg mb-0.5"></i><?= e($label) ?>
        </a>
    <?php endforeach; ?>
</nav>

<script>
    document.getElementById('btn-logout').onclick = async () => {
        try { await U.api('AuthController/logout.php', { method: 'POST' }); } catch (e) { /* se redirige igualmente */ }
        window.location.href = U.url('index.php?page=login');
    };
    // En páginas sin carrito el botón lleva al catálogo
    if (!document.getElementById('cart-drawer')) {
        document.getElementById('btn-cart').onclick = () => { window.location.href = U.url('solicitar/catalogo'); };
    }
</script>
</body>
</html>
