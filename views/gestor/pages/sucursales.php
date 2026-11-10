<div class="space-y-4">
    <div id="s-stats" class="grid grid-cols-2 lg:grid-cols-4 gap-3"></div>

    <div class="bg-white rounded-2xl border border-slate-200 p-4 flex flex-col lg:flex-row gap-3">
        <div class="relative flex-1">
            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input id="s-buscar" type="search" placeholder="Buscar sucursal, dirección o gerente…" autocomplete="off" class="w-full pl-11 pr-4 py-2.5 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-400">
        </div>
        <select id="s-tipo" class="rounded-lg border border-slate-200 px-3 py-2.5 text-sm"><option value="">Todos los tipos</option><option value="principal">Principal</option><option value="sucursal">Sucursal</option><option value="almacen">Almacén</option><option value="punto_venta">Punto de venta</option></select>
        <select id="s-estado" class="rounded-lg border border-slate-200 px-3 py-2.5 text-sm"><option value="">Todos los estados</option><option value="activa">Activa</option><option value="inactiva">Inactiva</option><option value="mantenimiento">Mantenimiento</option></select>
        <button id="s-nueva" class="px-4 py-2.5 rounded-lg bg-brand-400 hover:bg-brand-500 text-ink font-semibold whitespace-nowrap"><i class="fa-solid fa-plus mr-1"></i> Nueva sucursal</button>
    </div>

    <div id="s-lista" class="grid grid-cols-1 xl:grid-cols-2 gap-3" aria-live="polite"></div>
    <div id="s-vacio" class="hidden text-center text-slate-500 py-16"><i class="fa-solid fa-store-slash text-4xl text-slate-300 mb-3"></i><p>No hay sucursales con estos filtros.</p></div>
</div>

<div id="s-modal" data-modal class="fixed inset-0 z-50 bg-black/50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-2xl max-h-[92vh] flex flex-col shadow-2xl">
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between"><h2 id="s-titulo" class="font-bold text-lg">Sucursal</h2><button type="button" data-close class="w-9 h-9 rounded-lg hover:bg-slate-100" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button></div>
        <form id="s-form" class="flex flex-col min-h-0">
            <input type="hidden" name="id">
            <div class="p-5 overflow-y-auto grid sm:grid-cols-2 gap-4 text-xs font-semibold text-slate-600">
                <label>Nombre *<input name="nombre" required maxlength="120" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-normal"></label>
                <label>Gerente<input name="gerente" maxlength="120" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-normal"></label>
                <label class="sm:col-span-2">Dirección<input name="direccion" maxlength="255" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-normal"></label>
                <label>Teléfono<input name="telefono" maxlength="30" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-normal"></label>
                <label>Correo<input type="email" name="email" maxlength="150" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-normal"></label>
                <label>Tipo<select name="tipo" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-normal"><option value="principal">Principal</option><option value="sucursal">Sucursal</option><option value="almacen">Almacén</option><option value="punto_venta">Punto de venta</option></select></label>
                <label>Estado<select name="estado" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-normal"><option value="activa">Activa</option><option value="inactiva">Inactiva</option><option value="mantenimiento">Mantenimiento</option></select>
                    <span class="block mt-1 font-normal text-slate-500">Solo las sucursales activas pueden recibir pedidos.</span></label>
                <label>Apertura<input type="time" name="horario_apertura" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-normal"></label>
                <label>Cierre<input type="time" name="horario_cierre" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-normal"></label>
                <label class="sm:col-span-2">Notas<textarea name="notas" rows="3" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-normal"></textarea></label>
            </div>
            <div class="px-5 py-4 border-t border-slate-200 flex justify-end gap-2"><button type="button" data-close class="px-4 py-2.5 rounded-lg border border-slate-200 text-sm font-medium">Cancelar</button><button class="px-5 py-2.5 rounded-lg bg-brand-400 hover:bg-brand-500 text-ink font-semibold">Guardar</button></div>
        </form>
    </div>
</div>

