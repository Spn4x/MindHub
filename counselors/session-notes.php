<?php
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['counselor', 'admin'])) {
    header("Location: ../index.php");
    exit();
}

require_once __DIR__ . '/../config/db.php';
$counselor_id = $_SESSION['user_id'];

$appointment_id = isset($_GET['appointment_id']) ? (int)$_GET['appointment_id'] : 0;

$stmt = $pdo->prepare("
    select 
        a.appointment_id,
        a.appointment_date,
        a.start_time,
        a.nature_of_concern,
        a.consultation_mode,
        a.student_notes,
        a.status,
        u.first_name,
        u.last_name,
        u.id_number,
        u.department_or_program
    from appointments a
    join users u on a.student_id = u.user_id
    where a.appointment_id = ?
");
$stmt->execute([$appointment_id]);
$appointment = $stmt->fetch();

if (!$appointment) {
    header("Location: dashboard.php");
    exit();
}

$noteStmt = $pdo->prepare("
    select case_summary, action_taken, follow_up_needed 
    from counseling_notes 
    where appointment_id = ?
");
$noteStmt->execute([$appointment_id]);
$existingNote = $noteStmt->fetch();

$case_summary     = $existingNote['case_summary'] ?? '';
$action_taken     = $existingNote['action_taken'] ?? '';
$follow_up_needed = $existingNote['follow_up_needed'] ?? 0;

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $case_summary     = trim($_POST['case_summary'] ?? '');
    $action_taken     = trim($_POST['action_taken'] ?? '');
    $follow_up_needed = isset($_POST['follow_up_needed']) ? 1 : 0;

    if (empty($case_summary)) {
        $error = "Please enter a case summary.";
    } else {
        if ($existingNote) {
            $saveStmt = $pdo->prepare("
                update counseling_notes 
                set case_summary = ?, action_taken = ?, follow_up_needed = ?, counselor_id = ?
                where appointment_id = ?
            ");
            $saveStmt->execute([$case_summary, $action_taken, $follow_up_needed, $counselor_id, $appointment_id]);
        } else {
            $saveStmt = $pdo->prepare("
                insert into counseling_notes (appointment_id, counselor_id, case_summary, action_taken, follow_up_needed)
                values (?, ?, ?, ?, ?)
            ");
            $saveStmt->execute([$appointment_id, $counselor_id, $case_summary, $action_taken, $follow_up_needed]);
        }

        $updateApt = $pdo->prepare("
            update appointments 
            set status = 'completed', counselor_id = ? 
            where appointment_id = ?
        ");
        $updateApt->execute([$counselor_id, $appointment_id]);

        $success = "Session notes saved! Appointment marked as Completed.";
        $appointment['status'] = 'completed';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Notes - MindHub</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-body">

    <nav class="navbar">
        <div class="brand">MindHub - Consultation Notes</div>
        <div class="user-info">
            <span>Logged in as: <strong><?= htmlspecialchars($_SESSION['name'] ?? 'Counselor') ?></strong></span>
            <a href="../logout.php" class="btn-logout">Logout</a>
        </div>
    </nav>

    <div class="dashboard-container" style="max-width: 800px;">
        <div class="welcome-header">
            <div>
                <h1>Consultation Record</h1>
                <p>Document confidential case notes and actions taken for this session.</p>
            </div>
            <a href="dashboard.php" class="btn-logout" style="background:#64748b;">← Back to Dashboard</a>
        </div>

        <div class="table-container" style="margin-bottom: 25px;">
            <h2>Student & Consultation Details</h2>
            <p><strong>Student Name:</strong> <?= htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']) ?> (<?= htmlspecialchars($appointment['id_number']) ?>)</p>
            <p style="margin-top: 6px;"><strong>Program / Department:</strong> <?= htmlspecialchars($appointment['department_or_program'] ?? 'N/A') ?></p>
            <p style="margin-top: 6px;"><strong>Date & Time:</strong> <?= date('M d, Y', strtotime($appointment['appointment_date'])) ?> at <?= date('h:i A', strtotime($appointment['start_time'])) ?></p>
            <p style="margin-top: 6px;"><strong>Concern Category:</strong> <?= htmlspecialchars($appointment['nature_of_concern']) ?> (<?= htmlspecialchars($appointment['consultation_mode']) ?>)</p>
            <?php if (!empty($appointment['student_notes'])): ?>
                <p style="margin-top: 6px;"><strong>Student's Pre-Session Notes:</strong> <em>"<?= htmlspecialchars($appointment['student_notes']) ?>"</em></p>
            <?php endif; ?>
        </div>

        <div class="table-container">
            <h2>Confidential Case Notes</h2>

            <?php if (!empty($error)): ?>
                <div class="alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Case Summary / Discussion Details:*</label>
                    <textarea name="case_summary" rows="5" style="width:100%; padding:10px; border-radius:6px; border:1px solid #cbd5e1;" placeholder="Summarize the key concerns, student observations, and discussion points..." required><?= htmlspecialchars($case_summary) ?></textarea>
                </div>

                <div class="form-group">
                    <label>Action Taken / Recommendations:</label>
                    <textarea name="action_taken" rows="4" style="width:100%; padding:10px; border-radius:6px; border:1px solid #cbd5e1;" placeholder="Steps advised, coping strategies provided, or referrals made..."><?= htmlspecialchars($action_taken) ?></textarea>
                </div>

                <div class="form-group" style="display:flex; align-items:center; gap:10px; margin-top:15px;">
                    <input type="checkbox" name="follow_up_needed" id="follow_up" value="1" <?= $follow_up_needed ? 'checked' : '' ?> style="width:auto;">
                    <label for="follow_up" style="margin:0; cursor:pointer;">Follow-up consultation needed for this student</label>
                </div>

                <button type="submit" class="btn-submit" style="margin-top: 20px;">Save Notes & Complete Session</button>
            </form>
        </div>
    </div>

</body>
</html>