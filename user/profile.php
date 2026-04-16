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

// --- 1. AUTO-CREATE PROFILE TABLE ---
$conn->query("CREATE TABLE IF NOT EXISTS user_profiles (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL UNIQUE,
    profile_image VARCHAR(255) DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    location VARCHAR(100) DEFAULT NULL,
    skills VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES login_credentials(id) ON DELETE CASCADE
)");

// Ensure a profile row exists for this user
$check_prof = $conn->query("SELECT id FROM user_profiles WHERE user_id = $current_user");
if ($check_prof->num_rows == 0) {
    $conn->query("INSERT INTO user_profiles (user_id) VALUES ('$current_user')");
}

// --- 2. HANDLE PROFILE UPDATE ---
if (isset($_POST['update_profile'])) {
    $bio = $conn->real_escape_string($_POST['bio']);
    $location = $conn->real_escape_string($_POST['location']);
    $skills = $conn->real_escape_string($_POST['skills']);
    
    // Handle Profile Picture Upload
    $image_query_part = "";
    if (!empty($_FILES['profile_image']['name'])) {
        $target_dir = "uploads/profiles/"; 
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $image_name = time() . "_" . basename($_FILES["profile_image"]["name"]);
        $target_file = $target_dir . $image_name;
        
        $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        if(in_array($fileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
                $image_query_part = ", profile_image = '$image_name'";
            }
        }
    }
    
    $conn->query("UPDATE user_profiles SET bio = '$bio', location = '$location', skills = '$skills' $image_query_part WHERE user_id = $current_user");
    header("Location: profile.php"); 
    exit();
}

