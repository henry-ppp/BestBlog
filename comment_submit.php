<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || empty($_POST['blog_id']) || empty($_POST['content'])) {
  http_response_code(400);
  echo json_encode(['error' => 'Invalid request']);
  exit();
}

$blog_id = (int) $_POST['blog_id'];
$user_id = $_SESSION['user_id'];
$content = trim($_POST['content']);

if ($content === '') {
  echo json_encode(['error' => 'Empty comment']);
  exit();
}

// Get user full name
$stmt = $pdo->prepare("SELECT full_name FROM profiles WHERE user_id = ?");
$stmt->execute([$user_id]);
$full_name = $stmt->fetchColumn();

// Insert comment
$stmt = $pdo->prepare("INSERT INTO comments (blog_id, user_id, content) VALUES (?, ?, ?)");
$stmt->execute([$blog_id, $user_id, $content]);

echo json_encode([
  'success' => true,
  'name' => htmlspecialchars($full_name),
  'content' => nl2br(htmlspecialchars($content)),
  'time' => date("d M Y H:i")
]);
