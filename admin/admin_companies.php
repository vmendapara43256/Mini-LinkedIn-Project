<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header("Location: admin_login.php"); exit(); }

$host = "localhost"; $rootUser = "root"; $rootPass = ""; $dbName = "mini_linkedin";
$conn = new mysqli($host, $rootUser, $rootPass, $dbName);

// --- POWER ACTIONS LOGIC ---
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];

    if ($action == 'verify') {
        $conn->query("UPDATE company_credentials SET is_verified = 1 WHERE id = $id");
    } elseif ($action == 'unverify') {
        $conn->query("UPDATE company_credentials SET is_verified = 0 WHERE id = $id");
    } elseif ($action == 'delete') {
        $conn->query("DELETE FROM company_credentials WHERE id = $id");
    }
    header("Location: admin_companies.php");
    exit();
}

// Fetch companies with a safe count for jobs
$query = "SELECT c.*, 
          (SELECT COUNT(*) FROM jobs j WHERE j.company_id = c.id) as job_count 
          FROM company_credentials c ORDER BY c.id DESC";
$companies = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Tower | Company Verification</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="admin_style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="admin_companies.css?v=<?php echo time(); ?>">
</head>
<body class="admin-fixed-layout">

    <aside class="sidebar-fixed">
        <div class="sidebar-logo"><i class="fas fa-shield-alt"></i><span>ADMIN <strong>TOWER</strong></span></div>
        <nav class="sidebar-nav">
            <a href="admin_dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a>
            <a href="admin_users.php"><i class="fas fa-user-graduate"></i> Manage Students</a>
            <a href="admin_companies.php" class="active"><i class="fas fa-building"></i> Manage Companies</a>
            <a href="admin_jobs.php"><i class="fas fa-briefcase"></i> Job Moderation</a>
            <a href="admin_settings.php"><i class="fas fa-cog"></i> System Settings</a>
            <div class="nav-spacer"></div>
            <a href="admin_login.php?logout=true" class="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </aside>

    <div class="main-wrapper">
        <header class="content-header">
            <div class="header-breadcrumb">Pages / <strong>Company Verification</strong></div>
            <div class="header-status"><span class="pulse-online"></span> System Online <div class="admin-badge">A</div></div>
        </header>

        <main class="page-content">
            <div class="welcome-section">
                <h1>Business <span>Verification</span></h1>
                <p>Review and verify companies to build trust on the platform.</p>
            </div>

            <div class="data-panel">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Company</th>
                            <th>Contact Details</th>
                            <th>Activity</th>
                            <th>Status</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($companies && $companies->num_rows > 0): ?>
                            <?php while($row = $companies->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div class="comp-cell">
                                        <div class="c-logo"><?php echo strtoupper(substr($row['company_name'], 0, 1)); ?></div>
                                        <div class="c-info">
                                            <strong>
                                                <?php echo htmlspecialchars($row['company_name']); ?>
                                                <?php if(isset($row['is_verified']) && $row['is_verified']): ?>
                                                    <i class="fas fa-check-circle verified-tick"></i>
                                                <?php endif; ?>
                                            </strong>
                                            <span>ID: #COMP-<?php echo $row['id']; ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="u-link"><?php echo $row['company_website'] ?? $row['website'] ?? 'No Website'; ?></div>
                                    <div class="u-email"><?php echo $row['company_email'] ?? $row['email'] ?? 'No Email'; ?></div>
                                </td>
                                <td>
                                    <span class="job-count-badge"><?php echo $row['job_count']; ?> Jobs</span>
                                </td>
                                <td>
                                    <?php if(isset($row['is_verified']) && $row['is_verified']): ?>
                                        <span class="badge b-verified">Verified</span>
                                    <?php else: ?>
                                        <span class="badge b-pending">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: right;">
                                    <div class="action-flex">
                                        <?php if(!(isset($row['is_verified']) && $row['is_verified'])): ?>
                                            <a href="?action=verify&id=<?php echo $row['id']; ?>" class="btn-verify"><i class="fas fa-user-check"></i> Verify</a>
                                        <?php else: ?>
                                            <a href="?action=unverify&id=<?php echo $row['id']; ?>" class="btn-revoke">Revoke</a>
                                        <?php endif; ?>
                                        <a href="?action=delete&id=<?php echo $row['id']; ?>" class="btn-delete-icon" onclick="return confirm('Delete this company?')"><i class="fas fa-trash-alt"></i></a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align:center; padding: 40px; color: #999;">No companies found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

</body>
</html>