<?php
/**
 * Dashboard API - Aggregierte Daten
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

$heute = date('Y-m-d');
$monat = date('Y-m');
$jahr = date('Y');

// Gesamtbalance (bisherige Zahlungen)
$stmt = $db->query("
    SELECT 
        COALESCE(SUM(CASE WHEN b.betrag > 0 THEN z.betrag ELSE 0 END), 0) as einnahmen,
        COALESCE(SUM(CASE WHEN b.betrag < 0 THEN ABS(z.betrag) ELSE 0 END), 0) as ausgaben
    FROM zahlungen z
    LEFT JOIN buchungen b ON z.buchung_id = b.id
    WHERE z.zahlungsdatum LIKE '$jahr-%'
");
$jahresBilanz = $stmt->fetch(PDO::FETCH_ASSOC);

// Aktueller Monat (bisherige Zahlungen)
$stmt = $db->prepare("
    SELECT 
        COALESCE(SUM(CASE WHEN b.betrag > 0 THEN z.betrag ELSE 0 END), 0) as einnahmen,
        COALESCE(SUM(CASE WHEN b.betrag < 0 THEN ABS(z.betrag) ELSE 0 END), 0) as ausgaben
    FROM zahlungen z
    LEFT JOIN buchungen b ON z.buchung_id = b.id
    WHERE z.zahlungsdatum LIKE ?
");
$stmt->execute([$monat . '%']);
$monatsBilanz = $stmt->fetch(PDO::FETCH_ASSOC);

// Anstehende Fixkosten (nächste 30 Tage)
$stmt = $db->query("
    SELECT b.*, k.name as kategorie_name, k.farbe
    FROM buchungen b
    LEFT JOIN kategorien k ON b.kategorie_id = k.id
    WHERE b.aktiv = 1 AND b.betrag < 0
    ORDER BY b.start_datum
");
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

// Letzte 10 Transaktionen
$stmt = $db->query("
    SELECT z.*, b.beschreibung as buchung_beschreibung, b.betrag as buchung_betrag,
           k.name as kategorie_name, k.typ, k.farbe
    FROM zahlungen z
    LEFT JOIN buchungen b ON z.buchung_id = b.id
    LEFT JOIN kategorien k ON b.kategorie_id = k.id
    ORDER BY z.zahlungsdatum DESC
    LIMIT 10
");
$letzteTransaktionen = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'jahresBilanz' => [
        'einnahmen' => $jahresBilanz['einnahmen'],
        'ausgaben' => $jahresBilanz['ausgaben'],
        'bilanz' => $jahresBilanz['einnahmen'] - $jahresBilanz['ausgaben']
    ],
    'monatsBilanz' => [
        'einnahmen' => $monatsBilanz['einnahmen'],
        'ausgaben' => $monatsBilanz['ausgaben'],
        'bilanz' => $monatsBilanz['einnahmen'] - $monatsBilanz['ausgaben']
    ],
    'anstehendeKosten' => $anstehende,
    'letzteTransaktionen' => $letzteTransaktionen
]);

/**
 * Berechnet den nächsten Termin für eine wiederkehrende Buchung
 */
function berechneNaechstenTermin($startDatum, $intervall, $abDatum) {
    $start = new DateTime($startDatum);
    $ab = new DateTime($abDatum);
    
    // Falls Startdatum in der Zukunft, nimm Startdatum
    if ($start > $ab) {
        return $start->format('Y-m-d');
    }
    
    $intervalle = [
        'woechentlich' => '+7 days',
        'monatlich' => '+1 month',
        'vierteljaehrlich' => '+3 months',
        'jaehrlich' => '+1 year'
    ];
    
    if (!isset($intervalle[$intervall])) {
        return null;
    }
    
    $termin = clone $start;
    while ($termin < $ab) {
        $termin->modify($intervalle[$intervall]);
    }
    
    return $termin->format('Y-m-d');
}
