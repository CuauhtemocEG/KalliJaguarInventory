<?php /** Gestor · Reportes: analítica en pantalla + documentos PDF */ ?>
<div class="space-y-6">
    <!-- Periodo -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 flex flex-col lg:flex-row lg:items-center gap-3">
        <div id="presets" class="flex flex-wrap gap-2 text-sm">
            <button data-preset="hoy" class="px-3 py-2 rounded-full border border-slate-200 hover:border-slate-400">Hoy</button>
            <button data-preset="7" class="px-3 py-2 rounded-full border border-slate-200 hover:border-slate-400">7 días</button>
            <button data-preset="30" class="px-3 py-2 rounded-full border border-slate-200 hover:border-slate-400">30 días</button>
            <button data-preset="mes" class="px-3 py-2 rounded-full border border-slate-200 hover:border-slate-400">Este mes</button>
            <button data-preset="mesant" class="px-3 py-2 rounded-full border border-slate-200 hover:border-slate-400">Mes anterior</button>
            <button data-preset="anio" class="px-3 py-2 rounded-full border border-slate-200 hover:border-slate-400">Este año</button>
        </div>
        <div class="flex items-center gap-2 lg:ml-auto">
            <input id="r-desde" type="date" class="rounded-lg border border-slate-200 px-3 py-2 text-sm" aria-label="Desde">
            <span class="text-slate-400">→</span>
            <input id="r-hasta" type="date" class="rounded-lg border border-slate-200 px-3 py-2 text-sm" aria-label="Hasta">
        </div>
    </div>

    <!-- Resumen -->
    <section>
        <div class="flex items-end justify-between mb-3">
            <div><h2 class="font-bold text-lg">Resumen del periodo</h2><p id="r-rango" class="text-sm text-slate-500"></p></div>
            <div class="flex gap-2">
                <button data-csv="productos" class="px-3 py-2 rounded-lg border border-slate-200 bg-white text-sm hover:bg-slate-50"><i class="fa-solid fa-file-csv mr-1"></i>Productos</button>
                <button data-csv="dias" class="px-3 py-2 rounded-lg border border-slate-200 bg-white text-sm hover:bg-slate-50"><i class="fa-solid fa-file-csv mr-1"></i>Por día</button>
                <button data-csv="sucursales" class="hidden sm:inline-block px-3 py-2 rounded-lg border border-slate-200 bg-white text-sm hover:bg-slate-50"><i class="fa-solid fa-file-csv mr-1"></i>Sucursales</button>
            </div>
        </div>
        <div id="r-kpis" class="grid grid-cols-2 lg:grid-cols-5 gap-3"></div>
    </section>

    <section class="grid lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-4">
            <h3 class="font-semibold mb-3">Ventas por día <span class="text-xs font-normal text-slate-400">(c/IVA, sin cancelados)</span></h3>
            <div id="r-dias" class="h-48 flex items-end gap-1"></div>
            <div id="r-dias-ejes" class="flex justify-between text-[11px] text-slate-400 mt-1"></div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4">
            <h3 class="font-semibold mb-3">Por categoría</h3>
            <div id="r-categorias" class="space-y-2 text-sm"></div>
        </div>
    </section>

    <section class="grid lg:grid-cols-2 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-4">
            <h3 class="font-semibold mb-3">Por sucursal</h3>
            <div id="r-sucursales" class="space-y-2 text-sm"></div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4">
            <h3 class="font-semibold mb-3">Por solicitante</h3>
            <div id="r-solicitantes" class="space-y-2 text-sm"></div>
        </div>
    </section>

    <section class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <h3 class="font-semibold p-4 pb-2">Productos más solicitados</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="px-4 py-2">#</th><th class="px-4 py-2">Producto</th><th class="px-4 py-2 text-right">Cantidad</th><th class="px-4 py-2 text-right">Pedidos</th><th class="px-4 py-2 text-right">Importe</th></tr></thead>
                <tbody id="r-productos" class="divide-y divide-slate-100"></tbody>
            </table>
        </div>
    </section>

    <!-- Documentos PDF -->
    <section>
        <h2 class="font-bold text-lg">Documentos PDF</h2>
        <p class="text-sm text-slate-500 mb-3">Se generan con el periodo seleccionado arriba y se abren dentro de la app.</p>
        <div id="r-docs" class="grid sm:grid-cols-2 xl:grid-cols-3 gap-3"></div>
    </section>
</div>

