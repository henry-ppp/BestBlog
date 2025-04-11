<?php
session_start();
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once 'database.php';
    if (!isset($pdo)) {
        throw new Exception("Database connection failed");
    }

    $pdo->query("SELECT 1");
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

// Debug code
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// var_dump($_SESSION);
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
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Neuton:wght@700&family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Work Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
    }
    h1, h2, h3, h4, h5, h6 {
      font-family: 'Neuton', Georgia, 'Times New Roman', Times, serif;
    }
  </style>

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
                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                    <li class="nav-item"><a class="nav-link" href="admin.php">Admin Dashboard</a></li>
                    <?php endif; ?>
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
      <h2 class="section-title">Recent Insights</h2>
      
      <div class="row">
        <div class="col-lg-8 mb-5 mb-lg-0">
          <div class="row">
            <?php
            // Pagination setup
            $articlesPerPage = 6;
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $offset = ($page - 1) * $articlesPerPage;

            // Get total number of articles
            $totalArticles = $pdo->query("SELECT COUNT(*) FROM Posts")->fetchColumn();
            $totalPages = ceil($totalArticles / $articlesPerPage);

            // Get articles for current page
            $sql = "SELECT p.*, u.name as author_name 
                    FROM Posts p 
                    JOIN Users u ON p.authorID = u.id 
                    ORDER BY p.createdAt DESC 
                    LIMIT :limit OFFSET :offset";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':limit', $articlesPerPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $articles = $stmt->fetchAll();

            foreach ($articles as $article):
                $date = new DateTime($article['createdAt']);
            ?>
            <div class="col-md-6 mb-4">
              <article class="card">
                <a href="article.php?id=<?php echo $article['id']; ?>">
                  <img src="<?php echo htmlspecialchars($article['postImage']); ?>" alt="Post Thumbnail" class="w-100">
                  <div class="post-info"><?php echo $date->format('d M Y'); ?> | <?php echo ceil(str_word_count($article['content']) / 200); ?> min read</div>
                  <h2><a href="article.php?id=<?php echo $article['id']; ?>"><?php echo htmlspecialchars($article['title']); ?></a></h2>
                  <p><?php echo htmlspecialchars(substr($article['content'], 0, 150)) . '...'; ?></p>
                </a>
                <a class="read-more-btn" href="article.php?id=<?php echo $article['id']; ?>">Read Full Post</a>
              </article>
            </div>
            <?php endforeach; ?>
          </div>

          <!-- Pagination -->
          <?php if ($totalPages > 1): ?>
          <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
              <?php if ($page > 1): ?>
              <li class="page-item">
                <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                  <span aria-hidden="true">&laquo;</span>
                </a>
              </li>
              <?php endif; ?>

              <?php for ($i = 1; $i <= $totalPages; $i++): ?>
              <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
              </li>
              <?php endfor; ?>

              <?php if ($page < $totalPages): ?>
              <li class="page-item">
                <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                  <span aria-hidden="true">&raquo;</span>
                </a>
              </li>
              <?php endif; ?>
            </ul>
          </nav>
          <?php endif; ?>
        </div>

        <!-- Sidebar with widgets -->
        <div class="col-lg-4">
          <div class="widget">
            <h2>About Me</h2>
            <?php if (isset($_SESSION['user_id'])): 
                $userId = $_SESSION['user_id'];
                $sql = "SELECT * FROM Users WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':id' => $userId]);
                $user = $stmt->fetch();
            ?>
            <div class="about-me">
                <?php if (!empty($user['profilePicture'])): ?>
                <img src="<?php echo htmlspecialchars($user['profilePicture']); ?>" alt="Profile Picture" class="profile-picture mb-3">
                <?php else: ?>
                <img src="images/author.jpg" alt="Profile Picture" class="profile-picture mb-3">
                <?php endif; ?>
                <h3><?php echo htmlspecialchars($user['name']); ?></h3>
                <p><?php echo htmlspecialchars($user['bio'] ?? 'No bio available'); ?></p>
                <div class="social-links">
                    <a href="#" class="me-2"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="me-2"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="me-2"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>
            <?php else: ?>
            <div class="about-me">
                <img src="images/author.jpg" alt="Profile Picture" class="profile-picture mb-3">
                <h3>Jane Doe</h3>
                <p>Passionate writer and tech enthusiast. I love to explore the intersection of culture, history, and modern innovations. Join me on this journey of discovery and learning.</p>
                <div class="social-links">
                    <a href="#" class="me-2"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="me-2"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="me-2"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin"></i></a>
                </div>
                <p class="mt-3"><a href="login.php" class="btn btn-outline-primary">Login to View Your Profile</a></p>
            </div>
            <?php endif; ?>
          </div>

          <div class="widget">
            <h2>Editor's Picks</h2>
            <?php
            $sql = "SELECT p.*, u.name as author_name 
                    FROM Posts p 
                    JOIN Users u ON p.authorID = u.id 
                    ORDER BY p.createdAt DESC 
                    LIMIT 3";
            $stmt = $pdo->query($sql);
            $editorPicks = $stmt->fetchAll();

            foreach ($editorPicks as $pick):
                $date = new DateTime($pick['createdAt']);
            ?>
            <article class="card mb-4">
              <img src="<?php echo htmlspecialchars($pick['postImage']); ?>" alt="Post Thumbnail" class="w-100">
              <div class="post-info"><?php echo $date->format('d M Y'); ?></div>
              <h3><a href="article.php?id=<?php echo $pick['id']; ?>"><?php echo htmlspecialchars($pick['title']); ?></a></h3>
              <p><?php echo htmlspecialchars(substr($pick['content'], 0, 100)) . '...'; ?></p>
              <a class="read-more-btn" href="article.php?id=<?php echo $pick['id']; ?>">Read Full Post</a>
            </article>
            <?php endforeach; ?>
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
