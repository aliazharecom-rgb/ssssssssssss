<?php
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    
    $init = keyauth_init();
    if (!$init['success']) {
        flashMessage('danger', 'Failed to initialize session.');
        header('Location: forgot.php');
        exit;
    }
    
    $result = callKeyAuthAPI('forgot', [
        'sessionid' => $_SESSION['keyauth_sessionid'],
        'username' => $username,
        'email' => $email
    ]);
    
    if (isset($result['success']) && $result['success'] === true) {
        flashMessage('success', 'Password reset link sent to your email.');
    } else {
        flashMessage('danger', $result['message'] ?? 'Failed to send reset link.');
    }
    header('Location: forgot.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password - RealAuthX</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <div class="row justify-content-center min-vh-100 align-items-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-body p-5">
                    <h3 class="text-center fw-bold text-primary">Reset Password</h3>
                    <p class="text-center text-muted">Enter your username and email to receive reset link</p>
                    
                    <?php displayFlash(); ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <input type="text" class="form-control" name="username" placeholder="Username" required>
                        </div>
                        <div class="mb-3">
                            <input type="email" class="form-control" name="email" placeholder="Your registered email" required>
                        </div>
                        <button type="submit" name="reset" class="btn btn-primary w-100">Send Reset Link</button>
                    </form>
                    <p class="text-center mt-3"><a href="index.php">Back to Login</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>