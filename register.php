<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $email     = trim($_POST['email']);
    $bio       = trim($_POST['bio'] ?? '');
    $password  = $_POST['password'];
    $confirm   = $_POST['confirm_password'];

    if ($password !== $confirm) {
        die("Passwords do not match.");
    }

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        die("Email already registered.");
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insert into users table
    $stmt = $pdo->prepare("INSERT INTO users (email, password_hash) VALUES (?, ?)");
    $stmt->execute([$email, $password_hash]);

    $user_id = $pdo->lastInsertId();

    // Handle profile picture upload
    $profile_pic_filename = null;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['profile_picture']['tmp_name'];
        $original_name = basename($_FILES['profile_picture']['name']);
        $profile_pic_filename = "uploads/" . uniqid() . '_' . $original_name;

        move_uploaded_file($tmp_name, $profile_pic_filename);
    }

    // Insert into profiles table
    $stmt = $pdo->prepare("INSERT INTO profiles (user_id, full_name, bio, profile_picture) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $full_name, $bio, $profile_pic_filename]);

    header("Location: login.php?registered=1");
    exit();
}
?>
