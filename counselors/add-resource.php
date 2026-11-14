<?php
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['counselor', 'admin'])) {
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . '/../config/db.php';
$counselor_id = $_SESSION['user_id'];

$error = '';
$success = '';

if (isset($_GET['added'])) {
    $success = "Self-help resource published successfully.";
} elseif (isset($_GET['deleted'])) {
    $success = "Self-help resource deleted successfully.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle DELETE resource
    if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['resource_id'])) {
        $resource_id = (int)$_POST['resource_id'];

        $delStmt = $pdo->prepare("DELETE FROM self_help_resources WHERE resource_id = ?");
        if ($delStmt->execute([$resource_id])) {
            header("Location: add-resource.php?deleted=1");
            exit();
        } else {
            $error = "Failed to delete resource. Please try again.";
        }
    } else {
        // Handle INSERT resource
        $title    = trim($_POST['title'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $content  = trim($_POST['content'] ?? '');

        if (empty($title) || empty($category) || empty($content)) {
            $error = "Please fill in all fields.";
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO self_help_resources (title, category, content, created_by)
                VALUES (?, ?, ?, ?)
            ");

            if ($stmt->execute([$title, $category, $content, $counselor_id])) {
                header("Location: add-resource.php?added=1");
                exit();
            } else {
                $error = "Failed to publish article. Please try again.";
            }
        }
    }
}

// Fetch all published resources for management table (SELECT + JOIN)
$listStmt = $pdo->query("
    SELECT 
        r.resource_id,
        r.title,
        r.category,
        r.created_at,
        u.first_name,
        u.last_name
    FROM self_help_resources r
    JOIN users u ON r.created_by = u.user_id
    ORDER BY r.created_at DESC
");
$resources = $listStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Guidance Article - MindHub</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-body">

    <nav class="navbar">
        <a href="dashboard.php" class="brand">
            <img src="../assets/images/logo.png" alt="MindHub Logo">
            <span>Mind<span class="hub-accent">Hub</span></span>
            <span class="brand-badge">Staff Workspace</span>
        </a>
        <div class="user-info">
            <span>Counselor: <strong><?= htmlspecialchars($_SESSION['name'] ?? 'Staff') ?></strong></span>
            <a href="dashboard.php" class="btn-logout" style="background:#3e6a53; color:#fff; border:none;">Console</a>
            <a href="../logout.php" class="btn-logout">Logout</a>
        </div>
    </nav>

    <div class="dashboard-container" style="max-width: 820px;">
        <div class="welcome-header">
            <div>
                <h1>Publish Self-Help Resource</h1>
                <p>Share wellness advice, study strategies, and coping tips with students.</p>
            </div>
            <a href="dashboard.php" class="btn-logout" style="background:#64748b; color:#fff; border:none;">← Back</a>
        </div>

        <div class="table-container" style="margin-bottom: 24px;">
            <?php if (!empty($error)): ?>
                <div class="alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Article Title:*</label>
                    <input type="text" name="title" placeholder="e.g., Techniques for Managing College Burnout" required>
                </div>

                <div class="form-group">
                    <label>Category:*</label>
                    <select name="category" required>
                        <option value="">-- Select Category --</option>
                        <option value="Academic">Academic</option>
                        <option value="Mental Health">Mental Health</option>
                        <option value="Personal">Personal</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Article Content / Guidance Tips:*</label>
                    <textarea name="content" rows="6" placeholder="Write supportive advice, steps, or recommendations..." required></textarea>
                </div>

                <button type="submit" class="btn-submit">Publish Resource</button>
            </form>
        </div>

        <div class="table-container">
            <h2>Published Self-Help Resources</h2>

            <?php if (empty($resources)): ?>
                <div class="empty-state">
                    <p>No self-help resources published yet.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Author</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resources as $item): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($item['title']) ?></strong></td>
                                <td><?= htmlspecialchars($item['category']) ?></td>
                                <td><?= htmlspecialchars($item['first_name'] . ' ' . $item['last_name']) ?></td>
                                <td><?= date('M d, Y', strtotime($item['created_at'])) ?></td>
                                <td>
                                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this resource?');" style="display:inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="resource_id" value="<?= $item['resource_id'] ?>">
                                        <button type="submit" class="action-btn btn-reject">Delete</button>
                                    </form>
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