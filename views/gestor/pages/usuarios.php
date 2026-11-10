<?php $minPassword = max(1, (int)config('app.security.password_min_length', 8)); ?>
<div class="space-y-4">
    <div id="u-stats" class="grid grid-cols-2 lg:grid-cols-4 gap-3"></div>

    <div class="bg-white rounded-2xl border border-slate-200 p-4 flex flex-col lg:flex-row gap-3">
        <div class="relative flex-1">
            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input id="u-buscar" type="search" placeholder="Buscar nombre, usuario o correo…" autocomplete="off" class="w-full pl-11 pr-4 py-2.5 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-400">
        </div>
        <select id="u-rol" class="rounded-lg border border-slate-200 px-3 py-2.5 text-sm"><option value="">Todos los roles</option><option>Administrador</option><option>Logistica</option><option>Supervisor</option></select>
        <select id="u-estado" class="rounded-lg border border-slate-200 px-3 py-2.5 text-sm"><option value="">Todos los estados</option><option>activo</option><option>inactivo</option><option>suspendido</option></select>
        <button id="u-nuevo" class="px-4 py-2.5 rounded-lg bg-brand-400 hover:bg-brand-500 text-ink font-semibold whitespace-nowrap"><i class="fa-solid fa-user-plus mr-1"></i> Nuevo usuario</button>
    </div>

    <div id="u-lista" class="grid grid-cols-1 xl:grid-cols-2 gap-3" aria-live="polite"></div>
    <div id="u-vacio" class="hidden text-center text-slate-500 py-16"><i class="fa-solid fa-users-slash text-4xl text-slate-300 mb-3"></i><p>No hay usuarios con estos filtros.</p></div>
</div>

<div id="u-modal" data-modal class="fixed inset-0 z-50 bg-black/50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-xl max-h-[92vh] flex flex-col shadow-2xl">
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between"><h2 id="u-titulo" class="font-bold text-lg">Usuario</h2><button type="button" data-close class="w-9 h-9 rounded-lg hover:bg-slate-100" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button></div>
        <form id="u-form" class="flex flex-col min-h-0">
            <input type="hidden" name="id">
            <div class="p-5 overflow-y-auto grid sm:grid-cols-2 gap-4 text-xs font-semibold text-slate-600">
                <label>Nombre *<input name="nombre" required maxlength="40" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,40}" title="Solo letras, 3 a 40 caracteres" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-normal"></label>
                <label>Usuario *<input name="username" required maxlength="20" pattern="[a-zA-Z0-9]{4,20}" title="Letras y números, 4 a 20 caracteres" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-normal"></label>
                <label>Correo<input name="email" type="email" maxlength="150" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-normal"></label>
                <label>Teléfono<input name="telefono" maxlength="30" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-normal"></label>
                <label>Rol<select name="rol" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-normal"><option>Administrador</option><option>Logistica</option><option>Supervisor</option></select>
                    <span id="u-rol-ayuda" class="block mt-1 font-normal text-slate-500"></span></label>
                <label>Estado<select name="estado" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-normal"><option>activo</option><option>inactivo</option><option>suspendido</option></select></label>
                <label id="u-password-wrap" class="sm:col-span-2">Contraseña inicial * (mínimo <?= $minPassword ?> caracteres)
                    <input name="password" type="password" minlength="<?= $minPassword ?>" maxlength="72" autocomplete="new-password" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-normal"></label>
            </div>
            <div class="px-5 py-4 border-t border-slate-200 flex justify-end gap-2"><button type="button" data-close class="px-4 py-2.5 rounded-lg border border-slate-200 text-sm font-medium">Cancelar</button><button class="px-5 py-2.5 rounded-lg bg-brand-400 hover:bg-brand-500 text-ink font-semibold">Guardar</button></div>
        </form>
    </div>
</div>

<div id="u-actividad" data-modal class="fixed inset-0 z-50 bg-black/50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-xl max-h-[90vh] flex flex-col shadow-2xl">
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between"><h2 class="font-bold text-lg">Actividad</h2><button data-close class="w-9 h-9 rounded-lg hover:bg-slate-100" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button></div>
        <div id="u-actividad-contenido" class="p-5 overflow-y-auto text-sm"></div>
    </div>
</div>

<div id="u-reset" data-modal class="fixed inset-0 z-50 bg-black/50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl">
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between"><h2 class="font-bold text-lg">Restablecer contraseña</h2><button data-close class="w-9 h-9 rounded-lg hover:bg-slate-100" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button></div>
        <form id="u-reset-form" class="p-5 space-y-4"><input type="hidden" name="id">
            <p id="u-reset-nombre" class="text-sm font-semibold"></p>
            <label class="block text-xs font-semibold text-slate-600">Contraseña nueva (mínimo <?= $minPassword ?> caracteres)
                <input type="password" name="password" required minlength="<?= $minPassword ?>" maxlength="72" autocomplete="new-password" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-normal"></label>
            <div class="flex justify-end gap-2"><button type="button" data-close class="px-4 py-2.5 rounded-lg border border-slate-200 text-sm font-medium">Cancelar</button><button class="px-5 py-2.5 rounded-lg bg-brand-400 hover:bg-brand-500 text-ink font-semibold">Restablecer</button></div>
        </form>
    </div>
