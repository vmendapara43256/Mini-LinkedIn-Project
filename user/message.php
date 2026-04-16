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

// --- 1. AUTO-CREATE MESSAGES TABLE ---
$conn->query("CREATE TABLE IF NOT EXISTS messages (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    sender_id INT(11) NOT NULL,
    receiver_id INT(11) NOT NULL,
    message_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES login_credentials(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES login_credentials(id) ON DELETE CASCADE
)");

// --- 2. HANDLE SENDING A MESSAGE ---
if (isset($_POST['send_message'])) {
    $receiver_id = (int)$_POST['receiver_id'];
    $msg_text = $conn->real_escape_string(trim($_POST['message_text']));
    
    if (!empty($msg_text)) {
        // FIXED: Added sender_type and 'user' to the query
        $conn->query("INSERT INTO messages (sender_id, receiver_id, sender_type, message_text) VALUES ('$current_user', '$receiver_id', 'user', '$msg_text')");
    }
    // Refresh page to show the new message
    header("Location: message.php?user=" . $receiver_id); 
    exit();
}

// --- 3. FETCH ACCEPTED CONNECTIONS (For the Sidebar) ---
$connections_sql = "SELECT l.id as user_id, r.fullname, r.university 
                    FROM connections c 
                    JOIN login_credentials l ON (l.id = c.sender_id OR l.id = c.receiver_id) 
                    JOIN registration_details r ON l.id = r.login_id 
                    WHERE (c.sender_id = $current_user OR c.receiver_id = $current_user) 
                    AND c.status = 'accepted' AND l.id != $current_user";
$connections_result = $conn->query($connections_sql);

$connections = [];
if ($connections_result->num_rows > 0) {
    while($row = $connections_result->fetch_assoc()) {
        $connections[] = $row;
    }
}

// --- 4. DETERMINE ACTIVE CHAT ---
$active_user_id = isset($_GET['user']) ? (int)$_GET['user'] : (count($connections) > 0 ? $connections[0]['user_id'] : 0);
$active_user_name = "Select a connection";
$active_user_uni = "";

// Find the active user's details from our connections list
foreach ($connections as $conn_user) {
    if ($conn_user['user_id'] == $active_user_id) {
        $active_user_name = $conn_user['fullname'];
        $active_user_uni = $conn_user['university'];
        break;
    }
}

// --- 5. HANDLE "SHARE POST" FEATURE ---
$prefill_msg = "";
if (isset($_GET['share_post_id'])) {
    $post_id = (int)$_GET['share_post_id'];
    $prefill_msg = "Hey! Check out this post I found on the dashboard: Post #" . $post_id;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mini LinkedIn | Messaging</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="message.css?v=<?php echo time(); ?>">
</head>
<body class="dashboard-body">

    <nav class="navbar">
        <a href="dashboard.php" class="nav-logo">MINI LINKEDIN</a>
        <div class="nav-links">
            <a href="dashboard.php">Home</a>
            <a href="connection_page.php">Connections</a>
            <a href="job.php">Jobs</a>
            <a href="message.php" style="color: #004182; border-bottom: 2px solid #004182; padding-bottom: 23px;">Messaging</a>
            <a href="notification.php">Notifications</a>
            <a href="profile.php">Profile</a>
        </div>
        <div class="nav-logout">
            <a href="message.php?logout=true" class="btn-logout">LOGOUT <i class="fas fa-sign-out-alt"></i></a>
        </div>
    </nav>

    <div class="main-content-wrapper">
        <div class="messaging-container">
            
            <aside class="contacts-sidebar">
                <div class="sidebar-header">
                    <h3>Messaging</h3>
                    <i class="fas fa-edit new-msg-icon"></i>
                </div>
                
                <div class="contacts-list">
                    <?php if (count($connections) > 0): ?>
                        <?php foreach ($connections as $contact): ?>
                            <a href="message.php?user=<?php echo $contact['user_id']; ?>" class="contact-card <?php echo $active_user_id == $contact['user_id'] ? 'active-contact' : ''; ?>">
                                <div class="avatar-circle-small"><?php echo strtoupper(substr($contact['fullname'], 0, 1)); ?></div>
                                <div class="contact-info">
                                    <h4><?php echo htmlspecialchars($contact['fullname']); ?></h4>
                                    <p><?php echo htmlspecialchars($contact['university']); ?></p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="padding: 20px; text-align: center; color: #666; font-size: 14px;">
                            You don't have any connections to message yet. Go to the Connections tab to grow your network!
                        </div>
                    <?php endif; ?>
                </div>
            </aside>

            <main class="chat-main">
                <?php if ($active_user_id > 0): ?>
                    
                    <div class="chat-header">
                        <div class="chat-header-info">
                            <div class="avatar-circle-small"><?php echo strtoupper(substr($active_user_name, 0, 1)); ?></div>
                            <div>
                                <h3><?php echo htmlspecialchars($active_user_name); ?></h3>
                                <p><?php echo htmlspecialchars($active_user_uni); ?></p>
                            </div>
                        </div>
                        <i class="fas fa-ellipsis-h" style="color: #666; cursor: pointer;"></i>
                    </div>

                    <div class="chat-history" id="chat-box">
                        <?php
                        $msg_sql = "SELECT * FROM messages 
                                    WHERE (sender_id = $current_user AND receiver_id = $active_user_id) 
                                    OR (sender_id = $active_user_id AND receiver_id = $current_user) 
                                    ORDER BY created_at ASC";
                        $messages = $conn->query($msg_sql);

                        if ($messages->num_rows > 0) {
                            while ($msg = $messages->fetch_assoc()) {
                                $is_me = ($msg['sender_id'] == $current_user);
                                $bubble_class = $is_me ? "msg-sent" : "msg-received";
                                ?>
                                <div class="msg-row <?php echo $is_me ? 'row-sent' : 'row-received'; ?>">
                                    <?php if (!$is_me): ?>
                                        <div class="avatar-tiny"><?php echo strtoupper(substr($active_user_name, 0, 1)); ?></div>
                                    <?php endif; ?>
                                    
                                    <div class="msg-bubble <?php echo $bubble_class; ?>">
                                        <?php echo nl2br(htmlspecialchars($msg['message_text'])); ?>
                                        <span class="msg-time"><?php echo date("g:i A", strtotime($msg['created_at'])); ?></span>
                                    </div>
                                </div>
                                <?php
                            }
                        } else {
                            echo "<div style='text-align:center; color:#999; margin-top:20px; font-size: 13px;'>Say hello to " . htmlspecialchars($active_user_name) . " to start the conversation!</div>";
                        }
                        ?>
                    </div>

                    <div class="chat-input-area">
                        <form method="POST" action="message.php">
                            <input type="hidden" name="receiver_id" value="<?php echo $active_user_id; ?>">
                            <textarea name="message_text" placeholder="Write a message..." required><?php echo $prefill_msg; ?></textarea>
                            <div class="chat-actions">
                                <div>
                                    <i class="fas fa-image action-icon"></i>
                                    <i class="fas fa-paperclip action-icon"></i>
                                </div>
                                <button type="submit" name="send_message" class="btn-send">Send <i class="fas fa-paper-plane"></i></button>
                            </div>
                        </form>
                    </div>

                <?php else: ?>
                    <div class="empty-chat-state">
                        <i class="far fa-comments" style="font-size: 60px; color: #cbd5e0; margin-bottom: 15px;"></i>
                        <h2>Your Messages</h2>
                        <p>Select a connection on the left to start chatting.</p>
                    </div>
                <?php endif; ?>
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
        var chatBox = document.getElementById("chat-box");
        if (chatBox) {
            chatBox.scrollTop = chatBox.scrollHeight;
        }
    </script>

</body>
</html>