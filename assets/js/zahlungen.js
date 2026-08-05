/**
 * Zahlungen - Verwaltung
 */

var ausgewaehlteZahlungen = {};
var aktuelleZahlungen = [];

$(document).ready(async function() {
    await ladeBuchungenFuerAuswahl();
    await ladeKategorienFuerFilter();
    ladeZahlungen();
    
    $('#zahlungForm').on('submit', async function(e) {
        e.preventDefault();
        await speichereZahlung();
    });
    
    // Betrag automatisch setzen basierend auf Buchung
    $('#zahlungBuchung').on('change', function() {
        const buchungId = $(this).val();
        if (buchungId) {
            // Betrag aus Buchung holen (vereinfacht)
            const option = $(this).find(':selected');
            const betrag = option.data('betrag');
            if (betrag) {
                $('#zahlungBetrag').val(betrag);
            }
        }
    });
});

async function ladeBuchungenFuerAuswahl() {
    try {
        const buchungen = await App.api.get('/api/buchungen.php?aktiv=1');
        const select = $('#zahlungBuchung');
        
        buchungen.forEach(b => {
            const betragText = App.formatCurrency(b.betrag);
            const prefix = b.betrag >= 0 ? '+' : '';
            select.append(`
                <option value="${b.id}" data-betrag="${b.betrag}">
                    ${b.kategorie_name} - ${b.beschreibung || 'Keine Beschreibung'} (${prefix}${betragText})
                </option>
            `);
        });
        
    } catch (error) {
        console.error('Fehler:', error);
    }
}

async function ladeKategorienFuerFilter() {
    try {
        const kategorien = await App.api.get('/api/kategorien.php');
        const sel = $('#filterZahlungKategorie');
        kategorien.forEach(k => {
            const label = k.name + ' (' + (k.typ === 'einnahme' ? 'Einnahme' : 'Ausgabe') + ')';
            sel.append(`<option value="${k.id}">${label}</option>`);
        });
    } catch (error) {
        console.error('Fehler:', error);
    }
}

async function ladeZahlungen() {
    try {
        const alle = await App.api.get('/api/zahlungen.php');
        aktualisiereTagesbilanz(alle);

        const params = new URLSearchParams();
        const kid = $('#filterZahlungKategorie').val();
        const typ = $('#filterZahlungTyp').val();
        const von = $('#filterZahlungVon').val();
        const bis = $('#filterZahlungBis').val();
        if (kid) params.append('kategorie_id', kid);
        if (typ) params.append('typ', typ);
        if (von) params.append('von', von);
        if (bis) params.append('bis', bis);

        const liste = params.toString() ? await App.api.get('/api/zahlungen.php?' + params.toString()) : alle;
        renderZahlungen(liste);

    } catch (error) {
        console.error('Fehler:', error);
        App.error('Fehler beim Laden der Zahlungen');
    }
}

function renderZahlungen(zahlungen) {
    aktuelleZahlungen = zahlungen;
    ausgewaehlteZahlungen = {};
    aktualisiereZahlungAuswahl();
    $('#alleZahlungenAuswaehlen').prop('checked', false);

    const tbody = $('#zahlungenTabelle');
    tbody.empty();

    if (zahlungen.length === 0) {
        tbody.append('<tr><td colspan="7" class="text-center text-muted">Keine Zahlungen gefunden</td></tr>');
        return;
    }

    zahlungen.forEach(z => {
        const betragClass = z.typ === 'einnahme' ? 'text-success' : 'text-danger';
        const prefix = z.typ === 'einnahme' ? '+' : '-';

        tbody.append(`
            <tr>
                <td><input type="checkbox" class="form-check-input zahlung-check" value="${z.id}" onchange="toggleZahlungAuswahl(${z.id})"></td>
                <td>${App.formatDate(z.zahlungsdatum)}</td>
                <td>${z.kategorie_name}</td>
                <td>${z.buchung_beschreibung || '-'}</td>
                <td class="${betragClass} fw-bold">${prefix}${App.formatCurrency(Math.abs(z.betrag))}</td>
                <td>${z.bemerkung || '-'}</td>
                <td>
                    <button class="btn btn-sm btn-outline-danger" onclick="loescheZahlung(${z.id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `);
    });
}

function aktualisiereTagesbilanz(zahlungen) {
    const heute = new Date().toISOString().split('T')[0];
    let heuteEinnahmen = 0;
    let heuteAusgaben = 0;

    zahlungen.forEach(z => {
        if (z.zahlungsdatum === heute) {
            if (z.typ === 'einnahme') {
                heuteEinnahmen += Math.abs(z.betrag);
            } else {
                heuteAusgaben += Math.abs(z.betrag);
            }
        }
    });

    // Tagesbilanz anzeigen
    $('#heuteEinnahmen').text(App.formatCurrency(heuteEinnahmen));
    $('#heuteAusgaben').text(App.formatCurrency(heuteAusgaben));

    const bilanz = heuteEinnahmen - heuteAusgaben;
    const bilanzEl = $('#heuteBilanz');
    bilanzEl.text(App.formatCurrency(bilanz));
    bilanzEl.removeClass('text-success text-danger');
    bilanzEl.addClass(bilanz >= 0 ? 'text-success' : 'text-danger');
}

