<?php
session_start();

// Handle Logout
if (isset($_GET['logout']) && $_GET['logout'] == 'true') {
    session_unset();
    session_destroy();
    header("Location: company_login.php");
    exit();
}

// Security Check: Kick out anyone who isn't a logged-in company
if (!isset($_SESSION['company_id'])) {
    header("Location: company_login.php");
    exit();
}

$host = "localhost";
$rootUser = "root";
$rootPass = ""; 
$dbName = "mini_linkedin";
$conn = new mysqli($host, $rootUser, $rootPass, $dbName);

$company_id = $_SESSION['company_id'];
$company_name = $_SESSION['company_name'];

// --- SMART DATABASE UPGRADE ---
// We need to link the existing 'jobs' table to our specific companies!
$checkCol = $conn->query("SHOW COLUMNS FROM jobs LIKE 'company_id'");
if ($checkCol->num_rows == 0) {
    $conn->query("ALTER TABLE jobs ADD COLUMN company_id INT(11) AFTER id");
    // Assign any old test jobs to this first company just so they don't break
    $conn->query("UPDATE jobs SET company_id = $company_id WHERE company_id IS NULL");
}

// --- FETCH DASHBOARD STATS ---
$jobs_posted = $conn->query("SELECT COUNT(*) as total FROM jobs WHERE company_id = $company_id")->fetch_assoc()['total'];

// Count how many students applied to THIS company's jobs
$apps_query = $conn->query("SELECT COUNT(a.id) as total FROM applications a JOIN jobs j ON a.job_id = j.id WHERE j.company_id = $company_id");
$total_apps = $apps_query ? $apps_query->fetch_assoc()['total'] : 0;

// Placeholder for interviews (We will build this table in the schedule.php phase!)
$interviews_today = 0; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mini LinkedIn Business | Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="company_style.css?v=<?php echo time(); ?>">
</head>
<body class="dashboard-body">

    <nav class="navbar">
        <a href="company_dashboard.php" class="nav-logo">MINI LINKEDIN <span class="badge">BUSINESS</span></a>
        <div class="nav-links">
            <a href="company_dashboard.php" class="active-nav">Dashboard</a>
            <a href="post_job.php">Post a Job</a>
            <a href="applications.php">Pipeline</a>
            <a href="schedule.php">Schedule</a>
            <a href="company_message.php">Messages</a>
            <a href="company_profile.php">Profile</a>
        </div>
        <div class="nav-logout">
            <span style="font-size: 13px; font-weight: 600; color: #666; margin-right: 15px;">
                <i class="fas fa-building"></i> <?php echo htmlspecialchars($company_name); ?>
            </span>
            <a href="company_dashboard.php?logout=true" class="btn-logout">LOGOUT <i class="fas fa-sign-out-alt"></i></a>
        </div>
    </nav>

    <div class="main-content-wrapper">
        <div class="company-container">
            
            <div class="welcome-header">
                <h2>Welcome back, <?php echo htmlspecialchars($company_name); ?></h2>
                <p>Here is what's happening with your job listings today.</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon bg-blue"><i class="fas fa-briefcase"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $jobs_posted; ?></h3>
                        <p>Active Job Listings</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon bg-green"><i class="fas fa-users"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $total_apps; ?></h3>
                        <p>Total Applications</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon bg-purple"><i class="fas fa-calendar-check"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $interviews_today; ?></h3>
                        <p>Interviews Today</p>
                    </div>
                </div>
            </div>

            <div class="dashboard-content-grid">
                
                <div class="panel-card">
                    <div class="panel-header">
                        <h3>Your Recent Postings</h3>
                        <a href="post_job.php" class="btn-sm-outline">Post New Job</a>
                    </div>
                    <div class="panel-body">
                        <?php
                        $recent_jobs = $conn->query("SELECT * FROM jobs WHERE company_id = $company_id ORDER BY created_at DESC LIMIT 4");
                        if ($recent_jobs && $recent_jobs->num_rows > 0) {
                            while ($job = $recent_jobs->fetch_assoc()) {
                                ?>
                                <div class="list-item">
                                    <div class="item-details">
                                        <h4><?php echo htmlspecialchars($job['title']); ?></h4>
                                        <p><?php echo htmlspecialchars($job['job_type']); ?> &bull; <?php echo htmlspecialchars($job['location']); ?></p>
                                    </div>
                                    <div class="item-action">
                                        <a href="applications.php?job_id=<?php echo $job['id']; ?>" class="view-link">Manage Pipeline <i class="fas fa-arrow-right"></i></a>
                                    </div>
                                </div>
                                <?php
                            }
                        } else {
                            echo "<div class='empty-state'><p>You haven't posted any jobs yet. Let's find some talent!</p></div>";
                        }
                        ?>
                    </div>
                </div>

                <div class="panel-card">
                    <div class="panel-header">
                        <h3>To-Do List</h3>
                    </div>
                    <div class="panel-body">
                        <div class="todo-item">
                            <i class="fas fa-exclamation-circle" style="color: #e53e3e;"></i>
                            <p>You have <strong>new applications</strong> waiting for review. <a href="applications.php" style="color: #004182; text-decoration: none; font-weight: 600;">View now</a></p>
                        </div>
                        <div class="todo-item">
                            <i class="fas fa-building" style="color: #004182;"></i>
                            <p>Complete your <a href="company_profile.php" style="color: #004182; text-decoration: none; font-weight: 600;">Company Profile</a> to attract more students.</p>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <footer class="simple-bottom-footer">
        <div class="footer-content">
            <h3>MINI LINKEDIN <span class="badge" style="font-size: 10px;">BUSINESS</span></h3>
            <p>The premier hiring platform for university talent. Connect with students, manage applications, and schedule interviews all in one place.</p>
        </div>
        <div class="footer-copyright">
            <p>&copy; <?php echo date("Y"); ?> Mini LinkedIn Employer Portal. Designed with purpose.</p>
        </div>
    </footer>

</body>
</html>