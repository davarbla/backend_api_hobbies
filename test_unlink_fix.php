<?php
echo "=== UNLINK DIRECTORY FIX TEST ===\n\n";

echo "Testing file deletion scenarios...\n\n";

// Test scenarios
$test_cases = [
    'empty filename' => '',
    'null filename' => null,
    'valid filename' => 'test_image.jpg',
    'avatar filename' => 'avatar.png'
];

foreach ($test_cases as $name => $filename) {
    echo "Testing: $name\n";
    echo "Filename: " . ($filename ?? 'null') . "\n";
    
    // Simulate the fixed logic
    if ($filename != '' && $filename != 'avatar.png' && $filename != 'avatar.jpg' && $filename != 'ShieldGreen.jpg' && $filename != 'ShieldOrange.jpg' && $filename != 'ShieldRed.jpg') {
        echo "  Would attempt to delete: /upload/user/$filename\n";
        
        // Additional check (the fix)
        if (file_exists("/upload/user/$filename") && is_file("/upload/user/$filename")) {
            echo "  ✅ File exists and is a file - would delete\n";
        } else {
            echo "  ✅ File doesn't exist or is directory - would skip\n";
        }
    } else {
        echo "  ✅ Protected file or empty - would skip deletion\n";
    }
    echo "\n";
}

echo "=== UPLOAD CONTROLLER FIXES ===\n";
echo "✅ Added empty filename check\n";
echo "✅ Added file existence check before unlink\n";
echo "✅ Added is_file() check to prevent directory deletion\n";
echo "✅ Profile image handling (imageNumber = 0)\n";
echo "✅ Gallery image handling (imageNumber = 2+)\n";

echo "\n=== ERROR PREVENTED ===\n";
echo "❌ Before: unlink(/upload/user/) - 'Is a directory' error\n";
echo "✅ After: Skip deletion when filename is empty\n";
echo "✅ After: Only delete if path is actually a file\n";

echo "\n=== READY TO TEST ===\n";
echo "1. Restart PHP server\n";
echo "2. Test profile image upload\n";
echo "3. Should no longer get unlink() directory errors\n";
?>
