# Changelog

Alle wesentlichen Aenderungen an der Haushaltsplanung.

Das Format orientiert sich an [Keep a Changelog](https://keepachangelog.com/de/1.1.0/),
die Versionierung folgt [Semantic Versioning](https://semver.org/).

## [v2.5.0] - 2026-08-05

### Hinzugefuegt
- Filter unter Zahlungen (Kategorie, Typ, Zeitraum von/bis) - Tagesbilanz bleibt vom Filter unabhaengig
- CSV-Export der Zahlungen (gefiltert oder manuell ausgewaehlt) mit Checkbox-Auswahl und Massenaktionen
- Haushalt umbenennen (mit Schreib-/Besitzer-Recht, serverseitig geprueft)
- Filter reagieren sofort auf Aenderungen (Zahlungen, Buchungen, Kategorien)

### Geaendert
- Betragsvorzeichen bei Buchungen und Zahlungen: automatisch passend zur Kategorie gesetzt und serverseitig erzwungen (Ausgabe negativ, Einnahme positiv); Doppel-Minus behoben
- Heute-Bilanz korrekt berechnet (Ausgaben positiv gerechnet, Bilanz = Einnahmen - Ausgaben)
- Cache-Busting fuer alle Seiten-JS, damit veraltete Browser-Caches keine alten Skripte ausliefern

### Behoben
- Kontostand-Prognose startet beim erfassten Stand statt nach der ersten Monatsbewegung
- Prognose-Balken werden nur ab dem aktuellen Monat angezeigt, nicht in der Vergangenheit
- Monatsverlauf-Diagramm: Nullpunkte beider Y-Achsen synchronisiert, Achsen-Titel ergaenzt

### Sicherheit
- Automatische Abmeldung nach 15 Minuten Inaktivitaet (Session-TTL + kurzes user_id-Cookie)

## [v2.4.1] - 2026-08-05

### Behoben
- Remote-500 nach v2.4: selbstheilende Migration (12-Schritte-Prozedur statt RENAME) + defensives Datums-Parsing
- Intervall-Badge 'halbjaehrlich' war unsichtbar (Cache-Busting fuer CSS/JS)

## [v2.4.0] - 2026-08-05

### Hinzugefuegt
- Halbjaehrliches Intervall fuer Buchungen
- Automatische Erzeugung von Zahlungen aus Buchungen

### Behoben
- Prognose-Bugfixes
