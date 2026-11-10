<?php /** Gestor · Productos */ ?>
<div class="space-y-4">
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3" id="stats">
        <button type="button" data-estado="normal" class="kpi text-left bg-white rounded-2xl border-2 border-slate-200 p-4 hover:border-emerald-300 transition">
            <p class="text-xs text-slate-500 flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500"></span>Con stock</p><p id="st-normal" class="text-2xl font-bold text-emerald-600 mt-1">–</p></button>
        <button type="button" data-estado="bajo" class="kpi text-left bg-white rounded-2xl border-2 border-slate-200 p-4 hover:border-amber-300 transition">
            <p class="text-xs text-slate-500 flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-amber-500"></span>Stock bajo</p><p id="st-bajo" class="text-2xl font-bold text-amber-600 mt-1">–</p></button>
        <button type="button" data-estado="sin_stock" class="kpi text-left bg-white rounded-2xl border-2 border-slate-200 p-4 hover:border-red-300 transition">
            <p class="text-xs text-slate-500 flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-red-500"></span>Sin stock</p><p id="st-sin" class="text-2xl font-bold text-red-600 mt-1">–</p></button>
        <div class="text-left bg-white rounded-2xl border-2 border-slate-200 p-4">
            <p class="text-xs text-slate-500 flex items-center gap-1.5"><i class="fa-solid fa-coins text-brand-500"></i>Valor del inventario</p><p id="st-valor" class="text-2xl font-bold text-ink mt-1">–</p><p class="text-[11px] text-slate-400">stock × precio sin IVA</p></div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-4 space-y-3">
        <div class="flex flex-col xl:flex-row gap-3">
            <div class="relative flex-1">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input id="f-buscar" type="search" placeholder="Buscar por nombre, UPC o descripción…" autocomplete="off"
                       class="w-full pl-11 pr-4 py-2.5 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-400">
            </div>
            <button id="btn-nuevo" class="hidden px-4 py-2.5 rounded-lg bg-brand-400 hover:bg-brand-500 text-ink font-semibold whitespace-nowrap"><i class="fa-solid fa-plus mr-1"></i> Nuevo producto</button>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-6 gap-2">
            <select id="f-categoria" class="rounded-lg border border-slate-200 px-3 py-2.5 text-sm"><option value="">Todas las categorías</option></select>
            <select id="f-tag" class="rounded-lg border border-slate-200 px-3 py-2.5 text-sm"><option value="">Todas las etiquetas</option></select>
            <select id="f-tipo" class="rounded-lg border border-slate-200 px-3 py-2.5 text-sm"><option value="">Todas las unidades</option>
                <?php foreach (\App\Services\Unidades::paraJs() as $t => $u): ?><option value="<?= $t ?>"><?= htmlspecialchars($u['nombre']) ?></option><?php endforeach; ?>
            </select>
            <select id="f-orden" class="rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
                <option value="nombre">Orden: nombre</option>
                <option value="stock_asc">Menos stock primero</option>
                <option value="stock_desc">Más stock primero</option>
                <option value="precio_desc">Precio más alto</option>
                <option value="precio_asc">Precio más bajo</option>
                <option value="recientes">Modificados recientemente</option>
            </select>
            <select id="f-activo" class="rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
                <option value="1">Solo activos</option>
                <option value="0">Incluir inactivos</option>
            </select>
            <button id="f-limpiar" type="button" class="hidden rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50"><i class="fa-solid fa-filter-circle-xmark mr-1"></i>Limpiar filtros</button>
        </div>
    </div>

    <!-- Tabla (escritorio) -->
    <div class="hidden md:block bg-white rounded-2xl border border-slate-200 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">Producto</th><th class="px-4 py-3">Categoría</th>
                    <th class="px-4 py-3 text-right">Precio</th><th class="px-4 py-3 text-right">Costo / margen</th>
                    <th class="px-4 py-3">Stock</th><th class="px-4 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody id="tabla" class="divide-y divide-slate-100"></tbody>
        </table>
    </div>
    <!-- Tarjetas (móvil) -->
    <div id="tarjetas" class="md:hidden space-y-3"></div>
    <p id="vacio" class="hidden text-center text-slate-500 py-12">No se encontraron productos.</p>

    <div class="flex items-center justify-between text-sm text-slate-600">
        <span id="resumen"></span>
        <div class="flex gap-2">
            <button id="pg-prev" class="px-3 py-2 rounded-lg border border-slate-200 bg-white disabled:opacity-40">Anterior</button>
            <button id="pg-next" class="px-3 py-2 rounded-lg border border-slate-200 bg-white disabled:opacity-40">Siguiente</button>
        </div>
    </div>
