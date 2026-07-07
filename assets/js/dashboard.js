let monatsChart = null;
let kategorienChart = null;

$(document).ready(function() {
    ladeDashboard();
    $('#kontostandDatum').val(new Date().toISOString().split('T')[0]);
    $('#kontostandForm').on('submit', async function(e) {
        e.preventDefault();
        await speichereKontostand();
    });
});

async function ladeDashboard() {
    try {
        const dashboard = await App.api.get('/api/dashboard.php');
        const diagramme = await App.api.get('/api/diagramme.php');
        
        if (diagramme.kontostand && diagramme.kontostand.betrag) {
            $('#aktKontostand').text(App.formatCurrency(diagramme.kontostand.betrag));
            $('#aktKontostandDatum').text(App.formatDate(diagramme.kontostand.datum));
            $('#kontostandInfo').show();
            $('#kontostandLoeschen').show();
        } else {
            $('#kontostandInfo').hide();
            $('#kontostandLoeschen').hide();
        }
        
        const dezSaldo = diagramme.kontostandProMonat['12'] || 0;
        const prognoseEl = $('#jahresEndPrognose');
        prognoseEl.text(App.formatCurrency(dezSaldo));
        prognoseEl.removeClass('text-success text-danger text-warning');
        prognoseEl.addClass(dezSaldo > 0 ? 'text-success' : dezSaldo < 0 ? 'text-danger' : 'text-warning');
        
        $('#jahresEinnahmen').text(App.formatCurrency(dashboard.jahresBilanz.einnahmen));
        $('#jahresAusgaben').text(App.formatCurrency(dashboard.jahresBilanz.ausgaben));
        
        const bilanzEl = $('#jahresBilanz');
        bilanzEl.text(App.formatCurrency(dashboard.jahresBilanz.bilanz));
        bilanzEl.removeClass('text-success text-danger');
        bilanzEl.addClass(dashboard.jahresBilanz.bilanz >= 0 ? 'text-success' : 'text-danger');
        
        const ersparnisEl = $('#monatsErsparnis');
        ersparnisEl.text(App.formatCurrency(dashboard.monatsBilanz.bilanz));
        ersparnisEl.removeClass('text-success text-danger');
        ersparnisEl.addClass(dashboard.monatsBilanz.bilanz >= 0 ? 'text-success' : 'text-danger');
        
        const anstehendBody = $('#tabelleAnstehend tbody');
        anstehendBody.empty();
        if (dashboard.anstehendeKosten.length === 0) {
            anstehendBody.append('<tr><td colspan="3" class="text-muted text-center">Keine anstehenden Kosten</td></tr>');
        } else {
            dashboard.anstehendeKosten.forEach(function(k) {
                anstehendBody.append('<tr><td>' + App.formatDate(k.naechste_zahlung) + '</td><td>' + k.kategorie_name + '</td><td class="text-danger">' + App.formatCurrency(k.betrag) + '</td></tr>');
            });
        }
        
        const transBody = $('#tabelleTransaktionen tbody');
        transBody.empty();
        if (dashboard.letzteTransaktionen.length === 0) {
            transBody.append('<tr><td colspan="3" class="text-muted text-center">Keine Transaktionen</td></tr>');
        } else {
            dashboard.letzteTransaktionen.forEach(function(t) {
                const bc = t.typ === 'einnahme' ? 'text-success' : 'text-danger';
                const p = t.typ === 'einnahme' ? '+' : '-';
                transBody.append('<tr><td>' + App.formatDate(t.zahlungsdatum) + '</td><td>' + t.kategorie_name + '</td><td class="' + bc + '">' + p + App.formatCurrency(t.betrag) + '</td></tr>');
            });
        }
        
        zeichneMonatsChart(diagramme);
        zeichneKategorienChart(diagramme.kategorien);
        
        $('#loading').hide();
        $('#dashboard').show();
    } catch (error) {
        console.error('Fehler beim Laden:', error);
        App.error('Fehler beim Laden der Dashboard-Daten');
    }
}

