/**
 * Kategorien - Verwaltung
 */

$(document).ready(function() {
    ladeKategorien();
    
    // Formular-Submit
    $('#kategorieForm').on('submit', async function(e) {
        e.preventDefault();
        await speichereKategorie();
    });
});

async function ladeKategorien() {
    try {
        const params = new URLSearchParams();
        const typ = $('#filterTyp').val();
        const art = $('#filterArt').val();
        const aktiv = $('#filterAktiv').val();
        
        if (typ) params.append('typ', typ);
        if (art) params.append('art', art);
        if (aktiv) params.append('aktiv', aktiv);
        
        const kategorien = await App.api.get('/api/kategorien.php?' + params.toString());
        
        const tbody = $('#kategorienTabelle');
        tbody.empty();
        
        if (kategorien.length === 0) {
            tbody.append('<tr><td colspan="6" class="text-center text-muted">Keine Kategorien gefunden</td></tr>');
            return;
        }
        
        kategorien.forEach(k => {
            const typBadge = k.typ === 'einnahme' 
                ? '<span class="badge bg-success">Einnahme</span>' 
                : '<span class="badge bg-danger">Ausgabe</span>';
            
            const artBadge = k.art === 'fix' 
                ? '<span class="badge bg-warning text-dark">Fix</span>' 
                : '<span class="badge bg-info">Variabel</span>';
            
            const statusBadge = k.aktiv 
                ? '<span class="badge bg-success">Aktiv</span>' 
                : '<span class="badge bg-secondary">Inaktiv</span>';
            
            tbody.append(`
                <tr class="${k.aktiv ? '' : 'inaktiv'}">
                    <td><span class="farbe-preview" style="background-color: ${k.farbe}"></span></td>
                    <td>${k.name}</td>
                    <td>${typBadge}</td>
                    <td>${artBadge}</td>
                    <td>${statusBadge}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="bearbeiteKategorie(${k.id})">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-${k.aktiv ? 'warning' : 'success'} me-1" onclick="toggleAktiv(${k.id}, ${k.aktiv ? 0 : 1})">
                            <i class="bi bi-${k.aktiv ? 'pause' : 'play'}"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="loescheKategorie(${k.id})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `);
        });
        
    } catch (error) {
        console.error('Fehler:', error);
        App.error('Fehler beim Laden der Kategorien');
    }
}

function oeffneModal(id = null) {
    $('#kategorieId').val('');
    $('#kategorieName').val('');
    $('#kategorieTyp').val('einnahme');
    $('#kategorieArt').val('fix');
    $('#kategorieFarbe').val('#4e73df');
    $('#kategorieAktiv').prop('checked', true);
    $('#modalTitel').text('Neue Kategorie');
    
    const modal = new bootstrap.Modal(document.getElementById('kategorieModal'));
    modal.show();
}

async function bearbeiteKategorie(id) {
    try {
        const kategorien = await App.api.get('/api/kategorien.php');
        const k = kategorien.find(k => k.id === id);
        
        if (!k) {
            App.error('Kategorie nicht gefunden');
            return;
        }
        
        $('#kategorieId').val(k.id);
        $('#kategorieName').val(k.name);
        $('#kategorieTyp').val(k.typ);
        $('#kategorieArt').val(k.art);
        $('#kategorieFarbe').val(k.farbe);
        $('#kategorieAktiv').prop('checked', k.aktiv == 1);
        $('#modalTitel').text('Kategorie bearbeiten');
        
        const modal = new bootstrap.Modal(document.getElementById('kategorieModal'));
        modal.show();
        
    } catch (error) {
        console.error('Fehler:', error);
        App.error('Fehler beim Laden der Kategorie');
    }
}

async function speichereKategorie() {
    const id = $('#kategorieId').val();
    const data = {
        name: $('#kategorieName').val(),
        typ: $('#kategorieTyp').val(),
        art: $('#kategorieArt').val(),
        farbe: $('#kategorieFarbe').val(),
        aktiv: $('#kategorieAktiv').is(':checked') ? 1 : 0
    };
    
    try {
        if (id) {
            data.id = parseInt(id);
            await App.api.put('/api/kategorien.php', data);
            App.success('Kategorie aktualisiert');
        } else {
            await App.api.post('/api/kategorien.php', data);
            App.success('Kategorie erstellt');
        }
        
        bootstrap.Modal.getInstance(document.getElementById('kategorieModal')).hide();
        ladeKategorien();
        
    } catch (error) {
        console.error('Fehler:', error);
        App.error('Fehler beim Speichern');
    }
}

async function toggleAktiv(id, aktiv) {
    try {
        await App.api.put('/api/kategorien.php', { id, aktiv });
        App.success('Status aktualisiert');
        ladeKategorien();
    } catch (error) {
        console.error('Fehler:', error);
        App.error('Fehler beim Aktualisieren');
    }
}

async function loescheKategorie(id) {
    if (!await App.confirm('Möchten Sie diese Kategorie wirklich löschen?')) {
        return;
    }
    
    try {
        await App.api.delete('/api/kategorien.php?id=' + id);
        App.success('Kategorie gelöscht');
        ladeKategorien();
    } catch (error) {
        console.error('Fehler:', error);
        App.error('Fehler beim Löschen');
    }
}
