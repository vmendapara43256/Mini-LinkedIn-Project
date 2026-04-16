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

// --- FETCH ALL NOTIFICATIONS ---
$notifications = [];

// 1. Fetch Likes and Comments on YOUR posts (excluding your own actions)
$int_sql = "SELECT pi.interaction_type, pi.created_at, r.fullname, p.content 
            FROM post_interactions pi 
            JOIN posts p ON pi.post_id = p.id 
            JOIN registration_details r ON pi.user_id = r.login_id 
            WHERE p.user_id = $current_user AND pi.user_id != $current_user 
            ORDER BY pi.created_at DESC LIMIT 20";
$res_int = $conn->query($int_sql);
if ($res_int && $res_int->num_rows > 0) {
    while($row = $res_int->fetch_assoc()) {
        $row['notif_type'] = $row['interaction_type']; // 'like' or 'comment'
        $notifications[] = $row;
    }
}

// 2. Fetch Connection Requests you received
$conn_sql = "SELECT c.status, c.created_at, r.fullname 
             FROM connections c 
             JOIN registration_details r ON c.sender_id = r.login_id 
             WHERE c.receiver_id = $current_user AND c.status = 'pending'
             ORDER BY c.created_at DESC LIMIT 20";
$res_conn = $conn->query($conn_sql);
if ($res_conn && $res_conn->num_rows > 0) {
    while($row = $res_conn->fetch_assoc()) {
        $row['notif_type'] = 'connection_request';
        $notifications[] = $row;
    }
}

// 3. Sort the combined array by date (Newest first!)
usort($notifications, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mini LinkedIn | Notifications</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="notification.css?v=<?php echo time(); ?>">
</head>
<body class="dashboard-body">

    <nav class="navbar">
        <a href="dashboard.php" class="nav-logo">MINI LINKEDIN</a>
        <div class="nav-links">
            <a href="dashboard.php">Home</a>
            <a href="connection_page.php">Connections</a>
            <a href="job.php">Jobs</a>
            <a href="message.php">Messaging</a>
            <a href="notification.php" style="color: #004182; border-bottom: 2px solid #004182; padding-bottom: 23px;">Notifications</a>
            <a href="profile.php">Profile</a>
        </div>
        <div class="nav-logout">
            <a href="notification.php?logout=true" class="btn-logout">LOGOUT <i class="fas fa-sign-out-alt"></i></a>
        </div>
    </nav>

    <div class="main-content-wrapper">
        <div class="notification-container">
            
            <aside class="notif-sidebar">
                <div class="sidebar-card">
                    <div class="sidebar-header">
                        <h3>Manage your Notifications</h3>
                    </div>
                    <div class="sidebar-menu">
                        <a href="#" class="menu-link active">All Notifications</a>
                        <a href="#" class="menu-link">My Posts</a>
                        <a href="#" class="menu-link">Mentions</a>
                    </div>
                </div>
            </aside>

            <main class="notif-main">
                <div class="notif-header-card">
                    <h2>Notifications</h2>
                    <p>Stay up to date with your network's activity.</p>
                </div>

                <div class="notif-list">
                    <?php if (count($notifications) > 0): ?>
                        <?php foreach ($notifications as $notif): 
                            
                            // Determine the icon, color, and text based on the notification type
                            $icon = ""; $icon_bg = ""; $message = ""; $link = "#";
                            
                            if ($notif['notif_type'] == 'like') {
                                $icon = "fas fa-thumbs-up";
                                $icon_bg = "bg-blue";
                                $message = "<strong>" . htmlspecialchars($notif['fullname']) . "</strong> liked your post.";
                                $link = "profile.php"; // Can link to the specific post later
                            } 
                            elseif ($notif['notif_type'] == 'comment') {
                                $icon = "fas fa-comment";
                                $icon_bg = "bg-green";
                                $message = "<strong>" . htmlspecialchars($notif['fullname']) . "</strong> commented on your post.";
                                $link = "profile.php";
                            } 
                            elseif ($notif['notif_type'] == 'connection_request') {
                                $icon = "fas fa-user-friends";
                                $icon_bg = "bg-purple";
                                $message = "<strong>" . htmlspecialchars($notif['fullname']) . "</strong> sent you a connection request.";
                                $link = "connection_page.php?tab=request"; // Links directly to your received requests tab!
                            }
                        ?>
                        
                        <a href="<?php echo $link; ?>" class="notif-card">
                            <div class="notif-icon-circle <?php echo $icon_bg; ?>">
                                <i class="<?php echo $icon; ?>"></i>
                            </div>
                            
                            <div class="notif-content">
                                <p class="notif-text"><?php echo $message; ?></p>
                                
                                <?php if (isset($notif['content']) && !empty($notif['content'])): ?>
                                    <p class="notif-preview">"<?php echo htmlspecialchars(substr($notif['content'], 0, 50)) . '...'; ?>"</p>
                                <?php endif; ?>
                                
                                <span class="notif-time"><?php echo date("M j, g:i a", strtotime($notif['created_at'])); ?></span>
                            </div>
                            
                            <div class="notif-options">
                                <i class="fas fa-ellipsis-h"></i>
                            </div>
                        </a>
                        
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state-card">
                            <i class="far fa-bell-slash" style="font-size: 50px; color: #cbd5e0; margin-bottom: 15px;"></i>
                            <h3>No new notifications</h3>
                            <p>When someone interacts with you, you'll see it here.</p>
                        </div>
                    <?php endif; ?>
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

</body>
</html>