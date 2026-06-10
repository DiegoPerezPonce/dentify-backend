<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Data\StockAndProtocolSeed;
use App\Data\TreatmentCatalogSeed;

$catalogNames = [];
foreach (TreatmentCatalogSeed::categories() as $c) {
    foreach ($c['treatments'] as $t) {
        $catalogNames[] = $t['name'];
    }
}

$materialKeys = array_column(StockAndProtocolSeed::materials(), 'key');
$dup = array_diff_assoc($materialKeys, array_unique($materialKeys));

echo "=== Stock & protocol seed validation ===\n\n";
echo 'Materials: ' . count($materialKeys) . "\n";
echo 'Duplicate keys: ' . (empty($dup) ? 'none' : implode(', ', $dup)) . "\n\n";

$missingMat = [];
$protocolTreatments = [];
$usedMaterials = [];
foreach (StockAndProtocolSeed::protocols() as $p) {
    $protocolTreatments[] = $p['treatment'];
    foreach ($p['lines'] as $line) {
        $usedMaterials[] = $line['material'];
        if (!in_array($line['material'], $materialKeys, true)) {
            $missingMat[] = $p['treatment'] . ' -> ' . $line['material'];
        }
    }
}
echo 'Protocol lines with bad material ref: ' . count($missingMat) . "\n";
foreach ($missingMat as $m) {
    echo "  - $m\n";
}

$noCatalog = array_diff($protocolTreatments, $catalogNames);
echo "\nProtocols not in treatment catalog: " . count($noCatalog) . "\n";
foreach ($noCatalog as $t) {
    echo "  - $t\n";
}

$noProtocol = array_diff($catalogNames, $protocolTreatments);
echo "\nCatalog treatments without protocol (" . count($noProtocol) . "):\n";
foreach ($noProtocol as $t) {
    echo "  - $t\n";
}

$unused = array_diff($materialKeys, array_unique($usedMaterials));
echo "\nMaterials not used in any protocol (" . count($unused) . "):\n";
foreach ($unused as $k) {
    echo "  - $k\n";
}

echo "\n" . (empty($missingMat) && empty($noCatalog) && empty($dup) ? "OK\n" : "ISSUES FOUND\n");
