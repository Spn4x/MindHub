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
        a.end_time,
        a.nature_of_concern,
        a.consultation_mode,
        a.student_notes,
        a.status,
        u.first_name,
        u.last_name,
        u.id_number,
        u.email,
        u.contact_number,
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
    <title>Clinical Notes #<?= $appointment['appointment_id'] ?> - MindHub</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .patient-strip {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 12px 16px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px 16px;
            margin-bottom: 14px;
        }
        .meta-col .meta-label {
            font-size: 11px;
            text-transform: uppercase;
            font-weight: 700;
            color: #64748b;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        .meta-col .meta-val {
            font-size: 13px;
            font-weight: 600;
            color: #0f172a;
        }
        .student-pre-note {
            background: #fffbeb;
            border-left: 3px solid #f59e0b;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 12px;
            color: #92400e;
            margin-bottom: 14px;
        }
        .form-row {
            margin-bottom: 12px;
        }
        .form-row label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: #334155;
            margin-bottom: 4px;
        }
        .form-row textarea {
            width: 100%;
            padding: 8px 10px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            font-size: 13px;
            line-height: 1.5;
            outline: none;
            box-sizing: border-box;
        }
        .form-row textarea:focus {
            border-color: #2563eb;
        }
        .form-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #f1f5f9;
            padding-top: 14px;
            margin-top: 14px;
            flex-wrap: wrap;
            gap: 10px;
        }
    </style>
</head>
<body class="dashboard-body">

    <nav class="navbar">
        <div class="brand">MindHub</div>
        <div class="user-info">
            <span>Counselor: <strong><?= htmlspecialchars($_SESSION['name'] ?? 'Staff') ?></strong></span>
            <a href="dashboard.php" class="btn-logout" style="background:#2563eb;">Console</a>
            <a href="../logout.php" class="btn-logout">Logout</a>
        </div>
    </nav>

    <div class="dashboard-container" style="max-width: 860px;">
        
        <div class="welcome-header" style="margin-bottom: 15px;">
            <div>
                <h1 style="font-size: 20px;">Consultation Notes & Case Summary</h1>
                <p>Record confidential student findings, therapeutic actions, and follow-up requirements.</p>
            </div>
            <a href="dashboard.php" class="btn-logout" style="background:#64748b;">← Back to Roster</a>
        </div>

        <div class="table-container" style="padding: 18px 20px;">
            
            <div class="patient-strip">
                <div class="meta-col">
                    <div class="meta-label">Student Intake</div>
                    <div class="meta-val"><?= htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']) ?></div>
                    <small style="color:#64748b; font-size:11px;"><?= htmlspecialchars($appointment['id_number']) ?> &bull; <?= htmlspecialchars($appointment['department_or_program'] ?? 'General') ?></small>
                </div>
                <div class="meta-col">
                    <div class="meta-label">Schedule</div>
                    <div class="meta-val"><?= date('M d, Y', strtotime($appointment['appointment_date'])) ?></div>
                    <small style="color:#64748b; font-size:11px;"><?= date('h:i A', strtotime($appointment['start_time'])) ?> - <?= date('h:i A', strtotime($appointment['end_time'])) ?></small>
                </div>
                <div class="meta-col">
                    <div class="meta-label">Concern / Mode</div>
                    <div class="meta-val"><?= htmlspecialchars($appointment['nature_of_concern']) ?></div>
                    <small style="color:#64748b; font-size:11px;"><?= htmlspecialchars($appointment['consultation_mode']) ?></small>
                </div>
                <div class="meta-col">
                    <div class="meta-label">Case Status</div>
                    <div style="margin-top: 2px;">
                        <span class="badge badge-<?= htmlspecialchars($appointment['status']) ?>"><?= htmlspecialchars($appointment['status']) ?></span>
                    </div>
                </div>
            </div>

            <?php if (!empty($appointment['student_notes'])): ?>
                <div class="student-pre-note">
                    <strong>Student Intake Note:</strong> "<?= htmlspecialchars($appointment['student_notes']) ?>"
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert-error" style="margin-bottom: 12px;"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert-success" style="margin-bottom: 12px;"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-row">
                    <label>Clinical Case Summary & Discussion Details:*</label>
                    <textarea name="case_summary" rows="4" placeholder="Briefly document student observations, focal concerns, and counseling dialogue..." required><?= htmlspecialchars($case_summary) ?></textarea>
                </div>

                <div class="form-row">
                    <label>Action Taken & Coping Recommendations:</label>
                    <textarea name="action_taken" rows="3" placeholder="Action plan agreed upon, strategies introduced, or institutional referrals..."><?= htmlspecialchars($action_taken) ?></textarea>
                </div>

                <div class="form-footer">
                    <label style="display:flex; align-items:center; gap:8px; margin:0; cursor:pointer; font-size:13px; color:#334155; font-weight:600;">
                        <input type="checkbox" name="follow_up_needed" value="1" <?= $follow_up_needed ? 'checked' : '' ?> style="width:auto; margin:0;">
                        <span>Flag case for follow-up consultation</span>
                    </label>

                    <button type="submit" class="btn-submit" style="width:auto; padding: 10px 20px; margin:0;">
                        Save Notes & Complete Session
                    </button>
                </div>
            </form>

        </div>
    </div>

</body>
</html>