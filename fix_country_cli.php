<?php
/**
 * Fix country data CLI script
 * This fixes JSON parsing errors caused by NULL country values
 */

echo "=== Fix Country Data Script ===\n\n";

// Database configuration
$host = 'localhost';
$dbname = 'hobbies';
$username = 'root';
$password = '';

try {
    // Connect to database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Database Connected\n\n";
    
    // Fix tb_user table
    echo "Fixing tb_user table...\n";
    $stmt = $pdo->exec("UPDATE tb_user SET country = 'ZZ' WHERE country IS NULL OR country = ''");
    echo "  Fixed $stmt user records\n\n";
    
    // Fix tb_category table
    echo "Fixing tb_category table...\n";
    $stmt = $pdo->exec("UPDATE tb_category SET country = 'ZZ' WHERE country IS NULL OR country = ''");
    echo "  Fixed $stmt category records\n\n";
    
    // Fix tb_post table
    echo "Fixing tb_post table...\n";
    $stmt = $pdo->exec("UPDATE tb_post SET country = 'ZZ' WHERE country IS NULL OR country = ''");
    echo "  Fixed $stmt post records\n\n";
    
    // Verify the fix
    echo "Verification:\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM tb_user WHERE country IS NULL OR country = ''");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "  Users with NULL/empty country: {$result['count']}\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM tb_category WHERE country IS NULL OR country = ''");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "  Categories with NULL/empty country: {$result['count']}\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM tb_post WHERE country IS NULL OR country = ''");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "  Posts with NULL/empty country: {$result['count']}\n\n";
    
    echo "✓ COMPLETE!\n";
    echo "\nNext steps:\n";
    echo "1. Restart your Flutter app\n";
    echo "2. Pull to refresh on the home page\n";
    echo "3. The JSON parsing error should be fixed\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    echo "\nPlease check:\n";
    echo "- Database name: $dbname\n";
    echo "- MySQL is running\n";
    echo "- Credentials are correct\n";
    exit(1);
}
?>