function setzeFilterZurueck() {
    $('#filterZahlungKategorie').val('');
    $('#filterZahlungTyp').val('');
    $('#filterZahlungVon').val('');
    $('#filterZahlungBis').val('');
    ladeZahlungen();
}

function toggleZahlungAlleAuswahl() {
    const checked = $('#alleZahlungenAuswaehlen').is(':checked');
    $('.zahlung-check').each(function() {
        $(this).prop('checked', checked);
        toggleZahlungAuswahl(parseInt($(this).val()), checked);
    });
}

function toggleZahlungAuswahl(id, checked) {
    if (checked === undefined) checked = $('.zahlung-check[value="' + id + '"]').is(':checked');
    if (checked) ausgewaehlteZahlungen[id] = true; else delete ausgewaehlteZahlungen[id];
    aktualisiereZahlungAuswahl();
}

function aktualisiereZahlungAuswahl() {
    const cnt = Object.keys(ausgewaehlteZahlungen).length;
    $('#btnZahlungMassLoeschen').toggle(cnt > 0);
    $('#btnZahlungMassExport').toggle(cnt > 0);
    $('#anzahlZahlungAusgewaehlt').text(cnt);
    $('#anzahlZahlungExport').text(cnt);
}

async function loescheAusgewaehlteZahlungen() {
    if (!await App.confirm(Object.keys(ausgewaehlteZahlungen).length + ' Zahlungen wirklich loeschen?')) return;
    try {
        await App.api.delete('/api/zahlungen.php?ids=' + Object.keys(ausgewaehlteZahlungen).join(','));
        App.success('Zahlungen geloescht');
        ausgewaehlteZahlungen = {};
        ladeZahlungen();
    } catch (error) {
        console.error('Fehler:', error);
        App.error('Fehler beim Loeschen');
    }
}

function exportiereCSV(zahlungen) {
    if (!zahlungen || zahlungen.length === 0) { App.error('Keine Daten zum Exportieren'); return; }
    const zeilen = [['Datum', 'Kategorie', 'Typ', 'Buchung', 'Betrag', 'Bemerkung']];
    zahlungen.forEach(z => {
        zeilen.push([
            z.zahlungsdatum,
            z.kategorie_name || '',
            z.typ === 'einnahme' ? 'Einnahme' : 'Ausgabe',
            z.buchung_beschreibung || '',
            z.betrag,
            z.bemerkung || ''
        ]);
    });
    const csv = '\uFEFF' + zeilen.map(r => r.map(v => {
        let s = String(v === null || v === undefined ? '' : v);
        if (/[";\n\r]/.test(s)) { s = s.replace(/"/g, '""'); s = '"' + s + '"'; }
        return s;
    }).join(';')).join('\r\n');
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'zahlungen_' + new Date().toISOString().split('T')[0] + '.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    setTimeout(function() { URL.revokeObjectURL(url); }, 100);
}

function exportiereAusgewaehlteCSV() {
    const ausgewaehlt = aktuelleZahlungen.filter(z => ausgewaehlteZahlungen[z.id]);
    exportiereCSV(ausgewaehlt);
}

function oeffneModal() {
    $('#zahlungBuchung').val('');
    $('#zahlungBetrag').val('');
    $('#zahlungDatum').val(new Date().toISOString().split('T')[0]);
    $('#zahlungBemerkung').val('');
    
    const modal = new bootstrap.Modal(document.getElementById('zahlungModal'));
    modal.show();
}

async function speichereZahlung() {
    const data = {
        buchung_id: parseInt($('#zahlungBuchung').val()),
        betrag: parseFloat($('#zahlungBetrag').val()),
        zahlungsdatum: $('#zahlungDatum').val(),
        bemerkung: $('#zahlungBemerkung').val() || null
    };
    
    try {
        await App.api.post('/api/zahlungen.php', data);
        App.success('Zahlung erfasst');
        
        bootstrap.Modal.getInstance(document.getElementById('zahlungModal')).hide();
        ladeZahlungen();
        
    } catch (error) {
        console.error('Fehler:', error);
        App.error('Fehler beim Speichern');
    }
}

async function loescheZahlung(id) {
    if (!await App.confirm('Möchten Sie diese Zahlung wirklich löschen?')) {
        return;
    }
    
    try {
        await App.api.delete('/api/zahlungen.php?id=' + id);
        App.success('Zahlung gelöscht');
        ladeZahlungen();
    } catch (error) {
        console.error('Fehler:', error);
        App.error('Fehler beim Löschen');
    }
}
