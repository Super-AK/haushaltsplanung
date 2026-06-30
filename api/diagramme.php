<?php
/**
 * Diagramme API - Monatsübersicht + Prognose mit Kontostand
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

$jahr = (int)($_GET['jahr'] ?? date('Y'));
$heute = date('Y-m-d');
$aktMonat = (int)date('m');

// Letzter bekannter Kontostand
$stmt = $db->query('SELECT * FROM kontostand ORDER BY datum DESC LIMIT 1');
$kontostand = $stmt->fetch(PDO::FETCH_ASSOC);
$kontostandBetrag = $kontostand ? $kontostand['betrag'] : 0;
$kontostandDatum = $kontostand ? $kontostand['datum'] : "$jahr-01-01";

// Alle aktiven Buchungen laden
$stmt = $db->query("SELECT b.*, k.name as kategorie_name FROM buchungen b LEFT JOIN kategorien k ON b.kategorie_id = k.id WHERE b.aktiv = 1");
$alleBuchungen = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Monatliche Ist-Werte (bisherige Zahlungen)
$stmt = $db->prepare("
    SELECT 
        strftime('%m', z.zahlungsdatum) as monat,
        COALESCE(SUM(CASE WHEN b.betrag > 0 THEN z.betrag ELSE 0 END), 0) as einnahmen,
        COALESCE(SUM(CASE WHEN b.betrag < 0 THEN ABS(z.betrag) ELSE 0 END), 0) as ausgaben
    FROM zahlungen z
    LEFT JOIN buchungen b ON z.buchung_id = b.id
    WHERE strftime('%Y', z.zahlungsdatum) = ?
    GROUP BY monat
    ORDER BY monat
");
$stmt->execute([(string)$jahr]);
$istRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$monatsDaten = [];
for ($m = 1; $m <= 12; $m++) {
    $monatStr = str_pad($m, 2, '0', STR_PAD_LEFT);
    $monatsDaten[$monatStr] = ['einnahmen' => 0, 'ausgaben' => 0];
}
foreach ($istRows as $row) {
    $monatsDaten[$row['monat']] = [
        'einnahmen' => $row['einnahmen'],
        'ausgaben' => $row['ausgaben']
    ];
}

// Prognose für Rest-Jahr
$prognose = [];
$kumuliert = 0; // Kumulierter Saldo ab Kontostand

for ($m = 1; $m <= 12; $m++) {
    $monatStr = str_pad($m, 2, '0', STR_PAD_LEFT);
    $prognose[$monatStr] = ['einnahmen' => 0, 'ausgaben' => 0];
    
    if ($m < $aktMonat) {
        // Vergangene Monate: Prognose = Ist-Wert
        $prognose[$monatStr]['einnahmen'] = $monatsDaten[$monatStr]['einnahmen'];
        $prognose[$monatStr]['ausgaben'] = $monatsDaten[$monatStr]['ausgaben'];
    } elseif ($m == $aktMonat) {
        // Aktueller Monat: Prognose = Ist + Rest geschätzt
        $prognose[$monatStr]['einnahmen'] = $monatsDaten[$monatStr]['einnahmen'];
        $prognose[$monatStr]['ausgaben'] = $monatsDaten[$monatStr]['ausgaben'];
        
        $tageImMonat = cal_days_in_month(CAL_GREGORIAN, $m, $jahr);
        $tageBisher = (int)date('d');
        $restFaktor = $tageBisher > 0 ? ($tageImMonat - $tageBisher) / $tageBisher : 1;
        
        foreach ($alleBuchungen as $b) {
            if (!$b['aktiv']) continue;
            $termin = berechneNaechstenTermin($b['start_datum'], $b['intervall'], "$jahr-$monatStr-01");
            if ($termin && substr($termin, 0, 7) == "$jahr-$monatStr") {
                if ($b['betrag'] > 0) {
                    $prognose[$monatStr]['einnahmen'] += $b['betrag'] * $restFaktor;
                } else {
                    $prognose[$monatStr]['ausgaben'] += abs($b['betrag']) * $restFaktor;
                }
            }
        }
    } else {
        // Zukünftige Monate: Prognose aus wiederkehrenden Buchungen
        foreach ($alleBuchungen as $b) {
            if (!$b['aktiv']) continue;
            $termin = berechneNaechstenTermin($b['start_datum'], $b['intervall'], "$jahr-$monatStr-01");
            if ($termin && substr($termin, 0, 7) == "$jahr-$monatStr") {
                if ($b['betrag'] > 0) {
                    $prognose[$monatStr]['einnahmen'] += $b['betrag'];
                } else {
                    $prognose[$monatStr]['ausgaben'] += abs($b['betrag']);
                }
            }
        }
    }
}

// Kumulierten Saldo pro Monat berechnen (ab Kontostand)
$kontostandProMonat = [];
$saldo = $kontostandBetrag;

// Bereits vergangene Monate: Nur Ist-Werte berücksichtigen (kein Kontostand da bereits verbucht)
for ($m = 1; $m < $aktMonat; $m++) {
    $monatStr = str_pad($m, 2, '0', STR_PAD_LEFT);
    $saldaoA = $monatsDaten[$monatStr]['einnahmen'] - $monatsDaten[$monatStr]['ausgaben'];
    // Bei vergangenen Monaten: Nur ab Kontostand-Datum zählen
    if ("$jahr-$monatStr-15" >= $kontostandDatum) {
        $saldo += $saldaoA;
    }
    $kontostandProMonat[$monatStr] = $saldo;
}

// Aktueller Monat: Kontostand + Summe vergangener Monate + Ist aktueller Monat
$monatStr = str_pad($aktMonat, 2, '0', STR_PAD_LEFT);
$saldo += $prognose[$monatStr]['einnahmen'] - $prognose[$monatStr]['ausgaben'];
$kontostandProMonat[$monatStr] = $saldo;

// Zukünftige Monate: Kontostand + kumuliert
for ($m = $aktMonat + 1; $m <= 12; $m++) {
    $monatStr = str_pad($m, 2, '0', STR_PAD_LEFT);
    $saldo += $prognose[$monatStr]['einnahmen'] - $prognose[$monatStr]['ausgaben'];
    $kontostandProMonat[$monatStr] = $saldo;
}

// Kategorien-Verteilung (Ausgaben nach Kategorie)
$stmt = $db->prepare("
    SELECT k.name, k.farbe, SUM(ABS(z.betrag)) as betrag
    FROM zahlungen z
    LEFT JOIN buchungen b ON z.buchung_id = b.id
    LEFT JOIN kategorien k ON b.kategorie_id = k.id
    WHERE b.betrag < 0 AND strftime('%Y', z.zahlungsdatum) = ?
    GROUP BY k.id
    ORDER BY betrag DESC
");
$stmt->execute([(string)$jahr]);
$kategorienVerteilung = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'jahr' => $jahr,
    'kontostand' => [
        'betrag' => $kontostandBetrag,
        'datum' => $kontostandDatum
    ],
    'monate' => ['01','02','03','04','05','06','07','08','09','10','11','12'],
    'ist' => $monatsDaten,
    'prognose' => $prognose,
    'kontostandProMonat' => $kontostandProMonat,
    'kategorien' => $kategorienVerteilung
]);

function berechneNaechstenTermin($startDatum, $intervall, $abDatum) {
    $start = new DateTime($startDatum);
    $ab = new DateTime($abDatum);
    if ($start > $ab) return $start->format('Y-m-d');
    
    $intervalle = [
        'woechentlich' => '+7 days',
        'monatlich' => '+1 month',
        'vierteljaehrlich' => '+3 months',
        'jaehrlich' => '+1 year'
    ];
    if (!isset($intervalle[$intervall])) return null;
    
    $termin = clone $start;
    $maxIter = 60;
    while ($termin < $ab && $maxIter-- > 0) {
        $termin->modify($intervalle[$intervall]);
    }
    return $termin->format('Y-m-d');
}
