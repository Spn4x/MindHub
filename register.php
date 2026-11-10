<?php
session_start();
require_once 'config/db.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        die("Invalid Request.");
    }

    $id_number  = trim($_POST['id_number']);
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $email      = trim($_POST['email']);
    $program    = trim($_POST['department_or_program']);
    $contact    = trim($_POST['contact_number']);
    $password   = $_POST['password'];
    $confirm_pw = $_POST['confirm_password'];

    if (empty($id_number) || empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirm_pw) {
        $error = "Passwords do not match.";
    } else {
        $checkStmt = $pdo->prepare("select user_id from users where id_number = ? or email = ?");
        $checkStmt->execute([$id_number, $email]);

        if ($checkStmt->fetch()) {
            $error = "That Student ID Number or Email is already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $sql = "insert into users (
                        id_number, first_name, last_name, email, password_hash, role, department_or_program, contact_number
                    ) values (?, ?, ?, ?, ?, 'student', ?, ?)";

            $insertStmt = $pdo->prepare($sql);

            $success_status = $insertStmt->execute([
                $id_number,
                $first_name,
                $last_name,
                $email,
                $hashed_password,
                $program,
                $contact
            ]);

            if ($success_status) {
                header("Location: index.php?registered=1");
                exit();
            } else {
                $error = "Something went wrong while saving your account. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration - CCC MindHub</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="register-card">
    <div class="header">
        <h2>CCC MindHub</h2>
        <p>Student Counseling & Online Appointment System</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert-error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="register.php">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">

        <div class="form-group">
            <label>Student ID Number:*</label>
            <input type="text" name="id_number" placeholder="e.g., 2024-10023" 
                   value="<?= htmlspecialchars($_POST['id_number'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label>First Name:*</label>
            <input type="text" name="first_name" placeholder="e.g., Juan" 
                   value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label>Last Name:*</label>
            <input type="text" name="last_name" placeholder="e.g., Dela Cruz" 
                   value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label>CCC Email Address:*</label>
            <input type="email" name="email" placeholder="student@ccc.edu.ph" 
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label>Degree Program / Course:*</label>
            <input type="text" name="department_or_program" placeholder="e.g., BSIT, BSED, BSBA" 
                   value="<?= htmlspecialchars($_POST['department_or_program'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label>Contact Number (Optional):</label>
            <input type="text" name="contact_number" placeholder="e.g., 09123456789" 
                   value="<?= htmlspecialchars($_POST['contact_number'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Password:*</label>
            <input type="password" name="password" placeholder="At least 6 characters" required>
        </div>

        <div class="form-group">
            <label>Confirm Password:*</label>
            <input type="password" name="confirm_password" placeholder="Repeat your password" required>
        </div>

        <button type="submit" class="btn-submit">Register Account</button>
    </form>

    <div class="footer-link">
        Already have an account? <a href="index.php">Log in here</a>
    </div>
</div>

</body>
</html>