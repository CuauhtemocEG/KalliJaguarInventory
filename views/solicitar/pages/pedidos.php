<?php /** Solicitar insumos · historial de pedidos del usuario */ ?>
<div class="space-y-4">
    <div class="flex items-end justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold">Mis pedidos</h1>
            <p id="resumen" class="text-sm text-slate-500"></p>
        </div>
        <a href="<?= app_url('solicitar/catalogo') ?>" class="shrink-0 px-4 py-2.5 rounded-xl bg-brand-400 hover:bg-brand-500 text-ink text-sm font-semibold"><i class="fa-solid fa-plus mr-1"></i>Nueva solicitud</a>
    </div>

    <div class="flex flex-col sm:flex-row gap-2">
        <div class="relative flex-1">
            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input id="buscar" type="search" placeholder="Buscar por folio o sucursal…" class="w-full pl-11 pr-4 py-2.5 rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-brand-400">
        </div>
        <div id="chips" class="flex gap-2 overflow-x-auto [scrollbar-width:none]"></div>
    </div>

    <div id="lista" class="space-y-3"></div>
    <div id="vacio" class="hidden text-center text-slate-500 py-16">
        <i class="fa-solid fa-clipboard-list text-4xl text-slate-300 mb-3"></i>
        <p id="vacio-txt">Todavía no has realizado solicitudes.</p>
    </div>
</div>

<div id="modal-detalle" data-modal class="fixed inset-0 z-50 bg-black/50 hidden items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="bg-white w-full sm:max-w-lg rounded-t-2xl sm:rounded-2xl max-h-[90vh] flex flex-col shadow-2xl">
        <div class="px-5 py-4 border-b border-slate-200 flex items-start justify-between gap-3">
            <div>
                <p class="text-xs text-slate-500">Solicitud</p>
                <h2 id="d-titulo" class="font-bold text-lg leading-tight"></h2>
                <span id="d-estado" class="inline-block mt-1 px-2.5 py-1 rounded-full text-xs font-semibold"></span>
            </div>
            <button data-close class="w-9 h-9 rounded-lg hover:bg-slate-100" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="overflow-y-auto">
            <div id="d-progreso" class="px-5 pt-4"></div>
            <div id="d-meta" class="px-5 pt-4 grid grid-cols-2 gap-3 text-sm"></div>
            <div id="d-cuerpo" class="px-5 py-4 text-sm"></div>
        </div>
        <div class="px-5 py-4 border-t border-slate-200 bg-slate-50 sm:rounded-b-2xl space-y-3">
            <div class="flex justify-between items-center"><span class="text-slate-500 text-sm">Total c/IVA</span><b id="d-total" class="text-lg"></b></div>
            <button id="d-pdf" type="button" class="w-full py-2.5 rounded-xl bg-brand-400 hover:bg-brand-500 text-ink font-semibold"><i class="fa-solid fa-file-pdf mr-2"></i>Ver PDF de la comanda</button>
        </div>
    </div>
</div>

