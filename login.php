<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config/db.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'student') {
        header("Location: students/dashboard.php");
    } else {
        header("Location: counselors/dashboard.php");
    }
    exit();
}

$error = '';
$email = '';
$success = '';

if (isset($_GET['registered'])) {
    $success = "Registration successful! You may now log in.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        die("Invalid Request.");
    }

    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please enter both your email and password.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);

            $_SESSION['user_id']   = $user['user_id'];
            $_SESSION['id_number'] = $user['id_number'];
            $_SESSION['name']      = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['role']      = $user['role'];

            if ($user['role'] === 'student') {
                header("Location: students/dashboard.php");
            } else {
                header("Location: counselors/dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid email address or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MindHub</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">

    <a href="index.php" class="top-left-logo">
        <img src="assets/images/logo.png" alt="MindHub Logo">
    </a>

    <div class="register-card">
        <div class="header">
            <h2>Mind<span class="hub-accent">Hub</span></h2>
            <p>Student Counseling & Online Appointment System</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert-success">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">
            
            <div class="form-group">
                <label>Email Address:</label>
                <input type="email" name="email" placeholder="e.g., student@school.edu.ph" 
                       value="<?= htmlspecialchars($email) ?>" required autofocus>
            </div>

            <div class="form-group">
                <label>Password:</label>
                <div class="password-wrap">
                    <input type="password" id="login_password" name="password" placeholder="Enter your password" required>
                    <button type="button" class="btn-toggle-pw" onclick="togglePassword('login_password', this)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-submit">Log In</button>
        </form>

        <div class="footer-link">
            Don't have an account yet? <a href="register.php">Register here</a>
        </div>
    </div>

    <script>
    function togglePassword(inputId, btn) {
        const input = document.getElementById(inputId);
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';

        btn.innerHTML = isPassword 
            ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>'
            : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
    }
    </script>

</body>
</html>