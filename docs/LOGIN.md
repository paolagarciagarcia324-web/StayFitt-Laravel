# Login — explicación y trazabilidad

## Qué hace
Permite autenticar usuarios existentes de la tabla `usuarios` y abrir sesión Laravel. **No crea cuentas**: el alta de clientes ocurre por compra de plan (pre-registro).

## Flujo
1. Welcome → **Iniciar sesión** (`GET /login`)
2. Usuario envía correo + contraseña (`POST /login`)
3. `LoginRequest` valida y autentica contra BD
4. Si OK → regenera sesión → `dashboard`
5. Si falla → mensaje de error en el formulario

## Trazabilidad técnica

| Capa | Archivo / tabla | Rol |
|------|-----------------|-----|
| Vista | `resources/views/auth/login.blade.php` | UI (tarjeta split + `login.png`) |
| CSS | `public/css/login.css` | Estilos del login |
| Rutas | `routes/auth.php` | `login` GET/POST, `logout` |
| Controller | `AuthenticatedSessionController` | Muestra form, autentica, cierra sesión |
| Request | `LoginRequest` | Validación + `Auth::attempt` |
| Modelo | `app/Models/User.php` | Tabla `usuarios` |
| BD | `usuarios.correo` + `usuarios.password_hash` | Credenciales |

## Mapeo con StayFit BD
- Formulario `email` → columna `correo`
- Formulario `password` → se compara con `password_hash` (bcrypt)
- PK de sesión: `id_user`
- Si `estado = BLOQUEADO` → no deja entrar
- Al entrar se actualiza `ultimo_acceso_en`

## Usuario de prueba (seed)
Migración `2026_03_01_000071_seed_admin_login.php`:

- Correo: `admin@figuefit.com`
- Contraseña: `Admin123!`
- Rol: `ADMIN`

## Qué no incluye (a propósito)
- Botón “Crear cuenta” (registro solo vía plan)
- Middleware `verified` en dashboard (aún no hay flujo de verificación obligatorio)
- Panel por rol (siguiente fase: router Admin/Coach/Cliente)
