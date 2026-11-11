<?php
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['counselor', 'admin'])) {
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . '/../config/db.php';
$counselor_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['appointment_id'])) {
    $action = $_POST['action'];
    $appointment_id = (int)$_POST['appointment_id'];
    $meeting_link = trim($_POST['meeting_link'] ?? '');

    if (in_array($action, ['approved', 'rejected'])) {
        $stmt = $pdo->prepare("
            update appointments 
            set status = ?, counselor_id = ?, meeting_link = ? 
            where appointment_id = ?
        ");
        $stmt->execute([$action, $counselor_id, $meeting_link ?: null, $appointment_id]);
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
        a.meeting_link,
        u.user_id as student_user_id,
        u.first_name,
        u.last_name,
        u.id_number,
        u.email,
        u.contact_number,
        u.department_or_program,
        (select count(*) from appointments a2 where a2.student_id = a.student_id and a2.status = 'completed') as visit_count
    from appointments a
    join users u on a.student_id = u.user_id
    order by 
        case when a.status = 'pending' then 1 else 2 end,
        a.appointment_date asc, 
        a.start_time asc
");
$appointments = $stmt->fetchAll();

$watchlistStmt = $pdo->prepare("
    select 
        cn.note_id,
        cn.case_summary,
        cn.updated_at,
        a.appointment_id,
        u.first_name,
        u.last_name,
        u.id_number,
        u.contact_number
    from counseling_notes cn
    join appointments a on cn.appointment_id = a.appointment_id
    join users u on a.student_id = u.user_id
    where cn.follow_up_needed = 1 and cn.counselor_id = ?
    order by cn.updated_at desc
");
$watchlistStmt->execute([$counselor_id]);
$followupWatchlist = $watchlistStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Counselor Portal - MindHub</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .split-layout {
            display: grid;
            grid-template-columns: 1.65fr 1fr;
            gap: 24px;
            align-items: start;
        }
        @media (max-width: 950px) {
            .split-layout {
                grid-template-columns: 1fr;
            }
        }
        .action-btn {
            padding: 5px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            color: #fff;
        }
        .btn-approve { background-color: var(--matcha-dark); }
        .btn-approve:hover { background-color: var(--matcha-dark-hover); }
        .btn-reject  { background-color: var(--palette-brown); }
        .btn-reject:hover { background-color: #8c796b; }
        
        .student-meta {
            font-size: 12px;
            color: #64748b;
            margin-top: 3px;
            line-height: 1.4;
        }
        .student-contact {
            font-size: 12px;
            color: #475569;
            margin-top: 4px;
        }
        .student-contact span {
            color: #94a3b8;
            margin-right: 2px;
        }

        .student-note-text {
            font-size: 12px;
            color: #64748b;
            font-style: italic;
            margin-top: 4px;
            line-height: 1.4;
        }

        .schedule-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .schedule-row:last-child {
            border-bottom: none;
        }
        .schedule-date {
            font-weight: 700;
            color: var(--palette-navy);
            font-size: 14px;
        }
        .schedule-time {
            font-size: 12px;
            color: #64748b;
            margin-top: 2px;
        }
        .schedule-student {
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin-top: 4px;
        }

        .mode-text {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--palette-blue);
        }

        .followup-card {
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .followup-card:last-child {
            border-bottom: none;
        }
        .followup-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .followup-name {
            font-weight: 700;
            font-size: 13px;
            color: var(--palette-navy);
        }
        .followup-link {
            font-size: 12px;
            color: var(--matcha-dark);
            text-decoration: none;
            font-weight: 600;
        }
        .followup-link:hover {
            text-decoration: underline;
        }
        .followup-summary {
            font-size: 12px;
            color: #64748b;
            margin-top: 4px;
            line-height: 1.4;
        }
        
        .visit-flag {
            font-size: 11px;
            font-weight: 600;
            color: var(--palette-blue);
            margin-left: 6px;
        }
    </style>
</head>
<body class="dashboard-body">

    <nav class="navbar">
        <a href="dashboard.php" class="brand">
            <img src="../assets/images/logo.png" alt="MindHub Logo">
            <span>Mind<span class="hub-accent">Hub</span></span>
            <span class="brand-badge">Counselor Portal</span>
        </a>
        <div class="user-info">
            <span>Counselor: <strong><?= htmlspecialchars($_SESSION['name'] ?? 'Staff') ?></strong></span>
            <a href="../logout.php" class="btn-logout">Logout</a>
        </div>
    </nav>

    <div class="dashboard-container">
        
        <div class="welcome-header">
            <div>
                <h1>Appointments & Consultations</h1>
                <p>Manage student appointment requests, review today's schedule, and write case notes.</p>
            </div>
            <a href="add-resource.php" class="btn-book">+ Post Guidance Article</a>
        </div>

        <div class="split-layout">
            
            <div class="table-container">
                <h2>Appointment Requests</h2>

                <?php if (empty($appointments)): ?>
                    <div class="empty-state">
                        <p>No appointment records found.</p>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Concern</th>
                                <th>Schedule</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($appointments as $row): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></strong>
                                        <?php if ($row['visit_count'] == 0): ?>
                                            <span class="visit-flag">First Visit</span>
                                        <?php else: ?>
                                            <span class="visit-flag">Returning</span>
                                        <?php endif; ?>
                                        <div class="student-meta">
                                            ID: <?= htmlspecialchars($row['id_number']) ?> &bull; <?= htmlspecialchars($row['department_or_program'] ?? 'General') ?>
                                        </div>
                                        <div class="student-contact">
                                            <span>Email:</span> <?= htmlspecialchars($row['email']) ?>
                                            <?php if (!empty($row['contact_number'])): ?>
                                                <br><span>Phone:</span> <?= htmlspecialchars($row['contact_number']) ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php $concernClass = 'concern-' . str_replace(' ', '', $row['nature_of_concern']); ?>
                                        <span class="concern-text <?= $concernClass ?>">
                                            <?= htmlspecialchars($row['nature_of_concern']) ?>
                                        </span>
                                        <div class="mode-text"><?= htmlspecialchars($row['consultation_mode']) ?></div>
                                        <?php if (!empty($row['student_notes'])): ?>
                                            <div class="student-note-text">
                                                <?= htmlspecialchars($row['student_notes']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= date('M d, Y', strtotime($row['appointment_date'])) ?></strong>
                                        <div class="student-meta"><?= date('h:i A', strtotime($row['start_time'])) ?></div>
                                    </td>
                                    <td>
                                        <span class="status-indicator status-<?= htmlspecialchars($row['status']) ?>">
                                            <span class="status-dot"></span>
                                            <?= htmlspecialchars($row['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($row['status'] === 'pending'): ?>
                                            <form method="POST">
                                                <input type="hidden" name="appointment_id" value="<?= $row['appointment_id'] ?>">
                                                <?php if ($row['consultation_mode'] === 'Online'): ?>
                                                    <input type="text" name="meeting_link" placeholder="Enter meeting link..." style="font-size:11px; padding:4px 6px; margin-bottom:6px; width:100%;">
                                                <?php endif; ?>
                                                <div style="display:flex; gap:4px;">
                                                    <button type="submit" name="action" value="approved" class="action-btn btn-approve">Accept</button>
                                                    <button type="submit" name="action" value="rejected" class="action-btn btn-reject">Decline</button>
                                                </div>
                                            </form>
                                        <?php elseif ($row['status'] === 'approved'): ?>
                                            <a href="session-notes.php?appointment_id=<?= $row['appointment_id'] ?>" style="font-size:12px; color:var(--matcha-dark); text-decoration:none; font-weight:700;">
                                                Case Notes &rarr;
                                            </a>
                                        <?php elseif ($row['status'] === 'completed'): ?>
                                            <a href="session-notes.php?appointment_id=<?= $row['appointment_id'] ?>" style="color:#64748b; font-size:12px; text-decoration:none; font-weight:600;">
                                                View Notes
                                            </a>
                                        <?php else: ?>
                                            <span style="color:#94a3b8; font-size:12px;">Declined</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <div>
                
                <div class="table-container" style="margin-bottom: 24px;">
                    <h2>Upcoming Schedule</h2>

                    <?php 
                    $upcoming = array_filter($appointments, function($a) {
                        return in_array($a['status'], ['approved', 'pending']);
                    });
                    ?>

                    <?php if (empty($upcoming)): ?>
                        <div class="empty-state">
                            <p>No upcoming consultations scheduled.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($upcoming as $item): ?>
                            <?php
                                $counselor_link = $item['meeting_link'];
                                $clean_counselor_url = (strpos($counselor_link, 'http://') === 0 || strpos($counselor_link, 'https://') === 0) ? $counselor_link : 'https://' . $counselor_link;
                            ?>
                            <div class="schedule-row">
                                <div>
                                    <div class="schedule-date"><?= date('M d, Y', strtotime($item['appointment_date'])) ?></div>
                                    <div class="schedule-time"><?= date('h:i A', strtotime($item['start_time'])) ?> - <?= date('h:i A', strtotime($item['end_time'])) ?></div>
                                    <div class="schedule-student"><?= htmlspecialchars($item['first_name'] . ' ' . $item['last_name']) ?></div>
                                    <?php if (!empty($item['meeting_link'])): ?>
                                        <div style="margin-top: 4px; font-size: 11px;">
                                            <a href="<?= htmlspecialchars($clean_counselor_url) ?>" target="_blank" style="color: var(--palette-blue); text-decoration: underline;">Open Meeting Link</a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div style="text-align: right;">
                                    <div class="mode-text"><?= htmlspecialchars($item['consultation_mode']) ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="table-container">
                    <h2>Follow-up Reminders</h2>

                    <?php if (empty($followupWatchlist)): ?>
                        <div class="empty-state">
                            <p>No students flagged for follow-up.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($followupWatchlist as $case): ?>
                            <div class="followup-card">
                                <div class="followup-header">
                                    <div class="followup-name"><?= htmlspecialchars($case['first_name'] . ' ' . $case['last_name']) ?></div>
                                    <a href="session-notes.php?appointment_id=<?= $case['appointment_id'] ?>" class="followup-link">View Notes &rarr;</a>
                                </div>
                                <div class="student-meta">
                                    ID: <?= htmlspecialchars($case['id_number']) ?>
                                    <?php if (!empty($case['contact_number'])): ?>
                                        &bull; Phone: <?= htmlspecialchars($case['contact_number']) ?>
                                    <?php endif; ?>
                                </div>
                                <div class="followup-summary">
                                    <?= htmlspecialchars(substr($case['case_summary'], 0, 90)) ?>...
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            </div>

        </div>
    </div>

</body>
</html>