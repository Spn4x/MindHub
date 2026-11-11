<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../index.php");
    exit();
}

require_once __DIR__ . '/../config/db.php';

$category = trim($_GET['category'] ?? '');

if (!empty($category)) {
    $stmt = $pdo->prepare("
        select 
            r.resource_id, 
            r.title, 
            r.category, 
            r.content, 
            r.created_at, 
            u.first_name, 
            u.last_name
        from self_help_resources r
        join users u on r.created_by = u.user_id
        where r.category = ?
        order by r.created_at desc
    ");
    $stmt->execute([$category]);
} else {
    $stmt = $pdo->query("
        select 
            r.resource_id, 
            r.title, 
            r.category, 
            r.content, 
            r.created_at, 
            u.first_name, 
            u.last_name
        from self_help_resources r
        join users u on r.created_by = u.user_id
        order by r.created_at desc
    ");
}

$resources = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Self-Help Resources - MindHub</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .filter-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }
        .filter-btn {
            background-color: #ffffff;
            color: #334155;
            padding: 8px 16px;
            border-radius: 20px;
            border: 1px solid #cbd5e1;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }
        .filter-btn:hover, .filter-btn.active {
            background-color: #2563eb;
            color: #ffffff;
            border-color: #2563eb;
        }
        .resources-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 20px;
        }
        .resource-card {
            background: #ffffff;
            padding: 24px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .resource-card h3 {
            color: #0f172a;
            font-size: 18px;
            margin: 10px 0;
            line-height: 1.4;
        }
        .resource-card p {
            color: #475569;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        .resource-meta {
            font-size: 12px;
            color: #94a3b8;
            border-top: 1px solid #f1f5f9;
            padding-top: 12px;
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>
<body class="dashboard-body">

    <nav class="navbar">
        <div class="brand">MindHub - Self-Help Resources</div>
        <div class="user-info">
            <span>Welcome, <strong><?= htmlspecialchars($_SESSION['name'] ?? 'Student') ?></strong></span>
            <a href="dashboard.php" class="btn-logout" style="background:#2563eb;">Dashboard</a>
            <a href="../logout.php" class="btn-logout">Logout</a>
        </div>
    </nav>

    <div class="dashboard-container">
        <div class="welcome-header">
            <div>
                <h1>Wellness & Self-Help Hub</h1>
                <p>Read guidance articles, coping techniques, and mental health tips at your own pace.</p>
            </div>
            <a href="book.php" class="btn-book">+ Book Consultation</a>
        </div>

        <div class="filter-bar">
            <a href="resources.php" class="filter-btn <?= empty($category) ? 'active' : '' ?>">All Topics</a>
            <a href="resources.php?category=Academic" class="filter-btn <?= $category === 'Academic' ? 'active' : '' ?>">Academic</a>
            <a href="resources.php?category=Mental Health" class="filter-btn <?= $category === 'Mental Health' ? 'active' : '' ?>">Mental Health</a>
            <a href="resources.php?category=Personal" class="filter-btn <?= $category === 'Personal' ? 'active' : '' ?>">Personal</a>
        </div>

        <?php if (empty($resources)): ?>
            <div class="table-container empty-state">
                <p>No resources found in this category.</p>
            </div>
        <?php else: ?>
            <div class="resources-grid">
                <?php foreach ($resources as $item): ?>
                    <div class="resource-card">
                        <div>
                            <span class="badge badge-approved"><?= htmlspecialchars($item['category']) ?></span>
                            <h3><?= htmlspecialchars($item['title']) ?></h3>
                            <p><?= nl2br(htmlspecialchars($item['content'])) ?></p>
                        </div>
                        <div class="resource-meta">
                            <span>By Counselor <?= htmlspecialchars($item['first_name'] . ' ' . $item['last_name']) ?></span>
                            <span><?= date('M d, Y', strtotime($item['created_at'])) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>