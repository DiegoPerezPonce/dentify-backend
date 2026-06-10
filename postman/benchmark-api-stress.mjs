#!/usr/bin/env node
/**
 * Benchmark API para pruebas de estrés (E-01 … E-19).
 * Uso: node postman/benchmark-api-stress.mjs
 * Opciones: BENCHMARK_RUNS=3 API_BASE_URL=...
 */
const BASE = process.env.API_BASE_URL ?? 'http://127.0.0.1:8000';
const LOGIN = process.env.API_LOGIN ?? 'testuser';
const PASSWORD = process.env.API_PASSWORD ?? '123456';
const RUNS = Number(process.env.BENCHMARK_RUNS ?? 3);
const WARMUP = process.env.BENCHMARK_SKIP_WARMUP !== '1';

async function login() {
  const res = await fetch(`${BASE}/api/login`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
    body: JSON.stringify({ login: LOGIN, password: PASSWORD }),
  });
  const json = await res.json();
  if (res.status !== 200 || !json?.token) {
    throw new Error(`Login falló (${res.status}): ${JSON.stringify(json)}`);
  }
  return json.token;
}

async function timedGet(token, path) {
  const start = performance.now();
  const res = await fetch(`${BASE}${path}`, {
    headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' },
  });
  const text = await res.text();
  const ms = performance.now() - start;
  let json = null;
  try {
    json = text ? JSON.parse(text) : null;
  } catch {
    json = null;
  }
  const totalHeader = res.headers.get('X-Total-Count');
  return {
    status: res.status,
    ms,
    json,
    totalHeader: totalHeader != null ? Number(totalHeader) : null,
  };
}

function avg(values) {
  if (values.length === 0) return 0;
  return values.reduce((a, b) => a + b, 0) / values.length;
}

function fmtMs(n) {
  return `${Math.round(n)} ms`;
}

function countItems(json) {
  if (json && Array.isArray(json.items)) return json.items.length;
  if (Array.isArray(json)) return json.length;
  return null;
}

function describeResponse(status, json, totalHeader) {
  if (status !== 200) return `HTTP ${status}`;
  const items = countItems(json);
  const total = json?.total ?? totalHeader;
  if (total != null && items != null) return `${items} items, total ${total}`;
  if (items != null) return `${items} items`;
  if (json?.dentistIds) return `${json.dentistIds.length} dentistIds`;
  if (json?.boxIds) return `${json.boxIds.length} boxIds`;
  if (json?.id != null) return `id ${json.id}`;
  return 'OK';
}

async function discoverIds(token) {
  const patients = await timedGet(token, '/api/patients?page=1&itemsPerPage=20');
  if (patients.status !== 200) {
    throw new Error(`No se pudo listar pacientes (${patients.status})`);
  }
  const patientId = patients.json?.items?.[0]?.id ?? null;
  const patientTotal = patients.json?.total ?? 0;
  const lastPage = Math.max(1, Math.ceil(patientTotal / 20));

  const dentists = await timedGet(token, '/api/dentists');
  const dentistId = Array.isArray(dentists.json) && dentists.json[0]?.id ? dentists.json[0].id : 1;

  return {
    patientId: patientId ?? 1,
    patientTotal,
    lastPage,
    dentistId,
    scheduleSlot: '2026-06-15T11:00:00',
  };
}

