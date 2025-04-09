<?php
session_start();
require_once 'database.php';

//check if user is loged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

try {
    // Get all posts with author information
    $sql = "SELECT p.*, u.name as author_name 
            FROM Posts p 
            JOIN Users u ON p.authorID = u.id 
            ORDER BY p.createdAt DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $posts = $stmt->fetchAll();

    // Get all users
    $sql = "SELECT * FROM Users ORDER BY createdAt DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Admin Dashboard - BestBlog</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
    <link rel="icon" href="images/favicon.png" type="image/x-icon">
    
    <link href="https://fonts.googleapis.com/css2?family=Neuton:wght@700&family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
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
            <h1 class="mb-4">Admin Dashboard</h1>

            <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="row">
                <div class="col-12">
                    <h2>Manage Posts</h2>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($posts as $post): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($post['title']); ?></td>
                                    <td><?php echo htmlspecialchars($post['author_name']); ?></td>
                                    <td><?php echo date('d M Y', strtotime($post['createdAt'])); ?></td>
                                    <td>
                                        <a href="edit_post.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $post['id']; ?>">Delete</button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row mt-5">
                <div class="col-12">
                    <h2>Manage Users</h2>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Date Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                                    <td><?php echo date('d M Y', strtotime($user['createdAt'])); ?></td>
                                    <td>
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteUserModal<?php echo $user['id']; ?>">Delete</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Delete Post Modals -->
    <?php foreach ($posts as $post): ?>
    <div class="modal fade" id="deleteModal<?php echo $post['id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $post['id']; ?>" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel<?php echo $post['id']; ?>">Confirm Delete</h5>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this post? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form action="delete_post.php" method="POST">
                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Delete User Modals -->
    <?php foreach ($users as $user): ?>
    <?php if ($user['id'] != $_SESSION['user_id']): ?>
    <div class="modal fade" id="deleteUserModal<?php echo $user['id']; ?>" tabindex="-1" aria-labelledby="deleteUserModalLabel<?php echo $user['id']; ?>" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteUserModalLabel<?php echo $user['id']; ?>">Confirm Delete</h5>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this user? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form action="delete_user.php" method="POST">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <?php endforeach; ?>

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

    <script src="plugins/jquery/jquery.min.js"></script>
    <script src="plugins/bootstrap/bootstrap.min.js"></script>
    <script src="js/script.js"></script>
    <script>
    $(document).ready(function() {
        // Add submit handler for delete forms
        $('form[action="delete_post.php"]').on('submit', function(e) {
            e.preventDefault();
            var postId = $(this).find('input[name="post_id"]').val();
            
            $.ajax({
                url: 'delete_post.php',
                type: 'POST',
                data: { post_id: postId },
                success: function(response) {
                    location.reload();
                },
                error: function(xhr, status, error) {
                    alert('Error deleting post. Please try again.');
                }
            });
        });

        // Add submit handler for delete user forms
        $('form[action="delete_user.php"]').on('submit', function(e) {
            e.preventDefault();
            var userId = $(this).find('input[name="user_id"]').val();
            
            $.ajax({
                url: 'delete_user.php',
                type: 'POST',
                data: { user_id: userId },
                success: function(response) {
                    location.reload();
                },
                error: function(xhr, status, error) {
                    alert('Error deleting user. Please try again.');
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