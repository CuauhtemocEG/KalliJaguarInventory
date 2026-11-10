<?php /** Gestor · Etiquetas (solo Administrador) */ ?>
<div class="space-y-4">
    <div class="flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input id="buscar" type="search" placeholder="Buscar etiqueta…" autocomplete="off"
                   class="w-full pl-11 pr-4 py-2.5 rounded-lg border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-brand-400">
        </div>
        <button id="btn-nueva" class="px-4 py-2.5 rounded-lg bg-brand-400 hover:bg-brand-500 text-ink font-semibold whitespace-nowrap"><i class="fa-solid fa-plus mr-1"></i> Nueva etiqueta</button>
    </div>

    <div id="lista" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3"></div>
    <p id="vacio" class="hidden text-center text-slate-500 py-12">No hay etiquetas.</p>
</div>

<div id="modal-etiqueta" data-modal class="fixed inset-0 z-50 bg-black/50 hidden items-center justify-center p-4">
    <form id="form" class="bg-white rounded-2xl w-full max-w-md max-h-[92vh] overflow-y-auto" novalidate>
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
            <h3 id="m-titulo" class="font-bold text-lg">Nueva etiqueta</h3>
            <button type="button" data-close class="w-9 h-9 rounded-lg hover:bg-slate-100"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="p-5 space-y-4">
            <label class="block text-xs font-semibold text-slate-600">Nombre *
                <input id="t-nombre" maxlength="80" required class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-normal">
            </label>
            <label class="block text-xs font-semibold text-slate-600">Descripción
                <input id="t-desc" maxlength="255" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-normal">
            </label>
            <div class="flex items-end gap-4">
                <label class="text-xs font-semibold text-slate-600">Color
                    <input id="t-color" type="color" value="#6366f1" class="mt-1 block w-16 h-11 rounded-lg border border-slate-200 p-1 cursor-pointer">
                </label>
                <div class="flex-1">
                    <p class="text-xs font-semibold text-slate-600 mb-1">Vista previa</p>
                    <span id="t-preview" class="inline-flex items-center gap-1.5 text-sm px-3 py-1 rounded-full text-white"></span>
                </div>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-600 mb-1">Icono</p>
                <div id="t-iconos" class="grid grid-cols-8 gap-1.5"></div>
            </div>
        </div>
        <div class="px-5 py-4 border-t border-slate-200 flex justify-end gap-2">
            <button type="button" data-close class="px-4 py-2.5 rounded-lg border border-slate-200 text-sm font-medium">Cancelar</button>
            <button id="t-guardar" type="submit" class="px-5 py-2.5 rounded-lg bg-brand-400 hover:bg-brand-500 text-ink font-semibold disabled:opacity-50">Guardar</button>
        </div>
    </form>
</div>

