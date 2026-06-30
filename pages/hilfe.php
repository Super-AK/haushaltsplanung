<?php
$pageTitle = 'Hilfe - Haushaltsplanung';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="mb-4">
    <h4><i class="bi bi-question-circle me-2"></i>Hilfe & Anleitung</h4>
</div>

<ul class="nav nav-pills mb-4" id="hilfeNav">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tabUeberblick"><i class="bi bi-eye me-1"></i>Überblick</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tabHaushalte"><i class="bi bi-house me-1"></i>Haushalte</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tabKategorien"><i class="bi bi-tags me-1"></i>Kategorien</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tabBuchungen"><i class="bi bi-journal-text me-1"></i>Buchungen</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tabZahlungen"><i class="bi bi-cash-stack me-1"></i>Zahlungen</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tabDashboard"><i class="bi bi-speedometer2 me-1"></i>Dashboard</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tabFAQ"><i class="bi bi-chat-dots me-1"></i>FAQ</button></li>
</ul>

<div class="tab-content">

    <!-- Überblick -->
    <div class="tab-pane fade show active" id="tabUeberblick">
        <div class="card shadow-sm"><div class="card-body">
            <h5>Willkommen bei der Haushaltsplanung</h5>
            <p class="lead">Verwalten Sie Einnahmen und Ausgaben für einen oder mehrere Haushalte.</p>
            <hr>
            <h6><i class="bi bi-1-circle-fill text-primary me-2"></i>Haushalt anlegen</h6>
            <p>Erstellen Sie über das Dropdown in der Navbar einen neuen Haushalt. Sie können Beispieldaten laden, um sofort loszulegen.</p>
            <h6><i class="bi bi-2-circle-fill text-primary me-2"></i>Kategorien anlegen</h6>
            <p>Erstellen Sie Kategorien für Einnahmen und Ausgaben (Fixkosten oder variabel).</p>
            <h6><i class="bi bi-3-circle-fill text-primary me-2"></i>Buchungen erstellen</h6>
            <p>Legen Sie wiederkehrende Zahlungsströme an (monatlich, vierteljährlich, etc.).</p>
            <h6><i class="bi bi-4-circle-fill text-primary me-2"></i>Zahlungen erfassen</h6>
            <p>Tragen Sie ein, wann Geld geflossen ist.</p>
            <h6><i class="bi bi-5-circle-fill text-primary me-2"></i>Kontostand & Prognose</h6>
            <p>Geben Sie Ihren aktuellen Kontostand ein — die App berechnet die Prognose bis Jahresende.</p>
            <div class="alert alert-success mt-4"><i class="bi bi-lightbulb me-2"></i><strong>Tipp:</strong> Nutzen Sie "Haushalte" in der Navbar für eine Gesamtübersicht aller Haushalte mit Statistiken.</div>
        </div></div>
    </div>

    <!-- Haushalte -->
    <div class="tab-pane fade" id="tabHaushalte">
        <div class="card shadow-sm"><div class="card-body">
            <h5><i class="bi bi-house me-2"></i>Haushalte verwalten</h5>
            <p>Sie können mehrere Haushalte anlegen — z.B. für verschiedene Familien, Testdaten oder Nettoshaushalt.</p>

            <h6 class="mt-4">Haushalt wechseln</h6>
            <ol>
                <li>Klicken Sie auf den <strong>Haushalt-Namen</strong> in der Navbar (rechts)</li>
                <li>Wählen Sie den gewünschten Haushalt aus der Liste</li>
                <li>Die Seite lädt neu — alle Daten beziehen sich nun auf den gewählten Haushalt</li>
            </ol>

            <h6 class="mt-4">Neuen Haushalt anlegen</h6>
            <ol>
                <li>Navbar-Dropdown öffnen → <strong>"Neuer Haushalt"</strong></li>
                <li>Name eingeben (z.B. "Familie Müller")</li>
                <li>Optional: <strong>"Beispieldaten laden"</strong> aktivieren</li>
                <li>Haushalt wird erstellt und automatisch aktiv geschaltet</li>
            </ol>

            <h6 class="mt-4">Haushalt löschen</h6>
            <ul>
                <li>Nur möglich wenn <strong>mehr als ein Haushalt</strong> vorhanden ist</li>
                <li>Alle Kategorien, Buchungen und Zahlungen werden gelöscht</li>
                <li>Im Dropdown auf den <strong>Mülltonnen-Button</strong> klicken</li>
            </ul>

            <h6 class="mt-4">Haushalte-Übersicht</h6>
            <p>Unter <strong>"Haushalte"</strong> in der Navbar sehen Sie eine Gesamtübersicht:</p>
            <ul>
                <li>Statistiken zu jedem Haushalt (Einnahmen, Ausgaben, Bilanz)</li>
                <li>Anzahl Kategorien, Buchungen, Zahlungen</li>
                <li>Aktueller Kontostand</li>
                <li>Wechsel- und Löschen-Buttons</li>
            </ul>

            <div class="alert alert-info mt-3"><i class="bi bi-info-circle me-2"></i>Daten sind vollständig isoliert — ein Haushalt sieht nie die Daten eines anderen.</div>
        </div></div>
    </div>

    <!-- Kategorien -->
    <div class="tab-pane fade" id="tabKategorien">
        <div class="card shadow-sm"><div class="card-body">
            <h5><i class="bi bi-tags me-2"></i>Kategorien verwalten</h5>
            <h6 class="mt-4">Neue Kategorie anlegen</h6>
            <ol>
                <li>Klicken Sie auf <button class="btn btn-sm btn-primary"><i class="bi bi-plus-circle me-1"></i>Neue Kategorie</button></li>
                <li>Name, Typ (Einnahme/Ausgabe), Art (Fix/Variabel) und Farbe wählen</li>
            </ol>
            <h6 class="mt-4">Aktionen</h6>
            <ul>
                <li><i class="bi bi-pencil text-primary"></i> Bearbeiten</li>
                <li><i class="bi bi-pause text-warning"></i> Deaktivieren (Daten bleiben erhalten)</li>
                <li><i class="bi bi-trash text-danger"></i> Löschen (Cascading — auch zugehörige Buchungen)</li>
            </ul>
        </div></div>
    </div>

    <!-- Buchungen -->
    <div class="tab-pane fade" id="tabBuchungen">
        <div class="card shadow-sm"><div class="card-body">
            <h5><i class="bi bi-journal-text me-2"></i>Buchungen verwalten</h5>
            <p>Buchungen sind <strong>wiederkehrende Zahlungsströme</strong>.</p>
            <h6 class="mt-4">Intervalle</h6>
            <table class="table table-sm mt-2">
                <thead><tr><th>Intervall</th><th>Beispiel</th></tr></thead>
                <tbody>
                    <tr><td><span class="badge badge-einmalig">Einmalig</span></td><td>Einmaliger Einkauf</td></tr>
                    <tr><td><span class="badge badge-woechentlich">Wöchentlich</span></td><td>Wocheneinkauf</td></tr>
                    <tr><td><span class="badge badge-monatlich">Monatlich</span></td><td>Miete, Gehalt</td></tr>
                    <tr><td><span class="badge badge-vierteljaehrlich">Vierteljährlich</span></td><td>Versicherung</td></tr>
                    <tr><td><span class="badge badge-jaehrlich">Jährlich</span></td><td>Kfz-Steuer</td></tr>
                </tbody>
            </table>
            <div class="alert alert-info mt-3"><i class="bi bi-info-circle me-2"></i><strong>Wichtig:</strong> Buchungen definieren nur, <em>wie oft</em> etwas gezahlt wird. Die tatsächliche Zahlung erfassen Sie unter "Zahlungen".</div>
        </div></div>
    </div>

    <!-- Zahlungen -->
    <div class="tab-pane fade" id="tabZahlungen">
        <div class="card shadow-sm"><div class="card-body">
            <h5><i class="bi bi-cash-stack me-2"></i>Zahlungen erfassen</h5>
            <p>Zahlungen sind die <strong>tatsächlichen Geldflüsse</strong>.</p>
            <h6 class="mt-4">Tagesbilanz</h6>
            <p>Oben auf der Seite sehen Sie Einnahmen, Ausgaben und Bilanz des heutigen Tages.</p>
            <div class="alert alert-warning mt-3"><i class="bi bi-exclamation-triangle me-2"></i>Erfassen Sie Zahlungen regelmäßig — sie fließen in die Prognose und Diagramme ein!</div>
        </div></div>
    </div>

    <!-- Dashboard -->
    <div class="tab-pane fade" id="tabDashboard">
        <div class="card shadow-sm"><div class="card-body">
            <h5><i class="bi bi-speedometer2 me-2"></i>Dashboard</h5>
            <h6 class="mt-4">Kennzahlen</h6>
            <ul>
                <li><strong class="text-success">Einnahmen (Jahr)</strong> — Summe aller Einnahmen</li>
                <li><strong class="text-danger">Ausgaben (Jahr)</strong> — Summe aller Ausgaben</li>
                <li><strong>Bilanz (Jahr)</strong> — Differenz</li>
                <li><strong>Ersparnis (Monat)</strong> — Überschuss aktueller Monat</li>
            </ul>
            <h6 class="mt-4">Kontostand & Prognose</h6>
            <p>Tragen Sie Ihren aktuellen Kontostand ein. Das Balkendiagramm zeigt den <strong>Kontostand-Verlauf</strong> (blaue Linie) und die <strong>Jahresend-Prognose</strong>.</p>
            <h6 class="mt-4">Diagramme</h6>
            <ul>
                <li><strong>Balkendiagramm:</strong> Monatlicher Einnahmen/Ausgaben-Verlauf (Ist + Prognose)</li>
                <li><strong>Donut:</strong> Ausgaben nach Kategorie</li>
            </ul>
        </div></div>
    </div>

    <!-- FAQ -->
    <div class="tab-pane fade" id="tabFAQ">
        <div class="card shadow-sm"><div class="card-body">
            <h5><i class="bi bi-chat-dots me-2"></i>FAQ</h5>
            <div class="accordion mt-4" id="faqAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header"><button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">Was ist der Unterschied zwischen "Buchung" und "Zahlung"?</button></h2>
                    <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion"><div class="accordion-body">
                        <strong>Buchung</strong> = wiederkehrender Zahlungsfluss (z.B. "Miete: -1200€ monatlich").<br>
                        <strong>Zahlung</strong> = tatsächliche Erfassung, dass Geld geflossen ist.
                    </div></div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">Wie funktioniert die Jahresend-Prognose?</button></h2>
                    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body">
                        <code>Kontostand + (Monatlicher Saldo × Restmonate)</code><br>
                        Nur aktive, wiederkehrende Buchungen werden berücksichtigt.
                    </div></div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">Was passiert wenn ich einen Haushalt lösche?</button></h2>
                    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body">
                        Alle Kategorien, Buchungen und Zahlungen werden gelöscht (Cascading). Der letzte Haushalt kann nicht gelöscht werden.
                    </div></div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">Kann ich die Daten sichern?</button></h2>
                    <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body">
                        Ja! Die Datenbank liegt unter:<br><code>sqlite/haushaltsplanung.db</code><br>Kopieren Sie diese Datei als Backup.
                    </div></div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">Sind die Haushalte voneinander getrennt?</button></h2>
                    <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body">
                        Ja, jeder Haushalt hat eigene Kategorien, Buchungen, Zahlungen und einen eigenen Kontostand. Es gibt keine Datenübertragung zwischen Haushalten.
                    </div></div>
                </div>
            </div>
        </div></div>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
