# Bugs corregidos (actualizado)

## BUG-001 — CLIENTE_INSTITUCIONAL 403 en /cliente
- Fix: `routes/cliente.php` → `role:CLIENTE,CLIENTE_INSTITUCIONAL`
- Re-prueba E2E: 6/6 nav institucional empleado → 200

## BUG-002 — 500 crear producto sin categoría
- Fix: `TiendaController` null coalescing
- Re-prueba E2E + MySQL: producto `QA-SKU-083331`

## BUG-007 — Register no persistía password
- Fix: `RegisteredUserController` usa `assignPassword`
- Re-prueba Pest RegistrationTest

## BUG-008 — Password update no persistía
- Fix: `PasswordController` usa `assignPassword`
- Re-prueba Pest PasswordUpdateTest

## BUG-009 — Pago sin relación cliente (500 reportes)
- Fix: `Pago::cliente()` BelongsTo
- Re-prueba Pest Analitica preview pagos

## BUG-010 — Rol mass assignment / tests seed duplicados
- Fix: `Rol::$fillable` + `firstOrCreate` en helpers de test

## BUG-011 — WhatsApp verify 403 con token válido
- Fix: preferir config con `api_token_cifrado`; aceptar hub.mode aliases

## Suite Feature post-fix
**83/83 passed**
