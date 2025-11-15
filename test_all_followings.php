<?php
// Check all followings in the database
$host = 'localhost';
$dbname = 'hobbies';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== CHECKING ALL FOLLOWINGS IN DATABASE ===\n\n";
    
    // Get count of all follow records
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM tb_follow");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total tb_follow records: {$result['total']}\n";
    
    // Get count by status
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM tb_follow GROUP BY status");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "\nBreakdown by status:\n";
    foreach ($results as $row) {
        $statusLabel = $row['status'] == 1 ? 'following' : 'unfollowed';
        echo "  status={$row['status']} ({$statusLabel}): {$row['count']}\n";
    }
    
    // Get count by flag
    $stmt = $pdo->query("SELECT flag, COUNT(*) as count FROM tb_follow GROUP BY flag");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "\nBreakdown by flag:\n";
    foreach ($results as $row) {
        $flagLabel = $row['flag'] == 1 ? 'active' : 'deleted';
        echo "  flag={$row['flag']} ({$flagLabel}): {$row['count']}\n";
    }
    
    // Get active followings (status=1, flag=1)
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM tb_follow WHERE status=1 AND flag=1");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "\nActive followings (status=1, flag=1): {$result['total']}\n\n";
    
    if ($result['total'] > 0) {
        echo "=== USERS WITH ACTIVE FOLLOWINGS ===\n\n";
        
        $stmt = $pdo->query("
            SELECT id_user, COUNT(*) as following_count 
            FROM tb_follow 
            WHERE status=1 AND flag=1 
            GROUP BY id_user 
            ORDER BY following_count DESC
            LIMIT 10
        ");
        $usersWithFollowings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($usersWithFollowings as $userFollow) {
            $idUser = $userFollow['id_user'];
            $count = $userFollow['following_count'];
            
            // Get user details
            $stmt2 = $pdo->prepare("SELECT fullname, email FROM tb_user WHERE id_user = ?");
            $stmt2->execute([$idUser]);
            $user = $stmt2->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                echo "User: {$user['fullname']} (ID: {$idUser})\n";
                echo "  Following: {$count} users\n";
                
                // Get the followings for this user
                $stmt3 = $pdo->prepare("SELECT * FROM tb_follow WHERE id_user = ? AND status=1 AND flag=1 ORDER BY date_created DESC");
                $stmt3->execute([$idUser]);
                $followings = $stmt3->fetchAll(PDO::FETCH_ASSOC);
                
                echo "  List of people being followed:\n";
                foreach ($followings as $following) {
                    $idUserTo = $following['id_user_to'];
                    
                    // Try with INNER JOIN (the current query)
                    $stmt4 = $pdo->query("SELECT b.*, c.token_fcm FROM tb_user b, tb_install c 
                        WHERE b.id_install=c.id_install 
                        AND b.id_user='$idUserTo'");
                    $innerJoinResult = $stmt4->fetchAll(PDO::FETCH_ASSOC);
                    
                    // Try with LEFT JOIN
                    $stmt5 = $pdo->query("SELECT b.*, c.token_fcm FROM tb_user b 
                        LEFT JOIN tb_install c ON b.id_install=c.id_install 
                        WHERE b.id_user='$idUserTo'");
                    $leftJoinResult = $stmt5->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (count($innerJoinResult) > 0) {
                        echo "    ✓ {$innerJoinResult[0]['fullname']} (ID: {$idUserTo})\n";
                    } else if (count($leftJoinResult) > 0) {
                        echo "    ✗ {$leftJoinResult[0]['fullname']} (ID: {$idUserTo}) - NOT showing due to missing/invalid id_install\n";
                        echo "      id_install: '" . ($leftJoinResult[0]['id_install'] ?? 'NULL') . "'\n";
                    } else {
                        echo "    ✗ User ID {$idUserTo} - NOT FOUND in tb_user at all!\n";
                    }
                }
                echo "\n";
            }
        }
        
        echo "\n=== ROOT CAUSE IDENTIFIED ===\n";
        echo "The issue is in FollowModel.php line 150-152:\n";
        echo "The query uses INNER JOIN (implicit with comma syntax):\n";
        echo "  SELECT b.*, c.token_fcm FROM tb_user b, tb_install c\n";
        echo "  WHERE b.id_install=c.id_install AND b.id_user='...'\n\n";
        echo "This INNER JOIN requires BOTH conditions:\n";
        echo "  1. User exists in tb_user\n";
        echo "  2. User has a valid id_install that exists in tb_install\n\n";
        echo "If either is missing, the user won't appear in the followings list!\n\n";
        echo "SOLUTION: Change to LEFT JOIN to include users even if id_install is missing:\n";
        echo "  SELECT b.*, c.token_fcm FROM tb_user b\n";
        echo "  LEFT JOIN tb_install c ON b.id_install=c.id_install\n";
        echo "  WHERE b.id_user='...'\n";
    } else {
        echo "No active followings found in the database.\n";
        echo "This means either:\n";
        echo "1. No users have followed anyone yet\n";
        echo "2. All followings have been unfollowed (status=0)\n";
        echo "3. All followings have been deleted (flag=0)\n";
    }
    
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
}

echo "\n=== DONE ===\n";
