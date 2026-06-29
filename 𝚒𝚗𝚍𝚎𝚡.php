<?php
require_once 'functions.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['keyauth_validated']) && $_SESSION['keyauth_validated'] === true) {
    redirectToDashboard();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        flashMessage('danger', 'CSRF token validation failed.');
        redirectToLogin();
    }
    
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);
    $hwid = getHwid();
    
    if (empty($username) || empty($password)) {
        flashMessage('danger', 'Please enter both username and password.');
        redirectToLogin();
    }
    
    $result = keyauth_login($username, $password, $hwid);
    
    if (isset($result['success']) && $result['success'] === true) {
        if ($remember) {
            setcookie('remember_username', $username, time() + 86400*30, '/');
        }
        // Log the login event
        keyauth_log("User logged in from IP: " . $_SERVER['REMOTE_ADDR']);
        flashMessage('success', 'Welcome back, ' . htmlspecialchars($username) . '!');
        redirectToDashboard();
    } else {
        $errorMsg = $result['message'] ?? 'Invalid credentials.';
        flashMessage('danger', $errorMsg);
        redirectToLogin();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RealAuthX - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center min-vh-100 align-items-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-lg border-0 rounded-4">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h2 class="fw-bold text-primary">RealAuthX</h2>
                            <p class="text-muted">Bienvenido de vuelta</p>
                            <p class="small text-secondary">Inicia sesión para continuar</p>
                        </div>
                        
                        <?php displayFlash(); ?>
                        
                        <!-- Saved Accounts -->
                        <div class="mb-4">
                            <p class="small text-muted mb-2">Cuentas guardadas</p>
                            <div class="d-flex gap-2 flex-wrap">
                                <button class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('username').value='admin'">
                                    <i class="fas fa-user-circle"></i> Admin
                                </button>
                                <button class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('username').value='user'">
                                    <i class="fas fa-user"></i> User
                                </button>
                            </div>
                            <p class="small text-muted mt-1">Selecciona una cuenta guardada para entrar mas rapido.</p>
                        </div>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                            
                            <div class="mb-3">
                                <label for="username" class="form-label">Usuario</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" id="username" name="username" placeholder="Nombre de usuario" 
                                           value="<?= htmlspecialchars($_COOKIE['remember_username'] ?? '') ?>" required autofocus>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="remember" name="remember" 
                                           <?= isset($_COOKIE['remember_username']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="remember">Recordar usuario</label>
                                </div>
                                <a href="forgot.php" class="text-decoration-none small">¿Olvidaste la contraseña?</a>
                            </div>
                            
                            <button type="submit" name="login" class="btn btn-primary w-100 py-2 fw-bold">
                                <i class="fas fa-sign-in-alt me-2"></i>Entrar
                            </button>
                        </form>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <p class="text-muted small">RealAuthX OAuth</p>
                            <div class="d-flex justify-content-center gap-3">
                                <a href="#" class="btn btn-outline-dark" onclick="alert('Discord OAuth via KeyAuth coming soon!')">
                                    <i class="fab fa-discord me-2"></i>Discord
                                </a>
                                <a href="#" class="btn btn-outline-danger" onclick="alert('Google OAuth via KeyAuth coming soon!')">
                                    <i class="fab fa-google me-2"></i>Google
                                </a>
                            </div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <p class="small text-muted">¿No tienes una cuenta? <a href="register.php" class="text-decoration-none fw-bold">Registrarse</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const icon = this.querySelector('i');
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    </script>
</body>
</html>