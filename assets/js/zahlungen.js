/**
 * Zahlungen - Verwaltung
 */

$(document).ready(async function() {
    await ladeBuchungenFuerAuswahl();
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

async function ladeZahlungen() {
    try {
        const zahlungen = await App.api.get('/api/zahlungen.php');
        
        const tbody = $('#zahlungenTabelle');
        tbody.empty();
        
        // Tagesbilanz berechnen
        const heute = new Date().toISOString().split('T')[0];
        let heuteEinnahmen = 0;
        let heuteAusgaben = 0;
        
        if (zahlungen.length === 0) {
            tbody.append('<tr><td colspan="6" class="text-center text-muted">Keine Zahlungen gefunden</td></tr>');
        } else {
            zahlungen.forEach(z => {
                const betragClass = z.typ === 'einnahme' ? 'text-success' : 'text-danger';
                const prefix = z.typ === 'einnahme' ? '+' : '-';
                
                tbody.append(`
                    <tr>
                        <td>${App.formatDate(z.zahlungsdatum)}</td>
                        <td>${z.kategorie_name}</td>
                        <td>${z.buchung_beschreibung || '-'}</td>
                        <td class="${betragClass} fw-bold">${prefix}${App.formatCurrency(z.betrag)}</td>
                        <td>${z.bemerkung || '-'}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-danger" onclick="loescheZahlung(${z.id})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `);
                
                if (z.zahlungsdatum === heute) {
                    if (z.typ === 'einnahme') {
                        heuteEinnahmen += z.betrag;
                    } else {
                        heuteAusgaben += z.betrag;
                    }
                }
            });
        }
        
        // Tagesbilanz anzeigen
        $('#heuteEinnahmen').text(App.formatCurrency(heuteEinnahmen));
        $('#heuteAusgaben').text(App.formatCurrency(heuteAusgaben));
        
        const bilanz = heuteEinnahmen - heuteAusgaben;
        const bilanzEl = $('#heuteBilanz');
        bilanzEl.text(App.formatCurrency(bilanz));
        bilanzEl.removeClass('text-success text-danger');
        bilanzEl.addClass(bilanz >= 0 ? 'text-success' : 'text-danger');
        
    } catch (error) {
        console.error('Fehler:', error);
        App.error('Fehler beim Laden der Zahlungen');
    }
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
