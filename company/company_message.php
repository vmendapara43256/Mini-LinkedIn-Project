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

// --- SMART DATABASE UPGRADE: MESSAGES TABLE ---
$conn->query("CREATE TABLE IF NOT EXISTS messages (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    sender_id INT(11) NOT NULL,
    receiver_id INT(11) NOT NULL,
    message_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Check and add 'sender_type' if it's missing
$checkType = $conn->query("SHOW COLUMNS FROM messages LIKE 'sender_type'");
if ($checkType->num_rows == 0) {
    $conn->query("ALTER TABLE messages ADD COLUMN sender_type ENUM('company', 'student') NOT NULL AFTER receiver_id");
}

// Check and add 'is_read' if it's missing
$checkRead = $conn->query("SHOW COLUMNS FROM messages LIKE 'is_read'");
if ($checkRead->num_rows == 0) {
    $conn->query("ALTER TABLE messages ADD COLUMN is_read TINYINT(1) DEFAULT 0 AFTER message_text");
}

// --- HANDLE SENDING MESSAGE ---
if (isset($_POST['send_msg'])) {
    $receiver_id = (int)$_POST['receiver_id'];
    $msg_text = $conn->real_escape_string(trim($_POST['message_text']));
    
    if (!empty($msg_text)) {
        $conn->query("INSERT INTO messages (sender_id, receiver_id, sender_type, message_text) 
                      VALUES ($company_id, $receiver_id, 'company', '$msg_text')");
        header("Location: company_message.php?student_id=$receiver_id");
        exit();
    }
}

// --- GET ACTIVE CHAT ---
$active_student_id = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mini LinkedIn Business | Messages</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="company_style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="messages.css?v=<?php echo time(); ?>">
</head>
<body class="dashboard-body">

<nav class="navbar">
    <a href="company_dashboard.php" class="nav-logo">MINI LINKEDIN <span class="badge">BUSINESS</span></a>
    <div class="nav-links">
        <a href="company_dashboard.php">Dashboard</a>
        <a href="post_job.php">Post a Job</a>
        <a href="applications.php">Pipeline</a>
        <a href="schedule.php">Schedule</a>
        <a href="company_message.php" class="active-nav">Messages</a>
        <a href="company_profile.php">Profile</a>
    </div>
    <div class="nav-logout">
        <a href="company_dashboard.php?logout=true" class="btn-logout">LOGOUT</a>
    </div>
</nav>

<div class="main-content-wrapper">
    <div class="company-container">
        <div class="messaging-card panel-card">
            
            <aside class="contacts-sidebar">
                <div class="sidebar-header">
                    <h3>Conversations</h3>
                </div>
                <div class="contact-list">
                    <?php
                    // Fetch students who have applied to this company's jobs OR who have sent messages
                    $contacts_sql = "SELECT DISTINCT r.login_id, r.fullname, r.university 
                                    FROM registration_details r
                                    LEFT JOIN applications a ON r.login_id = a.user_id
                                    LEFT JOIN jobs j ON a.job_id = j.id
                                    LEFT JOIN messages m ON r.login_id = m.sender_id OR r.login_id = m.receiver_id
                                    WHERE j.company_id = $company_id OR (m.sender_id = $company_id OR m.receiver_id = $company_id)
                                    AND r.login_id IS NOT NULL";
                    $contacts = $conn->query($contacts_sql);

                    if ($contacts && $contacts->num_rows > 0) {
                        while($row = $contacts->fetch_assoc()):
                            if($row['login_id'] == null) continue;
                        ?>
                        <a href="company_message.php?student_id=<?php echo $row['login_id']; ?>" 
                           class="contact-item <?php echo ($active_student_id == $row['login_id']) ? 'active' : ''; ?>">
                            <div class="avatar-sm"><?php echo substr($row['fullname'], 0, 1); ?></div>
                            <div class="contact-info">
                                <p class="contact-name"><?php echo htmlspecialchars($row['fullname']); ?></p>
                                <p class="contact-sub"><?php echo htmlspecialchars($row['university']); ?></p>
                            </div>
                        </a>
                        <?php endwhile; 
                    } else {
                        echo "<p style='padding:20px; font-size:13px; color:#999;'>No conversations yet.</p>";
                    }
                    ?>
                </div>
            </aside>

            <main class="chat-main">
                <?php if ($active_student_id > 0): 
                    $student_name_query = $conn->query("SELECT fullname FROM registration_details WHERE login_id = $active_student_id");
                    $student_name = ($student_name_query) ? $student_name_query->fetch_assoc()['fullname'] : "Student";
                ?>
                    <div class="chat-header">
                        <div class="avatar-sm"><?php echo substr($student_name, 0, 1); ?></div>
                        <h4>Chatting with <?php echo htmlspecialchars($student_name); ?></h4>
                    </div>

                    <div class="message-display" id="chatBox">
                        <?php
                        $msg_sql = "SELECT * FROM messages 
                                    WHERE (sender_id = $company_id AND receiver_id = $active_student_id AND sender_type = 'company')
                                    OR (sender_id = $active_student_id AND receiver_id = $company_id AND sender_type = 'student')
                                    ORDER BY created_at ASC";
                        $msgs = $conn->query($msg_sql);
                        if($msgs):
                            while($m = $msgs->fetch_assoc()):
                                $side = ($m['sender_type'] == 'company') ? 'outgoing' : 'incoming';
                            ?>
                                <div class="message-bubble <?php echo $side; ?>">
                                    <p><?php echo htmlspecialchars($m['message_text']); ?></p>
                                    <span class="msg-time"><?php echo date('h:i A', strtotime($m['created_at'])); ?></span>
                                </div>
                            <?php endwhile;
                        endif; ?>
                    </div>

                    <form method="POST" class="message-input-area" autocomplete="off">
                        <input type="hidden" name="receiver_id" value="<?php echo $active_student_id; ?>">
                        <input type="text" name="message_text" placeholder="Write a message..." required>
                        <button type="submit" name="send_msg"><i class="fas fa-paper-plane"></i></button>
                    </form>
                <?php else: ?>
                    <div class="chat-empty">
                        <i class="far fa-comments"></i>
                        <p>Select a candidate to start the conversation.</p>
                    </div>
                <?php endif; ?>
            </main>

        </div>
    </div>
</div>

<script>
    const chatBox = document.getElementById('chatBox');
    if(chatBox) chatBox.scrollTop = chatBox.scrollHeight;
</script>
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