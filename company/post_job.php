<?php
session_start();

// Security Check
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

$success_msg = "";
$error_msg = "";

// --- HANDLE POSTING A NEW JOB ---
if (isset($_POST['publish_job'])) {
    $title = $conn->real_escape_string(trim($_POST['title']));
    $job_type = $conn->real_escape_string($_POST['job_type']);
    $location = $conn->real_escape_string(trim($_POST['location']));
    $description = $conn->real_escape_string(trim($_POST['description']));

    if (empty($title) || empty($location) || empty($description)) {
        $error_msg = "Please fill out all fields completely.";
    } else {
        // Notice how we insert this directly into the same 'jobs' table the students look at!
        $sql = "INSERT INTO jobs (company_id, company_name, title, job_type, location, description) 
                VALUES ('$company_id', '$company_name', '$title', '$job_type', '$location', '$description')";
        
        if ($conn->query($sql)) {
            $success_msg = "Job published successfully! Students can now see and apply for this position.";
        } else {
            $error_msg = "Something went wrong. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mini LinkedIn Business | Post a Job</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="company_style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="post_job.css?v=<?php echo time(); ?>">
</head>
<body class="dashboard-body">

    <nav class="navbar">
        <a href="company_dashboard.php" class="nav-logo">MINI LINKEDIN <span class="badge">BUSINESS</span></a>
        <div class="nav-links">
            <a href="company_dashboard.php">Dashboard</a>
            <a href="post_job.php" class="active-nav">Post a Job</a>
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
                <h2>Post a New Job</h2>
                <p>Find the perfect student for your open position.</p>
            </div>

            <?php if ($success_msg): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success_msg; ?></div>
            <?php endif; ?>
            <?php if ($error_msg): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error_msg; ?></div>
            <?php endif; ?>

            <div class="job-form-container">
                
                <div class="panel-card" style="padding: 30px;">
                    <form method="POST" action="post_job.php">
                        
                        <div class="form-group">
                            <label>Job Title</label>
                            <input type="text" name="title" placeholder="e.g. Junior Web Developer" required>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="form-group">
                                <label>Job Type</label>
                                <select name="job_type" required>
                                    <option value="Internship">Internship</option>
                                    <option value="Full-time">Full-time</option>
                                    <option value="Part-time">Part-time</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Location</label>
                                <input type="text" name="location" placeholder="e.g. Remote, or Rajkot, Gujarat" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Job Description & Requirements</label>
                            <textarea name="description" rows="8" placeholder="Describe the responsibilities, required skills, and what the student will learn..." required></textarea>
                        </div>

                        <button type="submit" name="publish_job" class="btn-publish">
                            <i class="fas fa-paper-plane"></i> Publish Job to Student Board
                        </button>

                    </form>
                </div>

                <div class="side-panels">
                    <div class="panel-card" style="margin-bottom: 20px;">
                        <div class="panel-header">
                            <h3>Tips for a great post</h3>
                        </div>
                        <div class="panel-body" style="padding: 20px;">
                            <ul style="color: #444; font-size: 13px; line-height: 1.6; padding-left: 15px;">
                                <li style="margin-bottom: 10px;">Be clear about whether this is for freshers or experienced students.</li>
                                <li style="margin-bottom: 10px;">List exact technologies (e.g., PHP, Java, Figma).</li>
                                <li>Specify if it's a paid internship or full-time offer.</li>
                            </ul>
                        </div>
                    </div>

                    <div class="panel-card">
                        <div class="panel-header">
                            <h3>Your Recent Posts</h3>
                        </div>
                        <div class="panel-body">
                            <?php
                            $recent_jobs = $conn->query("SELECT title, created_at FROM jobs WHERE company_id = $company_id ORDER BY created_at DESC LIMIT 3");
                            if ($recent_jobs && $recent_jobs->num_rows > 0) {
                                while ($job = $recent_jobs->fetch_assoc()) {
                                    echo "<div style='padding: 15px 20px; border-bottom: 1px solid #f0f2f5;'>";
                                    echo "<h4 style='font-size: 14px; margin: 0 0 5px 0; color: #1a1a1a;'>" . htmlspecialchars($job['title']) . "</h4>";
                                    echo "<p style='font-size: 12px; margin: 0; color: #666;'>" . date("M j, Y", strtotime($job['created_at'])) . "</p>";
                                    echo "</div>";
                                }
                            } else {
                                echo "<div style='padding: 20px; text-align: center; color: #666; font-size: 13px;'>No jobs posted yet.</div>";
                            }
                            ?>
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