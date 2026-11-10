<?php /** Solicitar · Requisiciones: el supervisor captura su lista de requerimientos */ ?>
<div class="space-y-6">
    <div>
        <h1 class="text-xl sm:text-2xl font-bold">Requisiciones</h1>
        <p class="text-sm text-slate-500">Escribe tu lista de lo que necesitas comprar. Logística la aprueba y el almacén registra lo que realmente llega.</p>
    </div>

    <section class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 sm:p-5 space-y-4">
        <div class="flex items-center gap-3">
            <span class="w-10 h-10 rounded-xl bg-brand-100 text-brand-600 flex items-center justify-center"><i class="fa-solid fa-pen-to-square"></i></span>
            <div><h2 class="font-bold">Nueva requisición</h2><p class="text-xs text-slate-500">Una línea por producto, o separados por comas</p></div>
        </div>
        <textarea id="texto" rows="4" maxlength="5000" placeholder="10 lechugas&#10;2 col&#10;5 kg de queso&#10;3 manojos de cilantro, 1.5 L de leche" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400"></textarea>
        <div class="flex flex-wrap gap-2">
            <button id="btn-interpretar" class="px-4 py-2.5 rounded-xl bg-ink text-white text-sm font-semibold hover:bg-slate-700"><i class="fa-solid fa-wand-magic-sparkles mr-2"></i>Interpretar lista</button>
            <button id="btn-linea" class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold hover:bg-slate-50"><i class="fa-solid fa-plus mr-2"></i>Agregar producto a mano</button>
        </div>

        <div id="revision" class="hidden space-y-3">
            <div class="flex items-center justify-between"><h3 class="font-semibold text-sm">Revisa y ajusta <span id="rev-cuenta" class="text-slate-400 font-normal"></span></h3><button id="btn-limpiar" class="text-xs text-slate-500 hover:text-red-600">Limpiar</button></div>
            <div id="lineas" class="space-y-2"></div>
            <div class="grid sm:grid-cols-3 gap-3 text-xs font-semibold text-slate-600 pt-2">
                <label>Sucursal<select id="sucursal" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-normal"><option value="">Sin sucursal</option></select></label>
                <label>Se necesita para<input id="fecha-req" type="date" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-normal"></label>
                <label>Notas<input id="notas" maxlength="500" placeholder="Opcional" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-normal"></label>
            </div>
            <div class="flex justify-end"><button id="btn-enviar" class="px-5 py-3 rounded-xl bg-brand-400 hover:bg-brand-500 text-ink font-bold disabled:opacity-50"><i class="fa-solid fa-paper-plane mr-2"></i>Enviar requisición</button></div>
        </div>
    </section>

    <section class="space-y-3">
        <h2 class="font-bold">Mis requisiciones</h2>
        <div id="mis" class="space-y-2"></div>
    </section>
</div>

<div id="m-detalle" data-modal class="fixed inset-0 z-50 bg-black/50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-2xl max-h-[92vh] flex flex-col shadow-2xl">
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between"><h2 id="d-titulo" class="font-bold text-lg"></h2><button type="button" data-close class="w-9 h-9 rounded-full hover:bg-slate-100"><i class="fa-solid fa-xmark"></i></button></div>
        <div id="d-cuerpo" class="p-5 overflow-y-auto space-y-4"></div>
        <div id="d-pie" class="px-5 py-3 border-t border-slate-200 flex justify-end"></div>
    </div>
</div>

