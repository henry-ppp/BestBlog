<?php
require_once 'database.php';

try {
    // Check if we have an admin user
    $stmt = $pdo->query("SELECT id FROM Users WHERE role = 'admin' LIMIT 1");
    $adminId = $stmt->fetchColumn();

    if (!$adminId) {
        die("No admin user found. Please run setup.php first.");
    }

    // Sample articles
    $articles = [
        [
            'title' => 'Understanding the Role of AI in Modern Times',
            'content' => 'Artificial Intelligence (AI) has become an integral part of our daily lives, influencing everything from how we shop to how we work. This article explores the current state of AI technology and its implications for the future of automation. We\'ll look at real-world applications, ethical considerations, and what the future might hold for AI development.',
            'postImage' => 'images/post/post-1.jpg'
        ],
        [
            'title' => 'Exploring the Secrets of the Ancient World',
            'content' => 'The ancient world holds countless mysteries waiting to be uncovered. From the pyramids of Egypt to the lost city of Atlantis, this article takes you on a journey through time to explore some of the most fascinating archaeological discoveries of recent years. We\'ll examine how modern technology is helping us understand our ancestors better than ever before.',
            'postImage' => 'images/post/post-2.jpg'
        ],
        [
            'title' => 'New Travel Guidelines in Europe',
            'content' => 'With the world slowly returning to normal after the pandemic, travel regulations in Europe have undergone significant changes. This comprehensive guide covers everything you need to know about current travel requirements, including visa information, health protocols, and must-visit destinations. Stay informed and plan your next European adventure with confidence.',
            'postImage' => 'images/post/post-9.jpg'
        ]
    ];

    // Insert articles
    $sql = "INSERT INTO Posts (title, content, postImage, authorID) VALUES (:title, :content, :postImage, :authorID)";
    $stmt = $pdo->prepare($sql);

    foreach ($articles as $article) {
        $stmt->execute([
            ':title' => $article['title'],
            ':content' => $article['content'],
            ':postImage' => $article['postImage'],
            ':authorID' => $adminId
        ]);
        echo "Inserted article: " . $article['title'] . "<br>";
    }

    echo "Sample data inserted successfully!";
} catch (PDOException $e) {
    die("Error inserting sample data: " . $e->getMessage());
}
?> 