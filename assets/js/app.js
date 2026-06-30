/**
 * Haushaltsplanung - Allgemeine Funktionen
 */

const App = {
    api: {
        async get(url) {
            const response = await fetch(BASE_URL + url);
            if (!response.ok) throw new Error('API-Fehler');
            return response.json();
        },

        async post(url, data) {
            const response = await fetch(BASE_URL + url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            if (!response.ok) throw new Error('API-Fehler');
            return response.json();
        },

        async put(url, data) {
            const response = await fetch(BASE_URL + url, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            if (!response.ok) throw new Error('API-Fehler');
            return response.json();
        },

        async delete(url) {
            const response = await fetch(BASE_URL + url, { method: 'DELETE' });
            if (!response.ok) throw new Error('API-Fehler');
            return response.json();
        }
    },

    formatCurrency(amount) {
        return new Intl.NumberFormat('de-DE', {
            style: 'currency',
            currency: 'EUR'
        }).format(amount);
    },

    formatDate(dateStr) {
        if (!dateStr) return '-';
        const d = new Date(dateStr);
        return d.toLocaleDateString('de-DE');
    },

    getIntervallText(intervall) {
        const texts = {
            'einmalig': 'Einmalig',
            'woechentlich': 'Wöchentlich',
            'monatlich': 'Monatlich',
            'vierteljaehrlich': 'Vierteljährlich',
            'jaehrlich': 'Jährlich'
        };
        return texts[intervall] || intervall;
    },

    getIntervallBadge(intervall) {
        return '<span class="badge badge-' + intervall + '">' + this.getIntervallText(intervall) + '</span>';
    },

    success(message) {
        this.showAlert(message, 'success');
    },

    error(message) {
        this.showAlert(message, 'danger');
    },

    showAlert(message, type) {
        type = type || 'info';
        var alertHtml = '<div class="alert alert-' + type + ' alert-dismissible fade show position-fixed top-0 end-0 m-3" style="z-index: 9999;">' +
            message +
            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        document.body.insertAdjacentHTML('beforeend', alertHtml);
        setTimeout(function() {
            var el = document.querySelector('.alert-dismissible');
            if (el) el.remove();
        }, 3000);
    },

    confirm: function(message) {
        return new Promise(function(resolve) {
            var modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.innerHTML = '<div class="modal-dialog"><div class="modal-content">' +
                '<div class="modal-header"><h5 class="modal-title">Bestätigung</h5>' +
                '<button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>' +
                '<div class="modal-body">' + message + '</div>' +
                '<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>' +
                '<button type="button" class="btn btn-danger" id="confirmBtn">Löschen</button></div>' +
                '</div></div>';
            document.body.appendChild(modal);
            var bsModal = new bootstrap.Modal(modal);
            bsModal.show();

            modal.querySelector('#confirmBtn').onclick = function() {
                bsModal.hide();
                modal.remove();
                resolve(true);
            };

            modal.addEventListener('hidden.bs.modal', function() {
                modal.remove();
                resolve(false);
            });
        });
    }
};
