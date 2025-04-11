<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['blog_id'])) {
  http_response_code(401);
  echo json_encode(['error' => 'Unauthorized']);
  exit();
}

$blog_id = (int) $_POST['blog_id'];
$user_id = $_SESSION['user_id'];

// Check if liked
$stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE blog_id = ? AND user_id = ?");
$stmt->execute([$blog_id, $user_id]);
$liked = $stmt->fetchColumn() > 0;

if ($liked) {
  $stmt = $pdo->prepare("DELETE FROM likes WHERE blog_id = ? AND user_id = ?");
  $stmt->execute([$blog_id, $user_id]);
} else {
  $stmt = $pdo->prepare("INSERT INTO likes (blog_id, user_id) VALUES (?, ?)");
  $stmt->execute([$blog_id, $user_id]);
}

// Return updated like count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE blog_id = ?");
$stmt->execute([$blog_id]);
$totalLikes = $stmt->fetchColumn();

echo json_encode([
  'liked' => !$liked,
  'total' => $totalLikes
]);
