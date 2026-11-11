<?php
session_start();
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
        $stmt = $pdo->prepare("select * from users where email = ?");
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
    <title>Login - CCC MindHub</title>
    <link rel="stylesheet" href="assets/css/style.css">

     <style>
        html, body {
            margin: 0;
            height: 100%;
            overflow: hidden;
        }
    </style>
    
</head>

<body>

   
<a href="#" class="top-left-logo">
    <img src="assets/images/logo.png" alt=" Logo">
</a>

<div class="register-card">
    <div class="header">
        <h2>MindHub</h2>
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
            <label>CCC Email Address:</label>
            <input type="email" name="email" placeholder="e.g., student@ccc.edu.ph" 
                   value="<?= htmlspecialchars($email) ?>" required autofocus>
        </div>

        <div class="form-group">
            <label>Password:</label>
            <input type="password" name="password" placeholder="Enter your password" required>
        </div>

        <button type="submit" class="btn-submit">Log In</button>
    </form>

    <div class="footer-link">
        Don't have an account yet? <a href="register.php">Register here</a>
    </div>
</div>

</body>
</html>