<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . '/../config/db.php';

$student_id = $_SESSION['user_id'];

$sql = "select 
            a.appointment_id,
            a.appointment_date,
            a.start_time,
            a.end_time,
            a.nature_of_concern,
            a.consultation_mode,
            a.status,
            a.meeting_link,
            a.created_at,
            u.first_name as counselor_fname,
            u.last_name as counselor_lname
        from appointments a
        left join users u on a.counselor_id = u.user_id
        where a.student_id = ?
        order by a.appointment_date desc, a.start_time desc";

$stmt = $pdo->prepare($sql);
$stmt->execute([$student_id]);
$appointments = $stmt->fetchAll();

$total_appointments = count($appointments);
$pending_count   = 0;
$approved_count  = 0;
$completed_count = 0;

foreach ($appointments as $apt) {
    if ($apt['status'] === 'pending')   $pending_count++;
    if ($apt['status'] === 'approved')  $approved_count++;
    if ($apt['status'] === 'completed') $completed_count++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - MindHub</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-body">

    <nav class="navbar">
        <a href="dashboard.php" class="brand">
            <img src="../assets/images/logo.png" alt="MindHub Logo">
            <span>MindHub</span>
        </a>
        <div class="user-info">
            <span>Welcome, <strong><?= htmlspecialchars($_SESSION['name']) ?></strong> (<?= htmlspecialchars($_SESSION['id_number']) ?>)</span>
            <a href="../logout.php" class="btn-logout">Logout</a>
        </div>
    </nav>

    <div class="dashboard-container">
        
        <div class="welcome-header">
            <div>
                <h1>Student Counseling Portal</h1>
                <p>Track your guidance appointments and consultations privately.</p>
            </div>
        
            <div style="display: flex; gap: 10px; align-items: center;">
                <a href="resources.php" class="btn-look">Self-Help Hub</a>
                <a href="book.php" class="btn-book">+ Book New Appointment</a>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Bookings</h3>
                <div class="number"><?= $total_appointments ?></div>
            </div>
            <div class="stat-card pending">
                <h3>Pending Review</h3>
                <div class="number"><?= $pending_count ?></div>
            </div>
            <div class="stat-card approved">
                <h3>Upcoming / Approved</h3>
                <div class="number"><?= $approved_count ?></div>
            </div>
            <div class="stat-card completed">
                <h3>Completed Sessions</h3>
                <div class="number"><?= $completed_count ?></div>
            </div>
        </div>

        <div class="table-container">
            <h2>Your Appointment History</h2>

            <?php if (empty($appointments)): ?>
                <div class="empty-state">
                    <p>You have not booked any appointments yet.</p>
                    <p style="margin-top: 10px;">Need academic or personal counseling? Click <strong>"+ Book New Appointment"</strong> above.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Concern</th>
                            <th>Mode</th>
                            <th>Counselor</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $row): ?>
                            <tr>
                                <td><?= date('M d, Y', strtotime($row['appointment_date'])) ?></td>
                                <td><?= date('h:i A', strtotime($row['start_time'])) ?></td>
                                <td><?= htmlspecialchars($row['nature_of_concern']) ?></td>
                                <td>
                                    <?= htmlspecialchars($row['consultation_mode']) ?>
                                    <?php if ($row['consultation_mode'] === 'Online' && !empty($row['meeting_link']) && $row['status'] === 'approved'): ?>
                                        <br><a href="<?= htmlspecialchars($row['meeting_link']) ?>" target="_blank" style="font-size: 11px; color: #2563eb; font-weight: 600; text-decoration: underline;">🔗 Join Meeting</a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['counselor_fname']): ?>
                                        <?= htmlspecialchars($row['counselor_fname'] . ' ' . $row['counselor_lname']) ?>
                                    <?php else: ?>
                                        <em style="color: #94a3b8;">Pending Assignment</em>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?= htmlspecialchars($row['status']) ?>">
                                        <?= htmlspecialchars($row['status']) ?>
                                    </span>
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