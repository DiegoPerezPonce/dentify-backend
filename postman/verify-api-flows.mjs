#!/usr/bin/env node
/**
 * Verifica flujos CRUD contra la API local.
 * Uso: node postman/verify-api-flows.mjs
 */
const BASE = process.env.API_BASE_URL ?? 'http://127.0.0.1:8000';
const LOGIN = process.env.API_LOGIN ?? 'testuser';
const PASSWORD = process.env.API_PASSWORD ?? '123456';

const results = [];

function ok(name, detail = '') {
  results.push({ name, pass: true, detail });
  console.log(`  OK  ${name}${detail ? ` — ${detail}` : ''}`);
}

function fail(name, detail = '') {
  results.push({ name, pass: false, detail });
  console.log(`FAIL  ${name}${detail ? ` — ${detail}` : ''}`);
}

async function request(method, path, { token, body } = {}) {
  const headers = { Accept: 'application/json' };
  if (token) headers.Authorization = `Bearer ${token}`;
  if (body !== undefined) {
    headers['Content-Type'] = 'application/json';
  }
  const res = await fetch(`${BASE}${path}`, {
    method,
    headers,
    body: body !== undefined ? JSON.stringify(body) : undefined,
  });
  const text = await res.text();
  let json = null;
  try {
    json = text ? JSON.parse(text) : null;
  } catch {
    json = null;
  }
  return { status: res.status, json, text };
}

function firstId(json) {
  if (json && Array.isArray(json.items) && json.items[0]?.id) return json.items[0].id;
  if (Array.isArray(json) && json[0]?.id) return json[0].id;
  return json?.id ?? null;
}

