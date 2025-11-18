<?php
try {
    $host = 'localhost';
    $dbname = 'hobbies';
    $username = 'root';
    $password = '';
    
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Count total posts
    $count = $db->query("SELECT COUNT(*) as count FROM tb_post WHERE status = 1")->fetch(PDO::FETCH_ASSOC);
    echo "✅ Total active posts: " . $count['count'] . "\n\n";
    
    // Show recent posts
    echo "📋 Recent posts (last 5):\n";
    $recentPosts = $db->query("
        SELECT p.id_post, p.title, p.id_user, p.id_category, p.date_created, p.status,
               u.fullname as user_name, c.title as category_name
        FROM tb_post p
        LEFT JOIN tb_user u ON p.id_user = u.id_user
        LEFT JOIN tb_category c ON p.id_category = c.id_category
        WHERE p.status = 1
        ORDER BY p.date_created DESC 
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($recentPosts) > 0) {
        foreach ($recentPosts as $post) {
            echo "  - [{$post['id_post']}] {$post['title']}\n";
            echo "    By: {$post['user_name']} | Category: {$post['category_name']} | Date: {$post['date_created']}\n";
        }
    } else {
        echo "  No posts found.\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}
