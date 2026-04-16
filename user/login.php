<?php
session_start();

// --- DATABASE SETUP ---
$host = "localhost";
$rootUser = "root";
$rootPass = ""; 
$dbName = "mini_linkedin";

$conn = new mysqli($host, $rootUser, $rootPass);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->query("CREATE DATABASE IF NOT EXISTS $dbName");
$conn->select_db($dbName);

// TABLE 1: Strictly for secure login credentials
// ADDED: account_status column for Admin Control
$loginTable = "CREATE TABLE IF NOT EXISTS login_credentials (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    account_status ENUM('active', 'blocked') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($loginTable);

// TABLE 2: Strictly for student registration profile details
$detailsTable = "CREATE TABLE IF NOT EXISTS registration_details (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    login_id INT(11) NOT NULL,
    fullname VARCHAR(100) NOT NULL,
    university VARCHAR(150) NOT NULL,
    FOREIGN KEY (login_id) REFERENCES login_credentials(id) ON DELETE CASCADE
)";
$conn->query($detailsTable);

// --- FLASH MESSAGES & SAVED DATA LOGIC ---
$message = "";
$messageType = "";
$showRegister = false;
$savedEmail = ""; 

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $messageType = $_SESSION['message_type'];
    
    if (isset($_SESSION['keep_register_open']) && $_SESSION['keep_register_open'] == true) {
        $showRegister = true;
    }
    
    if (isset($_SESSION['attempted_email'])) {
        $savedEmail = $_SESSION['attempted_email'];
    }

    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
    unset($_SESSION['keep_register_open']);
    unset($_SESSION['attempted_email']);
}

// --- HANDLE REGISTRATION ---
if (isset($_POST['register'])) {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $university = trim($_POST['university']); 
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($fullname) || empty($email) || empty($university) || empty($password) || empty($confirm_password)) {
        $_SESSION['message'] = "Please fill in all details, including password confirmation.";
        $_SESSION['message_type'] = "error";
        $_SESSION['keep_register_open'] = true;
        header("Location: login.php");
        exit();
    }

    if ($password !== $confirm_password) {
        $_SESSION['message'] = "Passwords do not match. Please try again.";
        $_SESSION['message_type'] = "error";
        $_SESSION['keep_register_open'] = true;
        header("Location: login.php");
        exit();
    }
    
    $fullname = $conn->real_escape_string($fullname);
    $email = $conn->real_escape_string($email);
    $university = $conn->real_escape_string($university);
    
    $checkEmail = $conn->query("SELECT id FROM login_credentials WHERE email = '$email'");
    if ($checkEmail->num_rows > 0) {
        $_SESSION['message'] = "Email is already registered. Please log in.";
        $_SESSION['message_type'] = "error";
        $_SESSION['keep_register_open'] = true;
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $insertLogin = $conn->query("INSERT INTO login_credentials (email, password) VALUES ('$email', '$hashed_password')");
        
        if ($insertLogin) {
            $new_login_id = $conn->insert_id;
            $insertDetails = $conn->query("INSERT INTO registration_details (login_id, fullname, university) VALUES ('$new_login_id', '$fullname', '$university')");
            
            $_SESSION['message'] = "You successfully registered! Now log in.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Something went wrong. Please try again.";
            $_SESSION['message_type'] = "error";
            $_SESSION['keep_register_open'] = true;
        }
    }
    
    header("Location: login.php");
    exit();
}

// --- HANDLE LOGIN ---
if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $_SESSION['message'] = "Please enter your email and password to login.";
        $_SESSION['message_type'] = "error";
        header("Location: login.php");
        exit();
    }

    $email = $conn->real_escape_string($email);

    // UPDATED QUERY: Including account_status check
    $query = "SELECT l.id, l.email, l.password, l.account_status, r.fullname, r.university 
              FROM login_credentials l 
              JOIN registration_details r ON l.id = r.login_id 
              WHERE l.email = '$email'";
              
    $result = $conn->query($query);
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // ADMIN CONTROL: Check if account is blocked
        if ($user['account_status'] == 'blocked') {
            $_SESSION['message'] = "Your account has been suspended by the Admin. Please contact support.";
            $_SESSION['message_type'] = "error";
            header("Location: login.php");
            exit();
        }

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['university'] = $user['university']; 
            header("Location: dashboard.php"); 
            exit();
        } else {
            $_SESSION['message'] = "Incorrect password.";
            $_SESSION['message_type'] = "error";
            $_SESSION['attempted_email'] = $email; 
            header("Location: login.php");
            exit();
        }
    } else {
        $_SESSION['message'] = "Account not found. Please register.";
        $_SESSION['message_type'] = "error";
        header("Location: login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mini LinkedIn - Student Portal</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    
    <?php if($message != ""): ?>
        <div class="message <?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div id="loginForm" class="form-box <?php echo $showRegister ? '' : 'active'; ?>">
        <h2>Student Login</h2>
        <form method="POST" action="login.php" autocomplete="off" novalidate>
            <div class="input-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="Enter your student email" value="<?php echo htmlspecialchars($savedEmail); ?>" autocomplete="off">
            </div>
            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter your password" autocomplete="new-password">
            </div>
            <button type="submit" name="login" class="btn">Login to Portal</button>
        </form>
        <div class="toggle-link">
            New to Mini LinkedIn? <a onclick="toggleForms('registerForm')">Create an account</a>
        </div>
    </div>

    <div id="registerForm" class="form-box <?php echo $showRegister ? 'active' : ''; ?>">
        <h2>Join as a Student</h2>
        <form method="POST" action="login.php" autocomplete="off" novalidate>
            <div class="input-group">
                <label>Full Name</label>
                <input type="text" name="fullname" placeholder="Enter your full name" autocomplete="off">
            </div>
            <div class="input-group">
                <label>University Name</label>
                <input type="text" name="university" placeholder="E.g. Gujarat Technological University" autocomplete="off">
            </div>
            <div class="input-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="Enter your email" autocomplete="off">
            </div>
            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Create a password (min 6 chars)" autocomplete="new-password">
            </div>
            <div class="input-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" placeholder="Confirm your password" autocomplete="new-password">
            </div>
            <button type="submit" name="register" class="btn">Register Account</button>
        </form>
        <div class="toggle-link">
            Already have an account? <a onclick="toggleForms('loginForm')">Log in here</a>
        </div>
    </div>

    <div class="external-access">
        <a href="../admin/admin_login.php">Admin Access</a>
        <a href="../company/company_login.php">Company Access</a>
    </div>

</div>

<script>
    function toggleForms(formId) {
        document.getElementById('loginForm').classList.remove('active');
        document.getElementById('registerForm').classList.remove('active');
        document.getElementById(formId).classList.add('active');
        
        const msg = document.querySelector('.message');
        if(msg) msg.style.display = 'none';
    }
</script>

</body> 
</html>