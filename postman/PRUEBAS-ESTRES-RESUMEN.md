# Resumen de pruebas de estrés en BBDD — Dentify

**Entorno:** desarrollo · MariaDB `dentify_db` · API `http://127.0.0.1:8000`  
**Fecha:** junio 2026  
**Herramientas:** `app:seed-stress`, `benchmark-api-stress.mjs`

## Preparación

- [x] Symfony en marcha: `symfony server:start`
- [x] Usuario de prueba: `php bin/console app:create-test-user`
- [x] Carga incremental sin borrar datos existentes
- [x] **No** ejecutado en producción

## Carga ejecutada (acumulada)

| Fase | Comando | Δ pacientes | Δ citas | Total BBDD |
|------|---------|-------------|---------|------------|
| 1 | `--patients=100 --appointments=300` | +100 | +300 | 110 / 305 |
| 2 | `--patients=1000 --appointments=3000` | +1000 | +3000 | 1 110 / 3 305 |
| 3 | `--patients=5000 --appointments=15000` | +5000 | +15000 | **6 110 / 18 305** |

Última ejecución: `php bin/console app:seed-stress --patients=5000 --appointments=15000`  
Inserción fase 3: **2,23 s** pacientes · **6,98 s** citas · batch 50

## Conteo de tablas (SQL)

```sql
SELECT 'patient' AS tabla, COUNT(*) AS filas FROM patient
UNION ALL SELECT 'appointment', COUNT(*) FROM appointment;
```

| Tabla | Tras fase 1 | Tras fase 2 | **Tras fase 3** |
|-------|-------------|-------------|-----------------|
| patient | 110 | 1 110 | **6 110** |
| appointment | 305 | 3 305 | **18 305** |

## Benchmark del comando (consultas ORM/SQL)

| Consulta | ~110 pac. | ~1 110 pac. | **~6 110 pac.** |
|----------|-----------|-------------|-----------------|
| SQL COUNT patient | 0,77 ms | 0,73 ms | **1,42 ms** |
| SQL LIKE patient (gar) | 2,64 ms | 0,60 ms | **0,91 ms** |
| ORM pacientes pág. 1 | 43 ms | 20 ms | **23 ms** |
| ORM pacientes search=garcia | 13 ms | 11 ms (96) | **22 ms** (531) |
| ORM citas 2025 (50 filas) | 31 ms | 29 ms | **76 ms** |

La paginación (`LIMIT 20`) mantiene el listado de pacientes estable. Las citas por año muestran mayor coste al crecer el volumen total.

## Tiempos API (`node postman/benchmark-api-stress.mjs`)

Medición: **3 repeticiones**, media, servidor caliente. Script ampliado: **19 endpoints** (E-01 … E-19).

### Evolución (endpoints originales)

| ID | Endpoint | ~110 pac. | ~1 110 pac. | **~6 110 pac.** |
|----|----------|-----------|-------------|-----------------|
| E-01 | Listado pacientes pág. 1 | 317 ms | 472 ms | **319 ms** |
| E-02 | Búsqueda Garcia | 308 ms | 470 ms | **323 ms** |
| E-03 | Citas año 2025 | 327 ms | 492 ms | **367 ms** |
| E-04 | Stock materiales | 311 ms | 460 ms | **309 ms** |

### Resultados completos — fase 3 (6 110 pacientes / 18 305 citas)

| ID | Endpoint | Runs (ms) | Media | HTTP |
|----|----------|-----------|-------|------|
| E-01 | Listado pág. 1 | 315, 345, 296 | **319** | 200 |
| E-02 | Búsqueda Garcia | 321, 330, 319 | **323** | 200 |
| E-05 | Listado pág. 306 (profunda) | 335, 322, 329 | **329** | 200 |
| E-06 | Paciente por ID | 281, 309, 290 | **294** | 200 |
| E-07 | Citas del paciente | 376, 376, 410 | **387** | 200 |
| E-08 | Historial clínico | 374, 300, 301 | **325** | 200 |
| E-03 | Citas año 2025 | 367, 367, 368 | **367** | 200 |
| E-09 | Citas un día (2025-06-15) | 336, 349, 335 | **340** | 200 |
| E-10 | Citas por odontólogo | 342, 340, 326 | **336** | 200 |
| E-11 | Odontólogos disponibles | 359, 323, 302 | **328** | 200 |
| E-12 | Cajas disponibles | 340, 320, 349 | **336** | 200 |
| E-04 | Stock materiales | 294, 314, 319 | **309** | 200 |
| E-13 | Reposiciones stock | 331, 363, 354 | **349** | 200 |
| E-14 | Odontólogos | 358, 285, 294 | **312** | 200 |
| E-15 | Cajas | 289, 284, 281 | **284** | 200 |
| E-16 | Protocolos | 311, 381, 383 | **358** | 200 |
| E-17 | Categorías tratamiento | 380, 298, 328 | **335** | 200 |
| E-18 | Usuarios | 305, 382, 382 | **356** | 200 |
| E-19 | Avisos pedagógicos | 364, 311, 305 | **327** | 200 |

**Errores 500:** 0 · **Todos HTTP 200**

**Criterios:** listados &lt; 500 ms aceptable; &gt; 2 s degradado.

## Conclusiones

1. **Volumen probado:** 6 110 pacientes y 18 305 citas sintéticas en `dentify_db` (desarrollo).
2. **Listados paginados:** E-01 y E-05 (~319–329 ms) se mantienen por debajo de 500 ms gracias a `page` + `itemsPerPage`, incluso en la página 306.
3. **Búsqueda LIKE:** E-02 devuelve 531 coincidencias para «Garcia» con media **323 ms**; aceptable en desarrollo.
4. **Agenda / disponibilidad:** E-11 y E-12 (~328–336 ms) escalan bien con 18k citas; no son el cuello de botella.
5. **Módulos de catálogo** (stock, protocolos, usuarios): tiempos estables (~280–360 ms), independientes del volumen de pacientes.
6. **Errores:** ningún 500 en las 19 peticiones medidas.
7. **Mejoras recomendadas (producción):** índices en `appointment.fecha` y `patient.dni`; búsqueda full-text; caché de catálogo; no usar OFFSET profundo sin keyset pagination.

## Nota metodológica (IA)

> Se utilizó el comando Symfony `app:seed-stress` y el script `benchmark-api-stress.mjs`, generados con asistencia de IA. Los datos son sintéticos (dominio `@dentify-stress.test`) y solo se usaron en desarrollo local.

## Comandos de reproducción

```bash
php bin/console app:seed-stress --patients=5000 --appointments=15000
node postman/benchmark-api-stress.mjs
```

Salida guardada en: `postman/benchmark-results-latest.txt`

## Restaurar entorno ligero (opcional)

```bash
php bin/console app:seed
php bin/console app:create-test-user
```

⚠️ `app:seed` **borra** pacientes, citas y usuarios (recrea `testuser`).
