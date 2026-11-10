<?php /** Gestor · Requisiciones: aprobación y recepción de mercancía por peso/cantidad real */ ?>
<div class="space-y-5">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h2 class="text-xl font-extrabold">Requisiciones</h2>
            <p class="text-sm text-slate-500">Listas de requerimientos de los supervisores: aprueba, recibe lo que llega y actualiza el inventario.</p>
        </div>
        <div class="relative w-full sm:w-72">
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input id="q" type="search" placeholder="Folio, solicitante o sucursal…" class="w-full pl-10 pr-3 py-2.5 rounded-xl border border-slate-200 bg-white text-sm">
        </div>
    </div>

    <div id="chips" class="flex flex-wrap gap-2"></div>
    <div id="lista" class="grid md:grid-cols-2 xl:grid-cols-3 gap-3"></div>
</div>

<div id="m-req" data-modal class="fixed inset-0 z-50 bg-black/50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-4xl max-h-[94vh] flex flex-col shadow-2xl">
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between gap-3">
            <div><h3 id="r-titulo" class="font-bold text-lg"></h3><div id="r-sub" class="text-xs text-slate-500"></div></div>
            <button type="button" data-close class="w-9 h-9 rounded-full hover:bg-slate-100"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div id="r-cuerpo" class="p-5 overflow-y-auto space-y-4"></div>
        <div id="r-pie" class="px-5 py-3 border-t border-slate-200 flex flex-wrap items-center justify-end gap-2"></div>
    </div>
</div>