</div>

<!-- Modal crear/editar -->
<div id="modal-producto" data-modal class="fixed inset-0 z-50 bg-black/50 hidden items-center justify-center p-4">
    <form id="form-producto" class="bg-white rounded-2xl w-full max-w-2xl max-h-[92vh] overflow-y-auto" novalidate>
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between sticky top-0 bg-white">
            <h3 id="mp-titulo" class="font-bold text-lg">Nuevo producto</h3>
            <button type="button" data-close class="w-9 h-9 rounded-lg hover:bg-slate-100"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <label class="text-xs font-semibold text-slate-600">UPC / Código
                <input id="p-upc" maxlength="50" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-normal">
            </label>
            <label class="text-xs font-semibold text-slate-600">Nombre *
                <input id="p-nombre" maxlength="200" required class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-normal">
            </label>
            <label class="text-xs font-semibold text-slate-600 sm:col-span-2">Descripción
                <textarea id="p-desc" rows="2" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-normal"></textarea>
            </label>
            <label class="text-xs font-semibold text-slate-600">Tipo
                <select id="p-tipo" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-normal">
                    <?php foreach (\App\Services\Unidades::paraJs() as $t => $u): ?><option value="<?= $t ?>"><?= htmlspecialchars($u['nombre']) ?></option><?php endforeach; ?>
                </select>
            </label>
            <label class="text-xs font-semibold text-slate-600">Categoría
                <select id="p-categoria" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-normal"><option value="">Sin categoría</option></select>
            </label>
            <label class="text-xs font-semibold text-slate-600">Precio sin IVA *
                <input id="p-precio" type="number" min="0" step="0.01" required class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-normal">
                <span id="p-iva" class="block mt-1 text-slate-500 font-normal"></span>
            </label>
            <label class="text-xs font-semibold text-slate-600">Stock mínimo
                <input id="p-min" type="number" min="0" step="0.001" value="5" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-normal">
            </label>
            <label id="wrap-stock" class="text-xs font-semibold text-slate-600">Stock inicial
                <input id="p-stock" type="number" min="0" step="0.001" value="0" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-normal">
            </label>
            <div class="sm:col-span-2">
                <p class="text-xs font-semibold text-slate-600 mb-1">Etiquetas</p>
                <div id="p-tags" class="flex flex-wrap gap-2"></div>
            </div>
            <div class="sm:col-span-2 flex items-center gap-4">
                <img id="p-preview" src="" alt="" class="w-24 h-24 rounded-xl object-cover border border-slate-200 bg-slate-50">
                <div>
                    <label class="inline-block cursor-pointer px-4 py-2 rounded-lg border border-slate-200 text-sm font-medium hover:bg-slate-50">
                        <i class="fa-solid fa-image mr-1"></i> Seleccionar imagen
                        <input id="p-file" type="file" accept="image/jpeg,image/png,image/gif,image/webp" class="hidden">
                    </label>
                    <p class="text-xs text-slate-500 mt-1">JPG, PNG, GIF o WEBP.</p>
                </div>
            </div>
        </div>
        <div class="px-5 py-4 border-t border-slate-200 flex justify-end gap-2 sticky bottom-0 bg-white">
            <button type="button" data-close class="px-4 py-2.5 rounded-lg border border-slate-200 text-sm font-medium">Cancelar</button>
            <button id="p-guardar" type="submit" class="px-5 py-2.5 rounded-lg bg-brand-400 hover:bg-brand-500 text-ink font-semibold disabled:opacity-50">Guardar</button>
        </div>
    </form>
</div>

