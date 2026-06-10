<?php

namespace App\Data;

/**
 * Inventario y protocolos de consumo para una clínica dental.
 * Los nombres de tratamiento deben coincidir con {@see TreatmentCatalogSeed}.
 *
 * @phpstan-type MaterialRow array{key: string, nombre: string, cantidad: float, umbral: float, unidad: string}
 * @phpstan-type ProtocolLine array{material: string, cantidad: float, notas?: string}
 * @phpstan-type ProtocolRow array{treatment: string, lines: list<ProtocolLine>}
 */
final class StockAndProtocolSeed
{
    /**
     * @return list<MaterialRow>
     */
    public static function materials(): array
    {
        return [
            // EPI y desechables
            ['key' => 'guantes_nitrilo', 'nombre' => 'Guantes de nitrilo (talla M)', 'cantidad' => 120, 'umbral' => 40, 'unidad' => 'pares'],
            ['key' => 'mascarilla_ffp2', 'nombre' => 'Mascarilla FFP2', 'cantidad' => 80, 'umbral' => 25, 'unidad' => 'unidades'],
            ['key' => 'babero_desechable', 'nombre' => 'Babero desechable', 'cantidad' => 200, 'umbral' => 60, 'unidad' => 'unidades'],
            ['key' => 'gasas_esteriles', 'nombre' => 'Gasas estériles 5x5 cm', 'cantidad' => 500, 'umbral' => 150, 'unidad' => 'unidades'],
            ['key' => 'rollos_algodon', 'nombre' => 'Rollos de algodón', 'cantidad' => 30, 'umbral' => 10, 'unidad' => 'rollos'],
            ['key' => 'campo_esteril', 'nombre' => 'Campo estéril adhesivo', 'cantidad' => 80, 'umbral' => 25, 'unidad' => 'unidades'],
            ['key' => 'vaso_desechable', 'nombre' => 'Vaso de papel desechable', 'cantidad' => 300, 'umbral' => 80, 'unidad' => 'unidades'],

            // Anestesia
            ['key' => 'articaina_4', 'nombre' => 'Cartucho anestesia articaina 4% 1:100.000', 'cantidad' => 48, 'umbral' => 15, 'unidad' => 'cartuchos'],
            ['key' => 'agujas_carpule', 'nombre' => 'Agujas carpule 27G corta', 'cantidad' => 100, 'umbral' => 30, 'unidad' => 'unidades'],

            // Diagnóstico y profilaxis
            ['key' => 'sonda_exploracion', 'nombre' => 'Sonda de exploración (esterilizable)', 'cantidad' => 12, 'umbral' => 4, 'unidad' => 'unidades'],
            ['key' => 'pasta_profesional', 'nombre' => 'Pasta profiláctica con flúor', 'cantidad' => 8, 'umbral' => 3, 'unidad' => 'tubos'],
            ['key' => 'cepillo_profesional', 'nombre' => 'Cepillo de profilaxis desechable', 'cantidad' => 60, 'umbral' => 20, 'unidad' => 'unidades'],
            ['key' => 'pasta_fluor', 'nombre' => 'Gel de flúor neutro 1.23%', 'cantidad' => 6, 'umbral' => 10, 'unidad' => 'jeringas'],
            ['key' => 'discos_pulido', 'nombre' => 'Discos de pulido composite (kit)', 'cantidad' => 15, 'umbral' => 5, 'unidad' => 'kits'],

            // Conservadora
            ['key' => 'acido_fosforico', 'nombre' => 'Ácido fosfórico 37% (gel)', 'cantidad' => 4, 'umbral' => 10, 'unidad' => 'jeringas'],
            ['key' => 'adhesivo_dentinario', 'nombre' => 'Adhesivo dentinario universal', 'cantidad' => 5, 'umbral' => 10, 'unidad' => 'frascos'],
            ['key' => 'composite_fluido', 'nombre' => 'Composite fluido A2', 'cantidad' => 6, 'umbral' => 10, 'unidad' => 'jeringas'],
            ['key' => 'composite_universal', 'nombre' => 'Composite universal A2 (jeringa 4g)', 'cantidad' => 12, 'umbral' => 4, 'unidad' => 'jeringas'],
            ['key' => 'composite_bulk', 'nombre' => 'Composite bulk fill A2', 'cantidad' => 8, 'umbral' => 3, 'unidad' => 'jeringas'],
            ['key' => 'matriz_metalica', 'nombre' => 'Matriz metálica sectorial (bandas)', 'cantidad' => 100, 'umbral' => 30, 'unidad' => 'unidades'],
            ['key' => 'cuna_plastica', 'nombre' => 'Cuña de plástico interdental', 'cantidad' => 80, 'umbral' => 25, 'unidad' => 'unidades'],
            ['key' => 'barniz_fluor', 'nombre' => 'Barniz de flúor 5% NaF', 'cantidad' => 5, 'umbral' => 10, 'unidad' => 'frascos'],
            ['key' => 'fresa_diamante', 'nombre' => 'Fresa diamante redonda FG', 'cantidad' => 25, 'umbral' => 8, 'unidad' => 'unidades'],

            // Endodoncia
            ['key' => 'lima_k', 'nombre' => 'Lima endodóntica K-File (set)', 'cantidad' => 10, 'umbral' => 3, 'unidad' => 'sets'],
            ['key' => 'gutapercha', 'nombre' => 'Conos de gutapercha (assorted)', 'cantidad' => 20, 'umbral' => 6, 'unidad' => 'cajas'],
            ['key' => 'cemento_endo', 'nombre' => 'Cemento sellador endodóntico', 'cantidad' => 4, 'umbral' => 10, 'unidad' => 'jeringas'],
            ['key' => 'hipoclorito', 'nombre' => 'Hipoclorito de sodio 5.25%', 'cantidad' => 6, 'umbral' => 10, 'unidad' => 'litros'],
            ['key' => 'edta', 'nombre' => 'EDTA 17% (gel irrigante)', 'cantidad' => 4, 'umbral' => 10, 'unidad' => 'jeringas'],

            // Periodoncia
            ['key' => 'cureta_gracey', 'nombre' => 'Cureta Gracey (esterilizable)', 'cantidad' => 8, 'umbral' => 3, 'unidad' => 'unidades'],
            ['key' => 'hilo_retraccion', 'nombre' => 'Hilo de retracción gingival #00', 'cantidad' => 10, 'umbral' => 3, 'unidad' => 'carretes'],

            // Cirugía
            ['key' => 'sutura_resorbible', 'nombre' => 'Sutura resorbible 4-0', 'cantidad' => 24, 'umbral' => 8, 'unidad' => 'sobres'],
            ['key' => 'gasas_quirurgicas', 'nombre' => 'Gasas quirúrgicas estériles', 'cantidad' => 200, 'umbral' => 60, 'unidad' => 'unidades'],
            ['key' => 'esponja_hemostatica', 'nombre' => 'Esponja hemostática colageno', 'cantidad' => 15, 'umbral' => 5, 'unidad' => 'unidades'],

            // Prótesis / impresión
            ['key' => 'alginato', 'nombre' => 'Alginato de impresión (bolsa 454g)', 'cantidad' => 6, 'umbral' => 10, 'unidad' => 'bolsas'],
            ['key' => 'cubeta_impresion', 'nombre' => 'Cubeta de impresión desechable', 'cantidad' => 40, 'umbral' => 12, 'unidad' => 'unidades'],
            ['key' => 'cemento_temporal', 'nombre' => 'Cemento provisional para corona', 'cantidad' => 5, 'umbral' => 10, 'unidad' => 'tubos'],

            // Ortodoncia
            ['key' => 'arco_niti', 'nombre' => 'Arco NiTi redondo 0.014', 'cantidad' => 20, 'umbral' => 6, 'unidad' => 'unidades'],
            ['key' => 'ligadura_elastica', 'nombre' => 'Ligaduras elásticas (bolsa colores)', 'cantidad' => 8, 'umbral' => 3, 'unidad' => 'bolsas'],

            // Odontopediatría
            ['key' => 'sellador_fosas', 'nombre' => 'Resina selladora de fosas y fisuras', 'cantidad' => 4, 'umbral' => 10, 'unidad' => 'jeringas'],
            ['key' => 'ionomero_vitreo', 'nombre' => 'Ionómero de vidrio para pediatría', 'cantidad' => 5, 'umbral' => 10, 'unidad' => 'cápsulas'],

            // Estética
            ['key' => 'peroxido_blanqueamiento', 'nombre' => 'Peróxido de carbamida 16% (blanqueamiento)', 'cantidad' => 4, 'umbral' => 10, 'unidad' => 'jeringas'],
            ['key' => 'ferula_blanqueamiento', 'nombre' => 'Férula de blanqueamiento (vacío)', 'cantidad' => 10, 'umbral' => 3, 'unidad' => 'unidades'],

            // Bajo stock intencional (alertas dashboard)
            ['key' => 'composite_critico', 'nombre' => 'Composite universal A3 (reposición urgente)', 'cantidad' => 2, 'umbral' => 5, 'unidad' => 'jeringas'],
        ];
    }

