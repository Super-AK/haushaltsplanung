var monatsChart = null;
var kategorienChart = null;
var transaktionenOffset = 10;
var transaktionenGesamt = 0;

$(document).ready(function() {
    ladeDashboard();
    $('#kontostandDatum').val(new Date().toISOString().split('T')[0]);
    
    // Form Submit
    $('#kontostandForm').on('submit', function(e) {
        e.preventDefault();
        speichereKontostand();
    });
    
    // Edit Button
    $('#btnKontostandEdit').on('click', function() {
        bearbeiteKontostand();
    });
    
    // Delete Button
    $('#btnKontostandDelete').on('click', function() {
        loescheKontostand();
    });
});

async function ladeDashboard() {
    try {
        var dashboard = await App.api.get('/api/dashboard.php');
        var diagramme = await App.api.get('/api/diagramme.php');
        
        if (diagramme.kontostand && diagramme.kontostand.betrag) {
            $('#aktKontostand').text(App.formatCurrency(diagramme.kontostand.betrag));
            $('#aktKontostandDatum').text(App.formatDate(diagramme.kontostand.datum));
            $('#aktKontostandBemerkung').text(diagramme.kontostand.bemerkung ? '(' + diagramme.kontostand.bemerkung + ')' : '');
            $('#kontostandInfo').show();
        } else {
            $('#kontostandInfo').hide();
        }
        
        var dezSaldo = diagramme.jahresendPrognose != null ? diagramme.jahresendPrognose : (diagramme.kontostandProMonat['12'] || 0);
        var prognoseEl = $('#jahresEndPrognose');
        prognoseEl.text(App.formatCurrency(dezSaldo));
        prognoseEl.removeClass('text-success text-danger text-warning');
        prognoseEl.addClass(dezSaldo > 0 ? 'text-success' : dezSaldo < 0 ? 'text-danger' : 'text-warning');
        
        $('#jahresEinnahmen').text(App.formatCurrency(dashboard.jahresBilanz.einnahmen));
        $('#jahresAusgaben').text(App.formatCurrency(dashboard.jahresBilanz.ausgaben));
        
        var bilanzEl = $('#jahresBilanz');
        bilanzEl.text(App.formatCurrency(dashboard.jahresBilanz.bilanz));
        bilanzEl.removeClass('text-success text-danger');
        bilanzEl.addClass(dashboard.jahresBilanz.bilanz >= 0 ? 'text-success' : 'text-danger');
        
        var ersparnisEl = $('#monatsErsparnis');
        ersparnisEl.text(App.formatCurrency(dashboard.monatsBilanz.bilanz));
        ersparnisEl.removeClass('text-success text-danger');
        ersparnisEl.addClass(dashboard.monatsBilanz.bilanz >= 0 ? 'text-success' : 'text-danger');
        
        var anstehendBody = $('#tabelleAnstehend tbody').empty();
        if (dashboard.anstehendeKosten.length === 0) {
            anstehendBody.append('<tr><td colspan="3" class="text-muted text-center">Keine anstehenden Kosten</td></tr>');
        } else {
            dashboard.anstehendeKosten.forEach(function(k) {
                anstehendBody.append('<tr><td>' + App.formatDate(k.naechste_zahlung) + '</td><td>' + k.kategorie_name + '</td><td class="text-danger">' + App.formatCurrency(k.betrag) + '</td></tr>');
            });
        }
        
        transaktionenGesamt = dashboard.transaktionenGesamt != null ? dashboard.transaktionenGesamt : dashboard.letzteTransaktionen.length;
        transaktionenOffset = dashboard.transaktionenLimit != null ? dashboard.transaktionenLimit : dashboard.letzteTransaktionen.length;
        renderTransaktionen(dashboard.letzteTransaktionen, true);
        aktualisiereTransaktionenButton();
        
        zeichneMonatsChart(diagramme);
        zeichneKategorienChart(diagramme.kategorien);
        
        $('#loading').hide();
        $('#dashboard').show();
    } catch (error) {
        console.error('Fehler:', error);
        App.error('Fehler beim Laden der Dashboard-Daten');
    }
}

