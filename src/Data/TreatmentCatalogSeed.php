<?php

namespace App\Data;

/**
 * Catálogo clínico: categoría → procedimientos.
 * Cada procedimiento indica la especialidad del odontólogo requerida (nombre en tabla `specialty`).
 *
 * @return list<array{category: string, sort: int, treatments: list<array{name: string, duration: int, dentist_specialty: string, urgent?: bool, first_visit?: bool}>}>
 */
final class TreatmentCatalogSeed
{
    public static function categories(): array
    {
        return [
            [
                'category' => 'Revisión dental',
                'sort' => 10,
                'treatments' => [
                    ['name' => 'Diagnóstico', 'duration' => 30, 'dentist_specialty' => 'Odontología General', 'first_visit' => true],
                    ['name' => 'Limpieza dental', 'duration' => 45, 'dentist_specialty' => 'Limpieza dental'],
                    ['name' => 'Fluorización', 'duration' => 30, 'dentist_specialty' => 'Odontología General'],
                ],
            ],
            [
                'category' => 'Conservadora',
                'sort' => 20,
                'treatments' => [
                    ['name' => 'Empaste simple', 'duration' => 30, 'dentist_specialty' => 'Odontología General'],
                    ['name' => 'Empaste compuesto', 'duration' => 45, 'dentist_specialty' => 'Odontología General'],
                    ['name' => 'Reconstrucción dental', 'duration' => 60, 'dentist_specialty' => 'Odontología General'],
                    ['name' => 'Incrustación', 'duration' => 60, 'dentist_specialty' => 'Odontología General'],
                ],
            ],
            [
                'category' => 'Endodoncia',
                'sort' => 30,
                'treatments' => [
                    ['name' => 'Endodoncia unirradicular', 'duration' => 60, 'dentist_specialty' => 'Endodoncia'],
                    ['name' => 'Endodoncia birradicular', 'duration' => 75, 'dentist_specialty' => 'Endodoncia'],
                    ['name' => 'Endodoncia multirradicular', 'duration' => 90, 'dentist_specialty' => 'Endodoncia'],
                    ['name' => 'Retratamiento endodóntico', 'duration' => 90, 'dentist_specialty' => 'Endodoncia'],
                ],
            ],
            [
                'category' => 'Periodoncia',
                'sort' => 40,
                'treatments' => [
                    ['name' => 'Curetaje', 'duration' => 45, 'dentist_specialty' => 'Periodoncia'],
                    ['name' => 'Raspado y alisado radicular', 'duration' => 60, 'dentist_specialty' => 'Periodoncia'],
                    ['name' => 'Tratamiento periodontal', 'duration' => 60, 'dentist_specialty' => 'Periodoncia'],
                    ['name' => 'Mantenimiento periodontal', 'duration' => 45, 'dentist_specialty' => 'Periodoncia'],
                ],
            ],
            [
                'category' => 'Cirugía oral',
                'sort' => 50,
                'treatments' => [
                    ['name' => 'Extracción simple', 'duration' => 30, 'dentist_specialty' => 'Cirugía Oral y Maxilofacial'],
                    ['name' => 'Extracción quirúrgica', 'duration' => 45, 'dentist_specialty' => 'Cirugía Oral y Maxilofacial'],
                    ['name' => 'Extracción de cordal', 'duration' => 60, 'dentist_specialty' => 'Cirugía Oral y Maxilofacial'],
                    ['name' => 'Frenectomía', 'duration' => 45, 'dentist_specialty' => 'Cirugía Oral y Maxilofacial'],
                ],
            ],
            [
                'category' => 'Implantología',
                'sort' => 60,
                'treatments' => [
                    ['name' => 'Implante dental', 'duration' => 60, 'dentist_specialty' => 'Implantología', 'urgent' => false],
                    ['name' => 'Corona sobre implante', 'duration' => 60, 'dentist_specialty' => 'Implantología', 'urgent' => false],
                    ['name' => 'Regeneración ósea', 'duration' => 90, 'dentist_specialty' => 'Implantología', 'urgent' => false],
                    ['name' => 'Elevación de seno maxilar', 'duration' => 90, 'dentist_specialty' => 'Implantología', 'urgent' => false],
                ],
            ],
            [
                'category' => 'Ortodoncia',
                'sort' => 70,
                'treatments' => [
                    ['name' => 'Ortodoncia metálica', 'duration' => 30, 'dentist_specialty' => 'Ortodoncia', 'urgent' => false],
                    ['name' => 'Ortodoncia estética', 'duration' => 30, 'dentist_specialty' => 'Ortodoncia', 'urgent' => false],
                    ['name' => 'Invisalign', 'duration' => 30, 'dentist_specialty' => 'Ortodoncia', 'urgent' => false],
                    ['name' => 'Estudio ortodóncico', 'duration' => 45, 'dentist_specialty' => 'Ortodoncia', 'first_visit' => true, 'urgent' => false],
                ],
            ],
            [
                'category' => 'Prótesis',
                'sort' => 80,
                'treatments' => [
                    ['name' => 'Corona dental', 'duration' => 60, 'dentist_specialty' => 'Prostodoncia', 'urgent' => false],
                    ['name' => 'Puente dental', 'duration' => 90, 'dentist_specialty' => 'Prostodoncia', 'urgent' => false],
                    ['name' => 'Prótesis removible', 'duration' => 60, 'dentist_specialty' => 'Prostodoncia', 'urgent' => false],
                    ['name' => 'Prótesis completa', 'duration' => 90, 'dentist_specialty' => 'Prostodoncia', 'urgent' => false],
                ],
            ],
            [
                'category' => 'Estética',
                'sort' => 90,
                'treatments' => [
                    ['name' => 'Blanqueamiento dental', 'duration' => 45, 'dentist_specialty' => 'Estética Dental', 'urgent' => false],
                    ['name' => 'Carillas dentales', 'duration' => 60, 'dentist_specialty' => 'Estética Dental', 'urgent' => false],
                    ['name' => 'Diseño de sonrisa', 'duration' => 60, 'dentist_specialty' => 'Estética Dental', 'urgent' => false],
                ],
            ],
            [
                'category' => 'ATM y bruxismo',
                'sort' => 100,
                'treatments' => [
                    ['name' => 'Férula de descarga', 'duration' => 30, 'dentist_specialty' => 'Odontología General'],
                    ['name' => 'Estudio ATM', 'duration' => 45, 'dentist_specialty' => 'Odontología General'],
                ],
            ],
            [
                'category' => 'Odontopediatría',
                'sort' => 110,
                'treatments' => [
                    ['name' => 'Selladores', 'duration' => 30, 'dentist_specialty' => 'Odontopediatría'],
                    ['name' => 'Pulpectomía', 'duration' => 45, 'dentist_specialty' => 'Odontopediatría'],
                    ['name' => 'Tratamiento infantil', 'duration' => 30, 'dentist_specialty' => 'Odontopediatría'],
                ],
            ],
        ];
    }
}
