<?php
/**
 * Check for data integrity issues that could cause JSON parsing errors
 */

echo "=== Data Integrity Check ===\n\n";

$host = 'localhost';
$dbname = 'hobbies';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Database Connected\n\n";
    
    // Check for unquoted values in critical fields
    echo "Checking for potential JSON-breaking data...\n\n";
    
    // Check users table
    echo "1. Checking tb_user table:\n";
    $stmt = $pdo->query("SELECT id_user, fullname, email, country, location FROM tb_user WHERE country IS NULL OR country = '' OR location IS NULL LIMIT 10");
    $issues = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($issues) > 0) {
        echo "   ⚠ Found " . count($issues) . " users with NULL/empty values:\n";
        foreach ($issues as $user) {
            echo "     - User ID {$user['id_user']}: country={$user['country']}, location={$user['location']}\n";
        }
    } else {
        echo "   ✓ No issues found\n";
    }
    
    // Check for special characters that might break JSON
    echo "\n2. Checking for special characters in user data:\n";
    $stmt = $pdo->query("SELECT id_user, fullname, location FROM tb_user WHERE 
        fullname LIKE '%\"%' OR 
        fullname LIKE '%\\\\%' OR
        location LIKE '%\"%' OR 
        location LIKE '%\\\\%' 
        LIMIT 10");
    $specialChars = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($specialChars) > 0) {
        echo "   ⚠ Found " . count($specialChars) . " users with special characters:\n";
        foreach ($specialChars as $user) {
            echo "     - User ID {$user['id_user']}: {$user['fullname']}\n";
        }
    } else {
        echo "   ✓ No issues found\n";
    }
    
    // Check posts table
    echo "\n3. Checking tb_post table:\n";
    $stmt = $pdo->query("SELECT id_post, title, country FROM tb_post WHERE country IS NULL OR country = '' LIMIT 10");
    $postIssues = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($postIssues) > 0) {
        echo "   ⚠ Found " . count($postIssues) . " posts with NULL/empty country:\n";
        foreach ($postIssues as $post) {
            echo "     - Post ID {$post['id_post']}: {$post['title']}\n";
        }
    } else {
        echo "   ✓ No issues found\n";
    }
    
    // Check categories table
    echo "\n4. Checking tb_category table:\n";
    $stmt = $pdo->query("SELECT id_category, title, country FROM tb_category WHERE country IS NULL OR country = '' LIMIT 10");
    $catIssues = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($catIssues) > 0) {
        echo "   ⚠ Found " . count($catIssues) . " categories with NULL/empty country:\n";
        foreach ($catIssues as $cat) {
            echo "     - Category ID {$cat['id_category']}: {$cat['title']}\n";
        }
    } else {
        echo "   ✓ No issues found\n";
    }
    
    // Summary
    echo "\n" . str_repeat("=", 50) . "\n";
    $totalIssues = count($issues) + count($postIssues) + count($catIssues) + count($specialChars);
    
    if ($totalIssues == 0) {
        echo "✓ ALL CHECKS PASSED!\n";
        echo "\nData integrity is good. The JSON parsing should work correctly.\n";
    } else {
        echo "⚠ Found $totalIssues potential issues\n";
        echo "\nRecommendation: Run fix_country_cli.php to fix these issues\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
