<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . '/../config/db.php';

$student_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['appointment_id']) && $_POST['action'] === 'cancel') {
    $apt_id = (int)$_POST['appointment_id'];
    $cancelStmt = $pdo->prepare("
        update appointments 
        set status = 'cancelled' 
        where appointment_id = ? and student_id = ? and status in ('pending', 'approved')
    ");
    $cancelStmt->execute([$apt_id, $student_id]);
    header("Location: dashboard.php");
    exit();
}

$sql = "select 
            a.appointment_id,
            a.appointment_date,
            a.start_time,
            a.end_time,
            a.nature_of_concern,
            a.consultation_mode,
            a.status,
            a.student_notes,
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

$today = date('Y-m-d');
$upcoming_approved = array_filter($appointments, function($a) use ($today) {
    return $a['status'] === 'approved' && $a['appointment_date'] >= $today;
});
usort($upcoming_approved, function($a, $b) {
    return strcmp($a['appointment_date'] . ' ' . $a['start_time'], $b['appointment_date'] . ' ' . $b['start_time']);
});
$next_session = !empty($upcoming_approved) ? reset($upcoming_approved) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - MindHub</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .spotlight-block {
            padding: 0 0 20px 0;
            margin-bottom: 25px;
        }
        .spotlight-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: var(--matcha-dark);
            margin-bottom: 4px;
        }
        .spotlight-details {
            font-size: 18px;
            font-weight: 700;
            color: var(--palette-navy);
            margin-bottom: 4px;
        }
        .spotlight-meta {
            font-size: 13px;
            color: #64748b;
        }
        .btn-cancel {
            background-color: transparent;
            color: #8c3b3b;
            border: 1px solid #e7d5d5;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-cancel:hover {
            background-color: #fee2e2;
            border-color: #fca5a5;
            color: #b91c1c;
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

        <?php if ($next_session): ?>
            <?php 
                $reminderConcernClass = 'concern-' . str_replace(' ', '', $next_session['nature_of_concern']); 
                $spotlight_link = $next_session['meeting_link'];
                $clean_spotlight_url = (strpos($spotlight_link, 'http://') === 0 || strpos($spotlight_link, 'https://') === 0) ? $spotlight_link : 'https://' . $spotlight_link;
            ?>
            <div class="spotlight-block">
                <div class="spotlight-title">Upcoming Consultation Reminder</div>
                <div class="spotlight-details">
                    <?= date('l, F j, Y', strtotime($next_session['appointment_date'])) ?> at <?= date('h:i A', strtotime($next_session['start_time'])) ?>
                </div>
                <div class="spotlight-meta">
                    Counselor: <strong><?= htmlspecialchars($next_session['counselor_fname'] . ' ' . $next_session['counselor_lname']) ?></strong> &bull; 
                    Concern: <span class="concern-text <?= $reminderConcernClass ?>"><?= htmlspecialchars($next_session['nature_of_concern']) ?></span> &bull; 
                    Mode: <?= htmlspecialchars($next_session['consultation_mode']) ?>
                    <?php if ($next_session['consultation_mode'] === 'Online' && !empty($next_session['meeting_link'])): ?>
                        &bull; <a href="<?= htmlspecialchars($clean_spotlight_url) ?>" target="_blank" style="color: var(--palette-blue); font-weight: 600; text-decoration: underline;">Open Meeting Room</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

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
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $row): ?>
                            <tr>
                                <td><?= date('M d, Y', strtotime($row['appointment_date'])) ?></td>
                                <td><?= date('h:i A', strtotime($row['start_time'])) ?></td>
                                <td>
                                    <?php $concernClass = 'concern-' . str_replace(' ', '', $row['nature_of_concern']); ?>
                                    <span class="concern-text <?= $concernClass ?>">
                                        <?= htmlspecialchars($row['nature_of_concern']) ?>
                                    </span>
                                    <?php if (!empty($row['student_notes'])): ?>
                                        <div style="font-size: 12px; color: #64748b; font-style: italic; margin-top: 4px;">
                                            <?= htmlspecialchars($row['student_notes']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($row['consultation_mode']) ?>
                                    <?php if ($row['consultation_mode'] === 'Online' && !empty($row['meeting_link']) && $row['status'] === 'approved'): ?>
                                        <?php 
                                            $link = $row['meeting_link'];
                                            $clean_url = (strpos($link, 'http://') === 0 || strpos($link, 'https://') === 0) ? $link : 'https://' . $link;
                                        ?>
                                        <div style="margin-top: 3px;">
                                            <a href="<?= htmlspecialchars($clean_url) ?>" target="_blank" style="font-size: 11px; color: var(--palette-blue); font-weight: 600; text-decoration: underline;">Open Meeting Room</a>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['counselor_fname']): ?>
                                        <?= htmlspecialchars($row['counselor_fname'] . ' ' . $row['counselor_lname']) ?>
                                    <?php else: ?>
                                        <span style="color: #94a3b8; font-style: italic;">Pending Assignment</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-indicator status-<?= htmlspecialchars($row['status']) ?>">
                                        <span class="status-dot"></span>
                                        <?= htmlspecialchars($row['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (in_array($row['status'], ['pending', 'approved'])): ?>
                                        <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this appointment request?');" style="display:inline;">
                                            <input type="hidden" name="appointment_id" value="<?= $row['appointment_id'] ?>">
                                            <button type="submit" name="action" value="cancel" class="btn-cancel">Cancel</button>
                                        </form>
                                    <?php else: ?>
                                        <span style="color: #94a3b8; font-size: 12px;">--</span>
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