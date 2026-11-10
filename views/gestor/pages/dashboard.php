<?php
/** Gestor · panel de control */
$hora = (int) date('G');
$saludo = $hora < 12 ? 'Buenos días' : ($hora < 19 ? 'Buenas tardes' : 'Buenas noches');
$primerNombre = explode(' ', trim((string) ($_SESSION['nombre'] ?? '')))[0] ?: 'equipo';
$meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
$dias = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
$fecha = ucfirst($dias[(int) date('w')]) . ' ' . date('j') . ' de ' . $meses[(int) date('n') - 1] . ' de ' . date('Y');
?>
<div class="space-y-6">
    <section class="relative overflow-hidden rounded-3xl bg-ink text-white p-6 sm:p-8">
        <div class="absolute -right-16 -top-20 w-72 h-72 rounded-full bg-brand-400/25 blur-3xl"></div>
        <div class="absolute right-24 -bottom-24 w-64 h-64 rounded-full bg-brand-600/20 blur-3xl"></div>
        <div class="relative flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
            <div>
                <p class="text-sm text-brand-400 font-semibold"><?= e($fecha) ?></p>
                <h2 class="mt-1 text-2xl sm:text-3xl font-extrabold"><?= e($saludo) ?>, <?= e($primerNombre) ?></h2>
                <p id="resumen" class="mt-2 text-slate-300 text-sm max-w-xl">Cargando resumen de la operación...</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="<?= app_url('gestor/pedidos') ?>" class="px-4 py-2.5 rounded-xl bg-brand-400 text-ink text-sm font-bold hover:bg-brand-500 transition"><i class="fa-solid fa-truck-ramp-box mr-2"></i>Pedidos</a>
                <a href="<?= app_url('gestor/requisiciones') ?>" class="px-4 py-2.5 rounded-xl bg-white/10 text-sm font-semibold hover:bg-white/20 transition"><i class="fa-solid fa-clipboard-list mr-2"></i>Requisiciones</a>
                <a href="<?= app_url('gestor/reportes') ?>" class="px-4 py-2.5 rounded-xl bg-white/10 text-sm font-semibold hover:bg-white/20 transition"><i class="fa-solid fa-chart-line mr-2"></i>Reportes</a>
            </div>
        </div>
    </section>

    <section id="kpis" class="grid grid-cols-2 xl:grid-cols-4 gap-4">
        <?php for ($i = 0; $i < 4; $i++): ?><div class="h-36 rounded-2xl bg-white border border-slate-200 animate-pulse"></div><?php endfor; ?>
    </section>

    <div class="grid lg:grid-cols-3 gap-6">
        <section class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm">
            <header class="px-5 py-4 flex items-start justify-between gap-3">
                <div>
                    <h2 class="font-bold">Ventas de los últimos 14 días</h2>
                    <p id="serie-total" class="text-sm text-slate-500"></p>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-600">Sin cancelados</span>
            </header>
            <div id="chart" class="px-5 pb-5"></div>
        </section>

        <section class="bg-white rounded-2xl border border-slate-200 shadow-sm">
            <header class="px-5 py-4 border-b border-slate-100"><h2 class="font-bold">Requiere tu atención</h2></header>
            <ul id="alertas" class="p-3 space-y-2"></ul>
        </section>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        <section class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm">
            <header class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h2 class="font-bold">Pedidos recientes</h2>
                <a href="<?= app_url('gestor/pedidos') ?>" class="text-sm text-brand-600 font-semibold hover:underline">Ver todos <i class="fa-solid fa-arrow-right text-xs"></i></a>
            </header>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-wide text-slate-500 bg-slate-50">
                        <tr><th class="px-5 py-3">Folio</th><th class="px-3 py-3">Sucursal</th><th class="px-3 py-3 hidden md:table-cell">Solicitante</th><th class="px-3 py-3">Estado</th><th class="px-5 py-3 text-right">Total</th></tr>
                    </thead>
                    <tbody id="recientes" class="divide-y divide-slate-100"></tbody>
                </table>
            </div>
        </section>

        <div class="space-y-6">
            <section class="bg-white rounded-2xl border border-slate-200 shadow-sm">
                <header class="px-5 py-4 border-b border-slate-100"><h2 class="font-bold">Sucursales del mes</h2></header>
                <ul id="sucursales" class="p-5 space-y-4"></ul>
            </section>
            <section class="bg-white rounded-2xl border border-slate-200 shadow-sm">
                <header class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h2 class="font-bold">Stock bajo</h2>
                    <a href="<?= app_url('gestor/productos') ?>" class="text-sm text-brand-600 font-semibold hover:underline">Productos</a>
                </header>
                <ul id="bajo-stock" class="divide-y divide-slate-100"></ul>
            </section>
            <section class="bg-white rounded-2xl border border-slate-200 shadow-sm">
                <header class="px-5 py-4 border-b border-slate-100"><h2 class="font-bold">Más solicitados <span class="text-xs font-normal text-slate-500">(30 días)</span></h2></header>
                <ul id="top" class="divide-y divide-slate-100"></ul>
            </section>
        </div>
    </div>
