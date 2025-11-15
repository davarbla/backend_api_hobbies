<?php
// Test the fixed followings query
$host = 'localhost';
$dbname = 'hobbies';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== TESTING FIXED FOLLOWINGS QUERY ===\n\n";
    
    // Get users with followings
    $stmt = $pdo->query("
        SELECT id_user, fullname 
        FROM tb_user 
        WHERE id_user IN (SELECT DISTINCT id_user FROM tb_follow WHERE status=1 AND flag=1)
        LIMIT 3
    ");
    $usersWithFollowings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($usersWithFollowings as $user) {
        $idUser = $user['id_user'];
        $fullname = $user['fullname'];
        
        echo "Testing with user: $fullname (ID: $idUser)\n";
        echo str_repeat("=", 60) . "\n";
        
        // Get followings using the OLD INNER JOIN query
        $stmt2 = $pdo->query("
            SELECT a.* FROM tb_follow a 
            WHERE a.status='1' 
            AND a.id_user='$idUser'
            AND a.flag='1'
            ORDER BY a.date_created DESC
        ");
        $followRecords = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Found " . count($followRecords) . " follow records\n\n";
        
        $innerJoinCount = 0;
        $leftJoinCount = 0;
        
        foreach ($followRecords as $row) {
            $idUserTo = $row['id_user_to'];
            
            // OLD QUERY (INNER JOIN - implicit comma syntax)
            $stmt3 = $pdo->query("
                SELECT b.*, c.token_fcm FROM tb_user b, tb_install c 
                WHERE b.id_install=c.id_install 
                AND b.id_user='$idUserTo'
            ");
            $innerJoinResult = $stmt3->fetchAll(PDO::FETCH_ASSOC);
            
            // NEW QUERY (LEFT JOIN)
            $stmt4 = $pdo->query("
                SELECT b.*, c.token_fcm FROM tb_user b 
                LEFT JOIN tb_install c ON b.id_install=c.id_install 
                WHERE b.id_user='$idUserTo'
            ");
            $leftJoinResult = $stmt4->fetchAll(PDO::FETCH_ASSOC);
            
            echo "  Following user ID: $idUserTo\n";
            
            if (!empty($innerJoinResult)) {
                $innerJoinCount++;
                echo "    OLD (INNER JOIN): ✓ Found - {$innerJoinResult[0]['fullname']}\n";
            } else {
                echo "    OLD (INNER JOIN): ✗ NOT FOUND\n";
            }
            
            if (!empty($leftJoinResult)) {
                $leftJoinCount++;
                echo "    NEW (LEFT JOIN):  ✓ Found - {$leftJoinResult[0]['fullname']}\n";
                if (empty($leftJoinResult[0]['id_install']) || $leftJoinResult[0]['id_install'] === null) {
                    echo "                      ⚠ Note: id_install is NULL/empty\n";
                }
            } else {
                echo "    NEW (LEFT JOIN):  ✗ NOT FOUND\n";
            }
            
            echo "\n";
        }
        
        echo "Summary for $fullname:\n";
        echo "  OLD query (INNER JOIN): $innerJoinCount users returned\n";
        echo "  NEW query (LEFT JOIN):  $leftJoinCount users returned\n";
        
        if ($leftJoinCount > $innerJoinCount) {
            echo "  ✓ FIX SUCCESSFUL: LEFT JOIN returns " . ($leftJoinCount - $innerJoinCount) . " more user(s)\n";
        } else if ($leftJoinCount == $innerJoinCount) {
            echo "  ✓ Both queries return same results (all users have valid id_install)\n";
        }
        
        echo "\n\n";
    }
    
    echo "=== CONCLUSION ===\n";
    echo "The LEFT JOIN fix ensures that users appear in the followings list\n";
    echo "even if they have a missing or invalid id_install field.\n";
    echo "\nThe fix is backward compatible - it still returns all users that\n";
    echo "the old INNER JOIN query returned, plus any additional users that\n";
    echo "were previously excluded due to id_install issues.\n";
    
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
}

echo "\n=== DONE ===\n";
