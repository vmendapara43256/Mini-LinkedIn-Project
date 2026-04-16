<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header("Location: admin_login.php"); exit(); }

$host = "localhost"; $rootUser = "root"; $rootPass = ""; $dbName = "mini_linkedin";
$conn = new mysqli($host, $rootUser, $rootPass, $dbName);

// --- AUTO-SQL FOR SETTINGS TABLE ---
$conn->query("CREATE TABLE IF NOT EXISTS system_settings (
    id INT PRIMARY KEY,
    maintenance_mode TINYINT(1) DEFAULT 0,
    broadcast_message TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

// Ensure one row exists
$check = $conn->query("SELECT * FROM system_settings WHERE id = 1");
if($check->num_rows == 0) { $conn->query("INSERT INTO system_settings (id, maintenance_mode, broadcast_message) VALUES (1, 0, 'Welcome to Mini LinkedIn!')"); }

$msg = "";

// --- UPDATE SETTINGS ---
if (isset($_POST['update_settings'])) {
    $m_mode = isset($_POST['maintenance_mode']) ? 1 : 0;
    $b_msg = $conn->real_escape_string($_POST['broadcast_message']);
    
    $conn->query("UPDATE system_settings SET maintenance_mode = $m_mode, broadcast_message = '$b_msg' WHERE id = 1");
    $msg = "System rules updated successfully!";
}

// --- CHANGE PASSWORD ---
if (isset($_POST['change_password'])) {
    $new_pass = $conn->real_escape_string($_POST['new_password']);
    $conn->query("UPDATE admin_auth SET password = '$new_pass' WHERE username = '".$_SESSION['admin_user']."'");
    $msg = "Master password updated!";
}

$settings = $conn->query("SELECT * FROM system_settings WHERE id = 1")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Tower | System Settings</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="admin_style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="admin_settings.css?v=<?php echo time(); ?>">
</head>
<body class="admin-fixed-layout">

    <aside class="sidebar-fixed">
        <div class="sidebar-logo"><i class="fas fa-shield-alt"></i><span>ADMIN <strong>TOWER</strong></span></div>
        <nav class="sidebar-nav">
            <a href="admin_dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a>
            <a href="admin_users.php"><i class="fas fa-user-graduate"></i> Manage Students</a>
            <a href="admin_companies.php"><i class="fas fa-building"></i> Manage Companies</a>
            <a href="admin_jobs.php"><i class="fas fa-briefcase"></i> Job Moderation</a>
            <a href="admin_settings.php" class="active"><i class="fas fa-cog"></i> System Settings</a>
            <div class="nav-spacer"></div>
            <a href="admin_login.php?logout=true" class="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </aside>

    <div class="main-wrapper">
        <header class="content-header">
            <div class="header-breadcrumb">Pages / <strong>System Settings</strong></div>
            <div class="header-status"><span class="pulse-online"></span> System Online <div class="admin-badge">A</div></div>
        </header>

        <main class="page-content">
            <div class="welcome-section">
                <h1>Platform <span>Control</span></h1>
                <p>Configure global rules and maintenance protocols.</p>
            </div>

            <?php if($msg): ?>
                <div class="alert-success-top"><?php echo $msg; ?></div>
            <?php endif; ?>

            <div class="settings-grid">
                <div class="settings-card">
                    <h3><i class="fas fa-tools"></i> Maintenance Mode</h3>
                    <form method="POST">
                        <div class="setting-item">
                            <label class="switch-label">
                                <span>Enable Maintenance Mode</span>
                                <input type="checkbox" name="maintenance_mode" <?php echo $settings['maintenance_mode'] ? 'checked' : ''; ?>>
                            </label>
                            <p class="hint">When enabled, students and companies will see a "Maintenance" screen.</p>
                        </div>
                        <div class="setting-item">
                            <label>Global Broadcast Message</label>
                            <textarea name="broadcast_message" rows="3"><?php echo $settings['broadcast_message']; ?></textarea>
                        </div>
                        <button type="submit" name="update_settings" class="save-btn">Update Platform Rules</button>
                    </form>
                </div>

                <div class="settings-card">
                    <h3><i class="fas fa-user-lock"></i> Security Credentials</h3>
                    <form method="POST" autocomplete="off">
                        <div class="setting-item">
                            <label>Admin Username</label>
                            <input type="text" value="<?php echo $_SESSION['admin_user']; ?>" disabled style="background: #f8fafc;">
                        </div>
                        <div class="setting-item">
                            <label>New Master Password</label>
                            <input type="password" name="new_password" placeholder="Enter new password" required>
                        </div>
                        <button type="submit" name="change_password" class="save-btn sec-btn">Change Master Password</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>