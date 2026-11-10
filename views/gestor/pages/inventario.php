<?php /** Gestor · Inventario: movimientos y bitácoras de stock/precio */ ?>
<div class="space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="inline-flex rounded-xl bg-white border border-slate-200 p-1 text-sm font-medium" id="tabs">
            <button data-vista="movimientos" class="px-4 py-2 rounded-lg">Movimientos</button>
            <button data-vista="stock" class="px-4 py-2 rounded-lg">Cambios de stock</button>
            <button data-vista="precio" class="px-4 py-2 rounded-lg">Cambios de precio</button>
        </div>
        <button id="btn-entrada" class="px-4 py-2.5 rounded-lg bg-brand-400 hover:bg-brand-500 text-ink font-semibold"><i class="fa-solid fa-truck-ramp-box mr-1"></i> Entrada de mercancía</button>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
        <input id="f-q" type="search" placeholder="Producto o UPC…" autocomplete="off" class="lg:col-span-2 rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
        <input id="f-desde" type="date" title="Desde" class="rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
        <input id="f-hasta" type="date" title="Hasta" class="rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
        <select id="f-tipo" class="rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
            <option value="">Entradas y salidas</option><option value="Entrada">Entradas</option><option value="Salida">Salidas</option>
        </select>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-x-auto">
        <table class="w-full text-sm">
            <thead id="thead" class="bg-slate-50 text-left text-xs uppercase text-slate-500"></thead>
            <tbody id="tbody" class="divide-y divide-slate-100"></tbody>
        </table>
    </div>
    <p id="vacio" class="hidden text-center text-slate-500 py-12">Sin registros para los filtros seleccionados.</p>

    <div class="flex items-center justify-between text-sm text-slate-600">
        <span id="resumen"></span>
        <div class="flex gap-2">
            <button id="pg-prev" class="px-3 py-2 rounded-lg border border-slate-200 bg-white disabled:opacity-40">Anterior</button>
            <button id="pg-next" class="px-3 py-2 rounded-lg border border-slate-200 bg-white disabled:opacity-40">Siguiente</button>
        </div>
    </div>
</div>

<div id="modal-entrada" data-modal class="fixed inset-0 z-50 bg-black/50 hidden items-center justify-center p-4">
    <form id="form-entrada" class="bg-white rounded-2xl w-full max-w-md" novalidate>
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
            <h3 class="font-bold text-lg">Entrada de mercancía</h3>
            <button type="button" data-close class="w-9 h-9 rounded-lg hover:bg-slate-100"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="p-5 space-y-4">
            <div class="relative">
                <label class="block text-xs font-semibold text-slate-600">Producto *
                    <input id="e-buscar" type="search" autocomplete="off" placeholder="Buscar por nombre o UPC…" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-normal">
                </label>
                <div id="e-resultados" class="hidden absolute z-10 left-0 right-0 mt-1 bg-white border border-slate-200 rounded-xl shadow-lg max-h-56 overflow-y-auto"></div>
                <p id="e-sel" class="hidden mt-2 text-sm bg-slate-50 rounded-lg px-3 py-2"></p>
            </div>
            <label class="block text-xs font-semibold text-slate-600">Cantidad recibida *
                <input id="e-cantidad" type="number" min="0" step="0.001" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-normal">
            </label>
        </div>
        <div class="px-5 py-4 border-t border-slate-200 flex justify-end gap-2">
            <button type="button" data-close class="px-4 py-2.5 rounded-lg border border-slate-200 text-sm font-medium">Cancelar</button>
            <button id="e-guardar" type="submit" class="px-5 py-2.5 rounded-lg bg-brand-400 hover:bg-brand-500 text-ink font-semibold disabled:opacity-50">Registrar</button>
        </div>
    </form>
</div>

