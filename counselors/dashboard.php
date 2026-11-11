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
    <title>Counselor Console - MindHub</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .split-layout {
            display: grid;
            grid-template-columns: 1.6fr 1fr;
            gap: 20px;
            align-items: start;
        }
        @media (max-width: 950px) {
            .split-layout {
                grid-template-columns: 1fr;
            }
        }
        .action-btn {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            color: #fff;
        }
        .btn-approve { background-color: #16a34a; }
        .btn-reject  { background-color: #dc2626; }
        .schedule-card {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 12px;
            background: #f8fafc;
        }
        .mode-badge {
            font-size: 10px;
            font-weight: 700;
            padding: 3px 6px;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .mode-ftf { background-color: #dbeafe; color: #1e40af; }
        .mode-online { background-color: #f3e8ff; color: #6b21a8; }
        .concern-tag {
            padding: 3px 7px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
            display: inline-block;
        }
        .concern-MentalHealth { background: #fee2e2; color: #991b1b; }
        .concern-Personal     { background: #fef3c7; color: #92400e; }
        .concern-Academic     { background: #dbeafe; color: #1e40af; }
        .concern-Career       { background: #dcfce7; color: #166534; }
        .concern-Other        { background: #f1f5f9; color: #475569; }
        .visit-badge {
            font-size: 10px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 10px;
            display: inline-block;
            margin-left: 4px;
        }
        .visit-new { background: #dcfce7; color: #166534; }
        .visit-repeat { background: #fef3c7; color: #854d0e; }
        .watchlist-item {
            border-left: 3px solid #ef4444;
            background: #fff;
            padding: 10px 12px;
            border-radius: 4px;
            margin-bottom: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body class="dashboard-body">

    <nav class="navbar">
        <div class="brand">MindHub - Clinical Guidance Portal</div>
        <div class="user-info">
            <span>Counselor: <strong><?= htmlspecialchars($_SESSION['name'] ?? 'Staff') ?></strong></span>
            <a href="../logout.php" class="btn-logout">Logout</a>
        </div>
    </nav>

    <div class="dashboard-container" style="max-width: 1250px;">
        <div class="welcome-header">
            <div>
                <h1>Hello, <strong><?= htmlspecialchars($_SESSION['name'] ?? 'Staff') ?></strong></h1>
                <p>Manage student intake triage, scheduled meeting rooms, and clinical follow-ups.</p>
            </div>
            <a href="add-resource.php" class="btn-book" style="background-color: #059669;">+ Post Guidance Article</a>
        </div>

        <div class="split-layout">
            
            <div class="table-container">
                <h2>Student Intake & Inquiries</h2>

                <?php if (empty($appointments)): ?>
                    <div class="empty-state">
                        <p>No student inquiries recorded.</p>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Student & Contact</th>
                                <th>Concern & Notes</th>
                                <th>Schedule</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($appointments as $row): ?>
                                <?php $concernClass = 'concern-' . str_replace(' ', '', $row['nature_of_concern']); ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></strong>
                                        <?php if ($row['visit_count'] == 0): ?>
                                            <span class="visit-badge visit-new">1st Visit</span>
                                        <?php else: ?>
                                            <span class="visit-badge visit-repeat"><?= $row['visit_count'] + 1 ?>th Consultation</span>
                                        <?php endif; ?>
                                        <br>
                                        <small style="color:#64748b;"><?= htmlspecialchars($row['id_number']) ?> &bull; <?= htmlspecialchars($row['department_or_program'] ?? 'General') ?></small><br>
                                        <small style="color:#2563eb;">✉ <?= htmlspecialchars($row['email']) ?></small>
                                        <?php if (!empty($row['contact_number'])): ?>
                                            <br><small style="color:#475569;">📞 <?= htmlspecialchars($row['contact_number']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="concern-tag <?= $concernClass ?>">
                                            <?= htmlspecialchars($row['nature_of_concern']) ?>
                                        </span>
                                        <?php if ($row['consultation_mode'] === 'Face-to-Face'): ?>
                                            <span class="mode-badge mode-ftf">FTF</span>
                                        <?php else: ?>
                                            <span class="mode-badge mode-online">Online</span>
                                        <?php endif; ?>

                                        <?php if (!empty($row['student_notes'])): ?>
                                            <div style="margin-top: 5px; font-size: 11px; color: #475569; background: #f8fafc; padding: 4px 6px; border-radius: 4px;">
                                                <em>"<?= htmlspecialchars($row['student_notes']) ?>"</em>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= date('M d', strtotime($row['appointment_date'])) ?></strong><br>
                                        <small style="color:#64748b;"><?= date('h:i A', strtotime($row['start_time'])) ?></small><br>
                                        <span class="badge badge-<?= htmlspecialchars($row['status']) ?>"><?= htmlspecialchars($row['status']) ?></span>
                                    </td>
                                    <td>
                                        <?php if ($row['status'] === 'pending'): ?>
                                            <form method="POST">
                                                <input type="hidden" name="appointment_id" value="<?= $row['appointment_id'] ?>">
                                                <?php if ($row['consultation_mode'] === 'Online'): ?>
                                                    <input type="text" name="meeting_link" placeholder="Google Meet / Zoom URL" style="font-size:11px; padding:4px; margin-bottom:4px; width:100%;">
                                                <?php endif; ?>
                                                <div style="display:flex; gap:4px;">
                                                    <button type="submit" name="action" value="approved" class="action-btn btn-approve">Accept</button>
                                                    <button type="submit" name="action" value="rejected" class="action-btn btn-reject">Decline</button>
                                                </div>
                                            </form>
                                        <?php elseif ($row['status'] === 'approved'): ?>
                                            <a href="session-notes.php?appointment_id=<?= $row['appointment_id'] ?>" style="font-size:11px; color:#2563eb; text-decoration:none; font-weight:700;">
                                                📝 Case Notes →
                                            </a>
                                        <?php elseif ($row['status'] === 'completed'): ?>
                                            <a href="session-notes.php?appointment_id=<?= $row['appointment_id'] ?>" style="color:#16a34a; font-size:11px; text-decoration:none; font-weight:700;">
                                                ✓ Completed
                                            </a>
                                        <?php else: ?>
                                            <span style="color:#94a3b8; font-size:11px;">Declined</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <div>
                
                <div class="table-container" style="margin-bottom: 20px;">
                    <h2>Upcoming Consultations</h2>

                    <?php 
                    $upcoming = array_filter($appointments, function($a) {
                        return in_array($a['status'], ['approved', 'pending']);
                    });
                    ?>

                    <?php if (empty($upcoming)): ?>
                        <div class="empty-state">
                            <p>No upcoming sessions scheduled.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($upcoming as $item): ?>
                            <div class="schedule-card">
                                <div style="display:flex; justify-content:space-between; align-items:center;">
                                    <strong><?= date('M d, Y', strtotime($item['appointment_date'])) ?></strong>
                                    <?php if ($item['consultation_mode'] === 'Face-to-Face'): ?>
                                        <span class="mode-badge mode-ftf">FTF</span>
                                    <?php else: ?>
                                        <span class="mode-badge mode-online">Online</span>
                                    <?php endif; ?>
                                </div>
                                <div style="font-size: 12px; color: #64748b; margin-top: 2px;">
                                    <?= date('h:i A', strtotime($item['start_time'])) ?> - <?= date('h:i A', strtotime($item['end_time'])) ?>
                                </div>
                                <div style="font-size: 13px; font-weight: 600; color: #0f172a; margin-top: 4px;">
                                    <?= htmlspecialchars($item['first_name'] . ' ' . $item['last_name']) ?>
                                </div>
                                <?php if (!empty($item['meeting_link'])): ?>
                                    <div style="margin-top: 6px; font-size: 11px;">
                                        🔗 <a href="<?= htmlspecialchars($item['meeting_link']) ?>" target="_blank" style="color: #2563eb; text-decoration: underline;">Open Online Room</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="table-container">
                    <h2 style="color: #991b1b;">⚠️ Clinical Follow-Up Watchlist</h2>

                    <?php if (empty($followupWatchlist)): ?>
                        <div class="empty-state">
                            <p>No cases currently flagged for follow-up.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($followupWatchlist as $case): ?>
                            <div class="watchlist-item">
                                <div style="display:flex; justify-content:space-between; align-items:center;">
                                    <strong><?= htmlspecialchars($case['first_name'] . ' ' . $case['last_name']) ?></strong>
                                    <a href="session-notes.php?appointment_id=<?= $case['appointment_id'] ?>" style="font-size:11px; color:#2563eb; font-weight:600; text-decoration:none;">View File →</a>
                                </div>
                                <div style="font-size: 11px; color: #64748b; margin: 3px 0;">
                                    ID: <?= htmlspecialchars($case['id_number']) ?> <?= !empty($case['contact_number']) ? '• ' . htmlspecialchars($case['contact_number']) : '' ?>
                                </div>
                                <div style="font-size: 12px; color: #334155; font-style: italic;">
                                    "<?= htmlspecialchars(substr($case['case_summary'], 0, 75)) ?>..."
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