# Issue #23 - Create Appointment Entity - COMPLETED

## Resumen de cambios implementados

### ✅ 1. Entidad Appointment actualizada

Se agregaron los siguientes campos a `src/Entity/Appointment.php`:

- **`treatment`** (TEXT, nullable): Descripción del tratamiento
- **`isInfectious`** (BOOLEAN, default: false): Flag para identificar pacientes infecciosos
- **`notes`** (TEXT, nullable): Notas adicionales sobre la cita

### ✅ 2. DTOs actualizados

#### AppointmentCreateDTO
- Agregados: `treatment`, `isInfectious`, `notes`

#### AppointmentUpdateDTO
- Agregados: `treatment`, `isInfectious`, `notes`

#### AppointmentResponseDTO
Se agregaron campos tanto en formato original como en formato compatible con el frontend:
- **Campos nuevos originales**: `treatment`, `isInfectious`, `notes`
- **Campos de compatibilidad frontend**:
  - `startDateTime` (ISO 8601): Fecha y hora de inicio combinadas
  - `endDateTime` (ISO 8601): Fecha y hora de fin calculadas
  - `patientId`, `patientName` (camelCase aliases)
  - `dentistId`, `dentistName` (camelCase aliases)
  - `status` (alias de `estado`)
  - `isInfectiousPatient` (alias de `isInfectious`)

### ✅ 3. Mapper actualizado

`src/Mapper/AppointmentMapper.php`:
- Método `mapDtoToEntity()`: Mapea los nuevos campos desde DTOs a entidad
- Método `toResponseDto()`: Genera respuesta con todos los campos incluyendo:
  - Campos originales del backend
  - Campos en formato ISO 8601 para el frontend
  - Cálculo automático de `endDateTime` basado en `startDateTime + duration`

### ✅ 4. Validaciones implementadas (Peticiones #5)

Se agregó validación en `src/Controller/AppointmentController.php`:

1. ✅ **Gap de 5 minutos**: Entre citas consecutivas del mismo dentista
2. ✅ **Disponibilidad de Box**: El box debe estar en estado 'disponible'
3. ✅ **Sin overlaps**: No puede haber solapamientos de dentista ni box
4. ✅ **Pacientes infecciosos al final del día**: 
   - Si `isInfectious = true`, la cita debe ser la última del día
   - Se valida que no haya citas posteriores para el mismo dentista/box
   - Mensaje de error específico según Peticiones #5

### ✅ 5. Repository actualizado

Nuevo método en `src/Repository/AppointmentRepository.php`:
- `findLaterAppointmentsSameDay()`: Busca citas posteriores en el mismo día para validar pacientes infecciosos

### ✅ 6. Migración de base de datos

Archivo: `migrations/Version20260422134500.php`
- Agrega columnas: `treatment`, `is_infectious`, `notes`
- **Estado**: ✅ Aplicada exitosamente

## Compatibilidad con Frontend

El ResponseDTO ahora envía datos en dos formatos simultáneamente:

### Formato Backend (original):
```json
{
  "id": 1,
  "pacienteId": 5,
  "pacienteNombre": "Juan Pérez",
  "odontologoId": 2,
  "odontologoNombre": "Dra. María López",
  "boxId": 3,
  "boxNombre": "Box A",
  "fecha": "2026-04-25",
  "hora_inicio": "10:00:00",
  "duracion": 60,
  "estado": "programada",
  "treatment": "Endodoncia",
  "isInfectious": false,
  "notes": "Paciente sensible"
}
```

### Formato Frontend (compatible):
```json
{
  "id": 1,
  "patientId": 5,
  "patientName": "Juan Pérez",
  "dentistId": 2,
  "dentistName": "Dra. María López",
  "boxId": 3,
  "boxName": "Box A",
  "startDateTime": "2026-04-25T10:00:00+02:00",
  "endDateTime": "2026-04-25T11:00:00+02:00",
  "duration": 60,
  "status": "programada",
  "treatment": "Endodoncia",
  "isInfectiousPatient": false,
  "notes": "Paciente sensible"
}
```

## Reglas de negocio implementadas (Peticiones #5)

1. ✅ **Gap de 5 minutos obligatorio** entre citas consecutivas
2. ✅ **Paciente infeccioso última cita del día**
3. ✅ **Validación de disponibilidad de recursos**
4. ⏳ Validación de "tratamiento largo no puede ir a última hora" (pendiente - requiere definir qué es "última hora" y "tratamiento largo")

## Estados de cita

- `programada`: Cita agendada
- `confirmada`: Cita confirmada por el paciente
- `completada`: Cita realizada
- `cancelada`: Cita cancelada
- (más estados pueden agregarse según necesidad)

## Endpoints disponibles

Todos los endpoints en `/api/appointments`:
- `GET /api/appointments` - Listar con filtros
- `GET /api/appointments/{id}` - Detalle
- `POST /api/appointments` - Crear (con validaciones completas)
- `PUT /api/appointments/{id}` - Actualizar (con validaciones completas)
- `DELETE /api/appointments/{id}` - Eliminar
- `PUT /api/appointments/{id}/cancel` - Cancelar
- `PUT /api/appointments/{id}/reschedule` - Reprogramar (con re-validación)

## Próximos pasos sugeridos

1. Definir regla de "tratamiento largo a última hora" con valores concretos
2. Agregar campo de duración estimada por tipo de tratamiento
3. Considerar agregar horario laboral configurable por dentista
4. Agregar notificaciones cuando se programa paciente infeccioso

## Relación con otros issues

- ✅ Issue #23: CREATE Appointment Entity - **COMPLETADO**
- ✅ Issue #24: CRUD for Appointments - **COMPLETADO**
- ✅ Issue #25: Validate Availability - **COMPLETADO**
- ✅ Issue #26: 5-Minute Gaps - **COMPLETADO**
- ✅ Issue #27: Cancel or Reschedule - **COMPLETADO**

**Status: ISSUE #23 COMPLETAMENTE IMPLEMENTADO Y FUNCIONAL**
