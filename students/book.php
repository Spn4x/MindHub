<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . '/../config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointment_date  = trim($_POST['appointment_date'] ?? '');
    $start_time        = trim($_POST['start_time'] ?? '');
    $nature_of_concern = trim($_POST['nature_of_concern'] ?? '');
    $consultation_mode = trim($_POST['consultation_mode'] ?? 'Face-to-Face');
    $student_notes     = trim($_POST['student_notes'] ?? '');

    if (empty($appointment_date) || empty($start_time) || empty($nature_of_concern)) {
        $error = "Please fill in all required fields.";
    } elseif (strtotime($appointment_date) < strtotime(date('Y-m-d'))) {
        $error = "You cannot book an appointment for a past date.";
    } else {
        $checkStmt = $pdo->prepare("
            select appointment_id from appointments 
            where appointment_date = ? 
              and start_time = ? 
              and status in ('pending', 'approved')
        ");
        $checkStmt->execute([$appointment_date, $start_time]);

        if ($checkStmt->fetch()) {
            $error = "Sorry, that time slot is already taken on that date. Please choose another time.";
        } else {
            $end_time = date('H:i:s', strtotime($start_time . ' +1 hour'));

            $stmt = $pdo->prepare("
                insert into appointments (
                    student_id, appointment_date, start_time, end_time,
                    nature_of_concern, consultation_mode, student_notes, status
                ) values (?, ?, ?, ?, ?, ?, ?, 'pending')
            ");

            if ($stmt->execute([
                $_SESSION['user_id'],
                $appointment_date,
                $start_time,
                $end_time,
                $nature_of_concern,
                $consultation_mode,
                $student_notes
            ])) {
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Failed to submit request. Please try again.";
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
    <title>Book Appointment - MindHub</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-body">

    <nav class="navbar">
        <a href="dashboard.php" class="brand">
            <img src="../assets/images/logo.png" alt="MindHub Logo">
            <span>Mind<span class="hub-accent">Hub</span></span>
        </a>
        <div class="user-info">
            <span>Welcome, <strong><?= htmlspecialchars($_SESSION['name'] ?? '') ?></strong></span>
            <a href="../logout.php" class="btn-logout">Logout</a>
        </div>
    </nav>

    <div class="dashboard-container" style="max-width: 600px;">
        <div class="welcome-header">
            <div>
                <h1>Book Counseling</h1>
                <p>Request a private consultation with the Guidance Office.</p>
            </div>
            <a href="dashboard.php" class="btn-logout" style="background:#122E24; color:#fff; border:none;">← Back</a>
        </div>

        <div class="table-container">
            <?php if (!empty($error)): ?>
                <div class="alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="book.php">
                <div class="form-group">
                    <label>Preferred Date:*</label>
                    <input type="date" name="appointment_date" min="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="form-group">
                    <label>Time Slot:*</label>
                    <select name="start_time" required>
                        <option value="">-- Select Time Slot --</option>
                        <option value="08:00:00">08:00 AM - 09:00 AM</option>
                        <option value="09:00:00">09:00 AM - 10:00 AM</option>
                        <option value="10:00:00">10:00 AM - 11:00 AM</option>
                        <option value="13:00:00">01:00 PM - 02:00 PM</option>
                        <option value="14:00:00">02:00 PM - 03:00 PM</option>
                        <option value="15:00:00">03:00 PM - 04:00 PM</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Nature of Concern:*</label>
                    <select name="nature_of_concern" required>
                        <option value="">-- Select Category --</option>
                        <option value="Academic">Academic</option>
                        <option value="Personal">Personal</option>
                        <option value="Career">Career</option>
                        <option value="Mental Health">Mental Health</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Consultation Mode:*</label>
                    <select name="consultation_mode" required>
                        <option value="Face-to-Face">Face-to-Face</option>
                        <option value="Online">Online</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Notes / Reason (Optional):</label>
                    <textarea name="student_notes" rows="3" placeholder="Anything you want the counselor to know in advance..."></textarea>
                </div>

                <button type="submit" class="btn-submit">Submit Request</button>
            </form>
        </div>
    </div>

</body>
</html>