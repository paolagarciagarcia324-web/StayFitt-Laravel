# Bugs encontrados — ejecución real E2E HTTP (2026-08-27)

Fuente: `QA/resultados/e2e_http_results.json` + `storage/logs/laravel.log`

| ID | Módulo | Rol | Pasos | Error | Causa raíz | Archivo | Estado |
|----|--------|-----|-------|-------|------------|---------|--------|
| BUG-001 | cliente | CLIENTE_INSTITUCIONAL | Login → GET /cliente | HTTP 403 en todo el portal cliente | Middleware `role:CLIENTE` excluía `CLIENTE_INSTITUCIONAL` | `routes/cliente.php` | CORREGIDO |
| BUG-002 | tienda admin | ADMIN | POST /admin/tienda sin categoría | HTTP 500 Undefined array key id_categoria_producto | Acceso a key nullable sin `??` | `TiendaController.php:48` | CORREGIDO |
| BUG-003 | coach | COACH | GET /coach/reportes | HTTP 404 | Ruta real es `/coach/reportes/progreso` | Runner E2E (expectativa) | CORREGIDO en matriz |
| BUG-004 | tienda cliente | CLIENTE_VIRTUAL | GET /cliente/tienda | HTTP 403 | Diseño: tienda exclusiva presencial (módulo) | EnsureModuloAcceso | POR DISEÑO (documentado) |
| BUG-005 | planes admin | ADMIN | GET /admin/planes/crear | HTTP 302 | `create()` redirige a index?create=1 | PlanController | POR DISEÑO; runner ajustado |
| BUG-006 | planes/tienda POST | ADMIN | CSRF 419 en corrida 1 | Token CSRF de página create inválido tras redirects | Runner usaba CSRF de ruta 302 | Runner ajustado | CORREGIDO en runner |

## Notas

- BUG-001 es crítico de producto: empleados institucionales no podían usar el portal cliente tras login exitoso.
- BUG-002 es crítico operativo: admin no podía crear productos sin enviar categoría.
