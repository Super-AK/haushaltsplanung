<?php
$pageTitle = 'Hilfe - Haushaltsplanung';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="mb-4"><h4><i class="bi bi-question-circle me-2"></i>Hilfe & Anleitung</h4></div>

<ul class="nav nav-pills mb-4" id="hilfeNav">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tabUeberblick"><i class="bi bi-eye me-1"></i>Ueberblick</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tabHaushalte"><i class="bi bi-house me-1"></i>Haushalte</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tabKategorien"><i class="bi bi-tags me-1"></i>Kategorien</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tabBuchungen"><i class="bi bi-journal-text me-1"></i>Buchungen</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tabZahlungen"><i class="bi bi-cash-stack me-1"></i>Zahlungen</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tabDashboard"><i class="bi bi-speedometer2 me-1"></i>Dashboard</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tabUsers"><i class="bi bi-person me-1"></i>User</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tabAPI"><i class="bi bi-code-slash me-1"></i>API</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tabFAQ"><i class="bi bi-chat-dots me-1"></i>FAQ</button></li>
</ul>

<div class="tab-content">

    <!-- Ueberblick -->
    <div class="tab-pane fade show active" id="tabUeberblick">
        <div class="card shadow-sm"><div class="card-body">
            <h5>Haushaltsplanung v2.3</h5>
            <p class="lead">Verwalten Sie Einnahmen und Ausgaben fuer einen oder mehrere Haushalte mit Multi-User-Unterstuetzung.</p>
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <h6><i class="bi bi-check-circle text-success me-2"></i>Funktionen</h6>
                    <ul>
                        <li>Dashboard mit Diagrammen und Prognose</li>
                        <li>Multi-User mit Rollen (Admin/Benutzer)</li>
                        <li>Mehrere Haushalte (erstellen, wechseln, loeschen)</li>
                        <li>Haushalte zwischen Usern teilen</li>
                        <li>Kategorien fuer Einnahmen und Ausgaben</li>
                        <li>Wiederkehrende Buchungen mit Intervallen</li>
                        <li>Zahlungserfassung mit Historie</li>
                        <li>Kontostand: Erfassen, bearbeiten, loeschen</li>
                        <li>Jahresend-Prognose basierend auf Kontostand</li>
                        <li>Daten aus anderen Haushalten kopieren</li>
                        <li>Massen-Loeschung mit Checkboxen</li>
                        <li>Dublikat-Erkennung beim Kopieren</li>
                        <li>Update-sicheres Migrationssystem</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6><i class="bi bi-stack text-primary me-2"></i>Technologien</h6>
                    <ul>
                        <li>PHP 8.5 + SQLite3</li>
                        <li>Bootstrap 5 + jQuery</li>
                        <li>Chart.js fuer Diagramme</li>
                        <li>Nginx als Webserver</li>
                    </ul>
                    <h6 class="mt-3"><i class="bi bi-key text-warning me-2"></i>Standard-Login</h6>
                    <ul>
                        <li><strong>Admin:</strong> admin / admin123</li>
                        <li><strong>Benutzer:</strong> demo / demo123</li>
                    </ul>
                </div>
            </div>
        </div></div>
    </div>

    <!-- Haushalte -->
    <div class="tab-pane fade" id="tabHaushalte">
        <div class="card shadow-sm"><div class="card-body">
            <h5><i class="bi bi-house me-2"></i>Haushalte verwalten</h5>
            <p>Jeder Haushalt hat eigene Kategorien, Buchungen und Zahlungen. Admin sieht alle Haushalte mit Besitzer-Namen.</p>

            <h6 class="mt-4">Haushalte-Uebersicht</h6>
            <ul>
                <li>Gesamtuebersicht mit Statistiken pro Haushalt</li>
                <li><strong>Recht-Badge:</strong> Besitzer / Schreiben / Lesen</li>
                <li><strong>Besitzer-Name:</strong> Admin sieht wer Eigentuemer ist</li>
                <li>Wechsel- und Loesch-Buttons nur fuer Berechtigte</li>
            </ul>

            <h6 class="mt-4">Haushalt wechseln</h6>
            <p>Navbar-Dropdown -> Haushalt auswaehlen.</p>

            <h6 class="mt-4">Neuen Haushalt anlegen</h6>
            <p>Dropdown -> "Neuer Haushalt" -> Name eingeben -> optional Beispieldaten laden.</p>

            <h6 class="mt-4">Daten kopieren</h6>
            <p>Dropdown -> "Daten kopieren" -> Quell-Haushalt waehlen -> Kategorien/Buchungen/Zahlungen auswaehlen -> Kopieren.</p>

            <h6 class="mt-4">Haushalt loeschen</h6>
            <p>Nur Besitzer haben den Loesch-Button. Der letzte Haushalt kann nicht geloescht werden.</p>
        </div></div>
    </div>

    <!-- Kategorien -->
    <div class="tab-pane fade" id="tabKategorien">
        <div class="card shadow-sm"><div class="card-body">
            <h5><i class="bi bi-tags me-2"></i>Kategorien verwalten</h5>

            <h6 class="mt-4">Neue Kategorie</h6>
            <ol>
                <li>"Neue Kategorie" klicken</li>
                <li>Name, Typ (Einnahme/Ausgabe), Art (Fix/Variabel) waehlen</li>
                <li>Fuer Diagramme eine Farbe auswaehlen</li>
            </ol>

            <h6 class="mt-4">Massen-Loeschung</h6>
            <p>Checkboxen links anklicken oder "Alle auswaehlen" -> "Ausgewaehlte loeschen".</p>
        </div></div>
    </div>

    <!-- Buchungen -->
    <div class="tab-pane fade" id="tabBuchungen">
        <div class="card shadow-sm"><div class="card-body">
            <h5><i class="bi bi-journal-text me-2"></i>Buchungen verwalten</h5>
            <p>Buchungen sind <strong>wiederkehrende Zahlungsstroeme</strong>.</p>

            <h6 class="mt-4">Intervalle</h6>
            <table class="table table-sm mt-2">
                <thead><tr><th>Intervall</th><th>Beispiel</th></tr></thead>
                <tbody>
                    <tr><td><span class="badge badge-einmalig">Einmalig</span></td><td>Einmaliger Einkauf</td></tr>
                    <tr><td><span class="badge badge-woechentlich">Woechentlich</span></td><td>Wocheneinkauf</td></tr>
                    <tr><td><span class="badge badge-monatlich">Monatlich</span></td><td>Miete, Gehalt</td></tr>
                    <tr><td><span class="badge badge-vierteljaehrlich">Vierteljaehrlich</span></td><td>Versicherung</td></tr>
                    <tr><td><span class="badge badge-jaehrlich">Jaehrlich</span></td><td>Kfz-Steuer</td></tr>
                </tbody>
            </table>
        </div></div>
    </div>

    <!-- Zahlungen -->
    <div class="tab-pane fade" id="tabZahlungen">
        <div class="card shadow-sm"><div class="card-body">
            <h5><i class="bi bi-cash-stack me-2"></i>Zahlungen erfassen</h5>
            <p>Zahlungen sind die <strong>tatsaechlichen Geldfluesse</strong>.</p>

            <h6 class="mt-4">Neue Zahlung</h6>
            <ol>
                <li>"Neue Zahlung" klicken</li>
                <li>Buchung auswaehlen (Betrag wird vorausgefuellt)</li>
                <li>Zahlungsdatum setzen</li>
            </ol>

            <div class="alert alert-warning mt-3"><i class="bi bi-exclamation-triangle me-2"></i>Erfassen Sie Zahlungen regelmaessig - sie fliesen in die Prognose und Diagramme ein!</div>
        </div></div>
    </div>

    <!-- Dashboard -->
    <div class="tab-pane fade" id="tabDashboard">
        <div class="card shadow-sm"><div class="card-body">
            <h5><i class="bi bi-speedometer2 me-2"></i>Dashboard</h5>

            <h6 class="mt-4">Kennzahlen</h6>
            <ul>
                <li><strong class="text-success">Einnahmen (Jahr)</strong> - Summe aller Einnahmen</li>
                <li><strong class="text-danger">Ausgaben (Jahr)</strong> - Summe aller Ausgaben</li>
                <li><strong>Bilanz (Jahr)</strong> - Differenz</li>
                <li><strong>Ersparnis (Monat)</strong> - Prognose aktueller Monat</li>
            </ul>

            <h6 class="mt-4">Kontostand erfassen</h6>
            <ul>
                <li><strong>Aktueller Stand:</strong> Zeigt den letzten erfassten Kontostand mit Datum</li>
                <li><strong>Bearbeiten</strong> (Bleistift-Button): Formular wird mit Werten befuellt</li>
                <li><strong>Loeschen</strong> (Muelleimer-Button): Kontostand entfernen</li>
                <li><strong>Speichern:</strong> Neuen Kontostand erfassen oder bestehenden aktualisieren</li>
            </ul>

            <h6 class="mt-4">Jahresend-Prognose</h6>
            <ul>
                <li><strong>Rechte Karte:</strong> Erwarteter Kontostand am 31.12.</li>
                <li><strong>Blaue Linie:</strong> Kontostand-Verlauf ueber das Jahr</li>
                <li>Berechnung: Startkapital + monatlicher Saldo</li>
            </ul>

            <h6 class="mt-4">Diagramme</h6>
            <ul>
                <li><strong>Balkendiagramm:</strong> Monatlicher Verlauf (Ist + Prognose)</li>
                <li><strong>Donut:</strong> Ausgaben nach Kategorie</li>
            </ul>
        </div></div>
    </div>

    <!-- User -->
    <div class="tab-pane fade" id="tabUsers">
        <div class="card shadow-sm"><div class="card-body">
            <h5><i class="bi bi-person me-2"></i>User-Verwaltung</h5>
            <p>Nur Admins haben Zugriff auf diesen Bereich.</p>

            <h6 class="mt-4">Rollen</h6>
            <table class="table table-sm"><thead><tr><th>Rolle</th><th>Berechtigung</th></tr></thead><tbody>
            <tr><td><span class="badge bg-primary">Admin</span></td><td>Sieht alles, kann User verwalten, Haushalte zuordnen</td></tr>
            <tr><td><span class="badge bg-secondary">Benutzer</span></td><td>Sieht nur zugewiesene Haushalte</td></tr>
            </tbody></table>

            <h6 class="mt-4">User anlegen</h6>
            <ol>
                <li>Admin -> "Users" in der Navbar</li>
                <li>"Neuer User" -> Benutzername, Passwort, Rolle</li>
            </ol>

            <h6 class="mt-4">Haushalte zuordnen</h6>
            <ol>
                <li>Bei User auf <i class="bi bi-house"></i> klicken</li>
                <li>Haushalt auswaehlen + Recht setzen</li>
            </ol>

            <h6 class="mt-4">Rechte pro Haushalt</h6>
            <ul>
                <li><strong>Lesen</strong> - Daten ansehen</li>
                <li><strong>Schreiben</strong> - Buchungen/Zahlungen erfassen</li>
                <li><strong>Besitzer</strong> - Volle Kontrolle, kann anderen Usern Zugriff geben</li>
            </ul>
        </div></div>
    </div>

    <!-- API -->
    <div class="tab-pane fade" id="tabAPI">
        <div class="card shadow-sm"><div class="card-body">
            <h5><i class="bi bi-code-slash me-2"></i>REST API (JSON)</h5>
            <p>Alle Endpunkte erfordern Login (ausser auth.php).</p>
            <table class="table table-sm mt-3">
                <thead><tr><th>Endpunkt</th><th>Methoden</th><th>Beschreibung</th></tr></thead>
                <tbody>
                    <tr><td><code>/api/auth.php</code></td><td>GET, POST, DELETE</td><td>Login/Logout/Status</td></tr>
                    <tr><td><code>/api/users.php</code></td><td>GET, POST, PUT, DELETE</td><td>User-Verwaltung (Admin)</td></tr>
                    <tr><td><code>/api/user_haushalte.php</code></td><td>GET, POST, DELETE</td><td>User-Haushalt-Zuordnung</td></tr>
                    <tr><td><code>/api/haushalte.php</code></td><td>GET, POST, PUT, DELETE</td><td>Haushalte CRUD</td></tr>
                    <tr><td><code>/api/haushalt_stats.php</code></td><td>GET</td><td>Statistiken mit Besitzer</td></tr>
                    <tr><td><code>/api/haushalt_kopieren.php</code></td><td>POST</td><td>Daten kopieren</td></tr>
                    <tr><td><code>/api/kategorien.php</code></td><td>GET, POST, PUT, DELETE</td><td>Kategorien CRUD</td></tr>
                    <tr><td><code>/api/buchungen.php</code></td><td>GET, POST, PUT, DELETE</td><td>Buchungen CRUD</td></tr>
                    <tr><td><code>/api/zahlungen.php</code></td><td>GET, POST, DELETE</td><td>Zahlungen erfassen</td></tr>
                    <tr><td><code>/api/kontostand.php</code></td><td>GET, POST, PUT, DELETE</td><td>Kontostand CRUD</td></tr>
                    <tr><td><code>/api/dashboard.php</code></td><td>GET</td><td>Dashboard-Daten</td></tr>
                    <tr><td><code>/api/diagramme.php</code></td><td>GET</td><td>Diagramm-Daten + Prognose</td></tr>
                </tbody>
            </table>
        </div></div>
    </div>

    <!-- FAQ -->
    <div class="tab-pane fade" id="tabFAQ">
        <div class="card shadow-sm"><div class="card-body">
            <h5><i class="bi bi-chat-dots me-2"></i>FAQ</h5>
            <div class="accordion mt-4" id="faqAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header"><button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">Unterschied zwischen "Buchung" und "Zahlung"?</button></h2>
                    <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion"><div class="accordion-body">
                        <strong>Buchung</strong> = wiederkehrender Zahlungsfluss (z.B. "Miete: -1200EUR monatlich").<br>
                        <strong>Zahlung</strong> = tatsaechliche Erfassung, dass Geld geflossen ist.
                    </div></div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">Funktioniert die Jahresend-Prognose?</button></h2>
                    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body">
                        Ja. <code>Kontostand + (Monatlicher Saldo x Restmonate)</code>. Nur aktive, wiederkehrende Buchungen werden beruecksichtigt.
                    </div></div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">Was passiert beim Loeschen eines Haushalts?</button></h2>
                    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body">
                        Alle Kategorien, Buchungen und Zahlungen werden geloescht (Cascading). Nur Besitzer koennen loeschen. Der letzte Haushalt kann nicht geloescht werden.
                    </div></div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">Kann ich Daten sichern?</button></h2>
                    <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body">
                        Ja! Die Datenbank liegt unter <code>sqlite/haushaltsplanung.db</code>. Backup: <code>cp sqlite/haushaltsplanung.db backup.db</code>
                    </div></div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">Sind die Haushalte voneinander getrennt?</button></h2>
                    <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body">
                        Ja. Jeder Haushalt hat eigene Kategorien, Buchungen, Zahlungen und einen eigenen Kontostand. User sehen nur zugewiesene Haushalte.
                    </div></div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">Was ist "Daten kopieren"?</button></h2>
                    <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body">
                        Kopiert Kategorien, Buchungen und Zahlungen aus einem anderen Haushalt. Dublikate werden erkannt und uebersprungen. Einzelne Optionen wahlweise.
                    </div></div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq7">Was passiert beim Update (git pull)?</button></h2>
                    <div id="faq7" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body">
                        Die Datenbank bleibt erhalten. Neuer Code wird einggespielt. Schema-Aenderungen laufen automatisch als Migration. User, Haushalte und Daten bleiben erhalten.
                    </div></div>
                </div>
            </div>
        </div></div>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
