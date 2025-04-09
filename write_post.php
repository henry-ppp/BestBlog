<?php
session_start();
require_once 'database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $postImage = $_FILES['postImage'] ?? null;

    if (empty($title) || empty($content)) {
        $error = 'Title and content are required.';
    } else {
        try {
            // Handle image upload
            $imagePath = '';
            if ($postImage && $postImage['error'] === UPLOAD_ERR_OK) {
                $uploadDir = 'uploads/post_images/';
                if (!file_exists($uploadDir)) {
                    if (!mkdir($uploadDir, 0777, true)) {
                        throw new Exception("Failed to create upload directory. Please check directory permissions.");
                    }
                }
                
                // Check if directory is writable
                if (!is_writable($uploadDir)) {
                    throw new Exception("Upload directory is not writable. Please check directory permissions.");
                }
                
                // Validate file type
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                $fileType = mime_content_type($postImage['tmp_name']);
                if (!in_array($fileType, $allowedTypes)) {
                    throw new Exception("Invalid file type. Only JPG, PNG, and GIF are allowed.");
                }
                
                // Generate unique filename
                $fileExtension = pathinfo($postImage['name'], PATHINFO_EXTENSION);
                $fileName = uniqid() . '.' . $fileExtension;
                $targetPath = $uploadDir . $fileName;
                
                // Check if file was uploaded successfully
                if (!is_uploaded_file($postImage['tmp_name'])) {
                    throw new Exception("Invalid file upload attempt.");
                }
                
                if (move_uploaded_file($postImage['tmp_name'], $targetPath)) {
                    $imagePath = $targetPath;
                } else {
                    $errorDetails = error_get_last();
                    throw new Exception("Failed to move uploaded file: " . ($errorDetails['message'] ?? 'Unknown error'));
                }
            }

            // Insert post into database
            $sql = "INSERT INTO Posts (title, content, postImage, authorID, createdAt) 
                    VALUES (:title, :content, :postImage, :authorID, NOW())";
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                ':title' => $title,
                ':content' => $content,
                ':postImage' => $imagePath,
                ':authorID' => $_SESSION['user_id']
            ]);

            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                throw new Exception("Database error: " . $errorInfo[2]);
            }

            $success = 'Post created successfully!';
            // Clear form
            $title = $content = '';
        } catch (PDOException $e) {
            $error = 'Error creating post: ' . $e->getMessage();
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Write a Post - BestBlog</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
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
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <h1 class="mb-4">Write a New Post</h1>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="write_post.php" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($title ?? ''); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="content" class="form-label">Content</label>
                            <textarea class="form-control" id="content" name="content" rows="10" required><?php echo htmlspecialchars($content ?? ''); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="postImage" class="form-label">Featured Image (optional)</label>
                            <input type="file" class="form-control" id="postImage" name="postImage" accept="image/*">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Publish Post</button>
                    </form>
                </div>
            </div>
        </section>
    </main>

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
</body>
</html> 