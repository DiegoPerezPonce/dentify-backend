# Resumen de pruebas API (Postman) — Dentify

Plantilla para el informe. Rellena la columna **Resultado** tras ejecutar la colección.

**Entorno:** Dentify Local · `http://127.0.0.1:8000`  
**Fecha:** _______________  
**Ejecutado por:** _______________

## Preparación

- [ ] `symfony server:start`
- [ ] `php bin/console app:create-test-user`
- [ ] Colección e entorno importados en Postman
- [ ] Ejecutado **00 - Auth → Login (éxito)**

## Resultados por módulo

| Módulo | Peticiones | OK | Fallos | Notas |
|--------|------------|----|--------|-------|
| 00 - Auth | 2 | | | |
| 01 - Setup | 7 | | | |
| 02 - Pacientes | 8 | | | |
| 03 - Citas | 7 | | | |
| 04 - Sesión cita | 7 | | | |
| 05 - Odontólogos | 4 | | | |
| 06 - Cajas | 4 | | | |
| 07 - Stock | 7 | | | |
| 08 - Protocolos | 4 | | | |
| 09 - Usuarios | 4 | | | |
| 10 - Catálogo | 4 | | | |
| 11 - Agenda | 5 | | | |
| 12 - Me y otros | 5 | | | |
| 99 - Negativas | 4 | | | |

## Casos representativos

| ID | Caso | Endpoint | Esperado | Resultado |
|----|------|----------|----------|-----------|
| API-01 | Login correcto | POST /api/login | 200 + token | |
| API-02 | Login incorrecto | POST /api/login | 401 | |
| API-03 | Sin JWT | GET /api/patients | 401 | |
| API-04 | Listado paginado | GET /api/patients | 200 + items | |
| API-05 | Crear paciente | POST /api/patients | 201 | |
| API-06 | Validación paciente | POST /api/patients (vacío) | 400/422 | |
| API-07 | Crear cita | POST /api/appointments | 201 | |
| API-08 | Sesión clínica | POST .../session/start | 200/201 | |
| API-09 | Stock reposición | POST /api/stock-restocks | 201 | |

## Incidencias detectadas

| # | Descripción | Severidad | Acción |
|---|-------------|-----------|--------|
| 1 | | | |

## Conclusiones

_Redactar 1–2 párrafos: cobertura de la API, coherencia con Angular, seguridad JWT, validación servidor, limitaciones (p. ej. sin upload radiografías en colección)._

## Anexo

- Export Runner / capturas Postman
- `Dentify-API.postman_collection.json`
- `Dentify-Local.postman_environment.json`
