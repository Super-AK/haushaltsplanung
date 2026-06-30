var kategorienListe = [];
var ausgewaehlt = {};

$(document).ready(async function() {
    await ladeKategorienFuerFilter();
    ladeBuchungen();
    $('#buchungForm').on('submit', function(e) { e.preventDefault(); speichereBuchung(); });
});

async function ladeKategorienFuerFilter() {
    try {
        kategorienListe = await App.api.get('/api/kategorien.php?aktiv=1');
        var sf = $('#filterKategorie'), sfo = $('#buchungKategorie');
        kategorienListe.forEach(function(k) {
            var label = k.name + ' (' + (k.typ === 'einnahme' ? 'Einnahme' : 'Ausgabe') + ')';
            sf.append('<option value="' + k.id + '">' + label + '</option>');
            sfo.append('<option value="' + k.id + '">' + label + '</option>');
        });
    } catch (e) { console.error(e); }
}

async function ladeBuchungen() {
    try {
        var params = new URLSearchParams();
        var kid = $('#filterKategorie').val(), iv = $('#filterIntervall').val(), ak = $('#filterAktiv').val();
        if (kid) params.append('kategorie_id', kid);
        if (ak) params.append('aktiv', ak);
        var buchungen = await App.api.get('/api/buchungen.php?' + params.toString());
        if (iv) buchungen = buchungen.filter(function(b) { return b.intervall === iv; });
        var tbody = $('#buchungenTabelle');
        tbody.empty();
        ausgewaehlt = {};
        aktualisiereAuswahl();
        if (buchungen.length === 0) { tbody.append('<tr><td colspan="8" class="text-center text-muted">Keine Buchungen gefunden</td></tr>'); return; }
        buchungen.forEach(function(b) {
            var bc = b.betrag >= 0 ? 'text-success' : 'text-danger';
            var p = b.betrag >= 0 ? '+' : '';
            tbody.append('<tr class="' + (b.aktiv ? '' : 'inaktiv') + '">' +
                '<td><input type="checkbox" class="form-check-input buch-check" value="' + b.id + '" onchange="toggleAuswahl(' + b.id + ')"></td>' +
                '<td><span class="farbe-preview" style="background-color:' + b.farbe + '"></span> ' + b.kategorie_name + '</td>' +
                '<td>' + (b.beschreibung || '-') + '</td>' +
                '<td class="' + bc + ' fw-bold">' + p + App.formatCurrency(b.betrag) + '</td>' +
                '<td>' + App.getIntervallBadge(b.intervall) + '</td>' +
                '<td>' + App.formatDate(b.start_datum) + '</td>' +
                '<td>' + (b.aktiv ? '<span class="badge bg-success">Aktiv</span>' : '<span class="badge bg-secondary">Inaktiv</span>') + '</td>' +
                '<td><button class="btn btn-sm btn-outline-primary me-1" onclick="bearbeiteBuchung(' + b.id + ')"><i class="bi bi-pencil"></i></button>' +
                '<button class="btn btn-sm btn-outline-danger" onclick="loescheBuchung(' + b.id + ')"><i class="bi bi-trash"></i></button></td></tr>');
        });
    } catch (e) { console.error(e); App.error('Fehler beim Laden'); }
}

function toggleAlleAuswahl() {
    var checked = $('#alleAuswaehlen').is(':checked');
    $('.buch-check').each(function() { $(this).prop('checked', checked); toggleAuswahl(parseInt($(this).val()), checked); });
}

function toggleAuswahl(id, checked) {
    if (checked === undefined) checked = $('.buch-check[value="' + id + '"]').is(':checked');
    if (checked) ausgewaehlt[id] = true; else delete ausgewaehlt[id];
    aktualisiereAuswahl();
}

function aktualisiereAuswahl() {
    var cnt = Object.keys(ausgewaehlt).length;
    $('#btnMassLoeschen').toggle(cnt > 0);
    $('#anzahlAusgewaehlt').text(cnt);
}

async function loescheAusgewaehlte() {
    if (!await App.confirm(Object.keys(ausgewaehlt).length + ' Buchungen wirklich loeschen?')) return;
    try {
        await App.api.delete('/api/buchungen.php?ids=' + Object.keys(ausgewaehlt).join(','));
        App.success('Buchungen geloescht');
        ladeBuchungen();
    } catch (e) { App.error('Fehler'); }
}

function oeffneModal() {
    $('#buchungId').val(''); $('#buchungKategorie').val(''); $('#buchungBetrag').val('');
    $('#buchungBeschreibung').val(''); $('#buchungIntervall').val('monatlich');
    $('#buchungStart').val(new Date().toISOString().split('T')[0]); $('#buchungEnde').val('');
    $('#buchungAktiv').prop('checked', true); $('#modalTitel').text('Neue Buchung');
    new bootstrap.Modal(document.getElementById('buchungModal')).show();
}

async function bearbeiteBuchung(id) {
    try {
        var buchungen = await App.api.get('/api/buchungen.php');
        var b = buchungen.find(function(b) { return b.id === id; });
        if (!b) { App.error('Nicht gefunden'); return; }
        $('#buchungId').val(b.id); $('#buchungKategorie').val(b.kategorie_id);
        $('#buchungBetrag').val(b.betrag); $('#buchungBeschreibung').val(b.beschreibung);
        $('#buchungIntervall').val(b.intervall); $('#buchungStart').val(b.start_datum);
        $('#buchungEnde').val(b.end_datum); $('#buchungAktiv').prop('checked', b.aktiv == 1);
        $('#modalTitel').text('Buchung bearbeiten');
        new bootstrap.Modal(document.getElementById('buchungModal')).show();
    } catch (e) { App.error('Fehler'); }
}

async function speichereBuchung() {
    var id = $('#buchungId').val();
    var data = { kategorie_id: parseInt($('#buchungKategorie').val()), betrag: parseFloat($('#buchungBetrag').val()),
        beschreibung: $('#buchungBeschreibung').val() || null, intervall: $('#buchungIntervall').val(),
        start_datum: $('#buchungStart').val(), end_datum: $('#buchungEnde').val() || null,
        aktiv: $('#buchungAktiv').is(':checked') ? 1 : 0 };
    try {
        if (id) { data.id = parseInt(id); await App.api.put('/api/buchungen.php', data); App.success('Aktualisiert'); }
        else { await App.api.post('/api/buchungen.php', data); App.success('Erstellt'); }
        bootstrap.Modal.getInstance(document.getElementById('buchungModal')).hide();
        ladeBuchungen();
    } catch (e) { App.error('Fehler'); }
}

async function loescheBuchung(id) {
    if (!await App.confirm('Buchung wirklich loeschen?')) return;
    try { await App.api.delete('/api/buchungen.php?id=' + id); App.success('Geloescht'); ladeBuchungen(); }
    catch (e) { App.error('Fehler'); }
}
