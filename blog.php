<?php
session_start();
require 'db.php';

$blog_id = $_GET['id'] ?? null;

if (!$blog_id) {
  header("Location: index.php");
  exit();
}

// Fetch blog with user info
$stmt = $pdo->prepare("
  SELECT b.*, p.full_name
  FROM blogs b
  JOIN users u ON b.user_id = u.id
  JOIN profiles p ON u.id = p.user_id
  WHERE b.id = ?
");
$stmt->execute([$blog_id]);
$blog = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$blog) {
  http_response_code(404);
  die("Blog not found.");
}

// If user is logged in, get name
$full_name = null;
if (isset($_SESSION['user_id'])) {
  $stmt = $pdo->prepare("SELECT full_name FROM profiles WHERE user_id = ?");
  $stmt->execute([$_SESSION['user_id']]);
  $profile = $stmt->fetch(PDO::FETCH_ASSOC);
  $full_name = $profile['full_name'] ?? 'User';
}

$hasLiked = false;
$totalLikes = 0;

if (isset($_SESSION['user_id'])) {
  $likeStmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE blog_id = ? AND user_id = ?");
  $likeStmt->execute([$blog_id, $_SESSION['user_id']]);
  $hasLiked = $likeStmt->fetchColumn() > 0;
}

$likeCountStmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE blog_id = ?");
$likeCountStmt->execute([$blog_id]);
$totalLikes = $likeCountStmt->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['like'])) {
    if (!$hasLiked) {
      $stmt = $pdo->prepare("INSERT INTO likes (blog_id, user_id) VALUES (?, ?)");
      $stmt->execute([$blog_id, $_SESSION['user_id']]);
    } else {
      $stmt = $pdo->prepare("DELETE FROM likes WHERE blog_id = ? AND user_id = ?");
      $stmt->execute([$blog_id, $_SESSION['user_id']]);
    }
    header("Location: blog.php?id=$blog_id");
    exit();
  }

  // Handle new comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment']) && isset($_POST['comment_text'])) {
    $comment = trim($_POST['comment_text']);
    if ($comment !== '' && isset($_SESSION['user_id'])) {
      $stmt = $pdo->prepare("INSERT INTO comments (blog_id, user_id, content) VALUES (?, ?, ?)");
      $stmt->execute([$blog_id, $_SESSION['user_id'], $comment]);
      header("Location: blog.php?id=$blog_id");
      exit();
    }
  }
  
  // Fetch comments
  $commentStmt = $pdo->prepare("
    SELECT c.content, c.created_at, p.full_name
    FROM comments c
    JOIN users u ON c.user_id = u.id
    JOIN profiles p ON u.id = p.user_id
    WHERE c.blog_id = ?
    ORDER BY c.created_at DESC
  ");
  $commentStmt->execute([$blog_id]);
  $comments = $commentStmt->fetchAll(PDO::FETCH_ASSOC);
  
  
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($blog['title']) ?> - BestBlog</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
  <link rel="icon" href="images/favicon.png" type="image/x-icon">
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
          <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
            <ul class="navbar-nav">
              <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
              <li class="nav-item"><a class="nav-link" href="#">About</a></li>
              <li class="nav-item"><a class="nav-link" href="#">Contact</a></li>
            </ul>
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

    <!-- Blog Content -->
    <main>
      <section class="section container" style="max-width: 800px; margin: 4rem auto;">
        <?php if ($blog['cover_image']): ?>
          <img src="<?= htmlspecialchars($blog['cover_image']) ?>" class="img-fluid rounded mb-4 w-100" alt="Cover Image">
        <?php else: ?>
          <img src="images/post/post-1.jpg" class="img-fluid rounded mb-4 w-100" alt="Default Cover">
        <?php endif; ?>

        <h1 class="mb-3"><?= htmlspecialchars($blog['title']) ?></h1>
        <p class="text-muted mb-4">
          <?= date('d M Y', strtotime($blog['created_at'])) ?> |
          By <strong><?= htmlspecialchars($blog['full_name']) ?></strong>
        </p>

        <article class="mb-5">
          <?= nl2br(htmlspecialchars($blog['content'])) ?>
        </article>

        <?php if (isset($_SESSION['user_id'])): ?>
            <div id="like-container" class="mb-4">
            <button id="like-btn" class="btn <?= $hasLiked ? 'btn-danger' : 'btn-outline-danger' ?>">
                <?= $hasLiked ? '❤️ Liked' : '♡ Like' ?> (<?= $totalLikes ?>)
            </button>
            </div>
            <?php else: ?>
            <p><a href="login.php">Login</a> to like this post. ❤️</p>
        <?php endif; ?>

        <hr class="my-5">

        <h4 class="mb-4">Comments (<?= count($comments) ?>)</h4>

        <?php if (isset($_SESSION['user_id'])): ?>
          <form id="comment-form" class="mb-5">
            <div class="mb-3">
              <textarea name="content" class="form-control" rows="3" placeholder="Write your comment..." required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Post Comment</button>
          </form>
        <?php else: ?>
          <p><a href="login.php">Login</a> to write a comment.</p>
        <?php endif; ?>

        <!-- ✅ Always render this container -->
        <div id="comment-list">
          <?php if (!empty($comments)): ?>
            <?php foreach ($comments as $c): ?>
              <div class="mb-4 border-bottom pb-3">
                <strong><?= htmlspecialchars($c['full_name']) ?></strong>
                <small class="text-muted d-block"><?= date('d M Y H:i', strtotime($c['created_at'])) ?></small>
                <p class="mt-2"><?= nl2br(htmlspecialchars($c['content'])) ?></p>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <?php if (empty($comments)): ?>
          <p class="text-muted" id="no-comments-msg">No comments yet. Be the first to share your thoughts!</p>
        <?php endif; ?>

        <a href="index.php" class="btn btn-outline-primary">← Back to Home</a>
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
  <script>
    document.getElementById('like-btn')?.addEventListener('click', function () {
    fetch('like_toggle.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'blog_id=<?= $blog['id'] ?>'
    })
    .then(res => res.json())
    .then(data => {
        const btn = document.getElementById('like-btn');
        if (data.liked) {
        btn.classList.remove('btn-outline-danger');
        btn.classList.add('btn-danger');
        btn.innerHTML = `❤️ Liked (${data.total})`;
        } else {
        btn.classList.remove('btn-danger');
        btn.classList.add('btn-outline-danger');
        btn.innerHTML = `♡ Like (${data.total})`;
        }
    });
    });
</script>
<script>
    document.getElementById('comment-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const form = e.target;
    const content = form.content.value.trim();
    if (!content) return;

    fetch('comment_submit.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `blog_id=<?= $blog['id'] ?>&content=${encodeURIComponent(content)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
        const newComment = `
            <div class="mb-4 border-bottom pb-3">
            <strong>${data.name}</strong>
            <small class="text-muted d-block">${data.time}</small>
            <p class="mt-2">${data.content}</p>
            </div>
        `;
        document.getElementById('comment-list').insertAdjacentHTML('afterbegin', newComment);
        form.reset();
        }
    });
    });
</script>


</body>
</html>