function renderTransaktionen(transaktionen, leeren) {
    var body = $('#tabelleTransaktionen tbody');
    if (leeren) body.empty();
    if (transaktionen.length === 0) {
        if (leeren) body.append('<tr><td colspan="3" class="text-muted text-center">Keine Transaktionen</td></tr>');
        return;
    }
    transaktionen.forEach(function(t) {
        var bc = t.typ === 'einnahme' ? 'text-success' : 'text-danger';
        var p = t.typ === 'einnahme' ? '+' : '-';
        body.append('<tr><td>' + App.formatDate(t.zahlungsdatum) + '</td><td>' + t.kategorie_name + '</td><td class="' + bc + '">' + p + App.formatCurrency(Math.abs(t.betrag)) + '</td></tr>');
    });
}

function aktualisiereTransaktionenButton() {
    $('#btnMehrTransaktionen').toggle(transaktionenOffset < transaktionenGesamt);
}

async function ladeMehrTransaktionen() {
    try {
        var data = await App.api.get('/api/dashboard.php?transaktionen_limit=10&transaktionen_offset=' + transaktionenOffset);
        transaktionenOffset += data.letzteTransaktionen.length;
        if (data.transaktionenGesamt != null) transaktionenGesamt = data.transaktionenGesamt;
        renderTransaktionen(data.letzteTransaktionen, false);
        aktualisiereTransaktionenButton();
    } catch (error) {
        console.error('Fehler:', error);
        App.error('Fehler beim Laden weiterer Transaktionen');
    }
}

async function speichereKontostand() {
    var data = {
        betrag: parseFloat($('#kontostandBetrag').val()),
        datum: $('#kontostandDatum').val(),
        bemerkung: $('#kontostandBemerkung').val() || null
    };
    
    try {
        var editId = $('#kontostandForm').data('edit-id');
        if (editId) {
            data.id = editId;
            await App.api.put('/api/kontostand.php', data);
            App.success('Kontostand aktualisiert');
        } else {
            await App.api.post('/api/kontostand.php', data);
            App.success('Kontostand gespeichert');
        }
        $('#kontostandBetrag').val('');
        $('#kontostandBemerkung').val('');
        $('#kontostandDatum').val(new Date().toISOString().split('T')[0]);
        $('#kontostandForm').data('edit-id', null);
        $('#btnKontostandSave').html('<i class="bi bi-check-circle me-1"></i>Kontostand speichern');
        await ladeDashboard();
    } catch (error) {
        console.error('Fehler:', error);
        App.error('Fehler beim Speichern');
    }
}

async function bearbeiteKontostand() {
    try {
        var kontostand = await App.api.get('/api/kontostand.php');
        if (kontostand.length === 0) { App.error('Kein Kontostand vorhanden'); return; }
        var k = kontostand[0];
        $('#kontostandBetrag').val(k.betrag);
        $('#kontostandDatum').val(k.datum);
        $('#kontostandBemerkung').val(k.bemerkung || '');
        $('#kontostandForm').data('edit-id', k.id);
        $('#btnKontostandSave').html('<i class="bi bi-check-circle me-1"></i>Kontostand aktualisieren');
        $('#kontostandBetrag').focus();
    } catch (error) {
        console.error('Fehler:', error);
        App.error('Fehler beim Laden');
    }
}

async function loescheKontostand() {
    try {
        var kontostand = await App.api.get('/api/kontostand.php');
        if (kontostand.length === 0) { App.error('Kein Kontostand vorhanden'); return; }
        if (!confirm('Kontostand wirklich loeschen?')) return;
        await App.api.delete('/api/kontostand.php?id=' + kontostand[0].id);
        App.success('Kontostand geloescht');
        $('#kontostandForm').data('edit-id', null);
        $('#btnKontostandSave').html('<i class="bi bi-check-circle me-1"></i>Kontostand speichern');
        await ladeDashboard();
    } catch (error) {
        console.error('Fehler:', error);
        App.error('Fehler beim Loeschen');
    }
}

