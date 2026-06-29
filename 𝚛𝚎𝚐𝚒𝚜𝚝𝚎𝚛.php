<?php
require_once 'functions.php';

if (isset($_SESSION['keyauth_validated']) && $_SESSION['keyauth_validated'] === true) {
    redirectToDashboard();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        flashMessage('danger', 'CSRF validation failed.');
        redirectToLogin();
    }
    
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $email = trim($_POST['email']);
    $license = trim($_POST['license'] ?? '');
    $hwid = getHwid();
    
    if (empty($username) || empty($password) || empty($email)) {
        flashMessage('danger', 'All fields are required.');
        redirectToLogin();
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flashMessage('danger', 'Invalid email format.');
        redirectToLogin();
    }
    
    $result = keyauth_register($username, $password, $license, $email, $hwid);
    
    if (isset($result['success']) && $result['success'] === true) {
        flashMessage('success', 'Registration successful! You can now login.');
        header('Location: index.php');
        exit;
    } else {
        $errorMsg = $result['message'] ?? 'Registration failed.';
        flashMessage('danger', $errorMsg);
        redirectToLogin();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register - RealAuthX</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <div class="row justify-content-center min-vh-100 align-items-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-body p-5">
                    <h2 class="text-center fw-bold text-primary">Create Account</h2>
                    <p class="text-center text-muted">Register to access the admin panel</p>
                    
                    <?php displayFlash(); ?>
                    
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" name="username" placeholder="Choose a username" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control" name="email" placeholder="your@email.com" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" name="password" placeholder="••••••••" required minlength="6">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">License Key (Required)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-key"></i></span>
                                <input type="text" class="form-control" name="license" placeholder="XXXX-XXXX-XXXX-XXXX" required>
                            </div>
                        </div>
                        <button type="submit" name="register" class="btn btn-primary w-100 py-2">Register</button>
                    </form>
                    <p class="text-center mt-3"><a href="index.php">Already have an account? Login</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>