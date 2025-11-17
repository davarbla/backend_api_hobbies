<?php
/**
 * Fix country data that's causing JSON parsing errors
 * Issue: Some country values may be stored incorrectly causing "country":FR instead of "country":"FR"
 */

echo "<h1>Fix Country Data in Database</h1>";
echo "<hr>";

// Database configuration - update these
$host = 'localhost';
$dbname = 'hobbies';
$username = 'root';
$password = '';

try {
    // Connect to database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>✓ Database Connected</h2>";
    echo "<hr>";
    
    // Check tb_user table for country issues
    echo "<h2>Checking tb_user table...</h2>";
    
    $stmt = $pdo->query("SELECT id_user, fullname, email, country, location FROM tb_user ORDER BY id_user DESC LIMIT 50");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Total users checked:</strong> " . count($users) . "</p>";
    
    $issuesFound = 0;
    $fixedCount = 0;
    
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr>";
    echo "<th>ID</th>";
    echo "<th>Fullname</th>";
    echo "<th>Email</th>";
    echo "<th>Country</th>";
    echo "<th>Location</th>";
    echo "<th>Status</th>";
    echo "</tr>";
    
    foreach ($users as $user) {
        $status = "OK";
        $rowColor = "";
        
        // Check if country is NULL, empty, or potentially problematic
        if ($user['country'] === null) {
            $status = "NULL - Setting to ZZ";
            $rowColor = 'style="background-color: #fff3cd;"';
            $issuesFound++;
            
            // Fix: Set to ZZ (international)
            $updateStmt = $pdo->prepare("UPDATE tb_user SET country = 'ZZ' WHERE id_user = ?");
            $updateStmt->execute([$user['id_user']]);
            $fixedCount++;
            
        } elseif (trim($user['country']) === '') {
            $status = "EMPTY - Setting to ZZ";
            $rowColor = 'style="background-color: #fff3cd;"';
            $issuesFound++;
            
            // Fix: Set to ZZ (international)
            $updateStmt = $pdo->prepare("UPDATE tb_user SET country = 'ZZ' WHERE id_user = ?");
            $updateStmt->execute([$user['id_user']]);
            $fixedCount++;
            
        } elseif (!ctype_alpha($user['country']) || strlen($user['country']) > 2) {
            $status = "INVALID FORMAT - Setting to ZZ";
            $rowColor = 'style="background-color: #f8d7da;"';
            $issuesFound++;
            
            // Fix: Set to ZZ (international)
            $updateStmt = $pdo->prepare("UPDATE tb_user SET country = 'ZZ' WHERE id_user = ?");
            $updateStmt->execute([$user['id_user']]);
            $fixedCount++;
        }
        
        echo "<tr $rowColor>";
        echo "<td>" . htmlspecialchars($user['id_user']) . "</td>";
        echo "<td>" . htmlspecialchars(substr($user['fullname'] ?? '', 0, 20)) . "</td>";
        echo "<td>" . htmlspecialchars(substr($user['email'] ?? '', 0, 25)) . "</td>";
        echo "<td><strong>" . htmlspecialchars($user['country'] ?? 'NULL') . "</strong></td>";
        echo "<td>" . htmlspecialchars(substr($user['location'] ?? '', 0, 20)) . "</td>";
        echo "<td>" . $status . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<hr>";
    echo "<h2>Summary</h2>";
    echo "<ul>";
    echo "<li><strong>Issues Found:</strong> $issuesFound</li>";
    echo "<li><strong>Records Fixed:</strong> $fixedCount</li>";
    echo "</ul>";
    
    // Check tb_category table
    echo "<hr>";
    echo "<h2>Checking tb_category table...</h2>";
    
    $stmt = $pdo->query("SELECT id_category, title, country FROM tb_category ORDER BY id_category DESC LIMIT 50");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Total categories checked:</strong> " . count($categories) . "</p>";
    
    $catIssuesFound = 0;
    $catFixedCount = 0;
    
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr>";
    echo "<th>ID</th>";
    echo "<th>Title</th>";
    echo "<th>Country</th>";
    echo "<th>Status</th>";
    echo "</tr>";
    
    foreach ($categories as $cat) {
        $status = "OK";
        $rowColor = "";
        
        // Check if country is NULL or empty - set to ZZ (international)
        if ($cat['country'] === null || trim($cat['country']) === '') {
            $status = "NULL/EMPTY - Setting to ZZ";
            $rowColor = 'style="background-color: #fff3cd;"';
            $catIssuesFound++;
            
            // Fix: Set to ZZ (international)
            $updateStmt = $pdo->prepare("UPDATE tb_category SET country = 'ZZ' WHERE id_category = ?");
            $updateStmt->execute([$cat['id_category']]);
            $catFixedCount++;
        }
        
        echo "<tr $rowColor>";
        echo "<td>" . htmlspecialchars($cat['id_category']) . "</td>";
        echo "<td>" . htmlspecialchars(substr($cat['title'] ?? '', 0, 30)) . "</td>";
        echo "<td><strong>" . htmlspecialchars($cat['country'] ?? 'NULL') . "</strong></td>";
        echo "<td>" . $status . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<hr>";
    echo "<h2>Category Summary</h2>";
    echo "<ul>";
    echo "<li><strong>Issues Found:</strong> $catIssuesFound</li>";
    echo "<li><strong>Records Fixed:</strong> $catFixedCount</li>";
    echo "</ul>";
    
    // Check tb_post table
    echo "<hr>";
    echo "<h2>Checking tb_post table...</h2>";
    
    $stmt = $pdo->query("SELECT id_post, title, country FROM tb_post ORDER BY id_post DESC LIMIT 50");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Total posts checked:</strong> " . count($posts) . "</p>";
    
    $postIssuesFound = 0;
    $postFixedCount = 0;
    
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr>";
    echo "<th>ID</th>";
    echo "<th>Title</th>";
    echo "<th>Country</th>";
    echo "<th>Status</th>";
    echo "</tr>";
    
    foreach ($posts as $post) {
        $status = "OK";
        $rowColor = "";
        
        // Check if country is NULL or empty
        if ($post['country'] === null || trim($post['country']) === '') {
            $status = "NULL/EMPTY - Setting to ZZ";
            $rowColor = 'style="background-color: #fff3cd;"';
            $postIssuesFound++;
            
            // Fix: Set to ZZ (international)
            $updateStmt = $pdo->prepare("UPDATE tb_post SET country = 'ZZ' WHERE id_post = ?");
            $updateStmt->execute([$post['id_post']]);
            $postFixedCount++;
        }
        
        echo "<tr $rowColor>";
        echo "<td>" . htmlspecialchars($post['id_post']) . "</td>";
        echo "<td>" . htmlspecialchars(substr($post['title'] ?? '', 0, 30)) . "</td>";
        echo "<td><strong>" . htmlspecialchars($post['country'] ?? 'NULL') . "</strong></td>";
        echo "<td>" . $status . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<hr>";
    echo "<h2>Post Summary</h2>";
    echo "<ul>";
    echo "<li><strong>Issues Found:</strong> $postIssuesFound</li>";
    echo "<li><strong>Records Fixed:</strong> $postFixedCount</li>";
    echo "</ul>";
    
    // Final summary
    echo "<hr>";
    echo "<div style='background-color: #d4edda; padding: 20px; border: 2px solid #28a745;'>";
    echo "<h2>✓ COMPLETE</h2>";
    $totalIssues = $issuesFound + $catIssuesFound + $postIssuesFound;
    $totalFixed = $fixedCount + $catFixedCount + $postFixedCount;
    echo "<p><strong>Total Issues Found:</strong> $totalIssues</p>";
    echo "<p><strong>Total Records Fixed:</strong> $totalFixed</p>";
    echo "<h3>Next Steps:</h3>";
    echo "<ol>";
    echo "<li>Restart your Flutter app</li>";
    echo "<li>Pull to refresh on the home page</li>";
    echo "<li>The JSON parsing error should be fixed</li>";
    echo "</ol>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div style='background-color: #f8d7da; padding: 15px; border: 2px solid #dc3545;'>";
    echo "<h3>❌ Database Error</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Update the database credentials at the top of this file:</strong></p>";
    echo "<ul>";
    echo "<li>Host: $host</li>";
    echo "<li>Database: $dbname</li>";
    echo "<li>Username: $username</li>";
    echo "</ul>";
    echo "</div>";
}
?>