<script>
(() => {
    const $ = (id) => document.getElementById(id);
    const ESTADOS = {
        Enviada: ['Por aprobar', 'bg-sky-100 text-sky-800'], Aprobada: ['Por recibir', 'bg-indigo-100 text-indigo-800'], Parcial: ['Recepción parcial', 'bg-amber-100 text-amber-800'],
        Recibida: ['Recibida', 'bg-emerald-100 text-emerald-800'], Rechazada: ['Rechazada', 'bg-red-100 text-red-800'], Cancelada: ['Cancelada', 'bg-slate-200 text-slate-600'],
    };
    const badge = (e) => `<span class="text-[11px] font-semibold rounded-full px-2.5 py-1 ${ESTADOS[e][1]}">${ESTADOS[e][0]}</span>`;
    const fecha = (s) => s ? new Date(String(s).replace(' ', 'T')).toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' }) : '—';
    const inp = 'rounded-lg border border-slate-200 px-2.5 py-2 text-sm text-right w-full';
    const API = 'gestor/requisiciones.php';
    let estado = 'Enviada', actual = null, timer;

    $('chips').innerHTML = [['Enviada', 'Por aprobar'], ['Aprobada', 'Por recibir'], ['Parcial', 'Parciales'], ['Recibida', 'Recibidas'], ['', 'Todas']]
        .map(([v, t]) => `<button data-e="${v}" class="px-4 py-2 rounded-full text-sm font-medium border border-slate-200 bg-white">${t}</button>`).join('');
    const marca = () => $('chips').querySelectorAll('button').forEach((b) => {
        const on = b.dataset.e === estado;
        b.className = 'px-4 py-2 rounded-full text-sm font-medium border ' + (on ? 'bg-ink text-white border-ink' : 'bg-white border-slate-200 hover:bg-slate-50');
    });
    $('chips').onclick = (e) => { const b = e.target.closest('button'); if (b) { estado = b.dataset.e; marca(); cargar(); } };
    $('q').oninput = () => { clearTimeout(timer); timer = setTimeout(cargar, 300); };

    async function cargar() {
        marca();
        try {
            const d = await U.api(API, { query: { estado, q: $('q').value.trim() } });
            $('lista').innerHTML = d.requisiciones.length ? d.requisiciones.map((r) => `
                <button data-id="${r.RequisicionID}" class="text-left bg-white rounded-2xl border border-slate-200 p-4 hover:border-brand-400 hover:shadow-sm transition space-y-2">
                    <div class="flex items-center justify-between gap-2"><b>${U.esc(r.Folio)}</b>${badge(r.Estado)}</div>
                    <p class="text-sm text-slate-600"><i class="fa-solid fa-user mr-1.5 text-slate-400"></i>${U.esc(r.solicitante)}${r.sucursal ? ' · ' + U.esc(r.sucursal) : ''}</p>
                    <p class="text-xs text-slate-500">${fecha(r.FechaCreacion)} · ${r.lineas} producto(s)${r.FechaRequerida ? ' · para ' + fecha(r.FechaRequerida) : ''}</p>
                    ${['Aprobada', 'Parcial', 'Recibida'].includes(r.Estado) ? `<div class="h-1.5 rounded-full bg-slate-100 overflow-hidden"><div class="h-full bg-emerald-500" style="width:${Math.round(r.lineas_recibidas / r.lineas * 100)}%"></div></div>` : ''}
                </button>`).join('') : '<p class="md:col-span-2 xl:col-span-3 text-center text-slate-500 bg-white rounded-2xl border border-dashed border-slate-300 p-10">No hay requisiciones en esta vista.</p>';
        } catch (e) { U.toast(e.message, 'error'); }
    }
    $('lista').onclick = (e) => { const b = e.target.closest('[data-id]'); if (b) { abrir(b.dataset.id); } };

    async function abrir(id) {
        try { const d = await U.api(API, { query: { id } }); render(d.requisicion); U.modal('m-req'); } catch (e) { U.toast(e.message, 'error'); }
    }

    const diff = (l) => {
        const rec = Number(l.CantidadRecibida), apr = Number(l.CantidadAprobada);
        if (!(apr > 0)) { return '<span class="text-slate-400">No aprobado</span>'; }
        const d = rec - apr;
        if (Math.abs(d) < 0.0005) { return '<span class="text-emerald-600 font-semibold">Completo</span>'; }
        return d > 0 ? `<span class="text-amber-600 font-semibold">+${U.qty(d, l.Tipo)}</span>` : `<span class="text-red-600 font-semibold">Faltan ${U.qty(-d, l.Tipo)}</span>`;
    };

    function render(r) {
        actual = r;
        const puedeAprobar = r.Estado === 'Enviada', recibe = ['Aprobada', 'Parcial'].includes(r.Estado), abierta = ['Enviada', 'Aprobada', 'Parcial'].includes(r.Estado);
        $('r-titulo').textContent = r.Folio;
        $('r-sub').innerHTML = `${badge(r.Estado)} <span class="ml-2">${U.esc(r.solicitante)}${r.sucursal ? ' · ' + U.esc(r.sucursal) : ''} · ${fecha(r.FechaCreacion)}${r.FechaRequerida ? ' · se necesita ' + fecha(r.FechaRequerida) : ''}</span>`;

        const cab = `<tr class="text-xs text-slate-500 text-left"><th class="py-2">Producto</th><th class="text-right">Pedido</th>${puedeAprobar ? '<th class="w-32 text-right">Aprobar</th>' : '<th class="text-right">Aprobado</th>'}${!puedeAprobar ? '<th class="text-right">Recibido</th>' : ''}${recibe ? '<th class="w-32 text-right">Llegó ahora</th><th class="w-28 text-right">Costo unit.</th>' : (puedeAprobar ? '' : '<th class="text-right">Diferencia</th>')}</tr>`;
        const filas = r.lineas.map((l) => {
            const libre = !l.ProductoID;
            const u = U.unit(l.Tipo), step = u.entero ? 1 : 0.001;
            const nombre = `<b>${U.esc(l.Descripcion)}</b>${libre ? ` <span class="text-[11px] rounded bg-amber-100 text-amber-800 px-1.5 py-0.5 ml-1">sin vincular</span>` : `<span class="block text-[11px] text-slate-400">Stock ${U.qty(l.stock, l.Tipo)}${l.CostoUltimo !== null ? ' · último costo ' + U.money(l.CostoUltimo) : ''}</span>`}
                ${libre && abierta ? `<div class="mt-1 relative" data-vinc="${l.LineaID}"><input type="search" placeholder="Vincular con producto del catálogo…" class="w-full rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs"><div class="hidden absolute z-10 left-0 right-0 mt-1 bg-white border border-slate-200 rounded-lg shadow-lg max-h-44 overflow-y-auto"></div></div>` : ''}`;
            const apr = puedeAprobar
                ? `<td><input data-apr="${l.LineaID}" type="number" min="0" step="${step}" value="${Number(l.CantidadSolicitada)}" class="${inp}"></td>`
                : `<td class="text-right">${l.CantidadAprobada === null ? '—' : U.qty(l.CantidadAprobada, l.Tipo)}</td>`;
            const rec = !puedeAprobar ? `<td class="text-right font-semibold">${Number(l.CantidadRecibida) > 0 ? U.qty(l.CantidadRecibida, l.Tipo) : '—'}</td>` : '';
            const ult = recibe
                ? (Number(l.CantidadAprobada) > 0 && !libre
                    ? `<td><input data-rec="${l.LineaID}" type="number" min="0" step="${step}" placeholder="0" class="${inp}"></td><td><input data-costo="${l.LineaID}" type="number" min="0" step="0.01" placeholder="${l.CostoUltimo !== null ? Number(l.CostoUltimo).toFixed(2) : '$'}" class="${inp}"></td>`
                    : `<td colspan="2" class="text-right text-xs text-slate-400">${libre ? 'Vincula el producto para recibir' : 'No aprobado'}</td>`)
                : (puedeAprobar ? '' : `<td class="text-right">${diff(l)}</td>`);
            return `<tr class="border-t border-slate-100 align-top"><td class="py-2.5 pr-2">${nombre}</td><td class="text-right py-2.5">${U.qty(l.CantidadSolicitada, l.Tipo)}</td>${apr}${rec}${ult}</tr>`;
        }).join('');

        $('r-cuerpo').innerHTML = `
            ${r.Notas ? `<p class="text-sm bg-slate-50 rounded-lg px-3 py-2"><i class="fa-regular fa-note-sticky mr-2 text-slate-400"></i>${U.esc(r.Notas)}</p>` : ''}
            ${r.MotivoRechazo ? `<p class="text-sm bg-red-50 text-red-700 rounded-lg px-3 py-2">Motivo del rechazo: ${U.esc(r.MotivoRechazo)}</p>` : ''}
            ${puedeAprobar ? `<label class="block text-xs font-semibold text-slate-600 max-w-sm">Proveedor (opcional)<input id="r-prov" maxlength="255" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-normal"></label>` : (r.Proveedor ? `<p class="text-sm text-slate-600">Proveedor: <b>${U.esc(r.Proveedor)}</b>${r.aprobador ? ' · aprobó ' + U.esc(r.aprobador) : ''}</p>` : '')}
            ${recibe ? '<p class="text-xs text-slate-500 bg-amber-50 border border-amber-100 rounded-lg px-3 py-2"><i class="fa-solid fa-scale-balanced mr-1.5"></i>Captura lo que realmente llegó (peso o cantidad). Puedes recibir en varias entregas; si llega más de lo aprobado se acepta y se marca la diferencia. El costo actualiza el último costo del producto.</p>' : ''}
            <div class="overflow-x-auto"><table class="w-full text-sm"><thead>${cab}</thead><tbody>${filas}</tbody></table></div>
            ${puedeAprobar ? '<div id="r-rechazo" class="hidden space-y-2"><input id="r-motivo" maxlength="255" placeholder="Motivo del rechazo" class="w-full rounded-lg border border-red-200 px-3 py-2.5 text-sm"></div>' : ''}`;

        const btn = (id, txt, cls) => `<button id="${id}" class="px-4 py-2.5 rounded-xl text-sm font-semibold ${cls}">${txt}</button>`;
        $('r-pie').innerHTML = [
            btn('b-pdf', '<i class="fa-solid fa-file-pdf text-red-500 mr-1.5"></i>PDF', 'border border-slate-200 hover:bg-slate-50'),
            puedeAprobar ? btn('b-rechazar', 'Rechazar', 'border border-red-200 text-red-600 hover:bg-red-50') : '',
            ['Enviada', 'Aprobada'].includes(r.Estado) && !puedeAprobar ? btn('b-cancelar', 'Cancelar requisición', 'border border-slate-200 hover:bg-slate-50') : '',
            r.Estado === 'Parcial' ? btn('b-cerrar', 'Cerrar con faltantes', 'border border-slate-200 hover:bg-slate-50') : '',
            puedeAprobar ? btn('b-aprobar', '<i class="fa-solid fa-check mr-1.5"></i>Aprobar', 'bg-brand-400 hover:bg-brand-500 text-ink') : '',
            recibe ? btn('b-recibir', '<i class="fa-solid fa-box-open mr-1.5"></i>Registrar recepción', 'bg-emerald-600 hover:bg-emerald-700 text-white') : '',
        ].join('');
        enlazar(r);
    }

    async function accion(body, ok) {
        try {
            const d = await U.api(API, { method: 'POST', body });
            U.toast(d.message); render(d.requisicion); cargar();
            if (ok) { ok(d); }
        } catch (e) { U.toast(e.message, 'error'); }
    }

    function enlazar(r) {
        const on = (id, fn) => { if ($(id)) { $(id).onclick = fn; } };
        on('b-pdf', () => U.pdf('', { title: 'Requisición ' + r.Folio, url: 'api/solicitar/requisicionPdf.php?id=' + r.RequisicionID, filename: r.Folio + '.pdf' }));
        on('b-aprobar', () => {
            const lineas = [...document.querySelectorAll('[data-apr]')].map((i) => ({ id: Number(i.dataset.apr), cantidad_aprobada: i.value }));
            accion({ accion: 'aprobar', id: r.RequisicionID, proveedor: $('r-prov').value, lineas });
        });
        on('b-rechazar', () => {
            if ($('r-rechazo').classList.contains('hidden')) { $('r-rechazo').classList.remove('hidden'); $('r-motivo').focus(); return; }
            accion({ accion: 'rechazar', id: r.RequisicionID, motivo: $('r-motivo').value });
        });
        on('b-cancelar', async () => { if (await U.confirm('¿Cancelar esta requisición?')) { accion({ accion: 'cancelar', id: r.RequisicionID }); } });
        on('b-cerrar', async () => { if (await U.confirm('¿Cerrar la requisición dando por completos los faltantes?')) { accion({ accion: 'cerrar', id: r.RequisicionID }); } });
        on('b-recibir', async () => {
            const lineas = [...document.querySelectorAll('[data-rec]')].filter((i) => i.value !== '' && Number(i.value) > 0)
                .map((i) => ({ id: Number(i.dataset.rec), cantidad: i.value, costo: document.querySelector(`[data-costo="${i.dataset.rec}"]`).value }));
            if (!lineas.length) { U.toast('Captura la cantidad recibida de al menos un producto.', 'error'); return; }
            const resumen = lineas.map((l) => { const x = r.lineas.find((y) => y.LineaID == l.id); return `${x.Descripcion}: ${U.qty(l.cantidad, x.Tipo)}`; }).join('\n');
            if (!(await U.confirm(`Se darán de alta en inventario:\n${resumen}`))) { return; }
            accion({ accion: 'recibir', id: r.RequisicionID, lineas });
        });
    }

    $('r-cuerpo').addEventListener('input', U.debounce(async (e) => {
        const box = e.target.closest('[data-vinc]'); if (!box) { return; }
        const lista = box.querySelector('div'), q = e.target.value.trim();
        if (q.length < 2) { lista.classList.add('hidden'); return; }
        try {
            const d = await U.api(API, { query: { buscar: q } });
            lista.innerHTML = d.productos.map((p) => `<button type="button" data-prod="${p.ProductoID}" class="block w-full text-left px-3 py-2 text-xs hover:bg-brand-50">${U.esc(p.Nombre)} <span class="text-slate-400">(${U.esc(U.unit(p.Tipo).singular)})</span></button>`).join('') || '<p class="px-3 py-2 text-xs text-slate-400">Sin resultados</p>';
            lista.classList.remove('hidden');
        } catch (err) { /* sin resultados */ }
    }, 300));
    $('r-cuerpo').addEventListener('click', (e) => {
        const b = e.target.closest('[data-prod]'); if (!b) { return; }
        accion({ accion: 'vincular', linea_id: Number(b.closest('[data-vinc]').dataset.vinc), producto_id: Number(b.dataset.prod) });
    });

    cargar();
})();
</script>
