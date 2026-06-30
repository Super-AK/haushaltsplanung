/**
 * Dashboard - Diagramme und Daten laden (mit Kontostand)
 */

let monatsChart = null;
let kategorienChart = null;

$(document).ready(function() {
    ladeDashboard();
    $('#kontostandDatum').val(new Date().toISOString().split('T')[0]);
    
    // Kontostand Form
    $('#kontostandForm').on('submit', async function(e) {
        e.preventDefault();
        await speichereKontostand();
    });
});

async function ladeDashboard() {
    try {
        const dashboard = await App.api.get('/api/dashboard.php');
        const diagramme = await App.api.get('/api/diagramme.php');
        
        // Kontostand anzeigen
        if (diagramme.kontostand && diagramme.kontostand.betrag) {
            $('#aktKontostand').text(App.formatCurrency(diagramme.kontostand.betrag));
            $('#aktKontostandDatum').text(App.formatDate(diagramme.kontostand.datum));
            $('#kontostandInfo').show();
        }
        
        // Jahresend-Prognose anzeigen
        const dezSaldo = diagramme.kontostandProMonat['12'] || 0;
        const prognoseEl = $('#jahresEndPrognose');
        prognoseEl.text(App.formatCurrency(dezSaldo));
        prognoseEl.removeClass('text-success text-danger text-warning');
        if (dezSaldo > 0) {
            prognoseEl.addClass('text-success');
        } else if (dezSaldo < 0) {
            prognoseEl.addClass('text-danger');
        } else {
            prognoseEl.addClass('text-warning');
        }
        
        // Kennzahlen
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
        
        // Anstehende Kosten
        const anstehendBody = $('#tabelleAnstehend tbody');
        anstehendBody.empty();
        if (dashboard.anstehendeKosten.length === 0) {
            anstehendBody.append('<tr><td colspan="3" class="text-muted text-center">Keine anstehenden Kosten</td></tr>');
        } else {
            dashboard.anstehendeKosten.forEach(k => {
                anstehendBody.append(`
                    <tr>
                        <td>${App.formatDate(k.naechste_zahlung)}</td>
                        <td>${k.kategorie_name}</td>
                        <td class="text-danger">${App.formatCurrency(k.betrag)}</td>
                    </tr>
                `);
            });
        }
        
        // Letzte Transaktionen
        const transBody = $('#tabelleTransaktionen tbody');
        transBody.empty();
        if (dashboard.letzteTransaktionen.length === 0) {
            transBody.append('<tr><td colspan="3" class="text-muted text-center">Keine Transaktionen</td></tr>');
        } else {
            dashboard.letzteTransaktionen.forEach(t => {
                const betragClass = t.typ === 'einnahme' ? 'text-success' : 'text-danger';
                const prefix = t.typ === 'einnahme' ? '+' : '-';
                transBody.append(`
                    <tr>
                        <td>${App.formatDate(t.zahlungsdatum)}</td>
                        <td>${t.kategorie_name} ${t.buchung_beschreibung ? '<small class="text-muted">(' + t.buchung_beschreibung + ')</small>' : ''}</td>
                        <td class="${betragClass}">${prefix}${App.formatCurrency(t.betrag)}</td>
                    </tr>
                `);
            });
        }
        
        // Diagramme zeichnen
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
        ladeDashboard();
    $('#kontostandDatum').val(new Date().toISOString().split('T')[0]);
    } catch (error) {
        console.error('Fehler:', error);
        App.error('Fehler beim Speichern');
    }
}

function zeichneMonatsChart(data) {
    const ctx = document.getElementById('monatsChart').getContext('2d');
    const monatsnamen = ['Jan', 'Feb', 'Mär', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez'];
    
    const einnahmen = data.monate.map(m => data.ist[m]?.einnahmen || 0);
    const ausgaben = data.monate.map(m => data.ist[m]?.ausgaben || 0);
    const prognoseEinnahmen = data.monate.map(m => data.prognose[m]?.einnahmen || 0);
    const prognoseAusgaben = data.monate.map(m => data.prognose[m]?.ausgaben || 0);
    const kontostandLinie = data.monate.map(m => data.kontostandProMonat?.[m] || null);
    
    monatsChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: monatsnamen,
            datasets: [
                {
                    label: 'Einnahmen (Ist)',
                    data: einnahmen,
                    backgroundColor: 'rgba(28, 200, 138, 0.8)',
                    borderColor: 'rgba(28, 200, 138, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Ausgaben (Ist)',
                    data: ausgaben,
                    backgroundColor: 'rgba(231, 74, 59, 0.8)',
                    borderColor: 'rgba(231, 74, 59, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Einnahmen (Prognose)',
                    data: prognoseEinnahmen,
                    backgroundColor: 'rgba(28, 200, 138, 0.3)',
                    borderColor: 'rgba(28, 200, 138, 0.5)',
                    borderWidth: 1
                },
                {
                    label: 'Ausgaben (Prognose)',
                    data: prognoseAusgaben,
                    backgroundColor: 'rgba(231, 74, 59, 0.3)',
                    borderColor: 'rgba(231, 74, 59, 0.5)',
                    borderWidth: 1
                },
                {
                    label: 'Kontostand (Verlauf)',
                    data: kontostandLinie,
                    type: 'line',
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    borderWidth: 3,
                    pointRadius: 4,
                    pointBackgroundColor: '#0d6efd',
                    fill: true,
                    tension: 0.3,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + App.formatCurrency(context.parsed.y);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    position: 'left',
                    ticks: {
                        callback: function(value) { return App.formatCurrency(value); }
                    }
                },
                y1: {
                    position: 'right',
                    grid: { drawOnChartArea: false },
                    ticks: {
                        callback: function(value) { return App.formatCurrency(value); }
                    }
                }
            }
        }
    });
}

function zeichneKategorienChart(kategorien) {
    const ctx = document.getElementById('kategorienChart').getContext('2d');
    const labels = kategorien.map(k => k.name);
    const betraege = kategorien.map(k => k.betrag);
    const farben = kategorien.map(k => k.farbe);
    
    kategorienChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: betraege,
                backgroundColor: farben,
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { padding: 15 } },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const prozent = ((context.parsed / total) * 100).toFixed(1);
                            return context.label + ': ' + App.formatCurrency(context.parsed) + ' (' + prozent + '%)';
                        }
                    }
                }
            }
        }
    });
}