<script>
(() => {
    const $ = (id) => document.getElementById(id);
    const LIMIT = 25;
    let vista = 'movimientos', page = 1, pages = 1, seleccionado = null;

    const fecha = (s) => s ? new Date(String(s).replace(' ', 'T')).toLocaleString('es-MX', { dateStyle: 'medium', timeStyle: 'short' }) : '—';
    const th = (...c) => '<tr>' + c.map((x) => `<th class="px-4 py-3 ${x.startsWith('>') ? 'text-right' : ''}">${x.replace(/^>/, '')}</th>`).join('') + '</tr>';
    const prod = (r) => `<p class="font-medium">${U.esc(r.ProductoNombre || 'Producto eliminado')}</p><p class="text-xs text-slate-500">${U.esc(r.UPC || '')}</p>`;
    const delta = (a, b, fmt) => {
        const d = Number(b) - Number(a);
        return `<span class="${d >= 0 ? 'text-emerald-600' : 'text-red-600'} font-semibold">${d >= 0 ? '+' : ''}${fmt(d)}</span>`;
    };

    const COLUMNAS = {
        movimientos: {
            head: th('Fecha', 'Producto', 'Tipo', '>Cantidad', '>Importe c/IVA', 'Origen / destino', 'Usuario', 'Estado'),
            row: (r) => `<tr>
                <td class="px-4 py-3 whitespace-nowrap">${fecha(r.Fecha)}</td><td class="px-4 py-3">${prod(r)}</td>
                <td class="px-4 py-3"><span class="text-xs px-2 py-0.5 rounded-full ${r.TipoMovimiento === 'Entrada' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'}">${U.esc(r.TipoMovimiento)}</span></td>
                <td class="px-4 py-3 text-right font-semibold whitespace-nowrap">${U.qty(r.Cantidad, r.ProductoTipo)}</td>
                <td class="px-4 py-3 text-right whitespace-nowrap">${r.TipoMovimiento === 'Salida' && Number(r.PrecioFinal) > 0 ? U.money(r.PrecioFinal) : '—'}</td>
                <td class="px-4 py-3 text-slate-600">${U.esc(r.SucursalNombre || '')}${r.ComandaID ? ` <span class="text-xs text-slate-400">#${U.esc(r.ComandaID)}</span>` : ''}${!r.SucursalNombre && !r.ComandaID ? '—' : ''}</td>
                <td class="px-4 py-3 text-slate-600">${U.esc(r.UsuarioNombre || '—')}</td>
                <td class="px-4 py-3 text-slate-600">${U.esc(r.Status || '—')}</td></tr>`,
        },
        stock: {
            head: th('Fecha', 'Producto', '>Antes', '>Después', '>Cambio', 'Usuario'),
            row: (r) => `<tr>
                <td class="px-4 py-3 whitespace-nowrap">${fecha(r.Fecha)}</td><td class="px-4 py-3">${prod(r)}</td>
                <td class="px-4 py-3 text-right whitespace-nowrap">${U.qty(r.StockBefore, r.ProductoTipo)}</td><td class="px-4 py-3 text-right whitespace-nowrap">${U.qty(r.StockAfter, r.ProductoTipo)}</td>
                <td class="px-4 py-3 text-right whitespace-nowrap">${delta(r.StockBefore, r.StockAfter, (n) => U.num(n, 3))}</td>
                <td class="px-4 py-3 text-slate-600">${U.esc(r.UsuarioNombre || '—')}</td></tr>`,
        },
        precio: {
            head: th('Fecha', 'Producto', '>Antes (sin IVA)', '>Después (sin IVA)', '>Cambio', 'Usuario'),
            row: (r) => `<tr>
                <td class="px-4 py-3 whitespace-nowrap">${fecha(r.Fecha)}</td><td class="px-4 py-3">${prod(r)}</td>
                <td class="px-4 py-3 text-right">${U.money(r.PrecioBefore)}</td><td class="px-4 py-3 text-right">${U.money(r.PrecioAfter)}</td>
                <td class="px-4 py-3 text-right">${delta(r.PrecioBefore, r.PrecioAfter, U.money)}</td>
                <td class="px-4 py-3 text-slate-600">${U.esc(r.UsuarioNombre || '—')}</td></tr>`,
        },
    };

    function pintarTabs() {
        document.querySelectorAll('#tabs [data-vista]').forEach((b) => {
            const on = b.dataset.vista === vista;
            b.className = 'px-4 py-2 rounded-lg ' + (on ? 'bg-ink text-white' : 'text-slate-600 hover:bg-slate-100');
        });
        $('f-tipo').classList.toggle('hidden', vista !== 'movimientos');
        $('thead').innerHTML = COLUMNAS[vista].head;
    }

    async function cargar() {
        const query = { vista, page, limit: LIMIT };
        if ($('f-q').value.trim()) { query.q = $('f-q').value.trim(); }
        if ($('f-desde').value) { query.desde = $('f-desde').value; }
        if ($('f-hasta').value) { query.hasta = $('f-hasta').value; }
        if (vista === 'movimientos' && $('f-tipo').value) { query.tipo = $('f-tipo').value; }
        try {
            const r = await U.api('gestor/movimientos.php', { query });
            pages = r.pages;
            $('tbody').innerHTML = r.items.map(COLUMNAS[vista].row).join('');
            $('vacio').classList.toggle('hidden', r.items.length > 0);
        $('vacio').textContent = vista === 'precio' ? 'Aún no hay cambios de precio registrados; se guardan a partir de ahora cada vez que se edita un precio.' : 'Sin registros para los filtros seleccionados.';
            $('resumen').textContent = `${r.total} registro(s) · página ${r.page} de ${r.pages}`;
            $('pg-prev').disabled = page <= 1;
            $('pg-next').disabled = page >= pages;
        } catch (e) { U.toast(e.message, 'error'); }
    }
    const recargar = () => { page = 1; cargar(); };

    $('tabs').addEventListener('click', (e) => {
        const b = e.target.closest('[data-vista]');
        if (b && b.dataset.vista !== vista) { vista = b.dataset.vista; pintarTabs(); recargar(); }
    });
    $('f-q').addEventListener('input', U.debounce(recargar, 350));
    ['f-desde', 'f-hasta', 'f-tipo'].forEach((id) => $(id).addEventListener('change', recargar));
    $('pg-prev').addEventListener('click', () => { page--; cargar(); });
    $('pg-next').addEventListener('click', () => { page++; cargar(); });

    // ---------- Entrada de mercancía ----------
    function elegir(p) {
        seleccionado = p;
        $('e-resultados').classList.add('hidden');
        $('e-buscar').value = '';
        $('e-sel').classList.remove('hidden');
        $('e-sel').innerHTML = `<b>${U.esc(p.Nombre)}</b> · stock actual ${U.qty(p.Cantidad, p.Tipo)}`;
        $('e-cantidad').step = U.unit(p.Tipo).entero ? '1' : '0.001';
        $('e-cantidad').focus();
    }
    let resultados = [];
    $('e-buscar').addEventListener('input', U.debounce(async () => {
        const q = $('e-buscar').value.trim();
        if (q.length < 2) { return $('e-resultados').classList.add('hidden'); }
        try {
            const r = await U.api('v2/productos/index.php', { query: { search: q, limit: 8 } });
            resultados = r.data.productos;
            $('e-resultados').innerHTML = resultados.map((p) => `<button type="button" data-id="${p.ProductoID}" class="w-full text-left px-3 py-2 hover:bg-slate-50 text-sm"><b>${U.esc(p.Nombre)}</b> <span class="text-xs text-slate-500">${U.esc(p.UPC || '')}</span></button>`).join('') || '<p class="px-3 py-2 text-sm text-slate-500">Sin resultados</p>';
            $('e-resultados').classList.remove('hidden');
        } catch (e) { U.toast(e.message, 'error'); }
    }, 300));
    $('e-resultados').addEventListener('click', (e) => {
        const b = e.target.closest('[data-id]');
        const p = b && resultados.find((x) => String(x.ProductoID) === b.dataset.id);
        if (p) { elegir(p); }
    });

    $('btn-entrada').addEventListener('click', () => {
        seleccionado = null;
        $('e-sel').classList.add('hidden');
        $('e-buscar').value = '';
        $('e-cantidad').value = '';
        U.modal('modal-entrada', true);
        $('e-buscar').focus();
    });
    $('form-entrada').addEventListener('submit', async (e) => {
        e.preventDefault();
        const cantidad = parseFloat($('e-cantidad').value);
        if (!seleccionado) { return U.toast('Seleccione un producto', 'error'); }
        if (isNaN(cantidad) || cantidad <= 0) { return U.toast('La cantidad debe ser mayor a 0', 'error'); }
        $('e-guardar').disabled = true;
        try {
            await U.api('gestor/movimientos.php', { method: 'POST', body: { ProductoID: seleccionado.ProductoID, Cantidad: cantidad } });
            U.modal('modal-entrada', false);
            U.toast('Entrada registrada');
            vista = 'movimientos';
            pintarTabs();
            recargar();
        } catch (err) { U.toast(err.message, 'error'); }
        finally { $('e-guardar').disabled = false; }
    });

    pintarTabs();
    cargar();
})();
</script>
