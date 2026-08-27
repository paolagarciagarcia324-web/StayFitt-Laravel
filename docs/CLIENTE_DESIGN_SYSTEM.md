# Design System Cliente Premium — FigueFit Member

## Diagnóstico previo

Las vistas cliente reutilizaban paneles admin (formularios largos, tablas, stacks verticales). Funcionaban, pero no transmitían producto de membresía premium.

## Sistema visual (`public/css/client-ds.css`)

Prefijo **`cv-*`**. Identidad FigueFit preservada: navy + pink `#ff2d78` + cyan `#2ef5e6` + Montserrat.

### Tokens
Fondos atmosféricos, superficies glass, radios 18/12, glow sutil pink/cyan, tipografía display.

### Componentes Blade
| Componente | Uso |
|---|---|
| `<x-cliente.page-head>` | Título de página + acciones |
| `<x-cliente.metric>` | KPI compacto |
| `<x-cliente.card>` | Superficie con head/body |

### Patrones CSS
Hero (`.cv-hero`), métricas, media grid (tienda/videos), pricing (`.cv-plan`), chat hub, composer, forms 2-col, tabs, charts.

## Layout

`layouts/cliente.blade.php` + clase `body.is-cliente`:
- Sidebar member con avatar/plan
- Navegación acortada (labels de producto)
- Header con avisos
- Contenido en `.cv-page`

## Vistas rediseñadas (todas)

Dashboard, perfil / ficha presencial, membresía, análisis, planes/detalle/solicitudes/cupones, pagos, tienda/carrito/órdenes, entrenamiento/rutina, nutrición, videos, progreso, agenda, novedades, comunidad (chat), notificaciones.

Ver también: `docs/PERFIL_PRESENCIAL.md` (`perfilpresencial01`).

**Comunidad** = centro de mensajes (no feed social inventado).

## Cómo ver

Login cliente demo → `/cliente` con hard refresh (`Ctrl+F5`) para cargar `client-ds.css`.
