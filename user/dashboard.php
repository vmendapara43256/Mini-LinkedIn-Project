<?php
session_start();

// --- HANDLE LOGOUT DIRECTLY ---
if (isset($_GET['logout']) && $_GET['logout'] == 'true') {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$host = "localhost";
$rootUser = "root";
$rootPass = ""; 
$dbName = "mini_linkedin";
$conn = new mysqli($host, $rootUser, $rootPass, $dbName);

// --- 1. THE NEW MASTER INTERACTIONS TABLE ---
$conn->query("CREATE TABLE IF NOT EXISTS post_interactions (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    post_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    interaction_type ENUM('like', 'comment', 'share') NOT NULL,
    content TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
)");

// --- 2. HANDLE NEW POST ---
if (isset($_POST['submit_post'])) {
    $content = $conn->real_escape_string($_POST['post_content']);
    $user_id = $_SESSION['user_id'];
    $image_name = null;

    if (!empty($_FILES['post_image']['name'])) {
        $target_dir = "uploads/"; 
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $image_name = time() . "_" . basename($_FILES["post_image"]["name"]);
        move_uploaded_file($_FILES["post_image"]["tmp_name"], $target_dir . $image_name);
    }
    
    if (!empty(trim($content)) || $image_name) {
        $conn->query("INSERT INTO posts (user_id, content, image_path) VALUES ('$user_id', '$content', '$image_name')");
    }
    header("Location: dashboard.php"); exit();
}

// --- 3. HANDLE DELETE POST ---
if (isset($_POST['delete_post'])) {
    $del_p_id = (int)$_POST['post_id'];
    $u_id = $_SESSION['user_id'];
    
    $imgCheck = $conn->query("SELECT image_path FROM posts WHERE id = $del_p_id AND user_id = $u_id");
    if ($imgCheck->num_rows > 0) {
        $imgRow = $imgCheck->fetch_assoc();
        if ($imgRow['image_path'] && file_exists("uploads/" . $imgRow['image_path'])) {
            unlink("uploads/" . $imgRow['image_path']); 
        }
        $conn->query("DELETE FROM posts WHERE id = $del_p_id AND user_id = $u_id");
    }
    header("Location: dashboard.php"); exit();
}

// --- 4. HANDLE LIKES ---
if (isset($_POST['like_post'])) {
    $p_id = (int)$_POST['post_id'];
    $u_id = $_SESSION['user_id'];
    
    $check = $conn->query("SELECT id FROM post_interactions WHERE post_id = $p_id AND user_id = $u_id AND interaction_type = 'like'");
    if ($check->num_rows > 0) {
        $conn->query("DELETE FROM post_interactions WHERE post_id = $p_id AND user_id = $u_id AND interaction_type = 'like'"); 
    } else {
        $conn->query("INSERT INTO post_interactions (post_id, user_id, interaction_type) VALUES ('$p_id', '$u_id', 'like')"); 
    }
    header("Location: dashboard.php"); exit();
}

// --- 5. HANDLE COMMENTS ---
if (isset($_POST['submit_comment'])) {
    $p_id = (int)$_POST['post_id'];
    $u_id = $_SESSION['user_id'];
    $c_text = $conn->real_escape_string($_POST['comment_text']);
    
    if (!empty(trim($c_text))) {
        $conn->query("INSERT INTO post_interactions (post_id, user_id, interaction_type, content) VALUES ('$p_id', '$u_id', 'comment', '$c_text')");
    }
    header("Location: dashboard.php"); exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mini LinkedIn | Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="dashboard.css?v=<?php echo time(); ?>">
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
            <a href="profile.php">Profile</a>
        </div>
        <div class="nav-logout">
            <a href="dashboard.php?logout=true" class="btn-logout">LOGOUT <i class="fas fa-sign-out-alt"></i></a>
        </div>
    </nav>

    <div class="main-content-wrapper">
        <div class="dashboard-container">
            
            <aside class="dashboard-sidebar">
                <div class="sidebar-card">
                    <div class="sidebar-cover"></div>
                    <div class="sidebar-profile">
                        <div class="sidebar-avatar"><?php echo strtoupper(substr($_SESSION['fullname'], 0, 1)); ?></div>
                        <h2><?php echo htmlspecialchars($_SESSION['fullname']); ?></h2>
                        <p><?php echo htmlspecialchars($_SESSION['university']); ?></p>
                    </div>
                    <div class="sidebar-stats">
                        <div class="stat"><span>Profile Views</span> <strong>1,402</strong></div>
                        <div class="stat"><span>Connections</span> <strong>85</strong></div>
                    </div>
                </div>
            </aside>

            <main class="dashboard-feed">
                <div class="create-post-card">
                    <form method="POST" action="dashboard.php" enctype="multipart/form-data">
                        <div class="create-post-input">
                            <div class="mini-avatar"><?php echo strtoupper(substr($_SESSION['fullname'], 0, 1)); ?></div>
                            <textarea name="post_content" placeholder="Share a photo, project, or thought..."></textarea>
                        </div>
                        <div class="create-post-actions">
                            <div style="display: flex; align-items: center;">
                                <label for="post_image" class="photo-upload-btn"><i class="fas fa-camera"></i> PHOTO</label>
                                <span id="file-chosen-text" style="font-size: 12px; color: #004182; margin-left: 10px; font-weight: 600;"></span>
                            </div>
                            <input type="file" id="post_image" name="post_image" accept="image/*" style="display:none;">
                            <button type="submit" name="submit_post" class="publish-btn">POST</button>
                        </div>
                    </form>
                </div>

                <?php
                $res = $conn->query("SELECT p.*, r.fullname, r.university FROM posts p JOIN registration_details r ON p.user_id = r.login_id ORDER BY p.created_at DESC");
                if($res && $res->num_rows > 0) {
                    while ($post = $res->fetch_assoc()):
                        $p_id = $post['id'];
                        $current_user = $_SESSION['user_id'];
                        
                        $likeCount = $conn->query("SELECT COUNT(*) as total FROM post_interactions WHERE post_id = $p_id AND interaction_type = 'like'")->fetch_assoc()['total'];
                        $hasLiked = $conn->query("SELECT id FROM post_interactions WHERE post_id = $p_id AND user_id = $current_user AND interaction_type = 'like'")->num_rows > 0;
                        $likeClass = $hasLiked ? 'liked-btn' : '';
                ?>
                <div class="feed-post-card">
                    
                    <div class="post-header-wrapper" style="display:flex; justify-content:space-between; align-items:flex-start;">
                        <div class="post-header">
                            <div class="mini-avatar"><?php echo strtoupper(substr($post['fullname'], 0, 1)); ?></div>
                            <div class="post-user-info">
                                <h3><?php echo htmlspecialchars($post['fullname']); ?></h3>
                                <span><?php echo htmlspecialchars($post['university']); ?> &bull; <?php echo date("M j", strtotime($post['created_at'])); ?></span>
                            </div>
                        </div>
                        
                        <?php if ($post['user_id'] == $current_user): ?>
                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this post?');">
                                <input type="hidden" name="post_id" value="<?php echo $p_id; ?>">
                                <button type="submit" name="delete_post" class="delete-btn" title="Delete Post"><i class="fas fa-trash-alt"></i></button>
                            </form>
                        <?php endif; ?>
                    </div>
                    
                    <div class="post-body">
                        <?php if (!empty($post['content'])): ?>
                            <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                        <?php endif; ?>
                        
                        <?php if ($post['image_path']): ?>
                            <img src="uploads/<?php echo $post['image_path']; ?>" class="post-attached-img">
                        <?php endif; ?>
                    </div>
                    
                    <div class="post-footer-actions">
                        <form method="POST" style="flex:1; display:flex;">
                            <input type="hidden" name="post_id" value="<?php echo $p_id; ?>">
                            <button type="submit" name="like_post" class="action-btn-styled <?php echo $likeClass; ?>" style="width:100%;">
                                <i class="fas fa-thumbs-up"></i> LIKE <?php echo $likeCount > 0 ? "($likeCount)" : ""; ?>
                            </button>
                        </form>
                        
                        <button class="action-btn-styled" style="flex:1;"><i class="far fa-comment"></i> COMMENT</button>
                        
                        <a href="message.php?share_post_id=<?php echo $p_id; ?>" class="action-btn-styled share-link-btn" style="flex:1;">
                            <i class="fas fa-share"></i> SHARE
                        </a>
                    </div>

                    <div class="comments-section">
                        <?php
                        $comments = $conn->query("SELECT c.*, r.fullname FROM post_interactions c JOIN registration_details r ON c.user_id = r.login_id WHERE c.post_id = $p_id AND c.interaction_type = 'comment' ORDER BY c.created_at ASC");
                        while ($comment = $comments->fetch_assoc()):
                        ?>
                            <div class="comment-bubble">
                                <strong><?php echo htmlspecialchars($comment['fullname']); ?></strong>
                                <p><?php echo htmlspecialchars($comment['content']); ?></p>
                            </div>
                        <?php endwhile; ?>
                        
                        <form method="POST" class="add-comment-form">
                            <input type="hidden" name="post_id" value="<?php echo $p_id; ?>">
                            <div class="mini-avatar" style="width: 35px; height: 35px; font-size: 14px;"><?php echo strtoupper(substr($_SESSION['fullname'], 0, 1)); ?></div>
                            <input type="text" name="comment_text" placeholder="Add a comment..." required>
                            <button type="submit" name="submit_comment"><i class="fas fa-paper-plane"></i></button>
                        </form>
                    </div>

                </div>
                <?php 
                    endwhile; 
                } else {
                    echo '<div class="feed-post-card"><p style="text-align:center; color:#666;">No posts yet. Be the first to share something!</p></div>';
                }
                ?>
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
                <a href="#"><i class="fab fa-github"></i></a>
            </div>
        </div>
        <div class="footer-copyright">
            <p>&copy; <?php echo date("Y"); ?> Mini LinkedIn Student Portal. Designed with purpose.</p>
        </div>
    </footer>

    <script>
        document.getElementById('post_image').addEventListener('change', function() {
            const fileNameSpan = document.getElementById('file-chosen-text');
            if (this.files && this.files.length > 0) {
                fileNameSpan.textContent = this.files[0].name;
            } else {
                fileNameSpan.textContent = "";
            }
        });
    </script>

</body>
</html>