<?php
// Fix passwords for all davarbla users
$password = '123456';

// Generate password hash the same way as API
$hashedPassword = md5(sha1(hash("sha256", $password)));

echo "Fixing passwords for all davarbla users...\n";
echo "Target hashed password: $hashedPassword\n\n";

// Connect to MySQL directly
$mysqli = new mysqli('localhost', 'root', '', 'hobbies');

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Find all davarbla users
$result = $mysqli->query("SELECT id_user, email, fullname, password_user, status FROM tb_user WHERE email LIKE '%davarbla%'");

echo "Found users:\n";
echo "============\n";
$usersToFix = [];
while ($user = $result->fetch_assoc()) {
    echo "- ID: {$user['id_user']}, Email: {$user['email']}, Name: {$user['fullname']}, Status: {$user['status']}\n";
    echo "  Current password: {$user['password_user']}\n";
    
    if ($user['password_user'] !== $hashedPassword) {
        echo "  → Needs fixing!\n";
        $usersToFix[] = $user;
    } else {
        echo "  → Already correct ✓\n";
    }
    echo "\n";
}

if (count($usersToFix) > 0) {
    echo "\nFixing " . count($usersToFix) . " user(s)...\n";
    echo "=======================\n\n";
    
    foreach ($usersToFix as $user) {
        $stmt = $mysqli->prepare("UPDATE tb_user SET password_user = ? WHERE id_user = ?");
        $stmt->bind_param("si", $hashedPassword, $user['id_user']);
        
        if ($stmt->execute()) {
            echo "✓ Fixed: {$user['email']} (ID: {$user['id_user']})\n";
        } else {
            echo "✗ Error fixing {$user['email']}: " . $stmt->error . "\n";
        }
    }
    
    // Verify all updates
    echo "\n\nVerification:\n";
    echo "=============\n";
    $result2 = $mysqli->query("SELECT id_user, email, password_user FROM tb_user WHERE email LIKE '%davarbla%'");
    while ($user = $result2->fetch_assoc()) {
        $match = ($user['password_user'] === $hashedPassword) ? "✓" : "✗";
        echo "$match {$user['email']}: " . ($user['password_user'] === $hashedPassword ? "CORRECT" : "INCORRECT") . "\n";
    }
} else {
    echo "\nAll users already have correct passwords! ✓\n";
}

$mysqli->close();

echo "\n\nAll users can now log in with password: 123456\n";
