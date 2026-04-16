<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header("Location: admin_login.php"); exit(); }

$host = "localhost"; $rootUser = "root"; $rootPass = ""; $dbName = "mini_linkedin";
$conn = new mysqli($host, $rootUser, $rootPass, $dbName);

// --- DELETE LOGIC ---
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $job_id = (int)$_GET['id'];
    $conn->query("DELETE FROM jobs WHERE id = $job_id");
    header("Location: admin_jobs.php?msg=Job Removed Successfully");
    exit();
}

// Fetch all jobs with company names
$query = "SELECT j.*, c.company_name 
          FROM jobs j 
          JOIN company_credentials c ON j.company_id = c.id 
          ORDER BY j.created_at DESC";
$jobs = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Tower | Job Moderation</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="admin_style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="admin_jobs.css?v=<?php echo time(); ?>">
</head>
<body class="admin-fixed-layout">

    <aside class="sidebar-fixed">
        <div class="sidebar-logo"><i class="fas fa-shield-alt"></i><span>ADMIN <strong>TOWER</strong></span></div>
        <nav class="sidebar-nav">
            <a href="admin_dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a>
            <a href="admin_users.php"><i class="fas fa-user-graduate"></i> Manage Students</a>
            <a href="admin_companies.php"><i class="fas fa-building"></i> Manage Companies</a>
            <a href="admin_jobs.php" class="active"><i class="fas fa-briefcase"></i> Job Moderation</a>
            <a href="admin_settings.php"><i class="fas fa-cog"></i> System Settings</a>
            <div class="nav-spacer"></div>
            <a href="admin_login.php?logout=true" class="logout-link"><i class="fas fa-sign-out-alt"></i> Logout System</a>
        </nav>
    </aside>

    <div class="main-wrapper">
        <header class="content-header">
            <div class="header-breadcrumb">Pages / <strong>Job Moderation</strong></div>
            <div class="header-status"><span class="pulse-online"></span> System Online <div class="admin-badge">A</div></div>
        </header>

        <main class="page-content">
            <div class="welcome-section">
                <h1>Job <span>Moderation</span></h1>
                <p>Review every live post and remove content that violates platform policies.</p>
            </div>

            <?php if(isset($_GET['msg'])): ?>
                <div class="alert-success-top"><?php echo $_GET['msg']; ?></div>
            <?php endif; ?>

            <div class="data-panel">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Job Title & ID</th>
                            <th>Posted By</th>
                            <th>Date Posted</th>
                            <th>Type</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($jobs->num_rows > 0): ?>
                            <?php while($row = $jobs->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div class="job-info">
                                        <strong><?php echo $row['title']; ?></strong>
                                        <span>#JOB-<?php echo $row['id']; ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="company-tag">
                                        <i class="fas fa-building"></i> <?php echo $row['company_name']; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="date-text"><?php echo date('d M, Y', strtotime($row['created_at'])); ?></div>
                                </td>
                                <td>
                                    <span class="job-type-pill"><?php echo $row['job_type'] ?? 'Full Time'; ?></span>
                                </td>
                                <td style="text-align: right;">
                                    <a href="?action=delete&id=<?php echo $row['id']; ?>" class="remove-btn" onclick="return confirm('Are you sure you want to remove this job post?')">
                                        <i class="fas fa-trash-alt"></i> Remove Post
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align:center; padding: 50px;">No active jobs to moderate.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

</body>
</html>