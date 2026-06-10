<?php

namespace App\Data;

/**
 * Pacientes de demostración con datos ficticios realistas (España).
 *
 * @phpstan-type PatientRow array{
 *   nombre: string,
 *   apellidos: string,
 *   dni: string,
 *   nss?: string,
 *   telefono: string,
 *   email: string,
 *   enfermedades?: string,
 *   alergias?: string,
 *   historial_clinico?: string,
 *   medical_flags?: list<string>,
 *   medical_notes?: string,
 *   terminos_generales_aceptados?: bool,
 *   historia_inicial?: string,
 *   motivo_primera_visita?: string,
 *   nivel_dolor?: int
 * }
 */
final class PatientDemoSeed
{
    /**
     * @return list<PatientRow>
     */
    public static function all(): array
    {
        return [
            [
                'nombre' => 'Alfonso',
                'apellidos' => 'Gómez Ruiz',
                'dni' => '45678912K',
                'nss' => '28/10456789-12',
                'telefono' => '612 34 56 78',
                'email' => 'alfonso.gomez.ruiz@gmail.com',
                'alergias' => 'Penicilina (reacción cutánea leve en 2019).',
                'historial_clinico' => 'Paciente asintomático en visitas de control. Ortodoncia en la adolescencia.',
                'terminos_generales_aceptados' => true,
                'historia_inicial' => 'Primera visita de revisión. Exploración general sin hallazgos urgentes. Se programa limpieza.',
                'motivo_primera_visita' => 'Revisión anual y molestia leve en molar inferior derecho',
                'nivel_dolor' => 2,
            ],
            [
                'nombre' => 'María',
                'apellidos' => 'Fernández López',
                'dni' => '52341876B',
                'nss' => '46/20987123-45',
                'telefono' => '687 91 23 40',
                'email' => 'maria.fernandez.lopez@outlook.es',
                'enfermedades' => 'Hipotiroidismo controlado con levotiroxina.',
                'alergias' => 'Látex (guantes): usar alternativas en gabinete.',
                'medical_flags' => ['Alergia_Latex'],
                'medical_notes' => 'Prefiere citas por la mañana.',
                'terminos_generales_aceptados' => true,
                'historia_inicial' => 'Control de sensibilidad cervical en piezas 13-23. Se indica pasta desensibilizante.',
                'motivo_primera_visita' => 'Sensibilidad al frío en dientes anteriores superiores',
                'nivel_dolor' => 3,
            ],
            [
                'nombre' => 'Javier',
                'apellidos' => 'Martín Serrano',
                'dni' => '30124567H',
                'telefono' => '655 12 88 03',
                'email' => 'javier.martin.serrano@yahoo.es',
                'historial_clinico' => 'Bruxismo nocturno. Portador de férula de descarga desde 2024.',
                'terminos_generales_aceptados' => true,
                'historia_inicial' => 'Revisión de férula y desgaste de puntas cúspides. Ajuste oclusal menor.',
                'motivo_primera_visita' => 'Dolor matutino en mandíbula y revisión de férula',
                'nivel_dolor' => 4,
            ],
            [
                'nombre' => 'Lucía',
                'apellidos' => 'Torres Vega',
                'dni' => '71239845M',
                'nss' => '41/18765432-01',
                'telefono' => '698 44 71 22',
                'email' => 'lucia.torres.vega@icloud.com',
                'alergias' => 'Ibuprofeno (náuseas). Analgesia alternativa: paracetamol.',
                'terminos_generales_aceptados' => false,
                'historia_inicial' => 'Primera visita. Pendiente firma de consentimiento informado general.',
                'motivo_primera_visita' => 'Primera visita en la clínica — valoración general',
                'nivel_dolor' => 1,
            ],
            [
                'nombre' => 'Carlos',
                'apellidos' => 'Ramírez Ortega',
                'dni' => '28901456R',
                'telefono' => '611 77 05 19',
                'email' => 'carlos.ramirez.ortega@hotmail.com',
                'enfermedades' => 'Diabetes tipo 2 (HbA1c en control).',
                'historial_clinico' => 'Extracción de cordal inferior izquierdo en 2023. Cicatrización correcta.',
                'terminos_generales_aceptados' => true,
                'historia_inicial' => 'Control postoperatorio y revisión de empaste en 36.',
                'motivo_primera_visita' => 'Control tras extracción y revisión de empaste',
                'nivel_dolor' => 2,
            ],
            [
                'nombre' => 'Elena',
                'apellidos' => 'Navarro Gil',
                'dni' => '33452109C',
                'nss' => '08/11223344-55',
                'telefono' => '622 38 90 15',
                'email' => 'elena.navarro.gil@gmail.com',
                'alergias' => 'Metales (níquel): evitar aleaciones con níquel en prótesis.',
                'terminos_generales_aceptados' => true,
                'historia_inicial' => 'Estudio para corona en 16. Impresión y tallado realizados.',
                'motivo_primera_visita' => 'Pieza fracturada — valoración para corona',
                'nivel_dolor' => 5,
            ],
            [
                'nombre' => 'Pablo',
                'apellidos' => 'Iglesias Mena',
                'dni' => '60127834T',
                'telefono' => '677 02 44 88',
                'email' => 'pablo.iglesias.mena@proton.me',
                'historial_clinico' => 'Fumador ocasional (en proceso de abandono). Higiene intermedia.',
                'terminos_generales_aceptados' => true,
                'historia_inicial' => 'Raspado y alisado radicular en cuadrante inferior. Reevaluación en 3 meses.',
                'motivo_primera_visita' => 'Sangrado de encías al cepillarse',
                'nivel_dolor' => 2,
            ],
            [
                'nombre' => 'Carmen',
                'apellidos' => 'Díaz Paredes',
                'dni' => '47890123W',
                'nss' => '28/55667788-99',
                'telefono' => '644 19 73 56',
                'email' => 'carmen.diaz.paredes@outlook.com',
                'enfermedades' => 'Hipertensión arterial leve.',
                'terminos_generales_aceptados' => true,
                'historia_inicial' => 'Blanqueamiento ambulatorio. Sesión 1 completada sin sensibilidad excesiva.',
                'motivo_primera_visita' => 'Interés en blanqueamiento dental',
                'nivel_dolor' => 1,
            ],
            [
                'nombre' => 'Diego',
                'apellidos' => 'Herrera Campos',
                'dni' => '25673418Y',
                'telefono' => '633 55 20 41',
                'email' => 'diego.herrera.campos@gmail.com',
                'historial_clinico' => 'Deportista. Traumatismo dental hace 2 años (avulsión 21, reimplante).',
                'terminos_generales_aceptados' => true,
                'historia_inicial' => 'Control de vitalidad pulpar en 21 y radiografía de seguimiento.',
                'motivo_primera_visita' => 'Control anual tras reimplante de incisivo',
                'nivel_dolor' => 1,
            ],
            [
                'nombre' => 'Isabel',
                'apellidos' => 'Castro Molina',
                'dni' => '39081267P',
                'nss' => '46/33445566-77',
                'telefono' => '661 88 34 02',
                'email' => 'isabel.castro.molina@yahoo.es',
                'alergias' => 'Anestesia con vasoconstrictor: valorar articáina sin adrenalina si es necesario.',
                'medical_notes' => 'Ansiedad dental moderada. Explicar cada paso antes de proceder.',
                'terminos_generales_aceptados' => true,
                'historia_inicial' => 'Endodoncia en 46 iniciada. Segunda sesión programada.',
                'motivo_primera_visita' => 'Dolor intenso en molar — posible pulpitis',
                'nivel_dolor' => 7,
            ],
            [
                'nombre' => 'Roberto',
                'apellidos' => 'Vega Santos',
                'dni' => '51236789D',
                'telefono' => '609 41 67 90',
                'email' => 'roberto.vega.santos@hotmail.es',
                'historial_clinico' => 'Prótesis removible superior desde 2022. Ajustes periódicos.',
                'terminos_generales_aceptados' => true,
                'historia_inicial' => 'Revisión de prótesis: pequeña úlcera por roce. Ajuste de rebase menor.',
                'motivo_primera_visita' => 'Rozadura con la prótesis superior',
                'nivel_dolor' => 3,
            ],
            [
                'nombre' => 'Sofía',
                'apellidos' => 'Méndez Ríos',
                'dni' => '67345210F',
                'telefono' => '686 23 11 74',
                'email' => 'sofia.mendez.rios@gmail.com',
                'terminos_generales_aceptados' => true,
                'historia_inicial' => 'Selladores en molares permanentes (odontopediatría transferida).',
                'motivo_primera_visita' => 'Revisión infantil — primera muela permanente erupcionada',
                'nivel_dolor' => 1,
            ],
        ];
    }

    /**
     * @return list<PatientRow>
     */
    public static function pickRandom(int $count = 8): array
    {
        $pool = self::all();
        shuffle($pool);

        return \array_slice($pool, 0, min($count, \count($pool)));
    }
}
