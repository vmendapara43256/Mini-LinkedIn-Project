<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header("Location: admin_login.php"); exit(); }

$host = "localhost"; $rootUser = "root"; $rootPass = ""; $dbName = "mini_linkedin";
$conn = new mysqli($host, $rootUser, $rootPass, $dbName);

// Analytics
$s_count = $conn->query("SELECT COUNT(*) as c FROM login_credentials")->fetch_assoc()['c'];
$c_count = $conn->query("SELECT COUNT(*) as c FROM company_credentials")->fetch_assoc()['c'];
$j_count = $conn->query("SELECT COUNT(*) as c FROM jobs")->fetch_assoc()['c'];
$a_count = $conn->query("SELECT COUNT(*) as c FROM applications")->fetch_assoc()['c'];

$recent_activity = $conn->query("SELECT j.title, c.company_name, j.created_at FROM jobs j JOIN company_credentials c ON j.company_id = c.id ORDER BY j.created_at DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Tower | Fixed Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="admin_style.css?v=<?php echo time(); ?>">
</head>
<body class="admin-fixed-layout">

    <aside class="sidebar-fixed">
        <div class="sidebar-logo">
            <i class="fas fa-shield-alt"></i>
            <span>ADMIN <strong>TOWER</strong></span>
        </div>
        
        <nav class="sidebar-nav">
            <a href="admin_dashboard.php" class="active"><i class="fas fa-th-large"></i> Dashboard</a>
            <a href="admin_users.php"><i class="fas fa-user-graduate"></i> Manage Students</a>
            <a href="admin_companies.php"><i class="fas fa-building"></i> Manage Companies</a>
            <a href="admin_jobs.php"><i class="fas fa-briefcase"></i> Job Moderation</a>
            <a href="admin_settings.php"><i class="fas fa-cog"></i> System Settings</a>
            <div class="nav-spacer"></div>
            <a href="admin_login.php?logout=true" class="logout-link"><i class="fas fa-sign-out-alt"></i> Logout System</a>
        </nav>
    </aside>

    <div class="main-wrapper">
        
        <header class="content-header">
            <div class="header-breadcrumb">Pages / <strong>Dashboard</strong></div>
            <div class="header-status">
                <span class="pulse-online"></span> System Online
                <div class="admin-badge">A</div>
            </div>
        </header>

        <main class="page-content">
            <div class="welcome-section">
                <h1>Master <span>Analytics</span></h1>
                <p>Welcome back, Hetvi. Here is what's happening on the platform today.</p>
            </div>

            <div class="stats-container">
                <div class="stat-box">
                    <div class="stat-icon b-blue"><i class="fas fa-users"></i></div>
                    <div class="stat-details">
                        <p>Students</p>
                        <h3><?php echo $s_count; ?></h3>
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon b-green"><i class="fas fa-building"></i></div>
                    <div class="stat-details">
                        <p>Companies</p>
                        <h3><?php echo $c_count; ?></h3>
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon b-orange"><i class="fas fa-briefcase"></i></div>
                    <div class="stat-details">
                        <p>Jobs</p>
                        <h3><?php echo $j_count; ?></h3>
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon b-purple"><i class="fas fa-file-invoice"></i></div>
                    <div class="stat-details">
                        <p>Applications</p>
                        <h3><?php echo $a_count; ?></h3>
                    </div>
                </div>
            </div>

            <div class="activity-panel">
                <div class="panel-header">
                    <h3><i class="fas fa-bolt"></i> Live Feed</h3>
                </div>
                <div class="feed-list">
                    <?php while($act = $recent_activity->fetch_assoc()): ?>
                    <div class="feed-item">
                        <div class="feed-dot"></div>
                        <div class="feed-text">
                            <strong><?php echo $act['company_name']; ?></strong> posted <em><?php echo $act['title']; ?></em>
                        </div>
                        <div class="feed-time"><?php echo date('h:i A', strtotime($act['created_at'])); ?></div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </main>
    </div>

</body>
</html>