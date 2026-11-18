# Matriz de cobertura QA — StayFitt (actualizada post-ejecución)

**Última corrida E2E:** 2026-08-27 08:33 UTC  
**Evidencia:** `QA/E2E_RESULTS.md`, `QA/resultados/e2e_http_results.json`, `QA/auditoria/db_verify.json`

## Estado global

| Métrica | Valor |
|---------|-------|
| Rutas inventariadas | 245 |
| Roles | 6 |
| Tablas MySQL | 99 |
| Casos E2E HTTP ejecutados | **145** |
| E2E APROBADOS | **145** |
| E2E FALLIDOS | **0** |
| Pest Feature | **83/83 APROBADO** |
| Bugs encontrados (corrida 1) | 6 documentados |
| Bugs de producto corregidos | BUG-001, BUG-002 (+ Pago.cliente, Password, Register, WhatsApp verify) |
| Pendiente NO PROBADO (fuera de los 145) | CRUD cupones edit/delete, pedidos E2E carrito completo, pagos TEST cambio estado, propuestas coach→admin E2E largo, responsive 3 breakpoints sistemático, consola JS en todas las vistas |

---

## 1. Infraestructura — APROBADO

| ID | Caso | Estado | Evidencia |
|----|------|--------|-----------|
| SYS-001 | GET /up | APROBADO | HTTP 200 |
| SYS-002 | GET / | APROBADO | HTTP 200 |
| SYS-003 | MySQL stayfit | APROBADO | db_verify DB-001 |
| SYS-004 | Migración institucional | APROBADO | columnas + tablas |
| SYS-005 | Seed demo | APROBADO | users_by_role.json |

## 2. Auth por rol — APROBADO (7 roles × login ok/bad/logout/relogin)

Ver `QA/E2E_RESULTS.md` tabla de roles.

## 3. Navegación dashboards — APROBADO en rutas listadas del runner

| Prefijo | Estado |
|---------|--------|
| /admin (23) | APROBADO |
| /coach (8) | APROBADO |
| /staff (8) | APROBADO |
| /cliente virtual (14) | APROBADO |
| /cliente presencial (8) | APROBADO |
| /cliente institucional empleado (6) | APROBADO (tras BUG-001) |
| /institucional (7) | APROBADO |

## 4. CRUD críticos

| Módulo | CREATE | READ | UPDATE | DELETE | Validación | Permisos | MySQL |
|--------|--------|------|--------|--------|------------|----------|-------|
| Planes admin | APROBADO | APROBADO | NO PROBADO | NO PROBADO | APROBADO (neg) | APROBADO | APROBADO |
| Productos tienda | APROBADO | APROBADO | NO PROBADO | NO PROBADO | — | APROBADO (403 cliente) | APROBADO |
| Invitaciones inst | APROBADO | APROBADO | — | NO PROBADO | — | APROBADO | APROBADO |
| Comunicados inst | APROBADO | APROBADO | — | — | — | APROBADO | APROBADO |
| Cupones | APROBADO | APROBADO | NO PROBADO | NO PROBADO | NO PROBADO | NO PROBADO | APROBADO |
| Coaches CRUD | NO PROBADO | NO PROBADO | NO PROBADO | NO PROBADO | NO PROBADO | NO PROBADO | NO PROBADO |
| Novedades staff | NO PROBADO | APROBADO (GET) | NO PROBADO | NO PROBADO | NO PROBADO | NO PROBADO | NO PROBADO |

## 5. Seguridad cruzada — APROBADO (casos del runner)

| Caso | Estado |
|------|--------|
| Cliente no entra /admin | APROBADO |
| Coach no entra /admin|/staff | APROBADO |
| Staff no entra /admin | APROBADO |
| Responsable no ve USCO en portal | APROBADO |
| Cliente no crea producto admin | APROBADO |
| Virtual 403 en tienda presencial | APROBADO (diseño) |

## 6. Flujos E2E

| Flujo | Estado |
|-------|--------|
| Admin crea plan → listado → MySQL | APROBADO |
| Admin crea producto → listado → MySQL | APROBADO |
| Admin edita producto → MySQL | APROBADO (B3) |
| Cupón create → listado → MySQL → cliente ve plan QA | APROBADO (EXT) |
| Pedido: agregar → carrito → checkout RETIRO → orden MySQL | APROBADO (orden #21 PENDIENTE; stock no baja en checkout) |
| Responsable invita + comunicado | APROBADO |
| Registro público invitación (Pest) | APROBADO |
| Aislamiento instituciones | APROBADO (parcial HTML) |
| Coach propuestas list/create GET | APROBADO (B3) |
| Staff propuestas/rutinas GET | APROBADO (B3) |
| Coach rutina completa → cliente | NO PROBADO |
| Staff borrador → coach aprueba | NO PROBADO |
| Stock decremento al confirmar orden admin | NO PROBADO |

## Criterio de continuidad

La auditoría **NO está cerrada**. Continuar por: cupones CRUD, pedidos, propuestas, update/delete planes/productos, browser visual restante roles, responsive.
