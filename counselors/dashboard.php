<?php
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['counselor', 'admin'])) {
    header("Location: ../index.php");
    exit();
}

require_once __DIR__ . '/../config/db.php';
$counselor_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['appointment_id'])) {
    $action = $_POST['action'];
    $appointment_id = (int)$_POST['appointment_id'];

    if (in_array($action, ['approved', 'rejected'])) {
        $stmt = $pdo->prepare("
            update appointments 
            set status = ?, counselor_id = ? 
            where appointment_id = ?
        ");
        $stmt->execute([$action, $counselor_id, $appointment_id]);
    }
    header("Location: dashboard.php");
    exit();
}

$stmt = $pdo->query("
    select 
        a.appointment_id,
        a.appointment_date,
        a.start_time,
        a.end_time,
        a.nature_of_concern,
        a.consultation_mode,
        a.status,
        a.student_notes,
        u.first_name,
        u.last_name,
        u.id_number,
        u.email,
        u.department_or_program
    from appointments a
    join users u on a.student_id = u.user_id
    order by a.appointment_date asc, a.start_time asc
");
$appointments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Counselor Dashboard - MindHub</title>
    <link rel="stylesheet" href="../assets/css/style.css">
  
</head>
<body class="dashboard-body">

    <nav class="navbar">
        <div class="brand">MindHub - Guidance Counselor Portal</div>
        <div class="user-info">
            <span>Welcome, <strong><?= htmlspecialchars($_SESSION['name'] ?? 'Counselor') ?></strong></span>
            <a href="../logout.php" class="btn-logout">Logout</a>
        </div>
    </nav>

    <div class="dashboard-container" style="max-width: 1200px;">
        <div class="welcome-header">
            <div>
                <h1>Consultation & Appointment Requests</h1>
                <p>Review and manage counseling requests from students.</p>
            </div>
        </div>

        <div class="table-container">
            <h2>All Appointments</h2>

            <?php if (empty($appointments)): ?>
                <div class="empty-state">
                    <p>No appointment requests yet.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Program</th>
                            <th>Date & Time</th>
                            <th>Concern</th>
                            <th>Mode</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $row): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></strong><br>
                                    <small style="color:#64748b;"><?= htmlspecialchars($row['id_number']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($row['department_or_program'] ?? 'N/A') ?></td>
                                <td>
                                    <?= date('M d, Y', strtotime($row['appointment_date'])) ?><br>
                                    <small style="color:#64748b;"><?= date('h:i A', strtotime($row['start_time'])) ?></small>
                                </td>
                                <td>
    <strong><?= htmlspecialchars($row['nature_of_concern']) ?></strong>
    <?php if (!empty($row['student_notes'])): ?>
        <br><small style="color:#64748b; font-style: italic;">"<?= htmlspecialchars($row['student_notes']) ?>"</small>
    <?php endif; ?>
</td>
                                <td><?= htmlspecialchars($row['consultation_mode']) ?></td>
                                <td>
                                    <span class="badge badge-<?= htmlspecialchars($row['status']) ?>">
                                        <?= htmlspecialchars($row['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($row['status'] === 'pending'): ?>
                                        <form method="POST" style="display:inline-flex; gap:6px;">
                                            <input type="hidden" name="appointment_id" value="<?= $row['appointment_id'] ?>">
                                            <button type="submit" name="action" value="approved" class="action-btn btn-approve">Approve</button>
                                            <button type="submit" name="action" value="rejected" class="action-btn btn-reject">Reject</button>
                                        </form>
                                    <?php elseif ($row['status'] === 'approved'): ?>
                                        <a href="session-notes.php?appointment_id=<?= $row['appointment_id'] ?>" style="font-size:13px; color:#2563eb; text-decoration:none; font-weight:600;">
                                            Add Notes →
                                        </a>
                                    <?php else: ?>
                                        <span style="color:#94a3b8; font-size:13px;">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>