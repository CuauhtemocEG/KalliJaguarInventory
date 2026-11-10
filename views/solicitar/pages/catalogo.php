<?php
/** Solicitar insumos · catálogo + carrito + confirmación */
require_once BASE_PATH . '/src/Helpers/maintenanceHelper.php';
$mantenimiento = getMaintenanceInfo();
if ($mantenimiento !== null) {
    logBlockedAccess((int) ($_SESSION['id'] ?? 0) ?: null, $mantenimiento['reason'], 'solicitar/catalogo');
}
?>
<?php if ($mantenimiento !== null): ?>
<section class="max-w-xl mx-auto mt-6 sm:mt-14 bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden text-center" role="alert">
    <div class="bg-ink px-6 pt-8 pb-10">
        <img src="<?= htmlspecialchars($mantenimiento['logo']) ?>" alt="" class="h-16 mx-auto" onerror="this.style.display='none'">
    </div>
    <div class="-mt-7 mx-auto w-14 h-14 rounded-full bg-brand-400 text-ink flex items-center justify-center text-2xl ring-4 ring-white"><i class="fa-solid fa-lock"></i></div>
    <div class="px-6 pb-8 pt-4 space-y-3">
        <h1 class="text-xl sm:text-2xl font-bold"><?= htmlspecialchars($mantenimiento['message']) ?></h1>
        <p class="text-slate-500"><?= htmlspecialchars($mantenimiento['submessage']) ?></p>
        <?php if ($mantenimiento['show_reasons'] && !empty($mantenimiento['reason_details'])): ?>
            <p class="text-sm bg-amber-50 border border-amber-100 text-amber-800 rounded-xl px-4 py-3"><i class="fa-regular fa-clock mr-1.5"></i><?= htmlspecialchars($mantenimiento['reason_details']) ?></p>
        <?php endif; ?>
        <div class="flex flex-col sm:flex-row gap-2 justify-center pt-2">
            <a href="<?= htmlspecialchars(app_url('solicitar/pedidos')) ?>" class="px-5 py-2.5 rounded-xl bg-brand-400 hover:bg-brand-500 text-ink font-semibold"><i class="fa-solid fa-clipboard-list mr-1.5"></i>Ver mis pedidos</a>
            <a href="<?= htmlspecialchars(app_url('solicitar/requisiciones')) ?>" class="px-5 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 font-semibold"><i class="fa-solid fa-list-check mr-1.5"></i>Requisiciones</a>
        </div>
    </div>
</section>
<?php return; endif; ?>
<div class="space-y-4 pb-24 sm:pb-6">
    <section class="rounded-2xl bg-ink text-white p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold">¿Qué necesitas hoy?</h1>
            <p class="text-sm text-slate-300 mt-1">Agrega los insumos, revisa tu solicitud y envíala al almacén.</p>
        </div>
        <div class="flex gap-4 text-sm text-slate-300">
            <span><i class="fa-solid fa-check text-brand-400 mr-1"></i>Stock en tiempo real</span>
            <span class="hidden md:inline"><i class="fa-solid fa-file-pdf text-brand-400 mr-1"></i>Comprobante en PDF</span>
        </div>
    </section>

    <div class="sticky top-16 z-20 -mx-4 px-4 py-3 bg-slate-50/95 backdrop-blur space-y-3 border-b border-slate-200">
        <div class="flex flex-col sm:flex-row gap-2">
            <div class="relative flex-1">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input id="buscar" type="search" placeholder="Buscar por nombre o código…" autocomplete="off"
                       class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-brand-400">
            </div>
            <div class="flex gap-2">
                <select id="orden" class="flex-1 sm:flex-none rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm">
                    <option value="pop">Más pedidos</option>
                    <option value="az">Nombre A–Z</option>
                    <option value="precio">Precio menor</option>
                </select>
                <label class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm cursor-pointer select-none whitespace-nowrap">
                    <input id="solo-disp" type="checkbox" class="rounded accent-amber-500"> Solo disponibles
                </label>
            </div>
        </div>
        <div id="categorias" class="flex gap-2 overflow-x-auto pb-1 [scrollbar-width:none]"></div>
    </div>

    <p id="resultados" class="text-sm text-slate-500"></p>
    <div id="productos" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3"></div>
    <div id="vacio" class="hidden text-center text-slate-500 py-16">
        <i class="fa-solid fa-box-open text-4xl text-slate-300 mb-3"></i>
        <p>No se encontraron productos con esos filtros.</p>
        <button id="limpiar-filtros" class="mt-3 text-sm font-semibold text-brand-600 hover:underline">Limpiar filtros</button>
    </div>
