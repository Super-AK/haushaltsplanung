/**
 * Haushaltsplanung - Allgemeine Funktionen
 */

const App = {
    /**
     * AJAX-Helper
     */
    api: {
        async get(url) {
            const response = await fetch(url);
            if (!response.ok) throw new Error('API-Fehler');
            return response.json();
        },

        async post(url, data) {
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            if (!response.ok) throw new Error('API-Fehler');
            return response.json();
        },

        async put(url, data) {
            const response = await fetch(url, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            if (!response.ok) throw new Error('API-Fehler');
            return response.json();
        },

        async delete(url) {
            const response = await fetch(url, { method: 'DELETE' });
            if (!response.ok) throw new Error('API-Fehler');
            return response.json();
        }
    },

    /**
     * Geld formatieren
     */
    formatCurrency(amount) {
        return new Intl.NumberFormat('de-DE', {
            style: 'currency',
            currency: 'EUR'
        }).format(amount);
    },

    /**
     * Datum formatieren
     */
    formatDate(dateStr) {
        if (!dateStr) return '-';
        const d = new Date(dateStr);
        return d.toLocaleDateString('de-DE');
    },

    /**
     * Intervall-Text
     */
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

    /**
     * Intervall-Badge
     */
    getIntervallBadge(intervall) {
        return `<span class="badge badge-${intervall}">${this.getIntervallText(intervall)}</span>`;
    },

    /**
     * Erfolgsmeldung
     */
    success(message) {
        this.showAlert(message, 'success');
    },

    /**
     * Fehlermeldung
     */
    error(message) {
        this.showAlert(message, 'danger');
    },

    /**
     * Alert anzeigen
     */
    showAlert(message, type = 'info') {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3" style="z-index: 9999;">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', alertHtml);
        setTimeout(() => {
            document.querySelector('.alert-dismissible')?.remove();
        }, 3000);
    },

    /**
     * Bestätigungsdialog
     */
    async confirm(message) {
        return new Promise(resolve => {
            const modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.innerHTML = `
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Bestätigung</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">${message}</div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                            <button type="button" class="btn btn-danger" id="confirmBtn">Löschen</button>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();

            modal.querySelector('#confirmBtn').onclick = () => {
                bsModal.hide();
                modal.remove();
                resolve(true);
            };

            modal.addEventListener('hidden.bs.modal', () => {
                modal.remove();
                resolve(false);
            });
        });
    }
};
