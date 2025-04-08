<?php
session_start();
require_once 'database.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message_content = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($subject) || empty($message_content)) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // In a real application, you would send an email here
        $message = 'Thank you for your message! We will get back to you soon.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Contact Us - BestBlog</title>
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
                        <li class="nav-item"><a class="nav-link active" href="contact.php">Contact</a></li>
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
            <h1 class="mb-4">Contact Us</h1>
            
            <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-6">
                    <div class="contact-info mb-4">
                        <h2>Get in Touch</h2>
                        <p>We'd love to hear from you! Whether you have a question about our platform, need technical support, or want to share feedback, our team is here to help.</p>
                        
                        <h3>Contact Information</h3>
                        <ul class="list-unstyled">
                            <li><strong>Email:</strong> support@bestblog.com</li>
                            <li><strong>Phone:</strong> +1 (555) 123-4567</li>
                            <li><strong>Address:</strong> 123 Blog Street, Digital City, DC 12345</li>
                        </ul>

                        <h3>Business Hours</h3>
                        <ul class="list-unstyled">
                            <li>Monday - Friday: 9:00 AM - 6:00 PM</li>
                            <li>Saturday: 10:00 AM - 4:00 PM</li>
                            <li>Sunday: Closed</li>
                        </ul>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="contact-form">
                        <h2>Send Us a Message</h2>
                        <form method="POST" action="contact.php">
                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="subject" class="form-label">Subject</label>
                                <input type="text" class="form-control" id="subject" name="subject" required>
                            </div>
                            <div class="mb-3">
                                <label for="message" class="form-label">Message</label>
                                <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Send Message</button>
                        </form>
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