<?php $registered = isset($_GET['registered']) && $_GET['registered'] == '1'; ?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <title>Login - BestBlog</title>
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1, maximum-scale=5"
    />
    <link rel="icon" href="images/favicon.png" type="image/x-icon" />

    <!-- Google Fonts -->
    <link
      href="https://fonts.googleapis.com/css2?family=Neuton:wght@700&family=Work+Sans:wght@400;500;600;700&display=swap"
      rel="stylesheet"
    />

    <!-- Bootstrap & Main Styles -->
    <link rel="stylesheet" href="plugins/bootstrap/bootstrap.min.css" />
    <link rel="stylesheet" href="css/style.css" />
  </head>
  <body class="d-flex flex-column min-vh-100">
    <div class="wrapper flex-grow-1">
      <header class="navigation">
        <div class="container">
          <nav class="navbar navbar-expand-lg navbar-light">
            <a class="navbar-brand" href="index.html">BestBlog</a>
          </nav>
        </div>
      </header>

      <main>
        <section
          class="section container"
          style="max-width: 500px; margin: 4rem auto"
        >
          <h2 class="section-title text-center mb-4">Login</h2>

          <?php if ($registered): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              Registration successful! Please log in.
            </div>
          <?php endif; ?>
          <?php if (isset($_GET['error']) && $_GET['error'] == '1'): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              Invalid email or password.
            </div>
          <?php endif; ?>


          <form action="login_handler.php" method="POST">
            <div class="mb-3">
              <label for="email" class="form-label">Email address</label>
              <input
                type="email"
                class="form-control"
                id="email"
                name="email"
                placeholder="Enter your email"
                required
              />
            </div>

            <div class="mb-3">
              <label for="password" class="form-label">Password</label>
              <input
                type="password"
                class="form-control"
                id="password"
                name="password"
                placeholder="Enter your password"
                required
              />
            </div>

            <div class="d-grid">
              <button type="submit" class="btn btn-primary">Login</button>
            </div>
          </form>

          <p class="text-center mt-3">
            Don’t have an account? <a href="register.html">Sign up</a>
          </p>
        </section>
      </main>
    </div>

    <footer class="bg-dark text-white text-center py-3 mt-auto">
      <div class="container">
        <p class="mb-0 text-white">
          &copy; 2025 BestBlog. All rights reserved.
        </p>
      </div>
    </footer>

    <script src="plugins/jquery/jquery.min.js"></script>
    <script src="plugins/bootstrap/bootstrap.min.js"></script>
  </body>
</html>