    /**
     * @return list<ProtocolRow>
     */
    public static function protocols(): array
    {
        return [
            [
                'treatment' => 'Diagnóstico',
                'lines' => [
                    ['material' => 'guantes_nitrilo', 'cantidad' => 1, 'notas' => 'EPI básica'],
                    ['material' => 'mascarilla_ffp2', 'cantidad' => 1],
                    ['material' => 'babero_desechable', 'cantidad' => 1],
                    ['material' => 'gasas_esteriles', 'cantidad' => 4],
                    ['material' => 'sonda_exploracion', 'cantidad' => 1, 'notas' => 'Exploración y registro'],
                ],
            ],
            [
                'treatment' => 'Limpieza dental',
                'lines' => [
                    ['material' => 'guantes_nitrilo', 'cantidad' => 1],
                    ['material' => 'mascarilla_ffp2', 'cantidad' => 1],
                    ['material' => 'babero_desechable', 'cantidad' => 1],
                    ['material' => 'pasta_profesional', 'cantidad' => 0.5, 'notas' => 'Media dosis por cuadrante aprox.'],
                    ['material' => 'cepillo_profesional', 'cantidad' => 1],
                    ['material' => 'gasas_esteriles', 'cantidad' => 6],
                ],
            ],
            [
                'treatment' => 'Fluorización',
                'lines' => [
                    ['material' => 'guantes_nitrilo', 'cantidad' => 1],
                    ['material' => 'babero_desechable', 'cantidad' => 1],
                    ['material' => 'pasta_fluor', 'cantidad' => 1, 'notas' => 'Aplicación tópica 4 min'],
                    ['material' => 'vaso_desechable', 'cantidad' => 1],
                ],
            ],
            [
                'treatment' => 'Empaste simple',
                'lines' => [
                    ['material' => 'guantes_nitrilo', 'cantidad' => 1],
                    ['material' => 'articaina_4', 'cantidad' => 1],
                    ['material' => 'agujas_carpule', 'cantidad' => 1],
                    ['material' => 'fresa_diamante', 'cantidad' => 1],
                    ['material' => 'acido_fosforico', 'cantidad' => 0.2],
                    ['material' => 'adhesivo_dentinario', 'cantidad' => 0.1],
                    ['material' => 'composite_universal', 'cantidad' => 1],
                    ['material' => 'matriz_metalica', 'cantidad' => 1],
                    ['material' => 'cuna_plastica', 'cantidad' => 1],
                    ['material' => 'discos_pulido', 'cantidad' => 0.1],
                ],
            ],
            [
                'treatment' => 'Empaste compuesto',
                'lines' => [
                    ['material' => 'guantes_nitrilo', 'cantidad' => 1],
                    ['material' => 'articaina_4', 'cantidad' => 1],
                    ['material' => 'agujas_carpule', 'cantidad' => 1],
                    ['material' => 'campo_esteril', 'cantidad' => 1],
                    ['material' => 'fresa_diamante', 'cantidad' => 1],
                    ['material' => 'acido_fosforico', 'cantidad' => 0.3],
                    ['material' => 'adhesivo_dentinario', 'cantidad' => 0.15],
                    ['material' => 'composite_universal', 'cantidad' => 1.5],
                    ['material' => 'matriz_metalica', 'cantidad' => 2],
                    ['material' => 'cuna_plastica', 'cantidad' => 2],
                    ['material' => 'discos_pulido', 'cantidad' => 0.2],
                ],
            ],
            [
                'treatment' => 'Reconstrucción dental',
                'lines' => [
                    ['material' => 'guantes_nitrilo', 'cantidad' => 1],
                    ['material' => 'articaina_4', 'cantidad' => 2, 'notas' => 'Bloqueo si pieza extensa'],
                    ['material' => 'agujas_carpule', 'cantidad' => 2],
                    ['material' => 'campo_esteril', 'cantidad' => 1],
                    ['material' => 'fresa_diamante', 'cantidad' => 2],
                    ['material' => 'acido_fosforico', 'cantidad' => 0.4],
                    ['material' => 'adhesivo_dentinario', 'cantidad' => 0.2],
                    ['material' => 'composite_bulk', 'cantidad' => 2, 'notas' => 'Base y contorno'],
                    ['material' => 'composite_universal', 'cantidad' => 2, 'notas' => 'Añadir jeringa extra en box si la pieza es grande'],
                    ['material' => 'matriz_metalica', 'cantidad' => 3],
                    ['material' => 'cuna_plastica', 'cantidad' => 3],
                    ['material' => 'discos_pulido', 'cantidad' => 0.3],
                ],
            ],
            [
                'treatment' => 'Incrustación',
                'lines' => [
                    ['material' => 'guantes_nitrilo', 'cantidad' => 1],
                    ['material' => 'articaina_4', 'cantidad' => 1],
                    ['material' => 'alginato', 'cantidad' => 0.5],
                    ['material' => 'cubeta_impresion', 'cantidad' => 1],
                    ['material' => 'cemento_temporal', 'cantidad' => 0.2],
                ],
            ],
            [
                'treatment' => 'Endodoncia unirradicular',
                'lines' => [
                    ['material' => 'guantes_nitrilo', 'cantidad' => 1],
                    ['material' => 'articaina_4', 'cantidad' => 1],
                    ['material' => 'campo_esteril', 'cantidad' => 1],
                    ['material' => 'lima_k', 'cantidad' => 1],
                    ['material' => 'hipoclorito', 'cantidad' => 0.05],
                    ['material' => 'edta', 'cantidad' => 0.2],
                    ['material' => 'gutapercha', 'cantidad' => 1],
                    ['material' => 'cemento_endo', 'cantidad' => 0.3],
                ],
            ],
            [
                'treatment' => 'Endodoncia birradicular',
                'lines' => [
                    ['material' => 'guantes_nitrilo', 'cantidad' => 1],
                    ['material' => 'articaina_4', 'cantidad' => 2],
                    ['material' => 'lima_k', 'cantidad' => 2],
                    ['material' => 'hipoclorito', 'cantidad' => 0.08],
                    ['material' => 'gutapercha', 'cantidad' => 2],
                    ['material' => 'cemento_endo', 'cantidad' => 0.5],
                ],
            ],
            [
                'treatment' => 'Endodoncia multirradicular',
                'lines' => [
                    ['material' => 'guantes_nitrilo', 'cantidad' => 1],
                    ['material' => 'articaina_4', 'cantidad' => 2],
                    ['material' => 'lima_k', 'cantidad' => 3],
                    ['material' => 'hipoclorito', 'cantidad' => 0.1],
                    ['material' => 'gutapercha', 'cantidad' => 3],
                    ['material' => 'cemento_endo', 'cantidad' => 0.6],
                ],
            ],
            [
                'treatment' => 'Curetaje',
                'lines' => [
                    ['material' => 'guantes_nitrilo', 'cantidad' => 1],
                    ['material' => 'cureta_gracey', 'cantidad' => 1],
                    ['material' => 'gasas_esteriles', 'cantidad' => 8],
                ],
            ],
            [
                'treatment' => 'Raspado y alisado radicular',
                'lines' => [
                    ['material' => 'guantes_nitrilo', 'cantidad' => 1],
                    ['material' => 'articaina_4', 'cantidad' => 2],
                    ['material' => 'cureta_gracey', 'cantidad' => 2],
                    ['material' => 'gasas_esteriles', 'cantidad' => 12],
                ],
            ],
            [
                'treatment' => 'Extracción simple',
                'lines' => [
                    ['material' => 'guantes_nitrilo', 'cantidad' => 1],
                    ['material' => 'articaina_4', 'cantidad' => 2],
                    ['material' => 'agujas_carpule', 'cantidad' => 2],
                    ['material' => 'gasas_quirurgicas', 'cantidad' => 6],
                    ['material' => 'esponja_hemostatica', 'cantidad' => 1],
                ],
            ],
            [
                'treatment' => 'Extracción quirúrgica',
                'lines' => [
                    ['material' => 'guantes_nitrilo', 'cantidad' => 1],
                    ['material' => 'articaina_4', 'cantidad' => 3],
                    ['material' => 'campo_esteril', 'cantidad' => 1],
                    ['material' => 'gasas_quirurgicas', 'cantidad' => 10],
                    ['material' => 'sutura_resorbible', 'cantidad' => 1],
                    ['material' => 'esponja_hemostatica', 'cantidad' => 2],
                ],
            ],
            [
                'treatment' => 'Extracción de cordal',
                'lines' => [
                    ['material' => 'guantes_nitrilo', 'cantidad' => 1],
                    ['material' => 'articaina_4', 'cantidad' => 4],
                    ['material' => 'campo_esteril', 'cantidad' => 2],
                    ['material' => 'gasas_quirurgicas', 'cantidad' => 15],
                    ['material' => 'sutura_resorbible', 'cantidad' => 2],
                    ['material' => 'esponja_hemostatica', 'cantidad' => 2],
                ],
            ],
            [
                'treatment' => 'Corona dental',
                'lines' => [
                    ['material' => 'guantes_nitrilo', 'cantidad' => 1],
                    ['material' => 'articaina_4', 'cantidad' => 1],
                    ['material' => 'alginato', 'cantidad' => 1],
                    ['material' => 'cubeta_impresion', 'cantidad' => 2],
                    ['material' => 'hilo_retraccion', 'cantidad' => 0.5],
                    ['material' => 'cemento_temporal', 'cantidad' => 0.3],
                ],
            ],
            [
                'treatment' => 'Puente dental',
                'lines' => [
                    ['material' => 'guantes_nitrilo', 'cantidad' => 1],
                    ['material' => 'alginato', 'cantidad' => 1.5],
                    ['material' => 'cubeta_impresion', 'cantidad' => 3],
                    ['material' => 'hilo_retraccion', 'cantidad' => 1],
                    ['material' => 'cemento_temporal', 'cantidad' => 0.5],
                ],
            ],
            [
                'treatment' => 'Blanqueamiento dental',
                'lines' => [
                    ['material' => 'guantes_nitrilo', 'cantidad' => 1],
                    ['material' => 'mascarilla_ffp2', 'cantidad' => 1],
                    ['material' => 'babero_desechable', 'cantidad' => 1],
                    ['material' => 'peroxido_blanqueamiento', 'cantidad' => 1],
                    ['material' => 'ferula_blanqueamiento', 'cantidad' => 1, 'notas' => 'Si técnica en consultorio con férula'],
                ],
            ],
            [
                'treatment' => 'Selladores',
                'lines' => [
                    ['material' => 'guantes_nitrilo', 'cantidad' => 1],
                    ['material' => 'babero_desechable', 'cantidad' => 1],
                    ['material' => 'acido_fosforico', 'cantidad' => 0.1],
                    ['material' => 'sellador_fosas', 'cantidad' => 0.5],
                ],
            ],
            [
                'treatment' => 'Pulpectomía',
                'lines' => [
                    ['material' => 'guantes_nitrilo', 'cantidad' => 1],
                    ['material' => 'articaina_4', 'cantidad' => 1],
                    ['material' => 'ionomero_vitreo', 'cantidad' => 1],
                    ['material' => 'gutapercha', 'cantidad' => 0.5],
                ],
            ],
            [
                'treatment' => 'Ortodoncia metálica',
                'lines' => [
                    ['material' => 'guantes_nitrilo', 'cantidad' => 1],
                    ['material' => 'arco_niti', 'cantidad' => 1],
                    ['material' => 'ligadura_elastica', 'cantidad' => 0.1],
                ],
            ],
            [
                'treatment' => 'Férula de descarga',
                'lines' => [
                    ['material' => 'guantes_nitrilo', 'cantidad' => 1],
                    ['material' => 'alginato', 'cantidad' => 1],
                    ['material' => 'cubeta_impresion', 'cantidad' => 2],
                ],
            ],
        ];
    }
}
