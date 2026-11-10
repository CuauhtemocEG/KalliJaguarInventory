<?php /** Cabecera común de /gestor y /solicitar. Variable: $titulo */ ?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
<meta name="theme-color" content="#F5C842">
<title><?= e($titulo) ?> · <?= e(config('app.short_name')) ?></title>
<link rel="icon" href="<?= asset('img/icons/icon-96x96.png') ?>" type="image/png">
<link rel="manifest" href="<?= app_url('manifest.json') ?>">
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: { brand: { 50: '#fffbeb', 100: '#fef3c7', 400: '#fbbf24', 500: '#f59e0b', 600: '#d97706' }, ink: '#0f172a' },
                fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] },
            },
        },
    };
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= asset('js/csrf.js') ?>"></script>
<script>window.APP = { base: <?= json_encode(app_url()) ?>, taxRate: <?= json_encode((float) config('app.tax_rate')) ?>, role: <?= json_encode($_SESSION['rol'] ?? '') ?>, unidades: <?= json_encode(\App\Services\Unidades::paraJs()) ?> };</script>
<script src="<?= asset('js/app.js') ?>"></script>
