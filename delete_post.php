<?php
session_start();
require_once 'database.php';

// Debug logging
error_log("Delete post request received");
error_log("POST data: " . print_r($_POST, true));
error_log("Session data: " . print_r($_SESSION, true));

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get post ID from POST data
$post_id = $_POST['post_id'] ?? 0;

if ($post_id) {
    try {
        // First, get the post to check permissions
        $stmt = $pdo->prepare("SELECT authorID FROM Posts WHERE id = ?");
        $stmt->execute([$post_id]);
        $post = $stmt->fetch();

        if ($post) {
            // Check if user is the author or an admin
            if ($_SESSION['user_id'] == $post['authorID'] || $_SESSION['user_role'] === 'admin') {
                // Delete the post (cascade will handle related comments and likes)
                $stmt = $pdo->prepare("DELETE FROM Posts WHERE id = ?");
                $result = $stmt->execute([$post_id]);

                if ($result) {
                    // Redirect to home page with success message
                    $_SESSION['success'] = 'Post deleted successfully.';
                    header("Location: index.php");
                    exit;
                } else {
                    throw new Exception("Failed to delete post");
                }
            } else {
                $_SESSION['error'] = 'You do not have permission to delete this post.';
            }
        } else {
            $_SESSION['error'] = 'Post not found.';
        }
    } catch (PDOException $e) {
        error_log("Delete Post Error: " . $e->getMessage());
        $_SESSION['error'] = 'Error deleting post: ' . $e->getMessage();
    } catch (Exception $e) {
        error_log("Delete Post Error: " . $e->getMessage());
        $_SESSION['error'] = $e->getMessage();
    }
} else {
    $_SESSION['error'] = 'Invalid post ID.';
}

// If we get here, something went wrong
header("Location: index.php");
exit; 