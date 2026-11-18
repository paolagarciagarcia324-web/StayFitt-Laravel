# Matriz de permisos por rol (evidencia HTTP real)

| Recurso | ADMIN | COACH | STAFF | CLIENTE | CLIENTE_INST | RESPONSABLE_INST |
|---------|-------|-------|-------|---------|--------------|------------------|
| /admin/* | 200 | 403 | 403 | 403 | 403 | 403 |
| /coach/* | (vía role) | 200 | (parcial) | 403 | 403 | 403 |
| /staff/* | — | 403 | 200 | 403 | — | — |
| /cliente/* | — | 403 | — | 200 | 200* | 403 |
| /institucional/* | selector | 403 | — | 403 | 403 | 200 |
| Crear producto | OK | — | — | 403 | — | — |
| Crear plan | OK | — | — | — | — | — |
| Invitar miembros | — | — | — | — | — | OK |
| Comunicado institucional | — | — | — | — | — | OK |

\* Tras fix BUG-001 (`role:CLIENTE,CLIENTE_INSTITUCIONAL`).