async function speichereKontostand() {
    const data = {
        betrag: parseFloat($('#kontostandBetrag').val()),
        datum: $('#kontostandDatum').val(),
        bemerkung: $('#kontostandBemerkung').val() || null
    };
    
    try {
        await App.api.post('/api/kontostand.php', data);
        App.success('Kontostand gespeichert');
        $('#kontostandBetrag').val('');
        $('#kontostandBemerkung').val('');
        $('#kontostandDatum').val(new Date().toISOString().split('T')[0]);
        await ladeDashboard();
    } catch (error) {
        console.error('Fehler:', error);
        App.error('Fehler beim Speichern');
    }
}

async function loescheKontostand() {
    try {
        const kontostand = await App.api.get('/api/kontostand.php');
        if (kontostand.length === 0) { App.error('Kein Kontostand vorhanden'); return; }
        const id = kontostand[0].id;
        if (!await App.confirm('Kontostand wirklich loeschen?')) return;
        await App.api.delete('/api/kontostand.php?id=' + id);
        App.success('Kontostand geloescht');
        await ladeDashboard();
    } catch (error) {
        console.error('Fehler:', error);
        App.error('Fehler beim Loeschen');
    }
}

function zeichneMonatsChart(data) {
    const ctx = document.getElementById('monatsChart').getContext('2d');
    const monatsnamen = ['Jan', 'Feb', 'Mär', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez'];
    
    const einnahmen = data.monate.map(function(m) { return data.ist[m] ? data.ist[m].einnahmen : 0; });
    const ausgaben = data.monate.map(function(m) { return data.ist[m] ? data.ist[m].ausgaben : 0; });
    const prognoseEin = data.monate.map(function(m) { return data.prognose[m] ? data.prognose[m].einnahmen : 0; });
    const prognoseAus = data.monate.map(function(m) { return data.prognose[m] ? data.prognose[m].ausgaben : 0; });
    const kontostandLinie = data.monate.map(function(m) { return data.kontostandProMonat ? data.kontostandProMonat[m] : null; });
    
    if (monatsChart) monatsChart.destroy();
    monatsChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: monatsnamen,
            datasets: [
                { label: 'Einnahmen (Ist)', data: einnahmen, backgroundColor: 'rgba(28, 200, 138, 0.8)', borderColor: 'rgba(28, 200, 138, 1)', borderWidth: 1 },
                { label: 'Ausgaben (Ist)', data: ausgaben, backgroundColor: 'rgba(231, 74, 59, 0.8)', borderColor: 'rgba(231, 74, 59, 1)', borderWidth: 1 },
                { label: 'Einnahmen (Prognose)', data: prognoseEin, backgroundColor: 'rgba(28, 200, 138, 0.3)', borderColor: 'rgba(28, 200, 138, 0.5)', borderWidth: 1 },
                { label: 'Ausgaben (Prognose)', data: prognoseAus, backgroundColor: 'rgba(231, 74, 59, 0.3)', borderColor: 'rgba(231, 74, 59, 0.5)', borderWidth: 1 },
                { label: 'Kontostand', data: kontostandLinie, type: 'line', borderColor: '#0d6efd', backgroundColor: 'rgba(13, 110, 253, 0.1)', borderWidth: 3, pointRadius: 4, pointBackgroundColor: '#0d6efd', fill: true, tension: 0.3, yAxisID: 'y1' }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' }, tooltip: { callbacks: { label: function(c) { return c.dataset.label + ': ' + App.formatCurrency(c.parsed.y); } } } },
            scales: {
                y: { beginAtZero: true, position: 'left', ticks: { callback: function(v) { return App.formatCurrency(v); } } },
                y1: { position: 'right', grid: { drawOnChartArea: false }, ticks: { callback: function(v) { return App.formatCurrency(v); } } }
            }
        }
    });
}

function zeichneKategorienChart(kategorien) {
    const ctx = document.getElementById('kategorienChart').getContext('2d');
    const labels = kategorien.map(function(k) { return k.name; });
    const betraege = kategorien.map(function(k) { return k.betrag; });
    const farben = kategorien.map(function(k) { return k.farbe; });
    
    if (kategorienChart) kategorienChart.destroy();
    kategorienChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{ data: betraege, backgroundColor: farben, borderWidth: 2 }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { padding: 15 } }, tooltip: { callbacks: { label: function(c) { var total = c.dataset.data.reduce(function(a, b) { return a + b; }, 0); var pct = ((c.parsed / total) * 100).toFixed(1); return c.label + ': ' + App.formatCurrency(c.parsed) + ' (' + pct + '%)'; } } } }
        }
    });
}
