<?php
// Quick check to see if followings exist and why they might not be showing
$host = 'localhost';
$dbname = 'hobbies';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== CHECKING YOUR FOLLOWINGS ===\n\n";
    
    // Get all users who are following someone
    $stmt = $pdo->query("
        SELECT u.id_user, u.fullname, u.email, 
               COUNT(f.id_follow) as following_count,
               GROUP_CONCAT(f.id_user_to) as following_ids
        FROM tb_user u
        LEFT JOIN tb_follow f ON u.id_user = f.id_user AND f.status = 1 AND f.flag = 1
        GROUP BY u.id_user
        HAVING following_count > 0
        ORDER BY following_count DESC
        LIMIT 10
    ");
    $usersWithFollowings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($usersWithFollowings) == 0) {
        echo "❌ NO USERS ARE FOLLOWING ANYONE\n";
        echo "This means no follow relationships exist in the database with status=1 and flag=1\n\n";
        echo "To test:\n";
        echo "1. Open the app\n";
        echo "2. Go to a user's profile\n";
        echo "3. Click the Follow button\n";
        echo "4. Return to home page\n";
        echo "5. Check the Following/Favorites section\n";
    } else {
        echo "Found " . count($usersWithFollowings) . " users who are following others:\n\n";
        
        foreach ($usersWithFollowings as $user) {
            $idUser = $user['id_user'];
            $followingIds = explode(',', $user['following_ids']);
            
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            echo "USER: {$user['fullname']} (ID: {$idUser})\n";
            echo "Email: {$user['email']}\n";
            echo "Following: {$user['following_count']} user(s)\n";
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
            
            // Test the OLD query (INNER JOIN - what was causing the problem)
            echo "OLD QUERY (INNER JOIN - before fix):\n";
            $oldCount = 0;
            foreach ($followingIds as $idUserTo) {
                $stmt2 = $pdo->query("
                    SELECT b.fullname, b.id_install, c.id_install as install_exists 
                    FROM tb_user b, tb_install c 
                    WHERE b.id_install=c.id_install 
                    AND b.id_user='$idUserTo'
                ");
                $oldResult = $stmt2->fetchAll(PDO::FETCH_ASSOC);
                if (!empty($oldResult)) {
                    $oldCount++;
                    echo "  ✓ {$oldResult[0]['fullname']}\n";
                } else {
                    // Check if user exists but was excluded
                    $stmt3 = $pdo->query("SELECT fullname, id_install FROM tb_user WHERE id_user='$idUserTo'");
                    $userCheck = $stmt3->fetch(PDO::FETCH_ASSOC);
                    if ($userCheck) {
                        echo "  ✗ {$userCheck['fullname']} - EXCLUDED (id_install issue)\n";
                    } else {
                        echo "  ✗ User ID $idUserTo - USER DELETED\n";
                    }
                }
            }
            echo "Result: $oldCount out of {$user['following_count']} would appear\n\n";
            
            // Test the NEW query (LEFT JOIN - the fix)
            echo "NEW QUERY (LEFT JOIN - after fix):\n";
            $newCount = 0;
            foreach ($followingIds as $idUserTo) {
                $stmt4 = $pdo->query("
                    SELECT b.fullname, b.id_install, b.status 
                    FROM tb_user b 
                    LEFT JOIN tb_install c ON b.id_install=c.id_install 
                    WHERE b.id_user='$idUserTo'
                ");
                $newResult = $stmt4->fetchAll(PDO::FETCH_ASSOC);
                if (!empty($newResult)) {
                    $status = $newResult[0]['status'];
                    if ($status == 1) {
                        $newCount++;
                        echo "  ✓ {$newResult[0]['fullname']}\n";
                    } else {
                        echo "  ⚠ {$newResult[0]['fullname']} - User is deleted (status=$status)\n";
                    }
                } else {
                    echo "  ✗ User ID $idUserTo - USER DOES NOT EXIST\n";
                }
            }
            echo "Result: $newCount out of {$user['following_count']} will appear\n\n";
            
            if ($newCount > $oldCount) {
                echo "✅ FIX SUCCESSFUL: {$newCount} users will now appear (was $oldCount)\n";
            } else if ($newCount == $oldCount && $newCount > 0) {
                echo "✅ All {$newCount} users have valid data and will appear\n";
            } else {
                echo "⚠ Some followed users may be deleted or invalid\n";
            }
            echo "\n\n";
        }
        
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "INSTRUCTIONS:\n";
        echo "1. The backend fix has been applied\n";
        echo "2. Restart your Flutter app or pull to refresh on home page\n";
        echo "3. Check the Following/Favorites section\n";
        echo "4. Users you're following should now appear\n";
    }
    
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
}

echo "\n=== DONE ===\n";
