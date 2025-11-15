<?php
/**
 * Check and create gallery upload directories
 */

$basePath = __DIR__ . '/root/public/upload/user/';

$directories = [
    'profile' => $basePath,
    'public' => $basePath . 'public/',
    'friends' => $basePath . 'friends/',
    'fun' => $basePath . 'fun/'
];

echo "Checking Gallery Upload Directories\n";
echo "====================================\n\n";

foreach ($directories as $type => $path) {
    echo "Checking '$type' gallery directory...\n";
    echo "Path: $path\n";
    
    if (file_exists($path)) {
        if (is_dir($path)) {
            echo "✅ Directory exists\n";
            
            // Check if writable
            if (is_writable($path)) {
                echo "✅ Directory is writable\n";
            } else {
                echo "⚠️  Directory exists but is NOT writable\n";
                echo "   Attempting to fix permissions...\n";
                if (chmod($path, 0777)) {
                    echo "✅ Permissions fixed\n";
                } else {
                    echo "❌ Failed to fix permissions\n";
                }
            }
        } else {
            echo "❌ Path exists but is NOT a directory\n";
        }
    } else {
        echo "⚠️  Directory does not exist\n";
        echo "   Creating directory...\n";
        if (mkdir($path, 0777, true)) {
            echo "✅ Directory created successfully\n";
        } else {
            echo "❌ Failed to create directory\n";
        }
    }
    echo "\n";
}

echo "Summary\n";
echo "=======\n";
echo "All gallery directories should now be ready for uploads:\n";
echo "- Profile images: root/public/upload/user/\n";
echo "- Public gallery: root/public/upload/user/public/\n";
echo "- Friends gallery: root/public/upload/user/friends/\n";
echo "- Fun gallery: root/public/upload/user/fun/\n";
?>
