<?php
session_start();

// Handle Logout
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

// --- 1. TABLE CREATION ---
$conn->query("CREATE TABLE IF NOT EXISTS connections (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    sender_id INT(11) NOT NULL,
    receiver_id INT(11) NOT NULL,
    status ENUM('pending', 'accepted') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES login_credentials(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES login_credentials(id) ON DELETE CASCADE
)");

// --- BUTTON LOGIC ---

// Connect -> Stays on People You May Know (suggestions)
if (isset($_POST['connect'])) {
    $receiver_id = (int)$_POST['receiver_id'];
    $check = $conn->query("SELECT id FROM connections WHERE (sender_id = $current_user AND receiver_id = $receiver_id) OR (sender_id = $receiver_id AND receiver_id = $current_user)");
    if ($check->num_rows == 0) {
        $conn->query("INSERT INTO connections (sender_id, receiver_id, status) VALUES ('$current_user', '$receiver_id', 'pending')");
    }
    header("Location: connection_page.php?tab=suggestions"); exit(); 
}

// Accept -> Stays on Request tab
if (isset($_POST['accept'])) {
    $conn_id = (int)$_POST['connection_id'];
    $conn->query("UPDATE connections SET status = 'accepted' WHERE id = $conn_id AND receiver_id = $current_user");
    header("Location: connection_page.php?tab=request"); exit(); 
}

// Reject -> Stays on Request tab
if (isset($_POST['reject'])) {
    $conn_id = (int)$_POST['connection_id'];
    $conn->query("DELETE FROM connections WHERE id = $conn_id AND receiver_id = $current_user AND status = 'pending'");
    header("Location: connection_page.php?tab=request"); exit(); 
}

// Cancel -> Stays on Sent Request tab
if (isset($_POST['cancel'])) {
    $conn_id = (int)$_POST['connection_id'];
    $conn->query("DELETE FROM connections WHERE id = $conn_id AND sender_id = $current_user AND status = 'pending'");
    header("Location: connection_page.php?tab=sent"); exit(); 
}

// Remove -> Stays on My Connection tab
if (isset($_POST['remove'])) {
    $conn_id = (int)$_POST['connection_id'];
    $conn->query("DELETE FROM connections WHERE id = $conn_id AND (receiver_id = $current_user OR sender_id = $current_user)");
    header("Location: connection_page.php?tab=connections"); exit(); 
}

// Sidebar Stats
$conn_count = $conn->query("SELECT COUNT(*) as total FROM connections WHERE status='accepted' AND (sender_id=$current_user OR receiver_id=$current_user)")->fetch_assoc()['total'];
$request_count = $conn->query("SELECT COUNT(*) as total FROM connections WHERE receiver_id = $current_user AND status = 'pending'")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mini LinkedIn | Connections</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="connection_page.css?v=<?php echo time(); ?>">
</head>
<body class="dashboard-body">

    <nav class="navbar">
        <a href="dashboard.php" class="nav-logo">MINI LINKEDIN</a>
        <div class="nav-links">
            <a href="dashboard.php">Home</a>
            <a href="connection_page.php" style="color: #004182; border-bottom: 2px solid #004182; padding-bottom: 23px;">Connections</a>
            <a href="job.php">Jobs</a>
            <a href="message.php">Messaging</a>
            <a href="notification.php">Notifications</a>
            <a href="profile.php">Profile</a>
        </div>
        <div class="nav-logout">
            <a href="connection_page.php?logout=true" class="btn-logout">LOGOUT <i class="fas fa-sign-out-alt"></i></a>
        </div>
    </nav>

    <div class="main-content-wrapper">
        <div class="network-container">
            
            <aside class="network-sidebar">
                <div class="sidebar-card">
                    <div class="sidebar-stats" style="border-top: none;">
                        <h3 style="font-size: 16px; color: #1a1a1a; margin-bottom: 15px;">Manage my network</h3>
                        <div class="stat"><span>Connections</span> <strong><?php echo $conn_count; ?></strong></div>
                        <div class="stat"><span>Followers</span> <strong><?php echo $conn_count; ?></strong></div>
                    </div>
                </div>
            </aside>

            <main class="network-main">
                
                <div class="network-tabs">
                    <button class="tab-btn" onclick="openTab(event, 'suggestions')" id="btn-suggestions">People you may know</button>
                    <button class="tab-btn" onclick="openTab(event, 'request')" id="btn-request">
                        Request
                        <?php if($request_count > 0): ?>
                            <span class="notification-badge"><?php echo $request_count; ?></span>
                        <?php endif; ?>
                    </button>
                    <button class="tab-btn" onclick="openTab(event, 'sent')" id="btn-sent">Sent Request</button>
                    <button class="tab-btn" onclick="openTab(event, 'connections')" id="btn-connections">My Connection</button>
                </div>

                <div id="suggestions" class="tab-content" style="display: none;">
                    <div class="network-section-card">
                        <div class="section-header"><h2>People you may know</h2></div>
                        <div class="user-grid">
                            <?php
                            $query = "SELECT l.id, r.fullname, r.university 
                                      FROM login_credentials l 
                                      JOIN registration_details r ON l.id = r.login_id 
                                      WHERE l.id != $current_user 
                                      AND l.id NOT IN (
                                          SELECT sender_id FROM connections WHERE receiver_id = $current_user
                                          UNION 
                                          SELECT receiver_id FROM connections WHERE sender_id = $current_user
                                      ) LIMIT 12";
                            $suggestions = $conn->query($query);
                            if ($suggestions->num_rows > 0) {
                                while ($user = $suggestions->fetch_assoc()):
                            ?>
                                <div class="user-card">
                                    <div class="user-card-cover"></div>
                                    <div class="user-card-avatar"><?php echo strtoupper(substr($user['fullname'], 0, 1)); ?></div>
                                    <div class="user-card-info">
                                        <h4><?php echo htmlspecialchars($user['fullname']); ?></h4>
                                        <p><?php echo htmlspecialchars($user['university']); ?></p>
                                    </div>
                                    <form method="POST" class="user-card-action">
                                        <input type="hidden" name="receiver_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" name="connect" class="btn-connect"><i class="fas fa-user-plus"></i> Connect</button>
                                    </form>
                                </div>
                            <?php endwhile; } else { echo "<p style='padding: 20px; color: #666;'>No new suggestions at the moment.</p>"; } ?>
                        </div>
                    </div>
                </div>

                <div id="request" class="tab-content" style="display: none;">
                    <div class="network-section-card">
                        <div class="section-header"><h2>Received Requests</h2></div>
                        <div class="request-list">
                            <?php
                            $received = $conn->query("SELECT c.id as connection_id, l.id as user_id, r.fullname, r.university 
                                                      FROM connections c JOIN login_credentials l ON c.sender_id = l.id 
                                                      JOIN registration_details r ON l.id = r.login_id 
                                                      WHERE c.receiver_id = $current_user AND c.status = 'pending'");
                            if ($received->num_rows > 0):
                                while ($req = $received->fetch_assoc()): ?>
                                    <div class="request-item">
                                        <div class="req-user-info">
                                            <div class="avatar-circle"><?php echo strtoupper(substr($req['fullname'], 0, 1)); ?></div>
                                            <div>
                                                <h4><?php echo htmlspecialchars($req['fullname']); ?></h4>
                                                <p><?php echo htmlspecialchars($req['university']); ?></p>
                                            </div>
                                        </div>
                                        <div class="req-actions">
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="connection_id" value="<?php echo $req['connection_id']; ?>">
                                                <button type="submit" name="reject" class="btn-grey">Reject</button>
                                                <button type="submit" name="accept" class="btn-blue">Accept</button>
                                            </form>
                                        </div>
                                    </div>
                            <?php endwhile; else: ?>
                                <p style="padding: 20px; color: #666;">You have no pending received requests.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div id="sent" class="tab-content" style="display: none;">
                    <div class="network-section-card">
                        <div class="section-header"><h2>Sent Requests</h2></div>
                        <div class="request-list">
                            <?php
                            $sent = $conn->query("SELECT c.id as connection_id, l.id as user_id, r.fullname, r.university 
                                                  FROM connections c JOIN login_credentials l ON c.receiver_id = l.id 
                                                  JOIN registration_details r ON l.id = r.login_id 
                                                  WHERE c.sender_id = $current_user AND c.status = 'pending'");
                            if ($sent->num_rows > 0):
                                while ($req = $sent->fetch_assoc()): ?>
                                    <div class="request-item">
                                        <div class="req-user-info">
                                            <div class="avatar-circle" style="background:#666;"><?php echo strtoupper(substr($req['fullname'], 0, 1)); ?></div>
                                            <div>
                                                <h4><?php echo htmlspecialchars($req['fullname']); ?></h4>
                                                <p>Waiting for them to accept...</p>
                                            </div>
                                        </div>
                                        <div class="req-actions">
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="connection_id" value="<?php echo $req['connection_id']; ?>">
                                                <button type="submit" name="cancel" class="btn-grey">Cancel</button>
                                            </form>
                                        </div>
                                    </div>
                            <?php endwhile; else: ?>
                                <p style="padding: 20px; color: #666;">You haven't sent any requests.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div id="connections" class="tab-content" style="display: none;">
                    <div class="network-section-card">
                        <div class="section-header"><h2>My Connections</h2></div>
                        <div class="user-grid">
                            <?php
                            $my_network = $conn->query("SELECT c.id as connection_id, l.id as user_id, r.fullname, r.university 
                                                        FROM connections c JOIN login_credentials l ON (l.id = c.sender_id OR l.id = c.receiver_id) 
                                                        JOIN registration_details r ON l.id = r.login_id 
                                                        WHERE (c.sender_id = $current_user OR c.receiver_id = $current_user) 
                                                        AND c.status = 'accepted' AND l.id != $current_user");
                            if ($my_network->num_rows > 0) {
                                while ($conn_user = $my_network->fetch_assoc()):
                            ?>
                                <div class="user-card">
                                    <div class="user-card-cover"></div>
                                    <div class="user-card-avatar"><?php echo strtoupper(substr($conn_user['fullname'], 0, 1)); ?></div>
                                    <div class="user-card-info">
                                        <h4><?php echo htmlspecialchars($conn_user['fullname']); ?></h4>
                                        <p><?php echo htmlspecialchars($conn_user['university']); ?></p>
                                    </div>
                                    <form method="POST" class="user-card-action">
                                        <input type="hidden" name="connection_id" value="<?php echo $conn_user['connection_id']; ?>">
                                        <a href="message.php?user=<?php echo $conn_user['user_id']; ?>" class="btn-message">Message</a>
                                        <button type="submit" name="remove" class="btn-remove" title="Remove Connection"><i class="fas fa-user-times"></i></button>
                                    </form>
                                </div>
                            <?php endwhile; } else { echo "<p style='padding: 20px; color: #666;'>You don't have any connections yet.</p>"; } ?>
                        </div>
                    </div>
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
        function openTab(evt, tabName) {
            var tabcontent = document.getElementsByClassName("tab-content");
            for (var i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }
            
            var tablinks = document.getElementsByClassName("tab-btn");
            for (var i = 0; i < tablinks.length; i++) {
                tablinks[i].classList.remove("active");
            }
            
            document.getElementById(tabName).style.display = "block";
            
            // If clicked by user
            if (evt) {
                evt.currentTarget.classList.add("active");
            } else {
                // If loaded by PHP
                document.getElementById('btn-' + tabName).classList.add("active");
            }
        }

        // On Page Load: Check URL for the tab, default to 'suggestions' (People you may know)
        window.onload = function() {
            var activeTab = '<?php echo isset($_GET['tab']) ? $_GET['tab'] : 'suggestions'; ?>';
            openTab(null, activeTab);
        };
    </script>

</body>
</html>