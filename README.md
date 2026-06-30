# Haushaltsplanung v2.0

WebApp zur Einnahmen-/Ausgabenverwaltung mit Dashboard, Diagrammen, Jahresend-Prognose und Multi-Haushalt-Unterstuetzung.

## Features

- **Multi-Haushalt** - Mehrere Haushalte anlegen, wechseln, loeschen
- **Dashboard** mit Kennzahlen-Karten und Chart.js-Diagrammen
- **Kontostand-Eingabe** mit automatischer Prognose bis Jahresende
- **Kategorien-Verwaltung** (Einnahmen, Fixkosten, Variable Kosten)
- **Buchungen** mit wiederkehrenden Intervallen (woechentlich bis jaehrlich)
- **Zahlungserfassung** mit Historie und Tagesbilanz
- **Daten kopieren** zwischen Haushalten mit Dublikat-Erkennung
- **Massen-Loeschung** mit Checkboxen fuer Kategorien und Buchungen
- **Hilfe-Seite** mit interaktiver Anleitung
- **REST API** fuer alle Operationen

## Technologien

- PHP 8.5 + SQLite3
- Bootstrap 5 + jQuery
- Chart.js fuer Diagramme
- Nginx als Webserver

## Installation

```bash
# Repository klonen
git clone https://github.com/Super-AK/haushaltsplanung.git

# In Webroot kopieren
cp -r haushaltsplanung/* /var/www/html/

# Datenbank initialisieren (automatisch beim ersten Aufruf)
```

## Struktur

```
├── index.php                    # Dashboard
├── api/                         # REST-API (JSON)
│   ├── haushalte.php            # Haushalte CRUD
│   ├── haushalt_stats.php       # Haushalt-Statistiken
│   ├── haushalt_kopieren.php    # Daten kopieren
│   ├── kategorien.php           # Kategorien CRUD
│   ├── buchungen.php            # Buchungen CRUD
│   ├── zahlungen.php            # Zahlungen erfassen
│   ├── kontostand.php           # Kontostand verwalten
│   ├── dashboard.php            # Dashboard-Daten
│   └── diagramme.php            # Diagramm-Daten + Prognose
├── pages/                       # Seiten
│   ├── kategorien.php
│   ├── buchungen.php
│   ├── zahlungen.php
│   ├── haushalte.php            # Haushalte-Uebersicht
│   └── hilfe.php                # Hilfe & Anleitung
├── assets/                      # CSS + JavaScript
├── includes/                    # PHP-Includes (DB, Header, Footer)
├── setup/                       # Datenbank-Initialisierung
│   ├── init_db.php
│   └── demo_data.php
└── sqlite/                      # SQLite Datenbank
```

## API-Beispiele

```bash
# Haushalte auflisten
curl http://localhost/api/haushalte.php

# Kategorien eines Haushalts
curl http://localhost/api/kategorien.php

# Kontostand setzen
curl -X POST http://localhost/api/kontostand.php \
  -H "Content-Type: application/json" \
  -d '{"betrag": 5000, "datum": "2026-06-30"}'
```

## Lizenz

MIT
