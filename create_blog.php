<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

$user_id = $_SESSION['user_id'];
$errors = [];
$success = false;

// Get categories
$category_stmt = $pdo->query("SELECT id, name FROM categories");
$categories = $category_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title']);
  $content = trim($_POST['content']);
  $selected_categories = $_POST['categories'] ?? [];
  $cover_image = null;

  if ($title === '' || $content === '') {
    $errors[] = "Title and content are required.";
  }

  // Handle image upload
  if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
    $tmp_name = $_FILES['cover_image']['tmp_name'];
    $original_name = basename($_FILES['cover_image']['name']);
    $cover_image = "uploads/" . uniqid() . '_' . $original_name;
    move_uploaded_file($tmp_name, $cover_image);
  }

  if (empty($errors)) {
    $stmt = $pdo->prepare("INSERT INTO blogs (user_id, title, content, cover_image) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $title, $content, $cover_image]);
    $blog_id = $pdo->lastInsertId();

    // Insert categories
    $cat_stmt = $pdo->prepare("INSERT INTO blog_categories (blog_id, category_id) VALUES (?, ?)");
    foreach ($selected_categories as $cat_id) {
      $cat_stmt->execute([$blog_id, $cat_id]);
    }

    $success = true;
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Create Blog - BestBlog</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
  <link rel="icon" href="images/favicon.png" type="image/x-icon">
  <link href="https://fonts.googleapis.com/css2?family=Neuton:wght@700&family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="plugins/bootstrap/bootstrap.min.css">
  <link rel="stylesheet" href="css/style.css">
  <style>
    select[multiple] {
      min-height: 120px;
      padding: 10px;
      border-radius: 6px;
    }
  </style>
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
      <section class="section container" style="max-width: 800px; margin: 4rem auto;">
        <h2 class="section-title text-center mb-4">Create New Blog</h2>

        <!-- ✅ Show Errors -->
        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger"><?= implode('<br>', $errors) ?></div>
        <?php endif; ?>

        <!-- ✅ Show Success -->
        <?php if ($success): ?>
          <div class="alert alert-success">Blog created successfully!</div>
        <?php endif; ?>

        <!-- Blog Form -->
        <form action="create_blog.php" method="POST" enctype="multipart/form-data">
          <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" id="title" name="title" class="form-control" required>
          </div>

          <div class="mb-3">
            <label for="content" class="form-label">Content</label>
            <textarea id="content" name="content" class="form-control" rows="8" required></textarea>
          </div>

          <div class="mb-3">
            <label for="cover_image" class="form-label">Cover Image (optional)</label>
            <input type="file" id="cover_image" name="cover_image" class="form-control" accept="image/*">
          </div>

          <div class="mb-3">
            <label for="categories" class="form-label">Categories</label>
            <select id="categories" name="categories[]" class="form-select">
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <button type="submit" class="btn btn-primary">Publish Blog</button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
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