</div>

<script>
(function () {
    const api = 'gestor/usuarios.php', $ = (id) => document.getElementById(id), form = $('u-form');
    let users = [], seq = 0;
    const YO = <?= json_encode((int) ($_SESSION['id'] ?? 0)) ?>;
    const rolColor = { Administrador: 'bg-violet-100 text-violet-800', Logistica: 'bg-sky-100 text-sky-800', Supervisor: 'bg-slate-100 text-slate-700' };
    const estColor = { activo: 'bg-emerald-100 text-emerald-800', inactivo: 'bg-slate-200 text-slate-600', suspendido: 'bg-red-100 text-red-700' };
    const rolAyuda = { Administrador: 'Acceso total al Gestor.', Logistica: 'Pedidos, productos e inventario.', Supervisor: 'Solo solicita insumos.' };
    const iniciales = (n) => String(n || '?').trim().split(/\s+/).slice(0, 2).map((w) => w[0]).join('').toUpperCase();
    const cuando = (s) => s ? new Date(String(s).replace(' ', 'T')).toLocaleString('es-MX', { dateStyle: 'medium', timeStyle: 'short' }) : null;
    const btn = 'px-2.5 py-1.5 rounded-lg text-xs font-medium border border-slate-200 hover:bg-slate-50';

    function stats() {
        const n = (f) => users.filter(f).length;
        $('u-stats').innerHTML = [['Usuarios', users.length, ''], ['Activos', n((u) => u.estado === 'activo'), 'text-emerald-600'], ['Administradores', n((u) => u.Rol === 'Administrador'), 'text-violet-600'], ['Inactivos / suspendidos', n((u) => u.estado !== 'activo'), 'text-amber-600']]
            .map(([t, v, c]) => `<div class="bg-white rounded-2xl border border-slate-200 p-4"><p class="text-xs text-slate-500">${t}</p><p class="text-2xl font-bold ${c}">${v}</p></div>`).join('');
    }

    async function load() {
        const n = ++seq;
        try {
            const data = await U.api(api, { query: { q: $('u-buscar').value, rol: $('u-rol').value, estado: $('u-estado').value } });
            if (n !== seq) return;
            users = data.usuarios; stats();
            $('u-vacio').classList.toggle('hidden', users.length > 0);
            $('u-lista').innerHTML = users.map((u) => `
                <div class="bg-white rounded-2xl border border-slate-200 p-4 ${u.estado !== 'activo' ? 'opacity-75' : ''}">
                    <div class="flex gap-3">
                        <span class="w-11 h-11 shrink-0 rounded-full bg-ink text-brand-400 font-bold flex items-center justify-center">${U.esc(iniciales(u.Nombre))}</span>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2"><strong class="truncate">${U.esc(u.Nombre)}</strong>${Number(u.UsuarioID) === YO ? '<span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-brand-100 text-ink">TÚ</span>' : ''}</div>
                            <p class="text-sm text-slate-500 truncate">@${U.esc(u.Username)} · ${U.esc(u.email || 'Sin correo')}</p>
                            <div class="flex flex-wrap gap-1.5 mt-1.5"><span class="px-2 py-0.5 rounded-full text-[11px] font-semibold ${rolColor[u.Rol] || 'bg-slate-100'}">${U.esc(u.Rol)}</span><span class="px-2 py-0.5 rounded-full text-[11px] font-semibold ${estColor[u.estado] || 'bg-slate-100'}">${U.esc(u.estado)}</span></div>
                            <p class="text-xs text-slate-400 mt-1.5"><i class="fa-regular fa-clock mr-1"></i>${cuando(u.ultimo_acceso) ? 'Último acceso: ' + cuando(u.ultimo_acceso) : 'Sin acceso registrado'}</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2 mt-3 pt-3 border-t border-slate-100">
                        <button data-action="editar" data-id="${u.UsuarioID}" class="${btn}"><i class="fa-solid fa-pen mr-1"></i>Editar</button>
                        <button data-action="password" data-id="${u.UsuarioID}" class="${btn}"><i class="fa-solid fa-key mr-1"></i>Contraseña</button>
                        <button data-action="actividad" data-id="${u.UsuarioID}" class="${btn}"><i class="fa-solid fa-clock-rotate-left mr-1"></i>Actividad</button>
                        <button data-action="estado" data-id="${u.UsuarioID}" class="${btn}">${u.estado === 'activo' ? 'Desactivar' : 'Activar'}</button>
                        <button data-action="eliminar" data-id="${u.UsuarioID}" class="${btn} text-red-700 border-red-200 hover:bg-red-50 ml-auto"><i class="fa-solid fa-trash-can"></i></button>
                    </div>
                </div>`).join('');
        } catch (e) { if (n === seq) U.toast(e.message, 'error'); }
    }

    const ayuda = () => { $('u-rol-ayuda').textContent = rolAyuda[form.elements.rol.value] || ''; };
    form.elements.rol.addEventListener('change', ayuda);

    $('u-nuevo').onclick = () => { form.reset(); form.elements.id.value = ''; form.elements.password.required = true; $('u-password-wrap').classList.remove('hidden'); $('u-titulo').textContent = 'Nuevo usuario'; ayuda(); U.modal('u-modal'); };
    form.onsubmit = async (e) => {
        e.preventDefault();
        const body = Object.fromEntries(new FormData(form)); const id = body.id; if (id) delete body.password;
        try { await U.api(api, { method: id ? 'PUT' : 'POST', body }); U.modal('u-modal', false); U.toast('Usuario guardado.', 'success'); load(); } catch (err) { U.toast(err.message, 'error'); }
    };
    $('u-reset-form').onsubmit = async (e) => {
        e.preventDefault();
        const body = Object.fromEntries(new FormData(e.target)); body.action = 'password';
        try { await U.api(api, { method: 'PUT', body }); e.target.reset(); U.modal('u-reset', false); U.toast('Contraseña restablecida.', 'success'); } catch (err) { U.toast(err.message, 'error'); }
    };

    $('u-lista').onclick = async (e) => {
        const b = e.target.closest('[data-action]'); if (!b) return;
        const user = users.find((x) => String(x.UsuarioID) === b.dataset.id); if (!user) return;
        const id = user.UsuarioID, a = b.dataset.action;
        try {
            if (a === 'editar') {
                form.reset();
                for (const [field, key] of Object.entries({ id: 'UsuarioID', nombre: 'Nombre', username: 'Username', email: 'email', rol: 'Rol', estado: 'estado', telefono: 'telefono' })) form.elements[field].value = user[key] ?? '';
                form.elements.password.required = false; $('u-password-wrap').classList.add('hidden'); $('u-titulo').textContent = 'Editar usuario'; ayuda(); U.modal('u-modal');
            } else if (a === 'password') {
                $('u-reset-form').reset(); $('u-reset-form').elements.id.value = id; $('u-reset-nombre').textContent = `${user.Nombre} (@${user.Username})`; U.modal('u-reset');
            } else if (a === 'actividad') {
                const data = await U.api(api, { query: { action: 'actividad', id } });
                const mov = data.movimientos.map((m) => `<li class="flex justify-between gap-3 py-2 border-b border-slate-100 last:border-0"><span>${m.TipoMovimiento === 'Entrada' ? '<i class="fa-solid fa-arrow-down text-emerald-600 mr-1"></i>' : '<i class="fa-solid fa-arrow-up text-amber-600 mr-1"></i>'}${U.esc(m.TipoMovimiento)}${m.ComandaID ? ' · ' + U.esc(m.ComandaID) : ''}<span class="block text-xs text-slate-400">${cuando(m.FechaMovimiento) || ''}</span></span><span class="text-right text-slate-500">${U.esc(m.Cantidad)}<span class="block text-xs">${U.esc(m.Status || '')}</span></span></li>`).join('');
                $('u-actividad-contenido').innerHTML = `<div class="rounded-xl bg-slate-50 p-3 mb-4"><p class="font-semibold">${U.esc(data.usuario.Nombre)}</p><p class="text-slate-500">Último acceso: ${cuando(data.usuario.ultimo_acceso) || 'Sin registro'} · IP: ${U.esc(data.usuario.ip_ultimo_acceso || 'Sin registro')}</p></div><h3 class="font-semibold mb-1">Últimos movimientos de inventario</h3>${mov ? `<ul>${mov}</ul>` : '<p class="text-slate-500">Sin movimientos registrados.</p>'}`;
                U.modal('u-actividad');
            } else if (a === 'estado' && await U.confirm(`¿${user.estado === 'activo' ? 'Desactivar' : 'Activar'} a ${user.Nombre}?`)) {
                await U.api(api, { method: 'PUT', body: { id, action: 'estado', estado: user.estado === 'activo' ? 'inactivo' : 'activo' } }); U.toast('Estado actualizado.', 'success'); load();
            } else if (a === 'eliminar' && await U.confirm(`¿Eliminar a ${user.Nombre}? Esta acción no se puede deshacer.`)) {
                await U.api(api, { method: 'DELETE', body: { id } }); U.toast('Usuario eliminado.', 'success'); load();
            }
        } catch (err) { U.toast(err.message, 'error'); }
    };
    $('u-buscar').oninput = U.debounce(load); $('u-rol').onchange = load; $('u-estado').onchange = load; load();
})();
</script>