<!-- Modal historial -->
<div id="modal-historial" data-modal class="fixed inset-0 z-50 bg-black/50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-lg max-h-[90vh] flex flex-col">
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
            <div><h3 class="font-bold">Historial</h3><p id="h-nombre" class="text-xs text-slate-500"></p></div>
            <button type="button" data-close class="w-9 h-9 rounded-lg hover:bg-slate-100"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div id="h-cuerpo" class="p-5 overflow-y-auto space-y-5 text-sm"></div>
    </div>
</div>

<!-- Modal ajuste rápido -->
<div id="modal-ajuste" data-modal class="fixed inset-0 z-50 bg-black/50 hidden items-center justify-center p-4">
    <form id="form-ajuste" class="bg-white rounded-2xl w-full max-w-sm" novalidate>
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
            <h3 class="font-bold">Ajuste rápido</h3>
            <button type="button" data-close class="w-9 h-9 rounded-lg hover:bg-slate-100"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="p-5 space-y-3">
            <p id="aj-nombre" class="font-semibold text-sm"></p>
            <label class="block text-xs font-semibold text-slate-600">Stock
                <input id="aj-stock" type="number" min="0" step="0.001" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-normal">
            </label>
            <p id="aj-dif" class="text-xs text-slate-500 -mt-1"></p>
            <label id="aj-wrap-precio" class="block text-xs font-semibold text-slate-600">Precio sin IVA
                <input id="aj-precio" type="number" min="0" step="0.01" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-normal">
                <span id="aj-iva" class="block mt-1 text-slate-500 font-normal"></span>
            </label>
            <p id="aj-nota" class="hidden text-xs bg-slate-50 rounded-lg px-3 py-2 text-slate-500"><i class="fa-solid fa-lock mr-1"></i>Solo un Administrador puede cambiar precios.</p>
        </div>
        <div class="px-5 py-4 border-t border-slate-200 flex justify-end gap-2">
            <button type="button" data-close class="px-4 py-2.5 rounded-lg border border-slate-200 text-sm font-medium">Cancelar</button>
            <button id="aj-guardar" type="submit" class="px-5 py-2.5 rounded-lg bg-brand-400 hover:bg-brand-500 text-ink font-semibold disabled:opacity-50">Aplicar</button>
        </div>
    </form>
</div>

