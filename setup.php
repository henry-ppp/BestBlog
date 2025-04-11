<?php
$dbHost = 'localhost';
$dbUser = 'hnry';
$dbPass = 'hnry';
$dbName = 'hnry';

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO("mysql:host=$dbHost;charset=utf8mb4", $dbUser, $dbPass, $options);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName`");
    echo "Database '$dbName' created or already exists.<br>";
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

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

// Insert example articles
try {
    // Get the admin user ID
    $sql = "SELECT id FROM Users WHERE email = 'admin@example.com'";
    $stmt = $pdo->query($sql);
    $adminId = $stmt->fetchColumn();

    if ($adminId) {
        // Check if articles already exist
        $stmt = $pdo->query("SELECT COUNT(*) FROM Posts");
        $postCount = $stmt->fetchColumn();

        if ($postCount == 0) {
            // Insert articles
            $articles = [
                [
                    'title' => 'Understanding the Role of AI in Modern Times',
                    'content' => 'Artificial Intelligence (AI) is revolutionizing industries, from healthcare to finance. This article explores how AI influences our daily lives and its implications for the future.

AI-powered automation is streamlining workflows, improving efficiency, and even driving scientific breakthroughs. Machine learning algorithms are helping doctors diagnose diseases more accurately, while natural language processing is making customer service more efficient.

The ethical implications of AI are also significant. As AI systems become more autonomous, questions arise about accountability, privacy, and the potential impact on employment. It\'s crucial that we develop AI responsibly, with proper safeguards and regulations in place.

Looking ahead, AI will continue to transform our world in ways we can\'t yet fully predict. The key is to ensure that this transformation benefits humanity as a whole, while mitigating potential risks and challenges.',
                    'postImage' => 'images/post/post-1.jpg',
                    'authorID' => $adminId
                ],
                [
                    'title' => 'Exploring the Secrets of the Ancient World',
                    'content' => 'A journey through the ancient civilizations that shaped our modern world.

From the pyramids of Egypt to the temples of Greece, ancient civilizations have left behind incredible architectural marvels that continue to fascinate us today. These structures not only showcase the engineering prowess of our ancestors but also provide valuable insights into their cultures, beliefs, and daily lives.

Archaeological discoveries continue to shed new light on these ancient societies. Recent excavations have revealed sophisticated urban planning, advanced medical knowledge, and complex social structures that challenge our assumptions about the past.

Understanding these ancient civilizations helps us appreciate the foundations of our modern world. Many of our current political systems, philosophical ideas, and technological innovations have roots in these early societies.',
                    'postImage' => 'images/post/post-2.jpg',
                    'authorID' => $adminId
                ],
                [
                    'title' => 'New Travel Guidelines in Europe',
                    'content' => 'Stay up to date with the latest travel regulations in the EU.

The European Union has implemented new travel guidelines to ensure safe and smooth travel across member states. These guidelines include standardized health documentation, digital COVID certificates, and updated entry requirements.

Travelers should be aware of the following key points:
- Digital COVID certificates are now accepted across all EU member states
- Some countries may require additional testing or quarantine periods
- Health insurance coverage is mandatory for all travelers
- Face masks are still required in certain public spaces

These measures aim to balance public health concerns with the need to revive the tourism industry. Travelers are encouraged to check the specific requirements of their destination country before departure.',
                    'postImage' => 'images/post/post-9.jpg',
                    'authorID' => $adminId
                ]
            ];

            $sql = "INSERT INTO Posts (title, content, postImage, authorID) VALUES (:title, :content, :postImage, :authorID)";
            $stmt = $pdo->prepare($sql);

            foreach ($articles as $article) {
                $stmt->execute($article);
                echo "Inserted article: " . $article['title'] . "<br>";
            }
            echo "Example articles inserted successfully.<br>";
        } else {
            echo "Articles already exist in the database.<br>";
        }
    }
} catch (PDOException $e) {
    echo "Error inserting articles: " . $e->getMessage() . "<br>";
}

echo "Database setup complete.";
?> 