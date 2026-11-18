---
inclusion: always
---

# Commits automáticos StayFit

Cuando el usuario diga **"sube un commit"**, **"sube un commit"**, **"haz commit y push"** o equivalente:

1. Ejecutar de inmediato (sin pedir confirmación extra):
   - `git status`, `git diff`, `git log -5` en paralelo
   - Si la rama local está detrás del remoto: `git pull --ff-only` (o pull con rebase si ya es la convención)
   - `git add` de los cambios relevantes del trabajo (nunca `.env`, credenciales ni secretos)
   - `git commit` con mensaje en español que **especifique qué cambios o mejoras hubo** (welcome, login, admin, pagos, etc.)
   - `git push -u origin HEAD` al remoto configurado (p. ej. `StayFitt-Laravel`)
2. Si el usuario da un mensaje exacto de commit, usarlo tal cual.
3. Si no da mensaje, redactar uno conciso (1–2 frases) basado en el diff: qué se mejoró y por qué.
4. Tras el push, devolver la URL del repo/commit o confirmar rama y remoto.
5. No usar `--force` en `main`/`master` salvo petición explícita.
6. No actualizar `git config`. No omitir hooks (`--no-verify`) salvo petición explícita.