</div>

<!-- Barra fija del carrito (móvil y escritorio) -->
<div id="cart-bar" class="hidden fixed z-30 inset-x-3 bottom-[68px] sm:bottom-4 sm:left-auto sm:right-4 sm:w-96">
    <button id="cart-bar-btn" class="w-full flex items-center justify-between gap-3 rounded-2xl bg-ink text-white px-4 py-3 shadow-xl ring-1 ring-white/10">
        <span class="flex items-center gap-3">
            <span class="relative"><i class="fa-solid fa-cart-shopping text-brand-400 text-lg"></i></span>
            <span class="text-sm text-left leading-tight"><b id="bar-n">0</b> <span id="bar-n-txt">productos</span><br><span class="text-xs text-slate-400">Ver y enviar solicitud</span></span>
        </span>
        <span class="text-right leading-tight"><span class="text-xs text-slate-400 block">Total c/IVA</span><b id="bar-total" class="text-base"></b></span>
    </button>
</div>

<!-- Carrito -->
<div id="cart-overlay" class="fixed inset-0 z-40 bg-black/50 hidden"></div>
<aside id="cart-drawer" class="fixed top-0 right-0 bottom-0 z-50 w-full max-w-md bg-white shadow-2xl translate-x-full transition-transform flex flex-col" aria-label="Tu solicitud">
    <div class="h-16 px-5 flex items-center justify-between border-b border-slate-200">
        <h2 class="font-bold text-lg">Tu solicitud</h2>
        <div class="flex items-center gap-1">
            <button id="cart-clear" class="hidden px-3 py-2 rounded-lg text-xs font-semibold text-red-600 hover:bg-red-50">Vaciar</button>
            <button id="cart-close" class="w-9 h-9 rounded-lg hover:bg-slate-100" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button>
        </div>
    </div>
    <div id="cart-items" class="flex-1 overflow-y-auto p-4 space-y-3"></div>
    <div class="border-t border-slate-200 p-4 space-y-3">
        <div class="grid grid-cols-2 gap-3">
            <label class="text-xs font-semibold text-slate-600">Sucursal destino
                <select id="sucursal" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-normal"></select>
            </label>
            <label class="text-xs font-semibold text-slate-600">Fecha de entrega
                <input id="fecha" type="date" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-normal">
            </label>
        </div>
        <div class="flex gap-2 text-xs">
            <button type="button" data-dias="0" class="px-3 py-1.5 rounded-full border border-slate-200 hover:border-slate-400">Hoy</button>
            <button type="button" data-dias="1" class="px-3 py-1.5 rounded-full border border-slate-200 hover:border-slate-400">Mañana</button>
            <button type="button" data-dias="2" class="px-3 py-1.5 rounded-full border border-slate-200 hover:border-slate-400">En 2 días</button>
        </div>
        <dl class="text-sm space-y-1">
            <div class="flex justify-between text-slate-500"><dt>Subtotal</dt><dd id="t-sub"></dd></div>
            <div class="flex justify-between text-slate-500"><dt id="t-iva-label">IVA</dt><dd id="t-iva"></dd></div>
            <div class="flex justify-between font-bold text-base"><dt>Total</dt><dd id="t-total"></dd></div>
        </dl>
        <p class="text-[11px] text-slate-400">El almacén confirmará las cantidades realmente enviadas y el total puede ajustarse.</p>
        <p id="cart-aviso" class="hidden text-xs text-red-600 bg-red-50 rounded-lg px-3 py-2">Corrige o elimina los productos marcados en rojo para poder enviar la solicitud.</p>
        <button id="btn-confirmar" class="w-full py-3 rounded-xl bg-brand-400 hover:bg-brand-500 text-ink font-bold disabled:opacity-50 disabled:cursor-not-allowed">Revisar y enviar solicitud</button>
    </div>
