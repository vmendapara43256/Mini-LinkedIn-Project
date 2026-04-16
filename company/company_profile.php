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

// --- DATABASE UPGRADE: ADD PROFILE COLUMNS ---
$checkProfile = $conn->query("SHOW COLUMNS FROM company_credentials LIKE 'about_us'");
if ($checkProfile->num_rows == 0) {
    $conn->query("ALTER TABLE company_credentials ADD COLUMN about_us TEXT AFTER company_name");
    $conn->query("ALTER TABLE company_credentials ADD COLUMN website VARCHAR(255) AFTER about_us");
    $conn->query("ALTER TABLE company_credentials ADD COLUMN logo_path VARCHAR(255) AFTER website");
    $conn->query("ALTER TABLE company_credentials ADD COLUMN location VARCHAR(150) AFTER logo_path");
}

$success_msg = "";
$error_msg = "";

// --- HANDLE PROFILE UPDATE ---
if (isset($_POST['update_profile'])) {
    $c_name = $conn->real_escape_string(trim($_POST['company_name']));
    $about = $conn->real_escape_string(trim($_POST['about_us']));
    $website = $conn->real_escape_string(trim($_POST['website']));
    $location = $conn->real_escape_string(trim($_POST['location']));

    // Handle Logo Upload
    $logo_sql = "";
    if (!empty($_FILES['logo']['name'])) {
        $target_dir = "uploads/logos/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $file_ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
        $new_filename = "logo_" . $company_id . "_" . time() . "." . $file_ext;
        $target_file = $target_dir . $new_filename;

        if (move_uploaded_file($_FILES['logo']['tmp_name'], $target_file)) {
            $logo_sql = ", logo_path = '$new_filename'";
        }
    }

    $update = "UPDATE company_credentials SET 
               company_name = '$c_name', 
               about_us = '$about', 
               website = '$website', 
               location = '$location' 
               $logo_sql 
               WHERE id = $company_id";

    if ($conn->query($update)) {
        $_SESSION['company_name'] = $c_name; // Update session name
        $success_msg = "Profile updated successfully!";
    } else {
        $error_msg = "Error updating profile.";
    }
}

// Fetch current details
$company = $conn->query("SELECT * FROM company_credentials WHERE id = $company_id")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mini LinkedIn Business | Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="company_style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="profile.css?v=<?php echo time(); ?>">
</head>
<body class="dashboard-body">

<nav class="navbar">
    <a href="company_dashboard.php" class="nav-logo">MINI LINKEDIN <span class="badge">BUSINESS</span></a>
    <div class="nav-links">
        <a href="company_dashboard.php">Dashboard</a>
        <a href="post_job.php">Post a Job</a>
        <a href="applications.php">Pipeline</a>
        <a href="schedule.php">Schedule</a>
        <a href="company_message.php">Messages</a>
        <a href="company_profile.php" class="active-nav">Profile</a>
    </div>
    <div class="nav-logout"><a href="company_dashboard.php?logout=true" class="btn-logout">LOGOUT</a></div>
</nav>

<div class="main-content-wrapper">
    <div class="company-container">
        <div class="welcome-header">
            <h2>Company Profile</h2>
            <p>Manage your corporate identity and brand presence.</p>
        </div>

        <?php if($success_msg) echo "<div class='alert alert-success'>$success_msg</div>"; ?>

        <div class="profile-layout">
            <div class="panel-card">
                <div class="panel-header"><h3>Edit Profile Details</h3></div>
                <form method="POST" enctype="multipart/form-data" class="p-30">
                    
                    <div class="profile-top-section">
                        <div class="logo-upload-box">
                            <?php if(!empty($company['logo_path'])): ?>
                                <img src="uploads/logos/<?php echo $company['logo_path']; ?>" alt="Logo">
                            <?php else: ?>
                                <div class="logo-placeholder"><i class="fas fa-building"></i></div>
                            <?php endif; ?>
                            <label for="logo-input" class="btn-upload-logo">Change Logo</label>
                            <input type="file" id="logo-input" name="logo" style="display:none;" accept="image/*">
                        </div>
                        
                        <div class="basic-info-fields">
                            <div class="form-group">
                                <label>Company Name</label>
                                <input type="text" name="company_name" value="<?php echo htmlspecialchars($company['company_name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Location</label>
                                <input type="text" name="location" value="<?php echo htmlspecialchars($company['location']); ?>" placeholder="e.g. Rajkot, Gujarat">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Website URL</label>
                        <input type="url" name="website" value="<?php echo htmlspecialchars($company['website']); ?>" placeholder="https://www.yourcompany.com">
                    </div>

                    <div class="form-group">
                        <label>About Us</label>
                        <textarea name="about_us" rows="6" placeholder="Describe your company culture, mission, and what you look for in students..."><?php echo htmlspecialchars($company['about_us']); ?></textarea>
                    </div>

                    <button type="submit" name="update_profile" class="btn-publish">Save Profile Changes</button>
                </form>
            </div>

            <div class="panel-card preview-card">
                <div class="panel-header"><h3>Public Preview</h3></div>
                <div class="p-30 text-center">
                    <div class="preview-logo">
                        <?php if(!empty($company['logo_path'])): ?>
                            <img src="uploads/logos/<?php echo $company['logo_path']; ?>" alt="Logo">
                        <?php else: ?>
                            <div class="logo-placeholder"><i class="fas fa-building"></i></div>
                        <?php endif; ?>
                    </div>
                    <h2 class="mt-15"><?php echo htmlspecialchars($company['company_name']); ?></h2>
                    <p class="text-muted"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($company['location'] ?? 'Not specified'); ?></p>
                    <a href="<?php echo htmlspecialchars($company['website']); ?>" target="_blank" class="preview-link">Visit Website</a>
                    <div class="preview-divider"></div>
                    <p class="preview-about"><?php echo !empty($company['about_us']) ? nl2br(htmlspecialchars($company['about_us'])) : 'No description provided yet.'; ?></p>
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