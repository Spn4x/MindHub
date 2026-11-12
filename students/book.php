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
    <title>Book Counseling - MindHub</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .book-layout {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 24px;
            align-items: start;
        }
        @media (max-width: 900px) {
            .book-layout {
                grid-template-columns: 1fr;
            }
        }
        .form-grid-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        @media (max-width: 600px) {
            .form-grid-row {
                grid-template-columns: 1fr;
            }
        }
        .info-panel {
            padding: 8px 0;
        }
        .info-block {
            padding: 14px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .info-block:last-child {
            border-bottom: none;
        }
        .info-title {
            font-size: 13px;
            font-weight: 700;
            color: var(--palette-navy);
            margin-bottom: 4px;
        }
        .info-text {
            font-size: 13px;
            color: #64748b;
            line-height: 1.5;
            margin: 0;
        }
        .field-helper {
            font-size: 11px;
            color: #94a3b8;
            margin-top: 4px;
        }
    </style>
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

    <div class="dashboard-container" style="max-width: 1100px;">
        
        <div class="welcome-header">
            <div>
                <h1>Schedule a Consultation</h1>
                <p>Request a private one-on-one session with the Guidance Office.</p>
            </div>
            <a href="dashboard.php" class="btn-logout">← Back to Dashboard</a>
        </div>

        <div class="book-layout">
            
            <div class="table-container">
                <h2>Appointment Form</h2>

                <?php if (!empty($error)): ?>
                    <div class="alert-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="book.php">
                    <div class="form-grid-row">
                        <div class="form-group">
                            <label>Preferred Date:*</label>
                            <input type="date" name="appointment_date" min="<?= date('Y-m-d') ?>" required>
                            <div class="field-helper">Consultations available weekdays</div>
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
                            <div class="field-helper">Each session is 1 hour</div>
                        </div>
                    </div>

                    <div class="form-grid-row">
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
                            <div class="field-helper">Helps assign the right counselor</div>
                        </div>

                        <div class="form-group">
                            <label>Consultation Mode:*</label>
                            <select name="consultation_mode" required>
                                <option value="Face-to-Face">Face-to-Face</option>
                                <option value="Online">Online</option>
                            </select>
                            <div class="field-helper">On-campus or Google Meet</div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Notes or Details (Optional):</label>
                        <textarea name="student_notes" rows="4" placeholder="Briefly share what you would like to discuss with the counselor..."></textarea>
                        <div class="field-helper">Only your assigned counselor will read this</div>
                    </div>

                    <button type="submit" class="btn-submit" style="background-color: var(--matcha-dark);">Submit Appointment Request</button>
                </form>
            </div>

            <div class="table-container">
                <h2>Consultation Guidelines</h2>
                
                <div class="info-panel">
                    <div class="info-block">
                        <div class="info-title">Confidentiality Guarantee</div>
                        <p class="info-text">Everything shared during your consultation is strictly private between you and your counselor under guidance ethics standards.</p>
                    </div>

                    <div class="info-block">
                        <div class="info-title">Consultation Modes</div>
                        <p class="info-text"><strong>Face-to-Face:</strong> Held at the Guidance & Counseling Office.<br><strong>Online:</strong> A meeting link will appear directly on your student dashboard once approved.</p>
                    </div>

                    <div class="info-block">
                        <div class="info-title">Rescheduling & Cancellations</div>
                        <p class="info-text">If your class schedule changes, you can cancel any pending or upcoming appointment directly from your dashboard table.</p>
                    </div>
                </div>
            </div>

        </div>

    </div>

</body>
</html>