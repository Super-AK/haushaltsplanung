# Haushaltsplanung v2.2

WebApp zur Einnahmen-/Ausgabenverwaltung mit Multi-User-Unterstuetzung, geteilten Haushalten und Jahresend-Prognose.

## Features

- **Multi-User** mit Rollen (Admin/Benutzer)
- **Haushalte teilen** zwischen Usern mit Berechtigungen (Lesen/Schreiben/Besitzer)
- **Dashboard** mit Kennzahlen und Chart.js-Diagrammen
- **Kontostand-Prognose** bis Jahresende
- **Kategorien** fuer Einnahmen, Fixkosten, Variable Kosten
- **Buchungen** mit wiederkehrenden Intervallen
- **Zahlungserfassung** mit Historie
- **Daten kopieren** zwischen Haushalten mit Dublikat-Erkennung
- **Massen-Loeschung** fuer Kategorien und Buchungen
- **Update-sicher** via Migrationssystem

## Technologien

- PHP 8.5 + SQLite3
- Bootstrap 5 + jQuery
- Chart.js fuer Diagramme
- Nginx als Webserver

## Installation

```bash
git clone https://github.com/Super-AK/haushaltsplanung.git
cp -r haushaltsplanung/* /var/www/html/
# Seite im Browser oeffnen - DB wird automatisch erstellt
```

## Updates

```bash
cd /var/www/html
git pull
# Datenbank bleibt erhalten, Migrationen laufen automatisch
```

## Standard-Login

| User | Passwort | Rolle |
|------|----------|-------|
| admin | admin123 | Admin |
| demo | demo123 | Benutzer |

## Struktur

```
├── index.php                    # Dashboard
├── api/                         # REST-API (JSON)
│   ├── auth.php                 # Login/Logout
│   ├── users.php                # User-Verwaltung (Admin)
│   ├── user_haushalte.php       # User-Haushalt-Zuordnung
│   ├── haushalte.php            # Haushalte CRUD
│   ├── haushalt_stats.php       # Statistiken + Besitzer
│   ├── haushalt_kopieren.php    # Daten kopieren
│   ├── kategorien.php           # Kategorien CRUD
│   ├── buchungen.php            # Buchungen CRUD
│   ├── zahlungen.php            # Zahlungen erfassen
│   ├── kontostand.php           # Kontostand verwalten
│   ├── dashboard.php            # Dashboard-Daten
│   └── diagramme.php            # Diagramm-Daten + Prognose
├── pages/                       # Seiten
│   ├── login.php                # Login-Seite
│   ├── users.php                # User-Verwaltung (Admin)
│   ├── haushalte.php            # Haushalte-Uebersicht
│   ├── kategorien.php
│   ├── buchungen.php
│   ├── zahlungen.php
│   └── hilfe.php                # Hilfe & Anleitung
├── includes/                    # PHP-Includes
│   ├── db.php                   # DB + Auth + Berechtigungen
│   ├── header.php               # Navbar + Modals
│   └── footer.php               # Footer + Scripts
├── assets/                      # CSS + JavaScript
├── setup/                       # Setup + Migrationen
│   ├── init_db.php              # Erstinitialisierung
│   ├── demo_data.php            # Demo-Daten
│   └── migrate.php              # Schema-Migrationen
└── sqlite/                      # SQLite Datenbank (nicht in Git)
```

## Lizenz

MIT
