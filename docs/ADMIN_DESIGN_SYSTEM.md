# Design System Admin — FigueFit / StayFit

## 1. Diagnóstico visual inicial

| Problema | Evidencia |
|----------|-----------|
| Pantallas muy largas | WhatsApp, Estadísticas, formularios monolíticos |
| Jerarquía débil | Todo en `.ad-card` con el mismo peso visual |
| Formularios en bloque | Campos seguidos sin secciones |
| Tablas sin contenedor | Scroll de página en vez de scroll de tabla |
| Inconsistencia | Algunos módulos con `ad-pas-grid`, otros con tablas sueltas |
| Espaciado vertical acumulado | `margin-top:16px` repetido entre cards |

**Identidad a preservar:** fondo `#0a0e17`, pink `#ff2d78`, cyan `#2ef5e6`, Montserrat, sidebar sticky.

---

## 2. Nuevo sistema de diseño

Archivo: `public/css/admin-ds.css` (cargado tras `admin.css` en `layouts/admin.blade.php`).

### Tokens
- Superficies, radios (`14px` / `10px`), estados OK/WARN/DANGER, elevación.

### Componentes
| Clase / componente | Uso |
|--------------------|-----|
| `.ad-page` | Contenedor global del contenido (layout) |
| `.ad-panel` / `<x-admin.panel>` | Card profesional con head/body/foot |
| `.ad-stat` / `<x-admin.stat>` | Mini KPI |
| `.ad-grid-2/3/4` / `.ad-grid-12` | Layout en columnas |
| `.ad-form-section` | Secciones plegables en formularios |
| `.ad-toolbar` | Filtros compactos |
| `.ad-tabs` | Navegación por pestañas |
| `.ad-table-wrap` | Tabla con scroll interno + thead sticky |
| `.ad-tile` | Tarjeta de módulo (pasarelas) |
| `.ad-chip` | Estado / etiqueta |

---

## 3. Vistas reestructuradas (referencia + módulos)

| Vista | Cambio |
|-------|--------|
| Layout Admin | Wrapper `.ad-page` + link DS |
| WhatsApp | Stats + grid 7/5 + secciones plegables + grupos/historial |
| Estadísticas | Toolbar + tabs (Resumen/Comercial/Comunicación/Detalle) |
| Reportes | Formulario seccionado + historial en panel |
| Preview reportes | Paneles de export + tabla scroll |
| Configuración | Tabla en panel con scroll |
| Comunidad | Listado + show con tiles de mensajes |
| Roles | Panel de catálogo |
| Pasarelas | Grid de tiles + config plegable |
| Cupones | Tabla en panel |
| Dashboard | Densidad de accesos rápidos |

**Módulos restantes** (Usuarios, Clientes, Planes, Tienda, Solicitudes, etc.) heredan automáticamente:
- contenedor `.ad-page`
- tipografía/espaciado del DS
- mejora de hover/sticky en tablas dentro de `.ad-page`

Pueden migrarse progresivamente a `<x-admin.panel>` sin cambiar lógica.

---

## 4. Comparación antes / después

| Antes | Después |
|-------|---------|
| Formulario WhatsApp único y largo | Config en 3 secciones plegables + rail de estado |
| Estadísticas = scroll infinito | 4 tabs + toolbar de filtros |
| Tablas estiran la página | `.ad-table-wrap` con altura máxima |
| Estilos ad-hoc por módulo | Tokens y componentes compartidos |

---

## 5. Validación

- [x] Colores de marca intactos
- [x] Funcionalidad sin cambios de backend
- [x] Responsive (breakpoints 1100 / 720)
- [x] Tema claro (`ad-theme-light`) contemplado en DS
- [x] Componentes Blade reutilizables

## Cómo ver el resultado

1. Recarga forzada del CSS (`Ctrl+F5`) en `/admin/whatsapp`, `/admin/estadisticas`, `/admin/reportes`.
2. Comparar scroll vertical vs. versión anterior.