</aside>

<script>
(() => {
    const IMG = <?= json_encode(product_upload_url()) ?>;
    const PLACEHOLDER = <?= json_encode(asset('img/producto.png')) ?>;
    const $ = (id) => document.getElementById(id);
    const MAX_DIAS = 60;
    let productos = [], cart = [], categoriaActiva = 0;
    const timers = new Map();
    let enVuelo = 0;

    const imgSrc = (f) => f ? IMG + encodeURIComponent(f) : PLACEHOLDER;
    const enCarrito = (id) => cart.find((c) => String(c.ProductoID) === String(id));
    const producto = (id) => productos.find((x) => String(x.ProductoID) === String(id));
    const redondea = (n) => Math.round(n * 1000) / 1000;
    const stockDe = (id) => { const p = producto(id), c = enCarrito(id); return Number(p ? p.Cantidad : (c ? c.Stock : 0)); };
    const tipoDe = (id) => (producto(id) || enCarrito(id) || {}).Tipo;
    const errorUI = (e) => U.toast(e.message || 'Ocurrió un error', 'error');

    // ---------- Control de cantidad (tarjeta y carrito) ----------
    function stepper(id, tipo, cantidad, stock, extra = '') {
        const u = U.unit(tipo);
        return `<div class="flex items-center gap-1 ${extra}">
            <button type="button" data-dec="${id}" class="w-9 h-9 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 font-bold" aria-label="Disminuir">−</button>
            <input data-qty="${id}" type="number" min="${u.entero ? 1 : 0.001}" max="${stock}" step="${u.entero ? 1 : 0.001}" inputmode="${u.entero ? 'numeric' : 'decimal'}" value="${Number(cantidad)}"
                   class="w-16 h-9 text-center rounded-lg border border-slate-200 text-sm font-semibold" aria-label="Cantidad">
            <button type="button" data-inc="${id}" class="w-9 h-9 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 font-bold" aria-label="Aumentar">+</button>
            <span class="text-xs text-slate-500 ml-1">${u.singular}</span>
        </div>`;
    }

    // ---------- Catálogo ----------
    function esqueleto() {
        $('productos').innerHTML = Array.from({ length: 8 }, () => `<div class="bg-white rounded-2xl border border-slate-200 overflow-hidden animate-pulse">
            <div class="aspect-[4/3] bg-slate-100"></div><div class="p-3 space-y-2"><div class="h-3 bg-slate-100 rounded w-4/5"></div><div class="h-3 bg-slate-100 rounded w-2/5"></div><div class="h-9 bg-slate-100 rounded"></div></div></div>`).join('');
    }

    async function cargarProductos() {
        const q = $('buscar').value.trim();
        const data = await U.api('requestInsumos/searchProducts.php', { query: q ? { query: q } : undefined });
        productos = data.productos.map((p, i) => ({ ...p, _pos: i }));
        if (categoriaActiva && !productos.some((p) => Number(p.CategoriaID) === categoriaActiva)) { categoriaActiva = 0; }
        renderCategorias();
        renderProductos();
    }

    function renderCategorias() {
        const cats = new Map();
        productos.forEach((p) => {
            const id = Number(p.CategoriaID), c = cats.get(id) || { n: p.nombreCategoria, count: 0 };
            c.count++; cats.set(id, c);
        });
        const chip = (id, label, count) => `<button data-cat="${id}" class="shrink-0 px-4 py-2 rounded-full text-sm font-medium border transition ${categoriaActiva === id ? 'bg-ink text-white border-ink' : 'bg-white border-slate-200 hover:border-slate-400'}">${U.esc(label)} <span class="opacity-60 text-xs">${count}</span></button>`;
        $('categorias').innerHTML = chip(0, 'Todos', productos.length) + [...cats].sort((a, b) => a[1].n.localeCompare(b[1].n)).map(([id, c]) => chip(id, c.n, c.count)).join('');
    }

    function listaFiltrada() {
        let lista = productos.filter((p) => (!categoriaActiva || Number(p.CategoriaID) === categoriaActiva) && (!$('solo-disp').checked || Number(p.Cantidad) > 0));
        const orden = $('orden').value;
        lista = [...lista].sort(orden === 'az' ? (a, b) => a.Nombre.localeCompare(b.Nombre)
            : orden === 'precio' ? (a, b) => Number(a.PrecioUnitario) - Number(b.PrecioUnitario)
            : (a, b) => a._pos - b._pos);
        return lista;
    }

    function tarjeta(p) {
        const stock = Number(p.Cantidad), agotado = stock <= 0, c = enCarrito(p.ProductoID), u = U.unit(p.Tipo);
        const bajo = !agotado && Number(p.StockMinimo) > 0 && stock <= Number(p.StockMinimo);
        const badge = agotado ? '<span class="bg-slate-800 text-white">Agotado</span>' : bajo ? '<span class="bg-amber-500 text-white">Últimas existencias</span>' : '';
        const accion = agotado
            ? '<button disabled class="w-full py-2.5 rounded-xl text-sm font-semibold bg-slate-200 text-slate-500 cursor-not-allowed">Sin stock</button>'
            : c ? stepper(p.ProductoID, p.Tipo, c.Cantidad, stock, 'justify-between')
                : `<button data-add="${p.ProductoID}" class="w-full py-2.5 rounded-xl text-sm font-semibold bg-brand-400 hover:bg-brand-500 text-ink"><i class="fa-solid fa-plus mr-1"></i>Agregar</button>`;
        return `<article class="bg-white rounded-2xl border ${c ? 'border-brand-400 ring-1 ring-brand-400' : 'border-slate-200'} overflow-hidden flex flex-col ${agotado ? 'opacity-70' : ''}">
            <div class="relative aspect-[4/3] bg-slate-100">
                <img src="${imgSrc(p.image)}" alt="" loading="lazy" onerror="this.onerror=null;this.src='${PLACEHOLDER}'" class="w-full h-full object-cover">
                <div class="absolute top-2 left-2 flex flex-col gap-1 text-[11px] font-semibold [&>span]:px-2 [&>span]:py-0.5 [&>span]:rounded-full">${badge}</div>
                ${c ? '<span class="absolute top-2 right-2 w-7 h-7 rounded-full bg-emerald-500 text-white flex items-center justify-center text-xs"><i class="fa-solid fa-check"></i></span>' : ''}
            </div>
            <div class="p-3 flex-1 flex flex-col gap-1">
                <h3 class="text-sm font-semibold leading-snug line-clamp-2 min-h-[2.5rem]">${U.esc(p.Nombre)}</h3>
                <p class="text-xs text-slate-500">${U.esc(p.nombreCategoria)}</p>
                <p class="text-xs ${agotado ? 'text-red-500' : 'text-slate-500'}">${agotado ? 'No disponible por ahora' : 'Disponible: ' + U.qty(stock, p.Tipo)}</p>
                <p class="text-base font-bold mt-auto pt-1">${U.money(p.PrecioUnitario)}<span class="text-xs font-normal text-slate-500"> / ${u.singular} + IVA</span></p>
                <div class="mt-2" data-accion-de="${p.ProductoID}">${accion}</div>
            </div>
        </article>`;
    }

    function renderProductos() {
        const lista = listaFiltrada();
        $('vacio').classList.toggle('hidden', lista.length > 0);
        $('resultados').textContent = lista.length ? `${lista.length} producto${lista.length === 1 ? '' : 's'}` : '';
        $('productos').innerHTML = lista.map(tarjeta).join('');
    }

    // ---------- Carrito ----------
    async function cargarCarrito() {
        cart = (await U.api('requestInsumos/getCart.php')).cart;
        renderCarrito();
        renderProductos();
    }

    function totales() {
        const sub = cart.reduce((s, c) => s + Number(c.PrecioUnitario) * Number(c.Cantidad), 0);
        return { sub, iva: sub * APP.taxRate, total: sub * (1 + APP.taxRate) };
    }

    function renderCarrito() {
        const n = cart.length, t = totales(), bloqueado = cart.some((c) => c.Problema);
        $('cart-count').textContent = n;
        $('cart-count').classList.toggle('hidden', n === 0);
        $('cart-count').classList.toggle('flex', n > 0);
        $('cart-bar').classList.toggle('hidden', n === 0);
        $('bar-n').textContent = n;
        $('bar-n-txt').textContent = n === 1 ? 'producto' : 'productos';
        $('bar-total').textContent = U.money(t.total);
        $('cart-clear').classList.toggle('hidden', n === 0);
        $('btn-confirmar').disabled = n === 0 || bloqueado;
        $('cart-aviso').classList.toggle('hidden', !bloqueado);

        $('cart-items').innerHTML = n === 0
            ? `<div class="text-center text-slate-500 py-16"><i class="fa-solid fa-cart-shopping text-4xl text-slate-300 mb-3"></i><p>Aún no agregas productos.</p>
               <button data-close-cart class="mt-3 text-sm font-semibold text-brand-600 hover:underline">Ver catálogo</button></div>`
            : cart.map((c) => {
                const u = U.unit(c.Tipo), stock = Number(c.Stock);
                return `<div class="flex gap-3 rounded-xl p-3 ${c.Problema ? 'bg-red-50 ring-1 ring-red-300' : 'bg-slate-50'}" data-line="${c.ProductoID}">
                    <img src="${imgSrc(c.Imagen)}" onerror="this.onerror=null;this.src='${PLACEHOLDER}'" class="w-16 h-16 rounded-lg object-cover bg-white" alt="">
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between gap-2">
                            <p class="text-sm font-semibold leading-snug">${U.esc(c.NombreProducto)}</p>
                            <button data-del="${c.ProductoID}" class="text-slate-400 hover:text-red-600" aria-label="Quitar"><i class="fa-solid fa-trash-can"></i></button>
                        </div>
                        <p class="text-xs text-slate-500">${U.money(c.PrecioUnitario)} / ${u.singular}${c.Problema ? '' : ' · máx. ' + U.qty(stock, c.Tipo)}</p>
                        ${c.Problema ? `<p class="text-xs font-semibold text-red-600 mt-0.5"><i class="fa-solid fa-triangle-exclamation mr-1"></i>${U.esc(c.Problema)}</p>` : ''}
                        <div class="mt-2 flex items-center justify-between gap-2">
                            ${stepper(c.ProductoID, c.Tipo, c.Cantidad, stock)}
                            <span class="text-sm font-bold">${U.money(Number(c.PrecioUnitario) * Number(c.Cantidad))}</span>
                        </div>
                    </div>
                </div>`;
            }).join('');

        $('t-iva-label').textContent = `IVA (${Math.round(APP.taxRate * 10000) / 100}%)`;
        $('t-sub').textContent = U.money(t.sub);
        $('t-iva').textContent = U.money(t.iva);
        $('t-total').textContent = U.money(t.total);
    }

    // Cambios inmediatos en pantalla y sincronización con el servidor en segundo plano
    function fijarCantidad(id, cantidad) {
        const tipo = tipoDe(id), u = U.unit(tipo), stock = stockDe(id);
        let nueva = redondea(Number(cantidad));
        if (!Number.isFinite(nueva) || nueva <= 0) { return quitar(id); }
        if (u.entero) { nueva = Math.round(nueva); }
        if (nueva > stock) { nueva = stock; U.toast(`Solo hay ${U.qty(stock, tipo)} disponibles`, 'error'); }
        if (nueva <= 0) { return; }

        const linea = enCarrito(id);
        if (linea) { linea.Cantidad = nueva; linea.Problema = null; }
        else {
            const p = producto(id);
            if (!p) { return; }
            cart.push({ ProductoID: p.ProductoID, NombreProducto: p.Nombre, Tipo: p.Tipo, Imagen: p.image, PrecioUnitario: p.PrecioUnitario, Cantidad: nueva, Stock: p.Cantidad, Problema: null });
        }
        refrescarLinea(id);
        programar(id, 'guardar');
    }

    function quitar(id) {
        cart = cart.filter((c) => String(c.ProductoID) !== String(id));
        refrescarLinea(id);
        programar(id, 'borrar');
    }

    function refrescarLinea(id) {
        renderCarrito();
        const caja = document.querySelector(`[data-accion-de="${id}"]`);
        const p = producto(id);
        if (caja && p) { renderProductos(); }
    }

    function programar(id, accion) {
        clearTimeout(timers.get(String(id)));
        timers.set(String(id), setTimeout(() => sincronizar(id, accion), 350));
    }

    async function sincronizar(id, accion) {
        timers.delete(String(id));
        enVuelo++;
        try {
            if (accion === 'borrar') {
                await U.api('requestInsumos/deleteToCart.php', { method: 'POST', body: { idProduct: id } }).catch(() => {});
            } else {
                const linea = enCarrito(id);
                if (linea) {
                    const data = await U.api('requestInsumos/saveToCart.php', { method: 'POST', body: { idProduct: id, cantidad: linea.Cantidad } });
                    if (timers.size === 0 && enVuelo === 1) { cart = data.cart; }
                }
            }
        } catch (err) {
            errorUI(err);
            timers.forEach(clearTimeout); timers.clear();
            enVuelo--;
            await cargarCarrito().catch(errorUI);
            return;
        }
        enVuelo--;
        if (timers.size === 0 && enVuelo === 0) { await cargarCarrito().catch(errorUI); }
    }

    const abrirCarrito = (open) => {
        $('cart-drawer').classList.toggle('translate-x-full', !open);
        $('cart-overlay').classList.toggle('hidden', !open);
        document.body.classList.toggle('overflow-hidden', open);
    };

    // ---------- Eventos ----------
    $('buscar').addEventListener('input', U.debounce(() => cargarProductos().catch(errorUI)));
    $('orden').addEventListener('change', renderProductos);
    $('solo-disp').addEventListener('change', renderProductos);
    $('limpiar-filtros').onclick = () => { $('buscar').value = ''; $('solo-disp').checked = false; categoriaActiva = 0; cargarProductos().catch(errorUI); };
    $('categorias').addEventListener('click', (e) => {
        const b = e.target.closest('[data-cat]');
        if (b) { categoriaActiva = Number(b.dataset.cat); renderCategorias(); renderProductos(); }
    });

    const manejarCantidad = (e) => {
        const add = e.target.closest('[data-add]'), inc = e.target.closest('[data-inc]'), dec = e.target.closest('[data-dec]'), del = e.target.closest('[data-del]');
        if (add) { const id = add.dataset.add; fijarCantidad(id, U.unit(tipoDe(id)).entero ? 1 : Math.min(1, stockDe(id))); }
        else if (inc || dec) {
            const id = (inc || dec).dataset.inc ?? (inc || dec).dataset.dec, c = enCarrito(id);
            if (c) { fijarCantidad(id, Number(c.Cantidad) + (inc ? 1 : -1) * U.unit(c.Tipo).paso); }
        } else if (del) { quitar(del.dataset.del); }
    };
    const manejarInput = (e) => {
        const input = e.target.closest('[data-qty]');
        if (input) { fijarCantidad(input.dataset.qty, input.value); }
    };
    $('productos').addEventListener('click', manejarCantidad);
    $('productos').addEventListener('change', manejarInput);
    $('cart-items').addEventListener('click', (e) => { if (e.target.closest('[data-close-cart]')) { abrirCarrito(false); } else { manejarCantidad(e); } });
    $('cart-items').addEventListener('change', manejarInput);

    $('btn-cart').onclick = $('cart-bar-btn').onclick = () => abrirCarrito(true);
    $('cart-close').onclick = $('cart-overlay').onclick = () => abrirCarrito(false);
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') { abrirCarrito(false); } });
    $('cart-clear').onclick = async () => {
        if (!(await U.confirm('¿Vaciar toda la solicitud?'))) { return; }
        const ids = cart.map((c) => c.ProductoID);
        cart = []; renderCarrito(); renderProductos();
        await Promise.all(ids.map((id) => U.api('requestInsumos/deleteToCart.php', { method: 'POST', body: { idProduct: id } }).catch(() => {})));
        await cargarCarrito().catch(errorUI);
    };

    document.querySelectorAll('[data-dias]').forEach((b) => b.addEventListener('click', () => {
        const d = new Date(); d.setDate(d.getDate() + Number(b.dataset.dias)); d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
        $('fecha').value = d.toISOString().slice(0, 10);
    }));
    $('sucursal').addEventListener('change', () => localStorage.setItem('kj_sucursal', $('sucursal').value));

    $('btn-confirmar').onclick = async () => {
        const idSucursal = $('sucursal').value, fechaEntrega = $('fecha').value;
        if (!idSucursal) { return U.toast('Selecciona la sucursal de destino', 'error'); }
        if (!fechaEntrega) { return U.toast('Selecciona la fecha de entrega', 'error'); }
        if (fechaEntrega < $('fecha').min || fechaEntrega > $('fecha').max) { return U.toast(`La fecha debe estar entre hoy y los próximos ${MAX_DIAS} días`, 'error'); }
        if (timers.size || enVuelo) { return U.toast('Guardando cambios, intenta de nuevo en un momento', 'info'); }

        const t = totales(), sucursal = $('sucursal').selectedOptions[0].textContent;
        const filas = cart.map((c) => `<li class="flex justify-between gap-3"><span class="truncate">${U.esc(c.NombreProducto)}</span><span class="whitespace-nowrap text-slate-500">${U.qty(c.Cantidad, c.Tipo)}</span></li>`).join('');
        const r = await Swal.fire({
            title: 'Confirma tu solicitud',
            html: `<div class="text-left text-sm space-y-3">
                <p><b>Destino:</b> ${U.esc(sucursal)}<br><b>Entrega:</b> ${U.esc(fechaEntrega.split('-').reverse().join('/'))}</p>
                <ul class="max-h-48 overflow-y-auto space-y-1 border-y border-slate-200 py-2">${filas}</ul>
                <p class="flex justify-between text-base"><b>Total c/IVA</b><b>${U.money(t.total)}</b></p>
            </div>`,
            showCancelButton: true, confirmButtonText: 'Enviar solicitud', cancelButtonText: 'Volver', confirmButtonColor: '#f59e0b',
        });
        if (!r.isConfirmed) { return; }

        $('btn-confirmar').disabled = true;
        try {
            const res = await U.api('requestInsumos/confirmRequest.php', { method: 'POST', body: { idSucursal, fechaEntrega } });
            abrirCarrito(false);
            const next = await Swal.fire({
                icon: 'success', title: 'Solicitud enviada', html: `Folio <b>${U.esc(res.comandaID)}</b><br><span class="text-sm text-slate-500">El almacén la preparará para tu entrega.</span>`,
                showCancelButton: true, confirmButtonText: 'Ver mis pedidos', cancelButtonText: 'Seguir pidiendo', confirmButtonColor: '#f59e0b',
            });
            if (next.isConfirmed) { window.location.href = U.url('solicitar/pedidos'); return; }
            await Promise.all([cargarCarrito(), cargarProductos()]);
        } catch (err) {
            Swal.fire({ icon: 'warning', title: 'No se pudo enviar', text: err.message, confirmButtonColor: '#f59e0b' });
            await Promise.all([cargarCarrito(), cargarProductos()]).catch(() => {});
        }
    };

    // ---------- Inicio ----------
    const hoy = new Date(); hoy.setMinutes(hoy.getMinutes() - hoy.getTimezoneOffset());
    const max = new Date(hoy); max.setDate(max.getDate() + MAX_DIAS);
    $('fecha').min = $('fecha').value = hoy.toISOString().slice(0, 10);
    $('fecha').max = max.toISOString().slice(0, 10);
    U.api('solicitar/sucursales.php').then((r) => {
        $('sucursal').innerHTML = '<option value="">Selecciona…</option>' + r.sucursales.map((s) => `<option value="${s.SucursalID}">${U.esc(s.nombre)}</option>`).join('');
        const guardada = localStorage.getItem('kj_sucursal');
        if (r.sucursales.length === 1) { $('sucursal').selectedIndex = 1; }
        else if (guardada && r.sucursales.some((s) => String(s.SucursalID) === guardada)) { $('sucursal').value = guardada; }
    }).catch(errorUI);
    esqueleto();
    Promise.all([cargarProductos(), cargarCarrito()]).catch(errorUI);
})();
</script>
