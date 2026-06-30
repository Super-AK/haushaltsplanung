/**
 * Buchungen - Verwaltung
 */

let kategorienListe = [];

$(document).ready(async function() {
    await ladeKategorienFuerFilter();
    ladeBuchungen();
    
    $('#buchungForm').on('submit', async function(e) {
        e.preventDefault();
        await speichereBuchung();
    });
    
    // Betrag automatisch negativ machen wenn Ausgabe
    $('#buchungKategorie').on('change', function() {
        const kategorie = kategorienListe.find(k => k.id == $(this).val());
        if (kategorie && kategorie.typ === 'ausgabe') {
            const betrag = parseFloat($('#buchungBetrag').val());
            if (betrag > 0) {
                $('#buchungBetrag').val(-betrag);
            }
        }
    });
});

async function ladeKategorienFuerFilter() {
    try {
        kategorienListe = await App.api.get('/api/kategorien.php?aktiv=1');
        
        const select = $('#filterKategorie');
        const selectForm = $('#buchungKategorie');
        
        kategorienListe.forEach(k => {
            const label = `${k.name} (${k.typ === 'einnahme' ? 'Einnahme' : 'Ausgabe'})`;
            select.append(`<option value="${k.id}">${label}</option>`);
            selectForm.append(`<option value="${k.id}">${label}</option>`);
        });
        
    } catch (error) {
        console.error('Fehler:', error);
    }
}

async function ladeBuchungen() {
    try {
        const params = new URLSearchParams();
        const kategorieId = $('#filterKategorie').val();
        const intervall = $('#filterIntervall').val();
        const aktiv = $('#filterAktiv').val();
        
        if (kategorieId) params.append('kategorie_id', kategorieId);
        if (aktiv) params.append('aktiv', aktiv);
        
        const buchungen = await App.api.get('/api/buchungen.php?' + params.toString());
        
        let gefiltert = buchungen;
        if (intervall) {
            gefiltert = buchungen.filter(b => b.intervall === intervall);
        }
        
        const tbody = $('#buchungenTabelle');
        tbody.empty();
        
        if (gefiltert.length === 0) {
            tbody.append('<tr><td colspan="7" class="text-center text-muted">Keine Buchungen gefunden</td></tr>');
            return;
        }
        
        gefiltert.forEach(b => {
            const betragClass = b.betrag >= 0 ? 'text-success' : 'text-danger';
            const prefix = b.betrag >= 0 ? '+' : '';
            
            tbody.append(`
                <tr class="${b.aktiv ? '' : 'inaktiv'}">
                    <td><span class="farbe-preview" style="background-color: ${b.farbe}"></span> ${b.kategorie_name}</td>
                    <td>${b.beschreibung || '-'}</td>
                    <td class="${betragClass} fw-bold">${prefix}${App.formatCurrency(b.betrag)}</td>
                    <td>${App.getIntervallBadge(b.intervall)}</td>
                    <td>${App.formatDate(b.start_datum)}</td>
                    <td>${b.aktiv ? '<span class="badge bg-success">Aktiv</span>' : '<span class="badge bg-secondary">Inaktiv</span>'}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="bearbeiteBuchung(${b.id})">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-${b.aktiv ? 'warning' : 'success'} me-1" onclick="toggleAktiv(${b.id}, ${b.aktiv ? 0 : 1})">
                            <i class="bi bi-${b.aktiv ? 'pause' : 'play'}"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="loescheBuchung(${b.id})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `);
        });
        
    } catch (error) {
        console.error('Fehler:', error);
        App.error('Fehler beim Laden der Buchungen');
    }
}

function oeffneModal(id = null) {
    $('#buchungId').val('');
    $('#buchungKategorie').val('');
    $('#buchungBetrag').val('');
    $('#buchungBeschreibung').val('');
    $('#buchungIntervall').val('monatlich');
    $('#buchungStart').val(new Date().toISOString().split('T')[0]);
    $('#buchungEnde').val('');
    $('#buchungAktiv').prop('checked', true);
    $('#modalTitel').text('Neue Buchung');
    
    const modal = new bootstrap.Modal(document.getElementById('buchungModal'));
    modal.show();
}

async function bearbeiteBuchung(id) {
    try {
        const buchungen = await App.api.get('/api/buchungen.php');
        const b = buchungen.find(b => b.id === id);
        
        if (!b) {
            App.error('Buchung nicht gefunden');
            return;
        }
        
        $('#buchungId').val(b.id);
        $('#buchungKategorie').val(b.kategorie_id);
        $('#buchungBetrag').val(b.betrag);
        $('#buchungBeschreibung').val(b.beschreibung);
        $('#buchungIntervall').val(b.intervall);
        $('#buchungStart').val(b.start_datum);
        $('#buchungEnde').val(b.end_datum);
        $('#buchungAktiv').prop('checked', b.aktiv == 1);
        $('#modalTitel').text('Buchung bearbeiten');
        
        const modal = new bootstrap.Modal(document.getElementById('buchungModal'));
        modal.show();
        
    } catch (error) {
        console.error('Fehler:', error);
        App.error('Fehler beim Laden der Buchung');
    }
}

async function speichereBuchung() {
    const id = $('#buchungId').val();
    const data = {
        kategorie_id: parseInt($('#buchungKategorie').val()),
        betrag: parseFloat($('#buchungBetrag').val()),
        beschreibung: $('#buchungBeschreibung').val() || null,
        intervall: $('#buchungIntervall').val(),
        start_datum: $('#buchungStart').val(),
        end_datum: $('#buchungEnde').val() || null,
        aktiv: $('#buchungAktiv').is(':checked') ? 1 : 0
    };
    
    try {
        if (id) {
            data.id = parseInt(id);
            await App.api.put('/api/buchungen.php', data);
            App.success('Buchung aktualisiert');
        } else {
            await App.api.post('/api/buchungen.php', data);
            App.success('Buchung erstellt');
        }
        
        bootstrap.Modal.getInstance(document.getElementById('buchungModal')).hide();
        ladeBuchungen();
        
    } catch (error) {
        console.error('Fehler:', error);
        App.error('Fehler beim Speichern');
    }
}

async function toggleAktiv(id, aktiv) {
    try {
        await App.api.put('/api/buchungen.php', { id, aktiv });
        App.success('Status aktualisiert');
        ladeBuchungen();
    } catch (error) {
        console.error('Fehler:', error);
        App.error('Fehler beim Aktualisieren');
    }
}

async function loescheBuchung(id) {
    if (!await App.confirm('Möchten Sie diese Buchung wirklich löschen?')) {
        return;
    }
    
    try {
        await App.api.delete('/api/buchungen.php?id=' + id);
        App.success('Buchung gelöscht');
        ladeBuchungen();
    } catch (error) {
        console.error('Fehler:', error);
        App.error('Fehler beim Löschen');
    }
}
