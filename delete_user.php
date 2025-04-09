<?php
session_start();
require_once 'database.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Check if user_id is provided
if (!isset($_POST['user_id'])) {
    header("Location: admin.php");
    exit;
}

$user_id = (int)$_POST['user_id'];

// Prevent admin from deleting themselves
if ($user_id === $_SESSION['user_id']) {
    header("Location: admin.php");
    exit;
}

try {
    // Start transaction
    $pdo->beginTransaction();

    // Delete user's comments
    $sql = "DELETE FROM Comments WHERE userID = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $user_id]);

    // Delete user's posts
    $sql = "DELETE FROM Posts WHERE authorID = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $user_id]);

    // Delete user
    $sql = "DELETE FROM Users WHERE id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $user_id]);

    // Commit transaction
    $pdo->commit();

    // Return success response
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?> 