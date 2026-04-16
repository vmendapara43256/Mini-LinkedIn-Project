<?php
session_start();

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
// Upgrade the status column to handle real interview rounds!
$conn->query("ALTER TABLE applications MODIFY COLUMN status ENUM('Pending', 'Reviewed', 'Round 1', 'Round 2', 'Hired', 'Rejected') DEFAULT 'Pending'");

// --- HANDLE STATUS UPDATES ---
$update_msg = "";
if (isset($_POST['update_status'])) {
    $app_id = (int)$_POST['application_id'];
    $new_status = $conn->real_escape_string($_POST['status']);
    
    if ($conn->query("UPDATE applications SET status = '$new_status' WHERE id = $app_id")) {
        $update_msg = "Application status updated successfully!";
    }
}

// --- DETERMINE WHICH JOB TO SHOW ---
$active_job_id = isset($_GET['job_id']) ? (int)$_GET['job_id'] : 0;

// Fetch all jobs for this company for the sidebar
$jobs_sql = "SELECT id, title FROM jobs WHERE company_id = $company_id ORDER BY created_at DESC";
$all_jobs = $conn->query($jobs_sql);

// If no job is selected but the company has jobs, default to the most recent one
if ($active_job_id == 0 && $all_jobs->num_rows > 0) {
    $first_job = $all_jobs->fetch_assoc();
    $active_job_id = $first_job['id'];
    // Reset pointer so we can loop through them again in the sidebar
    $all_jobs->data_seek(0); 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mini LinkedIn Business | Pipeline</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="company_style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="applications.css?v=<?php echo time(); ?>">
</head>
<body class="dashboard-body">

    <nav class="navbar">
        <a href="company_dashboard.php" class="nav-logo">MINI LINKEDIN <span class="badge">BUSINESS</span></a>
        <div class="nav-links">
            <a href="company_dashboard.php">Dashboard</a>
            <a href="post_job.php">Post a Job</a>
            <a href="applications.php" class="active-nav">Pipeline</a>
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
                <h2>Hiring Pipeline</h2>
                <p>Review student applications and move them through your interview stages.</p>
            </div>

            <?php if ($update_msg != ""): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $update_msg; ?></div>
            <?php endif; ?>

            <div class="pipeline-layout">
                
                <aside class="pipeline-sidebar panel-card">
                    <div class="panel-header">
                        <h3>Your Job Listings</h3>
                    </div>
                    <div class="job-list-menu">
                        <?php if ($all_jobs->num_rows > 0): ?>
                            <?php while($job = $all_jobs->fetch_assoc()): ?>
                                <a href="applications.php?job_id=<?php echo $job['id']; ?>" class="job-menu-link <?php echo $job['id'] == $active_job_id ? 'active' : ''; ?>">
                                    <?php echo htmlspecialchars($job['title']); ?>
                                </a>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div style="padding: 20px; font-size: 13px; color: #666;">No jobs posted yet. <br><a href="post_job.php" style="color: #004182;">Post a job first!</a></div>
                        <?php endif; ?>
                    </div>
                </aside>

                <main class="pipeline-main">
                    <?php if ($active_job_id > 0): ?>
                        <?php
                        // Fetch the job title so we know what we are looking at
                        $job_title = $conn->query("SELECT title FROM jobs WHERE id = $active_job_id")->fetch_assoc()['title'];
                        
                        // Fetch all applications for this specific job
                        $apps_sql = "SELECT a.id as app_id, a.status, a.applied_at, a.resume_path, 
                                            r.fullname, r.university, l.email, r.login_id as student_id
                                     FROM applications a 
                                     JOIN login_credentials l ON a.user_id = l.id 
                                     JOIN registration_details r ON l.id = r.login_id 
                                     WHERE a.job_id = $active_job_id 
                                     ORDER BY a.applied_at DESC";
                        $applications = $conn->query($apps_sql);
                        ?>

                        <div class="panel-card" style="margin-bottom: 20px;">
                            <div class="panel-header">
                                <h3>Applicants for: <?php echo htmlspecialchars($job_title); ?></h3>
                                <span style="background: #eef3f8; color: #004182; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700;">
                                    <?php echo $applications->num_rows; ?> Candidates
                                </span>
                            </div>
                        </div>

                        <div class="applicants-grid">
                            <?php if ($applications->num_rows > 0): ?>
                                <?php while($app = $applications->fetch_assoc()): 
                                    // Determine badge color based on status
                                    $badge_class = "status-pending";
                                    if ($app['status'] == 'Reviewed') $badge_class = "status-reviewed";
                                    if ($app['status'] == 'Round 1' || $app['status'] == 'Round 2') $badge_class = "status-interview";
                                    if ($app['status'] == 'Hired') $badge_class = "status-hired";
                                    if ($app['status'] == 'Rejected') $badge_class = "status-rejected";
                                ?>
                                    <div class="applicant-card panel-card">
                                        <div class="applicant-header">
                                            <div class="applicant-info">
                                                <div class="avatar-circle"><?php echo strtoupper(substr($app['fullname'], 0, 1)); ?></div>
                                                <div>
                                                    <h4><?php echo htmlspecialchars($app['fullname']); ?></h4>
                                                    <p><?php echo htmlspecialchars($app['university']); ?></p>
                                                </div>
                                            </div>
                                            <span class="status-badge <?php echo $badge_class; ?>"><?php echo $app['status']; ?></span>
                                        </div>
                                        
                                        <div class="applicant-meta">
                                            <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($app['email']); ?></p>
                                            <p><i class="far fa-clock"></i> Applied <?php echo date("M j, Y", strtotime($app['applied_at'])); ?></p>
                                        </div>

                                        <div class="applicant-actions">
                                            <a href="../user/uploads/resumes/<?php echo htmlspecialchars($app['resume_path']); ?>" target="_blank" class="btn-resume">
                                                <i class="far fa-file-pdf"></i> View Resume
                                            </a>
                                            
                                            <form method="POST" action="applications.php?job_id=<?php echo $active_job_id; ?>" class="status-form">
                                                <input type="hidden" name="application_id" value="<?php echo $app['app_id']; ?>">
                                                <select name="status" onchange="this.form.submit()">
                                                    <option value="Pending" <?php if($app['status'] == 'Pending') echo 'selected'; ?>>Pending</option>
                                                    <option value="Reviewed" <?php if($app['status'] == 'Reviewed') echo 'selected'; ?>>Reviewed</option>
                                                    <option value="Round 1" <?php if($app['status'] == 'Round 1') echo 'selected'; ?>>Interview Round 1</option>
                                                    <option value="Round 2" <?php if($app['status'] == 'Round 2') echo 'selected'; ?>>Interview Round 2</option>
                                                    <option value="Hired" <?php if($app['status'] == 'Hired') echo 'selected'; ?>>Hired</option>
                                                    <option value="Rejected" <?php if($app['status'] == 'Rejected') echo 'selected'; ?>>Rejected</option>
                                                </select>
                                                <input type="hidden" name="update_status" value="1">
                                            </form>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="empty-state panel-card">
                                    <i class="fas fa-folder-open" style="font-size: 40px; color: #cbd5e0; margin-bottom: 15px;"></i>
                                    <h3>No applicants yet</h3>
                                    <p>When students apply to this position, their resumes will appear here.</p>
                                </div>
                            <?php endif; ?>
                        </div>

                    <?php else: ?>
                        <div class="empty-state panel-card" style="padding: 50px;">
                            <i class="fas fa-briefcase" style="font-size: 50px; color: #cbd5e0; margin-bottom: 15px;"></i>
                            <h3>Your Pipeline is Empty</h3>
                            <p>You need to post a job before you can review applicants.</p>
                            <a href="post_job.php" class="btn-publish" style="margin-top: 20px; text-decoration: none;">Post your first job</a>
                        </div>
                    <?php endif; ?>
                </main>

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