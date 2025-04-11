<?php

session_start();

require_once 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Both email and password are required.";
    } else {
        try {
            $sql = "SELECT * FROM Users WHERE email = :email";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];

                header("Location: index.php");
                exit;
            } else {
                $error = "Invalid email or password.";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Login - BestBlog</title>
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
      </nav>
    </div>
  </header>

  <main>
    <section class="section container">
      <div class="row">
        <div class="col-lg-6 mx-auto">
          <h1 class="mb-4">Login</h1>
          
          <?php if (isset($_GET['registered']) && $_GET['registered'] == 1): ?>
            <div class="alert alert-success">Registration successful! Please login with your credentials.</div>
          <?php endif; ?>
          
          <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
          <?php endif; ?>

          <form action="login.php" method="POST">
            <div class="mb-3">
              <label for="email" class="form-label">Email address</label>
              <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
            </div>

            <div class="mb-3">
              <label for="password" class="form-label">Password</label>
              <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
            </div>

            <div class="d-grid">
              <button type="submit" class="btn btn-primary">Login</button>
            </div>
          </form>

          <p class="text-center mt-3">Don't have an account? <a href="register.php">Sign up</a></p>
        </div>
      </div>
    </section>
  </main>

  <footer class="bg-dark mt-5">
    <div class="container section text-center text-white">
      <p>&copy; 2025 BestBlog. All rights reserved.</p>
    </div>
  </footer>

  <!-- JS Plugins -->
  <script src="plugins/jquery/jquery.min.js"></script>
  <script src="plugins/bootstrap/bootstrap.min.js"></script>
</body>
</html>