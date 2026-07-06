<?php
$pageTitle = 'User-Verwaltung - Haushaltsplanung';
require_once __DIR__ . '/../includes/header.php';
requireLogin();
if (!isAdmin()) { echo '<div class="alert alert-danger">Kein Zugriff</div>'; require_once __DIR__ . '/../includes/footer.php'; exit; }
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-people me-2"></i>User-Verwaltung</h4>
    <button class="btn btn-primary" onclick="oeffneModal()"><i class="bi bi-plus-circle me-1"></i>Neuer User</button>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead><tr><th>Benutzername</th><th>Rolle</th><th>E-Mail</th><th>Status</th><th>Erstellt</th><th>Aktionen</th></tr></thead>
                <tbody id="usersTabelle"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- User Modal -->
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title" id="modalTitel">Neuer User</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="hidden" id="userId">
            <div class="mb-3"><label class="form-label">Benutzername *</label><input type="text" class="form-control" id="userBenutzername" required></div>
            <div class="mb-3"><label class="form-label" id="passwortLabel">Passwort *</label><input type="password" class="form-control" id="userPasswort"><small class="text-muted" id="passwortHinweis">Leer lassen um nicht zu aendern</small></div>
            <div class="mb-3"><label class="form-label">E-Mail</label><input type="email" class="form-control" id="userEmail"></div>
            <div class="mb-3"><label class="form-label">Rolle</label><select class="form-select" id="userRolle"><option value="benutzer">Benutzer</option><option value="admin">Admin</option></select></div>
            <div class="form-check"><input class="form-check-input" type="checkbox" id="userAktiv" checked><label class="form-check-label">Aktiv</label></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button><button type="button" class="btn btn-primary" onclick="speichereUser()">Speichern</button></div>
    </div></div>
</div>

<!-- Haushalt-Zuordnung Modal -->
<div class="modal fade" id="zuordnungModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Haushalt zuordnen</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <p>Haushalt fuer User <strong id="zuordnungUser"></strong> zuordnen:</p>
            <div class="mb-3"><label class="form-label">Haushalt</label><select class="form-select" id="zuordnungHaushalt"></select></div>
            <div class="mb-3"><label class="form-label">Recht</label><select class="form-select" id="zuordnungRecht"><option value="lesen">Lesen</option><option value="schreiben">Lesen + Schreiben</option><option value="besitzer">Besitzer</option></select></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button><button type="button" class="btn btn-primary" onclick="speichereZuordnung()">Speichern</button></div>
    </div></div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<script>
var zuordnungUserId = null;

$(document).ready(function() { ladeUsers(); });

async function ladeUsers() {
    try {
        var users = await App.api.get('/api/users.php');
        var tbody = $('#usersTabelle');
        tbody.empty();
        users.forEach(function(u) {
            tbody.append('<tr>' +
                '<td><i class="bi bi-person me-1"></i>' + u.benutzername + '</td>' +
                '<td><span class="badge bg-' + (u.rolle === 'admin' ? 'primary' : 'secondary') + '">' + u.rolle + '</span></td>' +
                '<td>' + (u.email || '-') + '</td>' +
                '<td>' + (u.aktiv ? '<span class="badge bg-success">Aktiv</span>' : '<span class="badge bg-danger">Inaktiv</span>') + '</td>' +
                '<td>' + App.formatDate(u.created_at) + '</td>' +
                '<td>' +
                    '<button class="btn btn-sm btn-outline-primary me-1" onclick="bearbeiteUser(' + u.id + ')" title="Bearbeiten"><i class="bi bi-pencil"></i></button>' +
                    '<button class="btn btn-sm btn-outline-info me-1" onclick="oeffneZuordnung(' + u.id + ', \'' + u.benutzername + '\')" title="Haushalte zuordnen"><i class="bi bi-house"></i></button>' +
                    (u.id != <?= $_SESSION['user_id'] ?? 0 ?> ? '<button class="btn btn-sm btn-outline-danger" onclick="loescheUser(' + u.id + ')" title="Loeschen"><i class="bi bi-trash"></i></button>' : '') +
                '</td></tr>');
        });
    } catch (e) { App.error('Fehler beim Laden'); }
}

function oeffneModal() {
    $('#userId').val(''); $('#userBenutzername').val('').prop('readonly', false);
    $('#userPasswort').val(''); $('#userEmail').val(''); $('#userRolle').val('benutzer');
    $('#userAktiv').prop('checked', true); $('#modalTitel').text('Neuer User');
    $('#passwortLabel').text('Passwort *'); $('#passwortHinweis').hide();
    new bootstrap.Modal(document.getElementById('userModal')).show();
}

async function bearbeiteUser(id) {
    try {
        var users = await App.api.get('/api/users.php');
        var u = users.find(function(u) { return u.id === id; });
        if (!u) return;
        $('#userId').val(u.id); $('#userBenutzername').val(u.benutzername).prop('readonly', true);
        $('#userPasswort').val(''); $('#userEmail').val(u.email || '');
        $('#userRolle').val(u.rolle); $('#userAktiv').prop('checked', u.aktiv == 1);
        $('#modalTitel').text('User bearbeiten');
        $('#passwortLabel').text('Neues Passwort'); $('#passwortHinweis').show();
        new bootstrap.Modal(document.getElementById('userModal')).show();
    } catch (e) { App.error('Fehler'); }
}

async function speichereUser() {
    var id = $('#userId').val();
    var data = {
        benutzername: $('#userBenutzername').val(),
        passwort: $('#userPasswort').val(),
        email: $('#userEmail').val() || null,
        rolle: $('#userRolle').val(),
        aktiv: $('#userAktiv').is(':checked') ? 1 : 0
    };
    try {
        if (id) {
            data.id = parseInt(id);
            if (!data.passwort) delete data.passwort;
            await App.api.put('/api/users.php', data);
            App.success('User aktualisiert');
        } else {
            if (!data.passwort) { App.error('Passwort erforderlich'); return; }
            await App.api.post('/api/users.php', data);
            App.success('User erstellt');
        }
        bootstrap.Modal.getInstance(document.getElementById('userModal')).hide();
        ladeUsers();
    } catch (e) { App.error('Fehler beim Speichern'); }
}

async function loescheUser(id) {
    if (!await App.confirm('User wirklich loeschen?')) return;
    try { await App.api.delete('/api/users.php?id=' + id); App.success('Geloescht'); ladeUsers(); }
    catch (e) { App.error('Fehler'); }
}

async function oeffneZuordnung(userId, name) {
    zuordnungUserId = userId;
    $('#zuordnungUser').text(name);
    try {
        var haushalte = await App.api.get('/api/haushalte.php');
        var sel = $('#zuordnungHaushalt');
        sel.empty();
        haushalte.forEach(function(h) { sel.append('<option value="' + h.id + '">' + h.name + '</option>'); });
        new bootstrap.Modal(document.getElementById('zuordnungModal')).show();
    } catch (e) { App.error('Fehler'); }
}

async function speichereZuordnung() {
    var data = {
        user_id: zuordnungUserId,
        haushalt_id: parseInt($('#zuordnungHaushalt').val()),
        recht: $('#zuordnungRecht').val()
    };
    try {
        await App.api.post('/api/user_haushalte.php', data);
        App.success('Zuordnung gespeichert');
        bootstrap.Modal.getInstance(document.getElementById('zuordnungModal')).hide();
    } catch (e) { App.error('Fehler'); }
}
</script>
