<?php
session_start();
require_once 'database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>About Us - BestBlog</title>
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
                        <li class="nav-item"><a class="nav-link active" href="about.php">About</a></li>
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
            <h1 class="mb-4">About BestBlog</h1>
            
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="about-content">
                        <h2>Our Story</h2>
                        <p>BestBlog was founded in 2023 with a simple mission: to create a platform where writers, thinkers, and creators can share their ideas with the world. We believe in the power of words and the importance of having a space where diverse voices can be heard.</p>

                        <h2>Our Platform</h2>
                        <p>BestBlog is more than just a blogging platform. It's a community of passionate individuals who share their knowledge, experiences, and perspectives. Our platform features:</p>
                        <ul>
                            <li>User-friendly interface for writing and publishing</li>
                            <li>Robust commenting system for engaging discussions</li>
                            <li>Secure user profiles and authentication</li>
                            <li>Admin tools for content moderation</li>
                            <li>Responsive design for all devices</li>
                        </ul>

                        <h2>Our Values</h2>
                        <p>At BestBlog, we are committed to:</p>
                        <ul>
                            <li>Freedom of expression</li>
                            <li>Quality content</li>
                            <li>User privacy and security</li>
                            <li>Community engagement</li>
                            <li>Continuous improvement</li>
                        </ul>

                        <h2>Join Our Community</h2>
                        <p>Whether you're a seasoned writer or just starting your blogging journey, BestBlog welcomes you. Create an account today and start sharing your stories with the world!</p>
                    </div>
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

    <script src="plugins/jquery/jquery.min.js"></script>
    <script src="plugins/bootstrap/bootstrap.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html> 