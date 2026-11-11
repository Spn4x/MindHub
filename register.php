<?php
session_start();
require_once 'config/db.php';

$error = '';
$success = '';

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
        $checkStmt = $pdo->prepare("SELECT user_id FROM users WHERE id_number = ? OR email = ?");
        $checkStmt->execute([$id_number, $email]);

        if ($checkStmt->fetch()) {
            $error = "That Student ID Number or Email is already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (
                        id_number, first_name, last_name, email, password_hash, role, department_or_program, contact_number
                    ) VALUES (?, ?, ?, ?, ?, 'student', ?, ?)";

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
                $success = "Registration successful! You may now proceed to log in.";
                $id_number = $first_name = $last_name = $email = $program = $contact = '';
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

 <style>
        html, body {
            margin: 0;
            height: 100%;
            overflow: hidden;
        }
    </style>


</head>
<body>


<img class="poster-img" src="assets/images/post.jpg" alt="poster">

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
            <?= htmlspecialchars($success) ?><br>
            <a href="index.php" style="color: #166534; font-weight: bold; text-decoration: underline;">Click here to log in now</a>
        </div>
    <?php endif; ?>

    

    <form method="POST" action="register.php" id="registrationForm">
        <!-- GROUP 1: Personal Details -->
        <div class="step-section active" id="group-1">

            <div class="form-group">
                <label>Student ID Number:*</label>
                <input type="text" id="id_number" name="id_number" placeholder="Enter your Student ID" 
                       value="<?= htmlspecialchars($id_number ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label>First Name:*</label>
                <input type="text" id="first_name" name="first_name" placeholder="Enter Your Name" 
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
            <button type="button" class="btn-submit" onclick="goToStep(2)">Next</button>
        </div>

        <!-- GROUP 2: Account Security -->
        <div class="step-section" id="group-2">
        

            <div class="form-group">
                <label>CCC Email Address:*</label>
                <input type="email" id="email" name="email" placeholder="student@ccc.edu.ph" 
                       value="<?= htmlspecialchars($email ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label>Password:*</label>
                <input type="password" name="password" placeholder="At least 6 characters" required>
            </div>

            <div class="form-group">
                <label>Confirm Password:*</label>
                <input type="password" name="confirm_password" placeholder="Repeat your password" required>
            </div>

            <div class="btn-group">
                <button type="button" class="btn-back" onclick="goToStep(1)">Back</button>
                <button type="submit" class="btn-submit">Register Account</button>
            </div>
        </div>
    </form>

    <div class="footer-link">
        Already have an account? <a href="index.php">Log in here</a>
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
</script>

</body>
</html>