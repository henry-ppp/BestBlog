<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

$user_id = $_SESSION['user_id'];

// Fetch current profile info
$stmt = $pdo->prepare("SELECT full_name, bio, profile_picture FROM profiles WHERE user_id = ?");
$stmt->execute([$user_id]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $full_name = trim($_POST['full_name']);
  $bio = trim($_POST['bio'] ?? '');
  $profile_picture = $profile['profile_picture']; // default to existing

  if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    $tmp_name = $_FILES['profile_picture']['tmp_name'];
    $original_name = basename($_FILES['profile_picture']['name']);
    $profile_picture = "uploads/" . uniqid() . '_' . $original_name;
    move_uploaded_file($tmp_name, $profile_picture);
  }

  $stmt = $pdo->prepare("UPDATE profiles SET full_name = ?, bio = ?, profile_picture = ? WHERE user_id = ?");
  $stmt->execute([$full_name, $bio, $profile_picture, $user_id]);

  header("Location: profile.php");
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Edit Profile - BestBlog</title>
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
              <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
              <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
          </div>
        </nav>
      </div>
    </header>

    <!-- Main content -->
    <main>
      <section class="section container" style="max-width: 700px; margin: 4rem auto;">
        <h2 class="section-title text-center mb-4">Edit Profile</h2>

        <form action="edit_profile.php" method="POST" enctype="multipart/form-data">
          <div class="mb-3">
            <label for="full_name" class="form-label">Full Name</label>
            <input type="text" class="form-control" id="full_name" name="full_name" value="<?= htmlspecialchars($profile['full_name']) ?>" required>
          </div>

          <div class="mb-3">
            <label for="bio" class="form-label">Bio</label>
            <textarea class="form-control" id="bio" name="bio" rows="4"><?= htmlspecialchars($profile['bio']) ?></textarea>
          </div>

          <div class="mb-3">
            <label for="profile_picture" class="form-label">Profile Picture</label>
            <?php if ($profile['profile_picture']): ?>
              <div class="mb-2">
                <img src="<?= htmlspecialchars($profile['profile_picture']) ?>" alt="Current Picture" class="rounded" style="max-width: 150px;">
              </div>
            <?php endif; ?>
            <input type="file" class="form-control" id="profile_picture" name="profile_picture" accept="image/*">
          </div>

          <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="profile.php" class="btn btn-secondary">Cancel</a>
          </div>
        </form>
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