</div>

<script>
(() => {
    const estilos = { Abierto: 'bg-sky-100 text-sky-800', 'En transito': 'bg-amber-100 text-amber-800', Cerrado: 'bg-emerald-100 text-emerald-800', Cancelado: 'bg-red-100 text-red-800' };
    const etiquetas = { Abierto: 'Abierto', 'En transito': 'En tránsito', Cerrado: 'Entregado', Cancelado: 'Cancelado' };
    const vacio = (t) => `<li class="px-5 py-6 text-center text-sm text-slate-500">${t}</li>`;
    const $ = (id) => document.getElementById(id);

    const tendencia = (actual, previo) => {
        actual = Number(actual) || 0; previo = Number(previo) || 0;
        if (!previo) return actual ? '<span class="text-xs font-semibold text-slate-500">Sin mes previo</span>' : '';
        const pct = ((actual - previo) / previo) * 100;
        const sube = pct >= 0;
        return `<span class="inline-flex items-center gap-1 text-xs font-bold ${sube ? 'text-emerald-600' : 'text-red-600'}"><i class="fa-solid ${sube ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down'}"></i>${sube ? '+' : ''}${pct.toFixed(1)}% vs mes anterior</span>`;
    };

    const kpi = (label, value, sub, icon, tone, href) => `
        <a href="${href}" class="group bg-white rounded-2xl border border-slate-200 shadow-sm p-5 hover:shadow-md hover:-translate-y-0.5 hover:border-brand-400 transition block">
            <div class="flex items-center justify-between">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center ${tone}"><i class="fa-solid ${icon}"></i></div>
                <i class="fa-solid fa-arrow-up-right-from-square text-xs text-slate-300 group-hover:text-brand-500 transition"></i>
            </div>
            <p class="mt-4 text-2xl font-extrabold tracking-tight">${value}</p>
            <p class="text-sm text-slate-500">${label}</p>
            <div class="mt-1 min-h-[1rem] text-xs text-slate-500">${sub}</div>
        </a>`;

    const alerta = (icon, tone, titulo, detalle, href) => `
        <li><a href="${href}" class="flex items-center gap-3 p-3 rounded-xl border border-slate-100 hover:border-brand-400 hover:bg-slate-50 transition">
            <span class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 ${tone}"><i class="fa-solid ${icon}"></i></span>
            <span class="flex-1 min-w-0"><span class="block text-sm font-semibold">${titulo}</span><span class="block text-xs text-slate-500 truncate">${detalle}</span></span>
            <i class="fa-solid fa-chevron-right text-xs text-slate-300"></i>
        </a></li>`;

    function grafica(serie) {
        const dias = [];
        for (let i = 13; i >= 0; i--) {
            const d = new Date(); d.setDate(d.getDate() - i);
            const k = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
            const r = serie[k] || {};
            dias.push({ k, d, monto: Number(r.monto) || 0, pedidos: Number(r.pedidos) || 0 });
        }
        const max = Math.max(...dias.map((x) => x.monto), 1);
        const total = dias.reduce((a, x) => a + x.monto, 0);
        const pedidos = dias.reduce((a, x) => a + x.pedidos, 0);
        $('serie-total').textContent = `${U.money(total)} en ${pedidos} pedidos`;
        const nombres = ['D', 'L', 'M', 'M', 'J', 'V', 'S'];
        $('chart').innerHTML = `
            <div class="relative h-52 flex items-end gap-1.5 sm:gap-2 border-b border-slate-200">
                <div class="absolute inset-x-0 top-0 border-t border-dashed border-slate-200"></div>
                <div class="absolute inset-x-0 top-1/2 border-t border-dashed border-slate-100"></div>
                ${dias.map((x, i) => {
                    const h = x.monto ? Math.max((x.monto / max) * 100, 4) : 0;
                    const hoy = i === 13;
                    return `<div class="relative flex-1 h-full flex items-end group">
                        <div class="w-full rounded-t-lg transition-all ${hoy ? 'bg-brand-500' : 'bg-brand-400/60 group-hover:bg-brand-500'}" style="height:${h}%"></div>
                        <div class="pointer-events-none absolute bottom-full left-1/2 -translate-x-1/2 mb-1 z-10 hidden group-hover:block whitespace-nowrap rounded-lg bg-ink px-2.5 py-1.5 text-xs text-white shadow-lg">
                            <b>${x.d.getDate()}/${x.d.getMonth() + 1}</b> · ${U.money(x.monto)} · ${x.pedidos} ped.
                        </div>
                    </div>`;
                }).join('')}
            </div>
            <div class="flex gap-1.5 sm:gap-2 mt-2 text-[11px] text-slate-400">
                ${dias.map((x, i) => `<div class="flex-1 text-center ${i === 13 ? 'font-bold text-brand-600' : ''}">${nombres[x.d.getDay()]}<span class="hidden sm:block">${x.d.getDate()}</span></div>`).join('')}
            </div>`;
    }

    U.api('gestor/dashboard.php').then((d) => {
        const e = d.estados, p = (k) => Number(e[k] || 0);
        const abiertos = p('Abierto'), transito = p('En transito');
        const bajo = Number(d.inventario.bajo_stock || 0);
        const req = d.requisiciones || {};
        const porAprobar = Number(req.por_aprobar || 0), porRecibir = Number(req.por_recibir || 0);

        $('resumen').textContent = `Hoy se registraron ${d.hoy} pedido${d.hoy == 1 ? '' : 's'}. Tienes ${abiertos} por surtir y ${transito} en camino.`;

        $('kpis').innerHTML =
            kpi('Ventas del mes', U.money(d.mes.monto), tendencia(d.mes.monto, d.mes_anterior?.monto), 'fa-sack-dollar', 'bg-emerald-100 text-emerald-700', U.url('gestor/reportes')) +
            kpi('Pedidos del mes', U.num(d.mes.pedidos, 0), tendencia(d.mes.pedidos, d.mes_anterior?.pedidos), 'fa-receipt', 'bg-sky-100 text-sky-700', U.url('gestor/pedidos')) +
            kpi('Pedidos por atender', abiertos + transito, `${abiertos} abiertos · ${transito} en tránsito`, 'fa-truck-fast', 'bg-amber-100 text-amber-700', U.url('gestor/pedidos')) +
            kpi('Valor de inventario', U.money(d.inventario.valor), `${U.num(d.inventario.productos, 0)} productos`, 'fa-warehouse', 'bg-brand-100 text-brand-600', U.url('gestor/productos'));

        grafica(d.serie || {});

        const alertas = [];
        if (abiertos) alertas.push(alerta('fa-inbox', 'bg-sky-100 text-sky-700', `${abiertos} pedido${abiertos > 1 ? 's' : ''} por surtir`, 'Pendientes de preparar en almacén', U.url('gestor/pedidos')));
        if (porAprobar) alertas.push(alerta('fa-clipboard-check', 'bg-amber-100 text-amber-700', `${porAprobar} requisición${porAprobar > 1 ? 'es' : ''} por aprobar`, 'Enviadas por supervisores', U.url('gestor/requisiciones')));
        if (porRecibir) alertas.push(alerta('fa-dolly', 'bg-indigo-100 text-indigo-700', `${porRecibir} requisición${porRecibir > 1 ? 'es' : ''} por recibir`, 'Aprobadas, esperando mercancía', U.url('gestor/requisiciones')));
        if (bajo) alertas.push(alerta('fa-triangle-exclamation', 'bg-red-100 text-red-700', `${bajo} producto${bajo > 1 ? 's' : ''} con stock bajo`, 'Por debajo del mínimo', U.url('gestor/productos')));
        $('alertas').innerHTML = alertas.join('') || `<li class="px-4 py-8 text-center"><i class="fa-solid fa-circle-check text-3xl text-emerald-500"></i><p class="mt-2 text-sm font-semibold">Todo en orden</p><p class="text-xs text-slate-500">No hay pendientes por ahora.</p></li>`;

        $('recientes').innerHTML = d.recientes.map((r) => `
            <tr class="hover:bg-slate-50">
                <td class="px-5 py-3 font-semibold">${U.esc(r.ComandaID)}</td>
                <td class="px-3 py-3">${U.esc(r.sucursal || '-')}</td>
                <td class="px-3 py-3 hidden md:table-cell text-slate-600">${U.esc(r.solicitante || '-')}</td>
                <td class="px-3 py-3"><span class="px-2.5 py-1 rounded-full text-xs font-semibold ${estilos[r.estado] || 'bg-slate-100'}">${etiquetas[r.estado] || U.esc(r.estado)}</span></td>
                <td class="px-5 py-3 text-right font-semibold">${U.money(r.total)}</td>
            </tr>`).join('') || '<tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">Sin pedidos todavía.</td></tr>';

        const maxS = Math.max(...(d.por_sucursal || []).map((s) => Number(s.monto)), 1);
        $('sucursales').innerHTML = (d.por_sucursal || []).map((s) => `
            <li>
                <div class="flex items-center justify-between text-sm"><span class="font-medium truncate pr-2">${U.esc(s.sucursal)}</span><span class="font-semibold">${U.money(s.monto)}</span></div>
                <div class="mt-1.5 h-2 rounded-full bg-slate-100 overflow-hidden"><div class="h-full rounded-full bg-gradient-to-r from-brand-400 to-brand-600" style="width:${Math.max((Number(s.monto) / maxS) * 100, 3)}%"></div></div>
                <p class="mt-1 text-xs text-slate-500">${s.pedidos} pedido${s.pedidos == 1 ? '' : 's'}</p>
            </li>`).join('') || '<li class="text-center text-sm text-slate-500">Sin ventas este mes.</li>';

        $('bajo-stock').innerHTML = d.bajo_stock.map((r) => `
            <li class="px-5 py-3 flex items-center justify-between gap-3">
                <span class="text-sm truncate">${U.esc(r.Nombre)}</span>
                <span class="text-xs font-semibold px-2 py-1 rounded-full whitespace-nowrap ${Number(r.Cantidad) <= 0 ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-800'}">${U.num(r.Cantidad, 3)} / mín. ${U.num(r.StockMinimo, 3)}</span>
            </li>`).join('') || vacio('Todo el inventario está por encima del mínimo.');

        $('top').innerHTML = d.mas_solicitados.map((r, i) => `
            <li class="px-5 py-3 flex items-center gap-3 text-sm">
                <span class="w-6 h-6 rounded-full bg-brand-100 text-brand-600 text-xs font-bold flex items-center justify-center">${i + 1}</span>
                <span class="flex-1 truncate">${U.esc(r.Nombre)}</span>
                <span class="text-slate-500">${U.qty(r.cantidad, r.Tipo)}</span>
            </li>`).join('') || vacio('Sin movimientos en los últimos 30 días.');
    }).catch((e) => { $('resumen').textContent = 'No se pudo cargar el resumen.'; U.toast(e.message, 'error'); });
})();
</script>