function zeichneMonatsChart(data) {
    var ctx = document.getElementById('monatsChart').getContext('2d');
    var monatsnamen = ['Jan', 'Feb', 'Mar', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez'];
    
    var einnahmen = data.monate.map(function(m) { return data.ist[m] ? data.ist[m].einnahmen : 0; });
    var ausgaben = data.monate.map(function(m) { return data.ist[m] ? data.ist[m].ausgaben : 0; });
    var prognoseEin = data.monate.map(function(m) { return data.prognose[m] ? data.prognose[m].einnahmen : 0; });
    var prognoseAus = data.monate.map(function(m) { return data.prognose[m] ? data.prognose[m].ausgaben : 0; });
    var kontostandLinie = data.monate.map(function(m) { return data.kontostandProMonat ? data.kontostandProMonat[m] : null; });
    
    var yMin = 0, yMax, y1Min = 0, y1Max = 1;
    yMax = Math.max.apply(null, einnahmen.concat(ausgaben, prognoseEin, prognoseAus));
    if (yMax <= 0) yMax = 1;
    var kwerte = kontostandLinie.filter(function(v) { return v !== null && !isNaN(v); });
    if (kwerte.length > 0) {
        var kMin = Math.min.apply(null, kwerte);
        var kMax = Math.max.apply(null, kwerte);
        var kPad = Math.max((kMax - kMin) * 0.08, 1);
        y1Min = kMin - kPad;
        y1Max = kMax + kPad;
        if (y1Min < 0 && y1Max > 0) {
            var f = -y1Min / (y1Max - y1Min);
            yMin = f * yMax / (f - 1);
        }
    }
    
    if (monatsChart) monatsChart.destroy();
    monatsChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: monatsnamen,
            datasets: [
                { label: 'Einnahmen (Ist)', data: einnahmen, backgroundColor: 'rgba(28, 200, 138, 0.8)', borderWidth: 1 },
                { label: 'Ausgaben (Ist)', data: ausgaben, backgroundColor: 'rgba(231, 74, 59, 0.8)', borderWidth: 1 },
                { label: 'Einnahmen (Prognose)', data: prognoseEin, backgroundColor: 'rgba(28, 200, 138, 0.3)', borderWidth: 1 },
                { label: 'Ausgaben (Prognose)', data: prognoseAus, backgroundColor: 'rgba(231, 74, 59, 0.3)', borderWidth: 1 },
                { label: 'Kontostand', data: kontostandLinie, type: 'line', borderColor: '#0d6efd', backgroundColor: 'rgba(13, 110, 253, 0.1)', borderWidth: 3, pointRadius: 4, pointBackgroundColor: '#0d6efd', fill: true, tension: 0.3, yAxisID: 'y1' }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } },
            scales: {
                y: { min: yMin, max: yMax, position: 'left', title: { display: true, text: 'Einnahmen / Ausgaben' }, ticks: { callback: function(v) { return App.formatCurrency(v); } } },
                y1: { min: y1Min, max: y1Max, position: 'right', grid: { drawOnChartArea: false }, title: { display: true, text: 'Kontostand' }, ticks: { callback: function(v) { return App.formatCurrency(v); } } }
            }
        }
    });
}

function zeichneKategorienChart(kategorien) {
    var ctx = document.getElementById('kategorienChart').getContext('2d');
    var labels = kategorien.map(function(k) { return k.name; });
    var betraege = kategorien.map(function(k) { return k.betrag; });
    var farben = kategorien.map(function(k) { return k.farbe; });
    
    if (kategorienChart) kategorienChart.destroy();
    kategorienChart = new Chart(ctx, {
        type: 'doughnut',
        data: { labels: labels, datasets: [{ data: betraege, backgroundColor: farben, borderWidth: 2 }] },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { padding: 15 } } }
        }
    });
}
