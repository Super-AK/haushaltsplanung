<?php
$pageTitle = 'Kategorien - Haushaltsplanung';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-tags me-2"></i>Kategorien verwalten</h4>
    <button class="btn btn-primary" onclick="oeffneModal()">
        <i class="bi bi-plus-circle me-1"></i>Neue Kategorie
    </button>
</div>

<!-- Filter -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Typ</label>
                <select class="form-select" id="filterTyp">
                    <option value="">Alle</option>
                    <option value="einnahme">Einnahmen</option>
                    <option value="ausgabe">Ausgaben</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Art</label>
                <select class="form-select" id="filterArt">
                    <option value="">Alle</option>
                    <option value="fix">Fixkosten</option>
                    <option value="variabel">Variabel</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select class="form-select" id="filterAktiv">
                    <option value="">Alle</option>
                    <option value="1">Aktiv</option>
                    <option value="0">Inaktiv</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-outline-secondary w-100" onclick="ladeKategorien()">
                    <i class="bi bi-search me-1"></i>Filtern
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Tabelle -->
<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Farbe</th>
                        <th>Name</th>
                        <th>Typ</th>
                        <th>Art</th>
                        <th>Status</th>
                        <th>Aktionen</th>
                    </tr>
                </thead>
                <tbody id="kategorienTabelle">
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="kategorieModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitel">Neue Kategorie</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="kategorieForm">
                <div class="modal-body">
                    <input type="hidden" id="kategorieId">
                    
                    <div class="mb-3">
                        <label class="form-label">Name *</label>
                        <input type="text" class="form-control" id="kategorieName" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Typ *</label>
                            <select class="form-select" id="kategorieTyp" required>
                                <option value="einnahme">Einnahme</option>
                                <option value="ausgabe">Ausgabe</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Art *</label>
                            <select class="form-select" id="kategorieArt" required>
                                <option value="fix">Fixkosten</option>
                                <option value="variabel">Variabel</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Farbe</label>
                        <input type="color" class="form-control form-control-color" id="kategorieFarbe" value="#4e73df">
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="kategorieAktiv" checked>
                        <label class="form-check-label">Aktiv</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                    <button type="submit" class="btn btn-primary">Speichern</button>
                </div>
            </form>
        </div>
    </div>
</div>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>
<!-- Page Script -->
<script src="/assets/js/kategorien.js"></script>
