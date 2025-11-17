<?php
// Fix password for davarbla@gmail.com
$email = 'davarbla@gmail.com';
$password = '123456';

// Generate password hash the same way as API
$hashedPassword = md5(sha1(hash("sha256", $password)));

echo "Fixing password for: $email\n";
echo "Setting hashed password: $hashedPassword\n\n";

// Connect to MySQL directly
$mysqli = new mysqli('localhost', 'root', '', 'hobbies');

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Check if user exists
$stmt = $mysqli->prepare("SELECT id_user, email, fullname, password_user, status FROM tb_user WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user) {
    echo "User found!\n";
    echo "ID: {$user['id_user']}\n";
    echo "Email: {$user['email']}\n";
    echo "Fullname: {$user['fullname']}\n";
    echo "Status: {$user['status']}\n";
    echo "Current password: {$user['password_user']}\n\n";
    
    if ($user['password_user'] !== $hashedPassword) {
        // Update the password
        $stmt2 = $mysqli->prepare("UPDATE tb_user SET password_user = ? WHERE email = ?");
        $stmt2->bind_param("ss", $hashedPassword, $email);
        
        if ($stmt2->execute()) {
            echo "✓ Password updated successfully!\n";
            echo "Affected rows: " . $stmt2->affected_rows . "\n";
        } else {
            echo "✗ Error updating password: " . $stmt2->error . "\n";
        }
    } else {
        echo "✓ Password already correct!\n";
    }
} else {
    echo "✗ User NOT found with email: $email\n";
    echo "\nThis user needs to be created first through registration.\n";
}

$mysqli->close();

echo "\nYou can now log in with:\n";
echo "Email: $email\n";
echo "Password: $password\n";
