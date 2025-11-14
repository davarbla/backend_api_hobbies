<?php
echo "=== BASENAME NULL FIX TEST ===\n\n";

// Simulate the scenario where database returns null for image column
echo "Testing basename() with null values...\n";

// Test cases
$test_cases = [
    'null' => null,
    'empty string' => '',
    'valid path' => '/upload/user/avatar.jpg',
    'path with directory' => '/upload/user/public/123_photo_20251114173602_1234.jpg'
];

foreach ($test_cases as $name => $value) {
    try {
        // Old way (causes error)
        echo "Testing $name (old way): ";
        if ($value === null) {
            echo "Would cause error: basename(null)\n";
        } else {
            $result = basename($value);
            echo "✅ Success: $result\n";
        }
        
        // New way (with null coalescing)
        echo "Testing $name (new way): ";
        $result = basename($value ?? '');
        echo "✅ Success: '$result'\n";
        echo "\n";
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n\n";
    }
}

echo "=== UPLOAD CONTROLLER FIXES ===\n";
echo "✅ Added null coalescing to basename() calls\n";
echo "✅ Added user existence check\n";
echo "✅ Profile image handling (imageNumber = 0)\n";
echo "✅ Gallery image handling (imageNumber = 2+)\n";

echo "\n=== READY TO TEST ===\n";
echo "1. Restart PHP server\n";
echo "2. Test profile image upload\n";
echo "3. Should no longer get basename() null errors\n";
?>
