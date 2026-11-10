<?php
$ivaActual = (float) config('app.tax_rate') * 100;
$inp = 'block w-full mt-1 rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-brand-400 focus:ring-2 focus:ring-brand-100 outline-none';
$tarjeta = 'bg-white rounded-2xl border border-slate-200 shadow-sm';
?>
<div class="max-w-5xl space-y-6">
  <div>
    <h2 class="text-xl font-extrabold">Configuración</h2>
    <p class="text-sm text-slate-500">Impuestos, disponibilidad del sistema e información de la aplicación.</p>
  </div>

  <div class="grid lg:grid-cols-3 gap-6 items-start">
    <section class="<?= $tarjeta ?> p-5 space-y-4 lg:col-span-1">
      <div class="flex items-center gap-3">
        <span class="w-11 h-11 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center"><i class="fa-solid fa-percent"></i></span>
        <div><h3 class="font-bold">IVA</h3><p class="text-xs text-slate-500">Aplica a nuevas solicitudes</p></div>
      </div>
      <div class="rounded-xl bg-slate-50 p-4 text-center">
        <p class="text-xs text-slate-500">Tasa vigente</p>
        <p class="text-4xl font-extrabold text-ink"><span id="c-iva-actual"><?= e((string) round($ivaActual, 4)) ?></span><span class="text-xl text-slate-400">%</span></p>
      </div>
      <form id="c-iva" class="space-y-3">
        <label class="block text-xs font-semibold text-slate-600">Nuevo porcentaje
          <input id="c-porcentaje" type="number" min="0" max="100" step="any" required value="<?= e((string) $ivaActual) ?>" class="<?= $inp ?>">
        </label>
        <p class="text-xs text-slate-500 rounded-lg bg-amber-50 border border-amber-100 px-3 py-2" id="c-iva-demo"></p>
        <button class="w-full bg-brand-400 hover:bg-brand-500 text-ink font-semibold rounded-xl px-4 py-2.5 transition">Guardar IVA</button>
        <p class="text-[11px] text-slate-400">Los pedidos ya generados conservan su importe; el cambio queda en bitácora.</p>
      </form>
    </section>

    <section class="<?= $tarjeta ?> p-5 lg:col-span-2">
      <div class="flex items-center justify-between gap-3 mb-4">
        <div class="flex items-center gap-3">
          <span class="w-11 h-11 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center"><i class="fa-solid fa-screwdriver-wrench"></i></span>
          <div><h3 class="font-bold">Modo mantenimiento</h3><p class="text-xs text-slate-500">Bloquea las solicitudes por día, fecha u horario</p></div>
        </div>
        <span id="c-estado" class="text-xs font-semibold rounded-full px-3 py-1 bg-slate-100 text-slate-500">Cargando…</span>
      </div>
      <p id="c-mantenimiento-error" class="text-red-700 bg-red-50 rounded-lg px-3 py-2 text-sm hidden mb-3" role="alert"></p>

      <form id="c-mantenimiento" class="space-y-5">
        <label class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 p-4 cursor-pointer">
          <span><b class="block text-sm">Bloquear todas las solicitudes ahora</b><span class="text-xs text-slate-500">Los usuarios verán el mensaje de abajo en lugar del catálogo.</span></span>
          <span class="relative shrink-0">
            <input type="checkbox" name="MAINTENANCE_MODE_ENABLED" class="peer sr-only">
            <span class="block w-12 h-7 rounded-full bg-slate-300 peer-checked:bg-red-500 transition"></span>
            <span class="absolute top-1 left-1 w-5 h-5 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
          </span>
        </label>

        <fieldset>
          <legend class="text-xs font-semibold text-slate-600 mb-2">Días sin servicio (cada semana)</legend>
          <div class="flex flex-wrap gap-2">
            <?php foreach (['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'] as $day => $name): ?>
              <label class="cursor-pointer">
                <input type="checkbox" name="dia" value="<?= $day ?>" class="peer sr-only">
                <span class="block rounded-full border border-slate-200 px-3.5 py-1.5 text-sm text-slate-600 peer-checked:bg-ink peer-checked:text-white peer-checked:border-ink peer-disabled:opacity-40 transition"><?= e($name) ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </fieldset>

        <div class="grid sm:grid-cols-2 gap-4">
          <label class="block text-xs font-semibold text-slate-600">Bloqueo diario desde
            <input type="time" name="BLOCKED_TIME_START" class="<?= $inp ?>"></label>
          <label class="block text-xs font-semibold text-slate-600">hasta
            <input type="time" name="BLOCKED_TIME_END" class="<?= $inp ?>"></label>
        </div>

        <label class="block text-xs font-semibold text-slate-600">Fechas bloqueadas <span class="font-normal text-slate-400">(una por línea, AAAA-MM-DD)</span>
          <textarea name="BLOCKED_DATES" rows="3" placeholder="2026-12-25&#10;2027-01-01" class="<?= $inp ?> font-mono"></textarea></label>

        <div class="grid md:grid-cols-2 gap-4">
          <div class="space-y-3">
            <label class="block text-xs font-semibold text-slate-600">Mensaje principal
              <textarea name="MAINTENANCE_MESSAGE" rows="2" class="<?= $inp ?>"></textarea></label>
            <label class="block text-xs font-semibold text-slate-600">Mensaje secundario
              <textarea name="MAINTENANCE_SUBMESSAGE" rows="2" class="<?= $inp ?>"></textarea></label>
          </div>
          <div>
            <p class="text-xs font-semibold text-slate-600 mb-1">Vista previa</p>
            <div class="rounded-xl bg-slate-900 text-white p-5 text-center min-h-[8rem] flex flex-col items-center justify-center">
              <i class="fa-solid fa-screwdriver-wrench text-brand-400 text-2xl mb-2"></i>
              <b id="c-prev-1" class="block"></b><span id="c-prev-2" class="block text-sm text-slate-300 mt-1"></span>
            </div>
          </div>
        </div>

        <div class="flex justify-end">
          <button id="c-guardar" disabled class="bg-brand-400 hover:bg-brand-500 text-ink font-semibold rounded-xl px-5 py-2.5 disabled:opacity-50 transition">Guardar mantenimiento</button>
        </div>
      </form>
    </section>
  </div>

  <section class="<?= $tarjeta ?> p-5">
    <div class="flex items-center gap-3 mb-4">
      <span class="w-11 h-11 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center"><i class="fa-solid fa-circle-info"></i></span>
      <div><h3 class="font-bold">Datos de la aplicación</h3><p class="text-xs text-slate-500">Solo lectura; se definen en el archivo .env</p></div>
    </div>
    <dl class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3 text-sm">
      <?php foreach (['Empresa' => 'company', 'Aplicación' => 'name', 'Nombre corto' => 'short_name', 'Versión' => 'version', 'Desarrollador' => 'developer', 'Sitio' => 'url', 'Zona horaria' => 'timezone'] as $label => $key): ?>
        <div class="rounded-xl bg-slate-50 px-4 py-3"><dt class="text-xs text-slate-500"><?= e($label) ?></dt><dd class="font-semibold break-words"><?= e((string) (config('app.' . $key) ?: '—')) ?></dd></div>
      <?php endforeach; ?>
      <div class="rounded-xl bg-slate-50 px-4 py-3"><dt class="text-xs text-slate-500">Hora del servidor</dt><dd class="font-semibold"><?= e(date('d/m/Y H:i')) ?></dd></div>
    </dl>
  </section>
