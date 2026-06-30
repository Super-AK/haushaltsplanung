# Haushaltsplanung

WebApp zur Einnahmen-/Ausgabenverwaltung mit Dashboard, Diagrammen und Jahresend-Prognose.

## Features

- **Dashboard** mit Kennzahlen-Karten und Chart.js-Diagrammen
- **Kontostand-Eingabe** mit automatischer Prognose bis Jahresende
- **Kategorien-Verwaltung** (Einnahmen, Fixkosten, Variable Kosten)
- **Buchungen** mit wiederkehrenden Intervallen (wöchentlich bis jährlich)
- **Zahlungserfassung** mit Historie und Tagesbilanz
- **Hilfe-Seite** mit interaktiver Anleitung

## Technologien

- PHP 8.5 + SQLite3
- Bootstrap 5 + jQuery
- Chart.js für Diagramme
- Nginx als Webserver

## Installation

```bash
# Repository klonen
git clone https://github.com/Super-AK/haushaltsplanung.git

# In Webroot kopieren
cp -r haushaltsplanung/* /var/www/html/

# Datenbank initialisieren
curl http://localhost/setup/init_db.php
```

## Struktur

```
├── index.php              # Dashboard
├── api/                   # REST-API (JSON)
├── pages/                 # Seiten (Kategorien, Buchungen, Zahlungen, Hilfe)
├── assets/                # CSS + JavaScript
├── includes/              # PHP-Includes (DB, Header, Footer)
└── setup/                 # Datenbank-Initialisierung
```

## Lizenz

MIT