<script>
(() => {
    const $ = (id) => document.getElementById(id);
    const colores = { Abierto: 'bg-sky-100 text-sky-800', 'En transito': 'bg-amber-100 text-amber-800', Cerrado: 'bg-emerald-100 text-emerald-800', Cancelado: 'bg-red-100 text-red-800' };
    const etiquetas = { Abierto: 'Recibido', 'En transito': 'En camino', Cerrado: 'Entregado', Cancelado: 'Cancelado' };
    const fecha = (s) => s ? new Date(String(s).replace(' ', 'T')).toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' }) : '—';
    let pedidos = [], filtro = '';

    const pasos = ['Abierto', 'En transito', 'Cerrado'];
    function progreso(estado) {
        if (estado === 'Cancelado') { return '<p class="rounded-xl bg-red-50 text-red-700 text-sm px-3 py-2"><i class="fa-solid fa-ban mr-1"></i>Esta solicitud fue cancelada. El stock se repuso.</p>'; }
        const i = pasos.indexOf(estado);
        return `<ol class="flex items-center">${pasos.map((p, k) => `
            <li class="flex items-center ${k < 2 ? 'flex-1' : ''}">
                <span class="flex flex-col items-center gap-1">
                    <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs ${k <= i ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-500'}">${k <= i ? '<i class="fa-solid fa-check"></i>' : k + 1}</span>
                    <span class="text-[11px] ${k <= i ? 'font-semibold' : 'text-slate-400'}">${etiquetas[p]}</span>
                </span>
                ${k < 2 ? `<span class="flex-1 h-0.5 mx-1 mb-5 ${k < i ? 'bg-emerald-500' : 'bg-slate-200'}"></span>` : ''}
            </li>`).join('')}</ol>`;
    }

    function renderChips() {
        const cuenta = (k) => k ? pedidos.filter((p) => p.estado === k).length : pedidos.length;
        $('chips').innerHTML = [['', 'Todos'], ...Object.entries(etiquetas)].map(([k, t]) =>
            `<button data-estado="${k}" class="shrink-0 px-3.5 py-2 rounded-full text-sm border ${filtro === k ? 'bg-ink text-white border-ink' : 'bg-white border-slate-200 hover:border-slate-400'}">${t} <span class="opacity-60 text-xs">${cuenta(k)}</span></button>`).join('');
    }

    function render() {
        const q = $('buscar').value.trim().toLowerCase();
        const lista = pedidos.filter((p) => (!filtro || p.estado === filtro) && (!q || (p.ComandaID + ' ' + (p.sucursal || '')).toLowerCase().includes(q)));
        $('vacio').classList.toggle('hidden', lista.length > 0);
        $('vacio-txt').textContent = pedidos.length ? 'No hay solicitudes con esos filtros.' : 'Todavía no has realizado solicitudes.';
        $('resumen').textContent = pedidos.length ? `${pedidos.length} solicitud${pedidos.length === 1 ? '' : 'es'} · ${pedidos.filter((p) => p.estado === 'Abierto' || p.estado === 'En transito').length} en proceso` : '';
        $('lista').innerHTML = lista.map((p) => `
            <button data-id="${U.esc(p.ComandaID)}" class="w-full text-left bg-white rounded-2xl border border-slate-200 p-4 hover:border-brand-400 hover:shadow-sm transition">
                <div class="flex items-center justify-between gap-3">
                    <p class="font-semibold">${U.esc(p.ComandaID)}</p>
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold ${colores[p.estado] || 'bg-slate-100'}">${etiquetas[p.estado] || U.esc(p.estado)}</span>
                </div>
                <div class="mt-2 grid grid-cols-2 sm:grid-cols-4 gap-2 text-sm">
                    <p><span class="block text-xs text-slate-400">Sucursal</span>${U.esc(p.sucursal || '—')}</p>
                    <p><span class="block text-xs text-slate-400">Solicitado</span>${fecha(p.fecha)}</p>
                    <p><span class="block text-xs text-slate-400">Entrega</span>${fecha(p.entrega)}</p>
                    <p class="sm:text-right"><span class="block text-xs text-slate-400">${p.articulos} artículo(s)</span><b>${U.money(p.total)}</b></p>
                </div>
            </button>`).join('');
    }

    async function cargar() {
        $('lista').innerHTML = Array.from({ length: 3 }, () => '<div class="h-24 bg-white rounded-2xl border border-slate-200 animate-pulse"></div>').join('');
        pedidos = (await U.api('solicitar/misPedidos.php')).pedidos;
        renderChips(); render();
    }

    $('chips').addEventListener('click', (e) => { const b = e.target.closest('[data-estado]'); if (b) { filtro = b.dataset.estado; renderChips(); render(); } });
    $('buscar').addEventListener('input', U.debounce(render, 150));

    $('lista').addEventListener('click', async (e) => {
        const b = e.target.closest('[data-id]');
        if (!b) { return; }
        try {
            const id = b.dataset.id, p = pedidos.find((x) => x.ComandaID === id);
            const { detalle } = await U.api('solicitar/misPedidos.php', { query: { comanda: id } });
            $('d-titulo').textContent = id;
            $('d-estado').className = 'inline-block mt-1 px-2.5 py-1 rounded-full text-xs font-semibold ' + (colores[p.estado] || 'bg-slate-100');
            $('d-estado').textContent = etiquetas[p.estado] || p.estado;
            $('d-progreso').innerHTML = progreso(p.estado);
            $('d-meta').innerHTML = `<p><span class="block text-xs text-slate-400">Sucursal</span>${U.esc(p.sucursal || '—')}</p>
                <p><span class="block text-xs text-slate-400">Entrega</span>${fecha(p.entrega)}</p>`;
            $('d-total').textContent = U.money(p.total);
            $('d-pdf').onclick = () => U.pdf(id);
            $('d-cuerpo').innerHTML = `<p class="font-semibold mb-1">Productos (${detalle.length})</p>` + detalle.map((d) => `
                <div class="flex justify-between gap-3 py-2.5 border-b border-slate-100 last:border-0">
                    <span>${U.esc(d.Nombre)}<span class="block text-xs text-slate-400">${U.qty(d.Cantidad, d.Tipo)}</span></span>
                    <span class="font-semibold whitespace-nowrap">${U.money(d.PrecioFinal)}</span>
                </div>`).join('') + (p.estado === 'Abierto' ? '' : '<p class="text-xs text-slate-400 mt-2">Las cantidades reflejan lo realmente surtido por el almacén.</p>');
            U.modal('modal-detalle');
        } catch (err) { U.toast(err.message, 'error'); }
    });

    cargar().catch((e) => U.toast(e.message, 'error'));
})();
</script>