<script>
(function () {
    const api = 'gestor/sucursales.php', $ = (id) => document.getElementById(id), form = $('s-form');
    let rows = [], seq = 0;
    const tipos = { principal: ['Principal', 'fa-star'], sucursal: ['Sucursal', 'fa-store'], almacen: ['Almacén', 'fa-warehouse'], punto_venta: ['Punto de venta', 'fa-cash-register'] };
    const estColor = { activa: 'bg-emerald-100 text-emerald-800', inactiva: 'bg-slate-200 text-slate-600', mantenimiento: 'bg-amber-100 text-amber-800' };
    const hora = (h) => h ? String(h).slice(0, 5) : '—';
    const dato = (icono, v, vacio) => `<p class="${v ? '' : 'text-slate-400'} truncate"><i class="fa-solid ${icono} w-5 text-slate-400"></i>${U.esc(v || vacio)}</p>`;
    const btn = 'px-2.5 py-1.5 rounded-lg text-xs font-medium border border-slate-200 hover:bg-slate-50';

    function stats() {
        const n = (f) => rows.filter(f).length;
        $('s-stats').innerHTML = [['Sucursales', rows.length, ''], ['Activas', n((s) => s.estado === 'activa'), 'text-emerald-600'], ['En mantenimiento', n((s) => s.estado === 'mantenimiento'), 'text-amber-600'], ['Inactivas', n((s) => s.estado === 'inactiva'), 'text-slate-500']]
            .map(([t, v, c]) => `<div class="bg-white rounded-2xl border border-slate-200 p-4"><p class="text-xs text-slate-500">${t}</p><p class="text-2xl font-bold ${c}">${v}</p></div>`).join('');
    }

    async function load() {
        const n = ++seq;
        try {
            const data = await U.api(api, { query: { q: $('s-buscar').value, tipo: $('s-tipo').value, estado: $('s-estado').value } });
            if (n !== seq) return;
            rows = data.sucursales; stats();
            $('s-vacio').classList.toggle('hidden', rows.length > 0);
            $('s-lista').innerHTML = rows.map((s) => { const [t, ic] = tipos[s.tipo] || [s.tipo, 'fa-store']; return `
                <div class="bg-white rounded-2xl border border-slate-200 p-4 ${s.estado !== 'activa' ? 'opacity-80' : ''}">
                    <div class="flex gap-3">
                        <span class="w-11 h-11 shrink-0 rounded-xl bg-ink text-brand-400 flex items-center justify-center"><i class="fa-solid ${ic}"></i></span>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2"><strong class="truncate">${U.esc(s.nombre)}</strong><span class="px-2 py-0.5 rounded-full text-[11px] font-semibold ${estColor[s.estado] || 'bg-slate-100'}">${U.esc(s.estado)}</span></div>
                            <p class="text-xs text-slate-500">${U.esc(t)}</p>
                        </div>
                    </div>
                    <div class="mt-3 grid sm:grid-cols-2 gap-x-4 gap-y-1 text-sm text-slate-600">
                        ${dato('fa-location-dot', s.direccion, 'Sin dirección')}${dato('fa-user-tie', s.gerente, 'Sin gerente')}
                        ${dato('fa-phone', s.telefono, 'Sin teléfono')}${dato('fa-envelope', s.email, 'Sin correo')}
                        <p class="sm:col-span-2"><i class="fa-regular fa-clock w-5 text-slate-400"></i>${hora(s.horario_apertura)} – ${hora(s.horario_cierre)}</p>
                        ${s.notas ? `<p class="sm:col-span-2 text-xs text-slate-500 bg-slate-50 rounded-lg px-3 py-2 mt-1">${U.esc(s.notas)}</p>` : ''}
                    </div>
                    <div class="flex gap-2 mt-3 pt-3 border-t border-slate-100">
                        <button data-action="editar" data-id="${s.SucursalID}" class="${btn}"><i class="fa-solid fa-pen mr-1"></i>Editar</button>
                        <button data-action="eliminar" data-id="${s.SucursalID}" class="${btn} text-red-700 border-red-200 hover:bg-red-50 ml-auto"><i class="fa-solid fa-trash-can"></i></button>
                    </div>
                </div>`; }).join('');
        } catch (e) { if (n === seq) U.toast(e.message, 'error'); }
    }

    $('s-nueva').onclick = () => { form.reset(); form.elements.id.value = ''; $('s-titulo').textContent = 'Nueva sucursal'; U.modal('s-modal'); };
    form.onsubmit = async (e) => {
        e.preventDefault();
        const body = Object.fromEntries(new FormData(form));
        try { await U.api(api, { method: body.id ? 'PUT' : 'POST', body }); U.modal('s-modal', false); U.toast('Sucursal guardada.', 'success'); load(); } catch (err) { U.toast(err.message, 'error'); }
    };
    $('s-lista').onclick = async (e) => {
        const b = e.target.closest('[data-action]'); if (!b) return;
        const row = rows.find((x) => String(x.SucursalID) === b.dataset.id); if (!row) return;
        if (b.dataset.action === 'editar') {
            form.reset();
            for (const f of ['nombre', 'direccion', 'telefono', 'email', 'gerente', 'tipo', 'estado', 'horario_apertura', 'horario_cierre', 'notas']) form.elements[f].value = row[f] ?? '';
            form.elements.id.value = row.SucursalID; $('s-titulo').textContent = 'Editar sucursal'; U.modal('s-modal');
        } else if (await U.confirm(`¿Eliminar la sucursal ${row.nombre}?`)) {
            try { await U.api(api, { method: 'DELETE', body: { id: row.SucursalID } }); U.toast('Sucursal eliminada.', 'success'); load(); } catch (err) { U.toast(err.message, 'error'); }
        }
    };
    $('s-buscar').oninput = U.debounce(load); $('s-tipo').onchange = load; $('s-estado').onchange = load; load();
})();
</script>
