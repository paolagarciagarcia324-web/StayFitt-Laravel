# E2E Results — ejecución real

**Fecha:** 2026-08-27  
**Servidor:** `http://127.0.0.1:8000` (artisan serve)  
**BD:** MySQL `stayfit`  
**Runner:** `QA/e2e/run_e2e_http.php`  
**Log:** `QA/logs/e2e_http.log` / `QA/logs/e2e_run2.txt`  
**JSON:** `QA/resultados/e2e_http_results.json`

## Corrida 1 (pre-fix)

| Métrica | Valor |
|---------|-------|
| Total | 145 |
| APROBADOS | 128 |
| FALLIDOS | 17 |
| Bugs abiertos | 9 |

## Corrida 2 (post-fix)

| Métrica | Valor |
|---------|-------|
| Total | 145 |
| APROBADOS | **145** |
| FALLIDOS | **0** |
| Bugs abiertos en runner | **0** |

## Persistencia MySQL verificada (post-E2E)

| Evidencia | Valor |
|-----------|-------|
| Plan QA | `Plan QA Automático QA083330` precio 99000 ACTIVO |
| Producto QA | `QA-SKU-083331` stock 25 |
| Invitaciones institucionales | 3 |
| Comunicados institucionales | 3 |
| Instituciones | 3 (≥2 para aislamiento) |

## Pest Feature (stayfit_test)

| Suite | Resultado |
|-------|-----------|
| `tests/Feature` completo | **83 passed / 83** (tras correcciones) |
| Institucional + Bugfix | 11/11 previo a suite completa |

## Roles ejercidos en HTTP real

| Rol | Login OK | Login bad | Dashboard | Nav | Forbidden | Logout | Relogin |
|-----|----------|-----------|-----------|-----|-----------|--------|---------|
| ADMIN | ✓ | ✓ | ✓ | 23 rutas | — | ✓ | ✓ |
| COACH | ✓ | ✓ | ✓ | 8 rutas | /admin /staff | ✓ | ✓ |
| ASISTENTE | ✓ | ✓ | ✓ | 8 rutas | /admin | ✓ | ✓ |
| CLIENTE VIRTUAL | ✓ | ✓ | ✓ | 14 rutas | admin/coach/staff/institucional | ✓ | ✓ |
| CLIENTE PRESENCIAL | ✓ | ✓ | ✓ | 8 rutas | admin/coach | ✓ | ✓ |
| CLIENTE INSTITUCIONAL | ✓ | ✓ | ✓ | 6 rutas | admin/institucional | ✓ | ✓ |
| RESPONSABLE INST | ✓ | ✓ | ✓ | 7 rutas | admin/coach | ✓ | ✓ |

## Extensiones posteriores

| Runner | Resultado |
|--------|-----------|
| `run_e2e_ext.php` (cupones + plan QA visible cliente) | **8/8** |
| `run_e2e_block3.php` (coach/staff/update producto/solicitudes) | **15/15** |
| `run_e2e_cart.php` (agregar→checkout→orden MySQL) | **9/9** orden `#21` PENDIENTE |

## Browser visual

| Pantalla | Evidencia |
|----------|-----------|
| Login FigueFit | Screenshot MCP |
| Dashboard institucional Colegio Campoalegre | Screenshot MCP — KPIs, miembros, invitaciones, plan |

## Pest Feature

**83/83 passed** (`stayfit_test`)

