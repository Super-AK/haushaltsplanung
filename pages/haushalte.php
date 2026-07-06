<?php
$pageTitle = 'Haushalte - Haushaltsplanung';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-house-door me-2"></i>Haushalte Uebersicht</h4>
    <button class="btn btn-primary" onclick="oeffneNeuenHaushalt()"><i class="bi bi-plus-circle me-1"></i>Neuer Haushalt</button>
</div>

<div id="loading" class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>

<div id="inhalt" style="display:none;">
    <div class="row mb-4">
        <div class="col-md-3"><div class="card stat-card shadow-sm text-center py-3"><div class="h3 mb-0 text-primary" id="totalHaushalte">0</div><div class="text-muted">Haushalte</div></div></div>
        <div class="col-md-3"><div class="card stat-card einnahme shadow-sm text-center py-3"><div class="h3 mb-0 text-success" id="totalEinnahmen">0 EUR</div><div class="text-muted">Einnahmen gesamt</div></div></div>
        <div class="col-md-3"><div class="card stat-card ausgabe shadow-sm text-center py-3"><div class="h3 mb-0 text-danger" id="totalAusgaben">0 EUR</div><div class="text-muted">Ausgaben gesamt</div></div></div>
        <div class="col-md-3"><div class="card stat-card bilanz shadow-sm text-center py-3"><div class="h3 mb-0" id="totalBilanz">0 EUR</div><div class="text-muted">Bilanz gesamt</div></div></div>
    </div>
    <div class="row" id="haushaltGrid"></div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<script>
$(document).ready(function() { ladeHaushalte(); });

async function ladeHaushalte() {
    try {
        var data = await App.api.get('/api/haushalt_stats.php');
        var aktiverId = <?= $aktiverHaushalt ?? 'null' ?>;

        $('#totalHaushalte').text(data.haushalte.length);
        var totalEin = 0, totalAus = 0;
        data.haushalte.forEach(function(h) { totalEin += h.einnahmen; totalAus += h.ausgaben; });
        $('#totalEinnahmen').text(App.formatCurrency(totalEin));
        $('#totalAusgaben').text(App.formatCurrency(totalAus));
        var bilanzEl = $('#totalBilanz');
        bilanzEl.text(App.formatCurrency(totalEin - totalAus));
        bilanzEl.removeClass('text-success text-danger');
        bilanzEl.addClass((totalEin - totalAus) >= 0 ? 'text-success' : 'text-danger');

        var grid = $('#haushaltGrid');
        grid.empty();

        data.haushalte.forEach(function(h) {
            var istAktiv = h.id == aktiverId;
            var bilanzClass = h.bilanz >= 0 ? 'text-success' : 'text-danger';
            var kannLoeschen = h.recht === 'besitzer' && data.haushalte.length > 1;
            var kannWechseln = h.id != aktiverId;

            var rechtBadge = '';
            if (h.recht === 'besitzer') rechtBadge = '<span class="badge bg-success ms-1">Besitzer</span>';
            else if (h.recht === 'schreiben') rechtBadge = '<span class="badge bg-info ms-1">Schreiben</span>';
            else rechtBadge = '<span class="badge bg-secondary ms-1">Lesen</span>';

            grid.append(
                '<div class="col-xl-4 col-md-6 mb-4">' +
                '<div class="card shadow-sm h-100 ' + (istAktiv ? 'border-primary border-2' : '') + '">' +
                '<div class="card-header d-flex justify-content-between align-items-center ' + (istAktiv ? 'bg-primary text-white' : '') + '">' +
                '<h6 class="mb-0"><i class="bi bi-house me-1"></i>' + h.name +
                (h.ist_demo ? ' <span class="badge bg-warning text-dark ms-1">Demo</span>' : '') +
                (istAktiv ? ' <span class="badge bg-light text-primary ms-1">Aktiv</span>' : '') +
                rechtBadge +
                '</h6><div>' +
                (kannWechseln ? '<button class="btn btn-sm btn-outline-' + (istAktiv ? 'light' : 'primary') + ' me-1" onclick="wechsleHaushalt(' + h.id + ')" title="Wechseln"><i class="bi bi-arrow-left-right"></i></button>' : '') +
                (kannLoeschen ? '<button class="btn btn-sm btn-outline-danger" onclick="oeffneHaushaltLoeschen(' + h.id + ', \'' + h.name.replace(/'/g, "\\'") + '\')" title="Loeschen"><i class="bi bi-trash"></i></button>' : '') +
                '</div></div>' +
                '<div class="card-body">' +
                '<div class="row text-center mb-3">' +
                '<div class="col"><div class="text-muted small">Einnahmen</div><div class="fw-bold text-success">' + App.formatCurrency(h.einnahmen) + '</div></div>' +
                '<div class="col"><div class="text-muted small">Ausgaben</div><div class="fw-bold text-danger">' + App.formatCurrency(h.ausgaben) + '</div></div>' +
                '<div class="col"><div class="text-muted small">Bilanz</div><div class="fw-bold ' + bilanzClass + '">' + App.formatCurrency(h.bilanz) + '</div></div>' +
                '</div>' +
                '<table class="table table-sm mb-0">' +
                '<tr><td class="text-muted">Kontostand</td><td class="text-end fw-bold">' + (h.kontostand !== null ? App.formatCurrency(h.kontostand) : '-') + '</td></tr>' +
                '<tr><td class="text-muted">Kategorien</td><td class="text-end">' + h.kategorien + '</td></tr>' +
                '<tr><td class="text-muted">Buchungen</td><td class="text-end">' + h.buchungen + '</td></tr>' +
                '<tr><td class="text-muted">Zahlungen</td><td class="text-end">' + h.zahlungen + '</td></tr>' +
                '</table></div></div></div>'
            );
        });

        $('#loading').hide();
        $('#inhalt').show();
    } catch (error) { console.error(error); App.error('Fehler beim Laden'); }
}
</script>