</div>
<script>
(function () {
    const $ = (id) => document.getElementById(id), form = $('c-mantenimiento'), path = 'maintenance-config/index.php';
    let configs = {};

    const demo = () => {
        const p = parseFloat($('c-porcentaje').value);
        $('c-iva-demo').innerHTML = Number.isFinite(p) && p >= 0 && p <= 100
            ? `Ejemplo: <b>${U.money(100)}</b> + IVA = <b>${U.money(100 * (1 + p / 100))}</b>` : 'Ingresa un porcentaje entre 0 y 100.';
    };
    $('c-porcentaje').addEventListener('input', demo); demo();

    $('c-iva').onsubmit = async (e) => {
        e.preventDefault();
        const p = parseFloat($('c-porcentaje').value);
        if (!(p >= 0 && p <= 100)) { U.toast('El IVA debe estar entre 0 y 100.', 'error'); return; }
        if (!(await U.confirm(`¿Cambiar el IVA a ${p}%? Aplicará a las nuevas solicitudes.`))) { return; }
        const button = e.target.querySelector('button'); button.disabled = true;
        try {
            const result = await U.api('gestor/configuracion.php', { method: 'PUT', body: { porcentaje: $('c-porcentaje').value } });
            const nuevo = Number((result.tax_rate * 100).toFixed(8));
            $('c-porcentaje').value = nuevo; $('c-iva-actual').textContent = nuevo; demo();
            U.toast(result.message);
        } catch (err) { U.toast(err.message, 'error'); } finally { button.disabled = false; }
    };

    function error(message) { $('c-mantenimiento-error').textContent = message; $('c-mantenimiento-error').classList.remove('hidden'); }

    function pinta() {
        const on = form.elements.MAINTENANCE_MODE_ENABLED.checked;
        const reglas = form.querySelectorAll('[name="dia"]:checked').length || form.elements.BLOCKED_DATES.value.trim()
            || (form.elements.BLOCKED_TIME_START.value && form.elements.BLOCKED_TIME_END.value);
        const est = $('c-estado');
        est.textContent = on ? 'Bloqueo activo' : (reglas ? 'Con restricciones programadas' : 'Operando normal');
        est.className = 'text-xs font-semibold rounded-full px-3 py-1 ' + (on ? 'bg-red-100 text-red-700' : reglas ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700');
        $('c-prev-1').textContent = form.elements.MAINTENANCE_MESSAGE.value || 'Sistema en mantenimiento';
        $('c-prev-2').textContent = form.elements.MAINTENANCE_SUBMESSAGE.value;
    }
    form.addEventListener('input', pinta); form.addEventListener('change', pinta);

    async function load() {
        try {
            const data = await U.api(path);
            configs = Object.fromEntries(data.data.configuraciones.map((c) => [c.key, c.value]));
            for (const key of ['MAINTENANCE_MODE_ENABLED', 'BLOCKED_DATES', 'BLOCKED_TIME_START', 'BLOCKED_TIME_END', 'MAINTENANCE_MESSAGE', 'MAINTENANCE_SUBMESSAGE']) {
                const field = form.elements[key];
                if (!(key in configs)) { field.disabled = true; continue; }
                if (field.type === 'checkbox') { field.checked = !!configs[key]; }
                else { field.value = Array.isArray(configs[key]) ? configs[key].join('\n') : (configs[key] ?? ''); }
            }
            const days = Array.isArray(configs.BLOCKED_WEEKDAYS) ? configs.BLOCKED_WEEKDAYS.map(Number) : [];
            form.querySelectorAll('[name="dia"]').forEach((el) => { el.disabled = !('BLOCKED_WEEKDAYS' in configs); el.checked = days.includes(Number(el.value)); });
            $('c-guardar').disabled = false; pinta();
        } catch (err) { error('No se pudo cargar mantenimiento: ' + err.message); $('c-estado').textContent = 'No disponible'; }
    }

    form.onsubmit = async (e) => {
        e.preventDefault();
        const dates = form.elements.BLOCKED_DATES.value.split(/\r?\n/).map((x) => x.trim()).filter(Boolean);
        if (dates.some((x) => !/^\d{4}-\d{2}-\d{2}$/.test(x) || Number.isNaN(Date.parse(x + 'T00:00:00Z')) || new Date(x + 'T00:00:00Z').toISOString().slice(0, 10) !== x)) {
            U.toast('Las fechas deben tener formato AAAA-MM-DD y ser válidas.', 'error'); return;
        }
        const values = {
            MAINTENANCE_MODE_ENABLED: form.elements.MAINTENANCE_MODE_ENABLED.checked,
            BLOCKED_WEEKDAYS: Array.from(form.querySelectorAll('[name="dia"]:checked'), (el) => Number(el.value)),
            BLOCKED_DATES: dates,
            BLOCKED_TIME_START: form.elements.BLOCKED_TIME_START.value,
            BLOCKED_TIME_END: form.elements.BLOCKED_TIME_END.value,
            MAINTENANCE_MESSAGE: form.elements.MAINTENANCE_MESSAGE.value,
            MAINTENANCE_SUBMESSAGE: form.elements.MAINTENANCE_SUBMESSAGE.value,
        };
        const changed = Object.keys(values).filter((key) => key in configs && JSON.stringify(values[key]) !== JSON.stringify(configs[key]));
        if (values.MAINTENANCE_MODE_ENABLED && changed.includes('MAINTENANCE_MODE_ENABLED') && !(await U.confirm('Se bloquearán TODAS las solicitudes de inmediato. ¿Continuar?'))) { return; }
        $('c-guardar').disabled = true; let saved = 0;
        try {
            for (const key of changed) { await U.api('gestor/configuracion.php', { method: 'PUT', body: { action: 'mantenimiento', key, value: values[key] } }); configs[key] = values[key]; saved++; }
            $('c-mantenimiento-error').classList.add('hidden');
            U.toast(changed.length ? 'Mantenimiento actualizado.' : 'No hay cambios.');
        } catch (err) { error(`Se guardaron ${saved} de ${changed.length} cambios. ${err.message}`); U.toast(err.message, 'error'); }
        finally { $('c-guardar').disabled = false; }
    };
    load();
})();
</script>
