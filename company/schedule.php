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

// --- DATABASE UPGRADES ---
$conn->query("CREATE TABLE IF NOT EXISTS interviews (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    company_id INT(11) NOT NULL,
    application_id INT(11) NOT NULL,
    interview_date DATE NOT NULL,
    interview_time TIME NOT NULL,
    duration INT DEFAULT 30,
    timezone VARCHAR(50) DEFAULT 'IST',
    interviewer_name TEXT, 
    interview_round VARCHAR(50) NOT NULL,
    interview_type VARCHAR(100) DEFAULT 'General',
    meeting_link VARCHAR(255) NOT NULL,
    notes TEXT,
    status ENUM('Scheduled', 'Completed', 'Cancelled') DEFAULT 'Scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE
)");

$success_msg = "";
$error_msg = "";

if (isset($_POST['schedule_interview'])) {
    $app_id = (int)$_POST['application_id'];
    $int_date = $_POST['interview_date'];
    $int_time = $_POST['interview_time'];
    $duration = (int)$_POST['duration'];
    $timezone = $_POST['timezone'];
    $interviewer = $conn->real_escape_string($_POST['interviewer_name']);
    $int_type = $_POST['interview_type'];
    $meeting_link = $conn->real_escape_string($_POST['meeting_link']);
    $notes = $conn->real_escape_string($_POST['notes']);
    
    // Get the current status of the application
    $app_data = $conn->query("SELECT status FROM applications WHERE id = $app_id")->fetch_assoc();
    $round = $app_data['status'];

    $sql = "INSERT INTO interviews (company_id, application_id, interview_date, interview_time, duration, timezone, interviewer_name, interview_round, interview_type, meeting_link, notes) 
            VALUES ('$company_id', '$app_id', '$int_date', '$int_time', '$duration', '$timezone', '$interviewer', '$round', '$int_type', '$meeting_link', '$notes')";
    
    if ($conn->query($sql)) {
        $success_msg = "Interview confirmed and candidate notified!";
    } else {
        $error_msg = "Error scheduling interview.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mini LinkedIn Business | Advanced Scheduler</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="company_style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="schedule.css?v=<?php echo time(); ?>">
</head>
<body class="dashboard-body">

<nav class="navbar">
    <a href="company_dashboard.php" class="nav-logo">MINI LINKEDIN <span class="badge">BUSINESS</span></a>
    <div class="nav-links">
        <a href="company_dashboard.php">Dashboard</a>
        <a href="post_job.php">Post a Job</a>
        <a href="applications.php">Pipeline</a>
        <a href="schedule.php" class="active-nav">Schedule</a>
        <a href="company_message.php">Messages</a>
        <a href="company_profile.php">Profile</a>
    </div>
    <div class="nav-logout">
        <a href="company_dashboard.php?logout=true" class="btn-logout">LOGOUT</a>
    </div>
</nav>

<div class="main-content-wrapper">
    <div class="company-container">
        <div class="welcome-header">
            <h2>Hiring Calendar</h2>
            <p>Coordinate with your team and set up professional interviews.</p>
        </div>

        <?php if($success_msg) echo "<div class='alert alert-success'>$success_msg</div>"; ?>
        <?php if($error_msg) echo "<div class='alert alert-danger'>$error_msg</div>"; ?>

        <div class="schedule-layout">
            <div class="panel-card">
                <div class="panel-header"><h3>Schedule Interview</h3></div>
                <form method="POST" class="p-25">
                    <div class="form-group">
                        <label>Candidate</label>
                        <select name="application_id" required>
                            <?php
                            // FIX: Removed the status filter so you can see EVERY applicant for your testing
                            $cands_sql = "SELECT a.id, r.fullname, j.title 
                                          FROM applications a 
                                          JOIN jobs j ON a.job_id = j.id 
                                          JOIN registration_details r ON a.user_id = r.login_id 
                                          WHERE j.company_id = $company_id";
                            
                            $cands = $conn->query($cands_sql);
                            
                            if ($cands->num_rows > 0) {
                                echo "<option value='' disabled selected>Select a candidate...</option>";
                                while($c = $cands->fetch_assoc()) {
                                    echo "<option value='".$c['id']."'>".$c['fullname']." - Job: ".$c['title']."</option>";
                                }
                            } else {
                                echo "<option value='' disabled>No applicants found yet</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Internal Interviewers</label>
                            <input type="text" name="interviewer_name" placeholder="e.g. Hetvi (HR), Rahul (Lead)" required>
                        </div>
                        <div class="form-group">
                            <label>Type</label>
                            <select name="interview_type">
                                <option>Technical Interview</option>
                                <option>Behavioral Discussion</option>
                                <option>Final Executive Round</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row three-cols">
                        <div class="form-group"><label>Date</label><input type="date" name="interview_date" required></div>
                        <div class="form-group"><label>Time</label><input type="time" name="interview_time" required></div>
                        <div class="form-group">
                            <label>Duration</label>
                            <select name="duration">
                                <option value="30">30 Mins</option>
                                <option value="60">1 Hour</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Meeting Link (Zoom/Meet/Teams)</label>
                        <input type="url" name="meeting_link" placeholder="Paste link here" required>
                    </div>

                    <div class="form-group">
                        <label>Private Notes for Interviewers</label>
                        <textarea name="notes" rows="2" placeholder="Key points to discuss..."></textarea>
                    </div>

                    <button type="submit" name="schedule_interview" class="btn-schedule">Confirm Interview</button>
                </form>
            </div>

            <div class="panel-card">
                <div class="panel-header"><h3>Upcoming Interviews</h3></div>
                <div class="upcoming-list">
                    <?php
                    $ints_sql = "SELECT i.*, r.fullname, j.title 
                                 FROM interviews i 
                                 JOIN applications a ON i.application_id = a.id 
                                 JOIN jobs j ON a.job_id = j.id 
                                 JOIN registration_details r ON a.user_id = r.login_id 
                                 WHERE i.company_id = $company_id 
                                 ORDER BY i.interview_date ASC, i.interview_time ASC";
                    $ints = $conn->query($ints_sql);
                    
                    if ($ints->num_rows > 0) {
                        while($i = $ints->fetch_assoc()):
                        ?>
                        <div class="interview-card">
                            <div class="int-header-row">
                                <div class="int-date-box">
                                    <span class="int-month"><?php echo date('M', strtotime($i['interview_date'])); ?></span>
                                    <span class="int-day"><?php echo date('d', strtotime($i['interview_date'])); ?></span>
                                </div>
                                <div class="int-details">
                                    <h4><?php echo htmlspecialchars($i['fullname']); ?></h4>
                                    <p class="int-job"><?php echo htmlspecialchars($i['title']); ?></p>
                                    <div class="int-meta">
                                        <span><i class="far fa-clock"></i> <?php echo date('h:i A', strtotime($i['interview_time'])); ?></span>
                                        <span><i class="fas fa-users"></i> <?php echo htmlspecialchars($i['interviewer_name']); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="int-actions-row">
                                <a href="<?php echo $i['meeting_link']; ?>" target="_blank" class="btn-join">
                                    <i class="fas fa-video"></i> Join Now
                                </a>
                            </div>
                        </div>
                        <?php endwhile;
                    } else {
                        echo "<div class='empty-state' style='padding: 40px; text-align: center; color: #999;'>
                                <i class='fas fa-calendar-alt' style='font-size: 30px; margin-bottom: 10px;'></i>
                                <p>No interviews scheduled yet.</p>
                              </div>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="simple-bottom-footer">
    <div class="footer-content">
        <h3>MINI LINKEDIN <span class="badge" style="font-size: 10px;">BUSINESS</span></h3>
        <p>&copy; <?php echo date("Y"); ?> Mini LinkedIn Employer Portal.</p>
    </div>
</footer>

</body>
</html>