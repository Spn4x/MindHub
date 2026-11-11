<?php
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['counselor', 'admin'])) {
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . '/../config/db.php';
$counselor_id = $_SESSION['user_id'];

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title    = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $content  = trim($_POST['content'] ?? '');

    if (empty($title) || empty($category) || empty($content)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $pdo->prepare("
            insert into self_help_resources (title, category, content, created_by)
            values (?, ?, ?, ?)
        ");

        if ($stmt->execute([$title, $category, $content, $counselor_id])) {
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Failed to publish article. Please try again.";
        }
    }
}
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

    <div class="dashboard-container" style="max-width: 680px;">
        <div class="welcome-header">
            <div>
                <h1>Publish Self-Help Resource</h1>
                <p>Share wellness advice, study strategies, and coping tips with students.</p>
            </div>
            <a href="dashboard.php" class="btn-logout" style="background:#64748b; color:#fff; border:none;">← Back</a>
        </div>

        <div class="table-container">
            <?php if (!empty($error)): ?>
                <div class="alert-error"><?= htmlspecialchars($error) ?></div>
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
    </div>

</body>
</html>