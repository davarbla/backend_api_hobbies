<?php
// Quick script to check user and password hash
$email = 'davarbla.com@gmail.com';
$password = '123456';

// Generate password hash the same way as API
$hashedPassword = md5(sha1(hash("sha256", $password)));

echo "Email: $email\n";
echo "Plain password: $password\n";
echo "Generated hash: $hashedPassword\n\n";

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
    echo "Stored password hash: {$user['password_user']}\n";
    echo "\nPassword match: " . ($user['password_user'] === $hashedPassword ? "YES ✓" : "NO ✗") . "\n";
    
    if ($user['password_user'] !== $hashedPassword) {
        echo "\nTrying different hash methods:\n";
        echo "MD5 only: " . md5($password) . "\n";
        echo "SHA1 only: " . sha1($password) . "\n";
        echo "SHA256 only: " . hash("sha256", $password) . "\n";
        echo "SHA256+SHA1: " . sha1(hash("sha256", $password)) . "\n";
    }
} else {
    echo "User NOT found with email: $email\n";
    echo "\nChecking all users with similar email:\n";
    $result = $mysqli->query("SELECT id_user, email, fullname FROM tb_user WHERE email LIKE '%davarbla%'");
    while ($u = $result->fetch_assoc()) {
        echo "- {$u['email']} (ID: {$u['id_user']}, Name: {$u['fullname']})\n";
    }
    
    echo "\nChecking first 5 users in database:\n";
    $result = $mysqli->query("SELECT id_user, email, fullname, status FROM tb_user ORDER BY id_user ASC LIMIT 5");
    while ($u = $result->fetch_assoc()) {
        echo "- {$u['email']} (ID: {$u['id_user']}, Name: {$u['fullname']}, Status: {$u['status']})\n";
    }
}

$mysqli->close();
