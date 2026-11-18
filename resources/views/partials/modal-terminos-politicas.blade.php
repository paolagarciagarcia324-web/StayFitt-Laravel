<!-- Modal de Políticas y Términos FigueFit -->
<div class="ff-policy-modal is-hidden" id="ff-policy-modal" role="dialog" aria-modal="true" aria-labelledby="ff-policy-title" aria-hidden="true">
    <div class="ff-policy-dialog" role="document">
        <button type="button" class="ff-policy-close" id="ff-policy-close-btn" aria-label="Cerrar modal">
            <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M18.3 5.71a1 1 0 0 0-1.41 0L12 10.59 7.11 5.7A1 1 0 0 0 5.7 7.11L10.59 12 5.7 16.89a1 1 0 1 0 1.41 1.41L12 13.41l4.89 4.89a1 1 0 0 0 1.41-1.41L13.41 12l4.89-4.89a1 1 0 0 0 0-1.4Z"/></svg>
        </button>

        <header class="ff-policy-dialog-head">
            <p class="ff-policy-kicker" id="ff-policy-kicker">Transparencia y Seguridad · FigueFit</p>
            <h2 id="ff-policy-title">Políticas del Servicio</h2>
            <p class="ff-policy-lead" id="ff-policy-lead">Conoce el alcance y las garantías de lo que estás autorizando.</p>
        </header>

        <div class="ff-policy-dialog-body" id="ff-policy-body">
            <!-- El contenido dinámico se inyecta vía JavaScript -->
        </div>

        <footer class="ff-policy-dialog-foot">
            <button type="button" class="ff-policy-btn-ack" id="ff-policy-ack-btn">Entendido</button>
        </footer>
    </div>
</div>

