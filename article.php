<?php
session_start();
require_once 'database.php';

// Get article ID from URL
$article_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($article_id <= 0) {
    header("Location: index.php");
    exit;
}

try {
    // Get aricle details
    $sql = "SELECT p.*, u.name as author_name 
            FROM Posts p 
            JOIN Users u ON p.authorID = u.id 
            WHERE p.id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $article_id]);
    $article = $stmt->fetch();

    if (!$article) {
        header("Location: index.php");
        exit;
    }

    // Get comments for the article
    $sql = "SELECT c.*, u.name as commenter_name 
            FROM Comments c 
            JOIN Users u ON c.userID = u.id 
            WHERE c.postID = :post_id 
            ORDER BY c.createdAt DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':post_id' => $article_id]);
    $comments = $stmt->fetchAll();

    // Handle comment submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment']) && isset($_SESSION['user_id'])) {
        $comment = trim($_POST['comment']);
        if (!empty($comment)) {
            $sql = "INSERT INTO Comments (postID, userID, comment) VALUES (:post_id, :user_id, :comment)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':post_id' => $article_id,
                ':user_id' => $_SESSION['user_id'],
                ':comment' => $comment
            ]);
            header("Location: article.php?id=" . $article_id);
            exit;
        }
    }
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($article['title']); ?> - BestBlog</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
    <meta name="description" content="<?php echo htmlspecialchars(substr($article['content'], 0, 160)); ?>">
    <link rel="icon" href="images/favicon.png" type="image/x-icon">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Neuton:wght@700&family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap & Main Styles -->
    <link rel="stylesheet" href="plugins/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="navigation">
        <div class="container">
            <nav class="navbar navbar-expand-lg navbar-light">
                <a class="navbar-brand" href="index.php">BestBlog</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                        <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
                        <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                    </ul>
                    <!-- User Info Dropdown -->
                    <ul class="navbar-nav ml-auto">
                        <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="user-profile.php">Profile</a></li>
                                <li><a class="dropdown-item" href="write_post.php">Write a Post</a></li>
                                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                                <li><a class="dropdown-item" href="admin.php">Admin Dashboard</a></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                        <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Sign Up</a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </nav>
        </div>
    </header>

    <main>
        <section class="section container">
            <article class="blog-post">
                <h1 class="post-title"><?php echo htmlspecialchars($article['title']); ?></h1>
                <div class="post-meta">
                    <?php 
                    $date = new DateTime($article['createdAt']);
                    echo $date->format('d M Y'); 
                    ?> | 
                    By <?php echo htmlspecialchars($article['author_name']); ?> | 
                    <?php echo ceil(str_word_count($article['content']) / 200); ?> min read
                </div>

                <?php if (isset($_SESSION['user_id']) && ($_SESSION['user_id'] == $article['authorID'] || $_SESSION['user_role'] === 'admin')): ?>
                <div class="mb-4">
                    <a href="edit_post.php?id=<?php echo $article['id']; ?>" class="btn btn-primary">Edit Article</a>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $article['id']; ?>">Delete Article</button>
                </div>
                <?php endif; ?>

                <?php if (!empty($article['postImage'])): ?>
                <img src="<?php echo htmlspecialchars($article['postImage']); ?>" alt="Post Thumbnail" class="w-100 mb-4">
                <?php endif; ?>
                <div class="post-content">
                    <?php echo nl2br(htmlspecialchars($article['content'])); ?>
                </div>
            </article>

            <section class="comments mt-5">
                <h2>Comments</h2>
                <?php if (empty($comments)): ?>
                <p>No comments yet. Be the first to comment!</p>
                <?php else: ?>
                    <?php foreach ($comments as $comment): ?>
                    <div class="comment mb-4">
                        <div class="comment-content">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong><?php echo htmlspecialchars($comment['commenter_name']); ?></strong>
                                <small class="text-muted">
                                    <?php 
                                    $date = new DateTime($comment['createdAt']);
                                    echo $date->format('d M Y H:i');
                                    ?>
                                </small>
                            </div>
                            <p class="mb-2"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                            <?php if (isset($_SESSION['user_id']) && ($_SESSION['user_id'] == $comment['userID'] || $_SESSION['user_role'] === 'admin')): ?>
                            <div class="comment-actions mt-2">
                                <button class="btn btn-sm btn-outline-primary edit-comment" data-comment-id="<?php echo $comment['id']; ?>" data-comment-content="<?php echo htmlspecialchars($comment['comment']); ?>">
                                    Edit
                                </button>
                                <button class="btn btn-sm btn-outline-danger delete-comment" data-comment-id="<?php echo $comment['id']; ?>">
                                    Delete
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                <h3>Add a Comment</h3>
                <form action="article.php?id=<?php echo $article_id; ?>" method="post">
                    <div class="mb-3">
                        <label for="comment" class="form-label">Your Comment</label>
                        <textarea class="form-control" id="comment" name="comment" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
                <?php else: ?>
                <p>Please <a href="login.php">login</a> to leave a comment.</p>
                <?php endif; ?>
            </section>
        </section>
    </main>

    <!-- Edit Comment Modal -->
    <div class="modal fade" id="editCommentModal" tabindex="-1" aria-labelledby="editCommentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCommentModalLabel">Edit Comment</h5>
                </div>
                <div class="modal-body">
                    <form id="editCommentForm">
                        <input type="hidden" id="editCommentId" name="comment_id">
                        <div class="mb-3">
                            <label for="editCommentContent" class="form-label">Comment</label>
                            <textarea class="form-control" id="editCommentContent" name="comment" rows="3" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveEditComment">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Comment Modal -->
    <div class="modal fade" id="deleteCommentModal" tabindex="-1" aria-labelledby="deleteCommentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteCommentModalLabel">Delete Comment</h5>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this comment? This action cannot be undone.</p>
                    <input type="hidden" id="deleteCommentId" name="comment_id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteComment">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal<?php echo $article['id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $article['id']; ?>" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel<?php echo $article['id']; ?>">Confirm Delete</h5>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this article? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form action="delete_post.php" method="POST">
                        <input type="hidden" name="post_id" value="<?php echo $article['id']; ?>">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark mt-5">
        <div class="container section">
            <div class="row">
                <div class="col-lg-10 mx-auto text-center">
                    <ul class="navbar-footer p-0 d-flex justify-content-center mb-0">
                        <li><a href="index.php" class="nav-link">About</a></li>
                        <li><a href="index.php" class="nav-link">Content</a></li>
                        <li><a href="index.php" class="nav-link">Privacy Policy</a></li>
                        <li><a href="index.php" class="nav-link">Terms & Conditions</a></li>
                        <li><a href="index.php" class="nav-link">Page Not Found</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <!-- JS Plugins -->
    <script src="plugins/jquery/jquery.min.js"></script>
    <script src="plugins/bootstrap/bootstrap.min.js"></script>
    <script src="js/script.js"></script>
    <script>
    $(document).ready(function() {
        // Add click handler for delete button
        $('.btn-danger[data-bs-toggle="modal"]').on('click', function() {
            var modalId = $(this).data('bs-target');
            $(modalId).modal('show');
        });

        // Add submit handler for delete form
        $('form[action="delete_post.php"]').on('submit', function(e) {
            e.preventDefault();
            var postId = $(this).find('input[name="post_id"]').val();
            
            $.ajax({
                url: 'delete_post.php',
                type: 'POST',
                data: { post_id: postId },
                success: function(response) {
                    window.location.href = 'index.php';
                },
                error: function(xhr, status, error) {
                    alert('Error deleting post. Please try again.');
                }
            });
        });

        // Edit Comment
        $('.edit-comment').click(function() {
            var commentId = $(this).data('comment-id');
            var commentContent = $(this).data('comment-content');
            $('#editCommentId').val(commentId);
            $('#editCommentContent').val(commentContent);
            $('#editCommentModal').modal('show');
        });

        // Save Edited Comment
        $('#saveEditComment').click(function() {
            var commentId = $('#editCommentId').val();
            var commentContent = $('#editCommentContent').val();
            
            $.ajax({
                url: 'update_comment.php',
                type: 'POST',
                data: {
                    comment_id: commentId,
                    comment: commentContent
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error updating comment: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error updating comment. Please try again.');
                }
            });
        });

        // Delete Comment
        $('.delete-comment').click(function() {
            var commentId = $(this).data('comment-id');
            $('#deleteCommentId').val(commentId);
            $('#deleteCommentModal').modal('show');
        });

        // Confirm Delete Comment
        $('#confirmDeleteComment').click(function() {
            var commentId = $('#deleteCommentId').val();
            
            $.ajax({
                url: 'delete_comment.php',
                type: 'POST',
                data: {
                    comment_id: commentId
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error deleting comment: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error deleting comment. Please try again.');
                }
            });
        });

        // Close modal when clicking cancel button
        $('.modal .btn-secondary').on('click', function() {
            $(this).closest('.modal').modal('hide');
        });

        // Close modal when clicking outside
        $('.modal').on('click', function(e) {
            if ($(e.target).hasClass('modal')) {
                $(this).modal('hide');
            }
        });

        // Close modal when pressing ESC key
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') {
                $('.modal').modal('hide');
            }
        });
    });
    </script>
</body>
</html> 