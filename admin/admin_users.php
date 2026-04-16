<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header("Location: admin_login.php"); exit(); }

$host = "localhost"; $rootUser = "root"; $rootPass = ""; $dbName = "mini_linkedin";
$conn = new mysqli($host, $rootUser, $rootPass, $dbName);

// --- ACTION LOGIC ---
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    if ($action == 'block') $conn->query("UPDATE login_credentials SET account_status = 'blocked' WHERE id = $id");
    elseif ($action == 'unblock') $conn->query("UPDATE login_credentials SET account_status = 'active' WHERE id = $id");
    elseif ($action == 'delete') $conn->query("DELETE FROM login_credentials WHERE id = $id");
    header("Location: admin_users.php"); exit();
}

// --- SEARCH LOGIC ---
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$query = "SELECT l.id, l.email, l.account_status, r.fullname, r.university 
          FROM login_credentials l 
          JOIN registration_details r ON l.id = r.login_id";
if ($search != '') $query .= " WHERE r.fullname LIKE '%$search%' OR l.email LIKE '%$search%'";
$query .= " ORDER BY l.id DESC";
$students = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Tower | User Governance</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="admin_style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="admin_users.css?v=<?php echo time(); ?>">
</head>
<body class="admin-fixed-layout">

    <aside class="sidebar-fixed">
        <div class="sidebar-logo"><i class="fas fa-shield-alt"></i><span>ADMIN <strong>TOWER</strong></span></div>
        <nav class="sidebar-nav">
            <a href="admin_dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a>
            <a href="admin_users.php" class="active"><i class="fas fa-user-graduate"></i> Manage Students</a>
            <a href="admin_companies.php"><i class="fas fa-building"></i> Manage Companies</a>
            <a href="admin_jobs.php"><i class="fas fa-briefcase"></i> Job Moderation</a>
            <a href="admin_settings.php"><i class="fas fa-cog"></i> System Settings</a>
            <div class="nav-spacer"></div>
            <a href="admin_login.php?logout=true" class="logout-link"><i class="fas fa-sign-out-alt"></i> Logout System</a>
        </nav>
    </aside>

    <div class="main-wrapper">
        <header class="content-header">
            <div class="header-breadcrumb">Pages / <strong>User Governance</strong></div>
            <div class="header-status"><span class="pulse-online"></span> System Online <div class="admin-badge">A</div></div>
        </header>

        <main class="page-content">
            <div class="welcome-section">
                <h1>Student <span>Management</span></h1>
                <p>Monitor student activity and control access permissions.</p>
            </div>

            <div class="search-panel">
                <form method="GET" class="admin-search-form">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit">Search</button>
                </form>
            </div>

            <div class="data-panel">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Student Details</th>
                            <th>University</th>
                            <th>Status</th>
                            <th style="text-align: right;">Power Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $students->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div class="user-cell">
                                    <div class="u-avatar"><?php echo strtoupper(substr($row['fullname'], 0, 1)); ?></div>
                                    <div class="u-info"><strong><?php echo $row['fullname']; ?></strong><span><?php echo $row['email']; ?></span></div>
                                </div>
                            </td>
                            <td><?php echo $row['university']; ?></td>
                            <td><span class="badge <?php echo $row['account_status'] == 'active' ? 'b-success' : 'b-danger'; ?>"><?php echo ucfirst($row['account_status']); ?></span></td>
                            <td style="text-align: right;">
                                <?php if($row['account_status'] == 'active'): ?>
                                    <a href="?action=block&id=<?php echo $row['id']; ?>" class="action-btn btn-warning" title="Block"><i class="fas fa-user-slash"></i></a>
                                <?php else: ?>
                                    <a href="?action=unblock&id=<?php echo $row['id']; ?>" class="action-btn btn-success" title="Unblock"><i class="fas fa-user-check"></i></a>
                                <?php endif; ?>
                                <a href="?action=delete&id=<?php echo $row['id']; ?>" class="action-btn btn-danger" onclick="return confirm('Delete permanently?')" title="Delete"><i class="fas fa-trash-alt"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

</body>
</html>