<script>
(() => {
    const modal = document.getElementById('ff-policy-modal');
    if (!modal) return;

    const kickerEl = document.getElementById('ff-policy-kicker');
    const titleEl = document.getElementById('ff-policy-title');
    const leadEl = document.getElementById('ff-policy-lead');
    const bodyEl = document.getElementById('ff-policy-body');
    const closeBtn = document.getElementById('ff-policy-close-btn');
    const ackBtn = document.getElementById('ff-policy-ack-btn');

    const contents = {
        recurrente: {
            kicker: 'Transparencia Financiera · FigueFit',
            title: 'Política de Cobros Recurrentes y Suscripción',
            lead: 'Esta autorización es totalmente opcional. Tú decides si deseas renovar de forma automática o gestionar tus pagos manualmente.',
            html: `
                <div class="ff-policy-section">
                    <div class="ff-policy-item">
                        <div class="ff-policy-icon">
                            <svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2Zm1 14.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Zm0-5a1 1 0 0 1-2 0V7a1 1 0 0 1 2 0Z"/></svg>
                        </div>
                        <div>
                            <h3>Permiso voluntario del usuario</h3>
                            <p>Esta casilla <strong>no es obligatoria</strong>. Es una decisión personal sobre tu dinero. Si la activas, autorizas que la pasarela de pago realice el cobro del plan mensual al cumplirse cada ciclo de 30 días.</p>
                        </div>
                    </div>

                    <div class="ff-policy-item">
                        <div class="ff-policy-icon">
                            <svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M12 4V1L8 5l4 4V6a6 6 0 1 1-6 6H4a8 8 0 1 0 8-8Z"/></svg>
                        </div>
                        <div>
                            <h3>¿Qué pasa si no la marcas?</h3>
                            <p>Si decides <strong>no marcar la casilla</strong>, tu plan continuará activo normalmente por el periodo que pagaste. Al término de los 30 días el servicio no se cobrará solo, y tendrás total libertad para ingresar a la plataforma y renovar manualmente cuando lo prefieras.</p>
                        </div>
                    </div>

                    <div class="ff-policy-item">
                        <div class="ff-policy-icon">
                            <svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M9 16.2 4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2Z"/></svg>
                        </div>
                        <div>
                            <h3>Cancelación en cualquier momento</h3>
                            <p>Si la marcas y más adelante deseas desactivar los cobros automáticos, puedes cancelarlos sin ningún tipo de sanción, penalidad o cláusula de permanencia, comunicándote con soporte o desde tu panel de usuario.</p>
                        </div>
                    </div>

                    <div class="ff-policy-item">
                        <div class="ff-policy-icon">
                            <svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2Zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2Zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1s3.1 1.39 3.1 3.1v2Z"/></svg>
                        </div>
                        <div>
                            <h3>Seguridad de tus datos de pago</h3>
                            <p>FigueFit nunca almacena números de tarjeta ni datos sensibles en texto plano. Los pagos y cobros periódicos son procesados directamente por pasarelas certificadas con cifrado bancario de alta seguridad.</p>
                        </div>
                    </div>
                </div>
            `
        },
        terminos: {
            kicker: 'Contrato de Servicio · FigueFit',
            title: 'Términos y Condiciones Generales',
            lead: 'Esta casilla es obligatoria para garantizar la trazabilidad, seguridad deportiva y el tratamiento legal de tu ficha.',
            html: `
                <div class="ff-policy-section">
                    <div class="ff-policy-item">
                        <div class="ff-policy-icon">
                            <svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6Zm2 16H8v-2h8v2Zm0-4H8v-2h8v2Zm-3-5V3.5L18.5 9H13Z"/></svg>
                        </div>
                        <div>
                            <h3>Aceptación obligatoria del servicio</h3>
                            <p>Al marcar esta casilla, confirmas tu vinculación a los servicios y metodologías de entrenamiento de FigueFit (tanto en modalidad virtual como presencial en sala).</p>
                        </div>
                    </div>

                    <div class="ff-policy-item">
                        <div class="ff-policy-icon">
                            <svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35Z"/></svg>
                        </div>
                        <div>
                            <h3>Salud y veracidad de la información</h3>
                            <p>Declaras bajo gravedad de juramento que las medidas iniciales, antecedentes médicos, lesiones, alergias y contactos de emergencia suministrados son verídicos, y que cuentas con las condiciones físicas para realizar ejercicio funcional o supervisado.</p>
                        </div>
                    </div>

                    <div class="ff-policy-item">
                        <div class="ff-policy-icon">
                            <svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2Zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2Zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1s3.1 1.39 3.1 3.1v2Z"/></svg>
                        </div>
                        <div>
                            <h3>Protección de datos (Habeas Data)</h3>
                            <p>Autorizas a FigueFit al tratamiento confidencial de tus datos personales, de contacto y métricas antropométricas de acuerdo con la legislación de protección de datos, utilizados exclusivamente para prestarte el servicio y seguimiento de tu avance.</p>
                        </div>
                    </div>

                    <div class="ff-policy-item">
                        <div class="ff-policy-icon">
                            <svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3Zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3Zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5Zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5Z"/></svg>
                        </div>
                        <div>
                            <h3>Reglamento de convivencia y cupos</h3>
                            <p>El acceso a sala y plataformas es personal e intransferible. En la modalidad presencial debes respetar el horario asignado para conservar tu cupo y el orden en las instalaciones.</p>
                        </div>
                    </div>
                </div>
            `
        }
    };

    const openModal = (type) => {
        const item = contents[type] || contents.terminos;
        kickerEl.textContent = item.kicker;
        titleEl.textContent = item.title;
        leadEl.textContent = item.lead;
        bodyEl.innerHTML = item.html;
        modal.classList.remove('is-hidden');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    };

    const closeModal = () => {
        modal.classList.add('is-hidden');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    };

    document.querySelectorAll('[data-policy-trigger]').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const type = btn.getAttribute('data-policy-trigger') || 'terminos';
            openModal(type);
        });
    });

    closeBtn?.addEventListener('click', closeModal);
    ackBtn?.addEventListener('click', closeModal);

    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeModal();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modal.classList.contains('is-hidden')) {
            closeModal();
        }
    });
})();
</script>
