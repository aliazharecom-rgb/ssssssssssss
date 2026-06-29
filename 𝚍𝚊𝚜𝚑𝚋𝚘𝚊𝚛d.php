<?php
require_once 'functions.php';

// Check if user is logged in
if (!isset($_SESSION['keyauth_validated']) || $_SESSION['keyauth_validated'] !== true) {
    flashMessage('danger', 'Please login first.');
    redirectToLogin();
}

$username = $_SESSION['keyauth_username'] ?? 'User';
$userInfo = $_SESSION['keyauth_userinfo'] ?? [];

// Fetch live stats from KeyAuth API
$stats = keyauth_fetchStats();
$onlineUsers = keyauth_fetchOnline();

// Stats data
$totalUsers = $stats['appinfo']['numUsers'] ?? 'N/A';
$activeUsers = $stats['appinfo']['numOnlineUsers'] ?? 'N/A';
$totalKeys = $stats['appinfo']['numKeys'] ?? 'N/A';
$version = $stats['appinfo']['version'] ?? '1.0';

// Online users list
$onlineList = [];
if (isset($onlineUsers['success']) && $onlineUsers['success'] === true) {
    $onlineList = $onlineUsers['users'] ?? [];
}

// Dummy chart data (replace with real data if available)
$dates = ['2025-06-23', '2025-06-24', '2025-06-25', '2025-06-26', '2025-06-27', '2025-06-28', '2025-06-29'];
$registrations = [12, 18, 9, 25, 30, 22, 23];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RealAuthX - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="dashboard.php">
                <i class="fas fa-shield-alt me-2"></i>RealAuthX Admin
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link active" href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-users"></i> Users</a></li>
                    <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-key"></i> Licenses</a></li>
                    <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-history"></i> Audit Logs</a></li>
                    <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-cog"></i> Settings</a></li>
                </ul>
                <div class="d-flex align-items-center">
                    <span class="text-light me-3">
                        <i class="fas fa-user-circle"></i> <?= htmlspecialchars($username) ?>
                        <?php if (!empty($userInfo['subscriptions'])): ?>
                            <span class="badge bg-success ms-1"><i class="fas fa-check"></i> Licensed</span>
                        <?php endif; ?>
                    </span>
                    <a href="logout.php" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <?php displayFlash(); ?>
        
        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white shadow h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50">Total Users</h6>
                                <h3 class="display-6"><?= $totalUsers ?></h3>
                            </div>
                            <i class="fas fa-users fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white shadow h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50">Online Users</h6>
                                <h3 class="display-6"><?= $activeUsers ?></h3>
                            </div>
                            <i class="fas fa-user-check fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark shadow h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-dark-50">Total Keys</h6>
                                <h3 class="display-6"><?= $totalKeys ?></h3>
                            </div>
                            <i class="fas fa-key fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white shadow h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50">Version</h6>
                                <h3 class="display-6"><?= $version ?></h3>
                            </div>
                            <i class="fas fa-code fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart -->
        <div class="row g-3 mb-4">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Registrations (Last 7 Days)</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="registrationsChart" height="200"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-user-tag me-2"></i>User Roles</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="rolesChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Online Users & Recent Activity -->
        <div class="row g-3">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Online Users</h5>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <?php if (empty($onlineList)): ?>
                                <li class="list-group-item text-muted text-center">No users currently online</li>
                            <?php else: ?>
                                <?php foreach ($onlineList as $user): ?>
                                    <li class="list-group-item">
                                        <i class="fas fa-circle text-success me-2" style="font-size: 10px;"></i>
                                        <?= htmlspecialchars($user['credential'] ?? 'Unknown') ?>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Activity</h5>
                        <a href="#" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>User</th>
                                        <th>Action</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><i class="fas fa-user-circle text-primary"></i> <?= htmlspecialchars($username) ?></td>
                                        <td><span class="badge bg-success">Login</span></td>
                                        <td>Just now</td>
                                    </tr>
                                    <tr>
                                        <td><i class="fas fa-user-circle text-info"></i> user@example.com</td>
                                        <td><span class="badge bg-warning">Edit</span></td>
                                        <td>2 min ago</td>
                                    </tr>
                                    <tr>
                                        <td><i class="fas fa-user-circle text-danger"></i> moderator@test.com</td>
                                        <td><span class="badge bg-danger">Delete</span></td>
                                        <td>5 min ago</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Registration chart
        const ctx = document.getElementById('registrationsChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($dates) ?>,
                datasets: [{
                    label: 'New Users',
                    data: <?= json_encode($registrations) ?>,
                    backgroundColor: 'rgba(13, 110, 253, 0.6)',
                    borderColor: 'rgba(13, 110, 253, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });

        // Roles chart (dummy data — you can fetch from API if available)
        const rolesCtx = document.getElementById('rolesChart').getContext('2d');
        new Chart(rolesCtx, {
            type: 'doughnut',
            data: {
                labels: ['Admins', 'Moderators', 'Users'],
                datasets: [{
                    data: [12, 45, 1191],
                    backgroundColor: ['#0d6efd', '#ffc107', '#198754'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    </script>
</body>
</html>