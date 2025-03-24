<?php

require_once 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($name) || empty($email) || empty($password)) {
        die("All fields are required.");
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (name, email, password) VALUES (:name, :email, :password)";
    $stmt = $pdo->prepare($sql);

    try {
        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':password' => $hashedPassword
        ]);
        echo "Registration successful! You can now log in.";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            echo "This email is already registered.";
        } else {
            echo "Error: " . $e->getMessage();
        }
    }
} else {
    ?>
    <!DOCTYPE html>
    <html>

    <head>
        <title>User Registration</title>
    </head>

    <body>
        <h2>Register</h2>
        <form action="register.php" method="POST">
            <label>Name:</label><br>
            <input type="text" name="name" required><br>
            <label>Email:</label><br>
            <input type="email" name="email" required><br>
            <label>Password:</label><br>
            <input type="password" name="password" required><br><br>
            <input type="submit" value="Register">
        </form>
    </body>

    </html>
    <?php
}
?>