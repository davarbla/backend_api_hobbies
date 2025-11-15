<?php
// Directly test what happens when user 30 tries to follow user 2  
$host = 'localhost';
$dbname = 'hobbies';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $idUser = 30; // pitie  
    $idUserTo = 2; // Erhacorpdotcom
    
    echo "=== SIMULATING FOLLOW ACTION ===\n";
    echo "User $idUser (pitie) trying to follow User $idUserTo (Erhacorpdotcom)\n\n";
    
    // Check existing follow
    echo "BEFORE:\n";
    $stmt = $pdo->prepare("SELECT * FROM tb_follow WHERE id_user = ? AND id_user_to = ? AND flag = 1");
    $stmt->execute([$idUser, $idUserTo]);
    $before = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($before) {
        echo "  Existing record: status={$before['status']}, counter_follow={$before['counter_follow']}\n";
    } else {
        echo "  No existing record\n";
    }
    
    // Simulate the do_follow logic
    echo "\nEXECUTING:\n";
    if ($before && $before['id_follow']) {
        // Update existing
        $idFollow = $before['id_follow'];
        $newCounter = $before['counter_follow'] + 1;
        
        $stmt = $pdo->prepare("UPDATE tb_follow SET status=1, counter_follow=? WHERE id_follow=?");
        $stmt->execute([$newCounter, $idFollow]);
        echo "  Updated existing record $idFollow, counter=$newCounter\n";
    } else {
        // Insert new
        $stmt = $pdo->prepare("INSERT INTO tb_follow (id_user, id_user_to, counter_follow, flag, status) VALUES (?, ?, 1, 1, 1)");
        $stmt->execute([$idUser, $idUserTo]);
        echo "  Inserted new record, ID=" . $pdo->lastInsertId() . "\n";
    }
    
    // Update totals
    $pdo->exec("UPDATE tb_user SET total_following=total_following+1 WHERE id_user='$idUser'");
    $pdo->exec("UPDATE tb_user SET total_follower=total_follower+1 WHERE id_user='$idUserTo'");
    echo "  Updated user totals\n";
    
    // Check result
    echo "\nAFTER:\n";
    $stmt = $pdo->prepare("SELECT * FROM tb_follow WHERE id_user = ? AND id_user_to = ? AND flag = 1");
    $stmt->execute([$idUser, $idUserTo]);
    $after = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($after) {
        echo "  ✓ Record exists: ID={$after['id_follow']}, status={$after['status']}\n";
    } else {
        echo "  ✗ NO RECORD - INSERT FAILED!\n";
    }
    
    // Check user totals
    $stmt = $pdo->prepare("SELECT total_following, total_follower FROM tb_user WHERE id_user = ?");
    $stmt->execute([$idUser]);
    $userStats = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "  User $idUser totals: following={$userStats['total_following']}, followers={$userStats['total_follower']}\n";
    
    echo "\n✓ MANUAL TEST COMPLETE\n";
    echo "Now test via API: http://localhost:8000/follow/follow_unfollow\n";
    
} catch (PDOException $e) {
    echo "ERROR: {$e->getMessage()}\n";
}

echo "\n=== DONE ===\n";
