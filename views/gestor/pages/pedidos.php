<?php /** Gestor · pedidos (comandas) */ ?>
<div id="contadores" class="grid grid-cols-2 lg:grid-cols-5 gap-3 mb-4"></div>

<div class="bg-white rounded-2xl border border-slate-200 p-4 mb-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
    <input id="f-q" type="search" placeholder="Buscar folio…" class="rounded-lg border border-slate-200 px-3 py-2.5 lg:col-span-2">
    <select id="f-sucursal" class="rounded-lg border border-slate-200 px-3 py-2.5"><option value="">Todas las sucursales</option></select>
    <select id="f-solicitante" class="rounded-lg border border-slate-200 px-3 py-2.5"><option value="">Todos los solicitantes</option></select>
    <input id="f-desde" type="date" title="Desde" class="rounded-lg border border-slate-200 px-3 py-2.5">
    <input id="f-hasta" type="date" title="Hasta" class="rounded-lg border border-slate-200 px-3 py-2.5">
    <div class="sm:col-span-2 lg:col-span-6 flex items-center justify-between text-xs text-slate-500">
        <span id="conteo"></span>
        <button id="f-limpiar" type="button" class="hidden font-semibold text-brand-600 hover:underline">Limpiar filtros</button>
    </div>
</div>

<div class="bg-white rounded-2xl border border-slate-200 overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-left">
            <tr>
                <th class="px-4 py-3">Folio</th><th class="px-4 py-3">Estado</th><th class="px-4 py-3">Sucursal</th>
                <th class="px-4 py-3">Solicitante</th><th class="px-4 py-3">Fecha</th><th class="px-4 py-3">Entrega</th>
                <th class="px-4 py-3 text-right">Art.</th><th class="px-4 py-3 text-right">Total</th>
            </tr>
        </thead>
        <tbody id="lista" class="divide-y divide-slate-100"></tbody>
    </table>
    <p id="vacio" class="hidden text-center text-slate-500 py-12">No hay pedidos con estos filtros.</p>
</div>

<div id="modal-pedido" data-modal class="fixed inset-0 z-50 bg-black/50 hidden items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="bg-white w-full max-w-3xl rounded-t-2xl sm:rounded-2xl max-h-[92vh] flex flex-col shadow-2xl">
        <div class="px-5 py-4 border-b border-slate-200 flex items-start justify-between gap-3">
            <div>
                <p class="text-xs text-slate-500">Pedido</p>
                <h2 id="d-titulo" class="font-bold text-lg leading-tight"></h2>
                <span id="d-estado" class="inline-block mt-1 px-2.5 py-1 rounded-full text-xs font-semibold"></span>
            </div>
            <button data-close class="w-9 h-9 rounded-lg hover:bg-slate-100" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div id="d-cuerpo" class="p-5 overflow-y-auto space-y-4 text-sm"></div>
        <div class="border-t border-slate-200 bg-slate-50 sm:rounded-b-2xl">
            <div id="d-totales" class="px-5 pt-3 flex flex-wrap items-baseline justify-end gap-x-6 gap-y-1 text-sm"></div>
            <div id="d-acciones" class="px-5 py-3 flex flex-wrap gap-2 justify-end"></div>
        </div>
    </div>
</div>

