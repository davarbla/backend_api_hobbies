<?php
// Fix user password to be properly hashed
$email = 'davarbla.com@gmail.com';
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

// Update the password
$stmt = $mysqli->prepare("UPDATE tb_user SET password_user = ? WHERE email = ?");
$stmt->bind_param("ss", $hashedPassword, $email);

if ($stmt->execute()) {
    echo "✓ Password updated successfully!\n";
    echo "Affected rows: " . $stmt->affected_rows . "\n";
    
    // Verify the update
    $stmt2 = $mysqli->prepare("SELECT id_user, email, password_user FROM tb_user WHERE email = ?");
    $stmt2->bind_param("s", $email);
    $stmt2->execute();
    $result = $stmt2->get_result();
    $user = $result->fetch_assoc();
    
    echo "\nVerification:\n";
    echo "User ID: {$user['id_user']}\n";
    echo "Email: {$user['email']}\n";
    echo "Password hash: {$user['password_user']}\n";
    echo "Match: " . ($user['password_user'] === $hashedPassword ? "YES ✓" : "NO ✗") . "\n";
} else {
    echo "✗ Error updating password: " . $stmt->error . "\n";
}

$mysqli->close();

echo "\nYou can now log in with:\n";
echo "Email: $email\n";
echo "Password: $password\n";
