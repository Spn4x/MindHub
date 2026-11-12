<?php
session_start();
require_once 'config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_number  = trim($_POST['id_number']);
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $program    = trim($_POST['department_or_program']);
    $contact    = trim($_POST['contact_number']);

    $email      = trim($_POST['email']);
    $password   = $_POST['password'];
    $confirm_pw = $_POST['confirm_password'];

    if (empty($id_number) || empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif ($password !== $confirm_pw) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
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
                header("Location: login.php?registered=1");
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
    <title>Student Registration - MindHub</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">

<div class="auth-wrapper">

    <div class="auth-poster-side">
        <img class="poster-hero" src="assets/images/post.png" alt="MindHub Guidance Poster">
    </div>

    <div class="auth-form-side">
        
        <a href="index.php" class="auth-logo">
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

            <form method="POST" action="register.php" id="registrationForm">
                <div class="step-section active" id="group-1">
                    <div class="form-group">
                        <label>Student ID Number:*</label>
                        <input type="text" id="id_number" name="id_number" placeholder="Enter your Student ID" 
                               value="<?= htmlspecialchars($id_number ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label>First Name:*</label>
                        <input type="text" id="first_name" name="first_name" placeholder="Enter Your First Name" 
                               value="<?= htmlspecialchars($first_name ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Last Name:*</label>
                        <input type="text" id="last_name" name="last_name" placeholder="Enter Your Last Name" 
                               value="<?= htmlspecialchars($last_name ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Degree Program / Course:*</label>
                        <input type="text" id="department_or_program" name="department_or_program" placeholder="e.g., BSIT, BSED, BSBA" 
                               value="<?= htmlspecialchars($program ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Contact Number (Optional):</label>
                        <input type="text" id="contact_number" name="contact_number" placeholder="e.g., 09123456789" 
                               value="<?= htmlspecialchars($contact ?? '') ?>">
                    </div>
                    
                    <div id="step-error-box" class="step-error"></div>
                    <button type="button" class="btn-submit" onclick="goToStep(2)">Next →</button>
                </div>

                <div class="step-section" id="group-2">
                    <div class="form-group">
                        <label>Institutional Email Address:*</label>
                        <input type="email" id="email" name="email" placeholder="student@school.edu.ph" 
                               value="<?= htmlspecialchars($email ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Password:*</label>
                        <div class="password-wrap">
                            <input type="password" id="reg_password" name="password" placeholder="At least 6 characters" required>
                            <button type="button" class="btn-toggle-pw" onclick="togglePassword('reg_password', this)">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Confirm Password:*</label>
                        <div class="password-wrap">
                            <input type="password" id="reg_confirm_password" name="confirm_password" placeholder="Repeat your password" required>
                            <button type="button" class="btn-toggle-pw" onclick="togglePassword('reg_confirm_password', this)">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="btn-group">
                        <button type="button" class="btn-back" onclick="goToStep(1)">← Back</button>
                        <button type="submit" class="btn-submit">Register Account</button>
                    </div>
                </div>
            </form>

            <div class="footer-link">
                Already have an account? <a href="login.php">Sign In</a>
            </div>
        </div>

        <div class="auth-footer-copy">
            &copy; 2026 MindHub. All rights reserved.
        </div>

    </div>

</div>

<script>
function goToStep(step) {
    const errorBox = document.getElementById('step-error-box');
    errorBox.style.display = 'none';

    if (step === 2) {
        const idNumber = document.getElementById('id_number').value.trim();
        const firstName = document.getElementById('first_name').value.trim();
        const lastName = document.getElementById('last_name').value.trim();
        const course = document.getElementById('department_or_program').value.trim();

        if (!idNumber || !firstName || !lastName || !course) {
            errorBox.textContent = 'Please complete all required fields before proceeding.';
            errorBox.style.display = 'block';
            return;
        }

        document.getElementById('group-1').classList.remove('active');
        document.getElementById('group-2').classList.add('active');
    } else if (step === 1) {
        document.getElementById('group-2').classList.remove('active');
        document.getElementById('group-1').classList.add('active');
    }
}

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