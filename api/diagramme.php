<?php
header('Content-Type: application/json');
require_once __DIR__ . "/../includes/db.php";
requireLogin();
$haushaltId = getAktivenHaushalt();
$jahr = (int)($_GET['jahr'] ?? date('Y'));
$aktMonat = (int)date('m');

// Kontostand
$stmt = $db->prepare('SELECT * FROM kontostand WHERE haushalt_id = ? ORDER BY datum DESC LIMIT 1');
$stmt->execute([$haushaltId]);
$kontostand = $stmt->fetch(PDO::FETCH_ASSOC);
$kontostandBetrag = $kontostand ? $kontostand['betrag'] : 0;
$kontostandDatum = $kontostand ? $kontostand['datum'] : "$jahr-01-01";

// Alle aktiven Buchungen
$stmt = $db->prepare("SELECT b.*, k.name as kategorie_name FROM buchungen b LEFT JOIN kategorien k ON b.kategorie_id = k.id WHERE b.haushalt_id = ? AND b.aktiv = 1");
$stmt->execute([$haushaltId]);
$alleBuchungen = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ist-Werte
$stmt = $db->prepare("
    SELECT strftime('%m', z.zahlungsdatum) as monat,
        COALESCE(SUM(CASE WHEN b.betrag > 0 THEN z.betrag ELSE 0 END), 0) as einnahmen,
        COALESCE(SUM(CASE WHEN b.betrag < 0 THEN ABS(z.betrag) ELSE 0 END), 0) as ausgaben
    FROM zahlungen z LEFT JOIN buchungen b ON z.buchung_id = b.id
    WHERE b.haushalt_id = ? AND strftime('%Y', z.zahlungsdatum) = ?
    GROUP BY monat ORDER BY monat
");
$stmt->execute([$haushaltId, (string)$jahr]);
$istRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$monatsDaten = [];
for ($m = 1; $m <= 12; $m++) {
    $monatStr = str_pad($m, 2, '0', STR_PAD_LEFT);
    $monatsDaten[$monatStr] = ['einnahmen' => 0, 'ausgaben' => 0];
}
foreach ($istRows as $row) {
    $monatsDaten[$row['monat']] = ['einnahmen' => $row['einnahmen'], 'ausgaben' => $row['ausgaben']];
}

// Prognose
$prognose = [];
for ($m = 1; $m <= 12; $m++) {
    $monatStr = str_pad($m, 2, '0', STR_PAD_LEFT);
    $prognose[$monatStr] = ['einnahmen' => 0, 'ausgaben' => 0];
    if ($m < $aktMonat) {
        $prognose[$monatStr] = $monatsDaten[$monatStr];
    } elseif ($m == $aktMonat) {
        $prognose[$monatStr] = $monatsDaten[$monatStr];
        $tageImMonat = cal_days_in_month(CAL_GREGORIAN, $m, $jahr);
        $tageBisher = (int)date('d');
        $restFaktor = $tageBisher > 0 ? ($tageImMonat - $tageBisher) / $tageBisher : 1;
        foreach ($alleBuchungen as $b) {
            $termin = berechneNaechstenTermin($b['start_datum'], $b['intervall'], "$jahr-$monatStr-01");
            if ($termin && substr($termin, 0, 7) == "$jahr-$monatStr") {
                if ($b['betrag'] > 0) $prognose[$monatStr]['einnahmen'] += $b['betrag'] * $restFaktor;
                else $prognose[$monatStr]['ausgaben'] += abs($b['betrag']) * $restFaktor;
            }
        }
    } else {
        foreach ($alleBuchungen as $b) {
            $termin = berechneNaechstenTermin($b['start_datum'], $b['intervall'], "$jahr-$monatStr-01");
            if ($termin && substr($termin, 0, 7) == "$jahr-$monatStr") {
                if ($b['betrag'] > 0) $prognose[$monatStr]['einnahmen'] += $b['betrag'];
                else $prognose[$monatStr]['ausgaben'] += abs($b['betrag']);
            }
        }
    }
}

// Kumulierter Kontostand
$kontostandProMonat = [];
$saldo = $kontostandBetrag;
for ($m = 1; $m <= 12; $m++) {
    $monatStr = str_pad($m, 2, '0', STR_PAD_LEFT);
    $saldaoA = $prognose[$monatStr]['einnahmen'] - $prognose[$monatStr]['ausgaben'];
    if ($m >= (int)date('m', strtotime($kontostandDatum))) {
        $saldo += $saldaoA;
    }
    $kontostandProMonat[$monatStr] = $saldo;
}

// Kategorien-Verteilung
$stmt = $db->prepare("
    SELECT k.name, k.farbe, SUM(ABS(z.betrag)) as betrag
    FROM zahlungen z LEFT JOIN buchungen b ON z.buchung_id = b.id
    LEFT JOIN kategorien k ON b.kategorie_id = k.id
    WHERE b.haushalt_id = ? AND b.betrag < 0 AND strftime('%Y', z.zahlungsdatum) = ?
    GROUP BY k.id ORDER BY betrag DESC
");
$stmt->execute([$haushaltId, (string)$jahr]);
$kategorienVerteilung = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'jahr' => $jahr,
    'kontostand' => ['betrag' => $kontostandBetrag, 'datum' => $kontostandDatum],
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
    $intervalle = ['woechentlich' => '+7 days', 'monatlich' => '+1 month', 'vierteljaehrlich' => '+3 months', 'jaehrlich' => '+1 year'];
    if (!isset($intervalle[$intervall])) return null;
    $termin = clone $start;
    $max = 60;
    while ($termin < $ab && $max-- > 0) $termin->modify($intervalle[$intervall]);
    return $termin->format('Y-m-d');
}