<script>
(() => {
    const IMG = <?= json_encode(product_upload_url()) ?>;
    const PLACEHOLDER = <?= json_encode(asset('img/producto.png')) ?>;
    const USER_ID = <?= json_encode((int) ($_SESSION['id'] ?? 0)) ?>;
    const ES_ADMIN = APP.role === 'Administrador'; // la API v2 solo permite escribir a Administrador
    const LIMIT = 20;
    const $ = (id) => document.getElementById(id);
    let estado = '', page = 0, total = 0, productos = [], editando = null, imagen = null, imagenOriginal = null, precioOriginal = null;

    const imgSrc = (f) => f ? IMG + encodeURIComponent(f) : PLACEHOLDER;
    const conIva = (p) => U.money(Number(p) * (1 + APP.taxRate));
    const bajo = (p) => Number(p.Cantidad) <= Number(p.StockMinimo);

    // Las unidades enteras (pieza, manojo) no aceptan decimales en el stock
    function ajustarPasos() {
        const paso = U.unit($('p-tipo').value).entero ? '1' : '0.001';
        $('p-stock').step = paso;
        $('aj-stock').step = paso;
    }
    $('p-tipo').addEventListener('change', ajustarPasos);

    function tagsHtml(p) {
        return (p.tags || []).map((t) => `<span class="inline-flex items-center gap-1 text-[11px] px-2 py-0.5 rounded-full text-white" style="background:${U.esc(t.Color || '#64748b')}"><i class="fa-solid ${U.esc(t.Icono || 'fa-tag')}"></i>${U.esc(t.Nombre)}</span>`).join(' ');
    }
    function stockBadge(p) {
        const q = Number(p.Cantidad);
        if (q <= 0) { return '<span class="text-[11px] px-2 py-0.5 rounded-full bg-red-100 text-red-700 font-medium">Sin stock</span>'; }
        if (bajo(p)) { return '<span class="text-[11px] px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 font-medium"><i class="fa-solid fa-triangle-exclamation"></i> Bajo</span>'; }
        return '';
    }
    // La barra llena a 2× el mínimo; así se ve de un vistazo qué tan cerca está de quedarse sin producto
    function stockBar(p) {
        const q = Number(p.Cantidad), min = Number(p.StockMinimo);
        const pct = min > 0 ? Math.min(100, (q / (min * 2)) * 100) : (q > 0 ? 100 : 0);
        const color = q <= 0 ? 'bg-red-500' : (bajo(p) ? 'bg-amber-500' : 'bg-emerald-500');
        return `<div class="h-1.5 w-28 rounded-full bg-slate-100 overflow-hidden mt-1.5" title="Mínimo: ${U.qty(min, p.Tipo)}"><div class="h-full ${color}" style="width:${pct}%"></div></div>`;
    }
    function costoHtml(p) {
        if (p.CostoUltimo === null || p.CostoUltimo === undefined || Number(p.CostoUltimo) <= 0) { return '<span class="text-slate-300">—</span>'; }
        const precio = Number(p.PrecioUnitario), costo = Number(p.CostoUltimo);
        const margen = precio > 0 ? ((precio - costo) / precio) * 100 : null;
        const color = margen === null ? 'text-slate-500' : (margen < 0 ? 'text-red-600' : (margen < 15 ? 'text-amber-600' : 'text-emerald-600'));
        return `<span class="text-slate-700">${U.money(costo)}</span>${margen === null ? '' : `<span class="block text-xs font-semibold ${color}">${margen.toFixed(0)}% margen</span>`}`;
    }
    function acciones(p) {
        const b = 'w-9 h-9 rounded-lg hover:bg-slate-100 text-slate-600';
        let h = `<button data-act="ajuste" data-id="${p.ProductoID}" title="Ajuste rápido" class="${b}"><i class="fa-solid fa-sliders"></i></button>
                 <button data-act="historial" data-id="${p.ProductoID}" title="Historial" class="${b}"><i class="fa-solid fa-clock-rotate-left"></i></button>`;
        if (ES_ADMIN) {
            const activo = Number(p.Activo) === 1;
            h += `<button data-act="editar" data-id="${p.ProductoID}" title="Editar" class="${b}"><i class="fa-solid fa-pen"></i></button>
                  <button data-act="toggle" data-id="${p.ProductoID}" title="${activo ? 'Desactivar' : 'Activar'}" class="${b}"><i class="fa-solid ${activo ? 'fa-eye-slash' : 'fa-eye'}"></i></button>
                  <button data-act="eliminar" data-id="${p.ProductoID}" title="Eliminar" class="${b} text-red-600"><i class="fa-solid fa-trash"></i></button>`;
        }
        return h;
    }
    const inactivo = (p) => Number(p.Activo) === 1 ? '' : '<span class="text-[11px] px-2 py-0.5 rounded-full bg-slate-200 text-slate-600 font-medium">Inactivo</span>';

    function hayFiltros() {
        return !!(estado || $('f-buscar').value.trim() || $('f-categoria').value || $('f-tag').value || $('f-tipo').value || $('f-activo').value === '0' || $('f-orden').value !== 'nombre');
    }
    function marcarKpis() {
        document.querySelectorAll('.kpi').forEach((k) => {
            const on = k.dataset.estado === estado;
            k.classList.toggle('border-brand-400', on);
            k.classList.toggle('ring-2', on);
            k.classList.toggle('ring-brand-200', on);
            k.classList.toggle('border-slate-200', !on);
        });
        $('f-limpiar').classList.toggle('hidden', !hayFiltros());
    }

    function render() {
        $('vacio').classList.toggle('hidden', productos.length > 0);
        $('tabla').innerHTML = productos.map((p) => `
            <tr class="hover:bg-slate-50/60 ${Number(p.Activo) === 1 ? '' : 'opacity-60'}">
                <td class="px-4 py-3"><div class="flex items-center gap-3">
                    <img src="${imgSrc(p.image)}" alt="" loading="lazy" class="w-12 h-12 rounded-lg object-cover bg-slate-100 shrink-0" onerror="this.src='${PLACEHOLDER}'">
                    <div class="min-w-0"><p class="font-semibold">${U.esc(p.Nombre)} ${inactivo(p)}</p><p class="text-xs text-slate-500">${U.esc(p.UPC || '—')} · ${U.esc(U.unit(p.Tipo).nombre)}</p><div class="mt-1 flex flex-wrap gap-1">${tagsHtml(p)}</div></div>
                </div></td>
                <td class="px-4 py-3 text-slate-600">${U.esc(p.CategoriaNombre || '—')}</td>
                <td class="px-4 py-3 text-right"><span class="font-semibold">${U.money(p.PrecioUnitario)}</span><span class="block text-xs text-slate-500">${conIva(p.PrecioUnitario)} c/IVA</span></td>
                <td class="px-4 py-3 text-right">${costoHtml(p)}</td>
                <td class="px-4 py-3"><span class="font-semibold">${U.qty(p.Cantidad, p.Tipo)}</span> ${stockBadge(p)}${stockBar(p)}</td>
                <td class="px-4 py-3 text-right whitespace-nowrap">${acciones(p)}</td>
            </tr>`).join('');
        $('tarjetas').innerHTML = productos.map((p) => `
            <div class="bg-white rounded-2xl border border-slate-200 p-3 ${Number(p.Activo) === 1 ? '' : 'opacity-60'}">
                <div class="flex gap-3">
                    <img src="${imgSrc(p.image)}" alt="" loading="lazy" class="w-16 h-16 rounded-xl object-cover bg-slate-100 shrink-0" onerror="this.src='${PLACEHOLDER}'">
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold truncate">${U.esc(p.Nombre)} ${inactivo(p)}</p>
                        <p class="text-xs text-slate-500 truncate">${U.esc(p.UPC || '—')} · ${U.esc(p.CategoriaNombre || 'Sin categoría')} · ${U.esc(U.unit(p.Tipo).nombre)}</p>
                        <p class="text-sm mt-1"><b>${U.money(p.PrecioUnitario)}</b> <span class="text-xs text-slate-500">(${conIva(p.PrecioUnitario)} c/IVA)</span></p>
                        <p class="text-sm">Stock: <b>${U.qty(p.Cantidad, p.Tipo)}</b> ${stockBadge(p)}</p>
                        ${stockBar(p)}
                    </div>
                </div>
                <div class="mt-2 flex flex-wrap gap-1">${tagsHtml(p)}</div>
                <div class="mt-2 flex justify-end">${acciones(p)}</div>
            </div>`).join('');
        marcarKpis();
        const desde = total ? page * LIMIT + 1 : 0;
        $('resumen').textContent = `${desde}–${Math.min(total, (page + 1) * LIMIT)} de ${total}`;
        $('pg-prev').disabled = page === 0;
        $('pg-next').disabled = (page + 1) * LIMIT >= total;
    }

    async function cargar() {
        const query = { limit: LIMIT, offset: page * LIMIT };
        if ($('f-buscar').value.trim()) { query.search = $('f-buscar').value.trim(); }
        if ($('f-categoria').value) { query.categoria = $('f-categoria').value; }
        if ($('f-tag').value) { query.tag = $('f-tag').value; }
        if (estado) { query.estado = estado; }
        if ($('f-tipo').value) { query.tipo = $('f-tipo').value; }
        if ($('f-orden').value !== 'nombre') { query.orden = $('f-orden').value; }
        if ($('f-activo').value === '0') { query.includeInactive = 'true'; }
        try {
            const r = await U.api('v2/productos/index.php', { query });
            productos = r.data.productos;
            total = Number(r.data.total);
            const s = r.data.stats || {};
            $('st-normal').textContent = s.normal ?? 0;
            $('st-bajo').textContent = s.bajo ?? 0;
            $('st-sin').textContent = s.sin_stock ?? 0;
            $('st-valor').textContent = U.money(s.valor ?? 0);
            render();
        } catch (e) { U.toast(e.message, 'error'); }
    }
    const recargar = () => { page = 0; cargar(); };

    async function cargarCatalogos() {
        try {
            const [c, t] = await Promise.all([U.api('v2/categorias/index.php'), U.api('v2/tags/index.php')]);
            const opts = c.data.categorias.map((x) => `<option value="${x.CategoriaID}">${U.esc(x.Nombre)}</option>`).join('');
            $('f-categoria').insertAdjacentHTML('beforeend', opts);
            $('p-categoria').insertAdjacentHTML('beforeend', opts);
            $('f-tag').insertAdjacentHTML('beforeend', t.data.tags.map((x) => `<option value="${x.TagID}">${U.esc(x.Nombre)}</option>`).join(''));
            $('p-tags').innerHTML = t.data.tags.map((x) => `<label class="inline-flex items-center gap-1.5 text-xs px-2.5 py-1.5 rounded-full border border-slate-200 cursor-pointer"><input type="checkbox" value="${x.TagID}" class="rounded"> <i class="fa-solid ${U.esc(x.Icono || 'fa-tag')}" style="color:${U.esc(x.Color || '#64748b')}"></i>${U.esc(x.Nombre)}</label>`).join('') || '<span class="text-xs text-slate-400">No hay etiquetas.</span>';
        } catch (e) { U.toast(e.message, 'error'); }
    }

    // ---------- Crear / editar ----------
    function abrirForm(p) {
        editando = p;
        imagen = p ? p.image || null : null;
        imagenOriginal = imagen;
        precioOriginal = p ? Number(p.PrecioUnitario) : null;
        $('mp-titulo').textContent = p ? 'Editar producto' : 'Nuevo producto';
        $('p-upc').value = p ? p.UPC || '' : '';
        $('p-nombre').value = p ? p.Nombre : '';
        $('p-desc').value = p ? p.Descripcion || '' : '';
        $('p-tipo').value = p ? p.Tipo : 'Unidad';
        ajustarPasos();
        $('p-categoria').value = p && p.CategoriaID ? p.CategoriaID : '';
        $('p-precio').value = p ? p.PrecioUnitario : '';
        $('p-min').value = p ? p.StockMinimo : 5;
        $('p-stock').value = 0;
        $('wrap-stock').classList.toggle('hidden', !!p);
        const ids = p ? (p.tags || []).map((t) => String(t.TagID)) : [];
        $('p-tags').querySelectorAll('input').forEach((i) => { i.checked = ids.includes(i.value); });
        $('p-preview').src = imgSrc(imagen);
        $('p-file').value = '';
        actualizarIva();
        U.modal('modal-producto', true);
    }
    function actualizarIva() {
        const v = parseFloat($('p-precio').value);
        $('p-iva').textContent = isNaN(v) ? '' : 'Con IVA: ' + conIva(v);
    }

    async function subirImagen(file) {
        const fd = new FormData();
        fd.append('imagen', file);
        fd.append('nombre', $('p-nombre').value || 'producto');
        const res = await fetch(U.url('api/uploadProductImage.php'), { method: 'POST', body: fd, credentials: 'same-origin' });
        let d = null;
        try { d = await res.json(); } catch (e) { /* no JSON */ }
        if (!res.ok || !d || !d.success) { throw new Error((d && d.message) || 'No se pudo subir la imagen'); }
        return d.data.filename;
    }

    async function guardar(e) {
        e.preventDefault();
        const nombre = $('p-nombre').value.trim();
        const precio = parseFloat($('p-precio').value);
        const minimo = parseFloat($('p-min').value);
        if (!nombre) { return U.toast('El nombre es obligatorio', 'error'); }
        if (isNaN(precio) || precio < 0) { return U.toast('Ingrese un precio válido', 'error'); }
        if (isNaN(minimo) || minimo < 0) { return U.toast('El stock mínimo no es válido', 'error'); }
        const tags = [...$('p-tags').querySelectorAll('input:checked')].map((i) => Number(i.value));
        const btn = $('p-guardar');
        btn.disabled = true;
        try {
            const file = $('p-file').files[0];
            if (file) { imagen = await subirImagen(file); }
            const body = {
                UPC: $('p-upc').value.trim() || null, Nombre: nombre, Descripcion: $('p-desc').value.trim(),
                Tipo: $('p-tipo').value, StockMinimo: minimo, tags,
            };
            if ($('p-categoria').value) { body.CategoriaID = Number($('p-categoria').value); }
            if (imagen) { body.image = imagen; }
            if (editando) {
                body.ProductoID = editando.ProductoID;
                await U.api('v2/productos/index.php', { method: 'PUT', body });
                // el precio pasa por el ajuste para quedar en Logs_precio
                if (precio !== precioOriginal) {
                    await U.api('gestor/productos.php', { method: 'POST', body: { ProductoID: editando.ProductoID, PrecioUnitario: precio } });
                }
            } else {
                body.PrecioUnitario = precio;
                body.Cantidad = parseFloat($('p-stock').value) || 0;
                body.UsuarioID = USER_ID || 1;
                await U.api('v2/productos/index.php', { method: 'POST', body });
            }
            U.modal('modal-producto', false);
            U.toast(editando ? 'Producto actualizado' : 'Producto creado');
            cargar();
        } catch (err) { U.toast(err.message, 'error'); }
        finally { btn.disabled = false; }
    }

    // ---------- Ajuste rápido ----------
    let ajustando = null;
    function abrirAjuste(p) {
        ajustando = p;
        $('aj-nombre').textContent = p.Nombre;
        $('aj-stock').value = p.Cantidad;
        $('aj-precio').value = p.PrecioUnitario;
        $('aj-wrap-precio').classList.toggle('hidden', !ES_ADMIN);
        $('aj-nota').classList.toggle('hidden', ES_ADMIN);
        previsualizarAjuste();
        U.modal('modal-ajuste', true);
    }
    function previsualizarAjuste() {
        const v = parseFloat($('aj-stock').value);
        const dif = isNaN(v) ? 0 : v - Number(ajustando.Cantidad);
        $('aj-dif').innerHTML = Math.abs(dif) < 0.0005 ? `Actual: ${U.qty(ajustando.Cantidad, ajustando.Tipo)}`
            : `Actual: ${U.qty(ajustando.Cantidad, ajustando.Tipo)} · <b class="${dif > 0 ? 'text-emerald-600' : 'text-red-600'}">${dif > 0 ? '+' : '−'}${U.qty(Math.abs(dif), ajustando.Tipo)}</b>`;
        const pr = parseFloat($('aj-precio').value);
        $('aj-iva').textContent = isNaN(pr) ? '' : 'Con IVA: ' + conIva(pr);
    }

    // ---------- Historial ----------
    async function abrirHistorial(p) {
        $('h-nombre').textContent = p.Nombre;
        $('h-cuerpo').innerHTML = '<p class="text-slate-500"><i class="fa-solid fa-spinner fa-spin mr-2"></i>Cargando…</p>';
        U.modal('modal-historial', true);
        try {
            const h = await U.api('gestor/productos.php', { query: { id: p.ProductoID } });
            const fecha = (f) => new Date(String(f).replace(' ', 'T')).toLocaleString('es-MX', { dateStyle: 'short', timeStyle: 'short' });
            const lista = (items, fmt, vacio) => items.length ? `<ul class="divide-y divide-slate-100">${items.map((x) => `<li class="py-2 flex items-center justify-between gap-3"><span><b>${fmt(x.antes)}</b> <i class="fa-solid fa-arrow-right text-xs text-slate-400 mx-1"></i> <b>${fmt(x.despues)}</b></span><span class="text-xs text-slate-500 text-right">${U.esc(x.usuario)}<br>${fecha(x.fecha)}</span></li>`).join('')}</ul>` : `<p class="text-slate-400">${vacio}</p>`;
            $('h-cuerpo').innerHTML = `
                <section><h4 class="font-bold mb-1"><i class="fa-solid fa-boxes-stacked text-slate-400 mr-1.5"></i>Cambios de stock</h4>${lista(h.stock, (v) => U.qty(v, p.Tipo), 'Sin cambios registrados.')}</section>
                <section><h4 class="font-bold mb-1"><i class="fa-solid fa-tag text-slate-400 mr-1.5"></i>Cambios de precio</h4>${lista(h.precio, (v) => U.money(v), 'Sin cambios registrados.')}</section>
                <p class="text-xs text-slate-400">Se muestran los últimos 15 cambios de cada tipo.</p>`;
        } catch (err) { $('h-cuerpo').innerHTML = `<p class="text-red-600">${U.esc(err.message)}</p>`; }
    }

    async function aplicarAjuste(e) {
        e.preventDefault();
        const stock = $('aj-stock').value, precio = $('aj-precio').value;
        const body = { ProductoID: ajustando.ProductoID };
        if (stock !== '' && Number(stock) !== Number(ajustando.Cantidad)) { body.Cantidad = Number(stock); }
        if (ES_ADMIN && precio !== '' && Number(precio) !== Number(ajustando.PrecioUnitario)) { body.PrecioUnitario = Number(precio); }
        if (body.Cantidad === undefined && body.PrecioUnitario === undefined) { return U.modal('modal-ajuste', false); }
        $('aj-guardar').disabled = true;
        try {
            await U.api('gestor/productos.php', { method: 'POST', body });
            U.modal('modal-ajuste', false);
            U.toast('Producto actualizado');
            cargar();
        } catch (err) { U.toast(err.message, 'error'); }
        finally { $('aj-guardar').disabled = false; }
    }

    // ---------- Activar / eliminar ----------
    async function toggle(p) {
        const activar = Number(p.Activo) !== 1;
        if (!(await U.confirm(`¿${activar ? 'Activar' : 'Desactivar'} "${p.Nombre}"?`))) { return; }
        try {
            await U.api('v2/productos/index.php', { method: 'PUT', body: { ProductoID: p.ProductoID, Activo: activar ? 1 : 0 } });
            U.toast(activar ? 'Producto activado' : 'Producto desactivado');
            cargar();
        } catch (e) { U.toast(e.message, 'error'); }
    }
    async function eliminar(p) {
        if (!(await U.confirm(`¿Eliminar definitivamente "${p.Nombre}"? Esta acción no se puede deshacer.`))) { return; }
        try {
            await U.api('v2/productos/index.php', { method: 'DELETE', query: { id: p.ProductoID, force: 'true' } });
            U.toast('Producto eliminado');
            cargar();
        } catch (e) { U.toast(e.message, 'error'); }
    }

    // ---------- Eventos ----------
    document.addEventListener('click', (e) => {
        const b = e.target.closest('[data-act]');
        if (!b) { return; }
        const p = productos.find((x) => String(x.ProductoID) === b.dataset.id);
        if (!p) { return; }
        ({ ajuste: abrirAjuste, historial: abrirHistorial, editar: abrirForm, toggle, eliminar })[b.dataset.act](p);
    });
    $('f-buscar').addEventListener('input', U.debounce(recargar, 350));
    ['f-categoria', 'f-tag', 'f-tipo', 'f-orden', 'f-activo'].forEach((id) => $(id).addEventListener('change', recargar));
    document.querySelectorAll('.kpi').forEach((k) => k.addEventListener('click', () => { estado = estado === k.dataset.estado ? '' : k.dataset.estado; recargar(); }));
    $('f-limpiar').addEventListener('click', () => {
        estado = ''; $('f-buscar').value = '';
        ['f-categoria', 'f-tag', 'f-tipo'].forEach((id) => { $(id).value = ''; });
        $('f-orden').value = 'nombre'; $('f-activo').value = '1';
        recargar();
    });
    $('aj-stock').addEventListener('input', previsualizarAjuste);
    $('aj-precio').addEventListener('input', previsualizarAjuste);
    $('pg-prev').addEventListener('click', () => { page--; cargar(); });
    $('pg-next').addEventListener('click', () => { page++; cargar(); });
    $('form-producto').addEventListener('submit', guardar);
    $('form-ajuste').addEventListener('submit', aplicarAjuste);
    $('p-precio').addEventListener('input', actualizarIva);
    $('p-file').addEventListener('change', (e) => {
        const f = e.target.files[0];
        if (f) { $('p-preview').src = URL.createObjectURL(f); }
    });
    if (ES_ADMIN) {
        $('btn-nuevo').classList.remove('hidden');
        $('btn-nuevo').addEventListener('click', () => abrirForm(null));
    }

    cargarCatalogos().then(cargar);
})();
</script>
