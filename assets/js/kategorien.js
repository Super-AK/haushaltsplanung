var ausgewaehlt = {};

$(document).ready(function() {
    ladeKategorien();
    $('#kategorieForm').on('submit', function(e) { e.preventDefault(); speichereKategorie(); });
});

async function ladeKategorien() {
    try {
        var params = new URLSearchParams();
        var typ = $('#filterTyp').val(), art = $('#filterArt').val(), aktiv = $('#filterAktiv').val();
        if (typ) params.append('typ', typ);
        if (art) params.append('art', art);
        if (aktiv) params.append('aktiv', aktiv);
        var kategorien = await App.api.get('/api/kategorien.php?' + params.toString());
        var tbody = $('#kategorienTabelle');
        tbody.empty();
        ausgewaehlt = {};
        aktualisiereAuswahl();
        if (kategorien.length === 0) { tbody.append('<tr><td colspan="7" class="text-center text-muted">Keine Kategorien gefunden</td></tr>'); return; }
        kategorien.forEach(function(k) {
            var typBadge = k.typ === 'einnahme' ? '<span class="badge bg-success">Einnahme</span>' : '<span class="badge bg-danger">Ausgabe</span>';
            var artBadge = k.art === 'fix' ? '<span class="badge bg-warning text-dark">Fix</span>' : '<span class="badge bg-info">Variabel</span>';
            tbody.append('<tr class="' + (k.aktiv ? '' : 'inaktiv') + '">' +
                '<td><input type="checkbox" class="form-check-input kat-check" value="' + k.id + '" onchange="toggleAuswahl(' + k.id + ')"></td>' +
                '<td><span class="farbe-preview" style="background-color:' + k.farbe + '"></span></td>' +
                '<td>' + k.name + '</td><td>' + typBadge + '</td><td>' + artBadge + '</td>' +
                '<td>' + (k.aktiv ? '<span class="badge bg-success">Aktiv</span>' : '<span class="badge bg-secondary">Inaktiv</span>') + '</td>' +
                '<td><button class="btn btn-sm btn-outline-primary me-1" onclick="bearbeiteKategorie(' + k.id + ')"><i class="bi bi-pencil"></i></button>' +
                '<button class="btn btn-sm btn-outline-danger" onclick="loescheKategorie(' + k.id + ')"><i class="bi bi-trash"></i></button></td></tr>');
        });
    } catch (e) { console.error(e); App.error('Fehler beim Laden'); }
}

function toggleAlleAuswahl() {
    var checked = $('#alleAuswaehlen').is(':checked');
    $('.kat-check').each(function() { $(this).prop('checked', checked); toggleAuswahl(parseInt($(this).val()), checked); });
}

function toggleAuswahl(id, checked) {
    if (checked === undefined) checked = $('.kat-check[value="' + id + '"]').is(':checked');
    if (checked) ausgewaehlt[id] = true; else delete ausgewaehlt[id];
    aktualisiereAuswahl();
}

function aktualisiereAuswahl() {
    var cnt = Object.keys(ausgewaehlt).length;
    $('#btnMassLoeschen').toggle(cnt > 0);
    $('#anzahlAusgewaehlt').text(cnt);
}

async function loescheAusgewaehlte() {
    if (!await App.confirm(Object.keys(ausgewaehlt).length + ' Kategorien wirklich loeschen?')) return;
    try {
        await App.api.delete('/api/kategorien.php?ids=' + Object.keys(ausgewaehlt).join(','));
        App.success('Kategorien geloescht');
        ladeKategorien();
    } catch (e) { App.error('Fehler beim Loeschen'); }
}

function oeffneModal() {
    $('#kategorieId').val(''); $('#kategorieName').val(''); $('#kategorieTyp').val('einnahme');
    $('#kategorieArt').val('fix'); $('#kategorieFarbe').val('#4e73df'); $('#kategorieAktiv').prop('checked', true);
    $('#modalTitel').text('Neue Kategorie');
    new bootstrap.Modal(document.getElementById('kategorieModal')).show();
}

async function bearbeiteKategorie(id) {
    try {
        var kategorien = await App.api.get('/api/kategorien.php');
        var k = kategorien.find(function(k) { return k.id === id; });
        if (!k) { App.error('Nicht gefunden'); return; }
        $('#kategorieId').val(k.id); $('#kategorieName').val(k.name); $('#kategorieTyp').val(k.typ);
        $('#kategorieArt').val(k.art); $('#kategorieFarbe').val(k.farbe); $('#kategorieAktiv').prop('checked', k.aktiv == 1);
        $('#modalTitel').text('Kategorie bearbeiten');
        new bootstrap.Modal(document.getElementById('kategorieModal')).show();
    } catch (e) { App.error('Fehler'); }
}

async function speichereKategorie() {
    var id = $('#kategorieId').val();
    var data = { name: $('#kategorieName').val(), typ: $('#kategorieTyp').val(), art: $('#kategorieArt').val(), farbe: $('#kategorieFarbe').val(), aktiv: $('#kategorieAktiv').is(':checked') ? 1 : 0 };
    try {
        if (id) { data.id = parseInt(id); await App.api.put('/api/kategorien.php', data); App.success('Aktualisiert'); }
        else { await App.api.post('/api/kategorien.php', data); App.success('Erstellt'); }
        bootstrap.Modal.getInstance(document.getElementById('kategorieModal')).hide();
        ladeKategorien();
    } catch (e) { App.error('Fehler'); }
}

async function loescheKategorie(id) {
    if (!await App.confirm('Kategorie wirklich loeschen?')) return;
    try { await App.api.delete('/api/kategorien.php?id=' + id); App.success('Geloescht'); ladeKategorien(); }
    catch (e) { App.error('Fehler'); }
}
