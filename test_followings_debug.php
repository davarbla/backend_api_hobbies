<?php
// Direct MySQL connection test for followings
$host = 'localhost';
$dbname = 'hobbies';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== TESTING FOLLOWINGS ===\n\n";
    
    // Get all users first to test with
    $stmt = $pdo->query("SELECT * FROM tb_user ORDER BY date_created DESC LIMIT 5");
    $allUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Sample users in database:\n";
    foreach ($allUsers as $user) {
        echo "- ID: {$user['id_user']}, Name: {$user['fullname']}, Email: {$user['email']}\n";
    }
    echo "\n";
    
    if (count($allUsers) > 0) {
        $testUser = $allUsers[0];
        $idUser = $testUser['id_user'];
        
        echo "Testing with user: {$testUser['fullname']} (ID: {$idUser})\n\n";
        
        // Check tb_follow table directly - WHERE I AM THE FOLLOWER
        echo "=== RAW tb_follow RECORDS (where I am following others) ===\n";
        $stmt = $pdo->prepare("SELECT * FROM tb_follow WHERE id_user = ? AND flag = 1 ORDER BY date_created DESC");
        $stmt->execute([$idUser]);
        $rawFollows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($rawFollows) > 0) {
            echo "Found " . count($rawFollows) . " follow records:\n\n";
            foreach ($rawFollows as $follow) {
                echo "Follow ID: {$follow['id_follow']}\n";
                echo "  id_user: {$follow['id_user']} (me - the follower)\n";
                echo "  id_user_to: {$follow['id_user_to']} (person I'm following)\n";
                echo "  status: {$follow['status']} (0=unfollowed, 1=following)\n";
                echo "  flag: {$follow['flag']}\n";
                echo "  date_created: {$follow['date_created']}\n";
                
                // Check if the person I'm following exists
                $stmt2 = $pdo->prepare("SELECT id_user, fullname, email, id_install FROM tb_user WHERE id_user = ?");
                $stmt2->execute([$follow['id_user_to']]);
                $followedUser = $stmt2->fetch(PDO::FETCH_ASSOC);
                
                if ($followedUser) {
                    echo "  ✓ Followed user EXISTS: {$followedUser['fullname']}\n";
                    echo "    id_install: {$followedUser['id_install']}\n";
                    
                    // Check if install exists
                    if (!empty($followedUser['id_install'])) {
                        $stmt3 = $pdo->prepare("SELECT id_install, token_fcm FROM tb_install WHERE id_install = ?");
                        $stmt3->execute([$followedUser['id_install']]);
                        $install = $stmt3->fetch(PDO::FETCH_ASSOC);
                        if ($install) {
                            echo "    ✓ Install record EXISTS\n";
                        } else {
                            echo "    ✗ Install record NOT FOUND!\n";
                        }
                    } else {
                        echo "    ⚠ id_install is NULL/empty\n";
                    }
                } else {
                    echo "  ✗ Followed user NOT FOUND in tb_user!\n";
                }
                echo "\n";
            }
        } else {
            echo "NO FOLLOW RECORDS FOUND for this user\n";
            echo "The user is not following anyone (or all followings have flag=0)\n\n";
        }
        
        // Test the actual query used in getAllFollowingByIdUser
        echo "\n=== TESTING ACTUAL API QUERY ===\n";
        $limit = 10;
        $offset = 0;
        $status = 1;
        $flag = 1;
        $getlimit = "$offset,$limit";
        
        $sql = "SELECT a.* FROM tb_follow a 
            WHERE a.status='$status' 
            AND a.id_user='$idUser'
            AND a.flag='$flag'
            ORDER BY a.date_created DESC
            LIMIT $getlimit";
        
        echo "SQL Query:\n$sql\n\n";
        
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Query returned " . count($results) . " records\n\n";
        
        if (count($results) > 0) {
            foreach ($results as $row) {
                echo "Processing follow record {$row['id_follow']}...\n";
                echo "  Looking for user: {$row['id_user_to']}\n";
                
                $stmt2 = $pdo->query("SELECT b.*, c.token_fcm FROM tb_user b, tb_install c 
                    WHERE b.id_install=c.id_install 
                    AND b.id_user='{$row['id_user_to']}'");
                $result1 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($result1) > 0) {
                    echo "  ✓ User found: {$result1[0]['fullname']}\n";
                } else {
                    echo "  ✗ User NOT FOUND with the INNER JOIN query!\n";
                    echo "  This is the problem - the INNER JOIN requires both tb_user AND tb_install to exist\n";
                    
                    // Try with LEFT JOIN
                    $stmt3 = $pdo->query("SELECT b.*, c.token_fcm FROM tb_user b 
                        LEFT JOIN tb_install c ON b.id_install=c.id_install 
                        WHERE b.id_user='{$row['id_user_to']}'");
                    $result2 = $stmt3->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (count($result2) > 0) {
                        echo "  ✓ BUT user DOES exist with LEFT JOIN: {$result2[0]['fullname']}\n";
                        echo "  Issue: User's id_install is '{$result2[0]['id_install']}'\n";
                    }
                }
                echo "\n";
            }
        }
    }
    
    echo "\n=== ANALYSIS ===\n";
    echo "If some users are not showing:\n";
    echo "1. Check if tb_follow records have status=1 (0 means unfollowed)\n";
    echo "2. Check if tb_follow records have flag=1 (0 means deleted)\n";
    echo "3. Check if the followed users exist in tb_user\n";
    echo "4. Check if the followed users have a valid id_install\n";
    echo "5. Check if the install records exist in tb_install\n";
    echo "   -> The query uses INNER JOIN which requires BOTH tables to have matching records\n";
    echo "   -> If id_install is NULL or the install record doesn't exist, the user won't appear!\n";
    
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
}

echo "\n=== DONE ===\n";
