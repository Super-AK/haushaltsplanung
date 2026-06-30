var BASE_URL = window.BASE_URL || '';

const App = {
    api: {
        async get(url) {
            const r = await fetch(BASE_URL + url);
            if (!r.ok) throw new Error('API-Fehler');
            return r.json();
        },
        async post(url, data) {
            const r = await fetch(BASE_URL + url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            if (!r.ok) throw new Error('API-Fehler');
            return r.json();
        },
        async put(url, data) {
            const r = await fetch(BASE_URL + url, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            if (!r.ok) throw new Error('API-Fehler');
            return r.json();
        },
        async delete(url) {
            const r = await fetch(BASE_URL + url, { method: 'DELETE' });
            if (!r.ok) throw new Error('API-Fehler');
            return r.json();
        }
    },

    formatCurrency(amount) {
        return new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' }).format(amount);
    },

    formatDate(dateStr) {
        if (!dateStr) return '-';
        return new Date(dateStr).toLocaleDateString('de-DE');
    },

    getIntervallText(i) {
        return { 'einmalig': 'Einmalig', 'woechentlich': 'Wöchentlich', 'monatlich': 'Monatlich', 'vierteljaehrlich': 'Vierteljährlich', 'jaehrlich': 'Jährlich' }[i] || i;
    },

    getIntervallBadge(i) {
        return '<span class="badge badge-' + i + '">' + this.getIntervallText(i) + '</span>';
    },

    success(msg) { this.showAlert(msg, 'success'); },
    error(msg) { this.showAlert(msg, 'danger'); },

    showAlert(msg, type) {
        type = type || 'info';
        document.body.insertAdjacentHTML('beforeend',
            '<div class="alert alert-' + type + ' alert-dismissible fade show position-fixed top-0 end-0 m-3" style="z-index:9999;">' +
            msg + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
        setTimeout(function() { var el = document.querySelector('.alert-dismissible'); if (el) el.remove(); }, 3000);
    },

    confirm(msg) {
        return new Promise(function(resolve) {
            var m = document.createElement('div');
            m.className = 'modal fade';
            m.innerHTML = '<div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Bestätigung</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body">' + msg + '</div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button><button type="button" class="btn btn-danger" id="confirmBtn">Löschen</button></div></div></div>';
            document.body.appendChild(m);
            var bs = new bootstrap.Modal(m);
            bs.show();
            m.querySelector('#confirmBtn').onclick = function() { bs.hide(); m.remove(); resolve(true); };
            m.addEventListener('hidden.bs.modal', function() { m.remove(); resolve(false); });
        });
    }
};

// Haushalt-Funktionen
function wechsleHaushalt(id) {
    App.api.put('/api/haushalte.php', { haushalt_id: id }).then(function() {
        window.location.reload();
    });
}

function oeffneNeuenHaushalt() {
    document.getElementById('haushaltName').value = '';
    document.getElementById('haushaltDemo').checked = true;
    new bootstrap.Modal(document.getElementById('haushaltModal')).show();
}

function speichereNeuenHaushalt() {
    var name = document.getElementById('haushaltName').value.trim();
    if (!name) { App.error('Name ist erforderlich'); return; }
    App.api.post('/api/haushalte.php', {
        name: name,
        mit_demo_daten: document.getElementById('haushaltDemo').checked ? 1 : 0
    }).then(function() {
        App.success('Haushalt erstellt');
        window.location.reload();
    });
}

function oeffneHaushaltLoeschen(id, name) {
    document.getElementById('loeschName').textContent = name;
    document.getElementById('haushaltLoeschenBtn').onclick = function() {
        App.api.delete('/api/haushalte.php?id=' + id).then(function(r) {
            if (r.error) { App.error(r.error); return; }
            App.success('Haushalt gelöscht');
            window.location.reload();
        });
    };
    new bootstrap.Modal(document.getElementById('haushaltLoeschenModal')).show();
}
