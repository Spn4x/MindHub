<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
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
</head>
<body class="dashboard-body">

    <nav class="navbar">
        <a href="dashboard.php" class="brand">
            <img src="../assets/images/logo.png" alt="MindHub Logo">
            <span>Mind<span class="hub-accent">Hub</span></span>
        </a>
        <div class="user-info">
            <span>Welcome, <strong><?= htmlspecialchars($_SESSION['name'] ?? 'Student') ?></strong></span>
            <a href="dashboard.php" class="btn-logout" style="background:#3e6a53; color:#fff; border:none;">Dashboard</a>
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