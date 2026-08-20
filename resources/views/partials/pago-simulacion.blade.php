<div class="ff-pay" data-pay-box>
    <p class="ff-reg-hint">¿Cómo pagas? *</p>
    <div class="ff-horario-grid" role="radiogroup" aria-label="Método de pago">
        <label class="ff-horario-card">
            <input type="radio" name="metodo_pago" value="TARJETA" @checked(old('metodo_pago', 'TARJETA') === 'TARJETA') required>
            <span class="ff-horario-body">
                <strong>Tarjeta débito / crédito</strong>
                <em>Checkout seguro · cobro automático</em>
            </span>
        </label>
        <label class="ff-horario-card">
            <input type="radio" name="metodo_pago" value="PSE" @checked(old('metodo_pago') === 'PSE') required>
            <span class="ff-horario-body">
                <strong>PSE · débito en línea</strong>
                <em>Tu banco autoriza el pago</em>
            </span>
        </label>
    </div>

    <div class="ff-pay-panel" data-pay-panel="TARJETA">
        <div class="ff-reg-grid two">
            <label class="ff-pay-span"><span>Número de tarjeta *</span><input type="text" name="numero_tarjeta" inputmode="numeric" maxlength="19" autocomplete="cc-number" value="{{ old('numero_tarjeta') }}" placeholder="**** **** **** ****"></label>
            <label><span>Vencimiento (MM/AA) *</span><input type="text" name="vencimiento" maxlength="5" placeholder="08/28" value="{{ old('vencimiento') }}" autocomplete="off"></label>
            <label><span>CVV *</span><input type="password" name="cvv" maxlength="4" inputmode="numeric" autocomplete="off"></label>
            <label><span>Nombre del titular *</span><input type="text" name="titular" value="{{ old('titular') }}" autocomplete="cc-name"></label>
            <label><span>Dirección *</span><input type="text" name="direccion_pago" value="{{ old('direccion_pago') }}"></label>
            <label><span>Ciudad *</span><input type="text" name="ciudad_pago" value="{{ old('ciudad_pago') }}"></label>
        </div>
        <p class="ff-pre-note">El número y el CVV no se guardan. En producción la pasarela los tokeniza.</p>
    </div>

    <div class="ff-pay-panel" data-pay-panel="PSE" hidden>
        <div class="ff-reg-grid two">
            <label>
                <span>Banco *</span>
                <select name="banco_pse">
                    <option value="">Selecciona tu banco...</option>
                    @foreach (['Bancolombia', 'Davivienda', 'BBVA', 'Banco de Bogotá', 'Banco de Occidente', 'Nequi', 'Otro'] as $banco)
                        <option value="{{ $banco }}" @selected(old('banco_pse') === $banco)>{{ $banco }}</option>
                    @endforeach
                </select>
            </label>
            <p class="ff-pre-note" style="margin:0;align-self:end">La clave se ingresa en el banco, nunca aquí.</p>
        </div>
    </div>

    <div class="ff-reg-grid two">
        <label>
            <span>Tipo de documento *</span>
            <select name="pago_tipo_documento" required>
                @foreach (['CC' => 'Cédula', 'CE' => 'Cédula extranjería', 'NIT' => 'NIT', 'PP' => 'Pasaporte'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('pago_tipo_documento', 'CC') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label><span>Número de documento *</span><input type="text" name="pago_documento" value="{{ old('pago_documento') }}" required></label>
        <label><span>Correo del pago *</span><input type="email" name="pago_correo" value="{{ old('pago_correo') }}" required></label>
        <label><span>Teléfono del pago *</span><input type="text" name="pago_telefono" value="{{ old('pago_telefono') }}" required></label>
    </div>

    <label class="ff-terms">
        <input type="checkbox" name="acepto_recurrente" value="1" @checked(old('acepto_recurrente')) required>
        <span>Acepto los cobros recurrentes de {{ $plan->precioFormateado() }} cada {{ (int) $plan->duracion_dias ?: 30 }} días *</span>
    </label>
</div>

<script>
(() => {
    const box = document.querySelector('[data-pay-box]');
    if (!box) return;
    const sync = () => {
        const metodo = box.querySelector('input[name="metodo_pago"]:checked')?.value;
        box.querySelectorAll('[data-pay-panel]').forEach((panel) => {
            panel.hidden = panel.getAttribute('data-pay-panel') !== metodo;
        });
    };
    box.querySelectorAll('input[name="metodo_pago"]').forEach((el) => el.addEventListener('change', sync));
    const fill = (from, to) => {
        const a = document.querySelector(`[name="${from}"]`);
        const b = document.querySelector(`[name="${to}"]`);
        if (a && b && !b.value) b.value = a.value;
    };
    fill('documento_identidad', 'pago_documento');
    fill('correo', 'pago_correo');
    fill('telefono', 'pago_telefono');
    fill('ciudad', 'ciudad_pago');
    const nombres = document.querySelector('[name="nombres"]');
    const apellidos = document.querySelector('[name="apellidos"]');
    const titular = document.querySelector('[name="titular"]');
    if (nombres && apellidos && titular && !titular.value) titular.value = `${nombres.value} ${apellidos.value}`.trim();
    sync();
})();
</script>
