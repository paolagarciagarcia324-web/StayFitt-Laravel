# Login — explicación y trazabilidad

**Versión:** StayFit v4.0 · Base de datos `stayfit`

## Qué hace
Autentica usuarios de la tabla `usuarios` y abre sesión Laravel.
La fuente de verdad es `correo` + `password_hash` en MySQL (`stayfit`).

## Credencial admin de desarrollo
Tras `php artisan migrate` (seed `2026_08_16_100012_seed_admin_paola`):

| Campo | Valor |
|-------|--------|
| Correo | `paola@correo.com` |
| Contraseña | `paola12345` |
| Rol | `ADMIN` → redirige a `/admin` |

## Flujo
1. Welcome → **Iniciar sesión** (`GET /login`)
2. Usuario envía correo + contraseña (`POST /login`)
3. `LoginRequest` busca en `usuarios` por **`correo`** (coincidencia exacta)
4. Valida contraseña contra `password_hash`
5. Si OK → `Auth::login` → `ADMIN` a `/admin`, resto a `/dashboard`
6. Si falla → *Credenciales incorrectas o usuario no encontrado*

## Trazabilidad técnica

| Capa | Archivo / tabla | Rol |
|------|-----------------|-----|
| Vista | `resources/views/auth/login.blade.php` | UI login FigueFit |
| CSS | `public/css/login.css` | Estilos |
| Rutas | `routes/auth.php` | `login` GET/POST, `logout` |
| Request | `app/Http/Requests/Auth/LoginRequest.php` | Validación + autenticación |
| Modelo | `app/Models/User.php` | Tabla `usuarios`, PK `id_user` |
| Roles | `user_roles` + `roles` | Decide a qué panel entra |
| BD | `stayfit.usuarios` | Fuente de verdad |

## Mapeo de campos

| Formulario / Laravel | Columna BD | Notas |
|----------------------|------------|--------|
| `email` | `correo` | Campo de negocio para login |
| `email` (alias) | `email` | Compatibilidad Breeze; debe ser igual a `correo` |
| `password` | `password_hash` | bcrypt (o texto plano legacy migrado al primer login) |
| sesión | `id_user` | PK |

## Reglas de acceso
- `BLOQUEADO` / `INACTIVO` no pueden entrar
- `PENDIENTE` y `ACTIVO` sí pueden autenticarse
- Al entrar se actualiza `ultimo_acceso_en`
- Sin rol `ADMIN` no hay acceso al panel `/admin`

## Contraseñas
- Altas/ediciones desde Admin se guardan con **bcrypt**
- Si en BD queda texto plano (dato viejo), el login lo acepta **una vez** y lo migra a bcrypt
- Seed admin actualiza el hash con `Hash::make`

## Alta / edición desde plataforma
- Admin → Usuarios: escribe `usuarios` y sincroniza `user_roles`
- Aprobar solicitud: crea/activa usuario + rol `CLIENTE` + `clientes` + plan/accesos

## Qué no incluye
- Botón “Crear cuenta” público (el alta de cliente va por compra/pre-registro de plan)
- Auth contra la tabla genérica `users` de Breeze (no se usa)
