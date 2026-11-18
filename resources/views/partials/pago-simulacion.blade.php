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
            <label class="ff-pay-span"><span>Dirección de facturación (opcional)</span><input type="text" name="direccion_pago" value="{{ old('direccion_pago') }}" placeholder="Dirección del pagador"></label>
        </div>
        <p class="ff-pre-note">El número y el CVV no se guardan. En producción la pasarela los tokeniza.</p>
    </div>

    <div class="ff-pay-panel" data-pay-panel="PSE" hidden>
        <div class="ff-reg-grid two">
            <label class="ff-pay-span">
                <span>Banco *</span>
                <select name="banco_pse">
                    <option value="">Selecciona tu banco...</option>
                    @foreach (['Bancolombia', 'Davivienda', 'BBVA', 'Banco de Bogotá', 'Banco de Occidente', 'Nequi', 'Otro'] as $banco)
                        <option value="{{ $banco }}" @selected(old('banco_pse') === $banco)>{{ $banco }}</option>
                    @endforeach
                </select>
            </label>
        </div>
        <p class="ff-pre-note">La clave se ingresa en el portal de tu banco, nunca aquí.</p>
    </div>

    <!-- Sección de datos del pagador (titular de la cuenta) -->
    <div class="ff-payer-box" style="margin-top:14px;padding-top:12px;border-top:1px dashed rgba(255,255,255,0.12)">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;margin-bottom:10px">
            <div>
                <strong style="color:#f1f5f9;font-size:0.92rem">Datos del pagador / titular de la cuenta</strong>
                <p style="margin:2px 0 0 0;color:#94a3b8;font-size:0.8rem">
                    Ingresa los datos de la cuenta o tarjeta con la que se realiza el pago (puede ser esposo, padres u otra persona).
                </p>
            </div>
            <label class="ff-terms-label" style="font-size:0.82rem;cursor:pointer;display:inline-flex;align-items:center;gap:6px;user-select:none;background:rgba(255,255,255,0.04);padding:5px 10px;border-radius:6px;border:1px solid rgba(255,255,255,0.08)">
                <input type="checkbox" name="pago_mismos_datos" id="pago_mismos_datos" value="1" @checked(old('pago_mismos_datos'))>
                <span>Usar mis mismos datos personales para el pago</span>
            </label>
        </div>

        <div class="ff-reg-grid two">
            <label class="ff-pay-span">
                <span>Nombre del titular de la cuenta o tarjeta *</span>
                <input type="text" name="titular" id="titular" value="{{ old('titular') }}" autocomplete="name" placeholder="Como figura en la tarjeta o cuenta bancaria" required>
            </label>
            <label>
                <span>Tipo de documento del pagador *</span>
                <select name="pago_tipo_documento" id="pago_tipo_documento" required>
                    @foreach (['CC' => 'Cédula de ciudadanía (CC)', 'CE' => 'Cédula de extranjería (CE)', 'NIT' => 'NIT', 'PP' => 'Pasaporte', 'TI' => 'Tarjeta de identidad (TI)', 'OTRO' => 'Otro'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('pago_tipo_documento', 'CC') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label><span>Número de documento del pagador *</span><input type="text" name="pago_documento" id="pago_documento" value="{{ old('pago_documento') }}" placeholder="Documento del pagador" required></label>
            <label><span>Correo del pagador (comprobante) *</span><input type="email" name="pago_correo" id="pago_correo" value="{{ old('pago_correo') }}" placeholder="correo@pagador.com" required></label>
            <label><span>Teléfono del pagador *</span><input type="text" name="pago_telefono" id="pago_telefono" value="{{ old('pago_telefono') }}" placeholder="Teléfono del pagador" required></label>
            <label class="ff-pay-span"><span>Ciudad del pagador *</span><input type="text" name="ciudad_pago" id="ciudad_pago" value="{{ old('ciudad_pago') }}" placeholder="Ciudad del pagador" required></label>
        </div>
    </div>

    <div class="ff-terms-card">
        <label class="ff-terms-label">
            <input type="checkbox" name="acepto_recurrente" value="1" @checked(old('acepto_recurrente'))>
            <span>Acepto los cobros recurrentes de {{ $plan->precioFormateado() }} cada {{ (int) $plan->duracion_dias ?: 30 }} días</span>
        </label>
        <button type="button" class="ff-terms-detail-btn" data-policy-trigger="recurrente" aria-haspopup="dialog" aria-label="Ver detalle de cobros recurrentes">
            <svg viewBox="0 0 24 24" width="13" height="13" aria-hidden="true"><path fill="currentColor" d="M12 2a10 10 0 1 0 10 10A10.011 10.011 0 0 0 12 2Zm0 18a8 8 0 1 1 8-8 8.009 8.009 0 0 1-8 8Zm0-13a1.25 1.25 0 1 0 1.25 1.25A1.25 1.25 0 0 0 12 7Zm1 4h-2a1 1 0 0 0 0 2h1v4a1 1 0 0 0 2 0v-5a1 1 0 0 0-1-1Z"/></svg>
            Ver detalle
        </button>
    </div>
</div>

<script>
(() => {
    const box = document.querySelector('[data-pay-box]');
    if (!box) return;

    const syncMetodo = () => {
        const metodo = box.querySelector('input[name="metodo_pago"]:checked')?.value;
        box.querySelectorAll('[data-pay-panel]').forEach((panel) => {
            panel.hidden = panel.getAttribute('data-pay-panel') !== metodo;
        });
    };
    box.querySelectorAll('input[name="metodo_pago"]').forEach((el) => el.addEventListener('change', syncMetodo));
    syncMetodo();

    const chkMismosDatos = document.getElementById('pago_mismos_datos');
    const nombres = document.querySelector('[name="nombres"]');
    const apellidos = document.querySelector('[name="apellidos"]');
    const titular = document.querySelector('[name="titular"]');
    const tipoDoc = document.querySelector('select[name="tipo_documento"]');
    const pagoTipoDoc = document.querySelector('select[name="pago_tipo_documento"]');
    const docIdentidad = document.querySelector('[name="documento_identidad"]');
    const pagoDoc = document.querySelector('[name="pago_documento"]');
    const correo = document.querySelector('[name="correo"]');
    const pagoCorreo = document.querySelector('[name="pago_correo"]');
    const telefono = document.querySelector('[name="telefono"]');
    const pagoTelefono = document.querySelector('[name="pago_telefono"]');
    const ciudad = document.querySelector('[name="ciudad"]');
    const ciudadPago = document.querySelector('[name="ciudad_pago"]');

    const copiarDatosPersonales = () => {
        if (!chkMismosDatos || !chkMismosDatos.checked) return;

        if (titular && (nombres?.value || apellidos?.value)) {
            const nom = `${nombres?.value || ''} ${apellidos?.value || ''}`.trim();
            if (nom) titular.value = nom;
        }

        if (pagoDoc && docIdentidad?.value) {
            pagoDoc.value = docIdentidad.value;
        }

        if (pagoCorreo && correo?.value) {
            pagoCorreo.value = correo.value;
        }

        if (pagoTelefono && telefono?.value) {
            pagoTelefono.value = telefono.value;
        }

        if (ciudadPago && ciudad?.value) {
            ciudadPago.value = ciudad.value;
        }

        if (tipoDoc && pagoTipoDoc && tipoDoc.value) {
            const map = { CC: 'CC', CE: 'CE', NIT: 'NIT', PP: 'PP', PASAPORTE: 'PP', TI: 'TI', OTRO: 'OTRO' };
            const mapped = map[tipoDoc.value] || 'CC';
            if (pagoTipoDoc.querySelector(`option[value="${mapped}"]`)) {
                pagoTipoDoc.value = mapped;
            }
        }
    };

    const limpiarDatosPagador = () => {
        if (titular) titular.value = '';
        if (pagoDoc) pagoDoc.value = '';
        if (pagoCorreo) pagoCorreo.value = '';
        if (pagoTelefono) pagoTelefono.value = '';
        if (ciudadPago) ciudadPago.value = '';
    };

    if (chkMismosDatos) {
        chkMismosDatos.addEventListener('change', () => {
            if (chkMismosDatos.checked) {
                copiarDatosPersonales();
            } else {
                limpiarDatosPagador();
            }
        });

        // Solo replica si el checkbox explícito está activado
        [nombres, apellidos, tipoDoc, docIdentidad, correo, telefono, ciudad].forEach((el) => {
            if (!el) return;
            el.addEventListener('input', () => {
                if (chkMismosDatos.checked) copiarDatosPersonales();
            });
            el.addEventListener('change', () => {
                if (chkMismosDatos.checked) copiarDatosPersonales();
            });
        });

        // Si el usuario edita directamente cualquier campo del pagador, se desmarca el checkbox
        [titular, pagoTipoDoc, pagoDoc, pagoCorreo, pagoTelefono, ciudadPago].forEach((el) => {
            if (!el) return;
            el.addEventListener('input', () => {
                if (chkMismosDatos.checked) {
                    chkMismosDatos.checked = false;
                }
            });
            el.addEventListener('change', () => {
                if (chkMismosDatos.checked) {
                    chkMismosDatos.checked = false;
                }
            });
        });

        if (chkMismosDatos.checked) {
            copiarDatosPersonales();
        }
    }
})();
</script>
