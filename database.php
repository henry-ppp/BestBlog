<?php
$dbHost = 'localhost';
$dbUser = 'your_db_username';
$dbPass = 'your_db_password';
$dbName = 'bestblog';

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO("mysql:host=$dbHost;charset=utf8mb4", $dbUser, $dbPass, $options);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
$pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName`");
echo "Database '$dbName' created or already exists.<br>";

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass, $options);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$createUsersTable = "
CREATE TABLE IF NOT EXISTS Users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    profilePicture VARCHAR(255),
    bio TEXT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
";

$createPostsTable = "
CREATE TABLE IF NOT EXISTS Posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    postImage VARCHAR(255),
    authorID INT NOT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (authorID) REFERENCES Users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
";

$createCommentsTable = "
CREATE TABLE IF NOT EXISTS Comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    postID INT NOT NULL,
    userID INT NOT NULL,
    comment TEXT NOT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (postID) REFERENCES Posts(id) ON DELETE CASCADE,
    FOREIGN KEY (userID) REFERENCES Users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
";

$createLikesTable = "
CREATE TABLE IF NOT EXISTS Likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    postID INT NOT NULL,
    userID INT NOT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (postID) REFERENCES Posts(id) ON DELETE CASCADE,
    FOREIGN KEY (userID) REFERENCES Users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
";

try {
    $pdo->exec($createUsersTable);
    echo "Table Users created or already exists.<br>";

    $pdo->exec($createPostsTable);
    echo "Table Posts created or already exists.<br>";

    $pdo->exec($createCommentsTable);
    echo "Table Comments created or already exists.<br>";

    $pdo->exec($createLikesTable);
    echo "Table Likes created or already exists.<br>";
} catch (PDOException $e) {
    die("Table creation failed: " . $e->getMessage());
}

$stmt = $pdo->query("SELECT COUNT(*) FROM Users");
$userCount = $stmt->fetchColumn();

if ($userCount == 0) {
    $insertUserSQL = "INSERT INTO Users (name, email, password, role) VALUES (:name, :email, :password, :role)";
    $stmt = $pdo->prepare($insertUserSQL);

    $adminPassword = password_hash("adminpass", PASSWORD_DEFAULT);
    $stmt->execute([
        ':name' => 'Admin',
        ':email' => 'admin@example.com',
        ':password' => $adminPassword,
        ':role' => 'admin'
    ]);

    $user1Password = password_hash("henrypass", PASSWORD_DEFAULT);
    $stmt->execute([
        ':name' => 'Henry Pak',
        ':email' => 'henry@gmail.com',
        ':password' => $user1Password,
        ':role' => 'user'
    ]);

    $user2Password = password_hash("ataharpass", PASSWORD_DEFAULT);
    $stmt->execute([
        ':name' => 'Atahar Imtiaz',
        ':email' => 'imtiaznasif@gmail.com',
        ':password' => $user2Password,
        ':role' => 'user'
    ]);

    $user1Password = password_hash("willpass", PASSWORD_DEFAULT);
    $stmt->execute([
        ':name' => 'Will Tilden',
        ':email' => 'abcdefg@example.com',
        ':password' => $user1Password,
        ':role' => 'user'
    ]);

    echo "Mock demo users inserted.<br>";
} else {
    echo "Users table already contains data.<br>";
}

echo "Database setup complete.";
?>