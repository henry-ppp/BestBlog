<?php
session_start();
require_once 'database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to delete comments']);
    exit;
}

if (!isset($_POST['comment_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing comment ID']);
    exit;
}

$comment_id = (int)$_POST['comment_id'];

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
        echo json_encode(['success' => false, 'message' => 'You do not have permission to delete this comment']);
        exit;
    }

    // Delete the comment
    $sql = "DELETE FROM Comments WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $comment_id]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?> 