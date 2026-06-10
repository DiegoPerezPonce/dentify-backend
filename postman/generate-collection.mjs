#!/usr/bin/env node
/**
 * Genera Dentify-API.postman_collection.json
 * Ejecutar: node postman/generate-collection.mjs
 */
import { writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = dirname(fileURLToPath(import.meta.url));

const saveIdScript = `
if (pm.response.code === 200 || pm.response.code === 201) {
  try {
    const json = pm.response.json();
    if (json.id != null) {
      const varName = pm.variables.get('saveIdAs');
      if (varName) pm.environment.set(varName, String(json.id));
    }
    const listVar = pm.variables.get('saveFirstItemIdAs');
    if (listVar) {
      if (Array.isArray(json.items) && json.items.length > 0 && json.items[0].id != null) {
        pm.environment.set(listVar, String(json.items[0].id));
      } else if (Array.isArray(json) && json.length > 0 && json[0].id != null) {
        pm.environment.set(listVar, String(json[0].id));
      }
    }
  } catch (e) {}
}
`.trim();

const loginTestScript = `
pm.test('Status 200', () => pm.response.to.have.status(200));
pm.test('Token presente', () => {
  const json = pm.response.json();
  pm.expect(json.token).to.be.a('string').and.not.empty;
  pm.environment.set('jwt', json.token);
});
`.trim();

const authTestScript = `
pm.test('Status 200', () => pm.response.to.have.status(200));
${saveIdScript}
`.trim();

const createdTestScript = `
pm.test('Status 201', () => pm.response.to.have.status(201));
${saveIdScript}
`.trim();

const unauthorizedTestScript = `
pm.test('Status 401 sin token', () => pm.response.to.have.status(401));
`.trim();

const validationErrorScript = `
pm.test('Error de validación (4xx)', () => {
  pm.expect(pm.response.code).to.be.oneOf([400, 422]);
});
`.trim();

const deleteLenientTestScript = `
pm.test('Status 204 o 404 (ya eliminado)', () => {
  pm.expect(pm.response.code).to.be.oneOf([204, 404]);
});
`.trim();

const saveSessionMaterialFromResponseScript = `
pm.test('Status 200', () => pm.response.to.have.status(200));
try {
  const json = pm.response.json();
  if (Array.isArray(json.materials) && json.materials.length > 0) {
    const last = json.materials[json.materials.length - 1];
    if (last.id != null) pm.environment.set('sessionMaterialLineId', String(last.id));
  }
} catch (e) {}
`.trim();

const sessionStartTestScript = `
pm.test('Status 201', () => pm.response.to.have.status(201));
pm.test('Sesión in_progress', () => {
  const json = pm.response.json();
  pm.expect(json.status).to.eql('in_progress');
});
`.trim();

const sessionGetTestScript = `
pm.test('Status 200', () => pm.response.to.have.status(200));
pm.test('Sesión con materiales array', () => {
  const json = pm.response.json();
  pm.expect(json.materials).to.be.an('array');
});
`.trim();

const sessionCompleteTestScript = `
pm.test('Status 200', () => pm.response.to.have.status(200));
pm.test('Sesión completada', () => {
  const json = pm.response.json();
  pm.expect(json.status).to.eql('completed');
});
`.trim();

function citaPrerequisitoRequests() {
  return [
    requestWithPrerequests(
      'POST Crear paciente (prerequisito)',
      'POST',
      '/api/patients',
      {
        body: `{
  "nombre": "Paciente",
  "apellidos": "Prueba API",
  "dni": "{{citaPatientDni}}",
  "telefono": "612345678",
  "email": "paciente.{{uniqueSuffix}}@dentify.test",
  "terminos_generales_aceptados": true
}`,
        tests: createdTestScript,
        saveIdAs: 'patientId',
        description: 'Crea paciente con DNI único. Necesario si patientId no existe.',
      },
      [prerequestUniqueSuffix()]
    ),
    requestWithPrerequests(
      'POST Crear cita (prerequisito)',
      'POST',
      '/api/appointments',
      {
        body: `{
  "pacienteId": {{patientId}},
  "odontologoId": {{dentistId}},
  "boxId": {{boxId}},
  "fecha": "{{citaFecha}}",
  "hora_inicio": "{{citaHora}}",
  "duracion": 30,
  "tiempo_limpieza": 5,
  "prioridad": "media",
  "estado": "programada",
  "appointment_kind": "tratamiento"
}`,
        tests: createdTestScript,
        saveIdAs: 'appointmentId',
        description: 'Crea cita programada. Guarda appointmentId para la sesión.',
      },
      [
        prerequestRequireEnvVars(
          ['patientId', 'dentistId', 'boxId'],
          'Ejecuta 01 - Setup (odontólogo/caja) antes.'
        ),
        prerequestCitaDateTime(),
      ]
    ),
  ];
}

function prerequestRequireEnvVars(varNames, hint) {
  return {
    listen: 'prerequest',
    script: {
      type: 'text/javascript',
      exec: [
        `const required = ${JSON.stringify(varNames)};`,
        'required.forEach((key) => {',
        '  const v = pm.environment.get(key);',
        "  if (v === undefined || v === null || String(v).trim() === '') {",
        `    throw new Error('${hint.replace(/'/g, "\\'")} Variable vacía: ' + key);`,
        '  }',
        '});',
      ],
    },
  };
}

function prerequestUniqueSuffix() {
  return {
    script: {
      exec: [
        "const u = String(Date.now());",
        "pm.environment.set('uniqueSuffix', u);",
        "pm.environment.set('citaPatientDni', u.slice(-8) + 'Z');",
      ],
    },
  };
}

function prerequestCitaDateTime() {
  return {
    listen: 'prerequest',
    script: {
      type: 'text/javascript',
      exec: [
        'const d = new Date();',
        'd.setDate(d.getDate() + 1);',
        'd.setHours(11, 0, 0, 0);',
        "pm.environment.set('citaFecha', d.toISOString().slice(0, 10));",
        "pm.environment.set('citaHora', '11:00:00');",
        "pm.environment.set('citaStartDateTime', pm.environment.get('citaFecha') + 'T11:00:00');",
      ],
    },
  };
}

function requestWithPrerequests(name, method, path, opts, extraPrerequests = []) {
  const item = request(name, method, path, opts);
  const existing = item.event.find((e) => e.listen === 'prerequest');
  const scripts = extraPrerequests.flatMap((p) => p.script.exec);
  if (existing) {
    existing.script.exec = [...scripts, ...existing.script.exec];
  } else if (scripts.length > 0) {
    item.event.unshift({ listen: 'prerequest', script: { type: 'text/javascript', exec: scripts } });
  }
  return item;
}

function deleteRequest(label, path, extraDescription = '') {
  return request(`DELETE ${label}`, 'DELETE', path, {
    tests: deleteLenientTestScript,
    description:
      (extraDescription ? extraDescription + ' ' : '') +
      'Ejecutar al final de la carpeta. Usa el ID de POST Crear de esta misma carpeta (variable de entorno).',
  });
}

function testEvent(script) {
  return [{ listen: 'test', script: { type: 'text/javascript', exec: script.split('\n') } }];
}

function request(name, method, path, opts = {}) {
  const {
    body = null,
    query = [],
    auth = 'inherit',
    tests = authTestScript,
    description = '',
    saveIdAs = null,
    saveFirstItemIdAs = null,
  } = opts;

  const url = {
    raw: `{{baseUrl}}${path}${query.length ? '?' + query.map((q) => `${q.key}=${q.value}`).join('&') : ''}`,
    host: ['{{baseUrl}}'],
    path: path.replace(/^\//, '').split('/'),
  };
  if (query.length) {
    url.query = query.map((q) => ({ key: q.key, value: q.value, disabled: q.disabled ?? false }));
  }

  const item = {
    name,
    request: {
      method,
      header: [{ key: 'Content-Type', value: 'application/json' }],
      url,
      description,
    },
    response: [],
    event: testEvent(tests),
  };

  if (body !== null) {
    item.request.header = [{ key: 'Content-Type', value: 'application/json' }];
    item.request.body = {
      mode: 'raw',
      raw: typeof body === 'string' ? body : JSON.stringify(body, null, 2),
    };
  } else if (['POST', 'PUT', 'PATCH'].includes(method)) {
    item.request.header = [];
  }

  if (auth === 'none') {
    item.request.auth = { type: 'noauth' };
  }

  if (saveIdAs || saveFirstItemIdAs) {
    item.event.unshift({
      listen: 'prerequest',
      script: {
        type: 'text/javascript',
        exec: [
          saveIdAs ? `pm.variables.set('saveIdAs', '${saveIdAs}');` : `pm.variables.unset('saveIdAs');`,
          saveFirstItemIdAs
            ? `pm.variables.set('saveFirstItemIdAs', '${saveFirstItemIdAs}');`
            : `pm.variables.unset('saveFirstItemIdAs');`,
        ].filter(Boolean),
      },
    });
  }

  return item;
}

function folder(name, description, items) {
  return { name, description, item: items };
}

const collection = {
  info: {
    name: 'Dentify API',
    description:
      'Batería de pruebas funcionales de la API REST Dentify (Symfony).\n\n' +
      '1. Importar entorno **Dentify Local**\n' +
      '2. Ejecutar **Login (éxito)**\n' +
      '3. Ejecutar **Setup - Obtener IDs** o Run collection',
    schema: 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
  },
  auth: {
    type: 'bearer',
    bearer: [{ key: 'token', value: '{{jwt}}', type: 'string' }],
  },
  variable: [{ key: 'baseUrl', value: 'http://127.0.0.1:8000' }],
  item: [
    folder('00 - Auth', 'Autenticación JWT', [
      request('Login (éxito)', 'POST', '/api/login', {
        auth: 'none',
        body: { login: '{{login}}', password: '{{password}}' },
        tests: loginTestScript,
        description: 'Obtiene JWT y lo guarda en la variable `jwt`.',
      }),
      request('Login (credenciales inválidas)', 'POST', '/api/login', {
        auth: 'none',
        body: { login: 'noexiste', password: 'wrong' },
        tests: `pm.test('Status 401', () => pm.response.to.have.status(401));`,
      }),
    ]),
    folder('01 - Setup (IDs)', 'Rellena variables de entorno desde listados', [
      request('Listar pacientes → patientId', 'GET', '/api/patients', {
        query: [
          { key: 'page', value: '1' },
          { key: 'itemsPerPage', value: '10' },
        ],
        saveFirstItemIdAs: 'patientId',
      }),
      request('Listar odontólogos → dentistId', 'GET', '/api/dentists', {
        query: [
          { key: 'page', value: '1' },
          { key: 'itemsPerPage', value: '10' },
        ],
        saveFirstItemIdAs: 'dentistId',
      }),
      request('Listar cajas → boxId', 'GET', '/api/boxes', {
        saveFirstItemIdAs: 'boxId',
      }),
      request('Listar citas → appointmentId', 'GET', '/api/appointments', {
        query: [
          { key: 'startDate', value: '2026-01-01' },
          { key: 'endDate', value: '2026-12-31' },
        ],
        saveFirstItemIdAs: 'appointmentId',
      }),
      request('Listar materiales → stockMaterialId', 'GET', '/api/stock-materials', {
        query: [
          { key: 'page', value: '1' },
          { key: 'itemsPerPage', value: '10' },
        ],
        saveFirstItemIdAs: 'stockMaterialId',
      }),
      request('Listar categorías → treatmentCategoryId', 'GET', '/api/treatment-categories', {
        saveFirstItemIdAs: 'treatmentCategoryId',
      }),
      request('Listar especialidades → specialtyId', 'GET', '/api/specialties', {
        saveFirstItemIdAs: 'specialtyId',
      }),
    ]),
    folder('02 - Pacientes', 'CRUD y recursos anidados', [
      request('GET Listar (paginado)', 'GET', '/api/patients', {
        query: [
          { key: 'page', value: '1' },
          { key: 'itemsPerPage', value: '10' },
          { key: 'search', value: '', disabled: true },
        ],
      }),
      request('GET Por ID', 'GET', '/api/patients/{{patientId}}'),
      request('GET Historial clínico', 'GET', '/api/patients/{{patientId}}/history'),
      request('GET Citas del paciente', 'GET', '/api/patients/{{patientId}}/appointments'),
      request('GET Odontogramas', 'GET', '/api/patients/{{patientId}}/odontogramas'),
      requestWithPrerequests(
        'POST Crear paciente',
        'POST',
        '/api/patients',
        {
          body: `{
  "nombre": "María",
  "apellidos": "Prueba Postman",
  "dni": "{{citaPatientDni}}",
  "telefono": "612345678",
  "email": "maria.{{uniqueSuffix}}@dentify.test",
  "terminos_generales_aceptados": true
}`,
          tests: createdTestScript,
          saveIdAs: 'patientId',
          description: 'DNI y email únicos en cada ejecución (evita duplicados).',
        },
        [prerequestUniqueSuffix()]
      ),
      request('PATCH Actualizar paciente', 'PATCH', '/api/patients/{{patientId}}', {
        body: { telefono: '699887766' },
        tests: authTestScript,
      }),
      deleteRequest('Paciente', '/api/patients/{{patientId}}', 'Puede fallar si tiene citas vinculadas.'),
    ]),
    folder(
      '03 - Citas',
      'Gestión de citas. Si borraste el paciente (carpeta 02), ejecuta primero «POST Crear paciente (prerequisito)».',
      [
      ...citaPrerequisitoRequests(),
      request('GET Listar por rango', 'GET', '/api/appointments', {
        query: [
          { key: 'startDate', value: '2026-01-01' },
          { key: 'endDate', value: '2026-12-31' },
        ],
      }),
      request('GET Por ID', 'GET', '/api/appointments/{{appointmentId}}'),
      requestWithPrerequests(
        'POST Crear cita',
        'POST',
        '/api/appointments',
        {
          body: `{
  "pacienteId": {{patientId}},
  "odontologoId": {{dentistId}},
  "boxId": {{boxId}},
  "fecha": "{{citaFecha}}",
  "hora_inicio": "{{citaHora}}",
  "duracion": 30,
  "tiempo_limpieza": 5,
  "prioridad": "media",
  "estado": "programada",
  "appointment_kind": "tratamiento"
}`,
          tests: createdTestScript,
          saveIdAs: 'appointmentId',
          description:
            'Requiere patientId, dentistId y boxId válidos. Fecha/hora se generan automáticamente (mañana 11:00).',
        },
        [
          prerequestRequireEnvVars(
            ['patientId', 'dentistId', 'boxId'],
            'Ejecuta 01 - Setup y/o POST Crear paciente (prerequisito).'
          ),
          prerequestCitaDateTime(),
        ]
      ),
      request('PATCH Actualizar cita', 'PATCH', '/api/appointments/{{appointmentId}}', {
        body: { notes: 'Actualizado desde Postman' },
      }),
      request('PUT Cancelar cita', 'PUT', '/api/appointments/{{appointmentId}}/cancel', {
        body: {},
        description: 'Solo si la cita está en estado cancelable.',
      }),
      deleteRequest('Cita', '/api/appointments/{{appointmentId}}'),
    ]
    ),
    folder(
      '04 - Sesión de cita',
      'Orden obligatorio: prerequisitos → Iniciar → GET → Añadir material → (opcional DELETE línea) → Completar. ' +
        'No uses una cita ya borrada ni una sesión ya completada.',
      [
        ...citaPrerequisitoRequests(),
        requestWithPrerequests(
          'POST Iniciar sesión',
          'POST',
          '/api/appointments/{{appointmentId}}/session/start',
          {
            body: null,
            tests: sessionStartTestScript,
            description: 'Crea la sesión clínica (201). Debe ejecutarse antes del GET Estado sesión.',
          },
          [prerequestRequireEnvVars(['appointmentId'], 'Ejecuta POST Crear cita (prerequisito) antes.')]
        ),
        requestWithPrerequests(
          'GET Estado sesión',
          'GET',
          '/api/appointments/{{appointmentId}}/session',
          {
            tests: sessionGetTestScript,
            description: 'Solo funciona después de POST Iniciar sesión.',
          },
          [prerequestRequireEnvVars(['appointmentId'], 'Falta appointmentId.')]
        ),
        requestWithPrerequests(
          'POST Añadir material',
          'POST',
          '/api/appointments/{{appointmentId}}/session/materials',
          {
            body: `{
  "stock_material_id": {{stockMaterialId}},
  "cantidad_usada": 1
}`,
            tests: saveSessionMaterialFromResponseScript,
            description: 'Requiere sesión iniciada y stockMaterialId válido (01 - Setup).',
          },
          [
            prerequestRequireEnvVars(
              ['appointmentId', 'stockMaterialId'],
              'Ejecuta 01 - Setup y POST Iniciar sesión.'
            ),
          ]
        ),
        requestWithPrerequests(
          'DELETE Línea material',
          'DELETE',
          '/api/appointments/{{appointmentId}}/session/materials/{{sessionMaterialLineId}}',
          {
            tests: `
pm.test('Status 200 o 404', () => pm.expect(pm.response.code).to.be.oneOf([200, 404]));
`.trim(),
            description: 'Opcional. Antes de Completar. Omite si no añadiste material manual.',
          },
          [prerequestRequireEnvVars(['appointmentId'], 'Falta appointmentId.')]
        ),
        requestWithPrerequests(
          'POST Completar sesión',
          'POST',
          '/api/appointments/{{appointmentId}}/session/complete',
          {
            body: null,
            tests: sessionCompleteTestScript,
            description: 'Cierra la sesión y descuenta stock. La cita pasa a completada.',
          },
          [prerequestRequireEnvVars(['appointmentId'], 'Falta appointmentId.')]
        ),
      ]
    ),
    folder('05 - Odontólogos', null, [
      request('GET Listar', 'GET', '/api/dentists', {
        query: [
          { key: 'page', value: '1' },
          { key: 'itemsPerPage', value: '10' },
        ],
      }),
      request('GET Por ID', 'GET', '/api/dentists/{{dentistId}}'),
      requestWithPrerequests(
        'POST Crear',
        'POST',
        '/api/dentists',
        {
          body: `{
  "nombre": "Ana",
  "apellidos": "Postman Test",
  "especialidad": "General",
  "email": "dentist.{{uniqueSuffix}}@dentify.test"
}`,
          tests: createdTestScript,
          saveIdAs: 'dentistId',
        },
        [prerequestUniqueSuffix()]
      ),
      deleteRequest('Odontólogo', '/api/dentists/{{dentistId}}', 'Puede fallar si está vinculado a un usuario.'),
    ]),
    folder('06 - Cajas', null, [
      request('GET Listar', 'GET', '/api/boxes'),
      request('GET Por ID', 'GET', '/api/boxes/{{boxId}}'),
      requestWithPrerequests(
        'POST Crear',
        'POST',
        '/api/boxes',
        {
          body: `{
  "nombre": "Box Postman {{uniqueSuffix}}",
  "descripcion": "Creada en prueba API",
  "estado": "disponible"
}`,
          tests: createdTestScript,
          saveIdAs: 'boxId',
        },
        [prerequestUniqueSuffix()]
      ),
      deleteRequest('Caja', '/api/boxes/{{boxId}}'),
    ]),
    folder('07 - Stock', 'Materiales y reposiciones', [
      request('GET Listar materiales', 'GET', '/api/stock-materials', {
        query: [
          { key: 'page', value: '1' },
          { key: 'itemsPerPage', value: '20' },
        ],
      }),
      request('GET Material por ID', 'GET', '/api/stock-materials/{{stockMaterialId}}'),
      requestWithPrerequests(
        'POST Crear material',
        'POST',
        '/api/stock-materials',
        {
          body: `{
  "nombre": "Material Postman {{uniqueSuffix}}",
  "cantidad_actual": 100,
  "unidad": "uds",
  "umbral_minimo": 10
}`,
          tests: createdTestScript,
          saveIdAs: 'stockMaterialId',
        },
        [prerequestUniqueSuffix()]
      ),
      request('PUT Añadir stock', 'PUT', '/api/stock-materials/{{stockMaterialId}}/add', {
        body: { cantidad: 5 },
      }),
      request('GET Reposiciones', 'GET', '/api/stock-restocks', {
        query: [
          { key: 'page', value: '1' },
          { key: 'itemsPerPage', value: '10' },
        ],
      }),
      request('POST Crear reposición', 'POST', '/api/stock-restocks', {
        body: `{
  "material_id": {{stockMaterialId}},
  "cantidad_recibida": 50,
  "proveedor": "Proveedor Postman",
  "fecha": "2026-06-02"
}`,
        tests: createdTestScript,
        saveIdAs: 'restockId',
      }),
      deleteRequest('Material', '/api/stock-materials/{{stockMaterialId}}'),
    ]),
    folder('08 - Protocolos', null, [
      request('GET Listar', 'GET', '/api/protocolos'),
      requestWithPrerequests(
        'POST Crear protocolo',
        'POST',
        '/api/protocolos',
        {
          body: `{
  "nombre_tratamiento": "Limpieza Postman {{uniqueSuffix}}",
  "material_id": {{stockMaterialId}},
  "cantidad_necesaria": 2,
  "notas_paso_a_paso": "Paso de prueba"
}`,
          tests: createdTestScript,
          saveIdAs: 'protocoloId',
          description: 'Ejecutar POST Crear material antes o usar stockMaterialId del Setup.',
        },
        [prerequestUniqueSuffix(), prerequestRequireEnvVars(['stockMaterialId'], 'Ejecuta 01 - Setup o POST Crear material.')]
      ),
      request('GET Por ID', 'GET', '/api/protocolos/{{protocoloId}}'),
      deleteRequest('Protocolo', '/api/protocolos/{{protocoloId}}'),
    ]),
    folder(
      '09 - Usuarios',
      'Orden: Listar → Crear (email/usuario únicos) → GET por ID → DELETE. No borrar testuser.',
      [
        request('GET Listar', 'GET', '/api/users'),
        requestWithPrerequests(
          'POST Crear usuario',
          'POST',
          '/api/users',
          {
            body: `{
  "email": "user.{{uniqueSuffix}}@dentify.test",
  "password": "123456",
  "nombre_usuario": "postman_{{uniqueSuffix}}",
  "roles": ["ROLE_USER"],
  "dentistNombre": "Usuario",
  "dentistApellidos": "Postman",
  "dentistEspecialidad": "General"
}`,
            tests: createdTestScript,
            saveIdAs: 'userId',
            description: 'Email y nombre_usuario únicos en cada ejecución. Guarda userId para GET y DELETE.',
          },
          [prerequestUniqueSuffix()]
        ),
        requestWithPrerequests(
          'GET Por ID',
          'GET',
          '/api/users/{{userId}}',
          {
            description: 'Usa userId del POST Crear de esta carpeta (no testuser).',
          },
          [prerequestRequireEnvVars(['userId'], 'Ejecuta POST Crear usuario antes.')]
        ),
        deleteRequest('Usuario', '/api/users/{{userId}}', 'Solo el usuario recién creado. Nunca testuser.'),
      ]
    ),
    folder('10 - Catálogo clínico', null, [
      request('GET Especialidades', 'GET', '/api/specialties'),
      request('GET Categorías tratamiento', 'GET', '/api/treatment-categories'),
      request('GET Tratamientos', 'GET', '/api/treatments', {
        query: [{ key: 'page', value: '1' }],
      }),
      requestWithPrerequests(
        'POST Crear tratamiento',
        'POST',
        '/api/treatments',
        {
          body: `{
  "name": "Tratamiento Postman {{uniqueSuffix}}",
  "categoryId": {{treatmentCategoryId}},
  "dentistSpecialtyId": {{specialtyId}},
  "defaultDurationMinutes": 45,
  "allowsUrgency": true,
  "forFirstVisit": false
}`,
          tests: createdTestScript,
        },
        [
          prerequestUniqueSuffix(),
          prerequestRequireEnvVars(['treatmentCategoryId', 'specialtyId'], 'Ejecuta 01 - Setup.'),
        ]
      ),
    ]),
    folder('11 - Agenda', null, [
      request('GET Horario clínica', 'GET', '/api/schedule/clinic'),
      requestWithPrerequests('GET Odontólogos disponibles', 'GET', '/api/schedule/dentists-available', {
        query: [
          { key: 'startDateTime', value: '{{citaStartDateTime}}' },
          { key: 'duration', value: '30' },
        ],
        tests: `
pm.test('Status 200', () => pm.response.to.have.status(200));
pm.test('Respuesta con dentistIds', () => {
  const json = pm.response.json();
  pm.expect(json.dentistIds).to.be.an('array');
});
`.trim(),
        description:
          'Parámetros alineados con Angular: startDateTime (ISO) y duration (minutos). ' +
          'Lista vacía si el odontólogo no tiene turno semanal o hay solape de citas.',
      }, [prerequestCitaDateTime()]),
      requestWithPrerequests('GET Cajas disponibles', 'GET', '/api/schedule/boxes-available', {
        query: [
          { key: 'startDateTime', value: '{{citaStartDateTime}}' },
          { key: 'duration', value: '30' },
        ],
        tests: `
pm.test('Status 200', () => pm.response.to.have.status(200));
pm.test('Respuesta con boxIds', () => {
  const json = pm.response.json();
  pm.expect(json.boxIds).to.be.an('array');
});
`.trim(),
        description: 'Mismos parámetros que dentists-available: startDateTime y duration.',
      }, [prerequestCitaDateTime()]),
      request('GET Horario semanal odontólogo (por ID)', 'GET', '/api/schedule/dentists/{{dentistId}}/weekly', {
        tests: authTestScript,
        description:
          'Horario semanal de un odontólogo concreto. Uso habitual con testuser (admin). ' +
          'Requiere dentistId del Setup o de Gestión de odontólogos.',
      }),
      request('GET Mi horario semanal', 'GET', '/api/schedule/me/weekly', {
        tests: `
pm.test('Status 200 o 403 según tipo de usuario', () => {
  pm.expect(pm.response.code).to.be.oneOf([200, 403]);
});
if (pm.response.code === 403) {
  pm.test('403 esperado si el usuario no tiene ficha odontólogo', () => {
    pm.expect(pm.response.text()).to.include('No dentist profile');
  });
} else {
  pm.test('Respuesta con horario semanal', () => {
    const json = pm.response.json();
    pm.expect(json).to.be.an('object');
  });
}
`.trim(),
        description:
          'Solo funciona si el usuario autenticado tiene ficha de odontólogo vinculada. ' +
          'Con testuser (admin sin dentist_id) devuelve 403 — comportamiento correcto. ' +
          'Para probar como admin, usa «Horario semanal odontólogo (por ID)» o haz login con un usuario odontólogo.',
      }),
    ]),
    folder('12 - Me y otros', null, [
      request('GET Mis preferencias', 'GET', '/api/me/preferences'),
      request('PATCH Mis preferencias', 'PATCH', '/api/me/preferences', {
        body: { scheme: 'light' },
        tests: authTestScript,
      }),
      request('POST Primera visita', 'POST', '/api/primera-visita', {
        body: `{
  "patient_id": {{patientId}},
  "motivo_consulta": "Dolor molar superior derecho desde hace 3 días",
  "nivel_dolor": 6,
  "observaciones": "Prueba Postman"
}`,
        tests: createdTestScript,
      }),
      request('GET Avisos pedagógicos (manage)', 'GET', '/api/pedagogical-notices/manage', {
        tests: `
pm.test('Status 200', () => pm.response.to.have.status(200));
try {
  const json = pm.response.json();
  if (Array.isArray(json) && json.length > 0 && json[0].id != null) {
    pm.environment.set('pedagogicalNoticeId', String(json[0].id));
  }
} catch (e) {}
`.trim(),
        description: 'Lista admin; guarda pedagogicalNoticeId para el DELETE de esta carpeta.',
      }),
      deleteRequest(
        'Aviso pedagógico',
        '/api/pedagogical-notices/{{pedagogicalNoticeId}}',
        'Requiere ROLE_ADMIN. Crear aviso antes o usar ID del GET manage.'
      ),
    ]),
    folder('99 - Pruebas negativas', 'Seguridad y validación', [
      request('GET Pacientes sin token', 'GET', '/api/patients', {
        auth: 'none',
        tests: unauthorizedTestScript,
      }),
      request('POST Paciente body inválido', 'POST', '/api/patients', {
        body: { nombre: '', apellidos: '', dni: '' },
        tests: validationErrorScript,
      }),
      request('POST Cita sin campos obligatorios', 'POST', '/api/appointments', {
        body: {},
        tests: validationErrorScript,
      }),
      request('DELETE Recurso inexistente', 'DELETE', '/api/patients/999999', {
        tests: `pm.test('Status 404', () => pm.response.to.have.status(404));`,
        description: 'ID que no existe en la BBDD.',
      }),
    ]),
  ],
};

const outPath = join(__dirname, 'Dentify-API.postman_collection.json');
writeFileSync(outPath, JSON.stringify(collection, null, 2), 'utf8');
console.log('Written:', outPath);
