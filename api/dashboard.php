<?php
header('Content-Type: application/json');
require_once __DIR__ . "/../includes/db.php";
requireLogin();
$haushaltId = getAktivenHaushalt();
$heute = date('Y-m-d');
$monat = date('Y-m');
$jahr = date('Y');

// Jahresbilanz (bisherige Zahlungen)
$stmt = $db->prepare("
    SELECT 
        COALESCE(SUM(CASE WHEN b.betrag > 0 THEN z.betrag ELSE 0 END), 0) as einnahmen,
        COALESCE(SUM(CASE WHEN b.betrag < 0 THEN ABS(z.betrag) ELSE 0 END), 0) as ausgaben
    FROM zahlungen z LEFT JOIN buchungen b ON z.buchung_id = b.id
    WHERE b.haushalt_id = ? AND strftime('%Y', z.zahlungsdatum) = ?
");
$stmt->execute([$haushaltId, $jahr]);
$jahresBilanz = $stmt->fetch(PDO::FETCH_ASSOC);

// Monatsbilanz (Zahlungen aktueller Monat)
$stmt->execute([$haushaltId, $monat . '%']);
$monatsBilanz = $stmt->fetch(PDO::FETCH_ASSOC);

// Wenn keine Zahlungen im aktuellen Monat: Prognose aus Buchungen verwenden
if ($monatsBilanz['einnahmen'] == 0 && $monatsBilanz['ausgaben'] == 0) {
    $stmt = $db->prepare("SELECT * FROM buchungen WHERE haushalt_id = ? AND aktiv = 1");
    $stmt->execute([$haushaltId]);
    $alleBuchungen = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $aktMonat = (int)date('m');
    $prognoseEin = 0;
    $prognoseAus = 0;
    
    foreach ($alleBuchungen as $b) {
        $termin = berechneNaechstenTermin($b['start_datum'], $b['intervall'], "$jahr-" . str_pad($aktMonat, 2, '0', STR_PAD_LEFT) . "-01");
        if ($termin && substr($termin, 0, 7) == $monat) {
            if ($b['betrag'] > 0) $prognoseEin += $b['betrag'];
            else $prognoseAus += abs($b['betrag']);
        }
    }
    
    $monatsBilanz = [
        'einnahmen' => $prognoseEin,
        'ausgaben' => $prognoseAus
    ];
}

// Anstehende Fixkosten (naechste 30 Tage)
$stmt = $db->prepare("SELECT b.*, k.name as kategorie_name, k.farbe FROM buchungen b LEFT JOIN kategorien k ON b.kategorie_id = k.id WHERE b.haushalt_id = ? AND b.aktiv = 1 AND b.betrag < 0 ORDER BY b.start_datum");
$stmt->execute([$haushaltId]);
$alleFixkosten = $stmt->fetchAll(PDO::FETCH_ASSOC);

$anstehende = [];
foreach ($alleFixkosten as $fk) {
    $naechsterTermin = berechneNaechstenTermin($fk['start_datum'], $fk['intervall'], $heute);
    if ($naechsterTermin && $naechsterTermin <= date('Y-m-d', strtotime('+30 days'))) {
        $fk['naechste_zahlung'] = $naechsterTermin;
        $anstehende[] = $fk;
    }
}
usort($anstehende, fn($a, $b) => $a['naechste_zahlung'] <=> $b['naechste_zahlung']);
$anstehende = array_slice($anstehende, 0, 5);

// Letzte Transaktionen
$stmt = $db->prepare("
    SELECT z.*, b.beschreibung as buchung_beschreibung, b.betrag as buchung_betrag,
           k.name as kategorie_name, k.typ, k.farbe
    FROM zahlungen z LEFT JOIN buchungen b ON z.buchung_id = b.id
    LEFT JOIN kategorien k ON b.kategorie_id = k.id
    WHERE b.haushalt_id = ? ORDER BY z.zahlungsdatum DESC LIMIT 10
");
$stmt->execute([$haushaltId]);
$letzteTransaktionen = $stmt->fetchAll(PDO::FETCH_ASSOC);

$monatsBilanz['bilanz'] = $monatsBilanz['einnahmen'] - $monatsBilanz['ausgaben'];

echo json_encode([
    'jahresBilanz' => ['einnahmen' => $jahresBilanz['einnahmen'], 'ausgaben' => $jahresBilanz['ausgaben'], 'bilanz' => $jahresBilanz['einnahmen'] - $jahresBilanz['ausgaben']],
    'monatsBilanz' => $monatsBilanz,
    'anstehendeKosten' => $anstehende,
    'letzteTransaktionen' => $letzteTransaktionen
]);

function berechneNaechstenTermin($startDatum, $intervall, $abDatum) {
    $start = new DateTime($startDatum);
    $ab = new DateTime($abDatum);
    if ($start > $ab) return $start->format('Y-m-d');
    $intervalle = ['woechentlich' => '+7 days', 'monatlich' => '+1 month', 'vierteljaehrlich' => '+3 months', 'jaehrlich' => '+1 year'];
    if (!isset($intervalle[$intervall])) return null;
    $termin = clone $start;
    while ($termin < $ab) $termin->modify($intervalle[$intervall]);
    return $termin->format('Y-m-d');
}
