<?php
session_start();

if (isset($_GET['logout']) && $_GET['logout'] == 'true') {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$host = "localhost";
$rootUser = "root";
$rootPass = ""; 
$dbName = "mini_linkedin";
$conn = new mysqli($host, $rootUser, $rootPass, $dbName);
$current_user = $_SESSION['user_id'];

// --- 1. AUTO-CREATE JOB TABLES ---
$conn->query("CREATE TABLE IF NOT EXISTS jobs (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(150) NOT NULL,
    title VARCHAR(150) NOT NULL,
    job_type ENUM('Internship', 'Full-time', 'Part-time') NOT NULL,
    location VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS applications (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    job_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    resume_path VARCHAR(255) NOT NULL,
    status ENUM('Pending', 'Reviewed', 'Rejected', 'Hired') DEFAULT 'Pending',
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES login_credentials(id) ON DELETE CASCADE
)");

// --- 2. INSERT DUMMY DATA FOR TESTING (If table is empty) ---
$checkJobs = $conn->query("SELECT id FROM jobs");
if ($checkJobs->num_rows == 0) {
    $conn->query("INSERT INTO jobs (company_name, title, job_type, location, description) VALUES 
        ('TechCorp Solutions', 'Junior Web Developer', 'Full-time', 'Ahmedabad, Gujarat', 'Looking for a fresh graduate with skills in PHP, HTML, CSS, and basic JavaScript to join our growing development team.'),
        ('Global IT Innovators', 'Android App Dev Intern', 'Internship', 'Remote', '3-month internship for computer science students. Must know Java or Kotlin and have experience with Android Studio.'),
        ('DataSync Systems', 'Database Administrator', 'Full-time', 'Rajkot, Gujarat', 'Manage and optimize MySQL databases for our client portals. Great opportunity for recent IT graduates.')");
}

// --- 3. HANDLE RESUME APPLICATION ---
$apply_msg = "";
if (isset($_POST['apply_job'])) {
    $job_id = (int)$_POST['job_id'];
    
    // Check if already applied
    $checkApp = $conn->query("SELECT id FROM applications WHERE job_id = $job_id AND user_id = $current_user");
    if ($checkApp->num_rows > 0) {
        $apply_msg = "You have already applied for this position!";
    } else {
        if (!empty($_FILES['resume_file']['name'])) {
            $target_dir = "uploads/resumes/"; 
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true); 
            
            // Secure file name
            $resume_name = time() . "_" . basename($_FILES["resume_file"]["name"]);
            $target_file = $target_dir . $resume_name;
            
            // Only allow PDF or DOCX
            $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            if($fileType == "pdf" || $fileType == "docx" || $fileType == "doc") {
                if (move_uploaded_file($_FILES["resume_file"]["tmp_name"], $target_file)) {
                    $conn->query("INSERT INTO applications (job_id, user_id, resume_path) VALUES ('$job_id', '$current_user', '$resume_name')");
                    $apply_msg = "Application submitted successfully!";
                }
            } else {
                $apply_msg = "Only PDF or DOC files are allowed!";
            }
        }
    }
}

// --- 4. FILTER LOGIC ---
$filter_type = isset($_GET['type']) ? $_GET['type'] : 'All';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mini LinkedIn | Jobs</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="job.css?v=<?php echo time(); ?>">
</head>
<body class="dashboard-body">

    <nav class="navbar">
        <a href="dashboard.php" class="nav-logo">MINI LINKEDIN</a>
        <div class="nav-links">
            <a href="dashboard.php">Home</a>
            <a href="connection_page.php">Connections</a>
            <a href="job.php" style="color: #004182; border-bottom: 2px solid #004182; padding-bottom: 23px;">Jobs</a>
            <a href="message.php">Messaging</a>
            <a href="notification.php">Notifications</a>
            <a href="profile.php">Profile</a>
        </div>
        <div class="nav-logout">
            <a href="job.php?logout=true" class="btn-logout">LOGOUT <i class="fas fa-sign-out-alt"></i></a>
        </div>
    </nav>

    <div class="main-content-wrapper">
        <div class="job-container">
            
            <aside class="job-sidebar">
                <div class="sidebar-card">
                    <div class="sidebar-header">
                        <h3>Job Filters</h3>
                    </div>
                    <div class="filter-list">
                        <a href="job.php?type=All" class="filter-link <?php echo $filter_type == 'All' ? 'active' : ''; ?>">All Jobs</a>
                        <a href="job.php?type=Internship" class="filter-link <?php echo $filter_type == 'Internship' ? 'active' : ''; ?>">Internships</a>
                        <a href="job.php?type=Full-time" class="filter-link <?php echo $filter_type == 'Full-time' ? 'active' : ''; ?>">Full-Time</a>
                    </div>
                </div>

                <div class="sidebar-card" style="margin-top: 20px;">
                    <div class="sidebar-header">
                        <h3>My Applications</h3>
                    </div>
                    <div class="stats-box">
                        <?php 
                        $my_apps = $conn->query("SELECT COUNT(*) as total FROM applications WHERE user_id = $current_user")->fetch_assoc()['total'];
                        ?>
                        <h1><?php echo $my_apps; ?></h1>
                        <p>Jobs Applied</p>
                    </div>
                </div>
            </aside>

            <main class="job-main">
                
                <?php if($apply_msg != ""): ?>
                    <div class="alert-box <?php echo strpos($apply_msg, 'successfully') !== false ? 'success' : 'error'; ?>">
                        <?php echo $apply_msg; ?>
                    </div>
                <?php endif; ?>

                <div class="job-header-card">
                    <h2>Recommended for you</h2>
                    <p>Based on your profile and student status</p>
                </div>

                <div class="job-list">
                    <?php
                    $sql = "SELECT * FROM jobs";
                    if ($filter_type != 'All') {
                        $sql .= " WHERE job_type = '$filter_type'";
                    }
                    $sql .= " ORDER BY created_at DESC";
                    
                    $jobs = $conn->query($sql);
                    
                    if ($jobs->num_rows > 0) {
                        while ($job = $jobs->fetch_assoc()):
                            $j_id = $job['id'];
                            // Check if user already applied
                            $has_applied = $conn->query("SELECT id FROM applications WHERE job_id = $j_id AND user_id = $current_user")->num_rows > 0;
                    ?>
                        <div class="job-card">
                            <div class="job-info">
                                <div class="company-logo"><i class="fas fa-building"></i></div>
                                <div class="job-details">
                                    <h3><?php echo htmlspecialchars($job['title']); ?></h3>
                                    <p class="company-name"><?php echo htmlspecialchars($job['company_name']); ?> &bull; <?php echo htmlspecialchars($job['location']); ?></p>
                                    
                                    <div class="job-tags">
                                        <span class="tag <?php echo $job['job_type'] == 'Internship' ? 'tag-intern' : 'tag-full'; ?>">
                                            <?php echo $job['job_type']; ?>
                                        </span>
                                        <span class="tag tag-time"><?php echo date("M j", strtotime($job['created_at'])); ?></span>
                                    </div>
                                    
                                    <p class="job-desc"><?php echo htmlspecialchars($job['description']); ?></p>
                                </div>
                            </div>
                            
                            <div class="job-action">
                                <?php if ($has_applied): ?>
                                    <button class="btn-applied" disabled><i class="fas fa-check-circle"></i> APPLIED</button>
                                <?php else: ?>
                                    <button class="btn-apply" onclick="toggleApplyForm(<?php echo $j_id; ?>)">Easy Apply</button>
                                <?php endif; ?>
                            </div>

                            <div class="apply-form-box" id="apply-form-<?php echo $j_id; ?>" style="display:none;">
                                <h4>Submit your Resume</h4>
                                <form method="POST" enctype="multipart/form-data" class="resume-form">
                                    <input type="hidden" name="job_id" value="<?php echo $j_id; ?>">
                                    <div class="file-upload-wrapper">
                                        <input type="file" name="resume_file" accept=".pdf,.doc,.docx" required>
                                        <p style="font-size: 12px; color: #666; margin-top: 5px;">Must be PDF or DOCX</p>
                                    </div>
                                    <div class="form-buttons">
                                        <button type="button" class="btn-cancel" onclick="toggleApplyForm(<?php echo $j_id; ?>)">Cancel</button>
                                        <button type="submit" name="apply_job" class="btn-submit">Submit Application</button>
                                    </div>
                                </form>
                            </div>

                        </div>
                    <?php 
                        endwhile;
                    } else {
                        // THIS IS THE LINE I FIXED! Single quotes on the outside, double quotes on the inside.
                        echo '<div class="job-card"><p style="padding: 20px; color: #666;">No jobs found for this category.</p></div>';
                    }
                    ?>
                </div>

            </main>
        </div>
    </div>

    <footer class="simple-bottom-footer">
        <div class="footer-content">
            <h3>MINI LINKEDIN</h3>
            <p>Your dedicated university networking hub. Built by students, for students. Connect, share your projects, and discover your next big career opportunity.</p>
            <div class="footer-socials">
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-linkedin-in"></i></a>
            </div>
        </div>
        <div class="footer-copyright">
            <p>&copy; <?php echo date("Y"); ?> Mini LinkedIn Student Portal. Designed with purpose.</p>
        </div>
    </footer>


    <script>
        function toggleApplyForm(jobId) {
            var formBox = document.getElementById('apply-form-' + jobId);
            if (formBox.style.display === "none") {
                formBox.style.display = "block";
            } else {
                formBox.style.display = "none";
            }
        }
    </script>

</body>
</html>