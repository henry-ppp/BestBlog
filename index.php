<?php
session_start();
require 'db.php';

$full_name = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT full_name FROM profiles WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
    $full_name = $profile['full_name'] ?? 'User';
}

$blogs = $pdo->query("
  SELECT b.id, b.title, b.content, b.cover_image, b.created_at, p.full_name
  FROM blogs b
  JOIN users u ON b.user_id = u.id
  JOIN profiles p ON u.id = p.user_id
  ORDER BY b.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>BestBlog - Personal Blogging</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
  <meta name="description" content="This is meta description">
  <link rel="icon" href="images/favicon.png" type="image/x-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Neuton:wght@700&family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

  <!-- Bootstrap & Main Styles -->
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
          <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
          <!-- Left links -->
          <ul class="navbar-nav">
            <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="#">About</a></li>
            <li class="nav-item"><a class="nav-link" href="#">Contact</a></li>
            <li class="nav-item"><a class="nav-link" href="#">Admin Dashboard</a></li>
          </ul>

          <!-- Right links -->
          <ul class="navbar-nav">
            <?php if ($full_name): ?>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                  Logged in as <?= htmlspecialchars($full_name) ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                  <li><a class="dropdown-item" href="profile.php">My Profile</a></li>
                  <li><hr class="dropdown-divider"></li>
                  <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                </ul>
              </li>
            <?php else: ?>
              <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
              <li class="nav-item"><a class="nav-link" href="register.html">Register</a></li>
            <?php endif; ?>
          </ul>
        </div>
        </nav>
      </div>
    </header>

    <!-- Main Content -->
    <main>
      <section class="section container">
        <?php if (isset($_SESSION['user_id'])): ?>
          <div class="mb-4 text-end">
            <a href="create_blog.php" class="btn btn-primary">+ Create New Blog</a>
          </div>
        <?php endif; ?>

        <h2 class="section-title">Recent Insights</h2>

        <div class="row">
          <div class="col-lg-8 mb-5">
            <div class="row">
              <?php if (!empty($blogs)): ?>
                <?php foreach ($blogs as $blog): ?>
                  <div class="col-12 mb-4">
                    <article class="card">
                      <a href="blog.php?id=<?= $blog['id'] ?>">
                        <?php if ($blog['cover_image']): ?>
                          <img src="<?= htmlspecialchars($blog['cover_image']) ?>" alt="Blog Cover" class="w-100">
                        <?php else: ?>
                          <img src="images/post/post-1.jpg" alt="Default Cover" class="w-100">
                        <?php endif; ?>
                        <div class="post-info">
                          <?= date('d M Y', strtotime($blog['created_at'])) ?> |
                          <?= htmlspecialchars($blog['full_name']) ?>
                        </div>
                        <h2><?= htmlspecialchars($blog['title']) ?></h2>
                        <p><?= htmlspecialchars(substr(strip_tags($blog['content']), 0, 150)) ?>...</p>
                      </a>
                      <a class="read-more-btn" href="blog.php?id=<?= $blog['id'] ?>">Read Full Post</a>
                    </article>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <p class="text-muted text-center">No blogs yet. Be the first to post!</p>
              <?php endif; ?>
            </div>
          </div>

          <!-- Sidebar -->
          <div class="col-lg-4">
            <div class="widget">
              <img src="images/author.jpg" alt="About Me" class="w-100">
              <h2>Jane Doe</h2>
              <p>Passionate writer and tech enthusiast. I love to explore the intersection of culture, history, and modern innovations.</p>
              <a href="#" class="btn btn-outline-primary">Personal Profile</a>
            </div>

            <div class="widget">
              <h2>Editor’s Picks</h2>
              <article class="card mb-4">
                <img src="images/post/post-9.jpg" alt="Post Thumbnail" class="w-100">
                <div class="post-info">2 min read</div>
                <h3><a href="#">New Travel Guidelines in Europe</a></h3>
                <p>Stay up to date with the latest travel regulations in the EU...</p>
                <a class="read-more-btn" href="#">Read Full Post</a>
              </article>
            </div>

            <div class="widget">
              <h2>Popular Topics</h2>
              <ul>
                <li><a href="#">Technology (5)</a></li>
                <li><a href="#">Culture (3)</a></li>
                <li><a href="#">Travel (2)</a></li>
              </ul>
            </div>
          </div>
        </div>
      </section>
    </main>
  </div>

  <!-- Footer -->
  <footer class="bg-dark mt-5">
    <div class="container section">
      <div class="row">
        <div class="col-lg-10 mx-auto text-center">
          <ul class="navbar-footer p-0 d-flex justify-content-center mb-0">
            <li><a href="#" class="nav-link">About</a></li>
            <li><a href="#" class="nav-link">Content</a></li>
            <li><a href="#" class="nav-link">Privacy Policy</a></li>
            <li><a href="#" class="nav-link">Terms & Conditions</a></li>
            <li><a href="#" class="nav-link">Page Not Found</a></li>
          </ul>
        </div>
      </div>
    </div>
  </footer>

  <!-- JS Plugins -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