<script>
(() => {
    const colores = { Abierto: 'bg-sky-100 text-sky-800', 'En transito': 'bg-amber-100 text-amber-800', Cerrado: 'bg-emerald-100 text-emerald-800', Cancelado: 'bg-red-100 text-red-800' };
    const etiquetas = { Abierto: 'Abierto', 'En transito': 'En tránsito', Cerrado: 'Entregado', Cancelado: 'Cancelado' };
    const fecha = (s) => s ? new Date(String(s).replace(' ', 'T')).toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' }) : '—';
    const $ = (id) => document.getElementById(id);
    const pasos = ['Abierto', 'En transito', 'Cerrado'];
    const hoyISO = () => { const d = new Date(); d.setMinutes(d.getMinutes() - d.getTimezoneOffset()); return d.toISOString().slice(0, 10); };
    function entregaTag(p) {
        if (!p.entrega || (p.estado !== 'Abierto' && p.estado !== 'En transito')) { return ''; }
        const e = String(p.entrega).slice(0, 10), h = hoyISO();
        const manana = new Date(h + 'T00:00:00'); manana.setDate(manana.getDate() + 1);
        if (e < h) { return '<span class="ml-1 px-1.5 py-0.5 rounded bg-red-100 text-red-700 text-[10px] font-bold">ATRASADO</span>'; }
        if (e === h) { return '<span class="ml-1 px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 text-[10px] font-bold">HOY</span>'; }
        if (e === manana.toISOString().slice(0, 10)) { return '<span class="ml-1 px-1.5 py-0.5 rounded bg-sky-100 text-sky-800 text-[10px] font-bold">MAÑANA</span>'; }
        return '';
    }
    function progreso(estado) {
        if (estado === 'Cancelado') { return '<p class="rounded-xl bg-red-50 text-red-700 px-3 py-2"><i class="fa-solid fa-ban mr-1"></i>Pedido cancelado.</p>'; }
        const i = pasos.indexOf(estado);
        return `<ol class="flex items-center">${pasos.map((p, k) => `<li class="flex items-center ${k < 2 ? 'flex-1' : ''}">
            <span class="flex flex-col items-center gap-1"><span class="w-7 h-7 rounded-full flex items-center justify-center text-xs ${k <= i ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-500'}">${k <= i ? '<i class="fa-solid fa-check"></i>' : k + 1}</span>
            <span class="text-[11px] ${k <= i ? 'font-semibold' : 'text-slate-400'}">${etiquetas[p]}</span></span>
            ${k < 2 ? `<span class="flex-1 h-0.5 mx-1 mb-5 ${k < i ? 'bg-emerald-500' : 'bg-slate-200'}"></span>` : ''}</li>`).join('')}</ol>`;
    }
    const filtros = { estado: '' };
    let actual = null;
    let combosListos = false;

    function opciones(sel, datos, id, nombre) {
        const v = sel.value;
        sel.innerHTML = sel.options[0].outerHTML + datos.map((d) => `<option value="${U.esc(d[id])}">${U.esc(d[nombre])}</option>`).join('');
        sel.value = v;
    }

    async function cargar() {
        const query = { ...filtros, q: $('f-q').value.trim(), sucursal: $('f-sucursal').value, solicitante: $('f-solicitante').value, desde: $('f-desde').value, hasta: $('f-hasta').value };
        Object.keys(query).forEach((k) => { if (!query[k]) { delete query[k]; } });
        const r = await U.api('gestor/pedidos.php', { query });
        if (!combosListos) {
            opciones($('f-sucursal'), r.sucursales, 'SucursalID', 'nombre');
            opciones($('f-solicitante'), r.solicitantes, 'UsuarioID', 'Nombre');
            combosListos = true;
        }
        const todos = Object.values(r.contadores).reduce((a, b) => a + b, 0);
        const tarjetas = [['', 'Todos', todos], ...Object.keys(etiquetas).map((k) => [k, etiquetas[k], r.contadores[k]])];
        $('contadores').innerHTML = tarjetas.map(([k, t, n]) => `
            <button data-estado="${U.esc(k)}" class="text-left bg-white rounded-2xl border p-4 transition ${filtros.estado === k ? 'border-brand-400 ring-2 ring-brand-400/40' : 'border-slate-200 hover:border-brand-400'}">
                <p class="text-xs text-slate-500">${t}</p><p class="text-2xl font-bold">${n}</p>
            </button>`).join('');
        $('vacio').classList.toggle('hidden', r.pedidos.length > 0);
        const hay = Object.keys(query).length > 0;
        $('conteo').textContent = `${r.pedidos.length} pedido${r.pedidos.length === 1 ? '' : 's'}${r.pedidos.length === 300 ? ' (mostrando los 300 más recientes)' : ''}`;
        $('f-limpiar').classList.toggle('hidden', !hay);
        $('lista').innerHTML = r.pedidos.map((p) => `
            <tr data-id="${U.esc(p.ComandaID)}" class="hover:bg-slate-50 cursor-pointer ${p.estado === 'Cancelado' ? 'opacity-60' : ''}">
                <td class="px-4 py-3 font-semibold">${U.esc(p.ComandaID)}</td>
                <td class="px-4 py-3"><span class="px-2.5 py-1 rounded-full text-xs font-semibold ${colores[p.estado] || 'bg-slate-100'}">${etiquetas[p.estado] || U.esc(p.estado)}</span></td>
                <td class="px-4 py-3">${U.esc(p.sucursal || '—')}</td>
                <td class="px-4 py-3">${U.esc(p.solicitante || '—')}</td>
                <td class="px-4 py-3">${fecha(p.fecha)}</td>
                <td class="px-4 py-3 whitespace-nowrap">${fecha(p.entrega)}${entregaTag(p)}</td>
                <td class="px-4 py-3 text-right">${p.articulos}</td>
                <td class="px-4 py-3 text-right font-semibold">${U.money(p.total)}</td>
            </tr>`).join('');
    }

    function pintarDetalle(r, conservar = {}) {
        actual = r;
        const p = r.pedido;
        $('d-titulo').textContent = p.ComandaID;
        $('d-estado').className = 'inline-block mt-1 px-2.5 py-1 rounded-full text-xs font-semibold ' + (colores[p.estado] || 'bg-slate-100');
        $('d-estado').textContent = etiquetas[p.estado] || p.estado;
        const editable = p.estado === 'Abierto';
        const complementable = editable || p.estado === 'En transito';
        const filas = r.partidas.map((d) => {
            const u = U.unit(d.Tipo);
            const pedida = editable ? d.Cantidad : d.CantidadSolicitada;
            const cambio = !editable && Math.abs(Number(d.Cantidad) - Number(d.CantidadSolicitada)) > 0.0005;
            const surtido = editable
                ? `<div class="inline-flex items-center gap-1"><input type="number" min="0" max="${Number(d.Cantidad) * 1.5}" step="${u.entero ? '1' : '0.001'}" inputmode="${u.entero ? 'numeric' : 'decimal'}" data-mov="${d.MovimientoID}" data-pedida="${d.Cantidad}" data-unit="${d.PrecioUnitarioIva}" value="${U.esc(d.Cantidad)}" class="w-24 rounded-lg border border-slate-200 px-2 py-1 text-right"><span class="text-xs text-slate-500 w-8 text-left">${u.abbr}</span></div>`
                : `<span class="${cambio ? 'font-bold text-amber-700' : ''}">${U.qty(d.Cantidad, d.Tipo)}</span>`;
            return `<tr class="border-b border-slate-100">
                <td class="py-2">${U.esc(d.Nombre)}<p class="text-xs text-slate-400">${U.esc(d.UPC || '')}</p></td>
                <td class="py-2 text-right whitespace-nowrap">${U.qty(pedida, d.Tipo)}</td>
                <td class="py-2 text-right">${surtido}</td>
                <td class="py-2 text-right whitespace-nowrap" data-importe="${d.MovimientoID}">${U.money(d.PrecioFinal)}</td>
                ${complementable ? `<td class="py-2 pl-2 text-right"><button type="button" data-quitar="${d.MovimientoID}" data-nombre="${U.esc(d.Nombre)}" title="Quitar del pedido" class="w-8 h-8 rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-600"><i class="fa-solid fa-trash-can"></i></button></td>` : ''}</tr>`;
        }).join('');
        const hist = r.historial.length ? r.historial.map((h) => `
            <li class="text-slate-600"><span class="text-slate-400">${fecha(h.FechaCambio)}</span> ${U.esc(etiquetas[h.EstadoAnterior] || h.EstadoAnterior)} → <strong>${U.esc(etiquetas[h.EstadoNuevo] || h.EstadoNuevo)}</strong>
            ${h.usuario ? '· ' + U.esc(h.usuario) : ''}${h.Observaciones ? ' · ' + U.esc(h.Observaciones) : ''}</li>`).join('') : '<li class="text-slate-400">Sin movimientos registrados</li>';
        $('d-cuerpo').innerHTML = `
            ${progreso(p.estado)}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                ${[['Solicitante', U.esc(p.solicitante || '—')], ['Sucursal', U.esc(p.sucursal || '—')], ['Solicitado', fecha(p.fecha)], ['Entrega', fecha(p.entrega) + entregaTag(p)]].map(([t, v]) => `<div class="rounded-xl bg-slate-50 px-3 py-2"><p class="text-[11px] text-slate-400">${t}</p><p class="font-medium">${v}</p></div>`).join('')}
            </div>
            <div class="overflow-x-auto"><table class="w-full">
                <thead class="text-slate-500 text-xs"><tr><th class="text-left">Producto</th><th class="text-right">Pedido</th><th class="text-right">${editable ? 'Surtir' : 'Enviado'}</th><th class="text-right">Importe</th>${complementable ? '<th></th>' : ''}</tr></thead>
                <tbody>${filas}</tbody>
            </table></div>
            ${complementable ? `<div class="rounded-xl border border-dashed border-slate-300 p-3">
                <p class="font-semibold mb-2"><i class="fa-solid fa-plus mr-1 text-brand-600"></i>Agregar producto al pedido${editable ? '' : ' <span class="text-xs font-normal text-slate-500">(se enviará como complemento)</span>'}</p>
                <div class="relative">
                    <input id="add-q" type="search" autocomplete="off" placeholder="Buscar por nombre, UPC o SKU…" class="w-full rounded-lg border border-slate-200 px-3 py-2.5">
                    <ul id="add-res" class="hidden absolute z-10 mt-1 w-full max-h-56 overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-lg divide-y divide-slate-100"></ul>
                </div>
                <div id="add-sel" class="hidden mt-3 flex flex-wrap items-center gap-2"></div>
            </div>` : ''}
            ${editable ? '<p class="text-xs text-slate-500 bg-slate-50 rounded-lg px-3 py-2"><i class="fa-solid fa-scale-balanced mr-1"></i>Captura lo que realmente se envía (máx. 50% sobre lo pedido). El importe, el total y el inventario se ajustan al confirmar.</p>' : ''}
            <div><p class="font-semibold mb-1">Historial</p><ul class="space-y-1">${hist}</ul></div>`;
        const btn = 'px-4 py-2.5 rounded-xl font-semibold ';
        const acc = [];
        if (r.pdf) { acc.push(`<button data-accion="pdf" class="${btn}border border-slate-200 hover:bg-slate-50"><i class="fa-solid fa-file-pdf mr-2"></i>Ver PDF</button>`); }
        if (p.estado === 'Abierto' || p.estado === 'En transito') { acc.push(`<button data-accion="cancelar" class="${btn}border border-red-200 text-red-700 hover:bg-red-50">Cancelar pedido</button>`); }
        if (p.estado === 'Abierto') { acc.push(`<button data-accion="transito" class="${btn}bg-brand-400 hover:bg-brand-500 text-ink">Surtir y enviar</button>`); }
        if (p.estado === 'En transito') { acc.push(`<button data-accion="entregar" class="${btn}bg-brand-400 hover:bg-brand-500 text-ink">Marcar entregado</button>`); }
        $('d-totales').innerHTML = `<span class="text-slate-500">Subtotal <b id="t-sub" class="text-ink">${U.money(r.totales.subtotal)}</b></span>
            <span class="text-slate-500">IVA <b id="t-iva" class="text-ink">${U.money(r.totales.iva)}</b></span>
            <span>Total <b id="t-total" class="text-lg">${U.money(r.totales.total)}</b></span>
            <span id="t-dif" class="hidden w-full text-xs text-right"></span>`;
        $('d-acciones').innerHTML = acc.join('');
        if (editable) {
            $('d-cuerpo').querySelectorAll('[data-mov]').forEach((i) => { if (conservar[i.dataset.mov] !== undefined) { i.value = conservar[i.dataset.mov]; } });
            recalcular();
        }
    }

    // Recalcula importes y totales con lo capturado, antes de confirmar
    function recalcular() {
        let total = 0, original = 0, hayCambios = false;
        $('d-cuerpo').querySelectorAll('[data-mov]').forEach((i) => {
            const v = Math.max(0, Number(i.value) || 0), unit = Number(i.dataset.unit), pedida = Number(i.dataset.pedida);
            const importe = Math.round(unit * v * 100) / 100;
            total += importe; original += Math.round(unit * pedida * 100) / 100;
            const cambio = Math.abs(v - pedida) > 0.0005;
            hayCambios = hayCambios || cambio;
            i.classList.toggle('border-amber-400', cambio); i.classList.toggle('bg-amber-50', cambio);
            const celda = $('d-cuerpo').querySelector(`[data-importe="${i.dataset.mov}"]`);
            celda.textContent = U.money(importe);
            celda.classList.toggle('font-bold', cambio);
        });
        total = Math.round(total * 100) / 100;
        const sub = Math.round(total / APP_IVA * 100) / 100;
        $('t-sub').textContent = U.money(sub); $('t-iva').textContent = U.money(total - sub); $('t-total').textContent = U.money(total);
        const dif = Math.round((total - original) * 100) / 100;
        $('t-dif').classList.toggle('hidden', !hayCambios);
        $('t-dif').textContent = `${dif >= 0 ? '+' : ''}${U.money(dif)} vs. lo pedido (${U.money(original)})`;
        $('t-dif').className = 'w-full text-xs text-right ' + (hayCambios ? (dif > 0 ? 'text-amber-700' : 'text-emerald-700') : 'hidden');
    }
    const APP_IVA = 1 + APP.taxRate;

    async function abrir(id) {
        pintarDetalle(await U.api('gestor/pedidos.php', { query: { comanda: id } }));
        U.modal('modal-pedido', true);
    }

    async function ejecutar(accion) {
        if (accion === 'pdf') { return U.pdf(actual.pedido.ComandaID); }
        const body = { accion, comanda: actual.pedido.ComandaID };
        if (accion === 'transito') {
            body.picking = {};
            $('d-cuerpo').querySelectorAll('[data-mov]').forEach((i) => { body.picking[i.dataset.mov] = i.value; });
            const cambios = [...$('d-cuerpo').querySelectorAll('[data-mov]')].filter((i) => Math.abs(Number(i.value) - Number(i.dataset.pedida)) > 0.0005).length;
            const msg = cambios ? `Se enviará con ${cambios} partida(s) distinta(s) a lo pedido. El total quedará en ${$('t-total').textContent}. ¿Confirmas?` : '¿Confirmas el surtido y enviar el pedido?';
            if (!(await U.confirm(msg))) { return; }
        } else if (accion === 'entregar') {
            if (!(await U.confirm('¿Marcar el pedido como entregado?'))) { return; }
        } else {
            const r = await Swal.fire({ title: 'Cancelar pedido', text: 'El stock se repondrá. Indica el motivo:', input: 'text', showCancelButton: true, confirmButtonText: 'Cancelar pedido', cancelButtonText: 'Volver', inputValidator: (v) => (v && v.trim() ? null : 'El motivo es obligatorio') });
            if (!r.isConfirmed) { return; }
            body.motivo = r.value.trim();
        }
        await U.api('gestor/pedidos.php', { method: 'POST', body });
        U.toast('Pedido actualizado', 'success');
        pintarDetalle(await U.api('gestor/pedidos.php', { query: { comanda: body.comanda } }));
        await cargar();
    }

    // ---- Agregar / quitar partidas (solo pedidos Abiertos) ----
    const capturado = () => Object.fromEntries([...$('d-cuerpo').querySelectorAll('[data-mov]')].map((i) => [i.dataset.mov, i.value]));
    let elegido = null;

    async function editarPartida(body) {
        const conservar = capturado();
        await U.api('gestor/pedidos.php', { method: 'POST', body: { ...body, comanda: actual.pedido.ComandaID } });
        pintarDetalle(await U.api('gestor/pedidos.php', { query: { comanda: actual.pedido.ComandaID } }), conservar);
        await cargar();
    }

    const buscarProducto = U.debounce(async () => {
        const q = $('add-q').value.trim(), lista = $('add-res');
        if (q.length < 2) { lista.classList.add('hidden'); return; }
        try {
            const r = await U.api('gestor/pedidos.php', { query: { buscar: q } });
            lista.innerHTML = r.productos.map((p) => `
                <li><button type="button" data-elegir='${U.esc(JSON.stringify(p))}' class="w-full text-left px-3 py-2 hover:bg-slate-50 flex items-center justify-between gap-3">
                    <span class="min-w-0"><span class="block truncate">${U.esc(p.Nombre)}</span><span class="block text-xs text-slate-400">${U.money(p.PrecioUnitarioIva)} c/IVA por ${U.unit(p.Tipo).abbr}</span></span>
                    <span class="text-xs font-semibold px-2 py-1 rounded-full whitespace-nowrap ${Number(p.Stock) > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'}">${U.qty(p.Stock, p.Tipo)}</span>
                </button></li>`).join('') || '<li class="px-3 py-3 text-slate-500">Sin resultados</li>';
            lista.classList.remove('hidden');
        } catch (e) { U.toast(e.message, 'error'); }
    }, 300);

    function elegirProducto(p) {
        elegido = p;
        const u = U.unit(p.Tipo);
        $('add-res').classList.add('hidden');
        $('add-q').value = '';
        const caja = $('add-sel');
        caja.classList.remove('hidden');
        caja.innerHTML = `<span class="font-medium">${U.esc(p.Nombre)}</span>
            <input id="add-cant" type="number" min="${u.entero ? 1 : 0.001}" max="${p.Stock}" step="${u.entero ? '1' : '0.001'}" inputmode="${u.entero ? 'numeric' : 'decimal'}" placeholder="Cantidad" class="w-28 rounded-lg border border-slate-200 px-2 py-1.5 text-right"><span class="text-xs text-slate-500">${u.abbr} · disp. ${U.qty(p.Stock, p.Tipo)}</span>
            <button type="button" id="add-ok" class="ml-auto px-3 py-1.5 rounded-lg bg-ink text-white font-semibold hover:bg-slate-700">Agregar</button>
            <button type="button" id="add-no" class="px-2 py-1.5 rounded-lg text-slate-500 hover:bg-slate-100" aria-label="Cancelar"><i class="fa-solid fa-xmark"></i></button>`;
        $('add-cant').focus();
    }

    $('d-cuerpo').addEventListener('input', (e) => { if (e.target.id === 'add-q') { buscarProducto(); } });
    $('d-cuerpo').addEventListener('click', async (e) => {
        const el = e.target.closest('[data-elegir]');
        if (el) { return elegirProducto(JSON.parse(el.dataset.elegir)); }
        if (e.target.closest('#add-no')) { elegido = null; return $('add-sel').classList.add('hidden'); }
        try {
            if (e.target.closest('#add-ok') && elegido) {
                const cantidad = $('add-cant').value;
                if (!(Number(cantidad) > 0)) { return U.toast('Indica la cantidad', 'error'); }
                await editarPartida({ accion: 'agregar', producto_id: elegido.ProductoID, cantidad });
                U.toast('Producto agregado; el PDF ya incluye el cambio', 'success');
                return;
            }
            const q = e.target.closest('[data-quitar]');
            if (q && await U.confirm(`¿Quitar "${q.dataset.nombre}" del pedido? El stock se repondrá.`)) {
                await editarPartida({ accion: 'quitar', movimiento_id: q.dataset.quitar });
                U.toast('Producto quitado', 'success');
            }
        } catch (err) { U.toast(err.message, 'error'); }
    });

    const recargar = U.debounce(() => cargar().catch((e) => U.toast(e.message, 'error')), 300);
    ['f-q', 'f-sucursal', 'f-solicitante', 'f-desde', 'f-hasta'].forEach((id) => { $(id).addEventListener('input', recargar); $(id).addEventListener('change', recargar); });
    $('f-limpiar').onclick = () => {
        ['f-q', 'f-sucursal', 'f-solicitante', 'f-desde', 'f-hasta'].forEach((id) => { $(id).value = ''; });
        filtros.estado = ''; recargar();
    };
    $('contadores').addEventListener('click', (e) => {
        const b = e.target.closest('[data-estado]');
        if (b) { filtros.estado = b.dataset.estado; recargar(); }
    });
    $('lista').addEventListener('click', (e) => {
        const tr = e.target.closest('[data-id]');
        if (tr) { abrir(tr.dataset.id).catch((err) => U.toast(err.message, 'error')); }
    });
    $('d-cuerpo').addEventListener('input', (e) => { if (e.target.matches('[data-mov]')) { recalcular(); } });
    $('d-acciones').addEventListener('click', (e) => {
        const b = e.target.closest('[data-accion]');
        if (b) { ejecutar(b.dataset.accion).catch((err) => U.toast(err.message, 'error')); }
    });

    cargar().catch((e) => U.toast(e.message, 'error'));
})();
</script>
