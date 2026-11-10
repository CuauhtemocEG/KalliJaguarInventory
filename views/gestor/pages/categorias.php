<?php /** Gestor · Categorías (solo Administrador) */ ?>
<div class="space-y-4">
    <div class="flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input id="buscar" type="search" placeholder="Buscar categoría…" autocomplete="off"
                   class="w-full pl-11 pr-4 py-2.5 rounded-lg border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-brand-400">
        </div>
        <button id="btn-nueva" class="px-4 py-2.5 rounded-lg bg-brand-400 hover:bg-brand-500 text-ink font-semibold whitespace-nowrap"><i class="fa-solid fa-plus mr-1"></i> Nueva categoría</button>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                <tr><th class="px-4 py-3">Nombre</th><th class="px-4 py-3 text-right">Productos</th><th class="px-4 py-3 text-right">Acciones</th></tr>
            </thead>
            <tbody id="tabla" class="divide-y divide-slate-100"></tbody>
        </table>
    </div>
    <p id="vacio" class="hidden text-center text-slate-500 py-12">No hay categorías.</p>
</div>

<div id="modal-categoria" data-modal class="fixed inset-0 z-50 bg-black/50 hidden items-center justify-center p-4">
    <form id="form" class="bg-white rounded-2xl w-full max-w-sm" novalidate>
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
            <h3 id="m-titulo" class="font-bold text-lg">Nueva categoría</h3>
            <button type="button" data-close class="w-9 h-9 rounded-lg hover:bg-slate-100"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="p-5">
            <label class="block text-xs font-semibold text-slate-600">Nombre *
                <input id="c-nombre" maxlength="100" required class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-normal">
            </label>
        </div>
        <div class="px-5 py-4 border-t border-slate-200 flex justify-end gap-2">
            <button type="button" data-close class="px-4 py-2.5 rounded-lg border border-slate-200 text-sm font-medium">Cancelar</button>
            <button id="c-guardar" type="submit" class="px-5 py-2.5 rounded-lg bg-brand-400 hover:bg-brand-500 text-ink font-semibold disabled:opacity-50">Guardar</button>
        </div>
    </form>
</div>

<script>
(() => {
    const $ = (id) => document.getElementById(id);
    let categorias = [], editando = null;

    function render() {
        const q = $('buscar').value.trim().toLowerCase();
        const lista = categorias.filter((c) => c.Nombre.toLowerCase().includes(q));
        $('vacio').classList.toggle('hidden', lista.length > 0);
        $('tabla').innerHTML = lista.map((c) => `
            <tr>
                <td class="px-4 py-3 font-medium">${U.esc(c.Nombre)}</td>
                <td class="px-4 py-3 text-right text-slate-600">${U.num(c.ProductosAsociados, 0)}</td>
                <td class="px-4 py-3 text-right whitespace-nowrap">
                    <button data-act="editar" data-id="${c.CategoriaID}" title="Editar" class="w-9 h-9 rounded-lg hover:bg-slate-100 text-slate-600"><i class="fa-solid fa-pen"></i></button>
                    <button data-act="eliminar" data-id="${c.CategoriaID}" title="Eliminar" class="w-9 h-9 rounded-lg hover:bg-slate-100 text-red-600"><i class="fa-solid fa-trash"></i></button>
                </td>
            </tr>`).join('');
    }

    async function cargar() {
        try {
            const r = await U.api('v2/categorias/index.php', { query: { withProductCount: 'true' } });
            categorias = r.data.categorias;
            render();
        } catch (e) { U.toast(e.message, 'error'); }
    }

    function abrir(c) {
        editando = c;
        $('m-titulo').textContent = c ? 'Editar categoría' : 'Nueva categoría';
        $('c-nombre').value = c ? c.Nombre : '';
        U.modal('modal-categoria', true);
        $('c-nombre').focus();
    }

    $('form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const nombre = $('c-nombre').value.trim();
        if (!nombre) { return U.toast('El nombre es obligatorio', 'error'); }
        $('c-guardar').disabled = true;
        try {
            if (editando) {
                await U.api('v2/categorias/index.php', { method: 'PUT', body: { CategoriaID: editando.CategoriaID, Nombre: nombre } });
            } else {
                await U.api('v2/categorias/index.php', { method: 'POST', body: { Nombre: nombre } });
            }
            U.modal('modal-categoria', false);
            U.toast(editando ? 'Categoría actualizada' : 'Categoría creada');
            cargar();
        } catch (err) { U.toast(err.message, 'error'); }
        finally { $('c-guardar').disabled = false; }
    });

    async function eliminar(c) {
        const n = Number(c.ProductosAsociados);
        const msg = n > 0
            ? `"${c.Nombre}" tiene ${n} producto(s). Se quedarán sin categoría. ¿Eliminar de todas formas?`
            : `¿Eliminar la categoría "${c.Nombre}"?`;
        if (!(await U.confirm(msg))) { return; }
        try {
            const query = { id: c.CategoriaID };
            if (n > 0) { query.force = 'true'; }
            const r = await U.api('v2/categorias/index.php', { method: 'DELETE', query });
            U.toast(r.message || 'Categoría eliminada');
            cargar();
        } catch (err) { U.toast(err.message, 'error'); }
    }

    document.addEventListener('click', (e) => {
        const b = e.target.closest('[data-act]');
        if (!b) { return; }
        const c = categorias.find((x) => String(x.CategoriaID) === b.dataset.id);
        if (c) { (b.dataset.act === 'editar' ? abrir : eliminar)(c); }
    });
    $('btn-nueva').addEventListener('click', () => abrir(null));
    $('buscar').addEventListener('input', U.debounce(render, 150));

    cargar();
})();
</script>
