<?php
require_once __DIR__ . '/../includes/db.php';
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/');
    exit;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Haushaltsplanung</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); min-height: 100vh; display: flex; align-items: center; }
        .login-card { max-width: 400px; margin: 0 auto; }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-card">
            <div class="card shadow-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-wallet2 text-primary" style="font-size: 3rem;"></i>
                        <h3 class="mt-2">Haushaltsplanung</h3>
                        <p class="text-muted">Bitte einloggen</p>
                    </div>
                    
                    <div id="fehler" class="alert alert-danger" style="display:none;"></div>
                    
                    <form id="loginForm">
                        <div class="mb-3">
                            <label class="form-label">Benutzername</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control" id="benutzername" required autofocus>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Passwort</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" id="passwort" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100" id="loginBtn">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Anmelden
                        </button>
                    </form>
                    
                    <div class="text-center mt-4">
                        <small class="text-muted">
                            Demo: <strong>demo</strong> / <strong>demo123</strong><br>
                            Admin: <strong>admin</strong> / <strong>admin123</strong>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    var BASE_URL = '<?= BASE_URL ?>';
    document.getElementById('loginForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        var btn = document.getElementById('loginBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Wird angemeldet...';
        
        try {
            var response = await fetch(BASE_URL + '/api/auth.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    benutzername: document.getElementById('benutzername').value,
                    passwort: document.getElementById('passwort').value
                })
            });
            var data = await response.json();
            
            if (response.ok) {
                window.location.href = BASE_URL + '/';
            } else {
                document.getElementById('fehler').textContent = data.error || 'Login fehlgeschlagen';
                document.getElementById('fehler').style.display = 'block';
            }
        } catch (err) {
            document.getElementById('fehler').textContent = 'Verbindungsfehler';
            document.getElementById('fehler').style.display = 'block';
        }
        
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-box-arrow-in-right me-1"></i>Anmelden';
    });
    </script>
</body>
</html>
