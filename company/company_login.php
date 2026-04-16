<?php
session_start();
$host = "localhost";
$rootUser = "root";
$rootPass = ""; 
$dbName = "mini_linkedin";

$conn = new mysqli($host, $rootUser, $rootPass);
$conn->query("CREATE DATABASE IF NOT EXISTS $dbName");
$conn->select_db($dbName);

// --- AUTO-CREATE COMPANY TABLE ---
$conn->query("CREATE TABLE IF NOT EXISTS company_credentials (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// --- FLASH MESSAGES LOGIC ---
$message = "";
$messageType = "";
$showRegister = false;

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $messageType = $_SESSION['message_type'];
    if (isset($_SESSION['keep_register_open']) && $_SESSION['keep_register_open'] == true) {
        $showRegister = true;
    }
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
    unset($_SESSION['keep_register_open']);
}

// --- 1. HANDLE REGISTRATION ---
if (isset($_POST['register_company'])) {
    $c_name = trim($_POST['company_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check for empty fields
    if (empty($c_name) || empty($email) || empty($password) || empty($confirm_password)) {
        $_SESSION['message'] = "Please fill in all details to register.";
        $_SESSION['message_type'] = "error";
        $_SESSION['keep_register_open'] = true;
        header("Location: company_login.php"); exit();
    }

    // Check if passwords match
    if ($password !== $confirm_password) {
        $_SESSION['message'] = "Passwords do not match. Please try again.";
        $_SESSION['message_type'] = "error";
        $_SESSION['keep_register_open'] = true;
        header("Location: company_login.php"); exit();
    }

    $c_name = $conn->real_escape_string($c_name);
    $email = $conn->real_escape_string($email);
    
    $checkEmail = $conn->query("SELECT id FROM company_credentials WHERE email = '$email'");
    if ($checkEmail->num_rows > 0) {
        $_SESSION['message'] = "An employer account with this email already exists.";
        $_SESSION['message_type'] = "error";
        $_SESSION['keep_register_open'] = true;
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        if ($conn->query("INSERT INTO company_credentials (company_name, email, password) VALUES ('$c_name', '$email', '$hashed_password')")) {
            // Success! Send them back to the Login view
            $_SESSION['message'] = "Company registered successfully! Please log in.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Something went wrong. Please try again.";
            $_SESSION['message_type'] = "error";
            $_SESSION['keep_register_open'] = true;
        }
    }
    header("Location: company_login.php"); exit();
}

// --- 2. HANDLE LOGIN ---
if (isset($_POST['login_company'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $_SESSION['message'] = "Please enter your email and password.";
        $_SESSION['message_type'] = "error";
        header("Location: company_login.php"); exit();
    }

    $email = $conn->real_escape_string($email);
    $result = $conn->query("SELECT * FROM company_credentials WHERE email = '$email'");
    
    if ($result->num_rows == 1) {
        $company = $result->fetch_assoc();
        // Verify hashed password
        if (password_verify($password, $company['password'])) {
            $_SESSION['company_id'] = $company['id'];
            $_SESSION['company_name'] = $company['company_name'];
            header("Location: company_dashboard.php"); 
            exit();
        } else {
            $_SESSION['message'] = "Incorrect password.";
            $_SESSION['message_type'] = "error";
        }
    } else {
        $_SESSION['message'] = "Company account not found. Please register.";
        $_SESSION['message_type'] = "error";
    }
    header("Location: company_login.php"); exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mini LinkedIn | Company Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="company_auth.css?v=<?php echo time(); ?>">
</head>
<body>

    <div class="auth-wrapper">
        <div class="auth-card">
            
            <div class="auth-header">
                <h2>MINI LINKEDIN <span class="badge">BUSINESS</span></h2>
            </div>
            
            <?php if ($message != ""): ?>
                <div class="alert <?php echo $messageType; ?>"><?php echo $message; ?></div>
            <?php endif; ?>

            <div id="loginForm" class="form-box <?php echo $showRegister ? '' : 'active'; ?>">
                <p class="form-subtitle">Log in to access your employer dashboard.</p>
                <form method="POST" action="company_login.php" autocomplete="off" novalidate>
                    <div class="input-group">
                        <label>Corporate Email</label>
                        <input type="email" name="email" required placeholder="hr@company.com" autocomplete="off">
                    </div>
                    <div class="input-group">
                        <label>Password</label>
                        <input type="password" name="password" required placeholder="Enter your password" autocomplete="new-password">
                    </div>
                    <button type="submit" name="login_company" class="btn-auth">Log In to Dashboard</button>
                </form>
                <div class="auth-footer">
                    <p>New to the platform? <a href="#" onclick="toggleForms('registerForm', event)">Register your company</a></p>
                </div>
            </div>

            <div id="registerForm" class="form-box <?php echo $showRegister ? 'active' : ''; ?>">
                <p class="form-subtitle">Create an employer account to post jobs.</p>
                <form method="POST" action="company_login.php" autocomplete="off" novalidate>
                    <div class="input-group">
                        <label>Company Name</label>
                        <input type="text" name="company_name" placeholder="e.g. TechCorp Solutions" autocomplete="off">
                    </div>
                    <div class="input-group">
                        <label>Corporate Email</label>
                        <input type="email" name="email" placeholder="hr@company.com" autocomplete="off">
                    </div>
                    <div class="input-group">
                        <label>Password</label>
                        <input type="password" name="password" placeholder="Create a secure password" autocomplete="new-password">
                    </div>
                    <div class="input-group">
                        <label>Confirm Password</label>
                        <input type="password" name="confirm_password" placeholder="Confirm your password" autocomplete="new-password">
                    </div>
                    <button type="submit" name="register_company" class="btn-auth">Register Company</button>
                </form>
                <div class="auth-footer">
                    <p>Already have an account? <a href="#" onclick="toggleForms('loginForm', event)">Log In</a></p>
                </div>
            </div>

            <div class="portal-switcher">
                <p>Looking for a different portal?</p>
                <div class="switcher-links">
                    <a href="../user/login.php"><i class="fas fa-user-graduate"></i> Student Portal</a>
                    <a href="../admin/admin_login.php"><i class="fas fa-shield-alt"></i> Admin Portal</a>
                </div>
            </div>

        </div> 
    </div>

    <script>
        // Smoothly switches between Login and Register views
        function toggleForms(formId, event) {
            event.preventDefault();
            document.getElementById('loginForm').classList.remove('active');
            document.getElementById('registerForm').classList.remove('active');
            document.getElementById(formId).classList.add('active');
            
            // Hide the alert message when switching tabs
            const alertBox = document.querySelector('.alert');
            if(alertBox) alertBox.style.display = 'none';
        }
    </script>

</body>
</html>