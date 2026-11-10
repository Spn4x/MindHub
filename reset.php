<?php
require_once __DIR__ . '/config/db.php';

$password = '12345678';
$email = 'counselor@ccc.edu.ph';

$new_hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("update users set password_hash = ? where email = ?");
$stmt->execute([$new_hash, $email]);

echo "<h2>Password successfully reset!</h2>";
echo "Email: <strong>$email</strong><br>";
echo "Password: <strong>$password</strong><br><br>";
echo "<a href='index.php'>Go to Login Page</a>";