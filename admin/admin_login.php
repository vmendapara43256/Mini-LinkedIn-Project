<?php
session_start();

$host = "localhost";
$rootUser = "root";
$rootPass = ""; 
$dbName = "mini_linkedin";
$conn = new mysqli($host, $rootUser, $rootPass, $dbName);

// --- AUTO-SQL SETUP ---
$conn->query("CREATE TABLE IF NOT EXISTS admin_auth (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL
)");

$checkAdmin = $conn->query("SELECT * FROM admin_auth LIMIT 1");
if ($checkAdmin->num_rows == 0) {
    $conn->query("INSERT INTO admin_auth (username, password) VALUES ('admin', 'admin123')");
}

$conn->query("ALTER TABLE login_credentials ADD COLUMN IF NOT EXISTS account_status ENUM('active', 'blocked') DEFAULT 'active'");
$conn->query("ALTER TABLE company_credentials ADD COLUMN IF NOT EXISTS is_verified TINYINT(1) DEFAULT 0");

$error = "";

if (isset($_POST['admin_login'])) {
    $user = $conn->real_escape_string($_POST['username']);
    $pass = $conn->real_escape_string($_POST['password']);

    $sql = "SELECT * FROM admin_auth WHERE username='$user' AND password='$pass'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user'] = $user;
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $error = "Invalid Master Credentials!";
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin_login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Portal | Secure Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="admin_login.css?v=<?php echo time(); ?>">
</head>
<body class="admin-login-body">

    <div class="login-card">
        <div class="card-header">
            <div class="admin-icon">
                <i class="fas fa-user-shield"></i>
            </div>
            <h1>Admin <span>Access</span></h1>
            <p>Enter master credentials to manage the platform.</p>
        </div>

        <?php if($error): ?>
            <div class="alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="" autocomplete="off">
            <input autocomplete="false" name="hidden" type="text" style="display:none;">

            <div class="form-group">
                <label>Username</label>
                <div class="input-wrapper">
                    <i class="fas fa-user"></i>
                    <input type="text" name="username" placeholder="Master ID" required autocomplete="new-password">
                </div>
            </div>

            <div class="form-group">
                <label>Password</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="••••••••" required autocomplete="new-password">
                </div>
            </div>

            <button type="submit" name="admin_login" class="btn-admin-login">
                Unlock Dashboard <i class="fas fa-arrow-right"></i>
            </button>
        </form>

        <div class="card-footer">
            <p><i class="fas fa-info-circle"></i> Restricted area for administrators only.</p>
        </div>

        <div class="portal-switcher">
            <a href="../user/login.php" class="portal-link">
                <i class="fas fa-user-graduate"></i> Student Portal
            </a>
            <div class="dot-sep"></div>
            <a href="../company/company_login.php" class="portal-link">
                <i class="fas fa-building"></i> Company Portal
            </a>
        </div>
    </div>

</body>
</html>