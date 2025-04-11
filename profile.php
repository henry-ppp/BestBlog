<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$stmt = $pdo->prepare("
  SELECT u.email, p.full_name, p.bio, p.profile_picture
  FROM users u
  JOIN profiles p ON u.id = p.user_id
  WHERE u.id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>My Profile - BestBlog</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
  <link rel="icon" href="images/favicon.png" type="image/x-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Neuton:wght@700&family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="plugins/bootstrap/bootstrap.min.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body class="d-flex flex-column min-vh-100">

  <div class="wrapper flex-grow-1">
    <!-- Header -->
    <header class="navigation">
      <div class="container">
        <nav class="navbar navbar-expand-lg navbar-light">
          <a class="navbar-brand" href="index.php">BestBlog</a>
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
              <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
              <li class="nav-item"><a class="nav-link" href="edit_profile.php">Edit Profile</a></li>
              <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
          </div>
        </nav>
      </div>
    </header>

    <!-- Main Content -->
    <main>
      <section class="section container" style="max-width: 800px; margin: 4rem auto;">
        <h2 class="section-title text-center mb-5">My Profile</h2>

        <div class="card shadow-sm border-0">
          <div class="row g-0">
            <div class="col-md-4 text-center p-4 border-end">
              <?php if ($user['profile_picture']): ?>
                <img src="<?= htmlspecialchars($user['profile_picture']) ?>" class="img-fluid rounded-circle mb-3" style="max-width: 150px;" alt="Profile Picture">
              <?php else: ?>
                <img src="images/default-avatar.png" class="img-fluid rounded-circle mb-3" style="max-width: 150px;" alt="Default Avatar">
              <?php endif; ?>
              <p class="text-muted mb-0">Member since <?= date('Y') ?></p>
            </div>
            <div class="col-md-8 p-4">
              <h4 class="mb-3"><?= htmlspecialchars($user['full_name']) ?></h4>
              <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
              <?php if ($user['bio']): ?>
                <p><strong>Bio:</strong><?= nl2br(htmlspecialchars($user['bio'])) ?></p>
              <?php else: ?>
                <p class="text-muted fst-italic">No bio added yet.</p>
              <?php endif; ?>
              <a href="edit_profile.php" class="btn btn-outline-primary mt-3">Edit Profile</a>
            </div>
          </div>
        </div>
      </section>
    </main>
  </div>

  <!-- Footer -->
  <footer class="bg-dark text-white text-center py-3 mt-auto">
    <div class="container">
      <p class="mb-0">&copy; 2025 BestBlog. All rights reserved.</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