<script>
(() => {
    const $ = (id) => document.getElementById(id);
    const ICONOS = ['fa-tag', 'fa-star', 'fa-fire', 'fa-leaf', 'fa-snowflake', 'fa-bolt', 'fa-heart', 'fa-gift',
        'fa-percent', 'fa-truck', 'fa-box', 'fa-cart-shopping', 'fa-bell', 'fa-flag', 'fa-bookmark', 'fa-crown',
        'fa-seedling', 'fa-droplet', 'fa-mug-hot', 'fa-burger', 'fa-pizza-slice', 'fa-wine-glass', 'fa-candy-cane', 'fa-circle-check'];
    let etiquetas = [], editando = null, icono = 'fa-tag';

    function chip(nombre, color, ico) {
        return `<span class="inline-flex items-center gap-1.5 text-sm px-3 py-1 rounded-full text-white" style="background:${U.esc(color)}"><i class="fa-solid ${U.esc(ico)}"></i>${U.esc(nombre)}</span>`;
    }

    function render() {
        const q = $('buscar').value.trim().toLowerCase();
        const lista = etiquetas.filter((t) => t.Nombre.toLowerCase().includes(q));
        $('vacio').classList.toggle('hidden', lista.length > 0);
        $('lista').innerHTML = lista.map((t) => `
            <div class="bg-white rounded-2xl border border-slate-200 p-4 flex flex-col gap-2">
                <div class="flex items-start justify-between gap-2">
                    ${chip(t.Nombre, t.Color || '#6366f1', t.Icono || 'fa-tag')}
                    <span class="text-xs text-slate-500">${U.num(t.ProductosAsociados, 0)} producto(s)</span>
                </div>
                <p class="text-sm text-slate-600 flex-1">${U.esc(t.Descripcion || 'Sin descripción')}</p>
                <div class="flex justify-end">
                    <button data-act="editar" data-id="${t.TagID}" title="Editar" class="w-9 h-9 rounded-lg hover:bg-slate-100 text-slate-600"><i class="fa-solid fa-pen"></i></button>
                    <button data-act="eliminar" data-id="${t.TagID}" title="Eliminar" class="w-9 h-9 rounded-lg hover:bg-slate-100 text-red-600"><i class="fa-solid fa-trash"></i></button>
                </div>
            </div>`).join('');
    }

    async function cargar() {
        try {
            const r = await U.api('v2/tags/index.php', { query: { withProductCount: 'true' } });
            etiquetas = r.data.tags;
            render();
        } catch (e) { U.toast(e.message, 'error'); }
    }

    function pintarIconos() {
        $('t-iconos').innerHTML = ICONOS.map((i) => `<button type="button" data-icono="${i}" class="h-9 rounded-lg border ${i === icono ? 'border-brand-500 bg-brand-100 ring-2 ring-brand-400' : 'border-slate-200 hover:bg-slate-50'}"><i class="fa-solid ${i}"></i></button>`).join('');
    }
    function preview() {
        $('t-preview').outerHTML = chip($('t-nombre').value.trim() || 'Etiqueta', $('t-color').value, icono).replace('<span ', '<span id="t-preview" ');
    }

    function abrir(t) {
        editando = t;
        icono = t && t.Icono ? t.Icono : 'fa-tag';
        if (!ICONOS.includes(icono)) { ICONOS.unshift(icono); }
        $('m-titulo').textContent = t ? 'Editar etiqueta' : 'Nueva etiqueta';
        $('t-nombre').value = t ? t.Nombre : '';
        $('t-desc').value = t ? t.Descripcion || '' : '';
        $('t-color').value = t && /^#[0-9a-f]{6}$/i.test(t.Color || '') ? t.Color : '#6366f1';
        pintarIconos();
        preview();
        U.modal('modal-etiqueta', true);
        $('t-nombre').focus();
    }

    $('t-iconos').addEventListener('click', (e) => {
        const b = e.target.closest('[data-icono]');
        if (b) { icono = b.dataset.icono; pintarIconos(); preview(); }
    });
    $('t-nombre').addEventListener('input', preview);
    $('t-color').addEventListener('input', preview);

    $('form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const nombre = $('t-nombre').value.trim();
        if (!nombre) { return U.toast('El nombre es obligatorio', 'error'); }
        const body = { Nombre: nombre, Descripcion: $('t-desc').value.trim(), Color: $('t-color').value, Icono: icono };
        $('t-guardar').disabled = true;
        try {
            if (editando) {
                body.TagID = editando.TagID;
                await U.api('v2/tags/index.php', { method: 'PUT', body });
            } else {
                await U.api('v2/tags/index.php', { method: 'POST', body });
            }
            U.modal('modal-etiqueta', false);
            U.toast(editando ? 'Etiqueta actualizada' : 'Etiqueta creada');
            cargar();
        } catch (err) { U.toast(err.message, 'error'); }
        finally { $('t-guardar').disabled = false; }
    });

    async function eliminar(t) {
        if (!(await U.confirm(`¿Eliminar la etiqueta "${t.Nombre}"?`))) { return; }
        try {
            const r = await U.api('v2/tags/index.php', { method: 'DELETE', query: { id: t.TagID } });
            U.toast(r.message || 'Etiqueta eliminada');
            cargar();
        } catch (err) { U.toast(err.message, 'error'); }
    }

    document.addEventListener('click', (e) => {
        const b = e.target.closest('[data-act]');
        if (!b) { return; }
        const t = etiquetas.find((x) => String(x.TagID) === b.dataset.id);
        if (t) { (b.dataset.act === 'editar' ? abrir : eliminar)(t); }
    });
    $('btn-nueva').addEventListener('click', () => abrir(null));
    $('buscar').addEventListener('input', U.debounce(render, 150));

    cargar();
})();
</script>
