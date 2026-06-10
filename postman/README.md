# Dentify API — Colección Postman

Batería de pruebas funcionales de la API REST Symfony para el informe técnico del proyecto Dentify.

## Requisitos

1. Symfony en marcha: `symfony server:start` (por defecto `http://127.0.0.1:8000`)
2. Base de datos migrada y con datos de prueba
3. Usuario de prueba:

```bash
php bin/console app:create-test-user
```

Credenciales por defecto: `testuser` / `123456`

## Verificar antes de importar (recomendado)

Con Symfony en marcha:

```bash
node postman/verify-api-flows.mjs
node postman/generate-collection.mjs
```

El script de verificación ejecuta los flujos CRUD reales y debe mostrar **todas OK** antes de reimportar en Postman.

## Importar en Postman

1. **File → Import**
2. Selecciona ambos archivos de esta carpeta:
   - `Dentify-API.postman_collection.json`
   - `Dentify-Local.postman_environment.json`
3. Activa el entorno **Dentify Local** (esquina superior derecha)

## Orden recomendado de ejecución

1. Carpeta **00 - Auth** → `Login (éxito)` — guarda el JWT automáticamente
2. Carpeta **01 - Setup (IDs)** — rellena `patientId`, `dentistId`, `boxId`, etc. desde listados
3. Resto de carpetas en el orden que prefieras
4. Carpetas funcionales **02–12** — cada una incluye su **DELETE** al final (ejecutar tras POST Crear de la misma carpeta)
   - **04 - Sesión de cita**: ejecutar en orden los 2 prerequisitos, luego **Iniciar** → **GET** → **Añadir material** → **Completar**
5. Carpeta **99 - Pruebas negativas** — valida 401, validación y DELETE 404

También puedes usar **Run collection**; ejecuta primero **Login**. Los DELETE usan el ID guardado por el POST de su carpeta.

## Variables de entorno

| Variable | Descripción |
|----------|-------------|
| `baseUrl` | URL del servidor Symfony |
| `login` / `password` | Credenciales para `/api/login` |
| `jwt` | Token Bearer (se rellena tras login) |
| `patientId`, `dentistId`, `boxId`, … | IDs usados en rutas y bodies |

Los scripts de test guardan IDs cuando la respuesta incluye `id` o el primer elemento de `items[]`.

## Informe / memoria

Usa `PRUEBAS-API-RESUMEN.md` como plantilla para documentar resultados, incidencias y conclusiones. Las capturas de Postman Runner o de peticiones individuales van en el anexo.

## Pruebas de estrés en BBDD

```bash
php bin/console app:seed-stress --patients=1000 --appointments=3000
node postman/benchmark-api-stress.mjs
```

- No borra usuarios ni `testuser`.
- Al final imprime tiempos de consultas de referencia.
- `benchmark-api-stress.mjs` mide E-01…E-19 (19 endpoints, 3 repeticiones, media) sin Postman.
- Plantilla de informe: `PRUEBAS-ESTRES-RESUMEN.md`.

## Notas

- Las peticiones **POST/PUT/DELETE** de creación pueden modificar datos en tu BBDD de desarrollo.
- `app:seed` borra usuarios y pacientes; si lo usas, vuelve a ejecutar `app:create-test-user`.
- Radiografías (upload multipart) no están incluidas en esta colección base; se pueden añadir manualmente.
