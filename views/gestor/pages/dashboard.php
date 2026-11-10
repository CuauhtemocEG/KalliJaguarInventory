<?php /** Gestor · panel de control */ ?>
<div class="space-y-6">
    <section id="kpis" class="grid grid-cols-2 xl:grid-cols-4 gap-4"></section>

    <div class="grid lg:grid-cols-3 gap-6">
        <section class="lg:col-span-2 bg-white rounded-2xl border border-slate-200">
            <header class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h2 class="font-bold">Pedidos recientes</h2>
                <a href="<?= app_url('gestor/pedidos') ?>" class="text-sm text-brand-600 font-semibold hover:underline">Ver todos</a>
            </header>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-left text-xs uppercase text-slate-500">
                        <tr><th class="px-5 py-3">Folio</th><th class="px-3 py-3">Sucursal</th><th class="px-3 py-3 hidden md:table-cell">Solicitante</th><th class="px-3 py-3">Estado</th><th class="px-5 py-3 text-right">Total</th></tr>
                    </thead>
                    <tbody id="recientes" class="divide-y divide-slate-100"></tbody>
                </table>
            </div>
        </section>

        <div class="space-y-6">
            <section class="bg-white rounded-2xl border border-slate-200">
                <header class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h2 class="font-bold">Stock bajo</h2>
                    <a href="<?= app_url('gestor/productos') ?>" class="text-sm text-brand-600 font-semibold hover:underline">Productos</a>
                </header>
                <ul id="bajo-stock" class="divide-y divide-slate-100"></ul>
            </section>
            <section class="bg-white rounded-2xl border border-slate-200">
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

    const kpi = (label, value, icon, tone, href) => `
        <a href="${href}" class="bg-white rounded-2xl border border-slate-200 p-5 hover:border-brand-400 transition block">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center ${tone}"><i class="fa-solid ${icon}"></i></div>
            <p class="mt-3 text-2xl font-extrabold">${value}</p>
            <p class="text-sm text-slate-500">${label}</p>
        </a>`;

    U.api('gestor/dashboard.php').then((d) => {
        const e = d.estados, p = (k) => Number(e[k] || 0);
        document.getElementById('kpis').innerHTML =
            kpi('Pedidos abiertos', p('Abierto'), 'fa-inbox', 'bg-sky-100 text-sky-700', U.url('gestor/pedidos')) +
            kpi('En tránsito', p('En transito'), 'fa-truck', 'bg-amber-100 text-amber-700', U.url('gestor/pedidos')) +
            kpi('Ventas del mes', U.money(d.mes.monto) + `<span class="block text-xs font-medium text-slate-500">${d.mes.pedidos} pedidos</span>`, 'fa-sack-dollar', 'bg-emerald-100 text-emerald-700', U.url('gestor/pedidos')) +
            kpi('Valor de inventario', U.money(d.inventario.valor) + `<span class="block text-xs font-medium text-slate-500">${d.inventario.productos} productos · ${d.inventario.bajo_stock || 0} con stock bajo</span>`, 'fa-warehouse', 'bg-brand-100 text-brand-600', U.url('gestor/productos'));

        document.getElementById('recientes').innerHTML = d.recientes.map((r) => `
            <tr class="hover:bg-slate-50">
                <td class="px-5 py-3 font-medium">${U.esc(r.ComandaID)}</td>
                <td class="px-3 py-3">${U.esc(r.sucursal || '—')}</td>
                <td class="px-3 py-3 hidden md:table-cell">${U.esc(r.solicitante || '—')}</td>
                <td class="px-3 py-3"><span class="px-2.5 py-1 rounded-full text-xs font-semibold ${estilos[r.estado] || 'bg-slate-100'}">${etiquetas[r.estado] || U.esc(r.estado)}</span></td>
                <td class="px-5 py-3 text-right font-semibold">${U.money(r.total)}</td>
            </tr>`).join('') || '<tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">Sin pedidos todavía.</td></tr>';

        document.getElementById('bajo-stock').innerHTML = d.bajo_stock.map((r) => `
            <li class="px-5 py-3 flex items-center justify-between gap-3">
                <span class="text-sm truncate">${U.esc(r.Nombre)}</span>
                <span class="text-xs font-semibold px-2 py-1 rounded-full ${Number(r.Cantidad) <= 0 ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-800'}">${U.num(r.Cantidad, 3)} / mín. ${U.num(r.StockMinimo, 3)}</span>
            </li>`).join('') || vacio('Todo el inventario está por encima del mínimo.');

        document.getElementById('top').innerHTML = d.mas_solicitados.map((r, i) => `
            <li class="px-5 py-3 flex items-center gap-3 text-sm">
                <span class="w-6 h-6 rounded-full bg-brand-100 text-brand-600 text-xs font-bold flex items-center justify-center">${i + 1}</span>
                <span class="flex-1 truncate">${U.esc(r.Nombre)}</span>
                <span class="text-slate-500">${U.qty(r.cantidad, r.Tipo)}</span>
            </li>`).join('') || vacio('Sin movimientos en los últimos 30 días.');
    }).catch((e) => U.toast(e.message, 'error'));
})();
</script>