function buildEndpoints(ctx) {
  const { patientId, lastPage, dentistId, scheduleSlot } = ctx;
  const encSlot = encodeURIComponent(scheduleSlot);

  return [
    { group: 'Pacientes', id: 'E-01', label: 'Listado pág. 1', path: '/api/patients?page=1&itemsPerPage=20' },
    { group: 'Pacientes', id: 'E-02', label: 'Búsqueda Garcia', path: '/api/patients?page=1&itemsPerPage=20&search=Garcia' },
    {
      group: 'Pacientes',
      id: 'E-05',
      label: `Listado pág. ${lastPage} (profunda)`,
      path: `/api/patients?page=${lastPage}&itemsPerPage=20`,
    },
    { group: 'Pacientes', id: 'E-06', label: 'Paciente por ID', path: `/api/patients/${patientId}` },
    { group: 'Pacientes', id: 'E-07', label: 'Citas del paciente', path: `/api/patients/${patientId}/appointments` },
    { group: 'Pacientes', id: 'E-08', label: 'Historial clínico', path: `/api/patients/${patientId}/history` },
    {
      group: 'Citas / agenda',
      id: 'E-03',
      label: 'Citas año 2025',
      path: '/api/appointments?startDate=2025-01-01&endDate=2025-12-31&page=1&itemsPerPage=20',
    },
    {
      group: 'Citas / agenda',
      id: 'E-09',
      label: 'Citas un día',
      path: '/api/appointments?fecha=2025-06-15&page=1&itemsPerPage=20',
    },
    {
      group: 'Citas / agenda',
      id: 'E-10',
      label: 'Citas por odontólogo',
      path: `/api/appointments?dentistId=${dentistId}&startDate=2025-01-01&endDate=2025-12-31&page=1&itemsPerPage=20`,
    },
    {
      group: 'Citas / agenda',
      id: 'E-11',
      label: 'Odontólogos disponibles',
      path: `/api/schedule/dentists-available?startDateTime=${encSlot}&duration=30`,
    },
    {
      group: 'Citas / agenda',
      id: 'E-12',
      label: 'Cajas disponibles',
      path: `/api/schedule/boxes-available?startDateTime=${encSlot}&duration=30`,
    },
    {
      group: 'Stock / catálogo',
      id: 'E-04',
      label: 'Stock materiales',
      path: '/api/stock-materials?page=1&itemsPerPage=20',
    },
    { group: 'Stock / catálogo', id: 'E-13', label: 'Reposiciones stock', path: '/api/stock-restocks' },
    { group: 'Stock / catálogo', id: 'E-14', label: 'Odontólogos', path: '/api/dentists' },
    { group: 'Stock / catálogo', id: 'E-15', label: 'Cajas', path: '/api/boxes' },
    { group: 'Stock / catálogo', id: 'E-16', label: 'Protocolos', path: '/api/protocolos' },
    { group: 'Stock / catálogo', id: 'E-17', label: 'Categorías tratamiento', path: '/api/treatment-categories' },
    { group: 'Stock / catálogo', id: 'E-18', label: 'Usuarios', path: '/api/users' },
    {
      group: 'Stock / catálogo',
      id: 'E-19',
      label: 'Avisos pedagógicos',
      path: '/api/pedagogical-notices?page=1&itemsPerPage=20',
    },
  ];
}

async function benchmarkEndpoint(token, ep) {
  const times = [];
  let lastStatus = 0;
  let lastDetail = '';

  for (let i = 0; i < RUNS; i++) {
    const { status, ms, json, totalHeader } = await timedGet(token, ep.path);
    times.push(ms);
    lastStatus = status;
    lastDetail = describeResponse(status, json, totalHeader);
  }

  return {
    ...ep,
    runs: times.map((t) => Math.round(t)),
    mean: avg(times),
    status: lastStatus,
    detail: lastDetail,
  };
}

async function main() {
  console.log(`Benchmark API estrés — ${BASE}`);
  console.log(`Repeticiones por endpoint: ${RUNS}\n`);

  const token = await login();
  console.log('Login OK');

  const ctx = await discoverIds(token);
  console.log(
    `Contexto: ${ctx.patientTotal} pacientes · patientId=${ctx.patientId} · dentistId=${ctx.dentistId} · última pág.=${ctx.lastPage}\n`,
  );

  const endpoints = buildEndpoints(ctx);

  if (WARMUP) {
    await timedGet(token, '/api/patients?page=1&itemsPerPage=20');
    console.log('Calentamiento OK\n');
  }

  const rows = [];
  let currentGroup = '';

  for (const ep of endpoints) {
    if (ep.group !== currentGroup) {
      currentGroup = ep.group;
      console.log(`\n## ${currentGroup}`);
    }
    const row = await benchmarkEndpoint(token, ep);
    rows.push(row);
    console.log(
      `${row.id}  ${row.label}\n` +
        `     ${row.runs.map((t) => fmtMs(t)).join(' · ')}  →  media ${fmtMs(row.mean)}  [${row.detail}]`,
    );
  }

  const errors = rows.filter((r) => r.status !== 200);
  console.log('\n--- Tabla para informe ---\n');
  console.log('| ID | Endpoint | Runs (ms) | Media (ms) | HTTP | Detalle |');
  console.log('|----|----------|-----------|------------|------|---------|');
  for (const r of rows) {
    console.log(
      `| ${r.id} | ${r.label} | ${r.runs.join(', ')} | ${Math.round(r.mean)} | ${r.status} | ${r.detail} |`,
    );
  }

  console.log(`\nTotal endpoints: ${rows.length} · Errores (≠200): ${errors.length}`);
  if (errors.length > 0) {
    errors.forEach((e) => console.log(`  ! ${e.id} ${e.label} → ${e.status}`));
    process.exit(1);
  }
}

main().catch((err) => {
  console.error(err.message ?? err);
  process.exit(1);
});