<script>
(() => {
    const $ = (id) => document.getElementById(id);
    const ESTADOS = {
        Enviada: ['Enviada', 'bg-sky-100 text-sky-800'], Aprobada: ['Aprobada', 'bg-indigo-100 text-indigo-800'], Parcial: ['Recibida parcial', 'bg-amber-100 text-amber-800'],
        Recibida: ['Recibida', 'bg-emerald-100 text-emerald-800'], Rechazada: ['Rechazada', 'bg-red-100 text-red-800'], Cancelada: ['Cancelada', 'bg-slate-200 text-slate-600'],
    };
    const badge = (e) => `<span class="text-[11px] font-semibold rounded-full px-2.5 py-1 ${ESTADOS[e][1]}">${ESTADOS[e][0]}</span>`;
    const fecha = (s) => s ? new Date(String(s).replace(' ', 'T')).toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' }) : '—';
    const TIPOS = Object.keys(window.APP && APP.unidades || { Unidad: 1, Pesable: 1, Litro: 1, Manojo: 1 });
    let rows = [];

    const paso = (tipo) => { const u = U.unit(tipo); return u.entero ? 1 : 0.001; };

    function pinta() {
        $('revision').classList.toggle('hidden', rows.length === 0);
        $('rev-cuenta').textContent = `· ${rows.length} producto(s)`;
        $('lineas').innerHTML = rows.map((r, i) => {
            const sinMatch = !r.producto_id;
            const opts = (r.candidatos || []).map((c) => `<option value="${c.id}" ${c.id === r.producto_id ? 'selected' : ''}>${U.esc(c.nombre)} (${U.esc(U.unit(c.tipo).singular)})</option>`).join('');
            return `<div class="rounded-xl border ${sinMatch ? 'border-amber-300 bg-amber-50/50' : 'border-slate-200'} p-3 space-y-2" data-i="${i}">
                <div class="flex items-start gap-2">
                    <div class="flex-1 min-w-0">
                        <select data-f="producto" class="w-full rounded-lg border border-slate-200 px-2 py-2 text-sm bg-white">
                            ${opts}
                            <option value="" ${sinMatch ? 'selected' : ''}>${sinMatch && !(r.candidatos || []).length ? 'Sin coincidencia en el catálogo' : 'No está en el catálogo (texto libre)'}</option>
                        </select>
                        ${sinMatch ? `<input data-f="desc" value="${U.esc(r.descripcion || '')}" maxlength="150" placeholder="Nombre del producto" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">` : ''}
                        ${r.original ? `<p class="text-[11px] text-slate-400 mt-1">Escribiste: “${U.esc(r.original)}”</p>` : ''}
                    </div>
                    <button data-f="quitar" class="w-8 h-8 shrink-0 rounded-full text-slate-400 hover:bg-red-50 hover:text-red-600" title="Quitar"><i class="fa-solid fa-trash-can"></i></button>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <input data-f="cant" type="number" min="0" step="${paso(r.tipo)}" value="${r.cantidad ?? ''}" class="w-28 rounded-lg border border-slate-200 px-3 py-2 text-sm text-right">
                    ${sinMatch
                        ? `<select data-f="tipo" class="rounded-lg border border-slate-200 px-2 py-2 text-sm bg-white">${TIPOS.map((t) => `<option value="${t}" ${t === r.tipo ? 'selected' : ''}>${U.esc(U.unit(t).plural)}</option>`).join('')}</select>`
                        : `<span class="text-sm text-slate-500">${U.esc(U.unit(r.tipo).plural)}</span>`}
                    <span class="flex-1"></span>
                    <input data-f="buscar" type="search" placeholder="Buscar otro producto…" class="w-full sm:w-56 rounded-lg border border-slate-200 px-3 py-2 text-xs">
                </div>
                <div data-res class="hidden flex-wrap gap-1"></div>
            </div>`;
        }).join('');
    }

    async function interpretar() {
        const texto = $('texto').value.trim();
        if (!texto) { U.toast('Escribe tu lista primero.', 'error'); return; }
        $('btn-interpretar').disabled = true;
        try {
            const data = await U.api('solicitar/requisiciones.php', { method: 'POST', body: { accion: 'interpretar', texto } });
            const ok = data.lineas.filter((l) => l.valida);
            const mal = data.lineas.filter((l) => !l.valida);
            rows = rows.concat(ok.map((l) => ({ ...l, cantidad: l.cantidad, tipo: l.producto_id ? (l.candidatos.find((c) => c.id === l.producto_id) || {}).tipo || l.tipo : l.tipo })));
            $('texto').value = mal.map((l) => l.original).join('\n');
            if (mal.length) { U.toast(`${mal.length} línea(s) no se entendieron; quedan en el cuadro para corregirlas.`, 'info'); }
            pinta();
        } catch (e) { U.toast(e.message, 'error'); } finally { $('btn-interpretar').disabled = false; }
    }

    $('btn-interpretar').onclick = interpretar;
    $('btn-linea').onclick = () => { rows.push({ descripcion: '', cantidad: 1, tipo: 'Unidad', producto_id: null, candidatos: [] }); pinta(); };
    $('btn-limpiar').onclick = () => { rows = []; pinta(); };

    const fila = (el) => rows[Number(el.closest('[data-i]').dataset.i)];
    $('lineas').addEventListener('change', (e) => {
        const r = fila(e.target), f = e.target.dataset.f;
        if (f === 'producto') {
            const id = Number(e.target.value) || null;
            r.producto_id = id;
            if (id) { const c = r.candidatos.find((x) => x.id === id); r.tipo = c.tipo; r.descripcion = c.nombre; }
            pinta();
        } else if (f === 'cant') { r.cantidad = e.target.value; }
        else if (f === 'desc') { r.descripcion = e.target.value; }
        else if (f === 'tipo') { r.tipo = e.target.value; pinta(); }
    });
    $('lineas').addEventListener('click', (e) => {
        const q = e.target.closest('[data-f="quitar"]');
        if (q) { rows.splice(Number(q.closest('[data-i]').dataset.i), 1); pinta(); return; }
        const pick = e.target.closest('[data-pick]');
        if (pick) {
            const r = fila(pick), c = JSON.parse(pick.dataset.pick);
            if (!r.candidatos.some((x) => x.id === c.id)) { r.candidatos.push(c); }
            r.producto_id = c.id; r.tipo = c.tipo; r.descripcion = c.nombre; pinta();
        }
    });
    $('lineas').addEventListener('input', U.debounce(async (e) => {
        if (e.target.dataset.f !== 'buscar') { return; }
        const box = e.target.closest('[data-i]').querySelector('[data-res]'), q = e.target.value.trim();
        if (q.length < 2) { box.classList.add('hidden'); box.innerHTML = ''; return; }
        try {
            const d = await U.api('solicitar/requisiciones.php', { query: { buscar: q } });
            box.innerHTML = d.productos.map((p) => `<button type="button" data-pick='${U.esc(JSON.stringify({ id: Number(p.ProductoID), nombre: p.Nombre, tipo: p.Tipo }))}' class="text-xs rounded-full border border-slate-200 px-3 py-1 hover:bg-brand-100">${U.esc(p.Nombre)}</button>`).join('') || '<span class="text-xs text-slate-400">Sin resultados</span>';
            box.classList.remove('hidden'); box.classList.add('flex');
        } catch (err) { /* sin resultados */ }
    }, 300));
    $('lineas').addEventListener('input', (e) => { if (e.target.dataset.f === 'cant') { fila(e.target).cantidad = e.target.value; } });

    $('btn-enviar').onclick = async () => {
        const lineas = rows.map((r) => ({ producto_id: r.producto_id, descripcion: r.descripcion, tipo: r.tipo, cantidad: r.cantidad }));
        const sin = rows.findIndex((r) => !r.producto_id && !String(r.descripcion || '').trim());
        if (sin >= 0) { U.toast(`Línea ${sin + 1}: escribe el nombre del producto.`, 'error'); return; }
        const libres = rows.filter((r) => !r.producto_id).length;
        const msg = `¿Enviar la requisición con ${rows.length} producto(s)?` + (libres ? ` ${libres} no están en el catálogo; el almacén los vinculará.` : '');
        if (!(await U.confirm(msg))) { return; }
        $('btn-enviar').disabled = true;
        try {
            const d = await U.api('solicitar/requisiciones.php', { method: 'POST', body: { accion: 'crear', sucursal_id: $('sucursal').value || null, fecha_requerida: $('fecha-req').value || null, notas: $('notas').value, lineas } });
            U.toast(d.message); rows = []; $('texto').value = ''; $('notas').value = ''; pinta(); cargar();
        } catch (e) { U.toast(e.message, 'error'); } finally { $('btn-enviar').disabled = false; }
    };

    async function cargar() {
        try {
            const d = await U.api('solicitar/requisiciones.php');
            $('mis').innerHTML = d.requisiciones.length ? d.requisiciones.map((r) => `
                <button data-id="${r.RequisicionID}" class="w-full text-left bg-white rounded-xl border border-slate-200 p-4 hover:border-brand-400 flex items-center justify-between gap-3">
                    <span><b class="block">${U.esc(r.Folio)}</b><span class="text-xs text-slate-500">${fecha(r.FechaCreacion)} · ${r.lineas} producto(s)${r.sucursal ? ' · ' + U.esc(r.sucursal) : ''}</span></span>
                    ${badge(r.Estado)}</button>`).join('')
                : '<p class="text-sm text-slate-500 bg-white rounded-xl border border-dashed border-slate-300 p-6 text-center">Aún no has enviado requisiciones.</p>';
        } catch (e) { $('mis').innerHTML = `<p class="text-sm text-red-600">${U.esc(e.message)}</p>`; }
    }

    $('mis').addEventListener('click', async (e) => {
        const b = e.target.closest('[data-id]'); if (!b) { return; }
        try {
            const { requisicion: r } = await U.api('solicitar/requisiciones.php', { query: { id: b.dataset.id } });
            $('d-titulo').textContent = r.Folio;
            $('d-cuerpo').innerHTML = `<div class="flex flex-wrap items-center gap-2">${badge(r.Estado)}<span class="text-xs text-slate-500">${fecha(r.FechaCreacion)}${r.FechaRequerida ? ' · para ' + fecha(r.FechaRequerida) : ''}${r.sucursal ? ' · ' + U.esc(r.sucursal) : ''}</span></div>
                ${r.MotivoRechazo ? `<p class="text-sm bg-red-50 text-red-700 rounded-lg px-3 py-2">Motivo: ${U.esc(r.MotivoRechazo)}</p>` : ''}
                ${r.Notas ? `<p class="text-sm text-slate-600">${U.esc(r.Notas)}</p>` : ''}
                <div class="overflow-x-auto"><table class="w-full text-sm"><thead class="text-xs text-slate-500"><tr><th class="text-left py-1">Producto</th><th class="text-right">Pedido</th><th class="text-right">Aprobado</th><th class="text-right">Recibido</th></tr></thead><tbody>
                ${r.lineas.map((l) => `<tr class="border-t border-slate-100"><td class="py-2">${U.esc(l.Descripcion)}</td><td class="text-right">${U.qty(l.CantidadSolicitada, l.Tipo)}</td><td class="text-right">${l.CantidadAprobada === null ? '—' : U.qty(l.CantidadAprobada, l.Tipo)}</td><td class="text-right font-semibold">${Number(l.CantidadRecibida) > 0 ? U.qty(l.CantidadRecibida, l.Tipo) : '—'}</td></tr>`).join('')}
                </tbody></table></div>`;
            $('d-pie').innerHTML = '<div class="flex gap-2"><button id="d-pdf" class="px-4 py-2 rounded-xl border border-slate-200 text-sm font-semibold hover:bg-slate-50"><i class="fa-solid fa-file-pdf text-red-500 mr-1.5"></i>PDF</button>'
                + (r.Estado === 'Enviada' ? '<button id="d-cancelar" class="px-4 py-2 rounded-xl border border-red-200 text-red-600 text-sm font-semibold hover:bg-red-50">Cancelar requisición</button>' : '') + '</div>';
            $('d-pdf').onclick = () => U.pdf('', { title: 'Requisición ' + r.Folio, url: 'api/solicitar/requisicionPdf.php?id=' + r.RequisicionID, filename: r.Folio + '.pdf' });
            if ($('d-cancelar')) {
                $('d-cancelar').onclick = async () => {
                    if (!(await U.confirm('¿Cancelar esta requisición?'))) { return; }
                    try { await U.api('solicitar/requisiciones.php', { method: 'POST', body: { accion: 'cancelar', id: r.RequisicionID } }); U.modal('m-detalle', false); U.toast('Requisición cancelada'); cargar(); }
                    catch (err) { U.toast(err.message, 'error'); }
                };
            }
            U.modal('m-detalle');
        } catch (err) { U.toast(err.message, 'error'); }
    });

    (async () => {
        try { const d = await U.api('solicitar/sucursales.php'); $('sucursal').innerHTML += d.sucursales.map((s) => `<option value="${s.SucursalID}">${U.esc(s.nombre)}</option>`).join(''); } catch (e) { /* opcional */ }
        const hoy = new Date(); $('fecha-req').min = hoy.toISOString().slice(0, 10);
        cargar();
    })();
})();
</script>
