<?php
$pageTitle = 'Hilfe - Haushaltsplanung';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="mb-4">
    <h4><i class="bi bi-question-circle me-2"></i>Hilfe & Anleitung</h4>
</div>

<!-- Navigation -->
<ul class="nav nav-pills mb-4" id="hilfeNav">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tabUeberblick">
            <i class="bi bi-eye me-1"></i>Überblick
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tabKategorien">
            <i class="bi bi-tags me-1"></i>Kategorien
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tabBuchungen">
            <i class="bi bi-journal-text me-1"></i>Buchungen
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tabZahlungen">
            <i class="bi bi-cash-stack me-1"></i>Zahlungen
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tabDashboard">
            <i class="bi bi-speedometer2 me-1"></i>Dashboard
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tabFAQ">
            <i class="bi bi-chat-dots me-1"></i>FAQ
        </button>
    </li>
</ul>

<div class="tab-content">

    <!-- Überblick -->
    <div class="tab-pane fade show active" id="tabUeberblick">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5>Willkommen bei der Haushaltsplanung</h5>
                <p class="lead">Diese App hilft Ihnen, Einnahmen und Ausgaben zu verwalten und Ihre Finanzen im Blick zu behalten.</p>
                
                <hr>
                
                <h6><i class="bi bi-1-circle-fill text-primary me-2"></i>Kategorien anlegen</h6>
                <p>Erstellen Sie Kategorien für Ihre Einnahmen und Ausgaben. Unterscheiden Sie zwischen:</p>
                <ul>
                    <li><strong>Einnahmen:</strong> Gehalt, Freelance, Zinsen, etc.</li>
                    <li><strong>Fixkosten:</strong> Miete, Versicherung, Internet (gleicher Betrag jeden Monat)</li>
                    <li><strong>Variable Kosten:</strong> Lebensmittel, Freizeit (Betrag kann schwanken)</li>
                </ul>
                
                <h6><i class="bi bi-2-circle-fill text-primary me-2"></i>Buchungen erstellen</h6>
                <p>Legen Sie wiederkehrende Buchungen an mit einem Intervall:</p>
                <ul>
                    <li>Wöchentlich, Monatlich, Vierteljährlich oder Jährlich</li>
                    <li>Einmalige Buchungen sind ebenfalls möglich</li>
                </ul>
                
                <h6><i class="bi bi-3-circle-fill text-primary me-2"></i>Zahlungen erfassen</h6>
                <p>Tragen Sie ein, wann Geld geflossen ist (Eingang oder Abgang). So wissen Sie immer, was bereits passiert ist.</p>
                
                <h6><i class="bi bi-4-circle-fill text-primary me-2"></i>Kontostand & Prognose</h6>
                <p>Geben Sie Ihren aktuellen Kontostand ein. Die App berechnet automatisch, wie viel Geld Sie voraussichtlich bis Jahresende haben werden.</p>
                
                <div class="alert alert-success mt-4">
                    <i class="bi bi-lightbulb me-2"></i>
                    <strong>Tipp:</strong> Beginnen Sie mit den Kategorien, legen Sie dann Ihre Buchungen an und erfassen Sie regelmäßig Zahlungen.
                </div>
            </div>
        </div>
    </div>

    <!-- Kategorien -->
    <div class="tab-pane fade" id="tabKategorien">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5><i class="bi bi-tags me-2"></i>Kategorien verwalten</h5>
                
                <h6 class="mt-4">Neue Kategorie anlegen</h6>
                <ol>
                    <li>Klicken Sie auf <button class="btn btn-sm btn-primary"><i class="bi bi-plus-circle me-1"></i>Neue Kategorie</button></li>
                    <li>Geben Sie einen <strong>Namen</strong> ein (z.B. "Miete", "Lebensmittel")</li>
                    <li>Wählen Sie den <strong>Typ</strong>: Einnahme oder Ausgabe</li>
                    <li>Wählen Sie die <strong>Art</strong>: Fixkosten oder Variabel</li>
                    <li>Wählen Sie eine <strong>Farbe</strong> für die Diagramme</li>
                    <li>Klicken Sie auf <strong>Speichern</strong></li>
                </ol>
                
                <h6 class="mt-4">Kategorien filtern</h6>
                <p>Verwenden Sie die Filter oben, um nach Typ, Art oder Status zu suchen.</p>
                
                <h6 class="mt-4">Kategorien bearbeiten/deaktivieren</h6>
                <ul>
                    <li><i class="bi bi-pencil text-primary"></i> <strong>Bearbeiten:</strong> Name, Typ, Farbe ändern</li>
                    <li><i class="bi bi-pause text-warning"></i> <strong>Pausieren:</strong> Kategorie deaktivieren (wird nicht mehr in Berechnungen verwendet)</li>
                    <li><i class="bi bi-play text-success"></i> <strong>Aktivieren:</strong> Kategorie wieder aktiv schalten</li>
                    <li><i class="bi bi-trash text-danger"></i> <strong>Löschen:</strong> Kategorie unwiderruflich entfernen</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Buchungen -->
    <div class="tab-pane fade" id="tabBuchungen">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5><i class="bi bi-journal-text me-2"></i>Buchungen verwalten</h5>
                
                <p>Buchungen sind <strong>wiederkehrende Zahlungsströme</strong> (z.B. monatliches Gehalt, vierteljährliche Versicherung).</p>
                
                <h6 class="mt-4">Neue Buchung anlegen</h6>
                <ol>
                    <li>Klicken Sie auf <button class="btn btn-sm btn-primary"><i class="bi bi-plus-circle me-1"></i>Neue Buchung</button></li>
                    <li>Wählen Sie eine <strong>Kategorie</strong></li>
                    <li>Geben Sie den <strong>Betrag</strong> ein:
                        <ul>
                            <li><strong>Positiv</strong> = Einnahme (z.B. +3000 Gehalt)</li>
                            <li><strong>Negativ</strong> = Ausgabe (z.B. -1200 Miete)</li>
                        </ul>
                    </li>
                    <li>Wählen Sie das <strong>Intervall</strong>:
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
                    </li>
                    <li>Setzen Sie das <strong>Startdatum</strong> (erster Zahlungstermin)</li>
                    <li>Optional: <strong>Enddatum</strong> für befristete Buchungen</li>
                </ol>
                
                <div class="alert alert-info mt-3">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Wichtig:</strong> Buchungen sind keine Zahlungen! Sie definieren nur, <em>wie oft</em> etwas gezahlt wird. Die tatsächliche Zahlung erfassen Sie separat unter "Zahlungen".
                </div>
            </div>
        </div>
    </div>

    <!-- Zahlungen -->
    <div class="tab-pane fade" id="tabZahlungen">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5><i class="bi bi-cash-stack me-2"></i>Zahlungen erfassen</h5>
                
                <p>Zahlungen sind die <strong>tatsächlichen Geldflüsse</strong>. Hier tragen Sie ein, wenn Geld ein- oder ausgegangen ist.</p>
                
                <h6 class="mt-4">Neue Zahlung erfassen</h6>
                <ol>
                    <li>Klicken Sie auf <button class="btn btn-sm btn-primary"><i class="bi bi-plus-circle me-1"></i>Neue Zahlung</button></li>
                    <li>Wählen Sie die <strong>Buchung</strong> aus (z.B. "Miete - Warmmiete")</li>
                    <li>Der <strong>Betrag</strong> wird automatisch vorausgefüllt</li>
                    <li>Setzen Sie das <strong>Zahlungsdatum</strong> (wann ist Geld geflossen?)</li>
                    <li>Optional: <strong>Bemerkung</strong> hinzufügen</li>
                </ol>
                
                <h6 class="mt-4">Tagesbilanz</h6>
                <p>Oben auf der Seite sehen Sie die Bilanz des heutigen Tages:</p>
                <ul>
                    <li><strong class="text-success">Einnahmen (heute)</strong> - Was heute reingekommen ist</li>
                    <li><strong class="text-danger">Ausgaben (heute)</strong> - Was heute rausgegangen ist</li>
                    <li><strong>Bilanz (heute)</strong> - Differenz</li>
                </ul>
                
                <div class="alert alert-warning mt-3">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Hinweis:</strong> Zahlungen werden für die Prognose und die Diagramme verwendet. Erfassen Sie Zahlungen regelmäßig, damit die Daten aktuell bleiben!
                </div>
            </div>
        </div>
    </div>

    <!-- Dashboard -->
    <div class="tab-pane fade" id="tabDashboard">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5><i class="bi bi-speedometer2 me-2"></i>Dashboard</h5>
                
                <h6 class="mt-4">Kennzahlen-Karten</h6>
                <div class="row">
                    <div class="col-md-6">
                        <ul>
                            <li><strong class="text-success">Einnahmen (Jahr)</strong> - Summe aller Einnah-Zahlungen im aktuellen Jahr</li>
                            <li><strong class="text-danger">Ausgaben (Jahr)</strong> - Summe aller Ausgaben-Zahlungen im aktuellen Jahr</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul>
                            <li><strong>Bilanz (Jahr)</strong> - Einnahmen minus Ausgaben</li>
                            <li><strong>Ersparnis (Monat)</strong> - Überschuss im aktuellen Monat</li>
                        </ul>
                    </div>
                </div>
                
                <h6 class="mt-4">Kontostand & Prognose</h6>
                <p>Hier können Sie Ihren <strong>aktuellen Kontostand</strong> eintragen. Die App berechnet daraus:</p>
                <ul>
                    <li>Den <strong>Kontostand-Verlauf</strong> (blaue Linie im Diagramm)</li>
                    <li>Die <strong>Prognose bis Jahresende</strong> (rechte Karte)</li>
                </ul>
                
                <h6 class="mt-4">Diagramme</h6>
                <ul>
                    <li><strong>Balkendiagramm:</strong> Monatlicher Verlauf von Einnahmen und Ausgaben (Ist + Prognose)</li>
                    <li><strong>Kontostand-Linie:</strong> Blaue Linie zeigt den erwarteten Kontostand pro Monat</li>
                    <li><strong>Donut-Diagramm:</strong> Verteilung der Ausgaben nach Kategorie</li>
                </ul>
                
                <h6 class="mt-4">Tabellen</h6>
                <ul>
                    <li><strong>Anstehende Kosten:</strong> Die nächsten fälligen Ausgaben</li>
                    <li><strong>Letzte Transaktionen:</strong> Die 10 neuesten Zahlungen</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- FAQ -->
    <div class="tab-pane fade" id="tabFAQ">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5><i class="bi bi-chat-dots me-2"></i>Häufig gestellte Fragen</h5>
                
                <div class="accordion mt-4" id="faqAccordion">
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                Was ist der Unterschied zwischen "Buchung" und "Zahlung"?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <strong>Buchung</strong> definiert einen <em>wiederkehrenden Zahlungsfluss</em> (z.B. "Miete: -1200€ monatlich").<br>
                                <strong>Zahlung</strong> ist die <em>tatsächliche Erfassung</em>, dass Geld geflossen ist (z.B. "Miete am 01.05.2026 gezahlt").<br><br>
                                <em>Beispiel:</em> Eine Buchung "Gehalt +3000€ monatlich" existiert ein Mal. Jeden Monat erfassen Sie eine Zahlung, wenn das Geld auf dem Konto ist.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Wie funktioniert die Jahresend-Prognose?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Die Prognose berechnet:<br>
                                <code>Kontostand + (Monatlicher Saldo × Restmonate)</code><br><br>
                                Dabei werden nur <strong>aktive, wiederkehrende Buchungen</strong> berücksichtigt. Vergangene Monate verwenden die Ist-Werte aus den Zahlungen.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Was passiert, wenn ich eine Kategorie lösche?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Alle zugehörigen <strong>Buchungen und Zahlungen</strong> werden ebenfalls gelöscht (Cascading Delete).<br>
                                <strong>Tipp:</strong> Deaktivieren Sie die Kategorie stattdessen, um die Daten zu erhalten.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                Kann ich die Daten sichern?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Ja! Die Datenbank liegt unter:<br>
                                <code>/var/www/sqlite/haushaltsplanung.db</code><br><br>
                                Kopieren Sie diese Datei, um ein Backup zu erstellen.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                Warum werden manche Monate im Diagramm nicht angezeigt?
                            </button>
                        </h2>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Monate ohne Zahlungen zeigen <strong>0 €</strong> an. Erfassen Sie regelmäßig Zahlungen, damit die Daten vollständig sind.
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
