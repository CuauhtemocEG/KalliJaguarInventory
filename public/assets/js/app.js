/**
 * Utilidades compartidas de las áreas /gestor y /solicitar.
 * Requiere window.APP = { base, taxRate } definido por el layout.
 */
(function () {
    'use strict';

    const money = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' });

    const U = {
        url: (path) => APP.base.replace(/\/$/, '') + '/' + String(path).replace(/^\//, ''),
        money: (n) => money.format(Number(n) || 0),
        num: (n, d = 2) => Number(n || 0).toLocaleString('es-MX', { maximumFractionDigits: d }),
        esc: (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c])),
        /** Definición de la unidad de un producto (Unidad, Pesable, Litro, Manojo). */
        unit: (tipo) => (APP.unidades && APP.unidades[tipo]) || { tipo: 'Unidad', singular: 'pza', plural: 'pzas', entero: true, paso: 1 },
        /** Cantidad con unidad: "2.5 kg", "3 manojos". */
        qty(n, tipo) {
            const u = U.unit(tipo), v = Number(n || 0);
            return U.num(v, u.entero ? 0 : 3) + ' ' + (Math.abs(v - 1) < 0.0005 ? u.singular : u.plural);
        },
        debounce(fn, ms = 300) {
            let t;
            return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); };
        },

        /** fetch JSON contra /api/...; lanza Error con el mensaje del servidor si falla. */
        async api(path, { method = 'GET', body, query } = {}) {
            let url = U.url('api/' + path);
            if (query) { url += (url.includes('?') ? '&' : '?') + new URLSearchParams(query).toString(); }
            const init = { method, headers: { Accept: 'application/json' }, credentials: 'same-origin' };
            if (body !== undefined) {
                init.headers['Content-Type'] = 'application/json';
                init.body = JSON.stringify(body);
            }
            const res = await fetch(url, init);
            let data = null;
            try { data = await res.json(); } catch (e) { /* respuesta no JSON */ }
            if (res.status === 401) { window.location.href = U.url('index.php?page=login'); }
            const failed = !res.ok || !data || data.success === false || data.status === 'error';
            if (failed) { throw new Error((data && data.message) || 'Error ' + res.status); }
            return data;
        },

        toast(message, type = 'success') {
            const colors = { success: 'bg-emerald-600', error: 'bg-red-600', info: 'bg-slate-800' };
            const el = document.createElement('div');
            el.className = 'fixed z-[100] bottom-5 right-5 max-w-sm px-4 py-3 rounded-xl text-white text-sm shadow-lg ' + (colors[type] || colors.info);
            el.textContent = message;
            document.body.appendChild(el);
            setTimeout(() => el.remove(), 3500);
        },

        confirm(message) {
            if (window.Swal) {
                return Swal.fire({ title: message, icon: 'question', showCancelButton: true, confirmButtonText: 'Sí', cancelButtonText: 'Cancelar', confirmButtonColor: '#f59e0b' })
                    .then((r) => r.isConfirmed);
            }
            return Promise.resolve(window.confirm(message));
        },

        /** Muestra el PDF de una comanda en un visor dentro de la app (sin pestaña nueva). */
        async pdf(comandaId, opts = {}) {
            let box = document.getElementById('pdf-viewer');
            if (!box) {
                box = document.createElement('div');
                box.id = 'pdf-viewer';
                box.className = 'fixed inset-0 z-[90] bg-black/60 hidden items-center justify-center p-2 sm:p-6';
                box.innerHTML = `<div class="bg-white rounded-2xl w-full max-w-5xl h-full flex flex-col overflow-hidden shadow-2xl">
                    <div class="flex items-center gap-2 px-4 py-3 border-b border-slate-200">
                        <i class="fa-solid fa-file-pdf text-red-500"></i>
                        <h3 class="font-bold text-sm sm:text-base truncate flex-1" data-pdf-title></h3>
                        <a data-pdf-download class="px-3 py-2 rounded-lg border border-slate-200 text-sm font-medium hover:bg-slate-50"><i class="fa-solid fa-download mr-1"></i><span class="hidden sm:inline">Descargar</span></a>
                        <button type="button" data-pdf-print class="px-3 py-2 rounded-lg border border-slate-200 text-sm font-medium hover:bg-slate-50"><i class="fa-solid fa-print mr-1"></i><span class="hidden sm:inline">Imprimir</span></button>
                        <button type="button" data-pdf-close class="w-9 h-9 rounded-lg hover:bg-slate-100" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                    <div class="flex-1 relative bg-slate-100">
                        <div data-pdf-loading class="absolute inset-0 flex items-center justify-center text-slate-500"><i class="fa-solid fa-spinner fa-spin mr-2"></i>Generando PDF…</div>
                        <iframe data-pdf-frame title="Comanda" class="absolute inset-0 w-full h-full bg-white"></iframe>
                    </div>
                </div>`;
                document.body.appendChild(box);
                const cerrar = () => {
                    box.classList.add('hidden'); box.classList.remove('flex');
                    const f = box.querySelector('[data-pdf-frame]');
                    f.removeAttribute('src');
                    if (box.dataset.blob) { URL.revokeObjectURL(box.dataset.blob); delete box.dataset.blob; }
                };
                box.querySelector('[data-pdf-close]').onclick = cerrar;
                box.addEventListener('click', (e) => { if (e.target === box) { cerrar(); } });
                document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && !box.classList.contains('hidden')) { cerrar(); } });
                box.querySelector('[data-pdf-print]').onclick = () => {
                    try { const w = box.querySelector('[data-pdf-frame]').contentWindow; w.focus(); w.print(); } catch (e) { U.toast('Usa Descargar para imprimir el PDF', 'info'); }
                };
                box._cerrar = cerrar;
            }
            const frame = box.querySelector('[data-pdf-frame]'), loading = box.querySelector('[data-pdf-loading]');
            const url = opts.url ? U.url(opts.url) : U.url('api/documents/download.php?ComandaID=' + encodeURIComponent(comandaId));
            box.querySelector('[data-pdf-title]').textContent = opts.title || 'Comanda ' + comandaId;
            const dl = box.querySelector('[data-pdf-download]');
            dl.removeAttribute('download'); dl.href = opts.url ? '#' : url + '&download=1';
            loading.classList.remove('hidden');
            frame.removeAttribute('src');
            box.classList.remove('hidden'); box.classList.add('flex');
            try {
                const res = await fetch(url, { credentials: 'same-origin', ...(opts.init || {}) });
                if (!res.ok || !(res.headers.get('Content-Type') || '').includes('pdf')) {
                    const txt = opts.url ? (await res.text().catch(() => '')).replace(/<[^>]*>/g, ' ').trim().slice(0, 160) : '';
                    throw new Error(txt || (res.status === 404 ? 'No se encontró el pedido' : res.status === 403 ? 'No tienes permiso para ver este documento' : 'No se pudo generar el PDF'));
                }
                const blobUrl = URL.createObjectURL(await res.blob());
                box.dataset.blob = blobUrl;
                if (opts.url) { dl.href = blobUrl; dl.download = opts.filename || 'reporte.pdf'; }
                frame.src = blobUrl;
                loading.classList.add('hidden');
            } catch (e) {
                box._cerrar();
                U.toast(e.message, 'error');
            }
        },

        /** Modal genérico: abre #id; cierra con [data-close] o clic en el fondo. */
        modal(id, open = true) {
            const el = document.getElementById(id);
            if (!el) { return; }
            el.classList.toggle('hidden', !open);
            el.classList.toggle('flex', open);
            const abiertos = document.querySelectorAll('[data-modal].flex').length > 0;
            document.body.classList.toggle('overflow-hidden', abiertos);
            const panel = el.firstElementChild;
            if (open && panel) { panel.animate([{ opacity: 0, transform: 'translateY(12px) scale(.98)' }, { opacity: 1, transform: 'none' }], { duration: 160, easing: 'ease-out' }); }
        },
    };

    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape' || document.getElementById('pdf-viewer')?.classList.contains('flex')) { return; }
        document.querySelectorAll('[data-modal].flex').forEach((m) => U.modal(m.id, false));
    });

    document.addEventListener('click', (e) => {
        const close = e.target.closest('[data-close]');
        if (close) { U.modal(close.closest('[data-modal]').id, false); }
        else if (e.target.matches('[data-modal]')) { U.modal(e.target.id, false); }
    });

    window.U = U;
})();
