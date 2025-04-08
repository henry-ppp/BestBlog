<?php
session_start();
require_once 'database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to edit comments']);
    exit;
}

if (!isset($_POST['comment_id']) || !isset($_POST['comment'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$comment_id = (int)$_POST['comment_id'];
$new_comment = trim($_POST['comment']);

if (empty($new_comment)) {
    echo json_encode(['success' => false, 'message' => 'Comment cannot be empty']);
    exit;
}

try {
    // Check if user owns the comment or is admin
    $sql = "SELECT userID FROM Comments WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $comment_id]);
    $comment = $stmt->fetch();

    if (!$comment) {
        echo json_encode(['success' => false, 'message' => 'Comment not found']);
        exit;
    }

    if ($_SESSION['user_id'] != $comment['userID'] && $_SESSION['user_role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'You do not have permission to edit this comment']);
        exit;
    }

    // Update the comment
    $sql = "UPDATE Comments SET comment = :comment WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':comment' => $new_comment,
        ':id' => $comment_id
    ]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?> 