async function main() {
  console.log(`Verificando API en ${BASE}\n`);
  const u = Date.now();

  const login = await request('POST', '/api/login', {
    body: { login: LOGIN, password: PASSWORD },
  });
  if (login.status !== 200 || !login.json?.token) {
    fail('Login', `status ${login.status}`);
    printSummary();
    process.exit(1);
  }
  ok('Login');
  const token = login.json.token;

  // Setup IDs (plain arrays + paginated)
  const patients = await request('GET', '/api/patients?page=1&itemsPerPage=10', { token });
  const dentists = await request('GET', '/api/dentists', { token });
  const boxes = await request('GET', '/api/boxes', { token });
  const stock = await request('GET', '/api/stock-materials?page=1&itemsPerPage=10', { token });
  const categories = await request('GET', '/api/treatment-categories', { token });
  const specialties = await request('GET', '/api/specialties', { token });

  let patientId = firstId(patients.json);
  const dentistId = firstId(dentists.json);
  const boxId = firstId(boxes.json);
  const stockMaterialId = firstId(stock.json);
  const treatmentCategoryId = firstId(categories.json);
  const specialtyId = firstId(specialties.json);

  if (!dentistId || !boxId || !stockMaterialId) {
    fail('Setup IDs', 'Faltan dentist/box/stock en BBDD');
  } else {
    ok('Setup IDs', `dentist=${dentistId} box=${boxId} stock=${stockMaterialId}`);
  }

  // Paciente (para cita y sesión; se borra al final)
  const patient = await request('POST', '/api/patients', {
    token,
    body: {
      nombre: 'Verify',
      apellidos: 'Paciente',
      dni: String(u).slice(-8) + 'Z',
      email: `p.${u}@dentify.test`,
      terminos_generales_aceptados: true,
    },
  });
  if (patient.status === 201) {
    patientId = patient.json.id;
    ok('POST Paciente', `id=${patientId}`);
  } else {
    fail('POST Paciente', String(patient.status));
  }

  // Cita
  const tomorrow = new Date();
  tomorrow.setDate(tomorrow.getDate() + 1);
  const fecha = tomorrow.toISOString().slice(0, 10);
  const apt = await request('POST', '/api/appointments', {
    token,
    body: {
      pacienteId: patientId ?? firstId(patients.json),
      odontologoId: dentistId,
      boxId: boxId,
      fecha,
      hora_inicio: '11:00:00',
      duracion: 30,
      tiempo_limpieza: 5,
      prioridad: 'media',
      estado: 'programada',
      appointment_kind: 'tratamiento',
    },
  });
  let appointmentId = apt.json?.id;
  if (apt.status === 201 && appointmentId) {
    ok('POST Cita', `id=${appointmentId}`);
  } else {
    fail('POST Cita', `status ${apt.status} ${apt.json?.message ?? apt.text?.slice(0, 120) ?? ''}`);
  }

  // Sesión
  if (appointmentId) {
    const start = await request('POST', `/api/appointments/${appointmentId}/session/start`, { token });
    start.status === 201 ? ok('POST Iniciar sesión') : fail('POST Iniciar sesión', String(start.status));
    const getS = await request('GET', `/api/appointments/${appointmentId}/session`, { token });
    getS.status === 200 ? ok('GET Sesión') : fail('GET Sesión', String(getS.status));
    const addM = await request('POST', `/api/appointments/${appointmentId}/session/materials`, {
      token,
      body: { stock_material_id: stockMaterialId, cantidad_usada: 1 },
    });
    addM.status === 200 ? ok('POST Añadir material') : fail('POST Añadir material', String(addM.status));
    const complete = await request('POST', `/api/appointments/${appointmentId}/session/complete`, { token });
    complete.status === 200 ? ok('POST Completar sesión') : fail('POST Completar sesión', String(complete.status));
    const delA = await request('DELETE', `/api/appointments/${appointmentId}`, { token });
    delA.status === 204 ? ok('DELETE Cita') : fail('DELETE Cita', String(delA.status));
  }

  if (patientId) {
    const delP = await request('DELETE', `/api/patients/${patientId}`, { token });
    delP.status === 204 ? ok('DELETE Paciente') : fail('DELETE Paciente', String(delP.status));
  }

  // Odontólogo
  const dentist = await request('POST', '/api/dentists', {
    token,
    body: {
      nombre: 'Verify',
      apellidos: 'Dentist',
      especialidad: 'General',
      email: `d.${u}@dentify.test`,
    },
  });
  if (dentist.status === 201) {
    ok('POST Odontólogo', `id=${dentist.json.id}`);
    const delD = await request('DELETE', `/api/dentists/${dentist.json.id}`, { token });
    delD.status === 204 ? ok('DELETE Odontólogo') : fail('DELETE Odontólogo', String(delD.status));
  } else fail('POST Odontólogo', String(dentist.status));

  // Caja
  const box = await request('POST', '/api/boxes', {
    token,
    body: { nombre: `Box ${u}`, descripcion: 'test', estado: 'disponible' },
  });
  if (box.status === 201) {
    ok('POST Caja', `id=${box.json.id}`);
    const delB = await request('DELETE', `/api/boxes/${box.json.id}`, { token });
    delB.status === 204 ? ok('DELETE Caja') : fail('DELETE Caja', String(delB.status));
  } else fail('POST Caja', String(box.status));

  // Stock
  const material = await request('POST', '/api/stock-materials', {
    token,
    body: { nombre: `Mat ${u}`, cantidad_actual: 50, unidad: 'uds', umbral_minimo: 5 },
  });
  if (material.status === 201) {
    ok('POST Material', `id=${material.json.id}`);
    const proto = await request('POST', '/api/protocolos', {
      token,
      body: {
        nombre_tratamiento: `Trat ${u}`,
        material_id: material.json.id,
        cantidad_necesaria: 1,
      },
    });
    if (proto.status === 201) {
      ok('POST Protocolo', `id=${proto.json.id}`);
      const delPr = await request('DELETE', `/api/protocolos/${proto.json.id}`, { token });
      delPr.status === 204 ? ok('DELETE Protocolo') : fail('DELETE Protocolo', String(delPr.status));
    } else fail('POST Protocolo', String(proto.status));
    const delM = await request('DELETE', `/api/stock-materials/${material.json.id}`, { token });
    delM.status === 204 ? ok('DELETE Material') : fail('DELETE Material', String(delM.status));
  } else fail('POST Material', String(material.status));

  // Usuario
  const user = await request('POST', '/api/users', {
    token,
    body: {
      email: `u.${u}@dentify.test`,
      password: '123456',
      nombre_usuario: `user_${u}`,
      roles: ['ROLE_USER'],
      dentistNombre: 'Verify',
      dentistApellidos: 'User',
      dentistEspecialidad: 'General',
    },
  });
  if (user.status === 201) {
    ok('POST Usuario', `id=${user.json.id}`);
    const getU = await request('GET', `/api/users/${user.json.id}`, { token });
    getU.status === 200 ? ok('GET Usuario por ID') : fail('GET Usuario por ID', String(getU.status));
    const delU = await request('DELETE', `/api/users/${user.json.id}`, { token });
    delU.status === 204 ? ok('DELETE Usuario') : fail('DELETE Usuario', String(delU.status));
  } else fail('POST Usuario', `status ${user.status}`);

  // Agenda
  const startDt = `${fecha}T11:00:00`;
  const availD = await request('GET', `/api/schedule/dentists-available?startDateTime=${encodeURIComponent(startDt)}&duration=30`, { token });
  availD.status === 200 ? ok('GET Odontólogos disponibles') : fail('GET Odontólogos disponibles', String(availD.status));
  const availB = await request('GET', `/api/schedule/boxes-available?startDateTime=${encodeURIComponent(startDt)}&duration=30`, { token });
  availB.status === 200 ? ok('GET Cajas disponibles') : fail('GET Cajas disponibles', String(availB.status));
  const weekly = await request('GET', `/api/schedule/dentists/${dentistId}/weekly`, { token });
  weekly.status === 200 ? ok('GET Horario semanal odontólogo') : fail('GET Horario semanal', String(weekly.status));
  const meWeekly = await request('GET', '/api/schedule/me/weekly', { token });
  meWeekly.status === 403 ? ok('GET Mi horario (403 testuser esperado)') : fail('GET Mi horario', `status ${meWeekly.status}`);

  // Negativas
  const noAuth = await request('GET', '/api/patients');
  noAuth.status === 401 ? ok('GET sin token → 401') : fail('GET sin token', String(noAuth.status));
  const badPatient = await request('POST', '/api/patients', { token, body: { nombre: '', apellidos: '', dni: '' } });
  [400, 422].includes(badPatient.status) ? ok('POST paciente inválido → 4xx') : fail('POST paciente inválido', String(badPatient.status));

  printSummary();
  process.exit(results.some((r) => !r.pass) ? 1 : 0);
}

function printSummary() {
  const passed = results.filter((r) => r.pass).length;
  const total = results.length;
  console.log(`\n${passed}/${total} pruebas OK`);
  if (passed < total) {
    console.log('\nFallidas:');
    results.filter((r) => !r.pass).forEach((r) => console.log(`  - ${r.name}: ${r.detail}`));
  }
}

main().catch((e) => {
  console.error(e);
  process.exit(1);
});