<div id="modal-doc" data-modal class="fixed inset-0 z-50 bg-black/50 hidden items-center justify-center p-4">
    <form id="form-doc" class="bg-white rounded-2xl w-full max-w-md shadow-2xl" novalidate>
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between"><h3 id="doc-titulo" class="font-bold text-lg"></h3><button type="button" data-close class="w-9 h-9 rounded-lg hover:bg-slate-100" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button></div>
        <div id="doc-campos" class="p-5 space-y-4"></div>
        <div class="px-5 py-4 border-t border-slate-200 flex justify-end gap-2"><button type="button" data-close class="px-4 py-2.5 rounded-lg border border-slate-200 text-sm font-medium">Cancelar</button><button class="px-5 py-2.5 rounded-lg bg-brand-400 hover:bg-brand-500 text-ink font-semibold"><i class="fa-solid fa-file-pdf mr-1"></i>Generar</button></div>
    </form>
</div>

<script>
(() => {
    const $ = (id) => document.getElementById(id);
    let datos = null, seq = 0, tags = [];
    const iso = (d) => { const x = new Date(d); x.setMinutes(x.getMinutes() - x.getTimezoneOffset()); return x.toISOString().slice(0, 10); };
    const fmtDia = (s) => new Date(s + 'T00:00:00').toLocaleDateString('es-MX', { day: '2-digit', month: 'short' });
    const fmtFecha = (s) => new Date(s + 'T00:00:00').toLocaleDateString('es-MX', { day: '2-digit', month: 'long', year: 'numeric' });

    // ---------- Periodo ----------
    function preset(k) {
        const h = new Date(), d = new Date();
        if (k === '7') { d.setDate(d.getDate() - 6); }
        else if (k === '30') { d.setDate(d.getDate() - 29); }
        else if (k === 'mes') { d.setDate(1); }
        else if (k === 'mesant') { d.setMonth(d.getMonth() - 1, 1); h.setDate(0); }
        else if (k === 'anio') { d.setMonth(0, 1); }
        $('r-desde').value = iso(d); $('r-hasta').value = iso(h);
        cargar();
    }

    // ---------- Gráficas simples ----------
    const barras = (filas, { color = 'bg-brand-400', vacio = 'Sin datos en el periodo.' } = {}) => {
        if (!filas.length) { return `<p class="text-slate-400">${vacio}</p>`; }
        const max = Math.max(...filas.map((f) => Number(f.monto))) || 1;
        return filas.map((f) => `<div><div class="flex justify-between gap-2"><span class="truncate">${U.esc(f.nombre)}</span><span class="font-semibold whitespace-nowrap">${U.money(f.monto)}</span></div>
            <div class="h-2 rounded-full bg-slate-100 mt-1"><div class="h-2 rounded-full ${color}" style="width:${Math.max(2, Number(f.monto) / max * 100)}%"></div></div></div>`).join('');
    };

    function pintar(r) {
        datos = r;
        const k = r.kpis;
        $('r-rango').textContent = `${fmtFecha(r.desde)} – ${fmtFecha(r.hasta)}`;
        $('r-kpis').innerHTML = [
            ['Ventas c/IVA', U.money(k.monto), 'text-ink'], ['Pedidos', k.pedidos, ''], ['Ticket promedio', U.money(k.ticket), ''],
            ['Partidas surtidas', k.partidas, ''], ['Cancelados', `${k.cancelados}${k.cancelados ? ' · ' + U.money(k.monto_cancelado) : ''}`, k.cancelados ? 'text-red-600' : ''],
        ].map(([t, v, c]) => `<div class="bg-white rounded-2xl border border-slate-200 p-4"><p class="text-xs text-slate-500">${t}</p><p class="text-xl font-bold ${c}">${v}</p></div>`).join('');

        const max = Math.max(0, ...r.dias.map((d) => Number(d.monto))) || 1;
        $('r-dias').innerHTML = r.dias.length ? r.dias.map((d) => `<div class="flex-1 min-w-[3px] group relative flex flex-col justify-end h-full" title="${fmtDia(d.dia)} · ${d.pedidos} pedido(s) · ${U.money(d.monto)}">
            <div class="w-full rounded-t bg-brand-400 group-hover:bg-brand-600 transition" style="height:${Math.max(3, Number(d.monto) / max * 100)}%"></div></div>`).join('') : '<p class="text-slate-400 m-auto">Sin ventas en el periodo.</p>';
        $('r-dias-ejes').innerHTML = r.dias.length ? `<span>${fmtDia(r.dias[0].dia)}</span><span>Máx. ${U.money(max)}</span><span>${fmtDia(r.dias[r.dias.length - 1].dia)}</span>` : '';

        $('r-categorias').innerHTML = barras(r.categorias.slice(0, 8), { color: 'bg-sky-400' });
        $('r-sucursales').innerHTML = barras(r.sucursales, { color: 'bg-emerald-400' });
        $('r-solicitantes').innerHTML = barras(r.solicitantes.slice(0, 8), { color: 'bg-violet-400' });
        $('r-productos').innerHTML = r.productos.length ? r.productos.map((p, i) => `<tr><td class="px-4 py-2 text-slate-400">${i + 1}</td><td class="px-4 py-2">${U.esc(p.Nombre)}<span class="block text-xs text-slate-400">${U.esc(p.UPC || '')}</span></td>
            <td class="px-4 py-2 text-right whitespace-nowrap">${U.qty(p.cantidad, p.Tipo)}</td><td class="px-4 py-2 text-right">${p.pedidos}</td><td class="px-4 py-2 text-right font-semibold">${U.money(p.monto)}</td></tr>`).join('')
            : '<tr><td colspan="5" class="px-4 py-8 text-center text-slate-400">Sin datos en el periodo.</td></tr>';
    }

    async function cargar() {
        const n = ++seq;
        $('r-kpis').classList.add('opacity-50');
        try {
            const r = await U.api('gestor/reportes.php', { query: { desde: $('r-desde').value, hasta: $('r-hasta').value } });
            if (n === seq) { pintar(r); }
        } catch (e) { if (n === seq) { U.toast(e.message, 'error'); } }
        finally { if (n === seq) { $('r-kpis').classList.remove('opacity-50'); } }
    }

    // ---------- CSV ----------
    const CSV = {
        productos: [['Producto', 'UPC', 'Tipo', 'Cantidad', 'Pedidos', 'Importe'], (p) => [p.Nombre, p.UPC, p.Tipo, p.cantidad, p.pedidos, p.monto]],
        dias: [['Día', 'Pedidos', 'Importe'], (d) => [d.dia, d.pedidos, d.monto]],
        sucursales: [['Sucursal', 'Pedidos', 'Importe'], (s) => [s.nombre, s.pedidos, s.monto]],
    };
    function csv(tipo) {
        if (!datos) { return; }
        const [cab, fila] = CSV[tipo];
        const celda = (v) => { v = String(v ?? ''); return /^[=+\-@]/.test(v) ? "'" + v : v; };
        const texto = [cab, ...datos[tipo].map(fila)].map((f) => f.map((v) => `"${celda(v).replace(/"/g, '""')}"`).join(',')).join('\r\n');
        const a = document.createElement('a');
        a.href = URL.createObjectURL(new Blob(['\ufeff' + texto], { type: 'text/csv;charset=utf-8' }));
        a.download = `reporte_${tipo}_${datos.desde}_${datos.hasta}.csv`;
        document.body.appendChild(a); a.click(); a.remove();
        setTimeout(() => URL.revokeObjectURL(a.href), 1000);
    }

    // ---------- Documentos PDF ----------
    const TIPOS = Object.entries(APP.unidades || {}).map(([k, u]) => [k, u.nombre || k]);
    const selTag = (obligatorio) => ({ n: 'tag', t: 'Etiqueta' + (obligatorio ? ' *' : ''), tipo: 'tag', req: obligatorio });
    const selSuc = { n: 'sucursal', t: 'Sucursal', tipo: 'suc' };
    let sucursales = null;
    const selTipo = { n: 'tipo', t: 'Tipo de producto', tipo: 'tipo' };
    const DOCS = [
        { id: 'comandas', icono: 'fa-clipboard-list', color: 'bg-sky-100 text-sky-700', titulo: 'Reporte por comandas', desc: 'Pedidos del periodo agrupados por sucursal.', url: 'api/gestor/reportePdf.php?reporte=comandas', nuevo: true, periodo: true, archivo: 'reporte_comandas',
          campos: [selSuc, { n: 'estado', t: 'Estado', tipo: 'sel', op: [['', 'Todos'], ['Abierto', 'Pendiente'], ['En transito', 'En tránsito'], ['Cerrado', 'Entregado'], ['Cancelado', 'Cancelado']] }] },
        { id: 'consolidado', icono: 'fa-layer-group', color: 'bg-emerald-100 text-emerald-700', titulo: 'Consolidado de productos', desc: 'Producto × sucursal, por categoría.', url: 'api/gestor/reportePdf.php?reporte=consolidado', nuevo: true, periodo: true, archivo: 'reporte_consolidado', campos: [selSuc] },
        { id: 'stock-tag', icono: 'fa-boxes-stacked', color: 'bg-violet-100 text-violet-700', titulo: 'Inventario por etiqueta', desc: 'Existencias actuales y qué reponer.', url: 'api/gestor/reportePdf.php?reporte=inventario', nuevo: true, archivo: 'inventario_por_etiqueta', campos: [selTag(false), selTipo, { n: 'bajo', t: 'Mostrar', tipo: 'sel', op: [['', 'Todo el inventario'], ['1', 'Solo stock bajo o agotado']] }] },
        { id: 'catalogo', icono: 'fa-barcode', color: 'bg-cyan-100 text-cyan-700', titulo: 'Catálogo con códigos de barras', desc: 'Para imprimir y escanear.', url: 'api/generarCatalogoBarcodePDF.php', archivo: 'catalogo_barras', campos: [selTag(false), selTipo] },
    ];
    $('r-docs').innerHTML = DOCS.map((d) => `<button data-doc="${d.id}" class="text-left bg-white rounded-2xl border border-slate-200 p-4 hover:border-brand-400 hover:shadow-sm transition flex gap-3">
        <span class="w-11 h-11 shrink-0 rounded-xl flex items-center justify-center ${d.color}"><i class="fa-solid ${d.icono}"></i></span>
        <span><b class="block">${d.titulo}</b><span class="text-sm text-slate-500">${d.desc}</span>${d.periodo ? '<span class="block text-[11px] text-slate-400 mt-1"><i class="fa-regular fa-calendar mr-1"></i>Usa el periodo seleccionado</span>' : ''}</span></button>`).join('');

    const campoHtml = (c) => {
        const cls = 'mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-normal';
        const op = c.tipo === 'tag' ? [['', c.req ? 'Selecciona…' : 'Todas'], ...tags.map((t) => [t, t])]
            : c.tipo === 'tipo' ? [['', 'Todos'], ...TIPOS]
            : c.tipo === 'suc' ? [['', 'Todas'], ...sucursales.map((x) => [x.SucursalID, x.nombre])] : c.op;
        return `<label class="block text-xs font-semibold text-slate-600">${c.t}<select name="${c.n}" ${c.req ? 'required' : ''} class="${cls}">${op.map(([v, t]) => `<option value="${U.esc(v)}">${U.esc(t)}</option>`).join('')}</select></label>`;
    };

    let docActual = null;
    async function generar(doc, valores) {
        const sufijo0 = doc.periodo ? `_${$('r-desde').value}_${$('r-hasta').value}` : '_' + iso(new Date());
        const titulo0 = doc.titulo + (doc.periodo ? ` · ${$('r-desde').value} a ${$('r-hasta').value}` : '');
        if (doc.nuevo) {
            const q = new URLSearchParams(valores);
            if (doc.periodo) { q.set('desde', $('r-desde').value); q.set('hasta', $('r-hasta').value); }
            for (const [k, v] of [...q]) { if (v === '') { q.delete(k); } }
            return U.pdf('', { title: titulo0, url: doc.url + '&' + q.toString(), filename: doc.archivo + sufijo0 + '.pdf' });
        }
        const cuerpo = new URLSearchParams(valores);
        if (doc.periodo) { cuerpo.set('fecha_desde', $('r-desde').value + ' 00:00:00'); cuerpo.set('fecha_hasta', $('r-hasta').value + ' 23:59:59'); }
        const sufijo = doc.periodo ? `_${$('r-desde').value}_${$('r-hasta').value}` : '_' + iso(new Date());
        U.pdf('', { title: doc.titulo + (doc.periodo ? ` · ${$('r-desde').value} a ${$('r-hasta').value}` : ''), url: doc.url, filename: doc.archivo + sufijo + '.pdf', init: { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: cuerpo.toString() } });
    }

    $('r-docs').addEventListener('click', async (e) => {
        const b = e.target.closest('[data-doc]'); if (!b) { return; }
        docActual = DOCS.find((d) => d.id === b.dataset.doc);
        if (!docActual.campos) { return generar(docActual, {}); }
        if (!sucursales && docActual.campos.includes(selSuc)) { try { sucursales = (await U.api('solicitar/sucursales.php')).sucursales || []; } catch (err) { sucursales = []; } }
        if (!tags.length) { try { tags = (await U.api('getTags.php')).tags || []; } catch (err) { tags = []; } }
        $('doc-titulo').textContent = docActual.titulo;
        $('doc-campos').innerHTML = docActual.campos.map(campoHtml).join('');
        U.modal('modal-doc');
    });
    $('form-doc').addEventListener('submit', (e) => {
        e.preventDefault();
        if (!e.target.reportValidity()) { return; }
        U.modal('modal-doc', false);
        generar(docActual, Object.fromEntries(new FormData(e.target)));
    });

    $('presets').addEventListener('click', (e) => { const b = e.target.closest('[data-preset]'); if (b) { preset(b.dataset.preset); } });
    ['r-desde', 'r-hasta'].forEach((id) => $(id).addEventListener('change', () => { if ($('r-desde').value && $('r-hasta').value) { cargar(); } }));
    document.querySelectorAll('[data-csv]').forEach((b) => b.addEventListener('click', () => csv(b.dataset.csv)));
    preset('30');
})();
</script>
