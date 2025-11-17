<?php
/**
 * Fix all NULL/empty values that could cause JSON parsing issues
 */

echo "=== Fix All NULL/Empty Values ===\n\n";

$host = 'localhost';
$dbname = 'hobbies';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Database Connected\n\n";
    
    // Fix users table - location
    echo "Fixing tb_user.location...\n";
    $stmt = $pdo->exec("UPDATE tb_user SET location = 'Unknown' WHERE location IS NULL OR location = ''");
    echo "  Fixed $stmt user location records\n\n";
    
    // Fix users table - country
    echo "Fixing tb_user.country...\n";
    $stmt = $pdo->exec("UPDATE tb_user SET country = 'ZZ' WHERE country IS NULL OR country = ''");
    echo "  Fixed $stmt user country records\n\n";
    
    // Fix users table - latitude
    echo "Fixing tb_user.latitude...\n";
    $stmt = $pdo->exec("UPDATE tb_user SET latitude = '0,0' WHERE latitude IS NULL OR latitude = ''");
    echo "  Fixed $stmt user latitude records\n\n";
    
    // Fix users table - lat/lng
    echo "Fixing tb_user.lat and lng...\n";
    $stmt = $pdo->exec("UPDATE tb_user SET lat = '0' WHERE lat IS NULL OR lat = ''");
    $stmt2 = $pdo->exec("UPDATE tb_user SET lng = '0' WHERE lng IS NULL OR lng = ''");
    echo "  Fixed $stmt lat and $stmt2 lng records\n\n";
    
    // Fix categories - country
    echo "Fixing tb_category.country...\n";
    $stmt = $pdo->exec("UPDATE tb_category SET country = 'ZZ' WHERE country IS NULL OR country = ''");
    echo "  Fixed $stmt category country records\n\n";
    
    // Fix categories - location
    echo "Fixing tb_category.location...\n";
    $stmt = $pdo->exec("UPDATE tb_category SET location = '' WHERE location IS NULL");
    echo "  Fixed $stmt category location records\n\n";
    
    // Fix posts - country
    echo "Fixing tb_post.country...\n";
    $stmt = $pdo->exec("UPDATE tb_post SET country = 'ZZ' WHERE country IS NULL OR country = ''");
    echo "  Fixed $stmt post country records\n\n";
    
    // Fix posts - location
    echo "Fixing tb_post.location...\n";
    $stmt = $pdo->exec("UPDATE tb_post SET location = '' WHERE location IS NULL");
    echo "  Fixed $stmt post location records\n\n";
    
    echo "✓ COMPLETE!\n\n";
    echo "All NULL/empty values have been fixed.\n";
    echo "\nNext steps:\n";
    echo "1. Restart your Flutter app (hot restart)\n";
    echo "2. Pull to refresh on the home page\n";
    echo "3. The data should now load correctly\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