// --- 3. FETCH USER DATA ---
$user_data = $conn->query("SELECT r.fullname, r.university, p.profile_image, p.bio, p.location, p.skills 
                           FROM registration_details r 
                           JOIN user_profiles p ON r.login_id = p.user_id 
                           WHERE r.login_id = $current_user")->fetch_assoc();

// Fallback for avatar
$avatar_letter = strtoupper(substr($user_data['fullname'], 0, 1));
$profile_pic_html = $user_data['profile_image'] 
    ? "<img src='uploads/profiles/" . $user_data['profile_image'] . "' class='real-avatar-img'>" 
    : "<div class='avatar-circle-large'>$avatar_letter</div>";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mini LinkedIn | Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="profile.css?v=<?php echo time(); ?>">
</head>
<body class="dashboard-body">

    <nav class="navbar">
        <a href="dashboard.php" class="nav-logo">MINI LINKEDIN</a>
        <div class="nav-links">
            <a href="dashboard.php">Home</a>
            <a href="connection_page.php">Connections</a>
            <a href="job.php">Jobs</a>
            <a href="message.php">Messaging</a>
            <a href="notification.php">Notifications</a>
            <a href="profile.php" style="color: #004182; border-bottom: 2px solid #004182; padding-bottom: 23px;">Profile</a>
        </div>
        <div class="nav-logout">
            <a href="profile.php?logout=true" class="btn-logout">LOGOUT <i class="fas fa-sign-out-alt"></i></a>
        </div>
    </nav>

    <div class="main-content-wrapper">
        <div class="profile-container">
            
            <div class="profile-left-col">
                
                <div class="profile-card" id="profile-view">
                    <div class="profile-cover">
                        <button class="btn-edit-profile" onclick="toggleEditMode()"><i class="fas fa-pen"></i> Edit</button>
                    </div>
                    <div class="profile-avatar-wrapper">
                        <?php echo $profile_pic_html; ?>
                    </div>
                    <div class="profile-info">
                        <h2><?php echo htmlspecialchars($user_data['fullname']); ?></h2>
                        <p class="profile-headline"><?php echo htmlspecialchars($user_data['university']); ?></p>
                        
                        <p class="profile-location">
                            <i class="fas fa-map-marker-alt"></i> 
                            <?php echo $user_data['location'] ? htmlspecialchars($user_data['location']) : 'Location not set'; ?>
                        </p>
                        
                        <div class="profile-about">
                            <h3>About</h3>
                            <p><?php echo $user_data['bio'] ? nl2br(htmlspecialchars($user_data['bio'])) : 'Write something about yourself...'; ?></p>
                        </div>
                        
                        <div class="profile-skills">
                            <h3>Top Skills</h3>
                            <div class="skills-wrapper">
                                <?php 
                                if($user_data['skills']) {
                                    $skills_arr = explode(',', $user_data['skills']);
                                    foreach($skills_arr as $skill) {
                                        echo "<span class='skill-tag'>" . htmlspecialchars(trim($skill)) . "</span>";
                                    }
                                } else {
                                    echo "<span style='color:#666; font-size:13px;'>No skills added yet.</span>";
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="profile-card" id="profile-edit" style="display:none;">
                    <div class="edit-header">
                        <h2>Edit Profile</h2>
                        <button class="btn-close-edit" onclick="toggleEditMode()"><i class="fas fa-times"></i></button>
                    </div>
                    
                    <form method="POST" enctype="multipart/form-data" class="edit-profile-form">
                        
                        <div class="form-group">
                            <label>Profile Picture</label>
                            <input type="file" name="profile_image" accept="image/*">
                            <small>Leave blank to keep current picture.</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Location</label>
                            <input type="text" name="location" value="<?php echo htmlspecialchars($user_data['location'] ?? ''); ?>" placeholder="e.g. Rajkot, Gujarat">
                        </div>
                        
                        <div class="form-group">
                            <label>About (Bio)</label>
                            <textarea name="bio" rows="4" placeholder="Tell your network about yourself..."><?php echo htmlspecialchars($user_data['bio'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Skills (Comma separated)</label>
                            <input type="text" name="skills" value="<?php echo htmlspecialchars($user_data['skills'] ?? ''); ?>" placeholder="e.g. HTML, CSS, Figma, Java">
                        </div>
                        
                        <button type="submit" name="update_profile" class="btn-save-profile">Save Changes</button>
                    </form>
                </div>

            </div>

            <div class="profile-right-col">
                <div class="activity-header">
                    <h3>My Activity</h3>
                    <p>Recent posts you've shared</p>
                </div>

                <div class="activity-feed">
                    <?php
                    // Fetch ONLY the posts created by this specific user
                    $my_posts = $conn->query("SELECT * FROM posts WHERE user_id = $current_user ORDER BY created_at DESC");
                    
                    if ($my_posts->num_rows > 0) {
                        while ($post = $my_posts->fetch_assoc()):
                    ?>
                        <div class="mini-post-card">
                            <span class="post-date"><?php echo date("M j, Y", strtotime($post['created_at'])); ?></span>
                            <?php if (!empty($post['content'])): ?>
                                <p class="post-text"><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                            <?php endif; ?>
                            
                            <?php if ($post['image_path']): ?>
                                <img src="uploads/<?php echo $post['image_path']; ?>" class="post-img-preview">
                            <?php endif; ?>
                            
                            <div class="post-stats">
                                <?php
                                $p_id = $post['id'];
                                $likes = $conn->query("SELECT COUNT(*) as t FROM post_interactions WHERE post_id = $p_id AND interaction_type='like'")->fetch_assoc()['t'];
                                $comments = $conn->query("SELECT COUNT(*) as t FROM post_interactions WHERE post_id = $p_id AND interaction_type='comment'")->fetch_assoc()['t'];
                                ?>
                                <span><i class="fas fa-thumbs-up"></i> <?php echo $likes; ?></span>
                                <span><i class="fas fa-comment"></i> <?php echo $comments; ?></span>
                            </div>
                        </div>
                    <?php 
                        endwhile;
                    } else {
                        // FIXED: Using double quotes outside and single quotes inside!
                        echo "<div class='empty-activity'><p>You haven't posted anything yet. Go to the Dashboard to share your first post!</p></div>";
                    }
                    ?>
                </div>
            </div>

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
        function toggleEditMode() {
            var viewMode = document.getElementById("profile-view");
            var editMode = document.getElementById("profile-edit");
            
            if (viewMode.style.display === "none") {
                viewMode.style.display = "block";
                editMode.style.display = "none";
            } else {
                viewMode.style.display = "none";
                editMode.style.display = "block";
            }
        }
    </script>

</body>
</html>